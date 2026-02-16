<div class="flex flex-col items-center justify-center p-4">
    @if($proofUrl)
        <img
            src="{{ $proofUrl }}"
            alt="Justificatif de paiement"
            class="max-w-full max-h-[70vh] rounded-lg shadow-lg object-contain cursor-zoom-in"
            onclick="window.open('{{ $proofUrl }}', '_blank')"
            title="Cliquez pour ouvrir en plein écran"
        />
        <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">
            Cliquez sur l'image pour l'ouvrir en plein écran
        </p>
    @else
        <div class="flex flex-col items-center justify-center py-8 text-gray-500 dark:text-gray-400">
            <x-heroicon-o-photo class="w-16 h-16 mb-4" />
            <p>Aucun justificatif disponible</p>
        </div>
    @endif
</div>
