package com.albabor.app.ui.screens.mylistings

import androidx.compose.animation.AnimatedVisibility
import androidx.compose.animation.fadeIn
import androidx.compose.animation.fadeOut
import androidx.compose.foundation.background
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.LazyRow
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Add
import androidx.compose.material.icons.filled.MoreVert
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.lifecycle.compose.collectAsStateWithLifecycle
import androidx.lifecycle.viewmodel.compose.viewModel
import androidx.navigation.NavController
import com.albabor.app.data.model.Listing
import com.albabor.app.ui.components.ListingRowCard
import com.albabor.app.ui.navigation.Screen
import com.albabor.app.ui.theme.*
import com.albabor.app.viewmodel.MyListingsViewModel
import com.google.accompanist.placeholder.PlaceholderHighlight
import com.google.accompanist.placeholder.material3.fade
import com.google.accompanist.placeholder.material3.placeholder
import kotlinx.coroutines.launch

// ─── Status tab data ──────────────────────────────────────────────────────────

private data class StatusTab(val label: String, val filter: String?)

private val statusTabs = listOf(
    StatusTab("Toutes",     null),
    StatusTab("Actives",    "active"),
    StatusTab("En pause",   "paused"),
    StatusTab("Brouillons", "draft"),
    StatusTab("Vendues",    "sold"),
)

