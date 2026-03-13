package com.albabor.app.ui.screens.conversations

import androidx.compose.animation.AnimatedVisibility
import androidx.compose.animation.fadeIn
import androidx.compose.animation.fadeOut
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
import androidx.compose.foundation.layout.navigationBarsPadding
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.width
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ArrowBack
import androidx.compose.material.icons.automirrored.filled.Chat
import androidx.compose.material.icons.filled.Refresh
import androidx.compose.material3.CircularProgressIndicator
import androidx.compose.material3.ExperimentalMaterial3Api
import androidx.compose.material3.HorizontalDivider
import androidx.compose.material3.Icon
import androidx.compose.material3.IconButton
import androidx.compose.material3.LinearProgressIndicator
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Scaffold
import androidx.compose.material3.Surface
import androidx.compose.material3.Text
import androidx.compose.material3.TopAppBar
import androidx.compose.material3.TopAppBarDefaults
import androidx.compose.runtime.Composable
import androidx.compose.runtime.getValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextOverflow
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.lifecycle.compose.collectAsStateWithLifecycle
import androidx.lifecycle.viewmodel.compose.viewModel
import androidx.navigation.NavController
import com.albabor.app.data.model.Conversation
import com.albabor.app.ui.navigation.Screen
import com.albabor.app.ui.theme.Gray100
import com.albabor.app.ui.theme.Gray400
import com.albabor.app.ui.theme.Gray50
import com.albabor.app.ui.theme.Gray500
import com.albabor.app.ui.theme.Gray700
import com.albabor.app.ui.theme.Gray900
import com.albabor.app.ui.theme.OceanBlue100
import com.albabor.app.ui.theme.OceanBlue700
import com.albabor.app.ui.theme.OceanBlue900
import com.albabor.app.ui.theme.Teal500
import com.albabor.app.ui.theme.White
import com.albabor.app.viewmodel.ConversationsViewModel

// ─── Avatar background colors (rotating palette) ──────────────────────────────

private val avatarColors = listOf(
    Color(0xFF2471A3), // OceanBlue700
    Color(0xFF17A2B8), // Teal500
    Color(0xFF8E44AD), // Purple
    Color(0xFF27AE60), // Green
    Color(0xFFE67E22), // Orange
    Color(0xFFE74C3C), // Red
    Color(0xFF2ECC71), // Emerald
    Color(0xFF1ABC9C)  // Turquoise
)

private fun avatarColorFor(name: String): Color {
    val idx = (name.firstOrNull()?.code ?: 0) % avatarColors.size
    return avatarColors[idx]
}

