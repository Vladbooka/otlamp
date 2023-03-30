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
 * Плагин записи на курс OTPAY. Панель управления купонами.
 *
 * @package    enrol
 * @subpackage otpay
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require ("../../config.php");
require_once ("$CFG->dirroot/enrol/otpay/lib.php");

// ID элемента
$id = optional_param('id', 0, PARAM_INT);
// Тип страницы ( Купоны или категории купонов )
$layout = optional_param('layout', 'couponlist', PARAM_TEXT);
// Ошибки
$error = optional_param('error', 0, PARAM_INT);
// Успешное завершение действий
$success = optional_param('success', 0, PARAM_INT);

// Требуется авторизация на странице
require_login();

// Получаем контекст страницы
$context = context_system::instance();
// Проверяем права
require_capability('enrol/otpay:config', $context);

// Устанавливаем свойства страницы
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$PAGE->set_url('/enrol/otpay/coupons.php', array (
        'id' => $id,
        'layout' => $layout 
));
$PAGE->set_title(get_string('coupon_system', 'enrol_otpay'));
$PAGE->set_heading(get_string('coupon_system', 'enrol_otpay'));
$PAGE->navbar->add(get_string('coupon_system', 'enrol_otpay'));

// Получаем плагин
$plugin = enrol_get_plugin('otpay');

switch ( $layout )
{ // Формирование страницы в зависимости от типа
  // Список купонов
    case 'couponlist' :
        
        // Получим форму добавления купонов
        $form = $plugin->get_coupon_add_form();
        $form->process();
        break;
    // Удаление купона
    case 'coupondelete' :
        
        // Получим форму удаления купона
        $form = $plugin->get_coupon_delete_form($id);
        $form->process();
        break;
    case 'couponview' :
        $PAGE->navbar->add(get_string('coupon_system_coupon_view', 'enrol_otpay'));
        $PAGE->set_title(get_string('coupon_system_coupon_view', 'enrol_otpay'));
        $PAGE->set_heading(get_string('coupon_system_coupon_view', 'enrol_otpay'));
        break;
    // Список категорий
    case 'categorylist' :
        
        // Получим форму добавления категории купонов
        $form = $plugin->get_category_add_form();
        $form->process();
        break;
    // Удаление категории
    case 'categorydelete' :
        
        // Получим форму удаления категории купонов
        $form = $plugin->get_category_delete_form($id);
        $form->process();
        break;
}
echo $OUTPUT->header();

// Напечатать вкладки
$plugin->print_coupon_tab_menu($layout);
switch ( $layout )
{ // Формирование страницы в зависимости от типа
    case 'couponlist' :
        
        if ( $error )
        { // Отобразить сообщение об ошибке
            switch ( $error )
            {
                case 1 :
                    echo $OUTPUT->notification(get_string('coupon_add_coupon_error', 'enrol_otpay'));
                    break;
                case 2 :
                    echo $OUTPUT->notification(get_string('coupon_delete_coupon_error', 'enrol_otpay'));
                    break;
            }
        }
        if ( $success )
        { // Отобразить сообщение об успешной операции
            switch ( $success )
            {
                case 1 :
                    echo $OUTPUT->notification(get_string('coupon_add_coupon_success', 'enrol_otpay'), 'notifysuccess');
                    break;
                case 2 :
                    echo $OUTPUT->notification(get_string('coupon_delete_coupon_success', 'enrol_otpay'), 'notifysuccess');
                    break;
            }
        }
        $form->display();
        $plugin->display_coupons_list();
        
        break;
    // Удаление купона
    case 'coupondelete' :
        $form->display();
        break;
    // Удаление купона
    case 'couponview' :
        $plugin->display_coupon($id);
        break;
    case 'categorylist' :
        
        if ( $error )
        { // Отобразить сообщение об ошибке
            switch ( $error )
            {
                case 1 :
                    echo $OUTPUT->notification(get_string('coupon_add_category_error', 'enrol_otpay'));
                    break;
                case 2 :
                    echo $OUTPUT->notification(get_string('coupon_delete_category_error', 'enrol_otpay'));
                    break;
            }
        }
        if ( $success )
        { // Отобразить сообщение об успешной операции
            switch ( $success )
            {
                case 1 :
                    echo $OUTPUT->notification(get_string('coupon_add_category_success', 'enrol_otpay'), 'notifysuccess');
                    break;
                case 2 :
                    echo $OUTPUT->notification(get_string('coupon_delete_category_success', 'enrol_otpay'), 'notifysuccess');
                    break;
            }
        }
        $form->display();
        $plugin->display_coupon_category_list();
        break;
    // Удаление категории
    case 'categorydelete' :
        $form->display();
        break;
}

echo $OUTPUT->footer();