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
 * Витрина курсов. Дополнительная библиотека
 *
 * @package    local
 * @subpackage crw
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use otcomponent_customclass\parsers\form\customform;

/**
 * Получить плагины витрины типа список курсов
 * @param array $plugins массив плагинов, если нужно получить конкрентные
 * @return array - Массив доступных плагинов
 */
function local_crw_get_plugin_type_courses_list($plugins = [])
{
    global $CFG;
    $pluginlist = [];
    if( ! is_array($plugins) )
    {
        $plugins = (array)$plugins;
    }

    if( ! empty($plugins) )
    {
        $subpluginnames = $plugins;
    } else
    {
        $subpluginnames = core_plugin_manager::instance()->get_installed_plugins('crw');
    }
    foreach(array_keys($subpluginnames) as $subpluginname)
    {
        $filename = $CFG->dirroot . '/local/crw/plugins/' . $subpluginname . '/lib.php';
        if( file_exists($filename) )
        {
            require_once($filename);
            $subpluginclassname = 'crw_'.$subpluginname;
            if(class_exists($subpluginclassname))
            {
                $subplugin = new $subpluginclassname($subpluginname);
                if($subplugin->get_type() == CRW_PLUGIN_TYPE_COURSES_LIST)
                {
                    $pluginlist[$subpluginname] = $subplugin;
                }
            }
        }
    }
    return $pluginlist;
}
/**
 * Формирование объекта с данными кастомных полей формы
 *
 * @param int $courseid - идентификатор курса, данные которого необходимо получить
 * @param array|null $allowedfields - поля, настроенные сейчас для редактирования
 * @return stdClass
 */
function custom_form_course_fields_get_data($courseid, $allowedfields=null)
{
    global $DB;
    
    // Получим все свойства текущего курса
    $cffrecords = $DB->get_records('crw_course_properties', ['courseid' => $courseid], '', 'id, name, value');
    
    $cfdata = new stdClass();
    foreach($cffrecords as $cffrecord)
    {
        $cffnameparts = explode('_', $cffrecord->name, 2);
        // Найдено кастомное поле и оно настроено сейчас для кастомной формы
        if ($cffnameparts[0] == 'cff' && ($cffname = $cffnameparts[1]) &&
            (is_null($allowedfields) || array_key_exists($cffname, $allowedfields)))
        {
            $cfdata->{$cffname} = json_decode($cffrecord->value);
        }
    }
    
    return $cfdata;
}

/**
 * Получение количества сохраненных данных по каждой из повторяемых групп полей формы
 * @param array $cffields - настройки полей формы
 * @param stdClass $cfdata - сохраненные данные полей
 * @return array
 */
function custom_form_course_fields_count_repeats($cffields, $cfdata)
{
    $repeats = [];
    foreach ($cffields as $fieldname => $fieldattrs )
    {
        if (isset($fieldattrs['repeatgroup']) && !array_key_exists($fieldattrs['repeatgroup'], $repeats))
        {
            $repeats[$fieldattrs['repeatgroup']] = isset($cfdata->{$fieldname}) ? count($cfdata->{$fieldname}) : null;
        }
    }
    return $repeats;
}

/**
 * Получение писка полей по каждой из повторяемых групп полей формы
 * @param array $cffields - настройки полей формы
 * @return array
 */
function custom_form_course_fields_get_repeat_fields($cffields)
{
    $repeats = [];
    foreach ($cffields as $fieldname => $fieldattrs )
    {
        if (isset($fieldattrs['repeatgroup']))
        {
            $repeats[$fieldattrs['repeatgroup']][] = $fieldname;
        }
    }
    return $repeats;
}

/**
 * Обработка формы
 *
 * @param customform $customform - кастомная форма
 * @param int $courseid - идентификатор курса
 */
