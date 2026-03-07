package com.albabor.app.ui.screens.auth

import androidx.compose.animation.AnimatedContent
import androidx.compose.animation.AnimatedVisibility
import androidx.compose.animation.fadeIn
import androidx.compose.animation.fadeOut
import androidx.compose.animation.slideInVertically
import androidx.compose.animation.slideOutVertically
import androidx.compose.animation.togetherWith
import androidx.compose.foundation.background
import androidx.compose.foundation.clickable
import androidx.compose.foundation.interaction.MutableInteractionSource
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.imePadding
import androidx.compose.foundation.layout.navigationBarsPadding
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.statusBarsPadding
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.text.KeyboardActions
import androidx.compose.foundation.text.KeyboardOptions
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.ArrowBack
import androidx.compose.material.icons.filled.CheckCircle
import androidx.compose.material.icons.filled.Email
import androidx.compose.material3.CircularProgressIndicator
import androidx.compose.material3.Icon
import androidx.compose.material3.IconButton
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Snackbar
import androidx.compose.material3.SnackbarHost
import androidx.compose.material3.SnackbarHostState
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.rememberCoroutineScope
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.platform.LocalFocusManager
import androidx.compose.ui.text.SpanStyle
import androidx.compose.ui.text.buildAnnotatedString
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.input.ImeAction
import androidx.compose.ui.text.input.KeyboardType
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.text.withStyle
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.lifecycle.compose.collectAsStateWithLifecycle
import androidx.lifecycle.viewmodel.compose.viewModel
import androidx.navigation.NavController
import com.albabor.app.ui.navigation.Screen
import com.albabor.app.ui.theme.Gray200
import com.albabor.app.ui.theme.Gray400
import com.albabor.app.ui.theme.Gray500
import com.albabor.app.ui.theme.Gray700
import com.albabor.app.ui.theme.Gray900
import com.albabor.app.ui.theme.OceanBlue100
import com.albabor.app.ui.theme.OceanBlue700
import com.albabor.app.ui.theme.OceanBlue900
import com.albabor.app.ui.theme.Success500
import com.albabor.app.ui.theme.Teal500
import com.albabor.app.ui.theme.White
import com.albabor.app.viewmodel.AuthViewModel
import kotlinx.coroutines.launch

