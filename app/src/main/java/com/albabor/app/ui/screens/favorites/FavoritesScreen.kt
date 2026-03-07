package com.albabor.app.ui.screens.favorites

import androidx.compose.animation.AnimatedVisibility
import androidx.compose.animation.fadeIn
import androidx.compose.animation.fadeOut
import androidx.compose.foundation.background
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.grid.GridCells
import androidx.compose.foundation.lazy.grid.LazyVerticalGrid
import androidx.compose.foundation.lazy.grid.items
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Explore
import androidx.compose.material.icons.filled.Favorite
import androidx.compose.material.icons.filled.FavoriteBorder
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.compose.ui.zIndex
import androidx.lifecycle.compose.collectAsStateWithLifecycle
import androidx.lifecycle.viewmodel.compose.viewModel
import androidx.navigation.NavController
import com.albabor.app.ui.components.ListingCard
import com.albabor.app.ui.components.LoadingGrid
import com.albabor.app.ui.navigation.Screen
import com.albabor.app.ui.theme.*
import com.albabor.app.viewmodel.FavoritesViewModel
import kotlinx.coroutines.launch

// ─── Screen ───────────────────────────────────────────────────────────────────

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun FavoritesScreen(
    navController: NavController,
    isAuthenticated: Boolean = true
) {
    val vm: FavoritesViewModel = viewModel()
    val listings     by vm.listings.collectAsStateWithLifecycle()
    val isLoading    by vm.isLoading.collectAsStateWithLifecycle()
    val isRefreshing by vm.isRefreshing.collectAsStateWithLifecycle()
    val error        by vm.error.collectAsStateWithLifecycle()

    val snackbarHostState = remember { SnackbarHostState() }
    val scope = rememberCoroutineScope()

    LaunchedEffect(error) {
        error?.let {
            scope.launch { snackbarHostState.showSnackbar(it) }
            vm.clearError()
        }
    }

    Scaffold(
        topBar = {
            TopAppBar(
                title = {
                    Row(
                        verticalAlignment = Alignment.CenterVertically,
                        horizontalArrangement = Arrangement.spacedBy(8.dp)
                    ) {
                        Icon(
                            imageVector = Icons.Default.Favorite,
                            contentDescription = null,
                            tint = Error500,
                            modifier = Modifier.size(22.dp)
                        )
                        Text(
                            text = "Mes favoris",
                            fontWeight = FontWeight.Bold,
                            fontSize = 20.sp,
                            color = Gray900
                        )
                        if (listings.isNotEmpty()) {
                            Box(
                                modifier = Modifier
                                    .clip(CircleShape)
                                    .background(Error500)
                                    .padding(horizontal = 7.dp, vertical = 2.dp)
                            ) {
                                Text(
                                    text = listings.size.toString(),
                                    color = White,
                                    fontSize = 11.sp,
                                    fontWeight = FontWeight.Bold
                                )
                            }
                        }
                    }
                },
                colors = TopAppBarDefaults.topAppBarColors(
                    containerColor = White,
                    scrolledContainerColor = White
                )
            )
        },
        snackbarHost = {
            SnackbarHost(hostState = snackbarHostState) { data ->
                Snackbar(
                    snackbarData = data,
                    containerColor = Gray900,
                    contentColor = White,
                    shape = RoundedCornerShape(12.dp)
                )
            }
        },
        containerColor = Gray50
    ) { padding ->

        Box(
            modifier = Modifier
                .fillMaxSize()
                .padding(padding)
        ) {
            when {
                // ── Not logged in ─────────────────────────────────────────────
                !isAuthenticated -> {
                    LoginPromptState(
                        onLoginClick = { navController.navigate(Screen.Login.route) }
                    )
                }

                // ── Loading ───────────────────────────────────────────────────
                isLoading -> {
                    LoadingGrid(
                        count = 6,
                        modifier = Modifier
                            .fillMaxSize()
                            .padding(horizontal = 16.dp, vertical = 14.dp)
                    )
                }

                // ── Empty ─────────────────────────────────────────────────────
                listings.isEmpty() -> {
                    EmptyFavoritesState(
                        onExploreClick = { navController.navigate(Screen.Explore.route) }
                    )
                }

                // ── Grid ──────────────────────────────────────────────────────
                else -> {
                    Box(modifier = Modifier.fillMaxSize()) {

                        LazyVerticalGrid(
                            columns = GridCells.Fixed(2),
                            modifier = Modifier.fillMaxSize(),
                            contentPadding = PaddingValues(horizontal = 12.dp, vertical = 14.dp),
                            verticalArrangement = Arrangement.spacedBy(12.dp),
                            horizontalArrangement = Arrangement.spacedBy(10.dp)
                        ) {
                            items(
                                items = listings,
                                key = { it.id }
                            ) { listing ->
                                ListingCard(
                                    listing    = listing,
                                    isFavorited = true,
                                    onClick    = {
                                        navController.navigate(Screen.ListingDetail.route(listing.id))
                                    },
                                    onFavorite = { vm.removeFavorite(listing.id) },
                                    modifier   = Modifier.fillMaxWidth()
                                )
                            }
                        }

                        // Pull-to-refresh spinner (top overlay)
                        AnimatedVisibility(
                            visible = isRefreshing,
                            enter = fadeIn(),
                            exit = fadeOut(),
                            modifier = Modifier
                                .align(Alignment.TopCenter)
                                .padding(top = 12.dp)
                                .zIndex(10f)
                        ) {
                            Surface(
                                shape = CircleShape,
                                color = White,
                                shadowElevation = 4.dp
                            ) {
                                CircularProgressIndicator(
                                    color = OceanBlue700,
                                    modifier = Modifier
                                        .padding(10.dp)
                                        .size(22.dp),
                                    strokeWidth = 2.5.dp
                                )
                            }
                        }
                    }
                }
            }
        }
    }
}

