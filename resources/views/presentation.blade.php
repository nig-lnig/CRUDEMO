<!DOCTYPE html>
<html lang="fr" data-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CRUDEMO — Le CRUD avec Laravel Eloquent</title>
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect width='32' height='32' rx='8' fill='%23FF6C37'/%3E%3Ctext x='16' y='22' font-family='monospace' font-size='16' font-weight='700' text-anchor='middle' fill='white'%3EC%3C/text%3E%3C/svg%3E">
    <script>
        // Applique le thème avant le premier rendu (évite un flash clair/sombre)
        try { document.documentElement.dataset.theme = JSON.parse(localStorage.getItem('crudemo.theme')) || 'dark'; } catch (e) {}
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full overflow-hidden" x-data>
    @php
        $deckData = [
            'slides' => collect($slides)->map(fn ($s) => collect($s)->only(['number', 'id', 'type', 'section', 'title', 'duration', 'notes'])),
            'sections' => $sections,
        ];
    @endphp
    <script type="application/json" id="crudemo-data">@json($deckData)</script>

    <div class="flex h-full">
        @include('partials.sidebar')

        <div class="flex min-w-0 flex-1 flex-col">
            @include('partials.topbar')

            <main class="stage-host flex-1"
                  x-init="$store.deck.fit($el); new ResizeObserver(() => $store.deck.fit($el)).observe($el)">
                <div class="stage"
                     :style="`transform: translate(${$store.deck.stage.x}px, ${$store.deck.stage.y}px) scale(${$store.deck.stage.scale})`">

                    @include('partials.cover')

                    @foreach ($slides as $slide)
                        <section class="slide"
                                 id="slide-{{ $slide['number'] }}"
                                 data-slide="{{ $slide['number'] }}"
                                 data-type="{{ $slide['type'] }}"
                                 x-show="$store.deck.started && $store.deck.current === {{ $slide['number'] }}"
                                 x-transition:enter="slide-enter"
                                 x-cloak>
                            @include('slides.'.$slide['type'], ['slide' => $slide])
                        </section>
                    @endforeach
                </div>
            </main>

            @include('partials.notes')
            @include('partials.footer')
        </div>
    </div>

    @include('partials.help')
    @include('partials.toast')
</body>
</html>
