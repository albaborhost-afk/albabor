{{-- Finition visuelle "Albabor Pro" — injecté via PanelsRenderHook::HEAD_END. Léger, sans build. --}}
<style>
    /* Dégradé teal -> gold subtil sur la page de connexion */
    .fi-simple-layout {
        background:
            radial-gradient(1100px 520px at 12% -8%, rgba(23, 162, 184, 0.18), transparent 60%),
            radial-gradient(900px 480px at 88% 108%, rgba(243, 156, 18, 0.16), transparent 60%);
    }

    .fi-simple-main {
        border-top: 3px solid #17A2B8;
        box-shadow: 0 18px 50px -12px rgba(23, 162, 184, 0.30);
    }

    /* Accent doré sous le logo de la barre supérieure */
    .fi-topbar .fi-logo {
        padding-bottom: 0.25rem;
        border-bottom: 2px solid #F39C12;
    }

    /* Bandeau d'identité au-dessus du contenu */
    .ab-pro-banner {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.75rem;
        font-weight: 600;
        color: #fff;
        padding: 0.4rem 1rem;
        background: linear-gradient(90deg, #17A2B8 0%, #2471A3 55%, #F39C12 100%);
        letter-spacing: 0.02em;
    }

    .ab-pro-banner svg {
        width: 1rem;
        height: 1rem;
        flex: none;
    }

    /* Pied de page discret */
    .ab-pro-footer {
        text-align: center;
        font-size: 0.75rem;
        padding: 1rem;
        color: rgb(113 113 122);
    }

    .ab-pro-footer strong {
        color: #17A2B8;
        font-weight: 600;
    }

    .dark .ab-pro-footer {
        color: rgb(161 161 170);
    }
</style>
