package com.albabor.app.data.repository

import com.albabor.app.data.model.*
import com.albabor.app.data.network.NetworkModule

class ListingRepository {
    private val api = NetworkModule.apiService

    suspend fun getListings(filters: ListingFilters): Result<PaginatedResponse<Listing>> = runCatching {
        val response = api.getListings(filters.toQueryMap())
        if (response.isSuccessful) response.body()!!
        else throw Exception(response.errorBody()?.string() ?: "Erreur")
    }

    suspend fun getFeaturedListings(): Result<List<Listing>> = runCatching {
        val response = api.getFeaturedListings()
        if (response.isSuccessful) response.body()?.data ?: emptyList()
        else throw Exception(response.errorBody()?.string() ?: "Erreur")
    }

    suspend fun getLatestListings(limit: Int = 25): Result<List<Listing>> = runCatching {
        val response = api.getLatestListings(limit)
        if (response.isSuccessful) response.body()?.data ?: emptyList()
        else throw Exception(response.errorBody()?.string() ?: "Erreur")
    }

    suspend fun getListing(id: Int): Result<Listing> = runCatching {
        val response = api.getListing(id)
        if (response.isSuccessful) response.body()?.data!!
        else throw Exception(response.errorBody()?.string() ?: "Erreur")
    }

    suspend fun getMyListings(): Result<List<Listing>> = runCatching {
        val response = api.getMyListings()
        if (response.isSuccessful) response.body()?.data ?: emptyList()
        else throw Exception(response.errorBody()?.string() ?: "Erreur")
    }

    suspend fun deleteListing(id: Int): Result<Unit> = runCatching {
        val response = api.deleteListing(id)
        if (!response.isSuccessful) throw Exception(response.errorBody()?.string() ?: "Erreur")
    }

    suspend fun markAsSold(id: Int): Result<Listing> = runCatching {
        val response = api.markAsSold(id)
        if (response.isSuccessful) response.body()?.data!!
        else throw Exception(response.errorBody()?.string() ?: "Erreur")
    }

    suspend fun pauseListing(id: Int): Result<Listing> = runCatching {
        val response = api.pauseListing(id)
        if (response.isSuccessful) response.body()?.data!!
        else throw Exception(response.errorBody()?.string() ?: "Erreur")
    }

    suspend fun reactivateListing(id: Int): Result<Listing> = runCatching {
        val response = api.reactivateListing(id)
        if (response.isSuccessful) response.body()?.data!!
        else throw Exception(response.errorBody()?.string() ?: "Erreur")
    }
}
