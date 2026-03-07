package com.albabor.app.viewmodel

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.albabor.app.data.model.Conversation
import com.albabor.app.data.model.ConversationMessage
import com.albabor.app.data.repository.MediationRepository
import com.albabor.app.data.repository.ProfileRepository
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch

class ConversationsViewModel : ViewModel() {
    private val repo = MediationRepository()
    private val profileRepo = ProfileRepository()

    private val _conversations = MutableStateFlow<List<Conversation>>(emptyList())
    val conversations: StateFlow<List<Conversation>> = _conversations.asStateFlow()

    private val _selectedConversation = MutableStateFlow<Conversation?>(null)
    val selectedConversation: StateFlow<Conversation?> = _selectedConversation.asStateFlow()

    private val _messages = MutableStateFlow<List<ConversationMessage>>(emptyList())
    val messages: StateFlow<List<ConversationMessage>> = _messages.asStateFlow()

    private val _isLoading = MutableStateFlow(false)
    val isLoading: StateFlow<Boolean> = _isLoading.asStateFlow()

    private val _isSending = MutableStateFlow(false)
    val isSending: StateFlow<Boolean> = _isSending.asStateFlow()

    private val _messageDraft = MutableStateFlow("")
    val messageDraft: StateFlow<String> = _messageDraft.asStateFlow()

    private val _error = MutableStateFlow<String?>(null)
    val error: StateFlow<String?> = _error.asStateFlow()

    // Current user ID — loaded from profile so we can determine bubble alignment
    private val _currentUserId = MutableStateFlow(0)
    val currentUserId: StateFlow<Int> = _currentUserId.asStateFlow()

    init {
        loadCurrentUser()
        loadConversations()
    }

    private fun loadCurrentUser() {
        viewModelScope.launch {
            profileRepo.getProfile()
                .onSuccess { _currentUserId.value = it.id }
        }
    }

    fun loadConversations() {
        viewModelScope.launch {
            _isLoading.value = true
            _error.value = null
            repo.getConversations()
                .onSuccess { _conversations.value = it }
                .onFailure { _error.value = it.message }
            _isLoading.value = false
        }
    }

    fun loadMessages(conversationId: Int) {
        viewModelScope.launch {
            _isLoading.value = true
            // Load both the conversation detail and its messages in parallel
            repo.getConversation(conversationId)
                .onSuccess { _selectedConversation.value = it }
            repo.getMessages(conversationId)
                .onSuccess { _messages.value = it }
                .onFailure { _error.value = it.message }
            _isLoading.value = false
        }
    }

    fun sendMessage(conversationId: Int) {
        val body = _messageDraft.value.trim()
        if (body.isEmpty()) return
        viewModelScope.launch {
            _isSending.value = true
            repo.sendConversationMessage(conversationId, body)
                .onSuccess { newMsg ->
                    _messageDraft.value = ""
                    _messages.value = _messages.value + newMsg
                    // Refresh conversations list so last_message updates
                    repo.getConversations()
                        .onSuccess { _conversations.value = it }
                }
                .onFailure { _error.value = it.message }
            _isSending.value = false
        }
    }

    fun updateDraft(text: String) {
        _messageDraft.value = text
    }

    fun clearSelectedConversation() {
        _selectedConversation.value = null
        _messages.value = emptyList()
        _messageDraft.value = ""
    }

    fun clearError() { _error.value = null }
}
