package com.albabor.app.data.model

import com.google.gson.annotations.SerializedName

data class Banner(
    val id: Int,
    val title: String,
    val subtitle: String?,
    @SerializedName("image_url") val imageUrl: String,
    @SerializedName("link_url") val linkUrl: String?,
    @SerializedName("company_name") val companyName: String?,
)

data class BannersResponse(
    val banners: List<Banner> = emptyList(),
)
