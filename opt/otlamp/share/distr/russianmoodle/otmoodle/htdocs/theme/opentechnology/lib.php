<?php
use theme_opentechnology\profilemanager;
use theme_opentechnology\cssprocessor;

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Тема СЭО 3KL. Библиотека функций темы.
 *
 * @package    theme
 * @subpackage opentechnology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Инициализация страницы
 *
 * @param moodle_page $page
 */
function theme_opentechnology_page_init(moodle_page $page)
{
    global $CFG;

    // Подключение jQuery
    $page->requires->jquery();

    // Переопределение типа страницы
    $customlayout = optional_param('page_layout', NULL, PARAM_ALPHA);
    if ( ! empty($customlayout) )
    {
        $page->set_pagelayout($customlayout);
    }

    // Инициализация менеджера профилей
    $manager = theme_opentechnology\profilemanager::instance();

    // Получение профиля текущей страницы
    $profile = $manager->get_current_profile();

    // Подключение CSS профиля
//     $profilecssurl = new moodle_url('/theme/opentechnology/stylesprofile.php/profile/'.$profile->get_code());
//     $page->requires->css($profilecssurl);

    // подключение amd-модуля для загрузки основных ядровых скриптов инициализации
    $page->requires->js_call_amd('theme_opentechnology/loader');

    // Получение настроки темы ориентации текста в шапке отчета по оценкам
    $verticaldisplay = (int)$manager->get_theme_setting('gradereport_table', $profile);
    if( $verticaldisplay == 1 || ($verticaldisplay == 2 && (int)get_user_preferences('verticaldisplay', 0)) )
    {// Если в теме выставлено вертикальное отображение или включено использование пользовательской настройки и у пользователя включено вертикальное отображение
        $page->requires->css('/theme/opentechnology/style/preference_verticaldisplay.css');
        $page->requires->js_call_amd('theme_opentechnology/gradereport_verticaldisplay', 'init');
    }

    // Модальное окно авторизации
    $setting = $manager->get_theme_setting('main_modal_login', $profile);
    if ( ! empty($setting) )
    {
        $currentpageurl = new moodle_url(qualified_me());
        $page->requires->js_call_amd('theme_opentechnology/login', 'init', [
            'contextid' => \context_system::instance()->id,
            'buttons' => '#page-footer .logininfo a.btn.ajaxpopup-footer-login, .login a, .ot-login-button',
            'pageurl' => $currentpageurl->out(false),
            'isloginorsignup' => is_login_or_signup_url($currentpageurl)
        ]);
    }

    // Защита от копирования

    // Получение системного контекста
    $systemcontext = \context_system::instance();
    $plugin = \core_plugin_manager::instance()->get_plugin_info('theme_opentechnology');
    // До версии 2019092300 прав не существовало
    $copy_draganddrop = $copy_contextmenu = $copy_copy = $copy_nojsaccess = false;
    $capabilitiesexists = !empty($plugin->versiondb) && $plugin->versiondb >= 2019092300;

    $setting = $manager->get_theme_setting('security_copy_draganddrop', $profile);
    if (!empty($setting))
    {
        if ($capabilitiesexists) {
            // Проверяем права, если включена настройка и права существуют
            // Право на игнорирование запрета на перетаскивание
            $copy_draganddrop = has_capability('theme/opentechnology:security_copy_draganddrop', $systemcontext);
        }
        if (!$copy_draganddrop) {
            // Если права нет - подключаем js-бработчик
            $page->requires->js(new moodle_url('/theme/opentechnology/javascript/secure_draganddrop.js'));
        }
    }
    $setting = $manager->get_theme_setting('security_copy_contextmenu', $profile);
    if (!empty($setting))
    {
        if ($capabilitiesexists) {
            // Проверяем права, если включена настройка и права существуют
            // Право на игнорирование запрета на вызов контекстного меню
            $copy_contextmenu = has_capability('theme/opentechnology:security_copy_contextmenu', $systemcontext);;
        }
        if (!$copy_contextmenu) {
            // Если права нет - подключаем js-бработчик
            $page->requires->js(new moodle_url('/theme/opentechnology/javascript/secure_contextmenu.js'));
        }
    }
    $setting = $manager->get_theme_setting('security_copy_copy', $profile);
    if (!empty($setting))
    {
        if ($capabilitiesexists) {
            // Проверяем права, если включена настройка и права существуют
            // Право на игнорирование запрета на копирование текста
            $copy_copy = has_capability('theme/opentechnology:security_copy_copy', $systemcontext);
        }
        if (!$copy_copy) {
            // Если права нет - подключаем js-бработчик
            $page->requires->js(new moodle_url('/theme/opentechnology/javascript/secure_copy.js'));
        }
    }
    $setting = $manager->get_theme_setting('security_copy_nojsaccess', $profile);
    if (!empty($setting))
    {
        if ($capabilitiesexists) {
            // Проверяем права, если включена настройка и права существуют
            // Право на игнорирование запрета на доступ без включенного javascript
            $copy_nojsaccess = has_capability('theme/opentechnology:security_copy_nojsaccess', $systemcontext);
        }
        if (!$copy_nojsaccess) {
            // Если права нет - подключаем js-бработчик
            $page->requires->js(new moodle_url('/theme/opentechnology/javascript/secure_nojs.js'));
        }
    }

    // Адаптивные таблицы
    $setting = $manager->get_theme_setting('responsive_tables', $profile);
    if ( ! empty($setting) )
    {
        $page->requires->js_call_amd('theme_opentechnology/tablecontroller', 'init');
    }

    // Отправка орфографической ошибки
    $setting = $manager->get_theme_setting('main_spelling_mistake', $profile);
    if ( ! empty($setting) )
    {
        $page->requires->js_call_amd('theme_opentechnology/spellingmistake', 'init');
    }

    // Для корректного вычисления z-index на лету, все элементы с z-index
    // должны иметь класс moodle-has-zindex
    // вычисление использует js, поэтому нет причин отказываться от него
    // благодаря этому решению не требуется переопределять template ради добавления класса
    $page->requires->js_call_amd('theme_opentechnology/z-index-fixer', 'fix', [
        implode(',', [
            '#h_rightblock_wrapper .popover-region',
            'div#dock',
            '#dock_bg .langmenu_wrapper > .langmenu',
            '#gridshadebox_content.absolute',
            'div#gridshadebox_overlay'
        ])
    ]);

    // Прилипающая шапка
    $stickyadv = $manager->get_theme_setting('header_sticky_adv', $profile);
    if (!empty($stickyadv))
    {
        $stickylevel = $manager->get_theme_setting('header_sticky', $profile);
        $stickyselector = null;
        switch($stickylevel)
        {
            case 1: $stickyselector = '.collapsible-section-htop-wrapper'; break;
            case 2: $stickyselector = '#page-header .headertext_wrapper'; break;
            case 3: $stickyselector = '#page-header .wrapper'; break;
            case 4: $stickyselector = '#page-header .dock_bg_wrapper'; break;
            case 5: $stickyselector = '#blocks-content-heading-wrapper'; break;
            case 6: $stickyselector = '.page-navbar-wrapper'; break;
        }
        if (!is_null($stickyselector))
        {
            $page->requires->js_call_amd('theme_opentechnology/sticky', 'init', [$stickyselector]);
        }
    }

    // Добавление регионов для блоков, если включена опция отображения сворачиваемых секций
    if (local_opentechnology_is_layout_supports_regions($page))
    {
        $collapsiblesections = theme_opentechnology_get_known_collapsiblesections();
        foreach($collapsiblesections as $collapsiblesection)
        {
            $collapsiblesectiondata = theme_opentechnology_get_collapsiblesection_data($collapsiblesection, $page);

            foreach((array)$collapsiblesectiondata->regions as $region)
            {
                $page->blocks->add_region($region);
            }
        }

        // добавление региона для дока
        $page->blocks->add_region('dock');
    }

    // добавление обработчиков драг и дропа для блоков (чтобы дружили с нашим доком)
    $page->requires->yui_module('moodle-theme_opentechnology-blocks', 'M.theme_ot.blocks.init', [], null, true);

    if( ! empty($CFG->whitelistdomains) && is_array($CFG->whitelistdomains) )
    {
        ob_start();
        register_shutdown_function('theme_opentechnology_replace_domains');
    }

}

function is_login_or_signup_url(moodle_url $url) {
    $loginorsignupurls = [
        // вход
        '/login/index.php',
        // восстановление пароля
        '/login/forgot_password.php',
        // создание учетной записи
        '/login/signup.php',
        // редактирование профиля (на случай заполнения недостающих обязательных полей)
        '/user/edit.php'
    ];
    $localurl = strip_querystring($url->out_as_local_url(false, []));
    return in_array($localurl, $loginorsignupurls);
}

/**
 * Поиск целевого типа страницы авторизации
 *
 * @param moodle_page $page Pass in $PAGE.
 *
 * @return string - Тип страницы авторизации
 */
function theme_opentechnology_loginpage_layout(moodle_page $page)
{
    global $CFG;

    // Получение менеджера профилей
    $manager = theme_opentechnology\profilemanager::instance();

    // Получение кода профиля текущей страницы
    $profile = $manager->get_current_profile();

    // Получение типа страницы авторизации
    $layout = $manager->get_theme_setting('loginpage_main_type', $profile);
    if ( $layout )
    {
        $layoutpath = $CFG->dirroot.'/theme/opentechnology/layout/login_'.$layout.'.php';
        if ( file_exists($layoutpath) )
        {
            return $layout;
        }
    }

    // Стандартный тип страницы авторизации
    return 'standard';
}

