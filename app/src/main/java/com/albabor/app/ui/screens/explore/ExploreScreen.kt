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
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.text.KeyboardActions
import androidx.compose.foundation.text.KeyboardOptions
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.ArrowBack
import androidx.compose.material.icons.filled.Clear
import androidx.compose.material.icons.filled.FilterList
import androidx.compose.material.icons.filled.Search
import androidx.compose.material3.*
import androidx.compose.material3.pulltorefresh.PullToRefreshBox
import androidx.compose.material3.pulltorefresh.rememberPullToRefreshState
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.focus.FocusRequester
import androidx.compose.ui.focus.focusRequester
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
import com.albabor.app.ui.components.FilterChip
import com.albabor.app.ui.components.ListingCard
import com.albabor.app.ui.components.LoadingGrid
import com.albabor.app.ui.navigation.Screen
import com.albabor.app.ui.theme.*
import com.albabor.app.viewmodel.ExploreViewModel

// ─── Algeria's wilayas ────────────────────────────────────────────────────────

private val wilayas = listOf(
    "Adrar", "Alger", "Annaba", "Batna", "Béjaïa", "Biskra", "Blida",
    "Bordj Bou Arréridj", "Bouira", "Boumerdès", "Chlef", "Constantine",
    "Djelfa", "El Bayadh", "El Oued", "El Tarf", "Ghardaïa", "Guelma",
    "Illizi", "Jijel", "Khenchela", "Laghouat", "Mascara", "Médéa",
    "Mila", "Mostaganem", "M'Sila", "Naâma", "Oran", "Ouargla",
    "Oum El Bouaghi", "Relizane", "Saïda", "Sétif", "Sidi Bel Abbès",
    "Skikda", "Souk Ahras", "Tamanrasset", "Tébessa", "Tiaret",
    "Tindouf", "Tipaza", "Tissemsilt", "Tizi Ouzou", "Tlemcen"
)

// ─── Filter option definitions ────────────────────────────────────────────────

private data class RadioOption(val key: String?, val label: String)

private val categoryOptions = listOf(
    RadioOption(null,     "Tous"),
    RadioOption("boat",   "Bateaux ⛵"),
    RadioOption("jetski", "Jet-Skis 🚤"),
    RadioOption("engine", "Moteurs ⚙️"),
    RadioOption("parts",  "Pièces 🔩"),
)

private val conditionOptions = listOf(
    RadioOption(null,              "Tous"),
    RadioOption("new",             "Jamais utilisé"),
    RadioOption("like_new",        "Comme neuf"),
    RadioOption("good",            "Bon état"),
    RadioOption("average",         "État moyen"),
    RadioOption("needs_revision",  "À réviser"),
)

private val offerTypeOptions = listOf(
    RadioOption(null,         "Tous"),
    RadioOption("negotiable", "Négociable"),
    RadioOption("fixed",      "Prix fixe"),
    RadioOption("free",       "Offert"),
)

private val sortOptions = listOf(
    RadioOption("newest",     "Plus récentes"),
    RadioOption("price_asc",  "Prix croissant"),
    RadioOption("price_desc", "Prix décroissant"),
    RadioOption("popular",    "Plus populaires"),
)

