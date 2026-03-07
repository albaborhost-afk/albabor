package com.albabor.app.ui.navigation

sealed class Screen(val route: String) {
    // Auth
    object Login          : Screen("login")
    object Register       : Screen("register")
    object ForgotPassword : Screen("forgot_password")

    // Main (bottom nav)
    object Home           : Screen("home")
    object Explore        : Screen("explore")
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
    object EditProfile    : Screen("edit_profile")
    object Subscriptions  : Screen("subscriptions")
    object Verification   : Screen("verification")
}
