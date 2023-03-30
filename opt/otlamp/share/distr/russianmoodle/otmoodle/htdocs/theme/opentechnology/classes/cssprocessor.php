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
 * Тема СЭО 3KL. Обработчик CSS Темы.
 *
 * @package    theme
 * @subpackage opentechnology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_opentechnology;

use html_writer;
use cache;
use theme_config;

class cssprocessor
{
    private $definedtemplates = [
//         'header_buttons',
//         'header_hovered_buttons',
//         'header_focused_buttons',
//         'header_active_buttons',
//         'content_buttons',
//         'content_hovered_buttons',
//         'content_focused_buttons',
//         'content_active_buttons',
//         'footer_buttons',
//         'footer_hovered_buttons',
//         'footer_focused_buttons',
//         'footer_active_buttons',
//         'course_section_header',
//         'block_header',
//         'collapsiblesection_buttons',
//         'collapsiblesection_hovered_buttons',
//         'collapsiblesection_focused_buttons',
//         'collapsiblesection_active_buttons',
//         'mod_subheader',
    ];
    private $template='';
    static private $profile;

    /**
     * Получение контента CSS-файла для указанного профиля
     *
     * @param string $profilecode
     */
    public static function get_profile_css($profilecode=null, $cssfile=null)
    {
        if (is_null($cssfile))
        {
            $cssfile = 'profile';
        }

        if (!is_null($profilecode))
        {
            // Получение профиля по коду
            $profile = profilemanager::instance()->get_profile((string)$profilecode);
        }

        if (!isset($profile))
        {// Профиль не найден
            // Получение профиля по умолчанию
            $profile = profilemanager::instance()->get_default_profile();
            if ( $profile == null )
            {// Профиль по умолчанию не найден
                return null;
            }
        }

        // Инициализация кэша профилей
        $cache = cache::make('theme_opentechnology', 'profilecss');

        self::$profile = $profile;
        // Получение кэшированных данных
        $profilecode = $profile->get_code();
        $profilecss = $cache->get($profilecode.'/'.$cssfile);
        if ( $profilecss === false )
        {// Генерация файла CSS

            // Получение CSS профиля
            $profilecss = self::build_profile_css($profile, $cssfile);
            if ( $profilecss === null )
            {// Ошибка генерации CSS
                return null;
            }
            $cache->set($profilecode.'/'.$cssfile, $profilecss);
        }

        return $profilecss;
    }

    /**
     * Генерация контента CSS-файла для указанного профиля
     *
     * @param base $profile - Профиль
     */
    private static function build_profile_css($profile, $cssfile='profile')
    {
        // Инициализация менеджера конфигурации темы
        $themeconfig = theme_config::load('opentechnology');

        $csscontent = $themeconfig->get_css_content();



        // Получение CSS для профилей
        $profilecss = '';
        if (file_exists($themeconfig->dir.'/style/'.$cssfile.'.css'))
        {
            $profilecss = file_get_contents($themeconfig->dir.'/style/'.$cssfile.'.css');
        }

        // Обработка CSS c учетом профиля
        $profilecss = theme_opentechnology_profile_process_css($profilecss, $themeconfig, $profile, $cssfile);

        // Обработка CSS без учета профиля
        $profilecss = $themeconfig->post_process($profilecss);



        return \core_minify::css($csscontent . $profilecss);
    }

    public static function get_extra_scss($theme) {
        global $CFG;

        $profile = self::$profile ?? profilemanager::instance()->get_default_profile();

        $scss= '';
        $scss .= file_get_contents($CFG->dirroot . '/theme/opentechnology/scss/otextra.scss');

        $profilesettingname = $profile->get_setting_name('main_scssextra');
        $scss .= isset($theme->settings->{$profilesettingname}) ? $theme->settings->{$profilesettingname} : '';

        return $scss;
    }

