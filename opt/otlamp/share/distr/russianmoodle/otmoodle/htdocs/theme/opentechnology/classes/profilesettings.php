<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * Тема СЭО 3KL. Настройки профилей Темы.
 *
 * @package    theme_opentechnology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_opentechnology;

use admin_category;
use admin_settingpage;
use admin_setting;
use admin_setting_configselect;
use admin_setting_configtextarea;
use admin_setting_configtext;
use admin_setting_heading;
use admin_setting_configstoredfile;
use admin_setting_confightmleditor;
use admin_setting_configcheckbox;

use moodle_url;
use \theme_opentechnology\profiles\base;

use html_writer;


class profilesettings
{
    const pages = ['main', 'responsive', 'header', 'footer', 'color', 'pagebacks', 'custom_fonts', 'blocks', 'security', 'loginpage_main'];
    const colorzones = ['header', 'navbar', 'content', 'blocks', 'footer', 'collapsiblesection', 'dock'];
    const colorelements = ['backgroundcolor', 'basecolor', 'linkscolor', 'linkscolor_active', 'elementscolor', 'elementscolor_active'];
    private $structure = [];
    private $profile;
    private $category;
    private $adminroot;
    private $profilemanager;

    public function __construct(base $profile, admin_category &$category, profilemanager $profilemanager) {

        global $ADMIN;

        $this->adminroot = $ADMIN ?? admin_get_root();
        $this->profile = $profile;
        $this->category = &$category;
        $this->profilemanager = $profilemanager;

        $this->fill_structure();
    }

    private function get_color_zone_elements($colorzone)
    {
        $elements = self::colorelements;
        switch($colorzone)
        {
            case 'header':
                $elements[] = 'topbasecolor';
                $elements[] = 'usermenu_ddtoggle';
                $elements[] = 'usermenu_ddtoggle_active';
                $elements[] = 'usermenu_ddmenu';
                $elements[] = 'usermenu_ddmenu_active';
                $elements[] = 'custommenu_item';
                $elements[] = 'custommenu_item_active';
                $elements[] = 'custommenu_ddtoggle';
                $elements[] = 'custommenu_ddtoggle_active';
                $elements[] = 'custommenu_ddmenu';
                $elements[] = 'custommenu_ddmenu_active';
                break;
            case 'content':
                $elements[] = 'mod_header';
                break;
            case 'dock':
                //
                $elements = [
                    'dockeditem_textview',
                    'dockeditem_textview_active',
                    'dockeditem_iconview',
                    'dockeditem_iconview_active',
                ];
                break;
        }
        return $elements;
    }

    public function add_all_profile_settings()
    {
//         if ($this->adminroot->fulltree)
        {
            $this->add_settings($this->structure);
        }
    }

    public function get_page($pagename) {
        if (array_key_exists($pagename, $this->structure) &&
            array_key_exists('settingspage', $this->structure[$pagename]) &&
            $this->structure[$pagename]['settingspage'] instanceof admin_settingpage)
        {
            return $this->structure[$pagename]['settingspage'];
        }
        throw new \moodle_exception('The requested settings page was not found');
    }

    private function fill_structure() {

        $this->fill_structure_pages();
        if ($this->adminroot->fulltree)
        {
            $this->fill_structure_settings();
        }
    }

    private function add_settings($structure) {
        foreach($structure as $pagedata)
        {
            if (array_key_exists('settingspage', $pagedata) &&
                array_key_exists('sections', $pagedata))
            {
                /** @var admin_settingpage $settingspage */
                $settingspage = $pagedata['settingspage'];

                foreach($pagedata['sections'] as $sectionname => $settings)
                {
                    if (!empty($sectionname))
                    {
                        $headersetting = [
                            'setting' => $this->create_header_setting($sectionname),
                            'settingname' => $sectionname,
                        ];
                        array_unshift($settings , $headersetting);
                    }
                    /** @var admin_setting $setting */
                    foreach($settings as $settingdata)
                    {
                        $addsettingresult = $settingspage->add($settingdata['setting']);
                        if ($addsettingresult === false)
                        {
                            debugging(var_export($settingdata, true));
                        }
                    }
                }
                $this->category->add($this->category->name, $settingspage);
            }
        }
    }

    private function fill_structure_pages() {

        foreach (self::pages as $page)
        {
            $this->structure[$page] = [
                'settingspage' => $this->create_page($page),
                'sections' => [],
            ];
            if (method_exists($this, 'get_page_sections_'.$page))
            {
                $sections = $this->{'get_page_sections_'.$page}();
                $this->structure[$page]['sections'] = array_fill_keys($sections, []);
                $this->structure[$page]['sections'][null] = [];
            }
        }
    }



    private function create_page($pagecode)
    {
        // Общие настройки темы
        $name = $this->profilemanager->get_theme_setting_name($pagecode, $this->profile);
        $settingspage = new admin_settingpage(
            'theme_opentechnology_'.$name,
            get_string('theme_opentechnology_'.$pagecode, 'theme_opentechnology'),
            'theme/opentechnology:settings_edit'
        );

        if ($this->adminroot->fulltree)
        {
            $overrides = $this->get_profile_overrides_types($this->profile);

            if(!empty($overrides))
            {
                array_walk($overrides, function(&$overridetype){
                    $overridetype = get_string('profile_override_'.$overridetype, 'theme_opentechnology');
                });

                $a = new \stdClass();
                $a->themedir = $this->theme_config->dir;
                $a->profilecode = $this->profile->get_code();
                $a->overrides = '<div>'.implode('</div><div>', $overrides).'</div>';

                $setting = new \admin_setting_configempty(
                    'profile_overrides_detected',
                    get_string('profile_overrides_detected','theme_opentechnology'),
                    get_string('profile_overrides_detected_desc','theme_opentechnology', $a)
                );
                $settingspage->add($setting);
            }
        }

        return $settingspage;
    }

















