{{-- Slide 3 — Cartographie des routes : de la requête HTTP à la réponse JSON --}}
@php
    $flow = [
        ['icon' => 'send', 'title' => 'Requête HTTP', 'detail' => 'GET /api/products/1'],
        ['icon' => 'route', 'title' => 'Route', 'detail' => 'routes/api.php'],
        ['icon' => 'file-code', 'title' => 'Contrôleur', 'detail' => 'ProductController@show'],
        ['icon' => 'box', 'title' => 'Eloquent', 'detail' => 'Product::findOrFail($id)'],
        ['icon' => 'database', 'title' => 'Base de données', 'detail' => 'SELECT … WHERE id = ?'],
        ['icon' => 'braces', 'title' => 'Réponse JSON', 'detail' => '200 OK · {"id": 1, …}'],
    ];
    $crudColor = ['Create' => 'text-[var(--post)]', 'Read' => 'text-[var(--get)]', 'Update' => 'text-[var(--patch)]', 'Delete' => 'text-[var(--delete)]'];
@endphp

<div class="flex items-center gap-3">
    <span class="eyebrow">Introduction</span>
    <span class="ml-auto font-mono text-[14px] text-faint">03 / 25</span>
</div>
<h1 class="mt-2 text-[54px] font-extrabold leading-tight tracking-tight">Cartographie des routes</h1>

<div x-data="routeDemo()" class="mt-6 flex min-h-0 flex-1 flex-col">
    {{-- Trajet d'une requête --}}
    <div class="grid grid-cols-6 gap-3">
        @foreach ($flow as $i => $node)
            <div class="panel relative px-4 py-4 transition-colors duration-150"
                 :class="step >= {{ $i }} ? '!border-brand bg-brand-soft' : ''">
                <div class="flex items-center gap-2">
                    <span x-icon="{{ $node['icon'] }}" class="text-[18px]" :class="step >= {{ $i }} ? 'text-brand' : 'text-muted'"></span>
                    <span class="text-[16px] font-bold">{{ $node['title'] }}</span>
                </div>
                <p class="mt-2 truncate font-mono text-[13.5px] text-muted">{{ $node['detail'] }}</p>
                @if (! $loop->last)
                    <span x-icon="chevron-right" class="absolute -right-[13px] top-1/2 z-10 -translate-y-1/2 rounded-full bg-bg text-[16px] text-faint"></span>
                @endif
            </div>
        @endforeach
    </div>

    <div class="mt-6 grid min-h-0 flex-1 grid-cols-[1fr_560px] gap-7">
        {{-- Table des routes, lue depuis le routeur Laravel --}}
        <div class="flex min-h-0 flex-col">
            <div class="mb-3 flex items-baseline gap-3">
                <h2 class="text-[20px] font-bold">API REST du projet</h2>
                <code class="font-mono text-[14px] text-muted">Route::apiResource('products', ProductController::class)</code>
            </div>
            <div class="panel overflow-hidden">
                <table class="w-full text-[16px]">
                    <thead class="bg-panel-2 text-left text-[13px] uppercase tracking-[0.06em] text-faint">
                        <tr>
                            <th class="px-4 py-2.5">CRUD</th>
                            <th class="px-4 py-2.5">Méthode</th>
                            <th class="px-4 py-2.5">Endpoint</th>
                            <th class="px-4 py-2.5">Contrôleur</th>
                            <th class="px-4 py-2.5">Eloquent</th>
                        </tr>
                    </thead>
                    <tbody class="font-mono">
                        @foreach ($apiRoutes as $route)
                            <tr class="border-t border-line">
                                <td class="px-4 py-2.5 font-sans font-bold {{ $crudColor[$route['crud']] ?? '' }}">{{ $route['crud'] }}</td>
                                <td class="m-{{ $route['method'] }} px-4 py-2.5 font-extrabold">{{ $route['method'] }}</td>
                                <td class="px-4 py-2.5">{{ $route['uri'] }}</td>
                                <td class="px-4 py-2.5 text-muted">{{ $route['action'] }}</td>
                                <td class="px-4 py-2.5 font-semibold text-brand">{{ $route['eloquent'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="mt-4 flex items-start gap-2.5 text-[16px] leading-snug text-muted">
                <span x-icon="info" class="mt-[3px] text-brand"></span>
                <span>Dans la suite, chaque slide utilise une route dédiée <span class="font-mono text-fg">/api/demo/…</span>
                    pour isoler <span class="font-semibold text-fg">une seule méthode Eloquent</span> — 20 routes, 20 démonstrations.</span>
            </p>
        </div>

        {{-- Essai en direct --}}
        <div class="panel flex min-h-0 flex-col overflow-hidden">
            <div class="flex flex-none items-center gap-2.5 border-b border-line p-3">
                <div class="flex h-10 flex-1 items-center overflow-hidden rounded-lg border border-line bg-code font-mono text-[15px]">
                    <span class="m-GET flex h-full items-center border-r border-line px-3 font-extrabold">GET</span>
                    <span class="pl-3">/api/products/</span>
                    <input class="w-16 bg-transparent font-bold text-brand outline-none" x-model="id" aria-label="ID du produit" @keydown.enter.prevent="run()">
                </div>
                <button type="button" class="btn btn-brand h-10" @click="run()" :disabled="loading">
                    <span x-icon.dynamic="loading ? 'loader-circle' : 'play'" :class="{ 'spin': loading }"></span> Essayer
                </button>
            </div>
            <div class="min-h-0 flex-1 overflow-auto bg-code p-4">
                <template x-if="!result && !error">
                    <p class="text-[15px] text-faint">Cliquez sur « Essayer » pour suivre une vraie requête à travers Laravel.</p>
                </template>
                <template x-if="error"><p class="text-[14px] text-err" x-text="error"></p></template>
                <template x-if="result">
                    <div class="space-y-3 fade-in">
                        <p class="font-mono text-[14px]">
                            <span class="font-bold" :class="result.ok ? 's-2' : 's-5'" x-text="result.status"></span>
                            <span class="text-muted" x-text="` · ${result.ms} ms`"></span>
                        </p>
                        <template x-if="result.sql">
                            <div>
                                <p class="mb-1 text-[12px] font-bold uppercase tracking-[0.08em] text-faint">SQL exécuté</p>
                                <pre class="code-plain !text-[13.5px]"><code x-html="result.sql"></code></pre>
                                <p class="mt-1 font-mono text-[12.5px] text-faint" x-text="'bindings : ' + JSON.stringify(result.bindings)"></p>
                            </div>
                        </template>
                        <div>
                            <p class="mb-1 text-[12px] font-bold uppercase tracking-[0.08em] text-faint">Réponse JSON</p>
                            <pre class="code-plain !text-[13.5px]"><code x-html="result.json"></code></pre>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>
