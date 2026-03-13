package com.albabor.app.ui.theme

import androidx.annotation.DrawableRes
import androidx.compose.ui.graphics.Color
import com.albabor.app.R

data class HomeCategoryVisual(
    val key: String?,
    val label: String,
    @DrawableRes val imageRes: Int? = null,
    val accent: Color = OceanBlue700,
)

val homeCategoryVisuals = listOf(
    HomeCategoryVisual(key = "boat", label = "Bateaux", imageRes = R.drawable.category_boats, accent = OceanBlue900),
    HomeCategoryVisual(key = "jetski", label = "Jet-Ski", imageRes = R.drawable.category_jetski, accent = Teal500),
    HomeCategoryVisual(key = "engine", label = "Moteurs", imageRes = R.drawable.category_engines, accent = Gold500),
    HomeCategoryVisual(key = "parts", label = "Pièces", imageRes = R.drawable.category_parts, accent = Lavender500),
)

@DrawableRes
fun categoryImageRes(category: String?): Int = when (category) {
    "boat" -> R.drawable.category_boats
    "jetski" -> R.drawable.category_jetski
    "engine" -> R.drawable.category_engines
    "parts" -> R.drawable.category_parts
    else -> R.drawable.category_boats
}

fun categoryAccentColor(category: String?): Color = when (category) {
    "boat" -> OceanBlue900
    "jetski" -> Teal500
    "engine" -> Gold500
    "parts" -> Lavender500
    else -> OceanBlue700
}

@DrawableRes
fun categoryHeroImageRes(category: String?): Int = when (category) {
    "boat" -> R.drawable.hero_boat
    "jetski" -> R.drawable.hero_jetski
    "engine" -> R.drawable.hero_engine
    "parts" -> R.drawable.hero_parts
    else -> R.drawable.hero_boat
}
