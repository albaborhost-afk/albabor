package com.albabor.app.data.network

import android.content.Context
import androidx.datastore.core.DataStore
import androidx.datastore.preferences.core.Preferences
import androidx.datastore.preferences.core.edit
import androidx.datastore.preferences.core.stringPreferencesKey
import androidx.datastore.preferences.preferencesDataStore
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
        val request = chain.request().newBuilder()
            .addHeader("Accept", "application/json")
            .addHeader("Content-Type", "application/json")
            .apply { token?.let { addHeader("Authorization", "Bearer $it") } }
            .build()
        return chain.proceed(request)
    }
}

object NetworkModule {
    private const val BASE_URL = "https://albabor.com/api/v1/"
    private lateinit var appContext: Context

    fun init(context: Context) {
        appContext = context.applicationContext
    }

    private val loggingInterceptor = HttpLoggingInterceptor().apply {
        level = HttpLoggingInterceptor.Level.BODY
    }

    private val okHttpClient: OkHttpClient by lazy {
        OkHttpClient.Builder()
            .addInterceptor(AuthInterceptor(appContext))
            .addInterceptor(loggingInterceptor)
            .connectTimeout(30, TimeUnit.SECONDS)
            .readTimeout(30, TimeUnit.SECONDS)
            .writeTimeout(60, TimeUnit.SECONDS)
            .build()
    }

    val apiService: ApiService by lazy {
        Retrofit.Builder()
            .baseUrl(BASE_URL)
            .client(okHttpClient)
            .addConverterFactory(GsonConverterFactory.create())
            .build()
            .create(ApiService::class.java)
    }
}
