package com.albabor.app.ui.screens.listing

import android.content.Intent
import android.net.Uri
import androidx.compose.animation.AnimatedVisibility
import androidx.compose.animation.core.animateFloatAsState
import androidx.compose.animation.core.tween
import androidx.compose.animation.fadeIn
import androidx.compose.animation.fadeOut
import androidx.compose.foundation.background
import androidx.compose.foundation.border
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.pager.HorizontalPager
import androidx.compose.foundation.pager.rememberPagerState
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ArrowBack
import androidx.compose.material.icons.filled.*
import androidx.compose.material.icons.outlined.FavoriteBorder
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.graphicsLayer
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextOverflow
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.lifecycle.compose.collectAsStateWithLifecycle
import androidx.lifecycle.viewmodel.compose.viewModel
import coil.compose.AsyncImage
import coil.request.ImageRequest
import com.albabor.app.data.model.Listing
import com.albabor.app.ui.theme.*
import com.albabor.app.viewmodel.ListingDetailViewModel

// ── Entry Point ───────────────────────────────────────────────────────────────

@Composable
fun ListingDetailScreen(
    listingId: Int,
    onBack: () -> Unit,
    onMediationRequested: (listingId: Int) -> Unit = {}
) {
    val vm: ListingDetailViewModel = viewModel(
        key     = "listing_$listingId",
        factory = ListingDetailViewModel.factory(listingId)
    )

    val state       by vm.state.collectAsStateWithLifecycle()
    val isFavorited by vm.isFavorited.collectAsStateWithLifecycle()
    val snackbar    by vm.snackbar.collectAsStateWithLifecycle()
    val snackbarHostState = remember { SnackbarHostState() }

    LaunchedEffect(snackbar) {
        snackbar?.let {
            snackbarHostState.showSnackbar(it)
            vm.clearSnackbar()
        }
    }

    Scaffold(
        snackbarHost = { SnackbarHost(snackbarHostState) },
        containerColor = MaterialTheme.colorScheme.background
    ) { padding ->
        Box(Modifier.fillMaxSize().padding(padding)) {
            when (val s = state) {
                is ListingDetailViewModel.State.Loading -> {
                    CircularProgressIndicator(
                        modifier = Modifier.align(Alignment.Center),
                        color    = OceanBlue700
                    )
                }
                is ListingDetailViewModel.State.Error -> {
                    ErrorContent(
                        msg     = s.msg,
                        onRetry = vm::loadListing,
                        modifier = Modifier.align(Alignment.Center)
                    )
                }
                is ListingDetailViewModel.State.Success -> {
                    ListingDetailContent(
                        listing             = s.listing,
                        isFavorited         = isFavorited,
                        onBack              = onBack,
                        onToggleFavorite    = vm::toggleFavorite,
                        onMediationRequested = onMediationRequested
                    )
                }
            }
        }
    }
}

// ── Main Content ──────────────────────────────────────────────────────────────

