package com.albabor.app.viewmodel

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.albabor.app.data.model.Payment
import com.albabor.app.data.model.Subscription
import com.albabor.app.data.repository.ProfileRepository
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch

class PaymentsViewModel : ViewModel() {
    private val repo = ProfileRepository()

    private val _payments = MutableStateFlow<List<Payment>>(emptyList())
    val payments: StateFlow<List<Payment>> = _payments.asStateFlow()

    private val _subscriptions = MutableStateFlow<List<Subscription>>(emptyList())
    val subscriptions: StateFlow<List<Subscription>> = _subscriptions.asStateFlow()

    private val _isLoading = MutableStateFlow(false)
    val isLoading: StateFlow<Boolean> = _isLoading.asStateFlow()

    private val _error = MutableStateFlow<String?>(null)
    val error: StateFlow<String?> = _error.asStateFlow()

    // Total of approved payments
    val totalApproved: Double
        get() = _payments.value.filter { it.status == "approved" }.sumOf { it.amount }

    init {
        loadPayments()
    }

    fun loadPayments() {
        viewModelScope.launch {
            _isLoading.value = true
            _error.value = null
            repo.getPayments()
                .onSuccess { _payments.value = it }
                .onFailure { _error.value = it.message }
            _isLoading.value = false
        }
    }

    fun loadSubscriptions() {
        viewModelScope.launch {
            repo.getSubscriptions()
                .onSuccess { _subscriptions.value = it }
                .onFailure { /* silently ignored for now */ }
        }
    }

    fun clearError() { _error.value = null }
}