@Composable
fun ForgotPasswordScreen(navController: NavController) {
    val context = LocalContext.current
    val vm: AuthViewModel = viewModel(factory = AuthViewModel.factory(context))
    val forgotState by vm.forgotState.collectAsStateWithLifecycle()

    val snackbarHostState = remember { SnackbarHostState() }
    val scope = rememberCoroutineScope()
    val focusManager = LocalFocusManager.current

    var email by remember { mutableStateOf("") }

    val isLoading = forgotState is AuthViewModel.ForgotState.Loading
    val isSuccess = forgotState is AuthViewModel.ForgotState.Success

    LaunchedEffect(forgotState) {
        when (val state = forgotState) {
            is AuthViewModel.ForgotState.Error -> {
                scope.launch { snackbarHostState.showSnackbar(state.message) }
                vm.resetForgotState()
            }
            else -> Unit
        }
    }

    Box(modifier = Modifier.fillMaxSize()) {

        Column(modifier = Modifier.fillMaxSize()) {

            // ── Shorter gradient header ───────────────────────────────────────
            Box(
                modifier = Modifier
                    .fillMaxWidth()
                    .height(200.dp)
                    .background(brush = oceanHeaderGradient)
            ) {
                // Back button
                IconButton(
                    onClick = { navController.navigateUp() },
                    modifier = Modifier
                        .statusBarsPadding()
                        .padding(8.dp)
                        .align(Alignment.TopStart)
                ) {
                    Box(
                        modifier = Modifier
                            .size(38.dp)
                            .clip(CircleShape)
                            .background(White.copy(alpha = 0.18f)),
                        contentAlignment = Alignment.Center
                    ) {
                        Icon(
                            imageVector = Icons.Default.ArrowBack,
                            contentDescription = "Retour",
                            tint = White,
                            modifier = Modifier.size(20.dp)
                        )
                    }
                }

                Column(
                    horizontalAlignment = Alignment.CenterHorizontally,
                    modifier = Modifier
                        .align(Alignment.BottomCenter)
                        .padding(bottom = 28.dp)
                ) {
                    Box(
                        modifier = Modifier
                            .size(56.dp)
                            .clip(CircleShape)
                            .background(White.copy(alpha = 0.18f)),
                        contentAlignment = Alignment.Center
                    ) {
                        Icon(
                            imageVector = Icons.Default.Email,
                            contentDescription = null,
                            tint = White,
                            modifier = Modifier.size(26.dp)
                        )
                    }
                    Spacer(modifier = Modifier.height(10.dp))
                    Text(
                        text = "Mot de passe oublie ?",
                        style = MaterialTheme.typography.titleLarge.copy(
                            color = White,
                            fontWeight = FontWeight.Bold
                        )
                    )
                }
            }

            // ── White card ────────────────────────────────────────────────────
            Box(
                modifier = Modifier
                    .fillMaxSize()
                    .clip(RoundedCornerShape(topStart = 32.dp, topEnd = 32.dp))
                    .background(White)
            ) {
                Column(
                    modifier = Modifier
                        .fillMaxSize()
                        .verticalScroll(rememberScrollState())
                        .padding(horizontal = 24.dp)
                        .padding(top = 36.dp, bottom = 24.dp)
                        .navigationBarsPadding()
                        .imePadding()
                ) {
                    AnimatedContent(
                        targetState = isSuccess,
                        transitionSpec = {
                            (slideInVertically { it / 2 } + fadeIn()) togetherWith
                                (slideOutVertically { -it / 2 } + fadeOut())
                        },
                        label = "forgot_content"
                    ) { success ->
                        if (success) {
                            SuccessPanel(
                                email = email,
                                onBackToLogin = {
                                    navController.navigate(Screen.Login.route) {
                                        popUpTo(Screen.ForgotPassword.route) { inclusive = true }
                                    }
                                }
                            )
                        } else {
                            RequestPanel(
                                email = email,
                                onEmailChange = { email = it },
                                isLoading = isLoading,
                                onSend = {
                                    focusManager.clearFocus()
                                    vm.forgotPassword(email)
                                },
                                onBackToLogin = {
                                    navController.navigate(Screen.Login.route) {
                                        popUpTo(Screen.ForgotPassword.route) { inclusive = true }
                                    }
                                }
                            )
                        }
                    }
                }
            }
        }

        // Loading overlay
        AnimatedVisibility(
            visible = isLoading,
            enter = fadeIn(),
            exit = fadeOut(),
            modifier = Modifier.fillMaxSize()
        ) {
            Box(
                modifier = Modifier
                    .fillMaxSize()
                    .background(Color.Black.copy(alpha = 0.35f))
                    .clickable(enabled = false) {},
                contentAlignment = Alignment.Center
            ) {
                Box(
                    modifier = Modifier
                        .size(80.dp)
                        .clip(RoundedCornerShape(16.dp))
                        .background(White),
                    contentAlignment = Alignment.Center
                ) {
                    CircularProgressIndicator(
                        color = OceanBlue700,
                        strokeWidth = 3.dp,
                        modifier = Modifier.size(36.dp)
                    )
                }
            }
        }

        // Snackbar
        SnackbarHost(
            hostState = snackbarHostState,
            modifier = Modifier
                .align(Alignment.BottomCenter)
                .navigationBarsPadding()
                .padding(16.dp)
        ) { data ->
            Snackbar(
                snackbarData = data,
                containerColor = Gray900,
                contentColor = White,
                shape = RoundedCornerShape(12.dp)
            )
        }
    }
}

// ─── Request panel (email form) ───────────────────────────────────────────────