// ─── Screen entry point ───────────────────────────────────────────────────────

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun ExploreScreen(
    navController: NavController,
    viewModel: ExploreViewModel = viewModel()
) {
    val filters by viewModel.filters.collectAsStateWithLifecycle()
    val listings by viewModel.listings.collectAsStateWithLifecycle()
    val isLoading by viewModel.isLoading.collectAsStateWithLifecycle()
    val totalCount by viewModel.totalCount.collectAsStateWithLifecycle()

    var showFilterSheet by remember { mutableStateOf(false) }
    var localSearch by remember { mutableStateOf(filters.search ?: "") }

    val focusManager = LocalFocusManager.current
    val searchFocusRequester = remember { FocusRequester() }
    val refreshState = rememberPullToRefreshState()

    // Active filter count for badge
    val activeFilterCount = remember(filters) {
        listOfNotNull(
            filters.category,
            filters.wilaya,
            filters.condition,
            filters.offerType,
            filters.minPrice,
            filters.maxPrice,
            filters.sortBy?.takeIf { it != "newest" }
        ).size
    }

    Scaffold(
        containerColor = MaterialTheme.colorScheme.background,
        topBar = {
            SearchTopBar(
                query = localSearch,
                onQueryChange = { localSearch = it },
                onSearch = {
                    viewModel.updateSearch(localSearch)
                    focusManager.clearFocus()
                },
                onClearQuery = {
                    localSearch = ""
                    viewModel.updateSearch("")
                },
                onNavigateBack = { navController.popBackStack() },
                focusRequester = searchFocusRequester
            )
        }
    ) { innerPadding ->
        Column(
            modifier = Modifier
                .fillMaxSize()
                .padding(innerPadding)
        ) {
            // ── Filter chips row ──────────────────────────────────────────────
            FilterChipsRow(
                filters = filters,
                activeFilterCount = activeFilterCount,
                onOpenFilters = { showFilterSheet = true }
            )

            // ── Results count ─────────────────────────────────────────────────
            ResultsCountBar(
                total = totalCount,
                isLoading = isLoading
            )

            // ── Grid ──────────────────────────────────────────────────────────
            PullToRefreshBox(
                isRefreshing = isLoading,
                onRefresh = { viewModel.search() },
                state = refreshState,
                modifier = Modifier.fillMaxSize()
            ) {
                if (isLoading && listings.isEmpty()) {
                    Box(modifier = Modifier.padding(16.dp)) {
                        LoadingGrid(count = 8)
                    }
                } else if (!isLoading && listings.isEmpty()) {
                    EmptyResults(
                        hasFilters = activeFilterCount > 0 || filters.search != null,
                        onClearFilters = {
                            viewModel.clearFilters()
                            localSearch = ""
                        }
                    )
                } else {
                    LazyVerticalGrid(
                        columns = GridCells.Fixed(2),
                        contentPadding = PaddingValues(
                            start = 12.dp,
                            end = 12.dp,
                            top = 4.dp,
                            bottom = 100.dp
                        ),
                        verticalArrangement = Arrangement.spacedBy(12.dp),
                        horizontalArrangement = Arrangement.spacedBy(12.dp),
                        modifier = Modifier.fillMaxSize()
                    ) {
                        items(listings, key = { it.id }) { listing ->
                            ListingCard(
                                listing = listing,
                                onClick = {
                                    navController.navigate(Screen.ListingDetail.route(listing.id))
                                },
                                onFavorite = { /* TODO: wire favorite */ }
                            )
                        }
                    }
                }
            }
        }
    }

    // ── Filter bottom sheet ───────────────────────────────────────────────────
    if (showFilterSheet) {
        FilterBottomSheet(
            currentFilters = filters,
            onApply = { newFilters ->
                viewModel.updateFilters(newFilters)
                showFilterSheet = false
            },
            onDismiss = { showFilterSheet = false }
        )
    }
}

// ─── Search TopAppBar ─────────────────────────────────────────────────────────

@OptIn(ExperimentalMaterial3Api::class)
@Composable
private fun SearchTopBar(
    query: String,
    onQueryChange: (String) -> Unit,
    onSearch: () -> Unit,
    onClearQuery: () -> Unit,
    onNavigateBack: () -> Unit,
    focusRequester: FocusRequester
) {
    Surface(
        modifier = Modifier.fillMaxWidth(),
        color = MaterialTheme.colorScheme.surface,
        shadowElevation = 4.dp
    ) {
        Row(
            modifier = Modifier
                .fillMaxWidth()
                .statusBarsPadding()
                .padding(horizontal = 8.dp, vertical = 8.dp),
            verticalAlignment = Alignment.CenterVertically,
            horizontalArrangement = Arrangement.spacedBy(4.dp)
        ) {
            IconButton(onClick = onNavigateBack) {
                Icon(
                    imageVector = Icons.Filled.ArrowBack,
                    contentDescription = "Retour",
                    tint = OceanBlue700
                )
            }

            TextField(
                value = query,
                onValueChange = onQueryChange,
                modifier = Modifier
                    .weight(1f)
                    .focusRequester(focusRequester),
                placeholder = {
                    Text(
                        text = "Rechercher bateaux, jet-skis...",
                        style = MaterialTheme.typography.bodyMedium,
                        color = MaterialTheme.colorScheme.onSurfaceVariant
                    )
                },
                singleLine = true,
                leadingIcon = {
                    Icon(
                        Icons.Filled.Search,
                        contentDescription = null,
                        tint = OceanBlue500,
                        modifier = Modifier.size(20.dp)
                    )
                },
                trailingIcon = {
                    if (query.isNotEmpty()) {
                        IconButton(onClick = onClearQuery) {
                            Icon(
                                Icons.Filled.Clear,
                                contentDescription = "Effacer",
                                modifier = Modifier.size(18.dp)
                            )
                        }
                    }
                },
                keyboardOptions = KeyboardOptions(imeAction = ImeAction.Search),
                keyboardActions = KeyboardActions(onSearch = { onSearch() }),
                colors = TextFieldDefaults.colors(
                    focusedContainerColor = OceanBlue50,
                    unfocusedContainerColor = MaterialTheme.colorScheme.surfaceVariant,
                    disabledContainerColor = MaterialTheme.colorScheme.surfaceVariant,
                    focusedIndicatorColor = Color.Transparent,
                    unfocusedIndicatorColor = Color.Transparent,
                    disabledIndicatorColor = Color.Transparent,
                    focusedTextColor = MaterialTheme.colorScheme.onSurface,
                    unfocusedTextColor = MaterialTheme.colorScheme.onSurface
                ),
                shape = RoundedCornerShape(12.dp)
            )
        }
    }
}

