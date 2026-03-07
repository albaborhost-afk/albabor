package com.albabor.app.ui.screens.profile

import androidx.compose.animation.AnimatedVisibility
import androidx.compose.animation.fadeIn
import androidx.compose.animation.fadeOut
import androidx.compose.foundation.background
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.text.KeyboardActions
import androidx.compose.foundation.text.KeyboardOptions
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.*
import androidx.compose.material.icons.outlined.*
import androidx.compose.material3.*
import androidx.compose.material3.TabRowDefaults.tabIndicatorOffset
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.focus.FocusDirection
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.platform.LocalFocusManager
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.input.ImeAction
import androidx.compose.ui.text.input.KeyboardType
import androidx.compose.ui.text.input.PasswordVisualTransformation
import androidx.compose.ui.text.input.VisualTransformation
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.lifecycle.compose.collectAsStateWithLifecycle
import androidx.lifecycle.viewmodel.compose.viewModel
import androidx.navigation.NavController
import com.albabor.app.ui.screens.auth.AuthTextField
import com.albabor.app.ui.screens.auth.GradientButton
import com.albabor.app.ui.theme.*
import com.albabor.app.viewmodel.ProfileViewModel
import kotlinx.coroutines.launch

// ─── Screen ───────────────────────────────────────────────────────────────────

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun EditProfileScreen(navController: NavController) {
    val context = LocalContext.current
    val vm: ProfileViewModel = viewModel(factory = ProfileViewModel.factory(context))
    val user          by vm.user.collectAsStateWithLifecycle()
    val updateState   by vm.updateState.collectAsStateWithLifecycle()
    val passwordState by vm.passwordState.collectAsStateWithLifecycle()

    // Tabs: 0 = Infos, 1 = Mot de passe
    var selectedTab by remember { mutableIntStateOf(0) }

    val snackbarHostState = remember { SnackbarHostState() }
    val scope = rememberCoroutineScope()
    val focusManager = LocalFocusManager.current

    // ── Profile fields ────────────────────────────────────────────────────────
    var name  by remember(user) { mutableStateOf(user?.name  ?: "") }
    var phone by remember(user) { mutableStateOf(user?.phone ?: "") }

    // ── Password fields ───────────────────────────────────────────────────────
    var currentPassword  by remember { mutableStateOf("") }
    var newPassword      by remember { mutableStateOf("") }
    var confirmPassword  by remember { mutableStateOf("") }
    var showCurrentPwd   by remember { mutableStateOf(false) }
    var showNewPwd       by remember { mutableStateOf(false) }
    var showConfirmPwd   by remember { mutableStateOf(false) }

    // ── Handle state results ──────────────────────────────────────────────────
    LaunchedEffect(updateState) {
        when (val s = updateState) {
            is ProfileViewModel.UpdateState.Success -> {
                scope.launch { snackbarHostState.showSnackbar("Profil mis a jour avec succes") }
                vm.clearUpdateState()
            }
            is ProfileViewModel.UpdateState.Error -> {
                scope.launch { snackbarHostState.showSnackbar(s.msg) }
                vm.clearUpdateState()
            }
            else -> Unit
        }
    }

    LaunchedEffect(passwordState) {
        when (val s = passwordState) {
            is ProfileViewModel.UpdateState.Success -> {
                scope.launch { snackbarHostState.showSnackbar("Mot de passe modifie avec succes") }
                currentPassword = ""; newPassword = ""; confirmPassword = ""
                vm.clearPasswordState()
            }
            is ProfileViewModel.UpdateState.Error -> {
                scope.launch { snackbarHostState.showSnackbar(s.msg) }
                vm.clearPasswordState()
            }
            else -> Unit
        }
    }

    val isProfileLoading  = updateState  is ProfileViewModel.UpdateState.Loading
    val isPasswordLoading = passwordState is ProfileViewModel.UpdateState.Loading

    Scaffold(
        topBar = {
            TopAppBar(
                title = {
                    Text(
                        text = "Modifier le profil",
                        fontWeight = FontWeight.Bold,
                        fontSize = 19.sp,
                        color = Gray900
                    )
                },
                navigationIcon = {
                    IconButton(onClick = { navController.navigateUp() }) {
                        Icon(
                            imageVector = Icons.Default.ArrowBack,
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
        snackbarHost = {
            SnackbarHost(hostState = snackbarHostState) { data ->
                Snackbar(
                    snackbarData = data,
                    containerColor = Gray900,
                    contentColor = White,
                    shape = RoundedCornerShape(12.dp),
                    modifier = Modifier
                        .padding(horizontal = 16.dp)
                        .navigationBarsPadding()
                )
            }
        },
        containerColor = Gray50
    ) { padding ->

        Column(
            modifier = Modifier
                .fillMaxSize()
                .padding(padding)
        ) {
            // ── Tab bar ───────────────────────────────────────────────────────
            Surface(color = White, shadowElevation = 1.dp) {
                TabRow(
                    selectedTabIndex = selectedTab,
                    containerColor = White,
                    contentColor = OceanBlue700,
                    indicator = { tabPositions ->
                        if (selectedTab < tabPositions.size) {
                            TabRowDefaults.SecondaryIndicator(
                                modifier = Modifier.tabIndicatorOffset(tabPositions[selectedTab]),
                                height = 2.5.dp,
                                color = OceanBlue700
                            )
                        }
                    }
                ) {
                    Tab(
                        selected = selectedTab == 0,
                        onClick = { selectedTab = 0 },
                        text = {
                            Text(
                                "Informations",
                                fontWeight = if (selectedTab == 0) FontWeight.SemiBold else FontWeight.Normal
                            )
                        },
                        icon = {
                            Icon(
                                imageVector = Icons.Outlined.Person,
                                contentDescription = null,
                                modifier = Modifier.size(18.dp)
                            )
                        }
                    )
                    Tab(
                        selected = selectedTab == 1,
                        onClick = { selectedTab = 1 },
                        text = {
                            Text(
                                "Mot de passe",
                                fontWeight = if (selectedTab == 1) FontWeight.SemiBold else FontWeight.Normal
                            )
                        },
                        icon = {
                            Icon(
                                imageVector = Icons.Outlined.Lock,
                                contentDescription = null,
                                modifier = Modifier.size(18.dp)
                            )
                        }
                    )
                }
            }

            // ── Tab content ───────────────────────────────────────────────────
            when (selectedTab) {
                0 -> {
                    // ── Profile info form ─────────────────────────────────────
                    Column(
                        modifier = Modifier
                            .fillMaxSize()
                            .verticalScroll(rememberScrollState())
                            .padding(horizontal = 20.dp, vertical = 24.dp)
                            .imePadding()
                            .navigationBarsPadding()
                    ) {
                        FormSectionLabel("Informations personnelles")

                        Spacer(modifier = Modifier.height(14.dp))

                        // Name field
                        AuthTextField(
                            value = name,
                            onValueChange = { name = it },
                            label = "Nom complet",
                            leadingIcon = {
                                Icon(
                                    imageVector = Icons.Outlined.Person,
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
                                onNext = { focusManager.moveFocus(FocusDirection.Down) }
                            )
                        )

                        Spacer(modifier = Modifier.height(14.dp))

                        // Phone field
                        AuthTextField(
                            value = phone,
                            onValueChange = { phone = it },
                            label = "Numero de telephone",
                            leadingIcon = {
                                Icon(
                                    imageVector = Icons.Outlined.Phone,
                                    contentDescription = null,
                                    tint = OceanBlue700,
                                    modifier = Modifier.size(20.dp)
                                )
                            },
                            keyboardOptions = KeyboardOptions(
                                keyboardType = KeyboardType.Phone,
                                imeAction = ImeAction.Done
                            ),
                            keyboardActions = KeyboardActions(
                                onDone = {
                                    focusManager.clearFocus()
                                    if (!isProfileLoading) vm.updateProfile(name, phone)
                                }
                            )
                        )

                        // Email (read-only display)
                        if (user?.email != null) {
                            Spacer(modifier = Modifier.height(14.dp))
                            ReadOnlyField(
                                label = "Adresse email",
                                value = user!!.email,
                                icon = Icons.Outlined.Email
                            )
                            Text(
                                text = "L'email ne peut pas etre modifie",
                                style = MaterialTheme.typography.labelSmall.copy(color = Gray400),
                                modifier = Modifier.padding(start = 4.dp, top = 4.dp)
                            )
                        }

                        Spacer(modifier = Modifier.height(32.dp))

                        GradientButton(
                            text = "Enregistrer",
                            onClick = {
                                focusManager.clearFocus()
                                vm.updateProfile(name, phone)
                            },
                            enabled = !isProfileLoading && name.isNotBlank(),
                            isLoading = isProfileLoading,
                            modifier = Modifier.fillMaxWidth()
                        )
                    }
                }

                1 -> {
                    // ── Password form ─────────────────────────────────────────
                    Column(
                        modifier = Modifier
                            .fillMaxSize()
                            .verticalScroll(rememberScrollState())
                            .padding(horizontal = 20.dp, vertical = 24.dp)
                            .imePadding()
                            .navigationBarsPadding()
                    ) {
                        FormSectionLabel("Changement de mot de passe")

                        Spacer(modifier = Modifier.height(6.dp))

                        Text(
                            text = "Votre nouveau mot de passe doit contenir au moins 8 caracteres.",
                            style = MaterialTheme.typography.bodySmall.copy(color = Gray400)
                        )

                        Spacer(modifier = Modifier.height(20.dp))

                        // Current password
                        AuthTextField(
                            value = currentPassword,
                            onValueChange = { currentPassword = it },
                            label = "Mot de passe actuel",
                            leadingIcon = {
                                Icon(
                                    imageVector = Icons.Default.Lock,
                                    contentDescription = null,
                                    tint = OceanBlue700,
                                    modifier = Modifier.size(20.dp)
                                )
                            },
                            visualTransformation = if (showCurrentPwd)
                                VisualTransformation.None else PasswordVisualTransformation(),
                            trailingIcon = {
                                IconButton(onClick = { showCurrentPwd = !showCurrentPwd }) {
                                    Icon(
                                        imageVector = if (showCurrentPwd)
                                            Icons.Default.VisibilityOff else Icons.Default.Visibility,
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
                            )
                        )

                        Spacer(modifier = Modifier.height(14.dp))

                        // New password
                        AuthTextField(
                            value = newPassword,
                            onValueChange = { newPassword = it },
                            label = "Nouveau mot de passe",
                            leadingIcon = {
                                Icon(
                                    imageVector = Icons.Default.Lock,
                                    contentDescription = null,
                                    tint = OceanBlue700,
                                    modifier = Modifier.size(20.dp)
                                )
                            },
                            visualTransformation = if (showNewPwd)
                                VisualTransformation.None else PasswordVisualTransformation(),
                            trailingIcon = {
                                IconButton(onClick = { showNewPwd = !showNewPwd }) {
                                    Icon(
                                        imageVector = if (showNewPwd)
                                            Icons.Default.VisibilityOff else Icons.Default.Visibility,
                                        contentDescription = null,
                                        tint = Gray400
                                    )
                                }
                            },
                            isError = newPassword.isNotEmpty() && newPassword.length < 8,
                            supportingText = if (newPassword.isNotEmpty() && newPassword.length < 8) {
                                { Text("Minimum 8 caracteres", color = Error500, fontSize = 12.sp) }
                            } else null,
                            keyboardOptions = KeyboardOptions(
                                keyboardType = KeyboardType.Password,
                                imeAction = ImeAction.Next
                            ),
                            keyboardActions = KeyboardActions(
                                onNext = { focusManager.moveFocus(FocusDirection.Down) }
                            )
                        )

                        Spacer(modifier = Modifier.height(14.dp))

                        // Confirm password
                        val passwordMismatch = confirmPassword.isNotEmpty() && confirmPassword != newPassword
                        AuthTextField(
                            value = confirmPassword,
                            onValueChange = { confirmPassword = it },
                            label = "Confirmer le nouveau mot de passe",
                            leadingIcon = {
                                Icon(
                                    imageVector = Icons.Default.Lock,
                                    contentDescription = null,
                                    tint = OceanBlue700,
                                    modifier = Modifier.size(20.dp)
                                )
                            },
                            visualTransformation = if (showConfirmPwd)
                                VisualTransformation.None else PasswordVisualTransformation(),
                            trailingIcon = {
                                IconButton(onClick = { showConfirmPwd = !showConfirmPwd }) {
                                    Icon(
                                        imageVector = if (showConfirmPwd)
                                            Icons.Default.VisibilityOff else Icons.Default.Visibility,
                                        contentDescription = null,
                                        tint = Gray400
                                    )
                                }
                            },
                            isError = passwordMismatch,
                            supportingText = if (passwordMismatch) {
                                { Text("Les mots de passe ne correspondent pas", color = Error500, fontSize = 12.sp) }
                            } else null,
                            keyboardOptions = KeyboardOptions(
                                keyboardType = KeyboardType.Password,
                                imeAction = ImeAction.Done
                            ),
                            keyboardActions = KeyboardActions(
                                onDone = {
                                    focusManager.clearFocus()
                                    if (!isPasswordLoading) {
                                        vm.updatePassword(currentPassword, newPassword, confirmPassword)
                                    }
                                }
                            )
                        )

                        Spacer(modifier = Modifier.height(32.dp))

                        val passwordFormValid = currentPassword.isNotBlank()
                            && newPassword.length >= 8
                            && newPassword == confirmPassword

                        GradientButton(
                            text = "Changer le mot de passe",
                            onClick = {
                                focusManager.clearFocus()
                                vm.updatePassword(currentPassword, newPassword, confirmPassword)
                            },
                            enabled = !isPasswordLoading && passwordFormValid,
                            isLoading = isPasswordLoading,
                            modifier = Modifier.fillMaxWidth()
                        )
                    }
                }
            }
        }

        // Global loading overlay (for profile update)
        AnimatedVisibility(
            visible = isProfileLoading || isPasswordLoading,
            enter = fadeIn(),
            exit = fadeOut()
        ) {
            Box(
                modifier = Modifier
                    .fillMaxSize()
                    .background(androidx.compose.ui.graphics.Color.Black.copy(alpha = 0.25f)),
                contentAlignment = Alignment.Center
            ) {
                Box(
                    modifier = Modifier
                        .size(72.dp)
                        .clip(RoundedCornerShape(16.dp))
                        .background(White),
                    contentAlignment = Alignment.Center
                ) {
                    CircularProgressIndicator(color = OceanBlue700, modifier = Modifier.size(32.dp))
                }
            }
        }
    }
}

// ─── Read-only display field ──────────────────────────────────────────────────

@Composable
private fun ReadOnlyField(
    label: String,
    value: String,
    icon: androidx.compose.ui.graphics.vector.ImageVector
) {
    Surface(
        modifier = Modifier.fillMaxWidth(),
        shape = RoundedCornerShape(12.dp),
        color = Gray100,
        border = androidx.compose.foundation.BorderStroke(1.dp, Gray200)
    ) {
        Row(
            modifier = Modifier.padding(horizontal = 16.dp, vertical = 14.dp),
            verticalAlignment = Alignment.CenterVertically,
            horizontalArrangement = Arrangement.spacedBy(12.dp)
        ) {
            Icon(
                imageVector = icon,
                contentDescription = null,
                tint = Gray400,
                modifier = Modifier.size(20.dp)
            )
            Column {
                Text(
                    text = label,
                    style = MaterialTheme.typography.labelSmall.copy(color = Gray400),
                    fontSize = 11.sp
                )
                Text(
                    text = value,
                    style = MaterialTheme.typography.bodyMedium.copy(color = Gray500)
                )
            }
        }
    }
}

// ─── Form section label ───────────────────────────────────────────────────────

@Composable
private fun FormSectionLabel(text: String) {
    Text(
        text = text,
        style = MaterialTheme.typography.titleSmall.copy(
            color = Gray900,
            fontWeight = FontWeight.Bold
        )
    )
}

// Note: tabIndicatorOffset is provided by the M3 TabRowDefaults import.
