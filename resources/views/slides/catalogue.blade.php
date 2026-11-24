{{-- Slide 25 — Aller plus loin avec Eloquent : catalogue interactif --}}
<div class="relative flex min-h-0 flex-1 flex-col" x-data="catalogue(@js($catalogue))">
    <div class="flex items-center gap-3">
        <span class="eyebrow">Aller plus loin</span>
        <span class="ml-auto font-mono text-[14px] text-faint">25 / 25</span>
    </div>
    <h1 class="mt-1.5 text-[40px] font-extrabold leading-tight tracking-tight">Maîtriser le CRUD, c'est aussi connaître les bons outils.</h1>
    <p class="mt-1.5 max-w-[1350px] text-[17px] leading-snug text-muted">
        « Nous avons découvert les méthodes essentielles du CRUD. Eloquent propose également de nombreuses méthodes qui permettent
        d'écrire un code plus simple, plus performant et plus fiable. Explorez-les et intégrez-les progressivement dans vos projets. »
    </p>

    {{-- Recherche et filtres --}}
    <div class="mt-4 flex flex-none items-center gap-2.5">
        <label class="relative w-[300px]">
            <span x-icon="search" class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[16px] text-faint"></span>
            <input type="search" class="field !h-9 !pl-9 !font-sans !text-[15px]" placeholder="Rechercher une méthode…" x-model="query" aria-label="Rechercher une méthode">
        </label>

        <div class="flex rounded-lg border border-line p-0.5">
            <button type="button" class="rounded-md px-3 py-1 text-[13.5px] font-semibold" :class="category === 'all' ? 'bg-hover text-fg' : 'text-muted'" @click="category = 'all'">
                Tout <span class="font-mono text-[12px] text-faint" x-text="countFor('all')"></span>
            </button>
            <template x-for="(meta, key) in categories" :key="key">
                <button type="button" class="rounded-md px-3 py-1 text-[13.5px] font-semibold" :class="category === key ? 'bg-brand-soft text-brand' : 'text-muted'" @click="category = key">
                    <span x-text="meta.label"></span> <span class="font-mono text-[12px] text-faint" x-text="countFor(key)"></span>
                </button>
            </template>
        </div>

        <select class="field !h-9 !w-auto !font-sans !text-[13.5px]" x-model="kind" aria-label="Type de méthode">
            <option value="all">Tous les types</option>
            <template x-for="(label, key) in kinds" :key="key"><option :value="key" x-text="label"></option></template>
        </select>

        <button type="button" class="chip !h-8" :class="tag === 'common' ? '!border-brand !text-brand' : ''" @click="tag = tag === 'common' ? 'all' : 'common'">
            <span x-icon="star" class="text-[13px]"></span> Très utilisées
        </button>
        <button type="button" class="chip !h-8" :class="tag === 'underused' ? '!border-info !text-info' : ''" @click="tag = tag === 'underused' ? 'all' : 'underused'">
            <span x-icon="gem" class="text-[13px]"></span> Sous-estimées
        </button>

        <span class="ml-auto font-mono text-[13px] text-faint" x-text="`${filtered.length} méthode${filtered.length > 1 ? 's' : ''}`"></span>
    </div>

    {{-- Catalogue en 4 colonnes CRUD --}}
    <div class="mt-3 grid min-h-0 flex-1 grid-cols-5 gap-3">
        <template x-for="group in groups" :key="group.key">
            <div class="flex min-h-0 flex-col rounded-xl border border-line bg-panel-2" :style="`grid-column: span ${group.span}`">
                <div class="flex flex-none items-center gap-2 border-b border-line px-3 py-2">
                    <span class="font-mono text-[20px] font-extrabold text-brand" x-text="group.label[0]"></span>
                    <span class="text-[15px] font-bold" x-text="group.label"></span>
                    <span class="text-[13px] text-muted" x-text="'· ' + group.fr"></span>
                    <span class="ml-auto font-mono text-[12px] text-faint" x-text="group.items.length"></span>
                </div>
                <div class="grid min-h-0 flex-1 content-start gap-1.5 overflow-y-auto p-2" :style="`grid-template-columns: repeat(${group.columns}, minmax(0, 1fr))`">
                    <template x-for="item in group.items" :key="group.key + item.name">
                        <button type="button"
                                class="group flex items-center gap-2 rounded-lg border border-line bg-panel px-2.5 py-[7px] text-left transition-colors hover:border-brand/60"
                                :class="{ '!border-brand bg-brand-soft': selected && selected.name === item.name }"
                                @click="open(item)">
                            <span class="min-w-0 flex-1 truncate font-mono text-[13.5px] font-semibold" x-text="item.name" :title="item.name"></span>
                            <span x-show="item.tag === 'common'" x-icon="star" class="text-[12px] text-brand" title="Très utilisée"></span>
                            <span x-show="item.tag === 'underused'" x-icon="gem" class="text-[12px] text-info" title="Sous-estimée mais précieuse"></span>
                            <span class="flex gap-[2px]" :title="levels[item.level]">
                                <template x-for="n in 3" :key="n">
                                    <span class="h-2.5 w-[3px] rounded-full" :class="n <= item.level ? 'bg-muted' : 'bg-line'"></span>
                                </template>
                            </span>
                        </button>
                    </template>
                    <template x-if="group.items.length === 0">
                        <p class="px-1 py-2 text-[13px] text-faint">Aucun résultat.</p>
                    </template>
                </div>
            </div>
        </template>
    </div>

    {{-- Message de clôture --}}
    <div class="mt-3 flex flex-none items-center gap-6 rounded-xl border border-brand/40 bg-brand-soft px-5 py-3">
        <p class="flex-1 text-[17px] font-medium italic leading-snug">
            « Un bon développeur ne mémorise pas toutes les méthodes. Il comprend leur fonctionnement et sait choisir la bonne méthode au bon moment. »
        </p>
        <a href="{{ $docsUrl }}" target="_blank" rel="noopener" class="btn btn-brand h-10 flex-none">
            <span x-icon="book-open" class="text-[17px]"></span>
            Explorer la documentation Eloquent
            <span class="font-mono text-[12px] opacity-80">{{ $docsVersion }}</span>
        </a>
    </div>

    {{-- Panneau de détail --}}
    <div class="absolute inset-y-0 right-0 z-20 flex w-[640px] flex-col overflow-hidden rounded-xl border border-line-strong bg-panel shadow-2xl"
         x-show="selected" x-cloak
         x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="translate-x-6 opacity-0"
         x-transition:leave="transition duration-100" x-transition:leave-end="opacity-0">
        <template x-if="selected">
            <div class="flex min-h-0 flex-1 flex-col">
                <div class="flex flex-none items-start gap-3 border-b border-line px-6 py-4">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-1.5">
                            <template x-for="c in selected.categories" :key="c">
                                <span class="chip !h-6 !border-brand/50 !text-[12px] !text-brand" x-text="categories[c].label"></span>
                            </template>
                            <span class="chip !h-6 !text-[12px]" x-text="kinds[selected.kind]"></span>
                            <span class="chip !h-6 !text-[12px]" x-text="levels[selected.level]"></span>
                            <span class="chip !h-6 !border-brand/50 !text-[12px] !text-brand" x-show="selected.tag === 'common'">Très utilisée</span>
                            <span class="chip !h-6 !border-info/50 !text-[12px] !text-info" x-show="selected.tag === 'underused'">Sous-estimée</span>
                        </div>
                        <h2 class="mt-2 font-mono text-[28px] font-extrabold" x-text="selected.name"></h2>
                    </div>
                    <button type="button" class="btn btn-ghost btn-icon" @click="selected = null" aria-label="Fermer">
                        <span x-icon="x" class="text-[18px]"></span>
                    </button>
                </div>

                <div class="min-h-0 flex-1 space-y-4 overflow-y-auto px-6 py-4 text-[15px] leading-snug">
                    <p class="text-[17px]" x-text="selected.description"></p>

                    <div>
                        <p class="mb-1.5 text-[12px] font-bold uppercase tracking-[0.08em] text-faint">Syntaxe</p>
                        <div class="code-block"><pre class="!py-2.5 !text-[14px]"><code x-html="syntaxHtml"></code></pre></div>
                    </div>
                    <div>
                        <p class="mb-1.5 text-[12px] font-bold uppercase tracking-[0.08em] text-faint">Exemple</p>
                        <div class="code-block"><pre class="!py-2.5 !text-[14px]"><code x-html="exampleHtml"></code></pre></div>
                    </div>

                    <dl class="grid grid-cols-[150px_1fr] gap-x-4 gap-y-3">
                        <dt class="font-semibold text-muted">Retour</dt>
                        <dd class="font-mono text-brand" x-text="selected.returns"></dd>
                        <dt class="font-semibold text-muted">Quand l'utiliser</dt>
                        <dd x-text="selected.when"></dd>
                        <dt class="flex items-start gap-1.5 font-semibold text-warn"><span x-icon="triangle-exclamation-point" class="mt-[3px]"></span>Erreur fréquente</dt>
                        <dd x-text="selected.mistakes"></dd>
                        <dt class="flex items-start gap-1.5 font-semibold text-ok"><span x-icon="shield-check" class="mt-[3px]"></span>Bonne pratique</dt>
                        <dd x-text="selected.practices"></dd>
                        <dt class="font-semibold text-muted">Voir aussi</dt>
                        <dd class="flex flex-wrap gap-1.5">
                            <template x-for="r in selected.related" :key="r">
                                <button type="button" class="rounded-md border border-line px-2 py-0.5 font-mono text-[13px]"
                                        :class="isRelatedAvailable(r) ? 'hover:border-brand hover:text-brand' : 'cursor-default text-muted'"
                                        @click="openRelated(r)" x-text="r"></button>
                            </template>
                        </dd>
                    </dl>
                </div>

                <div class="flex flex-none items-center border-t border-line px-6 py-3">
                    <a :href="selected.doc" target="_blank" rel="noopener" class="btn">
                        <span x-icon="external-link" class="text-[15px] text-brand"></span>
                        Documentation officielle
                    </a>
                    <span class="ml-auto font-mono text-[12px] text-faint">Vérifiée sur Laravel {{ app()->version() }}</span>
                </div>
            </div>
        </template>
    </div>
</div>