    private function get_settings_map() {

        ///////////////////////////////
        // настройки темы оформления //
        ///////////////////////////////

        $settingsmap = [


            // Общие настройки

            'main_favicon' => ['main' => null],
            'main_public_file' => ['main' => null],
            'main_langmenu' => ['main' => null],
            'main_dock_hide' => ['main' => null],
            'main_fixed_width' => ['main' => null],
            'main_modal_login' => ['main' => null],
            'main_dockeditem_title' => ['main' => null],
            'main_dockeditem_title_default' => ['main' => null],
            'main_dockeditem_title_iconset' => ['main' => null],
            'main_spelling_mistake' => ['main' => null],
            'main_scsspre' => ['main' => null],
            'main_scssextra' => ['main' => null],
            'main_customcss' => ['main' => null],
            'main_custombodyinnerclasses' => ['main' => null],


            // Адаптивность

            'responsive_tables' => ['responsive' => null],
            'gradereport_table' => ['responsive' => null],


            // Шапка страницы

            'header_sticky' => ['header' => 'header_title'],
            'header_backgroundimage' => ['header' => 'header_title'],

            'header_top_text' => ['header' => 'header_top_title'],
            'header_logoimage' => ['header' => 'header_logo_title'],
            'header_logo_link' => ['header' => 'header_logo_title'],
            'header_logo_text' => ['header' => 'header_logo_title'],
            'header_logoimage_padding' => ['header' => 'header_logo_title'],

            'header_text' => ['header' => 'header_text_title'],
            'header_text_padding' => ['header' => 'header_text_title'],

            'header_link_crw' => ['header' => 'header_usermenu_title'],
            'header_link_portfolio' => ['header' => 'header_usermenu_title'],
            'header_link_unread_messages' => ['header' => 'header_usermenu_title'],
            'header_link_search' => ['header' => 'header_usermenu_title'],
            'header_usermenu_padding' => ['header' => 'header_usermenu_title'],
            'header_usermenu_hide_caret' => ['header' => 'header_usermenu_title'],

            'header_custommenu_location' => ['header' => 'header_custommenu_title'],

            'header_dockpanel_texture' => ['header' => 'header_dockpanel_title'],
            'header_dockpanel_header' => ['header' => 'header_dockpanel_title'],
            'header_content_header' => ['header' => 'header_dockpanel_title'],


            // Подвал страницы

            'footer_backgroundimage' => ['footer' => 'footer_title'],
            'footer_border_texture' => ['footer' => 'footer_title'],
            'footer_logoimage' => ['footer' => 'footer_title'],
            'footer_logoimage_width' => ['footer' => 'footer_title'],
            'footer_logoimage_text' => ['footer' => 'footer_title'],
            'footer_social_links' => ['footer' => 'footer_title'],
            'footer_text' => ['footer' => 'footer_title'],
            'copyright_text' => ['footer' => 'footer_title'],


            // цветовая схема

            'color_dockeditems_backgroundcolor' => ['color' => 'color_dockeditems_title'],
            'color_dockeditems_backgroundcolor_text' => ['color' => 'color_dockeditems_title'],
            'color_dockeditems_backgroundcolor_active' => ['color' => 'color_dockeditems_title'],
            'color_dockeditems_backgroundcolor_active_text' => ['color' => 'color_dockeditems_title'],
            'color_dockeditems_iconview_backgroundcolor' => ['color' => 'color_dockeditems_title'],
            'color_dockeditems_iconview_backgroundcolor_text' => ['color' => 'color_dockeditems_title'],
            'color_dockeditems_iconview_backgroundcolor_active' => ['color' => 'color_dockeditems_title'],
            'color_dockeditems_iconview_backgroundcolor_active_text' => ['color' => 'color_dockeditems_title'],


            // Шрифты

            'custom_fonts_files' => ['custom_fonts' => null],


            // Базопасность

            'security_nojs_text' => ['security' => 'security_title'],
            'security_copy_draganddrop' => ['security' => 'security_title'],
            'security_copy_contextmenu' => ['security' => 'security_title'],
            'security_copy_copy' => ['security' => 'security_title'],
            'security_copy_nojsaccess' => ['security' => 'security_title'],


            // Cтраница авторизации

            'loginpage_main_type' => ['loginpage_main' => 'loginpage_main_title'],
        ];

        ///////////////////////////////////////
        // динамически добавляемые настройки //
        ///////////////////////////////////////

        // Основные цвета темы
        foreach ($this->profilemanager::$themecolors as $colorname)
        {
            $settingsmap['theme_color_'.$colorname] = ['color' => 'theme_color_title'];
        }
        $settingsmap['theme_color_input_border'] = ['color' => 'theme_color_title'];

        // схожие цветовые настройки имеющиеся в настраиваемых по цвету зонах
        foreach (self::colorzones as $colorzone)
        {
            foreach($this->get_color_zone_elements($colorzone) as $colorelement)
            {
                // Цвет фона
                $settingsmap['color_'.$colorzone.'_'.$colorelement] = ['color' => 'color_'.$colorzone.'_title_'.$colorelement];
                // Для кнопок добавляем еще цвет рамки
                if (in_array($colorelement, ['elementscolor', 'elementscolor_active'])) {
                    $settingsmap['color_'.$colorzone.'_'.$colorelement.'_border'] = ['color' => 'color_'.$colorzone.'_title_'.$colorelement];
                }
                // Цвет текста на фоне
                $settingsmap['color_'.$colorzone.'_'.$colorelement.'_text'] = ['color' => 'color_'.$colorzone.'_title_'.$colorelement];
                // Изменение яркости иконок под цвет фона
                $settingsmap['color_'.$colorzone.'_'.$colorelement.'_icon_brightness'] = ['color' => 'color_'.$colorzone.'_title_'.$colorelement];
            }
        }

        // настройки боковых фонов
        foreach($this->get_pageback_zones() as $pageback)
        {
            $settingsmap['color_pb_'.$pageback.'_backgroundcolor'] = ['pagebacks' => 'pb_'.$pageback.'_title'];
            $settingsmap['pb_'.$pageback.'_backgroundimage'] = ['pagebacks' => 'pb_'.$pageback.'_title'];
            $settingsmap['pb_'.$pageback.'_unlimit_width'] = ['pagebacks' => 'pb_'.$pageback.'_title'];
        }

        // настройки шрифтов
        $name = $this->profilemanager->get_theme_setting_name('custom_fonts_files', $this->profile);
        $filearea = $this->profilemanager->get_theme_setting_filearea('custom_fonts_files', $this->profile);
        $fontfiles = theme_opentechnology_get_filearea_files($filearea, $name, 0);
        foreach ($fontfiles as $fontfile)
        {
            $settingsmap['custom_fonts_font_settings_'.$fontfile->settingname] = ['custom_fonts' => null];
            $settingsmap['custom_fonts_font_family_'.$fontfile->settingname] = ['custom_fonts' => null];
            $settingsmap['custom_fonts_font_weight_'.$fontfile->settingname] = ['custom_fonts' => null];
            $settingsmap['custom_fonts_font_style_'.$fontfile->settingname] = ['custom_fonts' => null];
        }

        // настройки сворачивания блоков на странице
        foreach($this->get_theme_layouts() as $layout => $layoutdata)
        {
            // настройки сворачиваемых секций
            foreach(theme_opentechnology_get_known_collapsiblesections() as $cs)
            {
                // Состояние сворачиваемой секции
                $settingname = 'layout_'.$layout.'_collapsiblesection_'.$cs['code'].'_state';
                $settingsmap[$settingname] = ['blocks' => 'layout_'.$layout.'_title'];

                // Настройка позиций блоков сворачиваемой секции
                $settingname = 'layout_'.$layout.'_collapsiblesection_'.$cs['code'];
                $settingsmap[$settingname] = ['blocks' => 'layout_'.$layout.'_title'];
            }

            // настройки регионов блоков
            foreach ($layoutdata['regions'] as $region)
            {
                // Состояние позиции блоков
                $settingname = str_replace('-', '_', 'region_'.$layout.'_'.$region);
                $settingsmap[$settingname] = ['blocks' => 'layout_'.$layout.'_title'];
            }
        }

        // настройки страницы авторизации в зависимости от типа страницы авторизации
        switch($this->profilemanager->get_theme_setting('loginpage_main_type', $this->profile))
        {
            case 'sidebar':
                $settingsmap['loginpage_sidebar_logoimage'] = ['loginpage_main' => 'loginpage_sidebar_title'];
                $settingsmap['loginpage_sidebar_images'] = ['loginpage_main' => 'loginpage_sidebar_title'];
                $settingsmap['loginpage_sidebar_header_elements'] = ['loginpage_main' => 'loginpage_sidebar_title'];
                $settingsmap['loginpage_sidebar_text'] = ['loginpage_main' => 'loginpage_sidebar_title'];
                break;
            case 'slider':
                $settingsmap['loginpage_slider_images'] = ['loginpage_main' => 'loginpage_slider_title'];
                $settingsmap['loginpage_header_text'] = ['loginpage_main' => 'loginpage_slider_title'];
                $settingsmap['loginpage_header_text_padding'] = ['loginpage_main' => 'loginpage_slider_title'];
                break;
        }

        return $settingsmap;
    }

    private function fill_structure_settings() {
        $urlparts = parse_url($_SERVER['REQUEST_URI']);
        $filterpage = null;
        if ($urlparts['path'] == '/admin/settings.php')
        {
            $filterpage = optional_param('section', null, PARAM_SAFEDIR);
            $profileprefix = $this->profilemanager->get_theme_setting_name('', $this->profile);
            if (!is_null($filterpage) && strpos($filterpage, 'theme_opentechnology_'.$profileprefix) === 0)
            {
                $filterpage = substr($filterpage, strlen('theme_opentechnology_'.$profileprefix.'_')-1);
            } else
            {
                $filterpage = null;
            }
        }

        $settingsmap = $this->get_settings_map();
        foreach($settingsmap as $settingname => $settingplaces)
        {
            try {
                $setting = null;
                foreach($settingplaces as $pagename => $section)
                {
                    if (!is_null($filterpage) && $filterpage != $pagename) {
                        continue;
                    }
                    if (!array_key_exists($pagename, $this->structure)) {
                        continue;
                    }
                    if (!array_key_exists('sections', $this->structure[$pagename])) {
                        continue;
                    }
                    if (!array_key_exists($section, $this->structure[$pagename]['sections'])) {
                        continue;
                    }

                    if (is_null($setting))
                    {
                        $setting = $this->create_setting($settingname);
                    }

                    if ($setting instanceof admin_setting)
                    {
                        $this->structure[$pagename]['sections'][$section][] = [
                            'settingname' => $settingname,
                            'setting' => $setting,
                        ];
                    } else {
                        debugging('Bad setting was created:'.json_encode([
                            'pagename' => $pagename,
                            'section' => $section,
                            'settingname' => $settingname
                        ]));
                    }
                }
            } catch(\moodle_exception $ex)
            {

            }
        }
    }













