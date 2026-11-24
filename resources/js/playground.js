import { escapeHtml, formatSql, highlight } from './code';

/**
 * Playground « façon Postman » : exécute de VRAIES requêtes HTTP vers l'API Laravel,
 * puis récupère l'inspection (SQL + différentiel de la table) via l'en-tête X-Demo-Inspection.
 */
const STATUS_TEXT = {
    200: 'OK',
    201: 'Created',
    204: 'No Content',
    404: 'Not Found',
    405: 'Method Not Allowed',
    409: 'Conflict',
    422: 'Unprocessable Content',
    500: 'Internal Server Error',
};

export const FLOW_STEPS = ['Route', 'Contrôleur', 'Eloquent', 'Base de données', 'Réponse'];

const STATUS_LABELS = {
    inserted: 'insérée',
    modified: 'modifiée',
    soft_deleted: 'supprimée (logique)',
    restored: 'restaurée',
    force_deleted: 'supprimée définitivement',
    unchanged: 'inchangée',
    matched: 'correspond',
};

const STATUS_PLURALS = {
    inserted: 'insérées',
    modified: 'modifiées',
    soft_deleted: 'supprimées (logique)',
    restored: 'restaurées',
    force_deleted: 'supprimées définitivement',
};

const randomDigits =() => String(Math.floor(1000 + Math.random() * 9000));

/** Remplace {rand} par 4 chiffres (le même pour toute la requête). */
function resolveTokens(value) {
    if (value === null || value === undefined) return value;
    const rand = randomDigits();
    return JSON.parse(JSON.stringify(value).replaceAll('{rand}', rand));
}

const pretty = (value) => JSON.stringify(value, null, 2);
const toRows = (obj) => Object.entries(obj ?? {}).map(([key, value]) => ({ key, value: String(value) }));
const wait = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

function formatBytes(bytes) {
    return bytes < 1024 ? `${bytes} o` : `${(bytes / 1024).toFixed(2)} Ko`;
}

function formatCell(value, column) {
    if (value === null || value === undefined) return 'NULL';
    if (['created_at', 'updated_at', 'deleted_at'].includes(column)) {
        const s = String(value); // « 2026-11-24 21:31:27 » → « 24/11 21:31:27 »
        return `${s.slice(8, 10)}/${s.slice(5, 7)} ${s.slice(11, 19)}`;
    }
    return String(value);
}

