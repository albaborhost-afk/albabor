package com.albabor.app.ui.navigation

import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Add
import androidx.compose.material.icons.filled.Favorite
import androidx.compose.material.icons.filled.Home
import androidx.compose.material.icons.filled.Person
import androidx.compose.material.icons.filled.Search
import androidx.compose.material.icons.outlined.FavoriteBorder
import androidx.compose.material.icons.outlined.Home
import androidx.compose.material.icons.outlined.Person
import androidx.compose.material.icons.outlined.Search
import androidx.compose.material3.Icon
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.NavigationBar
import androidx.compose.material3.NavigationBarItem
import androidx.compose.material3.NavigationBarItemDefaults
import androidx.compose.material3.Scaffold
import androidx.compose.material3.Surface
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.getValue
import androidx.compose.runtime.remember
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.vector.ImageVector
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.unit.dp
import androidx.navigation.NavController
import androidx.navigation.NavType
import androidx.navigation.compose.NavHost
import androidx.navigation.compose.composable
import androidx.navigation.compose.currentBackStackEntryAsState
import androidx.navigation.compose.rememberNavController
import androidx.navigation.navArgument
import com.albabor.app.data.network.TokenStore
import com.albabor.app.ui.screens.auth.ForgotPasswordScreen
import com.albabor.app.ui.screens.auth.LoginScreen
import com.albabor.app.ui.screens.auth.RegisterScreen
import com.albabor.app.ui.screens.conversations.ConversationDetailScreen
import com.albabor.app.ui.screens.conversations.ConversationsScreen
import com.albabor.app.ui.screens.explore.ExploreScreen
import com.albabor.app.ui.screens.favorites.FavoritesScreen
import com.albabor.app.ui.screens.home.HomeScreen
import com.albabor.app.ui.screens.listing.CreateListingScreen
import com.albabor.app.ui.screens.listing.ListingDetailScreen
import com.albabor.app.ui.screens.mediation.MediationDetailScreen
import com.albabor.app.ui.screens.mediation.MediationScreen
import com.albabor.app.ui.screens.mylistings.MyListingsScreen
import com.albabor.app.ui.screens.payments.PaymentsScreen
import com.albabor.app.ui.screens.profile.EditProfileScreen
import com.albabor.app.ui.screens.profile.ProfileScreen
import com.albabor.app.ui.screens.payments.SubscriptionsScreen
import com.albabor.app.ui.screens.verification.VerificationScreen
import com.albabor.app.ui.theme.Gray400
import com.albabor.app.ui.theme.OceanBlue50
import com.albabor.app.ui.theme.OceanBlue700
import kotlinx.coroutines.runBlocking

data class BottomNavItem(
    val screen: Screen,
    val label: String,
    val selectedIcon: ImageVector,
    val unselectedIcon: ImageVector,
)