@Composable
private fun ListingDetailContent(
    listing: Listing,
    isFavorited: Boolean,
    onBack: () -> Unit,
    onToggleFavorite: () -> Unit,
    onMediationRequested: (listingId: Int) -> Unit
) {
    val context = LocalContext.current

    LazyColumn(
        modifier            = Modifier.fillMaxSize(),
        contentPadding      = PaddingValues(bottom = 120.dp),
        verticalArrangement = Arrangement.spacedBy(0.dp)
    ) {
        // ── Image carousel ────────────────────────────────────────────────────
        item {
            ImageCarousel(
                images      = listing.images.map { it.url },
                isFavorited = isFavorited,
                onBack      = onBack,
                onFavorite  = onToggleFavorite
            )
        }

        // ── Title + Status ────────────────────────────────────────────────────
        item {
            Column(
                modifier = Modifier
                    .fillMaxWidth()
                    .background(MaterialTheme.colorScheme.surface)
                    .padding(horizontal = 20.dp, vertical = 16.dp)
            ) {
                Row(
                    verticalAlignment    = Alignment.Top,
                    horizontalArrangement = Arrangement.spacedBy(12.dp)
                ) {
                    Text(
                        text       = listing.title,
                        style      = MaterialTheme.typography.headlineSmall,
                        fontWeight = FontWeight.Bold,
                        color      = MaterialTheme.colorScheme.onSurface,
                        modifier   = Modifier.weight(1f)
                    )
                    StatusBadge(status = listing.status)
                }

                Spacer(Modifier.height(12.dp))

                // Price
                Text(
                    text       = listing.formattedPrice,
                    fontSize   = 28.sp,
                    fontWeight = FontWeight.ExtraBold,
                    color      = OceanBlue700
                )
                listing.formattedConvertedPrice?.let { converted ->
                    Text(
                        text  = converted,
                        style = MaterialTheme.typography.bodyMedium,
                        color = MaterialTheme.colorScheme.onSurfaceVariant
                    )
                }
                listing.offerType?.let { offer ->
                    Spacer(Modifier.height(4.dp))
                    Text(
                        text  = offerTypeLabel(offer),
                        style = MaterialTheme.typography.bodySmall,
                        color = MaterialTheme.colorScheme.onSurfaceVariant
                    )
                }
            }
        }

        // ── Info chips ────────────────────────────────────────────────────────
        item {
            Spacer(Modifier.height(8.dp))
            InfoChipsRow(listing = listing)
        }

        // ── Seller card ───────────────────────────────────────────────────────
        listing.user?.let { seller ->
            item {
                Spacer(Modifier.height(8.dp))
                SellerCard(seller = seller)
            }
        }

        // ── Description ───────────────────────────────────────────────────────
        listing.description?.let { desc ->
            item {
                Spacer(Modifier.height(8.dp))
                SectionCard(title = "Description") {
                    ExpandableText(text = desc)
                }
            }
        }

        // ── Caractéristiques ──────────────────────────────────────────────────
        if (!listing.specs.isNullOrEmpty()) {
            item {
                Spacer(Modifier.height(8.dp))
                SpecsSection(listing = listing)
            }
        }

        // ── Stats ─────────────────────────────────────────────────────────────
        item {
            Spacer(Modifier.height(8.dp))
            StatsRow(
                views     = listing.viewsCount,
                favorites = listing.favoritesCount,
                timeAgo   = listing.timeAgo
            )
        }
    }

    // ── Bottom sticky contact bar ─────────────────────────────────────────────
    Box(Modifier.fillMaxSize()) {
        ContactBar(
            listing          = listing,
            isFavorited      = isFavorited,
            onToggleFavorite = onToggleFavorite,
            onMediation      = { onMediationRequested(listing.id) },
            onCall           = {
                listing.user?.phone?.let { phone ->
                    val intent = Intent(Intent.ACTION_DIAL, Uri.parse("tel:$phone"))
                    context.startActivity(intent)
                }
            },
            onEmail = {
                val intent = Intent(Intent.ACTION_SENDTO).apply {
                    data    = Uri.parse("mailto:")
                    putExtra(Intent.EXTRA_SUBJECT, "AlBabor – ${listing.title}")
                }
                context.startActivity(intent)
            },
            modifier = Modifier.align(Alignment.BottomCenter)
        )
    }
}

// ── Image Carousel ────────────────────────────────────────────────────────────

