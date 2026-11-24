{{-- Barre supérieure : fil d'Ariane, minuteur, actions globales (masquée en plein écran) --}}
<header class="flex h-[52px] flex-none items-center gap-3 border-b border-line bg-panel-2 px-3"
        x-show="!$store.deck.fullscreen">
    <button type="button" class="btn btn-ghost btn-icon" @click="$store.deck.toggleSidebar()" title="Plan des slides (S)">
        <span x-icon.dynamic="$store.deck.sidebar ? 'panel-left-close' : 'panel-left'" class="text-[18px]"></span>
    </button>

    <template x-if="!$store.deck.sidebar">
        <span class="font-mono text-[15px] font-bold tracking-wide">CRUDEMO</span>
    </template>

    <div class="flex min-w-0 items-center gap-2 text-[14px]">
        <template x-if="$store.deck.started">
            <div class="flex min-w-0 items-center gap-2">
                <span class="font-semibold text-brand" x-text="$store.deck.section.label"></span>
                <span x-icon="chevron-right" class="text-faint"></span>
                <span class="truncate font-medium" x-text="$store.deck.slide.title"></span>
            </div>
        </template>
        <template x-if="!$store.deck.started">
            <span class="text-muted">Écran d'accueil</span>
        </template>
    </div>

    <div class="ml-auto flex items-center gap-1.5">
        @include('partials.timer')

        <span class="mx-1.5 h-6 w-px bg-line"></span>

        <button type="button" class="btn" @click="$store.deck.resetDatabase()" :disabled="$store.deck.resetting"
                title="Restaure les 12 produits d'origine (Maj + R)">
            <span x-icon.dynamic="$store.deck.resetting ? 'loader-circle' : 'database'" :class="{ 'spin': $store.deck.resetting }" class="text-[16px] text-brand"></span>
            Réinitialiser la base
        </button>
        <button type="button" class="btn btn-ghost btn-icon" @click="$store.deck.toggleNotes()" :class="{ '!text-brand': $store.deck.notes }" title="Notes de l'orateur (N)">
            <span x-icon="notebook-pen" class="text-[18px]"></span>
        </button>
        <button type="button" class="btn btn-ghost btn-icon" @click="$store.deck.toggleTheme()" title="Thème clair / sombre (T)">
            <span x-icon.dynamic="$store.deck.theme === 'dark' ? 'sun' : 'moon'" class="text-[18px]"></span>
        </button>
        <button type="button" class="btn btn-ghost btn-icon" @click="$store.deck.help = true" title="Raccourcis clavier (?)">
            <span x-icon="keyboard" class="text-[18px]"></span>
        </button>
        <button type="button" class="btn btn-ghost btn-icon" @click="$store.deck.toggleFullscreen()" title="Plein écran (F)">
            <span x-icon="maximize" class="text-[18px]"></span>
        </button>
    </div>
</header>
