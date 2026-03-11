package com.albabor.app.ui.screens.profile

import androidx.compose.foundation.background
import androidx.compose.foundation.clickable
import androidx.compose.foundation.interaction.MutableInteractionSource
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.outlined.List
import androidx.compose.material.icons.automirrored.outlined.Logout
import androidx.compose.material.icons.filled.*
import androidx.compose.material.icons.outlined.*
import androidx.compose.material3.*
import androidx.compose.material3.ripple
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.draw.shadow
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.vector.ImageVector
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.lifecycle.compose.collectAsStateWithLifecycle
import androidx.lifecycle.viewmodel.compose.viewModel
import androidx.navigation.NavController
import coil.compose.AsyncImage
import com.albabor.app.data.model.User
import com.albabor.app.ui.navigation.Screen
import com.albabor.app.ui.theme.*
import com.albabor.app.viewmodel.ProfileViewModel
import kotlinx.coroutines.launch

// ─── Screen ───────────────────────────────────────────────────────────────────

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun ProfileScreen(navController: NavController) {
    val context = LocalContext.current
    val vm: ProfileViewModel = viewModel(factory = ProfileViewModel.factory(context))
    val user          by vm.user.collectAsStateWithLifecycle()
    val isLoading     by vm.isLoading.collectAsStateWithLifecycle()
    val profileError  by vm.profileError.collectAsStateWithLifecycle()
    val upgradeState  by vm.upgradeState.collectAsStateWithLifecycle()

    val snackbarHostState = remember { SnackbarHostState() }
    val scope = rememberCoroutineScope()

    var showLogoutDialog  by remember { mutableStateOf(false) }
    var showUpgradeDialog by remember { mutableStateOf(false) }

    LaunchedEffect(upgradeState) {
        when (val s = upgradeState) {
            is ProfileViewModel.UpdateState.Success ->
                scope.launch { snackbarHostState.showSnackbar("Compte vendeur active avec succes !") }
            is ProfileViewModel.UpdateState.Error ->
                scope.launch { snackbarHostState.showSnackbar(s.msg) }
            else -> Unit
        }
        if (upgradeState != ProfileViewModel.UpdateState.Idle) vm.clearUpgradeState()
    }

    Scaffold(
        topBar = {
            TopAppBar(
                title = {
                    Text(
                        text = "Mon profil",
                        fontWeight = FontWeight.Bold,
                        fontSize = 20.sp,
                        color = Gray900
                    )
                },
                actions = {
                    IconButton(onClick = { navController.navigate(Screen.EditProfile.route) }) {
                        Icon(
                            imageVector = Icons.Outlined.Settings,
                            contentDescription = "Parametres",
                            tint = Gray500
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
                    shape = RoundedCornerShape(12.dp)
                )
            }
        },
        containerColor = Gray50
    ) { padding ->

        if (isLoading && user == null) {
            Box(
                modifier = Modifier
                    .fillMaxSize()
                    .padding(padding),
                contentAlignment = Alignment.Center
            ) {
                CircularProgressIndicator(color = OceanBlue700)
            }
            return@Scaffold
        }

        if (!isLoading && user == null) {
            Box(
                modifier = Modifier
                    .fillMaxSize()
                    .padding(padding),
                contentAlignment = Alignment.Center
            ) {
                Column(
                    horizontalAlignment = Alignment.CenterHorizontally,
                    verticalArrangement = Arrangement.spacedBy(16.dp),
                    modifier = Modifier.padding(32.dp)
                ) {
                    Icon(
                        imageVector = Icons.Outlined.Person,
                        contentDescription = null,
                        modifier = Modifier.size(64.dp),
                        tint = Gray300
                    )
                    Text(
                        text = "Connectez-vous pour accéder à votre profil",
                        style = MaterialTheme.typography.bodyLarge.copy(color = Gray500),
                        textAlign = TextAlign.Center
                    )
                    if (profileError != null) {
                        Text(
                            text = profileError!!,
                            style = MaterialTheme.typography.bodySmall.copy(color = Error500),
                            textAlign = TextAlign.Center
                        )
                    }
                    Button(
                        onClick = {
                            navController.navigate(Screen.Login.route) {
                                popUpTo(0) { inclusive = true }
                            }
                        },
                        colors = ButtonDefaults.buttonColors(containerColor = OceanBlue700),
                        shape = RoundedCornerShape(12.dp)
                    ) {
                        Text("Se connecter")
                    }
                    TextButton(onClick = { vm.loadProfile() }) {
                        Text("Réessayer", color = OceanBlue700)
                    }
                }
            }
            return@Scaffold
        }

        Column(
            modifier = Modifier
                .fillMaxSize()
                .padding(padding)
                .verticalScroll(rememberScrollState())
        ) {

            // ── Hero header card ──────────────────────────────────────────────
            ProfileHeaderCard(user = user)

            Spacer(modifier = Modifier.height(16.dp))

            // ── Section 1: Mon compte ─────────────────────────────────────────
            MenuSection(title = "Mon compte") {
                MenuItem(
                    icon = Icons.AutoMirrored.Outlined.List,
                    label = "Mes annonces",
                    iconTint = OceanBlue700,
                    onClick = { navController.navigate(Screen.MyListings.route) }
                )
                MenuDivider()
                MenuItem(
                    icon = Icons.Outlined.FavoriteBorder,
                    label = "Mes favoris",
                    iconTint = Error500,
                    onClick = { navController.navigate(Screen.Favorites.route) }
                )
                MenuDivider()
                MenuItem(
                    icon = Icons.Outlined.ChatBubbleOutline,
                    label = "Mes messages",
                    iconTint = Teal500,
                    onClick = { navController.navigate(Screen.Conversations.route) }
                )
                MenuDivider()
                MenuItem(
                    icon = Icons.Outlined.Balance,
                    label = "Mediation",
                    iconTint = Gold500,
                    onClick = { navController.navigate(Screen.Mediation.route) }
                )
                MenuDivider()
                MenuItem(
                    icon = Icons.Outlined.CreditCard,
                    label = "Paiements",
                    iconTint = Success500,
                    onClick = { navController.navigate(Screen.Payments.route) }
                )
            }

            Spacer(modifier = Modifier.height(12.dp))

            // ── Section 2: Parametres ─────────────────────────────────────────
            MenuSection(title = "Parametres") {
                MenuItem(
                    icon = Icons.Outlined.Edit,
                    label = "Modifier le profil",
                    iconTint = OceanBlue700,
                    onClick = { navController.navigate(Screen.EditProfile.route()) }
                )
                MenuDivider()
                MenuItem(
                    icon = Icons.Outlined.Lock,
                    label = "Changer le mot de passe",
                    iconTint = Gray500,
                    onClick = { navController.navigate(Screen.EditProfile.route(tab = "password")) }
                )

                // Subscriptions — vendors only
                if (user?.isVendorAccount == true) {
                    MenuDivider()
                    MenuItem(
                        icon = Icons.Outlined.Storefront,
                        label = "Abonnements",
                        iconTint = OceanBlue700,
                        badge = "Pro",
                        badgeColor = Gold500,
                        onClick = { navController.navigate(Screen.Subscriptions.route) }
                    )
                }

                // Upgrade to vendor — non-vendors
                if (user?.isVendorAccount == false) {
                    MenuDivider()
                    MenuItem(
                        icon = Icons.Outlined.Storefront,
                        label = "Devenir vendeur",
                        iconTint = Gold500,
                        badge = "Nouveau",
                        badgeColor = Gold500,
                        onClick = { showUpgradeDialog = true }
                    )
                }

                // Identity verification — unverified
                if (user?.isVerifiedAccount == false) {
                    MenuDivider()
                    MenuItem(
                        icon = Icons.Outlined.VerifiedUser,
                        label = "Verification d'identite",
                        iconTint = Teal500,
                        onClick = { navController.navigate(Screen.Verification.route) }
                    )
                }
            }

            Spacer(modifier = Modifier.height(12.dp))

            // ── Section 3: Logout ─────────────────────────────────────────────
            MenuSection {
                MenuItem(
                    icon = Icons.AutoMirrored.Outlined.Logout,
                    label = "Se deconnecter",
                    iconTint = Error500,
                    textColor = Error500,
                    showArrow = false,
                    onClick = { showLogoutDialog = true }
                )
            }

            Spacer(modifier = Modifier.height(32.dp))

            // Version footer
            Text(
                text = "AlBabor v1.0.0 · La Mer, Votre Marche",
                modifier = Modifier.fillMaxWidth(),
                textAlign = TextAlign.Center,
                style = MaterialTheme.typography.bodySmall.copy(color = Gray300),
                fontSize = 11.sp
            )

            Spacer(modifier = Modifier.height(20.dp))
        }
    }

    // ── Logout dialog ─────────────────────────────────────────────────────────
    if (showLogoutDialog) {
        AlertDialog(
            onDismissRequest = { showLogoutDialog = false },
            title = { Text("Se deconnecter", fontWeight = FontWeight.Bold) },
            text = { Text("Voulez-vous vraiment vous deconnecter ?", color = Gray500) },
            confirmButton = {
                TextButton(
                    onClick = {
                        showLogoutDialog = false
                        vm.logout {
                            navController.navigate(Screen.Login.route) {
                                popUpTo(0) { inclusive = true }
                            }
                        }
                    }
                ) {
                    Text("Deconnecter", color = Error500, fontWeight = FontWeight.Bold)
                }
            },
            dismissButton = {
                TextButton(onClick = { showLogoutDialog = false }) {
                    Text("Annuler", color = Gray500)
                }
            },
            shape = RoundedCornerShape(16.dp),
            containerColor = White
        )
    }

    // ── Upgrade to vendor dialog ──────────────────────────────────────────────
    if (showUpgradeDialog) {
        AlertDialog(
            onDismissRequest = { showUpgradeDialog = false },
            icon = {
                Box(
                    modifier = Modifier
                        .size(56.dp)
                        .clip(CircleShape)
                        .background(Gold100),
                    contentAlignment = Alignment.Center
                ) {
                    Icon(
                        imageVector = Icons.Outlined.Storefront,
                        contentDescription = null,
                        tint = Gold500,
                        modifier = Modifier.size(28.dp)
                    )
                }
            },
            title = {
                Text(
                    "Devenir vendeur",
                    fontWeight = FontWeight.Bold,
                    textAlign = TextAlign.Center
                )
            },
            text = {
                Text(
                    "En devenant vendeur vous aurez acces a des outils avances pour gerer vos annonces, " +
                    "suivre vos statistiques et acceder aux abonnements professionnels.",
                    color = Gray500,
                    textAlign = TextAlign.Center
                )
            },
            confirmButton = {
                Button(
                    onClick = {
                        showUpgradeDialog = false
                        vm.upgradeToVendor()
                    },
                    colors = ButtonDefaults.buttonColors(containerColor = Gold500)
                ) {
                    Text("Activer le compte vendeur", fontWeight = FontWeight.SemiBold)
                }
            },
            dismissButton = {
                TextButton(onClick = { showUpgradeDialog = false }) {
                    Text("Pas maintenant", color = Gray500)
                }
            },
            shape = RoundedCornerShape(16.dp),
            containerColor = White
        )
    }
}

// ─── Header card with gradient background ────────────────────────────────────

@Composable
private fun ProfileHeaderCard(user: User?) {
    Box(
        modifier = Modifier
            .fillMaxWidth()
            .shadow(elevation = 4.dp, spotColor = OceanBlue900.copy(alpha = 0.15f))
            .background(
                Brush.verticalGradient(
                    colorStops = arrayOf(
                        0f    to OceanBlue900,
                        0.6f  to OceanBlue700,
                        1f    to Teal500
                    )
                )
            )
    ) {
        Column(
            modifier = Modifier
                .fillMaxWidth()
                .padding(horizontal = 24.dp, vertical = 28.dp),
            horizontalAlignment = Alignment.CenterHorizontally
        ) {
            // Avatar
            Box(
                modifier = Modifier
                    .size(88.dp)
                    .shadow(6.dp, CircleShape)
                    .clip(CircleShape)
                    .background(White)
            ) {
                if (user?.avatar != null) {
                    AsyncImage(
                        model = user.avatar,
                        contentDescription = user.name,
                        contentScale = ContentScale.Crop,
                        modifier = Modifier.fillMaxSize()
                    )
                } else {
                    // Initials fallback
                    Box(
                        modifier = Modifier
                            .fillMaxSize()
                            .background(
                                Brush.radialGradient(listOf(OceanBlue300, OceanBlue700))
                            ),
                        contentAlignment = Alignment.Center
                    ) {
                        Text(
                            text = user?.name?.take(1)?.uppercase() ?: "?",
                            color = White,
                            fontSize = 34.sp,
                            fontWeight = FontWeight.Bold
                        )
                    }
                }
            }

            Spacer(modifier = Modifier.height(14.dp))

            // Name
            Text(
                text = user?.name ?: "Chargement…",
                color = White,
                fontWeight = FontWeight.Bold,
                fontSize = 22.sp
            )

            Spacer(modifier = Modifier.height(4.dp))

            // Email
            Text(
                text = user?.email ?: "",
                color = White.copy(alpha = 0.75f),
                fontSize = 13.sp
            )

            Spacer(modifier = Modifier.height(10.dp))

            // Badges row
            Row(
                horizontalArrangement = Arrangement.spacedBy(8.dp),
                verticalAlignment = Alignment.CenterVertically
            ) {
                if (user?.isVerifiedAccount == true) {
                    Surface(
                        shape = RoundedCornerShape(50),
                        color = White.copy(alpha = 0.2f)
                    ) {
                        Row(
                            modifier = Modifier.padding(horizontal = 10.dp, vertical = 4.dp),
                            verticalAlignment = Alignment.CenterVertically,
                            horizontalArrangement = Arrangement.spacedBy(4.dp)
                        ) {
                            Icon(
                                imageVector = Icons.Default.Verified,
                                contentDescription = null,
                                tint = White,
                                modifier = Modifier.size(14.dp)
                            )
                            Text(
                                text = "Verifie",
                                color = White,
                                fontSize = 11.sp,
                                fontWeight = FontWeight.SemiBold
                            )
                        }
                    }
                }

                if (user?.isVendorAccount == true) {
                    Surface(
                        shape = RoundedCornerShape(50),
                        color = Gold500.copy(alpha = 0.85f)
                    ) {
                        Row(
                            modifier = Modifier.padding(horizontal = 10.dp, vertical = 4.dp),
                            verticalAlignment = Alignment.CenterVertically,
                            horizontalArrangement = Arrangement.spacedBy(4.dp)
                        ) {
                            Icon(
                                imageVector = Icons.Outlined.Storefront,
                                contentDescription = null,
                                tint = White,
                                modifier = Modifier.size(14.dp)
                            )
                            Text(
                                text = "Vendeur",
                                color = White,
                                fontSize = 11.sp,
                                fontWeight = FontWeight.SemiBold
                            )
                        }
                    }
                }
            }

            Spacer(modifier = Modifier.height(20.dp))

            // Stats row
            Surface(
                shape = RoundedCornerShape(14.dp),
                color = White.copy(alpha = 0.15f),
                modifier = Modifier.fillMaxWidth()
            ) {
                Row(
                    modifier = Modifier
                        .fillMaxWidth()
                        .padding(vertical = 14.dp),
                    horizontalArrangement = Arrangement.SpaceEvenly,
                    verticalAlignment = Alignment.CenterVertically
                ) {
                    StatItem(
                        value = user?.listingsCount?.toString() ?: "0",
                        label = "Annonces"
                    )
                    VerticalDivider(
                        modifier = Modifier.height(32.dp),
                        color = White.copy(alpha = 0.3f),
                        thickness = 1.dp
                    )
                    StatItem(
                        value = user?.favoritesCount?.toString() ?: "0",
                        label = "Favoris"
                    )
                }
            }
        }
    }
}

@Composable
private fun StatItem(value: String, label: String) {
    Column(horizontalAlignment = Alignment.CenterHorizontally) {
        Text(
            text = value,
            color = White,
            fontWeight = FontWeight.ExtraBold,
            fontSize = 24.sp
        )
        Text(
            text = label,
            color = White.copy(alpha = 0.75f),
            fontSize = 12.sp,
            fontWeight = FontWeight.Medium
        )
    }
}

// ─── Menu section card ────────────────────────────────────────────────────────

@Composable
private fun MenuSection(
    title: String? = null,
    content: @Composable ColumnScope.() -> Unit
) {
    Column(modifier = Modifier.fillMaxWidth()) {
        if (title != null) {
            Text(
                text = title.uppercase(),
                style = MaterialTheme.typography.labelSmall.copy(
                    color = Gray400,
                    fontWeight = FontWeight.SemiBold,
                    letterSpacing = 1.2.sp
                ),
                modifier = Modifier.padding(horizontal = 20.dp, vertical = 6.dp)
            )
        }
        Surface(
            modifier = Modifier
                .fillMaxWidth()
                .padding(horizontal = 16.dp),
            shape = RoundedCornerShape(14.dp),
            color = White,
            shadowElevation = 1.dp,
            tonalElevation = 0.dp
        ) {
            Column(content = content)
        }
    }
}

// ─── Menu item row ────────────────────────────────────────────────────────────

@Composable
private fun MenuItem(
    icon: ImageVector,
    label: String,
    onClick: () -> Unit,
    iconTint: Color = Gray500,
    textColor: Color = Gray900,
    badge: String? = null,
    badgeColor: Color = OceanBlue700,
    showArrow: Boolean = true
) {
    Row(
        modifier = Modifier
            .fillMaxWidth()
            .clickable(
                indication = menuRipple(),
                interactionSource = remember { MutableInteractionSource() },
                onClick = onClick
            )
            .padding(horizontal = 16.dp, vertical = 15.dp),
        verticalAlignment = Alignment.CenterVertically
    ) {
        // Icon container
        Box(
            modifier = Modifier
                .size(36.dp)
                .clip(RoundedCornerShape(10.dp))
                .background(iconTint.copy(alpha = 0.1f)),
            contentAlignment = Alignment.Center
        ) {
            Icon(
                imageVector = icon,
                contentDescription = null,
                tint = iconTint,
                modifier = Modifier.size(20.dp)
            )
        }

        Spacer(modifier = Modifier.width(14.dp))

        Text(
            text = label,
            style = MaterialTheme.typography.bodyMedium.copy(
                color = textColor,
                fontWeight = FontWeight.Medium
            ),
            modifier = Modifier.weight(1f)
        )

        // Optional badge
        if (badge != null) {
            Box(
                modifier = Modifier
                    .clip(RoundedCornerShape(50))
                    .background(badgeColor)
                    .padding(horizontal = 8.dp, vertical = 3.dp)
            ) {
                Text(
                    text = badge,
                    color = White,
                    fontSize = 10.sp,
                    fontWeight = FontWeight.Bold
                )
            }
            Spacer(modifier = Modifier.width(8.dp))
        }

        if (showArrow) {
            Icon(
                imageVector = Icons.Default.ChevronRight,
                contentDescription = null,
                tint = Gray300,
                modifier = Modifier.size(20.dp)
            )
        }
    }
}

// ─── Section divider ──────────────────────────────────────────────────────────

@Composable
private fun MenuDivider() {
    HorizontalDivider(
        modifier = Modifier.padding(start = 66.dp, end = 16.dp),
        color = Gray100,
        thickness = 0.5.dp
    )
}

// ─── Ripple helper ────────────────────────────────────────────────────────────

@Composable
private fun menuRipple() = ripple(
    bounded = true,
    color = OceanBlue700.copy(alpha = 0.08f)
)
