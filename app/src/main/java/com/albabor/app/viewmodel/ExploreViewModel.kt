package com.albabor.app.viewmodel

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.albabor.app.data.model.Listing
import com.albabor.app.data.model.ListingFilters
import com.albabor.app.data.repository.FavoriteRepository
import com.albabor.app.data.repository.ListingRepository
import kotlinx.coroutines.FlowPreview
import kotlinx.coroutines.Job
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch

@OptIn(FlowPreview::class)
class ExploreViewModel : ViewModel() {
    private val repo = ListingRepository()
    private val favRepo = FavoriteRepository()

    private val _filters = MutableStateFlow(ListingFilters())
    val filters: StateFlow<ListingFilters> = _filters.asStateFlow()

    private val _listings = MutableStateFlow<List<Listing>>(emptyList())
    val listings: StateFlow<List<Listing>> = _listings.asStateFlow()

    private val _isLoading = MutableStateFlow(false)
    val isLoading: StateFlow<Boolean> = _isLoading.asStateFlow()

    private val _totalCount = MutableStateFlow(0)
    val totalCount: StateFlow<Int> = _totalCount.asStateFlow()

    private val _error = MutableStateFlow<String?>(null)
    val error: StateFlow<String?> = _error.asStateFlow()

    private var searchJob: Job? = null

    init { search() }

    fun updateFilters(filters: ListingFilters) {
        _filters.value = filters
        search()
    }

    fun updateSearch(query: String) {
        _filters.value = _filters.value.copy(search = query.takeIf { it.isNotBlank() }, page = 1)
        search()
    }

    fun clearFilters() {
        _filters.value = ListingFilters(search = _filters.value.search)
        search()
    }

    fun search() {
        searchJob?.cancel()
        searchJob = viewModelScope.launch {
            _isLoading.value = true
            _error.value = null
            repo.getListings(_filters.value)
                .onSuccess {
                    _listings.value = it.data
                    _totalCount.value = it.meta?.total ?: it.data.size
                }
                .onFailure { _error.value = it.message }
            _isLoading.value = false
        }
    }

    fun toggleFavorite(listingId: Int) {
        viewModelScope.launch {
            favRepo.toggleFavorite(listingId)
                .onSuccess { isFavorited ->
                    // Update the listing's isFavorited state in the current list
                    _listings.value = _listings.value.map { listing ->
                        if (listing.id == listingId) listing.copy(isFavorited = isFavorited)
                        else listing
                    }
                }
        }
    }

    fun clearError() { _error.value = null }
}
