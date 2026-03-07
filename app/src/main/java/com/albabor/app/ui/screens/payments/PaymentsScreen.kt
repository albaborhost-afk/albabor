package com.albabor.app.ui.screens.payments

import androidx.compose.animation.AnimatedVisibility
import androidx.compose.animation.fadeIn
import androidx.compose.animation.fadeOut
import androidx.compose.foundation.background
import androidx.compose.foundation.clickable
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
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ArrowBack
import androidx.compose.material.icons.filled.CreditCard
import androidx.compose.material.icons.filled.OpenInNew
import androidx.compose.material.icons.filled.Refresh
import androidx.compose.material.icons.filled.UploadFile
import androidx.compose.material3.Button
import androidx.compose.material3.ButtonDefaults
import androidx.compose.material3.Card
import androidx.compose.material3.CardDefaults
import androidx.compose.material3.CircularProgressIndicator
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
import androidx.compose.runtime.getValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextOverflow
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.lifecycle.compose.collectAsStateWithLifecycle
import androidx.lifecycle.viewmodel.compose.viewModel
import androidx.navigation.NavController
import com.albabor.app.data.model.Payment
import com.albabor.app.ui.navigation.Screen
import com.albabor.app.ui.theme.Error100
import com.albabor.app.ui.theme.Error500
import com.albabor.app.ui.theme.Gold500
import com.albabor.app.ui.theme.Gray100
import com.albabor.app.ui.theme.Gray200
import com.albabor.app.ui.theme.Gray400
import com.albabor.app.ui.theme.Gray50
import com.albabor.app.ui.theme.Gray500
import com.albabor.app.ui.theme.Gray700
import com.albabor.app.ui.theme.Gray900
import com.albabor.app.ui.theme.OceanBlue100
import com.albabor.app.ui.theme.OceanBlue500
import com.albabor.app.ui.theme.OceanBlue700
import com.albabor.app.ui.theme.OceanBlue900
import com.albabor.app.ui.theme.Success100
import com.albabor.app.ui.theme.Success500
import com.albabor.app.ui.theme.Teal500
import com.albabor.app.ui.theme.Warning100
import com.albabor.app.ui.theme.Warning500
import com.albabor.app.ui.theme.White
import com.albabor.app.viewmodel.PaymentsViewModel

