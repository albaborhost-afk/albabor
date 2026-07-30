package com.albabor.app

import android.app.Application
import coil.ImageLoader
import coil.ImageLoaderFactory
import coil.disk.DiskCache
import coil.memory.MemoryCache
import com.albabor.app.data.network.NetworkModule
import com.albabor.app.data.network.TokenStore
import kotlinx.coroutines.runBlocking
import okhttp3.Interceptor
import okhttp3.OkHttpClient
import java.util.concurrent.TimeUnit

class AlBaborApp : Application(), ImageLoaderFactory {

    override fun onCreate() {
        super.onCreate()
        NetworkModule.init(this)
        // Prime the auth-token cache once so interceptors can read it synchronously
        // instead of blocking a network thread on a DataStore read per request.
        runBlocking { TokenStore.prime(this@AlBaborApp) }
    }

    /**
     * Custom Coil ImageLoader that adds the Bearer token to all image requests.
     * This allows loading listing media images (including non-active listings
     * visible to their owner) from the server.
     */
    override fun newImageLoader(): ImageLoader {
        val authInterceptor = Interceptor { chain ->
            val token = TokenStore.cached()
            val request = chain.request().newBuilder().apply {
                token?.let { header("Authorization", "Bearer $it") }
            }.build()
            chain.proceed(request)
        }

        val okHttpClient = OkHttpClient.Builder()
            .addInterceptor(authInterceptor)
            .connectTimeout(30, TimeUnit.SECONDS)
            .readTimeout(30, TimeUnit.SECONDS)
            .build()

        return ImageLoader.Builder(this)
            .okHttpClient(okHttpClient)
            .memoryCache {
                MemoryCache.Builder(this)
                    .maxSizePercent(0.30)   // 30% of available RAM for image memory cache
                    .build()
            }
            .diskCache {
                DiskCache.Builder()
                    .directory(cacheDir.resolve("image_cache"))
                    .maxSizePercent(0.05)   // 5% of cache dir (~50-100 MB) for disk cache
                    .build()
            }
            .crossfade(true)
            .allowHardware(true)           // GPU-backed bitmaps for sharper rendering
            .build()
    }
}
