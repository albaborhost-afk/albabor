package com.albabor.app.ui.screens.home

import androidx.compose.foundation.background
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.LazyRow
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.pager.HorizontalPager
import androidx.compose.foundation.pager.rememberPagerState
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Anchor
import androidx.compose.material.icons.filled.NotificationsNone
import androidx.compose.material.icons.filled.Search
import androidx.compose.material3.*
import androidx.compose.material3.pulltorefresh.PullToRefreshBox
import androidx.compose.material3.pulltorefresh.rememberPullToRefreshState
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.lifecycle.compose.collectAsStateWithLifecycle
import androidx.lifecycle.viewmodel.compose.viewModel
import androidx.navigation.NavController
import com.albabor.app.data.model.Listing
import com.albabor.app.data.model.ListingFilters
import com.albabor.app.ui.components.*
import com.albabor.app.ui.navigation.Screen
import com.albabor.app.ui.theme.*
import com.albabor.app.viewmodel.HomeViewModel

// ─── Screen entry point ───────────────────────────────────────────────────────

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun HomeScreen(
    navController: NavController,
    viewModel: HomeViewModel = viewModel()
) {
    val featured by viewModel.featured.collectAsStateWithLifecycle()
    val latest by viewModel.latest.collectAsStateWithLifecycle()
    val isLoading by viewModel.isLoading.collectAsStateWithLifecycle()

    var selectedCategory by remember { mutableStateOf<String?>(null) }

    val refreshState = rememberPullToRefreshState()

    PullToRefreshBox(
        isRefreshing = isLoading,
        onRefresh = { viewModel.loadData() },
        state = refreshState,
        modifier = Modifier.fillMaxSize()
    ) {
        LazyColumn(
            modifier = Modifier
                .fillMaxSize()
                .background(MaterialTheme.colorScheme.background),
            contentPadding = PaddingValues(bottom = 100.dp) // space for bottom nav
        ) {

            // ── Hero header ───────────────────────────────────────────────────
            item {
                HomeHeroHeader(
                    onSearchClick = {
                        navController.navigate(Screen.Explore.route)
                    },
                    onNotificationsClick = { /* TODO */ }
                )
            }

            // ── Category chips ────────────────────────────────────────────────
            item {
                CategoryChipRow(
                    selectedCategory = selectedCategory,
                    onCategorySelected = { key ->
                        selectedCategory = key
                        if (key != null) {
                            navController.navigate(Screen.Explore.route)
                        }
                    }
                )
            }

            // ── Featured / Sponsored section ──────────────────────────────────
            item {
                SectionHeader(
                    title = "Annonces sponsorisées",
                    actionLabel = "Voir tout",
                    onActionClick = { navController.navigate(Screen.Explore.route) },
                    modifier = Modifier.padding(horizontal = 16.dp, vertical = 8.dp)
                )
            }

            item {
                if (isLoading && featured.isEmpty()) {
                    Box(modifier = Modifier.padding(horizontal = 16.dp)) {
                        LoadingFeaturedRow()
                    }
                } else if (featured.isEmpty()) {
                    EmptyFeaturedPlaceholder()
                } else {
                    FeaturedCarousel(
                        listings = featured,
                        onListingClick = { listing ->
                            navController.navigate(Screen.ListingDetail.route(listing.id))
                        }
                    )
                }
            }

            // ── Latest listings section ───────────────────────────────────────
            item {
                SectionHeader(
                    title = "Dernières annonces",
                    actionLabel = "Voir tout",
                    onActionClick = { navController.navigate(Screen.Explore.route) },
                    modifier = Modifier.padding(start = 16.dp, end = 16.dp, top = 16.dp, bottom = 8.dp)
                )
            }

            // Render a 2-column grid inside LazyColumn using chunked rows
            if (isLoading && latest.isEmpty()) {
                item {
                    Box(modifier = Modifier.padding(horizontal = 16.dp)) {
                        LoadingGrid(count = 6)
                    }
                }
            } else {
                val rows = latest.chunked(2)
                items(rows, key = { row -> row.first().id }) { row ->
                    Row(
                        modifier = Modifier
                            .fillMaxWidth()
                            .padding(horizontal = 16.dp)
                            .padding(bottom = 12.dp),
                        horizontalArrangement = Arrangement.spacedBy(12.dp)
                    ) {
                        row.forEach { listing ->
                            ListingCard(
                                listing = listing,
                                onClick = {
                                    navController.navigate(Screen.ListingDetail.route(listing.id))
                                },
                                onFavorite = { /* TODO: wire favorite */ },
                                modifier = Modifier.weight(1f)
                            )
                        }
                        // Odd listing: fill remaining column with spacer
                        if (row.size == 1) {
                            Spacer(modifier = Modifier.weight(1f))
                        }
                    }
                }

                if (latest.isEmpty()) {
                    item {
                        EmptyLatestPlaceholder(
                            onExploreClick = { navController.navigate(Screen.Explore.route) }
                        )
                    }
                }
            }
        }
    }
}

