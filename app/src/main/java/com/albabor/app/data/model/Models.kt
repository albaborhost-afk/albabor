package com.albabor.app.data.model

import com.google.gson.annotations.SerializedName
import java.text.NumberFormat
import java.util.Locale

// ─── User ───────────────────────────────────────────────────────────────────

data class User(
    val id: Int,
    val name: String,
    val email: String,
    val phone: String?,
    val avatar: String?,
    @SerializedName("is_verified") val isVerified: Boolean = false,
    @SerializedName("is_vendor") val isVendor: Boolean = false,
    val role: String = "user",
    @SerializedName("listings_count") val listingsCount: Int = 0,
    @SerializedName("favorites_count") val favoritesCount: Int = 0,
    @SerializedName("created_at") val createdAt: String = ""
)

// ─── Listing ─────────────────────────────────────────────────────────────────

data class Listing(
    val id: Int,
    val title: String,
    val description: String?,
    @SerializedName("price_dzd") val priceDzd: Double,
    val currency: String = "DZD",        // "DZD" or "EUR"
    @SerializedName("converted_price") val convertedPrice: Double? = null,
    val category: String,                 // "boat" | "jetski" | "engine" | "parts"
    val status: String = "active",
    val wilaya: String?,
    val images: List<ListingImage> = emptyList(),
    val specs: Map<String, Any?>? = null,
    val user: ListingUser?,
    @SerializedName("created_at") val createdAt: String = "",
    @SerializedName("views_count") val viewsCount: Int = 0,
    @SerializedName("favorites_count") val favoritesCount: Int = 0,
    @SerializedName("mediation_enabled") val mediationEnabled: Boolean = false,
    @SerializedName("is_featured") val isFeatured: Boolean = false,
    @SerializedName("is_favorited") val isFavorited: Boolean = false,
    val condition: String? = null,        // "new" | "like_new" | "good" | "average" | "needs_revision"
    @SerializedName("offer_type") val offerType: String? = null  // "negotiable" | "fixed" | "free"
) {
    val formattedPrice: String
        get() = when (currency) {
            "EUR" -> "%.0f €".format(priceDzd)
            else  -> "%,.0f DA".format(priceDzd).replace(",", " ")
        }

    val formattedConvertedPrice: String?
        get() = convertedPrice?.let {
            when (currency) {
                "EUR" -> "%,.0f DA".format(it).replace(",", " ")
                else  -> "≈ %.0f €".format(it)
            }
        }

    val primaryImage: String?
        get() = images.firstOrNull { it.isPrimary }?.url ?: images.firstOrNull()?.url

    val categoryLabel: String
        get() = when (category) {
            "boat"   -> "Bateaux"
            "jetski" -> "Jet-Ski"
            "engine" -> "Moteur"
            "parts"  -> "Pièces"
            else     -> category
        }

    val categoryIcon: String
        get() = when (category) {
            "boat"   -> "⛵"
            "jetski" -> "🚤"
            "engine" -> "⚙️"
            "parts"  -> "🔩"
            else     -> "📋"
        }

    val statusLabel: String
        get() = when (status) {
            "draft"            -> "Brouillon"
            "awaiting_payment" -> "En attente de paiement"
            "pending_review"   -> "En cours de review"
            "active"           -> "Active"
            "sold"             -> "Vendu"
            "expired"          -> "Expiré"
            "paused"           -> "En pause"
            "rejected"         -> "Rejeté"
            else               -> status
        }

    val timeAgo: String
        get() {
            // Simple relative time — real implementation would parse createdAt
            return createdAt.take(10)
        }

    fun getSpec(group: String, key: String): String? {
        val groupMap = specs?.get(group) as? Map<*, *> ?: return null
        return groupMap[key]?.toString()
    }

    val year: String?    get() = getSpec("general", "annee_construction")
    val power: String?   get() = getSpec("motorisation", "puissance_totale")
    val length: String?  get() = getSpec("dimensions", "longueur")
}

data class ListingImage(
    val id: Int,
    val url: String,
    @SerializedName("is_primary") val isPrimary: Boolean = false
)

data class ListingUser(
    val id: Int,
    val name: String,
    val avatar: String?,
    @SerializedName("is_verified") val isVerified: Boolean = false,
    val phone: String?
)

// ─── Auth ─────────────────────────────────────────────────────────────────────

