<?php

defined('MOODLE_INTERNAL') || die;
require_once ($CFG->dirroot . "/filter/otmedialinkscleaner/lib.php");

if ($ADMIN->fulltree) 
{
    $component = 'filter_otmedialinkscleaner';
    $settingsprefix = "";

    
    //заголовок
    $settingname = 'videoprocessing';
    $name = $component . '/' . $settingsprefix . $settingname;
    $visiblename = get_string($settingsprefix . 'settings_' . $settingname,
        $component);
    $description = get_string($settingsprefix . 'settings_' . $settingname .
        '_desc', $component);
    $setting = new admin_setting_heading($name, $visiblename, $description);
    $settings->add($setting);
    
    //опция включение удаления кнопки скачать из тега video
    $settingname = 'downloadbutton_disable';
    $name = $component . '/' . $settingsprefix . $settingname;
    $visiblename = get_string(
        $settingsprefix . 'settings_' . $settingname,
        $component);
    $description = get_string(
        $settingsprefix . 'settings_' . $settingname . '_desc',
        $component);
    $setting = new admin_setting_configcheckbox($name, $visiblename, $description, 0);
    $settings->add($setting);
    
    //заголовок
    $settingname = 'general';
    $name = $component . '/' . $settingsprefix . $settingname;
    $visiblename = get_string($settingsprefix . 'settings_' . $settingname,
        $component);
    $description = get_string($settingsprefix . 'settings_' . $settingname .
        '_desc', $component);
    $setting = new admin_setting_heading($name, $visiblename, $description);
    $settings->add($setting);
    
    foreach (filter_otmedialinkscleaner_helper::get_supported_extensions() as $ext)
    {
        $langparam = 'ext';
        if( $ext == 'flv' )
        {
            $langparam = $ext;
        }
        
        
        //заголовок настроек для ссылок с текущим расширением
        $name = $component . '/' . $settingsprefix . $ext . '_header';
        $visiblename = get_string(
            $settingsprefix . 'settings_' . $langparam . '_header',
            $component, 
            $ext
        );
        $description = get_string(
            $settingsprefix . 'settings_' . $langparam . '_header_desc', 
            $component, 
            $ext
        );
        $setting = new admin_setting_heading($name, $visiblename, $description);
        $settings->add($setting);
    
        
        //опция, скрывать ли ссылки для текущего расширения
        $name = $component . '/' . $settingsprefix . $ext;
        $visiblename = get_string(
            $settingsprefix . 'settings_' . $langparam,
            $component, 
            $ext
        );
        $description = get_string(
            $settingsprefix . 'settings_' . $langparam . '_desc', 
            $component, 
            $ext
        );
        $setting = new admin_setting_configcheckbox($name, $visiblename, $description, 0);
        $settings->add($setting);
    
        
        
        //опция, скрывать ли все ссылки или только обернутые медиаплагином 
        $name = $component . '/' . $settingsprefix . $ext . "_fallback_nonmedia";
        $visiblename = get_string(
            $settingsprefix . 'settings_' . $langparam . "_fallback_nonmedia",
            $component, 
            $ext
        );
        $description = get_string(
            $settingsprefix . 'settings_' . $langparam . "_fallback_nonmedia" . '_desc', 
            $component, 
            $ext
        );
        $setting = new admin_setting_configcheckbox($name, $visiblename, $description, 0);
        $settings->add($setting);
        
        
        //текст, отображаемый вместо скрытой ссылки для текущего расширения
        $name = $component . '/' . $settingsprefix . $ext . "_fallback_text";
        $visiblename = get_string(
            $settingsprefix . 'settings_' . $langparam . '_fallback_text', 
            $component, 
            $ext
        );
        $description = get_string(
            $settingsprefix . 'settings_' . $langparam . '_fallback_text_desc', 
            $component, 
            $ext
        );
        $setting = new admin_setting_confightmleditor(
            $name, 
            $visiblename, 
            $description, 
            get_string(
                'settings_' . $langparam . '_fallback_text_default', 
                $component, 
                $ext
            )
        );
        $settings->add($setting);
    }
}
