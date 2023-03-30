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
 * Блок мессенджера курса. Класс блока.
 *
 * @package    block_coursemessage
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_coursemessage extends block_base 
{
    /**
     * Инициализация блока
     */
    public function init() 
    {
        $this->title = get_string('pluginname', 'block_coursemessage');
    }

    /**
     * Вернуть контент блока
     *
     * @return stdClass contents of block
     */
    public function get_content() 
    {
        global $CFG, $PAGE, $COURSE;
        
        require_once($CFG->dirroot.'/user/profile/lib.php');
        require_once($CFG->dirroot. '/blocks/coursemessage/forms.php');
        
        if ($this->content !== null) {
            return $this->content;
        }
        // Выбор метода определения получателей сообщения
        if (!isset($this->config->recipientselectionmode) || $this->config->recipientselectionmode == 'useglobal') {
            $recipientselectionmode = get_config('block_coursemessage', 'recipientselectionmode');
        } else {
            $recipientselectionmode = $this->config->recipientselectionmode;
        }
        $recipientselectionmode = !empty($recipientselectionmode) ? $recipientselectionmode : 'sendtoall';
        // Объявляем контент блока
        $this->content = new stdClass();
        $this->content->footer = '';
        // Получение контакта курсов
        // @todo заменить использование course_in_list на core_course_list_element при портировании на 3.9
        $course = new \core_course_list_element($COURSE);
        // Есть контакты курса с учетом группового режима
        if ($course && $notifiableusersbygroups = $this->get_notifiable_users($course)) {
            $this->content->text = html_writer::div(
                get_string('description_' . $recipientselectionmode, 'block_coursemessage'),
                'description'
                );
            if ($recipientselectionmode == 'allowuserselect') {
                // Подключим js
                $PAGE->requires->js_call_amd('block_coursemessage/select_user', 'init', []);
                $style = '.block_coursemessage .allowuserselect form{display: block}';
                $this->content->text .= html_writer::tag('noscript', html_writer::tag('style', $style));
            }
            // Форма отправки сообщения
            $customdata = new stdClass();
            $customdata->courseid = $COURSE->id;
            $customdata->notifiableusersbygroups = $notifiableusersbygroups;
            $customdata->recipientselectionmode = $recipientselectionmode;
            $customdata->senduserinfo = !empty($this->config->senduserinfo);
            $sendform = new block_coursemessage_send_form($PAGE->url, $customdata);
            $sendform->process();
            // Таблица преподавателей
            $teacherstable = $this->user_table($notifiableusersbygroups);
            $this->content->text .= html_writer::div($teacherstable . $sendform->render(), $recipientselectionmode);
        } else {
            $this->content->text = html_writer::div(
                get_string('no_contacts', 'block_coursemessage'),
                'description'
                );
        }
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
                'course' => true
        ];
    }

    /**
     * Отображение заголовка блока
     *
     * @return bool if true then header will be visible.
     */
    public function hide_header() 
    {
        return false;
    }
    
    /**
     * Таблица пользователей
     * 
     * @param array $users
     * @return string
     */
    public function user_table($coursecontacts)
    {
        global $OUTPUT;
        $html = '';
        if (isset($this->config->userfields)) {
            $userfields = explode(',', $this->config->userfields);
        } else {
            $userfields = [];
        }
        if (!empty($coursecontacts)) {
            // Есть пользователи
            $uniquecontactids = [];
            foreach ($coursecontacts as $groupcursecontacts) {
                foreach ($groupcursecontacts as $contactid => $user) {
                    if (in_array($contactid, $uniquecontactids)) continue;
                    $uniquecontactids[] = $contactid;
                    // Загрузка дополнительных полей пользователя
                    profile_load_custom_fields($user);
                    $htmlfullname = html_writer::div(fullname($user), 'fullname');
                    $htmladditional = '';
                    foreach ( $userfields as $field )
                    {
                        $htmlfield = '';
                        $htmlprofilefield = '';
                        // Получаем чистый идентификатор поля
                        $clearfield = trim($field);
                        if ( isset($user->$clearfield) )
                        {// Идентификатор найден
                            $htmlfield = html_writer::div($user->$clearfield, $clearfield);
                        }
                        if ( isset($user->profile[$clearfield]) )
                        {// Идентификатор найден в допполях
                            $htmlprofilefield = html_writer::div($user->profile[$clearfield], $clearfield);
                        }
                        $htmladditional .= html_writer::div($htmlfield . $htmlprofilefield, 'additional');
                    }
                    $htmluserimage = html_writer::div(
                        $OUTPUT->user_picture($user, ['link' => false, 'size' => 150]),
                        'userimage'
                        );
                    $htmluserinfo = html_writer::div($htmlfullname . $htmladditional, 'userinfo');
                    
                    $html .= html_writer::div(
                        $htmluserimage . $htmluserinfo,
                        'userblock',
                        ['data-id' => $user->id]
                        );
                }
            }     
        }
        return $html;
    }
    
    /**
     * Получим контакты курса с учетом группового режима
     * 
     * @param core_course_list_element $course
     * @return array[$group->id][$potentialuser->id]
     */
    private function get_notifiable_users(core_course_list_element $course) {
        global $USER, $DB;
        $notifiableusersbygroups = [];
        if ($course->has_course_contacts()) {
            $potentialusers = (array)$course->get_course_contacts();
            // Право на доступ ко всем курсам
            $aag = has_capability('moodle/site:accessallgroups', context_course::instance($course->id));
            // Дополним данные пользователей
            foreach ($potentialusers as $potentialuserid => $u) {
                // Получить пользовтеля
                $potentialusers[$potentialuserid] = $DB->get_record('user', ['id' => $potentialuserid]);
            }
            if ($course->groupmode == SEPARATEGROUPS || $course->groupmode == VISIBLEGROUPS) {
                if ($groups = groups_get_all_groups($course->id, $USER->id)) {
                    foreach ($groups as $group) {
                        foreach ($potentialusers as $potentialuser) {
                            if ($potentialuser->id == $USER->id) {
                                // Do not send self.
                                continue;
                            }
                            if (groups_is_member($group->id, $potentialuser->id) || $aag) {
                                $notifiableusersbygroups[$group->id][$potentialuser->id] = $potentialuser;
                            }
                        }
                    }
                } else {
                    // Пользователь не состоит в группах, попробуем найти такиеже контакты курса
                    $fakecm = new stdClass();
                    $fakecm->course = $course->id;
                    $fakecm->groupingid = 0;
                    // User not in group, try to find graders without group.
                    foreach ($potentialusers as $potentialuser) {
                        if ($potentialuser->id == $USER->id) {
                            // Do not send self.
                            continue;
                        }
                        if (!groups_has_membership($fakecm, $potentialuser->id) || $aag) {
                            $notifiableusersbygroups[0][$potentialuser->id] = $potentialuser;
                        }
                    }
                }
            } else {
                // групповой режим не SEPARATEGROUPS, вернем все контакты курса кроме себя
                foreach ($potentialusers as $potentialuser) {
                    if ($potentialuser->id == $USER->id) {
                        // Do not send self.
                        continue;
                    }
                    $notifiableusersbygroups[0][$potentialuser->id] = $potentialuser; 
                }
            }
        }
        return $notifiableusersbygroups;
    }
}