// ─── Screen ───────────────────────────────────────────────────────────────────

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun MyListingsScreen(navController: NavController) {
    val vm: MyListingsViewModel = viewModel()
    val listings      by vm.listings.collectAsStateWithLifecycle()
    val isLoading     by vm.isLoading.collectAsStateWithLifecycle()
    val actionState   by vm.actionState.collectAsStateWithLifecycle()
    val pendingDelete by vm.pendingDeleteId.collectAsStateWithLifecycle()

    var selectedTab by remember { mutableIntStateOf(0) }
    val snackbarHostState = remember { SnackbarHostState() }
    val scope = rememberCoroutineScope()

    // Handle action results via Snackbar
    LaunchedEffect(actionState) {
        when (val s = actionState) {
            is MyListingsViewModel.ActionState.Success -> {
                scope.launch { snackbarHostState.showSnackbar(s.msg) }
                vm.clearActionState()
            }
            is MyListingsViewModel.ActionState.Error -> {
                scope.launch { snackbarHostState.showSnackbar(s.msg) }
                vm.clearActionState()
            }
            else -> Unit
        }
    }

    // Filter listings by selected tab
    val filtered = remember(listings, selectedTab) {
        val tab = statusTabs[selectedTab]
        if (tab.filter == null) listings else listings.filter { it.status == tab.filter }
    }

    Scaffold(
        topBar = {
            TopAppBar(
                title = {
                    Text(
                        text = "Mes annonces",
                        fontWeight = FontWeight.Bold,
                        fontSize = 20.sp,
                        color = Gray900
                    )
                },
                colors = TopAppBarDefaults.topAppBarColors(
                    containerColor = White,
                    scrolledContainerColor = White
                )
            )
        },
        floatingActionButton = {
            FloatingActionButton(
                onClick = { navController.navigate(Screen.CreateListing.route) },
                containerColor = OceanBlue700,
                contentColor = White,
                shape = CircleShape,
                elevation = FloatingActionButtonDefaults.elevation(6.dp)
            ) {
                Icon(
                    imageVector = Icons.Default.Add,
                    contentDescription = "Publier une annonce",
                    modifier = Modifier.size(26.dp)
                )
            }
        },
        snackbarHost = {
            SnackbarHost(hostState = snackbarHostState) { data ->
                Snackbar(
                    snackbarData = data,
                    containerColor = Gray900,
                    contentColor = White,
                    shape = RoundedCornerShape(12.dp)
                )
            }
        },
        containerColor = Gray50
    ) { padding ->

        Column(
            modifier = Modifier
                .fillMaxSize()
                .padding(padding)
        ) {

            // ── Status tab filter row ─────────────────────────────────────────
            Surface(
                color = White,
                shadowElevation = 1.dp
            ) {
                LazyRow(
                    modifier = Modifier
                        .fillMaxWidth()
                        .padding(horizontal = 16.dp, vertical = 10.dp),
                    horizontalArrangement = Arrangement.spacedBy(8.dp),
                    verticalAlignment = Alignment.CenterVertically
                ) {
                    items(statusTabs.size) { index ->
                        val tab = statusTabs[index]
                        val isSelected = selectedTab == index
                        val count = if (tab.filter == null) listings.size
                                    else listings.count { it.status == tab.filter }

                        FilterTabChip(
                            label = tab.label,
                            count = count,
                            selected = isSelected,
                            onClick = { selectedTab = index }
                        )
                    }
                }
            }

            // ── Content ───────────────────────────────────────────────────────
            Box(modifier = Modifier.fillMaxSize()) {

                when {
                    isLoading -> {
                        LazyColumn(
                            modifier = Modifier.fillMaxSize(),
                            contentPadding = PaddingValues(16.dp),
                            verticalArrangement = Arrangement.spacedBy(12.dp)
                        ) {
                            items(5) { ShimmerRowCard() }
                        }
                    }

                    filtered.isEmpty() -> {
                        EmptyListingsState(
                            hasListings = listings.isNotEmpty(),
                            onCreateClick = { navController.navigate(Screen.CreateListing.route) }
                        )
                    }

                    else -> {
                        LazyColumn(
                            modifier = Modifier.fillMaxSize(),
                            contentPadding = PaddingValues(horizontal = 16.dp, vertical = 14.dp),
                            verticalArrangement = Arrangement.spacedBy(12.dp)
                        ) {
                            items(items = filtered, key = { it.id }) { listing ->
                                ListingItemRow(
                                    listing    = listing,
                                    onEdit     = { navController.navigate(Screen.EditListing.route(listing.id)) },
                                    onDelete   = { vm.requestDelete(listing.id) },
                                    onSold     = { vm.markAsSold(listing.id) },
                                    onPause    = { vm.pauseListing(listing.id) },
                                    onReactivate = { vm.reactivateListing(listing.id) },
                                    onClick    = { navController.navigate(Screen.ListingDetail.route(listing.id)) }
                                )
                            }
                        }
                    }
                }

                // Action loading overlay
                AnimatedVisibility(
                    visible = actionState is MyListingsViewModel.ActionState.Loading,
                    enter = fadeIn(),
                    exit = fadeOut(),
                    modifier = Modifier.fillMaxSize()
                ) {
                    Box(
                        modifier = Modifier
                            .fillMaxSize()
                            .background(androidx.compose.ui.graphics.Color.Black.copy(alpha = 0.3f)),
                        contentAlignment = Alignment.Center
                    ) {
                        Box(
                            modifier = Modifier
                                .size(72.dp)
                                .clip(RoundedCornerShape(16.dp))
                                .background(White),
                            contentAlignment = Alignment.Center
                        ) {
                            CircularProgressIndicator(
                                color = OceanBlue700,
                                modifier = Modifier.size(32.dp)
                            )
                        }
                    }
                }
            }
        }
    }

    // ── Delete confirmation dialog ─────────────────────────────────────────────
    if (pendingDelete != null) {
        AlertDialog(
            onDismissRequest = { vm.cancelDelete() },
            title = { Text("Supprimer l'annonce", fontWeight = FontWeight.Bold) },
            text = {
                Text(
                    "Cette action est irreversible. Souhaitez-vous vraiment supprimer cette annonce ?",
                    color = Gray500
                )
            },
            confirmButton = {
                TextButton(onClick = { vm.confirmDelete() }) {
                    Text("Supprimer", color = Error500, fontWeight = FontWeight.Bold)
                }
            },
            dismissButton = {
                TextButton(onClick = { vm.cancelDelete() }) {
                    Text("Annuler", color = Gray500)
                }
            },
            shape = RoundedCornerShape(16.dp),
            containerColor = White
        )
    }
}

// ─── Single listing row with 3-dot menu ──────────────────────────────────────