// ─── Payments Screen ──────────────────────────────────────────────────────────

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun PaymentsScreen(navController: NavController) {
    val vm: PaymentsViewModel = viewModel()
    val payments by vm.payments.collectAsStateWithLifecycle()
    val isLoading by vm.isLoading.collectAsStateWithLifecycle()

    Scaffold(
        topBar = {
            TopAppBar(
                title = {
                    Text(
                        text = "Paiements",
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
                actions = {
                    IconButton(onClick = { vm.loadPayments() }) {
                        Icon(
                            imageVector = Icons.Filled.Refresh,
                            contentDescription = "Actualiser",
                            tint = OceanBlue700
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
        Box(
            modifier = Modifier
                .fillMaxSize()
                .padding(innerPadding)
        ) {
            if (isLoading && payments.isEmpty()) {
                // Initial loading spinner
                CircularProgressIndicator(
                    color = OceanBlue700,
                    modifier = Modifier.align(Alignment.Center)
                )
            } else if (payments.isEmpty()) {
                PaymentsEmptyState()
            } else {
                LazyColumn(
                    modifier = Modifier
                        .fillMaxSize()
                        .navigationBarsPadding(),
                    verticalArrangement = Arrangement.spacedBy(0.dp)
                ) {
                    // Summary card
                    item {
                        PaymentsSummaryCard(payments = payments)
                        Spacer(modifier = Modifier.height(8.dp))
                    }

                    // Section header
                    item {
                        Text(
                            text = "Historique",
                            style = MaterialTheme.typography.titleSmall.copy(
                                color = Gray500,
                                fontWeight = FontWeight.SemiBold
                            ),
                            modifier = Modifier.padding(horizontal = 16.dp, vertical = 8.dp)
                        )
                    }

                    items(payments, key = { it.id }) { payment ->
                        PaymentCard(
                            payment = payment,
                            onSubmitProof = { /* open proof upload flow */ },
                            onViewListing = { listingId ->
                                navController.navigate(Screen.ListingDetail.route(listingId))
                            }
                        )
                        HorizontalDivider(
                            color = Gray100,
                            thickness = 1.dp,
                            modifier = Modifier.padding(horizontal = 16.dp)
                        )
                    }

                    item { Spacer(modifier = Modifier.height(24.dp)) }
                }
            }

            // Refresh spinner overlay
            AnimatedVisibility(
                visible = isLoading && payments.isNotEmpty(),
                enter = fadeIn(),
                exit = fadeOut(),
                modifier = Modifier.align(Alignment.TopCenter)
            ) {
                LinearLoadingBar()
            }
        }
    }
}

// ─── Summary card ─────────────────────────────────────────────────────────────

@Composable
private fun PaymentsSummaryCard(payments: List<Payment>) {
    val totalApproved = payments.filter { it.status == "approved" }.sumOf { it.amount }
    val pendingCount  = payments.count { it.status == "pending" }
    val rejectedCount = payments.count { it.status == "rejected" }

    Card(
        modifier = Modifier
            .fillMaxWidth()
            .padding(horizontal = 16.dp, vertical = 12.dp),
        shape = RoundedCornerShape(16.dp),
        colors = CardDefaults.cardColors(containerColor = OceanBlue900),
        elevation = CardDefaults.cardElevation(defaultElevation = 4.dp)
    ) {
        Column(modifier = Modifier.padding(20.dp)) {
            Text(
                text = "Total approuve",
                style = MaterialTheme.typography.labelMedium.copy(
                    color = White.copy(alpha = 0.70f),
                    letterSpacing = 0.8.sp
                )
            )
            Spacer(modifier = Modifier.height(4.dp))
            Text(
                text = "%,.0f DA".format(totalApproved).replace(",", " "),
                style = MaterialTheme.typography.headlineMedium.copy(
                    color = White,
                    fontWeight = FontWeight.Bold
                )
            )
            Spacer(modifier = Modifier.height(16.dp))
            Row(horizontalArrangement = Arrangement.spacedBy(24.dp)) {
                SummaryStatItem(
                    value = payments.size.toString(),
                    label = "Total",
                    color = White
                )
                SummaryStatItem(
                    value = pendingCount.toString(),
                    label = "En attente",
                    color = Gold500
                )
                SummaryStatItem(
                    value = rejectedCount.toString(),
                    label = "Rejetes",
                    color = Error500
                )
            }
        }
    }
}

@Composable
private fun SummaryStatItem(value: String, label: String, color: Color) {
    Column {
        Text(
            text = value,
            style = MaterialTheme.typography.titleMedium.copy(
                color = color,
                fontWeight = FontWeight.Bold
            )
        )
        Text(
            text = label,
            style = MaterialTheme.typography.labelSmall.copy(
                color = White.copy(alpha = 0.60f)
            )
        )
    }
}

// ─── Payment card ─────────────────────────────────────────────────────────────

@Composable
private fun PaymentCard(
    payment: Payment,
    onSubmitProof: () -> Unit,
    onViewListing: (Int) -> Unit
) {
    val (statusBg, statusFg) = when (payment.status) {
        "approved" -> Success100 to Success500
        "rejected" -> Error100   to Error500
        else       -> Warning100 to Warning500
    }
    val iconTint = when (payment.type) {
        "publish"      -> OceanBlue500
        "feature"      -> Gold500
        "subscription" -> Teal500
        "mediation"    -> Color(0xFF8E44AD)
        else           -> Gray500
    }

    Column(
        modifier = Modifier
            .fillMaxWidth()
            .background(White)
            .padding(horizontal = 16.dp, vertical = 14.dp)
    ) {
        Row(
            modifier = Modifier.fillMaxWidth(),
            verticalAlignment = Alignment.CenterVertically
        ) {
            // Icon circle
            Box(
                modifier = Modifier
                    .size(44.dp)
                    .clip(CircleShape)
                    .background(iconTint.copy(alpha = 0.12f)),
                contentAlignment = Alignment.Center
            ) {
                Icon(
                    imageVector = Icons.Filled.CreditCard,
                    contentDescription = null,
                    tint = iconTint,
                    modifier = Modifier.size(22.dp)
                )
            }

            Spacer(modifier = Modifier.width(12.dp))

            // Type + date
            Column(modifier = Modifier.weight(1f)) {
                Text(
                    text = payment.typeLabel,
                    style = MaterialTheme.typography.titleSmall.copy(
                        fontWeight = FontWeight.SemiBold,
                        color = Gray900
                    )
                )
                Spacer(modifier = Modifier.height(2.dp))
                Text(
                    text = payment.createdAt.take(10),
                    style = MaterialTheme.typography.bodySmall.copy(color = Gray400)
                )
            }

            Spacer(modifier = Modifier.width(8.dp))

            // Amount + status
            Column(horizontalAlignment = Alignment.End) {
                Text(
                    text = payment.formattedAmount,
                    style = MaterialTheme.typography.titleSmall.copy(
                        fontWeight = FontWeight.Bold,
                        color = OceanBlue900
                    )
                )
                Spacer(modifier = Modifier.height(4.dp))
                Surface(
                    shape = RoundedCornerShape(50),
                    color = statusBg
                ) {
                    Text(
                        text = payment.statusLabel,
                        modifier = Modifier.padding(horizontal = 8.dp, vertical = 3.dp),
                        style = MaterialTheme.typography.labelSmall.copy(
                            color = statusFg,
                            fontWeight = FontWeight.SemiBold
                        )
                    )
                }
            }
        }

        // Associated listing link
        payment.listing?.let { listing ->
            Spacer(modifier = Modifier.height(10.dp))
            Row(
                modifier = Modifier
                    .fillMaxWidth()
                    .clip(RoundedCornerShape(8.dp))
                    .background(OceanBlue100.copy(alpha = 0.50f))
                    .clickable { onViewListing(listing.id) }
                    .padding(horizontal = 10.dp, vertical = 7.dp),
                verticalAlignment = Alignment.CenterVertically
            ) {
                Icon(
                    imageVector = Icons.Filled.OpenInNew,
                    contentDescription = null,
                    tint = OceanBlue700,
                    modifier = Modifier.size(14.dp)
                )
                Spacer(modifier = Modifier.width(6.dp))
                Text(
                    text = listing.title,
                    style = MaterialTheme.typography.bodySmall.copy(
                        color = OceanBlue700,
                        fontWeight = FontWeight.Medium
                    ),
                    maxLines = 1,
                    overflow = TextOverflow.Ellipsis,
                    modifier = Modifier.weight(1f)
                )
            }
        }

        // "Soumettre la preuve" button for pending payments
        if (payment.status == "pending") {
            Spacer(modifier = Modifier.height(10.dp))
            Button(
                onClick = onSubmitProof,
                modifier = Modifier.fillMaxWidth(),
                shape = RoundedCornerShape(10.dp),
                colors = ButtonDefaults.buttonColors(
                    containerColor = OceanBlue700,
                    contentColor = White
                )
            ) {
                Icon(
                    imageVector = Icons.Filled.UploadFile,
                    contentDescription = null,
                    modifier = Modifier.size(16.dp)
                )
                Spacer(modifier = Modifier.width(8.dp))
                Text(
                    text = "Soumettre la preuve",
                    style = MaterialTheme.typography.labelLarge
                )
            }
        }
    }
}

// ─── Empty state ──────────────────────────────────────────────────────────────

@Composable
private fun PaymentsEmptyState() {
    Column(
        modifier = Modifier
            .fillMaxSize()
            .padding(32.dp),
        horizontalAlignment = Alignment.CenterHorizontally,
        verticalArrangement = Arrangement.Center
    ) {
        Box(
            modifier = Modifier
                .size(88.dp)
                .clip(CircleShape)
                .background(OceanBlue100),
            contentAlignment = Alignment.Center
        ) {
            Icon(
                imageVector = Icons.Filled.CreditCard,
                contentDescription = null,
                tint = OceanBlue700,
                modifier = Modifier.size(44.dp)
            )
        }
        Spacer(modifier = Modifier.height(24.dp))
        Text(
            text = "Aucun paiement",
            style = MaterialTheme.typography.titleMedium.copy(
                color = Gray900,
                fontWeight = FontWeight.Bold
            )
        )
        Spacer(modifier = Modifier.height(8.dp))
        Text(
            text = "Vos paiements apparaitront ici une fois effectues.",
            style = MaterialTheme.typography.bodyMedium.copy(color = Gray500),
            textAlign = androidx.compose.ui.text.style.TextAlign.Center
        )
    }
}

// ─── Thin linear loading bar ─────────────────────────────────────────────────

@Composable
private fun LinearLoadingBar() {
    androidx.compose.material3.LinearProgressIndicator(
        modifier = Modifier.fillMaxWidth(),
        color = OceanBlue700,
        trackColor = OceanBlue100
    )
}
