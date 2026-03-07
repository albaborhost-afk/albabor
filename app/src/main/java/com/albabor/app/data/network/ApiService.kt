package com.albabor.app.data.network

import com.albabor.app.data.model.*
import okhttp3.MultipartBody
import okhttp3.RequestBody
import retrofit2.Response
import retrofit2.http.*

interface ApiService {

    // ─── Auth ─────────────────────────────────────────────────────────────────

    @POST("auth/login")
    suspend fun login(@Body request: LoginRequest): Response<AuthResponse>

    @POST("auth/register")
    suspend fun register(@Body request: RegisterRequest): Response<AuthResponse>

    @POST("auth/forgot-password")
    suspend fun forgotPassword(@Body body: Map<String, String>): Response<ApiResponse<Unit>>

    @POST("auth/logout")
    suspend fun logout(): Response<ApiResponse<Unit>>

    // ─── Profile ──────────────────────────────────────────────────────────────

    @GET("profile")
    suspend fun getProfile(): Response<ApiResponse<User>>

    @PUT("profile")
    suspend fun updateProfile(@Body body: Map<String, String>): Response<ApiResponse<User>>

    @POST("profile/update-password")
    suspend fun updatePassword(@Body body: Map<String, String>): Response<ApiResponse<Unit>>

    @POST("profile/upgrade-vendor")
    suspend fun upgradeToVendor(): Response<ApiResponse<User>>

    @Multipart
    @POST("profile/verification")
    suspend fun uploadVerification(
        @Part idCard: MultipartBody.Part
    ): Response<ApiResponse<Unit>>

    // ─── Listings ─────────────────────────────────────────────────────────────

    @GET("listings")
    suspend fun getListings(@QueryMap params: Map<String, String>): Response<PaginatedResponse<Listing>>

    @GET("listings/featured")
    suspend fun getFeaturedListings(): Response<ApiResponse<List<Listing>>>

    @GET("listings/latest")
    suspend fun getLatestListings(@Query("limit") limit: Int = 25): Response<ApiResponse<List<Listing>>>

    @GET("listings/{id}")
    suspend fun getListing(@Path("id") id: Int): Response<ApiResponse<Listing>>

    @GET("listings/my")
    suspend fun getMyListings(): Response<ApiResponse<List<Listing>>>

    @Multipart
    @POST("listings")
    suspend fun createListing(
        @PartMap data: Map<String, @JvmSuppressWildcards RequestBody>,
        @Part images: List<MultipartBody.Part>
    ): Response<ApiResponse<Listing>>

    @Multipart
    @POST("listings/{id}")
    suspend fun updateListing(
        @Path("id") id: Int,
        @PartMap data: Map<String, @JvmSuppressWildcards RequestBody>,
        @Part images: List<MultipartBody.Part>
    ): Response<ApiResponse<Listing>>

    @DELETE("listings/{id}")
    suspend fun deleteListing(@Path("id") id: Int): Response<ApiResponse<Unit>>

    @POST("listings/{id}/sold")
    suspend fun markAsSold(@Path("id") id: Int): Response<ApiResponse<Listing>>

    @POST("listings/{id}/pause")
    suspend fun pauseListing(@Path("id") id: Int): Response<ApiResponse<Listing>>

    @POST("listings/{id}/reactivate")
    suspend fun reactivateListing(@Path("id") id: Int): Response<ApiResponse<Listing>>

    // ─── Favorites ────────────────────────────────────────────────────────────

    @GET("favorites")
    suspend fun getFavorites(): Response<ApiResponse<List<Listing>>>

    @POST("listings/{id}/favorite")
    suspend fun addFavorite(@Path("id") id: Int): Response<ApiResponse<Unit>>

    @DELETE("listings/{id}/favorite")
    suspend fun removeFavorite(@Path("id") id: Int): Response<ApiResponse<Unit>>

    // ─── Payments ─────────────────────────────────────────────────────────────

    @GET("payments")
    suspend fun getPayments(): Response<ApiResponse<List<Payment>>>

    @Multipart
    @POST("payments")
    suspend fun submitPayment(
        @Part proof: MultipartBody.Part,
        @PartMap data: Map<String, @JvmSuppressWildcards RequestBody>
    ): Response<ApiResponse<Payment>>

    // ─── Subscriptions ────────────────────────────────────────────────────────

    @GET("subscriptions")
    suspend fun getSubscriptions(): Response<ApiResponse<List<Subscription>>>

    @Multipart
    @POST("subscriptions")
    suspend fun subscribeVendor(
        @Part proof: MultipartBody.Part
    ): Response<ApiResponse<Subscription>>

    // ─── Mediation ────────────────────────────────────────────────────────────

    @GET("mediation")
    suspend fun getMediationTickets(): Response<ApiResponse<List<MediationTicket>>>

    @GET("mediation/{id}")
    suspend fun getMediationTicket(@Path("id") id: Int): Response<ApiResponse<MediationTicket>>

    @POST("mediation")
    suspend fun createMediationTicket(@Body body: Map<String, String>): Response<ApiResponse<MediationTicket>>

    @POST("mediation/{id}/messages")
    suspend fun sendMediationMessage(
        @Path("id") id: Int,
        @Body body: Map<String, String>
    ): Response<ApiResponse<MediationMessage>>

    @DELETE("mediation/{id}")
    suspend fun cancelMediationTicket(@Path("id") id: Int): Response<ApiResponse<Unit>>

    // ─── Conversations ────────────────────────────────────────────────────────

    @GET("conversations")
    suspend fun getConversations(): Response<ApiResponse<List<Conversation>>>

    @GET("conversations/{id}")
    suspend fun getConversation(@Path("id") id: Int): Response<ApiResponse<Conversation>>

    @GET("conversations/{id}/messages")
    suspend fun getMessages(@Path("id") id: Int): Response<ApiResponse<List<ConversationMessage>>>

    @POST("conversations/{id}/messages")
    suspend fun sendMessage(
        @Path("id") id: Int,
        @Body body: Map<String, String>
    ): Response<ApiResponse<ConversationMessage>>
}
