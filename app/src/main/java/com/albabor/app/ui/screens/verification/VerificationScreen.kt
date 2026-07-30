package com.albabor.app.ui.screens.verification

import android.net.Uri
import androidx.activity.compose.rememberLauncherForActivityResult
import androidx.activity.result.contract.ActivityResultContracts
import androidx.compose.animation.*
import androidx.compose.foundation.*
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.shape.*
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ArrowBack
import androidx.compose.material.icons.automirrored.filled.ManageSearch
import androidx.compose.material.icons.automirrored.filled.Send
import androidx.compose.material.icons.automirrored.filled.TrendingUp
import androidx.compose.material.icons.filled.*
import androidx.compose.material.icons.outlined.*
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.*
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.*
import androidx.compose.ui.graphics.vector.ImageVector
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.unit.*
import androidx.lifecycle.ViewModel
import androidx.lifecycle.compose.collectAsStateWithLifecycle
import androidx.lifecycle.viewModelScope
import androidx.lifecycle.viewmodel.compose.viewModel
import androidx.navigation.NavController
import coil.compose.AsyncImage
import com.albabor.app.data.model.User
import com.albabor.app.data.repository.ProfileRepository
import com.albabor.app.ui.theme.*
import kotlinx.coroutines.flow.*
import kotlinx.coroutines.launch

// ─── ViewModel ───────────────────────────────────────────────────────────────

class VerificationViewModel : ViewModel() {
    private val repo = ProfileRepository()

    sealed class SubmitState {
        object Idle    : SubmitState()
        object Loading : SubmitState()
        object Success : SubmitState()
        data class Error(val msg: String) : SubmitState()
    }

    private val _user         = MutableStateFlow<User?>(null)
    val user: StateFlow<User?> = _user.asStateFlow()

    private val _submitState         = MutableStateFlow<SubmitState>(SubmitState.Idle)
    val submitState: StateFlow<SubmitState> = _submitState.asStateFlow()

    var selectedImageUri by mutableStateOf<Uri?>(null)

    val verificationStatus: String
        get() = _user.value?.verificationStatus ?: "none"

    init { loadProfile() }

    fun loadProfile() {
        viewModelScope.launch {
            repo.getProfile().onSuccess { _user.value = it }
        }
    }

    fun submitVerification(context: android.content.Context) {
        val uri = selectedImageUri ?: return
        viewModelScope.launch {
            _submitState.value = SubmitState.Loading
            try {
                val bytes = context.contentResolver.openInputStream(uri)?.use { it.readBytes() }
                    ?: throw Exception("Impossible de lire le fichier")
                val mime = context.contentResolver.getType(uri) ?: "image/jpeg"
                repo.uploadVerification(bytes, mime).fold(
                    onSuccess = {
                        _submitState.value = SubmitState.Success
                        loadProfile()
                    },
                    onFailure = { _submitState.value = SubmitState.Error(it.message ?: "Erreur") }
                )
            } catch (e: Exception) {
                _submitState.value = SubmitState.Error(e.message ?: "Erreur")
            }
        }
    }

    fun resetState() { _submitState.value = SubmitState.Idle }
}

// ─── Screen ──────────────────────────────────────────────────────────────────

