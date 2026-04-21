package com.albabor.app.ui.components

import android.content.Context
import android.graphics.Bitmap
import android.graphics.BitmapFactory
import android.net.Uri
import androidx.compose.foundation.Canvas
import androidx.compose.foundation.background
import androidx.compose.foundation.border
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Close
import androidx.compose.material.icons.filled.Done
import androidx.compose.material.icons.filled.GridView
import androidx.compose.material.icons.filled.Refresh
import androidx.compose.material.icons.filled.Undo
import androidx.compose.material3.CircularProgressIndicator
import androidx.compose.material3.Icon
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.draw.shadow
import androidx.compose.ui.geometry.Offset
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.asImageBitmap
import androidx.compose.ui.input.pointer.pointerInput
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.compose.ui.window.Dialog
import androidx.compose.ui.window.DialogProperties
import com.albabor.app.ui.theme.OceanBlue700
import com.albabor.app.ui.theme.Teal500
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.launch
import kotlinx.coroutines.withContext
import java.io.File
import java.io.FileOutputStream
import kotlin.math.ceil
import kotlin.math.max
import kotlin.math.min
import kotlin.math.sqrt
import androidx.compose.foundation.gestures.detectDragGestures

// ─── Constants ──────────────────────────────────────────────────────────────
private const val MAX_EDITOR_DIM = 2000          // downscale input to keep memory reasonable
private const val MAX_HISTORY    = 6             // 1 original + 5 strokes

// Brush radius = percentage of min(width, height)
private val BRUSH_PERCENTS = floatArrayOf(0.04f, 0.07f, 0.12f)

// ─── Dialog ─────────────────────────────────────────────────────────────────

@Composable
fun BlurEditorDialog(
    uri: Uri,
    onSave: (Uri) -> Unit,
    onDismiss: () -> Unit,
) {
    val context = LocalContext.current
    val scope = rememberCoroutineScope()

    var currentBitmap by remember { mutableStateOf<Bitmap?>(null) }
    val history = remember { mutableStateListOf<Bitmap>() }
    var canvasVersion by remember { mutableIntStateOf(0) }   // bumped each time we mutate bitmap
    var brushLevel by remember { mutableIntStateOf(2) }       // 1=small, 2=med, 3=large
    var saving by remember { mutableStateOf(false) }

    // Load and downscale the bitmap from the URI
    LaunchedEffect(uri) {
        withContext(Dispatchers.IO) {
            val loaded = loadAndDownscale(context, uri, MAX_EDITOR_DIM)
            if (loaded != null) {
                val editable = loaded.copy(Bitmap.Config.ARGB_8888, true)
                val snapshot = editable.copy(Bitmap.Config.ARGB_8888, false)
                withContext(Dispatchers.Main) {
                    currentBitmap = editable
                    history.clear()
                    history.add(snapshot)
                }
            }
        }
    }

    Dialog(
        onDismissRequest = onDismiss,
        properties = DialogProperties(
            usePlatformDefaultWidth = false,
            dismissOnBackPress = true,
            dismissOnClickOutside = false,
        ),
    ) {
        Box(
            Modifier
                .fillMaxSize()
                .background(Color(0xFF080C16)),
        ) {
            Column(Modifier.fillMaxSize().statusBarsPadding()) {
                // ── Header ────────────────────────────────────────────────
                BlurEditorHeader(onClose = onDismiss)

                // ── Canvas ────────────────────────────────────────────────
                Box(
                    Modifier
                        .weight(1f)
                        .fillMaxWidth()
                        .padding(horizontal = 10.dp, vertical = 4.dp),
                    contentAlignment = Alignment.Center,
                ) {
                    val bm = currentBitmap
                    if (bm == null) {
                        CircularProgressIndicator(
                            color = Color.White,
                            strokeWidth = 2.5.dp,
                            modifier = Modifier.size(34.dp),
                        )
                    } else {
                        BlurPaintableCanvas(
                            bitmap = bm,
                            canvasVersion = canvasVersion,
                            brushLevel = brushLevel,
                            onStrokeDraw = {
                                canvasVersion++                         // force recomposition
                            },
                            onStrokeComplete = {
                                // Snapshot AFTER the stroke so undo can jump back
                                val snap = bm.copy(Bitmap.Config.ARGB_8888, false)
                                if (history.size >= MAX_HISTORY) {
                                    history.removeAt(1)                 // always keep original at index 0
                                }
                                history.add(snap)
                            },
                        )
                    }
                }

                // ── Toolbar ───────────────────────────────────────────────
                BlurEditorToolbar(
                    brushLevel = brushLevel,
                    onBrushLevelChange = { brushLevel = it },
                    strokeCount = history.size - 1,
                    canUndo = history.size > 1,
                    canReset = history.size > 1,
                    saving = saving,
                    onUndo = {
                        if (history.size > 1) {
                            history.removeAt(history.lastIndex)
                            val restore = history.last()
                            currentBitmap?.let { bm ->
                                bm.eraseColor(Color.Transparent.value.toInt())
                                val c = android.graphics.Canvas(bm)
                                c.drawBitmap(restore, 0f, 0f, null)
                                canvasVersion++
                            }
                        }
                    },
                    onReset = {
                        val original = history.firstOrNull() ?: return@BlurEditorToolbar
                        history.clear()
                        history.add(original)
                        currentBitmap?.let { bm ->
                            val c = android.graphics.Canvas(bm)
                            c.drawBitmap(original, 0f, 0f, null)
                            canvasVersion++
                        }
                    },
                    onSave = {
                        val bm = currentBitmap ?: return@BlurEditorToolbar
                        if (saving) return@BlurEditorToolbar
                        saving = true
                        scope.launch {
                            val newUri = withContext(Dispatchers.IO) {
                                saveBitmapToCache(context, bm)
                            }
                            saving = false
                            if (newUri != null) onSave(newUri)
                            else onDismiss()
                        }
                    },
                )
            }
        }
    }
}

