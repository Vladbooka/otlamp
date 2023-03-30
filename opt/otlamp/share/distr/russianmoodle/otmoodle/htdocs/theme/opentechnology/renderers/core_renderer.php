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
 * Тема СЭО 3KL. Рендер.
 *
 * @package    theme_opentechnology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// require_once($CFG->dirroot. '/theme/bootstrapbase/renderers/core_renderer.php');

use \theme_opentechnology\profilemanager;
use \theme_opentechnology\profiles\base;

class theme_opentechnology_core_renderer extends \core_renderer
{
    var $themesettings;

    /**
     * Профиль текущей страницы
     *
     * @var base
     */
    private $currentprofile = null;

    /**
     * Конструктор рендера
     *
     * @param moodle_page $page the page we are doing output for.
     * @param string $target one of rendering target constants
     */
    public function __construct($page, $target)
    {
        // Получение менеджера профилей
        $manager = profilemanager::instance();
        // Установка профиля для рендера
        $this->currentprofile = $manager->get_current_profile();

        parent::__construct($page, $target);

    }

    /**
     * Return the standard string that says whether you are logged in (and switched
     * roles/logged in as another user).
     * @param bool $withlinks if false, then don't include any links in the HTML produced.
     * If not set, the default is the nologinlinks option from the theme config.php file,
     * and if that is not set, then links are included.
     * @return string HTML fragment.
     */
    public function login_info($withlinks = null) {
        global $USER, $CFG, $DB, $SESSION;

        if (during_initial_install()) {
            return '';
        }

        if (is_null($withlinks)) {
            $withlinks = empty($this->page->layout_options['nologinlinks']);
        }

        $course = $this->page->course;
        if (\core\session\manager::is_loggedinas()) {
            $realuser = \core\session\manager::get_realuser();
            $fullname = fullname($realuser, true);
            if ($withlinks) {
                $loginastitle = get_string('loginas');
                $realuserinfo = " [<a href=\"$CFG->wwwroot/course/loginas.php?id=$course->id&amp;sesskey=".sesskey()."\"";
                $realuserinfo .= "title =\"".$loginastitle."\">$fullname</a>] ";
            } else {
                $realuserinfo = " [$fullname] ";
            }
        } else {
            $realuserinfo = '';
        }

        $loginpage = $this->is_login_page();
        $loginurl = get_login_url();

        if (empty($course->id)) {
            // $course->id is not defined during installation
            return '';
        } else if (isloggedin()) {
            $context = context_course::instance($course->id);

            $fullname = fullname($USER, true);
            // Since Moodle 2.0 this link always goes to the public profile page (not the course profile page)
            if ($withlinks) {
                $linktitle = get_string('viewprofile');
                $username = "<a href=\"$CFG->wwwroot/user/profile.php?id=$USER->id\" class=\"btn btn-link\" title=\"$linktitle\">$fullname</a>";
            } else {
                $username = $fullname;
            }
            if (is_mnet_remote_user($USER) and $idprovider = $DB->get_record('mnet_host', array('id'=>$USER->mnethostid))) {
                if ($withlinks) {
                    $username .= " from <a href=\"{$idprovider->wwwroot}\">{$idprovider->name}</a>";
                } else {
                    $username .= " from {$idprovider->name}";
                }
            }
            if (isguestuser()) {
                $loggedinas = $realuserinfo.get_string('loggedinasguest');
                if (!$loginpage && $withlinks) {
                    $loggedinas .= " <a href=\"$CFG->wwwroot/login/logout.php?sesskey=".sesskey()."\" class=\"btn button btn-primary\">".get_string('logout').'</a>';
                }
            } else if (is_role_switched($course->id)) { // Has switched roles
                $rolename = '';
                if ($role = $DB->get_record('role', array('id'=>$USER->access['rsw'][$context->path]))) {
                    $rolename = ': '.role_get_name($role, $context);
                }
                $loggedinas = get_string('loggedinas', 'moodle', $username).$rolename;
                if ($withlinks) {
                    $url = new moodle_url('/course/switchrole.php', array('id'=>$course->id,'sesskey'=>sesskey(), 'switchrole'=>0, 'returnurl'=>$this->page->url->out_as_local_url(false)));
                    $loggedinas .= ' ('.html_writer::tag('a', get_string('switchrolereturn'), array('href' => $url)).')';
                }
            } else {
                $loggedinas = $realuserinfo.get_string('loggedinas', 'moodle', $username);
                if ($withlinks) {
                    $loggedinas .= " <a href=\"$CFG->wwwroot/login/logout.php?sesskey=".sesskey()."\" class=\"btn button btn-primary\">".get_string('logout').'</a>';
                }
            }
        } else {
            $loggedinas = get_string('loggedinnot', 'moodle');
            if (!$loginpage && $withlinks) {
                $loggedinas .= " <a href=\"$loginurl\" class=\"btn button btn-primary ajaxpopup-footer-login\">".get_string('login').'</a>';
            }
        }

        $loggedinas = '<div class="logininfo">'.$loggedinas.'</div>';

        if (isset($SESSION->justloggedin)) {
            unset($SESSION->justloggedin);
            if (!empty($CFG->displayloginfailures)) {
                if (!isguestuser()) {
                    // Include this file only when required.
                    require_once($CFG->dirroot . '/user/lib.php');
                    if ($count = user_count_login_failures($USER)) {
                        $loggedinas .= '<div class="loginfailures">';
                        $a = new stdClass();
                        $a->attempts = $count;
                        $loggedinas .= get_string('failedloginattempts', '', $a);
                        if (file_exists("$CFG->dirroot/report/log/index.php") and has_capability('report/log:view', context_system::instance())) {
                            $loggedinas .= ' '.html_writer::link(new moodle_url('/report/log/index.php', array('chooselog' => 1,
                                'id' => 0 , 'modid' => 'site_errors')), get_string('logs'), [
                                    'class' => 'btn button btn-primary'
                                ]);
                        }
                        $loggedinas .= '</div>';
                    }
                }
            }
        }
        return $loggedinas;
    }