@Composable
fun VerificationScreen(navController: NavController) {
    val vm: VerificationViewModel = viewModel()
    val user       by vm.user.collectAsStateWithLifecycle()
    val submitState by vm.submitState.collectAsStateWithLifecycle()
    val context    = LocalContext.current

    val status = user?.verificationStatus ?: "none"

    val imagePickerLauncher = rememberLauncherForActivityResult(
        contract = ActivityResultContracts.GetContent()
    ) { uri -> vm.selectedImageUri = uri }

    // Success snackbar
    val snackbarHostState = remember { SnackbarHostState() }
    LaunchedEffect(submitState) {
        if (submitState is VerificationViewModel.SubmitState.Success) {
            snackbarHostState.showSnackbar("Demande soumise avec succès !")
            vm.resetState()
        }
    }

    Scaffold(
        topBar = {
            TopAppBar(
                title = { Text("Vérification d'identité", fontWeight = FontWeight.Bold) },
                navigationIcon = {
                    IconButton(onClick = { navController.popBackStack() }) {
                        Icon(Icons.AutoMirrored.Filled.ArrowBack, contentDescription = "Retour")
                    }
                },
                colors = TopAppBarDefaults.topAppBarColors(
                    containerColor = MaterialTheme.colorScheme.surface
                )
            )
        },
        snackbarHost = { SnackbarHost(snackbarHostState) }
    ) { padding ->
        Column(
            modifier = Modifier
                .fillMaxSize()
                .verticalScroll(rememberScrollState())
                .padding(padding)
                .padding(horizontal = 16.dp, vertical = 8.dp),
            verticalArrangement = Arrangement.spacedBy(16.dp)
        ) {
            // Status header
            StatusHeader(status = status)

            // Content based on status
            when (status) {
                "pending"  -> PendingSection()
                "approved" -> ApprovedSection()
                else       -> {
                    // "none" or "rejected"
                    BenefitsSection()
                    UploadSection(
                        selectedUri     = vm.selectedImageUri,
                        onPickImage     = { imagePickerLauncher.launch("image/*") }
                    )
                    SubmitButton(
                        canSubmit   = vm.selectedImageUri != null,
                        isLoading   = submitState is VerificationViewModel.SubmitState.Loading,
                        errorMsg    = (submitState as? VerificationViewModel.SubmitState.Error)?.msg,
                        onSubmit    = { vm.submitVerification(context) }
                    )
                }
            }

            Spacer(modifier = Modifier.height(80.dp))
        }
    }
}

// ─── Status Header ───────────────────────────────────────────────────────────

@Composable
private fun StatusHeader(status: String) {
    val (icon, color, title, subtitle) = when (status) {
        "pending"  -> Quad(Icons.Filled.HourglassEmpty, Warning500,
            "Vérification en cours",
            "Votre demande est en cours de traitement. Nous vous notifierons dès que la vérification sera terminée.")
        "approved" -> Quad(Icons.Filled.VerifiedUser, Success500,
            "Identité vérifiée",
            "Votre identité a été vérifiée avec succès. Vous bénéficiez du badge de confiance.")
        "rejected" -> Quad(Icons.Filled.Cancel, Error500,
            "Demande refusée",
            "Votre demande a été refusée. Veuillez soumettre un document plus lisible.")
        else       -> Quad(Icons.Filled.Shield, OceanBlue700,
            "Vérifiez votre identité",
            "Soumettez votre pièce d'identité pour obtenir le badge de confiance et augmenter votre visibilité.")
    }

    Column(
        modifier = Modifier.fillMaxWidth(),
        horizontalAlignment = Alignment.CenterHorizontally,
        verticalArrangement = Arrangement.spacedBy(12.dp)
    ) {
        Box(
            modifier = Modifier
                .size(80.dp)
                .clip(CircleShape)
                .background(color.copy(alpha = 0.12f)),
            contentAlignment = Alignment.Center
        ) {
            Icon(icon, contentDescription = null, tint = color, modifier = Modifier.size(40.dp))
        }
        Text(title, style = MaterialTheme.typography.titleLarge, color = OceanBlue900)
        Text(
            subtitle,
            style = MaterialTheme.typography.bodyMedium,
            color = Gray500,
            textAlign = TextAlign.Center,
            modifier = Modifier.padding(horizontal = 16.dp)
        )
        // Status pill
        val pillLabel = when (status) {
            "pending"  -> "En attente"
            "approved" -> "Approuvé"
            "rejected" -> "Refusé"
            else       -> "Non soumis"
        }
        Surface(
            shape  = CircleShape,
            color  = color.copy(alpha = 0.1f)
        ) {
            Row(
                modifier = Modifier.padding(horizontal = 14.dp, vertical = 6.dp),
                verticalAlignment = Alignment.CenterVertically,
                horizontalArrangement = Arrangement.spacedBy(6.dp)
            ) {
                Box(modifier = Modifier.size(8.dp).clip(CircleShape).background(color))
                Text(pillLabel, style = MaterialTheme.typography.labelMedium, color = color, fontWeight = FontWeight.SemiBold)
            }
        }
    }
}

// ─── Benefits Section ─────────────────────────────────────────────────────────