// ─── Header ─────────────────────────────────────────────────────────────────

@Composable
private fun BlurEditorHeader(onClose: () -> Unit) {
    Row(
        Modifier
            .fillMaxWidth()
            .padding(horizontal = 14.dp, vertical = 12.dp),
        verticalAlignment = Alignment.CenterVertically,
    ) {
        Box(
            Modifier
                .size(34.dp)
                .clip(RoundedCornerShape(10.dp))
                .background(Brush.linearGradient(listOf(OceanBlue700, Teal500))),
            contentAlignment = Alignment.Center,
        ) {
            Icon(
                imageVector = Icons.Filled.GridView,
                contentDescription = null,
                tint = Color.White,
                modifier = Modifier.size(17.dp),
            )
        }
        Spacer(Modifier.width(10.dp))
        Column(Modifier.weight(1f)) {
            Text(
                text = "Protéger une zone",
                color = Color.White,
                fontWeight = FontWeight.SemiBold,
                style = MaterialTheme.typography.titleSmall,
            )
            Text(
                text = "Peignez sur l'immatriculation ou info privée",
                color = Color(0xFF6B8CA8),
                style = MaterialTheme.typography.labelSmall,
            )
        }
        Box(
            Modifier
                .size(34.dp)
                .clip(RoundedCornerShape(10.dp))
                .background(Color.White.copy(alpha = 0.08f))
                .clickable(onClick = onClose),
            contentAlignment = Alignment.Center,
        ) {
            Icon(
                imageVector = Icons.Filled.Close,
                contentDescription = "Fermer",
                tint = Color.White.copy(alpha = 0.75f),
                modifier = Modifier.size(18.dp),
            )
        }
    }
}

// ─── Canvas ─────────────────────────────────────────────────────────────────

