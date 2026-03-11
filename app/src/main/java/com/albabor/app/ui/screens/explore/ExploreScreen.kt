package com.albabor.app.ui.screens.explore

import androidx.compose.foundation.background
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyRow
import androidx.compose.foundation.lazy.grid.GridCells
import androidx.compose.foundation.lazy.grid.LazyVerticalGrid
import androidx.compose.foundation.lazy.grid.items
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.text.KeyboardActions
import androidx.compose.foundation.text.KeyboardOptions
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ArrowBack
import androidx.compose.material.icons.filled.*
import androidx.compose.material3.*
import androidx.compose.material3.pulltorefresh.PullToRefreshBox
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.draw.shadow
import androidx.compose.ui.focus.FocusRequester
import androidx.compose.ui.focus.focusRequester
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.platform.LocalFocusManager
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.input.ImeAction
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.lifecycle.compose.collectAsStateWithLifecycle
import androidx.lifecycle.viewmodel.compose.viewModel
import androidx.navigation.NavController
import com.albabor.app.data.model.ListingFilters
import com.albabor.app.ui.components.ListingCard
import com.albabor.app.ui.components.LoadingGrid
import com.albabor.app.ui.navigation.Screen
import com.albabor.app.ui.theme.*
import com.albabor.app.viewmodel.ExploreViewModel

// ─── Filter option definitions ──────────────────────────────────────────────

private data class RadioOption(val key: String?, val label: String)

private val wilayas = listOf(
    "Adrar", "Chlef", "Laghouat", "Oum El Bouaghi", "Batna", "Béjaïa",
    "Biskra", "Béchar", "Blida", "Bouira", "Tamanrasset", "Tébessa",
    "Tlemcen", "Tiaret", "Tizi Ouzou", "Alger", "Djelfa", "Jijel",
    "Sétif", "Saïda", "Skikda", "Sidi Bel Abbès", "Annaba", "Guelma",
    "Constantine", "Médéa", "Mostaganem", "M'Sila", "Mascara", "Ouargla",
    "Oran", "El Bayadh", "Illizi", "Bordj Bou Arréridj", "Boumerdès",
    "El Tarf", "Tindouf", "Tissemsilt", "El Oued", "Khenchela",
    "Souk Ahras", "Tipaza", "Mila", "Aïn Defla", "Naâma", "Aïn Témouchent",
    "Ghardaïa", "Relizane", "El M'Ghair", "El Meniaa", "Ouled Djellal",
    "Bordj Baji Mokhtar", "Béni Abbès", "Timimoun", "Touggourt", "Djanet",
    "In Salah", "In Guezzam"
)

private val categoryOptions = listOf(
    RadioOption(null, "Tous"), RadioOption("boat", "Bateaux"), RadioOption("jetski", "Jet-Skis"),
    RadioOption("engine", "Moteurs"), RadioOption("parts", "Pièces"),
)

private val conditionOptions = listOf(
    RadioOption(null, "Tous"), RadioOption("new", "Jamais utilisé"), RadioOption("like_new", "Comme neuf"),
    RadioOption("good", "Bon état"), RadioOption("average", "État moyen"), RadioOption("needs_revision", "À réviser"),
)

private val offerTypeOptions = listOf(
    RadioOption(null, "Tous"), RadioOption("negotiable", "Négociable"),
    RadioOption("fixed", "Prix fixe"), RadioOption("free", "Offert"),
)

private val sortOptions = listOf(
    RadioOption("newest", "Plus récentes"), RadioOption("price_asc", "Prix croissant"),
    RadioOption("price_desc", "Prix décroissant"), RadioOption("popular", "Plus populaires"),
)

