package com.albabor.app.ui.components

import androidx.compose.foundation.background
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Bolt
import androidx.compose.material.icons.filled.Favorite
import androidx.compose.material.icons.filled.FavoriteBorder
import androidx.compose.material.icons.filled.LocationOn
import androidx.compose.material.icons.filled.Star
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

// ─── Large featured card for horizontal carousel ──────────────────────────────

@Composable
fun FeaturedListingCard(
    listing: Listing,
    onClick: () -> Unit,
    onFavorite: (() -> Unit)? = null,
    modifier: Modifier = Modifier
) {
    val cardShape = RoundedCornerShape(16.dp)

    Card(
        modifier = modifier
            .width(280.dp)
            .height(220.dp)
            .clip(cardShape)
            .clickable(onClick = onClick),
        shape = cardShape,
        elevation = CardDefaults.cardElevation(defaultElevation = 6.dp, pressedElevation = 10.dp),
        colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surface)
    ) {
        Box(modifier = Modifier.fillMaxSize()) {

            // Full-bleed image
            AsyncImage(
                model = listing.primaryImage,
                contentDescription = listing.title,
                contentScale = ContentScale.Crop,
                modifier = Modifier
                    .fillMaxSize()
                    .background(OceanBlue100)
            )

            // Deep gradient scrim from bottom
            Box(
                modifier = Modifier
                    .fillMaxWidth()
                    .fillMaxHeight(0.65f)
                    .align(Alignment.BottomCenter)
                    .background(
                        Brush.verticalGradient(
                            colors = listOf(
                                Color.Transparent,
                                Color(0xCC000000)
                            )
                        )
                    )
            )

            // Top row: "Sponsorisé" badge + favorite
            Row(
                modifier = Modifier
                    .fillMaxWidth()
                    .padding(10.dp)
                    .align(Alignment.TopStart),
                horizontalArrangement = Arrangement.SpaceBetween,
                verticalAlignment = Alignment.CenterVertically
            ) {
                // Sponsored badge
                Surface(
                    shape = RoundedCornerShape(6.dp),
                    color = Gold500
                ) {
                    Row(
                        modifier = Modifier.padding(horizontal = 7.dp, vertical = 4.dp),
                        verticalAlignment = Alignment.CenterVertically,
                        horizontalArrangement = Arrangement.spacedBy(3.dp)
                    ) {
                        Icon(
                            Icons.Filled.Star,
                            contentDescription = null,
                            tint = White,
                            modifier = Modifier.size(11.dp)
                        )
                        Text(
                            text = "Sponsorisé",
                            style = MaterialTheme.typography.labelSmall,
                            color = White,
                            fontWeight = FontWeight.Bold,
                            fontSize = 10.sp
                        )
                    }
                }

                // Favorite button
                if (onFavorite != null) {
                    Box(
                        modifier = Modifier
                            .size(32.dp)
                            .clip(CircleShape)
                            .background(Color(0x88000000))
                            .clickable(onClick = onFavorite),
                        contentAlignment = Alignment.Center
                    ) {
                        Icon(
                            imageVector = if (listing.isFavorited) Icons.Filled.Favorite
                                          else Icons.Filled.FavoriteBorder,
                            contentDescription = if (listing.isFavorited) "Retirer des favoris"
                                                 else "Ajouter aux favoris",
                            tint = if (listing.isFavorited) Color(0xFFFF6B6B) else White,
                            modifier = Modifier.size(18.dp)
                        )
                    }
                }
            }

            // Bottom content overlay
            Column(
                modifier = Modifier
                    .align(Alignment.BottomStart)
                    .fillMaxWidth()
                    .padding(12.dp),
                verticalArrangement = Arrangement.spacedBy(5.dp)
            ) {
                // Category badge
                Surface(
                    shape = RoundedCornerShape(6.dp),
                    color = OceanBlue700.copy(alpha = 0.9f)
                ) {
                    Text(
                        text = "${listing.categoryIcon} ${listing.categoryLabel}",
                        modifier = Modifier.padding(horizontal = 7.dp, vertical = 3.dp),
                        style = MaterialTheme.typography.labelSmall,
                        color = White,
                        fontSize = 10.sp
                    )
                }

                // Title
                Text(
                    text = listing.title,
                    style = MaterialTheme.typography.titleSmall,
                    fontWeight = FontWeight.Bold,
                    color = White,
                    maxLines = 2,
                    overflow = TextOverflow.Ellipsis,
                    lineHeight = 18.sp
                )

                // Price + location row
                Row(
                    modifier = Modifier.fillMaxWidth(),
                    horizontalArrangement = Arrangement.SpaceBetween,
                    verticalAlignment = Alignment.Bottom
                ) {
                    Column {
                        Text(
                            text = listing.formattedPrice,
                            style = MaterialTheme.typography.titleMedium,
                            fontWeight = FontWeight.Bold,
                            color = White,
                            fontSize = 16.sp
                        )
                        listing.formattedConvertedPrice?.let { converted ->
                            Text(
                                text = converted,
                                style = MaterialTheme.typography.labelSmall,
                                color = White.copy(alpha = 0.75f)
                            )
                        }
                    }

                    // Wilaya chip
                    listing.wilaya?.let { wilaya ->
                        Row(
                            verticalAlignment = Alignment.CenterVertically,
                            horizontalArrangement = Arrangement.spacedBy(3.dp)
                        ) {
                            Icon(
                                Icons.Filled.LocationOn,
                                contentDescription = null,
                                tint = Teal100,
                                modifier = Modifier.size(14.dp)
                            )
                            Text(
                                text = wilaya,
                                style = MaterialTheme.typography.labelSmall,
                                color = Teal100,
                                fontWeight = FontWeight.Medium
                            )
                        }
                    }
                }

                // Power chip (if available)
                listing.power?.let { power ->
                    Row(
                        verticalAlignment = Alignment.CenterVertically,
                        horizontalArrangement = Arrangement.spacedBy(3.dp)
                    ) {
                        Icon(
                            Icons.Filled.Bolt,
                            contentDescription = null,
                            tint = Gold100,
                            modifier = Modifier.size(13.dp)
                        )
                        Text(
                            text = "$power CV",
                            style = MaterialTheme.typography.labelSmall,
                            color = Gold100,
                            fontWeight = FontWeight.Medium
                        )
                    }
                }
            }
        }
    }
}
