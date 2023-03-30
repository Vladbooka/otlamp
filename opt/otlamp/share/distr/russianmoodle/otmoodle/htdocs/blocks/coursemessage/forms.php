<?php
///////////////////////////////////////////////////////////////////////////
//                                                                       //
// This file is part of Moodle - http://moodle.org/                      //
// Moodle - Modular Object-Oriented Dynamic Learning Environment         //
//                                                                       //
// Moodle is free software: you can redistribute it and/or modify        //
// it under the terms of the GNU General Public License as published by  //
// the Free Software Foundation, either version 3 of the License, or     //
// (at your option) any later version.                                   //
//                                                                       //
// Moodle is distributed in the hope that it will be useful,             //
// but WITHOUT ANY WARRANTY; without even the implied warranty of        //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         //
// GNU General Public License for more details.                          //
//                                                                       //
// You should have received a copy of the GNU General Public License     //
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.       //
//                                                                       //
///////////////////////////////////////////////////////////////////////////

/**
 * Блок мессенджера курса. Классы форм.
 *
 * @package    block_coursemessage
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
use core\notification;
use moodle_url as murl;

require_once($CFG->libdir . '/formslib.php');


class block_coursemessage_send_form extends moodleform 
{
    /**
     * ID курса
     * @var int
     */
    private $courseid = 0;
    /**
     *  Метод определения получателей сообщения
     * @var string
     */
    private $recipientselectionmode;
    /**
     * Контакты курса
     * 
     * @var array
     */
    private $notifiableusersbygroups;
    /**
     * Добавлять в сообщение данные о пользователе
     * @var bool
     */
    private $senduserinfo;
    
    public function definition() 
    {
        $mform = &$this->_form;
        $this->courseid = $this->_customdata->courseid;
        $this->recipientselectionmode = $this->_customdata->recipientselectionmode;
        $this->notifiableusersbygroups = $this->_customdata->notifiableusersbygroups;
        $this->senduserinfo = $this->_customdata->senduserinfo;
        
        if ($this->notifiableusersbygroups
            && has_capability('block/coursemessage:send', context_course::instance($this->courseid)))
        {
            $mform->addElement(
                    'static',
                    'form_send_message_desc',
                    NULL,
                    get_string('form_send_message_desc', 'block_coursemessage')
            );
            if ($this->recipientselectionmode == 'allowuserselect') {
                // выбор получателя
                $select = $mform->createElement(
                    'select',
                    'form_recipient',
                    ''
                    );
                foreach ($this->notifiableusersbygroups as $groupcursecontacts) {
                    foreach ($groupcursecontacts as $cursecontact) {
                        $select->addOption(
                            fullname($cursecontact),
                            $cursecontact->id
                            );
                    }
                }
                $mform->addElement($select);
            }
            $mform->addElement(
                    'textarea', 
                    'form_send_message',
                    NULL
            );
            $mform->setDefault('form_send_message', '');
            $mform->setType('form_send_message', PARAM_RAW_TRIMMED);
            
            $mform->addElement(
                    'submit', 
                    'form_send_submit', 
                    get_string('form_send_submit', 'block_coursemessage')
            );
        }
    }
    
    /**
     * Проверка на стороне сервера
     *
     * @param array data - данные из формы
     * @param array files - файлы из формы
     *
     * @return array - массив ошибок
     */
    function validation($data,$files)
    {
        $errors = [];
        if ( empty($data['form_send_message']) )
        {// дата начала больше даты конца - выведем сообщение
            $errors['form_send_message'] = get_string('error_form_empty_message','block_coursemessage');
        }
        // Возвращаем ошибки, если они возникли
        return $errors;
    }
    
    /**
     * Обработчик формы
     */
    public function process()
    {
        global $USER, $PAGE;
        if ($formdata = $this->get_data()) {
            // Форма отправлена и проверена
            if (!has_capability('block/coursemessage:send', context_course::instance($this->courseid)))
            {// У пользователя нет доступа к отправке сообщений
                notification::error(get_string('error_form_send_capability', 'block_coursemessage'));
            } else {
                $result = true;
                // Выборка получателей согласно настройкам
                $messagerecipients = $this->recipients_selection($formdata);
                if (empty($messagerecipients)) {
                    notification::error(get_string('error_form_send_receiver_not_set', 'block_coursemessage'));
                    $result = false;
                } else {
                    $message = $formdata->form_send_message;
                    if ($this->senduserinfo) {
                        $this->add_signature($message, $this->courseid);
                    }
                    foreach ($messagerecipients as $recipient) {
                        $result = ($result 
                            && message_post_message(
                                $USER, $recipient, $message, FORMAT_HTML
                                )
                            );
                    }
                }
                if ($result) {
                    // Отправка прошла успешно
                    notification::success(get_string('message_form_send_message_send_success', 'block_coursemessage'));
                    redirect($PAGE->url);
                } else {
                    notification::error(get_string('error_form_send_message_send_error', 'block_coursemessage'));
                }
            }
        }
    }
    /**
     * Выборка получателей согласно настройкам
     * 
     * @param object $formdata
     * @return array
     */
    private function recipients_selection($formdata) {
        global $DB;
        if (empty($this->notifiableusersbygroups)) {
            return [];
        }
        // Контакты курса которым будет отправлено сообщение
        $messagerecipients = [];
        switch ($this->recipientselectionmode) {
            // Пользователь сам выбирает получателя
            case 'allowuserselect':
                $messagerecipients = $DB->get_records('user', ['id' => $formdata->form_recipient]);
            break;
            // Автоматическое определение получателя 
            //для равномерного распределения нагрузки между преподавателями
            case 'automaticcontact':
                $cache = cache::make('block_coursemessage', 'last_recipient');
                // Если пользователь состоит в разных группах, выберем случайную группу
                $groupid = array_rand($this->notifiableusersbygroups);
                $contacts = $this->notifiableusersbygroups[$groupid];
                // В кеше хранится идентификатор преподавателя по каждой группе курса
                $key =  $this->courseid . '_' . $groupid;
                $lastrecipientid = $cache->get($key);
                // Отсортируем массив чтобы гарантировать не изменность порядка элементов
                ksort($contacts ,SORT_NUMERIC);
                $contactsids = array_keys($contacts);
                $contactid = null;
                // Проверим, что кеш присутствует
                if ($lastrecipientid !== false && in_array($lastrecipientid, $contactsids)) {
                    $index = array_search($lastrecipientid, $contactsids);
                    // Если в кеше хранится последный получатель сообшения перейдем к первому
                    $nextindex = count($contactsids) <= ($index + 1) ? 0 : $index + 1;
                    if (array_key_exists($nextindex, $contactsids)) {
                        $contactid = $contactsids[$nextindex];
                    }
                } 
                if (is_null($contactid)) {
                    // Кеш для выбранной группы отсутствует 
                    // или идентификатр получателя сообщения, хранящийся в кеше, отсутствует в группе
                    // $contacts не пусты так-как имеется проверка на пустоту выше
                    $contactid = array_rand($contacts);
                }
                $messagerecipients = [$contactid => $contacts[$contactid]];
                $cache->set($key, key($messagerecipients));
            break;
            // Отправить всем контактам курса
            default:
                // Эти циклы позволяют получить уникальных преподавателей так-как в группах они могут повторяться
                foreach ($this->notifiableusersbygroups as $groupcursecontacts) {
                    foreach ($groupcursecontacts as $contactid => $contact) {
                        $messagerecipients[$contactid] = $contact;
                    }
                }
        }
        return $messagerecipients;
    }
    /**
     * Добавляет подпись с курсом и группами пользователя к сообщению
     * 
     * @param string $message
     * @param int $courseid
     */
    private function add_signature(&$message, $courseid) {
        global $USER;
        // Получение данных о курсе
        $course = get_course($courseid);
        if ($course) {
            $courselist = new core_course_list_element($course);
            // Ссылка на курс
            $signaturedata['course'] = html_writer::link(
                new murl('/course/view.php', ['id' => $courseid]),
                $courselist->get_formatted_fullname()
                );
            $groups = groups_get_all_groups($courseid, $USER->id);
            $signaturedata['groups'] = '';
            if (!empty($groups)) {
                $groupsnames = [];
                foreach ($groups as $group) {
                    // Группы
                    $groupsnames[] = groups_get_group_name($group->id);
                }
                $signaturedata['groups'] = implode(', ', $groupsnames);
            }
            if (empty($groupsnames)) {
                $message .= get_string('form_send_signature_course', 'block_coursemessage', $signaturedata);
            } else {
                $message .= get_string('form_send_signature_all', 'block_coursemessage', $signaturedata);
            }
        }
    }
}
