package com.albabor.app.viewmodel

import androidx.lifecycle.ViewModel
import androidx.lifecycle.ViewModelProvider
import androidx.lifecycle.viewModelScope
import com.albabor.app.data.model.Listing
import com.albabor.app.data.model.SellerProfileStats
import com.albabor.app.data.model.SellerProfileUser
import com.albabor.app.data.repository.ProfileRepository
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch

/**
 * Profil public d'un vendeur : toutes ses annonces actives.
 *
 * Un vendeur publie souvent plusieurs bateaux, ou des dizaines de pièces ;
 * depuis une annonce, l'acheteur doit pouvoir parcourir le reste de son stock.
 */
class SellerProfileViewModel(private val sellerId: Int) : ViewModel() {

    private val repo = ProfileRepository()

    private val _seller = MutableStateFlow<SellerProfileUser?>(null)
    val seller: StateFlow<SellerProfileUser?> = _seller.asStateFlow()

    private val _stats = MutableStateFlow(SellerProfileStats())
    val stats: StateFlow<SellerProfileStats> = _stats.asStateFlow()

    private val _listings = MutableStateFlow<List<Listing>>(emptyList())
    val listings: StateFlow<List<Listing>> = _listings.asStateFlow()

    private val _isLoading = MutableStateFlow(false)
    val isLoading: StateFlow<Boolean> = _isLoading.asStateFlow()

    private val _isLoadingMore = MutableStateFlow(false)
    val isLoadingMore: StateFlow<Boolean> = _isLoadingMore.asStateFlow()

    private val _error = MutableStateFlow<String?>(null)
    val error: StateFlow<String?> = _error.asStateFlow()

    private var currentPage = 1
    private var lastPage = 1

    val canLoadMore: Boolean get() = currentPage < lastPage

    init {
        load()
    }

    fun load() {
        viewModelScope.launch {
            _isLoading.value = true
            _error.value = null
            currentPage = 1
            repo.getSellerProfile(sellerId, page = 1)
                .onSuccess { response ->
                    _seller.value = response.user
                    _stats.value = response.stats
                    _listings.value = response.listings.data
                    currentPage = response.listings.meta?.currentPage ?: response.listings.currentPage
                    lastPage = response.listings.meta?.lastPage ?: response.listings.lastPage
                }
                .onFailure { _error.value = it.message }
            _isLoading.value = false
        }
    }

    fun loadMore() {
        if (_isLoadingMore.value || _isLoading.value || !canLoadMore) return

        viewModelScope.launch {
            _isLoadingMore.value = true
            repo.getSellerProfile(sellerId, page = currentPage + 1)
                .onSuccess { response ->
                    // Une annonce peut changer de page entre deux appels : on
                    // filtre par id pour ne pas la faire apparaître deux fois.
                    val known = _listings.value.map { it.id }.toSet()
                    _listings.value = _listings.value + response.listings.data.filterNot { it.id in known }
                    currentPage = response.listings.meta?.currentPage ?: response.listings.currentPage
                    lastPage = response.listings.meta?.lastPage ?: response.listings.lastPage
                }
            _isLoadingMore.value = false
        }
    }

    companion object {
        fun factory(sellerId: Int): ViewModelProvider.Factory = object : ViewModelProvider.Factory {
            @Suppress("UNCHECKED_CAST")
            override fun <T : ViewModel> create(modelClass: Class<T>): T =
                SellerProfileViewModel(sellerId) as T
        }
    }
}
