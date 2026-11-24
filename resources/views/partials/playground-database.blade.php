{{-- État de la table products avant / après la requête (lignes concernées uniquement) --}}
<template x-if="inspectionError">
    <p class="text-[14px] text-err" x-text="inspectionError"></p>
</template>
<template x-if="!inspection && !inspectionError">
    <p class="flex items-center gap-2 text-[14px] text-muted"><span x-icon="loader-circle" class="spin"></span> Lecture de la table…</p>
</template>

<template x-if="db">
    <div class="space-y-3">
        <div class="flex flex-wrap items-center gap-2 text-[13px]">
            <template x-for="chip in summaryChips" :key="chip.status">
                <span class="chip"
                      :class="{
                        '!border-ok !text-ok': chip.status === 'inserted',
                        '!border-mod !text-mod': chip.status === 'modified',
                        '!border-err !text-err': ['soft_deleted', 'force_deleted'].includes(chip.status),
                        '!border-info !text-info': chip.status === 'restored',
                      }" x-text="chip.label"></span>
            </template>
            <template x-if="!hasChanges">
                <span class="chip" x-text="db.mode === 'read'
                    ? `Lecture seule — ${dbRows.length} ligne${dbRows.length > 1 ? 's' : ''} renvoyée${dbRows.length > 1 ? 's' : ''}`
                    : 'Aucune ligne modifiée'"></span>
            </template>
            <span class="ml-auto font-mono text-[12.5px] text-faint"
                  x-text="`products : ${db.table.after_total} lignes en base (${db.table.after_trashed} dans la corbeille)`"></span>
        </div>

        <template x-if="dbRows.length === 0">
            <p class="rounded-lg border border-line px-4 py-3 text-[15px] text-muted">Aucune ligne concernée par cette requête.</p>
        </template>

        {{-- Écriture : AVANT puis APRÈS --}}
        <template x-if="dbRows.length && hasChanges">
            <div class="space-y-3">
                <template x-for="side in ['before', 'after']" :key="side">
                    <div>
                        <p class="mb-1.5 text-[12px] font-bold uppercase tracking-[0.08em]"
                           :class="side === 'after' ? 'text-brand' : 'text-faint'"
                           x-text="side === 'before' ? 'Avant la requête' : 'Après la requête'"></p>
                        <div class="overflow-x-auto rounded-lg border border-line bg-panel">
                            <table class="db-table">
                                <thead>
                                    <tr>
                                        <th>état</th>
                                        <template x-for="c in dbColumns" :key="c"><th x-text="c"></th></template>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="row in dbRows" :key="side + row.id">
                                        <tr :class="side === 'after' ? 'row-' + row.status : ''">
                                            <td class="font-sans text-[12px] font-semibold"
                                                :class="side === 'before' ? 'text-faint' : {
                                                    'text-ok': row.status === 'inserted',
                                                    'text-mod': row.status === 'modified',
                                                    'text-err': ['soft_deleted', 'force_deleted'].includes(row.status),
                                                    'text-info': row.status === 'restored',
                                                }"
                                                x-text="side === 'before' ? (row.before ? 'existante' : 'absente') : statusLabel(row.status)"></td>
                                            <template x-for="c in dbColumns" :key="c">
                                                <td :class="{
                                                        'cell-changed': side === 'after' && isChanged(row, c),
                                                        'is-null': side === 'before' ? !row.before || isNull(row, c, side) : isNull(row, c, side),
                                                    }"
                                                    :title="side === 'before' ? (row.before ? row.before[c] : '') : ((row.after ?? row.before) ?? {})[c]"
                                                    x-text="side === 'before' && !row.before ? '—' : cell(row, c, side)"></td>
                                            </template>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </template>
            </div>
        </template>

        {{-- Lecture (ou écriture sans effet) : lignes concernées --}}
        <template x-if="dbRows.length && !hasChanges">
            <div class="overflow-x-auto rounded-lg border border-line bg-panel">
                <table class="db-table">
                    <thead>
                        <tr>
                            <template x-for="c in dbColumns" :key="c"><th x-text="c"></th></template>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="row in dbRows" :key="'r' + row.id">
                            <tr>
                                <template x-for="c in dbColumns" :key="c">
                                    <td :class="{ 'is-null': isNull(row, c, 'after') }" :title="row.after[c]" x-text="cell(row, c, 'after')"></td>
                                </template>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </template>

        <p class="text-[12.5px] text-faint" x-show="db.truncated">Affichage limité aux 30 premières lignes.</p>
        <p class="text-[12.5px] text-faint">
            Lignes lues directement dans SQLite (y compris deleted_at). Dates affichées au format JJ/MM hh:mm:ss (survolez une cellule pour la valeur brute).
        </p>
    </div>
</template>
