import { formatSql, highlight } from './code';

/**
 * Slide 3 — trajet d'une vraie requête GET /api/products/{id} à travers Laravel.
 */
const wait = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

export function routeDemo() {
    return {
        id: '1',
        step: -1,
        loading: false,
        result: null,
        error: null,

        async run() {
            if (this.loading) return;
            this.loading = true;
            this.error = null;
            this.result = null;
            this.step = 0;

            const animation = (async () => {
                for (let s = 0; s <= 4; s++) {
                    this.step = s;
                    await wait(140);
                }
            })();

            try {
                const started = performance.now();
                const res = await fetch(`/api/products/${encodeURIComponent(this.id)}`, { headers: { Accept: 'application/json' } });
                const text = await res.text();
                const ms = performance.now() - started;

                let sql = null;
                const inspectionId = res.headers.get('X-Demo-Inspection');
                if (inspectionId) {
                    const inspection = await (await fetch(`/api/demo/inspections/${inspectionId}`, { headers: { Accept: 'application/json' } })).json();
                    sql = inspection.queries.find((q) => q.phase === 'operation') ?? null;
                }

                await animation;
                this.step = 5;

                let pretty = text;
                try {
                    pretty = JSON.stringify(JSON.parse(text), null, 2);
                } catch {
                    /* corps non JSON */
                }

                this.result = {
                    status: res.status,
                    ok: res.ok,
                    ms: Math.round(ms),
                    json: highlight(pretty, 'json'),
                    sql: sql ? highlight(formatSql(sql.sql), 'sql') : null,
                    bindings: sql ? sql.bindings : [],
                };
            } catch (error) {
                this.error = `Requête impossible : ${error.message}`;
            } finally {
                this.loading = false;
            }
        },
    };
}
