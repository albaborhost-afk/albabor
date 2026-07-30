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

    /**
     * Toggles favorite status server-side (the API exposes a single toggle endpoint).
     * Returns true if the listing is now favorited, false if it was removed.
     */
    suspend fun toggleFavorite(listingId: Int): Result<Boolean> = runCatching {
        val response = api.toggleFavorite(listingId)
        if (response.isSuccessful) response.body()?.favorited ?: false
        else throw Exception(response.errorMessage())
    }
}
