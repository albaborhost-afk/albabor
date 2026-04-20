package com.albabor.app.ui.components

import androidx.compose.foundation.BorderStroke
import androidx.compose.foundation.Image
import androidx.compose.foundation.background
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.*
import androidx.compose.material3.*
import androidx.compose.runtime.Composable
import androidx.compose.runtime.CompositionLocalProvider
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.draw.shadow
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.res.painterResource
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextOverflow
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import coil.compose.AsyncImage
import coil.compose.SubcomposeAsyncImage
import com.albabor.app.data.model.Listing
import com.albabor.app.ui.theme.*

private val CardShape = RoundedCornerShape(20.dp)
private val HeartPink = Color(0xFFFF4D6D)
private val GridCardMinHeight = 320.dp
private val GridCardImageHeight = 245.dp

// ─── Main listing card (2-column grid) ───────────────────────────────────────

@Composable
fun ListingCard(
    listing: Listing,
    onClick: () -> Unit,
    onFavorite: (() -> Unit)? = null,
    isFavorited: Boolean = listing.isFavorited,
    modifier: Modifier = Modifier
) {
    Surface(
        modifier = modifier
            .fillMaxWidth()
            .heightIn(min = GridCardMinHeight)
            .clip(CardShape)
            .clickable(onClick = onClick),
        shape       = CardShape,
        color       = Color.White,
        shadowElevation = 8.dp
    ) {
        Column {
            // ── Image ────────────────────────────────────────────────────────
            Box(
                modifier = Modifier
                    .fillMaxWidth()
                    .height(GridCardImageHeight)
            ) {
                SubcomposeAsyncImage(
                    model              = coil.request.ImageRequest.Builder(androidx.compose.ui.platform.LocalContext.current)
                        .data(listing.primaryImage)
                        .size(coil.size.Size.ORIGINAL)
                        .build(),
                    contentDescription = listing.title,
                    contentScale       = ContentScale.Crop,
                    modifier           = Modifier.fillMaxSize().clip(
                        RoundedCornerShape(topStart = 20.dp, topEnd = 20.dp)
                    ),
                    error = {
                        Box(Modifier.fillMaxSize().background(
                            Brush.linearGradient(listOf(OceanBlue50, AppBackground))
                        ), contentAlignment = Alignment.Center) {
                            Image(
                                painter = painterResource(id = categoryHeroImageRes(listing.category)),
                                contentDescription = null,
                                modifier = Modifier.fillMaxSize(),
                                contentScale = ContentScale.Crop
                            )
                        }
                    },
                    loading = {
                        Box(Modifier.fillMaxSize().background(
                            Brush.linearGradient(listOf(OceanBlue50, AppBackground))
                        ), contentAlignment = Alignment.Center) {
                            CircularProgressIndicator(
                                modifier = Modifier.size(24.dp),
                                color = OceanBlue300,
                                strokeWidth = 2.dp
                            )
                        }
                    }
                )

                // Gradient scrim
                Box(
                    Modifier
                        .fillMaxWidth()
                        .height(60.dp)
                        .align(Alignment.BottomCenter)
                        .background(Brush.verticalGradient(listOf(Color.Transparent, Color.Black.copy(0.4f))))
                )

                // Category badge — glass pill top-left
                val accent = categoryAccentColor(listing.category)
                Surface(
                    modifier = Modifier.padding(10.dp).align(Alignment.TopStart),
                    shape    = RoundedCornerShape(12.dp),
                    color    = accent.copy(alpha = 0.9f),
                    shadowElevation = 4.dp
                ) {
                    Row(
                        Modifier.padding(horizontal = 10.dp, vertical = 5.dp),
                        verticalAlignment = Alignment.CenterVertically,
                        horizontalArrangement = Arrangement.spacedBy(4.dp)
                    ) {
                        Text(listing.categoryIcon, fontSize = 10.sp)
                        Text(
                            listing.categoryLabel,
                            fontSize = 10.sp,
                            fontWeight = FontWeight.Bold,
                            color = Color.White,
                            letterSpacing = 0.3.sp
                        )
                    }
                }

                // Featured badge
                if (listing.isFeatured) {
                    Surface(
                        modifier = Modifier.padding(top = 10.dp).align(Alignment.TopCenter),
                        shape    = RoundedCornerShape(10.dp),
                        color    = Color.Transparent
                    ) {
                        Box(
                            Modifier
                                .background(
                                    Brush.linearGradient(listOf(Color(0xFFFFA500), Color(0xFFFF6B00))),
                                    RoundedCornerShape(10.dp)
                                )
                                .padding(horizontal = 8.dp, vertical = 3.dp)
                        ) {
                            Row(verticalAlignment = Alignment.CenterVertically, horizontalArrangement = Arrangement.spacedBy(3.dp)) {
                                Icon(Icons.Default.Star, null, tint = Color.White, modifier = Modifier.size(10.dp))
                                Text("Vedette", color = Color.White, fontSize = 9.sp, fontWeight = FontWeight.Bold)
                            }
                        }
                    }
                }

                // Favorite button — glass circle top-right
                if (onFavorite != null) {
                    Box(
                        modifier = Modifier
                            .padding(10.dp)
                            .align(Alignment.TopEnd)
                            .size(34.dp)
                            .shadow(6.dp, CircleShape)
                            .clip(CircleShape)
                            .background(Color.Black.copy(0.3f))
                            .clickable(onClick = onFavorite),
                        contentAlignment = Alignment.Center
                    ) {
                        Icon(
                            imageVector = if (isFavorited) Icons.Filled.Favorite else Icons.Filled.FavoriteBorder,
                            contentDescription = null,
                            tint     = if (isFavorited) HeartPink else Color.White,
                            modifier = Modifier.size(17.dp)
                        )
                    }
                }

                // Condition badge — bottom-left on image
                listing.condition?.let { cond ->
                    val condColor = conditionColor(cond)
                    Surface(
                        modifier = Modifier.padding(10.dp).align(Alignment.BottomStart),
                        shape    = RoundedCornerShape(8.dp),
                        color    = condColor.copy(0.85f)
                    ) {
                        Text(
                            listing.conditionLabel,
                            Modifier.padding(horizontal = 8.dp, vertical = 3.dp),
                            color = Color.White, fontSize = 9.sp, fontWeight = FontWeight.SemiBold
                        )
                    }
                }

                // "Vendu" or "En pause" overlay
                if (listing.status == "sold" || listing.status == "paused") {
                    Box(
                        Modifier.fillMaxSize().background(Color.Black.copy(0.5f)),
                        contentAlignment = Alignment.Center
                    ) {
                        Surface(
                            shape = RoundedCornerShape(12.dp),
                            color = if (listing.status == "sold") Error500 else Gold500
                        ) {
                            Text(
                                if (listing.status == "sold") "VENDU" else "EN PAUSE",
                                Modifier.padding(horizontal = 16.dp, vertical = 8.dp),
                                color = Color.White, fontWeight = FontWeight.Bold, fontSize = 13.sp
                            )
                        }
                    }
                }
            }

            // ── Info section ─────────────────────────────────────────────────
            Column(
                Modifier.fillMaxWidth().padding(horizontal = 10.dp, vertical = 7.dp),
                verticalArrangement = Arrangement.spacedBy(3.dp)
            ) {
                Text(
                    listing.title,
                    fontSize   = 12.sp,
                    fontWeight = FontWeight.SemiBold,
                    maxLines   = 1,
                    overflow   = TextOverflow.Ellipsis,
                    color      = Gray900
                )

                // Price
                Text(
                    listing.formattedPrice,
                    fontSize   = 13.sp,
                    fontWeight = FontWeight.ExtraBold,
                    color      = OceanBlue900,
                    letterSpacing = (-0.3).sp
                )

                // Info chips row
                Row(
                    horizontalArrangement = Arrangement.spacedBy(4.dp),
                    verticalAlignment     = Alignment.CenterVertically
                ) {
                    listing.year?.let {
                        SmallInfoPill(Icons.Default.CalendarMonth, it, OceanBlue900)
                    }
                    listing.power?.let {
                        SmallInfoPill(Icons.Default.Bolt, "$it CV", Gold500)
                    }
                }
            }
        }
    }
}

