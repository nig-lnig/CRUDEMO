{{-- Notes de l'orateur (optionnelles) --}}
<section class="flex h-[150px] flex-none gap-6 border-t border-line bg-panel px-6 py-4"
         x-show="$store.deck.notes" x-cloak aria-label="Notes de l'orateur">
    <div class="w-[190px] flex-none">
        <div class="eyebrow mb-2 flex items-center gap-2 !text-[12px]">
            <span x-icon="notebook-pen"></span> Notes
        </div>
        <template x-if="$store.deck.started">
            <div class="space-y-1 text-[13px] text-muted">
                <div>Durée cible : <span class="font-mono font-semibold text-fg" x-text="$store.deck.slide.duration + ' s'"></span></div>
                <div>Prévu à : <span class="font-mono font-semibold text-fg" x-text="$store.deck.clock($store.deck.plannedAt)"></span></div>
                <div>Section : <span class="font-semibold text-fg" x-text="`${$store.deck.section.label} (${$store.deck.section.minutes} min)`"></span></div>
            </div>
        </template>
    </div>
    <p class="flex-1 overflow-y-auto text-[17px] leading-relaxed"
       x-text="$store.deck.started ? $store.deck.slide.notes : 'Présentez CRUDEMO en une phrase, puis appuyez sur Entrée : le minuteur démarre automatiquement.'"></p>
</section>