@Composable
private fun BenefitsSection() {
    SectionCard(title = "AVANTAGES DE LA VÉRIFICATION") {
        val benefits = listOf(
            Triple(Icons.Filled.VerifiedUser, "Badge de confiance", "Montrez aux acheteurs que vous êtes fiable"),
            Triple(Icons.Filled.Visibility,   "Plus de visibilité",  "Vos annonces apparaissent en priorité"),
            Triple(Icons.Filled.Shield,       "Transactions sécurisées", "Accédez à la médiation pour vos ventes"),
            Triple(Icons.Filled.Star,         "Crédibilité renforcée", "Les acheteurs préfèrent les vendeurs vérifiés"),
        )
        val colors = listOf(Success500, Teal500, OceanBlue700, Gold500)

        benefits.forEachIndexed { i, (icon, title, subtitle) ->
            if (i > 0) HorizontalDivider(modifier = Modifier.padding(start = 56.dp))
            BenefitRow(icon = icon, title = title, subtitle = subtitle, color = colors[i])
        }
    }
}

@Composable
private fun BenefitRow(icon: ImageVector, title: String, subtitle: String, color: Color) {
    Row(
        modifier = Modifier.padding(horizontal = 16.dp, vertical = 12.dp),
        horizontalArrangement = Arrangement.spacedBy(14.dp),
        verticalAlignment = Alignment.CenterVertically
    ) {
        Box(
            modifier = Modifier
                .size(36.dp)
                .clip(RoundedCornerShape(8.dp))
                .background(color),
            contentAlignment = Alignment.Center
        ) {
            Icon(icon, contentDescription = null, tint = Color.White, modifier = Modifier.size(18.dp))
        }
        Column(modifier = Modifier.weight(1f)) {
            Text(title, style = MaterialTheme.typography.titleSmall)
            Text(subtitle, style = MaterialTheme.typography.bodySmall, color = Gray500)
        }
    }
}

// ─── Upload Section ───────────────────────────────────────────────────────────

@Composable
private fun UploadSection(selectedUri: Uri?, onPickImage: () -> Unit) {
    SectionCard(title = "DOCUMENT D'IDENTITÉ") {
        Column(
            modifier = Modifier.padding(16.dp),
            verticalArrangement = Arrangement.spacedBy(12.dp)
        ) {
            // Info row
            Surface(color = Teal50, shape = RoundedCornerShape(12.dp)) {
                Row(
                    modifier = Modifier.padding(12.dp),
                    horizontalArrangement = Arrangement.spacedBy(10.dp),
                    verticalAlignment = Alignment.Top
                ) {
                    Icon(Icons.Filled.Info, contentDescription = null, tint = Teal500, modifier = Modifier.size(18.dp))
                    Text(
                        "Téléchargez une photo claire de votre carte d'identité nationale ou passeport.",
                        style = MaterialTheme.typography.bodySmall,
                        color = Gray700
                    )
                }
            }

            // Upload area
            Box(
                modifier = Modifier
                    .fillMaxWidth()
                    .clip(RoundedCornerShape(12.dp))
                    .border(
                        width = 2.dp,
                        color = if (selectedUri != null) OceanBlue300 else Gray200,
                        shape = RoundedCornerShape(12.dp)
                    )
                    .background(if (selectedUri != null) OceanBlue50 else Gray50)
                    .clickable { onPickImage() }
                    .padding(20.dp),
                contentAlignment = Alignment.Center
            ) {
                if (selectedUri != null) {
                    Column(
                        horizontalAlignment = Alignment.CenterHorizontally,
                        verticalArrangement = Arrangement.spacedBy(8.dp)
                    ) {
                        AsyncImage(
                            model   = selectedUri,
                            contentDescription = "Document sélectionné",
                            contentScale = ContentScale.Fit,
                            modifier = Modifier
                                .fillMaxWidth()
                                .heightIn(max = 200.dp)
                                .clip(RoundedCornerShape(8.dp))
                        )
                        Row(
                            horizontalArrangement = Arrangement.spacedBy(6.dp),
                            verticalAlignment = Alignment.CenterVertically
                        ) {
                            Icon(Icons.Filled.Refresh, contentDescription = null, tint = Teal500, modifier = Modifier.size(14.dp))
                            Text("Changer le document", style = MaterialTheme.typography.labelMedium, color = Teal500)
                        }
                    }
                } else {
                    Column(
                        horizontalAlignment = Alignment.CenterHorizontally,
                        verticalArrangement = Arrangement.spacedBy(10.dp)
                    ) {
                        Icon(Icons.Filled.UploadFile, contentDescription = null, tint = Gray300, modifier = Modifier.size(48.dp))
                        Text("Appuyez pour sélectionner", style = MaterialTheme.typography.bodyMedium, color = Gray500)
                        Text("JPG, PNG — Max 10 MB", style = MaterialTheme.typography.labelSmall, color = Gray400)
                    }
                }
            }
        }
    }
}

