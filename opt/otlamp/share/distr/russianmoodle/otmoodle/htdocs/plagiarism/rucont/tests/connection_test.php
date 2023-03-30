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
 * Плагин определения заимствований Руконтекст. Тестирование соединения с API
 *
 * @package    plagiarism
 * @subpackage rucont
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


// Подключаем библиотеки
require_once ('../../../config.php');
require_once($CFG->dirroot . '/plagiarism/rucont/lib.php');
/*
$connection = new plagiarism_rucont\connection();

print_object('Данные о соединении');
print_object($connection);
print_object('******************************');

print_object('Загрузка документа');
$data = [];
$data['content'] = file_get_contents("test.txt");
$data['filename'] = 'anothertest.txt';
$data['autor'] = 'Автор';
$data['title'] = 'Заголовок';
$data['tester'] = 'Проверяющий';
$data['comment'] = 'Комментарий';
$res = $connection->upload_document($data);
print_object($res);
print_object('******************************');


print_object('Статус проверки документа');
$id = 222222;
$res = $connection->get_result($id);
print_object($res);
print_object('******************************');

print_object('Ссылка на отчет документа');
$id = 222222;
$res = $connection->get_report_url($id);
print_object($res);
print_object('******************************');
*/
die;
