package com.albabor.app.ui.screens.home

import androidx.compose.foundation.BorderStroke
import androidx.compose.foundation.Image
import androidx.compose.foundation.background
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.LazyRow
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Favorite
import androidx.compose.material.icons.filled.FavoriteBorder
import androidx.compose.material.icons.filled.History
import androidx.compose.material.icons.filled.LocationOn
import androidx.compose.material.icons.filled.Notifications
import androidx.compose.material.icons.filled.NotificationsNone
import androidx.compose.material.icons.filled.Search
import androidx.compose.material.icons.filled.Star
import androidx.compose.material.icons.filled.Tune
import androidx.compose.material3.Card
import androidx.compose.material3.CardDefaults
import androidx.compose.material3.ButtonDefaults
import androidx.compose.material3.Icon
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.OutlinedButton
import androidx.compose.material3.Surface
import androidx.compose.material3.Text
import androidx.compose.material3.pulltorefresh.PullToRefreshBox
import androidx.compose.material3.pulltorefresh.rememberPullToRefreshState
import androidx.compose.runtime.Composable
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.draw.shadow
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.res.painterResource
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.text.style.TextOverflow
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.lifecycle.compose.collectAsStateWithLifecycle
import androidx.lifecycle.viewmodel.compose.viewModel
import androidx.navigation.NavController
import coil.compose.SubcomposeAsyncImage
import com.albabor.app.R
import com.albabor.app.data.model.Listing
import com.albabor.app.ui.components.FeaturedListingCard
import com.albabor.app.ui.components.LoadingFeaturedRow
import com.albabor.app.ui.components.LoadingGrid
import com.albabor.app.ui.navigation.Screen
import com.albabor.app.ui.theme.AppBackground
import com.albabor.app.ui.theme.Coral500
import com.albabor.app.ui.theme.GlassSurfaceStrong
import com.albabor.app.ui.theme.Gray500
import com.albabor.app.ui.theme.Gray100
import com.albabor.app.ui.theme.Gray900
import com.albabor.app.ui.theme.OceanBlue100
import com.albabor.app.ui.theme.OceanBlue50
import com.albabor.app.ui.theme.OceanBlue700
import com.albabor.app.ui.theme.OceanBlue900
import com.albabor.app.ui.theme.Teal500
import com.albabor.app.ui.theme.White
import com.albabor.app.ui.theme.categoryAccentColor
import com.albabor.app.ui.theme.categoryHeroImageRes
import com.albabor.app.ui.theme.categoryImageRes
import com.albabor.app.ui.theme.homeCategoryVisuals
import com.albabor.app.viewmodel.HomeViewModel
import java.util.Calendar

