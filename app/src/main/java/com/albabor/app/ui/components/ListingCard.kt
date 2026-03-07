package com.albabor.app.ui.components

import androidx.compose.foundation.background
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Bolt
import androidx.compose.material.icons.filled.CalendarMonth
import androidx.compose.material.icons.filled.Favorite
import androidx.compose.material.icons.filled.FavoriteBorder
import androidx.compose.material.icons.filled.LocationOn
import androidx.compose.material3.*
import androidx.compose.runtime.Composable
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextOverflow
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import coil.compose.AsyncImage
import com.albabor.app.data.model.Listing
import com.albabor.app.ui.theme.*

// ─── Main listing card (2-column grid) ───────────────────────────────────────

@Composable
fun ListingCard(
    listing: Listing,
    onClick: () -> Unit,
    onFavorite: (() -> Unit)? = null,
    isFavorited: Boolean = listing.isFavorited,
    modifier: Modifier = Modifier
) {
    val cardShape = RoundedCornerShape(12.dp)

    Card(
        modifier = modifier
            .fillMaxWidth()
            .clip(cardShape)
            .clickable(onClick = onClick),
        shape = cardShape,
        colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surface),
        elevation = CardDefaults.cardElevation(defaultElevation = 2.dp, pressedElevation = 6.dp)
    ) {
        Column {
            // ── Image ──────────────────────────────────────────────────────────
            Box(
                modifier = Modifier
                    .fillMaxWidth()
                    .aspectRatio(16f / 10f)
            ) {
                AsyncImage(
                    model = listing.primaryImage,
                    contentDescription = listing.title,
                    contentScale = ContentScale.Crop,
                    modifier = Modifier
                        .fillMaxSize()
                        .background(OceanBlue100)
                )

                // Gradient scrim at bottom of image
                Box(
                    modifier = Modifier
                        .fillMaxWidth()
                        .height(48.dp)
                        .align(Alignment.BottomCenter)
                        .background(
                            Brush.verticalGradient(
                                colors = listOf(Color.Transparent, Color(0x55000000))
                            )
                        )
                )

                // Category badge — top-left
                Surface(
                    modifier = Modifier
                        .padding(8.dp)
                        .align(Alignment.TopStart),
                    shape = RoundedCornerShape(6.dp),
                    color = OceanBlue900.copy(alpha = 0.82f)
                ) {
                    Text(
                        text = "${listing.categoryIcon} ${listing.categoryLabel}",
                        modifier = Modifier.padding(horizontal = 6.dp, vertical = 3.dp),
                        style = MaterialTheme.typography.labelSmall,
                        color = White,
                        fontSize = 10.sp
                    )
                }

                // Favorite button — top-right
                if (onFavorite != null) {
                    Box(
                        modifier = Modifier
                            .padding(6.dp)
                            .align(Alignment.TopEnd)
                            .size(30.dp)
                            .clip(CircleShape)
                            .background(Color(0x88000000))
                            .clickable(onClick = onFavorite),
                        contentAlignment = Alignment.Center
                    ) {
                        Icon(
                            imageVector = if (isFavorited) Icons.Filled.Favorite
                                          else Icons.Filled.FavoriteBorder,
                            contentDescription = if (isFavorited) "Retirer des favoris"
                                                 else "Ajouter aux favoris",
                            tint = if (isFavorited) Color(0xFFFF6B6B) else White,
                            modifier = Modifier.size(16.dp)
                        )
                    }
                }

                // "Vendu" or "En pause" overlay badge
                if (listing.status == "sold" || listing.status == "paused") {
                    Box(
                        modifier = Modifier
                            .fillMaxSize()
                            .background(Color(0x66000000)),
                        contentAlignment = Alignment.Center
                    ) {
                        Surface(
                            shape = RoundedCornerShape(8.dp),
                            color = if (listing.status == "sold") Error500 else Gold500
                        ) {
                            Text(
                                text = if (listing.status == "sold") "VENDU" else "EN PAUSE",
                                modifier = Modifier.padding(horizontal = 12.dp, vertical = 6.dp),
                                style = MaterialTheme.typography.labelLarge,
                                color = White,
                                fontWeight = FontWeight.Bold
                            )
                        }
                    }
                }
            }

            // ── Info section ──────────────────────────────────────────────────
            Column(
                modifier = Modifier
                    .fillMaxWidth()
                    .padding(horizontal = 10.dp, vertical = 9.dp),
                verticalArrangement = Arrangement.spacedBy(5.dp)
            ) {
                // Title
                Text(
                    text = listing.title,
                    style = MaterialTheme.typography.titleSmall,
                    fontWeight = FontWeight.Bold,
                    maxLines = 1,
                    overflow = TextOverflow.Ellipsis,
                    color = MaterialTheme.colorScheme.onSurface
                )

                // Price
                Column {
                    Text(
                        text = listing.formattedPrice,
                        style = MaterialTheme.typography.bodyLarge,
                        fontWeight = FontWeight.Bold,
                        color = OceanBlue700,
                        fontSize = 15.sp
                    )
                    listing.formattedConvertedPrice?.let { converted ->
                        Text(
                            text = converted,
                            style = MaterialTheme.typography.labelSmall,
                            color = MaterialTheme.colorScheme.onSurfaceVariant
                        )
                    }
                }

                // Info chips row
                Row(
                    horizontalArrangement = Arrangement.spacedBy(4.dp),
                    verticalAlignment = Alignment.CenterVertically
                ) {
                    listing.year?.let { year ->
                        InfoChip(
                            icon = { Icon(Icons.Filled.CalendarMonth, null, modifier = Modifier.size(10.dp)) },
                            text = year,
                            background = OceanBlue900,
                            textColor = White
                        )
                    }
                    listing.wilaya?.let { wilaya ->
                        InfoChip(
                            icon = { Icon(Icons.Filled.LocationOn, null, modifier = Modifier.size(10.dp)) },
                            text = wilaya,
                            background = Teal500,
                            textColor = White
                        )
                    }
                    listing.power?.let { power ->
                        InfoChip(
                            icon = { Icon(Icons.Filled.Bolt, null, modifier = Modifier.size(10.dp)) },
                            text = "${power} CV",
                            background = Gold500,
                            textColor = White
                        )
                    }
                }
            }
        }
    }
}

