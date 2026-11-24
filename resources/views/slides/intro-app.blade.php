{{-- Slide 2 — Présentation de CRUDEMO : les fichiers réellement impliqués dans le CRUD --}}
<div class="flex items-center gap-3">
    <span class="eyebrow">Introduction</span>
    <span class="ml-auto font-mono text-[14px] text-faint">02 / 25</span>
</div>
<h1 class="mt-2 text-[54px] font-extrabold leading-tight tracking-tight">Présentation de CRUDEMO</h1>
<p class="mt-2 text-[22px] text-muted">Seuls les fichiers qui participent au CRUD — cliquez pour voir leur contenu <span class="font-semibold text-fg">réel</span>.</p>

<div class="mt-7 grid min-h-0 flex-1 grid-cols-[520px_1fr] gap-7"
     x-data="{ files: @js($projectFiles), selected: 'model' }">

    {{-- Arborescence --}}
    <div class="panel flex flex-col overflow-hidden">
        <div class="flex items-center gap-2 border-b border-line px-5 py-3 text-[14px] font-semibold text-muted">
            <span x-icon="folder-open" class="text-brand"></span> crudemo/
        </div>
        <div class="flex-1 overflow-y-auto py-2">
            @php($folders = collect($projectFiles)->groupBy('folder'))
            @foreach ($folders as $folder => $files)
                <div class="flex items-center gap-2 px-5 pb-1 pt-3 font-mono text-[14px] text-faint">
                    <span x-icon="folder" class="text-[15px]"></span> {{ $folder }}/
                </div>
                @foreach ($files as $file)
                    <button type="button"
                            class="flex w-full items-center gap-3 py-2 pl-11 pr-5 text-left transition-colors hover:bg-hover"
                            :class="selected === '{{ $file['id'] }}' ? 'bg-brand-soft' : ''"
                            @click="selected = '{{ $file['id'] }}'">
                        <span x-icon="{{ $file['icon'] }}" class="text-[17px]" :class="selected === '{{ $file['id'] }}' ? 'text-brand' : 'text-muted'"></span>
                        <span class="min-w-0">
                            <span class="block truncate font-mono text-[15px] font-semibold">{{ $file['label'] }}</span>
                            <span class="block truncate text-[13px] text-muted">{{ $file['role'] }}</span>
                        </span>
                    </button>
                @endforeach
            @endforeach
        </div>
    </div>

    {{-- Contenu réel du fichier sélectionné --}}
    <template x-for="file in files" :key="file.id">
        <div class="code-block flex min-h-0 flex-col" x-show="selected === file.id">
            <div class="flex flex-none items-center gap-2.5 border-b border-line px-5 py-3">
                <span x-icon="file-code" class="text-faint"></span>
                <span class="font-mono text-[14px] text-muted" x-text="file.path"></span>
                <span class="ml-auto text-[13px] text-faint" x-text="file.code.split('\n').length + ' lignes'"></span>
            </div>
            <pre class="min-h-0 flex-1 overflow-auto !text-[14.5px]"><code x-html="$code(file.code, file.lang)"></code></pre>
        </div>
    </template>
</div>
