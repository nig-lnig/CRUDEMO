{{-- Retour visuel (réinitialisation de la base, erreurs) --}}
<div class="pointer-events-none fixed bottom-16 right-6 z-50" aria-live="polite">
    <template x-if="$store.deck.toast">
        <div class="toast panel pointer-events-auto flex max-w-[460px] items-start gap-3 px-4 py-3 shadow-2xl"
             :class="$store.deck.toast.tone === 'ok' ? 'border-ok' : 'border-err'">
            <span x-icon.dynamic="$store.deck.toast.tone === 'ok' ? 'circle-check-big' : 'triangle-exclamation-point'"
                  class="mt-0.5 text-[18px]" :class="$store.deck.toast.tone === 'ok' ? 'text-ok' : 'text-err'"></span>
            <p class="text-[14px] leading-snug" x-text="$store.deck.toast.message"></p>
        </div>
    </template>
</div>
