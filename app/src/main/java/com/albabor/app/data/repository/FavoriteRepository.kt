package com.albabor.app.data.repository

import com.albabor.app.data.model.Listing
import com.albabor.app.data.network.NetworkModule
import com.albabor.app.data.network.errorMessage

class FavoriteRepository {
    private val api = NetworkModule.apiService

    suspend fun getFavorites(): Result<List<Listing>> = runCatching {
        val response = api.getFavorites()
        if (response.isSuccessful) response.body()?.data ?: emptyList()
        else throw Exception(response.errorMessage())
    }

    /** Toggles favorite status. Returns true if now favorited, false if removed. */
    suspend fun toggleFavorite(listingId: Int): Result<Boolean> = runCatching {
        val response = api.toggleFavorite(listingId)
        if (response.isSuccessful) response.body()?.favorited ?: false
        else throw Exception(response.errorMessage())
    }

    /** Adds or ensures the listing is favorited. */
    suspend fun addFavorite(listingId: Int): Result<Unit> = runCatching {
        toggleFavorite(listingId).getOrThrow()
    }

    /** Removes or ensures the listing is not favorited. */
    suspend fun removeFavorite(listingId: Int): Result<Unit> = runCatching {
        toggleFavorite(listingId).getOrThrow()
    }
}