    private function get_pageback_zones() {
        // cs*  - collapsiblesection (сворачиваемые секции, шторка)
        // reg* - region (регион, зона для блоков)
        return [
            'cs_htop',
            'h_text',
            'header',
            'dockpanel',
            'reg_heading',
            'breadcrumbs',
            'cs_ctop',
            'content',
            'reg_footing',
            'cs_cbot',
            'f_border',
            'footer'
        ];
    }

    private function get_theme_layouts() {
        global $CFG, $THEME;
        include($CFG->dirroot.'/theme/opentechnology/config.php');

        $layouts = [];

        if ( isset($THEME->layouts) && is_array($THEME->layouts) )
        { // Указаны типы страниц
            foreach ( $THEME->layouts as $layoutname => $layoutdata )
            { // Обработка каждого типа страницы
                if ( ! isset($layoutdata['regions']) || empty($layoutdata['regions']) )
                { // Зоны блоков не объявлены
                    continue;
                }
                $layouts[$layoutname] = $layoutdata;
            }
        }

        return $layouts;
    }












    private function get_page_sections_main() {
        return [];
    }

    private function get_page_sections_responsive() {
        return [];
    }

    private function get_page_sections_header() {
        return [
            'header_title',
            'header_top_title',
            'header_logo_title',
            'header_text_title',
            'header_usermenu_title',
            'header_custommenu_title',
            'header_dockpanel_title',
        ];
    }

    private function get_page_sections_footer() {
        return ['footer_title'];
    }

    private function get_page_sections_color() {
        $colorsections = [
            'theme_color_title',
        ];
        foreach (self::colorzones as $colorzone)
        {
            $colorelements = $this->get_color_zone_elements($colorzone);
            foreach($colorelements as $colorelement)
            {
                $colorsections[] = 'color_'.$colorzone.'_title';
                $colorsections[] = 'color_'.$colorzone.'_title_'.$colorelement;
            }
        }
        return $colorsections;
    }

    private function get_page_sections_pagebacks() {
        $pagebacksheaders = [];
        foreach($this->get_pageback_zones() as $pageback)
        {
            $pagebacksheaders[] = 'pb_'.$pageback.'_title';
        }
        return $pagebacksheaders;
    }

    private function get_page_sections_custom_fonts() {
        return [];
    }

    private function get_page_sections_blocks() {
        $blocksheaders = [];
        foreach($this->get_theme_layouts() as $layoutname => $layoutdata)
        {
            $blocksheaders[] = 'layout_' . $layoutname . '_title';
        }
        return $blocksheaders;
    }

    private function get_page_sections_security() {
        return ['security_title'];
    }

    private function get_page_sections_loginpage_main() {
        $headers = ['loginpage_main_title'];
        switch($this->profilemanager->get_theme_setting('loginpage_main_type', $this->profile))
        {
            case 'sidebar':
                $headers[] = 'loginpage_sidebar_title';
                break;
            case 'slider':
                $headers[] = 'loginpage_slider_title';
                break;
        }
        return $headers;
    }

















    private function get_default_setting_params($name, $a=null) {
        $settingfullname = $this->profilemanager->get_theme_setting_name($name, $this->profile);
        $settingfullname = 'theme_opentechnology/'.$settingfullname;
        $title = '';
        $description = '';
        $stringman = get_string_manager();
        if ($stringman->string_exists('settings_'.$name, 'theme_opentechnology'))
        {
            $title = get_string('settings_'.$name, 'theme_opentechnology', $a);
        }
        if ($stringman->string_exists('settings_'.$name.'_desc', 'theme_opentechnology'))
        {
            $description = get_string('settings_'.$name.'_desc', 'theme_opentechnology', $a);
        }
        return [$settingfullname, $title, $description];
    }

    private function create_header_setting($name) {

        // если название настройки начинается с pb_, заменим на pageback, чтобы строки нашлись
        $name = preg_replace('/^pb_(.*)/', 'pageback_${1}', $name);

        if (preg_match('/^layout_(.*)_title$/', $name, $matches)) {
            $description = '';
            $title = get_string('layout_' . $matches[1], 'theme_opentechnology');
            return new admin_setting_heading($name, $title, $description);

        }

        foreach(self::colorzones as $colorzone)
        {
            $colorelements = $this->get_color_zone_elements($colorzone);
            if (preg_match('/^color_'.$colorzone.'_title_('.implode('|', $colorelements).')$/', $name, $matches)) {

                $name = $this->profilemanager->get_theme_setting_name($name, $this->profile);
                $name = 'theme_opentechnology/'.$name;

                $a = new \stdClass();
                $a->zone = get_string('color_zone_' . $colorzone, 'theme_opentechnology');
                $a->element = get_string('color_element_' . $matches[1], 'theme_opentechnology');

                $title = get_string('color_title', 'theme_opentechnology', $a);
                $description = '';


                return new admin_setting_heading($name, $title, $description);

            }
        }
        if (preg_match('/^color_('.implode('|', self::colorzones).')_title$/', $name, $matches)) {
            $colorzone = $matches[1];
            $name = $this->profilemanager->get_theme_setting_name($name, $this->profile);
            $name = 'theme_opentechnology/'.$name;
            $title = get_string('color_zone_'.$colorzone, 'theme_opentechnology');
            $description = '';
            return new admin_setting_heading($name, $title, $description);
        }

        list($name, $title, $description) = $this->get_default_setting_params($name);

        return new admin_setting_heading($name, $title, $description);
    }

    private function create_setting($name) {
        if (method_exists($this, 'create_setting_'.$name))
        {
            $defaultparams = $this->get_default_setting_params($name);
            return call_user_func_array([$this, 'create_setting_'.$name], $defaultparams);
        } else {
            if (strpos($name, 'pb_') === 0 || strpos($name, 'color_pb_') === 0)
            {
                foreach($this->get_pageback_zones() as $pageback) {
                    if (strpos($name, $pageback) !== false)
                    {
                        $settingname = str_replace('_'.$pageback, '', $name);
                        return call_user_func_array([$this, 'create_setting_'.$settingname], [$pageback]);
                    }
                }
            }

            if (strpos($name, 'custom_fonts_font_') === 0)
            {
                $customfontssettings = ['settings', 'family', 'weight', 'style'];
                foreach($customfontssettings as $customfontssetting)
                {
                    $settingprefix = 'custom_fonts_font_'.$customfontssetting;
                    if (strpos($name, $settingprefix) === 0)
                    {
                        return call_user_func_array(
                            [$this, 'create_setting_'.$settingprefix],
                            [substr($name, strlen($settingprefix)+1)]
                        );
                    }
                }
            }

            if (preg_match('/^layout_(.*)_collapsiblesection_(?:(.*)(_state)|(.*))?$/', $name, $matches)) {
                $collapsiblesectioncode = $matches[4] ?? $matches[2];
                $layoutname = $matches[1];
                $state = $matches[3]??'';
                return call_user_func_array(
                    [$this, 'create_setting_layout_collapsiblesection'.$state],
                    [$layoutname, $collapsiblesectioncode]
                );
            }

            $layouts = $this->get_theme_layouts();
            if (preg_match('/^region_('.implode('|',array_keys($layouts)).')_(.*)$/', $name, $matches)) {
                $layoutname = $matches[1];
                $region = str_replace('_', '-', $matches[2]);
                return call_user_func_array(
                    [$this, 'create_setting_region'],
                    [$layoutname, $region]
                );
            }


            if (preg_match('/^theme_color_(.*)$/', $name, $matches)) {
                $colorname = $matches[1];
                return call_user_func_array([$this, 'create_setting_theme_color'], [$colorname]);
            }

            foreach(self::colorzones as $colorzone)
            {
                $colorelements = $this->get_color_zone_elements($colorzone);
                $colorpattern = '/^color_'.$colorzone.'_('.implode('|', $colorelements).')(_icon_brightness|_text|_border|)$/';
                if(preg_match($colorpattern, $name, $matches))
                {
                    $colorelement = $matches[1];
                    $colorfunc = empty($matches[2]) ? 'bg' : substr($matches[2], 1);
                    $setting = call_user_func_array([$this, 'create_setting_color_zone_element'], [$colorzone, $colorelement, $colorfunc]);
                    return $setting;
                }
            }


            throw new \moodle_exception('unknown setting name');
        }
    }

