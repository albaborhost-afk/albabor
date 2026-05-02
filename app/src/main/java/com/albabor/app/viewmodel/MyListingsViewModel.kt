package com.albabor.app.viewmodel

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.albabor.app.data.model.Listing
import com.albabor.app.data.repository.ListingRepository
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch

class MyListingsViewModel : ViewModel() {

    private val repo = ListingRepository()

    // ── Listing state ─────────────────────────────────────────────────────────

    private val _listings = MutableStateFlow<List<Listing>>(emptyList())
    val listings: StateFlow<List<Listing>> = _listings.asStateFlow()

    private val _isLoading = MutableStateFlow(false)
    val isLoading: StateFlow<Boolean> = _isLoading.asStateFlow()

    // ── Action states for delete/pause/sell/reactivate ────────────────────────

    private val _actionState = MutableStateFlow<ActionState>(ActionState.Idle)
    val actionState: StateFlow<ActionState> = _actionState.asStateFlow()

    // ── Delete confirmation ───────────────────────────────────────────────────

    private val _pendingDeleteId = MutableStateFlow<Int?>(null)
    val pendingDeleteId: StateFlow<Int?> = _pendingDeleteId.asStateFlow()

    init {
        loadListings()
    }

    // ── Load ──────────────────────────────────────────────────────────────────

    fun loadListings() {
        viewModelScope.launch {
            _isLoading.value = true
            repo.getMyListings()
                .onSuccess { _listings.value = it }
                .onFailure { _actionState.value = ActionState.Error(it.message ?: "Erreur de chargement") }
            _isLoading.value = false
        }
    }

    // ── Delete ────────────────────────────────────────────────────────────────

    fun requestDelete(id: Int) {
        _pendingDeleteId.value = id
    }

    fun cancelDelete() {
        _pendingDeleteId.value = null
    }

    fun confirmDelete() {
        val id = _pendingDeleteId.value ?: return
        _pendingDeleteId.value = null
        viewModelScope.launch {
            _actionState.value = ActionState.Loading
            repo.deleteListing(id)
                .onSuccess {
                    _listings.value = _listings.value.filter { it.id != id }
                    _actionState.value = ActionState.Success("Annonce supprimee")
                }
                .onFailure {
                    _actionState.value = ActionState.Error(it.message ?: "Echec de la suppression")
                }
        }
    }

    // ── Mark as sold ──────────────────────────────────────────────────────────

    fun markAsSold(id: Int) {
        viewModelScope.launch {
            _actionState.value = ActionState.Loading
            repo.markAsSold(id)
                .onSuccess { updated ->
                    _listings.value = _listings.value.map { if (it.id == id) updated else it }
                    _actionState.value = ActionState.Success("Annonce marquee comme vendue")
                }
                .onFailure {
                    _actionState.value = ActionState.Error(it.message ?: "Echec")
                }
        }
    }

    // ── Pause ─────────────────────────────────────────────────────────────────

    fun pauseListing(id: Int) {
        viewModelScope.launch {
            _actionState.value = ActionState.Loading
            repo.pauseListing(id)
                .onSuccess { updated ->
                    _listings.value = _listings.value.map { if (it.id == id) updated else it }
                    _actionState.value = ActionState.Success("Annonce mise en pause")
                }
                .onFailure {
                    _actionState.value = ActionState.Error(it.message ?: "Echec")
                }
        }
    }

    // ── Reactivate ────────────────────────────────────────────────────────────

    fun reactivateListing(id: Int) {
        viewModelScope.launch {
            _actionState.value = ActionState.Loading
            repo.reactivateListing(id)
                .onSuccess { updated ->
                    _listings.value = _listings.value.map { if (it.id == id) updated else it }
                    _actionState.value = ActionState.Success("Annonce reactivee")
                }
                .onFailure {
                    _actionState.value = ActionState.Error(it.message ?: "Echec")
                }
        }
    }

    // ── Renew (bump to top) ───────────────────────────────────────────────────

    fun renewListing(id: Int) {
        viewModelScope.launch {
            _actionState.value = ActionState.Loading
            repo.renewListing(id)
                .onSuccess { result ->
                    _actionState.value = ActionState.Success(result.message.ifEmpty { "Annonce remontee" })
                    loadListings()
                }
                .onFailure {
                    _actionState.value = ActionState.Error(it.message ?: "Echec du renouvellement")
                }
        }
    }

    // ── Reset action state ────────────────────────────────────────────────────

    fun clearActionState() {
        _actionState.value = ActionState.Idle
    }

    // ── Sealed state ──────────────────────────────────────────────────────────

    sealed class ActionState {
        object Idle    : ActionState()
        object Loading : ActionState()
        data class Success(val msg: String) : ActionState()
        data class Error(val msg: String)   : ActionState()
    }
}