// ─── Empty favorites state ────────────────────────────────────────────────────

@Composable
private fun EmptyFavoritesState(onExploreClick: () -> Unit) {
    Column(
        modifier = Modifier
            .fillMaxSize()
            .padding(40.dp),
        horizontalAlignment = Alignment.CenterHorizontally,
        verticalArrangement = Arrangement.Center
    ) {
        Box(
            modifier = Modifier
                .size(110.dp)
                .clip(CircleShape)
                .background(
                    Brush.radialGradient(
                        listOf(Error100.copy(alpha = 0.8f), Error100.copy(alpha = 0.2f))
                    )
                ),
            contentAlignment = Alignment.Center
        ) {
            Icon(
                imageVector = Icons.Default.FavoriteBorder,
                contentDescription = null,
                tint = Error500.copy(alpha = 0.7f),
                modifier = Modifier.size(52.dp)
            )
        }

        Spacer(modifier = Modifier.height(24.dp))

        Text(
            text = "Aucun favori pour l'instant",
            style = MaterialTheme.typography.titleLarge.copy(
                fontWeight = FontWeight.Bold,
                color = Gray900
            ),
            textAlign = TextAlign.Center
        )

        Spacer(modifier = Modifier.height(10.dp))

        Text(
            text = "Explorez les annonces et ajoutez vos coups de coeur a vos favoris pour les retrouver facilement.",
            style = MaterialTheme.typography.bodyMedium.copy(color = Gray400),
            textAlign = TextAlign.Center,
            lineHeight = 22.sp
        )

        Spacer(modifier = Modifier.height(32.dp))

        Button(
            onClick = onExploreClick,
            colors = ButtonDefaults.buttonColors(containerColor = OceanBlue700),
            shape = RoundedCornerShape(12.dp),
            contentPadding = PaddingValues(horizontal = 28.dp, vertical = 14.dp),
            elevation = ButtonDefaults.buttonElevation(defaultElevation = 4.dp)
        ) {
            Icon(
                imageVector = Icons.Default.Explore,
                contentDescription = null,
                modifier = Modifier.size(18.dp)
            )
            Spacer(modifier = Modifier.width(8.dp))
            Text(
                text = "Explorer les annonces",
                fontWeight = FontWeight.SemiBold,
                fontSize = 15.sp
            )
        }
    }
}

// ─── Login prompt state ───────────────────────────────────────────────────────

@Composable
private fun LoginPromptState(onLoginClick: () -> Unit) {
    Column(
        modifier = Modifier
            .fillMaxSize()
            .padding(40.dp),
        horizontalAlignment = Alignment.CenterHorizontally,
        verticalArrangement = Arrangement.Center
    ) {
        Box(
            modifier = Modifier
                .size(110.dp)
                .clip(CircleShape)
                .background(OceanBlue50),
            contentAlignment = Alignment.Center
        ) {
            Text(text = "\u2693", fontSize = 48.sp)
        }

        Spacer(modifier = Modifier.height(24.dp))

        Text(
            text = "Connectez-vous pour acceder a vos favoris",
            style = MaterialTheme.typography.titleMedium.copy(
                fontWeight = FontWeight.Bold,
                color = Gray900
            ),
            textAlign = TextAlign.Center
        )

        Spacer(modifier = Modifier.height(10.dp))

        Text(
            text = "Creez un compte ou connectez-vous pour sauvegarder vos annonces preferees.",
            style = MaterialTheme.typography.bodyMedium.copy(color = Gray400),
            textAlign = TextAlign.Center
        )

        Spacer(modifier = Modifier.height(32.dp))

        Button(
            onClick = onLoginClick,
            colors = ButtonDefaults.buttonColors(containerColor = OceanBlue700),
            shape = RoundedCornerShape(12.dp),
            contentPadding = PaddingValues(horizontal = 32.dp, vertical = 14.dp)
        ) {
            Text(
                text = "Se connecter",
                fontWeight = FontWeight.SemiBold,
                fontSize = 15.sp
            )
        }
    }
}