@Composable
private fun ListingItemRow(
    listing: Listing,
    onClick: () -> Unit,
    onEdit: () -> Unit,
    onDelete: () -> Unit,
    onSold: () -> Unit,
    onPause: () -> Unit,
    onReactivate: () -> Unit
) {
    var menuExpanded by remember { mutableStateOf(false) }

    ListingRowCard(
        listing = listing,
        onClick = onClick,
        trailingContent = {
            Column(
                horizontalAlignment = Alignment.End,
                verticalArrangement = Arrangement.spacedBy(4.dp)
            ) {
                // Status badge
                StatusBadge(status = listing.status, label = listing.statusLabel)

                // 3-dot menu
                Box {
                    IconButton(
                        onClick = { menuExpanded = true },
                        modifier = Modifier.size(32.dp)
                    ) {
                        Icon(
                            imageVector = Icons.Default.MoreVert,
                            contentDescription = "Options",
                            tint = Gray400,
                            modifier = Modifier.size(20.dp)
                        )
                    }

                    DropdownMenu(
                        expanded = menuExpanded,
                        onDismissRequest = { menuExpanded = false },
                        containerColor = White,
                        shape = RoundedCornerShape(12.dp)
                    ) {
                        when (listing.status) {
                            "active" -> {
                                MenuAction("Modifier",             OceanBlue700) { menuExpanded = false; onEdit() }
                                MenuAction("Marquer comme vendu",  Success500)   { menuExpanded = false; onSold() }
                                MenuAction("Mettre en pause",      Gold500)      { menuExpanded = false; onPause() }
                                MenuAction("Supprimer",            Error500)     { menuExpanded = false; onDelete() }
                            }
                            "paused" -> {
                                MenuAction("Reactiver", Success500)   { menuExpanded = false; onReactivate() }
                                MenuAction("Modifier",  OceanBlue700) { menuExpanded = false; onEdit() }
                                MenuAction("Supprimer", Error500)     { menuExpanded = false; onDelete() }
                            }
                            "draft" -> {
                                MenuAction("Publier",   OceanBlue700) { menuExpanded = false; onEdit() }
                                MenuAction("Modifier",  Gray700)      { menuExpanded = false; onEdit() }
                                MenuAction("Supprimer", Error500)     { menuExpanded = false; onDelete() }
                            }
                            else -> {
                                MenuAction("Supprimer", Error500) { menuExpanded = false; onDelete() }
                            }
                        }
                    }
                }
            }
        }
    )
}

// ─── Status badge ─────────────────────────────────────────────────────────────

@Composable
private fun StatusBadge(status: String, label: String) {
    val (bg, fg) = when (status) {
        "active"           -> Success100 to Success500
        "paused"           -> Gold100    to Gold500
        "draft"            -> Gray100    to Gray400
        "sold"             -> OceanBlue100 to OceanBlue500
        "awaiting_payment" -> Warning100 to Warning500
        "pending_review"   -> Teal100   to Teal500
        "expired"          -> Gray100   to Gray400
        "rejected"         -> Error100  to Error500
        else               -> Gray100   to Gray400
    }

    Box(
        modifier = Modifier
            .clip(RoundedCornerShape(50))
            .background(bg)
            .padding(horizontal = 8.dp, vertical = 3.dp)
    ) {
        Text(
            text = label,
            color = fg,
            fontSize = 10.sp,
            fontWeight = FontWeight.SemiBold,
            maxLines = 1
        )
    }
}

// ─── Filter tab chip ──────────────────────────────────────────────────────────

@Composable
private fun FilterTabChip(
    label: String,
    count: Int,
    selected: Boolean,
    onClick: () -> Unit
) {
    Surface(
        onClick = onClick,
        shape = RoundedCornerShape(50),
        color = if (selected) OceanBlue700 else Gray100,
        shadowElevation = if (selected) 2.dp else 0.dp
    ) {
        Row(
            modifier = Modifier.padding(horizontal = 13.dp, vertical = 7.dp),
            verticalAlignment = Alignment.CenterVertically,
            horizontalArrangement = Arrangement.spacedBy(5.dp)
        ) {
            Text(
                text = label,
                color = if (selected) White else Gray500,
                fontWeight = if (selected) FontWeight.Bold else FontWeight.Medium,
                fontSize = 13.sp
            )
            if (count > 0) {
                Box(
                    modifier = Modifier
                        .clip(CircleShape)
                        .background(if (selected) White.copy(alpha = 0.25f) else Gray200)
                        .padding(horizontal = 5.dp, vertical = 1.dp)
                ) {
                    Text(
                        text = count.toString(),
                        color = if (selected) White else Gray500,
                        fontSize = 10.sp,
                        fontWeight = FontWeight.Bold
                    )
                }
            }
        }
    }
}

// ─── Dropdown menu action row ─────────────────────────────────────────────────

