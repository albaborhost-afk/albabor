package com.albabor.app.ui.navigation

sealed class Screen(val route: String) {
    // Auth
    object Login          : Screen("login")
    object Register       : Screen("register")
    object ForgotPassword : Screen("forgot_password")

    // Main (bottom nav)
    object Home           : Screen("home")
    object Explore        : Screen("explore") {
        const val categoryArg = "category"
        val pattern = "$route?$categoryArg={$categoryArg}"

        fun route(category: String? = null): String =
            if (category.isNullOrBlank()) route else "$route?$categoryArg=$category"
    }
    object CreateListing  : Screen("create_listing")
    object Favorites      : Screen("favorites")
    object Profile        : Screen("profile")

    // Listings
    object ListingDetail  : Screen("listing/{listingId}") {
        fun route(listingId: Int) = "listing/$listingId"
    }
    object EditListing    : Screen("edit_listing/{listingId}") {
        fun route(listingId: Int) = "edit_listing/$listingId"
    }
    object MyListings     : Screen("my_listings")

    /** Profil public d'un vendeur : toutes ses annonces actives. */
    object SellerProfile  : Screen("seller/{sellerId}") {
        fun route(sellerId: Int) = "seller/$sellerId"
    }

    // Payments
    object Payments       : Screen("payments")
    object PaymentDetail  : Screen("payment/{paymentId}") {
        fun route(paymentId: Int) = "payment/$paymentId"
    }

    // Mediation
    object Mediation      : Screen("mediation")
    object MediationDetail: Screen("mediation/{ticketId}") {
        fun route(ticketId: Int) = "mediation/$ticketId"
    }

    // Conversations
    object Conversations  : Screen("conversations")
    object ConversationDetail : Screen("conversation/{conversationId}") {
        fun route(conversationId: Int) = "conversation/$conversationId"
    }

    // Profile sub-screens
    object EditProfile    : Screen("edit_profile") {
        const val tabArg = "tab"
        val pattern = "$route?$tabArg={$tabArg}"

        fun route(tab: String? = null): String =
            if (tab.isNullOrBlank()) route else "$route?$tabArg=$tab"
    }
    object Subscriptions  : Screen("subscriptions")
    object Verification   : Screen("verification")
}
