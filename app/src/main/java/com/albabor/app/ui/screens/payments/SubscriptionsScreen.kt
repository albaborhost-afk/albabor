package com.albabor.app.ui.screens.payments

import androidx.compose.foundation.background
import androidx.compose.foundation.border
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.navigationBarsPadding
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.width
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ArrowBack
import androidx.compose.material.icons.filled.CheckCircle
import androidx.compose.material.icons.filled.CheckCircleOutline
import androidx.compose.material.icons.filled.UploadFile
import androidx.compose.material3.Button
import androidx.compose.material3.ButtonDefaults
import androidx.compose.material3.Card
import androidx.compose.material3.CardDefaults
import androidx.compose.material3.ExperimentalMaterial3Api
import androidx.compose.material3.HorizontalDivider
import androidx.compose.material3.Icon
import androidx.compose.material3.IconButton
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Scaffold
import androidx.compose.material3.Surface
import androidx.compose.material3.Text
import androidx.compose.material3.TopAppBar
import androidx.compose.material3.TopAppBarDefaults
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.getValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.lifecycle.compose.collectAsStateWithLifecycle
import androidx.lifecycle.viewmodel.compose.viewModel
import androidx.navigation.NavController
import com.albabor.app.data.model.Subscription
import com.albabor.app.ui.theme.Error500
import com.albabor.app.ui.theme.Gold100
import com.albabor.app.ui.theme.Gold500
import com.albabor.app.ui.theme.Gray100
import com.albabor.app.ui.theme.Gray200
import com.albabor.app.ui.theme.Gray400
import com.albabor.app.ui.theme.Gray50
import com.albabor.app.ui.theme.Gray500
import com.albabor.app.ui.theme.Gray700
import com.albabor.app.ui.theme.Gray900
import com.albabor.app.ui.theme.OceanBlue100
import com.albabor.app.ui.theme.OceanBlue700
import com.albabor.app.ui.theme.OceanBlue900
import com.albabor.app.ui.theme.Success100
import com.albabor.app.ui.theme.Success500
import com.albabor.app.ui.theme.Teal500
import com.albabor.app.ui.theme.White
import com.albabor.app.viewmodel.PaymentsViewModel

// ─── Subscriptions Screen ────────────────────────────────────────────────────

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun SubscriptionsScreen(navController: NavController) {
    val vm: PaymentsViewModel = viewModel()
    val subscriptions by vm.subscriptions.collectAsStateWithLifecycle()
    val isLoading by vm.isLoading.collectAsStateWithLifecycle()

    LaunchedEffect(Unit) {
        vm.loadSubscriptions()
    }

    val activeSubscription = subscriptions.firstOrNull { it.status == "active" }

    Scaffold(
        topBar = {
            TopAppBar(
                title = {
                    Text(
                        text = "Abonnements",
                        style = MaterialTheme.typography.titleLarge.copy(
                            fontWeight = FontWeight.Bold,
                            color = Gray900
                        )
                    )
                },
                navigationIcon = {
                    IconButton(onClick = { navController.popBackStack() }) {
                        Icon(
                            imageVector = Icons.AutoMirrored.Filled.ArrowBack,
                            contentDescription = "Retour",
                            tint = Gray700
                        )
                    }
                },
                colors = TopAppBarDefaults.topAppBarColors(
                    containerColor = White,
                    scrolledContainerColor = White
                )
            )
        },
        containerColor = Gray50
    ) { innerPadding ->
        Column(
            modifier = Modifier
                .fillMaxSize()
                .padding(innerPadding)
                .verticalScroll(rememberScrollState())
                .navigationBarsPadding()
                .padding(horizontal = 16.dp, vertical = 16.dp),
            verticalArrangement = Arrangement.spacedBy(16.dp)
        ) {
            // ── Active subscription status card ─────────────────────────────
            if (activeSubscription != null) {
                ActiveSubscriptionCard(subscription = activeSubscription)
            }

            // ── Vendor plan card ────────────────────────────────────────────
            VendorPlanCard(
                isActive = activeSubscription != null,
                onSubscribe = { /* navigate to proof upload */ }
            )

            // ── Payment instructions ────────────────────────────────────────
            PaymentInstructionsCard()

            // ── Upload proof button ─────────────────────────────────────────
            if (activeSubscription == null) {
                Button(
                    onClick = { /* open image picker for proof */ },
                    modifier = Modifier
                        .fillMaxWidth()
                        .height(52.dp),
                    shape = RoundedCornerShape(14.dp),
                    colors = ButtonDefaults.buttonColors(
                        containerColor = OceanBlue700,
                        contentColor = White
                    )
                ) {
                    Icon(
                        imageVector = Icons.Filled.UploadFile,
                        contentDescription = null,
                        modifier = Modifier.size(18.dp)
                    )
                    Spacer(modifier = Modifier.width(8.dp))
                    Text(
                        text = "Envoyer la preuve de paiement",
                        style = MaterialTheme.typography.labelLarge
                    )
                }
            }

            Spacer(modifier = Modifier.height(8.dp))
        }
    }
}