@Composable
private fun MenuAction(
    label: String,
    color: androidx.compose.ui.graphics.Color,
    onClick: () -> Unit
) {
    DropdownMenuItem(
        text = {
            Text(
                text = label,
                color = color,
                fontWeight = FontWeight.Medium,
                fontSize = 14.sp
            )
        },
        onClick = onClick
    )
}

// ─── Empty state ──────────────────────────────────────────────────────────────

@Composable
private fun EmptyListingsState(
    hasListings: Boolean,
    onCreateClick: () -> Unit
) {
    Column(
        modifier = Modifier
            .fillMaxSize()
            .padding(32.dp),
        horizontalAlignment = Alignment.CenterHorizontally,
        verticalArrangement = Arrangement.Center
    ) {
        Box(
            modifier = Modifier
                .size(100.dp)
                .clip(CircleShape)
                .background(Brush.radialGradient(listOf(OceanBlue100, OceanBlue50))),
            contentAlignment = Alignment.Center
        ) {
            Text(text = "\u2693", fontSize = 44.sp)
        }

        Spacer(modifier = Modifier.height(24.dp))

        Text(
            text = if (hasListings) "Aucune annonce dans cette categorie"
                   else "Vous n'avez pas encore d'annonces",
            style = MaterialTheme.typography.titleMedium.copy(
                fontWeight = FontWeight.Bold,
                color = Gray900
            ),
            textAlign = TextAlign.Center
        )

        Spacer(modifier = Modifier.height(8.dp))

        Text(
            text = if (hasListings) "Essayez un autre filtre"
                   else "Publiez votre premiere annonce et touchez des acheteurs serieux",
            style = MaterialTheme.typography.bodyMedium.copy(color = Gray400),
            textAlign = TextAlign.Center
        )

        if (!hasListings) {
            Spacer(modifier = Modifier.height(28.dp))

            Button(
                onClick = onCreateClick,
                colors = ButtonDefaults.buttonColors(containerColor = OceanBlue700),
                shape = RoundedCornerShape(12.dp),
                contentPadding = PaddingValues(horizontal = 28.dp, vertical = 14.dp)
            ) {
                Icon(
                    imageVector = Icons.Default.Add,
                    contentDescription = null,
                    modifier = Modifier.size(18.dp)
                )
                Spacer(modifier = Modifier.width(8.dp))
                Text("Creer une annonce", fontWeight = FontWeight.SemiBold)
            }
        }
    }
}

// ─── Shimmer skeleton row ─────────────────────────────────────────────────────

@Composable
private fun ShimmerRowCard() {
    Card(
        shape = RoundedCornerShape(12.dp),
        colors = CardDefaults.cardColors(containerColor = White),
        elevation = CardDefaults.cardElevation(defaultElevation = 1.dp),
        modifier = Modifier.fillMaxWidth()
    ) {
        Row(
            modifier = Modifier
                .fillMaxWidth()
                .padding(10.dp),
            verticalAlignment = Alignment.CenterVertically
        ) {
            Box(
                modifier = Modifier
                    .size(80.dp)
                    .clip(RoundedCornerShape(10.dp))
                    .placeholder(visible = true, highlight = PlaceholderHighlight.fade())
            )
            Spacer(modifier = Modifier.width(12.dp))
            Column(
                modifier = Modifier.weight(1f),
                verticalArrangement = Arrangement.spacedBy(8.dp)
            ) {
                Box(
                    modifier = Modifier
                        .fillMaxWidth(0.75f)
                        .height(14.dp)
                        .clip(RoundedCornerShape(4.dp))
                        .placeholder(visible = true, highlight = PlaceholderHighlight.fade())
                )
                Box(
                    modifier = Modifier
                        .fillMaxWidth(0.4f)
                        .height(14.dp)
                        .clip(RoundedCornerShape(4.dp))
                        .placeholder(visible = true, highlight = PlaceholderHighlight.fade())
                )
                Box(
                    modifier = Modifier
                        .width(80.dp)
                        .height(22.dp)
                        .clip(RoundedCornerShape(50))
                        .placeholder(visible = true, highlight = PlaceholderHighlight.fade())
                )
            }
        }
    }
}

// ─── Private color alias ──────────────────────────────────────────────────────
private val Gray700 = com.albabor.app.ui.theme.Gray700
