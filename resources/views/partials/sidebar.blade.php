{{-- Navigation latérale repliable : accès direct à chaque slide --}}
<aside class="flex w-[272px] flex-none flex-col border-r border-line bg-panel-2"
       x-show="$store.deck.sidebar && !$store.deck.fullscreen"
       x-transition:enter="transition-[opacity] duration-150"
       x-transition:enter-start="opacity-0"
       aria-label="Plan de la présentation">
    <div class="flex h-[52px] flex-none items-center gap-2.5 border-b border-line px-4">
        <span class="grid size-7 place-items-center rounded-md bg-brand font-mono text-[15px] font-bold text-white">C</span>
        <span class="font-mono text-[15px] font-bold tracking-wide">CRUDEMO</span>
        <span class="ml-auto font-mono text-[11px] text-faint">Laravel {{ app()->version() }}</span>
    </div>

    <nav class="flex-1 overflow-y-auto px-2.5 py-3">
        <button type="button"
                class="sidebar-item mb-2 flex w-full items-center gap-2.5 rounded-md px-2.5 py-1.5 text-left text-[13.5px] text-muted hover:bg-hover hover:text-fg"
                :class="{ 'is-active': !$store.deck.started }"
                @click="$store.deck.showCover()">
            <span x-icon="presentation" class="text-[15px]"></span>
            Écran d'accueil
        </button>

        @foreach ($sections as $key => $section)
            @php($items = collect($slides)->where('section', $key))
            <div class="mb-1 mt-3 flex items-center gap-2 px-2.5">
                <span class="text-[11px] font-bold uppercase tracking-[0.1em] {{ in_array($key, ['intro', 'conclusion']) ? 'text-faint' : 'text-brand' }}">{{ $section['label'] }}</span>
                @isset($section['fr'])
                    <span class="text-[11px] text-faint">· {{ $section['fr'] }}</span>
                @endisset
                <span class="ml-auto font-mono text-[11px] text-faint">{{ $section['minutes'] }} min</span>
            </div>
            @foreach ($items as $item)
                <button type="button"
                        class="sidebar-item flex w-full items-center gap-2.5 rounded-md px-2.5 py-[5px] text-left text-[13.5px] text-muted hover:bg-hover hover:text-fg"
                        :class="{ 'is-active': $store.deck.started && $store.deck.current === {{ $item['number'] }} }"
                        @click="$store.deck.go({{ $item['number'] }})">
                    <span class="num w-5 flex-none text-right font-mono text-[12px] text-faint">{{ $item['number'] }}</span>
                    <span class="{{ $item['type'] === 'method' ? 'font-mono text-[13px]' : '' }} truncate">{{ $item['title'] }}</span>
                </button>
            @endforeach
        @endforeach
    </nav>

    <div class="flex-none border-t border-line px-4 py-3 text-[12px] leading-relaxed text-faint">
        <span class="kbd">←</span> <span class="kbd">→</span> naviguer ·
        <span class="kbd">?</span> raccourcis
    </div>
</aside>