@Composable
fun AlBaborNavHost() {
    val navController = rememberNavController()
    val context = LocalContext.current

    // Check if user is logged in synchronously at startup only
    val isLoggedIn = remember {
        runBlocking {
            TokenStore.get(context) != null
        }
    }

    val startDestination = if (isLoggedIn) Screen.Home.route else Screen.Login.route

    val bottomNavItems = listOf(
        BottomNavItem(Screen.Home, "Accueil", Icons.Filled.Home, Icons.Outlined.Home),
        BottomNavItem(Screen.Explore, "Annonces", Icons.Filled.Search, Icons.Outlined.Search),
        BottomNavItem(Screen.CreateListing, "Publier", Icons.Filled.Add, Icons.Filled.Add),
        BottomNavItem(Screen.Favorites, "Favoris", Icons.Filled.Favorite, Icons.Outlined.FavoriteBorder),
        BottomNavItem(Screen.Profile, "Profil", Icons.Filled.Person, Icons.Outlined.Person),
    )

    // Screens that show the bottom navigation bar
    val bottomNavRoutes = setOf(
        Screen.Home.route,
        Screen.Explore.route,
        Screen.CreateListing.route,
        Screen.Favorites.route,
        Screen.Profile.route,
    )

    val navBackStackEntry by navController.currentBackStackEntryAsState()
    val currentRoute = navBackStackEntry?.destination?.route
    val showBottomBar = currentRoute in bottomNavRoutes

    Scaffold(
        bottomBar = {
            if (showBottomBar) {
                NavigationBar(
                    containerColor = MaterialTheme.colorScheme.surface,
                ) {
                    bottomNavItems.forEach { item ->
                        val selected = currentRoute == item.screen.route
                        NavigationBarItem(
                            selected = selected,
                            onClick = {
                                if (item.screen == Screen.CreateListing) {
                                    if (isLoggedIn) {
                                        navController.navigate(Screen.CreateListing.route)
                                    } else {
                                        navController.navigate(Screen.Login.route)
                                    }
                                } else {
                                    navController.navigate(item.screen.route) {
                                        popUpTo(Screen.Home.route) { saveState = true }
                                        launchSingleTop = true
                                        restoreState = true
                                    }
                                }
                            },
                            icon = {
                                if (item.screen == Screen.CreateListing) {
                                    // Special FAB-like centre button
                                    Surface(
                                        shape = CircleShape,
                                        color = MaterialTheme.colorScheme.primary,
                                        modifier = Modifier.size(48.dp),
                                    ) {
                                        Icon(
                                            imageVector = item.selectedIcon,
                                            contentDescription = item.label,
                                            tint = MaterialTheme.colorScheme.onPrimary,
                                            modifier = Modifier.padding(12.dp),
                                        )
                                    }
                                } else {
                                    Icon(
                                        imageVector = if (selected) item.selectedIcon else item.unselectedIcon,
                                        contentDescription = item.label,
                                    )
                                }
                            },
                            label = {
                                // No label under the centre Publier button
                                if (item.screen != Screen.CreateListing) {
                                    Text(
                                        text = item.label,
                                        style = MaterialTheme.typography.labelSmall,
                                    )
                                }
                            },
                            colors = NavigationBarItemDefaults.colors(
                                selectedIconColor = OceanBlue700,
                                selectedTextColor = OceanBlue700,
                                indicatorColor = OceanBlue50,
                                unselectedIconColor = Gray400,
                                unselectedTextColor = Gray400,
                            ),
                        )
                    }
                }
            }
        },
    ) { paddingValues ->
        NavHost(
            navController = navController,
            startDestination = startDestination,
            modifier = Modifier.padding(paddingValues),
        ) {
            // ── Auth ──────────────────────────────────────────────────────────────
            composable(Screen.Login.route) {
                LoginScreen(navController = navController)
            }
            composable(Screen.Register.route) {
                RegisterScreen(navController = navController)
            }
            composable(Screen.ForgotPassword.route) {
                ForgotPasswordScreen(navController = navController)
            }

            // ── Main tabs ─────────────────────────────────────────────────────────
            composable(Screen.Home.route) {
                HomeScreen(navController = navController)
            }
            composable(Screen.Explore.route) {
                ExploreScreen(navController = navController)
            }
            composable(Screen.CreateListing.route) {
                CreateListingScreen(
                    onBack = { navController.popBackStack() },
                    onSuccess = { listingId ->
                        navController.navigate(Screen.ListingDetail.route(listingId)) {
                            popUpTo(Screen.CreateListing.route) { inclusive = true }
                        }
                    }
                )
            }
            composable(Screen.Favorites.route) {
                FavoritesScreen(navController = navController)
            }
            composable(Screen.Profile.route) {
                ProfileScreen(navController = navController)
            }

            // ── Listing detail ────────────────────────────────────────────────────
            composable(
                route = Screen.ListingDetail.route,
                arguments = listOf(navArgument("listingId") { type = NavType.IntType }),
            ) { backStackEntry ->
                val listingId = backStackEntry.arguments?.getInt("listingId") ?: return@composable
                ListingDetailScreen(
                    listingId = listingId,
                    onBack = { navController.popBackStack() },
                    onMediationRequested = { id ->
                        navController.navigate(Screen.MediationDetail.route(id))
                    }
                )
            }

            // ── Edit listing ──────────────────────────────────────────────────────
            composable(
                route = Screen.EditListing.route,
                arguments = listOf(navArgument("listingId") { type = NavType.IntType }),
            ) { backStackEntry ->
                val listingId = backStackEntry.arguments?.getInt("listingId") ?: return@composable
                CreateListingScreen(
                    onBack = { navController.popBackStack() },
                    onSuccess = { navController.popBackStack() }
                )
            }

            // ── My Listings ───────────────────────────────────────────────────────
            composable(Screen.MyListings.route) {
                MyListingsScreen(navController = navController)
            }

            // ── Profile sub-screens ───────────────────────────────────────────────
            composable(Screen.EditProfile.route) {
                EditProfileScreen(navController = navController)
            }
            composable(Screen.Subscriptions.route) {
                SubscriptionsScreen(navController = navController)
            }
            composable(Screen.Verification.route) {
                VerificationScreen(navController = navController)
            }

            // ── Payments ──────────────────────────────────────────────────────────
            composable(Screen.Payments.route) {
                PaymentsScreen(navController = navController)
            }

            // ── Mediation ─────────────────────────────────────────────────────────
            composable(Screen.Mediation.route) {
                MediationScreen(navController = navController)
            }
            composable(
                route = Screen.MediationDetail.route,
                arguments = listOf(navArgument("ticketId") { type = NavType.IntType }),
            ) { backStackEntry ->
                val ticketId = backStackEntry.arguments?.getInt("ticketId") ?: return@composable
                MediationDetailScreen(navController = navController, ticketId = ticketId)
            }

            // ── Conversations ─────────────────────────────────────────────────────
            composable(Screen.Conversations.route) {
                ConversationsScreen(navController = navController)
            }
            composable(
                route = Screen.ConversationDetail.route,
                arguments = listOf(navArgument("conversationId") { type = NavType.IntType }),
            ) { backStackEntry ->
                val conversationId = backStackEntry.arguments?.getInt("conversationId") ?: return@composable
                ConversationDetailScreen(conversationId = conversationId, navController = navController)
            }
        }
    }
}
