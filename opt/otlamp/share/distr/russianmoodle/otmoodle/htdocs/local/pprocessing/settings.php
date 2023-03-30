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
 * Настройки плагина
 *
 * @package    local_pprocessing
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(realpath(__FILE__))."/locallib.php");

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig)
{
    $yesno = [
        0 => get_string('no', 'local_pprocessing'),
        1 => get_string('yes', 'local_pprocessing')
    ];

    $defaultsubjects = [
        'spelling_mistake' => 'Уведомление об орфографической ошибке',
        'student_enrolled' => 'Новая запись на курс',
        'teacher_enrolled' => 'Новая запись на курс',
        'user_registered_recently' => '',
        'user_registered_long_ago' => '',
        'send_user_password' => get_string('newusernewpassword_message_subject', 'local_pprocessing'),
        'send_user_db_password' => get_string('newusernewdbpassword_message_subject', 'local_pprocessing')
    ];

    $defaultmessagesshort = [
        'spelling_mistake' => 'Здравствуйте, %{user.fullname}! На странице %{data.url} обнаружена орфографическая ошибка.',
        'student_enrolled' => 'Здравствуйте, %{user.fullname}! Вас записали на курс «%{course.fullname}»',
        'teacher_enrolled' => 'Здравствуйте, %{user.fullname}! Вас записали на курс «%{course.fullname}» в качестве преподавателя',
        'user_registered_recently' => '',
        'user_registered_long_ago' => '',
        'send_user_password' => get_string('newusernewpassword_message_short', 'local_pprocessing'),
        'send_user_db_password' => get_string('newusernewdbpassword_message_short', 'local_pprocessing')
    ];

    $defaultmessagesfull = [
        'spelling_mistake' => 'Здравствуйте, %{user.fullname}!<br /><br />На сайте «%{site.fullname}» была обнаружена орфографическая ошибка.<br />Страница, на которой найдена орфографическая ошибка: %{data.url}<br />Ошибка: <span style="color:red;">%{data.mistake}</span><br />Полная фраза: %{data.phrase}<br />Комментарий к ошибке: %{data.comment}<br /><br />С уважением, администрация сайта «%{site.fullname}»!',
        'student_enrolled' => 'Здравствуйте, %{user.fullname}!<br /><br />Вас записали на курс «%{course.fullname}».<br />Чтобы перейти к курсу, нажмите на ссылку %{course.url}<br /><br />С уважением, администрация сайта «%{site.fullname}»!',
        'teacher_enrolled' => 'Здравствуйте, %{user.fullname}!<br /><br />Вас записали на курс «%{course.fullname}» в качестве преподавателя.<br />Чтобы перейти к курсу, нажмите на ссылку %{course.url}<br /><br />С уважением, администрация сайта «%{site.fullname}»!',
        'user_registered_recently' => '',
        'user_registered_long_ago' => '',
        'send_user_password' => get_string('newusernewpassword_message_full', 'local_pprocessing'),
        'send_user_db_password' => get_string('newusernewdbpassword_message_full', 'local_pprocessing')
    ];

    // Получим объект для работы с библиотекой деканата
    $dof = local_pprocessing_get_dof();
//     $userfields = $profilefields = $customfields = [];
//     $customfields = [];
//     if( ! is_null($dof) )
//     {
//         $userfields = $dof->modlib('ama')->user(false)->get_userfields_list();
//         $profilefields = $dof->modlib('ama')->user(false)->get_user_custom_fields();
//         foreach($profilefields as $profilefield)
//         {
//             $customfields['profile_field_' . $profilefield->shortname] = $profilefield->name;
//         }
//     }
//     $alluserfields = array_merge($userfields, $customfields);
//     $alluserfields = $dof->modlib('ama')->user(false)->get_all_user_fields_list([], '', 'profile_field_');
//     var_dump($alluserfields);


    // объявляем страницу настроек плагина
    $settings = new admin_settingpage('local_pprocessing', get_string('pluginname', 'local_pprocessing'));

    // Сообщение о конфликте двух сценариев
    if (get_config('local_pprocessing', 'assign_role_according_criteria__status')
        && get_config('local_pprocessing', 'role_unassign__status')) {
            $notification = $OUTPUT->notification(get_string('script_conflict', 'local_pprocessing'), 'warning');
            $settings->add(new admin_setting_heading('local_pprocessing/notification', '', $notification));
    }

    // добавление настроек в секцию локальных плагинов
    $ADMIN->add('localplugins', $settings);

    // Общие настройки
    $settings->add(
        new admin_setting_heading('local_pprocessing/common_settings',
            get_string('common_settings_header', 'local_pprocessing'),
            ''));
    // Настройка отключения логирования процесса выполнения сценариев
    $name = 'local_pprocessing/disable_logging';
    $visiblename = get_string('disable_logging', 'local_pprocessing');
    $description = get_string('disable_logging_desc', 'local_pprocessing');
    $settings->add(new admin_setting_configcheckbox($name, $visiblename, $description, 0));

    // Настройки сценариев
    // получение списка пользователей
    $users = array_merge(get_users_by_capability(context_course::instance(SITEID), 'local/pprocessing:receive_notifications'), get_admins());

    // обработанные записей пользователй
    $processedrecords = [];
    foreach ( $users as $user )
    {
        $processedrecords[$user->id] = fullname($user);
    }

    // уведомление об орфографической ошибке
    $settings->add(
            new admin_setting_heading('local_pprocessing/spelling_mistake',
                    get_string('spelling_mistake_header', 'local_pprocessing'),
                    ''));
    $settings->add(
            new admin_setting_configcheckbox('local_pprocessing/spelling_mistake_message_status',
                    get_string('message_status', 'local_pprocessing'), '', 1));
    // добавление мультиселекта выбора получателей
    $settings->add(
            new admin_setting_configmultiselect('local_pprocessing/recievers',
                    get_string('settings_recievers', 'local_pprocessing'),
                    get_string('settings_recievers_desc', 'local_pprocessing'), null, $processedrecords));
    $settings->add(
            new admin_setting_configtext('local_pprocessing/spelling_mistake_message_subject',
                    get_string('message_subject', 'local_pprocessing'), '', $defaultsubjects['spelling_mistake'],
                    PARAM_RAW));
    $settings->add(
            new admin_setting_confightmleditor('local_pprocessing/spelling_mistake_message_full',
                    get_string('message_full', 'local_pprocessing'), '', $defaultmessagesfull['spelling_mistake'], PARAM_RAW));
    $settings->add(
            new admin_setting_confightmleditor('local_pprocessing/spelling_mistake_message_short',
                    get_string('message_short', 'local_pprocessing'), '', $defaultmessagesshort['spelling_mistake'], PARAM_RAW));

    // уведомление студенту
    $settings->add(
            new admin_setting_heading('local_pprocessing/student_enrolled', get_string('student_enrolled_header', 'local_pprocessing'), ''));
    $settings->add(
            new admin_setting_configcheckbox('local_pprocessing/student_enrolled_message_status',
                    get_string('message_status', 'local_pprocessing'), '', 0));
    $settings->add(
            new admin_setting_configtext('local_pprocessing/student_enrolled_message_subject',
                    get_string('message_subject', 'local_pprocessing'), '', $defaultsubjects['student_enrolled'], PARAM_RAW));
    $settings->add(
            new admin_setting_confightmleditor('local_pprocessing/student_enrolled_message_full',
                    get_string('message_full', 'local_pprocessing'), '', $defaultmessagesfull['student_enrolled'], PARAM_RAW));
    $settings->add(
            new admin_setting_confightmleditor('local_pprocessing/student_enrolled_message_short',
                    get_string('message_short', 'local_pprocessing'), '', $defaultmessagesshort['student_enrolled'], PARAM_RAW));

    // уведомление преподу
    $settings->add(
        new admin_setting_heading('local_pprocessing/teacher_enrolled', get_string('teacher_enrolled_header', 'local_pprocessing'), ''));
    $settings->add(
        new admin_setting_configcheckbox('local_pprocessing/teacher_enrolled_message_status',
            get_string('message_status', 'local_pprocessing'), '', 0));
    $settings->add(
        new admin_setting_configtext('local_pprocessing/teacher_enrolled_message_subject',
            get_string('message_subject', 'local_pprocessing'), '', $defaultsubjects['teacher_enrolled'], PARAM_RAW));
    $settings->add(
        new admin_setting_confightmleditor('local_pprocessing/teacher_enrolled_message_full',
            get_string('message_full', 'local_pprocessing'), '', $defaultmessagesfull['teacher_enrolled'], PARAM_RAW));
    $settings->add(
        new admin_setting_confightmleditor('local_pprocessing/teacher_enrolled_message_short',
            get_string('message_short', 'local_pprocessing'), '', $defaultmessagesshort['teacher_enrolled'], PARAM_RAW));

    // уведомление о недавней регистрации
    $settings->add(new admin_setting_heading(
        'local_pprocessing/user_registered_recently',
        get_string('user_registered_recently__header', 'local_pprocessing'),
        ''
        ));
    $settings->add(new admin_setting_configcheckbox(
        'local_pprocessing/user_registered_recently__status',
        get_string('message_status', 'local_pprocessing'),
        '', 0
        ));
    $settings->add(new admin_setting_configtext(
        'local_pprocessing/user_registered_recently__message_subject',
        get_string('message_subject', 'local_pprocessing'), '',
        $defaultsubjects['user_registered_recently'], PARAM_RAW
    ));
    $settings->add(new admin_setting_confightmleditor(
        'local_pprocessing/user_registered_recently__message_full',
        get_string('message_full', 'local_pprocessing'), '',
        $defaultmessagesfull['user_registered_recently'], PARAM_RAW
    ));
    $settings->add(new admin_setting_confightmleditor(
        'local_pprocessing/user_registered_recently__message_short',
        get_string('message_short', 'local_pprocessing'), '',
        $defaultmessagesshort['user_registered_recently'], PARAM_RAW
    ));

    // уведомление о давней регистрации
    $settings->add(new admin_setting_heading(
        'local_pprocessing/user_registered_long_ago',
        get_string('user_registered_long_ago__header', 'local_pprocessing'),
        ''
    ));
    $settings->add(new admin_setting_configcheckbox(
        'local_pprocessing/user_registered_long_ago__status',
        get_string('message_status', 'local_pprocessing'),
        '', 0
    ));
    $settings->add(new admin_setting_configtext(
        'local_pprocessing/user_registered_long_ago__message_subject',
        get_string('message_subject', 'local_pprocessing'), '',
        $defaultsubjects['user_registered_long_ago'], PARAM_RAW
    ));
    $settings->add(new admin_setting_confightmleditor(
        'local_pprocessing/user_registered_long_ago__message_full',
        get_string('message_full', 'local_pprocessing'), '',
        $defaultmessagesfull['user_registered_long_ago'], PARAM_RAW
    ));
    $settings->add(new admin_setting_confightmleditor(
        'local_pprocessing/user_registered_long_ago__message_short',
        get_string('message_short', 'local_pprocessing'), '',
        $defaultmessagesshort['user_registered_long_ago'], PARAM_RAW
    ));

    // уведомление о давней регистрации
    $settings->add(new admin_setting_heading(
        'local_pprocessing/user_registered_long_ago_deleting',
        get_string('user_registered_long_ago_deleting__header', 'local_pprocessing'),
        ''
    ));
    $settings->add(new admin_setting_configcheckbox(
        'local_pprocessing/user_registered_long_ago_deleting__status',
        get_string('action_status', 'local_pprocessing'),
        '', 0
    ));

    // Снятие назначенных ролей
    $settings->add(new admin_setting_heading(
        'local_pprocessing/role_unassign',
        get_string('role_unassign__header', 'local_pprocessing'),
        ''
    ));
    $settings->add(new admin_setting_configcheckbox(
        'local_pprocessing/role_unassign__status',
        get_string('role_unassign_status', 'local_pprocessing'),
        '',
        0
    ));
    $contextlevels = [
        CONTEXT_SYSTEM,
        CONTEXT_COURSECAT,
        CONTEXT_COURSE,
        CONTEXT_MODULE,
        CONTEXT_USER,
        CONTEXT_BLOCK
    ];
    $contexts = [
        'none' => get_string('settings_role_unassign_context_none', 'local_pprocessing'),
        'system' => get_string('settings_role_unassign_context_system', 'local_pprocessing'),
        'coursecat' => get_string('settings_role_unassign_context_coursecat', 'local_pprocessing'),
        'course' => get_string('settings_role_unassign_context_course', 'local_pprocessing'),
        'module' => get_string('settings_role_unassign_context_module', 'local_pprocessing'),
        'user' => get_string('settings_role_unassign_context_user', 'local_pprocessing'),
        'block' => get_string('settings_role_unassign_context_block', 'local_pprocessing')
    ];
    $settings->add(new admin_setting_configselect(
        'local_pprocessing/role_unassign_context',
        get_string('settings_role_unassign_context', 'local_pprocessing'),
        get_string('settings_role_unassign_context_desc', 'local_pprocessing'),
        'none',
        $contexts
    ));
    $roles = $rolesselect = [];
    foreach($contextlevels as $contextlevel)
    {
        $roles = array_merge($roles, get_roles_for_contextlevels($contextlevel));
    }
    $roles = array_unique($roles);
    $rolesselect[0] = get_string('settings_role_unassign_role_none', 'local_pprocessing');
    foreach($roles as $roleid)
    {
        $role = local_pprocessing_get_role($roleid);
        if( $role === false )
        {
            continue;
        }
        $rolesselect[$roleid] = role_get_name($role);
    }
    $settings->add(new admin_setting_configselect(
        'local_pprocessing/role_unassign_role',
        get_string('settings_role_unassign_role', 'local_pprocessing'),
        get_string('settings_role_unassign_role_desc', 'local_pprocessing'),
        0,
        $rolesselect
    ));

    /////////////////////////////////////////////////////////////////////////
    // Отправка паролей пользователям, загруженным через csv
    /////////////////////////////////////////////////////////////////////////
    $settings->add(new admin_setting_heading(
        'local_pprocessing/send_user_password',
        get_string('send_user_password__header', 'local_pprocessing'),
        ''
    ));
    $settings->add(new admin_setting_configcheckbox(
        'local_pprocessing/send_new_user_passwords__status',
        get_string('send_user_password_status', 'local_pprocessing'),
        get_string('send_user_password_status_desc', 'local_pprocessing'),
        0
    ));
    $settings->add(
        new admin_setting_configtext(
            'local_pprocessing/send_user_password_message_subject',
            get_string('setting_send_user_password_message_subject', 'local_pprocessing'),
            '',
            $defaultsubjects['send_user_password'],
            PARAM_RAW
        )
    );
    $settings->add(
        new admin_setting_confightmleditor(
            'local_pprocessing/send_user_password_message_full',
            get_string('setting_send_user_password_message_full', 'local_pprocessing'),
            '',
            $defaultmessagesfull['send_user_password'],
            PARAM_RAW
        )
    );
    $settings->add(
        new admin_setting_confightmleditor(
            'local_pprocessing/send_user_password_message_short',
            get_string('setting_send_user_password_message_short', 'local_pprocessing'),
            '',
            $defaultmessagesshort['send_user_password'],
            PARAM_RAW
        )
    );

    $settings->add(new admin_setting_configcheckbox(
        'local_pprocessing/send_user_password_auth_forcepasswordchange',
        get_string('settings_send_user_password_auth_forcepasswordchange', 'local_pprocessing'),
        get_string('settings_send_user_password_auth_forcepasswordchange_desc', 'local_pprocessing'),
        '1'
    ));

    $settings->add(new admin_setting_configcheckbox(
        'local_pprocessing/send_user_password_additional_password_settings',
        get_string('settings_send_user_password_additional_password_settings', 'local_pprocessing'),
        get_string('settings_send_user_password_additional_password_settings_desc', 'local_pprocessing'),
        '0'
    ));
    $settings->add(new admin_setting_configtext(
        'local_pprocessing/send_user_password_p_maxlen',
        get_string('settings_send_user_password_p_maxlen', 'local_pprocessing'),
        get_string('settings_send_user_password_p_maxlen_desc', 'local_pprocessing'),
        10,
        PARAM_INT,
        30
    ));
    $settings->add(new admin_setting_configtext(
        'local_pprocessing/send_user_password_p_numnumbers',
        get_string('settings_send_user_password_p_numnumbers', 'local_pprocessing'),
        get_string('settings_send_user_password_p_numnumbers_desc', 'local_pprocessing'),
        4,
        PARAM_INT,
        30
    ));
    $settings->add(new admin_setting_configtext(
        'local_pprocessing/send_user_password_p_numsymbols',
        get_string('settings_send_user_password_p_numsymbols', 'local_pprocessing'),
        get_string('settings_send_user_password_p_numsymbols_desc', 'local_pprocessing'),
        0,
        PARAM_INT,
        30
    ));
    $settings->add(new admin_setting_configtext(
        'local_pprocessing/send_user_password_p_lowerletters',
        get_string('settings_send_user_password_p_lowerletters', 'local_pprocessing'),
        get_string('settings_send_user_password_p_lowerletters_desc', 'local_pprocessing'),
        3,
        PARAM_INT,
        30
    ));
    $settings->add(new admin_setting_configtext(
        'local_pprocessing/send_user_password_p_upperletters',
        get_string('settings_send_user_password_p_upperletters', 'local_pprocessing'),
        get_string('settings_send_user_password_p_upperletters_desc', 'local_pprocessing'),
        3,
        PARAM_INT,
        30
    ));

    /////////////////////////////////////////////////////////////////////////
    // Отправка паролей из внешней базы данных пользователям, загруженным в систему
    /////////////////////////////////////////////////////////////////////////
    $settings->add(new admin_setting_heading(
        'local_pprocessing/send_user_db_password',
        get_string('send_user_db_password__header', 'local_pprocessing'),
        ''
        ));
    $settings->add(new admin_setting_configcheckbox(
        'local_pprocessing/send_new_user_db_passwords__status',
        get_string('send_user_db_password_status', 'local_pprocessing'),
        get_string('send_user_db_password_status_desc', 'local_pprocessing'),
        0
        ));
    $passtypes = [
        'plaintext' => get_string('pass_plaintext', 'local_pprocessing'),
        'md5' => get_string('pass_md5', 'local_pprocessing')
    ];
    $settings->add(new admin_setting_configselect(
        'local_pprocessing/send_user_db_password_password_type',
        get_string('settings_password_type', 'local_pprocessing'),
        get_string('settings_password_type_desc', 'local_pprocessing'),
        'plaintext',
        $passtypes
        ));
    $settings->add(new admin_setting_configcheckbox(
        'local_pprocessing/send_new_user_db_passwords_send_message',
        get_string('send_user_db_password_send_message', 'local_pprocessing'),
        get_string('send_user_db_password_send_message_desc', 'local_pprocessing'),
        0
        ));
    $settings->add(
        new admin_setting_configtext(
            'local_pprocessing/send_user_db_password_message_subject',
            get_string('setting_send_user_db_password_message_subject', 'local_pprocessing'),
            '',
            $defaultsubjects['send_user_db_password'],
            PARAM_RAW
            )
        );
    $settings->add(
        new admin_setting_confightmleditor(
            'local_pprocessing/send_user_db_password_message_full',
            get_string('setting_send_user_db_password_message_full', 'local_pprocessing'),
            get_string('settings_send_user_db_password_macro_write', 'local_pprocessing'),
            $defaultmessagesfull['send_user_db_password'],
            PARAM_RAW
            )
        );
    $settings->add(
        new admin_setting_confightmleditor(
            'local_pprocessing/send_user_db_password_message_short',
            get_string('setting_send_user_db_password_message_short', 'local_pprocessing'),
            '',
            $defaultmessagesshort['send_user_db_password'],
            PARAM_RAW
            )
        );
    $settings->add(new admin_setting_configcheckbox(
        'local_pprocessing/send_user_db_password_auth_forcepasswordchange',
        get_string('settings_send_user_db_password_auth_forcepasswordchange', 'local_pprocessing'),
        get_string('settings_send_user_db_password_auth_forcepasswordchange_desc', 'local_pprocessing'),
        0
        ));

    /////////////////////////////////////////////////////////////////////////
    // Синхронизация пользователя с глобальными группами
    /////////////////////////////////////////////////////////////////////////
    $settings->add(new admin_setting_heading(
        'local_pprocessing/sync_user_cohorts',
        get_string('sync_user_cohorts__header', 'local_pprocessing'),
        ''
    ));
    $settings->add(new admin_setting_configcheckbox(
        'local_pprocessing/sync_user_cohorts__status',
        get_string('sync_user_cohorts_status', 'local_pprocessing'),
        '',
        0
    ));

    $alluserfields = $dof->modlib('ama')->user(false)->get_all_user_fields_list([], '', 'profile_field_');
    $settings->add(new admin_setting_configselect(
        'local_pprocessing/user_cohorts',
        get_string('settings_user_cohorts', 'local_pprocessing'),
        get_string('settings_user_cohorts_desc', 'local_pprocessing'),
        '0',
        $alluserfields
    ));
    $identifiers = [
        'id' => get_string('cohortid', 'local_pprocessing'),
        'name' => get_string('cohortname', 'local_pprocessing'),
        'idnumber' => get_string('cohortidnumber', 'local_pprocessing')
    ];
    $settings->add(new admin_setting_configselect(
        'local_pprocessing/cohort_identifier',
        get_string('settings_cohort_identifier', 'local_pprocessing'),
        get_string('settings_cohort_identifier_desc', 'local_pprocessing'),
        'idnumber',
        $identifiers
    ));
    $cohortsmanagemodes = [
        'enable' => get_string('cohortsmanagemodes_enable', 'local_pprocessing'),
        'disable' => get_string('cohortsmanagemodes_disable', 'local_pprocessing')
    ];

    $settings->add(new admin_setting_configselect(
        'local_pprocessing/cohorts_manage_mode',
        get_string('settings_cohorts_manage_mode', 'local_pprocessing'),
        get_string('settings_cohorts_manage_mode_desc', 'local_pprocessing'),
        'disable',
        $cohortsmanagemodes
    ));

    // Синхронизация пользователя с глобальными группами по крону
    $settings->add(new admin_setting_heading(
        'local_pprocessing/sync_user_cohorts_task',
        get_string('sync_user_cohorts_task__header', 'local_pprocessing'),
        ''
    ));
    $settings->add(new admin_setting_configcheckbox(
        'local_pprocessing/sync_user_cohorts_task__status',
        get_string('sync_user_cohorts_task_status', 'local_pprocessing'),
        get_string('setting_sync_user_cohorts_task_desc', 'local_pprocessing'),
        0
    ));

    /////////////////////////////////////////////////////////////////////////
    // Удаление подписок типа "Синхронизация с глобальной группой" по дате из настраиваемых полей глобальной группы
    /////////////////////////////////////////////////////////////////////////
    if (class_exists('\\local_mcov\\entity\\cohort'))
    {
        $entity = new \local_mcov\entity\cohort('cohort');
        $choices = $entity->get_public_fields_list();
        $scenario = 'unenrol_cohorts_by_date';

        if (!empty($choices)) {
            $scenariosetting = 'header';
            $name = 'local_pprocessing/'.$scenario.'__'.$scenariosetting;
            $displayname = get_string('settings_'.$scenario.'_'.$scenariosetting, 'local_pprocessing');
            $description = get_string('settings_'.$scenario.'_'.$scenariosetting.'_desc', 'local_pprocessing');
            $settings->add(new admin_setting_heading($name, $displayname, $description));

            $scenariosetting = 'status';
            $name = 'local_pprocessing/'.$scenario.'__'.$scenariosetting;
            $displayname = get_string('settings_'.$scenario.'_'.$scenariosetting, 'local_pprocessing');
            $description = get_string('settings_'.$scenario.'_'.$scenariosetting.'_desc', 'local_pprocessing');
            $settings->add(new admin_setting_configcheckbox($name, $displayname, $description, 0));

            $scenariosetting = 'unenroldate';
            $name = 'local_pprocessing/'.$scenario.'__'.$scenariosetting;
            $displayname = get_string('settings_'.$scenario.'_'.$scenariosetting, 'local_pprocessing');
            $description = get_string('settings_'.$scenario.'_'.$scenariosetting.'_desc', 'local_pprocessing');
            reset($choices);
            $settings->add(new admin_setting_configselect($name, $displayname, $description, key($choices), $choices));
        } else {
            $scenariosetting = 'header';
            $name = 'local_pprocessing/'.$scenario.'__'.$scenariosetting;
            $displayname = get_string('settings_'.$scenario.'_'.$scenariosetting, 'local_pprocessing');
            $description = get_string('settings_empty_cohort_config_'.$scenario.'_'.$scenariosetting.'_desc', 'local_pprocessing');
            $settings->add(new admin_setting_heading($name, $displayname, $description));
        }
    }


    /////////////////////////////////////////////////////////////////////////
    // Удаление глобальных групп по дате из настраиваемых полей глобальной группы
    /////////////////////////////////////////////////////////////////////////
    if (class_exists('\\local_mcov\\entity\\cohort'))
    {
        $entity = new \local_mcov\entity\cohort('cohort');
        $choices = $entity->get_public_fields_list();
        $scenario = 'delete_cohorts_by_date';

        if (!empty($choices)) {
            $scenariosetting = 'header';
            $name = 'local_pprocessing/'.$scenario.'__'.$scenariosetting;
            $displayname = get_string('settings_'.$scenario.'_'.$scenariosetting, 'local_pprocessing');
            $description = get_string('settings_'.$scenario.'_'.$scenariosetting.'_desc', 'local_pprocessing');
            $settings->add(new admin_setting_heading($name, $displayname, $description));

            $scenariosetting = 'status';
            $name = 'local_pprocessing/'.$scenario.'__'.$scenariosetting;
            $displayname = get_string('settings_'.$scenario.'_'.$scenariosetting, 'local_pprocessing');
            $description = get_string('settings_'.$scenario.'_'.$scenariosetting.'_desc', 'local_pprocessing');
            $settings->add(new admin_setting_configcheckbox($name, $displayname, $description, 0));

            $scenariosetting = 'deldate';
            $name = 'local_pprocessing/'.$scenario.'__'.$scenariosetting;
            $displayname = get_string('settings_'.$scenario.'_'.$scenariosetting, 'local_pprocessing');
            $description = get_string('settings_'.$scenario.'_'.$scenariosetting.'_desc', 'local_pprocessing');
            reset($choices);
            $settings->add(new admin_setting_configselect($name, $displayname, $description, key($choices), $choices));
        } else {
            $scenariosetting = 'header';
            $name = 'local_pprocessing/'.$scenario.'__'.$scenariosetting;
            $displayname = get_string('settings_'.$scenario.'_'.$scenariosetting, 'local_pprocessing');
            $description = get_string('settings_empty_cohort_config_'.$scenario.'_'.$scenariosetting.'_desc', 'local_pprocessing');
            $settings->add(new admin_setting_heading($name, $displayname, $description));
        }
    }

    ////////////////////////////////////////////////////////
    // Удаление попыток тестирования старше заданной даты //
    ////////////////////////////////////////////////////////
    $scenario = 'delete_quiz_attempts_by_date';

    $scenariosetting = 'header';
    $name = 'local_pprocessing/'.$scenario.'__'.$scenariosetting;
    $displayname = get_string('settings_'.$scenario.'_'.$scenariosetting, 'local_pprocessing');
    $description = get_string('settings_'.$scenario.'_'.$scenariosetting.'_desc', 'local_pprocessing');
    $settings->add(new admin_setting_heading($name, $displayname, $description));

    $scenariosetting = 'status';
    $name = 'local_pprocessing/'.$scenario.'__'.$scenariosetting;
    $displayname = get_string('settings_'.$scenario.'_'.$scenariosetting, 'local_pprocessing');
    $description = get_string('settings_'.$scenario.'_'.$scenariosetting.'_desc', 'local_pprocessing');
    $settings->add(new admin_setting_configcheckbox($name, $displayname, $description, 0));

    $scenariosetting = 'relativedate';
    $name = 'local_pprocessing/'.$scenario.'__'.$scenariosetting;
    $displayname = get_string('settings_'.$scenario.'_'.$scenariosetting, 'local_pprocessing');
    $description = get_string('settings_'.$scenario.'_'.$scenariosetting.'_desc', 'local_pprocessing');
    $settings->add(new admin_setting_configduration($name, $displayname, $description, 7776000, 86400));

    ////////////////////////////////////////////////////////
    // Назначение или снятие роли пользователям согласно критериям
    ////////////////////////////////////////////////////////
    $scenario = 'assign_role_according_criteria';

    $scenariosetting = 'header';
    $name = 'local_pprocessing/'.$scenario.'__'.$scenariosetting;
    $displayname = get_string('settings_'.$scenario.'_'.$scenariosetting, 'local_pprocessing');
    $description = get_string('settings_'.$scenario.'_'.$scenariosetting.'_desc', 'local_pprocessing');
    $settings->add(new admin_setting_heading($name, $displayname, $description));

    $scenariosetting = 'status';
    $name = 'local_pprocessing/'.$scenario.'__'.$scenariosetting;
    $displayname = get_string('settings_'.$scenario.'_'.$scenariosetting, 'local_pprocessing');
    $description = get_string('settings_'.$scenario.'_'.$scenariosetting.'_desc', 'local_pprocessing');
    $settings->add(new admin_setting_configcheckbox($name, $displayname, $description, 0));

    // выбор поля профиля
    $choices = [];
    foreach ($alluserfields as $key => $field) {
        if (strpos($key, 'profile_field_') === 0) {
            $choices[str_replace('profile_field_', 'profile.', $key)] = $field;
        } else {
            $choices[$key] = $field;
        }
    }
    $scenariosetting = 'user_field';
    $name = 'local_pprocessing/'.$scenario.'__'.$scenariosetting;
    $displayname = get_string('settings_'.$scenario.'_'.$scenariosetting, 'local_pprocessing');
    $description = get_string('settings_'.$scenario.'_'.$scenariosetting.'_desc', 'local_pprocessing');
    reset($choices);
    $settings->add(new admin_setting_configselect($name, $displayname, $description, key($choices), $choices));

    // выбор отношения к значению в поле профиля
    $fieldratiovariants = [
        '=' => get_string($scenario . '_fieldratiovariant_equal', 'local_pprocessing'),
        '<>' => get_string($scenario . '_fieldratiovariant_notequal', 'local_pprocessing'),
        'LIKE' => get_string($scenario . '_fieldratiovariant_contain', 'local_pprocessing'),
        'NOT LIKE' => get_string($scenario . '_fieldratiovariant_notcontain', 'local_pprocessing')
    ];
    $scenariosetting = 'field_ratio_variant';
    $name = 'local_pprocessing/'.$scenario.'__'.$scenariosetting;
    $displayname = get_string('settings_'.$scenario.'_'.$scenariosetting, 'local_pprocessing');
    $description = get_string('settings_'.$scenario.'_'.$scenariosetting.'_desc', 'local_pprocessing');
    reset($fieldratiovariants);
    $settings->add(new admin_setting_configselect($name, $displayname, $description, key($fieldratiovariants), $fieldratiovariants));

    // значение поля профиля
    $scenariosetting = 'user_field_value';
    $name = 'local_pprocessing/'.$scenario.'__'.$scenariosetting;
    $displayname = get_string('settings_'.$scenario.'_'.$scenariosetting, 'local_pprocessing');
    $description = get_string('settings_'.$scenario.'_'.$scenariosetting.'_desc', 'local_pprocessing');
    $settings->add(new admin_setting_configtext($name, $displayname, $description, ''));

    // выбор назначаемой роли
    $systemcontext = context_system::instance();
    $roles = [];
    foreach (role_fix_names(get_all_roles(), $systemcontext, ROLENAME_ORIGINAL) as $role) {
        $roles[$role->id] = $role->localname;
    }
    $scenariosetting = 'assigned_role';
    $name = 'local_pprocessing/'.$scenario.'__'.$scenariosetting;
    $displayname = get_string('settings_'.$scenario.'_'.$scenariosetting, 'local_pprocessing');
    $description = get_string('settings_'.$scenario.'_'.$scenariosetting.'_desc', 'local_pprocessing');
    reset($roles);
    $settings->add(new admin_setting_configselect($name, $displayname, $description, key($roles), $roles));

    // выбор уровня контекста для назначения роли
    $levels = [];
    foreach ([CONTEXT_SYSTEM, CONTEXT_COURSECAT] as $level) {
        $levels[$level] = context_helper::get_level_name($level);
    }
    $scenariosetting = 'context_level';
    $name = 'local_pprocessing/'.$scenario.'__'.$scenariosetting;
    $displayname = get_string('settings_'.$scenario.'_'.$scenariosetting, 'local_pprocessing');
    $description = get_string('settings_'.$scenario.'_'.$scenariosetting.'_desc', 'local_pprocessing');
    reset($levels);
    $settings->add(new admin_setting_configselect($name, $displayname, $description, key($levels), $levels));

    // выбор категорий для назначения роли (опционально)
    if (get_config('local_pprocessing', 'assign_role_according_criteria__context_level') == CONTEXT_COURSECAT) {
        $allcategories = [];
        foreach (\core_course_category::get_all(['returnhidden' => true]) as $category) {
            $allcategories[$category->id] = $category->name;
        }
        $scenariosetting = 'category';
        $name = 'local_pprocessing/'.$scenario.'__'.$scenariosetting;
        $displayname = get_string('settings_'.$scenario.'_'.$scenariosetting, 'local_pprocessing');
        $description = get_string('settings_'.$scenario.'_'.$scenariosetting.'_desc', 'local_pprocessing');
        reset($choices);
        $settings->add(new admin_setting_configselect($name, $displayname, $description, key($allcategories), $allcategories));
    }
    
    ////////////////////////////////////////////////////////
    // Выгрузка оценок во внешнюю базу данных
    ////////////////////////////////////////////////////////
    $scenario = 'export_grades';
    $dbconnection = new \local_opentechnology\dbconnection();
    $connetions = $dbconnection->get_list_configs();
    if (!empty($connetions)) {
        $scenariosetting = 'header';
        $name = 'local_pprocessing/'.$scenario.'__'.$scenariosetting;
        $displayname = get_string($scenario.'_'.$scenariosetting, 'local_pprocessing');
        $description = get_string($scenario.'_'.$scenariosetting.'_desc', 'local_pprocessing');
        $settings->add(new admin_setting_heading($name, $displayname, $description));
        
        $scenariosetting = 'status';
        $name = 'local_pprocessing/'.$scenario.'__'.$scenariosetting;
        $displayname = get_string('settings_'.$scenario.'_'.$scenariosetting, 'local_pprocessing');
        $description = get_string('settings_'.$scenario.'_'.$scenariosetting.'_desc', 'local_pprocessing');
        $settings->add(new admin_setting_configcheckbox($name, $displayname, $description, 0));
        
        $scenariosetting = 'connection';
        $name = 'local_pprocessing/'.$scenario.'__'.$scenariosetting;
        $displayname = get_string('settings_'.$scenario.'_'.$scenariosetting, 'local_pprocessing');
        $description = get_string('settings_'.$scenario.'_'.$scenariosetting.'_desc', 'local_pprocessing');
        reset($connetions);
        $settings->add(new admin_setting_configselect($name, $displayname, $description, key($connetions), $connetions));
        $cfgconnection = get_config('local_pprocessing', $scenario.'__'.$scenariosetting);
        
        $scenariosetting = 'table';
        $name = 'local_pprocessing/'.$scenario.'__'.$scenariosetting;
        $displayname = get_string('settings_'.$scenario.'_'.$scenariosetting, 'local_pprocessing');
        $description = get_string('settings_'.$scenario.'_'.$scenariosetting.'_desc', 'local_pprocessing');
        reset($connetions);
        $settings->add(new admin_setting_configtext($name, $displayname, $description, '', PARAM_TEXT));
        $cfgtable = get_config('local_pprocessing', $scenario.'__'.$scenariosetting);
        
        $gradeitemtypelist = [
            'mod' => get_string('gradeitemtype_mod', 'local_pprocessing'),
            'course' => get_string('gradeitemtype_course', 'local_pprocessing'),
            'all' => get_string('gradeitemtype_all', 'local_pprocessing'),
        ];
        $scenariosetting = 'grade_itemtype';
        $name = 'local_pprocessing/'.$scenario.'__'.$scenariosetting;
        $displayname = get_string('settings_'.$scenario.'_'.$scenariosetting, 'local_pprocessing');
        $description = get_string('settings_'.$scenario.'_'.$scenariosetting.'_desc', 'local_pprocessing');
        $settings->add(new admin_setting_configselect($name, $displayname, $description, 'mod', $gradeitemtypelist));
        
        $gradeitemmodulelist = [
            'all' => get_string('gradeitemmodule_all', 'local_pprocessing'),
            'quiz' => get_string('gradeitemmodule_quiz', 'local_pprocessing'),
        ];
        $scenariosetting = 'grade_itemmodule';
        $name = 'local_pprocessing/'.$scenario.'__'.$scenariosetting;
        $displayname = get_string('settings_'.$scenario.'_'.$scenariosetting, 'local_pprocessing');
        $description = get_string('settings_'.$scenario.'_'.$scenariosetting.'_desc', 'local_pprocessing');
        $settings->add(new admin_setting_configselect($name, $displayname, $description, 'all', $gradeitemmodulelist));
        
        $gradeformatlist = [
            GRADE_DISPLAY_TYPE_REAL => get_string('real', 'grades'),
            GRADE_DISPLAY_TYPE_PERCENTAGE => get_string('percentage', 'grades'),
        ];
        $scenariosetting = 'grade_format';
        $name = 'local_pprocessing/'.$scenario.'__'.$scenariosetting;
        $displayname = get_string('settings_'.$scenario.'_'.$scenariosetting, 'local_pprocessing');
        $description = get_string('settings_'.$scenario.'_'.$scenariosetting.'_desc', 'local_pprocessing');
        $settings->add(new admin_setting_configselect($name, $displayname, $description, GRADE_DISPLAY_TYPE_PERCENTAGE, $gradeformatlist));
        
        $dateformatlist = [
            'timestamp' => get_string('dateformat_timestamp', 'local_pprocessing'),
            'date' => get_string('dateformat_date', 'local_pprocessing'),
            'datetime' => get_string('dateformat_datetime', 'local_pprocessing'),
        ];
        $scenariosetting = 'date_format';
        $name = 'local_pprocessing/'.$scenario.'__'.$scenariosetting;
        $displayname = get_string('settings_'.$scenario.'_'.$scenariosetting, 'local_pprocessing');
        $description = get_string('settings_'.$scenario.'_'.$scenariosetting.'_desc', 'local_pprocessing');
        $settings->add(new admin_setting_configselect($name, $displayname, $description, 'timestamp', $dateformatlist));
        
        $historyfields = ['llh_courseid', 'llh_coursefullname', 'llh_courseshortname', 'llh_activetime', 'llh_userid', 'llh_finalgrade', 'llh_lastupdate',
            'llhm_name', 'llhm_modname', 'llhcm_cmid', 'llhcm_activetime', 'llhcm_finalgrade', 'llhcm_timemodified'];
        $specialfields = ['user_fullname'];
        
        if (!empty($cfgconnection) && !empty($cfgtable)) {
            $connection = new \local_opentechnology\dbconnection($cfgconnection);
            $db = $connection->get_connection();
            $dbcols = $db->MetaColumnNames($cfgtable);
            $db->Disconnect();
            if (!empty($dbcols)) {
                $scenariosetting = 'description_composite_keys';
                $name = 'local_pprocessing/'.$scenario.'__'.$scenariosetting;
                $displayname = '';
                $description = get_string('settings_' . $scenario . '_' . $scenariosetting, 'local_pprocessing');
                $settings->add(new admin_setting_heading($name, $displayname, $description));
                
                reset($alluserfields);
                $scenariosetting = 'foreignkey1';
                $name = 'local_pprocessing/'.$scenario.'__'.$scenariosetting;
                $displayname = get_string('settings_'.$scenario.'_'.$scenariosetting, 'local_pprocessing');
                $description = get_string('settings_'.$scenario.'_'.$scenariosetting.'_desc', 'local_pprocessing');
                $settings->add(new admin_setting_configselect($name, $displayname, $description, key($alluserfields), $alluserfields));
                
                reset($dbcols);
                $dbcols = array_combine(array_values($dbcols), array_values($dbcols));
                $scenariosetting = 'primarykey1';
                $name = 'local_pprocessing/'.$scenario.'__'.$scenariosetting;
                $displayname = get_string('settings_'.$scenario.'_'.$scenariosetting, 'local_pprocessing');
                $description = get_string('settings_'.$scenario.'_'.$scenariosetting.'_desc', 'local_pprocessing');
                $settings->add(new admin_setting_configselect($name, $displayname, $description, current($dbcols), $dbcols));
                
                $scenariosetting = 'foreignkey2';
                $name = 'local_pprocessing/'.$scenario.'__'.$scenariosetting;
                $displayname = get_string('settings_'.$scenario.'_'.$scenariosetting, 'local_pprocessing');
                $description = get_string('settings_'.$scenario.'_'.$scenariosetting.'_desc', 'local_pprocessing');
                $settings->add(new admin_setting_configselect($name, $displayname, $description, 'llhcm_cmid', 
                    [0 => get_string('do_not_relate', 'local_pprocessing'), 'llhcm_cmid' => get_string('llhcm_cmid', 'local_pprocessing')]));
                
                $scenariosetting = 'primarykey2';
                $name = 'local_pprocessing/'.$scenario.'__'.$scenariosetting;
                $displayname = get_string('settings_'.$scenario.'_'.$scenariosetting, 'local_pprocessing');
                $description = get_string('settings_'.$scenario.'_'.$scenariosetting.'_desc', 'local_pprocessing');
                $settings->add(new admin_setting_configselect($name, $displayname, $description, current($dbcols), $dbcols));
                
                $scenariosetting = 'foreignkey3';
                $name = 'local_pprocessing/'.$scenario.'__'.$scenariosetting;
                $displayname = get_string('settings_'.$scenario.'_'.$scenariosetting, 'local_pprocessing');
                $description = get_string('settings_'.$scenario.'_'.$scenariosetting.'_desc', 'local_pprocessing');
                $settings->add(new admin_setting_configselect($name, $displayname, $description, 'llh_courseid', 
                    [0 => get_string('do_not_relate', 'local_pprocessing'), 'llh_courseid' => get_string('llh_courseid', 'local_pprocessing')]));
                
                $scenariosetting = 'primarykey3';
                $name = 'local_pprocessing/'.$scenario.'__'.$scenariosetting;
                $displayname = get_string('settings_'.$scenario.'_'.$scenariosetting, 'local_pprocessing');
                $description = get_string('settings_'.$scenario.'_'.$scenariosetting.'_desc', 'local_pprocessing');
                $settings->add(new admin_setting_configselect($name, $displayname, $description, current($dbcols), $dbcols));
                
                $scenariosetting = 'description_mapping_fields';
                $name = 'local_pprocessing/'.$scenario.'__'.$scenariosetting;
                $displayname = '';
                $description = get_string('settings_' . $scenario . '_' . $scenariosetting, 'local_pprocessing');
                $settings->add(new admin_setting_heading($name, $displayname, $description));
                
                // Сопоставление полей
                $dbcols = array_merge([get_string('do_not_send', 'local_pprocessing')], $dbcols);
                reset($dbcols);
                foreach ($historyfields as $field) {
                    $name = 'local_pprocessing/'.$scenario.'__data_mapping_'.$field;
                    $displayname = get_string('settings_'.$scenario.'_data_mapping', 'local_pprocessing', get_string('settings_'.$scenario.'_'.$field, 'local_pprocessing'));
                    $description = get_string('settings_'.$scenario.'_'.$field.'_desc', 'local_pprocessing');
                    $settings->add(new admin_setting_configselect($name, $displayname, $description, current($dbcols), $dbcols));
                }
                $alluserfields = $dof->modlib('ama')->user(false)->get_all_user_fields_list([], 'user_', 'user_profile_');
                foreach ($alluserfields as $code => $field) {
                    $name = 'local_pprocessing/'.$scenario.'__data_mapping_'.$code;
                    $displayname = get_string('settings_'.$scenario.'_data_mapping', 'local_pprocessing', $field);
                    $description = get_string('settings_'.$scenario.'_data_mapping_desc', 'local_pprocessing', $code);
                    $settings->add(new admin_setting_configselect($name, $displayname, $description, current($dbcols), $dbcols));
                }
                reset($specialfields);
                foreach ($specialfields as $field) {
                    $name = 'local_pprocessing/'.$scenario.'__data_mapping_'.$field;
                    $displayname = get_string('settings_'.$scenario.'_data_mapping', 'local_pprocessing', get_string('settings_'.$scenario.'_'.$field, 'local_pprocessing'));
                    $description = get_string('settings_'.$scenario.'_'.$field.'_desc', 'local_pprocessing');
                    $settings->add(new admin_setting_configselect($name, $displayname, $description, current($dbcols), $dbcols));
                }
            }
        }
        
    } else {
        $scenariosetting = 'header';
        $name = 'local_pprocessing/'.$scenario.'__'.$scenariosetting;
        $displayname = get_string('empty_connections_'.$scenario.'_'.$scenariosetting, 'local_pprocessing');
        $description = get_string('empty_connections_'.$scenario.'_'.$scenariosetting.'_desc', 'local_pprocessing');
        $settings->add(new admin_setting_heading($name, $displayname, $description));
    }
    
    ////////////////////////////////////////////////////////////////////////////
    // Выгрузка уже проставленных оценок во внешнюю базу данных по расписанию
    ////////////////////////////////////////////////////////////////////////////
    $scenario = 'export_grades_schedule';
    if (!empty($connetions)) {
        $scenariosetting = 'header';
        $name = 'local_pprocessing/'.$scenario.'__'.$scenariosetting;
        $displayname = get_string($scenario.'_'.$scenariosetting, 'local_pprocessing');
        $description = get_string($scenario.'_'.$scenariosetting.'_desc', 'local_pprocessing');
        $settings->add(new admin_setting_heading($name, $displayname, $description));
        
        $scenariosetting = 'status';
        $name = 'local_pprocessing/'.$scenario.'__'.$scenariosetting;
        $displayname = get_string('settings_'.$scenario.'_'.$scenariosetting, 'local_pprocessing');
        $description = get_string('settings_'.$scenario.'_'.$scenariosetting.'_desc', 'local_pprocessing');
        $settings->add(new admin_setting_configcheckbox($name, $displayname, $description, 0));
    } else {
        $scenariosetting = 'header';
        $name = 'local_pprocessing/'.$scenario.'__'.$scenariosetting;
        $displayname = get_string('empty_connections_'.$scenario.'_'.$scenariosetting, 'local_pprocessing');
        $description = get_string('empty_connections_'.$scenario.'_'.$scenariosetting.'_desc', 'local_pprocessing');
        $settings->add(new admin_setting_heading($name, $displayname, $description));
    }
}
