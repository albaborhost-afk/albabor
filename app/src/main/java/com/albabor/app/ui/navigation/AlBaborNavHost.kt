package com.albabor.app.ui.navigation

import androidx.compose.foundation.background
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.Chat
import androidx.compose.material.icons.filled.Add
import androidx.compose.material.icons.filled.Favorite
import androidx.compose.material.icons.filled.Home
import androidx.compose.material.icons.filled.Person
import androidx.compose.material.icons.filled.Search
import androidx.compose.material.icons.outlined.ChatBubbleOutline
import androidx.compose.material.icons.outlined.FavoriteBorder
import androidx.compose.material.icons.outlined.Home
import androidx.compose.material.icons.outlined.Person
import androidx.compose.material.icons.outlined.Search
import androidx.compose.material3.Icon
import androidx.compose.material3.Scaffold
import androidx.compose.material3.Text
import androidx.compose.animation.animateColorAsState
import androidx.compose.animation.core.animateFloatAsState
import androidx.compose.animation.core.spring
import androidx.compose.animation.core.tween
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.setValue
import com.albabor.app.data.network.SessionBus
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.draw.scale
import androidx.compose.ui.draw.shadow
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.vector.ImageVector
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
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
import com.albabor.app.ui.screens.seller.SellerProfileScreen
import com.albabor.app.ui.screens.mediation.MediationDetailScreen
import com.albabor.app.ui.screens.mediation.MediationScreen
import com.albabor.app.ui.screens.mylistings.MyListingsScreen
import com.albabor.app.ui.screens.payments.PaymentsScreen
import com.albabor.app.ui.screens.profile.EditProfileScreen
import com.albabor.app.ui.screens.profile.ProfileScreen
import com.albabor.app.ui.screens.payments.SubscriptionsScreen
import com.albabor.app.ui.screens.verification.VerificationScreen
import com.albabor.app.ui.theme.Gray400
import com.albabor.app.ui.theme.OceanBlue900
import com.albabor.app.ui.theme.White

data class BottomNavItem(
    val screen: Screen,
    val label: String,
    val selectedIcon: ImageVector,
    val unselectedIcon: ImageVector,
    val badgeCount: Int = 0,
)

@Composable
fun AlBaborNavHost() {
    val navController = rememberNavController()
    val context = LocalContext.current

    // Check if user is logged in asynchronously at startup
    var isLoggedIn by remember { mutableStateOf<Boolean?>(null) }
    LaunchedEffect(Unit) {
        isLoggedIn = TokenStore.get(context) != null
    }

    // Wait until token check completes before rendering navigation
    val loggedIn = isLoggedIn ?: return

    val startDestination = if (loggedIn) Screen.Home.route else Screen.Login.route

    // Listen for 401 session expiry — kick the user back to the Login screen
    LaunchedEffect(Unit) {
        SessionBus.unauthorized.collect {
            navController.navigate(Screen.Login.route) {
                popUpTo(navController.graph.id) { inclusive = true }
            }
        }
    }

    val bottomNavItems = listOf(
        BottomNavItem(Screen.Home, "Accueil", Icons.Filled.Home, Icons.Outlined.Home),
        BottomNavItem(Screen.Explore, "Explorer", Icons.Filled.Search, Icons.Outlined.Search),
        BottomNavItem(Screen.Conversations, "Messages", Icons.AutoMirrored.Filled.Chat, Icons.Outlined.ChatBubbleOutline),
        BottomNavItem(Screen.CreateListing, "Publier", Icons.Filled.Add, Icons.Filled.Add),
        BottomNavItem(Screen.Favorites, "Favoris", Icons.Filled.Favorite, Icons.Outlined.FavoriteBorder),
        BottomNavItem(Screen.Profile, "Profil", Icons.Filled.Person, Icons.Outlined.Person),
    )

    val navBackStackEntry by navController.currentBackStackEntryAsState()
    val currentRoute = navBackStackEntry?.destination?.route
    val showBottomBar = currentRoute == Screen.Home.route ||
        currentRoute == Screen.Explore.route ||
        currentRoute == Screen.Explore.pattern ||
        currentRoute == Screen.Conversations.route ||
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
                            if (loggedIn) {
                                navController.navigate(Screen.CreateListing.route)
                            } else {
                                navController.navigate(Screen.Login.route)
                            }
                        } else if (item.screen == Screen.Home) {
                            // Home is always the root — pop back to it directly
                            if (!navController.popBackStack(Screen.Home.route, inclusive = false)) {
                                navController.navigate(Screen.Home.route)
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
                    onConversationRequested = { conversationId ->
                        navController.navigate(Screen.ConversationDetail.route(conversationId))
                    },
                    onMediationRequested = { ticketId ->
                        navController.navigate(Screen.MediationDetail.route(ticketId))
                    },
                    onSellerRequested = { sellerId ->
                        navController.navigate(Screen.SellerProfile.route(sellerId))
                    }
                )
            }

            // ── Profil public d'un vendeur ────────────────────────────────────────
            composable(
                route = Screen.SellerProfile.route,
                arguments = listOf(navArgument("sellerId") { type = NavType.IntType }),
            ) { backStackEntry ->
                val sellerId = backStackEntry.arguments?.getInt("sellerId") ?: return@composable
                SellerProfileScreen(
                    sellerId = sellerId,
                    navController = navController,
                    onBack = { navController.popBackStack() }
                )
            }

            // ── Edit listing ──────────────────────────────────────────────────────
            // TODO v1.1: wire up once CreateListingScreen supports edit-mode pre-fill.
            // Route kept in Screen.kt for link continuity but intentionally NOT registered here in v1.0.

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
                ConversationsScreen(
                    navController = navController,
                    showBackButton = false
                )
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
            .shadow(
                elevation = 24.dp,
                shape = RoundedCornerShape(topStart = 24.dp, topEnd = 24.dp),
                ambientColor = OceanBlue900.copy(alpha = 0.10f),
                spotColor = OceanBlue900.copy(alpha = 0.16f)
            )
            .clip(RoundedCornerShape(topStart = 24.dp, topEnd = 24.dp))
            .background(White)
    ) {
        Row(
            modifier = Modifier
                .fillMaxWidth()
                .padding(horizontal = 4.dp, vertical = 8.dp)
                .navigationBarsPadding(),
            horizontalArrangement = Arrangement.SpaceAround,
            verticalAlignment = Alignment.CenterVertically
        ) {
            items.forEach { item ->
                val isSelected = when (item.screen) {
                    Screen.Explore -> currentRoute == Screen.Explore.route || currentRoute == Screen.Explore.pattern
                    else -> currentRoute == item.screen.route
                }
                NavTabItem(
                    item = item,
                    selected = isSelected,
                    onClick = { onNavigate(item) },
                    modifier = Modifier.weight(1f)
                )
            }
        }
    }
}

