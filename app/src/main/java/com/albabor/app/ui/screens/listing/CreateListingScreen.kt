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
import androidx.compose.ui.res.painterResource
import com.albabor.app.R
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
import kotlinx.coroutines.delay

// ── Pays méditerranéens ────────────────────────────────────────────────────────

private data class CountryEntry(val flag: String, val name: String)

private val MEDITERRANEAN_COUNTRIES = listOf(
    CountryEntry("🇩🇿", "Algérie"),
    CountryEntry("🇹🇳", "Tunisie"),
    CountryEntry("🇲🇦", "Maroc"),
    CountryEntry("🇪🇬", "Égypte"),
    CountryEntry("🇪🇸", "Espagne"),
    CountryEntry("🇫🇷", "France"),
    CountryEntry("🇮🇹", "Italie"),
    CountryEntry("🇬🇷", "Grèce"),
    CountryEntry("🇭🇷", "Croatie"),
    CountryEntry("🇸🇮", "Slovénie"),
    CountryEntry("🇹🇷", "Turquie"),
    CountryEntry("🇱🇧", "Liban"),
    CountryEntry("🇲🇹", "Malte"),
    CountryEntry("🇲🇨", "Monaco"),
)

private val IMMATRICULATION_OPTIONS = listOf(
    "Algérien",
    "Polonais",
    "Espagnol",
    "Français",
    "Italien",
    "Autre",
)

private val PROPULSION_OPTIONS = listOf(
    "Hors-Bord",
    "In-bord",
)

