package com.albabor.app.ui.screens.listing

import android.net.Uri
import androidx.activity.compose.rememberLauncherForActivityResult
import androidx.activity.result.contract.ActivityResultContracts
import androidx.compose.animation.*
import androidx.compose.animation.core.*
import androidx.compose.foundation.*
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.grid.GridCells
import androidx.compose.foundation.lazy.grid.LazyVerticalGrid
import androidx.compose.foundation.lazy.grid.items
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.text.KeyboardOptions
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ArrowBack
import androidx.compose.material.icons.filled.*
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.vector.ImageVector
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.input.KeyboardType
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.compose.ui.window.Dialog
import androidx.lifecycle.compose.collectAsStateWithLifecycle
import androidx.lifecycle.viewmodel.compose.viewModel
import coil.compose.AsyncImage
import com.albabor.app.ui.theme.*
import com.albabor.app.viewmodel.CreateListingViewModel
import com.albabor.app.viewmodel.SubmitState

// ── Wilayas ───────────────────────────────────────────────────────────────────

val WILAYAS = listOf(
    "Adrar", "Alger", "Annaba", "Batna", "Béjaïa", "Biskra", "Blida",
    "Bordj Bou Arréridj", "Bouira", "Boumerdès", "Chlef", "Constantine",
    "Djelfa", "El Bayadh", "El Oued", "El Tarf", "Ghardaïa", "Guelma",
    "Illizi", "Jijel", "Khenchela", "Laghouat", "Mascara", "Médéa", "Mila",
    "Mostaganem", "M'Sila", "Naâma", "Oran", "Ouargla", "Oum El Bouaghi",
    "Relizane", "Saïda", "Sétif", "Sidi Bel Abbès", "Skikda", "Souk Ahras",
    "Tamanrasset", "Tébessa", "Tiaret", "Tindouf", "Tipaza", "Tissemsilt",
    "Tizi Ouzou", "Tlemcen"
)

// ── Entry Point ───────────────────────────────────────────────────────────────

@Composable
fun CreateListingScreen(
    onBack: () -> Unit,
    onSuccess: (listingId: Int) -> Unit = {}
) {
    val vm: CreateListingViewModel = viewModel()
    val submitState by vm.submitState.collectAsStateWithLifecycle()
    val context = LocalContext.current

    // Handle submit state dialogs
    var showSuccessDialog by remember { mutableStateOf(false) }
    var showErrorDialog   by remember { mutableStateOf<String?>(null) }

    LaunchedEffect(submitState) {
        when (val s = submitState) {
            is SubmitState.Success -> showSuccessDialog = true
            is SubmitState.Error   -> showErrorDialog = s.msg
            else                   -> {}
        }
    }

    if (showSuccessDialog) {
        SuccessDialog(
            onConfirm = {
                showSuccessDialog = false
                val listingId = (submitState as? SubmitState.Success)?.listing?.id ?: 0
                vm.resetSubmitState()
                onSuccess(listingId)
            }
        )
    }

    showErrorDialog?.let { msg ->
        ErrorDialog(
            msg       = msg,
            onDismiss = {
                showErrorDialog = null
                vm.resetSubmitState()
            }
        )
    }

    Scaffold(
        topBar = {
            CreateListingTopBar(
                step   = vm.step,
                total  = vm.totalSteps,
                onBack = {
                    if (vm.step > 1) vm.prevStep() else onBack()
                }
            )
        },
        containerColor = MaterialTheme.colorScheme.background
    ) { padding ->
        Column(
            modifier = Modifier
                .fillMaxSize()
                .padding(padding)
        ) {
            // Progress
            StepProgressBar(
                current = vm.step,
                total   = vm.totalSteps,
                modifier = Modifier.padding(horizontal = 20.dp, vertical = 8.dp)
            )

            // Step content with slide animation
            AnimatedContent(
                targetState    = vm.step,
                transitionSpec = {
                    val direction = if (targetState > initialState)
                        AnimatedContentTransitionScope.SlideDirection.Start
                    else
                        AnimatedContentTransitionScope.SlideDirection.End
                    slideIntoContainer(direction, tween(300)) togetherWith
                            slideOutOfContainer(direction, tween(300))
                },
                label = "step_content",
                modifier = Modifier.weight(1f)
            ) { step ->
                Box(
                    modifier = Modifier
                        .fillMaxSize()
                        .verticalScroll(rememberScrollState())
                        .padding(horizontal = 20.dp, vertical = 12.dp)
                ) {
                    when (step) {
                        1 -> Step1Category(vm = vm)
                        2 -> Step2Info(vm = vm)
                        3 -> Step3Price(vm = vm)
                        4 -> Step4Specs(vm = vm)
                        5 -> Step5Photos(vm = vm)
                    }
                }
            }

            // Bottom nav buttons
            BottomNavBar(
                step         = vm.step,
                total        = vm.totalSteps,
                isSubmitting = submitState is SubmitState.Loading,
                onBack       = { if (vm.step > 1) vm.prevStep() else onBack() },
                onNext       = {
                    val error = vm.validateCurrentStep()
                    if (error != null) {
                        showErrorDialog = error
                    } else if (vm.step < vm.totalSteps) {
                        vm.nextStep()
                    } else {
                        vm.submit(context)
                    }
                }
            )
        }
    }
}

// ── Top Bar ───────────────────────────────────────────────────────────────────