// ─── Screen entry point ─────────────────────────────────────────────────────

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun ExploreScreen(
    navController: NavController,
    viewModel: ExploreViewModel = viewModel()
) {
    val filters    by viewModel.filters.collectAsStateWithLifecycle()
    val listings   by viewModel.listings.collectAsStateWithLifecycle()
    val isLoading  by viewModel.isLoading.collectAsStateWithLifecycle()
    val totalCount by viewModel.totalCount.collectAsStateWithLifecycle()

    var showFilterSheet by remember { mutableStateOf(false) }
    var localSearch     by remember { mutableStateOf(filters.search ?: "") }

    val focusManager = LocalFocusManager.current
    val searchFocus  = remember { FocusRequester() }

    val activeCount = remember(filters) {
        listOfNotNull(
            filters.category, filters.wilaya, filters.condition, filters.offerType,
            filters.minPrice, filters.maxPrice, filters.sortBy?.takeIf { it != "newest" }
        ).size
    }

    Scaffold(containerColor = AppBackground) { padding ->
        Column(Modifier.fillMaxSize().padding(padding)) {
            // ── Search bar ──────────────────────────────────────────────
            SearchBar(
                query        = localSearch,
                onQuery      = { localSearch = it },
                onSearch     = { viewModel.updateSearch(localSearch); focusManager.clearFocus() },
                onClear      = { localSearch = ""; viewModel.updateSearch("") },
                onBack       = { navController.popBackStack() },
                focusReq     = searchFocus
            )

            // ── Filter chips ────────────────────────────────────────────
            FilterRow(filters, activeCount) { showFilterSheet = true }

            // ── Results count ────────────────────────────────────────────
            ResultsBar(totalCount, isLoading)

            // ── Grid ────────────────────────────────────────────────────
            PullToRefreshBox(
                isRefreshing = isLoading,
                onRefresh    = { viewModel.search() },
                modifier     = Modifier.fillMaxSize()
            ) {
                if (isLoading && listings.isEmpty()) {
                    Box(Modifier.padding(14.dp)) { LoadingGrid(count = 8) }
                } else if (!isLoading && listings.isEmpty()) {
                    EmptyState(activeCount > 0 || filters.search != null) {
                        viewModel.clearFilters(); localSearch = ""
                    }
                } else {
                    LazyVerticalGrid(
                        columns            = GridCells.Fixed(2),
                        contentPadding     = PaddingValues(start = 14.dp, end = 14.dp, top = 6.dp, bottom = 100.dp),
                        verticalArrangement   = Arrangement.spacedBy(14.dp),
                        horizontalArrangement = Arrangement.spacedBy(14.dp),
                        modifier           = Modifier.fillMaxSize()
                    ) {
                        items(listings, key = { it.id }) { listing ->
                            ListingCard(
                                listing    = listing,
                                onClick    = { navController.navigate(Screen.ListingDetail.route(listing.id)) },
                                onFavorite = { /* TODO */ }
                            )
                        }
                    }
                }
            }
        }
    }

    if (showFilterSheet) {
        FilterSheet(filters,
            onApply  = { viewModel.updateFilters(it); showFilterSheet = false },
            onDismiss = { showFilterSheet = false }
        )
    }
}

// ═════════════════════════════════════════════════════════════════════════════
//  SEARCH BAR — floating card style
// ═════════════════════════════════════════════════════════════════════════════

