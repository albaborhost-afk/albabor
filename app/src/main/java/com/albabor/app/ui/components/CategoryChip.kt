package com.albabor.app.ui.components

import androidx.compose.foundation.BorderStroke
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material3.*
import androidx.compose.runtime.Composable
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import com.albabor.app.ui.theme.*

// ─── Data ────────────────────────────────────────────────────────────────────

data class CategoryItem(
    val key: String?,       // null = "Tous"
    val label: String,
    val emoji: String
)

val allCategories = listOf(
    CategoryItem(null,     "Tous",      ""),
    CategoryItem("boat",   "Bateaux",   "⛵"),
    CategoryItem("jetski", "Jet-Skis",  "🚤"),
    CategoryItem("engine", "Moteurs",   "⚙️"),
    CategoryItem("parts",  "Pièces",    "🔩"),
)

// ─── Composable ──────────────────────────────────────────────────────────────

@Composable
fun CategoryChip(
    item: CategoryItem,
    selected: Boolean,
    onClick: () -> Unit,
    modifier: Modifier = Modifier
) {
    val shape = RoundedCornerShape(50)

    Surface(
        modifier = modifier
            .clip(shape)
            .clickable(onClick = onClick),
        shape = shape,
        color = if (selected) OceanBlue700 else MaterialTheme.colorScheme.surface,
        border = if (selected) null else BorderStroke(1.dp, OceanBlue300),
        tonalElevation = if (selected) 0.dp else 0.dp,
        shadowElevation = if (selected) 2.dp else 0.dp
    ) {
        Row(
            modifier = Modifier.padding(horizontal = 14.dp, vertical = 8.dp),
            verticalAlignment = Alignment.CenterVertically,
            horizontalArrangement = Arrangement.spacedBy(5.dp)
        ) {
            if (item.emoji.isNotEmpty()) {
                Text(
                    text = item.emoji,
                    fontSize = 14.sp
                )
            }
            Text(
                text = item.label,
                style = MaterialTheme.typography.labelLarge,
                fontWeight = if (selected) FontWeight.Bold else FontWeight.Medium,
                color = if (selected) White else OceanBlue700
            )
        }
    }
}

// ─── Small filter chip variant (used in ExploreScreen filter row) ─────────────

@Composable
fun FilterChip(
    label: String,
    active: Boolean,
    onClick: () -> Unit,
    modifier: Modifier = Modifier
) {
    val shape = RoundedCornerShape(50)

    Surface(
        modifier = modifier
            .clip(shape)
            .clickable(onClick = onClick),
        shape = shape,
        color = if (active) OceanBlue700 else MaterialTheme.colorScheme.surfaceVariant,
        shadowElevation = 0.dp
    ) {
        Row(
            modifier = Modifier.padding(horizontal = 12.dp, vertical = 6.dp),
            verticalAlignment = Alignment.CenterVertically,
            horizontalArrangement = Arrangement.spacedBy(4.dp)
        ) {
            Text(
                text = label,
                style = MaterialTheme.typography.labelMedium,
                color = if (active) White else MaterialTheme.colorScheme.onSurfaceVariant,
                fontWeight = if (active) FontWeight.SemiBold else FontWeight.Normal
            )
        }
    }
}
