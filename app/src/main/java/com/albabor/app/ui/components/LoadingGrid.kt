package com.albabor.app.ui.components

import androidx.compose.foundation.layout.*
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Surface
import androidx.compose.runtime.Composable
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.unit.dp
import com.google.accompanist.placeholder.PlaceholderHighlight
import com.google.accompanist.placeholder.material3.fade
import com.google.accompanist.placeholder.material3.placeholder

// ─── Single shimmer card ──────────────────────────────────────────────────────

@Composable
fun ShimmerListingCard(modifier: Modifier = Modifier) {
    val cardShape = RoundedCornerShape(12.dp)

    Surface(
        modifier = modifier,
        shape = cardShape,
        tonalElevation = 1.dp,
        shadowElevation = 2.dp
    ) {
        Column {
            // Image placeholder — 16:10 ratio
            Box(
                modifier = Modifier
                    .fillMaxWidth()
                    .aspectRatio(16f / 10f)
                    .placeholder(
                        visible = true,
                        highlight = PlaceholderHighlight.fade()
                    )
            )

            // Text placeholders
            Column(modifier = Modifier.padding(12.dp), verticalArrangement = Arrangement.spacedBy(8.dp)) {
                // Title
                Box(
                    modifier = Modifier
                        .fillMaxWidth(0.85f)
                        .height(14.dp)
                        .clip(RoundedCornerShape(4.dp))
                        .placeholder(visible = true, highlight = PlaceholderHighlight.fade())
                )
                // Price
                Box(
                    modifier = Modifier
                        .fillMaxWidth(0.5f)
                        .height(16.dp)
                        .clip(RoundedCornerShape(4.dp))
                        .placeholder(visible = true, highlight = PlaceholderHighlight.fade())
                )
                // Chips row
                Row(horizontalArrangement = Arrangement.spacedBy(6.dp)) {
                    Box(
                        modifier = Modifier
                            .width(52.dp)
                            .height(22.dp)
                            .clip(RoundedCornerShape(50))
                            .placeholder(visible = true, highlight = PlaceholderHighlight.fade())
                    )
                    Box(
                        modifier = Modifier
                            .width(68.dp)
                            .height(22.dp)
                            .clip(RoundedCornerShape(50))
                            .placeholder(visible = true, highlight = PlaceholderHighlight.fade())
                    )
                }
            }
        }
    }
}

// ─── Full 2-column shimmer grid ───────────────────────────────────────────────

@Composable
fun LoadingGrid(
    count: Int = 6,
    modifier: Modifier = Modifier
) {
    val pairs = (0 until count).chunked(2)

    Column(
        modifier = modifier,
        verticalArrangement = Arrangement.spacedBy(12.dp)
    ) {
        pairs.forEach { pair ->
            Row(
                modifier = Modifier.fillMaxWidth(),
                horizontalArrangement = Arrangement.spacedBy(12.dp)
            ) {
                pair.forEach { _ ->
                    ShimmerListingCard(
                        modifier = Modifier.weight(1f)
                    )
                }
                // If odd count, fill remaining slot with empty space
                if (pair.size == 1) {
                    Spacer(modifier = Modifier.weight(1f))
                }
            }
        }
    }
}

// ─── Horizontal shimmer row (for featured carousel) ──────────────────────────

@Composable
fun LoadingFeaturedRow(modifier: Modifier = Modifier) {
    Row(
        modifier = modifier,
        horizontalArrangement = Arrangement.spacedBy(16.dp)
    ) {
        repeat(2) {
            val cardShape = RoundedCornerShape(16.dp)
            Surface(
                modifier = Modifier
                    .width(280.dp)
                    .height(220.dp),
                shape = cardShape,
                tonalElevation = 1.dp,
                shadowElevation = 4.dp
            ) {
                Box(
                    modifier = Modifier
                        .fillMaxSize()
                        .placeholder(
                            visible = true,
                            highlight = PlaceholderHighlight.fade()
                        )
                )
            }
        }
    }
}