@Composable
private fun ImageCarousel(
    images: List<String>,
    isFavorited: Boolean,
    onBack: () -> Unit,
    onFavorite: () -> Unit
) {
    val displayImages = images.ifEmpty { listOf("") }
    val pagerState    = rememberPagerState(pageCount = { displayImages.size })

    Box(
        modifier = Modifier
            .fillMaxWidth()
            .height(320.dp)
    ) {
        HorizontalPager(
            state    = pagerState,
            modifier = Modifier.fillMaxSize()
        ) { page ->
            if (displayImages[page].isNotBlank()) {
                AsyncImage(
                    model = ImageRequest.Builder(LocalContext.current)
                        .data(displayImages[page])
                        .crossfade(true)
                        .build(),
                    contentDescription = "Photo ${page + 1}",
                    contentScale       = ContentScale.Crop,
                    modifier           = Modifier.fillMaxSize()
                )
            } else {
                Box(
                    modifier           = Modifier
                        .fillMaxSize()
                        .background(OceanBlue100),
                    contentAlignment   = Alignment.Center
                ) {
                    Icon(
                        imageVector        = Icons.Default.DirectionsBoat,
                        contentDescription = null,
                        tint               = OceanBlue500,
                        modifier           = Modifier.size(80.dp)
                    )
                }
            }
        }

        // Gradient overlay bottom
        Box(
            modifier = Modifier
                .fillMaxWidth()
                .height(100.dp)
                .align(Alignment.BottomCenter)
                .background(
                    Brush.verticalGradient(
                        listOf(Color.Transparent, Color.Black.copy(alpha = 0.45f))
                    )
                )
        )

        // Gradient overlay top (for back button readability)
        Box(
            modifier = Modifier
                .fillMaxWidth()
                .height(80.dp)
                .align(Alignment.TopCenter)
                .background(
                    Brush.verticalGradient(
                        listOf(Color.Black.copy(alpha = 0.35f), Color.Transparent)
                    )
                )
        )

        // Back button
        IconButton(
            onClick  = onBack,
            modifier = Modifier
                .align(Alignment.TopStart)
                .padding(8.dp)
                .size(40.dp)
                .clip(CircleShape)
                .background(Color.Black.copy(alpha = 0.35f))
        ) {
            Icon(
                imageVector        = Icons.AutoMirrored.Filled.ArrowBack,
                contentDescription = "Retour",
                tint               = Color.White
            )
        }

        // Favorite button
        val favScale by animateFloatAsState(
            targetValue = if (isFavorited) 1.2f else 1f,
            animationSpec = tween(150),
            label = "favScale"
        )
        IconButton(
            onClick  = onFavorite,
            modifier = Modifier
                .align(Alignment.TopEnd)
                .padding(8.dp)
                .size(40.dp)
                .clip(CircleShape)
                .background(Color.Black.copy(alpha = 0.35f))
        ) {
            Icon(
                imageVector = if (isFavorited) Icons.Filled.Favorite
                              else Icons.Outlined.FavoriteBorder,
                contentDescription = "Favoris",
                tint               = if (isFavorited) Color(0xFFFF4D6D) else Color.White,
                modifier           = Modifier
                    .size(22.dp)
                    .graphicsLayer(scaleX = favScale, scaleY = favScale)
            )
        }

        // Page indicators
        if (displayImages.size > 1) {
            Row(
                modifier              = Modifier
                    .align(Alignment.BottomCenter)
                    .padding(bottom = 12.dp),
                horizontalArrangement = Arrangement.spacedBy(6.dp),
                verticalAlignment     = Alignment.CenterVertically
            ) {
                repeat(displayImages.size) { index ->
                    val isSelected = index == pagerState.currentPage
                    Box(
                        modifier = Modifier
                            .size(if (isSelected) 8.dp else 6.dp)
                            .clip(CircleShape)
                            .background(
                                if (isSelected) Color.White
                                else Color.White.copy(alpha = 0.5f)
                            )
                    )
                }
            }
        }

        // Image count badge
        if (displayImages.size > 1) {
            Surface(
                modifier      = Modifier
                    .align(Alignment.BottomEnd)
                    .padding(12.dp),
                shape         = RoundedCornerShape(12.dp),
                color         = Color.Black.copy(alpha = 0.55f)
            ) {
                Text(
                    text     = "${pagerState.currentPage + 1} / ${displayImages.size}",
                    color    = Color.White,
                    style    = MaterialTheme.typography.labelSmall,
                    modifier = Modifier.padding(horizontal = 8.dp, vertical = 4.dp)
                )
            }
        }
    }
}

// ── Status Badge ──────────────────────────────────────────────────────────────

@Composable
private fun StatusBadge(status: String) {
    val (bg, fg, label) = when (status) {
        "active"           -> Triple(Success100, Success500, "Active")
        "sold"             -> Triple(Error100,   Error500,   "Vendu")
        "paused"           -> Triple(Warning100, Warning500, "En pause")
        "pending_review"   -> Triple(OceanBlue100, OceanBlue700, "En révision")
        "awaiting_payment" -> Triple(Gold100,    Gold500,    "Paiement requis")
        "expired"          -> Triple(Gray100,    Gray500,    "Expiré")
        "rejected"         -> Triple(Error100,   Error500,   "Rejeté")
        "draft"            -> Triple(Gray100,    Gray500,    "Brouillon")
        else               -> Triple(Gray100,    Gray500,    status)
    }
    Surface(
        shape = RoundedCornerShape(20.dp),
        color = bg
    ) {
        Text(
            text     = label,
            color    = fg,
            style    = MaterialTheme.typography.labelSmall,
            fontWeight = FontWeight.SemiBold,
            modifier = Modifier.padding(horizontal = 10.dp, vertical = 4.dp)
        )
    }
}

