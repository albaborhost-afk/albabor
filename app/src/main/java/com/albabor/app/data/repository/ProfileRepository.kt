package com.albabor.app.data.repository

import com.albabor.app.data.model.*
import com.albabor.app.data.network.NetworkModule
import okhttp3.MediaType.Companion.toMediaType
import okhttp3.RequestBody.Companion.toRequestBody

class ProfileRepository {
    private val api = NetworkModule.apiService

    suspend fun getProfile(): Result<User> = runCatching {
        val response = api.getProfile()
        if (response.isSuccessful) response.body()?.data!!
        else throw Exception(response.errorBody()?.string() ?: "Erreur")
    }

    suspend fun updateProfile(name: String, phone: String): Result<User> = runCatching {
        val response = api.updateProfile(mapOf("name" to name, "phone" to phone))
        if (response.isSuccessful) response.body()?.data!!
        else throw Exception(response.errorBody()?.string() ?: "Erreur")
    }

    suspend fun updatePassword(current: String, new: String, confirm: String): Result<Unit> = runCatching {
        val response = api.updatePassword(
            mapOf(
                "current_password"      to current,
                "password"              to new,
                "password_confirmation" to confirm
            )
        )
        if (!response.isSuccessful) throw Exception(response.errorBody()?.string() ?: "Erreur")
    }

    suspend fun upgradeToVendor(): Result<User> = runCatching {
        val response = api.upgradeToVendor()
        if (response.isSuccessful) response.body()?.data!!
        else throw Exception(response.errorBody()?.string() ?: "Erreur")
    }

    suspend fun getPayments(): Result<List<Payment>> = runCatching {
        val response = api.getPayments()
        if (response.isSuccessful) response.body()?.data ?: emptyList()
        else throw Exception(response.errorBody()?.string() ?: "Erreur")
    }

    suspend fun uploadVerification(imageBytes: ByteArray, mimeType: String): Result<Unit> = runCatching {
        val mediaType = mimeType.toMediaType()
        val part = okhttp3.MultipartBody.Part.createFormData(
            "id_card", "id_card.jpg",
            imageBytes.toRequestBody(mediaType)
        )
        val response = api.uploadVerification(part)
        if (!response.isSuccessful) throw Exception(response.errorBody()?.string() ?: "Erreur")
    }

    suspend fun getSubscriptions(): Result<List<Subscription>> = runCatching {
        val response = api.getSubscriptions()
        if (response.isSuccessful) response.body()?.data ?: emptyList()
        else throw Exception(response.errorBody()?.string() ?: "Erreur")
    }
}
