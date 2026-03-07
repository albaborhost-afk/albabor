package com.albabor.app.viewmodel

import androidx.lifecycle.ViewModel
import androidx.lifecycle.ViewModelProvider
import androidx.lifecycle.viewModelScope
import androidx.lifecycle.viewmodel.initializer
import androidx.lifecycle.viewmodel.viewModelFactory
import com.albabor.app.data.model.Listing
import com.albabor.app.data.repository.FavoriteRepository
import com.albabor.app.data.repository.ListingRepository
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch

class ListingDetailViewModel(private val listingId: Int) : ViewModel() {

    private val repo     = ListingRepository()
    private val favRepo  = FavoriteRepository()

    // ── UI State ──────────────────────────────────────────────────────────────

    sealed class State {
        object Loading                       : State()
        data class Success(val listing: Listing) : State()
        data class Error(val msg: String)    : State()
    }

    private val _state = MutableStateFlow<State>(State.Loading)
    val state: StateFlow<State> = _state.asStateFlow()

    /** Optimistic favorite flag — updated immediately on tap, then reconciled with API. */
    private val _isFavorited = MutableStateFlow(false)
    val isFavorited: StateFlow<Boolean> = _isFavorited.asStateFlow()

    /** Non-null while a favorite request is in flight (prevents double-taps). */
    private val _favLoading = MutableStateFlow(false)
    val favLoading: StateFlow<Boolean> = _favLoading.asStateFlow()

    private val _snackbar = MutableStateFlow<String?>(null)
    val snackbar: StateFlow<String?> = _snackbar.asStateFlow()

    // ── Init ──────────────────────────────────────────────────────────────────

    init {
        loadListing()
    }

    // ── Actions ───────────────────────────────────────────────────────────────

    fun loadListing() {
        viewModelScope.launch {
            _state.value = State.Loading
            repo.getListing(listingId)
                .onSuccess { listing ->
                    _state.value    = State.Success(listing)
                    _isFavorited.value = listing.isFavorited
                }
                .onFailure { e ->
                    _state.value = State.Error(e.message ?: "Impossible de charger l'annonce")
                }
        }
    }

    fun toggleFavorite() {
        if (_favLoading.value) return
        val wasLiked = _isFavorited.value
        // Optimistic update
        _isFavorited.value = !wasLiked
        _favLoading.value  = true

        viewModelScope.launch {
            val result = if (wasLiked) {
                favRepo.removeFavorite(listingId)
            } else {
                favRepo.addFavorite(listingId)
            }
            result
                .onSuccess {
                    _snackbar.value = if (wasLiked) "Retiré des favoris" else "Ajouté aux favoris"
                }
                .onFailure {
                    // Revert optimistic update
                    _isFavorited.value = wasLiked
                    _snackbar.value    = "Erreur : impossible de modifier les favoris"
                }
            _favLoading.value = false
        }
    }

    fun clearSnackbar() {
        _snackbar.value = null
    }

    // ── Factory ───────────────────────────────────────────────────────────────

    companion object {
        fun factory(listingId: Int): ViewModelProvider.Factory = viewModelFactory {
            initializer { ListingDetailViewModel(listingId) }
        }
    }
}