    public static function get_pre_scss($theme) {

        $profile = self::$profile ?? profilemanager::instance()->get_default_profile();

        $profilesettingname = $profile->get_setting_name('main_scsspre');
        $scss = isset($theme->settings->{$profilesettingname}) ? $theme->settings->{$profilesettingname} : '';

        // Prepend pre-scss.
        if (!empty($theme->settings->scsspre)) {
            $scss .= $theme->settings->scsspre;
        }

        $configurable = [];
        foreach(profilemanager::$themecolors as $colorname)
        {
            $targets = [$colorname];
            switch($colorname)
            {
                case 'primary':
                    // boost делает по умолчанию цвет ссылок подвала инвертированными, а мы сделаем по умолчанию primary
                    $targets[] = 'footer-link-color';
                    break;
            }
            $configurable['theme_color_'.$colorname] = $targets;
        }
        // Цвет рамки поля ввода
        $configurable['theme_color_input_border'] = ['input-border-color'];




        // сбор схожих настроек из разных зон
        $zones = ['header', 'navbar', 'content', 'blocks', 'footer', 'collapsiblesection'];
        foreach ($zones as $zone)
        {
            // Цвет фона
            $configurable['color_'.$zone.'_backgroundcolor'][] = 'ot-'.$zone.'-bg';
            // Цвет текста на фоне
            $configurable['color_'.$zone.'_backgroundcolor_text'][] = 'ot-'.$zone.'-color';
            // Изменение яркости иконок под цвет фона
            $configurable['color_'.$zone.'_backgroundcolor_icon_brightness'][] = 'ot-'.$zone.'-brightness-config';

            // Базовый цвет
            $configurable['color_'.$zone.'_basecolor'][] = 'ot-'.$zone.'-headings-bg';
            // Цвет текста под базовый цвет
            $configurable['color_'.$zone.'_basecolor_text'][] = 'ot-'.$zone.'-headings-color';
            // Изменение яркости иконок под базовый цвет
            $configurable['color_'.$zone.'_basecolor_icon_brightness'][] = 'ot-'.$zone.'-headings-brightness-config';

            // Сейчас настроек цвета ссылок под каждую зону нет, но стили прописываем так, чтобы в будущем можно было
            // создать настройки под каждую зону
            // Цвет ссылок

            // Внимание!!! Цвет фона ссылок (пока не используется в стилях)
            $configurable['color_'.$zone.'_linkscolor'][] = 'ot-'.$zone.'-link-bg';
            // Цвет текста ссылок
            $configurable['color_'.$zone.'_linkscolor_text'][] = 'ot-'.$zone.'-link-color';
            // Изменение яркости иконок под цвет ссылок (пока не используется в стилях)
            $configurable['color_'.$zone.'_linkscolor_icon_brightness'][] = 'ot-'.$zone.'-link-brightness-config';
            // Цвет фона ссылок при наведении (пока не используется в стилях)
            $configurable['color_'.$zone.'_linkscolor_active'][] = 'ot-'.$zone.'-link-hover-bg';
            // Цвет текста ссылок при наведении
            $configurable['color_'.$zone.'_linkscolor_active_text'][] = 'ot-'.$zone.'-link-hover-color';
            // Изменение яркости иконок под цвет ссылок при наведении (пока не используется в стилях)
            $configurable['color_'.$zone.'_linkscolor_active_icon_brightness'][] = 'ot-'.$zone.'-link-hover-brightness-config';

            // Цвет фона элементов
            $configurable['color_'.$zone.'_elementscolor'][] = 'ot-'.$zone.'-btn-primary-bg';
            $configurable['color_'.$zone.'_elementscolor'][] = 'ot-'.$zone.'-btn-secondary-bg';
            // Цвет рамки элементов
            $configurable['color_'.$zone.'_elementscolor_border'][] = 'ot-'.$zone.'-btn-primary-border';
            $configurable['color_'.$zone.'_elementscolor_border'][] = 'ot-'.$zone.'-btn-secondary-border';
            // Цвет текста элементов
            $configurable['color_'.$zone.'_elementscolor_text'][] = 'ot-'.$zone.'-btn-primary-color';
            $configurable['color_'.$zone.'_elementscolor_text'][] = 'ot-'.$zone.'-btn-secondary-color';
            // Изменение яркости иконок под цвет элементов
            $configurable['color_'.$zone.'_elementscolor_icon_brightness'][] = 'ot-'.$zone.'-btn-primary-brightness-config';
            $configurable['color_'.$zone.'_elementscolor_icon_brightness'][] = 'ot-'.$zone.'-btn-secondary-brightness-config';

            // Цвет фона активных элементов
            $configurable['color_'.$zone.'_elementscolor_active'][] = 'ot-'.$zone.'-btn-primary-hover-bg';
            $configurable['color_'.$zone.'_elementscolor_active'][] = 'ot-'.$zone.'-btn-secondary-hover-bg';
            // Цвет рамки активных элементов
            $configurable['color_'.$zone.'_elementscolor_active_border'][] = 'ot-'.$zone.'-btn-primary-hover-border';
            $configurable['color_'.$zone.'_elementscolor_active_border'][] = 'ot-'.$zone.'-btn-secondary-hover-border';
            // Цвет текста активных элементов
            $configurable['color_'.$zone.'_elementscolor_active_text'][] = 'ot-'.$zone.'-btn-primary-hover-color';
            $configurable['color_'.$zone.'_elementscolor_active_text'][] = 'ot-'.$zone.'-btn-secondary-hover-color';
            // Изменение яркости иконок под цвет активных элементов
            $configurable['color_'.$zone.'_elementscolor_active_icon_brightness'][] = 'ot-'.$zone.'-btn-primary-hover-brightness-config';
            $configurable['color_'.$zone.'_elementscolor_active_icon_brightness'][] = 'ot-'.$zone.'-btn-secondary-hover-brightness-config';
        }


        // переключатель выпадающего списка меню пользователя
        $configurable['color_header_usermenu_ddtoggle'][] = 'ot-header-usermenu-ddtoggle-bg';
        $configurable['color_header_usermenu_ddtoggle_text'][] = 'ot-header-usermenu-ddtoggle-color';
        $configurable['color_header_usermenu_ddtoggle_icon_brightness'][] = 'ot-header-usermenu-ddtoggle-brightness-config';
        // переключатель выпадающего списка меню пользователя ри наведении
        $configurable['color_header_usermenu_ddtoggle_active'][] = 'ot-header-usermenu-ddtoggle-hover-bg';
        $configurable['color_header_usermenu_ddtoggle_active_text'][] = 'ot-header-usermenu-ddtoggle-hover-color';
        $configurable['color_header_usermenu_ddtoggle_active_icon_brightness'][] = 'ot-header-usermenu-ddtoggle-hover-brightness-config';
        // выпадающий список меню пользователя
        $configurable['color_header_usermenu_ddmenu'][] = 'ot-header-usermenu-ddmenu-bg';
        $configurable['color_header_usermenu_ddmenu_text'][] = 'ot-header-usermenu-ddmenu-color';
        $configurable['color_header_usermenu_ddmenu_icon_brightness'][] = 'ot-header-usermenu-ddmenu-brightness-config';
        // выпадающий список меню пользователя при наведении
        $configurable['color_header_usermenu_ddmenu_active'][] = 'ot-header-usermenu-ddmenu-hover-bg';
        $configurable['color_header_usermenu_ddmenu_active_text'][] = 'ot-header-usermenu-ddmenu-hover-color';
        $configurable['color_header_usermenu_ddmenu_active_icon_brightness'][] = 'ot-header-usermenu-ddmenu-hover-brightness-config';

        // пункт настраиваемого меню
        $configurable['color_header_custommenu_item'][] = 'ot-header-custommenu-item-bg';
        $configurable['color_header_custommenu_item_text'][] = 'ot-header-custommenu-item-color';
        $configurable['color_header_custommenu_item_icon_brightness'][] = 'ot-header-custommenu-item-brightness-config';
        // пункт настраиваемого меню при наведении
        $configurable['color_header_custommenu_item_active'][] = 'ot-header-custommenu-item-hover-bg';
        $configurable['color_header_custommenu_item_active_text'][] = 'ot-header-custommenu-item-hover-color';
        $configurable['color_header_custommenu_item_active_icon_brightness'][] = 'ot-header-custommenu-item-hover-brightness-config';
        // переключатель выпадающего списка настраиваемого меню
        $configurable['color_header_custommenu_ddtoggle'][] = 'ot-header-custommenu-ddtoggle-bg';
        $configurable['color_header_custommenu_ddtoggle_text'][] = 'ot-header-custommenu-ddtoggle-color';
        $configurable['color_header_custommenu_ddtoggle_icon_brightness'][] = 'ot-header-custommenu-ddtoggle-brightness-config';
        // переключатель выпадающего списка настраиваемого меню при наведении
        $configurable['color_header_custommenu_ddtoggle_active'][] = 'ot-header-custommenu-ddtoggle-hover-bg';
        $configurable['color_header_custommenu_ddtoggle_active_text'][] = 'ot-header-custommenu-ddtoggle-hover-color';
        $configurable['color_header_custommenu_ddtoggle_active_icon_brightness'][] = 'ot-header-custommenu-ddtoggle-hover-brightness-config';
        // выпадающий список настраиваемого меню
        $configurable['color_header_custommenu_ddmenu'][] = 'ot-header-custommenu-ddmenu-bg';
        $configurable['color_header_custommenu_ddmenu_text'][] = 'ot-header-custommenu-ddmenu-color';
        $configurable['color_header_custommenu_ddmenu_icon_brightness'][] = 'ot-header-custommenu-ddmenu-brightness-config';
        // выпадающий список настраиваемого меню при наведении
        $configurable['color_header_custommenu_ddmenu_active'][] = 'ot-header-custommenu-ddmenu-hover-bg';
        $configurable['color_header_custommenu_ddmenu_active_text'][] = 'ot-header-custommenu-ddmenu-hover-color';
        $configurable['color_header_custommenu_ddmenu_active_icon_brightness'][] = 'ot-header-custommenu-ddmenu-hover-brightness-config';


        // ДОК
        // текст заголовка
        $configurable['color_dock_dockeditem_textview'][] = 'ot-dock-dockeditem-textview-bg';
        $configurable['color_dock_dockeditem_textview_text'][] = 'ot-dock-dockeditem-textview-color';
        $configurable['color_dock_dockeditem_textview_icon_brightness'][] = 'ot-dock-dockeditem-textview-brightness-config';
        // текст заголовка при наведении
        $configurable['color_dock_dockeditem_textview_active'][] = 'ot-dock-dockeditem-textview-hover-bg';
        $configurable['color_dock_dockeditem_textview_active_text'][] = 'ot-dock-dockeditem-textview-hover-color';
        $configurable['color_dock_dockeditem_textview_active_icon_brightness'][] = 'ot-dock-dockeditem-textview-hover-brightness-config';
        // иконка заголовка
        $configurable['color_dock_dockeditem_iconview'][] = 'ot-dock-dockeditem-iconview-bg';
        $configurable['color_dock_dockeditem_iconview_text'][] = 'ot-dock-dockeditem-iconview-color';
        $configurable['color_dock_dockeditem_iconview_icon_brightness'][] = 'ot-dock-dockeditem-iconview-brightness-config';
        // иконка заголовка при наведении
        $configurable['color_dock_dockeditem_iconview_active'][] = 'ot-dock-dockeditem-iconview-hover-bg';
        $configurable['color_dock_dockeditem_iconview_active_text'][] = 'ot-dock-dockeditem-iconview-hover-color';
        $configurable['color_dock_dockeditem_iconview_active_icon_brightness'][] = 'ot-dock-dockeditem-iconview-brightness-hover-config';




        // Цвет ссылок
        $configurable['color_links_color'] = ['link-color'];
        // Цвет ссылок при наведении
        $configurable['color_links_color_hover'] = ['link-hover-color'];


        $configurable['color_pb_breadcrumbs_backgroundcolor'] = ['ot-navbar-wrapper-bg'];

        // переопределение цвета ссылок подвала
        $configurable['color_footer_backgroundcolor_text'] = ['ot-footer-link-color'];

        // Prepend variables first.
        foreach ($configurable as $configkey => $targets) {
            $profilesettingname = $profile->get_setting_name($configkey);
            $value = isset($theme->settings->{$profilesettingname}) ? $theme->settings->{$profilesettingname} : null;
            if (empty($value)) {
                continue;
            }
            array_map(function($target) use (&$scss, $value) {
                $scss .= '$' . $target . ': ' . $value . ";\n";
            }, (array) $targets);
        }

        return $scss;

    }

