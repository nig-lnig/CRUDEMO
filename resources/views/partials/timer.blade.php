{{-- Minuteur de présentation : objectif 20 minutes, avance / retard par rapport au plan --}}
<div class="flex items-center gap-2 rounded-lg border border-line bg-panel px-2 py-1"
     title="Temps écoulé / objectif 20 min (P : lecture / pause)">
    <button type="button" class="grid size-6 place-items-center rounded text-muted hover:text-fg" @click="$store.deck.timerToggle()">
        <span x-icon.dynamic="$store.deck.timer.running ? 'pause' : 'play'" class="text-[14px]"></span>
    </button>
    <span x-icon="timer" class="text-[15px]"
          :class="{ 'text-ok': $store.deck.timerState === 'ok', 'text-warn': $store.deck.timerState === 'late', 'text-err': $store.deck.timerState === 'over' }"></span>
    <span class="font-mono text-[14px] font-semibold tabular-nums" x-text="$store.deck.timerLabel"></span>
    <span class="font-mono text-[12px] text-faint">/ 20:00</span>
    <div class="h-1.5 w-16 overflow-hidden rounded-full bg-hover">
        <div class="h-full rounded-full transition-[width] duration-300"
             :class="{ 'bg-ok': $store.deck.timerState === 'ok', 'bg-warn': $store.deck.timerState === 'late', 'bg-err': $store.deck.timerState === 'over' }"
             :style="`width: ${$store.deck.timerRatio * 100}%`"></div>
    </div>
    <template x-if="$store.deck.drift !== null && $store.deck.started">
        <span class="font-mono text-[12px] tabular-nums"
              :class="$store.deck.drift > 20 ? 'text-warn' : 'text-faint'"
              :title="`Prévu au début de cette slide : ${$store.deck.clock($store.deck.plannedAt)}`"
              x-text="($store.deck.drift >= 0 ? '+' : '−') + $store.deck.clock(Math.abs($store.deck.drift))"></span>
    </template>
    <button type="button" class="grid size-6 place-items-center rounded text-faint hover:text-fg" @click="$store.deck.timerReset()" title="Remettre le minuteur à zéro">
        <span x-icon="rotate-ccw" class="text-[13px]"></span>
    </button>
</div>
