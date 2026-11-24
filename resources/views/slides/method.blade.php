{{-- Slide de démonstration d'une méthode Eloquent : explication (45 %) + playground API (55 %) --}}
@php
    $sectionLabel = $sections[$slide['section']]['label'];
    $sectionFr = $sections[$slide['section']]['fr'] ?? '';
    [$name, $suffix] = str_contains($slide['method'], '()') ? [str_replace('()', '', $slide['method']), '()'] : [$slide['method'], ''];
@endphp

<div class="grid h-full min-h-0 grid-cols-[45fr_55fr] gap-8" x-data="{ compareOpen: false }"
     @crudemo:escape.window="compareOpen = false">

    {{-- ───────────── Colonne gauche : explication ───────────── --}}
    <div class="relative flex min-h-0 flex-col">
        <div class="flex items-center gap-3">
            <span class="eyebrow">{{ $sectionLabel }}</span>
            <span class="text-[14px] font-semibold text-faint">· {{ $sectionFr }}</span>
            @if ($slide['compare'])
                <button type="button" class="btn ml-auto h-8 text-[13.5px]" @click="compareOpen = true">
                    <span x-icon="git-compare-arrows" class="text-[15px] text-brand"></span>
                    Comparer : <span class="font-mono">{{ $slide['compare']['title'] }}</span>
                </button>
            @endif
        </div>

        <h1 class="mt-2 font-mono text-[50px] font-extrabold leading-[1.1] tracking-tight">
            {{ $name }}<span class="text-brand">{{ $suffix }}</span>
        </h1>

        <p class="mt-3 text-[20px] leading-snug">{{ $slide['definition'] }}</p>

        <p class="mt-3 flex items-start gap-2.5 text-[16.5px] leading-snug text-muted">
            <span x-icon="lightbulb" class="mt-[3px] text-[17px] text-brand"></span>
            <span><span class="font-semibold text-fg">Cas d'usage :</span> {{ $slide['useCase'] }}</span>
        </p>

        {{-- Code réel du contrôleur (extrait par réflexion) --}}
        <div class="code-block mt-4 flex min-h-0 flex-1 flex-col">
            <div class="flex flex-none items-center gap-2 border-b border-line px-4 py-2 text-[13px]">
                <span x-icon="file-code" class="text-faint"></span>
                <span class="font-mono text-muted" title="{{ $slide['sourceFile'] }}">{{ $slide['sourceLabel'] }}</span>
                <span class="ml-auto flex items-center gap-1.5 text-faint">
                    <span class="size-1.5 rounded-full bg-ok"></span> code exécuté par l'API
                </span>
            </div>
            @php
                // Taille du code adaptée au nombre de lignes ET à la ligne la plus longue :
                // le code doit tenir entièrement à l'écran, sans défilement, même projeté.
                $codeLines = explode("\n", $slide['code']);
                $byHeight = count($codeLines) <= 16 ? 0 : (count($codeLines) <= 18 ? 1 : 2);
                $widest = max(array_map('mb_strlen', $codeLines));
                $byWidth = $widest <= 67 ? 0 : ($widest <= 72 ? 1 : ($widest <= 77 ? 2 : 3));
                $codeSize = ['', 'code-md', 'code-sm', 'code-xs'][max($byHeight, $byWidth)];
            @endphp
            <pre class="min-h-0 flex-1 overflow-auto {{ $codeSize }}"><code data-lang="php" data-emphasize='@json($slide['highlight'])'>{{ $slide['code'] }}</code></pre>
        </div>

        <div class="mt-4 flex items-center gap-3">
            <span class="text-[15px] font-semibold text-muted">Retourne</span>
            <span class="rounded-md border border-brand/40 bg-brand-soft px-2.5 py-1 font-mono text-[15px] font-semibold text-brand">{{ $slide['returns'] }}</span>
            <span class="truncate text-[15px] text-muted">{{ $slide['returnsNote'] }}</span>
        </div>

        @php($isMistake = $slide['tip']['kind'] === 'mistake')
        <div class="mt-3 flex gap-3 rounded-xl border px-4 py-3 {{ $isMistake ? 'border-warn/40 bg-warn/8' : 'border-ok/35 bg-ok/8' }}">
            <span x-icon="{{ $isMistake ? 'triangle-exclamation-point' : 'shield-check' }}" class="mt-0.5 text-[19px] {{ $isMistake ? 'text-warn' : 'text-ok' }}"></span>
            <p class="text-[15.5px] leading-snug">
                <span class="font-bold {{ $isMistake ? 'text-warn' : 'text-ok' }}">{{ $isMistake ? 'Erreur fréquente' : 'Bonne pratique' }} —</span>
                {{ $slide['tip']['text'] }}
            </p>
        </div>

        @if ($slide['compare'])
            {{-- Comparaison (superposée à la colonne gauche) --}}
            <div class="panel absolute inset-x-0 bottom-0 z-10 flex flex-col p-6 shadow-2xl fade-in"
                 x-show="compareOpen" x-cloak @click.outside="compareOpen = false">
                <div class="mb-4 flex items-center gap-2.5">
                    <span x-icon="git-compare-arrows" class="text-[20px] text-brand"></span>
                    <h2 class="font-mono text-[22px] font-bold">{{ $slide['compare']['title'] }}</h2>
                    <button type="button" class="btn btn-ghost btn-icon ml-auto" @click="compareOpen = false" aria-label="Fermer">
                        <span x-icon="x" class="text-[18px]"></span>
                    </button>
                </div>
                <table class="w-full text-[17px]">
                    <thead>
                        <tr class="border-b border-line text-left">
                            <th class="w-[34%] pb-2.5"></th>
                            @foreach ($slide['compare']['headers'] as $header)
                                <th class="pb-2.5 font-mono text-[17px] {{ $loop->first ? 'text-muted' : 'text-brand' }}">{{ $header }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($slide['compare']['rows'] as [$label, $a, $b])
                            <tr class="border-b border-line last:border-0">
                                <td class="py-2.5 font-semibold text-muted">{{ $label }}</td>
                                <td class="py-2.5">{{ $a }}</td>
                                <td class="py-2.5 font-semibold">{{ $b }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- ───────────── Colonne droite : playground API ───────────── --}}
    @include('partials.playground', ['slide' => $slide])
</div>
