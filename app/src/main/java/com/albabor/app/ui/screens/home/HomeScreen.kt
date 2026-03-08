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
import androidx.compose.material.icons.filled.Search
import androidx.compose.material.icons.filled.Star
import androidx.compose.material.icons.filled.Tune
import androidx.compose.material3.Card
import androidx.compose.material3.CardDefaults
import androidx.compose.material3.Icon
import androidx.compose.material3.MaterialTheme
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
import com.albabor.app.ui.theme.Gray900
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

    var selectedCategory by remember { mutableStateOf<String?>(null) }
    val refreshState = rememberPullToRefreshState()

    PullToRefreshBox(
        isRefreshing = isLoading,
        onRefresh = { viewModel.loadData() },
        state = refreshState,
        modifier = Modifier.fillMaxSize()
    ) {
        Box(
            modifier = Modifier
                .fillMaxSize()
                .background(AppBackground)
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
                            if (key != null) navController.navigate(Screen.Explore.route)
                        }
                    )
                }

                if (featured.isNotEmpty()) {
                    item {
                        HomeSectionHeader(
                            icon = Icons.Filled.Star,
                            iconTint = Color(0xFFE67E22),
                            title = "Annonces en vedette",
                            actionLabel = "Voir tout",
                            onActionClick = { navController.navigate(Screen.Explore.route) },
                            modifier = Modifier.padding(top = 22.dp, bottom = 16.dp)
                        )
                    }

                    item {
                        LazyRow(
                            contentPadding = PaddingValues(horizontal = 20.dp),
                            horizontalArrangement = Arrangement.spacedBy(16.dp)
                        ) {
                            items(featured, key = { it.id }) { listing ->
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
                        title = "Annonces recentes",
                        actionLabel = "Voir tout",
                        onActionClick = { navController.navigate(Screen.Explore.route) },
                        modifier = Modifier.padding(
                            top = if (featured.isNotEmpty()) 28.dp else 22.dp,
                            bottom = 16.dp
                        )
                    )
                }

                if (isLoading && latest.isEmpty()) {
                    item {
                        Box(modifier = Modifier.padding(horizontal = 20.dp)) {
                            LoadingGrid(count = 2)
                        }
                    }
                } else if (latest.isEmpty()) {
                    item {
                        HomeEmptyLatest(onExploreClick = { navController.navigate(Screen.Explore.route) })
                    }
                } else {
                    items(latest, key = { it.id }) { listing ->
                        HomeRecentListingCard(
                            listing = listing,
                            onClick = { navController.navigate(Screen.ListingDetail.route(listing.id)) },
                            onFavorite = { },
                            modifier = Modifier.padding(horizontal = 20.dp, vertical = 7.dp)
                        )
                    }
                }
            }

            HomeHeader(
                onSearchClick = { navController.navigate(Screen.Explore.route) },
                onNotificationsClick = { }
            )
        }
    }
}

