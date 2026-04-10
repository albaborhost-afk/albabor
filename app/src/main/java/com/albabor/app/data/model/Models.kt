package com.albabor.app.data.model

import com.google.gson.annotations.SerializedName

private const val LISTING_MEDIA_BASE = "https://albabor.com"
private const val LISTING_MEDIA_ROUTE = "$LISTING_MEDIA_BASE/media/listings"

private fun String?.ifNotBlank(): String? = this?.takeIf { it.isNotBlank() }

/** Ensures URL is absolute so Coil can load it (API may send relative paths). */
private fun String.ensureAbsoluteUrl(): String =
    if (startsWith("http://") || startsWith("https://")) this else "$LISTING_MEDIA_BASE$this"

private fun formatWholeAmount(value: Double): String =
    "%,.0f".format(value).replace(",", " ")

/** Boat types: API slug → French label. Only boats have types for now. */
val BOAT_TYPES: Map<String, String> = linkedMapOf(
    "yacht"           to "Yacht",
    "voilier_catamaran" to "Voilier / Catamaran",
    "bateau_moteur"   to "Bateaux \u00e0 moteur",
    "cabine_cruiser"  to "Cabine Cruiser",
    "semi_rigide"     to "Semi-rigides (RIBs / Bateaux pneumatiques)",
    "bateau_peche"    to "Bateaux de p\u00eache",
)

// ─── User ───────────────────────────────────────────────────────────────────

data class User(
    val id: Int,
    val name: String,
    val email: String,
    val phone: String?,
    @SerializedName(value = "avatar", alternate = ["profile_picture_url"]) val avatar: String?,
    @SerializedName(value = "is_verified", alternate = ["verified_badge"]) val isVerified: Boolean = false,
    @SerializedName("is_vendor") val isVendor: Boolean = false,
    @SerializedName(value = "role", alternate = ["account_type"]) val role: String = "user",
    @SerializedName("listings_count") val listingsCount: Int = 0,
    @SerializedName("favorites_count") val favoritesCount: Int = 0,
    @SerializedName("created_at") val createdAt: String = "",
    @SerializedName("verification_status") val verificationStatus: String = "none"
    // verificationStatus: "none" | "pending" | "approved" | "rejected"
) {
    val isVerifiedAccount: Boolean
        get() = isVerified || verificationStatus == "approved"

    val isVendorAccount: Boolean
        get() = isVendor || role == "vendor" || role == "admin"
}

// ─── Listing ─────────────────────────────────────────────────────────────────