// ─── Filter chips row (category, wilaya, price, condition, sort) ──────────────

@Composable
private fun FilterChipsRow(
    filters: ListingFilters,
    activeFilterCount: Int,
    onOpenFilters: () -> Unit
) {
    LazyRow(
        modifier = Modifier
            .fillMaxWidth()
            .background(MaterialTheme.colorScheme.surface)
            .padding(horizontal = 12.dp, vertical = 10.dp),
        horizontalArrangement = Arrangement.spacedBy(8.dp),
        verticalAlignment = Alignment.CenterVertically
    ) {
        // Filter button with badge
        item {
            BadgedBox(
                badge = {
                    if (activeFilterCount > 0) {
                        Badge(
                            containerColor = OceanBlue700,
                            contentColor = White
                        ) {
                            Text(
                                text = activeFilterCount.toString(),
                                fontSize = 10.sp
                            )
                        }
                    }
                }
            ) {
                FilterChip(
                    label = "Filtres",
                    active = activeFilterCount > 0,
                    onClick = onOpenFilters
                )
            }
        }

        // Category quick chip
        item {
            FilterChip(
                label = filters.category?.let { key ->
                    when (key) {
                        "boat"   -> "Bateaux ⛵"
                        "jetski" -> "Jet-Skis 🚤"
                        "engine" -> "Moteurs ⚙️"
                        "parts"  -> "Pièces 🔩"
                        else     -> key
                    }
                } ?: "Catégorie",
                active = filters.category != null,
                onClick = onOpenFilters
            )
        }

        // Wilaya quick chip
        item {
            FilterChip(
                label = filters.wilaya ?: "Wilaya",
                active = filters.wilaya != null,
                onClick = onOpenFilters
            )
        }

        // Price quick chip
        item {
            val priceLabel = when {
                filters.minPrice != null && filters.maxPrice != null ->
                    "${filters.minPrice.toLong()} – ${filters.maxPrice.toLong()} DA"
                filters.minPrice != null -> "Min ${filters.minPrice.toLong()} DA"
                filters.maxPrice != null -> "Max ${filters.maxPrice.toLong()} DA"
                else -> "Prix"
            }
            FilterChip(
                label = priceLabel,
                active = filters.minPrice != null || filters.maxPrice != null,
                onClick = onOpenFilters
            )
        }

        // Condition quick chip
        item {
            FilterChip(
                label = filters.condition?.let { key ->
                    conditionOptions.find { it.key == key }?.label
                } ?: "État",
                active = filters.condition != null,
                onClick = onOpenFilters
            )
        }

        // Sort quick chip
        item {
            FilterChip(
                label = filters.sortBy?.let { key ->
                    sortOptions.find { it.key == key }?.label
                } ?: "Trier",
                active = filters.sortBy != null && filters.sortBy != "newest",
                onClick = onOpenFilters
            )
        }
    }
}

// ─── Results count bar ────────────────────────────────────────────────────────

