<?php

function atto_otmagnifier_after_config()
{
    global $PAGE;
    $PAGE->requires->js_call_amd('atto_otmagnifier/otmagnifier', 'init');
    $PAGE->requires->css('/lib/editor/atto/plugins/otmagnifier/magnifier.css?v=1');
}

/**
 * Set params for this plugin
 * @param string $elementid
 */
function atto_otmagnifier_params_for_js($elementid, $options, $fpoptions) {
    
    $clickhandler = get_config('atto_otmagnifier', 'clickhandler');
    
    $params = [
        'clickhandler' => $clickhandler
    ];
    
    return $params;
}