@Composable
private fun SmallInfoPill(
    icon: androidx.compose.ui.graphics.vector.ImageVector,
    text: String,
    color: Color
) {
    Surface(
        shape  = RoundedCornerShape(8.dp),
        color  = color.copy(0.08f),
        border = BorderStroke(0.5.dp, color.copy(0.15f))
    ) {
        Row(
            Modifier.padding(horizontal = 6.dp, vertical = 3.dp),
            verticalAlignment = Alignment.CenterVertically,
            horizontalArrangement = Arrangement.spacedBy(3.dp)
        ) {
            Icon(icon, null, tint = color, modifier = Modifier.size(10.dp))
            Text(text, fontSize = 9.sp, color = color, fontWeight = FontWeight.SemiBold, maxLines = 1)
        }
    }
}

private fun conditionColor(c: String) = when (c) {
    "jamais_utilise" -> Color(0xFF27AE60)
    "comme_neuf"     -> Color(0xFF17A2B8)
    "bon_etat"       -> Color(0xFF2471A3)
    "etat_moyen"     -> Color(0xFFF39C12)
    "a_reviser"      -> Color(0xFFE74C3C)
    else             -> Gray500
}

// ─── Compact horizontal row card (used in MyListings) ────────────────────────

@Composable
fun ListingRowCard(
    listing: Listing,
    onClick: () -> Unit,
    modifier: Modifier = Modifier,
    trailingContent: @Composable (() -> Unit)? = null
) {
    Surface(
        modifier = modifier.fillMaxWidth().clickable(onClick = onClick),
        shape    = RoundedCornerShape(16.dp),
        color    = Color.White,
        shadowElevation = 4.dp
    ) {
        Row(Modifier.fillMaxWidth().padding(12.dp), verticalAlignment = Alignment.CenterVertically) {
            Box(
                Modifier.size(80.dp).clip(RoundedCornerShape(12.dp)).background(AppBackground)
            ) {
                AsyncImage(
                    model = listing.primaryImage,
                    contentDescription = listing.title,
                    contentScale = ContentScale.Crop,
                    modifier = Modifier.fillMaxSize()
                )
                if (listing.primaryImage == null) {
                    Image(
                        painter = painterResource(id = categoryHeroImageRes(listing.category)),
                        contentDescription = null, modifier = Modifier.fillMaxSize(),
                        contentScale = ContentScale.Crop
                    )
                }
            }

            Spacer(Modifier.width(14.dp))

            Column(Modifier.weight(1f), verticalArrangement = Arrangement.spacedBy(4.dp)) {
                Text(listing.title, style = MaterialTheme.typography.bodyMedium,
                    fontWeight = FontWeight.SemiBold, color = Gray900,
                    maxLines = 2, overflow = TextOverflow.Ellipsis)
                Text(listing.formattedPrice, style = MaterialTheme.typography.bodyMedium,
                    color = OceanBlue700, fontWeight = FontWeight.Bold)
                Row(horizontalArrangement = Arrangement.spacedBy(5.dp), verticalAlignment = Alignment.CenterVertically) {
                    listing.wilaya?.let { MiniChip(it, Gray100, Gray500) }
                    MiniChip(listing.categoryLabel, OceanBlue50, OceanBlue700)
                }
            }

            if (trailingContent != null) {
                Spacer(Modifier.width(8.dp))
                trailingContent()
            }
        }
    }
}

// ─── Mini pill chip ──────────────────────────────────────────────────────────

@Composable
fun MiniChip(text: String, backgroundColor: Color, textColor: Color, modifier: Modifier = Modifier) {
    Box(modifier.clip(RoundedCornerShape(50)).background(backgroundColor).padding(horizontal = 7.dp, vertical = 3.dp)) {
        Text(text, color = textColor, fontSize = 10.sp, fontWeight = FontWeight.Medium, maxLines = 1)
    }
}
