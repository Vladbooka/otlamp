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
 * Тип вопроса Объекты на изображении. Страница генерации базового изображения.
 *
 * @package    qtype
 * @subpackage otimagepointer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../../config.php');
require_once($CFG->libdir . '/questionlib.php');

// ID набора вопросов
$qubaid = required_param('quba', PARAM_INT);
// Номер слота в наборе
$slot = required_param('slot', PARAM_INT);
// Токен доступа
$token = required_param('token', PARAM_RAW_TRIMMED);

// Требуется авторизация в системе
require_login();

// Получить набор вопросов
try {
    // Получение набора вопросов
    $quba = question_engine::load_questions_usage_by_activity($qubaid);
    // Получение попытки прохождения вопроса пользователем
    $qa = $quba->get_question_attempt($slot);
    // Получение вопроса
    $question = $qa->get_question();
    
    // Проверка на тип вопроса
    if ( $question->get_type_name() != 'otimagepointer' )
    {// Тип вопроса не валиден
        throw new moodle_exception('invalid_question_type');
    }
    
    // Получение типа источника изображения
    $imagesource = $question->get_imagesource();
    
    // Проверка токена доступа к старнице
    if ( ! $imagesource->verify_access_token($qubaid, $slot, $token) )
    {
        throw new moodle_exception('invalid_question_access_token');
    }

    // Получить источник изображения
    $baseimage = $question->get_image($qa);
    
    if ( $baseimage )
    {// Трансляция изображения
        \core\session\manager::write_close();
        send_stored_file($baseimage, 0, false);
    }
} catch ( moodle_exception $e )
{// Ошибка получения данных о вопросе
    send_file_not_found();
}