@Composable
private fun SearchBar(
    query: String, onQuery: (String) -> Unit, onSearch: () -> Unit,
    onClear: () -> Unit, onBack: () -> Unit, focusReq: FocusRequester
) {
    Surface(
        Modifier.fillMaxWidth(),
        color           = Color.White,
        shadowElevation = 6.dp
    ) {
        Row(
            Modifier.fillMaxWidth().statusBarsPadding()
                .padding(horizontal = 10.dp, vertical = 10.dp),
            verticalAlignment = Alignment.CenterVertically,
            horizontalArrangement = Arrangement.spacedBy(8.dp)
        ) {
            // Back button in subtle circle
            Box(
                Modifier.size(40.dp).clip(CircleShape).background(AppBackground)
                    .clickable(onClick = onBack),
                contentAlignment = Alignment.Center
            ) {
                Icon(Icons.AutoMirrored.Filled.ArrowBack, "Retour", tint = OceanBlue700, modifier = Modifier.size(20.dp))
            }

            // Search field — rounded with gradient focus
            Surface(
                Modifier.weight(1f).height(46.dp),
                shape = RoundedCornerShape(14.dp),
                color = AppBackground,
                shadowElevation = 0.dp
            ) {
                TextField(
                    value         = query,
                    onValueChange = onQuery,
                    modifier      = Modifier.fillMaxSize().focusRequester(focusReq),
                    placeholder   = {
                        Text("Rechercher bateaux, jet-skis...",
                            style = MaterialTheme.typography.bodyMedium, color = Gray400)
                    },
                    singleLine    = true,
                    leadingIcon   = {
                        Icon(Icons.Filled.Search, null, tint = OceanBlue500, modifier = Modifier.size(20.dp))
                    },
                    trailingIcon  = {
                        if (query.isNotEmpty()) {
                            IconButton(onClick = onClear) {
                                Icon(Icons.Filled.Clear, "Effacer", tint = Gray400, modifier = Modifier.size(17.dp))
                            }
                        }
                    },
                    keyboardOptions = KeyboardOptions(imeAction = ImeAction.Search),
                    keyboardActions = KeyboardActions(onSearch = { onSearch() }),
                    colors = TextFieldDefaults.colors(
                        focusedContainerColor    = Color.Transparent,
                        unfocusedContainerColor  = Color.Transparent,
                        focusedIndicatorColor    = Color.Transparent,
                        unfocusedIndicatorColor  = Color.Transparent,
                        focusedTextColor         = Gray900,
                        unfocusedTextColor       = Gray900
                    )
                )
            }
        }
    }
}

// ═════════════════════════════════════════════════════════════════════════════
//  FILTER ROW — scrollable styled chips
// ═════════════════════════════════════════════════════════════════════════════

@Composable
private fun FilterRow(filters: ListingFilters, activeCount: Int, onOpen: () -> Unit) {
    Surface(
        Modifier.fillMaxWidth(),
        color = Color.White,
        shadowElevation = 2.dp
    ) {
        LazyRow(
            Modifier.fillMaxWidth().padding(vertical = 10.dp),
            contentPadding        = PaddingValues(horizontal = 14.dp),
            horizontalArrangement = Arrangement.spacedBy(8.dp),
            verticalAlignment     = Alignment.CenterVertically
        ) {
            // Filter button with badge
            item {
                BadgedBox(badge = {
                    if (activeCount > 0) Badge(containerColor = OceanBlue700, contentColor = Color.White) {
                        Text("$activeCount", fontSize = 10.sp)
                    }
                }) {
                    StyledChip(
                        label  = "Filtres",
                        active = activeCount > 0,
                        icon   = Icons.Filled.Tune,
                        onClick = onOpen
                    )
                }
            }

            item {
                StyledChip(
                    label  = filters.category?.let {
                        when (it) { "boat" -> "Bateaux"; "jetski" -> "Jet-Skis"; "engine" -> "Moteurs"; "parts" -> "Pièces"; else -> it }
                    } ?: "Catégorie",
                    active = filters.category != null,
                    onClick = onOpen
                )
            }

            item {
                StyledChip(label = filters.wilaya ?: "Wilaya", active = filters.wilaya != null, onClick = onOpen)
            }

            item {
                val lbl = when {
                    filters.minPrice != null && filters.maxPrice != null -> "${filters.minPrice.toLong()} – ${filters.maxPrice.toLong()}"
                    filters.minPrice != null -> "Min ${filters.minPrice.toLong()}"
                    filters.maxPrice != null -> "Max ${filters.maxPrice.toLong()}"
                    else -> "Prix"
                }
                StyledChip(label = lbl, active = filters.minPrice != null || filters.maxPrice != null, onClick = onOpen)
            }

            item {
                StyledChip(
                    label  = filters.condition?.let { conditionOptions.find { o -> o.key == it }?.label } ?: "État",
                    active = filters.condition != null, onClick = onOpen
                )
            }

            item {
                StyledChip(
                    label  = filters.sortBy?.let { sortOptions.find { o -> o.key == it }?.label } ?: "Trier",
                    active = filters.sortBy != null && filters.sortBy != "newest", onClick = onOpen
                )
            }
        }
    }
}