    public function __construct($template='')
    {
        $this->change_template($template);
    }

    public function change_template($template)
    {
        if( in_array($template, $this->definedtemplates) )
        {
            $this->template = $template;
        } else
        {
            return false;
        }
    }

    public function get_selectors()
    {
        $selectors = "";
        switch($this->template)
        {
            case 'content_buttons':
                $selectors = [
                    '#dock input[type="submit"]',
                    '#dock .button',
                    '#dock .btn',
                    '#page-wrapper button:not([data-dismiss="alert"]):not(.vjs-big-play-button):not(.vjs-button)',
                    '#page-wrapper input.form-submit',
                    '#page-wrapper input[type="button"]',
                    '#page-wrapper input[type="submit"]',
                    '#page-wrapper input[type="reset"]',
                    '#page-wrapper .btn',
                    '#page-wrapper input#id_submitbutton',
                    '#page-wrapper input#id_submitbutton2',
                    '#page-wrapper .path-admin .buttons input[type="submit"]',
                    '#page-wrapper td.submit input',
                    '#page-wrapper .button',
                    '#page-wrapper .sc-form-submit',
                    '#page-wrapper .button.sc-modal-close',
                    '#page-wrapper #notice .singlebutton + .singlebutton input',
                    '#page-wrapper .submit.buttons input[name="cancel"]',
                    '#page-wrapper #page-mod-subcourse-view .actionbuttons .btn',
                    '#crw_formsearch #id_topblock_morelink',
                    '#crw_formsearch #id_submitbuttonmore',
                    '#crw_formsearch #fgroup_id_topblock .fgroup .crw_system_search_form_resetbutton',
                    '.button.sc-modal-close',
                    '.button.sc-form-submit',
                    '.breadcrumb-button input[type="submit"]',
                    '.lessonbutton > a',
                    '.addbloglink a',
                    '.forumpost.blog_entry .row.maincontent > .content .comment-ctrl .comment-area .fd a',
                    '.button',
                    '.btn',
                    '#block-region-content-heading input[type="submit"]',
                    '#block-region-content-heading .button',
                    '#block-region-content-heading .btn',
                    '#block-region-content-footing input[type="submit"]',
                    '#block-region-content-footing .button',
                    '#block-region-content-footing .btn',
                    '.moodle-dialogue-base input[type="button"]',
                    '.moodle-dialogue-base input[type="submit"]',
                    '.moodle-dialogue-base input[type="text"] + input[type="submit"]',
                    '#page-mod-quiz-edit .maxgrade input[type="submit"]',
                    'div.questionbankformforpopup div.modulespecificbuttonscontainer input[type="submit"].btn.btn-primary'
                ];
                break;
            case 'content_hovered_buttons':
                $selectors = [
                    '#dock input[type="submit"]:hover',
                    '#dock .button:hover',
                    '#dock .btn:hover',
                    '#page-wrapper button:not([data-dismiss="alert"]):not(.vjs-big-play-button):not(.vjs-button):hover',
                    '#page-wrapper input.form-submit:hover',
                    '#page-wrapper input[type="button"]:hover',
                    '#page-wrapper input[type="submit"]:hover',
                    '#page-wrapper input[type="reset"]:hover',
                    '#page-wrapper .btn:hover',
                    '#page-wrapper input#id_submitbutton:hover',
                    '#page-wrapper input#id_submitbutton2:hover',
                    '#page-wrapper .path-admin .buttons input[type="submit"]:hover',
                    '#page-wrapper td.submit input:hover',
                    '#page-wrapper .button:hover',
                    '#page-wrapper .sc-form-submit:hover',
                    '#page-wrapper .button.sc-modal-close:hover',
                    '#page-wrapper #notice .singlebutton + .singlebutton input:hover',
                    '#page-wrapper .submit.buttons input[name="cancel"]:hover',
                    '#page-wrapper #page-mod-subcourse-view .actionbuttons .btn:hover',
                    '#crw_formsearch #id_topblock_morelink:hover',
                    '#crw_formsearch #id_submitbuttonmore:hover',
                    '#crw_formsearch #fgroup_id_topblock .fgroup .crw_system_search_form_resetbutton:hover',
                    '.button.sc-modal-close:hover',
                    '.button.sc-form-submit:hover',
                    '.breadcrumb-button input[type="submit"]:hover',
                    '.lessonbutton > a:hover',
                    '.addbloglink a:hover',
                    '.forumpost.blog_entry .row.maincontent > .content .comment-ctrl .comment-area .fd a:hover',
                    '.button:hover',
                    '.btn:hover',
                    '#block-region-content-heading input[type="submit"]:hover',
                    '#block-region-content-heading .button:hover',
                    '#block-region-content-heading .btn:hover',
                    '#block-region-content-footing input[type="submit"]:hover',
                    '#block-region-content-footing .button:hover',
                    '#block-region-content-footing .btn:hover',
                    '.moodle-dialogue-base input[type="button"]:hover',
                    '.moodle-dialogue-base input[type="submit"]:hover',
                    '.moodle-dialogue-base input[type="text"] + input[type="submit"]:hover',
                    '#page-mod-quiz-edit .maxgrade input[type="submit"]:hover',
                    'div.questionbankformforpopup div.modulespecificbuttonscontainer input[type="submit"].btn.btn-primary:hover'
                ];
                break;
            case 'content_focused_buttons':
                $selectors = [
                    '#dock input[type="submit"]:focus',
                    '#dock .button:focus',
                    '#dock .btn:focus',
                    '#page-wrapper button:not([data-dismiss="alert"]):not(.vjs-big-play-button):not(.vjs-button):focus',
                    '#page-wrapper input.form-submit:focus',
                    '#page-wrapper input[type="button"]:focus',
                    '#page-wrapper input[type="submit"]:focus',
                    '#page-wrapper input[type="reset"]:focus',
                    '#page-wrapper .btn:focus',
                    '#page-wrapper input#id_submitbutton:focus',
                    '#page-wrapper input#id_submitbutton2:focus',
                    '#page-wrapper .path-admin .buttons input[type="submit"]:focus',
                    '#page-wrapper td.submit input:focus',
                    '#page-wrapper .button:focus',
                    '#page-wrapper .sc-form-submit:focus',
                    '#page-wrapper .button.sc-modal-close:focus',
                    '#page-wrapper #notice .singlebutton + .singlebutton input:focus',
                    '#page-wrapper .submit.buttons input[name="cancel"]:focus',
                    '#page-wrapper #page-mod-subcourse-view .actionbuttons .btn:focus',
                    '#crw_formsearch #id_topblock_morelink:focus',
                    '#crw_formsearch #id_submitbuttonmore:focus',
                    '#crw_formsearch #fgroup_id_topblock .fgroup .crw_system_search_form_resetbutton:focus',
                    '.button.sc-modal-close:focus',
                    '.button.sc-form-submit:focus',
                    '.breadcrumb-button input[type="submit"]:focus',
                    '.lessonbutton > a:focus',
                    '.addbloglink a:focus',
                    '.forumpost.blog_entry .row.maincontent > .content .comment-ctrl .comment-area .fd a:focus',
                    '.button:focus',
                    '.btn:focus',
                    '#block-region-content-heading input[type="submit"]:focus',
                    '#block-region-content-heading .button:focus',
                    '#block-region-content-heading .btn:focus',
                    '#block-region-content-footing input[type="submit"]:focus',
                    '#block-region-content-footing .button:focus',
                    '#block-region-content-footing .btn:focus',
                    '.moodle-dialogue-base input[type="button"]:focus',
                    '.moodle-dialogue-base input[type="submit"]:focus',
                    '.moodle-dialogue-base input[type="text"] + input[type="submit"]:focus',
                    '#page-mod-quiz-edit .maxgrade input[type="submit"]:focus',
                    'div.questionbankformforpopup div.modulespecificbuttonscontainer input[type="submit"].btn.btn-primary:focus'
                ];
                break;
            case 'content_active_buttons':
                $selectors = [
                    '#dock input[type="submit"]:active',
                    '#dock .button:active',
                    '#dock .btn:active',
                    '#page-wrapper button:not([data-dismiss="alert"]):not(.vjs-big-play-button):not(.vjs-button):active',
                    '#page-wrapper input.form-submit:active',
                    '#page-wrapper input[type="button"]:active',
                    '#page-wrapper input[type="submit"]:active',
                    '#page-wrapper input[type="reset"]:active',
                    '#page-wrapper .btn:active',
                    '#page-wrapper input#id_submitbutton:active',
                    '#page-wrapper input#id_submitbutton2:active',
                    '#page-wrapper .path-admin .buttons input[type="submit"]:active',
                    '#page-wrapper td.submit input:active',
                    '#page-wrapper .button:active',
                    '#page-wrapper .sc-form-submit:active',
                    '#page-wrapper .button.sc-modal-close:active',
                    '#page-wrapper #notice .singlebutton + .singlebutton input:active',
                    '#page-wrapper .submit.buttons input[name="cancel"]:active',
                    '#page-wrapper #page-mod-subcourse-view .actionbuttons .btn:active',
                    '#crw_formsearch #id_topblock_morelink:active',
                    '#crw_formsearch #id_submitbuttonmore:active',
                    '#crw_formsearch #fgroup_id_topblock .fgroup .crw_system_search_form_resetbutton:active',
                    '.button.sc-modal-close:active',
                    '.button.sc-form-submit:active',
                    '.breadcrumb-button input[type="submit"]:active',
                    '.lessonbutton > a:active',
                    '.addbloglink a:active',
                    '.forumpost.blog_entry .row.maincontent > .content .comment-ctrl .comment-area .fd a:active',
                    '.button:active',
                    '.btn:active',
                    '#block-region-content-heading input[type="submit"]:active',
                    '#block-region-content-heading .button:active',
                    '#block-region-content-heading .btn:active',
                    '#block-region-content-footing input[type="submit"]:active',
                    '#block-region-content-footing .button:active',
                    '#block-region-content-footing .btn:active',
                    '.moodle-dialogue-base input[type="button"]:active',
                    '.moodle-dialogue-base input[type="submit"]:active',
                    '.moodle-dialogue-base input[type="text"] + input[type="submit"]:active',
                    '#page-mod-quiz-edit .maxgrade input[type="submit"]:active',
                    'div.questionbankformforpopup div.modulespecificbuttonscontainer input[type="submit"].btn.btn-primary:active'
                ];
                break;

            case 'footer_buttons':
                $selectors = [
                    '#footer_wrapper .button',
                    '#footer_wrapper .btn',
                ];
                break;
            case 'footer_hovered_buttons':
                $selectors = [
                    '#footer_wrapper .button:hover',
                    '#footer_wrapper .btn:hover',
                ];
                break;
            case 'footer_focused_buttons':
                $selectors = [
                    '#footer_wrapper .button:focus',
                    '#footer_wrapper .btn:focus',
                ];
                break;
            case 'footer_active_buttons':
                $selectors = [
                    '#footer_wrapper .button:active',
                    '#footer_wrapper .btn:active',
                ];
                break;
            case 'header_buttons':
                $selectors = [
                    '#page-header .btn',
                    '#h_rightblock_wrapper .usermenu .moodle-actionmenu .toggle-display .caret',
                    '#h_rightblock_wrapper .usermenu .login a',
                    '#h_rightblock_wrapper .header_link',
                    '#h_rightblock_wrapper .search-input-wrapper > div'
                ];
                break;
            case 'header_hovered_buttons':
                $selectors = [
                    '#page-header .btn:hover',
                    '#h_rightblock_wrapper .usermenu .moodle-actionmenu .toggle-display:hover .caret',
                    '#h_rightblock_wrapper .usermenu .login a:hover',
                    '#h_rightblock_wrapper .header_link:hover',
                    '#h_rightblock_wrapper .search-input-wrapper > div:hover',
                    '#h_rightblock_wrapper .search-input-wrapper.expanded > div'
                ];
                break;
            case 'header_focused_buttons':
                $selectors = [
                    '#page-header .btn:focus',
                    '#h_rightblock_wrapper .usermenu .moodle-actionmenu .toggle-display:focus .caret',
                    '#h_rightblock_wrapper .usermenu .login a:focus',
                    '#h_rightblock_wrapper .header_link:focus',
                    '#h_rightblock_wrapper .search-input-wrapper > div:focus'
                ];
                break;
            case 'header_active_buttons':
                $selectors = [
                    '#page-header .btn:active',
                    '#h_rightblock_wrapper .usermenu .moodle-actionmenu .toggle-display:active .caret',
                    '#h_rightblock_wrapper .usermenu .login a:active',
                    '#h_rightblock_wrapper .header_link:active',
                    '#h_rightblock_wrapper .search-input-wrapper > div:active'
                ];
                break;
            case 'course_section_header':
                $selectors = [
                    '#page-wrapper ul.format_opentechnology_sections > li > .content > .sectionname',
                    '#page-wrapper ul.format_opentechnology_sections > li > .content > .sectionhead',
                    '#page-wrapper ul.topics > li > .content > .sectionname',
                    '#page-wrapper ul.weeks > li > .content > .sectionname',
                    '#page-wrapper ul.format_opentechnology_sections > li > .content > .sectionname a',
                    '#page-wrapper ul.format_opentechnology_sections > li > .content > .sectionhead a',
                    '#page-wrapper ul.topics > li > .content > .sectionname a',
                    '#page-wrapper ul.weeks > li > .content > .sectionname a'
                ];
                break;
            case 'block_header':
                $selectors = [
                    '.block > .header h2',
                    '#block-region-side-post .block > .header h2',
                    '#block-region-side-pre .block > .header h2',
                    '#block-region-side-content-top .block > .header h2',
                    '#block-region-side-content-bot .block > .header h2',
                    '#block-region-content .block > .header h2',
                    '.has_dock_top_horizontal #dock #dockeditempanel .dockeditempanel_hd h2'
                ];
                break;
            case 'collapsiblesection_buttons':
                $selectors = [
                    '.collapsible-section .button',
                    '.collapsible-section .btn',
                    '.collapsible-section-switcher-label .collapsible-section-switcher-label-text',
                    '.collapsible-section-switcher-label::before',
                ];
                break;
            case 'collapsiblesection_hovered_buttons':
                $selectors = [
                    '.collapsible-section .button:hover',
                    '.collapsible-section .btn:hover',
                    '.collapsible-section-switcher-label:hover .collapsible-section-switcher-label-text',
                ];
                break;
            case 'collapsiblesection_focused_buttons':
                $selectors = [
                    '.collapsible-section .button:focus',
                    '.collapsible-section .btn:focus',
                    '.collapsible-section-switcher-label:focus .collapsible-section-switcher-label-text',
                ];
                break;
            case 'collapsiblesection_active_buttons':
                $selectors = [
                    '.collapsible-section .button:active',
                    '.collapsible-section .btn:active',
                    '.collapsible-section-switcher-label:active .collapsible-section-switcher-label-text',
                ];
                break;
            case 'mod_subheader':
                $selectors = [
                    'body.format-opentechnology.path-mod #page-wrapper div[role="main"] h3',
                    'body.format-grid.path-mod #page-wrapper div[role="main"] h3',
                    'body.format-topics.path-mod #page-wrapper div[role="main"] h3',
                    'body.format-weeks.path-mod #page-wrapper div[role="main"] h3',
                    'body.format-singleactivity.path-mod #page-wrapper div[role="main"] h3'
                ];
                break;
            default:
                break;
        }
        return str_replace( "'", '"', implode(", ", $selectors) );
    }