// ── Info Chips Row ────────────────────────────────────────────────────────────

@Composable
private fun InfoChipsRow(listing: Listing) {
    Surface(
        modifier = Modifier.fillMaxWidth(),
        color    = MaterialTheme.colorScheme.surface
    ) {
        Column(
            modifier = Modifier.padding(horizontal = 20.dp, vertical = 14.dp),
            verticalArrangement = Arrangement.spacedBy(10.dp)
        ) {
            Row(
                horizontalArrangement = Arrangement.spacedBy(8.dp),
                modifier = Modifier.fillMaxWidth()
            ) {
                InfoChip(
                    icon  = Icons.Default.Category,
                    label = listing.categoryLabel,
                    emoji = listing.categoryIcon,
                    modifier = Modifier.weight(1f)
                )
                listing.year?.let {
                    InfoChip(
                        icon  = Icons.Default.CalendarToday,
                        label = it,
                        modifier = Modifier.weight(1f)
                    )
                }
                listing.wilaya?.let {
                    InfoChip(
                        icon  = Icons.Default.LocationOn,
                        label = it,
                        modifier = Modifier.weight(1f)
                    )
                }
            }
            Row(
                horizontalArrangement = Arrangement.spacedBy(8.dp),
                modifier = Modifier.fillMaxWidth()
            ) {
                listing.condition?.let {
                    InfoChip(
                        icon  = Icons.Default.Star,
                        label = conditionLabel(it),
                        modifier = Modifier.weight(1f)
                    )
                }
                listing.power?.let {
                    InfoChip(
                        icon  = Icons.Default.Speed,
                        label = "$it CV",
                        modifier = Modifier.weight(1f)
                    )
                }
                Spacer(Modifier.weight(if (listing.condition == null || listing.power == null) 1f else 0f))
            }
        }
    }
}

@Composable
private fun InfoChip(
    icon: androidx.compose.ui.graphics.vector.ImageVector,
    label: String,
    emoji: String? = null,
    modifier: Modifier = Modifier
) {
    Surface(
        modifier = modifier,
        shape    = RoundedCornerShape(10.dp),
        color    = OceanBlue50,
        border   = androidx.compose.foundation.BorderStroke(1.dp, OceanBlue100)
    ) {
        Row(
            modifier              = Modifier.padding(horizontal = 8.dp, vertical = 7.dp),
            verticalAlignment     = Alignment.CenterVertically,
            horizontalArrangement = Arrangement.spacedBy(4.dp)
        ) {
            if (emoji != null) {
                Text(text = emoji, fontSize = 13.sp)
            } else {
                Icon(
                    imageVector        = icon,
                    contentDescription = null,
                    tint               = OceanBlue700,
                    modifier           = Modifier.size(14.dp)
                )
            }
            Text(
                text     = label,
                style    = MaterialTheme.typography.labelSmall,
                color    = OceanBlue900,
                fontWeight = FontWeight.Medium,
                maxLines = 1,
                overflow = TextOverflow.Ellipsis
            )
        }
    }
}

// ── Seller Card ───────────────────────────────────────────────────────────────