@Composable
private fun StyledChip(
    label: String,
    active: Boolean,
    icon: androidx.compose.ui.graphics.vector.ImageVector? = null,
    onClick: () -> Unit
) {
    val shape = RoundedCornerShape(12.dp)

    Surface(
        modifier = Modifier.clip(shape).clickable(onClick = onClick),
        shape    = shape,
        color    = if (active) OceanBlue700 else AppBackground,
        border   = if (!active) androidx.compose.foundation.BorderStroke(1.dp, Gray200) else null,
        shadowElevation = if (active) 4.dp else 0.dp
    ) {
        Row(
            Modifier.padding(horizontal = 14.dp, vertical = 8.dp),
            verticalAlignment = Alignment.CenterVertically,
            horizontalArrangement = Arrangement.spacedBy(5.dp)
        ) {
            icon?.let {
                Icon(it, null, tint = if (active) Color.White else Gray500, modifier = Modifier.size(15.dp))
            }
            Text(
                label,
                fontSize   = 12.sp,
                fontWeight = if (active) FontWeight.SemiBold else FontWeight.Medium,
                color      = if (active) Color.White else Gray700,
                maxLines   = 1
            )
        }
    }
}

// ═════════════════════════════════════════════════════════════════════════════
//  RESULTS BAR
// ═════════════════════════════════════════════════════════════════════════════

@Composable
private fun ResultsBar(total: Int, loading: Boolean) {
    Row(
        Modifier.fillMaxWidth().padding(horizontal = 16.dp, vertical = 10.dp),
        verticalAlignment = Alignment.CenterVertically,
        horizontalArrangement = Arrangement.SpaceBetween
    ) {
        Text(
            if (loading) "Recherche en cours..."
            else if (total == 0) "Aucune annonce trouvée"
            else "$total annonce${if (total > 1) "s" else ""} trouvée${if (total > 1) "s" else ""}",
            fontSize   = 13.sp,
            color      = Gray500,
            fontWeight = FontWeight.Medium
        )
    }
}

// ═════════════════════════════════════════════════════════════════════════════
//  EMPTY STATE
// ═════════════════════════════════════════════════════════════════════════════

@Composable
private fun EmptyState(hasFilters: Boolean, onClear: () -> Unit) {
    Column(
        Modifier.fillMaxSize().padding(40.dp),
        horizontalAlignment = Alignment.CenterHorizontally,
        verticalArrangement = Arrangement.Center
    ) {
        // Icon circle
        Box(
            Modifier.size(80.dp).clip(CircleShape)
                .background(Brush.linearGradient(listOf(OceanBlue100, Teal100))),
            contentAlignment = Alignment.Center
        ) {
            Icon(Icons.Default.SearchOff, null, tint = OceanBlue500, modifier = Modifier.size(40.dp))
        }

        Spacer(Modifier.height(20.dp))

        Text(
            if (hasFilters) "Aucune annonce ne correspond à vos filtres"
            else "Aucune annonce disponible pour le moment",
            style     = MaterialTheme.typography.titleSmall,
            color     = Gray700,
            textAlign = TextAlign.Center,
            lineHeight = 22.sp
        )

        if (hasFilters) {
            Spacer(Modifier.height(20.dp))
            Button(
                onClick = onClear,
                shape   = RoundedCornerShape(14.dp),
                colors  = ButtonDefaults.buttonColors(containerColor = Color.Transparent),
                contentPadding = PaddingValues(0.dp)
            ) {
                Box(
                    Modifier.background(
                        Brush.linearGradient(listOf(OceanBlue700, Teal500)),
                        RoundedCornerShape(14.dp)
                    ).padding(horizontal = 24.dp, vertical = 12.dp)
                ) {
                    Text("Effacer les filtres", color = Color.White, fontWeight = FontWeight.SemiBold)
                }
            }
        }
    }
}

// ═════════════════════════════════════════════════════════════════════════════
//  FILTER BOTTOM SHEET
// ═════════════════════════════════════════════════════════════════════════════

