package com.albabor.app.viewmodel

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.albabor.app.data.model.Listing
import com.albabor.app.data.repository.FavoriteRepository
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch

class FavoritesViewModel : ViewModel() {

    private val repo = FavoriteRepository()

    // ── State ─────────────────────────────────────────────────────────────────

    private val _listings = MutableStateFlow<List<Listing>>(emptyList())
    val listings: StateFlow<List<Listing>> = _listings.asStateFlow()

    private val _isLoading = MutableStateFlow(false)
    val isLoading: StateFlow<Boolean> = _isLoading.asStateFlow()

    private val _isRefreshing = MutableStateFlow(false)
    val isRefreshing: StateFlow<Boolean> = _isRefreshing.asStateFlow()

    private val _error = MutableStateFlow<String?>(null)
    val error: StateFlow<String?> = _error.asStateFlow()

    // Tracks IDs currently being removed to show optimistic UI
    private val _removingIds = MutableStateFlow<Set<Int>>(emptySet())
    val removingIds: StateFlow<Set<Int>> = _removingIds.asStateFlow()

    init {
        loadFavorites()
    }

    // ── Load ──────────────────────────────────────────────────────────────────

    fun loadFavorites() {
        viewModelScope.launch {
            _isLoading.value = true
            _error.value = null
            repo.getFavorites()
                .onSuccess { _listings.value = it }
                .onFailure { _error.value = it.message ?: "Erreur de chargement" }
            _isLoading.value = false
        }
    }

    // ── Pull-to-refresh ───────────────────────────────────────────────────────

    fun refresh() {
        viewModelScope.launch {
            _isRefreshing.value = true
            _error.value = null
            repo.getFavorites()
                .onSuccess { _listings.value = it }
                .onFailure { _error.value = it.message ?: "Erreur de chargement" }
            _isRefreshing.value = false
        }
    }

    // ── Remove ────────────────────────────────────────────────────────────────

    fun removeFavorite(listingId: Int) {
        viewModelScope.launch {
            // Optimistic remove
            _removingIds.value = _removingIds.value + listingId
            _listings.value = _listings.value.filter { it.id != listingId }

            // The item is shown because it's currently favorited, so toggling removes it.
            repo.toggleFavorite(listingId)
                .onFailure {
                    // Revert on failure by reloading
                    _error.value = it.message ?: "Echec de la suppression"
                    loadFavorites()
                }

            _removingIds.value = _removingIds.value - listingId
        }
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    fun clearError() {
        _error.value = null
    }
}