@Composable
private fun BlurPaintableCanvas(
    bitmap: Bitmap,
    canvasVersion: Int,
    brushLevel: Int,
    onStrokeDraw: () -> Unit,
    onStrokeComplete: () -> Unit,
) {
    val aspect = bitmap.width.toFloat() / bitmap.height.toFloat()
    var canvasSizePx by remember { mutableStateOf(IntArray(2)) }

    val radiusCanvas = remember(brushLevel, bitmap) {
        (min(bitmap.width, bitmap.height) * BRUSH_PERCENTS[brushLevel - 1]).toInt().coerceAtLeast(8)
    }

    // Touch interpolation state
    var lastPoint by remember { mutableStateOf<Offset?>(null) }

    Box(
        Modifier
            .fillMaxWidth()
            .aspectRatio(aspect)
            .clip(RoundedCornerShape(18.dp))
            .border(1.dp, Color.White.copy(alpha = 0.08f), RoundedCornerShape(18.dp))
            .pointerInput(bitmap) {
                canvasSizePx = intArrayOf(size.width, size.height)
                detectDragGestures(
                    onDragStart = { offset ->
                        val bp = canvasToBitmap(offset, canvasSizePx, bitmap)
                        lastPoint = bp
                        pixelateBrush(bitmap, bp.x, bp.y, radiusCanvas)
                        onStrokeDraw()
                    },
                    onDrag = { change, _ ->
                        val bp = canvasToBitmap(change.position, canvasSizePx, bitmap)
                        val prev = lastPoint ?: bp
                        // Interpolate between previous and current for a smooth stroke
                        val dx = bp.x - prev.x
                        val dy = bp.y - prev.y
                        val dist = sqrt(dx * dx + dy * dy)
                        val step = max(1f, radiusCanvas * 0.35f)
                        val count = ceil(dist / step).toInt().coerceAtLeast(1)
                        for (i in 1..count) {
                            val t = i.toFloat() / count
                            pixelateBrush(bitmap, prev.x + dx * t, prev.y + dy * t, radiusCanvas)
                        }
                        lastPoint = bp
                        onStrokeDraw()
                    },
                    onDragEnd = {
                        lastPoint = null
                        onStrokeComplete()
                    },
                    onDragCancel = {
                        lastPoint = null
                        onStrokeComplete()
                    },
                )
            },
    ) {
        // Re-read canvasVersion so Canvas recomposes after each mutation
        @Suppress("UNUSED_EXPRESSION") canvasVersion
        Canvas(
            modifier = Modifier.fillMaxSize(),
        ) {
            drawImage(
                image = bitmap.asImageBitmap(),
                dstOffset = androidx.compose.ui.unit.IntOffset(0, 0),
                dstSize = androidx.compose.ui.unit.IntSize(size.width.toInt(), size.height.toInt()),
            )
        }
    }
}

// ─── Toolbar ────────────────────────────────────────────────────────────────

@Composable
private fun BlurEditorToolbar(
    brushLevel: Int,
    onBrushLevelChange: (Int) -> Unit,
    strokeCount: Int,
    canUndo: Boolean,
    canReset: Boolean,
    saving: Boolean,
    onUndo: () -> Unit,
    onReset: () -> Unit,
    onSave: () -> Unit,
) {
    Column(
        Modifier
            .fillMaxWidth()
            .background(Color.Black.copy(alpha = 0.55f))
            .navigationBarsPadding()
            .padding(horizontal = 14.dp, vertical = 10.dp),
        verticalArrangement = Arrangement.spacedBy(10.dp),
    ) {
        // ── Brush size row ─────────────────────────────────────────────────
        Row(
            verticalAlignment = Alignment.CenterVertically,
            horizontalArrangement = Arrangement.spacedBy(6.dp),
        ) {
            Text(
                "Pinceau :",
                color = Color(0xFF6B8CA8),
                style = MaterialTheme.typography.labelSmall,
            )
            BrushSizeChip(
                label = "Petit",
                dotSize = 7.dp,
                selected = brushLevel == 1,
                onClick = { onBrushLevelChange(1) },
                modifier = Modifier.weight(1f),
            )
            BrushSizeChip(
                label = "Moyen",
                dotSize = 11.dp,
                selected = brushLevel == 2,
                onClick = { onBrushLevelChange(2) },
                modifier = Modifier.weight(1f),
            )
            BrushSizeChip(
                label = "Grand",
                dotSize = 16.dp,
                selected = brushLevel == 3,
                onClick = { onBrushLevelChange(3) },
                modifier = Modifier.weight(1f),
            )
        }

        // ── Actions row ────────────────────────────────────────────────────
        Row(
            verticalAlignment = Alignment.CenterVertically,
            horizontalArrangement = Arrangement.spacedBy(8.dp),
        ) {
            ToolbarActionButton(
                icon = Icons.Filled.Undo,
                label = "Annuler",
                enabled = canUndo && !saving,
                onClick = onUndo,
            )
            ToolbarActionButton(
                icon = Icons.Filled.Refresh,
                label = "Reset",
                enabled = canReset && !saving,
                onClick = onReset,
            )
            Spacer(Modifier.weight(1f))
            if (strokeCount > 0) {
                Text(
                    text = "$strokeCount trait${if (strokeCount > 1) "s" else ""}",
                    color = Teal500,
                    style = MaterialTheme.typography.labelSmall,
                    fontWeight = FontWeight.SemiBold,
                    modifier = Modifier
                        .clip(RoundedCornerShape(999.dp))
                        .background(Teal500.copy(alpha = 0.15f))
                        .padding(horizontal = 8.dp, vertical = 4.dp),
                )
            }
            Box(
                Modifier
                    .clip(RoundedCornerShape(12.dp))
                    .background(Brush.linearGradient(listOf(OceanBlue700, Teal500)))
                    .shadow(6.dp, RoundedCornerShape(12.dp))
                    .clickable(enabled = !saving, onClick = onSave)
                    .padding(horizontal = 14.dp, vertical = 10.dp),
                contentAlignment = Alignment.Center,
            ) {
                if (saving) {
                    CircularProgressIndicator(
                        color = Color.White,
                        strokeWidth = 2.dp,
                        modifier = Modifier.size(15.dp),
                    )
                } else {
                    Row(
                        verticalAlignment = Alignment.CenterVertically,
                        horizontalArrangement = Arrangement.spacedBy(6.dp),
                    ) {
                        Icon(
                            imageVector = Icons.Filled.Done,
                            contentDescription = null,
                            tint = Color.White,
                            modifier = Modifier.size(15.dp),
                        )
                        Text(
                            "Enregistrer",
                            color = Color.White,
                            style = MaterialTheme.typography.labelMedium,
                            fontWeight = FontWeight.SemiBold,
                        )
                    }
                }
            }
        }
    }
}

