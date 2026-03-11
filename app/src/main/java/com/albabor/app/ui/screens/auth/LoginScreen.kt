package com.albabor.app.ui.screens.auth

import android.app.Activity
import androidx.activity.compose.rememberLauncherForActivityResult
import androidx.activity.result.contract.ActivityResultContracts.StartActivityForResult
import androidx.compose.animation.AnimatedVisibility
import androidx.compose.animation.fadeIn
import androidx.compose.animation.fadeOut
import androidx.compose.foundation.Image
import androidx.compose.foundation.background
import androidx.compose.foundation.clickable
import androidx.compose.foundation.interaction.MutableInteractionSource
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
import androidx.compose.foundation.layout.offset
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.statusBarsPadding
import androidx.compose.foundation.layout.width
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.text.KeyboardActions
import androidx.compose.foundation.text.KeyboardOptions
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Email
import androidx.compose.material.icons.filled.Lock
import androidx.compose.material.icons.filled.Visibility
import androidx.compose.material.icons.filled.VisibilityOff
import androidx.compose.material3.Button
import androidx.compose.material3.ButtonDefaults
import androidx.compose.material3.Checkbox
import androidx.compose.material3.CheckboxDefaults
import androidx.compose.material3.CircularProgressIndicator
import androidx.compose.material3.HorizontalDivider
import androidx.compose.material3.Icon
import androidx.compose.material3.IconButton
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.OutlinedButton
import androidx.compose.material3.OutlinedTextField
import androidx.compose.material3.OutlinedTextFieldDefaults
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
import androidx.compose.ui.draw.shadow
import androidx.compose.ui.focus.FocusDirection
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.platform.LocalFocusManager
import androidx.compose.ui.res.painterResource
import androidx.compose.ui.text.SpanStyle
import androidx.compose.ui.text.buildAnnotatedString
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.input.ImeAction
import androidx.compose.ui.text.input.KeyboardType
import androidx.compose.ui.text.input.PasswordVisualTransformation
import androidx.compose.ui.text.input.VisualTransformation
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.text.withStyle
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.lifecycle.compose.collectAsStateWithLifecycle
import androidx.lifecycle.viewmodel.compose.viewModel
import androidx.navigation.NavController
import com.albabor.app.R
import com.albabor.app.ui.navigation.Screen
import com.albabor.app.ui.theme.Gray200
import com.albabor.app.ui.theme.Gray400
import com.albabor.app.ui.theme.Gray500
import com.albabor.app.ui.theme.Gray700
import com.albabor.app.ui.theme.Gray900
import com.albabor.app.ui.theme.OceanBlue100
import com.albabor.app.ui.theme.OceanBlue300
import com.albabor.app.ui.theme.OceanBlue700
import com.albabor.app.ui.theme.OceanBlue900
import com.albabor.app.ui.theme.Teal500
import com.albabor.app.ui.theme.White
import com.albabor.app.viewmodel.AuthViewModel
import com.google.android.gms.auth.api.signin.GoogleSignIn
import com.google.android.gms.auth.api.signin.GoogleSignInOptions
import com.google.android.gms.common.api.ApiException
import kotlinx.coroutines.launch

private const val WEB_CLIENT_ID =
    "364258394169-6osv054m4a6ckphm8l78d7arrah8se2p.apps.googleusercontent.com"

// ─── Gradient helpers (shared across auth screens) ───────────────────────────

val oceanHeaderGradient = Brush.verticalGradient(
    colorStops = arrayOf(
        0.0f to OceanBlue900,
        0.55f to OceanBlue700,
        1.0f to Teal500
    )
)

val oceanButtonGradient = Brush.horizontalGradient(
    colors = listOf(OceanBlue900, OceanBlue700, Teal500)
)

// ─── Login Screen ─────────────────────────────────────────────────────────────