@OptIn(ExperimentalMaterial3Api::class)
@Composable
private fun CreateListingTopBar(step: Int, total: Int, onBack: () -> Unit) {
    TopAppBar(
        title = {
            Column {
                Text(
                    text       = "Publier une annonce",
                    style      = MaterialTheme.typography.titleMedium,
                    fontWeight = FontWeight.Bold
                )
                Text(
                    text  = "Étape $step sur $total",
                    style = MaterialTheme.typography.labelSmall,
                    color = MaterialTheme.colorScheme.onSurfaceVariant
                )
            }
        },
        navigationIcon = {
            IconButton(onClick = onBack) {
                Icon(
                    imageVector        = Icons.AutoMirrored.Filled.ArrowBack,
                    contentDescription = "Retour"
                )
            }
        },
        colors = TopAppBarDefaults.topAppBarColors(
            containerColor = MaterialTheme.colorScheme.surface
        )
    )
}

// ── Progress Bar ──────────────────────────────────────────────────────────────

@Composable
private fun StepProgressBar(current: Int, total: Int, modifier: Modifier = Modifier) {
    Row(
        modifier              = modifier.fillMaxWidth(),
        horizontalArrangement = Arrangement.spacedBy(6.dp),
        verticalAlignment     = Alignment.CenterVertically
    ) {
        repeat(total) { index ->
            val stepNum  = index + 1
            val isDone   = stepNum < current
            val isCurrent = stepNum == current

            val color = when {
                isDone    -> OceanBlue700
                isCurrent -> OceanBlue500
                else      -> Gray200
            }

            // Animated width: current step is wider
            val fraction by animateFloatAsState(
                targetValue   = if (isCurrent) 2f else 1f,
                animationSpec = tween(300),
                label         = "step_$index"
            )

            Box(
                modifier = Modifier
                    .weight(fraction)
                    .height(4.dp)
                    .clip(RoundedCornerShape(2.dp))
                    .background(color)
            )
        }
    }
}

// ── Bottom Nav ────────────────────────────────────────────────────────────────

