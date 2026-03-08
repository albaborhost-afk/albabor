package com.albabor.app.ui.screens.listing

import android.content.Intent
import android.net.Uri
import androidx.compose.animation.animateContentSize
import androidx.compose.animation.core.Spring
import androidx.compose.animation.core.animateDpAsState
import androidx.compose.animation.core.animateFloatAsState
import androidx.compose.animation.core.spring
import androidx.compose.animation.core.tween
import androidx.compose.foundation.BorderStroke
import androidx.compose.foundation.background
import androidx.compose.foundation.border
import androidx.compose.foundation.clickable
import androidx.compose.foundation.horizontalScroll
import androidx.compose.foundation.interaction.MutableInteractionSource
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.LazyRow
import androidx.compose.foundation.lazy.itemsIndexed
import androidx.compose.foundation.pager.HorizontalPager
import androidx.compose.foundation.pager.rememberPagerState
import androidx.compose.foundation.rememberScrollState
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
import androidx.compose.ui.draw.drawBehind
import androidx.compose.ui.draw.shadow
import androidx.compose.ui.geometry.CornerRadius
import androidx.compose.ui.geometry.Offset
import androidx.compose.ui.geometry.Size
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.graphicsLayer
import androidx.compose.ui.graphics.vector.ImageVector
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextAlign
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
import kotlinx.coroutines.launch

// ── Colours & constants ──────────────────────────────────────────────────────

private val CardShape = RoundedCornerShape(22.dp)
private val WhatsAppGreen = Color(0xFF25D366)
private val WhatsAppDark  = Color(0xFF128C7E)
private val SafetyGreen   = Color(0xFF27AE60)
private val HeartPink     = Color(0xFFFF4D6D)

private val HeroGradient       = Brush.linearGradient(listOf(OceanBlue900, Teal500))
private val HeroGradientSubtle = Brush.linearGradient(
    listOf(OceanBlue900.copy(alpha = 0.04f), Teal500.copy(alpha = 0.06f))
)

// ── Entry Point ──────────────────────────────────────────────────────────────

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
    val host = remember { SnackbarHostState() }

    LaunchedEffect(snackbar) { snackbar?.let { host.showSnackbar(it); vm.clearSnackbar() } }

    Scaffold(
        snackbarHost   = { SnackbarHost(host) },
        containerColor = AppBackground
    ) { padding ->
        Box(Modifier.fillMaxSize().padding(padding)) {
            when (val s = state) {
                is ListingDetailViewModel.State.Loading ->
                    CircularProgressIndicator(Modifier.align(Alignment.Center), color = OceanBlue700)

                is ListingDetailViewModel.State.Error ->
                    ErrorContent(s.msg, vm::loadListing, Modifier.align(Alignment.Center))

                is ListingDetailViewModel.State.Success ->
                    DetailBody(s.listing, isFavorited, onBack, vm::toggleFavorite, onMediationRequested)
            }
        }
    }
}

// ── Main scrollable body ─────────────────────────────────────────────────────

@Composable
private fun DetailBody(
    listing: Listing,
    isFavorited: Boolean,
    onBack: () -> Unit,
    onToggleFavorite: () -> Unit,
    onMediationRequested: (Int) -> Unit
) {
    val ctx = LocalContext.current

    LazyColumn(
        modifier            = Modifier.fillMaxSize(),
        contentPadding      = PaddingValues(bottom = 150.dp),
        verticalArrangement = Arrangement.spacedBy(14.dp)
    ) {
        item { ImageGallery(listing, isFavorited, onBack, onToggleFavorite) }
        item { PriceHeroCard(listing) }
        listing.user?.let { item { SellerCard(it) } }
        listing.description?.takeIf { it.isNotBlank() }?.let { item { DescriptionCard(it) } }
        item { GeneralSection(listing) }
        item { DimensionsSection(listing) }
        item { MotorisationSection(listing) }
        item { ReservoirsSection(listing) }
        item { AmenagementsSection(listing) }
        item { EquipementsSection(listing) }
        item { ExtrasSection(listing) }
        item { SafetyTips() }
    }

    // Sticky bottom bar
    Box(Modifier.fillMaxSize()) {
        BottomContactBar(
            listing          = listing,
            isFavorited      = isFavorited,
            onToggleFavorite = onToggleFavorite,
            onMediation      = { onMediationRequested(listing.id) },
            onCall = {
                (listing.numeroMobile ?: listing.user?.phone)?.let {
                    ctx.startActivity(Intent(Intent.ACTION_DIAL, Uri.parse("tel:$it")))
                }
            },
            onWhatsApp = {
                listing.numeroWhatsapp?.let {
                    ctx.startActivity(Intent(Intent.ACTION_VIEW,
                        Uri.parse("https://wa.me/${it.replace(Regex("[^0-9]"), "")}")))
                }
            },
            modifier = Modifier.align(Alignment.BottomCenter)
        )
    }
}

// ═══════════════════════════════════════════════════════════════════════════════
//  IMAGE GALLERY  with thumbnail strip
// ═══════════════════════════════════════════════════════════════════════════════

