package com.albabor.app.ui.screens.mediation

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
import androidx.compose.material.icons.automirrored.filled.ArrowForwardIos
import androidx.compose.material.icons.filled.Balance
import androidx.compose.material.icons.filled.Refresh
import androidx.compose.material3.Card
import androidx.compose.material3.CardDefaults
import androidx.compose.material3.CircularProgressIndicator
import androidx.compose.material3.ExperimentalMaterial3Api
import androidx.compose.material3.HorizontalDivider
import androidx.compose.material3.Icon
import androidx.compose.material3.IconButton
import androidx.compose.material3.LinearProgressIndicator
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Scaffold
import androidx.compose.material3.ScrollableTabRow
import androidx.compose.material3.Surface
import androidx.compose.material3.Tab
import androidx.compose.material3.Text
import androidx.compose.material3.TopAppBar
import androidx.compose.material3.TopAppBarDefaults
import androidx.compose.runtime.Composable
import androidx.compose.runtime.getValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextOverflow
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.lifecycle.compose.collectAsStateWithLifecycle
import androidx.lifecycle.viewmodel.compose.viewModel
import androidx.navigation.NavController
import coil.compose.AsyncImage
import com.albabor.app.data.model.MediationTicket
import com.albabor.app.ui.navigation.Screen
import com.albabor.app.ui.theme.Error100
import com.albabor.app.ui.theme.Error500
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
import com.albabor.app.ui.theme.Teal100
import com.albabor.app.ui.theme.Teal500
import com.albabor.app.ui.theme.Warning100
import com.albabor.app.ui.theme.Warning500
import com.albabor.app.ui.theme.White
import com.albabor.app.viewmodel.MediationViewModel

// ─── Tab definitions ──────────────────────────────────────────────────────────

private data class MediationTab(val key: String, val label: String)

private val mediationTabs = listOf(
    MediationTab("all",         "Tous"),
    MediationTab("new",         "Nouveaux"),
    MediationTab("in_progress", "En cours"),
    MediationTab("resolved",    "Resolus")
)

