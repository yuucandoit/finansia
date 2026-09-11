import './bootstrap';

import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();

import $ from 'jquery';
window.$ = window.jQuery = $;

import select2 from 'select2';
select2();

$(document).ready(function() {
    $('.select2').select2();
});