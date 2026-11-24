import Alpine from 'alpinejs';
import { renderCode, renderStaticCode } from './code';
import { createDeckStore } from './deck';
import { registerIconDirective } from './icons';
import { playground } from './playground';
import { catalogue } from './catalogue';
import { routeDemo } from './route-demo';

registerIconDirective(Alpine);

const data = JSON.parse(document.getElementById('crudemo-data').textContent);

Alpine.store('deck', createDeckStore(data));
Alpine.data('playground', playground);
Alpine.data('catalogue', catalogue);
Alpine.data('routeDemo', routeDemo);

// Rendu d'un fichier source à la demande (slide 2)
Alpine.magic('code', () => (code, lang, emphasize = []) => renderCode(code, lang, emphasize));

window.Alpine = Alpine;

// Les scripts module s'exécutent après l'analyse du DOM : les blocs de code statiques existent déjà.
renderStaticCode();

Alpine.start();