@Composable
fun LoginScreen(navController: NavController) {
    val context = LocalContext.current
    val vm: AuthViewModel = viewModel(factory = AuthViewModel.factory(context))
    val loginState by vm.loginState.collectAsStateWithLifecycle()
    val googleAuthState by vm.googleAuthState.collectAsStateWithLifecycle()

    val snackbarHostState = remember { SnackbarHostState() }
    val scope = rememberCoroutineScope()
    val focusManager = LocalFocusManager.current

    var email by remember { mutableStateOf("") }
    var password by remember { mutableStateOf("") }
    var passwordVisible by remember { mutableStateOf(false) }
    var rememberMe by remember { mutableStateOf(false) }

    val isLoading = loginState is AuthViewModel.LoginState.Loading ||
                    googleAuthState is AuthViewModel.GoogleAuthState.Loading

    // Google Sign-In client
    val googleSignInClient = remember {
        val gso = GoogleSignInOptions.Builder(GoogleSignInOptions.DEFAULT_SIGN_IN)
            .requestIdToken(WEB_CLIENT_ID)
            .requestEmail()
            .build()
        GoogleSignIn.getClient(context, gso)
    }

    // Launcher for Google sign-in intent
    val googleLauncher = rememberLauncherForActivityResult(StartActivityForResult()) { result ->
        if (result.resultCode == Activity.RESULT_OK) {
            try {
                val account = GoogleSignIn.getSignedInAccountFromIntent(result.data)
                    .getResult(ApiException::class.java)
                val idToken = account.idToken
                if (idToken != null) {
                    vm.loginWithGoogle(idToken)
                } else {
                    scope.launch {
                        snackbarHostState.showSnackbar("Impossible d'obtenir le jeton Google")
                    }
                }
            } catch (e: ApiException) {
                scope.launch {
                    snackbarHostState.showSnackbar("Connexion Google annulée (${e.statusCode})")
                }
            }
        }
    }

    LaunchedEffect(loginState) {
        when (val state = loginState) {
            is AuthViewModel.LoginState.Success -> {
                navController.navigate(Screen.Home.route) {
                    popUpTo(Screen.Login.route) { inclusive = true }
                }
            }
            is AuthViewModel.LoginState.Error -> {
                scope.launch { snackbarHostState.showSnackbar(state.message) }
                vm.resetLoginState()
            }
            else -> Unit
        }
    }

    LaunchedEffect(googleAuthState) {
        when (val state = googleAuthState) {
            is AuthViewModel.GoogleAuthState.Success -> {
                navController.navigate(Screen.Home.route) {
                    popUpTo(Screen.Login.route) { inclusive = true }
                }
            }
            is AuthViewModel.GoogleAuthState.Error -> {
                scope.launch { snackbarHostState.showSnackbar(state.message) }
                vm.resetGoogleAuthState()
            }
            else -> Unit
        }
    }

    Box(modifier = Modifier.fillMaxSize()) {

        Column(modifier = Modifier.fillMaxSize()) {

            // ── Ocean gradient header ─────────────────────────────────────────
            Box(
                modifier = Modifier
                    .fillMaxWidth()
                    .height(310.dp)
                    .background(brush = oceanHeaderGradient),
            ) {
                // Decorative circles in background
                Box(
                    modifier = Modifier
                        .size(200.dp)
                        .offset(x = (-60).dp, y = (-40).dp)
                        .background(
                            color = White.copy(alpha = 0.05f),
                            shape = CircleShape
                        )
                )
                Box(
                    modifier = Modifier
                        .size(140.dp)
                        .align(Alignment.TopEnd)
                        .offset(x = 40.dp, y = 20.dp)
                        .background(
                            color = White.copy(alpha = 0.05f),
                            shape = CircleShape
                        )
                )
                Box(
                    modifier = Modifier
                        .size(80.dp)
                        .align(Alignment.BottomStart)
                        .offset(x = 30.dp, y = 30.dp)
                        .background(
                            color = White.copy(alpha = 0.07f),
                            shape = CircleShape
                        )
                )

                Column(
                    horizontalAlignment = Alignment.CenterHorizontally,
                    verticalArrangement = Arrangement.Center,
                    modifier = Modifier
                        .fillMaxSize()
                        .statusBarsPadding()
                        .padding(bottom = 36.dp)
                ) {
                    // Clean white card logo
                    Box(
                        modifier = Modifier
                            .shadow(
                                elevation = 16.dp,
                                shape = RoundedCornerShape(28.dp),
                                ambientColor = OceanBlue900.copy(alpha = 0.4f),
                                spotColor = OceanBlue900.copy(alpha = 0.4f)
                            )
                            .clip(RoundedCornerShape(28.dp))
                            .background(White)
                            .size(120.dp)
                            .padding(12.dp),
                        contentAlignment = Alignment.Center
                    ) {
                        Image(
                            painter = painterResource(id = R.drawable.albabor_logo),
                            contentDescription = "AlBabor",
                            modifier = Modifier.fillMaxSize(),
                            contentScale = ContentScale.Fit
                        )
                    }

                    Spacer(modifier = Modifier.height(16.dp))

                    Text(
                        text = "La Mer, Votre Marché",
                        style = MaterialTheme.typography.bodyMedium.copy(
                            color = White.copy(alpha = 0.90f),
                            letterSpacing = 1.2.sp,
                            fontWeight = FontWeight.Medium
                        )
                    )
                }
            }

            // ── White card panel ──────────────────────────────────────────────
            Box(
                modifier = Modifier
                    .fillMaxSize()
                    .offset(y = (-28).dp)
                    .clip(RoundedCornerShape(topStart = 28.dp, topEnd = 28.dp))
                    .background(White)
            ) {
                Column(
                    modifier = Modifier
                        .fillMaxSize()
                        .verticalScroll(rememberScrollState())
                        .padding(horizontal = 24.dp)
                        .padding(top = 32.dp, bottom = 24.dp)
                        .navigationBarsPadding()
                        .imePadding()
                ) {
                    // Header text
                    Text(
                        text = "Bon retour 👋",
                        style = MaterialTheme.typography.headlineMedium.copy(
                            color = Gray900,
                            fontWeight = FontWeight.Bold
                        )
                    )
                    Spacer(modifier = Modifier.height(4.dp))
                    Text(
                        text = "Connectez-vous pour continuer",
                        style = MaterialTheme.typography.bodyMedium.copy(color = Gray500)
                    )

                    Spacer(modifier = Modifier.height(32.dp))

                    // Email field
                    AuthTextField(
                        value = email,
                        onValueChange = { email = it },
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
                            imeAction = ImeAction.Next
                        ),
                        keyboardActions = KeyboardActions(
                            onNext = { focusManager.moveFocus(FocusDirection.Down) }
                        )
                    )

                    Spacer(modifier = Modifier.height(16.dp))

                    // Password field
                    AuthTextField(
                        value = password,
                        onValueChange = { password = it },
                        label = "Mot de passe",
                        leadingIcon = {
                            Icon(
                                imageVector = Icons.Default.Lock,
                                contentDescription = null,
                                tint = OceanBlue700,
                                modifier = Modifier.size(20.dp)
                            )
                        },
                        visualTransformation = if (passwordVisible)
                            VisualTransformation.None
                        else
                            PasswordVisualTransformation(),
                        trailingIcon = {
                            IconButton(onClick = { passwordVisible = !passwordVisible }) {
                                Icon(
                                    imageVector = if (passwordVisible)
                                        Icons.Default.VisibilityOff
                                    else
                                        Icons.Default.Visibility,
                                    contentDescription = null,
                                    tint = Gray400
                                )
                            }
                        },
                        keyboardOptions = KeyboardOptions(
                            keyboardType = KeyboardType.Password,
                            imeAction = ImeAction.Done
                        ),
                        keyboardActions = KeyboardActions(
                            onDone = {
                                focusManager.clearFocus()
                                if (!isLoading) vm.login(email, password)
                            }
                        )
                    )

                    Spacer(modifier = Modifier.height(14.dp))

                    // Remember me + Forgot password row
                    Row(
                        modifier = Modifier.fillMaxWidth(),
                        horizontalArrangement = Arrangement.SpaceBetween,
                        verticalAlignment = Alignment.CenterVertically
                    ) {
                        Row(
                            verticalAlignment = Alignment.CenterVertically,
                            modifier = Modifier.clickable(
                                indication = null,
                                interactionSource = remember { MutableInteractionSource() }
                            ) { rememberMe = !rememberMe }
                        ) {
                            Checkbox(
                                checked = rememberMe,
                                onCheckedChange = { rememberMe = it },
                                colors = CheckboxDefaults.colors(
                                    checkedColor = OceanBlue700,
                                    uncheckedColor = Gray300
                                ),
                                modifier = Modifier.size(20.dp)
                            )
                            Spacer(modifier = Modifier.width(8.dp))
                            Text(
                                text = "Se souvenir de moi",
                                style = MaterialTheme.typography.bodySmall.copy(color = Gray700)
                            )
                        }

                        Text(
                            text = "Mot de passe oublié ?",
                            style = MaterialTheme.typography.bodySmall.copy(
                                color = OceanBlue700,
                                fontWeight = FontWeight.SemiBold
                            ),
                            modifier = Modifier.clickable(
                                indication = null,
                                interactionSource = remember { MutableInteractionSource() }
                            ) {
                                navController.navigate(Screen.ForgotPassword.route)
                            }
                        )
                    }

                    Spacer(modifier = Modifier.height(28.dp))

                    // Primary CTA — gradient button
                    GradientButton(
                        text = "Se connecter",
                        onClick = {
                            focusManager.clearFocus()
                            vm.login(email, password)
                        },
                        enabled = !isLoading && email.isNotBlank() && password.isNotBlank(),
                        isLoading = isLoading,
                        modifier = Modifier.fillMaxWidth()
                    )

                    Spacer(modifier = Modifier.height(24.dp))

                    // Divider "ou"
                    AuthDivider()

                    Spacer(modifier = Modifier.height(20.dp))

                    // Google sign-in button with real Google icon
                    OutlinedButton(
                        onClick = {
                            googleSignInClient.signOut().addOnCompleteListener {
                                googleLauncher.launch(googleSignInClient.signInIntent)
                            }
                        },
                        modifier = Modifier
                            .fillMaxWidth()
                            .height(52.dp),
                        shape = RoundedCornerShape(14.dp),
                        border = androidx.compose.foundation.BorderStroke(1.5.dp, Gray200),
                        colors = ButtonDefaults.outlinedButtonColors(contentColor = Gray700)
                    ) {
                        Icon(
                            painter = painterResource(id = R.drawable.ic_google),
                            contentDescription = "Google",
                            tint = Color.Unspecified,
                            modifier = Modifier.size(20.dp)
                        )
                        Spacer(modifier = Modifier.width(10.dp))
                        Text(
                            text = "Se connecter avec Google",
                            style = MaterialTheme.typography.labelLarge.copy(
                                color = Gray700,
                                fontWeight = FontWeight.Medium
                            )
                        )
                    }

                    Spacer(modifier = Modifier.height(32.dp))

                    // Sign-up link
                    Box(
                        modifier = Modifier.fillMaxWidth(),
                        contentAlignment = Alignment.Center
                    ) {
                        val signupText = buildAnnotatedString {
                            withStyle(SpanStyle(color = Gray500)) {
                                append("Pas de compte ? ")
                            }
                            withStyle(
                                SpanStyle(
                                    color = OceanBlue700,
                                    fontWeight = FontWeight.Bold
                                )
                            ) {
                                append("S'inscrire")
                            }
                        }
                        Text(
                            text = signupText,
                            style = MaterialTheme.typography.bodyMedium,
                            modifier = Modifier.clickable(
                                indication = null,
                                interactionSource = remember { MutableInteractionSource() }
                            ) {
                                navController.navigate(Screen.Register.route)
                            }
                        )
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
                        .clip(RoundedCornerShape(20.dp))
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

// ─── Shared composables ───────────────────────────────────────────────────────

@Composable
fun AuthTextField(
    value: String,
    onValueChange: (String) -> Unit,
    label: String,
    modifier: Modifier = Modifier,
    leadingIcon: @Composable (() -> Unit)? = null,
    trailingIcon: @Composable (() -> Unit)? = null,
    visualTransformation: VisualTransformation = VisualTransformation.None,
    keyboardOptions: KeyboardOptions = KeyboardOptions.Default,
    keyboardActions: KeyboardActions = KeyboardActions.Default,
    singleLine: Boolean = true,
    isError: Boolean = false,
    supportingText: @Composable (() -> Unit)? = null
) {
    OutlinedTextField(
        value = value,
        onValueChange = onValueChange,
        label = {
            Text(
                text = label,
                style = MaterialTheme.typography.bodyMedium
            )
        },
        leadingIcon = leadingIcon,
        trailingIcon = trailingIcon,
        visualTransformation = visualTransformation,
        keyboardOptions = keyboardOptions,
        keyboardActions = keyboardActions,
        singleLine = singleLine,
        isError = isError,
        supportingText = supportingText,
        shape = RoundedCornerShape(14.dp),
        colors = OutlinedTextFieldDefaults.colors(
            focusedBorderColor = OceanBlue700,
            unfocusedBorderColor = Gray200,
            focusedLabelColor = OceanBlue700,
            unfocusedLabelColor = Gray400,
            cursorColor = OceanBlue700,
            focusedLeadingIconColor = OceanBlue700,
            unfocusedLeadingIconColor = Gray400,
            errorBorderColor = MaterialTheme.colorScheme.error,
            focusedContainerColor = White,
            unfocusedContainerColor = Color(0xFFF7FAFD)
        ),
        modifier = modifier.fillMaxWidth()
    )
}

@Composable
fun GradientButton(
    text: String,
    onClick: () -> Unit,
    modifier: Modifier = Modifier,
    enabled: Boolean = true,
    isLoading: Boolean = false
) {
    val gradient = if (enabled)
        Brush.horizontalGradient(listOf(OceanBlue900, OceanBlue700, Teal500))
    else
        Brush.horizontalGradient(listOf(Gray300, Gray300))

    Box(
        modifier = modifier
            .height(54.dp)
            .clip(RoundedCornerShape(14.dp))
            .background(brush = gradient)
            .clickable(
                enabled = enabled && !isLoading,
                indication = null,
                interactionSource = remember { MutableInteractionSource() },
                onClick = onClick
            ),
        contentAlignment = Alignment.Center
    ) {
        if (isLoading) {
            CircularProgressIndicator(
                color = White,
                strokeWidth = 2.5.dp,
                modifier = Modifier.size(22.dp)
            )
        } else {
            Text(
                text = text,
                style = MaterialTheme.typography.labelLarge.copy(
                    color = White,
                    fontWeight = FontWeight.Bold,
                    letterSpacing = 0.5.sp
                )
            )
        }
    }
}

@Composable
fun AuthDivider() {
    Row(
        verticalAlignment = Alignment.CenterVertically,
        modifier = Modifier.fillMaxWidth()
    ) {
        HorizontalDivider(
            modifier = Modifier.weight(1f),
            color = Gray200,
            thickness = 1.dp
        )
        Text(
            text = "  ou  ",
            style = MaterialTheme.typography.bodySmall.copy(color = Gray400)
        )
        HorizontalDivider(
            modifier = Modifier.weight(1f),
            color = Gray200,
            thickness = 1.dp
        )
    }
}

// Needed for checkbox color reference
private val Gray300 = com.albabor.app.ui.theme.Gray300
