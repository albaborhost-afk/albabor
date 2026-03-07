package com.albabor.app

import android.app.Application
import com.albabor.app.data.network.NetworkModule

class AlBaborApp : Application() {
    override fun onCreate() {
        super.onCreate()
        NetworkModule.init(this)
    }
}