@Composable
private fun BrushSizeChip(
    label: String,
    dotSize: androidx.compose.ui.unit.Dp,
    selected: Boolean,
    onClick: () -> Unit,
    modifier: Modifier = Modifier,
) {
    Box(
        modifier
            .clip(RoundedCornerShape(12.dp))
            .background(
                if (selected) Brush.linearGradient(listOf(OceanBlue700, Teal500))
                else Brush.linearGradient(listOf(Color.White.copy(alpha = 0.07f), Color.White.copy(alpha = 0.07f)))
            )
            .clickable(onClick = onClick)
            .padding(vertical = 9.dp, horizontal = 10.dp),
        contentAlignment = Alignment.Center,
    ) {
        Row(
            verticalAlignment = Alignment.CenterVertically,
            horizontalArrangement = Arrangement.spacedBy(6.dp),
        ) {
            Box(
                Modifier
                    .size(dotSize)
                    .clip(CircleShape)
                    .background(if (selected) Color.White else Color.White.copy(alpha = 0.55f)),
            )
            Text(
                label,
                color = if (selected) Color.White else Color.White.copy(alpha = 0.55f),
                style = MaterialTheme.typography.labelSmall,
                fontWeight = FontWeight.SemiBold,
            )
        }
    }
}

@Composable
private fun ToolbarActionButton(
    icon: androidx.compose.ui.graphics.vector.ImageVector,
    label: String,
    enabled: Boolean,
    onClick: () -> Unit,
) {
    Row(
        Modifier
            .clip(RoundedCornerShape(12.dp))
            .background(Color.White.copy(alpha = if (enabled) 0.07f else 0.03f))
            .clickable(enabled = enabled, onClick = onClick)
            .padding(horizontal = 12.dp, vertical = 10.dp),
        verticalAlignment = Alignment.CenterVertically,
        horizontalArrangement = Arrangement.spacedBy(6.dp),
    ) {
        Icon(
            imageVector = icon,
            contentDescription = null,
            tint = Color.White.copy(alpha = if (enabled) 0.75f else 0.3f),
            modifier = Modifier.size(15.dp),
        )
        Text(
            label,
            color = Color.White.copy(alpha = if (enabled) 0.75f else 0.3f),
            style = MaterialTheme.typography.labelSmall,
            fontWeight = FontWeight.Medium,
        )
    }
}