// ─── ConversationsScreen ──────────────────────────────────────────────────────

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun ConversationsScreen(
    navController: NavController,
    showBackButton: Boolean = true
) {
    val vm: ConversationsViewModel = viewModel()
    val conversations by vm.conversations.collectAsStateWithLifecycle()
    val isLoading by vm.isLoading.collectAsStateWithLifecycle()

    Scaffold(
        topBar = {
            TopAppBar(
                title = {
                    Text(
                        text = "Messages",
                        style = MaterialTheme.typography.titleLarge.copy(
                            fontWeight = FontWeight.Bold,
                            color = Gray900
                        )
                    )
                },
                navigationIcon = {
                    if (showBackButton) {
                        IconButton(onClick = { navController.popBackStack() }) {
                            Icon(
                                imageVector = Icons.AutoMirrored.Filled.ArrowBack,
                                contentDescription = "Retour",
                                tint = Gray700
                            )
                        }
                    }
                },
                actions = {
                    IconButton(onClick = { vm.loadConversations() }) {
                        Icon(
                            imageVector = Icons.Filled.Refresh,
                            contentDescription = "Actualiser",
                            tint = OceanBlue700
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
        Box(
            modifier = Modifier
                .fillMaxSize()
                .padding(innerPadding)
        ) {
            // Loading indicator at top when refreshing existing list
            AnimatedVisibility(
                visible = isLoading && conversations.isNotEmpty(),
                enter = fadeIn(),
                exit = fadeOut(),
                modifier = Modifier.align(Alignment.TopCenter)
            ) {
                LinearProgressIndicator(
                    modifier = Modifier.fillMaxWidth(),
                    color = OceanBlue700,
                    trackColor = OceanBlue100
                )
            }

            if (isLoading && conversations.isEmpty()) {
                CircularProgressIndicator(
                    color = OceanBlue700,
                    modifier = Modifier.align(Alignment.Center)
                )
            } else if (conversations.isEmpty()) {
                ConversationsEmptyState()
            } else {
                LazyColumn(
                    modifier = Modifier
                        .fillMaxSize()
                        .background(White)
                        .navigationBarsPadding()
                ) {
                    items(conversations, key = { it.id }) { conversation ->
                        ConversationRow(
                            conversation = conversation,
                            onClick = {
                                navController.navigate(
                                    Screen.ConversationDetail.route(conversation.id)
                                )
                            }
                        )
                        HorizontalDivider(
                            color = Gray100,
                            thickness = 1.dp,
                            modifier = Modifier.padding(start = 76.dp)
                        )
                    }
                    item { Spacer(modifier = Modifier.height(24.dp)) }
                }
            }
        }
    }
}

// ─── Conversation row ─────────────────────────────────────────────────────────

@Composable
private fun ConversationRow(conversation: Conversation, onClick: () -> Unit) {
    val otherUser = conversation.otherUser
    val otherName = otherUser?.name ?: "Inconnu"
    val initials  = otherName.split(" ")
        .mapNotNull { it.firstOrNull()?.uppercaseChar() }
        .take(2)
        .joinToString("")
    val bgColor = avatarColorFor(otherName)

    val hasUnread = conversation.unreadCount > 0

    Row(
        modifier = Modifier
            .fillMaxWidth()
            .background(if (hasUnread) OceanBlue100.copy(alpha = 0.25f) else White)
            .clickable(onClick = onClick)
            .padding(horizontal = 16.dp, vertical = 14.dp),
        verticalAlignment = Alignment.CenterVertically
    ) {
        // ── Avatar ───────────────────────────────────────────────────────────
        Box(
            modifier = Modifier
                .size(52.dp)
                .clip(CircleShape)
                .background(bgColor),
            contentAlignment = Alignment.Center
        ) {
            Text(
                text = initials,
                style = MaterialTheme.typography.titleSmall.copy(
                    color = White,
                    fontWeight = FontWeight.Bold
                )
            )
        }

        Spacer(modifier = Modifier.width(12.dp))

        // ── Content ──────────────────────────────────────────────────────────
        Column(modifier = Modifier.weight(1f)) {
            // Name + timestamp row
            Row(
                modifier = Modifier.fillMaxWidth(),
                horizontalArrangement = Arrangement.SpaceBetween,
                verticalAlignment = Alignment.CenterVertically
            ) {
                Text(
                    text = otherName,
                    style = MaterialTheme.typography.titleSmall.copy(
                        fontWeight = if (hasUnread) FontWeight.Bold else FontWeight.SemiBold,
                        color = Gray900
                    ),
                    maxLines = 1,
                    overflow = TextOverflow.Ellipsis,
                    modifier = Modifier.weight(1f)
                )
                Spacer(modifier = Modifier.width(8.dp))
                Text(
                    text = formatConversationTime(conversation.lastMessage?.createdAt ?: conversation.createdAt),
                    style = MaterialTheme.typography.labelSmall.copy(
                        color = if (hasUnread) OceanBlue700 else Gray400,
                        fontWeight = if (hasUnread) FontWeight.SemiBold else FontWeight.Normal,
                        fontSize = 11.sp
                    )
                )
            }

            Spacer(modifier = Modifier.height(3.dp))

            // Listing title
            conversation.listing?.let { listing ->
                Text(
                    text = listing.title,
                    style = MaterialTheme.typography.bodySmall.copy(
                        color = OceanBlue700,
                        fontWeight = FontWeight.Medium
                    ),
                    maxLines = 1,
                    overflow = TextOverflow.Ellipsis
                )
                Spacer(modifier = Modifier.height(2.dp))
            }

            // Last message + unread badge row
            Row(
                modifier = Modifier.fillMaxWidth(),
                horizontalArrangement = Arrangement.SpaceBetween,
                verticalAlignment = Alignment.CenterVertically
            ) {
                Text(
                    text = conversation.lastMessage?.body ?: "Aucun message",
                    style = MaterialTheme.typography.bodySmall.copy(
                        color = if (hasUnread) Gray700 else Gray400,
                        fontWeight = if (hasUnread) FontWeight.Medium else FontWeight.Normal
                    ),
                    maxLines = 1,
                    overflow = TextOverflow.Ellipsis,
                    modifier = Modifier.weight(1f)
                )

                if (hasUnread) {
                    Spacer(modifier = Modifier.width(8.dp))
                    Box(
                        modifier = Modifier
                            .size(20.dp)
                            .clip(CircleShape)
                            .background(OceanBlue700),
                        contentAlignment = Alignment.Center
                    ) {
                        Text(
                            text = if (conversation.unreadCount > 99) "99+"
                                   else conversation.unreadCount.toString(),
                            style = MaterialTheme.typography.labelSmall.copy(
                                color = White,
                                fontWeight = FontWeight.Bold,
                                fontSize = 10.sp
                            )
                        )
                    }
                }
            }
        }
    }
}

// ─── Empty state ──────────────────────────────────────────────────────────────

@Composable
private fun ConversationsEmptyState() {
    Column(
        modifier = Modifier
            .fillMaxSize()
            .padding(32.dp),
        horizontalAlignment = Alignment.CenterHorizontally,
        verticalArrangement = Arrangement.Center
    ) {
        Box(
            modifier = Modifier
                .size(88.dp)
                .clip(CircleShape)
                .background(OceanBlue100),
            contentAlignment = Alignment.Center
        ) {
            Icon(
                imageVector = Icons.AutoMirrored.Filled.Chat,
                contentDescription = null,
                tint = OceanBlue700,
                modifier = Modifier.size(44.dp)
            )
        }
        Spacer(modifier = Modifier.height(24.dp))
        Text(
            text = "Aucun message",
            style = MaterialTheme.typography.titleMedium.copy(
                color = Gray900,
                fontWeight = FontWeight.Bold
            )
        )
        Spacer(modifier = Modifier.height(8.dp))
        Text(
            text = "Vos conversations avec les acheteurs et vendeurs apparaitront ici.",
            style = MaterialTheme.typography.bodyMedium.copy(color = Gray500),
            textAlign = androidx.compose.ui.text.style.TextAlign.Center
        )
    }
}

// ─── Time formatter ───────────────────────────────────────────────────────────

private fun formatConversationTime(createdAt: String): String {
    // Simplistic display: show date or time portion
    // Real implementation would use DateTimeFormatter + relative logic
    return if (createdAt.length >= 16) createdAt.substring(11, 16) else createdAt.take(10)
}
