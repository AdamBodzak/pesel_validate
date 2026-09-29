import './styles/app.css';
import Alpine from 'alpinejs';
import registerPersonForm from './js/components/register_person_form.js';

Alpine.data('registerPersonForm', registerPersonForm);

// Exposed globally so Alpine components can be inspected in the browser devtools
window.Alpine = Alpine;

Alpine.start();