@Composable
fun HomeScreen(
    navController: NavController,
    viewModel: HomeViewModel = viewModel()
) {
    val featured by viewModel.featured.collectAsStateWithLifecycle()
    val latest by viewModel.latest.collectAsStateWithLifecycle()
    val isLoading by viewModel.isLoading.collectAsStateWithLifecycle()
    val unreadCount by viewModel.unreadCount.collectAsStateWithLifecycle()

    var selectedCategory by remember { mutableStateOf<String?>(null) }
    val refreshState = rememberPullToRefreshState()
    val selectedCategoryLabel = remember(selectedCategory) {
        homeCategoryVisuals.firstOrNull { it.key == selectedCategory }?.label
    }
    val filteredFeatured = remember(featured, selectedCategory) {
        featured.filterByCategory(selectedCategory)
    }
    val filteredLatest = remember(latest, selectedCategory) {
        latest.filterByCategory(selectedCategory)
    }
    val hasCategoryFilter = selectedCategory != null

    PullToRefreshBox(
        isRefreshing = isLoading,
        onRefresh = { viewModel.loadData() },
        state = refreshState,
        modifier = Modifier.fillMaxSize()
    ) {
        Box(
            modifier = Modifier
                .fillMaxSize()
                .background(
                    Brush.verticalGradient(
                        colors = listOf(
                            Color(0xFFF0F4F8),
                            Color(0xFFE8EEF4),
                            Color(0xFFF4F8FC)
                        )
                    )
                )
        ) {
            LazyColumn(
                modifier = Modifier.fillMaxSize(),
                contentPadding = PaddingValues(top = 190.dp, bottom = 132.dp),
                verticalArrangement = Arrangement.spacedBy(0.dp)
            ) {
                item {
                    CategoriesSection(
                        selectedCategory = selectedCategory,
                        onCategorySelected = { key ->
                            selectedCategory = if (selectedCategory == key) null else key
                        }
                    )
                }

                if (filteredFeatured.isNotEmpty()) {
                    item {
                        HomeSectionHeader(
                            icon = Icons.Filled.Star,
                            iconTint = Color(0xFFE67E22),
                            title = if (hasCategoryFilter) {
                                "En vedette${selectedCategoryLabel?.let { " • $it" } ?: ""}"
                            } else {
                                "Annonces en vedette"
                            },
                            actionLabel = "Voir tout",
                            onActionClick = { navController.navigate(Screen.Explore.route(selectedCategory)) },
                            modifier = Modifier.padding(top = 22.dp, bottom = 16.dp)
                        )
                    }

                    item {
                        LazyRow(
                            contentPadding = PaddingValues(horizontal = 20.dp),
                            horizontalArrangement = Arrangement.spacedBy(16.dp)
                        ) {
                            items(filteredFeatured, key = { it.id }) { listing ->
                                FeaturedListingCard(
                                    listing = listing,
                                    onClick = {
                                        navController.navigate(Screen.ListingDetail.route(listing.id))
                                    },
                                    onFavorite = { }
                                )
                            }
                        }
                    }
                }

                item {
                    HomeSectionHeader(
                        icon = Icons.Filled.History,
                        iconTint = Teal500,
                        title = if (hasCategoryFilter) {
                            "Récentes${selectedCategoryLabel?.let { " • $it" } ?: ""}"
                        } else {
                            "Annonces recentes"
                        },
                        actionLabel = "Voir tout",
                        onActionClick = { navController.navigate(Screen.Explore.route(selectedCategory)) },
                        modifier = Modifier.padding(
                            top = if (filteredFeatured.isNotEmpty()) 28.dp else 22.dp,
                            bottom = 16.dp
                        )
                    )
                }

                if (isLoading && filteredLatest.isEmpty()) {
                    item {
                        Box(modifier = Modifier.padding(horizontal = 20.dp)) {
                            LoadingGrid(count = 2)
                        }
                    }
                } else if (filteredLatest.isEmpty()) {
                    item {
                        HomeEmptyLatest(
                            selectedCategoryLabel = selectedCategoryLabel,
                            onExploreClick = { navController.navigate(Screen.Explore.route(selectedCategory)) },
                            onClearFilter = { selectedCategory = null }
                        )
                    }
                } else {
                    items(filteredLatest, key = { it.id }) { listing ->
                        HomeRecentListingCard(
                            listing = listing,
                            onClick = { navController.navigate(Screen.ListingDetail.route(listing.id)) },
                            onFavorite = { },
                            modifier = Modifier.padding(horizontal = 12.dp, vertical = 6.dp)
                        )
                    }
                }
            }

            HomeHeader(
                onSearchClick = { navController.navigate(Screen.Explore.route) },
                onNotificationsClick = { navController.navigate(Screen.Conversations.route) },
                unreadCount = unreadCount
            )
        }
    }
}