// ─── Active subscription status card ─────────────────────────────────────────

@Composable
private fun ActiveSubscriptionCard(subscription: Subscription) {
    Card(
        modifier = Modifier.fillMaxWidth(),
        shape = RoundedCornerShape(16.dp),
        colors = CardDefaults.cardColors(containerColor = Success100),
        elevation = CardDefaults.cardElevation(defaultElevation = 0.dp)
    ) {
        Row(
            modifier = Modifier.padding(16.dp),
            verticalAlignment = Alignment.CenterVertically
        ) {
            Box(
                modifier = Modifier
                    .size(48.dp)
                    .clip(CircleShape)
                    .background(Success500.copy(alpha = 0.15f)),
                contentAlignment = Alignment.Center
            ) {
                Icon(
                    imageVector = Icons.Filled.CheckCircle,
                    contentDescription = null,
                    tint = Success500,
                    modifier = Modifier.size(26.dp)
                )
            }
            Spacer(modifier = Modifier.width(14.dp))
            Column {
                Text(
                    text = "Abonnement actif",
                    style = MaterialTheme.typography.titleSmall.copy(
                        color = Success500,
                        fontWeight = FontWeight.Bold
                    )
                )
                Spacer(modifier = Modifier.height(2.dp))
                Text(
                    text = "Expire le ${subscription.expiresAt?.take(10) ?: "—"}",
                    style = MaterialTheme.typography.bodySmall.copy(color = Gray500)
                )
            }
        }
    }
}

// ─── Vendor plan card ─────────────────────────────────────────────────────────

@Composable
private fun VendorPlanCard(isActive: Boolean, onSubscribe: () -> Unit) {
    val borderColor = if (isActive) Success500 else OceanBlue700

    Card(
        modifier = Modifier
            .fillMaxWidth()
            .border(
                width = if (isActive) 2.dp else 1.5.dp,
                color = borderColor,
                shape = RoundedCornerShape(16.dp)
            ),
        shape = RoundedCornerShape(16.dp),
        colors = CardDefaults.cardColors(containerColor = White),
        elevation = CardDefaults.cardElevation(defaultElevation = 2.dp)
    ) {
        Column(modifier = Modifier.padding(20.dp)) {
            // Header
            Row(
                modifier = Modifier.fillMaxWidth(),
                verticalAlignment = Alignment.CenterVertically,
                horizontalArrangement = Arrangement.SpaceBetween
            ) {
                Column {
                    Text(
                        text = "Abonnement Vendeur Mensuel",
                        style = MaterialTheme.typography.titleSmall.copy(
                            fontWeight = FontWeight.Bold,
                            color = Gray900
                        )
                    )
                    Spacer(modifier = Modifier.height(4.dp))
                    Text(
                        text = "3 000 DA / mois",
                        style = MaterialTheme.typography.headlineSmall.copy(
                            color = OceanBlue700,
                            fontWeight = FontWeight.ExtraBold
                        )
                    )
                }
                if (isActive) {
                    Surface(
                        shape = RoundedCornerShape(50),
                        color = Success100
                    ) {
                        Text(
                            text = "Actif",
                            modifier = Modifier.padding(horizontal = 12.dp, vertical = 5.dp),
                            style = MaterialTheme.typography.labelMedium.copy(
                                color = Success500,
                                fontWeight = FontWeight.Bold
                            )
                        )
                    }
                }
            }

            Spacer(modifier = Modifier.height(16.dp))
            HorizontalDivider(color = Gray100, thickness = 1.dp)
            Spacer(modifier = Modifier.height(16.dp))

            // Benefits
            val benefits = listOf(
                "Publiez moteurs et pieces detachees",
                "Annonces illimitees",
                "Acces aux outils vendeur",
                "Badge vendeur verifie",
                "Mise en avant prioritaire",
                "Support dedie"
            )

            Column(verticalArrangement = Arrangement.spacedBy(10.dp)) {
                benefits.forEach { benefit ->
                    BenefitRow(text = benefit)
                }
            }

            if (!isActive) {
                Spacer(modifier = Modifier.height(20.dp))
                Box(
                    modifier = Modifier
                        .fillMaxWidth()
                        .height(50.dp)
                        .clip(RoundedCornerShape(12.dp))
                        .background(
                            Brush.horizontalGradient(
                                listOf(OceanBlue900, OceanBlue700, Teal500)
                            )
                        ),
                    contentAlignment = Alignment.Center
                ) {
                    Button(
                        onClick = onSubscribe,
                        modifier = Modifier.fillMaxSize(),
                        shape = RoundedCornerShape(12.dp),
                        colors = ButtonDefaults.buttonColors(
                            containerColor = androidx.compose.ui.graphics.Color.Transparent,
                            contentColor = White
                        ),
                        elevation = ButtonDefaults.buttonElevation(defaultElevation = 0.dp)
                    ) {
                        Text(
                            text = "S'abonner",
                            style = MaterialTheme.typography.labelLarge.copy(
                                fontWeight = FontWeight.Bold,
                                letterSpacing = 0.5.sp
                            )
                        )
                    }
                }
            }
        }
    }
}

