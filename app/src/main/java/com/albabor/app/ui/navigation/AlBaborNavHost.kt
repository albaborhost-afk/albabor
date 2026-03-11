package com.albabor.app.ui.navigation

import androidx.compose.foundation.background
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
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
import androidx.compose.material3.Scaffold
import androidx.compose.material3.Surface
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.getValue
import androidx.compose.runtime.remember
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.vector.ImageVector
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
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
import com.albabor.app.ui.theme.AppBackground
import com.albabor.app.ui.theme.GlassSurfaceStrong
import com.albabor.app.ui.theme.Gray400
import com.albabor.app.ui.theme.OceanBlue900
import com.albabor.app.ui.theme.Teal500
import com.albabor.app.ui.theme.White
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
        BottomNavItem(Screen.Explore, "Explorer", Icons.Filled.Search, Icons.Outlined.Search),
        BottomNavItem(Screen.CreateListing, "Publier", Icons.Filled.Add, Icons.Filled.Add),
        BottomNavItem(Screen.Favorites, "Favoris", Icons.Filled.Favorite, Icons.Outlined.FavoriteBorder),
        BottomNavItem(Screen.Profile, "Profil", Icons.Filled.Person, Icons.Outlined.Person),
    )

    val navBackStackEntry by navController.currentBackStackEntryAsState()
    val currentRoute = navBackStackEntry?.destination?.route
    val showBottomBar = currentRoute == Screen.Home.route ||
        currentRoute == Screen.Explore.route ||
        currentRoute == Screen.Explore.pattern ||
        currentRoute == Screen.Favorites.route ||
        currentRoute == Screen.Profile.route

    Scaffold(
        bottomBar = {
            if (showBottomBar) {
                FloatingBottomBar(
                    items = bottomNavItems,
                    currentRoute = currentRoute,
                    onNavigate = { item ->
                        if (item.screen == Screen.CreateListing) {
                            if (isLoggedIn) {
                                navController.navigate(Screen.CreateListing.route)
                            } else {
                                navController.navigate(Screen.Login.route)
                            }
                        } else {
                            val destination = when (item.screen) {
                                Screen.Explore -> Screen.Explore.route()
                                else -> item.screen.route
                            }
                            navController.navigate(destination) {
                                popUpTo(Screen.Home.route) { saveState = true }
                                launchSingleTop = true
                                restoreState = true
                            }
                        }
                    }
                )
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
            composable(
                route = Screen.Explore.pattern,
                arguments = listOf(
                    navArgument(Screen.Explore.categoryArg) {
                        type = NavType.StringType
                        nullable = true
                        defaultValue = null
                    }
                ),
            ) { backStackEntry ->
                ExploreScreen(
                    navController = navController,
                    initialCategory = backStackEntry.arguments?.getString(Screen.Explore.categoryArg)
                )
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
            composable(
                route = Screen.EditProfile.pattern,
                arguments = listOf(
                    navArgument(Screen.EditProfile.tabArg) {
                        type = NavType.StringType
                        nullable = true
                        defaultValue = null
                    }
                ),
            ) { backStackEntry ->
                EditProfileScreen(
                    navController = navController,
                    initialTab = backStackEntry.arguments?.getString(Screen.EditProfile.tabArg)
                )
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

@Composable
private fun FloatingBottomBar(
    items: List<BottomNavItem>,
    currentRoute: String?,
    onNavigate: (BottomNavItem) -> Unit
) {
    Box(
        modifier = Modifier
            .fillMaxWidth()
            .background(AppBackground)
            .padding(start = 16.dp, end = 16.dp, top = 8.dp, bottom = 30.dp)
    ) {
        Surface(
            modifier = Modifier
                .fillMaxWidth()
                .padding(top = 18.dp),
            shape = RoundedCornerShape(28.dp),
            color = GlassSurfaceStrong,
            shadowElevation = 16.dp,
            tonalElevation = 0.dp
        ) {
            Row(
                modifier = Modifier
                    .fillMaxWidth()
                    .padding(start = 8.dp, end = 8.dp, top = 10.dp, bottom = 6.dp),
                horizontalArrangement = Arrangement.SpaceBetween
            ) {
                items.forEach { item ->
                    if (item.screen == Screen.CreateListing) {
                        Box(modifier = Modifier.width(72.dp))
                    } else {
                        FloatingTabItem(
                            item = item,
                            selected = when (item.screen) {
                                Screen.Explore -> currentRoute == Screen.Explore.route || currentRoute == Screen.Explore.pattern
                                else -> currentRoute == item.screen.route
                            },
                            onClick = { onNavigate(item) },
                            modifier = Modifier.weight(1f, fill = true)
                        )
                    }
                }
            }
        }

        items.firstOrNull { it.screen == Screen.CreateListing }?.let { publishItem ->
            PublishTabButton(
                modifier = Modifier.align(Alignment.TopCenter),
                onClick = { onNavigate(publishItem) }
            )
        }
    }
}

@Composable
private fun FloatingTabItem(
    item: BottomNavItem,
    selected: Boolean,
    onClick: () -> Unit,
    modifier: Modifier = Modifier
) {
    Column(
        modifier = modifier
            .clip(RoundedCornerShape(18.dp))
            .clickable(onClick = onClick)
            .padding(vertical = 6.dp),
        horizontalAlignment = Alignment.CenterHorizontally,
        verticalArrangement = Arrangement.spacedBy(4.dp)
    ) {
        Box(
            modifier = Modifier
                .clip(RoundedCornerShape(999.dp))
                .background(
                    if (selected) {
                        Brush.verticalGradient(
                            colors = listOf(
                                Teal500.copy(alpha = 0.16f),
                                OceanBlue900.copy(alpha = 0.08f)
                            )
                        )
                    } else {
                        Brush.verticalGradient(listOf(Color.Transparent, Color.Transparent))
                    }
                )
                .padding(horizontal = 12.dp, vertical = 4.dp)
        ) {
            Icon(
                imageVector = if (selected) item.selectedIcon else item.unselectedIcon,
                contentDescription = item.label,
                tint = if (selected) OceanBlue900 else Gray400,
                modifier = Modifier.size(20.dp)
            )
        }

        Text(
            text = item.label,
            color = if (selected) OceanBlue900 else Gray400,
            style = MaterialTheme.typography.labelSmall,
            fontWeight = if (selected) FontWeight.SemiBold else FontWeight.Medium
        )
    }
}

@Composable
private fun PublishTabButton(
    modifier: Modifier = Modifier,
    onClick: () -> Unit
) {
    Box(
        modifier = modifier
            .offset(y = (-2).dp)
            .clickable(onClick = onClick),
        contentAlignment = Alignment.Center
    ) {
        Box(
            modifier = Modifier
                .size(68.dp)
                .background(
                    Brush.radialGradient(
                        colors = listOf(Teal500.copy(alpha = 0.20f), Color.Transparent)
                    ),
                    CircleShape
                )
        )

        Surface(
            modifier = Modifier.size(58.dp),
            shape = CircleShape,
            color = White,
            shadowElevation = 10.dp,
            tonalElevation = 0.dp
        ) {
            Box(contentAlignment = Alignment.Center) {
                Box(
                    modifier = Modifier
                        .size(52.dp)
                        .clip(CircleShape)
                        .background(
                            Brush.linearGradient(
                                colors = listOf(OceanBlue900, Teal500)
                            )
                        ),
                    contentAlignment = Alignment.Center
                ) {
                    Icon(
                        imageVector = Icons.Filled.Add,
                        contentDescription = "Publier",
                        tint = White,
                        modifier = Modifier.size(22.dp)
                    )
                }
            }
        }
    }
}
