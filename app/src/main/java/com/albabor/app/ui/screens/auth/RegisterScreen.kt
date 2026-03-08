package com.albabor.app.ui.screens.auth

import androidx.compose.animation.AnimatedVisibility
import androidx.compose.animation.fadeIn
import androidx.compose.animation.fadeOut
import androidx.compose.foundation.background
import androidx.compose.foundation.Image
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
import androidx.compose.material.icons.filled.Person
import androidx.compose.material.icons.filled.Phone
import androidx.compose.material.icons.filled.Visibility
import androidx.compose.material.icons.filled.VisibilityOff
import androidx.compose.material3.ButtonDefaults
import androidx.compose.material3.Checkbox
import androidx.compose.material3.CheckboxDefaults
import androidx.compose.material3.CircularProgressIndicator
import androidx.compose.material3.Icon
import androidx.compose.material3.IconButton
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.OutlinedButton
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
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.res.painterResource
import com.albabor.app.R
import com.albabor.app.ui.theme.OceanBlue900
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.platform.LocalFocusManager
import androidx.compose.ui.text.SpanStyle
import androidx.compose.ui.text.buildAnnotatedString
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.input.ImeAction
import androidx.compose.ui.text.input.KeyboardType
import androidx.compose.ui.text.input.PasswordVisualTransformation
import androidx.compose.ui.text.input.VisualTransformation
import androidx.compose.ui.text.withStyle
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.lifecycle.compose.collectAsStateWithLifecycle
import androidx.lifecycle.viewmodel.compose.viewModel
import androidx.navigation.NavController
import com.albabor.app.ui.navigation.Screen
import com.albabor.app.ui.theme.Error500
import com.albabor.app.ui.theme.Gray200
import com.albabor.app.ui.theme.Gray300
import com.albabor.app.ui.theme.Gray400
import com.albabor.app.ui.theme.Gray500
import com.albabor.app.ui.theme.Gray700
import com.albabor.app.ui.theme.Gray900
import com.albabor.app.ui.theme.OceanBlue700
import com.albabor.app.ui.theme.White
import com.albabor.app.viewmodel.AuthViewModel
import kotlinx.coroutines.launch