@Composable
private fun HomeHeader(
    onSearchClick: () -> Unit,
    onNotificationsClick: () -> Unit,
    unreadCount: Int = 0,
) {
    val hour = remember { Calendar.getInstance().get(Calendar.HOUR_OF_DAY) }
    val greeting = when (hour) {
        in 5..11 -> "Bonjour !"
        in 12..17 -> "Bon apres-midi !"
        else -> "Bonsoir !"
    }

    Column(
        modifier = Modifier
            .fillMaxWidth()
            .background(
                brush = Brush.linearGradient(
                    colors = listOf(
                        Color(0xFF102B45),
                        OceanBlue900,
                        OceanBlue700,
                        Teal500
                    )
                ),
                shape = RoundedCornerShape(bottomStart = 28.dp, bottomEnd = 28.dp)
            )
            .padding(horizontal = 20.dp, vertical = 20.dp)
    ) {
        Spacer(modifier = Modifier.height(24.dp))

        Row(
            modifier = Modifier.fillMaxWidth(),
            verticalAlignment = Alignment.CenterVertically
        ) {
            Surface(
                modifier = Modifier.size(44.dp),
                shape = CircleShape,
                color = White.copy(alpha = 0.96f),
                shadowElevation = 6.dp,
                tonalElevation = 0.dp
            ) {
                Box(contentAlignment = Alignment.Center) {
                    Image(
                        painter = painterResource(id = R.drawable.albabor_logo),
                        contentDescription = "AlBabor",
                        modifier = Modifier
                            .size(32.dp)
                            .clip(CircleShape),
                        contentScale = ContentScale.Fit
                    )
                }
            }

            Spacer(modifier = Modifier.size(12.dp))

            Column(modifier = Modifier.weight(1f)) {
                Text(
                    text = greeting,
                    color = White,
                    fontSize = 18.sp,
                    fontWeight = FontWeight.Bold
                )
                Text(
                    text = "Trouvez votre bateau ideal",
                    color = White.copy(alpha = 0.72f),
                    fontSize = 12.sp,
                    fontWeight = FontWeight.Medium
                )
            }

            Box(
                modifier = Modifier
                    .size(42.dp)
                    .clip(CircleShape)
                    .background(
                        if (unreadCount > 0) White.copy(alpha = 0.28f)
                        else White.copy(alpha = 0.18f)
                    )
                    .clickable(onClick = onNotificationsClick),
                contentAlignment = Alignment.Center
            ) {
                Icon(
                    imageVector = if (unreadCount > 0) Icons.Filled.Notifications
                                  else Icons.Filled.NotificationsNone,
                    contentDescription = "Notifications",
                    tint = White,
                    modifier = Modifier.size(18.dp)
                )
                // Red badge dot with count
                if (unreadCount > 0) {
                    Box(
                        modifier = Modifier
                            .align(Alignment.TopEnd)
                            .offset(x = 2.dp, y = (-2).dp)
                            .defaultMinSize(minWidth = 16.dp, minHeight = 16.dp)
                            .clip(CircleShape)
                            .background(Coral500),
                        contentAlignment = Alignment.Center
                    ) {
                        Text(
                            text = if (unreadCount > 99) "99+" else unreadCount.toString(),
                            color = White,
                            fontSize = 9.sp,
                            fontWeight = FontWeight.Bold,
                            modifier = Modifier.padding(horizontal = 2.dp)
                        )
                    }
                }
            }
        }

        Spacer(modifier = Modifier.height(16.dp))

        Surface(
                modifier = Modifier
                    .fillMaxWidth()
                    .clip(RoundedCornerShape(16.dp))
                    .clickable(onClick = onSearchClick),
            color = Color.Transparent,
            shadowElevation = 10.dp,
            tonalElevation = 0.dp
        ) {
            Row(
                modifier = Modifier
                    .background(
                        Brush.linearGradient(
                            listOf(
                                White.copy(alpha = 0.92f),
                                Color(0xFFF6FBFD)
                            )
                        ),
                        RoundedCornerShape(16.dp)
                    )
                    .padding(start = 6.dp, end = 8.dp, top = 8.dp, bottom = 8.dp),
                verticalAlignment = Alignment.CenterVertically
            ) {
                Box(
                    modifier = Modifier
                        .size(32.dp)
                        .clip(CircleShape)
                        .background(OceanBlue900.copy(alpha = 0.10f)),
                    contentAlignment = Alignment.Center
                ) {
                    Icon(
                        imageVector = Icons.Filled.Search,
                        contentDescription = null,
                        tint = OceanBlue900.copy(alpha = 0.6f),
                        modifier = Modifier.size(16.dp)
                    )
                }

                Spacer(modifier = Modifier.size(10.dp))

                Text(
                    text = "Rechercher bateaux, jet-ski...",
                    modifier = Modifier.weight(1f),
                    color = OceanBlue900.copy(alpha = 0.4f),
                    fontSize = 14.sp,
                    fontWeight = FontWeight.Medium
                )

                Box(
                    modifier = Modifier
                        .size(32.dp)
                        .clip(RoundedCornerShape(9.dp))
                        .background(
                            Brush.linearGradient(listOf(OceanBlue900, Teal500))
                        ),
                    contentAlignment = Alignment.Center
                ) {
                    Icon(
                        imageVector = Icons.Filled.Tune,
                        contentDescription = null,
                        tint = White,
                        modifier = Modifier.size(14.dp)
                    )
                }
            }
        }
    }
}

