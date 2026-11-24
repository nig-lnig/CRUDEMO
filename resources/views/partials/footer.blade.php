{{-- Barre inférieure : navigation, progression ; en plein écran elle porte aussi les contrôles essentiels --}}
<footer class="flex h-[46px] flex-none items-center gap-3 border-t border-line bg-panel-2 px-3">
    <button type="button" class="btn btn-ghost btn-icon" @click="$store.deck.prev()" :disabled="!$store.deck.started || $store.deck.current === 1" title="Slide précédente (←)">
        <span x-icon="chevron-left" class="text-[20px]"></span>
    </button>
    <span class="w-[74px] text-center font-mono text-[14px] tabular-nums">
        <span class="font-bold" x-text="$store.deck.started ? String($store.deck.current).padStart(2, '0') : '00'"></span><span class="text-faint"> / {{ count($slides) }}</span>
    </span>
    <button type="button" class="btn btn-ghost btn-icon" @click="$store.deck.next()" :disabled="$store.deck.started && $store.deck.current === $store.deck.total" title="Slide suivante (→)">
        <span x-icon="chevron-right" class="text-[20px]"></span>
    </button>

    {{-- Barre de progression segmentée par section --}}
    <div class="relative flex h-2 flex-1 items-center gap-[3px]" aria-label="Progression">
        @foreach ($slides as $slide)
            <button type="button"
                    class="h-full flex-1 rounded-[2px] transition-colors duration-200"
                    :class="$store.deck.started && $store.deck.current >= {{ $slide['number'] }} ? 'bg-brand' : 'bg-hover hover:bg-line-strong'"
                    @click="$store.deck.go({{ $slide['number'] }})"
                    title="{{ $slide['number'] }}. {{ $slide['title'] }}"></button>
            @if (! $loop->last && $slides[$loop->index + 1]['section'] !== $slide['section'])
                <span class="mx-[3px] h-3 w-px bg-line-strong"></span>
            @endif
        @endforeach
    </div>

    <span class="w-[110px] truncate text-right text-[13px] font-semibold text-muted" x-show="!$store.deck.fullscreen"
          x-text="$store.deck.started ? $store.deck.section.label : 'Accueil'"></span>

    {{-- Contrôles visibles uniquement en plein écran --}}
    <template x-if="$store.deck.fullscreen">
        <div class="flex items-center gap-1.5">
            @include('partials.timer')
            <button type="button" class="btn btn-ghost btn-icon" @click="$store.deck.resetDatabase()" title="Réinitialiser la base (Maj + R)">
                <span x-icon.dynamic="$store.deck.resetting ? 'loader-circle' : 'database'" :class="{ 'spin': $store.deck.resetting }" class="text-[17px] text-brand"></span>
            </button>
            <button type="button" class="btn btn-ghost btn-icon" @click="$store.deck.toggleNotes()" title="Notes (N)">
                <span x-icon="notebook-pen" class="text-[17px]"></span>
            </button>
            <button type="button" class="btn btn-ghost btn-icon" @click="$store.deck.toggleTheme()" title="Thème (T)">
                <span x-icon.dynamic="$store.deck.theme === 'dark' ? 'sun' : 'moon'" class="text-[17px]"></span>
            </button>
            <button type="button" class="btn btn-ghost btn-icon" @click="$store.deck.toggleFullscreen()" title="Quitter le plein écran (F)">
                <span x-icon="minimize" class="text-[17px]"></span>
            </button>
        </div>
    </template>
</footer>
