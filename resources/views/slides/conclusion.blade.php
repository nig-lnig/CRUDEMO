{{-- Slide 24 — Choisir la bonne méthode Eloquent --}}
@php
    $choices = [
        ['crud' => 'C', 'need' => 'Créer un produit à partir de données validées', 'method' => 'create()'],
        ['crud' => 'C', 'need' => 'Créer seulement s\'il n\'existe pas encore', 'method' => 'firstOrCreate()'],
        ['crud' => 'C', 'need' => 'Créer ou mettre à jour (synchronisation)', 'method' => 'updateOrCreate()'],
        ['crud' => 'C', 'need' => 'Insérer un gros lot, sans événements', 'method' => 'insert()'],
        ['crud' => 'R', 'need' => 'Lire la ressource désignée dans l\'URL', 'method' => 'findOrFail()'],
        ['crud' => 'R', 'need' => 'Lister une table qui peut grandir', 'method' => 'paginate()'],
        ['crud' => 'R', 'need' => 'Filtrer selon des critères', 'method' => 'where()->get()'],
        ['crud' => 'U', 'need' => 'Modifier un produit avec des données validées', 'method' => 'update()'],
        ['crud' => 'U', 'need' => 'Modifier toutes les lignes d\'un filtre', 'method' => 'where()->update()'],
        ['crud' => 'U', 'need' => 'Faire varier un compteur ou un stock', 'method' => 'increment() / decrement()'],
        ['crud' => 'D', 'need' => 'Supprimer en gardant une restauration possible', 'method' => 'delete() + SoftDeletes'],
        ['crud' => 'D', 'need' => 'Effacer définitivement (cas exceptionnel)', 'method' => 'forceDelete()'],
    ];
    $practices = [
        ['icon' => 'shield-check', 'text' => 'Validez avant d\'écrire', 'detail' => '$request->validated(), jamais $request->all()'],
        ['icon' => 'circle-question-mark', 'text' => 'Préférez findOrFail()', 'detail' => 'quand une ressource absente doit donner un 404'],
        ['icon' => 'layers', 'text' => 'Paginez les grandes collections', 'detail' => 'paginate() plutôt que all()'],
        ['icon' => 'git-compare-arrows', 'text' => 'firstOrCreate() / updateOrCreate()', 'detail' => 'au bon moment — avec une contrainte UNIQUE en base'],
        ['icon' => 'zap', 'text' => 'Mises à jour atomiques', 'detail' => 'increment() / decrement() pour les compteurs'],
        ['icon' => 'filter', 'text' => 'Restreignez toujours les opérations de masse', 'detail' => 'un where() obligatoire avant update() / delete()'],
        ['icon' => 'trash', 'text' => 'Logique ≠ définitive', 'detail' => 'delete() masque, forceDelete() efface'],
    ];
@endphp

<div class="flex items-center gap-3">
    <span class="eyebrow">Conclusion</span>
    <span class="ml-auto font-mono text-[14px] text-faint">24 / 25</span>
</div>
<h1 class="mt-2 text-[54px] font-extrabold leading-tight tracking-tight">Choisir la bonne méthode Eloquent</h1>

<div class="mt-6 grid min-h-0 flex-1 grid-cols-[1fr_600px] gap-8">
    <div class="panel overflow-hidden">
        <div class="grid grid-cols-[46px_1fr_auto] items-center border-b border-line bg-panel-2 px-5 py-2.5 text-[13px] font-bold uppercase tracking-[0.06em] text-faint">
            <span></span><span>Je veux…</span><span>J'utilise</span>
        </div>
        @foreach ($choices as $choice)
            <div class="grid grid-cols-[46px_1fr_auto] items-center border-b border-line px-5 py-[9px] last:border-0 {{ ! $loop->first && $choices[$loop->index - 1]['crud'] !== $choice['crud'] ? 'border-t-2 border-t-line-strong' : '' }}">
                <span class="font-mono text-[20px] font-extrabold text-brand">{{ $choice['crud'] }}</span>
                <span class="text-[17.5px]">{{ $choice['need'] }}</span>
                <span class="font-mono text-[16.5px] font-bold">{{ $choice['method'] }}</span>
            </div>
        @endforeach
    </div>

    <div class="flex flex-col">
        <h2 class="mb-4 text-[22px] font-bold">7 bonnes pratiques à retenir</h2>
        <ol class="space-y-2.5">
            @foreach ($practices as $i => $practice)
                <li class="flex items-start gap-4 rounded-xl border border-line bg-panel px-4 py-3">
                    <span class="grid size-9 flex-none place-items-center rounded-lg bg-brand-soft font-mono text-[16px] font-bold text-brand">{{ $i + 1 }}</span>
                    <span>
                        <span class="block text-[18px] font-bold leading-snug">{{ $practice['text'] }}</span>
                        <span class="block text-[15px] text-muted">{{ $practice['detail'] }}</span>
                    </span>
                </li>
            @endforeach
        </ol>
    </div>
</div>
