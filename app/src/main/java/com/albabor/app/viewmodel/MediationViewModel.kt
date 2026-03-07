package com.albabor.app.viewmodel

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.albabor.app.data.model.MediationTicket
import com.albabor.app.data.repository.MediationRepository
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch

class MediationViewModel : ViewModel() {
    private val repo = MediationRepository()

    private val _tickets = MutableStateFlow<List<MediationTicket>>(emptyList())
    val tickets: StateFlow<List<MediationTicket>> = _tickets.asStateFlow()

    private val _selectedTicket = MutableStateFlow<MediationTicket?>(null)
    val selectedTicket: StateFlow<MediationTicket?> = _selectedTicket.asStateFlow()

    private val _isLoading = MutableStateFlow(false)
    val isLoading: StateFlow<Boolean> = _isLoading.asStateFlow()

    private val _isSending = MutableStateFlow(false)
    val isSending: StateFlow<Boolean> = _isSending.asStateFlow()

    private val _messageDraft = MutableStateFlow("")
    val messageDraft: StateFlow<String> = _messageDraft.asStateFlow()

    private val _error = MutableStateFlow<String?>(null)
    val error: StateFlow<String?> = _error.asStateFlow()

    // Tab filter: "all" | "open" | "in_progress" | "resolved"
    private val _activeTab = MutableStateFlow("all")
    val activeTab: StateFlow<String> = _activeTab.asStateFlow()

    val filteredTickets: List<MediationTicket>
        get() = when (_activeTab.value) {
            "open"        -> _tickets.value.filter { it.status == "open" }
            "in_progress" -> _tickets.value.filter { it.status == "in_progress" }
            "resolved"    -> _tickets.value.filter { it.status == "resolved" || it.status == "closed" }
            else          -> _tickets.value
        }

    init {
        loadTickets()
    }

    fun loadTickets() {
        viewModelScope.launch {
            _isLoading.value = true
            _error.value = null
            repo.getTickets()
                .onSuccess { _tickets.value = it }
                .onFailure { _error.value = it.message }
            _isLoading.value = false
        }
    }

    fun loadTicket(id: Int) {
        viewModelScope.launch {
            _isLoading.value = true
            repo.getTicket(id)
                .onSuccess { _selectedTicket.value = it }
                .onFailure { _error.value = it.message }
            _isLoading.value = false
        }
    }

    fun setActiveTab(tab: String) {
        _activeTab.value = tab
    }

    fun updateDraft(text: String) {
        _messageDraft.value = text
    }

    fun sendMessage(ticketId: Int) {
        val body = _messageDraft.value.trim()
        if (body.isEmpty()) return
        viewModelScope.launch {
            _isSending.value = true
            repo.sendMessage(ticketId, body)
                .onSuccess {
                    _messageDraft.value = ""
                    // Reload the ticket to get the updated message list
                    repo.getTicket(ticketId)
                        .onSuccess { _selectedTicket.value = it }
                }
                .onFailure { _error.value = it.message }
            _isSending.value = false
        }
    }

    fun cancelTicket(id: Int, onSuccess: () -> Unit) {
        viewModelScope.launch {
            _isLoading.value = true
            repo.cancelTicket(id)
                .onSuccess {
                    loadTickets()
                    onSuccess()
                }
                .onFailure { _error.value = it.message }
            _isLoading.value = false
        }
    }

    fun clearSelectedTicket() {
        _selectedTicket.value = null
        _messageDraft.value = ""
    }

    fun clearError() { _error.value = null }
}