// ─── Helpers ─────────────────────────────────────────────────────────────────

private fun canvasToBitmap(p: Offset, canvasSize: IntArray, bitmap: Bitmap): Offset {
    if (canvasSize.size < 2 || canvasSize[0] == 0 || canvasSize[1] == 0) {
        return Offset.Zero
    }
    val sx = bitmap.width.toFloat() / canvasSize[0]
    val sy = bitmap.height.toFloat() / canvasSize[1]
    return Offset(p.x * sx, p.y * sy)
}

/**
 * Pixelate a circular brush region on the bitmap in place.
 * Each NxN block within the circle is replaced with the average color of that block.
 */
private fun pixelateBrush(bitmap: Bitmap, cx: Float, cy: Float, radius: Int) {
    val r = radius
    val x = max(0, (cx - r).toInt())
    val y = max(0, (cy - r).toInt())
    val x2 = min(bitmap.width, (cx + r).toInt())
    val y2 = min(bitmap.height, (cy + r).toInt())
    val w = x2 - x
    val h = y2 - y
    if (w <= 0 || h <= 0) return

    val pixelSize = max(7, r / 4)
    val pixels = IntArray(w * h)
    bitmap.getPixels(pixels, 0, w, x, y, w, h)
    val rSq = (r * r).toFloat()

    var py = 0
    while (py < h) {
        var px = 0
        while (px < w) {
            val bcx = (px + pixelSize / 2f)
            val bcy = (py + pixelSize / 2f)
            val ddx = (x + bcx) - cx
            val ddy = (y + bcy) - cy
            if (ddx * ddx + ddy * ddy > rSq) {
                px += pixelSize
                continue
            }
            val bh = min(pixelSize, h - py)
            val bw = min(pixelSize, w - px)
            var rSum = 0L
            var gSum = 0L
            var bSum = 0L
            var n = 0
            for (dy in 0 until bh) {
                val rowStart = (py + dy) * w + px
                for (dx in 0 until bw) {
                    val color = pixels[rowStart + dx]
                    rSum += (color shr 16) and 0xFF
                    gSum += (color shr 8) and 0xFF
                    bSum += color and 0xFF
                    n++
                }
            }
            if (n > 0) {
                val avgR = (rSum / n).toInt()
                val avgG = (gSum / n).toInt()
                val avgB = (bSum / n).toInt()
                val avgColor = (0xFF shl 24) or (avgR shl 16) or (avgG shl 8) or avgB
                for (dy in 0 until bh) {
                    val rowStart = (py + dy) * w + px
                    for (dx in 0 until bw) {
                        pixels[rowStart + dx] = avgColor
                    }
                }
            }
            px += pixelSize
        }
        py += pixelSize
    }
    bitmap.setPixels(pixels, 0, w, x, y, w, h)
}

/**
 * Load the image from the URI, downscaled to [maxDim] on the longest edge
 * to keep editor memory and interactivity reasonable.
 */
private fun loadAndDownscale(context: Context, uri: Uri, maxDim: Int): Bitmap? {
    return try {
        // 1) Read bounds to compute sample size
        val bounds = BitmapFactory.Options().apply { inJustDecodeBounds = true }
        context.contentResolver.openInputStream(uri)?.use {
            BitmapFactory.decodeStream(it, null, bounds)
        }
        var sample = 1
        val longest = max(bounds.outWidth, bounds.outHeight)
        while (longest / sample > maxDim) sample *= 2

        val opts = BitmapFactory.Options().apply {
            inSampleSize = sample
            inPreferredConfig = Bitmap.Config.ARGB_8888
        }
        context.contentResolver.openInputStream(uri)?.use {
            BitmapFactory.decodeStream(it, null, opts)
        }
    } catch (t: Throwable) {
        null
    }
}

/** Write the bitmap to a new cache file and return a file:// URI. */
private fun saveBitmapToCache(context: Context, bitmap: Bitmap): Uri? {
    return try {
        val file = File(context.cacheDir, "blurred_${System.currentTimeMillis()}.jpg")
        FileOutputStream(file).use { out ->
            bitmap.compress(Bitmap.CompressFormat.JPEG, 92, out)
        }
        Uri.fromFile(file)
    } catch (t: Throwable) {
        null
    }
}
