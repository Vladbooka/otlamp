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
 * Внутренняя бибилотека локального плагина Техподдержки СЭО 3KL
 *
 * @package    local
 * @subpackage opentechnology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

use \local_opentechnology\output\renderer;
use local_opentechnology\output\techinfo;

/**
 * Возвращает объект dof
 * Данная функция используется другими плагинами, зависимыми от local_opentechnology, удалять ее без рефакторинга категорически нельзя
 * @return NULL|dof_control
 */
function local_opentechnology_get_dof()
{
    global $CFG;
    $dof = null;
    if( file_exists($CFG->dirroot . '/blocks/dof/locallib.php') )
    {
        require_once($CFG->dirroot . '/blocks/dof/locallib.php');
        global $DOF;
        $dof = & $DOF;
    }
    return $dof;
}

function local_opentechnology_get_about() {

    global $CFG, $PAGE;



    $html = '';
    $tables = array();

    //Таблица технической информации об инсталляции. заголовок - релиз local_opentechnology
    $tables[] = local_opentechnology_get_main_info();

    //Если это необходимо, добавляем таблицу с данными по свободному месту.
    if ((isset ($CFG->otshowdiskspace)) && (is_array($CFG->otshowdiskspace)) && (! empty($CFG->otshowdiskspace))){
        $tables[] = local_opentechnology_get_freediskspace();
    }

    $renderer = $PAGE->get_renderer('local_opentechnology');
    $techinfo = new techinfo($tables);
    $html .= $renderer->render($techinfo);

    return html_writer::div($html, 'techinfo');
}

function local_opentechnology_get_info_tables(){

    global $CFG;

    $tables = array();

    //Таблица технической информации об инсталляции. заголовок - релиз local_opentechnology
    $tables[] = local_opentechnology_get_main_info();

    //Если это необходимо, добавляем таблицу с данными по свободному месту.
    $tables[] = local_opentechnology_get_freediskspace();


    if ((isset ($CFG->otadditionalinfo)) && (! empty($CFG->otadditionalinfo))){
        $table['header'] = get_string('admins_additional_info', 'local_opentechnology');
        $table['table']['type'] = 'additionalinfo';
        $table['alert'] = $CFG->otadditionalinfo;
        $tables[] = $table;
    }
    if (isset ($CFG->otshownetworkinterface) && (! empty($CFG->otshownetworkinterface))){
        $tables[] = local_opentechnology_get_network_interfaces();
    }

    return $tables;
}

/**
 * Подготовка данных для отображения серийника в новой верстке
 */
function local_opentechnology_get_otapi_table(){

    $pluginman = core_plugin_manager::instance();
    $pluginfo = $pluginman->get_plugin_info('local_opentechnology');

    $data['header'] = get_string('our_release','local_opentechnology') . ' ' . $pluginfo->release;
    $data['table']['type'] = 'otapi';
    // Содержимое пока что добавляется "снаружи"
    $data['table']['info'] = '';
    return $data;
}

function local_opentechnology_get_maturity_info() {
    global $CFG;

    require($CFG->dirroot . '/local/opentechnology/version.php');
    return $plugin->maturity;
}

/**
 * Получение технической информации об инсталляции для отображения настранице техподдержки
 *
 * @return array $data - массив данных технической информации для отображения на странице техподдержки
 */
function local_opentechnology_get_main_info(){

    global $CFG;

    $pluginman = core_plugin_manager::instance();
    $pluginfo = $pluginman->get_plugin_info('local_opentechnology');

    $header = get_string('additional_info','local_opentechnology');

    $info = [];

    // Билд 3kl
    $info['our_build']['name'] = get_string('our_build', 'local_opentechnology');
    $info['our_build']['value'] = $pluginfo->versiondb;

    // Стадия разработки
    $maturity = local_opentechnology_get_maturity_info();
    $info['our_maturity']['name'] = get_string('maturity', 'local_opentechnology');
    $info['our_maturity']['value'] = get_string('maturity'.$maturity, 'core_admin');

    // Версия Moodle
    $info['moodle_version']['name'] = get_string('moodle_version', 'local_opentechnology');
    $info['moodle_version']['value'] = $CFG->version;

    // Релиз Moodle
    $info['moodle_release']['name'] = get_string('moodle_release', 'local_opentechnology');
    $info['moodle_release']['value'] = $CFG->release;

    // Размер moodledata
    $info['moodledata_size']['name'] = get_string('moodledata_size', 'local_opentechnology');
    try {
        $mdatasize = \local_opentechnology\statistics::get_moodledata_size();
        $mdatasize = \local_opentechnology\statistics::human_readable_bytes($mdatasize);
        $value = $mdatasize;
    } catch(\Exception $ex){
        $value = get_string('error_failed_to_get_moodledata_size', 'local_opentechnology');
    }
    $info['moodledata_size']['value'] = $value;

    // Размер базы данных
    $info['database_size']['name'] = get_string('database_size', 'local_opentechnology');
    try {
        $dbsize = \local_opentechnology\statistics::get_database_size();
        $dbsize = \local_opentechnology\statistics::human_readable_bytes($dbsize);
        $value = $dbsize;
    } catch(\Exception $ex){
        $value = get_string('error_failed_to_get_database_size', 'local_opentechnology');
    }
    $info['database_size']['value'] = $value;

    // Полезный объем
    $info['useful_volume']['name'] = get_string('useful_volume', 'local_opentechnology');
    try {
        $usefulvol = \local_opentechnology\statistics::get_useful_volume();
        $usefulvol = \local_opentechnology\statistics::human_readable_bytes($usefulvol);
        $value = $usefulvol;
    } catch(\Exception $ex){
        $value = get_string('error_failed_to_get_useful_volume', 'local_opentechnology');
    }
    $info['useful_volume']['value'] = $value;

    // Ссылка на отчет по разрамеру курса
    $info['report_coursesize_link']['name'] = '';
    $url = new moodle_url('/report/coursesize');
    $info['report_coursesize_link']['value'] = html_writer::link($url, get_string('go_to_report_coursesize', 'local_opentechnology'), ['target' => '_blank']);

    // Лимит на полезный объем
    $info['moodle_size_limit']['name'] = get_string('moodle_size_limit', 'local_opentechnology');
    if (empty($CFG->moodlesizelimit))
    {
        $moodlesizelimit = get_string('moodle_size_limit_disabled', 'local_opentechnology');
    } else {
        if ((int)$CFG->moodlesizelimit == 1)
        {
            $moodlesizelimit = get_string('moodle_size_limit_enabled', 'local_opentechnology');
        } else
        {
            $moodlesizelimit = \local_opentechnology\statistics::human_readable_bytes((int)$CFG->moodlesizelimit * 1024 * 1024);
        }
    }
    $info['moodle_size_limit']['value'] = $moodlesizelimit;

    // Включено ли ограничение на загрузку файлов
    $info['moodle_size_limit_exceeded']['name'] = get_string('moodle_size_limit_exceeded', 'local_opentechnology');
    $limitexceeded = \local_opentechnology\statistics::is_moodle_size_limit_exceeded();
    $info['moodle_size_limit_exceeded']['value'] = get_string($limitexceeded?'yes':'no');

    // Количество пользователей
    $info['users_count']['name'] = get_string('users_count', 'local_opentechnology');
    $info['users_count']['value'] = \local_opentechnology\statistics::get_users_count();
    // Количество онлайн пользователей
    $info['online_users_count']['name'] = get_string('online_users_count', 'local_opentechnology');
    $info['online_users_count']['value'] = \local_opentechnology\statistics::get_online_users_count();
    // Количество курсов
    $info['courses_count']['name'] = get_string('courses_count', 'local_opentechnology');
    $info['courses_count']['value'] = \local_opentechnology\statistics::get_courses_count();

    $alert = get_string('report_coursesize_comment', 'local_opentechnology');

    $data['header'] = $header;
    $data['table']['type'] = 'main';
    $data['table']['info'] = $info;
    $data['alert'] = $alert;

    return $data;
}