@Composable
private fun ImageGallery(
    listing: Listing,
    isFavorited: Boolean,
    onBack: () -> Unit,
    onFavorite: () -> Unit
) {
    val images    = listing.galleryImages.ifEmpty { listOf("") }
    val pager     = rememberPagerState(pageCount = { images.size })
    val scope     = rememberCoroutineScope()

    Column {
        // ── Main image ──────────────────────────────────────────────────
        Box(
            Modifier
                .fillMaxWidth()
                .height(340.dp)
                .clip(RoundedCornerShape(bottomStart = 28.dp, bottomEnd = 28.dp))
        ) {
            HorizontalPager(state = pager, modifier = Modifier.fillMaxSize()) { page ->
                if (images[page].isNotBlank()) {
                    AsyncImage(
                        model = ImageRequest.Builder(LocalContext.current)
                            .data(images[page]).crossfade(300).build(),
                        contentDescription = "Photo ${page + 1}",
                        contentScale       = ContentScale.Crop,
                        modifier           = Modifier.fillMaxSize()
                    )
                } else {
                    Box(
                        Modifier.fillMaxSize()
                            .background(Brush.linearGradient(listOf(OceanBlue100, OceanBlue50))),
                        contentAlignment = Alignment.Center
                    ) {
                        Icon(Icons.Default.DirectionsBoat, null, tint = OceanBlue300, modifier = Modifier.size(90.dp))
                    }
                }
            }

            // Gradient overlays
            Box(Modifier.fillMaxWidth().height(120.dp).align(Alignment.BottomCenter)
                .background(Brush.verticalGradient(listOf(Color.Transparent, Color.Black.copy(0.55f)))))
            Box(Modifier.fillMaxWidth().height(100.dp).align(Alignment.TopCenter)
                .background(Brush.verticalGradient(listOf(Color.Black.copy(0.45f), Color.Transparent))))

            // Featured badge
            if (listing.isFeatured) {
                Surface(
                    modifier = Modifier.align(Alignment.TopStart).padding(start = 58.dp, top = 14.dp),
                    shape    = RoundedCornerShape(20.dp),
                    color    = Color.Transparent
                ) {
                    Box(
                        Modifier
                            .background(Brush.linearGradient(listOf(Color(0xFFFFA500), Color(0xFFFF6B00))),
                                RoundedCornerShape(20.dp))
                            .padding(horizontal = 14.dp, vertical = 6.dp)
                    ) {
                        Row(verticalAlignment = Alignment.CenterVertically, horizontalArrangement = Arrangement.spacedBy(5.dp)) {
                            Icon(Icons.Default.Star, null, tint = Color.White, modifier = Modifier.size(14.dp))
                            Text("En Vedette", color = Color.White, fontSize = 11.sp, fontWeight = FontWeight.Bold)
                        }
                    }
                }
            }

            // Back button
            GlassCircleButton(Modifier.align(Alignment.TopStart).padding(12.dp), onBack) {
                Icon(Icons.AutoMirrored.Filled.ArrowBack, "Retour", tint = Color.White, modifier = Modifier.size(20.dp))
            }

            // Favorite button
            val favScale by animateFloatAsState(if (isFavorited) 1.25f else 1f, tween(200), label = "fav")
            GlassCircleButton(Modifier.align(Alignment.TopEnd).padding(12.dp), onFavorite) {
                Icon(
                    if (isFavorited) Icons.Filled.Favorite else Icons.Outlined.FavoriteBorder,
                    "Favoris",
                    tint     = if (isFavorited) HeartPink else Color.White,
                    modifier = Modifier.size(20.dp).graphicsLayer(scaleX = favScale, scaleY = favScale)
                )
            }

            // Page indicator
            if (images.size > 1) {
                Row(
                    Modifier.align(Alignment.BottomCenter).padding(bottom = 16.dp),
                    horizontalArrangement = Arrangement.spacedBy(5.dp),
                    verticalAlignment     = Alignment.CenterVertically
                ) {
                    repeat(images.size) { i ->
                        val sel    = i == pager.currentPage
                        val width by animateDpAsState(if (sel) 20.dp else 6.dp, spring(stiffness = Spring.StiffnessMediumLow), label = "dot")
                        Box(
                            Modifier
                                .height(6.dp)
                                .width(width)
                                .clip(CircleShape)
                                .background(if (sel) Color.White else Color.White.copy(0.4f))
                        )
                    }
                }

                // Count badge
                Surface(
                    Modifier.align(Alignment.BottomEnd).padding(14.dp),
                    shape = RoundedCornerShape(14.dp),
                    color = Color.Black.copy(0.5f)
                ) {
                    Row(Modifier.padding(horizontal = 10.dp, vertical = 5.dp),
                        verticalAlignment = Alignment.CenterVertically,
                        horizontalArrangement = Arrangement.spacedBy(4.dp)) {
                        Icon(Icons.Default.PhotoLibrary, null, tint = Color.White, modifier = Modifier.size(12.dp))
                        Text("${pager.currentPage + 1}/${images.size}", color = Color.White, fontSize = 11.sp, fontWeight = FontWeight.SemiBold)
                    }
                }
            }
        }

        // ── Thumbnail strip ─────────────────────────────────────────────
        if (images.size > 1) {
            LazyRow(
                modifier              = Modifier.fillMaxWidth().padding(top = 10.dp),
                contentPadding        = PaddingValues(horizontal = 16.dp),
                horizontalArrangement = Arrangement.spacedBy(8.dp)
            ) {
                itemsIndexed(images) { i, url ->
                    val selected = i == pager.currentPage
                    val elevation by animateDpAsState(if (selected) 6.dp else 0.dp, label = "thumbElev")
                    val scale by animateFloatAsState(if (selected) 1.05f else 1f, label = "thumbScale")

                    Box(
                        Modifier
                            .size(64.dp)
                            .graphicsLayer(scaleX = scale, scaleY = scale)
                            .shadow(elevation, RoundedCornerShape(14.dp))
                            .clip(RoundedCornerShape(14.dp))
                            .then(
                                if (selected) Modifier.border(2.dp, Teal500, RoundedCornerShape(14.dp))
                                else Modifier.border(1.dp, Gray200, RoundedCornerShape(14.dp))
                            )
                            .clickable { scope.launch { pager.animateScrollToPage(i) } }
                    ) {
                        if (url.isNotBlank()) {
                            AsyncImage(
                                model = ImageRequest.Builder(LocalContext.current).data(url).crossfade(true).build(),
                                contentDescription = null,
                                contentScale = ContentScale.Crop,
                                modifier = Modifier.fillMaxSize()
                                    .graphicsLayer(alpha = if (selected) 1f else 0.55f)
                            )
                        } else {
                            Box(Modifier.fillMaxSize().background(OceanBlue50), contentAlignment = Alignment.Center) {
                                Icon(Icons.Default.DirectionsBoat, null, tint = OceanBlue300, modifier = Modifier.size(28.dp))
                            }
                        }
                    }
                }
            }
        }
    }
}