@Composable
private fun CategoriesSection(
    selectedCategory: String?,
    onCategorySelected: (String?) -> Unit
) {
    Column(
        modifier = Modifier.fillMaxWidth(),
        verticalArrangement = Arrangement.spacedBy(14.dp)
    ) {
        Text(
            text = "Categories",
            modifier = Modifier.padding(horizontal = 20.dp),
            color = OceanBlue900,
            fontSize = 18.sp,
            fontWeight = FontWeight.Bold
        )

        LazyRow(
            contentPadding = PaddingValues(horizontal = 20.dp),
            horizontalArrangement = Arrangement.spacedBy(12.dp)
        ) {
            items(homeCategoryVisuals, key = { it.key ?: "all" }) { category ->
                val isSelected = selectedCategory == category.key
                HomeCategoryChip(
                    label = category.label,
                    imageRes = category.imageRes,
                    accent = category.accent,
                    selected = isSelected,
                    onClick = { onCategorySelected(category.key) }
                )
            }
        }
    }
}

@Composable
private fun HomeCategoryChip(
    label: String,
    imageRes: Int?,
    accent: Color,
    selected: Boolean,
    onClick: () -> Unit
) {
    val chipShape = RoundedCornerShape(999.dp)

    Surface(
        modifier = Modifier.clickable(onClick = onClick),
        shape = chipShape,
        color = Color.Transparent,
        border = BorderStroke(
            width = 1.dp,
            color = if (selected) OceanBlue900.copy(alpha = 0.14f) else OceanBlue100
        ),
        shadowElevation = 0.dp
    ) {
        Row(
            modifier = Modifier
                .background(
                    brush = if (selected) {
                        Brush.linearGradient(listOf(OceanBlue900, OceanBlue700, Teal500))
                    } else {
                        Brush.linearGradient(listOf(White, Color(0xFFF8FBFD)))
                    },
                    shape = chipShape
                )
                .padding(
                    start = if (imageRes != null) 6.dp else 16.dp,
                    end = 18.dp,
                    top = 10.dp,
                    bottom = 10.dp
                ),
            verticalAlignment = Alignment.CenterVertically,
            horizontalArrangement = Arrangement.spacedBy(8.dp)
        ) {
            imageRes?.let {
                Box(
                    modifier = Modifier
                        .size(28.dp)
                        .clip(RoundedCornerShape(10.dp))
                        .background(
                            if (selected) White.copy(alpha = 0.18f)
                            else accent.copy(alpha = 0.10f)
                        ),
                    contentAlignment = Alignment.Center
                ) {
                    Image(
                        painter = painterResource(id = it),
                        contentDescription = null,
                        modifier = Modifier
                            .size(24.dp)
                            .clip(RoundedCornerShape(8.dp)),
                        contentScale = ContentScale.Crop
                    )
                }
            }

            Text(
                text = label,
                color = if (selected) White else OceanBlue900,
                fontSize = 14.sp,
                fontWeight = FontWeight.SemiBold
            )
        }
    }
}