    public function user_picture(stdClass $user, array $options = null)
    {
        $options = (array)$options;
        if ( empty($options['size']) )
        {
            $options['size'] = 100;
        }
        return parent::user_picture($user,$options);
    }


//     public function render_user_picture(user_picture $userpicture)
//     {
//         if ( empty($userpicture->size) )
//         {
//             $userpicture->size = 100;
//         }
//         return parent::render_user_picture($userpicture);
//     }

    /**
     * Генерация аттрибутов для html-тега
     *
     * @return string - Список аттрибутов
     */
    public function htmlattributes()
    {
        // Получение базовых аттрибутов
        $attributes = parent::htmlattributes();

        // Получение менеджера профилей
        $manager = profilemanager::instance();

        // Получение системного контекста
        $systemcontext = \context_system::instance();
        $plugin = \core_plugin_manager::instance()->get_plugin_info('theme_opentechnology');
        // До версии 2019092300 прав не существовало
        $copy_nojsaccess = false;
        $capabilitiesexists = !empty($plugin->versiondb) && $plugin->versiondb >= 2019092300;

        // Получение настройки блокировки системы при отключенном JS
        $nojsaccess = $manager->get_theme_setting('security_copy_nojsaccess', $this->currentprofile);
        if (!empty($nojsaccess))
        {// Включена блокировка
            if ($capabilitiesexists) {
                // Проверяем права, если включена настройка и права существуют
                // Право на игнорирование запрета на доступ без включенного javascript
                $copy_nojsaccess = has_capability('theme/opentechnology:security_copy_nojsaccess', $systemcontext);
            }
            if (!$copy_nojsaccess) {
                $attributes .= ' data-nojs="message"';
            }

        }

        // Получение настройки типа заголовков элементов док-панели
        $dockeditemtitle = $manager->get_theme_setting('main_dockeditem_title', $this->currentprofile);
        if( empty($dockeditemtitle) )
        {
            $dockeditemtitle = 0;
        }
        $attributes .= ' data-dockeditem-title="'.$dockeditemtitle.'"';

        return $attributes;
    }

    /**
     * Получение ссылок на социальные сети
     *
     * @return string
     */
    public function social_links()
    {
        $html = '';

        // Получение менеджера профилей
        $manager = profilemanager::instance();
        // Получение текста
        $sociallinks = $manager->get_theme_setting('footer_social_links', $this->currentprofile);

        if ( ! empty($sociallinks) )
        {
            // Разбиение ссылок на соцсети
            $links = explode("\n", $sociallinks);
            if ( ! empty($links) )
            {
                $html .= html_writer::start_div('social_block');
                $html .= html_writer::start_div('social_blocklinks');
                foreach ( $links as $link )
                {
                    // Определение URL ссылки
                    $url = parse_url($link);
                    if ( ! empty($url) && isset($url['host']) )
                    {
                        $class = str_replace('.','-',$url['host']);
                        switch ( $url['host'] )
                        {
                            case 'www.facebook.com':
                            case 'facebook.com':
                                $class = 'facebook';
                                break;
                            case 'www.vk.com':
                            case 'vk.com':
                                $class = 'vkontakte';
                                break;
                            case 'twitter.com':
                            case 'www.twitter.com':
                                $class = 'twitter';
                                break;
                            case 'plus.google.com':
                            case 'www.plus.google.com':
                                $class = 'google';
                                break;
                            case 'ok.ru':
                            case 'www.ok.ru':
                            case 'odnoklassniki.ru':
                            case 'www.odnoklassniki.ru':
                                $class = 'ok';
                                break;
                            case 'www.youtube.com':
                            case 'youtube.com':
                                $class = 'youtube';
                                break;
                            case 'instagram.com':
                            case 'www.instagram.com':
                                $class = 'instagram';
                                break;
                            case 'telegram.me':
                            case 'www.telegram.me':
                                $class = 'telegram';
                                break;
                            case 'linkedin.com':
                            case 'www.linkedin.com':
                            case 'ru.linkedin.com':
                            case 'www.ru.linkedin.com':
                                $class = 'linkedin';
                                break;
                            default :
                                break;
                        }
                        $html .= html_writer::link($link, '', ['class' => $class.' btn btn-primary p-0']);
                    }
                }
                $html .= html_writer::end_div();
                $html .= html_writer::end_div();
            }
        }
        return $html;
    }

    /**
     * Получение текста логотипа в подвале
     *
     * @return string
     */
    public function f_text()
    {
        $html = '';

        // Получение менеджера профилей
        $manager = profilemanager::instance();
        // Получение текста
        $text = format_text($manager->get_theme_setting('footer_text', $this->currentprofile), FORMAT_HTML);

        if ( ! empty($text) )
        {
            $html .= html_writer::start_div('f_text');
            $html .= html_writer::div($text, 'f_text_content');
            $html .= html_writer::end_div();
        }

        return $html;
    }

    /**
     * Получение текста копирайта в подвале
     *
     * @return string
     */
    public function copyright_text()
    {
        $html = '';

        // Получение менеджера профилей
        $manager = profilemanager::instance();
        // Получение текста
        $text = format_text($manager->get_theme_setting('copyright_text', $this->currentprofile), FORMAT_HTML);

        if ( ! empty($text) )
        {
            $text = str_replace('{CURRENTYEAR}' , date('Y'), $text);

            $html .= html_writer::start_div('f_copyright_text');
            $html .= html_writer::div($text, 'f_copyright_text_content');
            $html .= html_writer::end_div();
        }

        return $html;
    }

