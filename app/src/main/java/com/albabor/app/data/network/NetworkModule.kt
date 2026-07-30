package com.albabor.app.data.network

import android.content.Context
import androidx.datastore.core.DataStore
import androidx.datastore.preferences.core.Preferences
import androidx.datastore.preferences.core.edit
import androidx.datastore.preferences.core.stringPreferencesKey
import androidx.datastore.preferences.preferencesDataStore
import com.google.gson.FieldNamingPolicy
import com.google.gson.GsonBuilder
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.SupervisorJob
import kotlinx.coroutines.flow.MutableSharedFlow
import kotlinx.coroutines.flow.SharedFlow
import kotlinx.coroutines.flow.asSharedFlow
import kotlinx.coroutines.flow.first
import kotlinx.coroutines.flow.map
import kotlinx.coroutines.launch
import okhttp3.Interceptor
import okhttp3.OkHttpClient
import okhttp3.logging.HttpLoggingInterceptor
import retrofit2.Retrofit
import retrofit2.converter.gson.GsonConverterFactory
import java.util.concurrent.TimeUnit

val Context.dataStore: DataStore<Preferences> by preferencesDataStore(name = "albabor_prefs")

object TokenStore {
    private val TOKEN_KEY = stringPreferencesKey("auth_token")

    // In-memory mirror of the persisted token. Interceptors read this synchronously
    // so they never block a network thread on a DataStore read (previous ANR risk).
    @Volatile
    private var cachedToken: String? = null
    @Volatile
    private var primed = false

    /** Best-effort token for interceptors — no suspension, no blocking. */
    fun cached(): String? = cachedToken

    suspend fun save(context: Context, token: String) {
        cachedToken = token
        primed = true
        context.dataStore.edit { it[TOKEN_KEY] = token }
    }

    suspend fun get(context: Context): String? {
        val token = context.dataStore.data.map { it[TOKEN_KEY] }.first()
        cachedToken = token
        primed = true
        return token
    }

    suspend fun clear(context: Context) {
        cachedToken = null
        primed = true
        context.dataStore.edit { it.remove(TOKEN_KEY) }
    }

    /** Load the persisted token into [cachedToken] once, at app startup. */
    suspend fun prime(context: Context) {
        if (!primed) get(context)
    }
}

class AuthInterceptor : Interceptor {
    override fun intercept(chain: Interceptor.Chain): okhttp3.Response {
        val token = TokenStore.cached()
        val original = chain.request()

        // Don't override Content-Type for multipart — OkHttp sets it automatically with the boundary
        val isMultipart = original.body?.contentType()?.type == "multipart"

        val request = original.newBuilder()
            .header("Accept", "application/json")
            .header("X-Requested-With", "XMLHttpRequest")
            .apply {
                if (!isMultipart) header("Content-Type", "application/json")
                token?.let { header("Authorization", "Bearer $it") }
            }
            .build()
        return chain.proceed(request)
    }
}

/**
 * Global session state. Emits on [unauthorized] whenever the server returns 401 for
 * an authenticated call so the UI can drop the user back to the Login screen.
 */
object SessionBus {
    private val _unauthorized = MutableSharedFlow<Unit>(extraBufferCapacity = 1)
    val unauthorized: SharedFlow<Unit> = _unauthorized.asSharedFlow()
    internal fun emitUnauthorized() { _unauthorized.tryEmit(Unit) }
}

class UnauthorizedInterceptor(private val context: Context) : Interceptor {
    private val scope = CoroutineScope(SupervisorJob() + Dispatchers.IO)

    override fun intercept(chain: Interceptor.Chain): okhttp3.Response {
        val response = chain.proceed(chain.request())
        if (response.code == 401) {
            // Don't trigger global logout on the login/register endpoints themselves —
            // those legitimately return 401 on bad credentials.
            val path = chain.request().url.encodedPath
            val isAuthAttempt = path.contains("/auth/login") ||
                path.contains("/auth/register") ||
                path.contains("/auth/google") ||
                path.contains("/auth/forgot-password")
            // Don't logout on background polling endpoints — a stale badge count
            // is not a reason to kick the user out of the app.
            val isBackgroundPoll = path.contains("/conversations/unread-count")
            if (!isAuthAttempt && !isBackgroundPoll) {
                scope.launch { TokenStore.clear(context) }
                SessionBus.emitUnauthorized()
            }
        }
        return response
    }
}

object NetworkModule {
    const val BASE_URL = "https://albabor.com/api/v1/"
    private lateinit var appContext: Context

    fun init(context: Context) {
        appContext = context.applicationContext
    }

    private val gson = GsonBuilder()
        .setLenient()
        .serializeNulls()
        // Auto-convert snake_case JSON ↔ camelCase Kotlin fields
        // (explicit @SerializedName annotations take priority over this policy)
        .setFieldNamingPolicy(FieldNamingPolicy.LOWER_CASE_WITH_UNDERSCORES)
        .create()

    private val loggingInterceptor = HttpLoggingInterceptor().apply {
        level = if (com.albabor.app.BuildConfig.DEBUG)
            HttpLoggingInterceptor.Level.BODY
        else
            HttpLoggingInterceptor.Level.NONE
    }

    private val okHttpClient: OkHttpClient by lazy {
        OkHttpClient.Builder()
            .addInterceptor(AuthInterceptor())
            .addInterceptor(UnauthorizedInterceptor(appContext))
            .addInterceptor(loggingInterceptor)
            .connectTimeout(30, TimeUnit.SECONDS)
            .readTimeout(30, TimeUnit.SECONDS)
            .writeTimeout(120, TimeUnit.SECONDS)
            .build()
    }

    val apiService: ApiService by lazy {
        Retrofit.Builder()
            .baseUrl(BASE_URL)
            .client(okHttpClient)
            .addConverterFactory(GsonConverterFactory.create(gson))
            .build()
            .create(ApiService::class.java)
    }
}
