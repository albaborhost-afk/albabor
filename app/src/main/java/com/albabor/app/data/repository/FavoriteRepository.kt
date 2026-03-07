package com.albabor.app.data.repository

import com.albabor.app.data.model.Listing
import com.albabor.app.data.network.NetworkModule

class FavoriteRepository {
    private val api = NetworkModule.apiService

    suspend fun getFavorites(): Result<List<Listing>> = runCatching {
        val response = api.getFavorites()
        if (response.isSuccessful) response.body()?.data ?: emptyList()
        else throw Exception(response.errorBody()?.string() ?: "Erreur")
    }

    suspend fun addFavorite(listingId: Int): Result<Unit> = runCatching {
        val response = api.addFavorite(listingId)
        if (!response.isSuccessful) throw Exception(response.errorBody()?.string() ?: "Erreur")
    }

    suspend fun removeFavorite(listingId: Int): Result<Unit> = runCatching {
        val response = api.removeFavorite(listingId)
        if (!response.isSuccessful) throw Exception(response.errorBody()?.string() ?: "Erreur")
    }
}
