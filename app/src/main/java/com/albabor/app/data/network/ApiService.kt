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

    @POST("auth/google")
    suspend fun loginWithGoogle(@Body request: GoogleLoginRequest): Response<AuthResponse>

    @POST("auth/forgot-password")
    suspend fun forgotPassword(@Body body: Map<String, String>): Response<ApiResponse<Unit>>

    @POST("auth/logout")
    suspend fun logout(): Response<ApiResponse<Unit>>

    // ─── Profile ──────────────────────────────────────────────────────────────

    @GET("profile")
    suspend fun getProfile(): Response<ProfileResponse>

    // Valeurs mixtes (String pour le nom, Boolean pour hide_name) : le corps
    // est typé Any pour éviter d'envoyer « true » sous forme de chaîne.
    @PUT("profile")
    suspend fun updateProfile(@Body body: Map<String, @JvmSuppressWildcards Any>): Response<ApiResponse<User>>

    @PUT("profile/password")
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

    @GET("listings/{id}")
    suspend fun getListing(@Path("id") id: Int): Response<ListingDetailResponse>

    /** Profil public d'un vendeur : ses annonces actives et ses compteurs. */
    @GET("vendors/{id}")
    suspend fun getSellerProfile(
        @Path("id") id: Int,
        @Query("page") page: Int = 1,
    ): Response<SellerProfileResponse>

    @GET("my-listings")
    suspend fun getMyListings(): Response<PaginatedResponse<Listing>>

    @Multipart
    @POST("listings")
    suspend fun createListing(
        @Part parts: List<MultipartBody.Part>
    ): Response<CreateListingResponse>

    @Multipart
    @PUT("listings/{id}")
    suspend fun updateListing(
        @Path("id") id: Int,
        @PartMap data: Map<String, @JvmSuppressWildcards RequestBody>,
        @Part newImages: List<MultipartBody.Part>
    ): Response<ListingActionResponse>

    @DELETE("listings/{id}")
    suspend fun deleteListing(@Path("id") id: Int): Response<ApiResponse<Unit>>

    @POST("listings/{id}/sold")
    suspend fun markAsSold(@Path("id") id: Int): Response<ListingActionResponse>

    @POST("listings/{id}/pause")
    suspend fun pauseListing(@Path("id") id: Int): Response<ListingActionResponse>

    @POST("listings/{id}/reactivate")
    suspend fun reactivateListing(@Path("id") id: Int): Response<ListingActionResponse>

    @POST("listings/{id}/renew")
    suspend fun renewListing(@Path("id") id: Int): Response<RenewListingResponse>

    // ─── Favorites ────────────────────────────────────────────────────────────

    @GET("favorites")
    suspend fun getFavorites(): Response<PaginatedResponse<Listing>>

    @POST("favorites/{id}")
    suspend fun toggleFavorite(@Path("id") id: Int): Response<ToggleFavoriteResponse>

    // ─── Payments ─────────────────────────────────────────────────────────────

    @GET("payments")
    suspend fun getPayments(): Response<PaginatedResponse<Payment>>

    @Multipart
    @POST("payments/listing/{listingId}")
    suspend fun submitListingPayment(
        @Path("listingId") listingId: Int,
        @Part proof: MultipartBody.Part,
        @PartMap data: Map<String, @JvmSuppressWildcards RequestBody>
    ): Response<PaymentSubmitResponse>

    @Multipart
    @POST("payments/feature/{listingId}")
    suspend fun submitFeaturePayment(
        @Path("listingId") listingId: Int,
        @Part proof: MultipartBody.Part,
        @PartMap data: Map<String, @JvmSuppressWildcards RequestBody>
    ): Response<PaymentSubmitResponse>

    @Multipart
    @POST("payments/subscription")
    suspend fun submitSubscriptionPayment(
        @Part proof: MultipartBody.Part,
        @PartMap data: Map<String, @JvmSuppressWildcards RequestBody>
    ): Response<PaymentSubmitResponse>

    @Multipart
    @POST("payments/mediation")
    suspend fun submitMediationPayment(
        @Part proof: MultipartBody.Part,
        @PartMap data: Map<String, @JvmSuppressWildcards RequestBody>
    ): Response<PaymentSubmitResponse>

    // ─── Subscriptions ────────────────────────────────────────────────────────

    @GET("subscriptions")
    suspend fun getSubscriptions(): Response<ApiResponse<List<Subscription>>>

    // ─── Mediation ────────────────────────────────────────────────────────────

    @GET("mediation")
    suspend fun getMediationTickets(): Response<MediationTicketsResponse>

    @GET("mediation/{id}")
    suspend fun getMediationTicket(@Path("id") id: Int): Response<MediationTicketResponse>

    @POST("mediation/{listingId}")
    suspend fun createMediationTicket(
        @Path("listingId") listingId: Int,
        @Body body: Map<String, String>
    ): Response<MediationTicketActionResponse>

    @POST("mediation/{id}/message")
    suspend fun sendMediationMessage(
        @Path("id") id: Int,
        @Body body: Map<String, String>
    ): Response<MediationTicketActionResponse>

    @POST("mediation/{id}/cancel")
    suspend fun cancelMediationTicket(@Path("id") id: Int): Response<MediationTicketActionResponse>

    // ─── Conversations ────────────────────────────────────────────────────────

    @GET("conversations/unread-count")
    suspend fun getUnreadCount(): Response<UnreadCountResponse>

    @GET("conversations")
    suspend fun getConversations(): Response<PaginatedResponse<Conversation>>

    @GET("conversations/{id}")
    suspend fun getConversation(@Path("id") id: Int): Response<Conversation>

    @POST("conversations/listing/{listingId}")
    suspend fun startConversation(
        @Path("listingId") listingId: Int,
        @Body body: Map<String, String>
    ): Response<StartConversationResponse>

    @GET("conversations/{id}/messages")
    suspend fun getMessages(@Path("id") id: Int): Response<ApiResponse<List<ConversationMessage>>>

    @POST("conversations/{id}/messages")
    suspend fun sendMessage(
        @Path("id") id: Int,
        @Body body: Map<String, String>
    ): Response<ConversationMessage>

    // ─── Banners ──────────────────────────────────────────────────────────────

    @GET("banners")
    suspend fun getBanners(): Response<BannersResponse>

    @POST("banners/{id}/click")
    suspend fun trackBannerClick(@Path("id") id: Int): Response<Map<String, Any>>
}
