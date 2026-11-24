{{-- Playground API inspiré de Postman : chaque « Send » exécute une vraie requête HTTP --}}
<div class="panel relative flex min-h-0 flex-col overflow-hidden"
     x-data="playground(@js(collect($slide)->only(['number', 'request', 'scenarios', 'setup', 'predict', 'confirm'])))">

    {{-- Barre de requête --}}
    <div class="flex flex-none items-center gap-2.5 border-b border-line p-3">
        <div class="flex h-11 min-w-0 flex-1 items-center overflow-hidden rounded-lg border border-line bg-code">
            <span class="m-{{ $slide['request']['method'] }} flex h-full flex-none items-center border-r border-line px-3.5 font-mono text-[15px] font-extrabold">
                {{ $slide['request']['method'] }}
            </span>
            <span class="min-w-0 flex-1 truncate px-3.5 font-mono text-[15.5px]" x-html="urlHtml" :title="url"></span>
        </div>
        <button type="button" class="btn btn-brand h-11 min-w-[112px] text-[16px]" @click="requestSend()" :disabled="loading"
                title="Envoyer la requête (Ctrl + Entrée)" data-send>
            <span x-icon.dynamic="loading ? 'loader-circle' : 'send'" :class="{ 'spin': loading }" class="text-[17px]"></span>
            <span x-text="loading ? 'Envoi…' : 'Send'"></span>
        </button>
    </div>

    {{-- Scénarios prédéfinis et préparation --}}
    @if (count($slide['scenarios']) || count($slide['setup']))
        <div class="flex flex-none flex-wrap items-center gap-2 border-b border-line px-3 py-2.5">
            @foreach ($slide['scenarios'] as $i => $scenario)
                <button type="button" class="scenario tone-{{ $scenario['tone'] }}"
                        :class="{ 'is-active': activeScenario === {{ $i }} }"
                        @click="applyScenario(@js($scenario), {{ $i }})">{{ $scenario['label'] }}</button>
            @endforeach
            @foreach ($slide['setup'] as $setup)
                <button type="button" class="btn ml-auto h-[30px] border-dashed px-3 text-[13px]"
                        @click="runSetup(@js($setup))" :disabled="setupBusy"
                        title="Requête réelle de préparation : {{ $setup['method'] }} {{ $setup['url'] }}">
                    <span x-icon.dynamic="setupBusy ? 'loader-circle' : 'flask-conical'" :class="{ 'spin': setupBusy }" class="text-[14px] text-brand"></span>
                    {{ $setup['label'] }}
                </button>
            @endforeach
        </div>
        <template x-if="setupMessage">
            <p class="flex-none border-b border-line px-3 py-1.5 text-[13px]" :class="setupMessage.tone === 'ok' ? 'text-ok' : 'text-err'" x-text="setupMessage.text"></p>
        </template>
    @endif

    {{-- Éditeur de requête --}}
    <div class="flex-none px-3">
        <div class="tabs">
            <button type="button" class="tab" :class="{ 'is-active': reqTab === 'params' }" @click="reqTab = 'params'">
                Params <span class="count" x-text="paramCount"></span>
            </button>
            <template x-if="hasBody">
                <button type="button" class="tab" :class="{ 'is-active': reqTab === 'body' }" @click="reqTab = 'body'">
                    Body <span class="font-mono text-[12px] text-faint">JSON</span>
                </button>
            </template>
            <span class="ml-auto self-center font-mono text-[12px] text-faint">Accept: application/json</span>
        </div>

        <div class="h-[128px] py-2">
            {{-- Paramètres de route et de query string --}}
            <div x-show="reqTab === 'params'" class="h-full overflow-auto">
                <template x-if="paramCount === 0">
                    <p class="pt-3 text-[15px] text-faint">Aucun paramètre : cette route n'en attend pas.</p>
                </template>
                <div class="grid grid-cols-[150px_1fr] items-center gap-x-3 gap-y-2">
                    <template x-for="p in params" :key="'p' + p.key">
                        <div class="contents">
                            <label class="flex items-center gap-2 font-mono text-[14px]">
                                <span class="rounded bg-brand-soft px-1.5 text-[11px] font-bold uppercase text-brand">route</span>
                                <span x-text="p.key"></span>
                            </label>
                            <input class="field" x-model="p.value" spellcheck="false" @keydown.enter.prevent="requestSend()">
                        </div>
                    </template>
                    <template x-for="q in query" :key="'q' + q.key">
                        <div class="contents">
                            <label class="flex items-center gap-2 font-mono text-[14px]">
                                <span class="rounded bg-hover px-1.5 text-[11px] font-bold uppercase text-muted">query</span>
                                <span x-text="q.key"></span>
                            </label>
                            <input class="field" x-model="q.value" spellcheck="false" @keydown.enter.prevent="requestSend()">
                        </div>
                    </template>
                </div>
            </div>

            {{-- Corps JSON --}}
            <template x-if="hasBody">
                <div x-show="reqTab === 'body'" class="relative h-full">
                    <textarea class="editor h-full" x-model="bodyText" spellcheck="false" aria-label="Corps JSON de la requête"
                              @input="bodyError = null"></textarea>
                    <template x-if="bodyError">
                        <p class="absolute inset-x-2 bottom-2 rounded-md bg-err px-3 py-1.5 text-[13px] font-semibold text-white" x-text="bodyError"></p>
                    </template>
                </div>
            </template>
        </div>
    </div>

    {{-- Prédiction --}}
    @if ($slide['predict'])
        <div class="mx-3 mb-2.5 flex flex-none items-start gap-3 rounded-lg border px-3.5 py-2.5"
             :class="revealed ? 'border-ok/40 bg-ok/8' : 'border-brand/40 bg-brand-soft'">
            <span x-icon.dynamic="revealed ? 'circle-check-big' : 'circle-question-mark'" class="mt-0.5 text-[18px]"
                  :class="revealed ? 'text-ok' : 'text-brand'"></span>
            <div class="min-w-0 flex-1 text-[15px] leading-snug">
                <p><span class="font-bold">Prédiction :</span> « {{ $slide['predict']['question'] }} »</p>
                <p x-show="revealed" x-cloak class="mt-1 text-muted fade-in">{{ $slide['predict']['answer'] }}</p>
            </div>
            <button type="button" class="flex-none text-[13px] font-semibold text-faint hover:text-fg" x-show="!revealed" @click="revealed = true">
                Révéler
            </button>
        </div>
    @endif

    {{-- Flux d'exécution --}}
    <div class="flex flex-none items-center border-y border-line bg-panel-2 px-3 py-2">
        <template x-for="(step, i) in flowSteps" :key="step">
            <div class="flex items-center">
                <template x-if="i > 0"><span class="flow-link" :class="{ 'is-on': flow >= i }"></span></template>
                <span class="flow-node"
                      :class="{
                        'is-on': flow >= i && !(i === 4 && flowResult),
                        'is-done-ok': i === 4 && flowResult === 'ok',
                        'is-done-err': i === 4 && flowResult === 'err',
                      }" x-text="step"></span>
            </div>
        </template>
        <template x-if="response">
            <span class="ml-auto font-mono text-[12px] text-faint" x-text="`serveur : ${inspection ? inspection.server_ms + ' ms' : '…'}`"></span>
        </template>
    </div>

    {{-- Réponse --}}
    <div class="flex min-h-0 flex-1 flex-col">
        <div class="flex flex-none items-center gap-2 px-3">
            <div class="tabs flex-1">
                <button type="button" class="tab" :class="{ 'is-active': resTab === 'response' }" @click="resTab = 'response'">
                    <span x-icon="braces" class="text-[15px]"></span> Response
                </button>
                <button type="button" class="tab" :class="{ 'is-active': resTab === 'sql' }" @click="resTab = 'sql'">
                    <span x-icon="code-xml" class="text-[15px]"></span> SQL
                    <span class="count" x-show="inspection" x-text="operationQueries.length"></span>
                </button>
                <button type="button" class="tab" :class="{ 'is-active': resTab === 'database' }" @click="resTab = 'database'">
                    <span x-icon="table-2" class="text-[15px]"></span> Base de données
                </button>

                <div class="ml-auto flex items-center gap-3 self-center font-mono text-[13.5px]" x-show="response">
                    <span class="font-bold" :class="statusClass" x-text="response && `${response.status} ${response.statusText}`"></span>
                    <span class="text-muted" x-text="durationLabel" title="Durée réelle mesurée dans le navigateur"></span>
                    <span class="text-muted" x-text="sizeLabel" title="Taille du corps de la réponse"></span>
                    <button type="button" class="text-faint hover:text-fg" @click="copy()" title="Copier la réponse">
                        <span x-icon.dynamic="copied ? 'check' : 'copy'" class="text-[15px]" :class="{ 'text-ok': copied }"></span>
                    </button>
                    <button type="button" class="text-faint hover:text-fg" @click="clear()" title="Effacer la réponse">
                        <span x-icon="eraser" class="text-[15px]"></span>
                    </button>
                </div>
            </div>
        </div>

        <div class="min-h-0 flex-1 overflow-auto bg-code px-4 py-3">
            {{-- État vide / erreur réseau --}}
            <template x-if="!response && !loading && !networkError">
                <div class="flex h-full flex-col items-center justify-center gap-2 text-center text-faint">
                    <span x-icon="send" class="text-[28px]"></span>
                    <p class="text-[15px]">Cliquez sur <span class="font-semibold text-brand">Send</span> pour exécuter la requête réelle.</p>
                    <p class="text-[13px]"><span class="kbd">Ctrl</span> + <span class="kbd">Entrée</span></p>
                </div>
            </template>
            <template x-if="loading && !response">
                <div class="flex h-full items-center justify-center gap-2 text-muted">
                    <span x-icon="loader-circle" class="spin text-[20px] text-brand"></span> Exécution…
                </div>
            </template>
            <template x-if="networkError">
                <p class="rounded-md bg-err/10 px-3 py-2 text-[14px] text-err" x-text="networkError"></p>
            </template>

            {{-- Onglet Response --}}
            <div x-show="response && resTab === 'response'">
                <template x-if="response && response.empty">
                    <p class="py-6 text-center text-[15px] text-muted">
                        Corps vide — <span class="font-mono font-semibold" :class="statusClass" x-text="`${response.status} ${response.statusText}`"></span>
                    </p>
                </template>
                <pre class="code-plain" x-show="response && !response.empty"><code x-html="response ? response.html : ''"></code></pre>
            </div>

            {{-- Onglet SQL --}}
            <div x-show="response && resTab === 'sql'">
                @include('partials.playground-sql')
            </div>

            {{-- Onglet Base de données --}}
            <div x-show="response && resTab === 'database'">
                @include('partials.playground-database')
            </div>
        </div>
    </div>

    {{-- Confirmation explicite (forceDelete) --}}
    @if ($slide['confirm'])
        <div class="absolute inset-0 z-20 grid place-items-center bg-black/50" x-show="confirmOpen" x-cloak x-transition.opacity>
            <div class="panel w-[520px] border-err/60 p-6 fade-in" @click.outside="confirmOpen = false">
                <div class="flex items-center gap-2.5">
                    <span x-icon="triangle-exclamation-point" class="text-[22px] text-err"></span>
                    <h3 class="text-[20px] font-bold">Suppression définitive</h3>
                </div>
                <p class="mt-3 text-[16px] leading-relaxed text-muted" x-text="confirmText"></p>
                <p class="mt-2 text-[14px] text-faint">Le corps de la requête doit aussi contenir <span class="font-mono">"confirm": true</span>, vérifié par la validation Laravel.</p>
                <div class="mt-6 flex justify-end gap-2.5">
                    <button type="button" class="btn" @click="confirmOpen = false">Annuler</button>
                    <button type="button" class="btn border-err bg-err text-white hover:!bg-err/85" @click="send(true)">
                        <span x-icon="trash" class="text-[16px]"></span> Supprimer définitivement
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
