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
 * Антиплагиат. Тестирование соединение с API сервиса "Антиплагиат".
 *
 * @package    plagiarism
 * @subpackage apru
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


// Подключаем библиотеки
require_once ('../../../config.php');
require_once($CFG->dirroot . '/plagiarism/apru/lib.php');
/*
global $USER;

$connection = new plagiarism_apru\connection();

print_object('Данные о соединении');
print_object($connection);
print_object('******************************');

if ( ! $rows = $DB->get_records('plagiarism_apru_files')) 
{
    print_object('Файлы не найдены');
} else 
{
    print_object('Файлы');
    print_object($rows);
    print_object('******************************');
}

print_object('Загрузка документа');
$data = new stdClass();
$data->Data     = file_get_contents("tt.txt");
$data->FileName = 'file.txt';
$data->FileType = '.txt';
$data->ExternalUserID = $USER->id;
$res = $connection->upload_document($data);
print_object($res);
$id = $res->UploadDocumentResult->Uploaded[0]->Id->Id;
print_object($id);
print_object('******************************');

print_object('Проверка документа');
$res = $connection->check_document($id);
print_object($res);
print_object('******************************');

print_object('Добавление в индекс');
var_dump($connection->set_indexed_status($id, true));
print_object('******************************');

print_object('Статус проверки документа');
$res = $connection->get_check_status($id);
print_object($res);
print_object('******************************');

$id = $id-1;
print_object('Отчет проверки документа');
$res = $connection->get_report_view($id);
print_object($res);
print_object('******************************');

print_object('Статус аккаунта');
$companystatus =  $connection->get_company_stats();
print_object($companystatus);
print_object('******************************');

die;
