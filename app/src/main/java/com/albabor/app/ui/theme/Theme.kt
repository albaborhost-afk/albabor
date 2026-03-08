package com.albabor.app.ui.theme

import android.app.Activity
import androidx.compose.foundation.isSystemInDarkTheme
import androidx.compose.material3.*
import androidx.compose.runtime.Composable
import androidx.compose.runtime.SideEffect
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.toArgb
import androidx.compose.ui.platform.LocalView
import androidx.core.view.WindowCompat

private val LightColorScheme = lightColorScheme(
    primary          = OceanBlue700,
    onPrimary        = White,
    primaryContainer = OceanBlue100,
    onPrimaryContainer = OceanBlue900,
    secondary        = Teal500,
    onSecondary      = White,
    secondaryContainer = Teal100,
    onSecondaryContainer = Color(0xFF00363D),
    tertiary         = Gold500,
    onTertiary       = White,
    tertiaryContainer = Gold100,
    onTertiaryContainer = Color(0xFF3E2000),
    error            = Error500,
    onError          = White,
    errorContainer   = Error100,
    onErrorContainer = Color(0xFF410002),
    background       = AppBackground,
    onBackground     = Gray900,
    surface          = GlassSurfaceStrong,
    onSurface        = Gray900,
    surfaceVariant   = Color(0xFFE8EEF4),
    onSurfaceVariant = Gray500,
    outline          = Gray200,
    outlineVariant   = Gray100,
    scrim            = Color(0x99000000),
    inverseSurface   = Gray900,
    inverseOnSurface = White,
    inversePrimary   = OceanBlue300,
)

private val DarkColorScheme = darkColorScheme(
    primary          = OceanBlue300,
    onPrimary        = OceanBlue900,
    primaryContainer = OceanBlue700,
    onPrimaryContainer = OceanBlue50,
    secondary        = Teal100,
    onSecondary      = Color(0xFF00363D),
    secondaryContainer = Color(0xFF004F57),
    onSecondaryContainer = Teal100,
    tertiary         = Gold100,
    onTertiary       = Color(0xFF3E2000),
    tertiaryContainer = Color(0xFF593000),
    onTertiaryContainer = Gold100,
    error            = Error100,
    onError          = Color(0xFF690005),
    errorContainer   = Color(0xFF93000A),
    onErrorContainer = Error100,
    background       = DarkBackground,
    onBackground     = Gray100,
    surface          = DarkSurface,
    onSurface        = Gray100,
    surfaceVariant   = DarkSurfaceHigh,
    onSurfaceVariant = Gray300,
    outline          = DarkBorder,
    outlineVariant   = DarkSurfaceHigh,
    scrim            = Color(0x99000000),
    inverseSurface   = Gray100,
    inverseOnSurface = Gray900,
    inversePrimary   = OceanBlue700,
)

@Composable
fun AlBaborTheme(
    darkTheme: Boolean = isSystemInDarkTheme(),
    content: @Composable () -> Unit
) {
    val colorScheme = if (darkTheme) DarkColorScheme else LightColorScheme

    val view = LocalView.current
    if (!view.isInEditMode) {
        SideEffect {
            val window = (view.context as Activity).window
            window.statusBarColor = colorScheme.surface.toArgb()
            WindowCompat.getInsetsController(window, view).isAppearanceLightStatusBars = !darkTheme
        }
    }

    MaterialTheme(
        colorScheme = colorScheme,
        typography   = AlBaborTypography,
        content      = content
    )
}
