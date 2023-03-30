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
 * Настраиваемые формы
 *
 * @package    block_otcustomform
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_otcustomform;

use html_table;
use html_writer;
use stdClass;
use core_user;
use coding_exception;
use moodle_url;
use otcomponent_customclass\utils as otutils;

class utils
{
    /**
     * Проверка существования формы
     * 
     * @param number $id
     * 
     * @return bool
     */
    public static function is_form_exists($id)
    {
        global $DB;
        return $DB->record_exists('block_otcustomform_forms', ['id' => $id]);
    }
    
    /**
     * Получение объекта формы
     * 
     * @param number $id
     * @param array $ajaxformdata
     * 
     * @return \otcomponent_customclass\parsers\form\customform | false
     */
    public static function get_form($id, $ajaxformdata = null)
    {
        $record = self::get_form_record($id);
        $result = otutils::parse($record->layout);
        
        if ( $result->is_form_exists() )
        {
            $form = $result->get_form();
            $form->setForm(null, null, 'post', '', null, true, $ajaxformdata);
            return $form;
        }
        
        return false;
    }
    
    /**
     * Получения записи формы из БД
     * 
     * @param number $id
     * 
     * @return stdClass
     */
    public static function get_form_record($id)
    {
        global $DB;
        $record = $DB->get_record('block_otcustomform_forms', ['id' => $id]);
        if ( empty($record) )
        {
            return false;
        }
        return $record;
    }
    
    /**
     * Получить главную родительскую анкета по цепочке наследования
     * 
     * @param number $id
     * 
     * @return int
     */
    public static function get_parent_form_id($id)
    {
        $record = self::get_form_record($id);
        if ( $record === false )
        {
            throw new coding_exception('invalid_formid');
        }
        
        $hasparent = false;
        do 
        {
            $record = self::get_form_record($id);
            if ( ! empty($record->parentid) )
            {
                $hasparent = true;
                $id = $record->parentid;
            } else 
            {
                $hasparent = false;
            }
            
        } while ( $hasparent );
        
        return $id;
    }
    
    /**
     * Получить количество ответов к форме
     * 
     * @param number $id
     * @param bool $includechild
     * 
     * @return int
     */
    public static function get_response_count($id, $includechild = true)
    {
        global $DB;
        
        $count = 0;
        if ( ! self::is_form_exists($id) )
        {
            return $count;
        }
        
        do 
        {
            $count += $DB->count_records('block_otcustomform_responses', ['customformid' => $id]);
            $record = $DB->get_record('block_otcustomform_forms', ['parentid' => $id]);
            if ( ! empty($record) )
            {
                $id = $record->id;
            } else 
            {
                $id = 0;
            }
        } while ( $includechild && ! empty($id) );
        
        return $count;
    }
    
    /**
     * Получить все ответы к форме
     *
     * @param number $id
     * @param bool $includechild
     *
     * @return []stdClass
     */
    public static function get_responses($id, $includechild = true)
    {
        global $DB;
        
        $records = [];
        if ( ! self::is_form_exists($id) )
        {
            return $records;
        }
        
        do
        {
            $records = array_merge($records, $DB->get_records('block_otcustomform_responses', ['customformid' => $id]));
            $record = $DB->get_record('block_otcustomform_forms', ['parentid' => $id]);
            if ( $record )
            {
                $id = $record->id;
            } else
            {
                $id = 0;
            }
        } while ( $includechild && ! empty($id) );
        
        return $records;
    }
    
    /**
     * Получить все ответы по персоне
     *
     * @param number $userid
     * @param number $formid
     * @param bool $includechild
     *
     * @return []
     */
    public static function get_responses_by_person($userid, $formid = null, $includechild = true)
    {
        global $DB;
        
        $records = [];
        if ( ! core_user::is_real_user($userid) )
        {
            return $records;
        }
        $params = ['userid' => $userid];
        if ( ! is_null($formid) && self::is_form_exists($formid) )
        {
            $params['customformid'] = $formid;
        }
        
        do
        {
            $records = array_merge($records, $DB->get_records('block_otcustomform_responses', $params));
            if ( ! empty($params['customformid']) )
            {
                $record = $DB->get_record('block_otcustomform_forms', ['parentid' => $params['customformid']]);
                if ( ! empty($record) )
                {
                    $params['customformid'] = $record->id;
                } else
                {
                    $params['customformid'] = 0;
                }
            }
        } while ( $includechild && ! empty($params['customformid']) );
        usort($records, function($a, $b) {
            if ($a->timecreated == $b->timecreated)
            {
                return 0;
            }
            return ($a->timecreated < $b->timecreated) ? 1 : -1;
        });
        
        return $records;
    }
    
