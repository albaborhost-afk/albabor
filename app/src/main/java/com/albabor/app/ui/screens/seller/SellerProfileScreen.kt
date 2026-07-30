package com.albabor.app.ui.screens.seller

import androidx.compose.foundation.background
import androidx.compose.foundation.border
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.grid.GridCells
import androidx.compose.foundation.lazy.grid.GridItemSpan
import androidx.compose.foundation.lazy.grid.LazyVerticalGrid
import androidx.compose.foundation.lazy.grid.items
import androidx.compose.foundation.lazy.grid.rememberLazyGridState
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ArrowBack
import androidx.compose.material.icons.filled.Check
import androidx.compose.material.icons.filled.Sailing
import androidx.compose.material.icons.filled.Verified
import androidx.compose.material.icons.outlined.Person
import androidx.compose.material.icons.outlined.VisibilityOff
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.draw.shadow
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.lifecycle.compose.collectAsStateWithLifecycle
import androidx.lifecycle.viewmodel.compose.viewModel
import androidx.navigation.NavController
import coil.compose.AsyncImage
import com.albabor.app.data.model.SellerProfileStats
import com.albabor.app.data.model.SellerProfileUser
import com.albabor.app.ui.components.ListingCard
import com.albabor.app.ui.components.LoadingGrid
import com.albabor.app.ui.navigation.Screen
import com.albabor.app.ui.theme.*
import com.albabor.app.viewmodel.SellerProfileViewModel

// Mêmes teintes que la carte vendeur de l'annonce (privées à cet écran-là).
private val SafetyGreen  = Color(0xFF27AE60)
private val HeroGradient = Brush.linearGradient(listOf(OceanBlue900, Teal500))

/**
 * Profil public d'un vendeur : son identité (ou « Invité » s'il se masque),
 * ses compteurs, et toutes ses annonces actives.
 */