@Composable
private fun BottomNavBar(
    step: Int,
    total: Int,
    isSubmitting: Boolean,
    onBack: () -> Unit,
    onNext: () -> Unit
) {
    Surface(
        modifier        = Modifier.fillMaxWidth(),
        shadowElevation = 8.dp,
        color           = MaterialTheme.colorScheme.surface
    ) {
        Row(
            modifier              = Modifier
                .fillMaxWidth()
                .padding(horizontal = 20.dp, vertical = 14.dp)
                .navigationBarsPadding(),
            horizontalArrangement = Arrangement.spacedBy(12.dp),
            verticalAlignment     = Alignment.CenterVertically
        ) {
            if (step > 1) {
                OutlinedButton(
                    onClick  = onBack,
                    modifier = Modifier
                        .weight(0.4f)
                        .height(50.dp),
                    shape    = RoundedCornerShape(14.dp),
                    border   = BorderStroke(1.5.dp, MaterialTheme.colorScheme.outline),
                    colors   = ButtonDefaults.outlinedButtonColors(
                        contentColor = MaterialTheme.colorScheme.onSurface
                    )
                ) {
                    Icon(
                        imageVector        = Icons.AutoMirrored.Filled.ArrowBack,
                        contentDescription = null,
                        modifier           = Modifier.size(18.dp)
                    )
                    Spacer(Modifier.width(6.dp))
                    Text("Retour", fontWeight = FontWeight.Medium)
                }
            }

            Button(
                onClick  = onNext,
                enabled  = !isSubmitting,
                modifier = Modifier
                    .weight(if (step > 1) 0.6f else 1f)
                    .height(50.dp),
                shape    = RoundedCornerShape(14.dp),
                colors   = ButtonDefaults.buttonColors(containerColor = OceanBlue700)
            ) {
                if (isSubmitting) {
                    CircularProgressIndicator(
                        modifier  = Modifier.size(20.dp),
                        color     = Color.White,
                        strokeWidth = 2.dp
                    )
                } else {
                    Text(
                        text       = if (step == total) "Publier l'annonce" else "Suivant",
                        fontWeight = FontWeight.SemiBold
                    )
                    if (step < total) {
                        Spacer(Modifier.width(6.dp))
                        Icon(
                            imageVector        = Icons.Default.ArrowForward,
                            contentDescription = null,
                            modifier           = Modifier.size(18.dp)
                        )
                    }
                }
            }
        }
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// STEP 1 – CATÉGORIE
// ─────────────────────────────────────────────────────────────────────────────

@Composable
private fun Step1Category(vm: CreateListingViewModel) {
    Column(
        modifier            = Modifier.fillMaxWidth(),
        verticalArrangement = Arrangement.spacedBy(20.dp)
    ) {
        StepHeader(
            title    = "Catégorie",
            subtitle = "Quel type d'article souhaitez-vous vendre ?"
        )

        val categories = listOf(
            CategoryItem("boat",   "Bateaux",  Icons.Default.DirectionsBoat, OceanBlue700, OceanBlue50),
            CategoryItem("jetski", "Jet-Skis", Icons.Default.Pool,           Teal500,      Teal50),
            CategoryItem("engine", "Moteurs",  Icons.Default.Settings,       Gold500,      Gold50),
            CategoryItem("parts",  "Pièces",   Icons.Default.Build,          Gray700,      Gray100),
        )

        // 2x2 grid
        Column(verticalArrangement = Arrangement.spacedBy(12.dp)) {
            categories.chunked(2).forEach { row ->
                Row(
                    modifier              = Modifier.fillMaxWidth(),
                    horizontalArrangement = Arrangement.spacedBy(12.dp)
                ) {
                    row.forEach { cat ->
                        CategoryCard(
                            item       = cat,
                            isSelected = vm.category == cat.key,
                            onSelect   = { vm.category = cat.key },
                            modifier   = Modifier.weight(1f)
                        )
                    }
                    if (row.size == 1) Spacer(Modifier.weight(1f))
                }
            }
        }
    }
}

private data class CategoryItem(
    val key: String,
    val label: String,
    val icon: ImageVector,
    val color: Color,
    val bg: Color
)

@Composable
private fun CategoryCard(
    item: CategoryItem,
    isSelected: Boolean,
    onSelect: () -> Unit,
    modifier: Modifier = Modifier
) {
    val borderColor = if (isSelected) item.color else MaterialTheme.colorScheme.outline
    val bgColor     = if (isSelected) item.bg    else MaterialTheme.colorScheme.surface
    val borderWidth = if (isSelected) 2.dp       else 1.dp

    Surface(
        modifier = modifier
            .aspectRatio(1f)
            .clickable(onClick = onSelect),
        shape    = RoundedCornerShape(16.dp),
        color    = bgColor,
        border   = BorderStroke(borderWidth, borderColor),
        shadowElevation = if (isSelected) 4.dp else 1.dp
    ) {
        Column(
            modifier              = Modifier
                .fillMaxSize()
                .padding(16.dp),
            verticalArrangement   = Arrangement.Center,
            horizontalAlignment   = Alignment.CenterHorizontally
        ) {
            Box(
                modifier         = Modifier
                    .size(56.dp)
                    .clip(RoundedCornerShape(14.dp))
                    .background(item.color.copy(alpha = if (isSelected) 0.15f else 0.08f)),
                contentAlignment = Alignment.Center
            ) {
                Icon(
                    imageVector        = item.icon,
                    contentDescription = null,
                    tint               = item.color,
                    modifier           = Modifier.size(30.dp)
                )
            }
            Spacer(Modifier.height(12.dp))
            Text(
                text       = item.label,
                style      = MaterialTheme.typography.titleSmall,
                fontWeight = if (isSelected) FontWeight.Bold else FontWeight.Medium,
                color      = if (isSelected) item.color else MaterialTheme.colorScheme.onSurface,
                textAlign  = TextAlign.Center
            )
            if (isSelected) {
                Spacer(Modifier.height(6.dp))
                Icon(
                    imageVector        = Icons.Default.CheckCircle,
                    contentDescription = null,
                    tint               = item.color,
                    modifier           = Modifier.size(18.dp)
                )
            }
        }
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// STEP 2 – INFORMATIONS
// ─────────────────────────────────────────────────────────────────────────────

@Composable
private fun Step2Info(vm: CreateListingViewModel) {
    Column(
        modifier            = Modifier.fillMaxWidth(),
        verticalArrangement = Arrangement.spacedBy(20.dp)
    ) {
        StepHeader(
            title    = "Informations",
            subtitle = "Décrivez votre annonce"
        )

        FormCard {
            FormField(
                label       = "Titre de l'annonce *",
                value       = vm.title,
                onValueChange = { vm.title = it },
                placeholder = "ex. Bayliner 185 2019 en excellent état",
                icon        = Icons.Default.Title,
                maxLength   = 100
            )

            HorizontalDivider(
                modifier = Modifier.padding(vertical = 4.dp),
                color    = MaterialTheme.colorScheme.outline
            )

            FormField(
                label         = "Description *",
                value         = vm.description,
                onValueChange = { vm.description = it },
                placeholder   = "Décrivez l'état, l'historique, les équipements inclus...",
                icon          = Icons.Default.Notes,
                singleLine    = false,
                minLines      = 4,
                maxLines      = 10,
                maxLength     = 2000
            )
        }

        FormCard {
            WilayaDropdown(
                selected = vm.wilaya,
                onSelect = { vm.wilaya = it }
            )
        }

        FormCard {
            SelectorGroup(
                title   = "État *",
                options = listOf(
                    "new"            to "Neuf",
                    "like_new"       to "Comme neuf",
                    "good"           to "Bon état",
                    "average"        to "État moyen",
                    "needs_revision" to "À réviser"
                ),
                selected  = vm.condition,
                onSelect  = { vm.condition = it }
            )
        }

        FormCard {
            SelectorGroup(
                title   = "Type d'offre *",
                options = listOf(
                    "negotiable" to "Négociable",
                    "fixed"      to "Prix ferme",
                    "free"       to "Gratuit"
                ),
                selected  = vm.offerType,
                onSelect  = { vm.offerType = it }
            )
        }
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// STEP 3 – PRIX
// ─────────────────────────────────────────────────────────────────────────────

@Composable
private fun Step3Price(vm: CreateListingViewModel) {
    Column(
        modifier            = Modifier.fillMaxWidth(),
        verticalArrangement = Arrangement.spacedBy(20.dp)
    ) {
        StepHeader(
            title    = "Prix",
            subtitle = "Définissez le prix de votre annonce"
        )

        // Currency toggle
        FormCard {
            Text(
                text       = "Devise",
                style      = MaterialTheme.typography.labelMedium,
                color      = MaterialTheme.colorScheme.onSurfaceVariant,
                fontWeight = FontWeight.Medium
            )
            Spacer(Modifier.height(10.dp))
            Row(
                modifier              = Modifier
                    .fillMaxWidth()
                    .clip(RoundedCornerShape(12.dp))
                    .background(MaterialTheme.colorScheme.surfaceVariant),
                horizontalArrangement = Arrangement.spacedBy(0.dp)
            ) {
                listOf("DZD" to "DA – Dinar algérien", "EUR" to "€ – Euro").forEach { (value, label) ->
                    val isSelected = vm.currency == value
                    Box(
                        modifier = Modifier
                            .weight(1f)
                            .clip(RoundedCornerShape(12.dp))
                            .background(if (isSelected) OceanBlue700 else Color.Transparent)
                            .clickable { vm.currency = value }
                            .padding(vertical = 12.dp),
                        contentAlignment = Alignment.Center
                    ) {
                        Text(
                            text       = label,
                            color      = if (isSelected) Color.White
                                         else MaterialTheme.colorScheme.onSurfaceVariant,
                            fontWeight = if (isSelected) FontWeight.Bold else FontWeight.Normal,
                            style      = MaterialTheme.typography.bodyMedium
                        )
                    }
                }
            }
        }

        // Price input
        FormCard {
            Text(
                text       = "Prix *",
                style      = MaterialTheme.typography.labelMedium,
                color      = MaterialTheme.colorScheme.onSurfaceVariant,
                fontWeight = FontWeight.Medium
            )
            Spacer(Modifier.height(10.dp))
            OutlinedTextField(
                value         = vm.price,
                onValueChange = { if (it.all { c -> c.isDigit() || c == '.' }) vm.price = it },
                modifier      = Modifier.fillMaxWidth(),
                placeholder   = { Text("0") },
                prefix        = {
                    Icon(
                        imageVector        = Icons.Default.Sell,
                        contentDescription = null,
                        tint               = OceanBlue700,
                        modifier           = Modifier.size(18.dp)
                    )
                },
                suffix = {
                    Text(
                        text       = if (vm.currency == "EUR") "€" else "DA",
                        color      = OceanBlue700,
                        fontWeight = FontWeight.Bold
                    )
                },
                keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Decimal),
                shape           = RoundedCornerShape(12.dp),
                singleLine      = true
            )

            // Price preview
            if (vm.price.isNotBlank() && vm.price.toDoubleOrNull() != null) {
                Spacer(Modifier.height(8.dp))
                val formattedPreview = if (vm.currency == "EUR") {
                    "%.0f €".format(vm.price.toDouble())
                } else {
                    "%,.0f DA".format(vm.price.toDouble()).replace(",", " ")
                }
                Surface(
                    shape = RoundedCornerShape(8.dp),
                    color = OceanBlue50
                ) {
                    Row(
                        modifier          = Modifier.padding(horizontal = 12.dp, vertical = 8.dp),
                        verticalAlignment = Alignment.CenterVertically
                    ) {
                        Icon(
                            imageVector        = Icons.Default.Info,
                            contentDescription = null,
                            tint               = OceanBlue700,
                            modifier           = Modifier.size(14.dp)
                        )
                        Spacer(Modifier.width(6.dp))
                        Text(
                            text  = "Sera affiché : $formattedPreview",
                            style = MaterialTheme.typography.bodySmall,
                            color = OceanBlue700
                        )
                    }
                }
            }
        }

        // Mediation toggle
        FormCard {
            Row(
                modifier              = Modifier.fillMaxWidth(),
                horizontalArrangement = Arrangement.SpaceBetween,
                verticalAlignment     = Alignment.CenterVertically
            ) {
                Column(modifier = Modifier.weight(1f)) {
                    Row(
                        verticalAlignment     = Alignment.CenterVertically,
                        horizontalArrangement = Arrangement.spacedBy(8.dp)
                    ) {
                        Icon(
                            imageVector        = Icons.Default.Shield,
                            contentDescription = null,
                            tint               = OceanBlue700,
                            modifier           = Modifier.size(20.dp)
                        )
                        Text(
                            text       = "Médiation activée",
                            style      = MaterialTheme.typography.titleSmall,
                            fontWeight = FontWeight.SemiBold,
                            color      = MaterialTheme.colorScheme.onSurface
                        )
                    }
                    Spacer(Modifier.height(4.dp))
                    Text(
                        text  = "Sécurisez vos transactions avec la médiation AlBabor",
                        style = MaterialTheme.typography.bodySmall,
                        color = MaterialTheme.colorScheme.onSurfaceVariant
                    )
                }
                Spacer(Modifier.width(12.dp))
                Switch(
                    checked         = vm.mediationEnabled,
                    onCheckedChange = { vm.mediationEnabled = it },
                    colors          = SwitchDefaults.colors(
                        checkedThumbColor  = Color.White,
                        checkedTrackColor  = OceanBlue700,
                        uncheckedThumbColor = Gray400,
                        uncheckedTrackColor = Gray200
                    )
                )
            }
        }
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// STEP 4 – CARACTÉRISTIQUES
// ─────────────────────────────────────────────────────────────────────────────

@Composable
private fun Step4Specs(vm: CreateListingViewModel) {
    Column(
        modifier            = Modifier.fillMaxWidth(),
        verticalArrangement = Arrangement.spacedBy(20.dp)
    ) {
        StepHeader(
            title    = "Caractéristiques",
            subtitle = "Détaillez les spécifications techniques (optionnel)"
        )

        when (vm.category) {
            "boat", "jetski" -> BoatJetSkiSpecs(vm = vm)
            "engine"         -> EngineSpecs(vm = vm)
            "parts"          -> PartsSpecs(vm = vm)
            else             -> {
                Surface(
                    shape = RoundedCornerShape(12.dp),
                    color = OceanBlue50
                ) {
                    Row(
                        modifier          = Modifier.padding(16.dp),
                        verticalAlignment = Alignment.CenterVertically
                    ) {
                        Icon(
                            Icons.Default.Info,
                            contentDescription = null,
                            tint = OceanBlue700
                        )
                        Spacer(Modifier.width(10.dp))
                        Text(
                            text  = "Sélectionnez une catégorie à l'étape 1 pour voir les champs disponibles.",
                            style = MaterialTheme.typography.bodySmall,
                            color = OceanBlue700
                        )
                    }
                }
            }
        }
    }
}

@Composable
private fun BoatJetSkiSpecs(vm: CreateListingViewModel) {
    // Général
    SpecsSectionHeader(title = "Général", icon = Icons.Default.Info)
    FormCard {
        FormField(
            label         = "Année de construction",
            value         = vm.year,
            onValueChange = { vm.year = it },
            placeholder   = "ex. 2019",
            icon          = Icons.Default.CalendarToday,
            keyboardType  = KeyboardType.Number
        )
        HorizontalDivider(modifier = Modifier.padding(vertical = 4.dp), color = MaterialTheme.colorScheme.outline)
        FormField(
            label         = "Marque",
            value         = vm.brand,
            onValueChange = { vm.brand = it },
            placeholder   = "ex. Bayliner, Yamaha...",
            icon          = Icons.Default.LocalOffer
        )
        HorizontalDivider(modifier = Modifier.padding(vertical = 4.dp), color = MaterialTheme.colorScheme.outline)
        FormField(
            label         = "Modèle",
            value         = vm.model,
            onValueChange = { vm.model = it },
            placeholder   = "ex. 185 Bowrider",
            icon          = Icons.Default.DirectionsBoat
        )
        HorizontalDivider(modifier = Modifier.padding(vertical = 4.dp), color = MaterialTheme.colorScheme.outline)
        FormField(
            label         = "Couleur",
            value         = vm.color,
            onValueChange = { vm.color = it },
            placeholder   = "ex. Blanc, Bleu marine...",
            icon          = Icons.Default.Palette
        )
    }

    // Dimensions
    SpecsSectionHeader(title = "Dimensions", icon = Icons.Default.Straighten)
    FormCard {
        FormField(
            label         = "Longueur (mètres)",
            value         = vm.length,
            onValueChange = { vm.length = it },
            placeholder   = "ex. 5.5",
            icon          = Icons.Default.Straighten,
            keyboardType  = KeyboardType.Decimal
        )
    }

    // Motorisation
    SpecsSectionHeader(title = "Motorisation", icon = Icons.Default.Speed)
    FormCard {
        FormField(
            label         = "Puissance totale (CV)",
            value         = vm.power,
            onValueChange = { vm.power = it },
            placeholder   = "ex. 150",
            icon          = Icons.Default.Speed,
            keyboardType  = KeyboardType.Number
        )
        HorizontalDivider(modifier = Modifier.padding(vertical = 4.dp), color = MaterialTheme.colorScheme.outline)
        SelectorGroup(
            title   = "Type de moteur",
            options = listOf(
                "inboard"  to "In-bord",
                "outboard" to "Hors-bord",
                "jet"      to "Jet"
            ),
            selected = vm.engineType,
            onSelect = { vm.engineType = it },
            compact  = true
        )
        HorizontalDivider(modifier = Modifier.padding(vertical = 4.dp), color = MaterialTheme.colorScheme.outline)
        FormField(
            label         = "Nombre de moteurs",
            value         = vm.nbEngines,
            onValueChange = { vm.nbEngines = it },
            placeholder   = "ex. 1",
            icon          = Icons.Default.Numbers,
            keyboardType  = KeyboardType.Number
        )
    }
}

@Composable
private fun EngineSpecs(vm: CreateListingViewModel) {
    SpecsSectionHeader(title = "Informations moteur", icon = Icons.Default.Settings)
    FormCard {
        FormField(
            label         = "Marque",
            value         = vm.engineBrand,
            onValueChange = { vm.engineBrand = it },
            placeholder   = "ex. Mercury, Suzuki, Yamaha...",
            icon          = Icons.Default.LocalOffer
        )
        HorizontalDivider(modifier = Modifier.padding(vertical = 4.dp), color = MaterialTheme.colorScheme.outline)
        FormField(
            label         = "Puissance (CV)",
            value         = vm.power,
            onValueChange = { vm.power = it },
            placeholder   = "ex. 90",
            icon          = Icons.Default.Speed,
            keyboardType  = KeyboardType.Number
        )
        HorizontalDivider(modifier = Modifier.padding(vertical = 4.dp), color = MaterialTheme.colorScheme.outline)
        SelectorGroup(
            title   = "Type de moteur",
            options = listOf(
                "inboard"  to "In-bord",
                "outboard" to "Hors-bord",
                "jet"      to "Jet"
            ),
            selected = vm.engineType,
            onSelect = { vm.engineType = it },
            compact  = true
        )
    }
}

@Composable
private fun PartsSpecs(vm: CreateListingViewModel) {
    SpecsSectionHeader(title = "Informations pièce", icon = Icons.Default.Build)
    FormCard {
        FormField(
            label         = "Marque",
            value         = vm.partBrand,
            onValueChange = { vm.partBrand = it },
            placeholder   = "ex. Mercury, Suzuki...",
            icon          = Icons.Default.LocalOffer
        )
        HorizontalDivider(modifier = Modifier.padding(vertical = 4.dp), color = MaterialTheme.colorScheme.outline)
        FormField(
            label         = "Compatible avec",
            value         = vm.compatibleWith,
            onValueChange = { vm.compatibleWith = it },
            placeholder   = "ex. Yamaha F115, 2015-2020",
            icon          = Icons.Default.Link
        )
    }
}

@Composable
private fun SpecsSectionHeader(title: String, icon: ImageVector) {
    Row(
        verticalAlignment     = Alignment.CenterVertically,
        horizontalArrangement = Arrangement.spacedBy(8.dp)
    ) {
        Icon(
            imageVector        = icon,
            contentDescription = null,
            tint               = OceanBlue700,
            modifier           = Modifier.size(18.dp)
        )
        Text(
            text       = title,
            style      = MaterialTheme.typography.titleSmall,
            fontWeight = FontWeight.Bold,
            color      = MaterialTheme.colorScheme.onSurface
        )
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// STEP 5 – PHOTOS
// ─────────────────────────────────────────────────────────────────────────────

@Composable
private fun Step5Photos(vm: CreateListingViewModel) {
    val launcher = rememberLauncherForActivityResult(
        contract = ActivityResultContracts.GetMultipleContents()
    ) { uris ->
        vm.addImages(uris)
    }

    Column(
        modifier            = Modifier.fillMaxWidth(),
        verticalArrangement = Arrangement.spacedBy(20.dp)
    ) {
        StepHeader(
            title    = "Photos",
            subtitle = "Ajoutez jusqu'à 10 photos de votre annonce"
        )

        // Info banner
        Surface(
            shape = RoundedCornerShape(12.dp),
            color = OceanBlue50,
            border = BorderStroke(1.dp, OceanBlue100)
        ) {
            Row(
                modifier          = Modifier.padding(14.dp),
                verticalAlignment = Alignment.CenterVertically
            ) {
                Icon(
                    imageVector        = Icons.Default.PhotoCamera,
                    contentDescription = null,
                    tint               = OceanBlue700,
                    modifier           = Modifier.size(20.dp)
                )
                Spacer(Modifier.width(10.dp))
                Column {
                    Text(
                        text       = "Conseils photos",
                        style      = MaterialTheme.typography.labelMedium,
                        fontWeight = FontWeight.SemiBold,
                        color      = OceanBlue700
                    )
                    Text(
                        text  = "La 1ère photo sera l'image principale. Prenez des photos de qualité sous différents angles.",
                        style = MaterialTheme.typography.bodySmall,
                        color = OceanBlue700
                    )
                }
            }
        }

        // Photo count indicator
        Row(
            modifier              = Modifier.fillMaxWidth(),
            horizontalArrangement = Arrangement.SpaceBetween,
            verticalAlignment     = Alignment.CenterVertically
        ) {
            Text(
                text  = "${vm.selectedImages.size} / 10 photos",
                style = MaterialTheme.typography.labelMedium,
                color = MaterialTheme.colorScheme.onSurfaceVariant
            )
            if (vm.selectedImages.isNotEmpty()) {
                TextButton(onClick = { /* Clear all handled per-item */ }) { }
            }
        }

        // Photo grid
        // Using a fixed-height box since LazyVerticalGrid inside verticalScroll is not recommended.
        // We use a FlowRow-style manual grid instead.
        val allSlots = vm.selectedImages + listOf<Uri?>(null) // null = "add" slot
        val displaySlots = allSlots.take(if (vm.selectedImages.size < 10) allSlots.size else 10)
        val rows = displaySlots.chunked(3)

        Column(verticalArrangement = Arrangement.spacedBy(10.dp)) {
            rows.forEach { row ->
                Row(
                    modifier              = Modifier.fillMaxWidth(),
                    horizontalArrangement = Arrangement.spacedBy(10.dp)
                ) {
                    row.forEach { uri ->
                        if (uri == null) {
                            // Add button
                            AddPhotoSlot(
                                onClick  = { launcher.launch("image/*") },
                                modifier = Modifier.weight(1f)
                            )
                        } else {
                            val index = vm.selectedImages.indexOf(uri)
                            PhotoSlot(
                                uri        = uri,
                                isPrimary  = index == 0,
                                onRemove   = { vm.removeImage(uri) },
                                modifier   = Modifier.weight(1f)
                            )
                        }
                    }
                    // Fill remaining slots in last row
                    repeat(3 - row.size) {
                        Spacer(Modifier.weight(1f))
                    }
                }
            }
        }

        // Add more button if < 10
        if (vm.selectedImages.size < 10) {
            OutlinedButton(
                onClick = { launcher.launch("image/*") },
                modifier = Modifier.fillMaxWidth().height(50.dp),
                shape    = RoundedCornerShape(14.dp),
                border   = BorderStroke(1.5.dp, OceanBlue700),
                colors   = ButtonDefaults.outlinedButtonColors(contentColor = OceanBlue700)
            ) {
                Icon(
                    imageVector        = Icons.Default.AddPhotoAlternate,
                    contentDescription = null,
                    modifier           = Modifier.size(20.dp)
                )
                Spacer(Modifier.width(8.dp))
                Text(
                    text       = "Ajouter des photos",
                    fontWeight = FontWeight.SemiBold
                )
            }
        }
    }
}

@Composable
private fun PhotoSlot(
    uri: Uri,
    isPrimary: Boolean,
    onRemove: () -> Unit,
    modifier: Modifier = Modifier
) {
    Box(
        modifier = modifier.aspectRatio(1f)
    ) {
        AsyncImage(
            model            = uri,
            contentDescription = null,
            contentScale     = ContentScale.Crop,
            modifier         = Modifier
                .fillMaxSize()
                .clip(RoundedCornerShape(12.dp))
        )

        // Primary badge
        if (isPrimary) {
            Surface(
                modifier = Modifier
                    .align(Alignment.BottomStart)
                    .padding(6.dp),
                shape    = RoundedCornerShape(6.dp),
                color    = OceanBlue700
            ) {
                Text(
                    text     = "Principale",
                    color    = Color.White,
                    style    = MaterialTheme.typography.labelSmall,
                    modifier = Modifier.padding(horizontal = 6.dp, vertical = 3.dp)
                )
            }
        }

        // Remove button
        IconButton(
            onClick  = onRemove,
            modifier = Modifier
                .align(Alignment.TopEnd)
                .padding(4.dp)
                .size(26.dp)
                .clip(CircleShape)
                .background(Color.Black.copy(alpha = 0.6f))
        ) {
            Icon(
                imageVector        = Icons.Default.Close,
                contentDescription = "Supprimer",
                tint               = Color.White,
                modifier           = Modifier.size(14.dp)
            )
        }
    }
}

@Composable
private fun AddPhotoSlot(onClick: () -> Unit, modifier: Modifier = Modifier) {
    Box(
        modifier         = modifier
            .aspectRatio(1f)
            .clip(RoundedCornerShape(12.dp))
            .background(MaterialTheme.colorScheme.surfaceVariant)
            .border(
                width = 2.dp,
                color = OceanBlue300,
                shape = RoundedCornerShape(12.dp)
            )
            .clickable(onClick = onClick),
        contentAlignment = Alignment.Center
    ) {
        Column(
            horizontalAlignment = Alignment.CenterHorizontally,
            verticalArrangement = Arrangement.spacedBy(6.dp)
        ) {
            Icon(
                imageVector        = Icons.Default.AddPhotoAlternate,
                contentDescription = "Ajouter photo",
                tint               = OceanBlue500,
                modifier           = Modifier.size(28.dp)
            )
            Text(
                text  = "Ajouter",
                style = MaterialTheme.typography.labelSmall,
                color = OceanBlue500
            )
        }
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// SHARED COMPONENTS
// ─────────────────────────────────────────────────────────────────────────────

@Composable
private fun StepHeader(title: String, subtitle: String) {
    Column(verticalArrangement = Arrangement.spacedBy(4.dp)) {
        Text(
            text       = title,
            style      = MaterialTheme.typography.headlineSmall,
            fontWeight = FontWeight.Bold,
            color      = MaterialTheme.colorScheme.onBackground
        )
        Text(
            text  = subtitle,
            style = MaterialTheme.typography.bodyMedium,
            color = MaterialTheme.colorScheme.onSurfaceVariant
        )
    }
}

@Composable
private fun FormCard(content: @Composable ColumnScope.() -> Unit) {
    Surface(
        modifier        = Modifier.fillMaxWidth(),
        shape           = RoundedCornerShape(16.dp),
        color           = MaterialTheme.colorScheme.surface,
        shadowElevation = 1.dp,
        border          = BorderStroke(1.dp, MaterialTheme.colorScheme.outline)
    ) {
        Column(
            modifier            = Modifier.padding(16.dp),
            verticalArrangement = Arrangement.spacedBy(12.dp),
            content             = content
        )
    }
}

@Composable
private fun FormField(
    label: String,
    value: String,
    onValueChange: (String) -> Unit,
    placeholder: String = "",
    icon: ImageVector? = null,
    singleLine: Boolean = true,
    minLines: Int = 1,
    maxLines: Int = 1,
    maxLength: Int = Int.MAX_VALUE,
    keyboardType: KeyboardType = KeyboardType.Text
) {
    Column(verticalArrangement = Arrangement.spacedBy(6.dp)) {
        Text(
            text       = label,
            style      = MaterialTheme.typography.labelMedium,
            color      = MaterialTheme.colorScheme.onSurfaceVariant,
            fontWeight = FontWeight.Medium
        )
        OutlinedTextField(
            value         = value,
            onValueChange = { if (it.length <= maxLength) onValueChange(it) },
            modifier      = Modifier.fillMaxWidth(),
            placeholder   = {
                Text(
                    text  = placeholder,
                    color = MaterialTheme.colorScheme.onSurfaceVariant.copy(alpha = 0.6f)
                )
            },
            leadingIcon = icon?.let {
                {
                    Icon(
                        imageVector        = it,
                        contentDescription = null,
                        tint               = OceanBlue700,
                        modifier           = Modifier.size(18.dp)
                    )
                }
            },
            singleLine      = singleLine,
            minLines        = minLines,
            maxLines        = if (singleLine) 1 else maxLines,
            keyboardOptions = KeyboardOptions(keyboardType = keyboardType),
            shape           = RoundedCornerShape(12.dp)
        )
        if (maxLength < Int.MAX_VALUE) {
            Text(
                text      = "${value.length} / $maxLength",
                style     = MaterialTheme.typography.labelSmall,
                color     = if (value.length >= maxLength) Error500
                            else MaterialTheme.colorScheme.onSurfaceVariant,
                modifier  = Modifier.align(Alignment.End)
            )
        }
    }
}

@Composable
private fun WilayaDropdown(selected: String, onSelect: (String) -> Unit) {
    var expanded by remember { mutableStateOf(false) }

    Column(verticalArrangement = Arrangement.spacedBy(6.dp)) {
        Text(
            text       = "Wilaya *",
            style      = MaterialTheme.typography.labelMedium,
            color      = MaterialTheme.colorScheme.onSurfaceVariant,
            fontWeight = FontWeight.Medium
        )
        ExposedDropdownMenuBox(
            expanded         = expanded,
            onExpandedChange = { expanded = it }
        ) {
            OutlinedTextField(
                value         = selected.ifBlank { "Sélectionnez une wilaya" },
                onValueChange = {},
                readOnly      = true,
                modifier      = Modifier
                    .menuAnchor()
                    .fillMaxWidth(),
                leadingIcon   = {
                    Icon(
                        imageVector        = Icons.Default.LocationOn,
                        contentDescription = null,
                        tint               = OceanBlue700,
                        modifier           = Modifier.size(18.dp)
                    )
                },
                trailingIcon  = {
                    ExposedDropdownMenuDefaults.TrailingIcon(expanded = expanded)
                },
                shape         = RoundedCornerShape(12.dp),
                textStyle     = if (selected.isBlank())
                    MaterialTheme.typography.bodyMedium.copy(
                        color = MaterialTheme.colorScheme.onSurfaceVariant.copy(alpha = 0.6f)
                    )
                else MaterialTheme.typography.bodyMedium
            )
            ExposedDropdownMenu(
                expanded         = expanded,
                onDismissRequest = { expanded = false }
            ) {
                WILAYAS.forEach { wilaya ->
                    DropdownMenuItem(
                        text     = { Text(wilaya) },
                        onClick  = {
                            onSelect(wilaya)
                            expanded = false
                        },
                        leadingIcon = if (selected == wilaya) {
                            { Icon(Icons.Default.Check, null, tint = OceanBlue700, modifier = Modifier.size(16.dp)) }
                        } else null
                    )
                }
            }
        }
    }
}

@Composable
private fun SelectorGroup(
    title: String,
    options: List<Pair<String, String>>,
    selected: String,
    onSelect: (String) -> Unit,
    compact: Boolean = false
) {
    Column(verticalArrangement = Arrangement.spacedBy(8.dp)) {
        Text(
            text       = title,
            style      = MaterialTheme.typography.labelMedium,
            color      = MaterialTheme.colorScheme.onSurfaceVariant,
            fontWeight = FontWeight.Medium
        )
        if (compact) {
            // Single row for fewer options
            Row(
                modifier              = Modifier.fillMaxWidth(),
                horizontalArrangement = Arrangement.spacedBy(8.dp)
            ) {
                options.forEach { (value, label) ->
                    SelectorChip(
                        label      = label,
                        isSelected = selected == value,
                        onSelect   = { onSelect(value) },
                        modifier   = Modifier.weight(1f)
                    )
                }
            }
        } else {
            // Wrap
            val rows = options.chunked(3)
            rows.forEach { row ->
                Row(
                    modifier              = Modifier.fillMaxWidth(),
                    horizontalArrangement = Arrangement.spacedBy(8.dp)
                ) {
                    row.forEach { (value, label) ->
                        SelectorChip(
                            label      = label,
                            isSelected = selected == value,
                            onSelect   = { onSelect(value) },
                            modifier   = Modifier.weight(1f)
                        )
                    }
                    repeat(3 - row.size) { Spacer(Modifier.weight(1f)) }
                }
            }
        }
    }
}

@Composable
private fun SelectorChip(
    label: String,
    isSelected: Boolean,
    onSelect: () -> Unit,
    modifier: Modifier = Modifier
) {
    Surface(
        modifier = modifier.clickable(onClick = onSelect),
        shape    = RoundedCornerShape(10.dp),
        color    = if (isSelected) OceanBlue700 else MaterialTheme.colorScheme.surfaceVariant,
        border   = if (isSelected) null
                   else BorderStroke(1.dp, MaterialTheme.colorScheme.outline)
    ) {
        Text(
            text       = label,
            color      = if (isSelected) Color.White else MaterialTheme.colorScheme.onSurface,
            fontWeight = if (isSelected) FontWeight.SemiBold else FontWeight.Normal,
            style      = MaterialTheme.typography.labelMedium,
            textAlign  = TextAlign.Center,
            modifier   = Modifier
                .padding(vertical = 10.dp, horizontal = 6.dp)
                .fillMaxWidth()
        )
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// DIALOGS
// ─────────────────────────────────────────────────────────────────────────────

@Composable
private fun SuccessDialog(onConfirm: () -> Unit) {
    Dialog(onDismissRequest = {}) {
        Surface(
            shape = RoundedCornerShape(20.dp),
            color = MaterialTheme.colorScheme.surface
        ) {
            Column(
                modifier              = Modifier.padding(32.dp),
                horizontalAlignment   = Alignment.CenterHorizontally,
                verticalArrangement   = Arrangement.spacedBy(16.dp)
            ) {
                Box(
                    modifier         = Modifier
                        .size(72.dp)
                        .clip(CircleShape)
                        .background(
                            Brush.radialGradient(listOf(Success100, Success500.copy(alpha = 0.2f)))
                        ),
                    contentAlignment = Alignment.Center
                ) {
                    Icon(
                        imageVector        = Icons.Default.CheckCircle,
                        contentDescription = null,
                        tint               = Success500,
                        modifier           = Modifier.size(40.dp)
                    )
                }
                Text(
                    text       = "Annonce publiée !",
                    style      = MaterialTheme.typography.headlineSmall,
                    fontWeight = FontWeight.Bold,
                    color      = MaterialTheme.colorScheme.onSurface,
                    textAlign  = TextAlign.Center
                )
                Text(
                    text      = "Votre annonce a été soumise avec succès et sera publiée après vérification.",
                    style     = MaterialTheme.typography.bodyMedium,
                    color     = MaterialTheme.colorScheme.onSurfaceVariant,
                    textAlign = TextAlign.Center
                )
                Button(
                    onClick  = onConfirm,
                    modifier = Modifier.fillMaxWidth().height(50.dp),
                    shape    = RoundedCornerShape(14.dp),
                    colors   = ButtonDefaults.buttonColors(containerColor = OceanBlue700)
                ) {
                    Text("Voir l'annonce", fontWeight = FontWeight.SemiBold)
                }
            }
        }
    }
}

@Composable
private fun ErrorDialog(msg: String, onDismiss: () -> Unit) {
    AlertDialog(
        onDismissRequest = onDismiss,
        icon = {
            Icon(
                imageVector        = Icons.Default.ErrorOutline,
                contentDescription = null,
                tint               = Error500,
                modifier           = Modifier.size(36.dp)
            )
        },
        title = {
            Text(
                text       = "Une erreur s'est produite",
                fontWeight = FontWeight.Bold
            )
        },
        text = {
            Text(
                text  = msg,
                style = MaterialTheme.typography.bodyMedium
            )
        },
        confirmButton = {
            Button(
                onClick = onDismiss,
                colors  = ButtonDefaults.buttonColors(containerColor = OceanBlue700)
            ) {
                Text("Réessayer")
            }
        },
        containerColor = MaterialTheme.colorScheme.surface,
        shape          = RoundedCornerShape(20.dp)
    )
}
