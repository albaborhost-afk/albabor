package com.albabor.app.data.repository

import android.content.Context
import com.albabor.app.data.model.*
import com.albabor.app.data.network.NetworkModule
import com.albabor.app.data.network.TokenStore

class AuthRepository(private val context: Context) {
    private val api = NetworkModule.apiService

    suspend fun login(email: String, password: String): Result<AuthResponse> = runCatching {
        val response = api.login(LoginRequest(email, password))
        if (response.isSuccessful) {
            val body = response.body()!!
            TokenStore.save(context, body.token)
            body
        } else {
            throw Exception(response.errorBody()?.string() ?: "Connexion échouée")
        }
    }

    suspend fun register(name: String, email: String, phone: String, password: String): Result<AuthResponse> = runCatching {
        val response = api.register(RegisterRequest(name, email, phone, password, password))
        if (response.isSuccessful) {
            val body = response.body()!!
            TokenStore.save(context, body.token)
            body
        } else {
            throw Exception(response.errorBody()?.string() ?: "Inscription échouée")
        }
    }

    suspend fun forgotPassword(email: String): Result<Unit> = runCatching {
        val response = api.forgotPassword(mapOf("email" to email))
        if (!response.isSuccessful) {
            throw Exception(response.errorBody()?.string() ?: "Erreur")
        }
    }

    suspend fun logout(): Result<Unit> = runCatching {
        try { api.logout() } catch (_: Exception) {}
        TokenStore.clear(context)
    }

    suspend fun isLoggedIn(): Boolean = TokenStore.get(context) != null
}
