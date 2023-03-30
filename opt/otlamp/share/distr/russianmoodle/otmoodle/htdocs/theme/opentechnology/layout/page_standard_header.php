<?php

// Получение менеджера профилей
$manager = \theme_opentechnology\profilemanager::instance();

// получение текущего профиля
$profile = $manager->get_current_profile();

$templatecontext = [
    'output' => $OUTPUT,
    'themedata' => $themedata,
];

echo $OUTPUT->render_from_template('theme_opentechnology/header', $templatecontext);
