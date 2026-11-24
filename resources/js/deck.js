/**
 * État global de la présentation : navigation, minuteur, thème, plein écran, réinitialisation.
 */
const TARGET_SECONDS = 20 * 60;
const STAGE_W = 1600;
const STAGE_H = 900;

function storage(key, fallback) {
    try {
        const value = localStorage.getItem(`crudemo.${key}`);
        return value === null ? fallback : JSON.parse(value);
    } catch {
        return fallback;
    }
}

function persist(key, value) {
    try {
        localStorage.setItem(`crudemo.${key}`, JSON.stringify(value));
    } catch {
        /* stockage indisponible : sans conséquence */
    }
}

export function createDeckStore(data) {
    return {
        slides: data.slides,
        sections: data.sections,
        total: data.slides.length,
        current: 1,
        visited: 1,
        started: false,
        sidebar: storage('sidebar', true),
        notes: storage('notes', false),
        theme: storage('theme', 'dark'),
        fullscreen: false,
        help: false,
        resetting: false,
        toast: null,
        toastTimer: null,
        jumpBuffer: '',
        jumpTimer: null,
        timer: { running: false, elapsed: 0, last: null },
        stage: { scale: 1, x: 0, y: 0 },

        init() {
            const fromHash = this.readHash();
            if (fromHash) {
                this.current = fromHash;
                this.started = true;
            }
            this.applyTheme();

            window.addEventListener('hashchange', () => {
                const n = this.readHash();
                if (n && n !== this.current) this.go(n);
            });
            window.addEventListener('keydown', (e) => this.onKey(e));
            document.addEventListener('fullscreenchange', () => {
                this.fullscreen = Boolean(document.fullscreenElement);
            });

            setInterval(() => this.tick(), 250);
        },

        /* ───── Navigation ───── */
        get slide() {
            return this.slides[this.current - 1];
        },
        get progress() {
            return (this.current / this.total) * 100;
        },
        get section() {
            return this.sections[this.slide.section];
        },
        readHash() {
            const m = location.hash.match(/^#\/(\d+)$/);
            const n = m ? Number(m[1]) : 0;
            return n >= 1 && n <= this.total ? n : 0;
        },
        go(n) {
            const target = Math.min(this.total, Math.max(1, Number(n)));
            this.started = true;
            if (target === this.current && location.hash === `#/${target}`) return;
            this.current = target;
            history.replaceState(null, '', `#/${target}`);
            this.help = false;
        },
        next() {
            if (!this.started) return this.start();
            if (this.current < this.total) this.go(this.current + 1);
        },
        prev() {
            if (this.current > 1) this.go(this.current - 1);
        },
        start() {
            this.started = true;
            this.go(this.current);
            if (!this.timer.running && this.timer.elapsed === 0) this.timerToggle();
        },
        showCover() {
            this.started = false;
        },

        /* ───── Scène 16:9 ───── */
        fit(el) {
            const { width, height } = el.getBoundingClientRect();
            const scale = Math.min(width / STAGE_W, height / STAGE_H);
            this.stage = {
                scale,
                x: Math.round((width - STAGE_W * scale) / 2),
                y: Math.round((height - STAGE_H * scale) / 2),
            };
        },

        /* ───── Interface ───── */
        toggleSidebar() {
            this.sidebar = !this.sidebar;
            persist('sidebar', this.sidebar);
        },
        toggleNotes() {
            this.notes = !this.notes;
            persist('notes', this.notes);
        },
        toggleTheme() {
            this.theme = this.theme === 'dark' ? 'light' : 'dark';
            persist('theme', this.theme);
            this.applyTheme();
        },
        applyTheme() {
            document.documentElement.dataset.theme = this.theme;
        },
        async toggleFullscreen() {
            try {
                if (document.fullscreenElement) await document.exitFullscreen();
                else await document.documentElement.requestFullscreen();
            } catch {
                this.notify('Plein écran indisponible dans ce navigateur.', 'error');
            }
        },

        /* ───── Minuteur (objectif 20 minutes) ───── */
        tick() {
            if (!this.timer.running) return;
            const now = performance.now();
            this.timer.elapsed += (now - this.timer.last) / 1000;
            this.timer.last = now;
        },
        timerToggle() {
            this.timer.running = !this.timer.running;
            this.timer.last = performance.now();
        },
        timerReset() {
            this.timer = { running: false, elapsed: 0, last: null };
        },
        get timerLabel() {
            return this.clock(this.timer.elapsed);
        },
        get timerRatio() {
            return Math.min(1, this.timer.elapsed / TARGET_SECONDS);
        },
        get timerState() {
            if (this.timer.elapsed > TARGET_SECONDS) return 'over';
            if (this.timer.elapsed > TARGET_SECONDS - 120) return 'late';
            return 'ok';
        },
        /** Temps prévu au début de la slide courante, d'après les durées cibles. */
        get plannedAt() {
            return this.slides.slice(0, this.current - 1).reduce((sum, s) => sum + s.duration, 0);
        },
        get drift() {
            if (this.timer.elapsed === 0) return null;
            return Math.round(this.timer.elapsed - this.plannedAt);
        },
        clock(seconds) {
            const s = Math.max(0, Math.floor(seconds));
            return `${String(Math.floor(s / 60)).padStart(2, '0')}:${String(s % 60).padStart(2, '0')}`;
        },

        /* ───── Base de données ───── */
        async resetDatabase() {
            if (this.resetting) return;
            this.resetting = true;
            try {
                const response = await fetch('/api/demo/reset', {
                    method: 'POST',
                    headers: { Accept: 'application/json' },
                });
                const body = await response.json();
                if (!response.ok) throw new Error(body.message || response.statusText);
                window.dispatchEvent(new CustomEvent('crudemo:reset'));
                this.notify(`${body.message} ${body.products} produits, dont ${body.trashed} dans la corbeille.`, 'ok');
            } catch (error) {
                this.notify(`Échec de la réinitialisation : ${error.message}`, 'error');
            } finally {
                this.resetting = false;
            }
        },
        notify(message, tone = 'ok') {
            clearTimeout(this.toastTimer);
            this.toast = { message, tone };
            this.toastTimer = setTimeout(() => (this.toast = null), 3800);
        },

        /* ───── Clavier ───── */
        onKey(e) {
            const target = e.target;
            const typing = target instanceof Element && target.closest('input, textarea, select, [contenteditable="true"]');

            // Ctrl/Cmd + Entrée : envoyer la requête de la slide courante (même depuis l'éditeur)
            if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                e.preventDefault();
                window.dispatchEvent(new CustomEvent('crudemo:send', { detail: this.current }));
                return;
            }

            if (e.key === 'Escape') {
                if (typing) target.blur();
                this.help = false;
                window.dispatchEvent(new CustomEvent('crudemo:escape'));
                return;
            }

            if (typing || e.ctrlKey || e.metaKey || e.altKey) return;

            if (!this.started && ['Enter', ' ', 'ArrowRight', 'PageDown'].includes(e.key)) {
                e.preventDefault();
                this.start();
                return;
            }

            if (/^\d$/.test(e.key)) {
                this.jumpBuffer = (this.jumpBuffer + e.key).slice(-2);
                clearTimeout(this.jumpTimer);
                this.jumpTimer = setTimeout(() => (this.jumpBuffer = ''), 1500);
                return;
            }

            const actions = {
                ArrowRight: () => this.next(),
                PageDown: () => this.next(),
                ArrowLeft: () => this.prev(),
                PageUp: () => this.prev(),
                Home: () => this.go(1),
                End: () => this.go(this.total),
                Enter: () => {
                    if (!this.jumpBuffer) return false;
                    this.go(Number(this.jumpBuffer));
                    this.jumpBuffer = '';
                },
                f: () => this.toggleFullscreen(),
                s: () => this.toggleSidebar(),
                n: () => this.toggleNotes(),
                t: () => this.toggleTheme(),
                p: () => this.timerToggle(),
                R: () => this.resetDatabase(),
                '?': () => (this.help = !this.help),
            };

            const action = actions[e.key] ?? actions[e.key.toLowerCase?.()] ?? null;
            if (action && !(e.key === 'R' && !e.shiftKey)) {
                if (action() !== false) e.preventDefault();
            }
        },
    };
}
