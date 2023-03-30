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
 * Плагин записи на курс OTPAY. Точка перенаправления пользователей после оплаты.
 *
 * @package    enrol
 * @subpackage otpay
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require ('../../config.php');
require_once($CFG->dirroot . '/enrol/otpay/lib.php');
$plugin = enrol_get_plugin('otpay');

$id = required_param('id', PARAM_INT);
$ok = required_param('ok', PARAM_INT);
$ko = required_param('ko', PARAM_INT);
$enrolotpayid = optional_param('enrolotpayid', NULL, PARAM_INT);

$PAGE->set_url(new moodle_url('/enrol/otpay/return.php'));

if ( ! $course = $DB->get_record('course',
    [
        'id' => $id
    ]) )
{// Не найден курс, за который была произведена оплата
    redirect($CFG->wwwroot);
}

// Контекст страницы
$context = context_course::instance($course->id, MUST_EXIST);
$PAGE->set_context($context);

// Требуется авторизация
require_login();

//получение объекта заказа
$enrolotpay = $DB->get_record('enrol_otpay', ['id' => $enrolotpayid]);

// Страница перенаправления
if (!empty($enrolotpay))
{
    $enrol = $DB->get_record('enrol', ['id' => $enrolotpay->instanceid]);
}

// место, настроенное для перенаправления после оплаты
$destination = $plugin->get_enrol_destination_url($enrol);

if (!$enrolotpay)
{//заказ не найден - залогируем и выведем ошибку
    $logdata = [
        'remote_addr' => $_SERVER['REMOTE_ADDR'],
        'post' => $_POST,
        'get' => $_GET,
        'enrolotpay' => $enrolotpay
    ];
    $plugin->otpay_log('Error on return page. Enrolotpayid is invalid', $logdata);
    if ( $ko == 1 )
    {
        notice(get_string('paymentinvalid', 'enrol_acquiropay'), $destination);
    } else
    {
        notice(get_string('otpay_wrong_data', 'enrol_otpay'), $destination);
    }
}
try
{// запросим обработку статуса заказа
    //в случае успешного статуса будет выполнена подписка на курс
    $plugin->process_payment_status($enrolotpay);
} catch ( Exception $ex )
{
    $plugin->otpay_log("exception", $ex->getTraceAsString());
}

// Получаем название курса
$fullname = format_string($course->fullname, true, array(
    'context' => $context
));


//выборка пользователей имеющих подписку в будущем
//получение инстансов способов записи
$courseenrols = enrol_get_instances($course->id, true);
$courseenrolids=[];
foreach($courseenrols as $courseenrol)
{
    $courseenrolids[]=$courseenrol->id;
}
if( ! empty($courseenrolids) )
{
    //получение подписок по этим инстансам, которые имеются на будущее
    $futureueselect = "userid=:userid AND enrolid IN (".implode(',',$courseenrolids).") AND
                status=0 AND timestart>:timestart
                ORDER BY timestart";
    $futureueparams = [
        'userid' => $USER->id,
        'timestart' => time()
    ];
    //имеется ли подписка на этот курс, которая начнет действовать в будущем
    $futureue = $DB->record_exists_select('user_enrolments', $futureueselect, $futureueparams);

}
else
{
    $futureue=false;
}


// Проверка на подписку пользователя
if ( is_enrolled($context, NULL, '', true) || $futureue )
{ // Пользователь подписан на курс, страница ipn.php успешно обработала запрос от платежной системы
    redirect($destination, get_string('paymentthanks', '', $fullname));
} else
{ // Пользователь не подписан на курс
    $PAGE->set_url($destination);
    echo $OUTPUT->header();
    $a = new stdClass();
    $a->teacher = get_string('defaultcourseteacher');
    $a->fullname = $fullname;
    if ( $ko == 1 )
    {
        notice(get_string('paymentinvalid', 'enrol_acquiropay'), $destination);
    } else
    {
        notice(get_string('paymentsorry', '', $a), $destination);
    }
}