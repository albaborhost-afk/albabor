package com.albabor.app.ui.screens.mediation

import androidx.compose.foundation.background
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.imePadding
import androidx.compose.foundation.layout.navigationBarsPadding
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.width
import androidx.compose.foundation.layout.widthIn
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.lazy.rememberLazyListState
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.text.KeyboardOptions
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ArrowBack
import androidx.compose.material.icons.automirrored.filled.Send
import androidx.compose.material.icons.filled.Block
import androidx.compose.material.icons.filled.Close
import androidx.compose.material3.AlertDialog
import androidx.compose.material3.Card
import androidx.compose.material3.CardDefaults
import androidx.compose.material3.CircularProgressIndicator
import androidx.compose.material3.ExperimentalMaterial3Api
import androidx.compose.material3.Icon
import androidx.compose.material3.IconButton
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.OutlinedTextField
import androidx.compose.material3.OutlinedTextFieldDefaults
import androidx.compose.material3.Scaffold
import androidx.compose.material3.Surface
import androidx.compose.material3.Text
import androidx.compose.material3.TextButton
import androidx.compose.material3.TopAppBar
import androidx.compose.material3.TopAppBarDefaults
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.input.ImeAction
import androidx.compose.ui.text.style.TextOverflow
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.lifecycle.compose.collectAsStateWithLifecycle
import androidx.lifecycle.viewmodel.compose.viewModel
import androidx.navigation.NavController
import coil.compose.AsyncImage
import com.albabor.app.data.model.MediationMessage
import com.albabor.app.data.model.MediationTicket
import com.albabor.app.ui.theme.Error500
import com.albabor.app.ui.theme.Gray100
import com.albabor.app.ui.theme.Gray200
import com.albabor.app.ui.theme.Gray400
import com.albabor.app.ui.theme.Gray50
import com.albabor.app.ui.theme.Gray500
import com.albabor.app.ui.theme.Gray700
import com.albabor.app.ui.theme.Gray900
import com.albabor.app.ui.theme.OceanBlue100
import com.albabor.app.ui.theme.OceanBlue700
import com.albabor.app.ui.theme.OceanBlue900
import com.albabor.app.ui.theme.White
import com.albabor.app.viewmodel.MediationViewModel