    public function get_list_supported_templates()
    {
        return $this->definedtemplates;
    }

    /**
     * Формирование описания для поля Дополнительного CSS
     *
     * @param string $fieldname - Имя поля
     *
     * @return string - HTML-код описания
     */
    public function customcss_description($fieldname)
    {
        $html = '';

        $currenttemplate = $this->template;

        // Кнопки макроподстановок
        $templatebuttons = '';
        foreach ( $this->definedtemplates as $template )
        {
            $this->change_template($template);
            $templatebuttons .= html_writer::div(
                get_string('selector_'.$template,'theme_opentechnology'),
                'btn btn-primary',
                [
                    'onclick' => 'var themeOtCustomCssField = document.getElementById(\'id_s_theme_opentechnology_'.$fieldname.'\');
                        if ( themeOtCustomCssField )
                        {
                            theme_ot_customcss_paste(themeOtCustomCssField, \'[[setting:selector_'.$template.']] {\r\n\r\n}\r\n\');
                        }'
                ]
                );
        }
        $html .= html_writer::div($templatebuttons).
        $html .= html_writer::script('
            var theme_ot_customcss_paste = function(myField, myValue){
                if (document.selection) {
                    myField.focus();
                    sel = document.selection.createRange();
                    sel.text = myValue;
                }
                else if (myField.selectionStart || myField.selectionStart == \'0\') {
                    var startPos = myField.selectionStart;
                    var endPos = myField.selectionEnd;
                    myField.value = myField.value.substring(0, startPos)
                    + myValue
                    + myField.value.substring(endPos, myField.value.length);
                    myField.selectionStart = startPos + myValue.length;
                    myField.selectionEnd = startPos + myValue.length;
                } else {
                    myField.value += myValue;
                }
            }; '
            );

        $this->change_template($currenttemplate);
        return str_replace("\r\n",'',$html);
    }
}