@Composable
private fun BenefitRow(text: String) {
    Row(verticalAlignment = Alignment.CenterVertically) {
        Icon(
            imageVector = Icons.Filled.CheckCircleOutline,
            contentDescription = null,
            tint = OceanBlue700,
            modifier = Modifier.size(18.dp)
        )
        Spacer(modifier = Modifier.width(10.dp))
        Text(
            text = text,
            style = MaterialTheme.typography.bodyMedium.copy(color = Gray700)
        )
    }
}

// ─── Payment instructions card ────────────────────────────────────────────────

@Composable
private fun PaymentInstructionsCard() {
    Card(
        modifier = Modifier.fillMaxWidth(),
        shape = RoundedCornerShape(16.dp),
        colors = CardDefaults.cardColors(containerColor = Gold100.copy(alpha = 0.50f)),
        elevation = CardDefaults.cardElevation(defaultElevation = 0.dp)
    ) {
        Column(modifier = Modifier.padding(18.dp)) {
            Text(
                text = "Comment payer",
                style = MaterialTheme.typography.titleSmall.copy(
                    color = Gray900,
                    fontWeight = FontWeight.Bold
                )
            )
            Spacer(modifier = Modifier.height(14.dp))

            PaymentMethodRow(
                emoji = "📱",
                name = "BaridiMob",
                detail = "0799 00 00 00 — AlBabor SAS"
            )
            Spacer(modifier = Modifier.height(10.dp))
            PaymentMethodRow(
                emoji = "🏦",
                name = "CCP",
                detail = "Compte 00400 12345678 — cle 01"
            )
            Spacer(modifier = Modifier.height(10.dp))
            PaymentMethodRow(
                emoji = "🏛️",
                name = "Virement bancaire",
                detail = "BNA — RIB 00200 45000 12345678901 49"
            )

            Spacer(modifier = Modifier.height(14.dp))
            HorizontalDivider(color = Gold500.copy(alpha = 0.30f), thickness = 1.dp)
            Spacer(modifier = Modifier.height(12.dp))

            Text(
                text = "Apres paiement, envoyez la capture d'ecran ou le recu ci-dessous. Votre abonnement sera active sous 24h.",
                style = MaterialTheme.typography.bodySmall.copy(
                    color = Gray500,
                    lineHeight = 18.sp
                )
            )
        }
    }
}

@Composable
private fun PaymentMethodRow(emoji: String, name: String, detail: String) {
    Row(verticalAlignment = Alignment.Top) {
        Text(text = emoji, fontSize = 20.sp)
        Spacer(modifier = Modifier.width(10.dp))
        Column {
            Text(
                text = name,
                style = MaterialTheme.typography.labelLarge.copy(
                    color = Gray900,
                    fontWeight = FontWeight.SemiBold
                )
            )
            Text(
                text = detail,
                style = MaterialTheme.typography.bodySmall.copy(color = Gray500)
            )
        }
    }
}