// ─── Mediation Detail Screen ──────────────────────────────────────────────────

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun MediationDetailScreen(navController: NavController, ticketId: Int) {
    val vm: MediationViewModel = viewModel()
    val ticket by vm.selectedTicket.collectAsStateWithLifecycle()
    val draft by vm.messageDraft.collectAsStateWithLifecycle()
    val isLoading by vm.isLoading.collectAsStateWithLifecycle()
    val isSending by vm.isSending.collectAsStateWithLifecycle()

    var showCancelDialog by remember { mutableStateOf(false) }

    val listState = rememberLazyListState()

    LaunchedEffect(ticketId) {
        vm.loadTicket(ticketId)
    }

    // Scroll to bottom when new messages arrive
    LaunchedEffect(ticket?.messages?.size) {
        val count = ticket?.messages?.size ?: 0
        if (count > 0) {
            listState.animateScrollToItem(count - 1)
        }
    }

    if (showCancelDialog) {
        CancelTicketDialog(
            onConfirm = {
                showCancelDialog = false
                vm.cancelTicket(ticketId) { navController.popBackStack() }
            },
            onDismiss = { showCancelDialog = false }
        )
    }

    Scaffold(
        topBar = {
            TopAppBar(
                title = {
                    Column {
                        Text(
                            text = ticket?.listing?.title ?: "Mediation",
                            style = MaterialTheme.typography.titleMedium.copy(
                                fontWeight = FontWeight.Bold,
                                color = Gray900
                            ),
                            maxLines = 1,
                            overflow = TextOverflow.Ellipsis
                        )
                        ticket?.let {
                            val (_, statusFg) = ticketStatusColors(it.status)
                            Text(
                                text = it.statusLabel,
                                style = MaterialTheme.typography.bodySmall.copy(
                                    color = statusFg,
                                    fontWeight = FontWeight.Medium
                                )
                            )
                        }
                    }
                },
                navigationIcon = {
                    IconButton(onClick = {
                        vm.clearSelectedTicket()
                        navController.popBackStack()
                    }) {
                        Icon(
                            imageVector = Icons.AutoMirrored.Filled.ArrowBack,
                            contentDescription = "Retour",
                            tint = Gray700
                        )
                    }
                },
                actions = {
                    // Show cancel button only if ticket is open/in-progress
                    val canCancel = ticket?.status == "open" || ticket?.status == "in_progress"
                    if (canCancel) {
                        IconButton(onClick = { showCancelDialog = true }) {
                            Icon(
                                imageVector = Icons.Filled.Block,
                                contentDescription = "Annuler la mediation",
                                tint = Error500
                            )
                        }
                    }
                },
                colors = TopAppBarDefaults.topAppBarColors(
                    containerColor = White,
                    scrolledContainerColor = White
                )
            )
        },
        containerColor = Gray50
    ) { innerPadding ->
        Column(
            modifier = Modifier
                .fillMaxSize()
                .padding(innerPadding)
        ) {
            // ── Listing summary card ─────────────────────────────────────────
            ticket?.listing?.let { listing ->
                ListingSummaryCard(
                    imageUrl = listing.primaryImage,
                    title = listing.title,
                    price = listing.formattedPrice,
                    wilaya = listing.wilaya
                )
            }

            // ── Status indicator ─────────────────────────────────────────────
            ticket?.let { t ->
                val (statusBg, statusFg) = ticketStatusColors(t.status)
                Row(
                    modifier = Modifier
                        .fillMaxWidth()
                        .background(statusBg.copy(alpha = 0.50f))
                        .padding(horizontal = 16.dp, vertical = 8.dp),
                    verticalAlignment = Alignment.CenterVertically,
                    horizontalArrangement = Arrangement.Center
                ) {
                    Text(
                        text = "Statut : ${t.statusLabel}",
                        style = MaterialTheme.typography.labelMedium.copy(
                            color = statusFg,
                            fontWeight = FontWeight.SemiBold
                        )
                    )
                }
            }

            // ── Messages list ────────────────────────────────────────────────
            if (isLoading && ticket == null) {
                Box(modifier = Modifier.weight(1f), contentAlignment = Alignment.Center) {
                    CircularProgressIndicator(color = OceanBlue700)
                }
            } else {
                val messages = ticket?.messages ?: emptyList()
                LazyColumn(
                    state = listState,
                    modifier = Modifier
                        .weight(1f)
                        .fillMaxWidth(),
                    verticalArrangement = Arrangement.spacedBy(8.dp),
                    contentPadding = androidx.compose.foundation.layout.PaddingValues(
                        horizontal = 12.dp,
                        vertical = 16.dp
                    )
                ) {
                    if (messages.isEmpty()) {
                        item {
                            Box(
                                modifier = Modifier.fillMaxWidth().padding(32.dp),
                                contentAlignment = Alignment.Center
                            ) {
                                Text(
                                    text = "Aucun message pour le moment.\nSoyez le premier a envoyer un message.",
                                    style = MaterialTheme.typography.bodyMedium.copy(color = Gray400),
                                    textAlign = androidx.compose.ui.text.style.TextAlign.Center
                                )
                            }
                        }
                    }
                    items(messages, key = { it.userId.toString() + it.createdAt }) { msg ->
                        // Determine if msg is "mine" — compare sender userId to current ticket buyer
                        val isMine = msg.userId == ticket?.buyer?.id
                        val senderName = when (msg.userId) {
                            ticket?.buyer?.id  -> ticket?.buyer?.name ?: "Acheteur"
                            ticket?.seller?.id -> ticket?.seller?.name ?: "Vendeur"
                            else               -> "Admin"
                        }
                        MediationChatBubble(message = msg, isMine = isMine, senderName = senderName)
                    }
                }
            }

            // ── Bottom input bar ─────────────────────────────────────────────
            val isResolved = ticket?.status == "resolved" || ticket?.status == "closed"
            ChatInputBar(
                draft = draft,
                onDraftChange = { vm.updateDraft(it) },
                onSend = { vm.sendMessage(ticketId) },
                isSending = isSending,
                disabled = isResolved
            )
        }
    }
}

