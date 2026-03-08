package com.albabor.app.data.repository

import com.albabor.app.data.model.*
import com.albabor.app.data.network.NetworkModule
import com.albabor.app.data.network.errorMessage

class ListingRepository {
    private val api = NetworkModule.apiService

    suspend fun getListings(filters: ListingFilters): Result<PaginatedResponse<Listing>> = runCatching {
        val response = api.getListings(filters.toQueryMap())
        if (response.isSuccessful) response.body()!!
        else throw Exception(response.errorMessage())
    }

    suspend fun getFeaturedListings(): Result<List<Listing>> = runCatching {
        val response = api.getFeaturedListings()
        if (response.isSuccessful) response.body()?.data ?: emptyList()
        else throw Exception(response.errorMessage())
    }

    suspend fun getListing(id: Int): Result<Listing> = runCatching {
        val response = api.getListing(id)
        if (response.isSuccessful) {
            val body = response.body()!!
            // Merge top-level is_favorited into the listing object
            body.listing.copy(isFavorited = body.isFavorited)
        } else throw Exception(response.errorMessage())
    }

    suspend fun getMyListings(): Result<List<Listing>> = runCatching {
        val response = api.getMyListings()
        if (response.isSuccessful) response.body()?.data ?: emptyList()
        else throw Exception(response.errorMessage())
    }

    suspend fun deleteListing(id: Int): Result<Unit> = runCatching {
        val response = api.deleteListing(id)
        if (!response.isSuccessful) throw Exception(response.errorMessage())
    }

    suspend fun markAsSold(id: Int): Result<Listing> = runCatching {
        val response = api.markAsSold(id)
        if (response.isSuccessful) response.body()?.listing!!
        else throw Exception(response.errorMessage())
    }

    suspend fun pauseListing(id: Int): Result<Listing> = runCatching {
        val response = api.pauseListing(id)
        if (response.isSuccessful) response.body()?.listing!!
        else throw Exception(response.errorMessage())
    }

    suspend fun reactivateListing(id: Int): Result<Listing> = runCatching {
        val response = api.reactivateListing(id)
        if (response.isSuccessful) response.body()?.listing!!
        else throw Exception(response.errorMessage())
    }
}