@Composable
private fun GlassCircleButton(modifier: Modifier, onClick: () -> Unit, content: @Composable () -> Unit) {
    IconButton(
        onClick  = onClick,
        modifier = modifier
            .size(44.dp)
            .shadow(8.dp, CircleShape)
            .clip(CircleShape)
            .background(Color.Black.copy(0.3f))
    ) { content() }
}

// ═══════════════════════════════════════════════════════════════════════════════
//  PRICE HERO CARD
// ═══════════════════════════════════════════════════════════════════════════════

@Composable
private fun PriceHeroCard(listing: Listing) {
    Surface(
        Modifier.fillMaxWidth().padding(horizontal = 14.dp),
        shape = CardShape,
        color = Color.White,
        shadowElevation = 6.dp
    ) {
        Column {
            // ── Title + Price area with decorative gradient stripe ───────
            Box(Modifier.fillMaxWidth()) {
                // Decorative left accent bar
                Box(
                    Modifier
                        .width(4.dp)
                        .fillMaxHeight()
                        .background(HeroGradient, RoundedCornerShape(topStart = 22.dp))
                )

                Column(
                    Modifier
                        .fillMaxWidth()
                        .background(HeroGradientSubtle)
                        .padding(start = 20.dp, end = 20.dp, top = 20.dp, bottom = 18.dp)
                ) {
                    // Title + status
                    Row(verticalAlignment = Alignment.Top) {
                        Text(
                            listing.title,
                            style      = MaterialTheme.typography.titleLarge,
                            fontWeight = FontWeight.Bold,
                            color      = Gray900,
                            modifier   = Modifier.weight(1f),
                            lineHeight = 26.sp
                        )
                        Spacer(Modifier.width(10.dp))
                        StatusPill(listing.status)
                    }

                    Spacer(Modifier.height(16.dp))

                    // Price row
                    Row(verticalAlignment = Alignment.Bottom, horizontalArrangement = Arrangement.spacedBy(10.dp)) {
                        Text(
                            listing.formattedPrice,
                            fontSize   = 32.sp,
                            fontWeight = FontWeight.ExtraBold,
                            color      = OceanBlue900,
                            letterSpacing = (-1).sp
                        )

                        listing.formattedConvertedPrice?.let {
                            Surface(shape = RoundedCornerShape(8.dp), color = OceanBlue50) {
                                Text(it, Modifier.padding(horizontal = 10.dp, vertical = 4.dp),
                                    style = MaterialTheme.typography.labelMedium, color = OceanBlue700, fontWeight = FontWeight.Medium)
                            }
                        }
                    }

                    // Offer type badge
                    listing.offerType?.let { offer ->
                        Spacer(Modifier.height(10.dp))
                        val v = offerTypeVisual(offer)
                        Surface(
                            shape  = RoundedCornerShape(8.dp),
                            color  = v.bg,
                            border = BorderStroke(1.dp, v.fg.copy(0.2f))
                        ) {
                            Row(
                                Modifier.padding(horizontal = 10.dp, vertical = 5.dp),
                                verticalAlignment = Alignment.CenterVertically,
                                horizontalArrangement = Arrangement.spacedBy(5.dp)
                            ) {
                                Icon(v.icon, null, tint = v.fg, modifier = Modifier.size(13.dp))
                                Text(v.label, fontSize = 10.sp, fontWeight = FontWeight.Bold, color = v.fg, letterSpacing = 0.6.sp)
                            }
                        }
                    }
                }
            }

            // ── Badges row ──────────────────────────────────────────────
            HorizontalDivider(color = Gray100)
            Row(
                Modifier.fillMaxWidth().horizontalScroll(rememberScrollState())
                    .padding(horizontal = 16.dp, vertical = 12.dp),
                horizontalArrangement = Arrangement.spacedBy(8.dp)
            ) {
                val accent = categoryAccentColor(listing.category)
                Pill("${listing.categoryIcon} ${listing.categoryLabel}", accent)
                listing.condition?.let { Pill(listing.conditionLabel, conditionColor(it)) }
                if (listing.remarqueEchange == "accepte") Pill("Échange accepté", SafetyGreen, Icons.Default.SwapHoriz)
                listing.wilaya?.let { Pill(it, Coral500, Icons.Default.LocationOn) }
            }

            // ── Stats row ───────────────────────────────────────────────
            HorizontalDivider(color = Gray100)
            Row(
                Modifier.fillMaxWidth().background(Gray50).padding(horizontal = 20.dp, vertical = 11.dp),
                horizontalArrangement = Arrangement.spacedBy(8.dp),
                verticalAlignment     = Alignment.CenterVertically
            ) {
                MiniStat(Icons.Default.Visibility, "${listing.viewsCount}", "vues")
                Dot()
                MiniStat(Icons.Default.Favorite, "${listing.favoritesCount}", "favoris", Coral500)
                Dot()
                MiniStat(Icons.Default.Schedule, listing.timeAgo, "publié")
            }
        }
    }
}

