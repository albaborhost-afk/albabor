package com.albabor.app.viewmodel

import android.content.Context
import android.net.Uri
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.setValue
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.albabor.app.data.model.Listing
import com.albabor.app.data.network.NetworkModule
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import okhttp3.MediaType.Companion.toMediaTypeOrNull
import okhttp3.MultipartBody
import okhttp3.RequestBody.Companion.asRequestBody
import okhttp3.RequestBody.Companion.toRequestBody
import java.io.File
import java.io.FileOutputStream

// ── Submit State ──────────────────────────────────────────────────────────────

sealed class SubmitState {
    object Idle                              : SubmitState()
    object Loading                           : SubmitState()
    data class Success(val listing: Listing) : SubmitState()
    data class Error(val msg: String)        : SubmitState()
}

// ── ViewModel ─────────────────────────────────────────────────────────────────

class CreateListingViewModel : ViewModel() {

    private val api = NetworkModule.apiService

    // ── Step ──────────────────────────────────────────────────────────────────

    var step by mutableStateOf(1)
        private set

    val totalSteps = 5

    // ── Step 1 – Catégorie ────────────────────────────────────────────────────

    var category by mutableStateOf("")

    // ── Step 2 – Informations ─────────────────────────────────────────────────

    var title       by mutableStateOf("")
    var description by mutableStateOf("")
    var wilaya      by mutableStateOf("")   // selected country
    var ville       by mutableStateOf("")   // city / region free text
    var condition   by mutableStateOf("")   // new | like_new | good | average | needs_revision
    var offerType   by mutableStateOf("")   // negotiable | fixed | free

    // ── Step 3 – Prix ─────────────────────────────────────────────────────────

    var price             by mutableStateOf("")
    var currency          by mutableStateOf("DZD")   // DZD | EUR
    var mediationEnabled  by mutableStateOf(false)

    // ── Step 4 – Caractéristiques ─────────────────────────────────────────────

    // General (boat / jetski)
    var year   by mutableStateOf("")
    var brand  by mutableStateOf("")
    var model  by mutableStateOf("")
    var color  by mutableStateOf("")
    var length by mutableStateOf("")   // metres

    // Motorisation (boat / jetski / engine)
    var power      by mutableStateOf("")   // CV
    var engineType by mutableStateOf("")   // inboard | outboard | jet
    var nbEngines  by mutableStateOf("")

    // Engine-only
    var engineBrand by mutableStateOf("")

    // Parts
    var partBrand       by mutableStateOf("")
    var compatibleWith  by mutableStateOf("")

    // ── Step 5 – Photos ───────────────────────────────────────────────────────

    var selectedImages by mutableStateOf<List<Uri>>(emptyList())
        private set

    // ── Submit ────────────────────────────────────────────────────────────────

    private val _submitState = MutableStateFlow<SubmitState>(SubmitState.Idle)
    val submitState: StateFlow<SubmitState> = _submitState.asStateFlow()

    // ── Validation ────────────────────────────────────────────────────────────

    /** Returns null if valid, or an error message string. */
    fun validateCurrentStep(): String? = when (step) {
        1 -> if (category.isBlank()) "Veuillez sélectionner une catégorie" else null
        2 -> when {
            title.isBlank()       -> "Le titre est obligatoire"
            description.isBlank() -> "La description est obligatoire"
            wilaya.isBlank()      -> "Le pays est obligatoire"
            condition.isBlank()   -> "L'état est obligatoire"
            offerType.isBlank()   -> "Le type d'offre est obligatoire"
            else                  -> null
        }
        3 -> when {
            price.isBlank()                            -> "Le prix est obligatoire"
            price.toDoubleOrNull() == null             -> "Le prix doit être un nombre valide"
            (price.toDoubleOrNull() ?: 0.0) <= 0      -> "Le prix doit être supérieur à 0"
            else                                       -> null
        }
        4 -> null   // Specs are optional
        5 -> if (selectedImages.isEmpty()) "Ajoutez au moins une photo" else null
        else -> null
    }

