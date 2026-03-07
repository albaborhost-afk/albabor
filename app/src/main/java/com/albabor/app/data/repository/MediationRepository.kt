package com.albabor.app.data.repository

import com.albabor.app.data.model.*
import com.albabor.app.data.network.NetworkModule

class MediationRepository {
    private val api = NetworkModule.apiService

    suspend fun getTickets(): Result<List<MediationTicket>> = runCatching {
        val response = api.getMediationTickets()
        if (response.isSuccessful) response.body()?.data ?: emptyList()
        else throw Exception(response.errorBody()?.string() ?: "Erreur")
    }

    suspend fun getTicket(id: Int): Result<MediationTicket> = runCatching {
        val response = api.getMediationTicket(id)
        if (response.isSuccessful) response.body()?.data!!
        else throw Exception(response.errorBody()?.string() ?: "Erreur")
    }

    suspend fun createTicket(listingId: Int, message: String): Result<MediationTicket> = runCatching {
        val response = api.createMediationTicket(
            mapOf("listing_id" to listingId.toString(), "message" to message)
        )
        if (response.isSuccessful) response.body()?.data!!
        else throw Exception(response.errorBody()?.string() ?: "Erreur")
    }

    suspend fun sendMessage(ticketId: Int, body: String): Result<MediationMessage> = runCatching {
        val response = api.sendMediationMessage(ticketId, mapOf("body" to body))
        if (response.isSuccessful) response.body()?.data!!
        else throw Exception(response.errorBody()?.string() ?: "Erreur")
    }

    suspend fun cancelTicket(id: Int): Result<Unit> = runCatching {
        val response = api.cancelMediationTicket(id)
        if (!response.isSuccessful) throw Exception(response.errorBody()?.string() ?: "Erreur")
    }

    suspend fun getConversations(): Result<List<Conversation>> = runCatching {
        val response = api.getConversations()
        if (response.isSuccessful) response.body()?.data ?: emptyList()
        else throw Exception(response.errorBody()?.string() ?: "Erreur")
    }

    suspend fun getConversation(id: Int): Result<Conversation> = runCatching {
        val response = api.getConversation(id)
        if (response.isSuccessful) response.body()?.data!!
        else throw Exception(response.errorBody()?.string() ?: "Erreur")
    }

    suspend fun getMessages(conversationId: Int): Result<List<ConversationMessage>> = runCatching {
        val response = api.getMessages(conversationId)
        if (response.isSuccessful) response.body()?.data ?: emptyList()
        else throw Exception(response.errorBody()?.string() ?: "Erreur")
    }

    suspend fun sendConversationMessage(conversationId: Int, body: String): Result<ConversationMessage> = runCatching {
        val response = api.sendMessage(conversationId, mapOf("body" to body))
        if (response.isSuccessful) response.body()?.data!!
        else throw Exception(response.errorBody()?.string() ?: "Erreur")
    }
}