    public function custom_custommenu($custommenuitems = '', $menuname='custommenu', $hidelang=false, $nomobile=false, $displaylabel=true)
    {
        global $CFG, $PAGE;

        // Получение менеджера профилей
        $manager = profilemanager::instance();

        // Получение настройки отображения выбора языка
        $langmenu = $manager->get_theme_setting('main_langmenu', $this->currentprofile);

        if ($hidelang)
        {
            $langmenuclass = ' langmenu-hidden';
        } else
        {
            switch ((int)$langmenu)
            {
                case 2:
                    $langmenuclass = ' langpanel';
                    break;
                case 1:
                    $langmenuclass = ' langmenu-base';
                    break;
                case 0:
                default:
                    $langmenuclass = ' langmenu-hidden';
                    break;
            }
        }

        if ( empty($custommenuitems) && $menuname == 'custommenu' && ! empty($CFG->custommenuitems))
        {
            $custommenuitems = $CFG->custommenuitems;
        }

        // получение объекта текущего профиля, если он есть
        $profileobject = $this->get_current_profile_class_object();

        // обработка макроподстановок в кастом меню
        $this->custom_menu_replace_macrosubstitutions($custommenuitems, $menuname);

        if ( empty($custommenuitems) && ((int)$langmenu == 0 || $hidelang))
        {
            return '';
        }


        $custommenu = new custom_menu($custommenuitems, current_language());

        $nomobileclass = $nomobile ? " nomobile" : '';
        $html = '<input type="checkbox" id="'.$menuname.'_mobile_checkbox" class="custom_menu_mobile_checkbox" />';
        if ($displaylabel)
        {
            $html .= '<label id="'.$menuname.'_mobile_label" class="custom_menu_mobile_label moodle-has-zindex'.$nomobileclass.'" for="'.$menuname.'_mobile_checkbox"></label>';
        }
        $html .= '<label id="'.$menuname.'_mobile_bg_label" class="custom_menu_mobile_bg_label'.$nomobileclass.' moodle-has-zindex" for="'.$menuname.'_mobile_checkbox"></label>';

        $custommenuparams = [];
        if (!empty($profileobject) && method_exists($profileobject, 'render_custom_menu'))
        {
            $custommenuitems = $profileobject->render_custom_menu($this, $menuname, $custommenu);
        } else
        {
            $custommenuitems = $this->render_custom_menu($custommenu);
        }
        $custommenuhtml = $this->render_from_template('theme_opentechnology/custom_menu', $custommenuitems);
        $html .= '<div id="'.$menuname.'_wrapper" class="custom_menu_wrapper navbar-expand '.$nomobileclass.$langmenuclass.'">'.$custommenuhtml.'</div>';

        if ($this->lang_menu() != '')
        {
            $dom = new DOMDocument();
            $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'utf-8');
            $dom->loadHTML($html);
            $lis = $dom->getElementsByTagName('li');
            if ($lis->length > 0)
            {
                $listitem = $lis[$lis->length-1];
                $listitem->setAttribute('class', $listitem->getAttribute('class').' langmenu');
                if ($langmenu == 2 && !$hidelang)
                {// требуется отобразить языковое меню в виде панели
                    foreach($listitem->getElementsByTagName('a') as $taga)
                    {
                        $classes = explode(' ', $taga->getAttribute('class'));
                        if (array_search('dropdown-toggle', $classes) !== false &&
                            preg_match('/(?=.*)\((.*)\)(?=.*)/', $taga->nodeValue, $match) )
                        {
                            $activated = strtoupper($match[1]);
                        }
                    }
                    foreach($listitem->getElementsByTagName('div') as $dropdownmenu)
                    {
                        if ($dropdownmenu->getAttribute('class') == 'dropdown-menu')
                        {
                            $dropdownmenu->setAttribute('class', 'btn-group btn-group-xs');
                            $dropdownmenu->setAttribute('role', 'group');

                            foreach($dropdownmenu->getElementsByTagName('a') as $dropdownitem)
                            {
                                if (preg_match('/(?=.*)\((.*)\)(?=.*)/', $dropdownitem->nodeValue, $match))
                                {
                                    $dditemclass = 'btn btn-outline-default';
                                    if (!empty($activated) && $activated == strtoupper($match[1]))
                                    {
                                        $dditemclass .= ' active';
                                    }
                                    $dropdownitem->setAttribute('class', $dditemclass);
                                    $dropdownitem->nodeValue = strtoupper($match[1]);
                                }
                            }
                        }
                    }
                }
            }
            $html = $dom->saveHTML();
        }