@Composable
private fun StatusPill(status: String) {
    val (bg, fg, lbl) = when (status) {
        "active"           -> Triple(SafetyGreen.copy(0.12f), SafetyGreen, "Active")
        "sold"             -> Triple(Error500.copy(0.12f), Error500, "Vendu")
        "paused"           -> Triple(Warning500.copy(0.12f), Warning500, "En pause")
        "pending_review"   -> Triple(OceanBlue100, OceanBlue700, "En révision")
        "awaiting_payment" -> Triple(Gold100, Gold500, "Paiement requis")
        "expired"          -> Triple(Gray100, Gray500, "Expiré")
        "rejected"         -> Triple(Error100, Error500, "Rejeté")
        "draft"            -> Triple(Gray100, Gray500, "Brouillon")
        else               -> Triple(Gray100, Gray500, status)
    }
    Surface(shape = RoundedCornerShape(20.dp), color = bg) {
        Text(lbl, Modifier.padding(horizontal = 10.dp, vertical = 4.dp),
            color = fg, fontSize = 11.sp, fontWeight = FontWeight.SemiBold)
    }
}

@Composable
private fun Pill(text: String, color: Color, icon: ImageVector? = null) {
    Surface(
        shape  = RoundedCornerShape(20.dp),
        color  = color.copy(0.08f),
        border = BorderStroke(1.dp, color.copy(0.15f))
    ) {
        Row(
            Modifier.padding(horizontal = 12.dp, vertical = 6.dp),
            verticalAlignment = Alignment.CenterVertically,
            horizontalArrangement = Arrangement.spacedBy(4.dp)
        ) {
            icon?.let { Icon(it, null, tint = color, modifier = Modifier.size(13.dp)) }
            Text(text, fontSize = 11.sp, fontWeight = FontWeight.SemiBold, color = color)
        }
    }
}

@Composable private fun MiniStat(ic: ImageVector, v: String, l: String, tint: Color = Gray400) {
    Row(verticalAlignment = Alignment.CenterVertically, horizontalArrangement = Arrangement.spacedBy(4.dp)) {
        Icon(ic, null, tint = tint, modifier = Modifier.size(14.dp))
        Text("$v $l", fontSize = 11.sp, color = Gray500, fontWeight = FontWeight.Medium)
    }
}

@Composable private fun Dot() {
    Box(Modifier.size(3.dp).clip(CircleShape).background(Gray300))
}

// ═══════════════════════════════════════════════════════════════════════════════
//  SELLER CARD
// ═══════════════════════════════════════════════════════════════════════════════

@Composable
private fun SellerCard(seller: com.albabor.app.data.model.ListingUser) {
    Surface(
        Modifier.fillMaxWidth().padding(horizontal = 14.dp),
        shape = CardShape, color = Color.White, shadowElevation = 6.dp
    ) {
        Row(
            Modifier.fillMaxWidth().background(HeroGradientSubtle).padding(20.dp),
            verticalAlignment = Alignment.CenterVertically,
            horizontalArrangement = Arrangement.spacedBy(16.dp)
        ) {
            // Avatar with decorative ring
            Box(contentAlignment = Alignment.BottomEnd) {
                val avatarMod = Modifier
                    .size(60.dp)
                    .clip(RoundedCornerShape(18.dp))

                if (seller.avatar != null) {
                    AsyncImage(seller.avatar, seller.name, contentScale = ContentScale.Crop,
                        modifier = avatarMod.background(OceanBlue100)
                            .border(2.dp, OceanBlue100, RoundedCornerShape(18.dp)))
                } else {
                    Box(
                        avatarMod.background(HeroGradient, RoundedCornerShape(18.dp))
                            .border(2.dp, Color.White.copy(0.3f), RoundedCornerShape(18.dp)),
                        contentAlignment = Alignment.Center
                    ) {
                        Text(seller.name.take(1).uppercase(), color = Color.White, fontSize = 26.sp, fontWeight = FontWeight.Bold)
                    }
                }

                if (seller.isVerified) {
                    Box(
                        Modifier.offset(x = 3.dp, y = 3.dp).size(22.dp)
                            .shadow(4.dp, CircleShape).clip(CircleShape).background(SafetyGreen),
                        contentAlignment = Alignment.Center
                    ) {
                        Icon(Icons.Default.Check, null, tint = Color.White, modifier = Modifier.size(13.dp))
                    }
                }
            }

            Column(Modifier.weight(1f)) {
                Text(seller.name, style = MaterialTheme.typography.titleSmall, fontWeight = FontWeight.Bold, color = Gray900)

                Spacer(Modifier.height(2.dp))

                if (seller.isVerified) {
                    Row(verticalAlignment = Alignment.CenterVertically, horizontalArrangement = Arrangement.spacedBy(4.dp)) {
                        Icon(Icons.Default.Verified, null, tint = SafetyGreen, modifier = Modifier.size(14.dp))
                        Text("Vendeur vérifié", fontSize = 12.sp, color = SafetyGreen, fontWeight = FontWeight.SemiBold)
                    }
                } else {
                    Text("Vendeur particulier", fontSize = 12.sp, color = Gray500)
                }
            }

            // Arrow in a subtle circle
            Box(
                Modifier.size(36.dp).clip(CircleShape).background(Gray100),
                contentAlignment = Alignment.Center
            ) {
                Icon(Icons.Default.ChevronRight, null, tint = Gray500, modifier = Modifier.size(20.dp))
            }
        }
    }
}