    // ── Navigation ────────────────────────────────────────────────────────────

    fun nextStep() {
        if (step < totalSteps) step++
    }

    fun prevStep() {
        if (step > 1) step--
    }

    // ── Image management ──────────────────────────────────────────────────────

    fun addImages(uris: List<Uri>) {
        val combined = (selectedImages + uris).take(10)
        selectedImages = combined
    }

    fun removeImage(uri: Uri) {
        selectedImages = selectedImages.filter { it != uri }
    }

    fun resetSubmitState() {
        _submitState.value = SubmitState.Idle
    }

    // ── Submit ────────────────────────────────────────────────────────────────

    fun submit(context: Context) {
        viewModelScope.launch {
            _submitState.value = SubmitState.Loading

            try {
                // Build text fields map
                val data = buildMap<String, @JvmSuppressWildcards okhttp3.RequestBody> {
                    fun str(value: String) = value.toRequestBody("text/plain".toMediaTypeOrNull())

                    put("title",        str(title.trim()))
                    put("description",  str(description.trim()))
                    put("category",     str(category))
                    put("wilaya",       str(wilaya))
                    if (ville.isNotBlank()) put("ville", str(ville.trim()))
                    put("condition",    str(condition))
                    put("offer_type",   str(offerType))
                    put("price_dzd",    str(price.trim()))
                    put("currency",     str(currency))
                    put("mediation_enabled", str(if (mediationEnabled) "1" else "0"))

                    // Specs packaged as flat keys: specs[group][key]
                    fun specStr(group: String, key: String, value: String) {
                        if (value.isNotBlank()) put("specs[$group][$key]", str(value))
                    }

                    when (category) {
                        "boat", "jetski" -> {
                            specStr("general",      "annee_construction", year)
                            specStr("general",      "marque",             brand)
                            specStr("general",      "modele",             model)
                            specStr("general",      "couleur",            color)
                            specStr("dimensions",   "longueur",           length)
                            specStr("motorisation", "puissance_totale",   power)
                            specStr("motorisation", "type_moteur",        engineType)
                            specStr("motorisation", "nb_moteurs",         nbEngines)
                        }
                        "engine" -> {
                            specStr("general",      "marque",           engineBrand)
                            specStr("motorisation", "puissance_totale", power)
                            specStr("motorisation", "type_moteur",      engineType)
                        }
                        "parts" -> {
                            specStr("general", "marque",          partBrand)
                            specStr("general", "compatible_avec", compatibleWith)
                        }
                    }
                }

                // Build image parts
                val imageParts = selectedImages.mapIndexed { index, uri ->
                    val inputStream = context.contentResolver.openInputStream(uri)
                        ?: throw Exception("Impossible de lire l'image $index")
                    val mimeType = context.contentResolver.getType(uri) ?: "image/jpeg"
                    val extension = when (mimeType) {
                        "image/png"  -> "png"
                        "image/webp" -> "webp"
                        else         -> "jpg"
                    }
                    val tempFile = File.createTempFile("img_$index", ".$extension", context.cacheDir)
                    FileOutputStream(tempFile).use { out -> inputStream.copyTo(out) }
                    inputStream.close()

                    val requestBody = tempFile.asRequestBody(mimeType.toMediaTypeOrNull())
                    MultipartBody.Part.createFormData("images[]", tempFile.name, requestBody)
                }

                val response = api.createListing(data, imageParts)

                if (response.isSuccessful) {
                    val listing = response.body()?.listing
                        ?: throw Exception("Réponse invalide du serveur")
                    _submitState.value = SubmitState.Success(listing)
                } else {
                    val errorBody = response.errorBody()?.string() ?: "Erreur inconnue"
                    _submitState.value = SubmitState.Error(
                        "Erreur ${response.code()}: $errorBody"
                    )
                }
            } catch (e: Exception) {
                _submitState.value = SubmitState.Error(
                    e.message ?: "Une erreur inattendue s'est produite"
                )
            }
        }
    }
}
