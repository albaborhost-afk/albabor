package com.albabor.app.viewmodel

import androidx.lifecycle.ViewModel
import androidx.lifecycle.ViewModelProvider
import androidx.lifecycle.viewModelScope
import androidx.lifecycle.viewmodel.initializer
import androidx.lifecycle.viewmodel.viewModelFactory
import com.albabor.app.data.model.Listing
import com.albabor.app.data.repository.FavoriteRepository
import com.albabor.app.data.repository.ListingRepository
import com.albabor.app.data.repository.MediationRepository
import com.albabor.app.data.repository.ProfileRepository
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch

class ListingDetailViewModel(private val listingId: Int) : ViewModel() {

    private val repo     = ListingRepository()
    private val favRepo  = FavoriteRepository()
    private val convoRepo = MediationRepository()
    private val profileRepo = ProfileRepository()

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

    private val _currentUserId = MutableStateFlow(0)
    val currentUserId: StateFlow<Int> = _currentUserId.asStateFlow()

    private val _isStartingConversation = MutableStateFlow(false)
    val isStartingConversation: StateFlow<Boolean> = _isStartingConversation.asStateFlow()

    private val _startedConversationId = MutableStateFlow<Int?>(null)
    val startedConversationId: StateFlow<Int?> = _startedConversationId.asStateFlow()

    private val _snackbar = MutableStateFlow<String?>(null)
    val snackbar: StateFlow<String?> = _snackbar.asStateFlow()

    // ── Init ──────────────────────────────────────────────────────────────────

    init {
        loadCurrentUser()
        loadListing()
    }

    // ── Actions ───────────────────────────────────────────────────────────────

    private fun loadCurrentUser() {
        viewModelScope.launch {
            profileRepo.getProfile()
                .onSuccess { _currentUserId.value = it.id }
        }
    }

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

    fun startConversation(body: String) {
        val message = body.trim()
        if (message.isEmpty() || _isStartingConversation.value) return

        viewModelScope.launch {
            _isStartingConversation.value = true
            convoRepo.startConversation(listingId, message)
                .onSuccess { conversation ->
                    _startedConversationId.value = conversation.id
                    _snackbar.value = "Message envoyé"
                }
                .onFailure { error ->
                    _snackbar.value = error.message ?: "Impossible d'envoyer le message"
                }
            _isStartingConversation.value = false
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

    fun clearStartedConversation() {
        _startedConversationId.value = null
    }

    // ── Factory ───────────────────────────────────────────────────────────────

    companion object {
        fun factory(listingId: Int): ViewModelProvider.Factory = viewModelFactory {
            initializer { ListingDetailViewModel(listingId) }
        }
    }
}
