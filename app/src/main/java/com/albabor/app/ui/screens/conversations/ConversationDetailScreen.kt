package com.albabor.app.ui.screens.conversations

import androidx.compose.animation.AnimatedVisibility
import androidx.compose.animation.expandVertically
import androidx.compose.animation.shrinkVertically
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
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.width
import androidx.compose.foundation.layout.widthIn
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.lazy.rememberLazyListState
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ArrowBack
import androidx.compose.material.icons.filled.ExpandLess
import androidx.compose.material.icons.filled.ExpandMore
import androidx.compose.material3.Card
import androidx.compose.material3.CardDefaults
import androidx.compose.material3.CircularProgressIndicator
import androidx.compose.material3.ExperimentalMaterial3Api
import androidx.compose.material3.Icon
import androidx.compose.material3.IconButton
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Scaffold
import androidx.compose.material3.Text
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
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextOverflow
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.lifecycle.compose.collectAsStateWithLifecycle
import androidx.lifecycle.viewmodel.compose.viewModel
import androidx.navigation.NavController
import coil.compose.AsyncImage
import com.albabor.app.data.model.ConversationMessage
import com.albabor.app.ui.screens.mediation.ChatInputBar
import com.albabor.app.ui.theme.Gray100
import com.albabor.app.ui.theme.Gray400
import com.albabor.app.ui.theme.Gray50
import com.albabor.app.ui.theme.Gray500
import com.albabor.app.ui.theme.Gray700
import com.albabor.app.ui.theme.Gray900
import com.albabor.app.ui.theme.OceanBlue100
import com.albabor.app.ui.theme.OceanBlue700
import com.albabor.app.ui.theme.White
import com.albabor.app.viewmodel.ConversationsViewModel