    private function create_setting_main_langmenu($name, $title, $description) {
        // Отображение языка
        return new admin_setting_configselect($name, $title, $description, 0, [
            0 => get_string('settings_main_langmenu_dockpanel', 'theme_opentechnology'),
            1 => get_string('settings_main_langmenu_default', 'theme_opentechnology'),
            2 => get_string('settings_main_langmenu_inline', 'theme_opentechnology'),
        ]);

    }
    private function create_setting_main_dock_hide($name, $title, $description) {
        // Скрытие док-панели
        return new admin_setting_configselect($name, $title, $description, 1, [
            0 => get_string('settings_main_dock_hide_never', 'theme_opentechnology'),
            1 => get_string('settings_main_dock_hide_auto', 'theme_opentechnology')
        ]);
    }
    private function create_setting_main_fixed_width($name, $title, $description) {
        // Фиксированая ширина
        return new admin_setting_configselect($name, $title, $description, 0, [
            0 => get_string('settings_main_fixed_width_disable', 'theme_opentechnology'),
            1 => get_string('settings_main_fixed_width_enable', 'theme_opentechnology'),
        ]);

    }
    private function create_setting_main_modal_login($name, $title, $description) {
        // Авторизация в модальном окне
        return new admin_setting_configselect($name, $title, $description, 0, [
            0 => get_string('settings_main_modal_login_disable', 'theme_opentechnology'),
            1 => get_string('settings_main_modal_login_enable', 'theme_opentechnology'),
        ]);

    }
    private function create_setting_main_dockeditem_title($name, $title, $description) {
        // Заголовки элементов док-панели
        return new admin_setting_configselect($name, $title, $description, 2, [
            0 => get_string('settings_main_dockeditem_title_text', 'theme_opentechnology'),
            1 => get_string('settings_main_dockeditem_title_icon', 'theme_opentechnology'),
            2 => get_string('settings_main_dockeditem_title_depends_on_width', 'theme_opentechnology'),
            3 => get_string('settings_main_dockeditem_title_icon_and_text', 'theme_opentechnology'),
            4 => get_string('settings_main_dockeditem_title_icon_and_text_if_fit', 'theme_opentechnology')
        ]);

    }
    private function create_setting_main_dockeditem_title_default($name, $title, $description) {
        // Заголовки элементов док-панели
        return new admin_setting_configselect($name, $title, $description, 1, [
            0 => get_string('settings_main_dockeditem_title_default_text', 'theme_opentechnology'),
            1 => get_string('settings_main_dockeditem_title_default_icon', 'theme_opentechnology')
        ]);

    }
    private function create_setting_main_dockeditem_title_iconset($name, $title, $description) {
        // Используемый набор изображений
        $profilecode =$this->profile->get_code();
        return new admin_setting_configselect($name, $title, $description, '04', [
            $profilecode => get_string('settings_main_dockeditem_title_iconset_profile', 'theme_opentechnology', $profilecode),

            '01' => get_string('settings_main_dockeditem_title_iconset_01', 'theme_opentechnology'),
            '07' => get_string('settings_main_dockeditem_title_iconset_07', 'theme_opentechnology'),
            '08' => get_string('settings_main_dockeditem_title_iconset_08', 'theme_opentechnology'),

            '02' => get_string('settings_main_dockeditem_title_iconset_02', 'theme_opentechnology'),
            '03' => get_string('settings_main_dockeditem_title_iconset_03', 'theme_opentechnology'),
            '04' => get_string('settings_main_dockeditem_title_iconset_04', 'theme_opentechnology'),
            '05' => get_string('settings_main_dockeditem_title_iconset_05', 'theme_opentechnology'),
            '06' => get_string('settings_main_dockeditem_title_iconset_06', 'theme_opentechnology'),

            '09' => get_string('settings_main_dockeditem_title_iconset_09', 'theme_opentechnology'),
            '10' => get_string('settings_main_dockeditem_title_iconset_10', 'theme_opentechnology'),
            '11' => get_string('settings_main_dockeditem_title_iconset_11', 'theme_opentechnology'),
            '12' => get_string('settings_main_dockeditem_title_iconset_12', 'theme_opentechnology'),

            '13' => get_string('settings_main_dockeditem_title_iconset_13', 'theme_opentechnology'),
            '14' => get_string('settings_main_dockeditem_title_iconset_14', 'theme_opentechnology'),
            '15' => get_string('settings_main_dockeditem_title_iconset_15', 'theme_opentechnology'),
            '16' => get_string('settings_main_dockeditem_title_iconset_16', 'theme_opentechnology'),
            '17' => get_string('settings_main_dockeditem_title_iconset_17', 'theme_opentechnology'),
        ]);
    }
    private function create_setting_main_spelling_mistake($name, $title, $description) {
        // Отправка орфографической ошибки в тексте
        return new admin_setting_configselect($name, $title, $description, 1, [
            0 => get_string('settings_main_spelling_mistake_disable', 'theme_opentechnology'),
            1 => get_string('settings_main_spelling_mistake_enable', 'theme_opentechnology'),
        ]);

    }
    private function create_setting_main_customcss($name, $title, $description) {
        // Дополнительный CSS подвала
        $cssprocessor = new cssprocessor();
        $shortname = $this->profilemanager->get_theme_setting_name('main_customcss', $this->profile);
        $description = get_string('settings_main_customcss_desc', 'theme_opentechnology');
        $setting = new admin_setting_configtextarea($name, $title, $description, '');
        $setting->set_updatedcallback('theme_opentechnology_purge_caches');
        return $setting;
    }
    private function create_setting_main_scsspre($name, $title, $description) {
        // инициализирующий код SCSS
        $setting = new admin_setting_configtextarea($name, $title, $description, '');
        $setting->set_updatedcallback('theme_opentechnology_purge_caches');
        return $setting;
    }
    private function create_setting_main_scssextra($name, $title, $description) {
        // инициализирующий код SCSS
        $setting = new admin_setting_configtextarea($name, $title, $description, '');
        $setting->set_updatedcallback('theme_opentechnology_purge_caches');
        return $setting;
    }
    private function create_setting_main_custombodyinnerclasses($name, $title, $description) {
        // Классы, которые надо докинуть в body
        return new admin_setting_configtext($name, $title, $description, '');
    }



    private function create_setting_responsive_tables($name, $title, $description) {
        // Адаптивность таблиц
        $setting = new admin_setting_configselect($name, $title, $description, 0, [
            0 => get_string('settings_responsive_tables_disable', 'theme_opentechnology'),
            1 => get_string('settings_responsive_tables_enable', 'theme_opentechnology'),
        ]);
        $setting->set_updatedcallback('theme_opentechnology_purge_caches');
        return $setting;
    }
    private function create_setting_gradereport_table($name, $title, $description) {
        // Вертикальные заголовки таблицы отчета по оценкам
        $setting = new admin_setting_configselect($name, $title, $description, 0, [
            0 => get_string('settings_gradereport_table_disable', 'theme_opentechnology'),
            1 => get_string('settings_gradereport_table_enable', 'theme_opentechnology'),
            2 => get_string('settings_gradereport_table_user_preference', 'theme_opentechnology')
        ]);
        $setting->set_updatedcallback('theme_opentechnology_purge_caches');
        return $setting;
    }



