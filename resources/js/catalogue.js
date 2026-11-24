import { renderCode } from './code';

/**
 * Slide 25 — catalogue interactif des méthodes Eloquent complémentaires.
 */
export const CATEGORY_META = {
    create: { label: 'Create', fr: 'Création' },
    read: { label: 'Read', fr: 'Lecture' },
    update: { label: 'Update', fr: 'Modification' },
    delete: { label: 'Delete', fr: 'Suppression' },
};

export const KIND_META = {
    model: 'Méthode du modèle',
    builder: 'Query Builder',
    state: "Inspection d'état",
};

export const LEVEL_META = { 1: 'Débutant', 2: 'Intermédiaire', 3: 'Avancé' };

const normalize = (text) =>
    text
        .toLowerCase()
        .normalize('NFD')
        .replace(/[̀-ͯ]/g, '');

export function catalogue(items) {
    return {
        items,
        query: '',
        category: 'all',
        kind: 'all',
        tag: 'all',
        selected: null,
        categories: CATEGORY_META,
        kinds: KIND_META,
        levels: LEVEL_META,

        init() {
            window.addEventListener('crudemo:escape', () => (this.selected = null));
        },

        get filtered() {
            const q = normalize(this.query.trim());
            return this.items.filter(
                (item) =>
                    (this.category === 'all' || item.categories.includes(this.category)) &&
                    (this.kind === 'all' || item.kind === this.kind) &&
                    (this.tag === 'all' || item.tag === this.tag) &&
                    (q === '' || normalize(item.name).includes(q) || normalize(item.description).includes(q)),
            );
        },

        get groups() {
            const keys = this.category === 'all' ? Object.keys(CATEGORY_META) : [this.category];
            return keys.map((key) => ({
                key,
                ...CATEGORY_META[key],
                items: this.filtered.filter((item) => item.categories.includes(key)),
                columns: this.category !== 'all' ? 4 : key === 'read' ? 2 : 1,
                span: this.category !== 'all' ? 5 : key === 'read' ? 2 : 1,
            }));
        },

        countFor(category) {
            return category === 'all' ? this.items.length : this.items.filter((i) => i.categories.includes(category)).length;
        },

        open(item) {
            this.selected = item;
        },

        get exampleHtml() {
            return this.selected ? renderCode(this.selected.example, 'php') : '';
        },

        get syntaxHtml() {
            return this.selected ? renderCode(this.selected.syntax, 'php') : '';
        },

        openRelated(name) {
            const target = this.items.find((i) => i.name === name || i.name.split(' / ').includes(name));
            if (target) this.selected = target;
        },

        isRelatedAvailable(name) {
            return this.items.some((i) => i.name === name || i.name.split(' / ').includes(name));
        },

        reset() {
            this.query = '';
            this.category = 'all';
            this.kind = 'all';
            this.tag = 'all';
        },
    };
}