// ─── MediationScreen ─────────────────────────────────────────────────────────

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun MediationScreen(navController: NavController) {
    val vm: MediationViewModel = viewModel()
    val tickets by vm.tickets.collectAsStateWithLifecycle()
    val activeTab by vm.activeTab.collectAsStateWithLifecycle()
    val isLoading by vm.isLoading.collectAsStateWithLifecycle()

    val filteredTickets = vm.filteredTickets

    Scaffold(
        topBar = {
            TopAppBar(
                title = {
                    Text(
                        text = "Mediation",
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
                    IconButton(onClick = { vm.loadTickets() }) {
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
        Column(
            modifier = Modifier
                .fillMaxSize()
                .padding(innerPadding)
        ) {
            // ── Tab bar ─────────────────────────────────────────────────────
            Surface(
                color = White,
                shadowElevation = 2.dp
            ) {
                ScrollableTabRow(
                    selectedTabIndex = mediationTabs.indexOfFirst { it.key == activeTab }.coerceAtLeast(0),
                    containerColor = White,
                    contentColor = OceanBlue700,
                    edgePadding = 16.dp,
                    divider = {}
                ) {
                    mediationTabs.forEach { tab ->
                        val isSelected = tab.key == activeTab
                        Tab(
                            selected = isSelected,
                            onClick = { vm.setActiveTab(tab.key) },
                            text = {
                                Text(
                                    text = tab.label,
                                    style = MaterialTheme.typography.labelLarge.copy(
                                        fontWeight = if (isSelected) FontWeight.Bold else FontWeight.Normal,
                                        color = if (isSelected) OceanBlue700 else Gray400
                                    )
                                )
                            }
                        )
                    }
                }
            }

            // ── Loading bar ──────────────────────────────────────────────────
            AnimatedVisibility(visible = isLoading, enter = fadeIn(), exit = fadeOut()) {
                LinearProgressIndicator(
                    modifier = Modifier.fillMaxWidth(),
                    color = OceanBlue700,
                    trackColor = OceanBlue100
                )
            }

            // ── Content ──────────────────────────────────────────────────────
            Box(modifier = Modifier.fillMaxSize()) {
                if (!isLoading && filteredTickets.isEmpty()) {
                    MediationEmptyState()
                } else {
                    LazyColumn(
                        modifier = Modifier
                            .fillMaxSize()
                            .navigationBarsPadding(),
                        verticalArrangement = Arrangement.spacedBy(0.dp)
                    ) {
                        items(filteredTickets, key = { it.id }) { ticket ->
                            MediationTicketRow(
                                ticket = ticket,
                                onClick = {
                                    navController.navigate(Screen.MediationDetail.route(ticket.id))
                                }
                            )
                            HorizontalDivider(
                                color = Gray100,
                                thickness = 1.dp,
                                modifier = Modifier.padding(start = 72.dp)
                            )
                        }
                        item { Spacer(modifier = Modifier.height(24.dp)) }
                    }
                }
            }
        }
    }
}

// ─── Ticket row ───────────────────────────────────────────────────────────────

@Composable
private fun MediationTicketRow(ticket: MediationTicket, onClick: () -> Unit) {
    val (statusBg, statusFg) = ticketStatusColors(ticket.status)
    val lastMsg = ticket.messages.lastOrNull()

    Row(
        modifier = Modifier
            .fillMaxWidth()
            .background(White)
            .clickable(onClick = onClick)
            .padding(horizontal = 16.dp, vertical = 14.dp),
        verticalAlignment = Alignment.CenterVertically
    ) {
        // Thumbnail
        Box(
            modifier = Modifier
                .size(52.dp)
                .clip(RoundedCornerShape(10.dp))
                .background(OceanBlue100)
        ) {
            ticket.listing?.primaryImage?.let { url ->
                AsyncImage(
                    model = url,
                    contentDescription = null,
                    contentScale = ContentScale.Crop,
                    modifier = Modifier.fillMaxSize()
                )
            }
        }

        Spacer(modifier = Modifier.width(12.dp))

        Column(modifier = Modifier.weight(1f)) {
            // Title
            Text(
                text = ticket.listing?.title ?: "Annonce supprimee",
                style = MaterialTheme.typography.titleSmall.copy(
                    fontWeight = FontWeight.SemiBold,
                    color = Gray900
                ),
                maxLines = 1,
                overflow = TextOverflow.Ellipsis
            )
            Spacer(modifier = Modifier.height(3.dp))

            // Buyer → Seller
            Row(verticalAlignment = Alignment.CenterVertically) {
                Text(
                    text = ticket.buyer?.name ?: "—",
                    style = MaterialTheme.typography.bodySmall.copy(color = Gray500),
                    maxLines = 1,
                    overflow = TextOverflow.Ellipsis,
                    modifier = Modifier.weight(1f, fill = false)
                )
                Text(
                    text = "  →  ",
                    style = MaterialTheme.typography.bodySmall.copy(color = Gray400)
                )
                Text(
                    text = ticket.seller?.name ?: "—",
                    style = MaterialTheme.typography.bodySmall.copy(color = Gray500),
                    maxLines = 1,
                    overflow = TextOverflow.Ellipsis,
                    modifier = Modifier.weight(1f, fill = false)
                )
            }

            Spacer(modifier = Modifier.height(5.dp))

            // Last message preview
            if (lastMsg != null) {
                Text(
                    text = lastMsg.body,
                    style = MaterialTheme.typography.bodySmall.copy(color = Gray400),
                    maxLines = 1,
                    overflow = TextOverflow.Ellipsis
                )
            }
        }

        Spacer(modifier = Modifier.width(10.dp))

        // Right column: status badge + date + chevron
        Column(horizontalAlignment = Alignment.End) {
            Surface(
                shape = RoundedCornerShape(50),
                color = statusBg
            ) {
                Text(
                    text = ticket.statusLabel,
                    modifier = Modifier.padding(horizontal = 8.dp, vertical = 3.dp),
                    style = MaterialTheme.typography.labelSmall.copy(
                        color = statusFg,
                        fontWeight = FontWeight.SemiBold
                    )
                )
            }
            Spacer(modifier = Modifier.height(4.dp))
            Text(
                text = ticket.createdAt.take(10),
                style = MaterialTheme.typography.labelSmall.copy(
                    color = Gray400,
                    fontSize = 10.sp
                )
            )
            Spacer(modifier = Modifier.height(4.dp))
            Icon(
                imageVector = Icons.AutoMirrored.Filled.ArrowForwardIos,
                contentDescription = null,
                tint = Gray200,
                modifier = Modifier.size(12.dp)
            )
        }
    }
}

// ─── Empty state ──────────────────────────────────────────────────────────────

@Composable
private fun MediationEmptyState() {
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
                imageVector = Icons.Filled.Balance,
                contentDescription = null,
                tint = OceanBlue700,
                modifier = Modifier.size(44.dp)
            )
        }
        Spacer(modifier = Modifier.height(24.dp))
        Text(
            text = "Aucune mediation en cours",
            style = MaterialTheme.typography.titleMedium.copy(
                color = Gray900,
                fontWeight = FontWeight.Bold
            )
        )
        Spacer(modifier = Modifier.height(8.dp))
        Text(
            text = "Vos demandes de mediation apparaitront ici.",
            style = MaterialTheme.typography.bodyMedium.copy(color = Gray500),
            textAlign = androidx.compose.ui.text.style.TextAlign.Center
        )
    }
}

// ─── Status color helper ──────────────────────────────────────────────────────

internal fun ticketStatusColors(status: String): Pair<Color, Color> = when (status) {
    "new"              -> OceanBlue100 to OceanBlue700
    "in_progress"      -> Warning100  to Warning500
    "awaiting_payment" -> Warning100  to Warning500
    "resolved"         -> Success100  to Success500
    "closed"           -> Gray100     to Gray500
    "cancelled"        -> Error100    to Error500
    else               -> Gray100     to Gray500
}
