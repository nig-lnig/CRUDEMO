import hljs from 'highlight.js/lib/core';
import php from 'highlight.js/lib/languages/php';
import json from 'highlight.js/lib/languages/json';
import sql from 'highlight.js/lib/languages/sql';

hljs.registerLanguage('php', php);
hljs.registerLanguage('json', json);
hljs.registerLanguage('sql', sql);

export function highlight(code, language) {
    return hljs.highlight(code ?? '', { language, ignoreIllegals: true }).value;
}

export function escapeHtml(value) {
    return String(value)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;');
}

/**
 * Découpe le HTML produit par highlight.js en lignes,
 * en refermant / rouvrant les <span> qui traversent un saut de ligne.
 */
function splitHtmlLines(html) {
    const lines = [];
    const stack = [];
    let current = '';
    const pattern = /(<span[^>]*>)|(<\/span>)|(\n)|([^<\n]+)/g;
    let match;

    while ((match = pattern.exec(html)) !== null) {
        if (match[1]) {
            stack.push(match[1]);
            current += match[1];
        } else if (match[2]) {
            stack.pop();
            current += match[2];
        } else if (match[3]) {
            current += '</span>'.repeat(stack.length);
            lines.push(current);
            current = stack.join('');
        } else {
            current += match[0];
        }
    }

    lines.push(current);

    return lines;
}

/**
 * Rendu d'un bloc de code avec numéro de ligne implicite et mise en évidence
 * des lignes qui appellent les méthodes Eloquent démontrées (->save( ou ::create().
 */
export function renderCode(code, language, emphasize = []) {
    const rawLines = code.split('\n');
    const htmlLines = splitHtmlLines(highlight(code, language));

    return htmlLines
        .map((line, index) => {
            const raw = rawLines[index] ?? '';
            const hit = emphasize.some((word) => new RegExp(`(->|::)${word}\\(`).test(raw));
            let html = line;

            if (hit) {
                for (const word of emphasize) {
                    html = html.replace(
                        new RegExp(`((?:-&gt;|::)(?:<[^>]+>)*)(${word})(?=(?:</span>)*\\()`),
                        '$1<mark class="em">$2</mark>',
                    );
                }
            }

            return `<span class="line${hit ? ' is-hl' : ''}">${html || ' '}</span>`;
        })
        .join('');
}

/**
 * Met en forme le SQL capturé (sauts de ligne uniquement — le texte n'est pas modifié).
 */
export function formatSql(sqlText) {
    return sqlText.replace(/\s+(from|where|and|or|order by|group by|limit|offset|set|values)\s+/gi, (m, keyword) => `\n${keyword} `);
}

/**
 * Colore tous les <code data-lang> statiques de la page.
 */
export function renderStaticCode(root = document) {
    root.querySelectorAll('code[data-lang]:not([data-rendered])').forEach((el) => {
        const words = JSON.parse(el.dataset.emphasize || '[]');
        el.innerHTML = renderCode(el.textContent, el.dataset.lang, words);
        el.dataset.rendered = '1';
    });
}
