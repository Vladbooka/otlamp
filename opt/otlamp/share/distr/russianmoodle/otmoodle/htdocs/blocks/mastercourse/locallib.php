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
 * Блок согласования мастеркурса, методы обработки
 *
 * @package    block_mastercourse
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Согласование мастер-курса запрошено. Обработчик. Отправка уведомлений.
 *
 * @param int $courseid - идентификатор курса
 * @param array $options    [
 *                              'initiator' - инициатор согласования для подстановки в текст уведомления
 *                              'course' - инициатор согласования для подстановки в текст уведомления
 *                              'discipline' - инициатор согласования для подстановки в текст уведомления
 *                              'notificationresult' - результат отправки уведомлений в деканате, в колонке userto должны
 *                                                     содержаться пользователи, которым уже отправлены уведомления
 *                          ]
 * @return false в случае ошибки
 */
function mastercourse_verification_requested($courseid, $options=[])
{
    if( empty($options['initiator']) || empty($options['course']) || empty($options['discipline']) )
    {
        //@TODO: создать дефолтные значения, на случай, если не переданы опции
        return false;
    }
    
    $alreadynotificated = [];
    if( ! empty($options['notificationresult']) AND is_array($options['notificationresult']) )
    {
        foreach($options['notificationresult'] as $notificationresult)
        {
            if( ! empty($notificationresult['userto']->id) )
            {
                $alreadynotificated[] = $notificationresult['userto']->id;
            }
        }
    }
    
    $a = new stdClass();
    $a->initiator = $options['initiator'];
    $a->course = $options['course'];
    $a->discipline =  $options['discipline'];

    $coursecontext = context_course::instance($courseid);
    
    if( $blockcontext = find_block_in_course($coursecontext) )
    {
        // блок найден в контексте курса
        notify_users_with_capability(
            get_string('mastercourse_verification_requested_message', 'block_mastercourse', $a),
            $blockcontext,
            'block/mastercourse:receive_notification_of_requests',
            $alreadynotificated
        );
    } elseif ( find_block_in_system() )
    {
        // блок найден в контексте системы с отображением в дочерних контекстах
        notify_users_with_capability(
                get_string('mastercourse_verification_requested_message', 'block_mastercourse', $a),
                $coursecontext,
                'block/mastercourse:receive_notification_of_requests',
                $alreadynotificated
                );
    }
}

/**
 * Версия мастер-курса одобрена. Обработчик. Отправка уведомлений.
 *
 * @param int $courseid - идентификатор курса
 * @param array $options    [
 *                              'course' - инициатор согласования для подстановки в текст уведомления
 *                              'discipline' - инициатор согласования для подстановки в текст уведомления
 *                              'notificationresult' - результат отправки уведомлений в деканате, в колонке userto должны
 *                                                     содержаться пользователи, которым уже отправлены уведомления
 *                          ]
 * @return false в случае ошибки
 */
function mastercourse_accepted($courseid, $options=[])
{
    if( empty($options['course']) || empty($options['discipline']) )
    {
        //@TODO: создать дефолтные значения, на случай, если не переданы опции
        return false;
    }

    $alreadynotificated = [];
    if( ! empty($options['notificationresult']) AND is_array($options['notificationresult']) )
    {
        foreach($options['notificationresult'] as $notificationresult)
        {
            if( ! empty($notificationresult['userto']->id) )
            {
                $alreadynotificated[] = $notificationresult['userto']->id;
            }
        }
    }
    
    $a = new stdClass();
    $a->course = $options['course'];
    $a->discipline =  $options['discipline'];

    $coursecontext = context_course::instance($courseid);
    
    if( $blockcontext = find_block_in_course($coursecontext) )
    {
        // блок найден в контексте курса
        notify_users_with_capability(
                get_string('mastercourse_accepted_mail_text', 'block_mastercourse', $a),
                $blockcontext,
                'block/mastercourse:receive_notification_of_responses',
                $alreadynotificated
                );
    } elseif ( find_block_in_system() )
    {
        // блок найден в контексте системы с отображением в дочерних контекстах
        notify_users_with_capability(
                get_string('mastercourse_accepted_mail_text', 'block_mastercourse', $a),
                $coursecontext,
                'block/mastercourse:receive_notification_of_responses',
                $alreadynotificated
                );
    }
}


