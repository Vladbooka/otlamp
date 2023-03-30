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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Блок Информация
 *
 * @package    block_myinfo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core_competency\api;
require_once($CFG->dirroot . '/blocks/myinfo/locallib.php');
require_once($CFG->dirroot . '/user/lib.php');

class block_myinfo extends block_base
{
    
    /**
     * Инициализация блока
     */
    public function init()
    {
        $this->title = get_string('pluginname', 'block_myinfo');
    }

    /**
     * Вернуть контент блока
     *
     * @return stdClass contents of block
     */
    public function get_content() {
        
        global $USER, $CFG, $DB, $OUTPUT, $PAGE;
        
        require_once($CFG->dirroot.'/user/profile/lib.php');


        if( $PAGE->url->get_path() == '/user/profile.php' )
        {
            $userid = optional_param('id', 0, PARAM_INT);
        }
        if( empty($userid) )
        {
            if ( empty($USER->id) )
            {// Пользователь не определен
                return '';
            }
            $userid = $USER->id;
        }
        
        $users = user_get_users_by_id([$userid]);
        if( empty($users) )
        {// Пользователь не определен
            return '';
        }
        $user = reset($users);
        
        if ( ! is_null($this->content) )
        {
            return $this->content;
        }
        
        // Объявляем контент блока
        $this->content = new stdClass();
        $this->content->text = '';
        $this->content->footer = '';
        
        
        
        // Счетчики
        $counters = [];
        
        // Непрочитанные сообщения
        if ( ! empty( $this->instance )
            && isloggedin()
            && ! isguestuser()
            && ! empty( $CFG->messaging )
            && ($userid == $USER->id || ( has_capability('block/myinfo:view_others_counter_unread', $this->context))) )
        {
            $countunread = message_count_unread_messages($user);

            // Ссылка на систему сообщений
            $counterlabel = html_writer::link(
                '/message/index.php',
                get_string('unread_messages', 'block_myinfo'),
                [ 'class' => 'block_myinfo_counters_counter_label']
            );
            
            $countervalue = html_writer::div(
                '',
                'block_myinfo_counters_counter_value',
                [
                    'data-value' => (int)$countunread
                ]
            );
            
            $counters[] = html_writer::div(
                $countervalue .
                html_writer::div($counterlabel, 'block_myinfo_counters_counter_label_wrapper'),
                'block_myinfo_counters_counter block_myinfo_counters_unread'
            );
        }
        
        // Неоцененные работы
        if ( ! empty( $this->instance )
            && isloggedin()
            && ! isguestuser()
            && file_exists($CFG->dirroot.'/blocks/notgraded/lib.php') )
        {
            // Подключение API
            require_once($CFG->dirroot.'/blocks/notgraded/lib.php');
        
            // проверим, является ли пользователь учителем хотя бы где-то, 
            // чтобы понять, стоит ли ему отображать счетчик
            if (user_has_grade_capability_anywhere($userid))
            {
            
                // Экземпляр класса кэшируемых данных по непроверенным работам
                $gradercache = new block_notgraded_gradercache($userid);
                
                // Значение кэша по умолчанию не определено
                $countnotgraded = '...';
                $counterlabel = html_writer::span(
                    get_string('not_graded', 'block_myinfo'),
                    'block_myinfo_counters_counter_label'
                );
                $cachednotgraded = 0;
                $cacheremainedtime = 0;
                $cacherecord = $gradercache->get_cache(false);
                if ( ! empty($cacherecord) )
                {// Значение кэша определено
                    $countnotgraded = (int)$cacherecord->countnotgraded;
                    $cachelifetime = 0;
                    $cachelifetimesetting = get_config('block_notgraded','cache_lifetime');
                    if ( ! empty($cachelifetimesetting) )
                    {
                        $cachelifetime = (int)$cachelifetimesetting;
                    }
                    $cacheremainedtime = ( (int)$cacherecord->lastupdate + $cachelifetime ) - time();
                    if( $cacheremainedtime < 0 )
                    {
                        $cacheremainedtime = 0;
                    }
                    if( (int)$cacherecord->countnotgraded > 0 )
                    {
                        $notgradedurl = new moodle_url('/blocks/notgraded/notgraded_courses.php', [
                            'userid' => $userid
                        ]);
                        $counterlabel = html_writer::link(
                            $notgradedurl,
                            get_string('not_graded', 'block_myinfo'),
                            [ 'class' => 'block_myinfo_counters_counter_label']
                        );
                    }
                } else 
                {
                    $cachelifetime = 60;
                }
                
                $hidden = '';
                if( (int)$countnotgraded == 0 )
                {
                    $hidden = ' block_myinfo_counters_counter_hidden';
                }
                
                // Подсчет количества непроверенных работ для пользователя
                $countervalue = html_writer::div(
                    '',
                    'block_myinfo_counters_counter_value', //block_myinfo_counters_counter_value_loading
                    [
                        'data-userid' => $userid,
                        'data-cache-remained-time' => $cacheremainedtime+1,
                        'data-cache-lifetime' => ($cachelifetime == 0 ? 60 : $cachelifetime),
                        'data-value' => $countnotgraded
                    ]
                );
                
                // Подсчитать непроверенные задания после загрузки страницы по ajax-запросу
                $PAGE->requires->js(new moodle_url('/blocks/myinfo/script.js'));
                
                $counters[] = html_writer::div(
                    $countervalue .
                    html_writer::div($counterlabel, 'block_myinfo_counters_counter_label_wrapper'),
                    'block_myinfo_counters_counter block_myinfo_counters_notgraded'.$hidden
                );
            }
        }

        // Учебные планы
        if ( ! empty( $this->instance )
            && api::is_enabled()
            && isloggedin()
            && ! isguestuser()
            && ($userid == $USER->id || ( has_capability('block/myinfo:view_others_counter_templates', $this->context)))
        )
        {
            $plans = api::list_user_plans($userid);
            foreach($plans as $plan)
            {
                if ( ! $plan->is_draft() )
                {
                    $page = new \tool_lp\output\plan_page($plan);
                    $planotherdata = $page->export_for_template($PAGE->get_renderer('tool_lp'));
                    
                    $plandata = $plan->to_record();
                    
                    $counterlabel = html_writer::span(
                        $plandata->name,
                        'block_myinfo_counters_counter_label'
                    );
                    
                    
                    $planlink = html_writer::link(
                        new moodle_url('/admin/tool/lp/plan.php', [
                            'id' => $plandata->id
                        ]),
                        get_string('eduplan','block_myinfo')
                    );
                    
                    // блок отображения дополнительной информации по плану (сейчас одна ссылка, позднее будет еще ссылка на проектируемый интерфейс)
                    $counteradditional = html_writer::div($planlink, 'block_myinfo_counters_counter_additional');
                    
                    // Подсчет количества непроверенных работ для пользователя
                    $countervalue = html_writer::div(
                        '',
                        'block_myinfo_counters_counter_value',
                        [
                            'data-value' => (int)$planotherdata->proficientcompetencypercentageformatted
                        ]
                    );

                    $counters[] = html_writer::div(
                        $countervalue .
                        html_writer::div(
                            $counterlabel . $counteradditional,
                            'block_myinfo_counters_counter_label_wrapper'
                        ),
                        'block_myinfo_counters_counter block_myinfo_counters_plan'
                    );
                }
            }
        }
        
        
        // Поля профиля пользователя
        $profilefields = $actions = [];
        
        // Настроенные для отображения поля профиля пользователя (и стандартные, и настраиваемые)
        $displayfieldsconfig = get_config('block_myinfo', 'displayfields');
        if( ! empty($displayfieldsconfig) )
        {
            $displayfields = explode(',', $displayfieldsconfig);
            if( is_array($displayfields) )
            {
                $fields = get_fields_data($user, $displayfields);
                
                foreach($fields as $fieldcode=>$field)
                {
                    if( isset($field->displayvalue) )
                    {
                        $wrappedfieldval = html_writer::div(
                            $field->displayvalue,
                            'block_myinfo_profilefields_profilefield_value'
                        );
                        
                        $a = new stdClass();
                        $a->name = $field->name;
                        $a->value = $wrappedfieldval;
                        
                        $profilefields[] = html_writer::div(
                            get_string('displayfield', 'block_myinfo', $a),
                            'block_myinfo_profilefields_profilefield block_myinfo_profilefields_'.$fieldcode
                        );
                    }
                }
            }
        }
        

        if (isloggedin() && !isguestuser($user) && !is_mnet_remote_user($user))
        {
            $iscurrentuser = ($user->id == $USER->id);
            $systemcontext = context_system::instance();
            $usercontext = context_user::instance($user->id, MUST_EXIST);
            
            // Кнопка редактирования профиля
            $editprofileurl = '';
            if (($iscurrentuser || is_siteadmin($USER) || !is_siteadmin($user)) && has_capability('moodle/user:update',
                $systemcontext))
            {
                $editprofileurl = new moodle_url('/user/editadvanced.php', [
                    'id' => $user->id,
                    'course' => SITEID,
                    'returnto' => 'profile'
                ]);
            } else if ((has_capability('moodle/user:editprofile', $usercontext) && !is_siteadmin($user))
                || ($iscurrentuser && has_capability('moodle/user:editownprofile', $systemcontext)))
            {
                $userauthplugin = false;
                if (!empty($user->auth))
                {
                    $userauthplugin = get_auth_plugin($user->auth);
                }
                if ($userauthplugin && $userauthplugin->can_edit_profile())
                {
                    $editprofileurl = $userauthplugin->edit_profile_url();
                    if (empty($url))
                    {
                        $editprofileurl = new moodle_url('/user/edit.php', [
                            'id' => $user->id,
                            'returnto' => 'profile'
                        ]);
                    }
                }
            }
            if( ! empty($editprofileurl) )
            {
                $actions[] = html_writer::link(
                    $editprofileurl,
                    get_string('edit_profile', 'block_myinfo'),
                    ['class' => 'btn btn-secondary mb-1 mr-1', 'role' => 'button']
                );
            }
            
            
            if ($PAGE->theme->name == 'opentechnology' && (
                ($iscurrentuser && has_capability('theme/opentechnology:profile_link_self', $systemcontext)) ||
                has_capability('theme/opentechnology:profile_links_manage', $systemcontext)
                ))
            {// пользователь имеет право менять себе профиль в нашей теме оформления
                
                $linkprofileurl = new moodle_url('/theme/opentechnology/profiles/link_to_user.php', [
                    'userid' => $user->id,
                    'returnto' => $PAGE->url->out(true)
                ]);
                $actions[] = html_writer::link(
                    $linkprofileurl,
                    get_string('link_profile', 'block_myinfo'),
                    ['class' => 'btn btn-secondary mb-1 mr-1', 'role' => 'button']
                );
            }
        }
        
        if (!empty($actions)) {
            $profilefields[] = html_writer::div(implode(' ', $actions), 'd-flex flex-wrap justify-content-lg-start justify-content-center');
        }
        
        $userpicturewrapper = '';
        $counterswrapper = '';
        $profilefieldswrapper = '';
        
        $userpicture = html_writer::div(
            $OUTPUT->user_picture($user, [
                'size' => 512,
                'class' => 'block_myinfo_user_picture_img'
            ]),
            'block_myinfo_user_picture'
        );
        $userpicturewrapper = html_writer::div(
            $userpicture,
            'block_myinfo_user_picture_wrapper'
        );
        
        $userfullnamewrapper = html_writer::div(
            fullname($user),
            'block_myinfo_user_fullname_wrapper'
        );

        if( ! empty($counters) )
        {
            $counterswrapper = html_writer::div(
                implode('',$counters),
                'block_myinfo_counters_wrapper'
            );
        }
        if( ! empty($profilefields) )
        {
            $profilefieldswrapper = html_writer::div(
                implode(' ',$profilefields),
                'block_myinfo_profilefields_wrapper'
            );
        }
        
        $this->content->text = html_writer::div(
            $userpicturewrapper . $userfullnamewrapper . $counterswrapper . $profilefieldswrapper,
            'block_myinfo_wrapper clearfix'
        );
        
                
        return $this->content;
    }

    /**
     * Поддержка блоком страницы конфигурации
     *
     * @return boolean
     */
    public function has_config()
    {
        return true;
    }

    /**
     * Отображение блока на страницах
     *
     * @return array
     */
    public function applicable_formats()
    {
        return [
            'all' => true
        ];
    }

    /**
     * Отображение заголовка блока
     *
     * @return bool if true then header will be visible.
     */
    public function hide_header()
    {
        $display = get_config('block_myinfo', 'display_header');
        return empty($display);
    }
}
