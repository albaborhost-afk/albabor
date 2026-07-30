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

    private val _isCreatingMediation = MutableStateFlow(false)
    val isCreatingMediation: StateFlow<Boolean> = _isCreatingMediation.asStateFlow()

    private val _createdTicketId = MutableStateFlow<Int?>(null)
    val createdTicketId: StateFlow<Int?> = _createdTicketId.asStateFlow()

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
            favRepo.toggleFavorite(listingId)
                .onSuccess { nowFavorited ->
                    // Trust the server's returned state rather than our optimistic guess,
                    // so the heart can't drift out of sync with the backend.
                    _isFavorited.value = nowFavorited
                    _snackbar.value = if (nowFavorited) "Ajouté aux favoris" else "Retiré des favoris"
                }
                .onFailure {
                    // Revert optimistic update
                    _isFavorited.value = wasLiked
                    _snackbar.value    = "Erreur : impossible de modifier les favoris"
                }
            _favLoading.value = false
        }
    }

    fun createMediationTicket(message: String) {
        val body = message.trim()
        if (body.isEmpty() || _isCreatingMediation.value) return
        viewModelScope.launch {
            _isCreatingMediation.value = true
            convoRepo.createTicket(listingId, body)
                .onSuccess { ticket ->
                    _createdTicketId.value = ticket.id
                    _snackbar.value = "Ticket de médiation créé"
                }
                .onFailure { error ->
                    _snackbar.value = error.message ?: "Impossible de créer le ticket de médiation"
                }
            _isCreatingMediation.value = false
        }
    }

    fun clearCreatedTicket() {
        _createdTicketId.value = null
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