@Composable
private fun ResultsCountBar(total: Int, isLoading: Boolean) {
    Box(
        modifier = Modifier
            .fillMaxWidth()
            .background(MaterialTheme.colorScheme.background)
            .padding(horizontal = 16.dp, vertical = 6.dp)
    ) {
        Text(
            text = if (isLoading) "Recherche en cours..."
                   else if (total == 0) "Aucune annonce trouvée"
                   else "$total annonce${if (total > 1) "s" else ""} trouvée${if (total > 1) "s" else ""}",
            style = MaterialTheme.typography.bodySmall,
            color = MaterialTheme.colorScheme.onSurfaceVariant,
            fontWeight = FontWeight.Medium
        )
    }
}

// ─── Empty results state ──────────────────────────────────────────────────────

@Composable
private fun EmptyResults(
    hasFilters: Boolean,
    onClearFilters: () -> Unit
) {
    Column(
        modifier = Modifier
            .fillMaxSize()
            .padding(32.dp),
        horizontalAlignment = Alignment.CenterHorizontally,
        verticalArrangement = Arrangement.Center
    ) {
        Text(text = "🔍", fontSize = 56.sp)
        Spacer(modifier = Modifier.height(16.dp))
        Text(
            text = if (hasFilters) "Aucune annonce ne correspond à vos filtres"
                   else "Aucune annonce disponible pour le moment",
            style = MaterialTheme.typography.titleSmall,
            color = MaterialTheme.colorScheme.onSurfaceVariant,
            textAlign = TextAlign.Center,
            lineHeight = 20.sp
        )
        if (hasFilters) {
            Spacer(modifier = Modifier.height(16.dp))
            Button(
                onClick = onClearFilters,
                colors = ButtonDefaults.buttonColors(containerColor = OceanBlue700)
            ) {
                Text(text = "Effacer les filtres", color = White)
            }
        }
    }
}

// ─── Filter bottom sheet ──────────────────────────────────────────────────────

