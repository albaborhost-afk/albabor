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

    sealed class GoogleAuthState {
        object Idle : GoogleAuthState()
        object Loading : GoogleAuthState()
        data class Success(val response: AuthResponse) : GoogleAuthState()
        data class Error(val message: String) : GoogleAuthState()
    }

    // ─── State Flows ──────────────────────────────────────────────────────────

    private val _loginState = MutableStateFlow<LoginState>(LoginState.Idle)
    val loginState: StateFlow<LoginState> = _loginState.asStateFlow()

    private val _registerState = MutableStateFlow<RegisterState>(RegisterState.Idle)
    val registerState: StateFlow<RegisterState> = _registerState.asStateFlow()

    private val _forgotState = MutableStateFlow<ForgotState>(ForgotState.Idle)
    val forgotState: StateFlow<ForgotState> = _forgotState.asStateFlow()

    private val _googleAuthState = MutableStateFlow<GoogleAuthState>(GoogleAuthState.Idle)
    val googleAuthState: StateFlow<GoogleAuthState> = _googleAuthState.asStateFlow()

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
        val phoneError = validateAlgerianPhone(phone)
        if (phoneError != null) {
            _registerState.value = RegisterState.Error(phoneError)
            return
        }
        val fullName = "${firstName.trim()} ${lastName.trim()}".trim()
        val normalizedPhone = normalizeAlgerianPhone(phone)
        viewModelScope.launch {
            _registerState.value = RegisterState.Loading
            repo.register(fullName, email.trim(), normalizedPhone, password).fold(
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

    fun loginWithGoogle(idToken: String) {
        viewModelScope.launch {
            _googleAuthState.value = GoogleAuthState.Loading
            repo.googleLogin(idToken).fold(
                onSuccess = { _googleAuthState.value = GoogleAuthState.Success(it) },
                onFailure = { _googleAuthState.value = GoogleAuthState.Error(parseError(it.message)) }
            )
        }
    }

    fun resetLoginState()    { _loginState.value    = LoginState.Idle }
    fun resetRegisterState() { _registerState.value = RegisterState.Idle }
    fun resetForgotState()   { _forgotState.value   = ForgotState.Idle }
    fun resetGoogleAuthState() { _googleAuthState.value = GoogleAuthState.Idle }

    // ─── Phone Validation ─────────────────────────────────────────────────────

    /**
     * Valide un numéro de téléphone algérien.
     * Formats acceptés : 0[5-7]XXXXXXXX (10 chiffres) ou +213[5-7]XXXXXXXX
     * Retourne null si valide, sinon un message d'erreur en français.
     */
    fun validateAlgerianPhone(phone: String): String? {
        val cleaned = stripPhone(phone)
        val localRegex = Regex("^0[5-7]\\d{8}$")
        val intlRegex  = Regex("^\\+213[5-7]\\d{8}$")
        return if (localRegex.matches(cleaned) || intlRegex.matches(cleaned)) null
        else "Numéro invalide. Utilisez le format 0676085441 ou +213676085441 (10 chiffres)"
    }

    /**
     * Normalise vers le format local 0XXXXXXXXX.
     * +213676085441 → 0676085441
     */
    fun normalizeAlgerianPhone(phone: String): String {
        val cleaned = stripPhone(phone)
        return when {
            cleaned.startsWith("+213") -> "0" + cleaned.drop(4)
            else -> cleaned
        }
    }

    private fun stripPhone(phone: String): String {
        var s = phone.trim().replace(Regex("[\\s\\-\\.\\(\\)]"), "")
        if (s.startsWith("00213")) s = "+213" + s.drop(5)
        return s
    }

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
