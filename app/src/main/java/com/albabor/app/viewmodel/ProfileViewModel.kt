package com.albabor.app.viewmodel

import android.content.Context
import androidx.lifecycle.ViewModel
import androidx.lifecycle.ViewModelProvider
import androidx.lifecycle.viewModelScope
import com.albabor.app.data.model.User
import com.albabor.app.data.repository.AuthRepository
import com.albabor.app.data.repository.ProfileRepository
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch

class ProfileViewModel(private val context: Context) : ViewModel() {

    private val repo     = ProfileRepository()
    private val authRepo = AuthRepository(context)

    // ── State ─────────────────────────────────────────────────────────────────

    private val _user = MutableStateFlow<User?>(null)
    val user: StateFlow<User?> = _user.asStateFlow()

    private val _isLoading = MutableStateFlow(false)
    val isLoading: StateFlow<Boolean> = _isLoading.asStateFlow()

    private val _profileError = MutableStateFlow<String?>(null)
    val profileError: StateFlow<String?> = _profileError.asStateFlow()

    private val _updateState = MutableStateFlow<UpdateState>(UpdateState.Idle)
    val updateState: StateFlow<UpdateState> = _updateState.asStateFlow()

    private val _passwordState = MutableStateFlow<UpdateState>(UpdateState.Idle)
    val passwordState: StateFlow<UpdateState> = _passwordState.asStateFlow()

    private val _upgradeState = MutableStateFlow<UpdateState>(UpdateState.Idle)
    val upgradeState: StateFlow<UpdateState> = _upgradeState.asStateFlow()

    init {
        loadProfile()
    }

    // ── Load profile ──────────────────────────────────────────────────────────

    fun loadProfile() {
        viewModelScope.launch {
            _isLoading.value = true
            _profileError.value = null
            repo.getProfile()
                .onSuccess { _user.value = it; _profileError.value = null }
                .onFailure { e -> _profileError.value = e.message }
            _isLoading.value = false
        }
    }

    // ── Update profile ────────────────────────────────────────────────────────

    /**
     * @param hideName publier sous « Invité ». `null` laisse le réglage tel quel.
     */
    fun updateProfile(name: String, phone: String, hideName: Boolean? = null) {
        if (name.isBlank()) {
            _updateState.value = UpdateState.Error("Le nom ne peut pas etre vide")
            return
        }
        if (phone.isNotBlank()) {
            val phoneError = validateAlgerianPhone(phone)
            if (phoneError != null) {
                _updateState.value = UpdateState.Error(phoneError)
                return
            }
        }
        val normalizedPhone = if (phone.isBlank()) phone else normalizeAlgerianPhone(phone)
        viewModelScope.launch {
            _updateState.value = UpdateState.Loading
            repo.updateProfile(name, normalizedPhone, hideName)
                .onSuccess { updated ->
                    _user.value = updated
                    _updateState.value = UpdateState.Success
                }
                .onFailure {
                    _updateState.value = UpdateState.Error(it.message ?: "Echec de la mise a jour")
                }
        }
    }

    // ── Phone validation (shared logic) ──────────────────────────────────────

    /**
     * Valide un numéro algérien : 0[5-7]XXXXXXXX ou +213[5-7]XXXXXXXX
     * Retourne null si valide, sinon un message d'erreur.
     */
    fun validateAlgerianPhone(phone: String): String? {
        val cleaned = stripPhone(phone)
        return if (Regex("^0[5-7]\\d{8}$").matches(cleaned) || Regex("^\\+213[5-7]\\d{8}$").matches(cleaned)) null
        else "Numéro invalide. Utilisez le format 0676085441 ou +213676085441 (10 chiffres)"
    }

    /** Normalise vers le format local 0XXXXXXXXX. */
    fun normalizeAlgerianPhone(phone: String): String {
        val cleaned = stripPhone(phone)
        return if (cleaned.startsWith("+213")) "0" + cleaned.drop(4) else cleaned
    }

    private fun stripPhone(phone: String): String {
        var s = phone.trim().replace(Regex("[\\s\\-\\.\\(\\)]"), "")
        if (s.startsWith("00213")) s = "+213" + s.drop(5)
        return s
    }

    // ── Change password ───────────────────────────────────────────────────────

    fun updatePassword(current: String, new: String, confirm: String) {
        if (new.length < 8) {
            _passwordState.value = UpdateState.Error("Le mot de passe doit contenir au moins 8 caracteres")
            return
        }
        if (new != confirm) {
            _passwordState.value = UpdateState.Error("Les mots de passe ne correspondent pas")
            return
        }
        viewModelScope.launch {
            _passwordState.value = UpdateState.Loading
            repo.updatePassword(current, new, confirm)
                .onSuccess {
                    _passwordState.value = UpdateState.Success
                }
                .onFailure {
                    _passwordState.value = UpdateState.Error(it.message ?: "Echec du changement de mot de passe")
                }
        }
    }

    // ── Upgrade to vendor ─────────────────────────────────────────────────────

    fun upgradeToVendor() {
        viewModelScope.launch {
            _upgradeState.value = UpdateState.Loading
            repo.upgradeToVendor()
                .onSuccess { updated ->
                    _user.value = updated
                    _upgradeState.value = UpdateState.Success
                }
                .onFailure {
                    _upgradeState.value = UpdateState.Error(it.message ?: "Echec de l'upgrade")
                }
        }
    }

    // ── Logout ────────────────────────────────────────────────────────────────

    fun logout(onLogout: () -> Unit) {
        viewModelScope.launch {
            authRepo.logout()
            _user.value = null
            onLogout()
        }
    }

    // ── Reset states ──────────────────────────────────────────────────────────

    fun clearUpdateState()   { _updateState.value   = UpdateState.Idle }
    fun clearPasswordState() { _passwordState.value = UpdateState.Idle }
    fun clearUpgradeState()  { _upgradeState.value  = UpdateState.Idle }

    // ── Sealed state ──────────────────────────────────────────────────────────

    sealed class UpdateState {
        object Idle    : UpdateState()
        object Loading : UpdateState()
        object Success : UpdateState()
        data class Error(val msg: String) : UpdateState()
    }

    // ── Factory ───────────────────────────────────────────────────────────────

    companion object {
        fun factory(context: Context): ViewModelProvider.Factory =
            object : ViewModelProvider.Factory {
                @Suppress("UNCHECKED_CAST")
                override fun <T : ViewModel> create(modelClass: Class<T>): T =
                    ProfileViewModel(context.applicationContext) as T
            }
    }
}