// ─── Listing summary card ─────────────────────────────────────────────────────

@Composable
private fun ListingSummaryCard(
    imageUrl: String?,
    title: String,
    price: String,
    wilaya: String?
) {
    Card(
        modifier = Modifier
            .fillMaxWidth()
            .padding(horizontal = 12.dp, vertical = 8.dp),
        shape = RoundedCornerShape(12.dp),
        colors = CardDefaults.cardColors(containerColor = White),
        elevation = CardDefaults.cardElevation(defaultElevation = 2.dp)
    ) {
        Row(
            modifier = Modifier.padding(12.dp),
            verticalAlignment = Alignment.CenterVertically
        ) {
            Box(
                modifier = Modifier
                    .size(56.dp)
                    .clip(RoundedCornerShape(8.dp))
                    .background(OceanBlue100)
            ) {
                imageUrl?.let {
                    AsyncImage(
                        model = it,
                        contentDescription = null,
                        contentScale = ContentScale.Crop,
                        modifier = Modifier.fillMaxSize()
                    )
                }
            }
            Spacer(modifier = Modifier.width(12.dp))
            Column(modifier = Modifier.weight(1f)) {
                Text(
                    text = title,
                    style = MaterialTheme.typography.titleSmall.copy(
                        fontWeight = FontWeight.SemiBold,
                        color = Gray900
                    ),
                    maxLines = 2,
                    overflow = TextOverflow.Ellipsis
                )
                Spacer(modifier = Modifier.height(4.dp))
                Text(
                    text = price,
                    style = MaterialTheme.typography.bodyMedium.copy(
                        color = OceanBlue700,
                        fontWeight = FontWeight.Bold
                    )
                )
                wilaya?.let {
                    Text(
                        text = it,
                        style = MaterialTheme.typography.bodySmall.copy(color = Gray400)
                    )
                }
            }
        }
    }
}

// ─── Chat bubble ─────────────────────────────────────────────────────────────

@Composable
private fun MediationChatBubble(message: MediationMessage, isMine: Boolean, senderName: String = "") {
    val bubbleBg   = if (isMine) OceanBlue700 else Gray100
    val textColor  = if (isMine) White else Gray900
    val alignment  = if (isMine) Alignment.End else Alignment.Start
    val bubbleShape = if (isMine) {
        RoundedCornerShape(topStart = 16.dp, topEnd = 4.dp, bottomStart = 16.dp, bottomEnd = 16.dp)
    } else {
        RoundedCornerShape(topStart = 4.dp, topEnd = 16.dp, bottomStart = 16.dp, bottomEnd = 16.dp)
    }

    Column(
        modifier = Modifier.fillMaxWidth(),
        horizontalAlignment = alignment
    ) {
        // Sender name (for other party)
        if (!isMine) {
            Text(
                text = senderName.ifEmpty { "—" },
                style = MaterialTheme.typography.labelSmall.copy(
                    color = Gray500,
                    fontWeight = FontWeight.SemiBold
                ),
                modifier = Modifier.padding(start = 4.dp, bottom = 2.dp)
            )
        }

        Box(
            modifier = Modifier
                .widthIn(max = 280.dp)
                .clip(bubbleShape)
                .background(bubbleBg)
                .padding(horizontal = 14.dp, vertical = 10.dp)
        ) {
            Column {
                Text(
                    text = message.body,
                    style = MaterialTheme.typography.bodyMedium.copy(
                        color = textColor,
                        lineHeight = 20.sp
                    )
                )
                Spacer(modifier = Modifier.height(4.dp))
                Text(
                    text = message.createdAt.take(16).replace("T", " "),
                    style = MaterialTheme.typography.labelSmall.copy(
                        color = if (isMine) White.copy(alpha = 0.65f) else Gray400,
                        fontSize = 11.sp
                    ),
                    modifier = Modifier.align(Alignment.End)
                )
            }
        }
    }
}