/**
 * Получение пути до кастомного лэйаута для профиля темы
 *
 * @param moodle_page $page Pass in $PAGE.
 *
 * @return string|bool - путь до кастомного файла лэйаута профиля или false в случае, если его нет
 */
function theme_opentechnology_get_profile_layout(moodle_page $page, $filename=null)
{
    global $CFG;

    // Получение темы
    $theme = theme_config::load('opentechnology');

    // Получение менеджера профилей
    $manager = \theme_opentechnology\profilemanager::instance();

    // Получение кода профиля текущей страницы
    $profile = $manager->get_current_profile();

    if( empty($filename) )
    {
        $filename = $page->theme->layouts[$page->pagelayout]['file'];
    }

    $profilelayoutfile =    $CFG->dirroot . "/theme/" . $theme->name . '/profiles/overrides/' .
                            $profile->get_code(). "/layouts/" . $filename;
    if( file_exists($profilelayoutfile) )
    {
        return $profilelayoutfile;
    } else
    {
        return false;
    }
}


/**
 * Получение пути до лейаута с шапкой страницы
 *
 * @param moodle_page $page Pass in $PAGE.
 *
 * @return string|bool - путь до файла лэйаута или false в случае, если его нет
 */
function theme_opentechnology_get_page_standard_header(moodle_page $page)
{
    global $CFG;

    // Получение темы
    $theme = theme_config::load('opentechnology');

    $layoutpath = $CFG->dirroot . "/theme/" . $theme->name . "/layout/page_standard_header.php";
    $profilelayoutpath = theme_opentechnology_get_profile_layout($page, 'page_standard_header.php');

    if( file_exists($profilelayoutpath) )
    {
        return $profilelayoutpath;
    }
    elseif( file_exists($layoutpath) )
    {
        return $layoutpath;
    }
    else
    {
        return false;
    }
}

/**
 * Получение пути до лейаута с подвалом страницы
 *
 * @param moodle_page $page Pass in $PAGE.
 *
 * @return string|bool - путь до файла лэйаута или false в случае, если его нет
 */
function theme_opentechnology_get_page_standard_footer(moodle_page $page)
{
    global $CFG;

    // Получение темы
    $theme = theme_config::load('opentechnology');

    $layoutpath = $CFG->dirroot . "/theme/" . $theme->name . "/layout/page_standard_footer.php";
    $profilelayoutpath = theme_opentechnology_get_profile_layout($page, 'page_standard_footer.php');
    if( file_exists($profilelayoutpath) )
    {
        return $profilelayoutpath;
    }
    elseif( file_exists($layoutpath) )
    {
        return $layoutpath;
    }
    else
    {
        return false;
    }
}


/**
 * Лейауты, не поддерживающие навигацию
 * Страницы, зная, что они будут отображены с помощью лейаутов, не использующих навигацию,
 * могут не делать предварительных настроек, без которых конструирование навигации завершится ошибкой.
 * Этого никто не обнаружит на обычных темах, поэтому и проходит незамеченным.
 * Поэтому мы не должны конструировать навигацию на таких лейаутах.
 * Найти такие свойства лейаутов в мудл не удалось.
 * Формируем список самостоятельно. Можно расширять при необходимости.
 *
 * @param moodle_page $page
 */
function local_opentechnology_layouts_not_supports_navigation(moodle_page $page) {
    return [
        'maintenance',
        'embedded',
        'redirect'
    ];
}

/**
 * Поддерживает ли лейаут навигацию?
 * Есть проблема формирования навигации для некоторых лейаутах,
 * описана в local_opentechnology_layouts_not_supports_navigation.
 *
 * @param moodle_page $page
 */
function local_opentechnology_is_layout_supports_navigation(moodle_page $page) {
    $notsupports = local_opentechnology_layouts_not_supports_navigation($page);
    return !in_array($page->pagelayout, $notsupports);
}

/**
 * Поддерживает ли лейаут регионы для блоков?
 * Есть проблема формирования навигации для некоторых лейаутах,
 * описана в local_opentechnology_layouts_not_supports_navigation.
 * Как минимум при использовании тех же лейаутов не должны быть добавлены регионы,
 * так как в них добавится блок навигация и будет пытаться тоже сформировать навигацию.
 *
 * @param moodle_page $page
 */
function local_opentechnology_is_layout_supports_regions(moodle_page $page) {
    $notsupports = local_opentechnology_layouts_not_supports_navigation($page);
    return !in_array($page->pagelayout, $notsupports);
}

/**
 * Подготовка динамических данных страницы в зависимости от профиля
 *
 * @param renderer_base $output Pass in $OUTPUT.
 * @param moodle_page $page Pass in $PAGE.
 *
 * @return stdClass - Объект с данными для генерации страницы
 */
