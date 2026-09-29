import './styles/app.css';
import Alpine from 'alpinejs';
import peselHint from './js/components/pesel_hint.js';

Alpine.data('peselHint', peselHint);

// Exposed globally so Alpine components can be inspected in the browser devtools
window.Alpine = Alpine;

Alpine.start();
