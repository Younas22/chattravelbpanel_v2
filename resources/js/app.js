import './bootstrap';
import 'emoji-picker-element';
import Alpine from 'alpinejs';
import focus from '@alpinejs/focus';

Alpine.plugin(focus);

window.Alpine = Alpine;
Alpine.start();
