<?php
////////////////////////////////////////////////////////////////////////////
//                                                                        //
// NOTICE OF COPYRIGHT                                                    //
//                                                                        //
// Dean`s Office for Moodle                                               //
// Электронный деканат                                                    //
// <http://deansoffice.ru/>                                               //
//
// This program is free software: you can redistribute it and/or modify   //
// it under the terms of the GNU General Public License as published by   //
// the Free Software Foundation, either version 3 of the Licensen.        //
//                                                                        //
// This program is distributed in the hope that it will be useful,        //
// but WITHOUT ANY WARRANTY; without even the implied warranty of         //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the          //
// GNU General Public License for more details.                           //
//                                                                        //
// You should have received a copy of the GNU General Public License      //
// along with this program.  If not, see <http://www.gnu.org/licenses/>.  //
//                                                                        //
////////////////////////////////////////////////////////////////////////////

/**
 * Обмен данных с внешними источниками. Класс источника типа userdata
 *
 * @package    modlib
 * @subpackage transmit
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class dof_modlib_transmit_source_moodle_userdata extends dof_modlib_transmit_source_moodle
{
    /**
     * Поддержка импорта
     *
     * @return bool
     */
    public static function support_import()
    {
        return true;
    }
    
    /**
     * Поддержка экспорта
     *
     * @return bool
     */
    public static function support_export()
    {
        return false;
    }
    
    
    protected function get_fields()
    {
        if( ! empty($this->datafields) )
        {
            return $this->datafields;
        } else
        {
            $fields = [];
            
            // объект для обращения к методам ama_user без привязки к пользователю
            $fakeamauser = $this->dof->modlib('ama')->user(false);
            
            $addfields = [
                'lastname' => $this->dof->get_string('source_moodle_field_lastname', 'transmit', null, 'modlib'),
                'firstname' => $this->dof->get_string('source_moodle_field_firstname', 'transmit', null, 'modlib'),
                'middlename' => $this->dof->get_string('source_moodle_field_middlename', 'transmit', null, 'modlib')
            ];
            // список стандартных полей пользователя
            $userfields = $fakeamauser->get_userfields_list($addfields);
            if( ! empty($userfields) )
            {
                foreach ($userfields as $userfieldcode => $userfielddisplayname)
                {
                    $fields['user_field_'.$userfieldcode] = $userfielddisplayname;
                }
            }
            
            // список настраиваемых полей профиля пользователя
            $profilefields = $fakeamauser->get_user_custom_fields();
            if( ! empty($profilefields) )
            {
                foreach ($profilefields as $profilefield)
                {
                    $fields['user_profilefield_'.$profilefield->shortname] = $profilefield->name;
                }
            }
            
            return $fields;
        }
    }
    
    /**
     * Получить текущий элемент из БД и преобразовать поля
     *
     * @return array
     */
    protected function get_element() {
        
        // Заполнение данными элемента для обмена
        $transmitdata = [];
        
        $fakeamauser = $this->dof->modlib('ama')->user(false);
        
        $filters = $this->get_configitem('filters');
        
        // получение текущего пользователя, согласно итератору
        $users = $fakeamauser->fullsearch($filters, 'id DESC', $this->row_counter, 1);
        
        if( ! empty($users) )
        {// пользователь найден
            $user = array_shift($users);
            // получение данных пользователя
            $userdata = $fakeamauser->get_not_validated_fields_data($user, array_keys($this->datafields));
            foreach(array_keys($this->datafields) as $fieldcode)
            {
                if( isset($userdata[$fieldcode]) && isset($userdata[$fieldcode]->value) )
                {
                    // по умолчанию используется хранимое значение
                    $transmitdata[$this->datafields[$fieldcode]] = $userdata[$fieldcode]->value;
                    
                    if( ! empty($userdata[$fieldcode]->type) && isset($userdata[$fieldcode]->displayvalue) )
                    {// значение задано и у него определен тип данных
                        switch($userdata[$fieldcode]->type)
                        {
                            // дата должна быть в формате, который способен обработать strtotime
                            case 'datetime':
                                $transmitdata[$this->datafields[$fieldcode]] = '@'.$userdata[$fieldcode]->value;
                                break;
                            default: 
                                break;
                        }
                    }
                }
            }
        }
        
        if ( empty($transmitdata) )
        {
            return [];
        }
        
        return $transmitdata;
    }
}