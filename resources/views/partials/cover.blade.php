{{-- Écran d'introduction CRUDEMO (hors numérotation des 25 slides) --}}
<section class="slide items-center justify-center text-center" x-show="!$store.deck.started" x-transition.opacity>
    <div class="absolute inset-x-0 top-0 h-[3px] bg-brand"></div>

    <div class="mb-10 flex items-center gap-3 rounded-full border border-line px-4 py-1.5 text-[15px] text-muted">
        <span class="size-2 rounded-full bg-ok"></span>
        Application Laravel {{ app()->version() }} · base SQLite réelle · {{ count($slides) }} slides
    </div>

    <h1 class="font-mono text-[132px] font-extrabold leading-none tracking-tight">
        CRUD<span class="text-brand">EMO</span>
    </h1>
    <p class="mt-7 text-[34px] font-semibold">Le CRUD avec Laravel Eloquent</p>
    <p class="mt-3 max-w-[900px] text-[21px] leading-relaxed text-muted">
        Une présentation interactive : chaque méthode est expliquée, puis exécutée en direct
        contre une vraie base de données — requête HTTP, SQL et table à l'appui.
    </p>

    <div class="mt-14 grid grid-cols-4 gap-4">
        @foreach ([['C', 'Create', 'POST'], ['R', 'Read', 'GET'], ['U', 'Update', 'PATCH'], ['D', 'Delete', 'DELETE']] as [$letter, $label, $verb])
            <div class="panel flex w-[200px] items-center gap-4 px-5 py-4 text-left">
                <span class="font-mono text-[40px] font-extrabold text-brand">{{ $letter }}</span>
                <span>
                    <span class="block text-[18px] font-bold">{{ $label }}</span>
                    <span class="m-{{ $verb }} font-mono text-[13px] font-bold">{{ $verb }}</span>
                </span>
            </div>
        @endforeach
    </div>

    <button type="button" class="btn btn-brand mt-14 h-14 px-8 text-[19px]" @click="$store.deck.start()">
        Commencer la présentation
        <span x-icon="arrow-right" class="text-[20px]"></span>
    </button>
    <p class="mt-4 text-[15px] text-faint">
        <span class="kbd">Entrée</span> pour démarrer — le minuteur de 20 minutes se lance automatiquement
    </p>
</section>