function custom_form_course_fields_process($customform, $courseid)
{
    global $DB;
    
    // Кастомные поля формы
    $cffields = $customform->get_fields();
    // обработка отправки формы
    if ($cfdata = $customform->get_data())
    {
        // форма отправлена
        // в результате обработки формы будет произведена попытка сохранения новых значений
        // поэтому старые значения удаляем
        $DB->delete_records_select('crw_course_properties', "name LIKE 'cff_%' AND courseid=:courseid", ['courseid' => $courseid]);
        
        // Обработка удаления повторяющихся групп полей (если потребуется)
        $repeatsdata = custom_form_course_fields_get_repeat_fields($cffields);
        $repeatdeletion = false;
        foreach($repeatsdata as $repeatgroup => $rgfields)
        {
            if (!isset($cfdata->{$repeatgroup.'_del'}))
            {
                continue;
            }
            
            $repeatdeletion = true;
            
            // производится удаление одной из повторяющихся групп
            foreach($rgfields as $rgfieldname)
            {
                $cffrec = $DB->get_record('crw_course_properties', ['name' => 'cff_'.$rgfieldname, 'courseid' => $courseid]);
                if (empty($cffrec))
                {
                    continue;
                }
                
                $savedvalue = json_decode($cffrec->value);
                foreach($cfdata->{$repeatgroup.'_del'} as $delnum => $submitname)
                {
                    unset($savedvalue[$delnum]);
                }
                if (!empty($savedvalue))
                {
                    $cffrec->value = json_encode(array_values($savedvalue));
                    $DB->update_record('crw_course_properties', $cffrec);
                } else
                {
                    $DB->delete_records('crw_course_properties', ['id' => $cffrec->id]);
                }
            }
            
        }
        if ($repeatdeletion)
        {
            redirect($customform->get_form_action());
        }
        
        
        // Сохранение формы, если не было удаления
        foreach($cffields as $cffname => $cffdata)
        {
            if ($cffdata['type'] == 'checkbox' && !isset($cfdata->{$cffname}))
            {
                $cfdata->{$cffname} = 0;
            }
            
            if (!isset($cfdata->{$cffname}) || $cffdata['type'] == 'submit')
            {
                continue;
            }
            
            $cffrec = $DB->get_record('crw_course_properties', [
                'name' => 'cff_'.$cffname,
                'courseid' => $courseid
            ]);
            if (!empty($cffrec))
            {
                $cffrec->value = json_encode($cfdata->{$cffname});
                $cffrec->svalue = custom_form_course_fields_prepare_svalue(
                    $cffdata['type'],
                    $cfdata->{$cffname}
                );
                $sortvalue = custom_form_course_fields_prepare_sortvalue($cfdata->{$cffname}, $cffdata);
                if (!is_null($sortvalue))
                {
                    $cffrec->sortvalue = $sortvalue;
                }
                $DB->update_record('crw_course_properties', $cffrec);
            } else
            {
                // Объект для сохранения кастомного поля
                $cffrec = new stdClass();
                $cffrec->name = 'cff_'.$cffname;
                $cffrec->courseid = $courseid;
                $cffrec->value = json_encode($cfdata->{$cffname});
                // для всех типов полей продумать хранение поисковой информации
                // учитывая $cffdata['type']
                $cffrec->svalue = custom_form_course_fields_prepare_svalue(
                    $cffdata['type'],
                    $cfdata->{$cffname}
                );
                $sortvalue = custom_form_course_fields_prepare_sortvalue($cfdata->{$cffname}, $cffdata);
                if (!is_null($sortvalue))
                {
                    $cffrec->sortvalue = $sortvalue;
                }
                $DB->insert_record('crw_course_properties', $cffrec);
            }
        }
    }
}

function custom_form_course_fields_prepare_sortvalue($value, $cffdata)
{
    $sortvalue = null;
    
    // Если элемент формы имеет целочисленный тип данных, по умолчанию считаем, что он подходит под сортировочное значение
    if (array_key_exists('filter', $cffdata) && $cffdata['filter'] == 'int')
    {
        $sortvalue = $value;
    }
    
    return $sortvalue;
}

function custom_form_course_fields_prepare_svalue($type, $value)
{
    $result = '';
    // для всех типов полей продумать хранение поисковой информации
    // учитывая $cffdata['type']
    switch($type)
    {
        case 'select':
        case 'text':
        case 'textarea':
            $svalue = $value;
            if (is_array($value))
            {
                $svalue = implode(',', $value);
            }
            $result = mb_substr($svalue, 0, 255);
            break;
        case 'checkbox':
            $result = (int)!empty($value);
            break;
        default:
            break;
    }
    return $result;
}

/**
 * Рассчитать популярность курсов в системе (расчет не производится по скрытым курсам)
 * @param string $type тип популярности (как считать)
 */
function local_crw_calculation_course_popularity($type) {
    global $DB, $CFG;
    $filepath = $CFG->dirroot . '/local/crw/classes/popularity/' . $type . '.php';
    if (!file_exists($filepath))
    {
        return;
    }
    require_once($filepath);
    $classname = '\\local_crw\\popularity\\' . $type;
    $popularity = new $classname();
    $rs = $DB->get_recordset_select('course', 'visible = 1');
    foreach ($rs as $record) {
        $result = $popularity->get_course_popularity($record->id);
        $popularity->save($record->id, $result);
    }
    $rs->close();
}


function local_crw_get_custom_fields()
{
    $cffields = [];
    
    $customcoursefields = get_config('local_crw', 'custom_course_fields');
    if (!empty($customcoursefields))
    {
        $result = \otcomponent_customclass\utils::parse($customcoursefields);
        
        if ( $result->is_form_exists() )
        {
            // Форма
            $customform = $result->get_form();
            // Кастомные поля формы
            $cffields = $customform->get_fields();
            
        } else {
            // Настройка кастомных полей формы не валидна
        }
    } else {
        // Кастомные поля формы никто не настраивал
    }
    
    return $cffields;
}