data class Listing(
    val id: Int,
    val title: String,
    val description: String?,
    @SerializedName("price_dzd") val priceDzd: Double,
    val currency: String = "DZD",        // "DZD" or "EUR"
    @SerializedName("converted_price") val convertedPrice: Double? = null,
    val category: String,                 // "boat" | "jetski" | "engine" | "parts"
    val type: String? = null,             // boat type slug (only for category "boat")
    val status: String = "active",
    val wilaya: String?,
    @SerializedName(value = "media", alternate = ["images"]) val images: List<ListingImage> = emptyList(),
    val specs: Map<String, Any?>? = null,
    val user: ListingUser?,
    @SerializedName("created_at") val createdAt: String = "",
    @SerializedName("views_count") val viewsCount: Int = 0,
    @SerializedName("favorites_count") val favoritesCount: Int = 0,
    @SerializedName("mediation_enabled") val mediationEnabled: Boolean = false,
    @SerializedName("is_featured") val isFeatured: Boolean = false,
    @SerializedName("is_favorited") val isFavorited: Boolean = false,
    @SerializedName("etat") val condition: String? = null,
    // "jamais_utilise" | "comme_neuf" | "bon_etat" | "etat_moyen" | "a_reviser"
    @SerializedName("type_offre") val offerType: String? = null,
    // "negociable" | "offert" | "fix"
    @SerializedName("numero_whatsapp") val numeroWhatsapp: String? = null,
    @SerializedName("numero_mobile") val numeroMobile: String? = null,
    @SerializedName("contact_email") val contactEmail: String? = null,
    @SerializedName("remarque_echange") val remarqueEchange: String? = null,
) {
    val formattedPrice: String
        get() = when (currency) {
            "EUR" -> "${formatWholeAmount(priceDzd)} €"
            else  -> "${formatWholeAmount(priceDzd)} DA"
        }

    val formattedConvertedPrice: String?
        get() = convertedPrice?.let {
            when (currency) {
                "EUR" -> "${formatWholeAmount(it)} DA"
                else  -> "≈ ${formatWholeAmount(it)} €"
            }
        }

    val primaryImage: String?
        get() = images.firstOrNull()?.cardUrl

    val galleryImages: List<String>
        get() = images.mapNotNull { it.detailUrl }.distinct()

    val categoryLabel: String
        get() = when (category) {
            "boat"   -> "Bateau"
            "jetski" -> "Jet-Ski"
            "engine" -> "Moteur"
            "parts"  -> "Pièce"
            else     -> category
        }

    val categoryPluralLabel: String
        get() = when (category) {
            "boat"   -> "Bateaux"
            "jetski" -> "Jet-Ski"
            "engine" -> "Moteurs"
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

    val typeLabel: String?
        get() = type?.let { BOAT_TYPES[it] ?: it }

    val conditionLabel: String
        get() = when (condition) {
            "jamais_utilise" -> "Jamais utilisé"
            "comme_neuf"     -> "Comme neuf"
            "bon_etat"       -> "Bon état"
            "etat_moyen"     -> "État moyen"
            "a_reviser"      -> "À réviser"
            else             -> condition ?: ""
        }

    val offerTypeLabel: String
        get() = when (offerType) {
            "negociable" -> "Négociable"
            "offert"     -> "Offert"
            "fix"        -> "Prix fixe"
            else         -> offerType ?: ""
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
        get() = createdAt.take(10)

    fun getSpec(group: String, key: String): String? {
        val groupMap = specs?.get(group) as? Map<*, *> ?: return null
        return groupMap[key]?.toString()
    }

    val year: String?    get() = getSpec("general", "annee_construction")
    val power: String?   get() = getSpec("motorisation", "puissance_totale")
    val length: String?  get() = getSpec("dimensions", "longueur")
}

data class ListingImage(
    val id: Int = 0,
    val url: String? = null,
    @SerializedName("thumbnail_url") val thumbnailUrl: String? = null,
    val path: String? = null,
    @SerializedName("thumbnail_path") val thumbnailPath: String? = null,
    val order: Int = 0,
) {
    val isPrimary: Boolean get() = order == 1

    private val mediaRouteBase: String?
        get() = id.takeIf { it > 0 }?.let { "$LISTING_MEDIA_ROUTE/$it" }

    val detailUrl: String?
        get() = url.ifNotBlank()?.ensureAbsoluteUrl()
            ?: mediaRouteBase

    val cardUrl: String?
        get() = thumbnailUrl.ifNotBlank()?.ensureAbsoluteUrl()
            ?: mediaRouteBase?.let { "$it/thumb" }
            ?: detailUrl
}

data class ListingUser(
    val id: Int,
    val name: String,
    @SerializedName(value = "avatar", alternate = ["profile_picture_url"]) val avatar: String?,
    @SerializedName(value = "is_verified", alternate = ["verified_badge"]) val isVerified: Boolean = false,
    val phone: String?
)

// ─── Auth ─────────────────────────────────────────────────────────────────────

data class LoginRequest(val email: String, val password: String)

data class GoogleLoginRequest(val id_token: String)

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
    val meta: PaginationMeta? = null,
    // Laravel pagination puts these at root level
    @SerializedName("current_page") val currentPage: Int = 1,
    @SerializedName("last_page") val lastPage: Int = 1,
    val total: Int = 0,
    @SerializedName("per_page") val perPage: Int = 20,
)

data class PaginationMeta(
    @SerializedName("current_page") val currentPage: Int,
    @SerializedName("last_page") val lastPage: Int,
    val total: Int,
    @SerializedName("per_page") val perPage: Int
)

// ─── Listing-specific response wrappers ──────────────────────────────────────

data class ListingDetailResponse(
    val listing: Listing,
    @SerializedName("is_favorited") val isFavorited: Boolean = false,
    @SerializedName("related_listings") val relatedListings: List<Listing> = emptyList(),
)

data class ListingActionResponse(
    val message: String? = null,
    val listing: Listing? = null,
)

data class CreateListingResponse(
    val listing: Listing? = null,
    val message: String? = null,
    @SerializedName("publish_price") val publishPrice: Int = 0,
    @SerializedName("is_first_listing") val isFirstListing: Boolean = false,
)

// ─── Mediation ───────────────────────────────────────────────────────────────

data class MediationTicket(
    val id: Int,
    val listing: Listing?,
    val buyer: User?,
    val seller: User?,
    val status: String,
    // "new" | "in_progress" | "resolved" | "closed" | "cancelled"
    val messages: List<MediationMessage> = emptyList(),
    @SerializedName("created_at") val createdAt: String = ""
) {
    val statusLabel: String
        get() = when (status) {
            "new"         -> "Nouveau"
            "in_progress" -> "En cours"
            "resolved"    -> "Résolu"
            "closed"      -> "Fermé"
            "cancelled"   -> "Annulé"
            else          -> status
        }
}

data class MediationMessage(
    @SerializedName("user_id") val userId: Int = 0,
    val message: String = "",
    @SerializedName("created_at") val createdAt: String = ""
) {
    /** Alias for backward compat with UI code that reads `.body`. */
    val body: String get() = message
}

data class MediationTicketsResponse(
    @SerializedName("buyerTickets") val buyerTickets: List<MediationTicket> = emptyList(),
    @SerializedName("sellerTickets") val sellerTickets: List<MediationTicket> = emptyList(),
)

data class MediationTicketResponse(
    val ticket: MediationTicket? = null,
)

data class MediationTicketActionResponse(
    val message: String? = null,
    val ticket: MediationTicket? = null,
)

// ─── Conversations ────────────────────────────────────────────────────────────

data class Conversation(
    val id: Int,
    val listing: Listing?,
    val buyer: User?,
    val seller: User?,
    @SerializedName("other_user") val otherUser: User?,
    @SerializedName("last_message") val lastMessage: ConversationMessage?,
    @SerializedName("unread_count") val unreadCount: Int = 0,
    @SerializedName("created_at") val createdAt: String = "",
    val role: String = "buyer"  // "buyer" | "seller"
)

data class ConversationMessage(
    val id: Int,
    val body: String,
    @SerializedName("sender_id") val senderId: Int,
    val sender: User? = null,
    @SerializedName("created_at") val createdAt: String = ""
)

data class StartConversationResponse(
    val conversation: Conversation? = null,
    val message: ConversationMessage? = null,
)

// ─── Payments ────────────────────────────────────────────────────────────────

data class Payment(
    val id: Int,
    val amount: Double,
    val type: String,
    // "publish_listing" | "featured_listing" | "vendor_subscription" | "mediation_fee"
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
            "publish_listing"    -> "Publication"
            "featured_listing"   -> "Mise en avant"
            "vendor_subscription"-> "Abonnement"
            "mediation_fee"      -> "Médiation"
            else                 -> type
        }

    val formattedAmount: String
        get() = "${formatWholeAmount(amount)} DA"
}