// ─── ConversationDetailScreen ─────────────────────────────────────────────────

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun ConversationDetailScreen(navController: NavController, conversationId: Int) {
    val vm: ConversationsViewModel = viewModel()
    val conversation by vm.selectedConversation.collectAsStateWithLifecycle()
    val messages by vm.messages.collectAsStateWithLifecycle()
    val draft by vm.messageDraft.collectAsStateWithLifecycle()
    val isLoading by vm.isLoading.collectAsStateWithLifecycle()
    val isSending by vm.isSending.collectAsStateWithLifecycle()
    val currentUserId by vm.currentUserId.collectAsStateWithLifecycle()

    var listingCardExpanded by remember { mutableStateOf(true) }

    val listState = rememberLazyListState()

    LaunchedEffect(conversationId) {
        vm.loadMessages(conversationId)
    }

    // Scroll to latest message whenever messages change
    LaunchedEffect(messages.size) {
        if (messages.isNotEmpty()) {
            listState.animateScrollToItem(messages.size - 1)
        }
    }

    val otherUser    = conversation?.otherUser
    val otherName    = otherUser?.name ?: "Message"
    val listingTitle = conversation?.listing?.title ?: ""

    Scaffold(
        topBar = {
            TopAppBar(
                title = {
                    Column {
                        Text(
                            text = otherName,
                            style = MaterialTheme.typography.titleMedium.copy(
                                fontWeight = FontWeight.Bold,
                                color = Gray900
                            ),
                            maxLines = 1,
                            overflow = TextOverflow.Ellipsis
                        )
                        if (listingTitle.isNotEmpty()) {
                            Text(
                                text = listingTitle,
                                style = MaterialTheme.typography.bodySmall.copy(color = Gray500),
                                maxLines = 1,
                                overflow = TextOverflow.Ellipsis
                            )
                        }
                    }
                },
                navigationIcon = {
                    IconButton(onClick = {
                        vm.clearSelectedConversation()
                        navController.popBackStack()
                    }) {
                        Icon(
                            imageVector = Icons.AutoMirrored.Filled.ArrowBack,
                            contentDescription = "Retour",
                            tint = Gray700
                        )
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
            // ── Collapsible listing preview card ─────────────────────────────
            conversation?.listing?.let { listing ->
                Card(
                    modifier = Modifier
                        .fillMaxWidth()
                        .padding(horizontal = 12.dp, vertical = 8.dp),
                    shape = RoundedCornerShape(12.dp),
                    colors = CardDefaults.cardColors(containerColor = White),
                    elevation = CardDefaults.cardElevation(defaultElevation = 2.dp)
                ) {
                    Column {
                        // Header row (always visible)
                        Row(
                            modifier = Modifier
                                .fillMaxWidth()
                                .clickable { listingCardExpanded = !listingCardExpanded }
                                .padding(horizontal = 12.dp, vertical = 10.dp),
                            verticalAlignment = Alignment.CenterVertically
                        ) {
                            Text(
                                text = "Annonce",
                                style = MaterialTheme.typography.labelMedium.copy(
                                    color = OceanBlue700,
                                    fontWeight = FontWeight.SemiBold
                                ),
                                modifier = Modifier.weight(1f)
                            )
                            Icon(
                                imageVector = if (listingCardExpanded)
                                    Icons.Filled.ExpandLess else Icons.Filled.ExpandMore,
                                contentDescription = null,
                                tint = Gray400,
                                modifier = Modifier.size(20.dp)
                            )
                        }

                        // Expandable content
                        AnimatedVisibility(
                            visible = listingCardExpanded,
                            enter = expandVertically(),
                            exit = shrinkVertically()
                        ) {
                            Row(
                                modifier = Modifier.padding(
                                    start = 12.dp, end = 12.dp, bottom = 12.dp
                                ),
                                verticalAlignment = Alignment.CenterVertically
                            ) {
                                Box(
                                    modifier = Modifier
                                        .size(56.dp)
                                        .clip(RoundedCornerShape(8.dp))
                                        .background(OceanBlue100)
                                ) {
                                    listing.primaryImage?.let { url ->
                                        AsyncImage(
                                            model = url,
                                            contentDescription = null,
                                            contentScale = ContentScale.Crop,
                                            modifier = Modifier.fillMaxSize()
                                        )
                                    }
                                }
                                Spacer(modifier = Modifier.width(12.dp))
                                Column(modifier = Modifier.weight(1f)) {
                                    Text(
                                        text = listing.title,
                                        style = MaterialTheme.typography.titleSmall.copy(
                                            fontWeight = FontWeight.SemiBold,
                                            color = Gray900
                                        ),
                                        maxLines = 2,
                                        overflow = TextOverflow.Ellipsis
                                    )
                                    Spacer(modifier = Modifier.height(4.dp))
                                    Text(
                                        text = listing.formattedPrice,
                                        style = MaterialTheme.typography.bodyMedium.copy(
                                            color = OceanBlue700,
                                            fontWeight = FontWeight.Bold
                                        )
                                    )
                                    listing.wilaya?.let { w ->
                                        Text(
                                            text = w,
                                            style = MaterialTheme.typography.bodySmall.copy(
                                                color = Gray400
                                            )
                                        )
                                    }
                                }
                            }
                        }
                    }
                }
            }

            // ── Messages ─────────────────────────────────────────────────────
            if (isLoading && messages.isEmpty()) {
                Box(modifier = Modifier.weight(1f), contentAlignment = Alignment.Center) {
                    CircularProgressIndicator(color = OceanBlue700)
                }
            } else {
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
                                modifier = Modifier
                                    .fillMaxWidth()
                                    .padding(32.dp),
                                contentAlignment = Alignment.Center
                            ) {
                                Text(
                                    text = "Aucun message. Dites bonjour !",
                                    style = MaterialTheme.typography.bodyMedium.copy(color = Gray400),
                                    textAlign = androidx.compose.ui.text.style.TextAlign.Center
                                )
                            }
                        }
                    }
                    items(messages, key = { it.id }) { msg ->
                        ConversationBubble(
                            message = msg,
                            isMine = msg.senderId == currentUserId
                        )
                    }
                }
            }

            // ── Input bar ─────────────────────────────────────────────────────
            ChatInputBar(
                draft = draft,
                onDraftChange = { vm.updateDraft(it) },
                onSend = { vm.sendMessage(conversationId) },
                isSending = isSending
            )
        }
    }
}

// ─── Conversation chat bubble ─────────────────────────────────────────────────

@Composable
private fun ConversationBubble(message: ConversationMessage, isMine: Boolean) {
    val bubbleBg  = if (isMine) OceanBlue700 else Gray100
    val textColor = if (isMine) White else Gray900
    val alignment = if (isMine) Alignment.End else Alignment.Start
    val bubbleShape = if (isMine) {
        RoundedCornerShape(
            topStart = 16.dp, topEnd = 4.dp,
            bottomStart = 16.dp, bottomEnd = 16.dp
        )
    } else {
        RoundedCornerShape(
            topStart = 4.dp, topEnd = 16.dp,
            bottomStart = 16.dp, bottomEnd = 16.dp
        )
    }

    Column(
        modifier = Modifier.fillMaxWidth(),
        horizontalAlignment = alignment
    ) {
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