@Composable
private fun RequestPanel(
    email: String,
    onEmailChange: (String) -> Unit,
    isLoading: Boolean,
    onSend: () -> Unit,
    onBackToLogin: () -> Unit
) {
    val focusManager = LocalFocusManager.current

    Column {
        Text(
            text = "Reinitialiser votre mot de passe",
            style = MaterialTheme.typography.headlineSmall.copy(
                color = Gray900,
                fontWeight = FontWeight.Bold
            )
        )
        Spacer(modifier = Modifier.height(10.dp))
        Text(
            text = "Entrez votre adresse email et nous vous enverrons un lien pour reinitialiser votre mot de passe.",
            style = MaterialTheme.typography.bodyMedium.copy(
                color = Gray500,
                lineHeight = 22.sp
            )
        )

        Spacer(modifier = Modifier.height(32.dp))

        AuthTextField(
            value = email,
            onValueChange = onEmailChange,
            label = "Adresse email",
            leadingIcon = {
                Icon(
                    imageVector = Icons.Default.Email,
                    contentDescription = null,
                    tint = OceanBlue700,
                    modifier = Modifier.size(20.dp)
                )
            },
            keyboardOptions = KeyboardOptions(
                keyboardType = KeyboardType.Email,
                imeAction = ImeAction.Send
            ),
            keyboardActions = KeyboardActions(
                onSend = {
                    focusManager.clearFocus()
                    if (email.isNotBlank()) onSend()
                }
            )
        )

        Spacer(modifier = Modifier.height(28.dp))

        GradientButton(
            text = "Envoyer le lien de reinitialisation",
            onClick = onSend,
            enabled = email.isNotBlank() && !isLoading,
            isLoading = isLoading,
            modifier = Modifier.fillMaxWidth()
        )

        Spacer(modifier = Modifier.height(32.dp))

        Box(
            modifier = Modifier.fillMaxWidth(),
            contentAlignment = Alignment.Center
        ) {
            val text = buildAnnotatedString {
                withStyle(SpanStyle(color = Gray500)) {
                    append("Vous vous souvenez? ")
                }
                withStyle(SpanStyle(color = OceanBlue700, fontWeight = FontWeight.Bold)) {
                    append("Se connecter")
                }
            }
            Text(
                text = text,
                style = MaterialTheme.typography.bodyMedium,
                modifier = Modifier.clickable(
                    indication = null,
                    interactionSource = remember { MutableInteractionSource() },
                    onClick = onBackToLogin
                )
            )
        }
    }
}

// ─── Success panel ────────────────────────────────────────────────────────────

@Composable
private fun SuccessPanel(
    email: String,
    onBackToLogin: () -> Unit
) {
    Column(
        horizontalAlignment = Alignment.CenterHorizontally,
        modifier = Modifier.fillMaxWidth()
    ) {
        Spacer(modifier = Modifier.height(16.dp))

        // Success icon
        Box(
            modifier = Modifier
                .size(96.dp)
                .clip(CircleShape)
                .background(OceanBlue100),
            contentAlignment = Alignment.Center
        ) {
            Icon(
                imageVector = Icons.Default.CheckCircle,
                contentDescription = null,
                tint = Success500,
                modifier = Modifier.size(52.dp)
            )
        }

        Spacer(modifier = Modifier.height(24.dp))

        Text(
            text = "Email envoye !",
            style = MaterialTheme.typography.headlineSmall.copy(
                color = Gray900,
                fontWeight = FontWeight.Bold
            ),
            textAlign = TextAlign.Center
        )

        Spacer(modifier = Modifier.height(12.dp))

        Text(
            text = "Nous avons envoye un lien de reinitialisation a :",
            style = MaterialTheme.typography.bodyMedium.copy(
                color = Gray500,
                textAlign = TextAlign.Center
            ),
            textAlign = TextAlign.Center
        )

        Spacer(modifier = Modifier.height(8.dp))

        Text(
            text = email,
            style = MaterialTheme.typography.bodyMedium.copy(
                color = OceanBlue700,
                fontWeight = FontWeight.SemiBold,
                textAlign = TextAlign.Center
            ),
            textAlign = TextAlign.Center
        )

        Spacer(modifier = Modifier.height(12.dp))

        Text(
            text = "Verifiez votre boite de reception et vos spams. Le lien expirera dans 60 minutes.",
            style = MaterialTheme.typography.bodySmall.copy(
                color = Gray400,
                textAlign = TextAlign.Center,
                lineHeight = 20.sp
            ),
            textAlign = TextAlign.Center,
            modifier = Modifier.padding(horizontal = 8.dp)
        )

        Spacer(modifier = Modifier.height(36.dp))

        GradientButton(
            text = "Retour a la connexion",
            onClick = onBackToLogin,
            modifier = Modifier.fillMaxWidth()
        )

        Spacer(modifier = Modifier.height(20.dp))

        // Instructional tip card
        Box(
            modifier = Modifier
                .fillMaxWidth()
                .clip(RoundedCornerShape(12.dp))
                .background(OceanBlue100)
                .padding(16.dp)
        ) {
            Column {
                Text(
                    text = "Conseil",
                    style = MaterialTheme.typography.labelLarge.copy(
                        color = OceanBlue900,
                        fontWeight = FontWeight.Bold
                    )
                )
                Spacer(modifier = Modifier.height(4.dp))
                Text(
                    text = "Si vous ne recevez pas l'email dans quelques minutes, verifiez votre dossier spam ou contactez notre support.",
                    style = MaterialTheme.typography.bodySmall.copy(
                        color = OceanBlue700,
                        lineHeight = 18.sp
                    )
                )
            }
        }
    }
}
