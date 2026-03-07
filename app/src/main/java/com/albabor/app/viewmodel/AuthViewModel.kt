package com.albabor.app.viewmodel

import android.content.Context
import androidx.lifecycle.ViewModel
import androidx.lifecycle.ViewModelProvider
import androidx.lifecycle.viewModelScope
import com.albabor.app.data.model.AuthResponse
import com.albabor.app.data.repository.AuthRepository
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch

class AuthViewModel(context: Context) : ViewModel() {

    private val repo = AuthRepository(context)

    // ─── State Definitions ────────────────────────────────────────────────────

    sealed class LoginState {
        object Idle : LoginState()
        object Loading : LoginState()
        data class Success(val response: AuthResponse) : LoginState()
        data class Error(val message: String) : LoginState()
    }

    sealed class RegisterState {
        object Idle : RegisterState()
        object Loading : RegisterState()
        data class Success(val response: AuthResponse) : RegisterState()
        data class Error(val message: String) : RegisterState()
    }

    sealed class ForgotState {
        object Idle : ForgotState()
        object Loading : ForgotState()
        object Success : ForgotState()
        data class Error(val message: String) : ForgotState()
    }

    // ─── State Flows ──────────────────────────────────────────────────────────

    private val _loginState = MutableStateFlow<LoginState>(LoginState.Idle)
    val loginState: StateFlow<LoginState> = _loginState.asStateFlow()

    private val _registerState = MutableStateFlow<RegisterState>(RegisterState.Idle)
    val registerState: StateFlow<RegisterState> = _registerState.asStateFlow()

    private val _forgotState = MutableStateFlow<ForgotState>(ForgotState.Idle)
    val forgotState: StateFlow<ForgotState> = _forgotState.asStateFlow()

    // ─── Actions ──────────────────────────────────────────────────────────────

    fun login(email: String, password: String) {
        viewModelScope.launch {
            _loginState.value = LoginState.Loading
            repo.login(email.trim(), password).fold(
                onSuccess = { _loginState.value = LoginState.Success(it) },
                onFailure = { _loginState.value = LoginState.Error(parseError(it.message)) }
            )
        }
    }

    fun register(
        firstName: String,
        lastName: String,
        email: String,
        phone: String,
        password: String,
        passwordConfirmation: String
    ) {
        if (password != passwordConfirmation) {
            _registerState.value = RegisterState.Error("Les mots de passe ne correspondent pas")
            return
        }
        val fullName = "${firstName.trim()} ${lastName.trim()}".trim()
        viewModelScope.launch {
            _registerState.value = RegisterState.Loading
            repo.register(fullName, email.trim(), phone.trim(), password).fold(
                onSuccess = { _registerState.value = RegisterState.Success(it) },
                onFailure = { _registerState.value = RegisterState.Error(parseError(it.message)) }
            )
        }
    }

    fun forgotPassword(email: String) {
        viewModelScope.launch {
            _forgotState.value = ForgotState.Loading
            repo.forgotPassword(email.trim()).fold(
                onSuccess = { _forgotState.value = ForgotState.Success },
                onFailure = { _forgotState.value = ForgotState.Error(parseError(it.message)) }
            )
        }
    }

    fun resetLoginState()    { _loginState.value    = LoginState.Idle }
    fun resetRegisterState() { _registerState.value = RegisterState.Idle }
    fun resetForgotState()   { _forgotState.value   = ForgotState.Idle }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private fun parseError(raw: String?): String {
        if (raw.isNullOrBlank()) return "Une erreur s'est produite. Veuillez réessayer."
        // Attempt to strip JSON wrapper from Retrofit error body strings
        return try {
            val msgRegex = Regex("\"message\"\\s*:\\s*\"([^\"]+)\"")
            msgRegex.find(raw)?.groupValues?.get(1) ?: raw
        } catch (_: Exception) {
            raw
        }
    }

    // ─── Factory ──────────────────────────────────────────────────────────────

    companion object {
        fun factory(context: Context): ViewModelProvider.Factory =
            object : ViewModelProvider.Factory {
                @Suppress("UNCHECKED_CAST")
                override fun <T : ViewModel> create(modelClass: Class<T>): T {
                    if (modelClass.isAssignableFrom(AuthViewModel::class.java)) {
                        return AuthViewModel(context.applicationContext) as T
                    }
                    throw IllegalArgumentException("Unknown ViewModel: ${modelClass.name}")
                }
            }
    }
}
