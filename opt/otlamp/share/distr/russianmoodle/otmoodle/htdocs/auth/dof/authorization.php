<?php
// This file is not a part of Moodle - http://moodle.org/
// This is a none core contributed module.
//
// This is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// The GNU General Public License
// can be see at <http://www.gnu.org/licenses/>.

/**
 * Плагин аутентификации Деканата. Класс плагина.
 *
 * @package    auth_dof
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require('../../config.php');

// Try to prevent searching for sites that allow sign-up.
if (!isset($CFG->additionalhtmlhead)) {
    $CFG->additionalhtmlhead = '';
}
$CFG->additionalhtmlhead .= '<meta name="robots" content="noindex" />';

redirect_if_major_upgrade_required();

$context = context_system::instance();
$PAGE->set_url("$CFG->httpswwwroot/auth/dof/authorization.php");
$PAGE->set_context($context);

$cancel    = optional_param('cancel', 0, PARAM_BOOL);  // redirect to frontpage, needed for loginhttps
$codeinput = optional_param('code', 0, PARAM_NOTAGS);  // Проверочный код пользователя
$userid    = optional_param('userid', 0, PARAM_INT);   // Ид пользовтеля

if ($cancel) {
    redirect(new moodle_url('/'));
}
// Конфигурация плагина
$config = get_config('auth_dof');

global $OUTPUT;
$html = '';
//создаем форму
$url = new moodle_url("$CFG->httpswwwroot/auth/dof/authorization.php", ['userid' => $userid]);
$mform = new auth_dof\forms\dual_auth_form($url->out());
$currentattempts = get_user_preferences('auth_dof_dualauth_attemptsentrycode', 0, $userid);
if( $codeinput )
{
    $currentattempts = $currentattempts + 1;
    // Увеличиваем количество попыток ввода проверочного кода на единицу
    set_user_preference('auth_dof_dualauth_attemptsentrycode', $currentattempts, $userid);
}
$allowedattempts = (int)$config->allowedentryattempts;

$showform = true;
$timeleft = false;

if(!$userid)
{
    \core\notification::error(get_string('no_user_id', 'auth_dof'));
    $showform = false;
} 
if( time() > get_user_preferences('auth_dof_dualauth_creation_time', null, $userid) + $config->codelivetime && ! isloggedin() ) 
{
    \core\notification::error(get_string('auth_time_expiried', 'auth_dof'));
    $showform = false;
    $timeleft = true;
} 
if( $currentattempts > $allowedattempts )
{
    \core\notification::error(get_string('exhausted_all_attempts', 'auth_dof'));
    $showform = false;
} 
if( $codeinput ) 
{
    $code = get_user_preferences('auth_dof_dualauth_code', null, $userid);  // Проверочный код
    if (is_null($code))
    {
        \core\notification::error(get_string('dualauth_error_code_missed', 'auth_dof'));
    } else
    {
        if( ! $timeleft )
        {
            if( $code == $codeinput )
            {
                // авторизуем пользователя
                complete_user_login(core_user::get_user($userid));
                // убиваем
                unset_user_preference('auth_dof_dualauth_code', $userid);
                unset_user_preference('auth_dof_dualauth_creation_time', $userid);
                unset_user_preference('auth_dof_dualauth_attemptsentrycode', $userid);
                // Редирект
                redirect(get_user_preferences('auth_dof_dualauth_urltogo', '/', $userid));
            } else
            {
                \core\notification::error(get_string('wrong_code', 'auth_dof'));
                if( $currentattempts >= $allowedattempts )
                {
                    \core\notification::error(get_string('exhausted_all_attempts', 'auth_dof'));
                } else
                {
                    // рендерим форму
                    if( $showform )
                    {
                        $html .= $OUTPUT->heading(get_string('dual_auth', 'auth_dof'));
                        $html .= $mform->render();
                    }
                }
            }
        }
    }
} else 
{
    if( $currentattempts >= $allowedattempts )
    {
        \core\notification::error(get_string('exhausted_all_attempts', 'auth_dof'));
    } else 
    {
        // рендерим форму
        if( $showform )
        {
            $html .= $OUTPUT->heading(get_string('dual_auth', 'auth_dof'));
            $html .= $mform->render();
        }
    }
}

echo $OUTPUT->header();
//displays the form
echo $html;
echo $OUTPUT->footer();