    private function create_setting_header_sticky($name, $title, $description) {
        // Прилипающая шапка
        $choices = [
            // 1 => get_string('settings_header_sticky_cshtop', 'theme_opentechnology'),
            // 2 => get_string('settings_header_sticky_headertoptext', 'theme_opentechnology'),
            3 => get_string('settings_header_sticky_header', 'theme_opentechnology'),
            4 => get_string('settings_header_sticky_dockpanel', 'theme_opentechnology'),
            // 5 => get_string('settings_header_sticky_regioncontentheading', 'theme_opentechnology'),
            6 => get_string('settings_header_sticky_navbar', 'theme_opentechnology'),
        ];
        $default = ['value' => 3, 'adv' => false];
        $setting = new \admin_setting_configselect_with_advanced($name, $title, $description, $default, $choices);
        $setting->set_updatedcallback('theme_opentechnology_purge_caches');
        return $setting;
    }
    private function create_setting_header_backgroundimage($name, $title, $description) {
        // Фоновое изображение
        $filearea = $this->profilemanager->get_theme_setting_filearea('header_backgroundimage', $this->profile);
        $setting = new admin_setting_configstoredfile($name, $title, $description, $filearea);
        $setting->set_updatedcallback('theme_opentechnology_purge_caches');
        return $setting;
    }
    private function create_setting_header_top_text($name, $title, $description) {
        // Текст верха шапки
        return new admin_setting_confightmleditor($name, $title, $description, '');
    }
    private function create_setting_header_logoimage($name, $title, $description) {
        // Логотип
        $filearea = $this->profilemanager->get_theme_setting_filearea('header_logoimage', $this->profile);
        $setting = new admin_setting_configstoredfile($name, $title, $description, $filearea);
        $setting->set_updatedcallback('theme_opentechnology_purge_caches');
        return $setting;
    }
    private function create_setting_header_logo_link($name, $title, $description) {
        // URL логотипа
        $default = new moodle_url('/');
        $setting = new admin_setting_configtext($name, $title, $description, (string)$default, PARAM_URL);
        $setting->set_updatedcallback('theme_opentechnology_purge_caches');
        return $setting;
    }
    private function create_setting_header_logo_text($name, $title, $description) {
        // Текст логотипа
        return new admin_setting_confightmleditor($name, $title, $description, '');
    }
    private function create_setting_header_logoimage_padding($name, $title, $description) {
        // Отступы логотипа
        $setting = new admin_setting_configtext($name, $title, $description, '');
        $setting->set_updatedcallback('theme_opentechnology_purge_caches');
        return $setting;
    }
    private function create_setting_header_text($name, $title, $description) {
        // Текст описания
        return new admin_setting_confightmleditor($name, $title, $description, '');
    }
    private function create_setting_header_text_padding($name, $title, $description) {
        // Отступы блока описания
        $setting = new admin_setting_configtext($name, $title, $description, '');
        $setting->set_updatedcallback('theme_opentechnology_purge_caches');
        return $setting;
    }
    private function create_setting_header_link_crw($name, $title, $description) {
        // Добавить кнопку с ссылкой на витрину
        return new admin_setting_configselect($name, $title, $description, 0, [
            0 => get_string('settings_header_link_crw_disable', 'theme_opentechnology'),
            1 => get_string('settings_header_link_crw_enable', 'theme_opentechnology'),
        ]);
    }
    private function create_setting_header_link_portfolio($name, $title, $description) {
        // Добавить кнопку с ссылкой на портфолио
        return new admin_setting_configselect($name, $title, $description, 0, [
            0 => get_string('settings_header_link_portfolio_disable', 'theme_opentechnology'),
            1 => get_string('settings_header_link_portfolio_enable', 'theme_opentechnology'),
        ]);
    }
    private function create_setting_header_link_unread_messages($name, $title, $description) {
        // Добавить кнопку-идикатор с ссылкой на сообщения
        return new admin_setting_configselect($name, $title, $description, 1, [
            0 => get_string('settings_header_link_unread_messages_disable', 'theme_opentechnology'),
            1 => get_string('settings_header_link_unread_messages_enable', 'theme_opentechnology'),
        ]);
    }
    private function create_setting_header_link_search($name, $title, $description) {
        // Добавить кнопку поиск
        return new admin_setting_configselect($name, $title, $description, 1, [
            0 => get_string('settings_header_link_search_disable', 'theme_opentechnology'),
            1 => get_string('settings_header_link_search_enable', 'theme_opentechnology'),
        ]);
    }
    private function create_setting_header_usermenu_padding($name, $title, $description) {
        // Отступы пользовательского меню
        $setting = new admin_setting_configtext($name, $title, $description, '');
        $setting->set_updatedcallback('theme_opentechnology_purge_caches');
        return $setting;
    }
    private function create_setting_header_usermenu_hide_caret($name, $title, $description) {
        // Скрыть/показать кнопку пользовательского меню
        $setting = new admin_setting_configselect($name, $title, $description, 0, [
            0 => get_string('user_menu_caret_hide', 'theme_opentechnology'),
            1 => get_string('user_menu_caret_show', 'theme_opentechnology'),
        ]);
        $setting->set_updatedcallback('theme_opentechnology_purge_caches');
        return $setting;
    }
    private function create_setting_header_custommenu_location($name, $title, $description) {
        // Расположение персонального меню
        return new admin_setting_configselect($name, $title, $description, 0, [
            6 => get_string('settings_header_custommenu_location_top_left', 'theme_opentechnology'),
            7 => get_string('settings_header_custommenu_location_top_right', 'theme_opentechnology'),
            1 => get_string('settings_header_custommenu_location_above_logo', 'theme_opentechnology'),
            4 => get_string('settings_header_custommenu_location_above_usermenu', 'theme_opentechnology'),
            5 => get_string('settings_header_custommenu_location_under_logo', 'theme_opentechnology'),
            2 => get_string('settings_header_custommenu_location_under_usermenu', 'theme_opentechnology'),
            0 => get_string('settings_header_custommenu_location_bottom_left', 'theme_opentechnology'),
            8 => get_string('settings_header_custommenu_location_bottom_right', 'theme_opentechnology'),
            3 => get_string('settings_header_custommenu_location_profile_custom_position', 'theme_opentechnology')
        ]);
    }
    private function create_setting_header_dockpanel_texture($name, $title, $description) {
        // Текстура док-панели

        global $CFG;

        $choices = ['' => get_string('settings_header_dockpanel_texture_none', 'theme_opentechnology')];
        $description = '';
        $files = glob($CFG->dirroot."/theme/opentechnology/pix/texture/*.png");
        if ( ! empty($files) )
        {// Есть загруженные текстуры
            $description = '<ul class="media-list">';
            foreach ( $files as $file )
            {
                // Получение имени файла
                $path = explode('/', $file);
                $path = end($path);
                $filename = explode('.', $path);
                $filename = reset($filename);
                // Добавление файла в список
                $choices[$filename] = $filename;
                // Добавление отображения текстуры
                $description .=
                '<li class="media">
                    <div class="media-body">
                        <span class="media-heading">'.$filename.'</span>
                        <div class="media">
                            <img class="media-object col-md-12" src="/theme/opentechnology/pix/texture/'.$path.'">
                        </div>
                    </div>
                </li>';
            }
            $description .= '</ul>';
        }
        $setting = new admin_setting_configselect($name, $title, $description, '', $choices);
        $setting->set_updatedcallback('theme_opentechnology_purge_caches');
        return $setting;
    }
    private function create_setting_header_dockpanel_header($name, $title, $description) {
        // Добавить заголовок страницы
        return new admin_setting_configselect($name, $title, $description, 1, [
            0 => get_string('settings_header_dockpanel_header_disable', 'theme_opentechnology'),
            1 => get_string('settings_header_dockpanel_header_enable', 'theme_opentechnology'),
        ]);
    }
    private function create_setting_header_content_header($name, $title, $description) {
        // Добавить заголовок страницы в контентную область главной страницы
        return new admin_setting_configselect($name, $title, $description, 0, [
            0 => get_string('settings_header_content_header_disable', 'theme_opentechnology'),
            1 => get_string('settings_header_content_header_enable', 'theme_opentechnology'),
        ]);

    }
    private function create_setting_footer_backgroundimage($name, $title, $description) {
        // Фоновое изображение
        $filearea = $this->profilemanager->get_theme_setting_filearea('footer_backgroundimage', $this->profile);
        $setting = new admin_setting_configstoredfile($name, $title, $description, $filearea);
        $setting->set_updatedcallback('theme_opentechnology_purge_caches');
        return $setting;
    }
    private function create_setting_footer_border_texture($name, $title, $description) {
        // Текстура рамки

        global $CFG;

        $choices = ['' => get_string('settings_footer_border_texture_none', 'theme_opentechnology')];
        $description = '';
        $files = glob($CFG->dirroot."/theme/opentechnology/pix/texture/*.png");
        if ( ! empty($files) )
        {// Есть загруженные текстуры
            $description = '<ul class="media-list">';
            foreach ( $files as $file )
            {
                // Получение имени файла
                $path = explode('/', $file);
                $path = end($path);
                $filename = explode('.', $path);
                $filename = reset($filename);
                // Добавление файла в список
                $choices[$filename] = $filename;
                // Добавление отображения текстуры
                $description .=
                '<li class="media">
                    <div class="media-body">
                        <span class="media-heading">'.$filename.'</span>
                        <div class="media">
                            <img class="media-object col-md-12" src="/theme/opentechnology/pix/texture/'.$path.'">
                        </div>
                    </div>
                </li>';
            }
            $description .= '</ul>';
        }
        $setting = new admin_setting_configselect($name, $title, $description, '', $choices);
        $setting->set_updatedcallback('theme_opentechnology_purge_caches');
        return $setting;
    }
    private function create_setting_footer_logoimage($name, $title, $description) {
        // Логотип
        $filearea = $this->profilemanager->get_theme_setting_filearea('footer_logoimage', $this->profile);
        $setting = new admin_setting_configstoredfile($name, $title, $description, $filearea);
        $setting->set_updatedcallback('theme_opentechnology_purge_caches');
        return $setting;
    }
    private function create_setting_footer_logoimage_width($name, $title, $description) {
        // Ширина логотипа
        $range = range(1, 12);
        return new admin_setting_configselect($name, $title, $description, 3, array_combine($range, $range));
    }
    private function create_setting_footer_logoimage_text($name, $title, $description) {
        // Текст описания к логотипу
        return new admin_setting_confightmleditor($name, $title, $description, '');
    }
    private function create_setting_footer_social_links($name, $title, $description) {
        // Ссылки на социальные сети
        return new admin_setting_configtextarea($name, $title, $description, '');
    }
    private function create_setting_footer_text($name, $title, $description) {
        // Текст описания
        return new admin_setting_confightmleditor($name, $title, $description, '');
    }
    private function create_setting_copyright_text($name, $title, $description) {
        // Текст копирайта
        return new admin_setting_confightmleditor($name, $title, $description, '');
    }