// ─── Submit Button ────────────────────────────────────────────────────────────

@Composable
private fun SubmitButton(
    canSubmit: Boolean,
    isLoading: Boolean,
    errorMsg: String?,
    onSubmit: () -> Unit
) {
    Column(verticalArrangement = Arrangement.spacedBy(8.dp)) {
        if (errorMsg != null) {
            Surface(color = Error100, shape = RoundedCornerShape(12.dp)) {
                Row(
                    modifier = Modifier.padding(12.dp),
                    horizontalArrangement = Arrangement.spacedBy(8.dp),
                    verticalAlignment = Alignment.CenterVertically
                ) {
                    Icon(Icons.Filled.ErrorOutline, contentDescription = null, tint = Error500, modifier = Modifier.size(16.dp))
                    Text(errorMsg, style = MaterialTheme.typography.bodySmall, color = Error500)
                }
            }
        }

        Button(
            onClick = onSubmit,
            enabled = canSubmit && !isLoading,
            modifier = Modifier
                .fillMaxWidth()
                .height(52.dp),
            colors = ButtonDefaults.buttonColors(containerColor = OceanBlue700),
            shape = RoundedCornerShape(14.dp)
        ) {
            if (isLoading) {
                CircularProgressIndicator(color = Color.White, modifier = Modifier.size(20.dp), strokeWidth = 2.dp)
                Spacer(Modifier.width(8.dp))
            } else {
                Icon(Icons.AutoMirrored.Filled.Send, contentDescription = null, modifier = Modifier.size(18.dp))
                Spacer(Modifier.width(8.dp))
            }
            Text("Soumettre la demande", fontWeight = FontWeight.Bold, fontSize = 16.sp)
        }
    }
}

// ─── Pending Section ──────────────────────────────────────────────────────────

@Composable
private fun PendingSection() {
    Card(
        modifier = Modifier.fillMaxWidth(),
        colors = CardDefaults.cardColors(containerColor = Gold50),
        shape = RoundedCornerShape(20.dp)
    ) {
        Column(
            modifier = Modifier.padding(24.dp),
            horizontalAlignment = Alignment.CenterHorizontally,
            verticalArrangement = Arrangement.spacedBy(12.dp)
        ) {
            Icon(Icons.Filled.HourglassEmpty, contentDescription = null, tint = Warning500, modifier = Modifier.size(48.dp))
            Text("Votre demande est en cours de traitement", style = MaterialTheme.typography.titleMedium, color = OceanBlue900, textAlign = TextAlign.Center)
            Text(
                "Notre équipe examine votre document. La vérification prend généralement 24 à 48 heures ouvrables.",
                style = MaterialTheme.typography.bodyMedium,
                color = Gray500,
                textAlign = TextAlign.Center
            )
        }
    }

    // Timeline
    SectionCard(title = "ÉTAPES DE VÉRIFICATION") {
        Column(modifier = Modifier.padding(16.dp), verticalArrangement = Arrangement.spacedBy(0.dp)) {
            TimelineStep(
                title = "Demande soumise",
                subtitle = "Votre document a été reçu",
                icon = Icons.Filled.CheckCircle,
                color = Success500,
                isDone = true
            )
            TimelineConnector(isActive = true)
            TimelineStep(
                title = "En cours de vérification",
                subtitle = "Notre équipe examine votre document",
                icon = Icons.AutoMirrored.Filled.ManageSearch,
                color = Warning500,
                isDone = false,
                isActive = true
            )
            TimelineConnector(isActive = false)
            TimelineStep(
                title = "Vérification terminée",
                subtitle = "Vous recevrez une notification",
                icon = Icons.Outlined.Notifications,
                color = Gray400,
                isDone = false
            )
        }
    }
}