@Composable
private fun HomeSectionHeader(
    icon: androidx.compose.ui.graphics.vector.ImageVector,
    iconTint: Color,
    title: String,
    actionLabel: String,
    onActionClick: () -> Unit,
    modifier: Modifier = Modifier
) {
    Row(
        modifier = modifier
            .fillMaxWidth()
            .padding(horizontal = 20.dp),
        verticalAlignment = Alignment.CenterVertically
    ) {
        Row(
            modifier = Modifier.weight(1f),
            verticalAlignment = Alignment.CenterVertically,
            horizontalArrangement = Arrangement.spacedBy(8.dp)
        ) {
            Icon(
                imageVector = icon,
                contentDescription = null,
                tint = iconTint,
                modifier = Modifier.size(15.dp)
            )
            Text(
                text = title,
                color = OceanBlue900,
                fontSize = 18.sp,
                fontWeight = FontWeight.Bold
            )
        }

        Text(
            text = actionLabel,
            color = OceanBlue700,
            fontSize = 14.sp,
            fontWeight = FontWeight.SemiBold,
            modifier = Modifier.clickable(onClick = onActionClick)
        )
    }
}

@Composable
private fun HomeRecentListingCard(
    listing: Listing,
    onClick: () -> Unit,
    onFavorite: () -> Unit,
    modifier: Modifier = Modifier
) {
    val quickFacts = buildList {
        listing.year?.let { add(it) }
        listing.power?.let { add("$it CV") }
    }.take(2)

    // Cadre (frame) around the annonce card — nicer from outside
    Box(
        modifier = modifier
            .fillMaxWidth()
            .padding(horizontal = 4.dp, vertical = 2.dp)
            .shadow(12.dp, RoundedCornerShape(24.dp), spotColor = OceanBlue900.copy(alpha = 0.12f))
            .clip(RoundedCornerShape(24.dp))
            .background(
                brush = Brush.verticalGradient(
                    colors = listOf(
                        OceanBlue100.copy(alpha = 0.25f),
                        OceanBlue50.copy(alpha = 0.15f)
                    )
                )
            )
            .padding(6.dp)
    ) {
        Card(
            modifier = Modifier
                .fillMaxWidth()
                .clickable(onClick = onClick),
            shape = RoundedCornerShape(18.dp),
            colors = CardDefaults.cardColors(containerColor = White),
            elevation = CardDefaults.cardElevation(defaultElevation = 0.dp, pressedElevation = 2.dp),
            border = BorderStroke(1.5.dp, OceanBlue100.copy(alpha = 0.5f))
        ) {
        Column {

            // ── IMAGE — 80% ──────────────────────────────────────────────────
            Box(
                modifier = Modifier
                    .fillMaxWidth()
                    .aspectRatio(1.3f)
                    .clip(RoundedCornerShape(topStart = 18.dp, topEnd = 18.dp))
            ) {
                SubcomposeAsyncImage(
                    model = coil.request.ImageRequest.Builder(androidx.compose.ui.platform.LocalContext.current)
                        .data(listing.primaryImage)
                        .size(coil.size.Size.ORIGINAL)
                        .build(),
                    contentDescription = listing.title,
                    modifier = Modifier.fillMaxSize(),
                    contentScale = ContentScale.Crop,
                    loading = { HomeCategoryBackdrop(listing = listing) },
                    error = { HomeCategoryBackdrop(listing = listing) }
                )

                // Gradient overlay — strong at bottom for price readability
                Box(
                    modifier = Modifier
                        .matchParentSize()
                        .background(
                            Brush.verticalGradient(
                                colorStops = arrayOf(
                                    0.0f to Color.Black.copy(alpha = 0.06f),
                                    0.45f to Color.Transparent,
                                    1.0f to Color.Black.copy(alpha = 0.62f)
                                )
                            )
                        )
                )

                // Category badge — top left
                Surface(
                    modifier = Modifier
                        .align(Alignment.TopStart)
                        .padding(11.dp),
                    shape = RoundedCornerShape(999.dp),
                    color = categoryAccentColor(listing.category)
                ) {
                    Text(
                        text = listing.categoryLabel,
                        modifier = Modifier.padding(horizontal = 10.dp, vertical = 5.dp),
                        color = White,
                        fontSize = 11.sp,
                        fontWeight = FontWeight.Bold
                    )
                }

                // Favorite button — top right
                Box(
                    modifier = Modifier
                        .align(Alignment.TopEnd)
                        .padding(9.dp)
                        .size(34.dp)
                        .clip(CircleShape)
                        .background(White.copy(alpha = 0.88f))
                        .clickable(onClick = onFavorite),
                    contentAlignment = Alignment.Center
                ) {
                    Icon(
                        imageVector = if (listing.isFavorited) Icons.Filled.Favorite else Icons.Filled.FavoriteBorder,
                        contentDescription = null,
                        tint = if (listing.isFavorited) Coral500 else Gray500,
                        modifier = Modifier.size(15.dp)
                    )
                }

                // Price + converted — bottom left inside image
                Column(
                    modifier = Modifier
                        .align(Alignment.BottomStart)
                        .padding(start = 12.dp, bottom = 12.dp),
                    verticalArrangement = Arrangement.spacedBy(4.dp)
                ) {
                    Text(
                        text = listing.formattedPrice,
                        color = White,
                        fontSize = 20.sp,
                        fontWeight = FontWeight.ExtraBold
                    )
                    listing.formattedConvertedPrice?.let { converted ->
                        Text(
                            text = converted,
                            color = White.copy(alpha = 0.80f),
                            fontSize = 12.sp,
                            fontWeight = FontWeight.Medium
                        )
                    }
                }
            }

            // ── INFO STRIP — 20% ─────────────────────────────────────────────
            Row(
                modifier = Modifier
                    .fillMaxWidth()
                    .padding(horizontal = 13.dp, vertical = 10.dp),
                verticalAlignment = Alignment.CenterVertically,
                horizontalArrangement = Arrangement.SpaceBetween
            ) {
                // Left: title + location
                Column(
                    modifier = Modifier.weight(1f),
                    verticalArrangement = Arrangement.spacedBy(3.dp)
                ) {
                    Text(
                        text = listing.title,
                        color = Gray900,
                        fontSize = 14.sp,
                        fontWeight = FontWeight.Bold,
                        maxLines = 1,
                        overflow = TextOverflow.Ellipsis
                    )
                    Row(
                        verticalAlignment = Alignment.CenterVertically,
                        horizontalArrangement = Arrangement.spacedBy(3.dp)
                    ) {
                        Icon(
                            imageVector = Icons.Filled.LocationOn,
                            contentDescription = null,
                            tint = OceanBlue700,
                            modifier = Modifier.size(11.dp)
                        )
                        Text(
                            text = listing.wilaya ?: "Algérie",
                            color = Gray500,
                            fontSize = 11.sp,
                            fontWeight = FontWeight.Medium,
                            maxLines = 1
                        )
                    }
                }

                // Right: year + CV chips
                if (quickFacts.isNotEmpty()) {
                    Row(horizontalArrangement = Arrangement.spacedBy(5.dp)) {
                        quickFacts.forEach { fact ->
                            Surface(
                                shape = RoundedCornerShape(999.dp),
                                color = OceanBlue100.copy(alpha = 0.7f)
                            ) {
                                Text(
                                    text = fact,
                                    modifier = Modifier.padding(horizontal = 9.dp, vertical = 4.dp),
                                    color = OceanBlue900,
                                    fontSize = 11.sp,
                                    fontWeight = FontWeight.SemiBold
                                )
                            }
                        }
                    }
                }
            }
        }
        }
    }
}