@OptIn(ExperimentalMaterial3Api::class)
@Composable
private fun FilterSheet(
    currentFilters: ListingFilters,
    onApply: (ListingFilters) -> Unit,
    onDismiss: () -> Unit
) {
    var cat       by remember { mutableStateOf(currentFilters.category) }
    var wilaya    by remember { mutableStateOf(currentFilters.wilaya) }
    var minP      by remember { mutableStateOf(currentFilters.minPrice?.toLong()?.toString() ?: "") }
    var maxP      by remember { mutableStateOf(currentFilters.maxPrice?.toLong()?.toString() ?: "") }
    var cond      by remember { mutableStateOf(currentFilters.condition) }
    var offer     by remember { mutableStateOf(currentFilters.offerType) }
    var sort      by remember { mutableStateOf(currentFilters.sortBy ?: "newest") }
    var wSearch   by remember { mutableStateOf("") }

    ModalBottomSheet(
        onDismissRequest = onDismiss,
        sheetState       = rememberModalBottomSheetState(skipPartiallyExpanded = true),
        containerColor   = Color.White,
        shape            = RoundedCornerShape(topStart = 24.dp, topEnd = 24.dp)
    ) {
        Column(Modifier.fillMaxWidth().navigationBarsPadding()) {
            // Header
            Row(
                Modifier.fillMaxWidth().padding(horizontal = 20.dp, vertical = 6.dp),
                horizontalArrangement = Arrangement.SpaceBetween,
                verticalAlignment     = Alignment.CenterVertically
            ) {
                Text("Filtres", style = MaterialTheme.typography.titleLarge, fontWeight = FontWeight.Bold, color = Gray900)
                TextButton(onClick = {
                    cat = null; wilaya = null; minP = ""; maxP = ""
                    cond = null; offer = null; sort = "newest"; wSearch = ""
                }) {
                    Text("Effacer tout", color = Error500, fontWeight = FontWeight.SemiBold)
                }
            }

            HorizontalDivider(color = Gray100)

            // Content
            Column(
                Modifier.weight(1f, false).verticalScroll(rememberScrollState())
                    .padding(horizontal = 20.dp, vertical = 14.dp),
                verticalArrangement = Arrangement.spacedBy(22.dp)
            ) {
                Section("Catégorie") { categoryOptions.forEach { RadioRow(it.label, cat == it.key) { cat = it.key } } }
                HorizontalDivider(color = Gray100)

                Section("Wilaya") {
                    OutlinedTextField(
                        wSearch, { wSearch = it }, Modifier.fillMaxWidth(),
                        placeholder = { Text("Rechercher une wilaya...", color = Gray400) },
                        singleLine = true,
                        leadingIcon = { Icon(Icons.Filled.Search, null, Modifier.size(18.dp), tint = Gray400) },
                        trailingIcon = { if (wSearch.isNotEmpty()) IconButton({ wSearch = "" }) { Icon(Icons.Filled.Clear, null, Modifier.size(16.dp)) } },
                        shape = RoundedCornerShape(12.dp),
                        colors = OutlinedTextFieldDefaults.colors(focusedBorderColor = OceanBlue500, unfocusedBorderColor = Gray200)
                    )
                    Spacer(Modifier.height(4.dp))
                    RadioRow("Toutes les wilayas", wilaya == null) { wilaya = null }
                    val filtered = remember(wSearch) {
                        if (wSearch.isBlank()) wilayas else wilayas.filter { it.contains(wSearch.trim(), true) }
                    }
                    filtered.forEach { w -> RadioRow(w, wilaya == w) { wilaya = w } }
                }
                HorizontalDivider(color = Gray100)

                Section("Fourchette de prix (DA)") {
                    Row(horizontalArrangement = Arrangement.spacedBy(12.dp), verticalAlignment = Alignment.CenterVertically) {
                        OutlinedTextField(minP, { minP = it.filter { c -> c.isDigit() } }, Modifier.weight(1f),
                            label = { Text("Min") }, singleLine = true, shape = RoundedCornerShape(12.dp),
                            colors = OutlinedTextFieldDefaults.colors(focusedBorderColor = OceanBlue500, unfocusedBorderColor = Gray200))
                        Text("–", color = Gray400)
                        OutlinedTextField(maxP, { maxP = it.filter { c -> c.isDigit() } }, Modifier.weight(1f),
                            label = { Text("Max") }, singleLine = true, shape = RoundedCornerShape(12.dp),
                            colors = OutlinedTextFieldDefaults.colors(focusedBorderColor = OceanBlue500, unfocusedBorderColor = Gray200))
                    }
                }
                HorizontalDivider(color = Gray100)

                Section("État") { conditionOptions.forEach { RadioRow(it.label, cond == it.key) { cond = it.key } } }
                HorizontalDivider(color = Gray100)
                Section("Type d'offre") { offerTypeOptions.forEach { RadioRow(it.label, offer == it.key) { offer = it.key } } }
                HorizontalDivider(color = Gray100)
                Section("Trier par") { sortOptions.forEach { RadioRow(it.label, sort == it.key) { sort = it.key ?: "newest" } } }
                Spacer(Modifier.height(8.dp))
            }

            // Action buttons
            HorizontalDivider(color = Gray200)
            Row(
                Modifier.fillMaxWidth().padding(horizontal = 20.dp, vertical = 14.dp),
                horizontalArrangement = Arrangement.spacedBy(12.dp)
            ) {
                OutlinedButton(onDismiss, Modifier.weight(1f), shape = RoundedCornerShape(14.dp),
                    colors = ButtonDefaults.outlinedButtonColors(contentColor = OceanBlue700),
                    border = androidx.compose.foundation.BorderStroke(1.5.dp, OceanBlue700)
                ) { Text("Annuler", fontWeight = FontWeight.SemiBold) }

                Button(
                    onClick = {
                        onApply(ListingFilters(
                            search = currentFilters.search, category = cat, wilaya = wilaya,
                            minPrice = minP.toDoubleOrNull(), maxPrice = maxP.toDoubleOrNull(),
                            condition = cond, offerType = offer,
                            sortBy = sort.takeIf { it != "newest" }, page = 1
                        ))
                    },
                    Modifier.weight(1f), shape = RoundedCornerShape(14.dp),
                    colors = ButtonDefaults.buttonColors(containerColor = Color.Transparent),
                    contentPadding = PaddingValues(0.dp)
                ) {
                    Box(
                        Modifier.fillMaxWidth().background(
                            Brush.linearGradient(listOf(OceanBlue700, Teal500)),
                            RoundedCornerShape(14.dp)
                        ).padding(vertical = 12.dp),
                        contentAlignment = Alignment.Center
                    ) { Text("Appliquer", color = Color.White, fontWeight = FontWeight.Bold) }
                }
            }
        }
    }
}

@Composable
private fun Section(title: String, content: @Composable ColumnScope.() -> Unit) {
    Column(verticalArrangement = Arrangement.spacedBy(4.dp)) {
        Text(title, style = MaterialTheme.typography.titleSmall, fontWeight = FontWeight.Bold, color = OceanBlue900)
        Spacer(Modifier.height(4.dp))
        content()
    }
}

@Composable
private fun RadioRow(label: String, selected: Boolean, onClick: () -> Unit) {
    Row(
        Modifier.fillMaxWidth().clip(RoundedCornerShape(10.dp)).clickable(onClick = onClick)
            .background(if (selected) OceanBlue50 else Color.Transparent)
            .padding(horizontal = 6.dp, vertical = 7.dp),
        verticalAlignment = Alignment.CenterVertically,
        horizontalArrangement = Arrangement.spacedBy(8.dp)
    ) {
        RadioButton(selected, onClick,
            colors = RadioButtonDefaults.colors(selectedColor = OceanBlue700, unselectedColor = Gray300))
        Text(label, style = MaterialTheme.typography.bodyMedium,
            color = if (selected) OceanBlue900 else Gray700,
            fontWeight = if (selected) FontWeight.SemiBold else FontWeight.Normal)
    }
}