@Composable
private fun SellerCard(seller: com.albabor.app.data.model.ListingUser) {
    Surface(
        modifier = Modifier
            .fillMaxWidth()
            .padding(horizontal = 0.dp),
        color = MaterialTheme.colorScheme.surface
    ) {
        Row(
            modifier              = Modifier
                .fillMaxWidth()
                .padding(horizontal = 20.dp, vertical = 16.dp),
            verticalAlignment     = Alignment.CenterVertically,
            horizontalArrangement = Arrangement.spacedBy(14.dp)
        ) {
            // Avatar
            Box(
                contentAlignment = Alignment.BottomEnd
            ) {
                if (seller.avatar != null) {
                    AsyncImage(
                        model = seller.avatar,
                        contentDescription = seller.name,
                        contentScale = ContentScale.Crop,
                        modifier = Modifier
                            .size(52.dp)
                            .clip(CircleShape)
                            .background(OceanBlue100)
                    )
                } else {
                    Box(
                        modifier         = Modifier
                            .size(52.dp)
                            .clip(CircleShape)
                            .background(
                                Brush.linearGradient(
                                    listOf(OceanBlue500, OceanBlue700)
                                )
                            ),
                        contentAlignment = Alignment.Center
                    ) {
                        Text(
                            text     = seller.name.take(1).uppercase(),
                            color    = Color.White,
                            fontSize = 22.sp,
                            fontWeight = FontWeight.Bold
                        )
                    }
                }
                if (seller.isVerified) {
                    Icon(
                        imageVector        = Icons.Default.Verified,
                        contentDescription = "Vendeur vérifié",
                        tint               = OceanBlue700,
                        modifier           = Modifier
                            .size(18.dp)
                            .background(Color.White, CircleShape)
                    )
                }
            }

            // Info
            Column(modifier = Modifier.weight(1f)) {
                Row(
                    verticalAlignment     = Alignment.CenterVertically,
                    horizontalArrangement = Arrangement.spacedBy(6.dp)
                ) {
                    Text(
                        text       = seller.name,
                        style      = MaterialTheme.typography.titleSmall,
                        fontWeight = FontWeight.SemiBold,
                        color      = MaterialTheme.colorScheme.onSurface
                    )
                    if (seller.isVerified) {
                        Icon(
                            imageVector        = Icons.Default.CheckCircle,
                            contentDescription = null,
                            tint               = Success500,
                            modifier           = Modifier.size(16.dp)
                        )
                    }
                }
                Text(
                    text  = "Vendeur particulier",
                    style = MaterialTheme.typography.bodySmall,
                    color = MaterialTheme.colorScheme.onSurfaceVariant
                )
            }

            Icon(
                imageVector        = Icons.Default.ChevronRight,
                contentDescription = null,
                tint               = MaterialTheme.colorScheme.onSurfaceVariant
            )
        }
    }
}

// ── Section Card ──────────────────────────────────────────────────────────────

@Composable
private fun SectionCard(
    title: String,
    content: @Composable ColumnScope.() -> Unit
) {
    Surface(
        modifier = Modifier.fillMaxWidth(),
        color    = MaterialTheme.colorScheme.surface
    ) {
        Column(modifier = Modifier.padding(horizontal = 20.dp, vertical = 16.dp)) {
            Text(
                text       = title,
                style      = MaterialTheme.typography.titleMedium,
                fontWeight = FontWeight.Bold,
                color      = MaterialTheme.colorScheme.onSurface
            )
            Spacer(Modifier.height(12.dp))
            content()
        }
    }
}

// ── Expandable Text ───────────────────────────────────────────────────────────

@Composable
private fun ExpandableText(text: String) {
    var expanded by remember { mutableStateOf(false) }
    val shouldClip = text.length > 300

    Column {
        Text(
            text     = text,
            style    = MaterialTheme.typography.bodyMedium,
            color    = MaterialTheme.colorScheme.onSurface,
            maxLines = if (expanded || !shouldClip) Int.MAX_VALUE else 5,
            overflow = TextOverflow.Ellipsis
        )
        if (shouldClip) {
            Spacer(Modifier.height(8.dp))
            Text(
                text     = if (expanded) "Voir moins" else "Voir plus",
                color    = OceanBlue700,
                style    = MaterialTheme.typography.labelMedium,
                fontWeight = FontWeight.SemiBold,
                modifier = Modifier.clickable { expanded = !expanded }
            )
        }
    }
}

// ── Specs Section ─────────────────────────────────────────────────────────────

