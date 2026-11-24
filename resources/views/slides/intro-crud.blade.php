{{-- Slide 1 — Introduction au CRUD --}}
@php
    $operations = [
        ['letter' => 'C', 'en' => 'Create', 'fr' => 'Créer', 'action' => 'Ajouter un nouveau produit au catalogue', 'http' => 'POST', 'sql' => 'INSERT', 'eloquent' => 'Product::create($data)', 'icon' => 'plus'],
        ['letter' => 'R', 'en' => 'Read', 'fr' => 'Lire', 'action' => 'Afficher la liste ou la fiche d\'un produit', 'http' => 'GET', 'sql' => 'SELECT', 'eloquent' => 'Product::find($id)', 'icon' => 'eye'],
        ['letter' => 'U', 'en' => 'Update', 'fr' => 'Modifier', 'action' => 'Changer le prix ou le stock d\'un produit', 'http' => 'PATCH', 'sql' => 'UPDATE', 'eloquent' => '$product->update($data)', 'icon' => 'pencil'],
        ['letter' => 'D', 'en' => 'Delete', 'fr' => 'Supprimer', 'action' => 'Retirer un produit du catalogue', 'http' => 'DELETE', 'sql' => 'DELETE', 'eloquent' => '$product->delete()', 'icon' => 'trash'],
    ];
@endphp

<div class="flex items-center gap-3">
    <span class="eyebrow">Introduction</span>
    <span class="ml-auto font-mono text-[14px] text-faint">01 / 25</span>
</div>
<h1 class="mt-2 text-[54px] font-extrabold leading-tight tracking-tight">Introduction au CRUD</h1>
<p class="mt-2 max-w-[1100px] text-[23px] leading-snug text-muted">
    Quatre opérations suffisent pour gérer presque toutes les données d'une application.
    Exemple fil rouge : <span class="font-semibold text-fg">le catalogue de produits d'une boutique</span>.
</p>

<div class="relative mt-10 grid flex-1 grid-cols-4 gap-6">
    @foreach ($operations as $op)
        <div class="panel relative flex flex-col overflow-hidden">
            <div class="flex items-start justify-between px-6 pt-6">
                <span class="font-mono text-[96px] font-extrabold leading-[0.85] text-brand">{{ $op['letter'] }}</span>
                <span class="grid size-11 place-items-center rounded-xl bg-brand-soft text-brand">
                    <span x-icon="{{ $op['icon'] }}" class="text-[22px]"></span>
                </span>
            </div>
            <div class="px-6 pt-4">
                <p class="text-[30px] font-bold leading-none">{{ $op['en'] }}</p>
                <p class="mt-1.5 text-[18px] font-medium text-muted">{{ $op['fr'] }}</p>
                <p class="mt-4 min-h-[56px] text-[18px] leading-snug">{{ $op['action'] }}</p>
            </div>

            <dl class="mt-auto space-y-0 border-t border-line font-mono text-[16px]">
                <div class="flex items-center justify-between border-b border-line px-6 py-3">
                    <dt class="font-sans text-[14px] font-semibold text-faint">HTTP</dt>
                    <dd class="m-{{ $op['http'] }} font-extrabold">{{ $op['http'] }}</dd>
                </div>
                <div class="flex items-center justify-between border-b border-line px-6 py-3">
                    <dt class="font-sans text-[14px] font-semibold text-faint">SQL</dt>
                    <dd class="font-bold text-[var(--hl-keyword)]">{{ $op['sql'] }}</dd>
                </div>
                <div class="px-6 py-3.5">
                    <dt class="font-sans text-[14px] font-semibold text-faint">Eloquent</dt>
                    <dd class="mt-1 truncate text-[16.5px] font-semibold">{{ $op['eloquent'] }}</dd>
                </div>
            </dl>
        </div>
    @endforeach
</div>

<div class="mt-6 flex items-center justify-center gap-4 text-[17px] text-muted">
    <span class="flex items-center gap-2"><span x-icon="database" class="text-brand"></span> Une seule table :</span>
    <span class="rounded-md border border-line bg-panel px-3 py-1 font-mono font-semibold text-fg">products</span>
    <span x-icon="arrow-right" class="text-faint"></span>
    <span>un modèle Eloquent :</span>
    <span class="rounded-md border border-line bg-panel px-3 py-1 font-mono font-semibold text-fg">App\Models\Product</span>
    <span x-icon="arrow-right" class="text-faint"></span>
    <span><span class="font-semibold text-fg">20 méthodes</span> démontrées en direct</span>
</div>