@Composable
private fun NavTabItem(
    item: BottomNavItem,
    selected: Boolean,
    onClick: () -> Unit,
    modifier: Modifier = Modifier
) {
    val pillAlpha by animateFloatAsState(
        targetValue = if (selected) 1f else 0f,
        animationSpec = tween(220),
        label = "pillAlpha"
    )
    val iconColor by animateColorAsState(
        targetValue = if (selected) OceanBlue900 else Gray400,
        animationSpec = tween(220),
        label = "iconColor"
    )
    val labelColor by animateColorAsState(
        targetValue = if (selected) OceanBlue900 else Gray400,
        animationSpec = tween(220),
        label = "labelColor"
    )
    val iconScale by animateFloatAsState(
        targetValue = if (selected) 1.08f else 1f,
        animationSpec = spring(dampingRatio = 0.48f, stiffness = 360f),
        label = "iconScale"
    )

    Column(
        modifier = modifier
            .clip(RoundedCornerShape(14.dp))
            .clickable(onClick = onClick)
            .padding(vertical = 8.dp, horizontal = 4.dp),
        horizontalAlignment = Alignment.CenterHorizontally,
        verticalArrangement = Arrangement.spacedBy(5.dp)
    ) {
        Box(
            modifier = Modifier
                .clip(RoundedCornerShape(12.dp))
                .background(OceanBlue900.copy(alpha = 0.09f * pillAlpha))
                .padding(horizontal = 13.dp, vertical = 5.dp),
            contentAlignment = Alignment.Center
        ) {
            Icon(
                imageVector = if (selected) item.selectedIcon else item.unselectedIcon,
                contentDescription = item.label,
                tint = iconColor,
                modifier = Modifier
                    .size(22.dp)
                    .scale(iconScale)
            )
            // Unread badge
            if (item.badgeCount > 0) {
                Box(
                    modifier = Modifier
                        .align(Alignment.TopEnd)
                        .offset(x = 6.dp, y = (-4).dp)
                        .defaultMinSize(minWidth = 15.dp, minHeight = 15.dp)
                        .clip(CircleShape)
                        .background(Color(0xFFE74C3C)),
                    contentAlignment = Alignment.Center
                ) {
                    Text(
                        text = if (item.badgeCount > 99) "99+" else item.badgeCount.toString(),
                        color = White,
                        fontSize = 8.sp,
                        fontWeight = FontWeight.Bold,
                        modifier = Modifier.padding(horizontal = 2.dp)
                    )
                }
            }
        }
        Text(
            text = item.label,
            color = labelColor,
            fontSize = 9.sp,
            fontWeight = if (selected) FontWeight.SemiBold else FontWeight.Normal,
            maxLines = 1,
            letterSpacing = 0.2.sp
        )
    }
}