@Composable
private fun SpecsSection(listing: Listing) {
    Surface(
        modifier = Modifier.fillMaxWidth(),
        color    = MaterialTheme.colorScheme.surface
    ) {
        Column(modifier = Modifier.padding(horizontal = 20.dp, vertical = 16.dp)) {
            Text(
                text       = "Caractéristiques",
                style      = MaterialTheme.typography.titleMedium,
                fontWeight = FontWeight.Bold,
                color      = MaterialTheme.colorScheme.onSurface
            )
            Spacer(Modifier.height(12.dp))

            val specs = listing.specs ?: return@Column

            // General group
            val general = specs["general"] as? Map<*, *>
            if (!general.isNullOrEmpty()) {
                SpecsGroup(
                    title = "Général",
                    icon  = Icons.Default.Info,
                    items = buildList {
                        general["annee_construction"]?.toString()?.let { add("Année" to it) }
                        general["marque"]?.toString()?.let             { add("Marque" to it) }
                        general["modele"]?.toString()?.let             { add("Modèle" to it) }
                        general["couleur"]?.toString()?.let            { add("Couleur" to it) }
                        general["compatible_avec"]?.toString()?.let    { add("Compatible avec" to it) }
                    }
                )
                Spacer(Modifier.height(12.dp))
            }

            // Motorisation group
            val moto = specs["motorisation"] as? Map<*, *>
            if (!moto.isNullOrEmpty()) {
                SpecsGroup(
                    title = "Motorisation",
                    icon  = Icons.Default.Speed,
                    items = buildList {
                        moto["puissance_totale"]?.toString()?.let { add("Puissance" to "$it CV") }
                        moto["type_moteur"]?.toString()?.let      { add("Type moteur" to engineTypeLabel(it)) }
                        moto["nb_moteurs"]?.toString()?.let       { add("Nb moteurs" to it) }
                    }
                )
                Spacer(Modifier.height(12.dp))
            }

            // Dimensions group
            val dims = specs["dimensions"] as? Map<*, *>
            if (!dims.isNullOrEmpty()) {
                SpecsGroup(
                    title = "Dimensions",
                    icon  = Icons.Default.Straighten,
                    items = buildList {
                        dims["longueur"]?.toString()?.let     { add("Longueur" to "$it m") }
                        dims["largeur"]?.toString()?.let      { add("Largeur" to "$it m") }
                        dims["tirant_eau"]?.toString()?.let   { add("Tirant d'eau" to "$it m") }
                        dims["deplacement"]?.toString()?.let  { add("Déplacement" to "$it kg") }
                    }
                )
                Spacer(Modifier.height(12.dp))
            }

            // Equipements group
            val equip = specs["equipements"] as? List<*>
            if (!equip.isNullOrEmpty()) {
                SpecsGroup(
                    title = "Équipements",
                    icon  = Icons.Default.Build,
                    items = equip.mapNotNull { it?.toString()?.let { e -> e to "" } }
                )
            }
        }
    }
}

@Composable
private fun SpecsGroup(
    title: String,
    icon: androidx.compose.ui.graphics.vector.ImageVector,
    items: List<Pair<String, String>>
) {
    if (items.isEmpty()) return

    var expanded by remember { mutableStateOf(true) }

    Surface(
        shape  = RoundedCornerShape(12.dp),
        color  = MaterialTheme.colorScheme.surfaceVariant.copy(alpha = 0.5f),
        border = androidx.compose.foundation.BorderStroke(1.dp, MaterialTheme.colorScheme.outline)
    ) {
        Column(modifier = Modifier.fillMaxWidth()) {
            // Header
            Row(
                modifier              = Modifier
                    .fillMaxWidth()
                    .clickable { expanded = !expanded }
                    .padding(horizontal = 14.dp, vertical = 12.dp),
                verticalAlignment     = Alignment.CenterVertically,
                horizontalArrangement = Arrangement.spacedBy(8.dp)
            ) {
                Icon(
                    imageVector        = icon,
                    contentDescription = null,
                    tint               = OceanBlue700,
                    modifier           = Modifier.size(18.dp)
                )
                Text(
                    text       = title,
                    style      = MaterialTheme.typography.titleSmall,
                    fontWeight = FontWeight.SemiBold,
                    color      = MaterialTheme.colorScheme.onSurface,
                    modifier   = Modifier.weight(1f)
                )
                val rotation by animateFloatAsState(
                    targetValue   = if (expanded) 180f else 0f,
                    animationSpec = tween(200),
                    label         = "chevron"
                )
                Icon(
                    imageVector        = Icons.Default.KeyboardArrowDown,
                    contentDescription = null,
                    tint               = MaterialTheme.colorScheme.onSurfaceVariant,
                    modifier           = Modifier
                        .size(20.dp)
                        .graphicsLayer(rotationZ = rotation)
                )
            }

            AnimatedVisibility(visible = expanded) {
                Column(modifier = Modifier.fillMaxWidth()) {
                    HorizontalDivider(color = MaterialTheme.colorScheme.outline)
                    items.forEachIndexed { index, (key, value) ->
                        SpecRow(
                            key   = key,
                            value = value,
                            tinted = index % 2 == 0
                        )
                    }
                }
            }
        }
    }
}