@OptIn(ExperimentalMaterial3Api::class)
@Composable
private fun FilterBottomSheet(
    currentFilters: ListingFilters,
    onApply: (ListingFilters) -> Unit,
    onDismiss: () -> Unit
) {
    // Local mutable copies of all filter fields
    var selectedCategory by remember { mutableStateOf(currentFilters.category) }
    var selectedWilaya    by remember { mutableStateOf(currentFilters.wilaya) }
    var minPriceText      by remember { mutableStateOf(currentFilters.minPrice?.toLong()?.toString() ?: "") }
    var maxPriceText      by remember { mutableStateOf(currentFilters.maxPrice?.toLong()?.toString() ?: "") }
    var selectedCondition by remember { mutableStateOf(currentFilters.condition) }
    var selectedOfferType by remember { mutableStateOf(currentFilters.offerType) }
    var selectedSort      by remember { mutableStateOf(currentFilters.sortBy ?: "newest") }
    var wilayaSearch      by remember { mutableStateOf("") }

    val sheetState = rememberModalBottomSheetState(skipPartiallyExpanded = true)

    ModalBottomSheet(
        onDismissRequest = onDismiss,
        sheetState = sheetState,
        containerColor = MaterialTheme.colorScheme.surface,
        shape = RoundedCornerShape(topStart = 20.dp, topEnd = 20.dp)
    ) {
        Column(
            modifier = Modifier
                .fillMaxWidth()
                .navigationBarsPadding()
        ) {
            // Sheet header
            Row(
                modifier = Modifier
                    .fillMaxWidth()
                    .padding(horizontal = 20.dp, vertical = 4.dp),
                horizontalArrangement = Arrangement.SpaceBetween,
                verticalAlignment = Alignment.CenterVertically
            ) {
                Text(
                    text = "Filtres",
                    style = MaterialTheme.typography.titleLarge,
                    fontWeight = FontWeight.Bold,
                    color = MaterialTheme.colorScheme.onSurface
                )
                TextButton(onClick = {
                    selectedCategory = null
                    selectedWilaya = null
                    minPriceText = ""
                    maxPriceText = ""
                    selectedCondition = null
                    selectedOfferType = null
                    selectedSort = "newest"
                    wilayaSearch = ""
                }) {
                    Text(
                        text = "Effacer tout",
                        color = Error500,
                        fontWeight = FontWeight.SemiBold
                    )
                }
            }

            HorizontalDivider(color = MaterialTheme.colorScheme.outline)

            // Scrollable content
            Column(
                modifier = Modifier
                    .weight(1f, fill = false)
                    .verticalScroll(rememberScrollState())
                    .padding(horizontal = 20.dp, vertical = 12.dp),
                verticalArrangement = Arrangement.spacedBy(20.dp)
            ) {

                // ── Category ──────────────────────────────────────────────────
                FilterSection(title = "Catégorie") {
                    categoryOptions.forEach { option ->
                        RadioRow(
                            label = option.label,
                            selected = selectedCategory == option.key,
                            onClick = { selectedCategory = option.key }
                        )
                    }
                }

                HorizontalDivider(color = MaterialTheme.colorScheme.outlineVariant)

                // ── Wilaya ────────────────────────────────────────────────────
                FilterSection(title = "Wilaya") {
                    // Search field for wilaya
                    OutlinedTextField(
                        value = wilayaSearch,
                        onValueChange = { wilayaSearch = it },
                        modifier = Modifier.fillMaxWidth(),
                        placeholder = { Text("Rechercher une wilaya...") },
                        singleLine = true,
                        leadingIcon = {
                            Icon(Icons.Filled.Search, null, modifier = Modifier.size(18.dp))
                        },
                        trailingIcon = {
                            if (wilayaSearch.isNotEmpty()) {
                                IconButton(onClick = { wilayaSearch = "" }) {
                                    Icon(Icons.Filled.Clear, null, modifier = Modifier.size(16.dp))
                                }
                            }
                        },
                        shape = RoundedCornerShape(10.dp),
                        colors = OutlinedTextFieldDefaults.colors(
                            focusedBorderColor = OceanBlue500,
                            unfocusedBorderColor = MaterialTheme.colorScheme.outline
                        )
                    )

                    Spacer(modifier = Modifier.height(4.dp))

                    // "Toutes" option
                    RadioRow(
                        label = "Toutes les wilayas",
                        selected = selectedWilaya == null,
                        onClick = { selectedWilaya = null }
                    )

                    // Filtered wilaya list
                    val filteredWilayas = remember(wilayaSearch) {
                        if (wilayaSearch.isBlank()) wilayas
                        else wilayas.filter {
                            it.contains(wilayaSearch.trim(), ignoreCase = true)
                        }
                    }

                    filteredWilayas.forEach { wilaya ->
                        RadioRow(
                            label = wilaya,
                            selected = selectedWilaya == wilaya,
                            onClick = { selectedWilaya = wilaya }
                        )
                    }
                }

                HorizontalDivider(color = MaterialTheme.colorScheme.outlineVariant)

                // ── Price range ───────────────────────────────────────────────
                FilterSection(title = "Fourchette de prix (DA)") {
                    Row(
                        horizontalArrangement = Arrangement.spacedBy(12.dp),
                        verticalAlignment = Alignment.CenterVertically
                    ) {
                        OutlinedTextField(
                            value = minPriceText,
                            onValueChange = { minPriceText = it.filter { c -> c.isDigit() } },
                            modifier = Modifier.weight(1f),
                            label = { Text("Min") },
                            singleLine = true,
                            keyboardOptions = KeyboardOptions(imeAction = ImeAction.Next),
                            shape = RoundedCornerShape(10.dp),
                            colors = OutlinedTextFieldDefaults.colors(
                                focusedBorderColor = OceanBlue500,
                                unfocusedBorderColor = MaterialTheme.colorScheme.outline
                            )
                        )
                        Text("–", style = MaterialTheme.typography.titleMedium, color = MaterialTheme.colorScheme.onSurfaceVariant)
                        OutlinedTextField(
                            value = maxPriceText,
                            onValueChange = { maxPriceText = it.filter { c -> c.isDigit() } },
                            modifier = Modifier.weight(1f),
                            label = { Text("Max") },
                            singleLine = true,
                            keyboardOptions = KeyboardOptions(imeAction = ImeAction.Done),
                            shape = RoundedCornerShape(10.dp),
                            colors = OutlinedTextFieldDefaults.colors(
                                focusedBorderColor = OceanBlue500,
                                unfocusedBorderColor = MaterialTheme.colorScheme.outline
                            )
                        )
                    }
                }

                HorizontalDivider(color = MaterialTheme.colorScheme.outlineVariant)

                // ── Condition ─────────────────────────────────────────────────
                FilterSection(title = "État") {
                    conditionOptions.forEach { option ->
                        RadioRow(
                            label = option.label,
                            selected = selectedCondition == option.key,
                            onClick = { selectedCondition = option.key }
                        )
                    }
                }

                HorizontalDivider(color = MaterialTheme.colorScheme.outlineVariant)

                // ── Offer type ────────────────────────────────────────────────
                FilterSection(title = "Type d'offre") {
                    offerTypeOptions.forEach { option ->
                        RadioRow(
                            label = option.label,
                            selected = selectedOfferType == option.key,
                            onClick = { selectedOfferType = option.key }
                        )
                    }
                }

                HorizontalDivider(color = MaterialTheme.colorScheme.outlineVariant)

                // ── Sort by ───────────────────────────────────────────────────
                FilterSection(title = "Trier par") {
                    sortOptions.forEach { option ->
                        RadioRow(
                            label = option.label,
                            selected = selectedSort == option.key,
                            onClick = { selectedSort = option.key ?: "newest" }
                        )
                    }
                }

                // Bottom spacer to avoid overlap with buttons
                Spacer(modifier = Modifier.height(8.dp))
            }

            // ── Action buttons ─────────────────────────────────────────────────
            HorizontalDivider(color = MaterialTheme.colorScheme.outline)

            Row(
                modifier = Modifier
                    .fillMaxWidth()
                    .padding(horizontal = 20.dp, vertical = 14.dp),
                horizontalArrangement = Arrangement.spacedBy(12.dp)
            ) {
                OutlinedButton(
                    onClick = onDismiss,
                    modifier = Modifier.weight(1f),
                    shape = RoundedCornerShape(12.dp),
                    colors = ButtonDefaults.outlinedButtonColors(contentColor = OceanBlue700)
                ) {
                    Text("Annuler", fontWeight = FontWeight.SemiBold)
                }

                Button(
                    onClick = {
                        val newFilters = ListingFilters(
                            search      = currentFilters.search,
                            category    = selectedCategory,
                            wilaya      = selectedWilaya,
                            minPrice    = minPriceText.toDoubleOrNull(),
                            maxPrice    = maxPriceText.toDoubleOrNull(),
                            condition   = selectedCondition,
                            offerType   = selectedOfferType,
                            sortBy      = selectedSort.takeIf { it != "newest" },
                            page        = 1
                        )
                        onApply(newFilters)
                    },
                    modifier = Modifier.weight(1f),
                    shape = RoundedCornerShape(12.dp),
                    colors = ButtonDefaults.buttonColors(containerColor = OceanBlue700)
                ) {
                    Text("Appliquer", color = White, fontWeight = FontWeight.Bold)
                }
            }
        }
    }
}