/**
 * Версия мастер-курса отклонена. Обработчик. Отправка уведомлений.
 *
 * @param int $courseid - идентификатор курса
 * @param array $options    [
 *                              'course' - инициатор согласования для подстановки в текст уведомления
 *                              'discipline' - инициатор согласования для подстановки в текст уведомления
 *                              'notificationresult' - результат отправки уведомлений в деканате, в колонке userto должны
 *                                                     содержаться пользователи, которым уже отправлены уведомления
 *                          ]
 * @return false в случае ошибки
 */
function mastercourse_declined($courseid, $options=[])
{
    if( empty($options['course']) || empty($options['discipline']) )
    {
        //@TODO: создать дефолтные значения, на случай, если не переданы опции
        return false;
    }

    $alreadynotificated = [];
    if( ! empty($options['notificationresult']) AND is_array($options['notificationresult']) )
    {
        foreach($options['notificationresult'] as $notificationresult)
        {
            if( ! empty($notificationresult['userto']->id) )
            {
                $alreadynotificated[] = $notificationresult['userto']->id;
            }
        }
    }
    
    $a = new stdClass();
    $a->course = $options['course'];
    $a->discipline =  $options['discipline'];

    $coursecontext = context_course::instance($courseid);
    
    if( $blockcontext = find_block_in_course($coursecontext) )
    {
        // блок найден в контексте курса
        notify_users_with_capability(
                get_string('mastercourse_declined_mail_text', 'block_mastercourse', $a),
                $blockcontext,
                'block/mastercourse:receive_notification_of_responses',
                $alreadynotificated
                );
    } elseif ( find_block_in_system() )
    {
        // блок найден в контексте системы с отображением в дочерних контекстах
        notify_users_with_capability(
                get_string('mastercourse_declined_mail_text', 'block_mastercourse', $a),
                $coursecontext,
                'block/mastercourse:receive_notification_of_responses',
                $alreadynotificated
                );
    }
}


/**
 * Отправка уведомлений пользователям, имеющий право в контексте
 *
 * @param string $notificationtext
 * @param  $context - контект для проверки права
 * @param string $capability - право, наличие которого проверяется
 * @param array $excludeusers - массив идентификаторов пользователей, которым отправлять ничего не нужно
 */
function notify_users_with_capability($notificationtext, $context, $capability, $excludeusers=[])
{
    global $DB;
    
    $supportuser = core_user::get_support_user();
    $capusers = get_users_by_capability($context, $capability, 'u.id');
    
    foreach($capusers as $capuser)
    {
        if( ! in_array($capuser->id, $excludeusers) )
        {
            // Получим пользовтаеля
            $userto = $DB->get_record('user', ['id' => $capuser->id]);
            if ( empty($userto) )
            {// Не нашли пользователя
                continue;
            }
            message_post_message($supportuser, $userto, $notificationtext, FORMAT_MOODLE);
        }
    }
}

/**
 * Найти блок "Согласование мастер-курса" в контексте курса
 *
 * @param context_course $coursecontext - контекст курса
 * @return context_block|boolean
 */
function find_block_in_course($coursecontext)
{
    global $DB;
    
    // поиск курса, добавленного в контекст курса
    $blockinstancerecord = $DB->get_record(
            'block_instances',
            [
                'blockname' => 'mastercourse',
                'parentcontextid' => $coursecontext->id
            ],
            'id',
            IGNORE_MULTIPLE
            );
    
    if( empty($blockinstancerecord) )
    {
        return false;
    }
    
    return context_block::instance($blockinstancerecord->id);
}

/**
 * Найти блок "Согласование мастер-курса" в контексте системе но с отображением во всех курсах
 *
 * @return context_block|boolean
 */
function find_block_in_system()
{
    global $DB;
    
    // поиск в контексте системы с отображением во всех курсах
    $blockinstancerecord = $DB->get_record(
            'block_instances',
            [
                'blockname' => 'mastercourse',
                'parentcontextid' => context_system::instance()->id,
                'showinsubcontexts' => 1
            ],
            'id',
            IGNORE_MULTIPLE
            );
    
    if ( empty($blockinstancerecord) )
    {
        return false;
    }
    
    return context_block::instance($blockinstancerecord->id);
}

/**
 * Массив плащадок
 *
 * @return array shortnames
 */
function get_all_services() {
    return \block_mastercourse\helper::get_eduportals_codes();
}