@Composable
private fun HomeHeader(
    onSearchClick: () -> Unit,
    onNotificationsClick: () -> Unit
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
                        OceanBlue900,
                        Color(0xFF145F85),
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
                    .background(White.copy(alpha = 0.15f))
                    .clickable(onClick = onNotificationsClick),
                contentAlignment = Alignment.Center
            ) {
                Icon(
                    imageVector = Icons.Filled.Notifications,
                    contentDescription = "Notifications",
                    tint = White,
                    modifier = Modifier.size(18.dp)
                )
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
                    .background(GlassSurfaceStrong, RoundedCornerShape(16.dp))
                    .padding(start = 6.dp, end = 8.dp, top = 8.dp, bottom = 8.dp),
                verticalAlignment = Alignment.CenterVertically
            ) {
                Box(
                    modifier = Modifier
                        .size(32.dp)
                        .clip(CircleShape)
                        .background(OceanBlue900.copy(alpha = 0.08f)),
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
    Row(
        modifier = Modifier
            .clip(RoundedCornerShape(999.dp))
            .background(if (selected) accent else accent.copy(alpha = 0.10f))
            .clickable(onClick = onClick)
            .padding(start = if (imageRes != null) 6.dp else 16.dp, end = 18.dp, top = 10.dp, bottom = 10.dp),
        verticalAlignment = Alignment.CenterVertically,
        horizontalArrangement = Arrangement.spacedBy(8.dp)
    ) {
        imageRes?.let {
            Image(
                painter = painterResource(id = it),
                contentDescription = null,
                modifier = Modifier
                    .size(28.dp)
                    .clip(RoundedCornerShape(8.dp)),
                contentScale = ContentScale.Crop
            )
        }

        Text(
            text = label,
            color = if (selected) White else accent,
            fontSize = 14.sp,
            fontWeight = FontWeight.SemiBold
        )
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
            color = Teal500,
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
    val offerLabel = listing.offerTypeLabel.takeIf { it.isNotBlank() }
    val quickFacts = buildList {
        listing.year?.let { add(it) }
        listing.power?.let { add("$it CV") }
        listing.conditionLabel.takeIf { it.isNotBlank() }?.let { add(it) }
    }.take(2)

    Card(
        modifier = modifier
            .fillMaxWidth()
            .clickable(onClick = onClick),
        shape = RoundedCornerShape(24.dp),
        colors = CardDefaults.cardColors(containerColor = White.copy(alpha = 0.98f)),
        elevation = CardDefaults.cardElevation(defaultElevation = 14.dp, pressedElevation = 18.dp),
        border = BorderStroke(1.dp, White.copy(alpha = 0.88f))
    ) {
        Column {
            Box(
                modifier = Modifier
                    .fillMaxWidth()
                    .height(214.dp)
            ) {
                SubcomposeAsyncImage(
                    model = listing.primaryImage,
                    contentDescription = listing.title,
                    modifier = Modifier.fillMaxSize(),
                    contentScale = ContentScale.Crop,
                    loading = { HomeCategoryBackdrop(listing = listing) },
                    error = { HomeCategoryBackdrop(listing = listing) }
                )

                Box(
                    modifier = Modifier
                        .matchParentSize()
                        .background(
                            Brush.verticalGradient(
                                colors = listOf(
                                    Color.Black.copy(alpha = 0.16f),
                                    Color.Transparent,
                                    OceanBlue900.copy(alpha = 0.38f)
                                )
                            )
                        )
                )

                Row(
                    modifier = Modifier
                        .fillMaxWidth()
                        .padding(horizontal = 14.dp, vertical = 14.dp),
                    verticalAlignment = Alignment.CenterVertically,
                    horizontalArrangement = Arrangement.SpaceBetween
                ) {
                    Surface(
                        shape = RoundedCornerShape(999.dp),
                        color = categoryAccentColor(listing.category).copy(alpha = 0.92f)
                    ) {
                        Text(
                            text = listing.categoryLabel,
                            modifier = Modifier.padding(horizontal = 11.dp, vertical = 6.dp),
                            color = White,
                            fontSize = 12.sp,
                            fontWeight = FontWeight.Bold
                        )
                    }

                    Box(
                        modifier = Modifier
                            .size(40.dp)
                            .clip(CircleShape)
                            .background(White.copy(alpha = 0.92f))
                            .clickable(onClick = onFavorite),
                        contentAlignment = Alignment.Center
                    ) {
                        Icon(
                            imageVector = if (listing.isFavorited) Icons.Filled.Favorite else Icons.Filled.FavoriteBorder,
                            contentDescription = null,
                            tint = if (listing.isFavorited) Coral500 else Gray500,
                            modifier = Modifier.size(18.dp)
                        )
                    }
                }

                Column(
                    modifier = Modifier
                        .align(Alignment.BottomStart)
                        .padding(14.dp),
                    verticalArrangement = Arrangement.spacedBy(8.dp)
                ) {
                    Surface(
                        shape = RoundedCornerShape(999.dp),
                        color = White.copy(alpha = 0.95f)
                    ) {
                        Text(
                            text = listing.formattedPrice,
                            modifier = Modifier.padding(horizontal = 12.dp, vertical = 7.dp),
                            color = OceanBlue900,
                            fontSize = 18.sp,
                            fontWeight = FontWeight.ExtraBold
                        )
                    }

                    Row(horizontalArrangement = Arrangement.spacedBy(8.dp)) {
                        listing.formattedConvertedPrice?.let { converted ->
                            HomeImageMetaTag(text = converted)
                        }

                        offerLabel?.let {
                            HomeImageMetaTag(text = it)
                        }
                    }
                }
            }

            Column(
                modifier = Modifier
                    .fillMaxWidth()
                    .padding(horizontal = 16.dp, vertical = 16.dp),
                verticalArrangement = Arrangement.spacedBy(12.dp)
            ) {
                Text(
                    text = listing.title,
                    color = Gray900,
                    fontSize = 18.sp,
                    fontWeight = FontWeight.Bold,
                    maxLines = 2,
                    overflow = TextOverflow.Ellipsis,
                    lineHeight = 23.sp
                )

                Row(
                    verticalAlignment = Alignment.CenterVertically,
                    horizontalArrangement = Arrangement.spacedBy(6.dp)
                ) {
                    Icon(
                        imageVector = Icons.Filled.LocationOn,
                        contentDescription = null,
                        tint = Teal500,
                        modifier = Modifier.size(15.dp)
                    )
                    Text(
                        text = listing.wilaya ?: "Algérie",
                        color = Gray500,
                        fontSize = 13.sp,
                        fontWeight = FontWeight.Medium
                    )
                }

                if (quickFacts.isNotEmpty()) {
                    Row(horizontalArrangement = Arrangement.spacedBy(8.dp)) {
                        quickFacts.forEach { fact ->
                            HomeListingFactChip(text = fact)
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
private fun HomeEmptyLatest(onExploreClick: () -> Unit) {
    Column(
        modifier = Modifier
            .fillMaxWidth()
            .padding(horizontal = 20.dp, vertical = 32.dp)
            .clip(RoundedCornerShape(24.dp))
            .background(GlassSurfaceStrong)
            .padding(24.dp),
        horizontalAlignment = Alignment.CenterHorizontally,
        verticalArrangement = Arrangement.spacedBy(12.dp)
    ) {
        Text(
            text = "Aucune annonce disponible",
            color = OceanBlue900,
            fontSize = 18.sp,
            fontWeight = FontWeight.Bold,
            textAlign = TextAlign.Center
        )
        Text(
            text = "Explorez les annonces disponibles sur la marketplace.",
            color = Gray500,
            fontSize = 14.sp,
            textAlign = TextAlign.Center
        )
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
