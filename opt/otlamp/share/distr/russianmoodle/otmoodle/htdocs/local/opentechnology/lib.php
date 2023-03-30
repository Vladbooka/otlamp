<?php

function local_opentechnology_before_footer() {
    global $PAGE;
    $PAGE->requires->js_call_amd('local_opentechnology/pendingevents', 'init');
}

function local_opentechnology_after_config() {

    global $CFG, $USER, $PAGE;

    if (\local_opentechnology\statistics::is_moodle_size_limit_exceeded())
    {
        // достигнут допустимый лимит размера инсталляции

        // сокращаем допустимый размер загружаемого файла до 1 байта
        $CFG->maxbytes = 1;

        // для пользователей, имеющих право обходить лимит, не найдено элегантного решения по установке лимита
        // поэтому очищается массив файлов, чтобы загрузить их всё равно не получилось
        if ($USER->id == 0 ||
            has_capability('moodle/user:ignoreuserquota', \context_user::instance($USER->id)))
        {
            $_FILES = [];
        }
    }

    $lot = $CFG->wwwroot . '/local/opentechnology';
    $bst = $lot . '/js/bootstrap-table/dist';
    $paths = [
        'tableExport' => $lot . '/js/tableexport.jquery.plugin/tableExport.min',
        'bootstrap-table' => $bst . '/bootstrap-table.min',
        'bootstrap-table-locale-all' => $bst . '/bootstrap-table-locale-all.min',
        'bootstrap-table-toolbar' => $bst . '/extensions/toolbar/bootstrap-table-toolbar.min',
        'bootstrap-table-page-changed' => $bst . '/extensions/page-changed/bootstrap-table-page-changed',
        'bootstrap-table-export' => $bst . '/extensions/export/bootstrap-table-export.min',
    ];
    $shim = [
        'bootstrap-table' => [
            'deps' => ['jquery'],
            'exports' => '$.fn.bootstrapTable',
        ],
        'bootstrap-table-locale-all' => [
            'deps' => ['bootstrap-table'],
            'exports' => '$.fn.bootstrapTable.defaults',
        ],
        'bootstrap-table-toolbar' => [
            'deps' => ['bootstrap-table'],
            'exports' => '$.fn.bootstrapTable.defaults',
        ],
        'bootstrap-table-page-changed' => [
            'deps' => ['bootstrap-table'],
            'exports' => '$.fn.bootstrapTable.defaults',
        ],
        'tableExport' => [
            'deps' => ['jquery'],
            'exports' => '$.fn.extend',
        ],
        'bootstrap-table-export' => [
            'deps' => ['bootstrap-table'],
            'exports' => '$.fn.bootstrapTable.defaults',
        ],
    ];
    $config = [
        'paths' => $paths,
        'shim' => $shim
    ];
    $requirejs = 'require.config(' . json_encode($config) . ')';
    $PAGE->requires->js_amd_inline($requirejs);
}