function theme_opentechnology_get_html_for_settings(renderer_base $output, moodle_page $page)
{
    global $CFG, $USER;

    $return = new stdClass();

    // Получение темы
    $theme = theme_config::load('opentechnology');

    // Получение менеджера профилей
    $manager = \theme_opentechnology\profilemanager::instance();

    // Получение кода профиля текущей страницы
    $profile = $manager->get_current_profile();
    $profilecode = $profile->get_code();

    //favicon
    $favicon_url = $manager->get_theme_setting_file_url('main_favicon', $profile);
    if ($favicon_url) {
        $return->favicon = $page->url->get_scheme() . ':' . $favicon_url;
    } else {
        //Если не задан favicon, то отобразить favicon ОТ по умолчанию
        $default_favicon_moodle_url = new moodle_url('/theme/opentechnology/pix/favicon.ico');
        $default_favicon_url = $default_favicon_moodle_url->out(false);
        $return->favicon = $default_favicon_url;
    }

    // Добавление кода профиля в body
    $return->additionalbodyclass = 'theme-ot profile_'.$profilecode;

    $return->bodyinnerclasses = '';
    $setting = $manager->get_theme_setting('main_custombodyinnerclasses', $profile);
    if ( ! empty($setting) )
    {
        $return->bodyinnerclasses = $setting;
    }

    // Добавление классов навигации
    $return->navbarclass = '';

    // Фактор ширины темы
    $return->widthfactorclass = '-fluid';
    $setting = $manager->get_theme_setting('main_fixed_width', $profile);
    if ( ! empty($setting) )
    {
        $return->widthfactorclass = '';
    }

    // Тип страницы авторизации
    $return->loginpagetype = '';
    $setting = $manager->get_theme_setting('loginpage_main_type', $profile);
    if ( ! empty($setting) )
    {
        $return->loginpagetype = $setting;
    }

    // Текст в верху шапки
    $return->header_top_text = '';
    $setting = format_text($manager->get_theme_setting('header_top_text', $profile), FORMAT_HTML);
    if ( ! empty($setting) )
    {
        $return->header_top_text = $setting;
    }

    // Блок логотипа в шапке
    $return->header_logoimage = '';
    $setting = $manager->get_theme_setting('header_logoimage', $profile);
    $syslogo = $output->get_logo_url(420);
    $syscompactlogo = $output->get_compact_logo_url(240);
    if ( ! empty($setting) || $syslogo)
    {
        if (!empty($setting))
        {
            // Получение кода настройки с учетом профиля
            $settingcode = $manager->get_theme_setting_name('header_logoimage', $profile);
            // Получение имени файловой зоны с учетом профиля
            $settingfilearea = $manager->get_theme_setting_filearea('header_logoimage', $profile);
            // Путь к изображению логотипа
            $src = $srccompact = $theme->setting_file_url($settingcode, $settingfilearea);
        } else
        {
            // Путь к изображению логотипа
            $src = $syslogo->out(false);
            $srccompact = $syscompactlogo ? $syscompactlogo->out(false) : $src;
        }

        $return->header_logoimage = theme_opentechnology_prepare_logo_code(
            $src,
            $srccompact,
            theme_opentechnology_get_logo_text($profile),
            theme_opentechnology_get_logo_link($profile)
        );
    }

    // Блок логотипа в подвале
    $return->footer_logoimage = '';
    $setting = $manager->get_theme_setting('footer_logoimage', $profile);
    if ( ! empty($setting) )
    {
        // Получение кода настройки с учетом профиля
        $settingcode = $manager->get_theme_setting_name('footer_logoimage', $profile);
        // Получение имени файловой зоны с учетом профиля
        $settingfilearea = $manager->get_theme_setting_filearea('footer_logoimage', $profile);

        $src = $theme->setting_file_url($settingcode, $settingfilearea);
        $image = html_writer::img($src, get_string('home'));
        $return->footer_logoimage = html_writer::link(
            '/',
            $image,
            [
                'title' => get_string('home'),
                'class' => 'footer_logoimage'
            ]
        );
    }

    $return->footer_logoimage_text = '';
    $setting = format_text($manager->get_theme_setting('footer_logoimage_text', $profile), FORMAT_HTML);
    if ( ! empty($setting) )
    {
        $return->footer_logoimage_text = html_writer::div($setting, 'footer_logoimage_text');
    }

    $return->footnote = '';
    $setting = format_text($manager->get_theme_setting('footnote', $profile));
    if ( ! empty($setting) )
    {
        $return->footnote = html_writer::div($setting, 'footnote text-center');
    }

    // Скрытие каретки в user_menu
    $showcaret = $manager->get_theme_setting('header_usermenu_hide_caret', $profile);
    if( empty($showcaret) )
    {
        $return->caret_class = ' nocaret';
    } else
    {
        $return->caret_class = '';
    }

    // Дополнительные ссылки в шапке страницы
    $return->header_links = '';

    $setting = $manager->get_theme_setting('header_link_crw', $profile);
    if ( ! empty($setting) )
    {// Добавить кнопку перехода в витрину
        $installlist = core_plugin_manager::instance()->get_installed_plugins('local');
        $isinstall = array_key_exists('crw', $installlist);
        if ( $isinstall )
        {
            $return->header_links .= html_writer::link(
                new moodle_url('/local/crw'),
                '',
                [
                    'class' => 'btn btn-primary button_crw header_link',
                    'title' => get_string('title','local_crw'),
                    'data-toggle' => 'tooltip',
                    'data-placement' => 'bottom'
                ]
            )."\r\n";
        }
    }

    $setting = $manager->get_theme_setting('header_link_portfolio', $profile);
    if ( ! empty($setting) && isloggedin() )
    {// Добавить кнопку перехода в портфолио
        $installlist = core_plugin_manager::instance()->get_installed_plugins('block');
        $isinstall = ( array_key_exists('dof', $installlist) );
        if ( $isinstall )
        {
            $return->header_links .= html_writer::link(
                new moodle_url('/blocks/dof/im/achievements/my.php'),
                '',
                [
                    'class' => 'btn btn-primary button_portfolio header_link',
                    'title' => get_string('my_portfolio','theme_opentechnology'),
                    'data-toggle' => 'tooltip',
                    'data-placement' => 'bottom'
                ]
            )."\r\n";
        }
    }

    $setting = $manager->get_theme_setting('header_link_search', $profile);
    if ( ! empty($setting) )
    {
        $return->showsearch = true;
    } else
    {
        $return->showsearch = false;
    }

    // Расположение персонального меню
    $return->custommenulocation = 0;
    $setting = $manager->get_theme_setting('header_custommenu_location', $profile);
    if ( ! empty($setting) )
    {// Требуется отобразить хлебные крошки
        $return->custommenulocation = (int)$setting;
    }
    // обход логики для шаблонизатора
    switch($return->custommenulocation)
    {
        case 6: $return->cml_top_left = true; break;
        case 7: $return->cml_top_right = true; break;
        case 1: $return->cml_above_logo = true; break;
        case 5: $return->cml_under_logo = true; break;
        case 4: $return->cml_above_usermenu = true; break;
        case 2: $return->cml_under_usermenu = true; break;
        case 0: $return->cml_bottom_left = true; break;
        case 8: $return->cml_bottom_right = true; break;
        case 3: $return->cml_profile_custom_position = true; break;
    }


    $return->custommenu = $output->custom_menu();


    // Хлебные крошки
    $return->navbar = '';
    // настройка отображения хлебных крошек на главной странице
    $settingname = 'homepage_display_breadcrumbs';
    $displayonfrontpage = !empty($manager->get_theme_setting($settingname, $profile));
    // текущий лейаут - главная страница (может отображать хлебные крошки если настроено)
    $isfrontpage = ($page->pagelayout == 'frontpage');

    if ((!$isfrontpage || $displayonfrontpage) && local_opentechnology_is_layout_supports_navigation($page))
    {// Требуется отобразить хлебные крошки
        $return->navbar = $output->navbar();
    }

    // Сообщение от отключенном js
    $setting = $manager->get_theme_setting('security_nojs_text', $profile);
    if ( ! empty($setting) )
    {
        $return->noscript = format_string($setting);
    } else
    {
        $return->noscript = get_string('settings_security_nojs_text_default', 'theme_opentechnology');
    }

    // Сворачиваемые секции с позициями блоков
    $collapsiblesections = theme_opentechnology_get_known_collapsiblesections();
    foreach($collapsiblesections as $collapsiblesection)
    {
        $collapsiblesectiondata = theme_opentechnology_get_collapsiblesection_data($collapsiblesection, $page, $output);
        $return->{'collapsiblesection_'.$collapsiblesection['code']} = $collapsiblesectiondata->html;
    }

    // Ширина логотипа в футере
    $return->footer_logoimage_width = 3;
    $setting = $manager->get_theme_setting('footer_logoimage_width', $profile);
    if ( isset($setting) )
    {
        $return->footer_logoimage_width = $setting;
    }

    // Заголовок в ДОК-панели
    $return->dockheading = '';
    $setting = $manager->get_theme_setting('header_dockpanel_header', $profile);
    if ( ! empty($setting) )
    {
        $return->dockheading = html_writer::tag('h1', $page->heading, ['class' => 'headermain']);
    }

    $return->dock_lang_menu = ($manager->get_theme_setting('main_langmenu', $profile) == 0);

    // Подпись Русский Moodle 3KL
    if( ! isset($CFG->showcopyrightlink) || ! empty($CFG->showcopyrightlink) )
    {// Если настройка не задана или не пуста - покажем ссылку
        $rm3klurl = new moodle_url("");
        $return->footer_rm3kl_text = html_writer::link($rm3klurl, "ЦОДИВ");
    } else
    {// Если в настройке указано, что нужно скрыть ссылку - скроем
        $return->footer_rm3kl_text = '';
    }

    // заголвок дока пустой, проверим, включена ли настройка отображения заголовка в контенте главной страницы
    $setting = $manager->get_theme_setting('header_content_header', $profile);

    // заголовок секции
    if ( empty($return->dockheading) && ! empty($setting) )
    {
        $return->sectionheader = html_writer::div($output->page_heading('h2'), 'headermain');
    } else
    {
        $return->sectionheader = html_writer::div($output->page_heading('h2'), 'headermain', ['style' => 'display:none;']);
    }


    $pagebacks = [
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
    foreach($pagebacks as $pageback)
    {
        $setting = $manager->get_theme_setting('pb_'.$pageback.'_unlimit_width', $profile);
        $htmlsetting = 'pageback_'.$pageback.'_unlimit_width';
        $return->$htmlsetting = empty($setting) ? '' : ' no-limit';
    }




    $headerclasses = [];
    if (!empty($return->navbarclass))
    {
        $headerclasses[] = $return->navbarclass;
    }
    if ($manager->get_theme_setting('main_dock_hide', $profile) == 1)
    {
        $headerclasses[] = 'hide-empty-dock';
    }
    // класс наличия в док-панели переключателя языка
    if ($manager->get_theme_setting('main_langmenu', $profile) == 0)
    {
        $langmenu = $page->get_renderer('core')->lang_menu();
        if (!empty($langmenu))
        {
            $headerclasses[] = 'dock-has-lang';
        }
    }
    // класс наличия в док-панели заголовка страницы
    if (!empty($return->dockheading))
    {
        $headerclasses[] = 'dock-has-heading';
    }
    // класс наличия в док-панели свернутых блоков во время инициализации страницы
    if ($page->blocks->region_uses_dock($page->blocks->get_regions(), $output))
    {
        $headerclasses[] = 'dock-has-items';
    }
    $return->headerclasses = implode(' ', $headerclasses);

    return $return;
}


/**
 * Получение текста логотипа в шапке
 *
 * @return string
 */
function theme_opentechnology_get_h_logo_title()
{
    $html = '';

    // Получение менеджера профилей
    $manager = theme_opentechnology\profilemanager::instance();
    // Получение кода профиля текущей страницы
    $profile = $manager->get_current_profile();
    // Получение текста
    $text = format_text($manager->get_theme_setting('header_text', $profile), FORMAT_HTML);

    if ( ! empty($text) )
    {
        $html .= html_writer::start_div('h_logo_title');
        $html .= html_writer::div($text, 'h_logo_title_text');
        $html .= html_writer::end_div();
    }

    return $html;
}


/**
 * Получить список используемых темой сворачиваемых секций с настраиваемыми регионами для блоков
 *
 * @return array - массив кодов сворачиваемых секций
 */
function theme_opentechnology_get_known_collapsiblesections()
{
    // Bспользуются короткие названия, так как в БД предусмотрено совсем небольшое поле для хранения
    return [
        'htop' => [ // Над шапкой
            'code' => 'htop',
            'name' => get_string('collapsiblesection_htop','theme_opentechnology'),
            'widthclass' => ''
        ],
        'ctop' => [ // Над контентом
            'code' => 'ctop',
            'name' => get_string('collapsiblesection_ctop','theme_opentechnology'),
            'widthclass' => 'auto'
        ],
        'cmid' => [ // Под заголовком контента
            'code' => 'cmid',
            'name' => get_string('collapsiblesection_cmid','theme_opentechnology'),
            'widthclass' => 'auto'
        ],
        'cbot' => [ // Под контентом
            'code' => 'cbot',
            'name' => get_string('collapsiblesection_cbot','theme_opentechnology'),
            'widthclass' => 'auto'
        ]
    ];
}

/**
 * Получить данные по сворачиваемой секции на странице
 *
 * @param moodle_page $page - Текущая страница
 * @param renderer_base $render - Рендер страницы
 *
 * @return stdClass - Объект с данными по сворачиваемой секции
 */
function theme_opentechnology_get_collapsiblesection_data($collapsiblesection, moodle_page $page, renderer_base $render = null)
{
    // Подготовка данных
    $result = new stdClass();
    $result->html = '';
    $result->regions = [];

    // Получение темы
    $theme = theme_config::load('opentechnology');

    // Получение менеджера профилей
    $manager = theme_opentechnology\profilemanager::instance();

    // Получение кода профиля текущей страницы
    $profile = $manager->get_current_profile();

    // Получение настройки состояния сворачиваемой секции
    $collapsiblesectionstate = (int)$manager->get_theme_setting(
        'layout_'.$page->pagelayout.'_collapsiblesection_'.$collapsiblesection['code'].'_state',
        $profile
    );

    if( (int)$collapsiblesectionstate == 4 )
    {
        // Использование сворачиваемой секции отключено
        // Блоки должны отобразиться в регионе по умолчанию
        return $result;
    }

    // настройка отмены максимальной ширины
    $pagebacksetting = $manager->get_theme_setting('pb_cs_'.$collapsiblesection['code'].'_unlimit_width', $profile);

    // Конфигурация регионов для шторки
    $setting = $manager->get_theme_setting(
        'layout_' . $page->pagelayout . '_collapsiblesection_' . $collapsiblesection['code'],
        $profile
    );

    // Фактор ширины темы
    $widthfactorclass = '-fluid';
    $widthfactorclasssetting = $manager->get_theme_setting('main_fixed_width', $profile);
    if ( ! empty($widthfactorclasssetting) )
    {
        $widthfactorclass = '';
    }
    if( $collapsiblesection['widthclass'] == 'auto' )
    {
        $collapsiblesection['widthclass'] = 'container'.$widthfactorclass;

        if (!empty($pagebacksetting))
        {
            $collapsiblesection['widthclass'] .= ' no-limit';
        }
    }


    if ( ! empty($setting) && $blindregionsdata = json_decode($setting) )
    {
        // Наполненность регионов блоками
        $filledregions = 0;
        $blindrows = [];
        foreach($blindregionsdata as $rownum=>$row)
        {
            // Регионы в строке
            $blindregions = [];
            foreach($row as $regionnum=>$blindregionsize)
            {
                $region = 'cs-'.$collapsiblesection['code'].'-'.($rownum+1).'-'.($regionnum+1);

                if( ! is_null($render) )
                {
                    $blindregions[] = $render->blocks(
                        $region,
                        'collapsible-section-region collapsible-section-regionnum-'.($regionnum+1).' col-'.$blindregionsize
                    );
                    if( $page->blocks->region_has_content($region, $render) )
                    {
                        // В регионе есть блоки
                        $filledregions++;
                    }
                }
                $result->regions[] = $region;
            }
            // Добавление строки с регионами
            $blindrows[] = html_writer::div(
                implode('',$blindregions),
                'collapsible-section-row collapsible-section-rownum-'.($rownum+1).' row'
            );
        }

        // Шторка должна отображаться только если есть хотя бы один блок
        if( $filledregions > 0 && ! empty($blindrows) )
        {
            // Определение состояния сворачиваемой секции
            if ( $collapsiblesectionstate < 2 ) {
                $displaycontent = get_user_preferences('theme_opentechnology_collapsiblesection_'.$collapsiblesection['code'].'_'.$page->pagelayout.'_state', $collapsiblesectionstate);
            } else {
                $displaycontent = $collapsiblesectionstate % 2;
            }

            // Переключатель сворачивания шторки
            $switcher = html_writer::checkbox(
                'collapsible-section-switcher',
                'showhide',
                $displaycontent,
                '',
                [
                    'id' => 'collapsible-section-'.$collapsiblesection['code'].'-switcher',
                    'class' => 'collapsible-section-switcher'
                ]
            );


            if( (int)$collapsiblesectionstate == 5 )
            {
                // Включена настройка принудительного отображения сворачиваемой секции (нельзя сворачивать-разворачивать)
                $slidedownlabel = '';
                $slideuplabel = '';
            } else
            {
                // Кнопка открытия шторки
                $slidedowntext = html_writer::div(
                    get_string('collapsiblesection-switcher-slidedown', 'theme_opentechnology'),
                    'collapsible-section-switcher-label-text'
                );
                $slidedownlabel = html_writer::label(
                    html_writer::div($slidedowntext, 'container'.$widthfactorclass),
                    'collapsible-section-'.$collapsiblesection['code'].'-switcher',
                    true,
                    [
                        'class' => 'collapsible-section-switcher-label collapsible-section-switcher-slidedown-label'
                    ]
                );

                // Кнопка закрытия шторки
                $slideuptext = html_writer::div(
                    get_string('collapsiblesection-switcher-slideup', 'theme_opentechnology'),
                    'collapsible-section-switcher-label-text'
                );
                $slideuplabel = html_writer::label(
                    html_writer::div($slideuptext, 'container'.$widthfactorclass),
                    'collapsible-section-'.$collapsiblesection['code'].'-switcher',
                    true,
                    [
                        'class' => 'collapsible-section-switcher-label collapsible-section-switcher-slideup-label'
                    ]
                );
            }

            // Сам контент для cdjhfчиваемой секции
            $blindcontent = html_writer::div(
                implode('',$blindrows),
                'collapsible-section-content container'.$widthfactorclass. (empty($pagebacksetting) ? '' : ' no-limit')
            );

            $html = html_writer::div(
                $switcher . $slideuplabel . $blindcontent . $slidedownlabel . $slideuplabel,
                'collapsible-section collapsible-section-'.$collapsiblesection['code'] . ' ' .
                $collapsiblesection['widthclass'] . ' ' .
                ( $displaycontent ? 'expanded' : 'collapsed' ),
                [
                    'data-collapsible-section' => $collapsiblesection['code']
                ]
            );
            $result->html = html_writer::div($html, 'collapsible-section-wrapper collapsible-section-'.$collapsiblesection['code'].'-wrapper');

        }
    }
    return $result;
}

/**
 * Подготовка динамических данных страницы авторизации в зависимости от профиля
 *
 * @param renderer_base $output Pass in $OUTPUT.
 * @param moodle_page $page Pass in $PAGE.
 *
 * @return stdClass - Объект с данными для генерации страницы
 */
function theme_opentechnology_get_html_for_settings_loginpage_standard(renderer_base $output, moodle_page $page)
{
    $return = theme_opentechnology_get_html_for_settings($output, $page);

    // Получение темы
    $theme = theme_config::load('opentechnology');

    // Получение менеджера профилей
    $profilemanager = theme_opentechnology\profilemanager::instance();

    // Получение кода профиля текущей страницы
    $profile = $profilemanager->get_current_profile();

    $return->additionalbodyclass .= ' loginpage_standard';
    $setting = $profilemanager->get_theme_setting('main_custombodyclasses', $profile);
    if ( ! empty($setting) )
    {
        $return->additionalbodyclass .= " ".$setting;
    }

    return $return;
}

/**
 * Подготовка динамических данных страницы авторизации в зависимости от профиля
 *
 * @param renderer_base $output Pass in $OUTPUT.
 * @param moodle_page $page Pass in $PAGE.
 * @param string $loginpagelayout - Шаблон
 *
 * @return stdClass - Объект с данными для генерации страницы
 */
function theme_opentechnology_get_html_for_settings_loginpage_slider(renderer_base $output, moodle_page $page)
{
    global $CFG, $USER;

    $return = theme_opentechnology_get_html_for_settings($output, $page);

    // Получение темы
    $theme = theme_config::load('opentechnology');

    // Получение менеджера профилей
    $profilemanager = theme_opentechnology\profilemanager::instance();

    // Получение кода профиля текущей страницы
    $profile = $profilemanager->get_current_profile();

    $return->additionalbodyclass .= ' loginpage_slider';
    $setting = $profilemanager->get_theme_setting('main_custombodyclasses', $profile);
    if ( ! empty($setting) )
    {
        $return->additionalbodyclass .= " ".$setting;
    }

    // Слайды
    $return->loginpage_slider_images = [];
    // Получение набора слайдов
    $settingfullname = $profile->get_setting_name('loginpage_slider_images');
    $filearea = $profilemanager->get_theme_setting_filearea('loginpage_slider_images', $profile);
    $images = theme_opentechnology_get_filearea_files($filearea, $settingfullname, 0);
    foreach ( $images as $image )
    {
        $return->loginpage_slider_images[] = $image->url->out(false);
    }

    // Текст в шапке
    $return->header_text = '';
    $setting = $profilemanager->get_theme_setting('loginpage_header_text', $profile);
    if ( ! empty($setting) )
    {
        $return->header_text = $setting;
    }

    return $return;
}

/**
 * Подготовка динамических данных в зависимости от профиля для страницы авторизации типа "Боковая панель"
 *
 * @param renderer_base $output Pass in $OUTPUT
 * @param moodle_page $page Pass in $PAGE
 *
 * @return stdClass - Объект с данными для генерации страницы
 */
function theme_opentechnology_get_html_for_settings_loginpage_sidebar(renderer_base $output, moodle_page $page)
{
    $result = theme_opentechnology_get_html_for_settings($output, $page);

    // Получение темы
    $theme = theme_config::load('opentechnology');

    // Получение менеджера профилей
    $profilemanager = theme_opentechnology\profilemanager::instance();

    // Получение кода профиля текущей страницы
    $profile = $profilemanager->get_current_profile();

    $result->additionalbodyclass .= ' loginpage_sidebar';


    // Избражение для логотипа
    $result->loginpage_sidebar_logoimage = $result->header_logoimage;
    $setting = $profilemanager->get_theme_setting('loginpage_sidebar_logoimage', $profile);
    if (! empty($setting))
    {
        // Получение кода настройки с учетом профиля
        $settingcode = $profilemanager->get_theme_setting_name('loginpage_sidebar_logoimage', $profile);
        // Получение имени файловой зоны с учетом профиля
        $settingfilearea = $profilemanager->get_theme_setting_filearea('loginpage_sidebar_logoimage', $profile);
        // Путь к изображению логотипа
        $imageurl = $theme->setting_file_url($settingcode, $settingfilearea);

        $result->loginpage_sidebar_logoimage = theme_opentechnology_prepare_logo_code(
            $imageurl,
            $imageurl,
            theme_opentechnology_get_logo_text($profile),
            theme_opentechnology_get_logo_link($profile)
        );

    }


    // Изображение для фона
    $settingfullname = $profile->get_setting_name('loginpage_sidebar_images');
    $filearea = $profilemanager->get_theme_setting_filearea('loginpage_sidebar_images', $profile);
    $images = theme_opentechnology_get_filearea_files($filearea, $settingfullname, 0);
    if (count($images) > 0)
    {
        $mainimage = array_shift($images);
        $result->loginpage_sidebar_image = $mainimage->url->out(false);
    }



    // Текст
    $result->loginpage_sidebar_text = '';
    $setting = $profilemanager->get_theme_setting('loginpage_sidebar_text', $profile);
    if ( ! empty($setting) )
    {
        $result->loginpage_sidebar_text = $setting;
    }



    // Элементы шапки
    $result->loginpage_header_elements = [];
    $setting = $profilemanager->get_theme_setting('loginpage_sidebar_header_elements', $profile);
    if ( ! empty($setting) )
    {
        foreach(explode(',',$setting) as $headerelement)
        {
            $result->loginpage_header_elements[$headerelement] = true;
        }
    }


    if (!array_key_exists('custommenu', $result->loginpage_header_elements))
    {
        $result->custommenu = null;
    }


    // Ширину любого стандартного блока увеличиваем на полную
    $pagebacks = [
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
    foreach($pagebacks as $pageback)
    {
        $result->{'pageback_'.$pageback.'_unlimit_width'} = ' no-limit';
    }

    return $result;
}

function theme_opentechnology_get_logo_text($profile)
{
    // Получение менеджера профилей
    $profilemanager = theme_opentechnology\profilemanager::instance();

    // Получение текста логотипа
    return format_text($profilemanager->get_theme_setting('header_logo_text', $profile), FORMAT_HTML);
}

function theme_opentechnology_get_logo_link($profile)
{
    // Получение менеджера профилей
    $profilemanager = theme_opentechnology\profilemanager::instance();

    // Получение ссылки логотипа
    $logolink = $profilemanager->get_theme_setting('header_logo_link', $profile);

    if (!is_null($logolink))
    {
        $logolink = new moodle_url($logolink);
    }

    return $logolink;
}

function theme_opentechnology_prepare_logo_code($imageurl, $imagecompacturl, $logotext, moodle_url $logolink=null)
{
    if (!empty($imageurl))
    {
        $image = html_writer::img($imageurl, get_string('home'), ['class' => 'logo']);
    } else {
        $image = '';
    }

    if (!empty($imagecompacturl))
    {
        $imagecompact = html_writer::img($imagecompacturl, get_string('home'), ['class' => 'compact-logo']);
    } else {
        $imagecompact = '';
    }

    if ( ! empty($logotext) )
    {
        $logotext = html_writer::div($logotext, 'header_logotext');
    }

    if ( !is_null($logolink) )
    {// Ссылка логотипа определена
        $logolink = $logolink->out(false);
        $logoimage = html_writer::link(
            $logolink,
            $image . $imagecompact . $logotext,
            [ 'class' => 'header_logoimage' ]
        );
    } else
    {// Логотип без ссылки
        $logoimage = html_writer::span(
            $image . $imagecompact . $logotext,
            'header_logoimage'
        );
    }
    return html_writer::div(
        $logoimage . theme_opentechnology_get_h_logo_title(),
        'header_logoimage_wrappper'
    );
}

/**
 * Сформировать CSS перед сохранением
 *
 * Добавляет в CSS значения из настроек темы
 *
 * @param string $css - Текст CSS
 * @param theme_config $theme - Объект настроек темы
 *
 * @return string - Сформированный текст CSS
 */
function theme_opentechnology_process_css($css, $theme)
{
    global $CFG;

    // Общие обозначения
    $tag = '[[config:wwwroot]]';
    $replacement =  $CFG->wwwroot;
    $css = str_replace($tag, $replacement, $css);

    return $css;
}

/**
 * Постобработка CSS с учетом профиля
 *
 * Добавляет в CSS значения из настроек профиля Темы
 *
 * @param string $css - Текст CSS
 * @param theme_config $theme - Объект настроек темы
 * @param \theme_opentechnology\base - Профиль темы
 *
 * @return string - Сформированный текст CSS
 */
function theme_opentechnology_profile_process_css($css, $theme, $profile, $cssfile='profile')
{
    global $CFG, $PAGE;

    if( file_exists($theme->dir.'/profiles/overrides/'.$profile->get_code().'/style/'.$cssfile.'.css') )
    {
        // Получение CSS для профилей
        $css .= file_get_contents($theme->dir.'/profiles/overrides/'.$profile->get_code().'/style/'.$cssfile.'.css');
    }

    // Инициализация менеджера профилей
    $profilemanager = \theme_opentechnology\profilemanager::instance();

    if ($cssfile == 'profile')
    {
        // Добавление дополнительного CSS профиля
        // Может содержать в себе другие макроподстановки
        $setting = $profilemanager->get_theme_setting('main_customcss', $profile);
        if ( ! empty($setting) )
        {// Есть пользовательская настройка
            $css .= $setting;
        }
    }

    $responsivecss = '';

    $setting = $profilemanager->get_theme_setting('responsive_video', $profile);
    if ( ! empty($setting) )
    {
        $responsivecss .= '
            .mediaplugin_html5video,
            .mediaplugin_youtube,
            .mediaplugin_flv {
                width: 100%;
                padding-bottom: [[setting:responsive_video_ratio]]%;
                position: relative;
                margin-top: 0;
            }
            div[data-aspect-ratio="3x4"] .mediaplugin_html5video,
            div[data-aspect-ratio="3x4"] .mediaplugin_youtube,
            div[data-aspect-ratio="3x4"] .mediaplugin_flv {
                padding-bottom: 75%;
            }
            div[data-aspect-ratio="16x9"] .mediaplugin_html5video,
            div[data-aspect-ratio="16x9"] .mediaplugin_youtube,
            div[data-aspect-ratio="16x9"] .mediaplugin_flv {
                padding-bottom: 56.25%;
            }
            .mediaplugin_html5video video,
            .mediaplugin_youtube iframe,
            .mediaplugin_flv object {
                position: absolute;
                width: 100%;
                top: 0;
                left: 0;
                height: 100%;
            }';
    }

    // Адаптивный CSS
    $tag = '[[setting:responsive]]';
    $replacement = $responsivecss;
    $css = str_replace($tag, $replacement, $css);

    // Установка пользовательских шрифтов
    $tag = '[[setting:fonts]]';
    $replacement = theme_opentechnology_process_fonts_css($theme, $profile);
    $css = str_replace($tag, $replacement, $css);

    // Автоматическая подстановка селекторов
    $csstemplates = new theme_opentechnology\cssprocessor();
    foreach ( $csstemplates->get_list_supported_templates() as $template )
    {
        $csstemplates->change_template($template);
        $css = str_replace('[[setting:selector_'.$template.']]', $csstemplates->get_selectors(), $css);
    }

    // ШАПКА
    // Логотип в шапке

    $setting = $profilemanager->get_theme_setting('header_logoimage', $profile);
    $renderer = new core_renderer($PAGE, RENDERER_TARGET_GENERAL);
    $syslogo = $renderer->get_logo_url(420);
    $syscompactlogo = $renderer->get_compact_logo_url(240);
    if ( ! empty($setting) || $syslogo)
    {
        if (!empty($setting))
        {
            // Путь к изображению логотипа
            $logourl = $compactlogourl = (is_https()?'https:':'http:') . $profilemanager->get_theme_setting_file_url('header_logoimage', $profile);
        } else
        {
            // Путь к изображению логотипа
            $logourl = $syslogo->out(false);
            $compactlogourl = $syscompactlogo ? $syscompactlogo->out(false) : $logourl;
        }
    }

    $tag = '[[setting:header_logoimage]]';
    $replacement = is_null($logourl) ? 'none' : "url('" . $logourl . "')";
    $css = str_replace($tag, (string)$replacement, $css);

    $tag = '[[setting:header_logoimage_compact]]';
    $replacement = is_null($compactlogourl) ? 'none' : "url('" . $compactlogourl . "')";
    $css = str_replace($tag, (string)$replacement, $css);

    // Отступ логотипа
    $tag = '[[setting:header_logoimage_padding]]';
    $replacement = '0';
    $setting = $profilemanager->get_theme_setting('header_logoimage_padding', $profile);
    if ( ! empty($setting) )
    {// Есть пользовательская настройка
        $replacement = $setting;
    }
    $css = str_replace($tag, $replacement, $css);

    // Отступ описания
    $tag = '[[setting:header_text_padding]]';
    $replacement = '0';
    $setting = $profilemanager->get_theme_setting('header_text_padding', $profile);
    if ( ! empty($setting) )
    {// Есть пользовательская настройка
        $replacement = $setting;
    }
    $css = str_replace($tag, $replacement, $css);

    // Отступ пользовательского меню
    $tag = '[[setting:header_usermenu_padding]]';
    $replacement = '0';
    $setting = $profilemanager->get_theme_setting('header_usermenu_padding', $profile);
    if ( ! empty($setting) )
    {// Есть пользовательская настройка
        $replacement = $setting;
    }
    $css = str_replace($tag, $replacement, $css);

    // Фоновое изображение шапки
    $tag = '[[setting:header_backgroundimage]]';
    $replacement = $profilemanager->get_theme_setting_file_url('header_backgroundimage', $profile);
    $replacement = is_null($replacement) ? 'none' : "url('" . (is_https()?'https:':'http:') . $replacement . "')";
    $css = str_replace($tag, $replacement, $css);

    // Установка текстуры док-панели
    $tag = '[[setting:header_dockpanel_texture]]';
    $replacement = 'none';
    $setting = $profilemanager->get_theme_setting('header_dockpanel_texture', $profile);
    if ( ! empty($setting) )
    {// Есть пользовательская настройка
        $replacement = 'url("'.$theme->image_url('texture/'.$setting, 'theme_opentechnology').'")';
    }
    $css = str_replace($tag, $replacement, $css);

    // ФУТЕР
    // Установка текстуры рамки
    $tag = '[[setting:footer_border_texture]]';
    $replacement = 'none';
    $setting = $profilemanager->get_theme_setting('footer_border_texture', $profile);
    if ( ! empty($setting) )
    {// Текстура определена
        $replacement = 'url("'.$theme->image_url('texture/'.$setting, 'theme_opentechnology').'")';
    }
    $css = str_replace($tag, $replacement, $css);

    // Фоновое изображение футера
    $tag = '[[setting:footer_backgroundimage]]';
    $replacement = $profilemanager->get_theme_setting_file_url('footer_backgroundimage', $profile);
    $replacement = is_null($replacement) ? 'none' : "url('" . $replacement . "')";
    $css = str_replace($tag, $replacement, $css);

    // Страница авторизации
    // Отступ описания
    $tag = '[[setting:loginpage_header_text_padding]]';
    $replacement = '0';
    $setting = $profilemanager->get_theme_setting('loginpage_header_text_padding', $profile);
    if ( ! empty($setting) )
    {// Есть пользовательская настройка
        $replacement = $setting;
    }
    $css = str_replace($tag, $replacement, $css);

    // Адаптация
    // Соотношение сторон видео
    $tag = '[[setting:responsive_video_ratio]]';
    $replacement = '75';
    $setting = $profilemanager->get_theme_setting('responsive_video_ratio', $profile);
    if ( ! empty($setting) )
    {// Есть пользовательская настройка
        $replacement = $setting;
    }
    $css = str_replace($tag, $replacement, $css);

    // Инициализация цветов
    $css = theme_opentechnology_color_init($css, $profile, 'header', 'backgroundcolor');
    $setting = $profilemanager->get_theme_setting('color_header_backgroundcolor_text', $profile);
    if( empty($setting) || $setting == 'transparent' )
    {// Цвет не определен
        $setting = '#636361';
    }
    $css = theme_opentechnology_color_set_same($css, $profile, 'header', 'backgroundcolor_active_text', $setting, 50);
    $css = theme_opentechnology_color_init($css, $profile, 'header', 'basecolor');
    $css = theme_opentechnology_color_init($css, $profile, 'header', 'elementscolor');
    $css = theme_opentechnology_color_init($css, $profile, 'header', 'elementscolor_active');
    $css = theme_opentechnology_color_init($css, $profile, 'header', 'usermenubackgroundcolor');
    $css = theme_opentechnology_color_init($css, $profile, 'header', 'custommenubackgroundcolor');

    $basecolorsetting = $profilemanager->get_theme_setting('color_header_basecolor', $profile);
    $css = theme_opentechnology_color_set_same($css, $profile, 'header', 'topbasecolor', $basecolorsetting, null);
    $css = theme_opentechnology_color_init($css, $profile, 'header', 'topbasecolor');

    $setting = $profilemanager->get_theme_setting('color_header_custommenubackgroundcolor', $profile);
    $css = theme_opentechnology_color_set_same($css, $profile, 'header', 'custommenubackgroundcolor_active', $setting, -10);
    $css = theme_opentechnology_color_init($css, $profile, 'header', 'custommenubackgroundcolor_active');

    $setting = $profilemanager->get_theme_setting('color_header_elementscolor', $profile);
    $css = theme_opentechnology_color_set_same($css, $profile, 'header', 'custommenuelementscolor', $setting, null);
    $css = theme_opentechnology_color_init($css, $profile, 'header', 'custommenuelementscolor');

    $setting = $profilemanager->get_theme_setting('color_header_elementscolor_active', $profile);
    $css = theme_opentechnology_color_set_same($css, $profile, 'header', 'custommenuelementscolor_active', $setting, null);
    $css = theme_opentechnology_color_init($css, $profile, 'header', 'custommenuelementscolor_active');

    // КОНТЕНТ
    // Инициализация цветов
    $css = theme_opentechnology_color_init($css, $profile, 'content', 'backgroundcolor');
    $css = theme_opentechnology_color_init($css, $profile, 'content', 'basecolor');
    $css = theme_opentechnology_color_init($css, $profile, 'content', 'mod_header_backgroundcolor');
    $css = theme_opentechnology_color_init($css, $profile, 'content', 'mod_header_text_backgroundcolor');
    $css = theme_opentechnology_color_init($css, $profile, 'content', 'elementscolor');
    $css = theme_opentechnology_color_init($css, $profile, 'content', 'elementscolor_active');

    // БЛОКИ
    // Инициализация цветов
    $css = theme_opentechnology_color_init($css, $profile, 'blocks', 'backgroundcolor');
    $css = theme_opentechnology_color_init($css, $profile, 'blocks', 'basecolor');
    $css = theme_opentechnology_color_init($css, $profile, 'blocks', 'elementscolor');
    $css = theme_opentechnology_color_init($css, $profile, 'blocks', 'elementscolor_active');

    // ПОДВАЛ
    $css = theme_opentechnology_color_init($css, $profile, 'footer', 'backgroundcolor');
    $css = theme_opentechnology_color_init($css, $profile, 'footer', 'basecolor');
    $css = theme_opentechnology_color_init($css, $profile, 'footer', 'elementscolor');
    $css = theme_opentechnology_color_init($css, $profile, 'footer', 'elementscolor_active');

    // СВОРАЧИВАЕМЫЕ СЕКЦИИ
    $css = theme_opentechnology_color_init($css, $profile, 'collapsiblesection', 'backgroundcolor');
    $css = theme_opentechnology_color_init($css, $profile, 'collapsiblesection', 'elementscolor');
    $css = theme_opentechnology_color_init($css, $profile, 'collapsiblesection', 'elementscolor_active');

    // ЭЛЕМЕНТЫ ДОК-ПАНЕЛИ
    $css = theme_opentechnology_color_init($css, $profile, 'dockeditems', 'backgroundcolor');
    $css = theme_opentechnology_color_init($css, $profile, 'dockeditems', 'backgroundcolor_active', 'rgba(0,0,0,0.13)');
    $css = theme_opentechnology_color_init($css, $profile, 'dockeditems_iconview', 'backgroundcolor');
    $css = theme_opentechnology_color_init($css, $profile, 'dockeditems_iconview', 'backgroundcolor_active', 'rgba(0,0,0,0.13)');

    // ССЫЛКИ
    // Добавление цвета ссылок
    $setting = $profilemanager->get_theme_setting('color_content_elementscolor', $profile);
    if( empty($setting) || $setting == 'transparent' )
    {// Цвет не определен
        $setting = '#0070a8';
    }
    $css = theme_opentechnology_color_set_same($css, $profile, 'links', 'color', $setting, 25);

    // Добавление цвета ссылок при наведении
    $setting = $profilemanager->get_theme_setting('color_links_color', $profile);
    $css = theme_opentechnology_color_set_same($css, $profile, 'links', 'color_hover', $setting, -10);

    // Добавление цвета ссылок в хлебных крошках
    $setting = $profilemanager->get_theme_setting('color_content_backgroundcolor_text', $profile);
    if( empty($setting) || $setting == 'transparent' )
    {// Цвет не определен
        $setting = '#333333';
    }
    $css = theme_opentechnology_color_set_same($css, $profile, 'breadcrumb_links', 'color', $setting, 25);

    // Добавление цвета ссылок в хлебных крошках при наведении
    $css = theme_opentechnology_color_set_same($css, $profile, 'breadcrumb_links', 'color_hover', $setting, '+25');


    // Задние фоны
    $pagebacks = [
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
    foreach($pagebacks as $pageback)
    {
        $tag = '[[setting:pb_'.$pageback.'_backgroundimage]]';
        $replacement = $profilemanager->get_theme_setting_file_url('pb_'.$pageback.'_backgroundimage', $profile);
        $replacement = is_null($replacement) ? 'none' : "url('" . $replacement . "')";
        $css = str_replace($tag, $replacement, $css);

        $css = theme_opentechnology_color_init($css, $profile, 'pb_'.$pageback, 'backgroundcolor');
    }

    return $css;
}

/**
 * Установка цвета на основе другого цвета
 *
 * @param string $css - css-код
 * @param \theme_opentechnology\base - Профиль темы
 * @param string $section - секция настроек
 * @param string $type - тип настройки
 * @param string $sourcesection - секция настройки-источника цвета
 * @param string $sourcetype - тип настройки-источника цвета
 * @param string $lighten - устанавливает светлоту от 1 до 100.
 *                          Если указывается со знаком вначале (+ или -), то изменяет светлоту цвета-источника.
 *                          Если null, не меняет светлоту цвета-источника
 * @return string css-код с заменами
 */
function theme_opentechnology_color_set_same($css, $profile, $section, $type, $sourcecolorstring, $lighten = null)
{
    // Инициализация менеджера профилей
    $profilemanager = \theme_opentechnology\profilemanager::instance();

    // Базовое имя настройки
    $settingname = 'color_'.$section.'_'.$type;
    // Базвая Макроподстановка
    $tag = 'setting:'.$settingname;

    // Полное имя настройки с учетом профиля
    $settingfullname = $profilemanager->get_theme_setting_name($settingname, $profile);
    $setting = $profilemanager->get_theme_setting($settingname, $profile);
    $replacement = '#333333';
    if ( ! empty($setting) )
    {// Цвет задан в настройках
        $replacement = $setting;
    } elseif ( ! empty($sourcecolorstring))
    {// Указан источник для вычисления цвета
        if ( ! is_null($lighten) )
        {
            $toc = new \theme_opentechnology\colormanager($sourcecolorstring);
            $toc->change_lighten($lighten);
            $replacement = (string)$toc;
        } else
        {
            $replacement = $sourcecolorstring;
        }
    }

    // Добавление временной настройки
    $profilemanager->theme_config->settings->$settingfullname = $replacement;

    $css = str_replace('[['.$tag.']]', $replacement, $css);

    return $css;
}

/**
 * Формирование css-кода для подключения шрифтов
 *
 * @param theme_config $theme - Объект настроек темы
 * @param \theme_opentechnology\base - Профиль темы
 *
 * @return string - Сформированный текст CSS
 */
function theme_opentechnology_process_fonts_css($theme, $profile)
{
    $css = '';

    // Инициализация менеджера профилей
    $profilemanager = \theme_opentechnology\profilemanager::instance();

    // Семейство шрифтов по умолчанию
    $defaultfontfamily = 'DefaultFont';

    // Минимальный набор шлифтов
    $defaultfontsettings = [
        '300 normal' => $theme->font_url('OpenSans-Light.ttf', 'theme_opentechnology'),
        '300 italic' => $theme->font_url('OpenSans-LightItalic.ttf', 'theme_opentechnology'),
        '400 normal' => $theme->font_url('OpenSans-Regular.ttf', 'theme_opentechnology'),
        '600 normal' => $theme->font_url('OpenSans-Semibold.ttf', 'theme_opentechnology'),
        '700 normal' => $theme->font_url('OpenSans-Bold.ttf', 'theme_opentechnology')
    ];

    // Получение имени настройки для профиля
    $settigfullname = $profile->get_setting_name('custom_fonts_files');
    // Получение зоны
    $filearea = $profilemanager->get_theme_setting_filearea('custom_fonts_files', $profile);

    // Получение загруженных шрифтов
    $fontfiles = theme_opentechnology_get_filearea_files($filearea, $settigfullname, 0);
    foreach ( $fontfiles as $fontfile )
    {
        // Код пользовательской настройки font-family для файла
        $fontfamilysetting = $profilemanager->get_theme_setting('custom_fonts_font_family_'.$fontfile->settingname, $profile);
        // Код пользовательской настройки font-weight для файла
        $fontweightsetting = $profilemanager->get_theme_setting('custom_fonts_font_weight_'.$fontfile->settingname, $profile);
        // Код пользовательской настройки font-style для файла
        $fontstylesetting = $profilemanager->get_theme_setting('custom_fonts_font_style_'.$fontfile->settingname, $profile);

        if ( ! empty($fontfamilysetting) && ! empty($fontweightsetting) && ! empty($fontstylesetting) )
        {// Настройки шрифтов заданы

            // Строка пользовательских настроек
            $fontsettings = $fontweightsetting.' '.$fontstylesetting;
            if($fontfamilysetting == $defaultfontfamily && array_key_exists($fontsettings, $defaultfontsettings) )
            {// Переопределение шрифта по умолчанию

                // удаляем настройку из требуемой, чтобы не загружать дефолтный шрифт
                unset($defaultfontsettings[$fontsettings]);
            }

            $url = new moodle_url($fontfile->url);
            // Добавление стилей для загруженного пользователем шрифта
            $css .= "
                @font-face {
                     font-family: '".$fontfamilysetting."';
                     src: url(".$url->out(false).") format('truetype');
                     font-weight: ".$fontweightsetting.";
                     font-style: ".$fontstylesetting.";
                }";
        }
    }

    // Добавление стилей для требуемых шрифтов, не определенных пользователем
    foreach ( $defaultfontsettings as $defaultfontsetting => $url )
    {
        list($fontweight, $fontstyle) = explode(' ', $defaultfontsetting);

        // Стили со шрифтами по умолчанию
        $css .=  "
            @font-face {
                 font-family: '".$defaultfontfamily."';
                 src: url(".$url->out(false).") format('truetype');
                 font-weight: ".$fontweight.";
                 font-style: ".$fontstyle.";
            }";
    }
    return $css;
}

/**
 * Подготовить файл в соответствие с настройками темы
 *
 * @param stdClass $course - Курс
 * @param stdClass $cm
 * @param context $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @param array $options
 *
 * @return bool
 */
function theme_opentechnology_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array())
{
    // Получение менеджера профилей
    $manager = \theme_opentechnology\profilemanager::instance();

    // Получение профилей
    $profiles = $manager->get_profiles();

    $pagebacks = [
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
    // Получение списка зон файлов
    $settingsareas = [];
    foreach ( $profiles as $profile )
    {
        $settingsareas[] = $manager->get_theme_setting_filearea('main_public_file', $profile);
        $settingsareas[] = $manager->get_theme_setting_filearea('main_favicon', $profile);
        $settingsareas[] = $manager->get_theme_setting_filearea('header_logoimage', $profile);
        $settingsareas[] = $manager->get_theme_setting_filearea('header_backgroundimage', $profile);
        $settingsareas[] = $manager->get_theme_setting_filearea('loginpage_slider_images', $profile);
        $settingsareas[] = $manager->get_theme_setting_filearea('loginpage_sidebar_images', $profile);
        $settingsareas[] = $manager->get_theme_setting_filearea('loginpage_sidebar_logoimage', $profile);
        $settingsareas[] = $manager->get_theme_setting_filearea('loginpage_header_logo', $profile);
        $settingsareas[] = $manager->get_theme_setting_filearea('footer_logoimage', $profile);
        $settingsareas[] = $manager->get_theme_setting_filearea('footer_backgroundimage', $profile);
        $settingsareas[] = $manager->get_theme_setting_filearea('custom_fonts_files', $profile);
        $settingsareas[] = $manager->get_theme_setting_filearea('slider_images', $profile);
        foreach($pagebacks as $pageback)
        {
            $settingsareas[] = $manager->get_theme_setting_filearea('pb_'.$pageback.'_backgroundimage', $profile);
        }
    }
    if ( $context->contextlevel == CONTEXT_SYSTEM && in_array($filearea, $settingsareas) )
    {

        $theme = theme_config::load('opentechnology');
        if ( ! array_key_exists('cacheability', $options))
        {
            $options['cacheability'] = 'public';
        }


        return $theme->setting_file_serve($filearea, $args, $forcedownload, $options);
    }

    // Экспорт файла настроек
    if ( $context->contextlevel == CONTEXT_SYSTEM && $filearea == 'exportsettings' )
    {
        $itemid = array_shift($args);
        $filename = array_pop($args);
        if (!$args) {
            $filepath = '/';
        } else {
            $filepath = '/'.implode('/', $args).'/';
        }

        $fs = get_file_storage();
        $file = $fs->get_file($context->id, 'theme_opentechnology', $filearea, $itemid, $filepath, $filename);

        if (!$file) {
            return false;
        }

        \core\session\manager::write_close();
        send_stored_file($file, null, 0, $forcedownload, $options);
    }

    send_file_not_found();
}

/**
 *
 * @param unknown $css
 * @param \theme_opentechnology\base - Профиль темы
 * @param unknown $section
 * @param unknown $type
 * @return mixed
 */
function theme_opentechnology_color_init($css, $profile, $section, $type, $notsetvalue='transparent')
{
    // Инициализация менеджера профилей
    $profilemanager = \theme_opentechnology\profilemanager::instance();

    // Базовое имя настройки
    $settingname = 'color_'.$section.'_'.$type;
    // Базвая Макроподстановка
    $tag = 'setting:'.$settingname;

    // ОПРЕДЕЛЕНИЕ ЦВЕТА ФОНА
    // Полное имя настройки с учетом профиля
    $settingfullname = $profilemanager->get_theme_setting_name($settingname, $profile);
    // Получение цвета для профиля
    $color = $profilemanager->get_theme_setting($settingname, $profile);
    $replacement = $notsetvalue;
    if ( ! empty($color) )
    {// Цвет указан
        $replacement = $color;
    }
    if( $replacement == 'transparent' )
    {
        $islight = true;
    } else
    {
        $toc = new \theme_opentechnology\colormanager($replacement);
        $islight = $toc->is_light();
    }
    // Добавление цвета фона в CSS
    $css = str_replace('[['.$tag.']]', $replacement, $css);

    // Добавление временной настройки
    $profilemanager->theme_config->settings->$settingfullname = $replacement;

    // ОПРЕДЕЛЕНИЕ ЦВЕТА ТЕКСТА НА ФОНЕ
    $settingtext = $settingname.'_text';
    $tagtext = $tag.'_text';

    // Полное имя настройки с учетом профиля
    $settingfullname = $profilemanager->get_theme_setting_name($settingtext, $profile);
    // Получение цвета для профиля
    $color = $profilemanager->get_theme_setting($settingtext, $profile);
    if ( empty($color) )
    {// Автоматическая генерация цвета текста
        if ( $islight )
        { // фон очень светлый, используем темный текст
            $replacement = '#333333';
        } else
        { //фон очень темный, делаем белый текст
            $replacement = '#FFFFFF';
        }
    } else
    {// Ручная генерация цвета
         $replacement = $color;
    }
    $css = str_replace('[['.$tagtext.']]', $replacement, $css);

    // Добавление временной настройки
    $profilemanager->theme_config->settings->$settingfullname = $replacement;

    // ОПРЕДЕЛЕНИЕ ЦВЕТА ИКОНОК НА ФОНЕ
    $tagicon = $tag.'_icon';
    $settingiconbrightness = $settingname.'_icon_brightness';

    // По умолчанию будем определять яркость автоматически
    $iconbrightness = '0';

    // Полное имя настройки с учетом профиля
    $settingfullname = $profilemanager->get_theme_setting_name($settingiconbrightness, $profile);
    // Получение цвета для профиля
    $settingvalue = $profilemanager->get_theme_setting($settingiconbrightness, $profile);
    if( ! empty($settingvalue) )
    {// задана настройка яркости
        $iconbrightness = $settingvalue;
    }

    switch ( $iconbrightness )
    {
        // Автоматическое определение
        case '0':
            if ( $islight )
            {// Светлый фон - стандартные иконки
                $brightnesslevel = .665;
                $invert = 0;
            } else
            {// Темный фон - увеличение яркости иконок
                $brightnesslevel = 0;
                $invert = 100;
            }
            break;
        // Сильное затемнение
        case '1':
            $brightnesslevel = 0.31;
            $invert = 0;
            break;
        // Затемнение
        case '2':
            $brightnesslevel = 0.665;
            $invert = 0;
            break;
        // Осветление
        case '4':
            $brightnesslevel = 0;
            $invert = 75;
            break;
        // Сильное осветление
        case '5':
            $brightnesslevel = 0;
            $invert = 100;
            break;
        // Стандартный цвет иконок
        case '3':
        default:
            $brightnesslevel = 1;
            $invert = 0;
            break;
    }

    // Добавление временной настройки
    $profilemanager->theme_config->settings->$settingfullname = $brightnesslevel;

    $replacement = 'filter: brightness('.$brightnesslevel.') invert('.$invert.'%);
                    -moz-filter: brightness('.($brightnesslevel*100).'%) invert('.$invert.'%);
                    -webkit-filter: brightness('.($brightnesslevel*100).'%) invert('.$invert.'%);
                    -ms-filter: brightness('.($brightnesslevel*100).'%) invert('.$invert.'%);
                    -o-filter: brightness('.($brightnesslevel*100).'%) invert('.$invert.'%);';

    $css = str_replace('[['.$tagicon.']]', $replacement, $css);

    return $css;
}

/**
 * Получение файлов, соответствующих указанной зоне настроек темы
 *
 * @param unknown $filearea
 * @param unknown $setting
 * @param number $itemid
 *
 * @return stdClass[]
 */
function theme_opentechnology_get_filearea_files($filearea, $setting, $itemid = 0)
{
    // Результат
    $result = [];

    $theme = theme_config::load('opentechnology');
    $fs = get_file_storage();
    $syscontext = context_system::instance();
    $component = 'theme_'.$theme->name;

    // Получение файлов темы
    $files = $fs->get_area_files($syscontext->id, $component, $filearea, $itemid);
    foreach ( $files as $file )
    {
        if ( $file->is_directory() )
        {// Пропуск директорий
            continue;
        }

        $resultfile = new stdClass();
        //наименование файла
        $resultfile->filename = $file->get_filename();
        //ссылка на файл
        $resultfile->url = moodle_url::make_pluginfile_url(
            $file->get_contextid(),
            $file->get_component(),
            $file->get_filearea(),
            $file->get_itemid(),
            $file->get_filepath(),
            $file->get_filename()
        );
        // Траектория до файла
        $resultfile->path = $file->get_filepath();

        $resultfile->settingname = strtolower(preg_replace("/[^A-Za-z0-9]/", '_', $file->get_filename()));
        $result[] = $resultfile;
    }
    return $result;
}

function theme_opentechnology_purge_caches()
{
    // Очистка всех кэшей темы
    theme_reset_all_caches();

    // Очистка кэша профилей
    $cache = cache::make('theme_opentechnology', 'profilecss');
    $cache->purge();
}

function theme_opentechnology_replace_domains()
{
    global $CFG;

    if( ! empty($CFG->whitelistdomains) && is_array($CFG->whitelistdomains) )
    {
        $buffer = ob_get_clean();

        $hosts = [];
        foreach($CFG->whitelistdomains as $whitedomain)
        {
            $hosts[] = str_replace(".", "\.", $whitedomain);
        }

        $currenthost = parse_url($CFG->wwwroot, PHP_URL_HOST);

        // Производится замена всех доменов из белого списка на текущий домен
        echo preg_replace(
            '/(<a[^>]*href=["\']?[^"\'>]+)('.implode('|', $hosts).')([^"\'>]+["\']?)/',
            "$1".$currenthost."$3",
            $buffer
        );

    }
}

function theme_opentechnology_extend_navigation_user_settings($usersetting, $user, $usercontext, $course, $coursecontext)
{
    $url = new moodle_url('/theme/opentechnology/gradereportsettings.php', ['userid' => $user->id, 'courseid' => $course->id]);
    $subsnode = navigation_node::create(
        get_string('gradereportsettings', 'theme_opentechnology'),
        $url,
        navigation_node::TYPE_SETTING,
        null,
        'gradereportsettings',
        new pix_icon('i/settings', '')
    );

    if (isset($subsnode) && !empty($usersetting)) {
        $usersetting->add_node($subsnode);
    }
}

function theme_opentechnology_user_preferences()
{
    $preferences = [];
    $preferences['verticaldisplay'] = [
        'type' => PARAM_INT,
        'null' => NULL_NOT_ALLOWED,
        'default' => '0',
        'choices' => [0, 1]
    ];
    return $preferences;
}


/**
 * Renders the popup.
 *
 * @param renderer_base $renderer
 * @return string The HTML
 */
function theme_opentechnology_render_navbar_output(\renderer_base $renderer) {
    if (method_exists($renderer, 'get_current_profile_class_object'))
    {
        $profileobject = $renderer->get_current_profile_class_object();
        if ($profileobject && method_exists($profileobject, 'render_navbar_output'))
        {
            // Дополнительная обработка сформированного кода меню
            return $profileobject->render_navbar_output($renderer);
        }
    }
}

/**
 * Вместо обычного пути для подгрузки стилей нашей темы (all) подключаем стили профиля
 * @param moodle_url[] $urls
 */
function theme_opentechnology_alter_css_urls(&$urls)
{
    foreach($urls as $u => $url)
    {
        $urlpath = $url->get_path();
        if (preg_match('/\/theme\/styles\.php\/opentechnology\/.*\/all/', $urlpath))
        {
            // Инициализация менеджера профилей
            $manager = theme_opentechnology\profilemanager::instance();
            // Получение профиля текущей страницы
            $profile = $manager->get_current_profile();

            $urls[$u] = new moodle_url('/theme/opentechnology/stylesprofile.php/profile/'.$profile->get_code());
//             $urls[$u] = new moodle_url('/theme/opentechnology/empty.css');
        }
    }
}

function theme_opentechnology_get_pre_scss($theme)
{
    return cssprocessor::get_pre_scss($theme);
}


function theme_opentechnology_get_main_scss_content($theme) {
    global $CFG;
    $scss= '';
    $scss .= file_get_contents($CFG->dirroot . '/theme/opentechnology/scss/otmain.scss');
//     echo PHP_EOL.'theme_opentechnology_get_main_scss_content'.PHP_EOL;
//     echo $scss;
    return $scss;
}


function theme_opentechnology_get_extra_scss($theme) {
    return cssprocessor::get_extra_scss($theme);
}

function theme_opentechnology_after_config() {
    global $CFG;

    if (empty($CFG->blockmanagerclass)) {
        $CFG->blockmanagerclass = '\\theme_opentechnology\\block_manager';
    }

}

function theme_opentechnology_get_precompiled_css() {
    global $CFG;
    return file_get_contents($CFG->dirroot . '/theme/opentechnology/style/precompiled.css');
}

function theme_opentechnology_before_standard_top_of_body_html() {
    global $USER;

    $result = '';

    ///////////////////////////
    // Редирект после логина //
    ///////////////////////////

    if (!isloggedin()) {
        // редирект нужен только для залогиненных
        return '';
    }

    $currentpageurl = new moodle_url(qualified_me());
    if (is_login_or_signup_url($currentpageurl)) {
        // не редиректим, если пользователь на странице регистрации/авторизации
        return '';
    }

    if (user_not_fully_set_up($USER, true)) {
        // редирект будет преждевеременным, если у пользователя недозаполнен профиль
        // дозаполнит - тогда и вернемся к этому вопросу
        return '';
    }

    // скрипт редиректа
    $result .= "<script>".
        "var u=localStorage.getItem('otLoginWantsUrl');".
        "if(u){".
            "localStorage.removeItem('otLoginWantsUrl');".
            "localStorage.removeItem('otJustRegistered');".
            "document.location.href=u".
        "}".
    "</script>";


    return $result;
}
function theme_opentechnology_post_signup_requests($data) {
    echo "<script>localStorage.setItem('otJustRegistered', true);</script>";
}

