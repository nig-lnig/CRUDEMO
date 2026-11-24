{{-- Aide : raccourcis clavier --}}
<div class="fixed inset-0 z-50 grid place-items-center bg-black/55 p-6"
     x-show="$store.deck.help" x-cloak x-transition.opacity
     @click.self="$store.deck.help = false">
    <div class="panel w-full max-w-[560px] p-6 fade-in" role="dialog" aria-label="Raccourcis clavier">
        <div class="mb-4 flex items-center gap-2">
            <span x-icon="keyboard" class="text-[20px] text-brand"></span>
            <h2 class="text-[18px] font-bold">Raccourcis clavier</h2>
            <button type="button" class="btn btn-ghost btn-icon ml-auto" @click="$store.deck.help = false" aria-label="Fermer">
                <span x-icon="x" class="text-[18px]"></span>
            </button>
        </div>
        @php
            $shortcuts = [
            [['←', '→'], 'Slide précédente / suivante'],
            [['Début', 'Fin'], 'Première / dernière slide'],
            [['1', '2', 'Entrée'], 'Aller à la slide 12'],
            [['Ctrl', 'Entrée'], 'Envoyer la requête (Send)'],
            [['F'], 'Plein écran'],
            [['S'], 'Plan des slides'],
            [['N'], "Notes de l'orateur"],
            [['T'], 'Thème clair / sombre'],
            [['P'], 'Minuteur : lecture / pause'],
            [['Maj', 'R'], 'Réinitialiser la base'],
            [['Échap'], 'Fermer / quitter un champ'],
            ];
        @endphp
        <dl class="grid grid-cols-[auto_1fr] items-center gap-x-6 gap-y-2.5 text-[14px]">
            @foreach ($shortcuts as [$keys, $label])
                <dt class="flex gap-1">@foreach ($keys as $key)<span class="kbd">{{ $key }}</span>@endforeach</dt>
                <dd class="text-muted">{{ $label }}</dd>
            @endforeach
        </dl>
        <p class="mt-5 border-t border-line pt-4 text-[13px] text-faint">
            Les raccourcis sont désactivés pendant la saisie dans un champ ou l'éditeur JSON (sauf Ctrl + Entrée et Échap).
        </p>
    </div>
</div>
