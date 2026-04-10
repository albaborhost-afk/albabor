package com.albabor.app.data.repository

import com.albabor.app.data.model.*
import com.albabor.app.data.network.NetworkModule
import com.albabor.app.data.network.errorMessage
import okhttp3.MediaType.Companion.toMediaType
import okhttp3.RequestBody.Companion.toRequestBody

class ProfileRepository {
    private val api = NetworkModule.apiService

    suspend fun getProfile(): Result<User> = runCatching {
        val response = api.getProfile()
        if (!response.isSuccessful) throw Exception(response.errorMessage())

        val body = response.body() ?: throw Exception("Empty response body")
        val stats = body.stats.orEmpty()

        body.user.copy(
            listingsCount = body.user.listingsCount.takeUnless { it == 0 }
                ?: stats["listings_count"]
                ?: stats["listings"]
                ?: 0,
            favoritesCount = body.user.favoritesCount.takeUnless { it == 0 }
                ?: stats["favorites_count"]
                ?: stats["favorites"]
                ?: 0,
        )
    }

    suspend fun updateProfile(name: String, phone: String): Result<User> = runCatching {
        val response = api.updateProfile(mapOf("name" to name, "phone" to phone))
        if (response.isSuccessful) response.body()?.data ?: throw Exception("Empty response body")
        else throw Exception(response.errorMessage())
    }

    suspend fun updatePassword(current: String, new: String, confirm: String): Result<Unit> = runCatching {
        val response = api.updatePassword(
            mapOf(
                "current_password"      to current,
                "password"              to new,
                "password_confirmation" to confirm
            )
        )
        if (!response.isSuccessful) throw Exception(response.errorMessage())
    }

    suspend fun upgradeToVendor(): Result<User> = runCatching {
        val response = api.upgradeToVendor()
        if (response.isSuccessful) response.body()?.data ?: throw Exception("Empty response body")
        else throw Exception(response.errorMessage())
    }

    suspend fun getPayments(): Result<List<Payment>> = runCatching {
        val response = api.getPayments()
        if (response.isSuccessful) response.body()?.data ?: emptyList()
        else throw Exception(response.errorMessage())
    }

    suspend fun uploadVerification(imageBytes: ByteArray, mimeType: String): Result<Unit> = runCatching {
        val mediaType = mimeType.toMediaType()
        val part = okhttp3.MultipartBody.Part.createFormData(
            "id_card", "id_card.jpg",
            imageBytes.toRequestBody(mediaType)
        )
        val response = api.uploadVerification(part)
        if (!response.isSuccessful) throw Exception(response.errorMessage())
    }

    suspend fun getSubscriptions(): Result<List<Subscription>> = runCatching {
        val response = api.getSubscriptions()
        if (response.isSuccessful) response.body()?.data ?: emptyList()
        else throw Exception(response.errorMessage())
    }
}