@Composable
private fun TimelineStep(title: String, subtitle: String, icon: ImageVector, color: Color, isDone: Boolean, isActive: Boolean = false) {
    Row(
        modifier = Modifier.padding(vertical = 4.dp),
        horizontalArrangement = Arrangement.spacedBy(14.dp),
        verticalAlignment = Alignment.CenterVertically
    ) {
        Icon(
            icon,
            contentDescription = null,
            tint = if (isDone || isActive) color else Gray300,
            modifier = Modifier.size(28.dp)
        )
        Column {
            Text(title, style = MaterialTheme.typography.titleSmall, color = if (isDone || isActive) Gray900 else Gray400)
            Text(subtitle, style = MaterialTheme.typography.bodySmall, color = Gray500)
        }
    }
}

@Composable
private fun TimelineConnector(isActive: Boolean) {
    Box(
        modifier = Modifier
            .padding(start = 13.dp)
            .width(2.dp)
            .height(20.dp)
            .background(if (isActive) Warning500.copy(alpha = 0.4f) else Gray200)
    )
}

// ─── Approved Section ─────────────────────────────────────────────────────────

@Composable
private fun ApprovedSection() {
    Card(
        modifier = Modifier.fillMaxWidth(),
        colors = CardDefaults.cardColors(containerColor = Success100),
        shape = RoundedCornerShape(24.dp)
    ) {
        Column(
            modifier = Modifier.padding(28.dp),
            horizontalAlignment = Alignment.CenterHorizontally,
            verticalArrangement = Arrangement.spacedBy(12.dp)
        ) {
            Icon(Icons.Filled.VerifiedUser, contentDescription = null, tint = Success500, modifier = Modifier.size(64.dp))
            Text("Félicitations !", style = MaterialTheme.typography.headlineSmall, color = OceanBlue900)
            Text(
                "Votre identité a été vérifiée avec succès. Le badge de confiance est maintenant visible sur votre profil et vos annonces.",
                style = MaterialTheme.typography.bodyMedium,
                color = Gray700,
                textAlign = TextAlign.Center
            )
        }
    }

    SectionCard(title = "AVANTAGES ACTIFS") {
        Column(modifier = Modifier.padding(16.dp), verticalArrangement = Arrangement.spacedBy(12.dp)) {
            ActiveBenefitRow(icon = Icons.Filled.VerifiedUser, text = "Badge de confiance actif")
            ActiveBenefitRow(icon = Icons.AutoMirrored.Filled.TrendingUp, text = "Visibilité augmentée")
            ActiveBenefitRow(icon = Icons.Filled.Shield,       text = "Médiation disponible")
        }
    }
}

@Composable
private fun ActiveBenefitRow(icon: ImageVector, text: String) {
    Row(
        modifier = Modifier.fillMaxWidth(),
        verticalAlignment = Alignment.CenterVertically,
        horizontalArrangement = Arrangement.spacedBy(12.dp)
    ) {
        Icon(icon, contentDescription = null, tint = Success500, modifier = Modifier.size(20.dp))
        Text(text, style = MaterialTheme.typography.bodyMedium, modifier = Modifier.weight(1f))
        Icon(Icons.Filled.Check, contentDescription = null, tint = Success500, modifier = Modifier.size(16.dp))
    }
}

// ─── Helpers ──────────────────────────────────────────────────────────────────

@Composable
private fun SectionCard(title: String, content: @Composable ColumnScope.() -> Unit) {
    Column(verticalArrangement = Arrangement.spacedBy(8.dp)) {
        Text(
            title,
            style = MaterialTheme.typography.labelSmall,
            color = Gray400,
            fontWeight = FontWeight.Bold,
            modifier = Modifier.padding(horizontal = 4.dp)
        )
        Card(
            modifier = Modifier.fillMaxWidth(),
            colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surface),
            elevation = CardDefaults.cardElevation(defaultElevation = 1.dp),
            shape = RoundedCornerShape(16.dp)
        ) {
            Column(content = content)
        }
    }
}

private data class Quad<A, B, C, D>(val first: A, val second: B, val third: C, val fourth: D)
