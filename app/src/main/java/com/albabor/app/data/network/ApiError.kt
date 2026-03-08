package com.albabor.app.data.network

import org.json.JSONObject
import retrofit2.Response

/**
 * Extract a human-readable error message from a failed Retrofit Response.
 *
 * Laravel typically returns:
 *   {"message": "...", "errors": {"field": ["msg1", ...]}}
 *
 * Priority:
 *   1. First validation error value (e.g. "The email has already been taken.")
 *   2. Top-level "message" field
 *   3. Raw error body string
 *   4. Fallback string
 */
fun Response<*>.errorMessage(fallback: String = "Une erreur s'est produite"): String {
    val raw = errorBody()?.string()?.takeIf { it.isNotBlank() } ?: return fallback
    return try {
        val json = JSONObject(raw)
        // Check validation errors first
        val errors = json.optJSONObject("errors")
        if (errors != null) {
            val firstKey = errors.keys().asSequence().firstOrNull()
            if (firstKey != null) {
                val arr = errors.optJSONArray(firstKey)
                if (arr != null && arr.length() > 0) return arr.getString(0)
            }
        }
        json.optString("message", raw).ifBlank { raw }
    } catch (_: Exception) {
        raw
    }
}
