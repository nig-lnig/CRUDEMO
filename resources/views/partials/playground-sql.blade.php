{{-- SQL réellement exécuté (capturé par DB::listen pendant la requête) --}}
<template x-if="inspectionError">
    <p class="text-[14px] text-err" x-text="inspectionError"></p>
</template>
<template x-if="!inspection && !inspectionError">
    <p class="flex items-center gap-2 text-[14px] text-muted"><span x-icon="loader-circle" class="spin"></span> Chargement du SQL capturé…</p>
</template>

<template x-if="inspection">
    <div class="space-y-3">
        <div class="flex flex-wrap items-center gap-2 text-[13.5px]">
            <span class="chip !border-brand/50 !text-brand">
                <span x-text="operationQueries.length"></span>
                <span x-text="operationQueries.length > 1 ? 'requêtes de l\'opération' : 'requête de l\'opération'"></span>
            </span>
            <span class="chip font-mono" x-text="ms(inspection.stats.operation.time_ms)" title="Somme des durées mesurées par la base"></span>
            <span class="chip font-mono" title="Route Laravel exécutée" x-text="inspection.request.method + ' ' + inspection.request.route"></span>
        </div>

        <template x-if="operationQueries.length === 0">
            <p class="rounded-lg border border-line px-4 py-3 text-[15px] text-muted">
                Aucune requête SQL exécutée par le contrôleur
                <span class="text-faint">(par exemple : validation refusée avant tout accès à la base, ou save() sans modification).</span>
            </p>
        </template>

        <template x-for="(q, i) in operationQueries" :key="'op' + i">
            <div class="overflow-hidden rounded-lg border border-line bg-panel">
                <div class="flex items-center gap-2.5 border-b border-line px-3 py-1.5 font-mono text-[12.5px]">
                    <span class="font-bold text-brand" x-text="'#' + (i + 1)"></span>
                    <span class="rounded bg-hover px-1.5 font-bold" x-text="q.statement"></span>
                    <span class="ml-auto text-muted" x-text="ms(q.time_ms)"></span>
                </div>
                <pre class="code-plain px-3 py-2.5 !text-[14.5px]"><code x-html="sqlHtml(q)"></code></pre>
                <template x-if="q.bindings.length">
                    <div class="flex flex-wrap items-center gap-1.5 border-t border-line px-3 py-2 font-mono text-[12.5px]">
                        <span class="mr-1 font-sans font-semibold text-faint">Bindings</span>
                        <template x-for="(b, j) in q.bindings" :key="j">
                            <span class="rounded border border-line px-1.5 py-0.5">
                                <span class="text-faint" x-text="(j + 1) + ':'"></span>
                                <span class="text-[var(--hl-string)]" x-text="bindingLabel(b)"></span>
                            </span>
                        </template>
                    </div>
                </template>
            </div>
        </template>

        {{-- Requêtes ajoutées par CRUDEMO pour photographier la table --}}
        <div class="border-t border-dashed border-line pt-3">
            <button type="button" class="flex items-center gap-2 text-[13px] font-semibold text-faint hover:text-fg"
                    @click="showInspectionQueries = !showInspectionQueries">
                <span x-icon.dynamic="showInspectionQueries ? 'chevron-down' : 'chevron-right'"></span>
                <span x-text="`${inspectionQueries.length} requêtes d'inspection CRUDEMO (${ms(inspection.stats.inspection.time_ms)}) — hors démonstration`"></span>
            </button>
            <template x-if="showInspectionQueries">
                <div class="mt-2 space-y-2 opacity-70">
                    <template x-for="(q, i) in inspectionQueries" :key="'in' + i">
                        <div class="rounded-lg border border-dashed border-line px-3 py-2">
                            <div class="mb-1 flex font-mono text-[12px] text-faint">
                                <span x-text="i === 0 ? 'Photo AVANT' : 'Photo APRÈS'"></span>
                                <span class="ml-auto" x-text="ms(q.time_ms)"></span>
                            </div>
                            <pre class="code-plain !text-[13px]"><code x-html="sqlHtml(q)"></code></pre>
                        </div>
                    </template>
                </div>
            </template>
        </div>
    </div>
</template>