export function playground(cfg) {
    return {
        cfg,
        number: cfg.number,
        method: cfg.request.method,
        route: cfg.request.route,
        params: toRows(cfg.request.params),
        query: toRows(cfg.request.query),
        hasBody: cfg.request.body !== null,
        bodyText: cfg.request.body !== null ? pretty(resolveTokens(cfg.request.body)) : '',
        bodyError: null,
        reqTab: cfg.request.body !== null ? 'body' : 'params',
        resTab: 'response',
        loading: false,
        response: null,
        inspection: null,
        inspectionError: null,
        networkError: null,
        showInspectionQueries: false,
        flow: -1,
        flowResult: null,
        activeScenario: null,
        revealed: false,
        confirmOpen: false,
        setupBusy: false,
        setupMessage: null,
        copied: false,
        flowSteps: FLOW_STEPS,

        init() {
            window.addEventListener('crudemo:send', (e) => {
                if (e.detail === this.number) this.send();
            });
            window.addEventListener('crudemo:reset', () => {
                this.clear();
                this.revealed = false;
                this.setupMessage = null;
            });
            window.addEventListener('crudemo:escape', () => (this.confirmOpen = false));
        },

        /* ───── URL ───── */
        get path() {
            return this.params.reduce(
                (path, p) => path.replace(`{${p.key}}`, encodeURIComponent(p.value.trim())),
                this.route,
            );
        },
        get queryString() {
            const qs = new URLSearchParams();
            this.query.filter((q) => q.value !== '').forEach((q) => qs.append(q.key, q.value));
            const text = qs.toString();
            return text ? `?${text}` : '';
        },
        get url() {
            return this.path + this.queryString;
        },
        get urlHtml() {
            let html = escapeHtml(this.route);
            for (const p of this.params) {
                html = html.replace(`{${escapeHtml(p.key)}}`, `<span class="text-brand font-bold">${escapeHtml(p.value) || `{${p.key}}`}</span>`);
            }
            if (this.queryString) {
                html += `<span class="text-muted">${escapeHtml(this.queryString)}</span>`;
            }
            return html;
        },
        get paramCount() {
            return this.params.length + this.query.length;
        },

        /* ───── Scénarios & préparation ───── */
        applyScenario(scenario, index) {
            if (scenario.params) {
                for (const [key, value] of Object.entries(scenario.params)) {
                    const row = this.params.find((p) => p.key === key);
                    if (row) row.value = String(value);
                }
            }
            if (scenario.query) this.query = toRows(scenario.query);
            if (scenario.body !== undefined) {
                this.bodyText = pretty(resolveTokens(scenario.body));
                this.bodyError = null;
            }
            this.activeScenario = index;
        },

        async runSetup(setup) {
            this.setupBusy = true;
            this.setupMessage = null;
            try {
                const response = await fetch(setup.url, {
                    method: setup.method,
                    headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
                    body: setup.body ? JSON.stringify(resolveTokens(setup.body)) : undefined,
                });
                const data = await response.json().catch(() => ({}));
                if (!response.ok) throw new Error(data.message || `HTTP ${response.status}`);

                for (const [key, source] of Object.entries(setup.fill ?? {})) {
                    const value = String(source).startsWith('@') ? data[source.slice(1)] : source;
                    const row = this.params.find((p) => p.key === key);
                    if (row) row.value = String(value);
                }
                this.setupMessage = { tone: 'ok', text: `Prêt : ${setup.label.toLowerCase()} (HTTP ${response.status}). ID ${this.params[0]?.value ?? ''} prérempli.` };
            } catch (error) {
                this.setupMessage = { tone: 'error', text: `Préparation impossible : ${error.message}` };
            } finally {
                this.setupBusy = false;
            }
        },

        /* ───── Envoi ───── */
        requestSend() {
            if (this.cfg.confirm) {
                this.confirmOpen = true;
                return;
            }
            this.send(true);
        },

        get confirmText() {
            return (this.cfg.confirm ?? '').replace('{id}', this.params.find((p) => p.key === 'id')?.value ?? '');
        },

        async send(confirmed = false) {
            if (this.loading) return;
            if (this.cfg.confirm && !confirmed) {
                this.confirmOpen = true;
                return;
            }
            this.confirmOpen = false;

            let body;
            if (this.hasBody) {
                try {
                    body = JSON.stringify(JSON.parse(this.bodyText));
                    this.bodyError = null;
                } catch (error) {
                    this.bodyError = `JSON invalide : ${error.message}`;
                    this.reqTab = 'body';
                    return;
                }
            }

            this.loading = true;
            this.networkError = null;
            this.inspection = null;
            this.inspectionError = null;
            this.flowResult = null;
            this.flow = 0;
            const animation = this.animateFlow();

            const started = performance.now();
            try {
                const res = await fetch(this.url, {
                    method: this.method,
                    headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
                    body,
                });
                const text = await res.text();
                const duration = performance.now() - started;

                let parsed = null;
                try {
                    parsed = text ? JSON.parse(text) : null;
                } catch {
                    parsed = null;
                }

                const formatted = parsed !== null ? pretty(parsed) : text;

                this.response = {
                    status: res.status,
                    statusText: STATUS_TEXT[res.status] ?? res.statusText,
                    ms: duration,
                    size: new TextEncoder().encode(text).length,
                    empty: text.length === 0,
                    raw: formatted,
                    html: parsed !== null ? highlight(formatted, 'json') : escapeHtml(text),
                    contentType: res.headers.get('content-type') ?? '—',
                };

                await animation;
                this.flow = 4;
                this.flowResult = res.ok ? 'ok' : 'err';
                this.revealed = true;

                const inspectionId = res.headers.get('X-Demo-Inspection');
                if (inspectionId) await this.loadInspection(inspectionId);
                else this.inspectionError = 'Aucune inspection pour cette requête.';
            } catch (error) {
                this.networkError = `Requête impossible : ${error.message}. Le serveur Laravel est-il lancé (php artisan serve) ?`;
                this.flowResult = 'err';
            } finally {
                this.loading = false;
            }
        },

        async animateFlow() {
            for (let step = 0; step <= 3; step++) {
                this.flow = step;
                await wait(90);
            }
        },

        async loadInspection(id) {
            try {
                const res = await fetch(`/api/demo/inspections/${id}`, { headers: { Accept: 'application/json' } });
                if (!res.ok) throw new Error(`HTTP ${res.status}`);
                this.inspection = await res.json();
            } catch (error) {
                this.inspectionError = `Inspection indisponible : ${error.message}`;
            }
        },

        /* ───── Réponse ───── */
        get statusClass() {
            if (!this.response) return '';
            const s = this.response.status;
            if (s === 404 || s >= 500) return 's-5';
            return s < 300 ? 's-2' : 's-4';
        },
        get durationLabel() {
            return this.response ? `${Math.round(this.response.ms)} ms` : '';
        },
        get sizeLabel() {
            return this.response ? formatBytes(this.response.size) : '';
        },

        async copy() {
            if (!this.response) return;
            try {
                await navigator.clipboard.writeText(this.response.raw);
            } catch {
                const area = document.createElement('textarea');
                area.value = this.response.raw;
                document.body.append(area);
                area.select();
                document.execCommand('copy');
                area.remove();
            }
            this.copied = true;
            setTimeout(() => (this.copied = false), 1400);
        },

        clear() {
            this.response = null;
            this.inspection = null;
            this.inspectionError = null;
            this.networkError = null;
            this.flow = -1;
            this.flowResult = null;
        },

        /* ───── SQL ───── */
        get operationQueries() {
            return (this.inspection?.queries ?? []).filter((q) => q.phase === 'operation');
        },
        get inspectionQueries() {
            return (this.inspection?.queries ?? []).filter((q) => q.phase === 'inspection');
        },
        sqlHtml(query) {
            return highlight(formatSql(query.sql), 'sql');
        },
        bindingLabel(value) {
            if (value === null) return 'NULL';
            return typeof value === 'string' ? `'${value}'` : String(value);
        },
        ms(value) {
            return `${Number(value).toFixed(2)} ms`;
        },

        /* ───── Base de données ───── */
        get db() {
            return this.inspection?.database ?? null;
        },
        get dbColumns() {
            return this.db?.columns ?? [];
        },
        get dbRows() {
            return this.db?.rows ?? [];
        },
        get hasChanges() {
            return this.dbRows.some((r) => !['unchanged', 'matched'].includes(r.status));
        },
        get summaryChips() {
            const s = this.db?.summary ?? {};
            return Object.entries(s).map(([status, count]) => ({
                status,
                label: `${count} ligne${count > 1 ? 's' : ''} ${count > 1 ? STATUS_PLURALS[status] : STATUS_LABELS[status]}`,
            }));
        },
        statusLabel(status) {
            return STATUS_LABELS[status] ?? status;
        },
        cell(row, column, side) {
            const source = side === 'before' ? row.before : row.after ?? row.before;
            return formatCell(source?.[column], column);
        },
        isNull(row, column, side) {
            const source = side === 'before' ? row.before : row.after ?? row.before;
            return source?.[column] === null || source?.[column] === undefined;
        },
        isChanged(row, column) {
            return row.changed.includes(column);
        },
    };
}