    private function create_setting_color_pb_backgroundcolor($pageback) {
        $name = $this->profilemanager->get_theme_setting_name('color_pb_'.$pageback.'_backgroundcolor', $this->profile);
        $name = 'theme_opentechnology/'.$name;
        $title = get_string('settings_pageback_'.$pageback.'_backgroundcolor', 'theme_opentechnology');
        $description = get_string('settings_pageback_'.$pageback.'_backgroundcolor_desc', 'theme_opentechnology');
        $setting = new colourpicker($name, $title, $description, '');
        $setting->set_updatedcallback('theme_opentechnology_purge_caches');
        return $setting;
    }
    private function create_setting_pb_backgroundimage($pageback) {
        $name = $this->profilemanager->get_theme_setting_name('pb_'.$pageback.'_backgroundimage', $this->profile);
        $name = 'theme_opentechnology/'.$name;
        $title = get_string('settings_pageback_'.$pageback.'_backgroundimage', 'theme_opentechnology');
        $description = get_string('settings_pageback_'.$pageback.'_backgroundimage_desc', 'theme_opentechnology');
        $filearea = $this->profilemanager->get_theme_setting_filearea('pb_'.$pageback.'_backgroundimage', $this->profile);
        $setting = new admin_setting_configstoredfile($name, $title, $description, $filearea);
        $setting->set_updatedcallback('theme_opentechnology_purge_caches');
        return $setting;
    }
    private function create_setting_pb_unlimit_width($pageback) {
        $name = $this->profilemanager->get_theme_setting_name('pb_'.$pageback.'_unlimit_width', $this->profile);
        $name = 'theme_opentechnology/'.$name;
        $title = get_string('settings_pageback_'.$pageback.'_unlimit_width', 'theme_opentechnology');
        $description = get_string('settings_pageback_'.$pageback.'_unlimit_width_desc', 'theme_opentechnology');
        $setting = new admin_setting_configcheckbox($name, $title, $description, 0);
        return $setting;
    }





    private function create_setting_custom_fonts_files($name, $title, $description) {
        // Загрузка шрифта
        $filearea = $this->profilemanager->get_theme_setting_filearea('custom_fonts_files', $this->profile);
        $setting = new admin_setting_configstoredfile($name, $title, $description, $filearea, 0, [
            'maxfiles' => 20,
            'accepted_types' => '.ttf'
        ]);
        $setting->set_updatedcallback('theme_opentechnology_purge_caches');
        return $setting;
    }






    private function create_setting_custom_fonts_font_settings($fontfilesettingname) {
        // Набор свойств шрифта

        $name = $this->profilemanager->get_theme_setting_name('custom_fonts_files', $this->profile);
        $filearea = $this->profilemanager->get_theme_setting_filearea('custom_fonts_files', $this->profile);
        $fontfiles = theme_opentechnology_get_filearea_files($filearea, $name, 0);

        foreach ($fontfiles as $fontfile)
        {
            if ($fontfile->settingname == $fontfilesettingname)
            {
                $name = $this->profilemanager->get_theme_setting_name(
                    'custom_fonts_font_settings_'.$fontfile->settingname,
                    $this->profile);
                $name = 'theme_opentechnology/'.$name;
                $title = get_string('settings_custom_fonts_font_settings', 'theme_opentechnology', $fontfile->filename);
                $description = get_string('settings_custom_fonts_font_settings_desc', 'theme_opentechnology', $fontfile->filename);
                $setting = new admin_setting_heading($name, $title, $description);
                $setting->set_updatedcallback('theme_opentechnology_purge_caches');
                return $setting;
            }
        }
    }
    private function create_setting_custom_fonts_font_family($fontfilesettingname) {
        // font-family

        $name = $this->profilemanager->get_theme_setting_name('custom_fonts_files', $this->profile);
        $filearea = $this->profilemanager->get_theme_setting_filearea('custom_fonts_files', $this->profile);
        $fontfiles = theme_opentechnology_get_filearea_files($filearea, $name, 0);

        foreach ($fontfiles as $fontfile)
        {
            if ($fontfile->settingname == $fontfilesettingname)
            {
                $name = $this->profilemanager->get_theme_setting_name(
                    'custom_fonts_font_family_'.$fontfile->settingname,
                    $this->profile);
                $name = 'theme_opentechnology/'.$name;
                $title = get_string('settings_custom_fonts_font_family', 'theme_opentechnology', $fontfile->filename);
                $description = get_string('settings_custom_fonts_font_family_desc', 'theme_opentechnology', $fontfile->filename);
                $setting = new admin_setting_configtext($name, $title, $description, 'DefaultFont');
                $setting->set_updatedcallback('theme_opentechnology_purge_caches');
                return $setting;
            }
        }
    }
    private function create_setting_custom_fonts_font_weight($fontfilesettingname) {
        // Толщина шрифта
        $fontweights = [
            '100' => '100',
            '200' => '200',
            '300' => '300',
            '400' => '400',
            '500' => '500',
            '600' => '600',
            '700' => '700',
            '800' => '800',
            '900' => '900'
        ];

        $name = $this->profilemanager->get_theme_setting_name('custom_fonts_files', $this->profile);
        $filearea = $this->profilemanager->get_theme_setting_filearea('custom_fonts_files', $this->profile);
        $fontfiles = theme_opentechnology_get_filearea_files($filearea, $name, 0);

        foreach ($fontfiles as $fontfile)
        {
            if ($fontfile->settingname == $fontfilesettingname)
            {
                $name = $this->profilemanager->get_theme_setting_name(
                    'custom_fonts_font_weight_'.$fontfile->settingname,
                    $this->profile);
                $name = 'theme_opentechnology/'.$name;
                $title = get_string('settings_custom_fonts_font_weight', 'theme_opentechnology', $fontfile->filename);
                $description = get_string('settings_custom_fonts_font_weight_desc', 'theme_opentechnology', $fontfile->filename);
                $setting = new admin_setting_configselect($name, $title, $description, '400', $fontweights);
                $setting->set_updatedcallback('theme_opentechnology_purge_caches');
                return $setting;
            }
        }

    }
    private function create_setting_custom_fonts_font_style($fontfilesettingname) {

        // Кегель шрифта
        $fontstyles = [
            'normal' => 'normal',
            'italic' => 'italic'
        ];

        $name = $this->profilemanager->get_theme_setting_name('custom_fonts_files', $this->profile);
        $filearea = $this->profilemanager->get_theme_setting_filearea('custom_fonts_files', $this->profile);
        $fontfiles = theme_opentechnology_get_filearea_files($filearea, $name, 0);

        foreach ($fontfiles as $fontfile)
        {
            if ($fontfile->settingname == $fontfilesettingname)
            {
                $name = $this->profilemanager->get_theme_setting_name(
                    'custom_fonts_font_style_'.$fontfile->settingname,
                    $this->profile);
                $name = 'theme_opentechnology/'.$name;
                $title = get_string('settings_custom_fonts_font_style', 'theme_opentechnology', $fontfile->filename);
                $description = get_string('settings_custom_fonts_font_style_desc', 'theme_opentechnology', $fontfile->filename);
                $setting = new admin_setting_configselect($name, $title, $description, 'normal', $fontstyles);
                $setting->set_updatedcallback('theme_opentechnology_purge_caches');
                return $setting;
            }
        }
    }