        return $html;
    }

    /**
     * Генерация основного меню
     *
     * @return string
     */
    public function custom_menu($custommenuitems = '')
    {
        $custommenu = $this->custom_custommenu($custommenuitems, 'custommenu', false, false, false);
        if (!empty($custommenu))
        {
            $manager = profilemanager::instance();
            $location = $manager->get_theme_setting('header_custommenu_location', $this->currentprofile) ?? 0;
            $custommenu = html_writer::div($custommenu, 'h_custommenu_wrapper moodle-has-zindex', [
                'id' => 'h_custommenu_wrapper',
                'data-location' => $location
            ]);
        }
        return $custommenu;
    }

    protected function get_mainmenu_branches($indent=0, $branches=null, $displaybranchheader = true)
    {
        global $CFG;

        $res = [];

        $course = get_site();
        require_once($CFG->dirroot.'/course/lib.php');
        $modinfo = get_fast_modinfo($course);
        if ( !empty($modinfo->sections[0]) )
        {
            $branchindent = null;
            $visiblebranch = false;
            $branchheader = false;
            foreach ($modinfo->sections[0] as $cmid)
            {
                $cm = $modinfo->cms[$cmid];

                $branchheader = false;
                if (is_null($branchindent) || $cm->indent <= $branchindent)
                {// new branch
                    $branchheader = true;
                    $branchindent = $cm->indent;
                    $visiblebranch = $cm->visible;
                    if (!is_null($branches) && !in_array($cm->id, $branches))
                    {
                        $visiblebranch = false;
                    }
                }

                if (!$cm->visible || !$visiblebranch || ($branchheader && !$displaybranchheader))
                {
                    continue;
                }

                $activityname = $cm->get_formatted_name([]);
                $actualindent = $cm->indent + $indent + ($displaybranchheader ? 0 : -1);
                if ( ! empty($cm->url) )
                {
                    $res[] = str_repeat('-', $actualindent) . $activityname . '|' . $cm->url->out(false) . '|' . $activityname;
                } else
                {
                    $res[] = str_repeat('-', $actualindent) . $activityname . '||' . $activityname;
                }
            }
//             $processed = implode(PHP_EOL, $res);
        }
        return $res;
    }

    /**
     * замена макроподстановок в кастом меню
     *
     * @param string $custommenu
     *
     * @return void
     */
    public function custom_menu_replace_macrosubstitutions(&$custommenu, $menuname='custommenu')
    {
        global $CFG, $OUTPUT, $PAGE;

        $profileobject = $this->get_current_profile_class_object();
        if ( $profileobject )
        {
            // запуск метод замены макроподстановок профиля темы
            $profileobject->custom_menu_replace_macrosubstitutions($custommenu, $menuname);
        }

        $result = [];
        $strings = explode(PHP_EOL, $custommenu);
        foreach ( $strings as $val )
        {
            $matches = [];
            // получение количества дефисов в начале
            $dashes = '';
            if ( preg_match('/^[.-]*/', $val, $matches, PREG_OFFSET_CAPTURE, 0) )
            {
                $dashes = $matches[0][0];
            }
            $processed = trim($val);
            // замена
            if ( preg_match('/%breadcrumbs%$/', $processed, $matches, PREG_OFFSET_CAPTURE, 0) )
            {
                $navitems = $this->page->navbar->get_items();
                $navmenuitems = [];
                foreach($navitems as $navitem)
                {
                    $link = '';
                    if (!is_null($navitem->action))
                    {
                        $link = '|' . $navitem->action->out(false);
                    }
                    $navmenuitems[] = $dashes . $navitem->text . $link;
                }
                $processed = implode(PHP_EOL, $navmenuitems);
            }
            else if ( preg_match('/%mainmenu%$/', $processed, $matches, PREG_OFFSET_CAPTURE, 0) )
            {
                $processed = implode(PHP_EOL, $this->get_mainmenu_branches(strlen($dashes)));
            }
            else if ( preg_match('/%mainmenu\[{1}(.[0-9, ]*)\]{1}%$/', $processed, $matches, PREG_OFFSET_CAPTURE, 0) )
            {
                $processed = implode(PHP_EOL, $this->get_mainmenu_branches(strlen($dashes), explode(',', $matches[1][0])));
            }
            else if ( preg_match('/%mainmenu_flat%$/', $processed, $matches, PREG_OFFSET_CAPTURE, 0) )
            {
                $processed = implode(PHP_EOL, $this->get_mainmenu_branches(strlen($dashes), null, false));
            }
            else if ( preg_match('/%mainmenu_flat\[{1}(.[0-9, ]*)\]{1}%$/', $processed, $matches, PREG_OFFSET_CAPTURE, 0) )
            {
                $processed = implode(PHP_EOL, $this->get_mainmenu_branches(strlen($dashes), explode(',', $matches[1][0]), false));
            }
            else if ( preg_match('/%home%$/', $processed, $matches, PREG_OFFSET_CAPTURE, 0) )
            {
                $url = new moodle_url('/', ['redirect' => 0]);
                $processed = $dashes
                . html_writer::img($OUTPUT->image_url('home', 'theme_opentechnology'), '', ['style' => 'width:20px;', 'class' => 'custommenu_home_button'])
                . html_writer::div(get_string('home_text_mobile', 'theme_opentechnology'), 'custommenu_home_button_mobile') . '|' . $url->out(false);
            }
            else if ( preg_match('/%course\[{1}(.[0-9]*)\]{1}%$/', $processed, $matches, PREG_OFFSET_CAPTURE, 0) )
            {
                try
                {
                    $coursename = get_course($matches[1][0])->fullname;
                    $url = new moodle_url('/course/view.php', ['id' => $matches[1][0]]);
                    $processed = $dashes . $coursename . '|' . $url->out(false) . '|' . $coursename;
                } catch ( Exception $e )
                {
                    // не удалось получить курс
                }
            }
            else if ( preg_match('/%category\[{1}(.[0-9]*)\]{1}%$/', $processed, $matches, PREG_OFFSET_CAPTURE, 0) )
            {
                try
                {
                    $categoryname = \core_course_category::get($matches[1][0])->name;
                    $url = new moodle_url('/course/index.php', ['categoryid' => $matches[1][0]]);
                    $processed = $dashes . $categoryname . '|' . $url->out(false) . '|' . $categoryname;
                } catch ( Exception $e )
                {
                    // не удалось получить категорию
                }
            }
            else if ( preg_match('/%category_with_other_cats%$/', $processed, $matches, PREG_OFFSET_CAPTURE, 0) )
            {
                $catres = [];
                $catcontext = $PAGE->context;
                $mainid = 0;
                if ($coursecontext = $PAGE->context->get_course_context(false) )
                {
                    $course = get_course($coursecontext->__get('instanceid'));
                    if ($course->category > 0)
                    {
                        $catcontext = context_coursecat::instance($course->category);
                    }
                }
                if ( ! empty($catcontext) && is_a($catcontext, 'context_coursecat') )
                {
                    $url = new moodle_url('/course/index.php', ['categoryid' => $catcontext->instanceid]);
                    $mainid = $catcontext->instanceid;
                    $catname = \core_course_category::get($catcontext->instanceid)->name;
                    $catres[] = $dashes . $catname . '|' . $url->out(false) . '|' . $catname;
                } else
                {
                    $string = get_string('general_categories', 'theme_opentechnology');
                    $catres[] = $dashes . $string . '||' . $string;
                }

                $cats = \core_course_category::get(0)->get_children();
                foreach ($cats as $cat)
                {
                    $this->get_categories_with_children($cat, $catres, $mainid, $dashes . '-');
                }

                $processed = implode(PHP_EOL, $catres);
            }
            else if ( preg_match('/%cur_cat_siblings%$/', $processed, $matches, PREG_OFFSET_CAPTURE, 0) )
            {
                $catres = [];
                $catcontext = $PAGE->context;
                $mainid = 0;
                $parentid = 0;
                if ($coursecontext = $PAGE->context->get_course_context(false) )
                {
                    $course = get_course($coursecontext->__get('instanceid'));
                    if ($course->category > 0)
                    {
                        $catcontext = context_coursecat::instance($course->category);
                    }
                }
                if ( ! empty($catcontext) && is_a($catcontext, 'context_coursecat') )
                {
                    $url = new moodle_url('/course/index.php', ['categoryid' => $catcontext->instanceid]);
                    $mainid = $catcontext->instanceid;
                    $curcat = \core_course_category::get($catcontext->instanceid);
                    $parentid = $curcat->parent;
                    $catres[] = $dashes . $curcat->name . '|' . $url->out(false) . '|' . $curcat->name;
                } else
                {
                    $string = get_string('general_categories', 'theme_opentechnology');
                    $catres[] = $dashes . $string . '||' . $string;
                }

                $cats = \core_course_category::get($parentid)->get_children();
                foreach ($cats as $cat)
                {
                    $this->get_categories_with_children($cat, $catres, $mainid, $dashes . '-');
                }

                $processed = implode(PHP_EOL, $catres);
            }

            $result[] = $processed;
        }

        $custommenu = implode(PHP_EOL, $result);
    }

    /**
     * Получение категорий с вложенными подкатегориями
     *
     * @param \core_course_category $cat
     * @param array $res
     * @param string $dashes
     */
    protected function get_categories_with_children(\core_course_category $cat, &$res, $mainid = 0, $dashes = '')
    {
        if ( (!$cat->has_children() && $cat->id == $mainid) || !$cat->visible )
        {
            return;
        }
        $catname = $cat->name;
        $url = new moodle_url('/course/index.php', ['categoryid' => $cat->id]);
        $res[] = $dashes . $catname . '|' . $url->out(false) . '|' . $catname;
        foreach ($cat->get_children() as $child)
        {
            $this->get_categories_with_children($child, $res, $mainid, $dashes . '-');
        }
    }


    /**
     * Сформировать CSS-классы для body
     *
     * @param array $additionalclasses - Массив дополнительных классов
     *
     * @return string
     */
    public function body_css_classes(array $additionalclasses = [])
    {
        // Получение настройки
        $layout = $this->page->pagelayout;

        // Получение менеджера профилей
        $manager = profilemanager::instance();


        $navbaritems = $this->get_navbar_items();
        if (empty($navbaritems))
        {
            $additionalclasses[] = 'empty-navbar';
        }

        $custommenu = $this->custom_custommenu();
        if (empty($custommenu))
        {
            $additionalclasses[] = 'empty-custommenu';
        }

        return parent::body_css_classes($additionalclasses);
    }

    /**
     * Get the HTML for blocks in the given region.
     *
     * @since Moodle 2.5.1 2.6
     * @param string $region The region to get HTML for.
     * @return string HTML.
     */
    public function dock() {
        global $PAGE;

        $dockeditems = [];

        $manager = profilemanager::instance();
        //Режим отображения блоков в док-панели (текст, иконки, их сочетания)
        $dockeditemtitle = $manager->get_theme_setting('main_dockeditem_title', $this->currentprofile) ?? 0;
        $dockeditemtitle = (int)$dockeditemtitle;
        //Использовать ли иконку блока по умолчанию, если на найдена иконка для блока
        $usedefaultblockicon = $manager->get_theme_setting('main_dockeditem_title_default', $this->currentprofile) ?? 0;
        $usedefaultblockicon = (int)$usedefaultblockicon;
        
        // Получение менеджера профилей
        $region = $this->page->apply_theme_region_manipulations('dock');

        $itemnum = 0;
        // добавление дока
        foreach($this->page->blocks->get_regions() as $region) {
            foreach($this->page->blocks->get_content_for_region($region, $this) as $bc)
            {
                if ($bc instanceof block_contents) {
                    if ($region == 'dock')
                    {
                        $dockedtitleclasses = ['dockedtitle'];

                        $dockeditem = [
                            'html' => $this->block($bc, $region),
                            'title' => $bc->title,
                            'blockinstanceid' => $bc->blockinstanceid,
                            'itemnum' => $itemnum++,
                        ];

                        try {
                            $dockeditem['icon'] = $this->get_block_icon($bc);
                        } catch(\Exception $ex)
                        {
                            //Если указано отображать без иконки когда для блока её нет
                            if ($usedefaultblockicon == 0) {
                                $dockedtitleclasses[] = 'noicon';
                            }
                        }

                        if ($dockeditemtitle == 1 || $dockeditemtitle == 3)
                        {
                            $dockedtitleclasses[] = 'iconview';
                            if ($dockeditemtitle == 1)
                            {
                                $dockedtitleclasses[] = 'texthide';
                            }
                        }
                        $dockeditem['dockedtitleclasses'] = implode(' ', $dockedtitleclasses);

                        $dockeditems[] = $dockeditem;
                    }
                }
            }
        }
        
        //Использовать иконку по умолчанию, если она есть
        $defaultblockicon = ($usedefaultblockicon == 1) ? $this->get_default_block_icon() : null;

        $this->page->requires->js_call_amd('theme_opentechnology/dock', 'init', [
            $PAGE->course->id,
            $PAGE->pagetype,
            $PAGE->pagelayout,
            $PAGE->subpage,
            $PAGE->context->id,
            $this->get_default_block_icon()
        ]);


        return [
            'setting_dockeditemtitle' => $dockeditemtitle,
            'dockeditems' => $dockeditems,
            'hasdockeditems' => !empty($dockeditems),
            'blockshtml' => $this->custom_block_region('dock'),
            'defaulticon' => $defaultblockicon
        ];
    }

    public function get_default_block_icon() {

        global $PAGE;

        // Получение менеджера профилей
        $manager = profilemanager::instance();
        $dockiconset = $manager->get_theme_setting('main_dockeditem_title_iconset', $this->currentprofile);

        $pattern = $PAGE->theme->dir . '/pix/dock_icon_'.$dockiconset . '.{gif,png,jpg,jpeg,svg}';
        if (!empty(glob($pattern, GLOB_BRACE))) {
            return $this->image_url('dock_icon_'.$dockiconset, 'theme_opentechnology')->out(true);
        }

        // стандартная иконка для набора иконок не создана - отобразим обычную стандартную иконку
        $pattern = $PAGE->theme->dir . '/pix/dock_icon'. '.{gif,png,jpg,jpeg,svg}';
        if (!empty(glob($pattern, GLOB_BRACE))) {
            return $this->image_url('dock_icon', 'theme_opentechnology')->out(true);
        }

        throw new Exception('Default block icon was not found');
    }

    public function get_block_icon($bc) {

        global $PAGE;

        $bc = clone($bc);

        if (!empty($bc->attributes['data-block']) && $bc->attributes['data-block'] !== "_fake")
        {
            // Получение менеджера профилей
            $manager = profilemanager::instance();
            $blockname = $bc->attributes['data-block'];
            $dockiconset = $manager->get_theme_setting('main_dockeditem_title_iconset', $this->currentprofile);

            $pattern = $PAGE->theme->dir . '/pix_plugins/block/' . $blockname . '/dock_icon_'.$dockiconset . '.{gif,png,jpg,jpeg,svg}';
            if (!empty(glob($pattern, GLOB_BRACE)))
            {// иконка не найдена
                return $this->image_url('dock_icon_'.$dockiconset, 'block_'.$blockname)->out(true);
            }

            $dockeditemtitledefault = $manager->get_theme_setting('main_dockeditem_title_default', $this->currentprofile);
            if (!empty($dockeditemtitledefault))
            {// в случае отсутствия иконки настроено отобразить стандартную иконку
                return $this->get_default_block_icon();
            }
        }

        throw new Exception('Block icon was not found');
    }

    /**
     * Отображение заголовка блока
     *
     * @param block_contents $bc - Содержимое блока
     *
     * @return string - Сгенерированный HTML-код
     */
    protected function block_header(block_contents $bc)
    {
        // Формирование заголовка блока
        $title = '';
        if ( $bc->title )
        {// Заголовок указан
            $attributes = [];
            if ( $bc->blockinstanceid )
            {
                $attributes['id'] = 'instance-'.$bc->blockinstanceid.'-header';
            }
            if ( isset($bc->attributes['data-fixdock']) && $bc->attributes['data-fixdock'] === 1 )
            {// Требуется фиксация в док-панели
                $attributes['class'] = 'fixed_dock';
            }
            $title = html_writer::tag('h2', $bc->title, $attributes);
        }

        $blockid = null;
        if ( isset($bc->attributes['id']) )
        {
            $blockid = $bc->attributes['id'];
        }

        $controlshtml = $this->block_controls($bc->controls, $blockid);

        $output = '';
        if ( $title || $controlshtml )
        {
            $output .= html_writer::tag(
                'div',
                html_writer::tag(
                    'div',
                    html_writer::tag(
                        'div',
                        '',
                        ['class'=>'block_action moodle-has-zindex']). $title . $controlshtml,
                    ['class' => 'title']),
                ['class' => 'header']);
        }
        return $output;
    }

    public function lang_menu()
    {
        // Получение базового языкового меню
        $langmenu = parent::lang_menu();
        if ( ! empty($langmenu) )
        {// Языковое меню получено
            // Генерация переключателя для отображения меню
            $randomid = uniqid();
            $button = html_writer::label('', 'langmenu'.$randomid);
            $checkbox = html_writer::checkbox(
                'langmenu'.$randomid,
                '',
                false,
                '',
                ['id' => 'langmenu'.$randomid, 'class' => 'langmenu_controller']
            );
            // Генерация результирующего языкового меню
            $langmenu = html_writer::div($checkbox.$langmenu.$button, 'langmenu_wrapper');
        }

        return $langmenu;
    }

    public function get_navbar_items()
    {
        if (!local_opentechnology_is_layout_supports_navigation($this->page)) {
            return [];
        }
        $items = $this->page->navbar->get_items();

        $profileobject = $this->get_current_profile_class_object();
        if ( $profileobject && method_exists($profileobject, 'get_navbar_items') )
        {
            // запуск метод замены макроподстановок профиля темы
            $profileobject->get_navbar_items($items);
        }

        return $items;
    }

    /*
     * This renders the navbar.
     * Uses bootstrap compatible html.
     */
    public function navbar()
    {
        $items = $this->get_navbar_items();

        if (empty($items))
        {
            return '';
        }

        $breadcrumbs = '';
        $itemskeys = array_keys($items);
        $lastitemkey = array_pop($itemskeys);
        $divider = html_writer::span(get_separator(), 'divider');

        foreach ($items as $key=>$item)
        {
            $item->hideicon = true;

            $attributes = [
                'data-node-type' => $item->type,
                'class' => implode(' ', $item->classes)
            ];

            $breadcrumbs .= html_writer::tag(
                'li',
                $this->render($item) . ( $lastitemkey == $key ? "" : $divider),
                $attributes
            );
        }

        $title = html_writer::span(get_string('pagepath'), 'accesshide', ['id' => 'navbar-label']);
        $breadcrumbs = html_writer::tag('ul', $breadcrumbs, ['class' => 'breadcrumb']);

        return $title . html_writer::tag('nav', $breadcrumbs, ['aria-labelledby' => 'navbar-label']);
    }

    /**
     * {@inheritDoc}
     * @see core_renderer::user_menu()
     */
    public function user_menu($user = null, $withlinks = null)
    {
        global $USER, $CFG;
        require_once($CFG->dirroot . '/user/lib.php');

        $profileobject = $this->get_current_profile_class_object();
        if ($profileobject && method_exists($profileobject, 'user_menu'))
        {
            // Дополнительная обработка сформированного кода меню
            return $profileobject->user_menu($this, $this->page, $this->is_login_page(), $user, $withlinks);
        }

        if (is_null($user)) {
            $user = $USER;
        }

        // Note: this behaviour is intended to match that of core_renderer::login_info,
        // but should not be considered to be good practice; layout options are
        // intended to be theme-specific. Please don't copy this snippet anywhere else.
        if (is_null($withlinks)) {
            $withlinks = empty($this->page->layout_options['nologinlinks']);
        }

        // Add a class for when $withlinks is false.
        $usermenuclasses = 'usermenu moodle-has-zindex';
        if (!$withlinks) {
            $usermenuclasses .= ' withoutlinks';
        }

        $returnstr = "";

        // If during initial install, return the empty return string.
        if (during_initial_install()) {
            return $returnstr;
        }

        $loginpage = $this->is_login_page();
        $loginurl = get_login_url();
        // If not logged in, show the typical not-logged-in string.
        if (!isloggedin()) {
            $returnstr = '<span class="loggedinnot">' . get_string('loggedinnot', 'moodle') . '</span>';
            if (!$loginpage) {
                $returnstr .= " <a href=\"$loginurl\" class=\"btn btn-primary\">" . get_string('login') . '</a>';
            }
            return html_writer::div(
                html_writer::span(
                    $returnstr,
                    'login'
                ),
                $usermenuclasses
            );

        }

        // If logged in as a guest user, show a string to that effect.
        if (isguestuser()) {
            $returnstr = '<span class="loggedinasguest">' . get_string('loggedinasguest', 'theme_opentechnology') . '</span>';
            if (!$loginpage && $withlinks) {
                $returnstr .= " <a href=\"$loginurl\" class=\"btn btn-primary\">".get_string('login').'</a>';
            }

            return html_writer::div(
                html_writer::span(
                    $returnstr,
                    'login'
                ),
                $usermenuclasses
            );
        }

        // Get some navigation opts.
        $opts = user_get_user_navigation_info($user, $this->page);

        $avatarclasses = "avatars";
        $avatarcontents = html_writer::span($opts->metadata['useravatar'], 'avatar current');
        $usertextcontents = $opts->metadata['userfullname'];

        // Other user.
        if (!empty($opts->metadata['asotheruser'])) {
            $avatarcontents .= html_writer::span(
                $opts->metadata['realuseravatar'],
                'avatar realuser'
                );
            $usertextcontents = $opts->metadata['realuserfullname'];
            $usertextcontents .= html_writer::tag(
                'span',
                get_string(
                    'loggedinas',
                    'moodle',
                    html_writer::span(
                        $opts->metadata['userfullname'],
                        'value'
                        )
                    ),
                array('class' => 'meta viewingas')
                );
        }

        // Role.
        if (!empty($opts->metadata['asotherrole'])) {
            $role = core_text::strtolower(preg_replace('#[ ]+#', '-', trim($opts->metadata['rolename'])));
            $usertextcontents .= html_writer::span(
                $opts->metadata['rolename'],
                'meta role role-' . $role
            );
        }

        // User login failures.
        if (!empty($opts->metadata['userloginfail'])) {
            $usertextcontents .= html_writer::span(
                $opts->metadata['userloginfail'],
                'meta loginfailures'
            );
        }

        // MNet.
        if (!empty($opts->metadata['asmnetuser'])) {
            $mnet = strtolower(preg_replace('#[ ]+#', '-', trim($opts->metadata['mnetidprovidername'])));
            $usertextcontents .= html_writer::span(
                $opts->metadata['mnetidprovidername'],
                'meta mnet mnet-' . $mnet
            );
        }

        $returnstr .= html_writer::span(
            html_writer::span($usertextcontents, 'usertext') .
            html_writer::span($avatarcontents, $avatarclasses),
            'userbutton'
        );

        // Create a divider (well, a filler).
        $divider = new action_menu_filler();
        $divider->primary = false;

        $am = new action_menu();
        $am->set_menu_trigger($returnstr, 'toggle-display');
        $aml = new action_menu_link(
            new moodle_url('/my'),
            null,
            $usertextcontents,
            true,
            ['class' => 'userfullname']
        );
        $am->add_primary_action($aml);
        $am->set_alignment(action_menu::TR, action_menu::BR);
        $am->set_nowrap_on_items();
        if ($withlinks) {
            $navitemcount = count($opts->navitems);
            $idx = 0;
            foreach ($opts->navitems as $key => $value) {

                switch ($value->itemtype) {
                    case 'divider':
                        // If the nav item is a divider, add one and skip link processing.
                        $am->add($divider);
                        break;

                    case 'invalid':
                        // Silently skip invalid entries (should we post a notification?).
                        break;

                    case 'link':
                        // Process this as a link item.
                        $pix = null;
                        if (isset($value->pix) && !empty($value->pix)) {
                            $pix = new pix_icon($value->pix, $value->title, null, array('class' => 'iconsmall'));
                        } else if (isset($value->imgsrc) && !empty($value->imgsrc)) {
                            $value->title = html_writer::img(
                                $value->imgsrc,
                                $value->title,
                                array('class' => 'iconsmall')
                            ) . $value->title;
                        }

                        $al = new action_menu_link_secondary(
                            $value->url,
                            $pix,
                            $value->title,
                            array('class' => 'icon')
                        );
                        if (!empty($value->titleidentifier)) {
                            $al->attributes['data-title'] = $value->titleidentifier;
                        }
                        $am->add($al);
                        break;
                }

                $idx++;

                // Add dividers after the first item and before the last item.
                if ($idx == 1 || $idx == $navitemcount - 1) {
                    $am->add($divider);
                }
            }
        }

        return html_writer::div(
            $this->render($am),
            $usermenuclasses
        );
    }

    /**
     * Получение класс текущего профиля
     *
     * @return theme_opentechnology_profile|false
     */
    public function get_current_profile_class_object()
    {
        global $CFG;

        // Получение темы
        $theme = theme_config::load('opentechnology');
        // Код текущего профиля
        $profilecode = $this->currentprofile->get_code();
        // Путь до класса с альтернативными функциями
        $classpath = $CFG->dirroot . '/theme/' . $theme->name . '/profiles/overrides/'.$profilecode.'/'.$profilecode.'.php';
        if( file_exists($classpath))
        {
            require_once($classpath);
            $classname = 'theme_opentechnology_profile_'.$profilecode;
            if( class_exists($classname) )
            {
                return new $classname();
            }
        }

        return false;
    }

    /**
     * Получение пути до формы авторизации
     *
     * @return string
     */
    public function get_login_form_path()
    {
        global $CFG;

        // Получение темы
        $theme = theme_config::load('opentechnology');
        // Код текущего профиля
        $profilecode = $this->currentprofile->get_code();
        // Путь до формы авторизации для профиля
        $loginformpath = $CFG->dirroot . '/theme/' . $theme->name . '/profiles/overrides/'.$profilecode.'/login_form.php';
        if( file_exists($loginformpath))
        {
            // форма авторизации для темы определена - вернем путь до нее
            return $loginformpath;
        } else
        {
            // форма авторизации в теме не определена
            return null;
        }
    }

    /**
     * Подключение дополнительного js обработчика для кастомной страницы авторизации профиля
     * @return NULL
     */
    public function js_login_form_call()
    {
        global $CFG, $PAGE;

        // Получение темы
        $theme = theme_config::load('opentechnology');
        // Код текущего профиля
        $profilecode = $this->currentprofile->get_code();
        // Путь до js обработчика для кастомной страницы авторизации профиля
        $jsloginformpath = $CFG->dirroot . '/theme/' . $theme->name . '/amd/build/profile_' . $profilecode . '__login_form.min.js';
        if( file_exists($jsloginformpath) )
        {
            $PAGE->requires->js_call_amd('theme_opentechnology/profile_' . $profilecode . '__login_form', 'init');
        } else
        {
            // нет дополнительного js обработчика для кастомной страницы авторизации профиля
            return null;
        }
    }

    /**
     * Returns a search box.
     *
     * @param  string $id     The search box wrapper div id, defaults to an autogenerated one.
     * @return string         HTML with the search form hidden by default.
     */
    public function search_box($id = false) {
        global $CFG;

        // Смотрим, есть ли текущем профиле переопределение метода
        $profileobject = $this->get_current_profile_class_object();
        if ($profileobject && method_exists($profileobject, 'search_box'))
        {
            // Если есть - используем переопределение
            return $profileobject->search_box($id);
        }

        // Accessing $CFG directly as using \core_search::is_global_search_enabled would
        // result in an extra included file for each site, even the ones where global search
        // is disabled.
        if (empty($CFG->enableglobalsearch) || !has_capability('moodle/search:query', context_system::instance())) {
            return '';
        }

        if ($id == false) {
            $id = uniqid();
        } else {
            // Needs to be cleaned, we use it for the input id.
            $id = clean_param($id, PARAM_ALPHANUMEXT);
        }

        // JS to animate the form.
        $this->page->requires->js_call_amd('theme_opentechnology/search-input', 'init', array($id));

        $searchicon = html_writer::tag('div', $this->pix_icon('a/search', get_string('search', 'search'), 'moodle'),
            array('role' => 'button', 'tabindex' => 0, 'class' => 'btn btn-primary'));
        $formattrs = array('class' => 'search-input-form', 'action' => $CFG->wwwroot . '/search/index.php');
        $inputattrs = array('type' => 'text', 'name' => 'q', 'placeholder' => get_string('search', 'search'),
            'size' => 13, 'tabindex' => -1, 'id' => 'id_q_' . $id, 'class' => 'form-control moodle-has-zindex');

        $contents = html_writer::tag('label', get_string('enteryoursearchquery', 'search'),
            array('for' => 'id_q_' . $id, 'class' => 'accesshide')) . html_writer::tag('input', '', $inputattrs);
            if ($this->page->context && $this->page->context->contextlevel !== CONTEXT_SYSTEM) {
                $contents .= html_writer::empty_tag('input', ['type' => 'hidden',
                    'name' => 'context', 'value' => $this->page->context->id]);
            }
            $searchinput = html_writer::tag('form', $contents, $formattrs);

            return html_writer::tag('div', $searchicon . $searchinput, array('class' => 'search-input-wrapper', 'id' => $id));
    }

    /**
     *
     * @param \auth_dof\output\identityproviders $form
     * @return string|boolean
     */
    public function render_identityproviders(\auth_dof\output\identityproviders $form) {
        $context = $form->export_for_template($this);

        return $this->render_from_template('auth_dof/identityproviders', $context);
    }

    /**
     * Allow plugins to provide some content to be rendered in the navbar.
     * The plugin must define a PLUGIN_render_navbar_output function that returns
     * the HTML they wish to add to the navbar.
     *
     * @return string HTML for the navbar
     */
    public function navbar_plugin_output() {
        global $CFG, $PAGE;
        // части кода для последующего отображения
        $outputparts = [];
        // плагины, отображение которых ожидается и не навредит теме
        $expectedplugins = ['theme_opentechnology'];

        // Получение менеджера профилей
        $manager = \theme_opentechnology\profilemanager::instance();
        // Получение кода профиля текущей страницы
        $profile = $manager->get_current_profile();
        // настройка отображения сообщений, уведомлений
        $setting = $manager->get_theme_setting('header_link_unread_messages', $profile);
        if (!empty($setting) && isloggedin() && !isguestuser())
        {
            $expectedplugins[] = 'message_popup';
        }

        if ($pluginsfunction = get_plugins_with_function('render_navbar_output')) {
            foreach ($pluginsfunction as $plugintype => $plugins) {
                foreach ($plugins as $plugin => $pluginfunction) {
                    if (in_array($plugintype.'_'.$plugin, $expectedplugins))
                    {
                        $outputparts[$plugintype.'_'.$plugin] = $pluginfunction($this);
                        if( file_exists($CFG->dirroot . '/theme/opentechnology/amd/build/' . $pluginfunction . '.min.js') )
                        {
                            $PAGE->requires->js_call_amd('theme_opentechnology/' . $pluginfunction, 'init', []);
                        }
                    }
                }
            }
        }

        $navbarhtml = implode('', $outputparts);
        return empty($navbarhtml) ? '' : html_writer::div($navbarhtml, 'navbar-nav');
    }

    public function render_from_template($templatefullname, $context)
    {
        // Получение менеджера профилей
        $manager = \theme_opentechnology\profilemanager::instance();
        // Получение кода профиля текущей страницы
        $profile = $manager->get_current_profile();

        list($pluginname, $templatename) = explode('/', $templatefullname);

        try {
            $overridentemplatename = 'profile_'.$profile->get_code().'__'.$pluginname.'__'.$templatename;
            $mustache = $this->get_mustache();
            $mustache->loadTemplate($overridentemplatename);
            return parent::render_from_template('theme_opentechnology/'.$overridentemplatename, $context);
        } catch(moodle_exception $ex)
        {
            return parent::render_from_template($templatefullname, $context);
        }
    }

    public function image_url($imagename, $component = 'moodle')
    {
        global $CFG;

        if (empty($component) or $component === 'moodle' or $component === 'core') {
            $component = 'core';
        }

        // Получение темы
        $theme = theme_config::load('opentechnology');
        // Код текущего профиля
        $profilecode = $this->currentprofile->get_code();
        // Путь до класса с альтернативными функциями
        $imgpath = $CFG->dirroot . '/theme/' . $theme->name . '/pix/profiles/'.$profilecode.'/'.$component.'/'.$imagename;


        if (file_exists($imgpath.'.svg') || file_exists($imgpath.'.png'))
        {
            return parent::image_url('profiles/'.$profilecode.'/'.$component.'/'.$imagename, 'theme');
        } else
        {
            return parent::image_url($imagename, $component);
        }
    }
}