@Composable
private fun HomeImageMetaTag(text: String) {
    Surface(
        shape = RoundedCornerShape(999.dp),
        color = Color.Black.copy(alpha = 0.28f)
    ) {
        Text(
            text = text,
            modifier = Modifier.padding(horizontal = 10.dp, vertical = 5.dp),
            color = White,
            fontSize = 11.sp,
            fontWeight = FontWeight.SemiBold,
            maxLines = 1
        )
    }
}

@Composable
private fun HomeListingFactChip(text: String) {
    Surface(
        shape = RoundedCornerShape(999.dp),
        color = OceanBlue900.copy(alpha = 0.08f),
        border = BorderStroke(1.dp, OceanBlue900.copy(alpha = 0.08f))
    ) {
        Text(
            text = text,
            modifier = Modifier.padding(horizontal = 11.dp, vertical = 6.dp),
            color = OceanBlue900,
            fontSize = 12.sp,
            fontWeight = FontWeight.SemiBold,
            maxLines = 1
        )
    }
}

@Composable
private fun HomeCategoryBackdrop(listing: Listing) {
    Image(
        painter = painterResource(id = categoryHeroImageRes(listing.category)),
        contentDescription = null,
        modifier = Modifier.fillMaxSize(),
        contentScale = ContentScale.Crop
    )
}

