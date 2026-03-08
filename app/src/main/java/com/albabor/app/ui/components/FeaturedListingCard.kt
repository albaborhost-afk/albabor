package com.albabor.app.ui.components

import androidx.compose.foundation.background
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Favorite
import androidx.compose.material.icons.filled.FavoriteBorder
import androidx.compose.material.icons.filled.LocationOn
import androidx.compose.material.icons.filled.Star
import androidx.compose.material3.Card
import androidx.compose.material3.CardDefaults
import androidx.compose.material3.Icon
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Surface
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.res.painterResource
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextOverflow
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import coil.compose.SubcomposeAsyncImage
import com.albabor.app.data.model.Listing
import com.albabor.app.ui.theme.AppBackground
import com.albabor.app.ui.theme.Coral500
import com.albabor.app.ui.theme.GlassSurfaceStrong
import com.albabor.app.ui.theme.OceanBlue900
import com.albabor.app.ui.theme.Teal100
import com.albabor.app.ui.theme.White
import com.albabor.app.ui.theme.categoryAccentColor
import com.albabor.app.ui.theme.categoryHeroImageRes

@Composable
fun FeaturedListingCard(
    listing: Listing,
    onClick: () -> Unit,
    onFavorite: (() -> Unit)? = null,
    modifier: Modifier = Modifier
) {
    val shape = RoundedCornerShape(20.dp)

    Card(
        modifier = modifier
            .width(260.dp)
            .clip(shape)
            .clickable(onClick = onClick),
        shape = shape,
        colors = CardDefaults.cardColors(containerColor = GlassSurfaceStrong),
        elevation = CardDefaults.cardElevation(defaultElevation = 10.dp, pressedElevation = 14.dp)
    ) {
        Column {
            Box(
                modifier = Modifier
                    .fillMaxWidth()
                    .height(170.dp)
            ) {
                SubcomposeAsyncImage(
                    model = listing.primaryImage,
                    contentDescription = listing.title,
                    contentScale = ContentScale.Crop,
                    modifier = Modifier.fillMaxSize(),
                    loading = { CategoryFallbackImage(listing = listing) },
                    error = { CategoryFallbackImage(listing = listing) }
                )

                Box(
                    modifier = Modifier
                        .matchParentSize()
                        .background(
                            Brush.verticalGradient(
                                colors = listOf(Color.Transparent, Color.Black.copy(alpha = 0.18f))
                            )
                        )
                )

                Row(
                    modifier = Modifier
                        .fillMaxWidth()
                        .padding(12.dp),
                    horizontalArrangement = Arrangement.SpaceBetween,
                    verticalAlignment = Alignment.Top
                ) {
                    Surface(
                        shape = RoundedCornerShape(999.dp),
                        color = Color.Transparent,
                        tonalElevation = 0.dp,
                        shadowElevation = 0.dp
                    ) {
                        Row(
                            modifier = Modifier
                                .background(
                                    Brush.horizontalGradient(
                                        listOf(Color(0xFFF1C40F), Color(0xFFE67E22))
                                    ),
                                    RoundedCornerShape(999.dp)
                                )
                                .padding(horizontal = 10.dp, vertical = 6.dp),
                            verticalAlignment = Alignment.CenterVertically,
                            horizontalArrangement = Arrangement.spacedBy(4.dp)
                        ) {
                            Icon(
                                imageVector = Icons.Filled.Star,
                                contentDescription = null,
                                tint = White,
                                modifier = Modifier.height(10.dp)
                            )
                            Text(
                                text = "En vedette",
                                color = White,
                                fontSize = 11.sp,
                                fontWeight = FontWeight.Bold
                            )
                        }
                    }

                    if (onFavorite != null) {
                        Box(
                            modifier = Modifier
                                .size(36.dp)
                                .clip(CircleShape)
                                .background(GlassSurfaceStrong.copy(alpha = 0.82f))
                                .clickable(onClick = onFavorite),
                            contentAlignment = Alignment.Center
                        ) {
                            Icon(
                                imageVector = if (listing.isFavorited) Icons.Filled.Favorite else Icons.Filled.FavoriteBorder,
                                contentDescription = null,
                                tint = if (listing.isFavorited) Coral500 else White,
                                modifier = Modifier.height(16.dp)
                            )
                        }
                    }
                }

                Surface(
                    modifier = Modifier
                        .align(Alignment.BottomStart)
                        .padding(12.dp),
                    shape = RoundedCornerShape(999.dp),
                    color = categoryAccentColor(listing.category).copy(alpha = 0.88f)
                ) {
                    Text(
                        text = listing.categoryLabel,
                        modifier = Modifier.padding(horizontal = 10.dp, vertical = 5.dp),
                        color = White,
                        fontSize = 11.sp,
                        fontWeight = FontWeight.SemiBold
                    )
                }
            }

            Column(
                modifier = Modifier
                    .fillMaxWidth()
                    .background(GlassSurfaceStrong)
                    .padding(horizontal = 16.dp, vertical = 15.dp),
                verticalArrangement = Arrangement.spacedBy(8.dp)
            ) {
                Column(verticalArrangement = Arrangement.spacedBy(2.dp)) {
                    Text(
                        text = listing.formattedPrice,
                        color = OceanBlue900,
                        fontSize = 20.sp,
                        fontWeight = FontWeight.Bold
                    )
                    listing.formattedConvertedPrice?.let { converted ->
                        Text(
                            text = converted,
                            color = MaterialTheme.colorScheme.onSurfaceVariant,
                            fontSize = 12.sp,
                            fontWeight = FontWeight.Medium
                        )
                    }
                }

                Text(
                    text = listing.title,
                    color = MaterialTheme.colorScheme.onSurface,
                    fontSize = 15.sp,
                    fontWeight = FontWeight.Medium,
                    maxLines = 2,
                    overflow = TextOverflow.Ellipsis,
                    lineHeight = 20.sp
                )

                listing.wilaya?.let { wilaya ->
                    Row(
                        verticalAlignment = Alignment.CenterVertically,
                        horizontalArrangement = Arrangement.spacedBy(4.dp)
                    ) {
                        Icon(
                            imageVector = Icons.Filled.LocationOn,
                            contentDescription = null,
                            tint = Teal100.copy(alpha = 0.95f),
                            modifier = Modifier.height(13.dp)
                        )
                        Text(
                            text = wilaya,
                            color = MaterialTheme.colorScheme.onSurfaceVariant,
                            fontSize = 13.sp
                        )
                    }
                }
            }
        }
    }
}

@Composable
private fun CategoryFallbackImage(listing: Listing) {
    Box(
        modifier = Modifier
            .fillMaxSize()
            .background(AppBackground)
    ) {
        androidx.compose.foundation.Image(
            painter = painterResource(id = categoryHeroImageRes(listing.category)),
            contentDescription = null,
            modifier = Modifier.fillMaxSize(),
            contentScale = ContentScale.Crop
        )
        Box(
            modifier = Modifier
                .matchParentSize()
                .background(
                    Brush.verticalGradient(
                        colors = listOf(Color.Transparent, Color.Black.copy(alpha = 0.16f))
                    )
                )
        )
    }
}