// ─── Hero header ──────────────────────────────────────────────────────────────

@Composable
private fun HomeHeroHeader(
    onSearchClick: () -> Unit,
    onNotificationsClick: () -> Unit
) {
    Box(
        modifier = Modifier
            .fillMaxWidth()
            .background(
                Brush.verticalGradient(
                    colors = listOf(OceanBlue900, OceanBlue700)
                )
            )
    ) {
        Column(
            modifier = Modifier
                .fillMaxWidth()
                .padding(horizontal = 20.dp)
                .padding(top = 52.dp, bottom = 24.dp),
            verticalArrangement = Arrangement.spacedBy(16.dp)
        ) {
            // Top bar: logo + notification icon
            Row(
                modifier = Modifier.fillMaxWidth(),
                horizontalArrangement = Arrangement.SpaceBetween,
                verticalAlignment = Alignment.CenterVertically
            ) {
                // Logo
                Row(
                    verticalAlignment = Alignment.CenterVertically,
                    horizontalArrangement = Arrangement.spacedBy(8.dp)
                ) {
                    Box(
                        modifier = Modifier
                            .size(38.dp)
                            .clip(CircleShape)
                            .background(White.copy(alpha = 0.15f)),
                        contentAlignment = Alignment.Center
                    ) {
                        Icon(
                            imageVector = Icons.Filled.Anchor,
                            contentDescription = "AlBabor",
                            tint = White,
                            modifier = Modifier.size(22.dp)
                        )
                    }
                    Column {
                        Text(
                            text = "AlBabor",
                            style = MaterialTheme.typography.titleLarge,
                            color = White,
                            fontWeight = FontWeight.Bold,
                            fontSize = 20.sp
                        )
                        Text(
                            text = "Marketplace nautique",
                            style = MaterialTheme.typography.labelSmall,
                            color = OceanBlue100.copy(alpha = 0.85f),
                            fontSize = 11.sp
                        )
                    }
                }

                // Notification icon
                IconButton(
                    onClick = onNotificationsClick,
                    modifier = Modifier
                        .size(40.dp)
                        .clip(CircleShape)
                        .background(White.copy(alpha = 0.12f))
                ) {
                    Icon(
                        imageVector = Icons.Filled.NotificationsNone,
                        contentDescription = "Notifications",
                        tint = White,
                        modifier = Modifier.size(22.dp)
                    )
                }
            }

            // Tagline
            Text(
                text = "La marketplace nautique n°1 en Algérie",
                style = MaterialTheme.typography.bodyMedium,
                color = OceanBlue100.copy(alpha = 0.9f),
                fontWeight = FontWeight.Medium
            )

            // Search bar (tappable, navigates to Explore)
            Surface(
                modifier = Modifier
                    .fillMaxWidth()
                    .clip(RoundedCornerShape(14.dp))
                    .clickable(onClick = onSearchClick),
                shape = RoundedCornerShape(14.dp),
                color = White.copy(alpha = 0.15f),
                tonalElevation = 0.dp
            ) {
                Row(
                    modifier = Modifier
                        .fillMaxWidth()
                        .padding(horizontal = 16.dp, vertical = 14.dp),
                    verticalAlignment = Alignment.CenterVertically,
                    horizontalArrangement = Arrangement.spacedBy(10.dp)
                ) {
                    Icon(
                        imageVector = Icons.Filled.Search,
                        contentDescription = "Rechercher",
                        tint = White.copy(alpha = 0.75f),
                        modifier = Modifier.size(20.dp)
                    )
                    Text(
                        text = "Rechercher bateaux, jet-skis...",
                        style = MaterialTheme.typography.bodyMedium,
                        color = White.copy(alpha = 0.65f)
                    )
                }
            }
        }
    }
}

// ─── Category chips row ───────────────────────────────────────────────────────

