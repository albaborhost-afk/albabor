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
        // Public setter so we can reset type when category changes
    var selectedType by mutableStateOf<String?>(null)

    fun updateCategory(newCategory: String) {
        category = newCategory
        // Reset type when switching categories — only boats have types
        if (newCategory != "boat") {
            selectedType = null
        }
    }

    // ── Step 2 – Informations ─────────────────────────────────────────────────

    var title       by mutableStateOf("")
    var description by mutableStateOf("")
    var wilaya      by mutableStateOf("")   // selected country
    var ville       by mutableStateOf("")   // city / region free text

    // ── Step 3 – Prix & conditions ────────────────────────────────────────────

    var condition   by mutableStateOf("")   // new | like_new | good | average | needs_revision
    var offerType   by mutableStateOf("")   // negotiable | fixed | free
    var price             by mutableStateOf("")
    var currency          by mutableStateOf("DZD")   // DZD | EUR
    var mediationEnabled  by mutableStateOf(false)

    // ── Step 4 – Caractéristiques ─────────────────────────────────────────────

    // General (boat / jetski / engine)
    var year           by mutableStateOf("")
    var manufacturer   by mutableStateOf("")
    var model          by mutableStateOf("")
    var color          by mutableStateOf("")
    var registration   by mutableStateOf("")

    // Dimensions (boat / jetski)
    var length         by mutableStateOf("")   // metres
    var width          by mutableStateOf("")   // metres
    var tonnage        by mutableStateOf("")   // tonnes
    var waterDraft     by mutableStateOf("")   // boat only
    var airDraft       by mutableStateOf("")   // boat only

    // Motorisation (boat / jetski / engine)
    var engineBrand    by mutableStateOf("")
    var propulsion     by mutableStateOf("")
    var fuelType       by mutableStateOf("")
    var powerPerEngine by mutableStateOf("")   // CV
    var engineCount    by mutableStateOf("")
    var engineHours    by mutableStateOf("")
    var cylinders      by mutableStateOf("")   // engine only

    // Reservoirs (boat)
    var reservoirCount         by mutableStateOf("")
    var fuelTankCapacity       by mutableStateOf("")
    var freshWaterTankCapacity by mutableStateOf("")
    var storageCapacity        by mutableStateOf("")

    // Amenities (boat)
    var berthCount     by mutableStateOf("")
    var cabinCount     by mutableStateOf("")
    var sanitaryCount  by mutableStateOf("")
    var kitchenCount   by mutableStateOf("")

    // Extras (boat / jetski)
    var trailerIncluded by mutableStateOf("")
    var trailerBrand    by mutableStateOf("")
    var berthAvailable  by mutableStateOf("")
    var portAddress     by mutableStateOf("")
    var berthLength     by mutableStateOf("")
    var berthWidth      by mutableStateOf("")

    // Parts
    var partType        by mutableStateOf("")
    var partBrand       by mutableStateOf("")
    var compatibleWith  by mutableStateOf("")
    var partNumber      by mutableStateOf("")

    // ── Step 5 – Photos ───────────────────────────────────────────────────────

    var selectedImages by mutableStateOf<List<Uri>>(emptyList())
        private set

    // ── Submit ────────────────────────────────────────────────────────────────

    private val _submitState = MutableStateFlow<SubmitState>(SubmitState.Idle)
    val submitState: StateFlow<SubmitState> = _submitState.asStateFlow()

    // ── Validation ────────────────────────────────────────────────────────────

    /** Returns null if valid, or an error message string. */
    fun validateCurrentStep(): String? = when (step) {
        1 -> when {
            category.isBlank() -> "Veuillez sélectionner une catégorie"
            category == "boat" && selectedType.isNullOrBlank() -> "Veuillez sélectionner un type de bateau"
            else -> null
        }
        2 -> when {
            title.isBlank()       -> "Le titre est obligatoire"
            description.isBlank() -> "La description est obligatoire"
            wilaya.isBlank()      -> "Le pays est obligatoire"
            else                  -> null
        }
        3 -> when {
            condition.isBlank()   -> "L'état est obligatoire"
            offerType.isBlank()   -> "Le type d'offre est obligatoire"
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

    fun replaceImage(oldUri: Uri, newUri: Uri) {
        selectedImages = selectedImages.map { if (it == oldUri) newUri else it }
    }

    fun resetSubmitState() {
        _submitState.value = SubmitState.Idle
    }

    private fun formatCalculatedValue(value: Double): String {
        val rounded = value.toLong()
        return if (value == rounded.toDouble()) rounded.toString() else value.toString()
    }

    val totalPower: String
        get() {
            val unitPower = powerPerEngine.toDoubleOrNull() ?: return ""

            return when (category) {
                "boat", "jetski" -> {
                    val count = engineCount.toDoubleOrNull() ?: return ""
                    formatCalculatedValue(count * unitPower)
                }
                "engine" -> formatCalculatedValue(unitPower)
                else     -> ""
            }
        }

    val totalReservoirCapacity: String
        get() {
            val values = listOf(fuelTankCapacity, freshWaterTankCapacity, storageCapacity)
                .mapNotNull { it.toDoubleOrNull() }

            if (values.isEmpty()) return ""

            return formatCalculatedValue(values.sum())
        }

    // ── Submit ────────────────────────────────────────────────────────────────

    fun submit(context: Context) {
        viewModelScope.launch {
            _submitState.value = SubmitState.Loading

            try {
                val parts = buildList {
                    fun textPart(name: String, value: String) {
                        if (value.isNotBlank()) add(MultipartBody.Part.createFormData(name, value))
                    }

                    textPart("title", title.trim())
                    textPart("description", description.trim())
                    textPart("category", category)
                    selectedType?.let { textPart("type", it) }
                    textPart("wilaya", wilaya)
                    textPart("ville", ville.trim())
                    textPart("etat", condition)
                    textPart("type_offre", offerType)
                    textPart("price_dzd", price.trim())
                    textPart("currency", currency)
                    textPart("mediation_enabled", if (mediationEnabled) "1" else "0")

                    fun specPart(group: String, key: String, value: String) {
                        textPart("specs[$group][$key]", value)
                    }

                    when (category) {
                        "boat", "jetski" -> {
                            specPart("general",      "annee_construction", year)
                            specPart("general",      "fabricant",          manufacturer)
                            specPart("general",      "modele",             model)
                            specPart("general",      "couleur",            color)
                            specPart("general",      "immatriculation",    registration)

                            specPart("dimensions",   "longueur",           length)
                            specPart("dimensions",   "largeur",            width)
                            specPart("dimensions",   "tonnage",            tonnage)

                            specPart("motorisation", "marque_moteur",      engineBrand)
                            specPart("motorisation", "propulsion",         propulsion)
                            specPart("motorisation", "type_carburant",     fuelType)
                            specPart("motorisation", "nombre_moteurs",     engineCount)
                            specPart("motorisation", "puissance_par_moteur", powerPerEngine)
                            specPart("motorisation", "puissance_totale",   totalPower)
                            specPart("motorisation", "nombre_heures",      engineHours)

                            specPart("extras",       "remorque",           trailerIncluded)
                            if (trailerIncluded == "oui") {
                                specPart("extras",   "marque_remorque",    trailerBrand)
                            }

                            specPart("extras",       "place_au_port",      berthAvailable)
                            if (berthAvailable == "oui") {
                                specPart("extras",   "adresse_port",       portAddress)
                                specPart("extras",   "longueur_place",     berthLength)
                                specPart("extras",   "largeur_place",      berthWidth)
                            }

                            if (category == "boat") {
                                specPart("dimensions",   "tirant_eau",            waterDraft)
                                specPart("dimensions",   "tirant_air",            airDraft)

                                specPart("reservoirs",   "nombre_reservoirs",     reservoirCount)
                                specPart("reservoirs",   "reservoir_carburant",   fuelTankCapacity)
                                specPart("reservoirs",   "reservoir_eau_douce",   freshWaterTankCapacity)
                                specPart("reservoirs",   "stockage",              storageCapacity)

                                specPart("amenagements", "nombre_couchettes",     berthCount)
                                specPart("amenagements", "nombre_cabines",        cabinCount)
                                specPart("amenagements", "nombre_sanitaire",      sanitaryCount)
                                specPart("amenagements", "nombre_cuisine",        kitchenCount)
                            }
                        }

                        "engine" -> {
                            specPart("general",      "annee_construction", year)
                            specPart("general",      "fabricant",          manufacturer)
                            specPart("general",      "modele",             model)

                            specPart("motorisation", "marque_moteur",      engineBrand)
                            specPart("motorisation", "propulsion",         propulsion)
                            specPart("motorisation", "type_carburant",     fuelType)
                            specPart("motorisation", "puissance_par_moteur", powerPerEngine)
                            specPart("motorisation", "puissance_totale",   totalPower)
                            specPart("motorisation", "nombre_heures",      engineHours)
                            specPart("motorisation", "cylindree",          cylinders)
                        }

                        "parts" -> {
                            specPart("general", "marque",          partBrand)
                            specPart("general", "part_type",       partType)
                            specPart("general", "compatible_with", compatibleWith)
                            specPart("general", "part_number",     partNumber)
                        }
                    }
                }

                // Build image parts — collect temp files so we can clean up afterwards
                val tempFiles = mutableListOf<File>()
                val imageParts = selectedImages.mapIndexed { index, uri ->
                    val mimeType = context.contentResolver.getType(uri) ?: "image/jpeg"
                    val extension = when (mimeType) {
                        "image/png"  -> "png"
                        "image/webp" -> "webp"
                        else         -> "jpg"
                    }
                    val tempFile = File.createTempFile("img_$index", ".$extension", context.cacheDir)
                    tempFiles += tempFile
                    (context.contentResolver.openInputStream(uri)
                        ?: throw Exception("Impossible de lire l'image $index"))
                        .use { stream ->
                            FileOutputStream(tempFile).use { out -> stream.copyTo(out) }
                        }

                    val requestBody = tempFile.asRequestBody(mimeType.toMediaTypeOrNull())
                    MultipartBody.Part.createFormData("images[]", tempFile.name, requestBody)
                }

                try {
                    val response = api.createListing(parts + imageParts)

                    if (response.isSuccessful) {
                        val listing = response.body()?.listing
                            ?: throw Exception("Réponse invalide du serveur")
                        _submitState.value = SubmitState.Success(listing)
                    } else {
                        val rawError = response.errorBody()?.string() ?: ""
                        val msg = try {
                            val json = org.json.JSONObject(rawError)
                            json.optString("message", "").ifBlank { "Erreur ${response.code()}" }
                        } catch (_: Exception) {
                            "Erreur ${response.code()}"
                        }
                        _submitState.value = SubmitState.Error(msg)
                    }
                } finally {
                    // Always clean up temp cache files, success or failure
                    tempFiles.forEach { runCatching { it.delete() } }
                }
            } catch (e: Exception) {
                _submitState.value = SubmitState.Error(
                    e.message ?: "Une erreur inattendue s'est produite"
                )
            }
        }
    }
}
