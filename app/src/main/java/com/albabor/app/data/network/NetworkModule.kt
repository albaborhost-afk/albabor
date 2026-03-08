package com.albabor.app.data.network

import android.content.Context
import androidx.datastore.core.DataStore
import androidx.datastore.preferences.core.Preferences
import androidx.datastore.preferences.core.edit
import androidx.datastore.preferences.core.stringPreferencesKey
import androidx.datastore.preferences.preferencesDataStore
import com.google.gson.FieldNamingPolicy
import com.google.gson.GsonBuilder
import kotlinx.coroutines.flow.first
import kotlinx.coroutines.flow.map
import kotlinx.coroutines.runBlocking
import okhttp3.Interceptor
import okhttp3.OkHttpClient
import okhttp3.logging.HttpLoggingInterceptor
import retrofit2.Retrofit
import retrofit2.converter.gson.GsonConverterFactory
import java.util.concurrent.TimeUnit

val Context.dataStore: DataStore<Preferences> by preferencesDataStore(name = "albabor_prefs")

object TokenStore {
    private val TOKEN_KEY = stringPreferencesKey("auth_token")

    suspend fun save(context: Context, token: String) {
        context.dataStore.edit { it[TOKEN_KEY] = token }
    }

    suspend fun get(context: Context): String? {
        return context.dataStore.data.map { it[TOKEN_KEY] }.first()
    }

    suspend fun clear(context: Context) {
        context.dataStore.edit { it.remove(TOKEN_KEY) }
    }
}

class AuthInterceptor(private val context: Context) : Interceptor {
    override fun intercept(chain: Interceptor.Chain): okhttp3.Response {
        val token = runBlocking { TokenStore.get(context) }
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
        level = HttpLoggingInterceptor.Level.BODY
    }

    private val okHttpClient: OkHttpClient by lazy {
        OkHttpClient.Builder()
            .addInterceptor(AuthInterceptor(appContext))
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