    private function create_setting_security_nojs_text($name, $title, $description) {
        // Текст уведомления об отключенном Javascript
        $default = get_string('settings_security_nojs_text_default', 'theme_opentechnology');
        return new admin_setting_configtext($name, $title, $description, $default);
    }
    private function create_setting_security_copy_draganddrop($name, $title, $description) {
        // Запрет перетаскивания
        $setting = new admin_setting_configselect($name, $title, $description, 0, [
            0 => get_string('settings_security_copy_draganddrop_disable', 'theme_opentechnology'),
            1 => get_string('settings_security_copy_draganddrop_enable', 'theme_opentechnology'),
        ]);
        $setting->set_updatedcallback('theme_opentechnology_purge_caches');
        return $setting;
    }
    private function create_setting_security_copy_contextmenu($name, $title, $description) {
        // Запрет контекстного меню
        $setting = new admin_setting_configselect($name, $title, $description, 0, [
            0 => get_string('settings_security_copy_contextmenu_disable', 'theme_opentechnology'),
            1 => get_string('settings_security_copy_contextmenu_enable', 'theme_opentechnology'),
        ]);
        $setting->set_updatedcallback('theme_opentechnology_purge_caches');
        return $setting;
    }
    private function create_setting_security_copy_copy($name, $title, $description) {
        // Запрет копирования текста
        $setting = new admin_setting_configselect($name, $title, $description, 0, [
            0 => get_string('settings_security_copy_copy_disable', 'theme_opentechnology'),
            1 => get_string('settings_security_copy_copy_enable', 'theme_opentechnology'),
        ]);
        $setting->set_updatedcallback('theme_opentechnology_purge_caches');
        return $setting;
    }
    private function create_setting_security_copy_nojsaccess($name, $title, $description) {
        // Запрет доступа с отключенным JS
        $setting = new admin_setting_configselect($name, $title, $description, 0, [
            0 => get_string('settings_security_copy_nojsaccess_disable', 'theme_opentechnology'),
            1 => get_string('settings_security_copy_nojsaccess_enable', 'theme_opentechnology'),
        ]);
        $setting->set_updatedcallback('theme_opentechnology_purge_caches');
        return $setting;
    }









    private function create_setting_loginpage_main_type($name, $title, $description) {
        // Тип страницы авторизации
        $setting = new admin_setting_configselect($name, $title, $description, '', [
            '' => get_string('settings_loginpage_main_type_standard', 'theme_opentechnology'),
            'slider' => get_string('settings_loginpage_main_type_slider', 'theme_opentechnology'),
            'sidebar' => get_string('settings_loginpage_main_type_sidebar', 'theme_opentechnology'),
        ]);
        return $setting;
    }
    private function create_setting_loginpage_slider_images($name, $title, $description) {
        // Изображения для слайдера на странице авторизации типа слайдер
        $options = ['maxfiles' => 10, 'accepted_types' => 'image'];
        $filearea = $this->profilemanager->get_theme_setting_filearea('loginpage_slider_images', $this->profile);
        $setting = new admin_setting_configstoredfile($name, $title, $description, $filearea, 0, $options);
        $setting->set_updatedcallback('theme_opentechnology_purge_caches');
        return $setting;
    }
    private function create_setting_loginpage_header_text($name, $title, $description) {
        // Текст в шапке на странице авторизации типа слайдер
        return new admin_setting_confightmleditor($name, $title, $description, '');
    }
    private function create_setting_loginpage_header_text_padding($name, $title, $description) {
        // Отступы текста шапки на странице авторизации типа слайдер
        $setting = new admin_setting_configtext($name, $title, $description, '');
        $setting->set_updatedcallback('theme_opentechnology_purge_caches');
        return $setting;
    }
    private function create_setting_loginpage_sidebar_logoimage($name, $title, $description) {
        // Логотип на странице авторизации типа боковая панель
        $filearea = $this->profilemanager->get_theme_setting_filearea('loginpage_sidebar_logoimage', $this->profile);
        $setting = new admin_setting_configstoredfile($name, $title, $description, $filearea);
        $setting->set_updatedcallback('theme_opentechnology_purge_caches');
        return $setting;
    }
    private function create_setting_loginpage_sidebar_images($name, $title, $description) {
        // Изображение для фона на странице авторизации типа боковая панель
        $options = ['maxfiles' => 1, 'accepted_types' => 'image'];
        $filearea = $this->profilemanager->get_theme_setting_filearea('loginpage_sidebar_images', $this->profile);
        $setting = new admin_setting_configstoredfile($name, $title, $description, $filearea, 0, $options);
        $setting->set_updatedcallback('theme_opentechnology_purge_caches');
        return $setting;
    }
    private function create_setting_loginpage_sidebar_header_elements($name, $title, $description) {
        // Настройки шапки на странице авторизации типа боковая панель
        return new \admin_setting_configmulticheckbox($name, $title, $description, null, [
            'usernav' => get_string('loginpage_sidebar_header_element_usernav', 'theme_opentechnology'),
            'custommenu' => get_string('loginpage_sidebar_header_element_custommenu', 'theme_opentechnology'),
        ]);
    }
    private function create_setting_loginpage_sidebar_text($name, $title, $description) {
        // Текст в шапке на странице авторизации типа боковая панель
        return new admin_setting_confightmleditor($name, $title, $description, '');
    }












