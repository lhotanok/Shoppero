<?php

function display_header() {
    require_once(__DIR__ . '/templates/header.php');
}

function display_body($items, $known_items) {
    require_once(__DIR__ . '/templates/table.php');
    require_once(__DIR__ . '/templates/form.php');
}

function display_footer() {
    require_once(__DIR__ . '/templates/footer.php');
}

function display_page($items, $known_items) {
    display_header();
    display_body($items, $known_items);
    display_footer();
}