// ─── Compact horizontal row card (used in MyListings) ────────────────────────

@Composable
fun ListingRowCard(
    listing: Listing,
    onClick: () -> Unit,
    modifier: Modifier = Modifier,
    trailingContent: @Composable (() -> Unit)? = null
) {
    val cardShape = RoundedCornerShape(12.dp)

    Card(
        modifier = modifier
            .fillMaxWidth()
            .clickable(onClick = onClick),
        shape = cardShape,
        colors = CardDefaults.cardColors(containerColor = White),
        elevation = CardDefaults.cardElevation(defaultElevation = 1.dp, pressedElevation = 4.dp)
    ) {
        Row(
            modifier = Modifier
                .fillMaxWidth()
                .padding(10.dp),
            verticalAlignment = Alignment.CenterVertically
        ) {
            // Thumbnail
            Box(
                modifier = Modifier
                    .size(80.dp)
                    .clip(RoundedCornerShape(10.dp))
                    .background(OceanBlue100)
            ) {
                AsyncImage(
                    model = listing.primaryImage,
                    contentDescription = listing.title,
                    contentScale = ContentScale.Crop,
                    modifier = Modifier.fillMaxSize()
                )
                // No-image fallback
                if (listing.primaryImage == null) {
                    Box(
                        modifier = Modifier
                            .fillMaxSize()
                            .background(
                                Brush.verticalGradient(listOf(OceanBlue100, OceanBlue300))
                            ),
                        contentAlignment = Alignment.Center
                    ) {
                        Text(text = listing.categoryIcon, fontSize = 28.sp)
                    }
                }
            }

            Spacer(modifier = Modifier.width(12.dp))

            // Info column
            Column(
                modifier = Modifier.weight(1f),
                verticalArrangement = Arrangement.spacedBy(4.dp)
            ) {
                Text(
                    text = listing.title,
                    style = MaterialTheme.typography.bodyMedium.copy(
                        fontWeight = FontWeight.SemiBold,
                        color = Gray900
                    ),
                    maxLines = 2,
                    overflow = TextOverflow.Ellipsis
                )

                Text(
                    text = listing.formattedPrice,
                    style = MaterialTheme.typography.bodyMedium.copy(
                        color = OceanBlue700,
                        fontWeight = FontWeight.Bold
                    )
                )

                Row(
                    horizontalArrangement = Arrangement.spacedBy(5.dp),
                    verticalAlignment = Alignment.CenterVertically
                ) {
                    listing.wilaya?.let { wilaya ->
                        MiniChip(text = wilaya, backgroundColor = Gray100, textColor = Gray500)
                    }
                    MiniChip(
                        text = listing.categoryLabel,
                        backgroundColor = OceanBlue50,
                        textColor = OceanBlue700
                    )
                }
            }

            // Trailing slot (status badge + menu)
            if (trailingContent != null) {
                Spacer(modifier = Modifier.width(8.dp))
                trailingContent()
            }
        }
    }
}

// ─── Mini pill chip ───────────────────────────────────────────────────────────

@Composable
fun MiniChip(
    text: String,
    backgroundColor: Color,
    textColor: Color,
    modifier: Modifier = Modifier
) {
    Box(
        modifier = modifier
            .clip(RoundedCornerShape(50))
            .background(backgroundColor)
            .padding(horizontal = 7.dp, vertical = 3.dp)
    ) {
        Text(
            text = text,
            color = textColor,
            fontSize = 10.sp,
            fontWeight = FontWeight.Medium,
            maxLines = 1
        )
    }
}

// ─── Small info chip inside listing card ─────────────────────────────────────

@Composable
private fun InfoChip(
    icon: @Composable () -> Unit,
    text: String,
    background: Color,
    textColor: Color
) {
    Surface(
        shape = RoundedCornerShape(50),
        color = background
    ) {
        Row(
            modifier = Modifier.padding(horizontal = 5.dp, vertical = 3.dp),
            verticalAlignment = Alignment.CenterVertically,
            horizontalArrangement = Arrangement.spacedBy(3.dp)
        ) {
            CompositionLocalProvider(LocalContentColor provides textColor) {
                icon()
            }
            Text(
                text = text,
                style = MaterialTheme.typography.labelSmall,
                color = textColor,
                maxLines = 1,
                fontSize = 9.sp
            )
        }
    }
}