/**
 * Получение свободного дискового пространства для разделов
 *
 * @param array $rows массив вида [1]['label']="Свободного пространства для загрузки файлов moodle"; - Текстовая метка, которая выводится пользователю
 *                                [1]['path']="/var/opt/otlamp/w1/ctmp1/www/data/filedir"; - Путь к папке, для которой нужно измерить место
 * @return array массив данных о свободном пространстве для разделов для отображения на странице техподдержки или пустой массив, если конфиг задан неверно
 */

function local_opentechnology_get_freediskspace(){

    global $CFG;

    $data = array();
    if ((! empty($CFG->otshowdiskspace)) && (is_array($CFG->otshowdiskspace))){
        // Заголовок
        $header = get_string('diskspace_monitoring', 'local_opentechnology');
        $info = array();
        $rows = $CFG->otshowdiskspace;
        foreach ($rows as $row){
            if ((array_key_exists('label', $row)) && (array_key_exists('path', $row))){
                $record['name'] = $row['label'];
                //Свободное место в байтах
                try{
                    $bytes = \local_opentechnology\statistics::get_free_diskspace_bytes($row['path']);
                    $record['value']['dsbytes'] = \local_opentechnology\statistics::human_readable_bytes($bytes);
                } catch(\Exception $ex){
                    $record['value']['failed'] = get_string('error_failed_to_get_free_diskspace', 'local_opentechnology');
                    $info[] = $record;
                    continue;
                }
                // Свободное место в процентах
                try{
                    $record['value']['dspercentage'] = \local_opentechnology\statistics::get_free_diskspace_percentage($row['path']);
                } catch(\Exception $ex){
                    $record['value']['failed'] = get_string('error_failed_to_get_free_diskspace', 'local_opentechnology');
                    $info[] = $record;
                    continue;
                }
                $info[] = $record;
            }
        }
        $alert = get_string('diskspace_comment', 'local_opentechnology');
        $data['header'] = $header;
        $data['table']['type'] = 'diskspace';
        $data['table']['info'] = $info;
        $data['alert'] = $alert;
    }

    return $data;
}

function local_opentechnology_get_network_interfaces(){
    $header = get_string('network_interface_parameters', 'local_opentechnology');
    $info = array();
    try{
        $nwinterfaces = \local_opentechnology\statistics::get_network_interfaces();
    }
    catch(\Exception $ex){
        $nwinterfaces = get_string('error_failed_to_get_ifconfig','local_opentechnology');
    }
    $info['nwinterfaces'] = $nwinterfaces;
    try{
        $value = \local_opentechnology\statistics::get_default_gateway();
    }
    catch (\Exception $ex){
        $value['defgateway'] = get_string('error_failed_to_get_defgateway','local_opentechnology');
    }
    $info['defgateway']['name'] = get_string('default_gateway','local_opentechnology');
    $info['defgateway']['value'] = $value;
    try{
        $dnsserverlist = \local_opentechnology\statistics::get_dns_server();
    }
    catch (\Exception $ex){
        $dnsserverlist = get_string('error_failed_to_get_dns_server_list','local_opentechnology');
    }
    $info['dnsserverlist']['name'] =get_string('dns_server_list','local_opentechnology');
    $info['dnsserverlist']['value'] = $dnsserverlist;
    $alert = '';
    $data['header'] = $header;
    $data['table']['type'] = 'networkinterfaces';
    $data['table']['info'] = $info;
    $data['alert'] = $alert;
    return $data;
}