@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun SellerProfileScreen(
    sellerId: Int,
    navController: NavController,
    onBack: () -> Unit,
) {
    val vm: SellerProfileViewModel = viewModel(
        key = "seller_$sellerId",
        factory = SellerProfileViewModel.factory(sellerId)
    )

    val seller        by vm.seller.collectAsStateWithLifecycle()
    val stats         by vm.stats.collectAsStateWithLifecycle()
    val listings      by vm.listings.collectAsStateWithLifecycle()
    val isLoading     by vm.isLoading.collectAsStateWithLifecycle()
    val isLoadingMore by vm.isLoadingMore.collectAsStateWithLifecycle()
    val error         by vm.error.collectAsStateWithLifecycle()

    val gridState = rememberLazyGridState()

    // Pagination : on charge la suite avant d'atteindre le bas de la liste.
    val shouldLoadMore by remember {
        derivedStateOf {
            val last = gridState.layoutInfo.visibleItemsInfo.lastOrNull()?.index ?: 0
            val total = gridState.layoutInfo.totalItemsCount
            total > 0 && last >= total - 4
        }
    }

    LaunchedEffect(shouldLoadMore) {
        if (shouldLoadMore) vm.loadMore()
    }

    Scaffold(
        containerColor = Gray50,
        topBar = {
            TopAppBar(
                title = {
                    Text(
                        text = seller?.name ?: "Vendeur",
                        fontWeight = FontWeight.Bold,
                        maxLines = 1
                    )
                },
                navigationIcon = {
                    IconButton(onClick = onBack) {
                        Icon(Icons.AutoMirrored.Filled.ArrowBack, contentDescription = "Retour")
                    }
                },
                colors = TopAppBarDefaults.topAppBarColors(
                    containerColor = Color.White,
                    titleContentColor = Gray900
                )
            )
        }
    ) { padding ->
        when {
            isLoading && listings.isEmpty() -> {
                Box(Modifier.fillMaxSize().padding(padding)) { LoadingGrid() }
            }

            error != null && seller == null -> {
                SellerProfileMessage(
                    modifier = Modifier.fillMaxSize().padding(padding),
                    title = "Profil indisponible",
                    body = error ?: "Réessayez dans un instant.",
                    onRetry = vm::load
                )
            }

            else -> {
                LazyVerticalGrid(
                    columns = GridCells.Fixed(2),
                    state = gridState,
                    modifier = Modifier.fillMaxSize().padding(padding),
                    contentPadding = PaddingValues(14.dp),
                    horizontalArrangement = Arrangement.spacedBy(12.dp),
                    verticalArrangement = Arrangement.spacedBy(12.dp)
                ) {
                    item(span = { GridItemSpan(maxLineSpan) }) {
                        seller?.let { SellerHeader(it, stats) }
                    }

                    item(span = { GridItemSpan(maxLineSpan) }) {
                        Text(
                            text = if (stats.activeListings > 0) {
                                "Ses annonces (${stats.activeListings})"
                            } else {
                                "Ses annonces"
                            },
                            style = MaterialTheme.typography.titleSmall.copy(
                                color = Gray900,
                                fontWeight = FontWeight.Bold
                            ),
                            modifier = Modifier.padding(top = 4.dp, bottom = 2.dp)
                        )
                    }

                    if (listings.isEmpty()) {
                        item(span = { GridItemSpan(maxLineSpan) }) {
                            SellerProfileMessage(
                                modifier = Modifier.fillMaxWidth().padding(vertical = 48.dp),
                                title = "Aucune annonce active",
                                body = "Ce vendeur n'a pas d'annonce en ligne pour le moment."
                            )
                        }
                    } else {
                        items(listings, key = { it.id }) { listing ->
                            ListingCard(
                                listing = listing,
                                onClick = {
                                    navController.navigate(Screen.ListingDetail.route(listing.id))
                                }
                            )
                        }
                    }

                    if (isLoadingMore) {
                        item(span = { GridItemSpan(maxLineSpan) }) {
                            Box(
                                Modifier.fillMaxWidth().padding(vertical = 20.dp),
                                contentAlignment = Alignment.Center
                            ) {
                                CircularProgressIndicator(
                                    color = OceanBlue700,
                                    modifier = Modifier.size(26.dp),
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

// ─── En-tête ──────────────────────────────────────────────────────────────────

@Composable
private fun SellerHeader(seller: SellerProfileUser, stats: SellerProfileStats) {
    Surface(
        modifier = Modifier.fillMaxWidth(),
        shape = RoundedCornerShape(20.dp),
        color = Color.White,
        shadowElevation = 4.dp
    ) {
        Column(Modifier.padding(18.dp)) {
            Row(
                verticalAlignment = Alignment.CenterVertically,
                horizontalArrangement = Arrangement.spacedBy(14.dp)
            ) {
                Box(contentAlignment = Alignment.BottomEnd) {
                    val avatarMod = Modifier.size(64.dp).clip(RoundedCornerShape(20.dp))

                    if (seller.avatar != null) {
                        AsyncImage(
                            model = seller.avatar,
                            contentDescription = seller.name,
                            contentScale = ContentScale.Crop,
                            modifier = avatarMod.background(OceanBlue100)
                        )
                    } else {
                        Box(
                            avatarMod
                                .background(HeroGradient, RoundedCornerShape(20.dp))
                                .border(2.dp, Color.White.copy(0.3f), RoundedCornerShape(20.dp)),
                            contentAlignment = Alignment.Center
                        ) {
                            if (seller.hideName) {
                                // Silhouette : une initiale trahirait le nom.
                                Icon(
                                    Icons.Outlined.Person,
                                    contentDescription = null,
                                    tint = Color.White,
                                    modifier = Modifier.size(32.dp)
                                )
                            } else {
                                Text(
                                    text = seller.name.take(1).uppercase(),
                                    color = Color.White,
                                    fontSize = 26.sp,
                                    fontWeight = FontWeight.Bold
                                )
                            }
                        }
                    }

                    if (seller.verifiedBadge) {
                        Box(
                            Modifier.offset(x = 4.dp, y = 4.dp).size(22.dp)
                                .shadow(4.dp, CircleShape).clip(CircleShape).background(SafetyGreen),
                            contentAlignment = Alignment.Center
                        ) {
                            Icon(Icons.Default.Check, null, tint = Color.White, modifier = Modifier.size(13.dp))
                        }
                    }
                }

                Column(Modifier.weight(1f)) {
                    Text(
                        text = seller.name,
                        style = MaterialTheme.typography.titleMedium.copy(
                            color = Gray900,
                            fontWeight = FontWeight.Bold
                        ),
                        maxLines = 1
                    )
                    Spacer(Modifier.height(4.dp))

                    if (seller.verifiedBadge) {
                        Row(
                            verticalAlignment = Alignment.CenterVertically,
                            horizontalArrangement = Arrangement.spacedBy(4.dp)
                        ) {
                            Icon(Icons.Default.Verified, null, tint = SafetyGreen, modifier = Modifier.size(14.dp))
                            Text(
                                "Vendeur vérifié",
                                fontSize = 12.sp,
                                color = SafetyGreen,
                                fontWeight = FontWeight.SemiBold
                            )
                        }
                    } else {
                        Text(
                            text = if (seller.isVendorAccount) "Vendeur professionnel" else "Vendeur particulier",
                            fontSize = 12.sp,
                            color = Gray500
                        )
                    }

                    if (seller.hideName) {
                        Spacer(Modifier.height(4.dp))
                        Row(
                            verticalAlignment = Alignment.CenterVertically,
                            horizontalArrangement = Arrangement.spacedBy(4.dp)
                        ) {
                            Icon(
                                Icons.Outlined.VisibilityOff,
                                null,
                                tint = Gray400,
                                modifier = Modifier.size(13.dp)
                            )
                            Text("Identité masquée", fontSize = 11.sp, color = Gray400)
                        }
                    }
                }
            }

            Spacer(Modifier.height(16.dp))

            Row(horizontalArrangement = Arrangement.spacedBy(10.dp)) {
                SellerStat(
                    modifier = Modifier.weight(1f),
                    value = stats.activeListings.toString(),
                    label = "Annonces"
                )
                SellerStat(
                    modifier = Modifier.weight(1f),
                    value = stats.totalViews.toString(),
                    label = "Vues"
                )
            }
        }
    }
}

@Composable
private fun SellerStat(modifier: Modifier = Modifier, value: String, label: String) {
    Surface(
        modifier = modifier,
        shape = RoundedCornerShape(14.dp),
        color = Gray100
    ) {
        Column(
            Modifier.padding(vertical = 12.dp),
            horizontalAlignment = Alignment.CenterHorizontally
        ) {
            Text(
                text = value,
                style = MaterialTheme.typography.titleMedium.copy(
                    color = Gray900,
                    fontWeight = FontWeight.Bold
                )
            )
            Text(text = label, fontSize = 11.sp, color = Gray500)
        }
    }
}

// ─── État vide / erreur ───────────────────────────────────────────────────────

@Composable
private fun SellerProfileMessage(
    modifier: Modifier = Modifier,
    title: String,
    body: String,
    onRetry: (() -> Unit)? = null,
) {
    Column(
        modifier = modifier.padding(horizontal = 32.dp),
        horizontalAlignment = Alignment.CenterHorizontally,
        verticalArrangement = Arrangement.Center
    ) {
        Box(
            Modifier.size(64.dp).clip(CircleShape).background(Gray100),
            contentAlignment = Alignment.Center
        ) {
            Icon(
                Icons.Default.Sailing,
                contentDescription = null,
                tint = Gray400,
                modifier = Modifier.size(30.dp)
            )
        }
        Spacer(Modifier.height(14.dp))
        Text(
            text = title,
            style = MaterialTheme.typography.titleSmall.copy(
                color = Gray900,
                fontWeight = FontWeight.Bold
            ),
            textAlign = TextAlign.Center
        )
        Spacer(Modifier.height(6.dp))
        Text(
            text = body,
            style = MaterialTheme.typography.bodySmall.copy(color = Gray500),
            textAlign = TextAlign.Center
        )

        if (onRetry != null) {
            Spacer(Modifier.height(16.dp))
            Button(
                onClick = onRetry,
                colors = ButtonDefaults.buttonColors(containerColor = OceanBlue700)
            ) {
                Text("Réessayer")
            }
        }
    }
}