@Composable
private fun SpecRow(key: String, value: String, tinted: Boolean) {
    Row(
        modifier              = Modifier
            .fillMaxWidth()
            .background(if (tinted) MaterialTheme.colorScheme.surface else Color.Transparent)
            .padding(horizontal = 14.dp, vertical = 10.dp),
        horizontalArrangement = Arrangement.SpaceBetween,
        verticalAlignment     = Alignment.CenterVertically
    ) {
        Text(
            text  = key,
            style = MaterialTheme.typography.bodySmall,
            color = MaterialTheme.colorScheme.onSurfaceVariant,
            modifier = Modifier.weight(1f)
        )
        if (value.isNotEmpty()) {
            Text(
                text       = value,
                style      = MaterialTheme.typography.bodySmall,
                fontWeight = FontWeight.Medium,
                color      = MaterialTheme.colorScheme.onSurface,
                modifier   = Modifier.padding(start = 8.dp)
            )
        } else {
            // Equipment list item — show as bullet
            Icon(
                imageVector        = Icons.Default.Check,
                contentDescription = null,
                tint               = Success500,
                modifier           = Modifier.size(16.dp)
            )
        }
    }
}

// ── Stats Row ─────────────────────────────────────────────────────────────────

@Composable
private fun StatsRow(views: Int, favorites: Int, timeAgo: String) {
    Surface(
        modifier = Modifier.fillMaxWidth(),
        color    = MaterialTheme.colorScheme.surface
    ) {
        Row(
            modifier              = Modifier
                .fillMaxWidth()
                .padding(horizontal = 20.dp, vertical = 14.dp),
            horizontalArrangement = Arrangement.spacedBy(20.dp)
        ) {
            StatItem(icon = Icons.Default.Visibility,  value = "$views", label = "vues")
            StatItem(icon = Icons.Default.Favorite,    value = "$favorites", label = "favoris")
            StatItem(icon = Icons.Default.Schedule,    value = timeAgo, label = "publié le")
        }
    }
}

@Composable
private fun StatItem(
    icon: androidx.compose.ui.graphics.vector.ImageVector,
    value: String,
    label: String
) {
    Row(
        verticalAlignment     = Alignment.CenterVertically,
        horizontalArrangement = Arrangement.spacedBy(5.dp)
    ) {
        Icon(
            imageVector        = icon,
            contentDescription = null,
            tint               = MaterialTheme.colorScheme.onSurfaceVariant,
            modifier           = Modifier.size(15.dp)
        )
        Text(
            text  = "$value $label",
            style = MaterialTheme.typography.bodySmall,
            color = MaterialTheme.colorScheme.onSurfaceVariant
        )
    }
}

// ── Contact Bar ───────────────────────────────────────────────────────────────