// ═══════════════════════════════════════════════════════════════════════════════
//  DESCRIPTION
// ═══════════════════════════════════════════════════════════════════════════════

@Composable
private fun DescriptionCard(desc: String) {
    Section("Description", Icons.Default.Description) {
        var expanded by remember { mutableStateOf(false) }
        val clip = desc.length > 300

        Column(Modifier.animateContentSize(spring(stiffness = Spring.StiffnessLow))) {
            Text(desc, style = MaterialTheme.typography.bodyMedium, color = Gray500, lineHeight = 23.sp,
                maxLines = if (expanded || !clip) Int.MAX_VALUE else 5, overflow = TextOverflow.Ellipsis)
            if (clip) {
                Spacer(Modifier.height(10.dp))
                Surface(
                    Modifier.clickable(remember { MutableInteractionSource() }, null) { expanded = !expanded },
                    shape = RoundedCornerShape(10.dp), color = Teal500.copy(0.08f)
                ) {
                    Text(
                        if (expanded) "Voir moins" else "Voir plus",
                        Modifier.padding(horizontal = 14.dp, vertical = 6.dp),
                        color = Teal500, fontSize = 12.sp, fontWeight = FontWeight.SemiBold
                    )
                }
            }
        }
    }
}

// ═══════════════════════════════════════════════════════════════════════════════
//  SPEC SECTIONS  — each with coloured left accent
// ═══════════════════════════════════════════════════════════════════════════════

@Composable
private fun GeneralSection(listing: Listing) {
    val items = specItems(listing, "general", listOf(
        "fabricant" to "Fabricant", "modele" to "Modèle", "annee_construction" to "Année",
        "marque" to "Marque", "couleur" to "Couleur", "immatriculation" to "Immatriculation",
        "part_type" to "Type de pièce", "compatible_with" to "Compatible avec",
        "compatible_avec" to "Compatible avec", "part_number" to "Référence",
    ))
    if (items.isEmpty()) return
    Section("Informations générales", Icons.Default.Info, accent = OceanBlue700) { SpecGrid(items, OceanBlue700) }
}

@Composable
private fun DimensionsSection(listing: Listing) {
    val u = mapOf("longueur" to "m", "largeur" to "m", "tonnage" to "T",
        "tirant_eau" to "m", "tirant_air" to "m", "deplacement" to "kg")
    val items = specItems(listing, "dimensions", listOf(
        "longueur" to "Longueur", "largeur" to "Largeur", "tonnage" to "Tonnage",
        "tirant_eau" to "Tirant d'eau", "tirant_air" to "Tirant d'air", "deplacement" to "Déplacement",
    ), u)
    if (items.isEmpty()) return
    Section("Dimensions", Icons.Default.Straighten, accent = Teal500) { SpecGrid(items, Teal500) }
}

@Composable
private fun MotorisationSection(listing: Listing) {
    val u = mapOf("puissance_par_moteur" to "CV", "puissance_totale" to "CV",
        "nombre_heures" to "h", "cylindree" to "cc")
    val items = specItems(listing, "motorisation", listOf(
        "marque_moteur" to "Marque", "propulsion" to "Propulsion", "type_moteur" to "Type moteur",
        "type_carburant" to "Carburant", "nombre_moteurs" to "Nb. moteurs", "nb_moteurs" to "Nb. moteurs",
        "puissance_par_moteur" to "Puissance/moteur", "puissance_totale" to "Puissance totale",
        "nombre_heures" to "Heures", "cylindree" to "Cylindrée",
    ), u)
    if (items.isEmpty()) return
    Section("Motorisation", Icons.Default.Settings, accent = Gold500) { SpecGrid(items, Gold500) }
}

@Composable
private fun ReservoirsSection(listing: Listing) {
    val u = mapOf("reservoir_carburant" to "L", "reservoir_eau_douce" to "L", "stockage" to "L")
    val items = specItems(listing, "reservoirs", listOf(
        "reservoir_carburant" to "Carburant", "reservoir_eau_douce" to "Eau douce", "stockage" to "Stockage"
    ), u)
    if (items.isEmpty()) return
    Section("Réservoirs", Icons.Default.LocalGasStation, accent = OceanBlue500) { SpecGrid(items, OceanBlue500) }
}

@Composable
private fun AmenagementsSection(listing: Listing) {
    val items = specItems(listing, "amenagements", listOf(
        "nombre_couchettes" to "Couchettes", "nombre_cabines" to "Cabines",
        "nombre_sanitaire" to "Sanitaires", "nombre_cuisine" to "Cuisines",
    ))
    if (items.isEmpty()) return

    val icons = mapOf(
        "Couchettes" to Icons.Default.Hotel, "Cabines" to Icons.Default.MeetingRoom,
        "Sanitaires" to Icons.Default.Shower, "Cuisines" to Icons.Default.Restaurant
    )

    Section("Aménagements", Icons.Default.Home, accent = Lavender500) {
        Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.spacedBy(10.dp)) {
            items.forEach { (label, value) ->
                Surface(Modifier.weight(1f), RoundedCornerShape(16.dp), color = Gray50,
                    border = BorderStroke(1.dp, Gray200)) {
                    Column(Modifier.padding(vertical = 16.dp), horizontalAlignment = Alignment.CenterHorizontally) {
                        Box(
                            Modifier.size(40.dp).clip(RoundedCornerShape(12.dp))
                                .background(Lavender500.copy(0.08f)),
                            contentAlignment = Alignment.Center
                        ) {
                            Icon(icons[label] ?: Icons.Default.Home, null, tint = Lavender500, modifier = Modifier.size(22.dp))
                        }
                        Spacer(Modifier.height(8.dp))
                        Text(value, fontSize = 26.sp, fontWeight = FontWeight.ExtraBold, color = OceanBlue900)
                        Text(label.uppercase(), fontSize = 8.sp, fontWeight = FontWeight.Bold, color = Gray400, letterSpacing = 1.sp)
                    }
                }
            }
        }
    }
}