@Composable
private fun CategoryChipRow(
    selectedCategory: String?,
    onCategorySelected: (String?) -> Unit
) {
    LazyRow(
        modifier = Modifier
            .fillMaxWidth()
            .background(OceanBlue900)
            .padding(horizontal = 16.dp, vertical = 12.dp),
        horizontalArrangement = Arrangement.spacedBy(8.dp)
    ) {
        items(allCategories, key = { it.key ?: "all" }) { item ->
            CategoryChip(
                item = item,
                selected = selectedCategory == item.key,
                onClick = { onCategorySelected(item.key) }
            )
        }
    }
}

// ─── Section header with "Voir tout" ─────────────────────────────────────────

@Composable
private fun SectionHeader(
    title: String,
    actionLabel: String,
    onActionClick: () -> Unit,
    modifier: Modifier = Modifier
) {
    Row(
        modifier = modifier.fillMaxWidth(),
        horizontalArrangement = Arrangement.SpaceBetween,
        verticalAlignment = Alignment.CenterVertically
    ) {
        Text(
            text = title,
            style = MaterialTheme.typography.titleMedium,
            fontWeight = FontWeight.Bold,
            color = MaterialTheme.colorScheme.onBackground
        )
        TextButton(
            onClick = onActionClick,
            contentPadding = PaddingValues(horizontal = 8.dp, vertical = 4.dp)
        ) {
            Text(
                text = actionLabel,
                style = MaterialTheme.typography.labelLarge,
                color = OceanBlue700,
                fontWeight = FontWeight.SemiBold
            )
        }
    }
}

// ─── Featured carousel ────────────────────────────────────────────────────────

@Composable
private fun FeaturedCarousel(
    listings: List<Listing>,
    onListingClick: (Listing) -> Unit
) {
    val pagerState = rememberPagerState(pageCount = { listings.size })

    Column(verticalArrangement = Arrangement.spacedBy(10.dp)) {
        HorizontalPager(
            state = pagerState,
            contentPadding = PaddingValues(horizontal = 16.dp),
            pageSpacing = 14.dp,
            modifier = Modifier.fillMaxWidth()
        ) { page ->
            FeaturedListingCard(
                listing = listings[page],
                onClick = { onListingClick(listings[page]) },
                onFavorite = { /* TODO: wire favorite */ }
            )
        }

        // Pager indicator dots
        if (listings.size > 1) {
            Row(
                modifier = Modifier.fillMaxWidth(),
                horizontalArrangement = Arrangement.Center,
                verticalAlignment = Alignment.CenterVertically
            ) {
                repeat(listings.size) { index ->
                    val isSelected = pagerState.currentPage == index
                    Box(
                        modifier = Modifier
                            .padding(horizontal = 3.dp)
                            .size(if (isSelected) 8.dp else 6.dp)
                            .clip(CircleShape)
                            .background(
                                if (isSelected) OceanBlue700
                                else MaterialTheme.colorScheme.outline
                            )
                    )
                }
            }
        }
    }
}

// ─── Empty states ─────────────────────────────────────────────────────────────

@Composable
private fun EmptyFeaturedPlaceholder() {
    Box(
        modifier = Modifier
            .fillMaxWidth()
            .height(120.dp)
            .padding(horizontal = 16.dp)
            .clip(RoundedCornerShape(16.dp))
            .background(OceanBlue50),
        contentAlignment = Alignment.Center
    ) {
        Text(
            text = "Aucune annonce sponsorisée pour le moment",
            style = MaterialTheme.typography.bodyMedium,
            color = OceanBlue700.copy(alpha = 0.7f),
            textAlign = TextAlign.Center
        )
    }
}

@Composable
private fun EmptyLatestPlaceholder(onExploreClick: () -> Unit) {
    Column(
        modifier = Modifier
            .fillMaxWidth()
            .padding(32.dp),
        horizontalAlignment = Alignment.CenterHorizontally,
        verticalArrangement = Arrangement.spacedBy(12.dp)
    ) {
        Text(
            text = "⚓",
            fontSize = 48.sp
        )
        Text(
            text = "Aucune annonce disponible",
            style = MaterialTheme.typography.titleSmall,
            color = MaterialTheme.colorScheme.onSurfaceVariant,
            textAlign = TextAlign.Center
        )
        Button(
            onClick = onExploreClick,
            colors = ButtonDefaults.buttonColors(containerColor = OceanBlue700)
        ) {
            Text(text = "Explorer les annonces", color = White)
        }
    }
}
