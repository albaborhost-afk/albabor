package com.albabor.app.data.repository

import android.content.Context
import com.albabor.app.data.model.*
import com.albabor.app.data.network.NetworkModule
import com.albabor.app.data.network.TokenStore
import com.albabor.app.data.network.errorMessage

class AuthRepository(private val context: Context) {
    private val api = NetworkModule.apiService

    suspend fun login(email: String, password: String): Result<AuthResponse> = runCatching {
        val response = api.login(LoginRequest(email, password))
        if (response.isSuccessful) {
            val body = response.body()!!
            TokenStore.save(context, body.token)
            body
        } else {
            throw Exception(response.errorMessage("Connexion échouée"))
        }
    }

    suspend fun register(name: String, email: String, phone: String, password: String): Result<AuthResponse> = runCatching {
        val response = api.register(RegisterRequest(name, email, phone, password, password))
        if (response.isSuccessful) {
            val body = response.body()!!
            TokenStore.save(context, body.token)
            body
        } else {
            throw Exception(response.errorMessage("Inscription échouée"))
        }
    }

    suspend fun forgotPassword(email: String): Result<Unit> = runCatching {
        val response = api.forgotPassword(mapOf("email" to email))
        if (!response.isSuccessful) throw Exception(response.errorMessage())
    }

    suspend fun logout(): Result<Unit> = runCatching {
        try { api.logout() } catch (_: Exception) {}
        TokenStore.clear(context)
    }

    suspend fun isLoggedIn(): Boolean = TokenStore.get(context) != null
}