// ─── Chat input bar ───────────────────────────────────────────────────────────

@Composable
fun ChatInputBar(
    draft: String,
    onDraftChange: (String) -> Unit,
    onSend: () -> Unit,
    isSending: Boolean,
    disabled: Boolean = false
) {
    Surface(
        color = White,
        shadowElevation = 8.dp
    ) {
        Row(
            modifier = Modifier
                .fillMaxWidth()
                .navigationBarsPadding()
                .imePadding()
                .padding(horizontal = 12.dp, vertical = 10.dp),
            verticalAlignment = Alignment.Bottom
        ) {
            OutlinedTextField(
                value = draft,
                onValueChange = onDraftChange,
                placeholder = {
                    Text(
                        text = if (disabled) "Conversation terminee" else "Votre message...",
                        style = MaterialTheme.typography.bodyMedium.copy(color = Gray400)
                    )
                },
                modifier = Modifier.weight(1f),
                shape = RoundedCornerShape(24.dp),
                enabled = !disabled && !isSending,
                maxLines = 5,
                keyboardOptions = KeyboardOptions(imeAction = ImeAction.Default),
                colors = OutlinedTextFieldDefaults.colors(
                    focusedBorderColor = OceanBlue700,
                    unfocusedBorderColor = Gray200,
                    focusedContainerColor = Gray50,
                    unfocusedContainerColor = Gray50,
                    cursorColor = OceanBlue700
                )
            )

            Spacer(modifier = Modifier.width(8.dp))

            // Send button
            Box(
                modifier = Modifier
                    .size(48.dp)
                    .clip(CircleShape)
                    .background(
                        if (draft.isNotBlank() && !disabled) OceanBlue700
                        else Gray200
                    )
                    .then(
                        if (draft.isNotBlank() && !disabled && !isSending) {
                            Modifier.android_clickable(onClick = onSend)
                        } else Modifier
                    ),
                contentAlignment = Alignment.Center
            ) {
                if (isSending) {
                    CircularProgressIndicator(
                        color = White,
                        strokeWidth = 2.dp,
                        modifier = Modifier.size(22.dp)
                    )
                } else {
                    Icon(
                        imageVector = Icons.AutoMirrored.Filled.Send,
                        contentDescription = "Envoyer",
                        tint = if (draft.isNotBlank() && !disabled) White else Gray400,
                        modifier = Modifier.size(20.dp)
                    )
                }
            }
        }
    }
}

// Alias to keep send-box modifier clean
private fun Modifier.android_clickable(onClick: () -> Unit): Modifier =
    this.clickable(onClick = onClick)

// ─── Cancel ticket dialog ─────────────────────────────────────────────────────

@Composable
private fun CancelTicketDialog(onConfirm: () -> Unit, onDismiss: () -> Unit) {
    AlertDialog(
        onDismissRequest = onDismiss,
        icon = {
            Icon(
                imageVector = Icons.Filled.Block,
                contentDescription = null,
                tint = Error500,
                modifier = Modifier.size(28.dp)
            )
        },
        title = {
            Text(
                text = "Annuler la mediation ?",
                style = MaterialTheme.typography.titleMedium.copy(fontWeight = FontWeight.Bold)
            )
        },
        text = {
            Text(
                text = "Cette action est irreversible. La demande de mediation sera fermee et aucun remboursement ne sera effectue.",
                style = MaterialTheme.typography.bodyMedium.copy(color = Gray500)
            )
        },
        confirmButton = {
            TextButton(onClick = onConfirm) {
                Text(
                    text = "Annuler la mediation",
                    style = MaterialTheme.typography.labelLarge.copy(color = Error500)
                )
            }
        },
        dismissButton = {
            TextButton(onClick = onDismiss) {
                Text(
                    text = "Garder",
                    style = MaterialTheme.typography.labelLarge.copy(color = OceanBlue700)
                )
            }
        },
        containerColor = White,
        shape = RoundedCornerShape(20.dp)
    )
}