@Composable
private fun HomeEmptyLatest(
    selectedCategoryLabel: String? = null,
    onExploreClick: () -> Unit,
    onClearFilter: (() -> Unit)? = null
) {
    Column(
        modifier = Modifier
            .fillMaxWidth()
            .padding(horizontal = 20.dp, vertical = 32.dp)
            .clip(RoundedCornerShape(24.dp))
            .background(
                Brush.linearGradient(
                    listOf(
                        White,
                        Color(0xFFF7FBFD)
                    )
                )
            )
            .padding(24.dp),
        horizontalAlignment = Alignment.CenterHorizontally,
        verticalArrangement = Arrangement.spacedBy(12.dp)
    ) {
        Text(
            text = if (selectedCategoryLabel != null) {
                "Aucune annonce pour $selectedCategoryLabel"
            } else {
                "Aucune annonce disponible"
            },
            color = OceanBlue900,
            fontSize = 18.sp,
            fontWeight = FontWeight.Bold,
            textAlign = TextAlign.Center
        )
        Text(
            text = if (selectedCategoryLabel != null) {
                "Restez sur l'accueil ou effacez le filtre pour revoir toutes les annonces."
            } else {
                "Explorez les annonces disponibles sur la marketplace."
            },
            color = Gray500,
            fontSize = 14.sp,
            textAlign = TextAlign.Center
        )
        onClearFilter?.let { clearFilter ->
            OutlinedButton(
                onClick = clearFilter,
                shape = RoundedCornerShape(14.dp),
                border = BorderStroke(1.dp, OceanBlue100),
                colors = ButtonDefaults.outlinedButtonColors(contentColor = OceanBlue900)
            ) {
                Text("Effacer le filtre", fontWeight = FontWeight.SemiBold)
            }
        }
        Surface(
            modifier = Modifier.clickable(onClick = onExploreClick),
            shape = RoundedCornerShape(14.dp),
            color = Color.Transparent
        ) {
            Box(
                modifier = Modifier
                    .background(Brush.linearGradient(listOf(OceanBlue900, Teal500)), RoundedCornerShape(14.dp))
                    .padding(horizontal = 20.dp, vertical = 12.dp)
            ) {
                Text(
                    text = "Explorer les annonces",
                    color = White,
                    fontWeight = FontWeight.SemiBold
                )
            }
        }
    }
}

private fun List<Listing>.filterByCategory(category: String?): List<Listing> =
    if (category.isNullOrBlank()) this else filter { it.category == category }
