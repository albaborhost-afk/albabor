package com.albabor.app.data.repository

import com.albabor.app.data.model.*
import com.albabor.app.data.network.NetworkModule
import com.albabor.app.data.network.errorMessage

class MediationRepository {
    private val api = NetworkModule.apiService

    // ── Mediation ─────────────────────────────────────────────────────────────

    /** Returns combined buyer+seller tickets sorted by newest first. */
    suspend fun getTickets(): Result<List<MediationTicket>> = runCatching {
        val response = api.getMediationTickets()
        if (response.isSuccessful) {
            val body = response.body()!!
            (body.buyerTickets + body.sellerTickets).sortedByDescending { it.createdAt }
        } else throw Exception(response.errorMessage())
    }

    suspend fun getTicket(id: Int): Result<MediationTicket> = runCatching {
        val response = api.getMediationTicket(id)
        if (response.isSuccessful) response.body()?.ticket!!
        else throw Exception(response.errorMessage())
    }

    /** listingId goes in the URL path; message is the opening message body. */
    suspend fun createTicket(listingId: Int, message: String): Result<MediationTicket> = runCatching {
        val response = api.createMediationTicket(listingId, mapOf("message" to message))
        if (response.isSuccessful) response.body()?.ticket!!
        else throw Exception(response.errorMessage())
    }

    suspend fun sendMessage(ticketId: Int, body: String): Result<Unit> = runCatching {
        val response = api.sendMediationMessage(ticketId, mapOf("message" to body))
        if (!response.isSuccessful) throw Exception(response.errorMessage())
    }

    suspend fun cancelTicket(id: Int): Result<Unit> = runCatching {
        val response = api.cancelMediationTicket(id)
        if (!response.isSuccessful) throw Exception(response.errorMessage())
    }

    // ── Conversations ─────────────────────────────────────────────────────────

    suspend fun getConversations(): Result<List<Conversation>> = runCatching {
        val response = api.getConversations()
        if (response.isSuccessful) response.body()?.data ?: emptyList()
        else throw Exception(response.errorMessage())
    }

    suspend fun getConversation(id: Int): Result<Conversation> = runCatching {
        val response = api.getConversation(id)
        if (response.isSuccessful) response.body()!!
        else throw Exception(response.errorMessage())
    }

    suspend fun startConversation(listingId: Int, body: String): Result<Conversation> = runCatching {
        val response = api.startConversation(listingId, mapOf("body" to body))
        if (response.isSuccessful) response.body()?.conversation!!
        else throw Exception(response.errorMessage())
    }

    suspend fun getMessages(conversationId: Int): Result<List<ConversationMessage>> = runCatching {
        val response = api.getMessages(conversationId)
        if (response.isSuccessful) response.body()?.data ?: emptyList()
        else throw Exception(response.errorMessage())
    }

    suspend fun sendConversationMessage(conversationId: Int, body: String): Result<ConversationMessage> = runCatching {
        val response = api.sendMessage(conversationId, mapOf("body" to body))
        if (response.isSuccessful) response.body()!!
        else throw Exception(response.errorMessage())
    }
}