    private function create_setting_layout_collapsiblesection($layoutname, $collapsiblesectioncode) {
        // Настройка позиций блоков сворачиваемой секции

        $collapsiblesections = theme_opentechnology_get_known_collapsiblesections();
        $collapsiblesection = $collapsiblesections[$collapsiblesectioncode];

        // Объект, передаваемый в языковую строку
        $a = new \stdClass();
        // наименование сворачиваемой секции
        $a->collapsiblesection = $collapsiblesection['name'];
        // наименование зоны (layout)
        $a->layout = get_string('layout_' . $layoutname, 'theme_opentechnology');


        $name = 'theme_opentechnology/' . $this->profilemanager->get_theme_setting_name(
            'layout_' . $layoutname . '_collapsiblesection_' . $collapsiblesection['code'],
            $this->profile
        );
        $title = get_string('settings_collapsiblesection', 'theme_opentechnology', $a);
        $description = get_string('settings_collapsiblesection_desc', 'theme_opentechnology', $a);

        return new gridsetter($name, $title, $description, '', 0);
    }
    private function create_setting_layout_collapsiblesection_state($layoutname, $collapsiblesectioncode) {
        // Состояние сворачиваемой секции

        $collapsiblesections = theme_opentechnology_get_known_collapsiblesections();
        $collapsiblesection = $collapsiblesections[$collapsiblesectioncode];

        // Объект, передаваемый в языковую строку
        $a = new \stdClass();
        // наименование сворачиваемой секции
        $a->collapsiblesection = $collapsiblesection['name'];
        // наименование зоны (layout)
        $a->layout = get_string('layout_' . $layoutname, 'theme_opentechnology');

        $name = 'theme_opentechnology/' . $this->profilemanager->get_theme_setting_name(
            'layout_'.$layoutname.'_collapsiblesection_'.$collapsiblesection['code'].'_state',
            $this->profile
        );
        $title = get_string('settings_collapsiblesection_state', 'theme_opentechnology', $a);
        $description = get_string('settings_collapsiblesection_state_desc', 'theme_opentechnology', $a);

        return new admin_setting_configselect($name, $title, $description, 0, [
            0 => get_string('settings_collapsiblesection_defaultstate_collapse', 'theme_opentechnology'),
            1 => get_string('settings_collapsiblesection_defaultstate_expand', 'theme_opentechnology'),
            2 => get_string('settings_collapsiblesection_defaultstate_fixcollapse', 'theme_opentechnology'),
            3 => get_string('settings_collapsiblesection_defaultstate_fixexpand', 'theme_opentechnology'),
            4 => get_string('settings_collapsiblesection_forcedstate_fixcollapse', 'theme_opentechnology'),
            5 => get_string('settings_collapsiblesection_forcedstate_fixexpand', 'theme_opentechnology')
        ]);
    }
    private function create_setting_region($layoutname, $region) {
        // Состояние позиции блоков

        $layouts = $this->get_theme_layouts();
        $layoutdata = $layouts[$layoutname];

        $name = $this->profilemanager->get_theme_setting_name('region_' . $layoutname . '_' . $region, $this->profile);
        $name = 'theme_opentechnology/'.$name;
        $name = str_replace('-', '_', $name);
        $title = get_string('region-' . $region, 'theme_opentechnology');
        $description = '';
        $default = !empty($layoutdata['defaultregiondocking'][$region]) ? $layoutdata['defaultregiondocking'][$region] : 'standard';

        return new admin_setting_configselect($name, $title, $description, $default,[
            'standard' => get_string('region_standard', 'theme_opentechnology'),
            'dock' => get_string('region_dock', 'theme_opentechnology'),
        ]);
    }

    private function create_setting_main_favicon($name, $title, $description) {
        $filearea = $this->profilemanager->get_theme_setting_filearea('main_favicon', $this->profile);
        $setting = new admin_setting_configstoredfile($name, $title, $description, $filearea, 0, [
            'accepted_types' => '.ico'
        ]);
        $setting->set_updatedcallback('theme_opentechnology_purge_caches');
        return $setting;
    }

    private function create_setting_main_public_file($name, $title, $description) {
        $filearea = $this->profilemanager->get_theme_setting_filearea('main_public_file', $this->profile);
        $files = theme_opentechnology_get_filearea_files($filearea, $name, 0);
        $html = html_writer::empty_tag('br');
        $data = [];
        foreach ($files as $file) {
            if (! isset($data[$file->path])) {
                $data[$file->path] = [];
            }
            $data[$file->path][] = html_writer::link(
                $file->url->out_as_local_url(), $file->filename, ['target' => '_blank']);
        }
        foreach ($data as $path => $dirfiles) {
            $html .= get_string('settings_main_public_file_directory', 'theme_opentechnology', $path);
            $html .= html_writer::alist($dirfiles);
        }
        $description = $description . $html;
        $setting = new admin_setting_configstoredfile($name, $title, $description, $filearea, 0, [
            'maxfiles' => 1000, 'subdirs' => true
        ]);
        $setting->set_updatedcallback('theme_opentechnology_purge_caches');
        return $setting;
    }



    private function create_setting_theme_color($colorname) {
        // Настройка цвета темы
        list($name, $title, $description) = $this->get_default_setting_params('theme_color_'.$colorname);
        $setting = new colourpicker($name, $title, $description, '');
        $setting->set_updatedcallback('theme_opentechnology_purge_caches');
        return $setting;
    }
    private function create_setting_color_zone_element($colorzone, $colorelement, $colorfunc) {

        $a = new \stdClass();
        $a->zone = get_string('color_zone_'.$colorzone, 'theme_opentechnology');
        $a->element = get_string('color_element_'.$colorelement, 'theme_opentechnology');
        $a->func = get_string('color_func_'.$colorfunc, 'theme_opentechnology');


        $name = 'color_'.$colorzone.'_'.$colorelement . ($colorfunc == 'bg' ? '' : '_'.$colorfunc);
        $name = $this->profilemanager->get_theme_setting_name($name, $this->profile);
        $name = 'theme_opentechnology/'.$name;

        $title = get_string('element_color', 'theme_opentechnology', $a);
        $description = get_string('element_color', 'theme_opentechnology', $a);

        switch($colorfunc)
        {
            case 'bg':
            case 'text':
            case 'border':
                $setting = new colourpicker($name, $title, $description, '');
                $setting->set_updatedcallback('theme_opentechnology_purge_caches');
                break;
            case 'icon_brightness':
                $setting = new admin_setting_configselect($name, $title, $description, '0', [
                0 => get_string('settings_icon_brightness_auto', 'theme_opentechnology'),
                1 => get_string('settings_icon_brightness_0', 'theme_opentechnology'),
                2 => get_string('settings_icon_brightness_70', 'theme_opentechnology'),
                3 => get_string('settings_icon_brightness_100', 'theme_opentechnology'),
                4 => get_string('settings_icon_brightness_175', 'theme_opentechnology'),
                5 => get_string('settings_icon_brightness_300', 'theme_opentechnology'),
                ]);
                $setting->set_updatedcallback('theme_opentechnology_purge_caches');
                break;
        }

        return $setting;
    }
//     private function create_setting_($name, $title, $description) {

//         return $setting;
//     }
//     private function create_setting_($name, $title, $description) {

//         return $setting;
//     }
//     private function create_setting_($name, $title, $description) {

//         return $setting;
//     }
//     private function create_setting_($name, $title, $description) {

//         return $setting;
//     }
//     private function create_setting_($name, $title, $description) {

//         return $setting;
//     }
//     private function create_setting_($name, $title, $description) {

//         return $setting;
//     }
//     private function create_setting_($name, $title, $description) {

//         return $setting;
//     }
//     private function create_setting_($name, $title, $description) {

//         return $setting;
//     }
//     private function create_setting_($name, $title, $description) {

//         return $setting;
//     }
//     private function create_setting_($name, $title, $description) {

//         return $setting;
//     }
//     private function create_setting_($name, $title, $description) {

//         return $setting;
//     }
//     private function create_setting_($name, $title, $description) {

//         return $setting;
//     }
//     private function create_setting_($name, $title, $description) {

//         return $setting;
//     }
//     private function create_setting_($name, $title, $description) {

//         return $setting;
//     }
//     private function create_setting_($name, $title, $description) {

//         return $setting;
//     }
//     private function create_setting_($name, $title, $description) {

//         return $setting;
//     }
//     private function create_setting_($name, $title, $description) {

//         return $setting;
//     }
//     private function create_setting_($name, $title, $description) {

//         return $setting;
//     }
//     private function create_setting_($name, $title, $description) {

//         return $setting;
//     }
//     private function create_setting_($name, $title, $description) {

//         return $setting;
//     }
//     private function create_setting_($name, $title, $description) {

//         return $setting;
//     }
//     private function create_setting_($name, $title, $description) {

//         return $setting;
//     }
//     private function create_setting_($name, $title, $description) {

//         return $setting;
//     }
//     private function create_setting_($name, $title, $description) {

//         return $setting;
//     }
//     private function create_setting_($name, $title, $description) {

//         return $setting;
//     }








    protected function get_profile_overrides_types($profile)
    {
        $result = [];

        $overridedir = $this->profilemanager->theme_config->dir.'/profiles/overrides';
        $knownpaths = [
            'core_methods' => $overridedir.'/'.$profile->get_code().'/'.$profile->get_code().'.php',
            'theme_layouts' => $overridedir.'/'.$profile->get_code().'/layouts/*.php',
            'theme_styles' => $overridedir.'/'.$profile->get_code().'/style/profile.css'
        ];
        foreach($knownpaths as $overridetype => $knownpath)
        {
            $files = glob($knownpath);
            if ( ! empty($files) )
            {
                $result[] = $overridetype;
            }
        }

        return $result;
    }
}
