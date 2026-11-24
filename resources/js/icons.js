// Généré : seules les icônes utilisées sont importées (bundle léger).
import { createElement, Send, Play, Pause, RotateCcw, Maximize, Minimize, PanelLeft, PanelLeftClose, Sun, Moon, NotebookPen, Database, Copy, Eraser, LoaderCircle, Check, X, ChevronLeft, ChevronRight, ChevronDown, Timer, Keyboard, Search, BookOpen, ExternalLink, Sparkles, Lightbulb, TriangleExclamationPoint, CircleQuestionMark, Route, FileCode, FilePlus, FileSearch, FilePen, FileX, Box, Table2, Folder, FolderOpen, ArrowRight, GitCompareArrows, WandSparkles, FlaskConical, Trash, List, Star, Gem, Undo2, ShieldCheck, Layers, Server, Braces, CircleCheckBig, Zap, Info, Eye, CodeXml, Plus, Pencil, Filter, Hash, Clock, ArrowUpRight, CircleDot, Workflow, Presentation, ScrollText, Gauge } from 'lucide';

const ICONS = {
    'send': Send,
    'play': Play,
    'pause': Pause,
    'rotate-ccw': RotateCcw,
    'maximize': Maximize,
    'minimize': Minimize,
    'panel-left': PanelLeft,
    'panel-left-close': PanelLeftClose,
    'sun': Sun,
    'moon': Moon,
    'notebook-pen': NotebookPen,
    'database': Database,
    'copy': Copy,
    'eraser': Eraser,
    'loader-circle': LoaderCircle,
    'check': Check,
    'x': X,
    'chevron-left': ChevronLeft,
    'chevron-right': ChevronRight,
    'chevron-down': ChevronDown,
    'timer': Timer,
    'keyboard': Keyboard,
    'search': Search,
    'book-open': BookOpen,
    'external-link': ExternalLink,
    'sparkles': Sparkles,
    'lightbulb': Lightbulb,
    'triangle-exclamation-point': TriangleExclamationPoint,
    'circle-question-mark': CircleQuestionMark,
    'route': Route,
    'file-code': FileCode,
    'file-plus': FilePlus,
    'file-search': FileSearch,
    'file-pen': FilePen,
    'file-x': FileX,
    'box': Box,
    'table-2': Table2,
    'folder': Folder,
    'folder-open': FolderOpen,
    'arrow-right': ArrowRight,
    'git-compare-arrows': GitCompareArrows,
    'wand-sparkles': WandSparkles,
    'flask-conical': FlaskConical,
    'trash': Trash,
    'list': List,
    'star': Star,
    'gem': Gem,
    'undo-2': Undo2,
    'shield-check': ShieldCheck,
    'layers': Layers,
    'server': Server,
    'braces': Braces,
    'circle-check-big': CircleCheckBig,
    'zap': Zap,
    'info': Info,
    'eye': Eye,
    'code-xml': CodeXml,
    'plus': Plus,
    'pencil': Pencil,
    'filter': Filter,
    'hash': Hash,
    'clock': Clock,
    'arrow-up-right': ArrowUpRight,
    'circle-dot': CircleDot,
    'workflow': Workflow,
    'presentation': Presentation,
    'scroll-text': ScrollText,
    'gauge': Gauge,
};

export function iconElement(name) {
    const node = ICONS[name];
    if (!node) {
        console.warn(`[CRUDEMO] icône inconnue : ${name}`);
        return document.createElement('span');
    }
    return createElement(node, { 'aria-hidden': 'true' });
}

/**
 * Directive Alpine : <span x-icon="send"></span>
 * Variante dynamique : <span x-icon.dynamic="cond ? 'sun' : 'moon'"></span>
 */
export function registerIconDirective(Alpine) {
    Alpine.directive('icon', (el, { expression, modifiers }, { evaluateLater, effect }) => {
        el.classList.add('icon');
        const draw = (name) => el.replaceChildren(iconElement(name));

        if (modifiers.includes('dynamic')) {
            const read = evaluateLater(expression);
            effect(() => read(draw));
        } else {
            draw(expression);
        }
    });
}
