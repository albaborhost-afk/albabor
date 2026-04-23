package com.albabor.app.viewmodel

import android.util.Log
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.albabor.app.data.model.Banner
import com.albabor.app.data.model.Listing
import com.albabor.app.data.model.ListingFilters
import com.albabor.app.data.network.NetworkModule
import com.albabor.app.data.repository.BannerRepository
import com.albabor.app.data.repository.ListingRepository
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch

class HomeViewModel : ViewModel() {
    private val repo = ListingRepository()
    private val bannerRepo = BannerRepository()

    private val _featured = MutableStateFlow<List<Listing>>(emptyList())
    val featured: StateFlow<List<Listing>> = _featured.asStateFlow()

    private val _latest = MutableStateFlow<List<Listing>>(emptyList())
    val latest: StateFlow<List<Listing>> = _latest.asStateFlow()

    private val _banners = MutableStateFlow<List<Banner>>(emptyList())
    val banners: StateFlow<List<Banner>> = _banners.asStateFlow()

    private val _isLoading = MutableStateFlow(false)
    val isLoading: StateFlow<Boolean> = _isLoading.asStateFlow()

    private val _error = MutableStateFlow<String?>(null)
    val error: StateFlow<String?> = _error.asStateFlow()

    private val _unreadCount = MutableStateFlow(0)
    val unreadCount: StateFlow<Int> = _unreadCount.asStateFlow()

    init { loadData() }

    fun loadData() {
        viewModelScope.launch {
            _isLoading.value = true
            _error.value = null
            repo.getFeaturedListings()
                .onSuccess { _featured.value = it }
                .onFailure { _error.value = it.message }
            repo.getListings(ListingFilters(sortBy = "recent", page = 1))
                .onSuccess { _latest.value = it.data }
                .onFailure { if (_error.value == null) _error.value = it.message }
            _isLoading.value = false
        }
        loadBanners()
        loadUnreadCount()
    }

    fun loadBanners() {
        viewModelScope.launch {
            bannerRepo.getBanners()
                .onSuccess { _banners.value = it }
                .onFailure { Log.w("HomeViewModel", "Banner load failed: ${it.message}") }
        }
    }

    fun trackBannerClick(id: Int) {
        viewModelScope.launch {
            bannerRepo.trackClick(id)
                .onFailure { Log.w("HomeViewModel", "Banner click track failed: ${it.message}") }
        }
    }

    fun loadUnreadCount() {
        viewModelScope.launch {
            runCatching { NetworkModule.apiService.getUnreadCount() }
                .onSuccess { response ->
                    if (response.isSuccessful) {
                        _unreadCount.value = response.body()?.unreadCount ?: 0
                    }
                }
        }
    }

    fun clearError() { _error.value = null }
}
