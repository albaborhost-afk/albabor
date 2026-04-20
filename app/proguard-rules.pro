# AlBabor – ProGuard / R8 rules for release builds

# ── Kotlin ─────────────────────────────────────────────────────────────────
-keep class kotlin.** { *; }
-keep class kotlin.Metadata { *; }
-dontwarn kotlin.**

# ── Gson (JSON serialisation) ───────────────────────────────────────────────
-keepattributes Signature
-keepattributes *Annotation*
-keep class com.google.gson.** { *; }
-keep class * implements com.google.gson.TypeAdapterFactory { *; }
-keep class * implements com.google.gson.JsonSerializer { *; }
-keep class * implements com.google.gson.JsonDeserializer { *; }
# Preserve field names used by Gson (data models)
-keep class com.albabor.app.data.model.** { *; }

# ── Retrofit ───────────────────────────────────────────────────────────────
-dontwarn retrofit2.**
-keep class retrofit2.** { *; }
-keepclasseswithmembers class * {
    @retrofit2.http.* <methods>;
}

# ── OkHttp ─────────────────────────────────────────────────────────────────
-dontwarn okhttp3.**
-dontwarn okio.**
-keep class okhttp3.** { *; }
-keep interface okhttp3.** { *; }

# ── Coil (image loading) ───────────────────────────────────────────────────
-keep class coil.** { *; }
-dontwarn coil.**

# ── Coroutines ─────────────────────────────────────────────────────────────
-keepnames class kotlinx.coroutines.internal.MainDispatcherFactory { *; }
-keepnames class kotlinx.coroutines.CoroutineExceptionHandler { *; }
-dontwarn kotlinx.coroutines.**

# ── AndroidX / Compose ─────────────────────────────────────────────────────
-keep class androidx.compose.** { *; }
-dontwarn androidx.compose.**

# ── Google Sign-In ─────────────────────────────────────────────────────────
-keep class com.google.android.gms.auth.** { *; }
-keep class com.google.android.gms.common.** { *; }
-dontwarn com.google.android.gms.**

# ── App – keep all ViewModels & Repositories ───────────────────────────────
-keep class com.albabor.app.viewmodel.** { *; }
-keep class com.albabor.app.data.repository.** { *; }
-keep class com.albabor.app.data.network.ApiService { *; }

# ── General safety ─────────────────────────────────────────────────────────
-keepattributes SourceFile,LineNumberTable
-renamesourcefileattribute SourceFile