data class LoginRequest(val email: String, val password: String)

data class RegisterRequest(
    val name: String,
    val email: String,
    val phone: String,
    val password: String,
    @SerializedName("password_confirmation") val passwordConfirmation: String
)

data class AuthResponse(
    val token: String,
    val user: User
)

// ─── API Wrappers ─────────────────────────────────────────────────────────────

data class ApiResponse<T>(
    val data: T?,
    val message: String? = null,
    val success: Boolean = true
)

data class PaginatedResponse<T>(
    val data: List<T>,
    val meta: PaginationMeta? = null
)

data class PaginationMeta(
    @SerializedName("current_page") val currentPage: Int,
    @SerializedName("last_page") val lastPage: Int,
    val total: Int,
    @SerializedName("per_page") val perPage: Int
)

// ─── Mediation ───────────────────────────────────────────────────────────────

data class MediationTicket(
    val id: Int,
    val listing: Listing?,
    val buyer: User?,
    val seller: User?,
    val status: String,    // "open" | "in_progress" | "resolved" | "closed"
    val messages: List<MediationMessage> = emptyList(),
    @SerializedName("created_at") val createdAt: String = ""
) {
    val statusLabel: String
        get() = when (status) {
            "open"        -> "Ouvert"
            "in_progress" -> "En cours"
            "resolved"    -> "Résolu"
            "closed"      -> "Fermé"
            else          -> status
        }
}

data class MediationMessage(
    val id: Int,
    val body: String,
    val sender: User?,
    @SerializedName("created_at") val createdAt: String = ""
)

// ─── Conversations ────────────────────────────────────────────────────────────

data class Conversation(
    val id: Int,
    val listing: Listing?,
    val otherUser: User?,
    @SerializedName("last_message") val lastMessage: ConversationMessage?,
    @SerializedName("unread_count") val unreadCount: Int = 0,
    @SerializedName("created_at") val createdAt: String = ""
)

data class ConversationMessage(
    val id: Int,
    val body: String,
    @SerializedName("sender_id") val senderId: Int,
    @SerializedName("created_at") val createdAt: String = ""
)

// ─── Payments ────────────────────────────────────────────────────────────────

data class Payment(
    val id: Int,
    val amount: Double,
    val type: String,      // "publish" | "feature" | "subscription" | "mediation"
    val status: String,    // "pending" | "approved" | "rejected"
    val listing: Listing?,
    @SerializedName("proof_url") val proofUrl: String?,
    @SerializedName("created_at") val createdAt: String = ""
) {
    val statusLabel: String
        get() = when (status) {
            "pending"  -> "En attente"
            "approved" -> "Approuvé"
            "rejected" -> "Rejeté"
            else       -> status
        }

    val typeLabel: String
        get() = when (type) {
            "publish"      -> "Publication"
            "feature"      -> "Mise en avant"
            "subscription" -> "Abonnement"
            "mediation"    -> "Médiation"
            else           -> type
        }

    val formattedAmount: String
        get() = "%,.0f DA".format(amount).replace(",", " ")
}

// ─── Subscription ─────────────────────────────────────────────────────────────

data class Subscription(
    val id: Int,
    val plan: String,
    val status: String,   // "active" | "expired" | "cancelled"
    @SerializedName("expires_at") val expiresAt: String?,
    @SerializedName("created_at") val createdAt: String = ""
)

// ─── Listing filter params ────────────────────────────────────────────────────

data class ListingFilters(
    val search: String? = null,
    val category: String? = null,
    val wilaya: String? = null,
    val currency: String? = null,
    val minPrice: Double? = null,
    val maxPrice: Double? = null,
    val condition: String? = null,
    val offerType: String? = null,
    val sortBy: String? = null,   // "newest" | "price_asc" | "price_desc" | "popular"
    val page: Int = 1
) {
    fun toQueryMap(): Map<String, String> = buildMap {
        search?.let    { put("search", it) }
        category?.let  { put("category", it) }
        wilaya?.let    { put("wilaya", it) }
        currency?.let  { put("currency", it) }
        minPrice?.let  { put("min_price", it.toString()) }
        maxPrice?.let  { put("max_price", it.toString()) }
        condition?.let { put("condition", it) }
        offerType?.let { put("offer_type", it) }
        sortBy?.let    { put("sort", it) }
        put("page", page.toString())
    }
}