    /**
     * Получить все ответы по персоне
     *
     * @param []stdClass $responses - результат функции get_responses_by_person()
     *
     * @return html_table
     */
    public static function get_table_responses_by_person($responses)
    {
        $html = '';
        foreach ($responses as $response)
        {
            $html .= html_writer::tag('h4', get_string('response_info', 'block_otcustomform', date('d-m-Y G:i:s', $response->timecreated)));
            $form = self::get_form($response->customformid);
            $data = json_decode($response->data);
            $form->set_data($data);
            $form->hardFreezeAll();
            $html .= $form->render();
        }
        return $html;
    }
    
    /**
     * Получение все пользователей, ответивших на форму в хронологическом порядке
     * 
     * @param number $formid
     * 
     * @return []stdClass
     */
    public static function get_users($formid)
    {
        if ( ! self::is_form_exists($formid) )
        {
            return [];
        }
        
        // получение всех записей
        $records = self::get_responses($formid);
        $newrecords = [];
        
        // группировка по пользователям в хронологическом порядке
        // новый - выше
        foreach ($records as $record)
        {
            if ( ! array_key_exists($record->userid, $newrecords) )
            {
                $templrecord = new stdClass();
                $templrecord->user = core_user::get_user($record->userid);
                $templrecord->time = $record->timecreated;
                $newrecords[$record->userid] = $templrecord;
                continue;
            }
            if ( $record->timecreated > $newrecords[$record->userid]->time )
            {
                $newrecords[$record->userid]->time = $record->timecreated;
            }
        }
        
        usort($newrecords, function($a, $b) {
            if ($a->time == $b->time) 
            {
                return 0;
            }
            return ($a->time < $b->time) ? 1 : -1;
        });
        
        return $newrecords;
    }
    
    /**
     * Получение спика пользователей
     * 
     * @param []stdClass $users
     * 
     * @return html_table
     */
    public static function get_users_table($formid, $users)
    {
        $table = new html_table();
        $table->head = [
            get_string('fullname', 'block_otcustomform'),
            get_string('lastfilltime', 'block_otcustomform'),
            get_string('actions', 'block_otcustomform')
        ];
        
        foreach ($users as $user)
        {
            $table->data[] = [
                ! empty($user->user) ? fullname($user->user) : get_string('no_login_user', 'block_otcustomform'),
                date('d-m-Y G:i:s', $user->time),
                html_writer::link(
                        new moodle_url('/blocks/otcustomform/uresponses.php',
                                [
                                    'id' => $formid,
                                    'uid' => ! empty($user->user->id) ? $user->user->id : 0
                                ]), get_string('view_all_user_responses', 'block_otcustomform'), [
                            'target' => '_blank'
                        ])
            ];
        }
        
        return $table;
    }
    
    /**
     * Сохранение формы в БД
     * 
     * @param stdClass $record
     * 
     * @return int|false
     */
    public static function save_form_record(stdClass $record)
    {
        global $DB;
        
        if ( ! empty($record->id) && self::is_form_exists($record->id) )
        {
            $existrecord = self::get_form_record($record->id);
            if ( $record->layout == $existrecord->layout )
            {
                // сохранять нечего, разметка не изменилась
                return $record->id;
            }
            if( self::get_response_count($record->id) )
            {// уже есть результаты сохранения формы - создадим новую, а текущую запишем в родители
                $record->parentid = $record->id;
                $record->timecreated = time();
                unset($record->id);
            }
        } else
        {// форма еще не существует, создаем новую
            unset($record->id);
            $record->timecreated = time();
        }
        
        if ( ! empty($record->id) )
        {
            if ( $DB->update_record('block_otcustomform_forms', $record) )
            {
                return $record->id;
            }
        } else 
        {
            if ( $id = $DB->insert_record('block_otcustomform_forms', $record) )
            {
                return $id;
            }
        }
        
        return false;
    }
    
    /**
     * Сохранение ответа по форме в БД
     *
     * @param stdClass $record
     *
     * @return bool
     */
    public static function save_respone_record(stdClass $record)
    {
        global $DB;
        
        unset($record->id);
        if ( empty($record->customformid) || ! self::is_form_exists($record->customformid) )
        {
            return false;
        }
        if ( empty($record->data) )
        {
            return false;
        }
        
        return $DB->insert_record('block_otcustomform_responses', $record);
    }
}