@Composable
private fun ContactBar(
    listing: Listing,
    isFavorited: Boolean,
    onToggleFavorite: () -> Unit,
    onMediation: () -> Unit,
    onCall: () -> Unit,
    onEmail: () -> Unit,
    modifier: Modifier = Modifier
) {
    Surface(
        modifier     = modifier.fillMaxWidth(),
        shadowElevation = 16.dp,
        color        = MaterialTheme.colorScheme.surface,
        shape        = RoundedCornerShape(topStart = 20.dp, topEnd = 20.dp)
    ) {
        Column(
            modifier = Modifier
                .fillMaxWidth()
                .padding(horizontal = 20.dp, vertical = 16.dp)
                .navigationBarsPadding()
        ) {
            if (listing.mediationEnabled) {
                // Mediation button
                Button(
                    onClick  = onMediation,
                    modifier = Modifier.fillMaxWidth().height(50.dp),
                    shape    = RoundedCornerShape(14.dp),
                    colors   = ButtonDefaults.buttonColors(containerColor = OceanBlue700)
                ) {
                    Icon(
                        imageVector        = Icons.Default.Shield,
                        contentDescription = null,
                        modifier           = Modifier.size(18.dp)
                    )
                    Spacer(Modifier.width(8.dp))
                    Text(
                        text       = "Contacter via médiation",
                        fontWeight = FontWeight.SemiBold
                    )
                }
            } else {
                Row(
                    modifier              = Modifier.fillMaxWidth(),
                    horizontalArrangement = Arrangement.spacedBy(10.dp)
                ) {
                    Button(
                        onClick  = onCall,
                        modifier = Modifier.weight(1f).height(50.dp),
                        shape    = RoundedCornerShape(14.dp),
                        colors   = ButtonDefaults.buttonColors(containerColor = Success500)
                    ) {
                        Icon(
                            imageVector        = Icons.Default.Phone,
                            contentDescription = null,
                            modifier           = Modifier.size(18.dp)
                        )
                        Spacer(Modifier.width(6.dp))
                        Text(
                            text       = "Appeler",
                            fontWeight = FontWeight.SemiBold
                        )
                    }
                    OutlinedButton(
                        onClick  = onEmail,
                        modifier = Modifier.weight(1f).height(50.dp),
                        shape    = RoundedCornerShape(14.dp),
                        border   = androidx.compose.foundation.BorderStroke(1.5.dp, OceanBlue700),
                        colors   = ButtonDefaults.outlinedButtonColors(contentColor = OceanBlue700)
                    ) {
                        Icon(
                            imageVector        = Icons.Default.Email,
                            contentDescription = null,
                            modifier           = Modifier.size(18.dp)
                        )
                        Spacer(Modifier.width(6.dp))
                        Text(
                            text       = "Email",
                            fontWeight = FontWeight.SemiBold
                        )
                    }
                }
            }

            Spacer(Modifier.height(10.dp))

            OutlinedButton(
                onClick  = onToggleFavorite,
                modifier = Modifier.fillMaxWidth().height(46.dp),
                shape    = RoundedCornerShape(14.dp),
                border   = androidx.compose.foundation.BorderStroke(
                    1.5.dp,
                    if (isFavorited) Color(0xFFFF4D6D) else MaterialTheme.colorScheme.outline
                ),
                colors   = ButtonDefaults.outlinedButtonColors(
                    contentColor = if (isFavorited) Color(0xFFFF4D6D)
                                  else MaterialTheme.colorScheme.onSurface
                )
            ) {
                Icon(
                    imageVector = if (isFavorited) Icons.Filled.Favorite
                                  else Icons.Outlined.FavoriteBorder,
                    contentDescription = null,
                    modifier   = Modifier.size(18.dp)
                )
                Spacer(Modifier.width(8.dp))
                Text(
                    text       = if (isFavorited) "Retiré des favoris" else "Ajouter aux favoris",
                    fontWeight = FontWeight.Medium
                )
            }
        }
    }
}

// ── Error Content ─────────────────────────────────────────────────────────────

@Composable
private fun ErrorContent(
    msg: String,
    onRetry: () -> Unit,
    modifier: Modifier = Modifier
) {
    Column(
        modifier              = modifier.padding(32.dp),
        horizontalAlignment   = Alignment.CenterHorizontally,
        verticalArrangement   = Arrangement.spacedBy(16.dp)
    ) {
        Icon(
            imageVector        = Icons.Default.ErrorOutline,
            contentDescription = null,
            tint               = Error500,
            modifier           = Modifier.size(56.dp)
        )
        Text(
            text  = "Impossible de charger l'annonce",
            style = MaterialTheme.typography.titleMedium,
            color = MaterialTheme.colorScheme.onSurface
        )
        Text(
            text  = msg,
            style = MaterialTheme.typography.bodySmall,
            color = MaterialTheme.colorScheme.onSurfaceVariant
        )
        Button(
            onClick = onRetry,
            colors  = ButtonDefaults.buttonColors(containerColor = OceanBlue700)
        ) {
            Text("Réessayer")
        }
    }
}

// ── Helpers ───────────────────────────────────────────────────────────────────

private fun conditionLabel(condition: String) = when (condition) {
    "new"            -> "Neuf"
    "like_new"       -> "Comme neuf"
    "good"           -> "Bon état"
    "average"        -> "État moyen"
    "needs_revision" -> "À réviser"
    else             -> condition
}

private fun engineTypeLabel(type: String) = when (type) {
    "inboard"  -> "In-bord"
    "outboard" -> "Hors-bord"
    "jet"      -> "Jet"
    else       -> type
}

private fun offerTypeLabel(type: String) = when (type) {
    "negotiable" -> "Prix négociable"
    "fixed"      -> "Prix ferme"
    "free"       -> "Gratuit"
    else         -> type
}
