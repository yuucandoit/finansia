import './bootstrap';
import $ from 'jquery';
window.$ = window.jQuery = $;

import select2 from 'select2';
select2();

// Optional: Initialize right away if the element exists
$(document).ready(function() {
    $('.select2').select2();
});