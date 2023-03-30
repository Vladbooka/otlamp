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

defined('MOODLE_INTERNAL') || die();

/**
 * local_pprocessing test data generator class
 *
 * @package     local_pprocessing
 * @category    phpunit
 * @copyright   2021 LTD "OPEN TECHNOLOGY"
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_pprocessing_generator extends component_generator_base {

    /**
     * Создание категории поля профиля
     *
     * @param stdClass|array|null - объект с желаемыми свойствами категории
     * @return stdClass profile category record
     */
    public function create_profile_category($record=null) {
        global $DB;

        static $index = 0;
        $index++;

        if (is_null($record)) {
            $record = new \stdClass();
        } else {
            $record = (object)(array)$record;
        }

        if (!property_exists($record, 'name')) {
            $record->name = 'Test category '.$index;
        }
        if (!property_exists($record, 'sortorder')) {
            $record->sortorder = $index;
        }

        $id = $DB->insert_record('user_info_category', $record);
        return $DB->get_record('user_info_category', ['id'=>$id], '*', MUST_EXIST);
    }



    /**
     * Создание поля профиля (обновление при совпадении shortname)
     *
     * @param stdClass|array|null - объект с желаемыми свойствами поля
     * @return stdClass profile field record
     */
    public function create_profile_field($record=null) {
        global $DB;

        static $index = 0;
        $index++;

        if (is_null($record)) {
            $record = new \stdClass();
        } else {
            $record = (object)(array)$record;
        }

        if (!property_exists($record, 'datatype')) {
            $record->datatype = 'text';
        }
        if (!property_exists($record, 'categoryid')) {
            $record->categoryid = 1;
        }
        if (!property_exists($record, 'shortname')) {
            $record->shortname = 'tstField'.$index;
        }
        if (!property_exists($record, 'name')) {
            $record->name = 'Test field '.$index;
        }
        if (!property_exists($record, 'description')) {
            $record->description = 'This is a test field '.$index;
        }
        if (!property_exists($record, 'required')) {
            $record->required = false;
        }
        if (!property_exists($record, 'locked')) {
            $record->locked = false;
        }
        if (!property_exists($record, 'forceunique')) {
            $record->forceunique = false;
        }
        if (!property_exists($record, 'signup')) {
            $record->signup = false;
        }
        if (!property_exists($record, 'visible')) {
            $record->visible = '0';
        }

        $recordexist = $DB->get_record('user_info_field', ['shortname' => $record->shortname]);
        if ($recordexist) {
            $record->id = $recordexist->id;
            $DB->update_record('user_info_field', $record);
        } else {
            $DB->insert_record('user_info_field', $record);
        }

        return $DB->get_record('user_info_field', ['shortname' => $record->shortname], '*', MUST_EXIST);
    }

    /**
     * Создание глобальной группы. Если idnumber не указан, то он генерируется.
     * @param stdClass|array|null $record
     * @return stdClass cohort record
     */
    public function create_cohort_with_idnumber($record = null) {

        static $i = 0;
        $i++;

        if (is_null($record)) {
            $record = new \stdClass();
        } else {
            $record = (object)(array)$record;
        }

        if (!property_exists($record, 'name')) {
            $record->name = 'Cohort '.$i.' with idnumber';
        }

        if (!property_exists($record, 'idnumber')) {
            $record->idnumber = 'idnumber'.$i;
        }

        return $this->datagenerator->create_cohort($record);
    }

    public function create_table(ADOConnection $conn, $tablename, $sqlfields, $drop=true) {

        // https://adodb.org/dokuwiki/doku.php?id=v5:dictionary:createtablesql
        $dict = NewDataDictionary($conn);

        $conn->startTrans();

        if ($drop) {
            $droptable = $dict->dropTableSql($tablename);
            $dict->executeSqlArray($droptable);
        }

        $createtable = $dict->createTableSQL($tablename, $sqlfields);
        $dict->executeSqlArray($createtable);

        $conn->completeTrans();
    }

    /**
     * Если нет id - создает, если есть - обновляет запись пользователя
     * включая profile_field_ - поля
     * @param stdClass|array|null $record
     * @param array $options
     * @throws coding_exception
     * @return void|stdClass
     */
    public function save_user_data($record=null, array $options=null) {

        global $DB;

        if (is_null($record) || !array_key_exists('id', $record)) {
            $user = $this->datagenerator->create_user($record, $options);
        } else {

            $record = (array)$record;

            // Обычные пользовательские поля
            $userfields = array_filter($record, function($key){
                return strpos($key, 'profile_field_') !== 0;
            }, ARRAY_FILTER_USE_KEY);
            if (count($userfields) > 1) {
                user_update_user($userfields, false, false);
            }

            // Поля профиля
            $profilefields = array_filter($record, function($key){
                return strpos($key, 'profile_field_') === 0;
            }, ARRAY_FILTER_USE_KEY);
            if (!empty($profilefields)) {
                $profilefields['id'] = $record['id'];
                profile_save_data((object)$profilefields);
            }

            $user = $DB->get_record('user', ['id' => $record['id']]);
        }


        $fields = profile_get_user_fields_with_data($user->id);
        foreach ($fields as $formfield) {
            $user->{'profile_field_'.$formfield->field->shortname} = $formfield->data;
        }

        return $user;
    }
}