// ─── Reusable filter section wrapper ─────────────────────────────────────────

@Composable
private fun FilterSection(
    title: String,
    content: @Composable ColumnScope.() -> Unit
) {
    Column(verticalArrangement = Arrangement.spacedBy(4.dp)) {
        Text(
            text = title,
            style = MaterialTheme.typography.titleSmall,
            fontWeight = FontWeight.Bold,
            color = OceanBlue900
        )
        Spacer(modifier = Modifier.height(4.dp))
        content()
    }
}

// ─── Radio row ────────────────────────────────────────────────────────────────

@Composable
private fun RadioRow(
    label: String,
    selected: Boolean,
    onClick: () -> Unit
) {
    Row(
        modifier = Modifier
            .fillMaxWidth()
            .clip(RoundedCornerShape(8.dp))
            .clickable(onClick = onClick)
            .background(
                if (selected) OceanBlue50
                else Color.Transparent
            )
            .padding(horizontal = 4.dp, vertical = 6.dp),
        verticalAlignment = Alignment.CenterVertically,
        horizontalArrangement = Arrangement.spacedBy(8.dp)
    ) {
        RadioButton(
            selected = selected,
            onClick = onClick,
            colors = RadioButtonDefaults.colors(
                selectedColor = OceanBlue700,
                unselectedColor = MaterialTheme.colorScheme.outline
            )
        )
        Text(
            text = label,
            style = MaterialTheme.typography.bodyMedium,
            color = if (selected) OceanBlue900 else MaterialTheme.colorScheme.onSurface,
            fontWeight = if (selected) FontWeight.SemiBold else FontWeight.Normal
        )
    }
}