private val FUEL_OPTIONS = listOf(
    "Essence",
    "Diesel",
    "Électrique",
    "Hybride",
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
    var showSuccessDialog  by remember { mutableStateOf(false) }
    var showErrorDialog    by remember { mutableStateOf<String?>(null) }
    var validationMessage  by remember { mutableStateOf<String?>(null) }

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

            // Step content + snackbar overlay + bottom nav — all inside a Box
            Box(modifier = Modifier.weight(1f)) {
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
                    modifier = Modifier.fillMaxSize()
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

                // Validation snackbar — floats at the bottom of the step content area
                ValidationSnackbar(
                    message   = validationMessage,
                    onDismiss = { validationMessage = null },
                    modifier  = Modifier.align(Alignment.BottomCenter)
                )
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
                        validationMessage = error
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
            CategoryItem("boat",   "Bateaux",  R.drawable.category_boats,   OceanBlue700, OceanBlue50),
            CategoryItem("jetski", "Jet-Skis", R.drawable.category_jetski,  Teal500,      Teal50),
            CategoryItem("engine", "Moteurs",  R.drawable.category_engines, Gold500,      Gold50),
            CategoryItem("parts",  "Pièces",   R.drawable.category_parts,   Gray700,      Gray100),
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
    @androidx.annotation.DrawableRes val imageRes: Int,
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
    Surface(
        modifier = modifier
            .aspectRatio(0.85f)
            .clickable(onClick = onSelect),
        shape    = RoundedCornerShape(20.dp),
        color    = Color.White,
        border   = if (isSelected) BorderStroke(2.5.dp, item.color) else BorderStroke(1.dp, Gray200),
        shadowElevation = if (isSelected) 8.dp else 2.dp
    ) {
        Box(Modifier.fillMaxSize()) {
            // Selected check badge top-right
            if (isSelected) {
                Box(
                    Modifier.align(Alignment.TopEnd).padding(8.dp)
                        .size(24.dp)
                        .clip(CircleShape)
                        .background(item.color),
                    contentAlignment = Alignment.Center
                ) {
                    Icon(Icons.Default.Check, null, tint = Color.White, modifier = Modifier.size(14.dp))
                }
            }

            Column(
                Modifier.fillMaxSize().padding(12.dp),
                verticalArrangement = Arrangement.Center,
                horizontalAlignment = Alignment.CenterHorizontally
            ) {
                Image(
                    painter            = painterResource(id = item.imageRes),
                    contentDescription = item.label,
                    modifier           = Modifier.size(100.dp),
                    contentScale       = ContentScale.Fit
                )
                Spacer(Modifier.height(10.dp))
                Text(
                    text       = item.label,
                    style      = MaterialTheme.typography.titleSmall,
                    fontWeight = FontWeight.Bold,
                    color      = if (isSelected) item.color else Gray900,
                    textAlign  = TextAlign.Center
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

            HorizontalDivider(color = Gray100)

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
            CountryPicker(
                selected = vm.wilaya,
                onSelect = { vm.wilaya = it }
            )
            HorizontalDivider(color = Gray100)
            FormField(
                label         = "Ville / Région",
                value         = vm.ville,
                onValueChange = { vm.ville = it },
                placeholder   = "Ex: Alger, Oran, Tunis, Paris...",
                icon          = Icons.Default.LocationCity,
                maxLength     = 100
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
            subtitle = "Définissez le prix et les conditions de votre annonce"
        )

        // Currency toggle
        FormCard {
            Text(
                text       = "Devise",
                style      = MaterialTheme.typography.labelLarge,
                color      = Gray900,
                fontWeight = FontWeight.SemiBold
            )
            Spacer(Modifier.height(10.dp))
            Row(
                modifier              = Modifier
                    .fillMaxWidth()
                    .clip(RoundedCornerShape(14.dp))
                    .background(Gray100),
                horizontalArrangement = Arrangement.spacedBy(0.dp)
            ) {
                listOf("DZD" to "DA – Dinar algérien", "EUR" to "€ – Euro").forEach { (value, label) ->
                    val isSelected = vm.currency == value
                    Box(
                        modifier = Modifier
                            .weight(1f)
                            .clip(RoundedCornerShape(14.dp))
                            .background(if (isSelected) OceanBlue700 else Color.Transparent)
                            .clickable { vm.currency = value }
                            .padding(vertical = 14.dp),
                        contentAlignment = Alignment.Center
                    ) {
                        Text(
                            text       = label,
                            color      = if (isSelected) Color.White
                                         else Gray700,
                            fontWeight = if (isSelected) FontWeight.Bold else FontWeight.Normal,
                            style      = MaterialTheme.typography.bodyMedium
                        )
                    }
                }
            }
        }

        FormCard {
            SelectorGroup(
                title   = "État *",
                options = listOf(
                    "jamais_utilise" to "Neuf",
                    "comme_neuf"     to "Comme neuf",
                    "bon_etat"       to "Bon état",
                    "etat_moyen"     to "État moyen",
                    "a_reviser"      to "À réviser"
                ),
                selected  = vm.condition,
                onSelect  = { vm.condition = it }
            )
        }

        FormCard {
            SelectorGroup(
                title   = "Type d'offre *",
                options = listOf(
                    "negociable" to "Négociable",
                    "fix"        to "Prix ferme",
                    "offert"     to "Gratuit"
                ),
                selected  = vm.offerType,
                onSelect  = { vm.offerType = it },
                compact   = true
            )
        }

        // Price input
        FormCard {
            Text(
                text       = "Prix *",
                style      = MaterialTheme.typography.labelLarge,
                color      = Gray900,
                fontWeight = FontWeight.SemiBold
            )
            Spacer(Modifier.height(10.dp))
            OutlinedTextField(
                value         = vm.price,
                onValueChange = { if (it.all { c -> c.isDigit() || c == '.' }) vm.price = it },
                modifier      = Modifier.fillMaxWidth(),
                placeholder   = {
                    Text(
                        text  = "0",
                        color = Gray400
                    )
                },
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
                singleLine      = true,
                colors          = OutlinedTextFieldDefaults.colors(
                    focusedTextColor         = Gray900,
                    unfocusedTextColor       = Gray900,
                    disabledTextColor        = Gray900,
                    focusedPlaceholderColor  = Gray400,
                    unfocusedPlaceholderColor = Gray400,
                    focusedContainerColor    = Color.White,
                    unfocusedContainerColor  = Color.White,
                    focusedBorderColor       = OceanBlue500,
                    unfocusedBorderColor     = Gray300,
                    cursorColor              = OceanBlue700
                )
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
                            color      = Gray900
                        )
                    }
                    Spacer(Modifier.height(4.dp))
                    Text(
                        text  = "Sécurisez vos transactions avec la médiation AlBabor",
                        style = MaterialTheme.typography.bodySmall,
                        color = Gray500
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
        HorizontalDivider(color = Gray100)
        FormField(
            label         = "Fabricant",
            value         = vm.manufacturer,
            onValueChange = { vm.manufacturer = it },
            placeholder   = "ex. Bayliner, Yamaha...",
            icon          = Icons.Default.LocalOffer
        )
        HorizontalDivider(color = Gray100)
        FormField(
            label         = "Modèle",
            value         = vm.model,
            onValueChange = { vm.model = it },
            placeholder   = "ex. 185 Bowrider",
            icon          = Icons.Default.DirectionsBoat
        )
        HorizontalDivider(color = Gray100)
        FormField(
            label         = "Couleur",
            value         = vm.color,
            onValueChange = { vm.color = it },
            placeholder   = "ex. Blanc, Bleu marine...",
            icon          = Icons.Default.Palette
        )
        HorizontalDivider(color = Gray100)
        SelectorGroup(
            title   = "Immatriculation",
            options = IMMATRICULATION_OPTIONS.map { it to it },
            selected = vm.registration,
            onSelect = { vm.registration = it }
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
        HorizontalDivider(color = Gray100)
        FormField(
            label         = "Largeur (mètres)",
            value         = vm.width,
            onValueChange = { vm.width = it },
            placeholder   = "ex. 2.3",
            icon          = Icons.Default.Straighten,
            keyboardType  = KeyboardType.Decimal
        )
        HorizontalDivider(color = Gray100)
        FormField(
            label         = "Tonnage (T)",
            value         = vm.tonnage,
            onValueChange = { vm.tonnage = it },
            placeholder   = "ex. 1.5",
            icon          = Icons.Default.Scale,
            keyboardType  = KeyboardType.Decimal
        )

        if (vm.category == "boat") {
            HorizontalDivider(color = Gray100)
            FormField(
                label         = "Tirant d'eau (mètres)",
                value         = vm.waterDraft,
                onValueChange = { vm.waterDraft = it },
                placeholder   = "ex. 0.8",
                icon          = Icons.Default.Water,
                keyboardType  = KeyboardType.Decimal
            )
            HorizontalDivider(color = Gray100)
            FormField(
                label         = "Tirant d'air (mètres)",
                value         = vm.airDraft,
                onValueChange = { vm.airDraft = it },
                placeholder   = "ex. 3.2",
                icon          = Icons.Default.Height,
                keyboardType  = KeyboardType.Decimal
            )
        }
    }

    // Motorisation
    SpecsSectionHeader(title = "Motorisation", icon = Icons.Default.Speed)
    FormCard {
        FormField(
            label         = "Marque du moteur",
            value         = vm.engineBrand,
            onValueChange = { vm.engineBrand = it },
            placeholder   = "ex. Yamaha, Mercury...",
            icon          = Icons.Default.Settings
        )
        HorizontalDivider(color = Gray100)
        SelectorGroup(
            title   = "Propulsion",
            options = PROPULSION_OPTIONS.map { it to it },
            selected = vm.propulsion,
            onSelect = { vm.propulsion = it },
            compact  = true
        )
        HorizontalDivider(color = Gray100)
        SelectorGroup(
            title   = "Type de carburant",
            options = FUEL_OPTIONS.map { it to it },
            selected = vm.fuelType,
            onSelect = { vm.fuelType = it }
        )
        HorizontalDivider(color = Gray100)
        FormField(
            label         = "Nombre de moteurs",
            value         = vm.engineCount,
            onValueChange = { vm.engineCount = it },
            placeholder   = "ex. 1",
            icon          = Icons.Default.Numbers,
            keyboardType  = KeyboardType.Number
        )
        HorizontalDivider(color = Gray100)
        FormField(
            label         = "Puissance par moteur (CV)",
            value         = vm.powerPerEngine,
            onValueChange = { vm.powerPerEngine = it },
            placeholder   = "ex. 150",
            icon          = Icons.Default.Speed,
            keyboardType  = KeyboardType.Number
        )
        HorizontalDivider(color = Gray100)
        FormField(
            label         = "Puissance totale (CV)",
            value         = vm.totalPower,
            onValueChange = {},
            placeholder   = "Calcul automatique",
            icon          = Icons.Default.Bolt,
            keyboardType  = KeyboardType.Number,
            readOnly      = true,
            supportingText = "Calculée à partir du nombre de moteurs et de la puissance par moteur."
        )
        HorizontalDivider(color = Gray100)
        FormField(
            label         = "Nombre d'heures",
            value         = vm.engineHours,
            onValueChange = { vm.engineHours = it },
            placeholder   = "ex. 250",
            icon          = Icons.Default.Schedule,
            keyboardType  = KeyboardType.Number
        )
    }

    if (vm.category == "boat") {
        SpecsSectionHeader(title = "Réservoirs", icon = Icons.Default.LocalGasStation)
        FormCard {
            FormField(
                label         = "Nombre de réservoirs",
                value         = vm.reservoirCount,
                onValueChange = { vm.reservoirCount = it },
                placeholder   = "ex. 1",
                icon          = Icons.Default.Numbers,
                keyboardType  = KeyboardType.Number
            )
            HorizontalDivider(color = Gray100)
            FormField(
                label         = "Carburant (L)",
                value         = vm.fuelTankCapacity,
                onValueChange = { vm.fuelTankCapacity = it },
                placeholder   = "ex. 200",
                icon          = Icons.Default.LocalGasStation,
                keyboardType  = KeyboardType.Number
            )
            HorizontalDivider(color = Gray100)
            FormField(
                label         = "Eau douce (L)",
                value         = vm.freshWaterTankCapacity,
                onValueChange = { vm.freshWaterTankCapacity = it },
                placeholder   = "ex. 100",
                icon          = Icons.Default.WaterDrop,
                keyboardType  = KeyboardType.Number
            )
            HorizontalDivider(color = Gray100)
            FormField(
                label         = "Stockage (L)",
                value         = vm.storageCapacity,
                onValueChange = { vm.storageCapacity = it },
                placeholder   = "ex. 50",
                icon          = Icons.Default.Inventory2,
                keyboardType  = KeyboardType.Number
            )
            HorizontalDivider(color = Gray100)
            FormField(
                label          = "Capacité totale (L)",
                value          = vm.totalReservoirCapacity,
                onValueChange  = {},
                placeholder    = "Calcul automatique",
                icon           = Icons.Default.Calculate,
                keyboardType   = KeyboardType.Number,
                readOnly       = true,
                supportingText = "Somme automatique carburant + eau douce + stockage."
            )
        }

        SpecsSectionHeader(title = "Aménagements", icon = Icons.Default.Home)
        FormCard {
            FormField(
                label         = "Couchettes",
                value         = vm.berthCount,
                onValueChange = { vm.berthCount = it },
                placeholder   = "ex. 2",
                icon          = Icons.Default.Hotel,
                keyboardType  = KeyboardType.Number
            )
            HorizontalDivider(color = Gray100)
            FormField(
                label         = "Cabines",
                value         = vm.cabinCount,
                onValueChange = { vm.cabinCount = it },
                placeholder   = "ex. 1",
                icon          = Icons.Default.MeetingRoom,
                keyboardType  = KeyboardType.Number
            )
            HorizontalDivider(color = Gray100)
            FormField(
                label         = "Sanitaires",
                value         = vm.sanitaryCount,
                onValueChange = { vm.sanitaryCount = it },
                placeholder   = "ex. 1",
                icon          = Icons.Default.Shower,
                keyboardType  = KeyboardType.Number
            )
            HorizontalDivider(color = Gray100)
            FormField(
                label         = "Cuisines",
                value         = vm.kitchenCount,
                onValueChange = { vm.kitchenCount = it },
                placeholder   = "ex. 1",
                icon          = Icons.Default.Restaurant,
                keyboardType  = KeyboardType.Number
            )
        }
    }

    SpecsSectionHeader(title = "Extras", icon = Icons.Default.Add)
    FormCard {
        SelectorGroup(
            title   = "Remorque",
            options = listOf("oui" to "Oui, incluse", "non" to "Non"),
            selected = vm.trailerIncluded,
            onSelect = { vm.trailerIncluded = it },
            compact  = true
        )

        if (vm.trailerIncluded == "oui") {
            HorizontalDivider(color = Gray100)
            FormField(
                label         = "Marque de la remorque",
                value         = vm.trailerBrand,
                onValueChange = { vm.trailerBrand = it },
                placeholder   = "ex. Satellite...",
                icon          = Icons.Default.LocalShipping
            )
        }

        HorizontalDivider(color = Gray100)
        SelectorGroup(
            title   = "Place au port",
            options = listOf("oui" to "Oui", "non" to "Non"),
            selected = vm.berthAvailable,
            onSelect = { vm.berthAvailable = it },
            compact  = true
        )

        if (vm.berthAvailable == "oui") {
            HorizontalDivider(color = Gray100)
            FormField(
                label         = "Adresse du port",
                value         = vm.portAddress,
                onValueChange = { vm.portAddress = it },
                placeholder   = "ex. Port de plaisance d'Oran",
                icon          = Icons.Default.Place
            )
            HorizontalDivider(color = Gray100)
            FormField(
                label         = "Longueur place (mètres)",
                value         = vm.berthLength,
                onValueChange = { vm.berthLength = it },
                placeholder   = "ex. 8.0",
                icon          = Icons.Default.Straighten,
                keyboardType  = KeyboardType.Decimal
            )
            HorizontalDivider(color = Gray100)
            FormField(
                label         = "Largeur place (mètres)",
                value         = vm.berthWidth,
                onValueChange = { vm.berthWidth = it },
                placeholder   = "ex. 3.0",
                icon          = Icons.Default.Straighten,
                keyboardType  = KeyboardType.Decimal
            )
        }
    }
}

@Composable
private fun EngineSpecs(vm: CreateListingViewModel) {
    SpecsSectionHeader(title = "Général", icon = Icons.Default.Info)
    FormCard {
        FormField(
            label         = "Année de construction",
            value         = vm.year,
            onValueChange = { vm.year = it },
            placeholder   = "ex. 2020",
            icon          = Icons.Default.CalendarToday,
            keyboardType  = KeyboardType.Number
        )
        HorizontalDivider(color = Gray100)
        FormField(
            label         = "Fabricant",
            value         = vm.manufacturer,
            onValueChange = { vm.manufacturer = it },
            placeholder   = "ex. Mercury, Suzuki...",
            icon          = Icons.Default.LocalOffer
        )
        HorizontalDivider(color = Gray100)
        FormField(
            label         = "Modèle",
            value         = vm.model,
            onValueChange = { vm.model = it },
            placeholder   = "ex. F150",
            icon          = Icons.Default.Settings
        )
    }

    SpecsSectionHeader(title = "Motorisation", icon = Icons.Default.Settings)
    FormCard {
        FormField(
            label         = "Marque du moteur",
            value         = vm.engineBrand,
            onValueChange = { vm.engineBrand = it },
            placeholder   = "ex. Mercury, Suzuki, Yamaha...",
            icon          = Icons.Default.Settings
        )
        HorizontalDivider(color = Gray100)
        SelectorGroup(
            title   = "Propulsion",
            options = PROPULSION_OPTIONS.map { it to it },
            selected = vm.propulsion,
            onSelect = { vm.propulsion = it },
            compact  = true
        )
        HorizontalDivider(color = Gray100)
        SelectorGroup(
            title   = "Type de carburant",
            options = FUEL_OPTIONS.map { it to it },
            selected = vm.fuelType,
            onSelect = { vm.fuelType = it }
        )
        HorizontalDivider(color = Gray100)
        FormField(
            label         = "Puissance (CV)",
            value         = vm.powerPerEngine,
            onValueChange = { vm.powerPerEngine = it },
            placeholder   = "ex. 90",
            icon          = Icons.Default.Speed,
            keyboardType  = KeyboardType.Number
        )
        HorizontalDivider(color = Gray100)
        FormField(
            label         = "Nombre d'heures",
            value         = vm.engineHours,
            onValueChange = { vm.engineHours = it },
            placeholder   = "ex. 250",
            icon          = Icons.Default.Schedule,
            keyboardType  = KeyboardType.Number
        )
        HorizontalDivider(color = Gray100)
        FormField(
            label         = "Cylindrée (cc)",
            value         = vm.cylinders,
            onValueChange = { vm.cylinders = it },
            placeholder   = "ex. 2670",
            icon          = Icons.Default.Tune,
            keyboardType  = KeyboardType.Number
        )
    }
}

@Composable
private fun PartsSpecs(vm: CreateListingViewModel) {
    SpecsSectionHeader(title = "Informations pièce", icon = Icons.Default.Build)
    FormCard {
        FormField(
            label         = "Type de pièce",
            value         = vm.partType,
            onValueChange = { vm.partType = it },
            placeholder   = "ex. Hélice, filtre...",
            icon          = Icons.Default.Category
        )
        HorizontalDivider(color = Gray100)
        FormField(
            label         = "Marque",
            value         = vm.partBrand,
            onValueChange = { vm.partBrand = it },
            placeholder   = "ex. Mercury, Suzuki...",
            icon          = Icons.Default.LocalOffer
        )
        HorizontalDivider(color = Gray100)
        FormField(
            label         = "Compatible avec",
            value         = vm.compatibleWith,
            onValueChange = { vm.compatibleWith = it },
            placeholder   = "ex. Yamaha F115, 2015-2020",
            icon          = Icons.Default.Link
        )
        HorizontalDivider(color = Gray100)
        FormField(
            label         = "Référence / N° de pièce",
            value         = vm.partNumber,
            onValueChange = { vm.partNumber = it },
            placeholder   = "ex. 6D8-WS24A-00-00",
            icon          = Icons.Default.Tag
        )
    }
}

@Composable
private fun SpecsSectionHeader(title: String, icon: ImageVector) {
    Row(
        verticalAlignment     = Alignment.CenterVertically,
        horizontalArrangement = Arrangement.spacedBy(10.dp)
    ) {
        Box(
            Modifier.size(32.dp).clip(RoundedCornerShape(10.dp))
                .background(OceanBlue50),
            contentAlignment = Alignment.Center
        ) {
            Icon(icon, null, tint = OceanBlue700, modifier = Modifier.size(18.dp))
        }
        Text(
            text       = title,
            style      = MaterialTheme.typography.titleSmall,
            fontWeight = FontWeight.Bold,
            color      = Gray900
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
        shape           = RoundedCornerShape(20.dp),
        color           = Color.White,
        shadowElevation = 4.dp
    ) {
        Column(
            modifier            = Modifier.padding(18.dp),
            verticalArrangement = Arrangement.spacedBy(14.dp),
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
    keyboardType: KeyboardType = KeyboardType.Text,
    readOnly: Boolean = false,
    supportingText: String? = null
) {
    Column(verticalArrangement = Arrangement.spacedBy(8.dp)) {
        Text(
            text       = label,
            style      = MaterialTheme.typography.labelLarge,
            color      = Gray900,
            fontWeight = FontWeight.SemiBold
        )
        OutlinedTextField(
            value         = value,
            onValueChange = { if (it.length <= maxLength) onValueChange(it) },
            modifier      = Modifier.fillMaxWidth(),
            readOnly      = readOnly,
            placeholder   = {
                Text(
                    text  = placeholder,
                    color = Gray400,
                    style = MaterialTheme.typography.bodyMedium
                )
            },
            leadingIcon = icon?.let {
                {
                    Icon(
                        imageVector        = it,
                        contentDescription = null,
                        tint               = OceanBlue500,
                        modifier           = Modifier.size(20.dp)
                    )
                }
            },
            singleLine      = singleLine,
            minLines        = minLines,
            maxLines        = if (singleLine) 1 else maxLines,
            keyboardOptions = KeyboardOptions(keyboardType = keyboardType),
            shape           = RoundedCornerShape(14.dp),
            colors          = OutlinedTextFieldDefaults.colors(
                focusedTextColor        = Gray900,
                unfocusedTextColor      = Gray900,
                disabledTextColor       = Gray900,
                focusedPlaceholderColor = Gray400,
                unfocusedPlaceholderColor = Gray400,
                disabledPlaceholderColor = Gray400,
                focusedBorderColor      = OceanBlue500,
                unfocusedBorderColor    = Gray200,
                disabledBorderColor     = Gray200,
                cursorColor             = OceanBlue700,
                focusedContainerColor   = if (readOnly) OceanBlue50 else Color.White,
                unfocusedContainerColor = if (readOnly) OceanBlue50 else AppBackground
            )
        )
        supportingText?.let {
            Text(
                text  = it,
                style = MaterialTheme.typography.bodySmall,
                color = Gray500
            )
        }
        if (maxLength < Int.MAX_VALUE) {
            Text(
                text      = "${value.length} / $maxLength",
                style     = MaterialTheme.typography.labelSmall,
                color     = if (value.length >= maxLength) Error500 else Gray400,
                modifier  = Modifier.align(Alignment.End)
            )
        }
    }
}

@Composable
private fun CountryPicker(selected: String, onSelect: (String) -> Unit) {
    var showPicker by remember { mutableStateOf(false) }

    Column(verticalArrangement = Arrangement.spacedBy(6.dp)) {
        Text(
            text       = "Pays *",
            style      = MaterialTheme.typography.labelLarge,
            color      = Gray900,
            fontWeight = FontWeight.SemiBold
        )

        // Trigger field
        Box(modifier = Modifier.fillMaxWidth()) {
            val selectedFlag = MEDITERRANEAN_COUNTRIES.find { it.name == selected }?.flag ?: ""
            OutlinedTextField(
                value         = selected.ifBlank { "" },
                onValueChange = {},
                readOnly      = true,
                modifier      = Modifier.fillMaxWidth(),
                placeholder   = { Text("Sélectionnez un pays", fontSize = 14.sp) },
                leadingIcon   = {
                    Text(
                        text     = if (selected.isNotBlank()) selectedFlag else "🌍",
                        fontSize = 20.sp,
                        modifier = Modifier.padding(start = 4.dp)
                    )
                },
                trailingIcon  = {
                    Icon(Icons.Default.KeyboardArrowDown, null, tint = OceanBlue700)
                },
                shape = RoundedCornerShape(12.dp),
            )
            Box(modifier = Modifier.matchParentSize().clickable { showPicker = true })
        }
    }

    // ── Country picker dialog ─────────────────────────────────────────────────
    if (showPicker) {
        AlertDialog(
            onDismissRequest = { showPicker = false },
            containerColor   = White,
            shape            = RoundedCornerShape(24.dp),
            title = {
                Column(
                    modifier            = Modifier.fillMaxWidth(),
                    horizontalAlignment = Alignment.CenterHorizontally
                ) {
                    Text("🌍", fontSize = 36.sp)
                    Spacer(Modifier.height(8.dp))
                    Text(
                        text       = "Choisissez un pays",
                        fontWeight = FontWeight.Bold,
                        fontSize   = 18.sp,
                        color      = Gray900
                    )
                    Text(
                        text      = "Mer Méditerranée — 20 pays disponibles",
                        style     = MaterialTheme.typography.bodySmall,
                        color     = Gray500,
                        textAlign = TextAlign.Center
                    )
                }
            },
            text = {
                Column(
                    modifier            = Modifier.verticalScroll(rememberScrollState()),
                    verticalArrangement = Arrangement.spacedBy(8.dp)
                ) {
                    MEDITERRANEAN_COUNTRIES.chunked(3).forEach { row ->
                        Row(
                            modifier              = Modifier.fillMaxWidth(),
                            horizontalArrangement = Arrangement.spacedBy(8.dp)
                        ) {
                            row.forEach { country ->
                                val isSelected = selected == country.name
                                Surface(
                                    modifier        = Modifier
                                        .weight(1f)
                                        .clickable {
                                            onSelect(country.name)
                                            showPicker = false
                                        },
                                    shape           = RoundedCornerShape(14.dp),
                                    color           = if (isSelected) OceanBlue700 else Color.White,
                                    border          = BorderStroke(
                                        if (isSelected) 2.dp else 1.dp,
                                        if (isSelected) OceanBlue700 else Gray200
                                    ),
                                    shadowElevation = if (isSelected) 4.dp else 1.dp
                                ) {
                                    Column(
                                        modifier            = Modifier
                                            .padding(vertical = 10.dp, horizontal = 4.dp)
                                            .fillMaxWidth(),
                                        horizontalAlignment = Alignment.CenterHorizontally,
                                        verticalArrangement = Arrangement.spacedBy(4.dp)
                                    ) {
                                        Text(country.flag, fontSize = 26.sp)
                                        Text(
                                            text      = country.name,
                                            fontSize  = 10.sp,
                                            fontWeight = if (isSelected) FontWeight.Bold else FontWeight.Medium,
                                            color     = if (isSelected) Color.White else Gray700,
                                            textAlign = TextAlign.Center,
                                            maxLines  = 2
                                        )
                                    }
                                }
                            }
                            repeat(3 - row.size) { Spacer(Modifier.weight(1f)) }
                        }
                    }
                }
            },
            confirmButton = {
                TextButton(onClick = { showPicker = false }) {
                    Text("Fermer", color = OceanBlue700, fontWeight = FontWeight.SemiBold)
                }
            }
        )
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
    Column(verticalArrangement = Arrangement.spacedBy(10.dp)) {
        Text(
            text       = title,
            style      = MaterialTheme.typography.labelLarge,
            color      = Gray900,
            fontWeight = FontWeight.SemiBold
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
        shape    = RoundedCornerShape(12.dp),
        color    = if (isSelected) OceanBlue700 else Color.White,
        border   = BorderStroke(if (isSelected) 2.dp else 1.dp, if (isSelected) OceanBlue700 else Gray200),
        shadowElevation = if (isSelected) 4.dp else 0.dp
    ) {
        Row(
            Modifier.padding(vertical = 11.dp, horizontal = 8.dp).fillMaxWidth(),
            horizontalArrangement = Arrangement.Center,
            verticalAlignment = Alignment.CenterVertically
        ) {
            if (isSelected) {
                Icon(Icons.Default.Check, null, tint = Color.White, modifier = Modifier.size(14.dp))
                Spacer(Modifier.width(4.dp))
            }
            Text(
                text       = label,
                color      = if (isSelected) Color.White else Gray700,
                fontWeight = if (isSelected) FontWeight.Bold else FontWeight.Medium,
                style      = MaterialTheme.typography.labelMedium,
                textAlign  = TextAlign.Center
            )
        }
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// VALIDATION SNACKBAR
// ─────────────────────────────────────────────────────────────────────────────

@Composable
private fun ValidationSnackbar(
    message: String?,
    onDismiss: () -> Unit,
    modifier: Modifier = Modifier
) {
    // Auto-dismiss after 3 seconds whenever a message is shown
    LaunchedEffect(message) {
        if (message != null) {
            delay(3000L)
            onDismiss()
        }
    }

    AnimatedVisibility(
        visible = message != null,
        enter   = slideInVertically(
            initialOffsetY = { fullHeight -> fullHeight },
            animationSpec  = tween(durationMillis = 320, easing = FastOutSlowInEasing)
        ) + fadeIn(animationSpec = tween(200)),
        exit    = slideOutVertically(
            targetOffsetY = { fullHeight -> fullHeight },
            animationSpec = tween(durationMillis = 250, easing = FastOutLinearInEasing)
        ) + fadeOut(animationSpec = tween(150)),
        modifier = modifier.padding(horizontal = 16.dp, vertical = 12.dp)
    ) {
        Surface(
            modifier = Modifier
                .fillMaxWidth()
                .clickable(onClick = onDismiss),
            shape           = RoundedCornerShape(50.dp),
            color           = Gold500,
            shadowElevation = 8.dp,
            tonalElevation  = 0.dp
        ) {
            Row(
                modifier          = Modifier.padding(horizontal = 18.dp, vertical = 14.dp),
                verticalAlignment = Alignment.CenterVertically
            ) {
                Icon(
                    imageVector        = Icons.Default.Warning,
                    contentDescription = null,
                    tint               = Color.White,
                    modifier           = Modifier.size(20.dp)
                )
                Spacer(Modifier.width(12.dp))
                Text(
                    text       = message ?: "",
                    style      = MaterialTheme.typography.bodyMedium,
                    fontWeight = FontWeight.SemiBold,
                    color      = Color.White,
                    modifier   = Modifier.weight(1f)
                )
                Spacer(Modifier.width(8.dp))
                Icon(
                    imageVector        = Icons.Default.Close,
                    contentDescription = "Fermer",
                    tint               = Color.White.copy(alpha = 0.8f),
                    modifier           = Modifier.size(16.dp)
                )
            }
        }
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