@Composable
private fun EquipementsSection(listing: Listing) {
    val tags = listing.specs?.get("tags") as? Map<*, *> ?: return

    data class TagSec(val key: String, val label: String, val color: Color, val icon: ImageVector)
    val secs = listOf(
        TagSec("equipement", "Équipement de sécurité", OceanBlue900, Icons.Default.Shield),
        TagSec("options", "Options de confort", Teal500, Icons.Default.AutoAwesome),
        TagSec("electronique", "Électronique", OceanBlue700, Icons.Default.Devices),
    )

    val any = secs.any { s -> (tags[s.key] as? List<*>)?.isNotEmpty() == true }
    if (!any) return

    Section("Équipements & Options", Icons.Default.CheckCircle) {
        var first = true
        secs.forEach { s ->
            val items = (tags[s.key] as? List<*>)?.mapNotNull { it?.toString() } ?: return@forEach
            if (items.isEmpty()) return@forEach

            if (!first) HorizontalDivider(Modifier.padding(vertical = 14.dp), color = Gray100)
            first = false

            // Sub-header
            Row(Modifier.padding(bottom = 10.dp), verticalAlignment = Alignment.CenterVertically,
                horizontalArrangement = Arrangement.spacedBy(6.dp)) {
                Box(
                    Modifier.size(24.dp).clip(RoundedCornerShape(7.dp)).background(s.color.copy(0.1f)),
                    contentAlignment = Alignment.Center
                ) { Icon(s.icon, null, tint = s.color, modifier = Modifier.size(14.dp)) }
                Text(s.label.uppercase(), fontSize = 10.sp, fontWeight = FontWeight.Bold, color = s.color, letterSpacing = 0.6.sp)
            }

            // Tags as wrapping chips (column of rows, max 2 per row for readability)
            val chunked = items.chunked(2)
            Column(verticalArrangement = Arrangement.spacedBy(8.dp)) {
                chunked.forEach { row ->
                    Row(horizontalArrangement = Arrangement.spacedBy(8.dp)) {
                        row.forEach { tag ->
                            Surface(
                                shape = RoundedCornerShape(12.dp),
                                color = s.color.copy(0.05f),
                                border = BorderStroke(1.dp, s.color.copy(0.12f))
                            ) {
                                Row(
                                    Modifier.padding(horizontal = 12.dp, vertical = 9.dp),
                                    verticalAlignment = Alignment.CenterVertically,
                                    horizontalArrangement = Arrangement.spacedBy(6.dp)
                                ) {
                                    Box(
                                        Modifier.size(16.dp).clip(CircleShape).background(s.color.copy(0.12f)),
                                        contentAlignment = Alignment.Center
                                    ) { Icon(Icons.Default.Check, null, tint = s.color, modifier = Modifier.size(10.dp)) }
                                    Text(tag, fontSize = 12.sp, fontWeight = FontWeight.SemiBold, color = s.color)
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
private fun ExtrasSection(listing: Listing) {
    val extras = listing.specs?.get("extras") as? Map<*, *> ?: return
    val items = buildList<Pair<String, String>> {
        extras["annexe"]?.toString()?.let { add("Annexe" to if (it == "oui") "Oui, incluse" else "Non") }
        extras["remorque"]?.toString()?.let { r ->
            val m = extras["marque_remorque"]?.toString()
            add("Remorque" to if (r == "oui") "Oui${m?.let { " — $it" } ?: ""}" else "Non")
        }
        if (extras["place_au_port"]?.toString() == "oui") {
            val a = extras["adresse_port"]?.toString()
            var t = "Oui${a?.let { " — $it" } ?: ""}"
            val lon = extras["longueur_place"]?.toString()
            val lar = extras["largeur_place"]?.toString()
            if (lon != null || lar != null) t += "\n${lon ?: "?"}m x ${lar ?: "?"}m"
            add("Place au port" to t)
        }
    }
    if (items.isEmpty()) return
    Section("Extras", Icons.Default.Add, accent = Teal700) { SpecGrid(items, Teal700) }
}

// ═══════════════════════════════════════════════════════════════════════════════
//  SAFETY TIPS
// ═══════════════════════════════════════════════════════════════════════════════

@Composable
private fun SafetyTips() {
    Surface(
        Modifier.fillMaxWidth().padding(horizontal = 14.dp),
        shape = CardShape, color = Color.White, shadowElevation = 6.dp
    ) {
        Column(Modifier.padding(20.dp)) {
            Row(verticalAlignment = Alignment.CenterVertically, horizontalArrangement = Arrangement.spacedBy(10.dp)) {
                Box(
                    Modifier.size(36.dp).clip(RoundedCornerShape(11.dp))
                        .background(Brush.linearGradient(listOf(Warning500.copy(0.15f), Warning500.copy(0.05f)))),
                    contentAlignment = Alignment.Center
                ) { Icon(Icons.Default.Warning, null, tint = Warning500, modifier = Modifier.size(19.dp)) }
                Text("Conseils de sécurité", style = MaterialTheme.typography.titleSmall, fontWeight = FontWeight.Bold, color = Gray900)
            }

            Spacer(Modifier.height(16.dp))

            listOf(
                "Vérifiez le produit avant le paiement",
                "Rencontrez dans un lieu public",
                "Ne payez jamais à l'avance"
            ).forEachIndexed { i, tip ->
                Row(
                    Modifier.padding(vertical = 6.dp),
                    verticalAlignment = Alignment.CenterVertically,
                    horizontalArrangement = Arrangement.spacedBy(12.dp)
                ) {
                    Box(
                        Modifier.size(26.dp).clip(CircleShape)
                            .background(SafetyGreen.copy(0.1f)),
                        contentAlignment = Alignment.Center
                    ) {
                        Text("${i + 1}", fontSize = 11.sp, fontWeight = FontWeight.Bold, color = SafetyGreen)
                    }
                    Text(tip, style = MaterialTheme.typography.bodySmall, color = Gray500, lineHeight = 18.sp)
                }
            }
        }
    }
}

// ═══════════════════════════════════════════════════════════════════════════════
//  SECTION CARD  wrapper with optional accent colour
// ═══════════════════════════════════════════════════════════════════════════════

@Composable
private fun Section(
    title: String,
    icon: ImageVector,
    accent: Color = OceanBlue700,
    content: @Composable ColumnScope.() -> Unit
) {
    Surface(
        Modifier.fillMaxWidth().padding(horizontal = 14.dp),
        shape = CardShape, color = Color.White, shadowElevation = 6.dp
    ) {
        Column(Modifier.padding(20.dp)) {
            Row(
                verticalAlignment = Alignment.CenterVertically,
                horizontalArrangement = Arrangement.spacedBy(12.dp),
                modifier = Modifier.padding(bottom = 16.dp)
            ) {
                Box(
                    Modifier.size(36.dp).clip(RoundedCornerShape(11.dp))
                        .background(Brush.linearGradient(listOf(accent, accent.copy(0.7f)))),
                    contentAlignment = Alignment.Center
                ) { Icon(icon, null, tint = Color.White, modifier = Modifier.size(18.dp)) }
                Text(title, style = MaterialTheme.typography.titleSmall, fontWeight = FontWeight.Bold, color = Gray900)
            }
            content()
        }
    }
}

// ═══════════════════════════════════════════════════════════════════════════════
//  SPEC GRID  — cards with coloured left accent stripe
// ═══════════════════════════════════════════════════════════════════════════════

@Composable
private fun SpecGrid(items: List<Pair<String, String>>, accent: Color) {
    Column(verticalArrangement = Arrangement.spacedBy(8.dp)) {
        items.chunked(2).forEach { row ->
            Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.spacedBy(8.dp)) {
                row.forEach { (label, value) ->
                    Box(Modifier.weight(1f)) {
                        Surface(
                            Modifier.fillMaxWidth(),
                            shape = RoundedCornerShape(14.dp),
                            color = Gray50,
                            border = BorderStroke(1.dp, Gray200)
                        ) {
                            Row {
                                // Accent stripe
                                Box(
                                    Modifier
                                        .width(3.dp)
                                        .fillMaxHeight()
                                        .background(accent, RoundedCornerShape(topStart = 14.dp, bottomStart = 14.dp))
                                )
                                Column(Modifier.padding(start = 10.dp, end = 12.dp, top = 10.dp, bottom = 10.dp)) {
                                    Text(label.uppercase(), fontSize = 9.sp, fontWeight = FontWeight.Bold,
                                        color = Gray400, letterSpacing = 0.8.sp)
                                    Spacer(Modifier.height(3.dp))
                                    Text(value, style = MaterialTheme.typography.bodyMedium,
                                        fontWeight = FontWeight.SemiBold, color = Gray900)
                                }
                            }
                        }
                    }
                }
                if (row.size < 2) Spacer(Modifier.weight(1f))
            }
        }
    }
}

// ═══════════════════════════════════════════════════════════════════════════════
//  BOTTOM CONTACT BAR
// ═══════════════════════════════════════════════════════════════════════════════

@Composable
private fun BottomContactBar(
    listing: Listing,
    isFavorited: Boolean,
    onToggleFavorite: () -> Unit,
    onMediation: () -> Unit,
    onCall: () -> Unit,
    onWhatsApp: () -> Unit,
    modifier: Modifier = Modifier
) {
    Surface(
        modifier.fillMaxWidth(),
        shadowElevation = 24.dp,
        color = Color.White,
        shape = RoundedCornerShape(topStart = 26.dp, topEnd = 26.dp)
    ) {
        Column(
            Modifier.fillMaxWidth()
                .padding(horizontal = 20.dp, vertical = 16.dp)
                .navigationBarsPadding()
        ) {
            if (listing.mediationEnabled) {
                // Gradient mediation button
                GradientButton(
                    onClick  = onMediation,
                    gradient = Brush.linearGradient(listOf(OceanBlue900, Teal500)),
                    icon     = Icons.Default.Shield,
                    text     = "Contacter via médiation"
                )
                Row(
                    Modifier.fillMaxWidth().padding(top = 6.dp),
                    horizontalArrangement = Arrangement.Center,
                    verticalAlignment     = Alignment.CenterVertically
                ) {
                    Icon(Icons.Default.VerifiedUser, null, tint = SafetyGreen, modifier = Modifier.size(12.dp))
                    Spacer(Modifier.width(4.dp))
                    Text("Transaction sécurisée par AlBabor", fontSize = 10.sp, color = Gray400, fontWeight = FontWeight.Medium)
                }
            } else {
                Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.spacedBy(10.dp)) {
                    if (listing.numeroWhatsapp != null) {
                        GradientButton(
                            onClick  = onWhatsApp,
                            gradient = Brush.linearGradient(listOf(WhatsAppGreen, WhatsAppDark)),
                            icon     = Icons.Default.Forum,
                            text     = "WhatsApp",
                            modifier = Modifier.weight(1f)
                        )
                    }
                    val hasPhone = listing.numeroMobile != null || listing.user?.phone != null
                    if (hasPhone) {
                        GradientButton(
                            onClick  = onCall,
                            gradient = Brush.linearGradient(listOf(OceanBlue700, OceanBlue900)),
                            icon     = Icons.Default.Phone,
                            text     = "Appeler",
                            modifier = Modifier.weight(1f)
                        )
                    }
                }
            }

            Spacer(Modifier.height(10.dp))

            // Favorite button
            OutlinedButton(
                onClick  = onToggleFavorite,
                modifier = Modifier.fillMaxWidth().height(48.dp),
                shape    = RoundedCornerShape(14.dp),
                border   = BorderStroke(1.5.dp, if (isFavorited) HeartPink else Gray300),
                colors   = ButtonDefaults.outlinedButtonColors(
                    contentColor = if (isFavorited) HeartPink else Gray700
                )
            ) {
                val s by animateFloatAsState(if (isFavorited) 1.2f else 1f, tween(200), label = "fs")
                Icon(
                    if (isFavorited) Icons.Filled.Favorite else Icons.Outlined.FavoriteBorder,
                    null, modifier = Modifier.size(18.dp).graphicsLayer(scaleX = s, scaleY = s)
                )
                Spacer(Modifier.width(8.dp))
                Text(if (isFavorited) "Retirer des favoris" else "Ajouter aux favoris", fontWeight = FontWeight.Medium)
            }
        }
    }
}

@Composable
private fun GradientButton(
    onClick: () -> Unit,
    gradient: Brush,
    icon: ImageVector,
    text: String,
    modifier: Modifier = Modifier
) {
    Button(
        onClick        = onClick,
        modifier       = modifier.height(52.dp),
        shape          = RoundedCornerShape(16.dp),
        colors         = ButtonDefaults.buttonColors(containerColor = Color.Transparent),
        contentPadding = PaddingValues(0.dp),
        elevation      = ButtonDefaults.buttonElevation(defaultElevation = 6.dp, pressedElevation = 2.dp)
    ) {
        Box(
            Modifier.fillMaxSize().background(gradient, RoundedCornerShape(16.dp)),
            contentAlignment = Alignment.Center
        ) {
            Row(verticalAlignment = Alignment.CenterVertically, horizontalArrangement = Arrangement.spacedBy(8.dp)) {
                Icon(icon, null, tint = Color.White, modifier = Modifier.size(20.dp))
                Text(text, color = Color.White, fontWeight = FontWeight.SemiBold, fontSize = 14.sp)
            }
        }
    }
}

// ═══════════════════════════════════════════════════════════════════════════════
//  ERROR SCREEN
// ═══════════════════════════════════════════════════════════════════════════════

@Composable
private fun ErrorContent(msg: String, onRetry: () -> Unit, modifier: Modifier) {
    Column(modifier.padding(32.dp), horizontalAlignment = Alignment.CenterHorizontally,
        verticalArrangement = Arrangement.spacedBy(16.dp)) {
        Icon(Icons.Default.ErrorOutline, null, tint = Error500, modifier = Modifier.size(56.dp))
        Text("Impossible de charger l'annonce", style = MaterialTheme.typography.titleMedium, color = Gray900)
        Text(msg, style = MaterialTheme.typography.bodySmall, color = Gray500)
        Button(onRetry, shape = RoundedCornerShape(14.dp),
            colors = ButtonDefaults.buttonColors(containerColor = OceanBlue700)) { Text("Réessayer") }
    }
}

// ═══════════════════════════════════════════════════════════════════════════════
//  HELPERS
// ═══════════════════════════════════════════════════════════════════════════════

private fun specItems(
    listing: Listing, group: String,
    keys: List<Pair<String, String>>,
    units: Map<String, String> = emptyMap()
): List<Pair<String, String>> =
    keys.mapNotNull { (k, l) ->
        listing.getSpec(group, k)?.let { v ->
            l to (units[k]?.let { "$v $it" } ?: v)
        }
    }.distinctBy { it.first }

private fun conditionColor(c: String) = when (c) {
    "jamais_utilise" -> Color(0xFF27AE60)
    "comme_neuf"     -> Color(0xFF17A2B8)
    "bon_etat"       -> Color(0xFF2471A3)
    "etat_moyen"     -> Color(0xFFF39C12)
    "a_reviser"      -> Color(0xFFE74C3C)
    else             -> Gray500
}

private data class OfferVis(val bg: Color, val fg: Color, val label: String, val icon: ImageVector)

private fun offerTypeVisual(t: String) = when (t) {
    "negociable" -> OfferVis(Teal500.copy(0.1f), Teal500, "NÉGOCIABLE", Icons.Default.SwapHoriz)
    "fix"        -> OfferVis(OceanBlue900.copy(0.08f), OceanBlue900, "PRIX FIXE", Icons.Default.Lock)
    "offert"     -> OfferVis(SafetyGreen.copy(0.1f), SafetyGreen, "OFFERT", Icons.Default.CardGiftcard)
    else         -> OfferVis(Gray100, Gray500, t.uppercase(), Icons.Default.Info)
}