@Composable
fun RegisterScreen(navController: NavController) {
    val context = LocalContext.current
    val vm: AuthViewModel = viewModel(factory = AuthViewModel.factory(context))
    val registerState by vm.registerState.collectAsStateWithLifecycle()

    val snackbarHostState = remember { SnackbarHostState() }
    val scope = rememberCoroutineScope()
    val focusManager = LocalFocusManager.current

    // Form fields
    var firstName by remember { mutableStateOf("") }
    var lastName by remember { mutableStateOf("") }
    var email by remember { mutableStateOf("") }
    var phone by remember { mutableStateOf("") }
    var password by remember { mutableStateOf("") }
    var confirmPassword by remember { mutableStateOf("") }
    var passwordVisible by remember { mutableStateOf(false) }
    var confirmPasswordVisible by remember { mutableStateOf(false) }
    var acceptedTerms by remember { mutableStateOf(false) }

    // Inline validation flags
    var showPasswordMismatch by remember { mutableStateOf(false) }

    val isLoading = registerState is AuthViewModel.RegisterState.Loading

    LaunchedEffect(registerState) {
        when (val state = registerState) {
            is AuthViewModel.RegisterState.Success -> {
                navController.navigate(Screen.Home.route) {
                    popUpTo(Screen.Register.route) { inclusive = true }
                }
            }
            is AuthViewModel.RegisterState.Error -> {
                scope.launch { snackbarHostState.showSnackbar(state.message) }
                vm.resetRegisterState()
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
                    .height(260.dp)
                    .background(brush = oceanHeaderGradient),
            ) {
                // Decorative circles
                Box(
                    modifier = Modifier
                        .size(200.dp)
                        .offset(x = (-60).dp, y = (-40).dp)
                        .background(color = White.copy(alpha = 0.05f), shape = CircleShape)
                )
                Box(
                    modifier = Modifier
                        .size(140.dp)
                        .align(Alignment.TopEnd)
                        .offset(x = 40.dp, y = 20.dp)
                        .background(color = White.copy(alpha = 0.05f), shape = CircleShape)
                )
                Box(
                    modifier = Modifier
                        .size(80.dp)
                        .align(Alignment.BottomStart)
                        .offset(x = 30.dp, y = 30.dp)
                        .background(color = White.copy(alpha = 0.07f), shape = CircleShape)
                )

                Column(
                    horizontalAlignment = Alignment.CenterHorizontally,
                    verticalArrangement = Arrangement.Center,
                    modifier = Modifier
                        .fillMaxSize()
                        .statusBarsPadding()
                        .padding(bottom = 36.dp)
                ) {
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
                            .size(100.dp)
                            .padding(10.dp),
                        contentAlignment = Alignment.Center
                    ) {
                        Image(
                            painter = painterResource(id = R.drawable.albabor_logo),
                            contentDescription = "AlBabor",
                            modifier = Modifier.fillMaxSize(),
                            contentScale = ContentScale.Fit
                        )
                    }
                    Spacer(modifier = Modifier.height(14.dp))
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
                    Text(
                        text = "Creer mon compte",
                        style = MaterialTheme.typography.headlineSmall.copy(
                            color = Gray900,
                            fontWeight = FontWeight.Bold
                        )
                    )
                    Spacer(modifier = Modifier.height(6.dp))
                    Text(
                        text = "Achetez et vendez en toute confiance",
                        style = MaterialTheme.typography.bodyMedium.copy(color = Gray500)
                    )

                    Spacer(modifier = Modifier.height(24.dp))

                    // First name + Last name in a row
                    Row(
                        modifier = Modifier.fillMaxWidth(),
                        horizontalArrangement = Arrangement.spacedBy(12.dp)
                    ) {
                        AuthTextField(
                            value = firstName,
                            onValueChange = { firstName = it },
                            label = "Prenom",
                            leadingIcon = {
                                Icon(
                                    imageVector = Icons.Default.Person,
                                    contentDescription = null,
                                    tint = OceanBlue700,
                                    modifier = Modifier.size(20.dp)
                                )
                            },
                            keyboardOptions = KeyboardOptions(
                                keyboardType = KeyboardType.Text,
                                imeAction = ImeAction.Next
                            ),
                            keyboardActions = KeyboardActions(
                                onNext = { focusManager.moveFocus(FocusDirection.Right) }
                            ),
                            modifier = Modifier.weight(1f)
                        )
                        AuthTextField(
                            value = lastName,
                            onValueChange = { lastName = it },
                            label = "Nom de famille",
                            keyboardOptions = KeyboardOptions(
                                keyboardType = KeyboardType.Text,
                                imeAction = ImeAction.Next
                            ),
                            keyboardActions = KeyboardActions(
                                onNext = { focusManager.moveFocus(FocusDirection.Down) }
                            ),
                            modifier = Modifier.weight(1f)
                        )
                    }

                    Spacer(modifier = Modifier.height(14.dp))

                    // Email
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

                    Spacer(modifier = Modifier.height(14.dp))

                    // Phone
                    AuthTextField(
                        value = phone,
                        onValueChange = { phone = it },
                        label = "Numero de telephone",
                        leadingIcon = {
                            Icon(
                                imageVector = Icons.Default.Phone,
                                contentDescription = null,
                                tint = OceanBlue700,
                                modifier = Modifier.size(20.dp)
                            )
                        },
                        keyboardOptions = KeyboardOptions(
                            keyboardType = KeyboardType.Phone,
                            imeAction = ImeAction.Next
                        ),
                        keyboardActions = KeyboardActions(
                            onNext = { focusManager.moveFocus(FocusDirection.Down) }
                        )
                    )

                    Spacer(modifier = Modifier.height(14.dp))

                    // Password
                    AuthTextField(
                        value = password,
                        onValueChange = {
                            password = it
                            if (showPasswordMismatch) showPasswordMismatch = false
                        },
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
                            imeAction = ImeAction.Next
                        ),
                        keyboardActions = KeyboardActions(
                            onNext = { focusManager.moveFocus(FocusDirection.Down) }
                        ),
                        supportingText = if (password.isNotEmpty() && password.length < 8) {
                            { Text("Minimum 8 caracteres", color = Error500) }
                        } else null
                    )

                    Spacer(modifier = Modifier.height(14.dp))

                    // Confirm password
                    AuthTextField(
                        value = confirmPassword,
                        onValueChange = {
                            confirmPassword = it
                            if (showPasswordMismatch) showPasswordMismatch = false
                        },
                        label = "Confirmer le mot de passe",
                        leadingIcon = {
                            Icon(
                                imageVector = Icons.Default.Lock,
                                contentDescription = null,
                                tint = OceanBlue700,
                                modifier = Modifier.size(20.dp)
                            )
                        },
                        visualTransformation = if (confirmPasswordVisible)
                            VisualTransformation.None
                        else
                            PasswordVisualTransformation(),
                        trailingIcon = {
                            IconButton(onClick = { confirmPasswordVisible = !confirmPasswordVisible }) {
                                Icon(
                                    imageVector = if (confirmPasswordVisible)
                                        Icons.Default.VisibilityOff
                                    else
                                        Icons.Default.Visibility,
                                    contentDescription = null,
                                    tint = Gray400
                                )
                            }
                        },
                        isError = showPasswordMismatch,
                        supportingText = if (showPasswordMismatch) {
                            { Text("Les mots de passe ne correspondent pas", color = Error500) }
                        } else null,
                        keyboardOptions = KeyboardOptions(
                            keyboardType = KeyboardType.Password,
                            imeAction = ImeAction.Done
                        ),
                        keyboardActions = KeyboardActions(
                            onDone = { focusManager.clearFocus() }
                        )
                    )

                    Spacer(modifier = Modifier.height(16.dp))

                    // Terms checkbox
                    Row(
                        verticalAlignment = Alignment.CenterVertically,
                        modifier = Modifier
                            .fillMaxWidth()
                            .clickable(
                                indication = null,
                                interactionSource = remember { MutableInteractionSource() }
                            ) { acceptedTerms = !acceptedTerms }
                    ) {
                        Checkbox(
                            checked = acceptedTerms,
                            onCheckedChange = { acceptedTerms = it },
                            colors = CheckboxDefaults.colors(
                                checkedColor = OceanBlue700,
                                uncheckedColor = Gray300
                            ),
                            modifier = Modifier.size(20.dp)
                        )
                        Spacer(modifier = Modifier.width(10.dp))
                        val termsText = buildAnnotatedString {
                            withStyle(SpanStyle(color = Gray700)) {
                                append("J'accepte les ")
                            }
                            withStyle(SpanStyle(color = OceanBlue700, fontWeight = FontWeight.SemiBold)) {
                                append("conditions d'utilisation")
                            }
                            withStyle(SpanStyle(color = Gray700)) {
                                append(" et la ")
                            }
                            withStyle(SpanStyle(color = OceanBlue700, fontWeight = FontWeight.SemiBold)) {
                                append("politique de confidentialite")
                            }
                        }
                        Text(
                            text = termsText,
                            style = MaterialTheme.typography.bodySmall
                        )
                    }

                    Spacer(modifier = Modifier.height(24.dp))

                    // Primary CTA
                    val canSubmit = firstName.isNotBlank()
                        && lastName.isNotBlank()
                        && email.isNotBlank()
                        && phone.isNotBlank()
                        && password.length >= 8
                        && confirmPassword.isNotBlank()
                        && acceptedTerms
                        && !isLoading

                    GradientButton(
                        text = "Creer mon compte",
                        onClick = {
                            focusManager.clearFocus()
                            if (password != confirmPassword) {
                                showPasswordMismatch = true
                                return@GradientButton
                            }
                            vm.register(firstName, lastName, email, phone, password, confirmPassword)
                        },
                        enabled = canSubmit,
                        isLoading = isLoading,
                        modifier = Modifier.fillMaxWidth()
                    )

                    Spacer(modifier = Modifier.height(20.dp))

                    AuthDivider()

                    Spacer(modifier = Modifier.height(20.dp))

                    // Google sign-up
                    OutlinedButton(
                        onClick = { /* Google OAuth integration */ },
                        modifier = Modifier
                            .fillMaxWidth()
                            .height(50.dp),
                        shape = RoundedCornerShape(12.dp),
                        border = androidx.compose.foundation.BorderStroke(1.5.dp, Gray200),
                        colors = ButtonDefaults.outlinedButtonColors(contentColor = Gray700)
                    ) {
                        Text(
                            text = "G",
                            fontWeight = FontWeight.Bold,
                            fontSize = 18.sp,
                            color = Color(0xFF4285F4)
                        )
                        Spacer(modifier = Modifier.width(10.dp))
                        Text(
                            text = "S'inscrire avec Google",
                            style = MaterialTheme.typography.labelLarge.copy(color = Gray700)
                        )
                    }

                    Spacer(modifier = Modifier.height(28.dp))

                    // Sign-in link
                    Box(
                        modifier = Modifier.fillMaxWidth(),
                        contentAlignment = Alignment.Center
                    ) {
                        val signinText = buildAnnotatedString {
                            withStyle(SpanStyle(color = Gray500)) {
                                append("Deja un compte? ")
                            }
                            withStyle(SpanStyle(color = OceanBlue700, fontWeight = FontWeight.Bold)) {
                                append("Se connecter")
                            }
                        }
                        Text(
                            text = signinText,
                            style = MaterialTheme.typography.bodyMedium,
                            modifier = Modifier.clickable(
                                indication = null,
                                interactionSource = remember { MutableInteractionSource() }
                            ) {
                                navController.navigate(Screen.Login.route) {
                                    popUpTo(Screen.Register.route) { inclusive = true }
                                }
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