data class PaymentSubmitResponse(
    val message: String? = null,
    val payment: Payment? = null,
)

// ─── Profile ──────────────────────────────────────────────────────────────────

data class ProfileResponse(
    val user: User,
    val stats: Map<String, Int>? = null,
)

// ─── Subscription ─────────────────────────────────────────────────────────────

data class Subscription(
    val id: Int,
    val plan: String,
    val status: String,   // "active" | "expired" | "cancelled"
    @SerializedName("expires_at") val expiresAt: String?,
    @SerializedName("created_at") val createdAt: String = ""
)

// ─── Favorites ────────────────────────────────────────────────────────────────

data class ToggleFavoriteResponse(
    val favorited: Boolean = false,
    val message: String? = null,
    val count: Int = 0,
)

// ─── Listing filter params ────────────────────────────────────────────────────

data class ListingFilters(
    val search: String? = null,
    val category: String? = null,
    val type: String? = null,        // boat type slug (only when category == "boat")
    val wilaya: String? = null,
    val currency: String? = null,
    val minPrice: Double? = null,
    val maxPrice: Double? = null,
    val condition: String? = null,   // "jamais_utilise" | "comme_neuf" | "bon_etat" | "etat_moyen" | "a_reviser"
    val offerType: String? = null,   // "negociable" | "offert" | "fix"
    val sortBy: String? = null,      // "recent" | "price_asc" | "price_desc" | "views"
    val page: Int = 1
) {
    fun toQueryMap(): Map<String, String> = buildMap {
        search?.let    { put("q", it) }
        category?.let  { put("category", it) }
        type?.let      { put("type", it) }
        wilaya?.let    { put("wilaya", it) }
        currency?.let  { put("currency", it) }
        minPrice?.let  { put("price_min", it.toString()) }
        maxPrice?.let  { put("price_max", it.toString()) }
        condition?.let { put("etat", it) }
        offerType?.let { put("type_offre", it) }
        sortBy?.let    { put("sort", it) }
        put("page", page.toString())
    }
}
