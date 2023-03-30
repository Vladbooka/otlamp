<?php

function block_mastercourse_user_preferences()
{
    $preferences = [];
    $preferences['eduportal_auth_allowed'] = [
        'type' => PARAM_RAW,
        'null' => NULL_ALLOWED,
        'default' => null
    ];
    return $preferences;
}