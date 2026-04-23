package com.albabor.app.data.repository

import com.albabor.app.data.model.Banner
import com.albabor.app.data.network.NetworkModule
import com.albabor.app.data.network.errorMessage

class BannerRepository {
    private val api = NetworkModule.apiService

    suspend fun getBanners(): Result<List<Banner>> = runCatching {
        val response = api.getBanners()
        if (response.isSuccessful) response.body()?.banners ?: emptyList()
        else throw Exception(response.errorMessage())
    }

    suspend fun trackClick(id: Int): Result<Unit> = runCatching {
        val response = api.trackBannerClick(id)
        if (!response.isSuccessful) throw Exception(response.errorMessage())
    }
}
