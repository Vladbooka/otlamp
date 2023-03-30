<?php
////////////////////////////////////////////////////////////////////////////
//                                                                        //
// NOTICE OF COPYRIGHT                                                    //
//                                                                        //
// Dean`s Office for Moodle                                               //
// Электронный деканат                                                    //
// <http://deansoffice.ru/>                                               //
//                                                                        //
//                                                                        //
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

// Подключение библиотеки
global $DOF;
require_once($DOF->plugin_path('storage','achievements','/classes/userform.php'));
require_once($DOF->plugin_path('storage','achievements','/classes/settingsform.php'));

/**
 * Форма дополнительных настроек шаблона достижения Simple
 * 
 * @package    storage
 * @subpackage achievements
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/** 
 * Форма создания/редактирования разделов
 */
class dof_storage_simple_settings_form extends dof_storage_achievement_form
{
    /**
     * @param MoodleQuickForm $mform
     */
    protected function definition_ext(&$mform)
    {
        // Скрытые поля
        $mform->addElement('hidden', 'sesskey', sesskey());
        $mform->setType('sesskey', PARAM_ALPHANUM);
        $mform->addElement('hidden', 'id', $this->id);
        $mform->setType('id', PARAM_INT);
        
        // Заголовок формы
        $mform->addElement(
                'header', 
                'simple_settings_form_title', 
                $this->dof->get_string('simple_settings_form_title', 'achievements', null, 'storage')
        );
        
        // Добавить критерий
        $group = [];
        $types = $this->get_criteria_types();
        $group[] = $mform->createElement(
                'select', 
                'addcriteriatype', 
                '',
                $types
        );
        $group[] = $mform->createElement(
                'submit', 
                'addcriteriasubmit',
                $this->dof->get_string('simple_settings_form_addcriteriasubmit', 'achievements', null, 'storage')
        );
        $mform->addGroup(
                $group, 
                'addcriteriagroup', 
                $this->dof->get_string('simple_settings_form_addcriteriatype', 'achievements', null, 'storage')
        );
        
        // Текущие добавленные критерии
        if ( isset($this->data['simple_data']) )
        {// Критерии есть
            foreach ( $this->data['simple_data'] as $key => $criteria )
            {
                switch ( $criteria->type )
                {
                    
                    case 'text' :
                        $mform->addElement('hidden', 'criteria'.$key.'_type', 'text');
                        
                        $mform->closeHeaderBefore('criteria'.$key.'_header');
                        $mform->addElement(
                            'header', 
                            'criteria'.$key.'_header', 
                            $this->dof->get_string('simple_settings_form_type_text', 'achievements', null, 'storage')
                        );
                        
                        $mform->addElement(
                                'text',
                                'criteria'.$key.'_name',
                                $this->dof->get_string('simple_settings_form_criteria_name', 'achievements', null, 'storage')
                        );
                        if ( $this->ratingenabled )
                        {// Подсистема рейтинга включена
                            $mform->addElement(
                                'text',
                                'criteria'.$key.'_rate',
                                $this->dof->get_string('simple_settings_form_criteria_rate', 'achievements', null, 'storage')
                            );
                        } else
                        {// Подсистема отключена - коэфициент не требуется
                            $mform->addElement('hidden', 'criteria'.$key.'_rate', 0.0);
                        }
                        if ( $this->moderationenabled )
                        {// Подсистема модерации включена
                            $mform->addElement(
                                'selectyesno',
                                'criteria'.$key.'_significant',
                                $this->dof->get_string('simple_settings_form_criteria_significant', 'achievements', null, 'storage')
                            );
                        } else
                        {// Подсистема отключена - модерация не требуется
                            $mform->addElement('hidden', 'criteria'.$key.'_significant', 0);
                        }
                        $mform->addElement(
                            'submit',
                            'criteria'.$key.'_delete',
                            $this->dof->get_string('simple_settings_form_delete', 'achievements', null, 'storage')
                            );
                        $mform->setType('criteria'.$key.'_type', PARAM_TEXT);
                        $mform->setType('criteria'.$key.'_name', PARAM_TEXT);
                        $mform->setType('criteria'.$key.'_significant', PARAM_INT);
                        $mform->setType('criteria'.$key.'_rate', PARAM_RAW);
                        $mform->setExpanded('criteria'.$key.'_header', true);
                        break;
                    case 'data' :
                        $mform->closeHeaderBefore('criteria'.$key.'_header');
                        $mform->addElement('hidden', 'criteria'.$key.'_type', 'data');
                        $mform->addElement(
                                'header',
                                'criteria'.$key.'_header',
                                $this->dof->get_string('simple_settings_form_type_data', 'achievements', null, 'storage')
                        );
                        
                        $mform->addElement(
                                'text',
                                'criteria'.$key.'_name',
                                $this->dof->get_string('simple_settings_form_criteria_name', 'achievements', null, 'storage')
                        );
                        
                        if ( $this->ratingenabled )
                        {// Подсистема рейтинга включена
                            $mform->addElement(
                                'text',
                                'criteria'.$key.'_rate',
                                $this->dof->get_string('simple_settings_form_criteria_rate', 'achievements', null, 'storage')
                            );
                        } else
                        {// Подсистема отключена - коэфициент не требуется
                            $mform->addElement('hidden', 'criteria'.$key.'_rate', 0.0);
                        }
                        if ( $this->moderationenabled )
                        {// Подсистема модерации включена
                            $mform->addElement(
                                'selectyesno',
                                'criteria'.$key.'_significant',
                                $this->dof->get_string('simple_settings_form_criteria_significant', 'achievements', null, 'storage')
                            );
                        } else
                        {// Подсистема отключена - модерация не требуется
                            $mform->addElement('hidden', 'criteria'.$key.'_significant', 0);
                        }
                        $mform->addElement(
                            'submit',
                            'criteria'.$key.'_delete',
                            $this->dof->get_string('simple_settings_form_delete', 'achievements', null, 'storage')
                        );
                        $mform->setType('criteria'.$key.'_type', PARAM_TEXT);
                        $mform->setType('criteria'.$key.'_name', PARAM_TEXT);
                        $mform->setType('criteria'.$key.'_significant', PARAM_INT);
                        $mform->setType('criteria'.$key.'_rate', PARAM_RAW);
                        $mform->setExpanded('criteria'.$key.'_header', true);
                        break;
                    case 'file' :
                        $mform->closeHeaderBefore('criteria'.$key.'_header');
                        $mform->addElement('hidden', 'criteria'.$key.'_type', 'file');
                        $mform->addElement(
                            'header',
                            'criteria'.$key.'_header',
                            $this->dof->get_string('simple_settings_form_type_file', 'achievements', null, 'storage')
                        );
                            
                        $mform->addElement(
                            'text',
                            'criteria'.$key.'_name',
                            $this->dof->get_string('simple_settings_form_criteria_name', 'achievements', null, 'storage')
                        );
                    
                        if ( $this->ratingenabled )
                        {// Подсистема рейтинга включена
                            $mform->addElement(
                                'text',
                                'criteria'.$key.'_rate',
                                $this->dof->get_string('simple_settings_form_criteria_rate', 'achievements', null, 'storage')
                            );
                        } else
                        {// Подсистема отключена - коэфициент не требуется
                            $mform->addElement('hidden', 'criteria'.$key.'_rate', 0.0);
                        }
                        if ( $this->moderationenabled )
                        {// Подсистема модерации включена
                            $mform->addElement(
                                'selectyesno',
                                'criteria'.$key.'_significant',
                                $this->dof->get_string('simple_settings_form_criteria_significant', 'achievements', null, 'storage')
                            );
                        } else
                        {// Подсистема отключена - модерация не требуется
                            $mform->addElement('hidden', 'criteria'.$key.'_significant', 0);
                        }
                        // Блок настроек плагиаризма
                        $mform->addElement(
                            'html',
                            dof_html_writer::tag(
                                'h5', 
                                $this->dof->get_string('simple_settings_form_type_file_plagiarism_header', 'achievements', null, 'storage')
                            )
                        );
                        // Получение включенных плагинов плагиаризма
                        $plugins = $this->dof->sync('achievements')->get_plagiarism_plugins_code();
                        foreach ( $plugins as $plugincode => $plugin )
                        {
                            // Блок настроек плагиаризма
                            $mform->addElement(
                                'html',
                                dof_html_writer::tag(
                                    'h6',
                                    $this->dof->sync('achievements')->get_plagiarism_plugin_name($plugincode)
                                )
                            );
                            $mform->addElement(
                                'selectyesno',
                                'criteria'.$key.'_plagiarism_'.$plugincode.'_addtoindex',
                                $this->dof->get_string('simple_settings_form_criteria_plagiarism_add_to_index', 'achievements', null, 'storage')
                            );
                        }
                        
                        $mform->addElement(
                            'submit',
                            'criteria'.$key.'_delete',
                            $this->dof->get_string('simple_settings_form_delete', 'achievements', null, 'storage')
                        );
                        $mform->setType('criteria'.$key.'_type', PARAM_TEXT);
                        $mform->setType('criteria'.$key.'_name', PARAM_TEXT);
                        $mform->setType('criteria'.$key.'_significant', PARAM_INT);
                        $mform->setType('criteria'.$key.'_rate', PARAM_RAW);
                        $mform->setExpanded('criteria'.$key.'_header', true);
                        break;
                    case 'select' :
                        $mform->closeHeaderBefore('criteria'.$key.'_header');
                        $mform->addElement('hidden', 'criteria'.$key.'_type', 'select');
                        $mform->addElement(
                                'header',
                                'criteria'.$key.'_header',
                                $this->dof->get_string('simple_settings_form_type_select', 'achievements', null, 'storage')
                        );
                        
                        $mform->addElement(
                                'text',
                                'criteria'.$key.'_name',
                                $this->dof->get_string('simple_settings_form_criteria_name', 'achievements', null, 'storage')
                        );
                        if ( $this->moderationenabled )
                        {// Подсистема модерации включена
                            $mform->addElement(
                                'selectyesno',
                                'criteria'.$key.'_significant',
                                $this->dof->get_string('simple_settings_form_criteria_significant', 'achievements', null, 'storage')
                            );
                        } else
                        {// Подсистема отключена - модерация не требуется
                            $mform->addElement('hidden', 'criteria'.$key.'_significant', 0);
                        }
                        // Добавить элемент списка
                        $mform->addElement(
                                'submit',
                                'criteria'.$key.'_addoptsubmit',
                                $this->dof->get_string('simple_settings_form_addselectoptsubmit', 'achievements', null, 'storage')
                        );
                        $mform->addElement(
                            'submit',
                            'criteria'.$key.'_delete',
                            $this->dof->get_string('simple_settings_form_delete', 'achievements', null, 'storage')
                        );
                        if ( isset($criteria->options) && ! empty($criteria->options) )
                        {// Определены элементы списка
                            foreach ( $criteria->options as $okey => $option )
                            {
                                $mform->addElement(
                                        'static',
                                        NULL,
                                        NULL,
                                        '<hr>'
                                );
                                $mform->addElement(
                                        'text',
                                        'criteria'.$key.'_option'.$okey.'_name',
                                        $this->dof->get_string('simple_settings_form_criteria_name', 'achievements', null, 'storage')
                                );
                                if ( $this->ratingenabled )
                                {// Подсистема рейтинга включена
                                    $mform->addElement(
                                        'text',
                                        'criteria'.$key.'_option'.$okey.'_rate',
                                        $this->dof->get_string('simple_settings_form_criteria_rate', 'achievements', null, 'storage')
                                    );
                                } else
                                {// Подсистема отключена - коэфициент не требуется
                                    $mform->addElement('hidden', 'criteria'.$key.'_option'.$okey.'_rate', 0.0);
                                }
                                
                                $mform->setType('criteria'.$key.'_option'.$okey.'_name', PARAM_TEXT);
                                $mform->setType('criteria'.$key.'_option'.$okey.'_rate', PARAM_RAW);
                            }
                        }
                        $mform->setType('criteria'.$key.'_type', PARAM_TEXT);
                        $mform->setType('criteria'.$key.'_name', PARAM_TEXT);
                        $mform->setType('criteria'.$key.'_significant', PARAM_INT);
                        $mform->setExpanded('criteria'.$key.'_header', true);
                        break;
                    default:
                        break;
                }
            }
        }
        $mform->closeHeaderBefore('submit');
        $mform->addElement(
                'submit', 
                'submit', 
                $this->dof->get_string('simple_settings_form_save', 'achievements', null, 'storage')
        );
        
        // Применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
    }

    /**
     * Заполнение формы данными
     */
    protected function definition_after_data_ext(&$mform)
    {
        if ( isset($this->data['simple_data']) && ! empty($this->data['simple_data']) )
        {// Критерии есть
            foreach ( $this->data['simple_data'] as $id => $criteria )
            {// Для каждого критерия заполним данные
                if ( empty( $criteria ) )
                {// Критерий не определен
                    continue;
                }
                foreach ( $criteria as $field => $value )
                {
                    if ( $field == 'options' && ! empty($field) )
                    {// Массив значений для выпадающего списка
                        foreach ( $value as $fid => $fval )
                        {// Свойства каждого элемента
                            if ( ! empty($fval) )
                            {
                                foreach ( $fval as $foption  => $foptionvalue  )
                                {
                                    $mform->setDefault('criteria'.$id.'_option'.$fid.'_'.$foption, $foptionvalue);
                                }
                            }
                        }
                        continue;
                    }
                    $mform->setDefault('criteria'.$id.'_'.$field, $value);
                }
            }
        }
    }
    
    /** 
     * Обработать пришедшие из формы данные
     *
     * @return bool
     */
    protected function process_ext($formdata)
    {
        // сбор данных о критериях
        $data['simple_data'] = $this->criteria_data($formdata);
        return $data;
    }
    
    
    /**
     * Получить массив критериев
     * 
     * @return array
     */
    private function get_criteria_types()
    {
        $types = [];
        $types['text'] = $this->dof->get_string('simple_settings_form_type_text', 'achievements', null, 'storage');
        $types['data'] = $this->dof->get_string('simple_settings_form_type_data', 'achievements', null, 'storage');
        $types['file'] = $this->dof->get_string('simple_settings_form_type_file', 'achievements', null, 'storage');
        $types['select'] = $this->dof->get_string('simple_settings_form_type_select', 'achievements', null, 'storage');
        
        return $types;
    }

    /**
     * Привести к Float
     * 
     * @param string $str - Значение в виде строки
     * 
     * @return float - Очищенное значение
     */
    private function str_to_float($str)
    {
        $string = (string) $str;
        if( strstr($string, ",") ) 
        {// Найдены запятые
            $str = str_replace(".", "", $str);
            $str = str_replace(",", ".", $str);
        } 
  
        if( preg_match("#([0-9\.]+)#", $str, $match) ) 
        { 
            return floatval($match[0]); 
        } else { 
            return floatval($str); 
        } 
    }
    
    
    /**
     * Сформировать массив критериев из данных формы
     * 
     * Формат массива
     * [
     *      [ID критерия] => stdClass Object
     *      (
     *           [Свойство критерия] => Значение
     *      ]
     * ]
     * 
     * @param unknown $formdata
     * @return string|multitype:stdClass
     */
    private function criteria_data($formdata)
    {
        if ( empty($formdata) )
        {
            return '';
        }
        // Массив задач на постобработку
        $tasks = [];
        
        // Массив критериев
        $criterias = [];
        // Обработка полей критериев
        foreach( $formdata as $field => $value )
        {
            $str = substr($field, 0, 8);
            if ( $str == 'criteria' )
            {// Данные критерия
                // Определим id критерия
                $id = preg_replace( '/criteria(\d+)_.+/i' , '$1', $field );
                // Определим опцию критерия
                $option = preg_replace( '/criteria\d+_(.+)/i' , '$1', $field );
                if ( ! isset($criterias[$id]) )
                {// Объявим критерий
                    $criterias[$id] = new stdClass();
                }
                
                // Является ли поле свойством элемента выпадающего списка
                $is_selectoption = preg_match( '/option\d+_.+/i' , $option );
                if ( ! empty($is_selectoption) )
                {// Свойство элемента выпадающего списка
                    // Определим id элемента
                    $sid = preg_replace( '/option(\d+)_.+/i' , '$1', $option );
                    // Определим опцию выпадающего списка
                    $soption = preg_replace( '/option\d+_(.+)/i' , '$1', $option );
                    
                    if ( ! isset($criterias[$id]->options) )
                    {// Массив элементов для данного выпадающего списка не установлен
                        $criterias[$id]->options = [];
                    }
                    
                    if ( ! isset($criterias[$id]->options[$sid]) )
                    {// Элемент списка не объявлен
                        $criterias[$id]->options[$sid] = new stdClass();
                    }
                    
                    if ( $soption == 'rate' )
                    {// Критерий коэффициента
                        // Преобразуем в float
                        $criterias[$id]->options[$sid]->$soption = $this->str_to_float($value);
                        continue;
                    }
                    // Добавить свойство элемента списка
                    $criterias[$id]->options[$sid]->$soption = $value;
                    continue;
                }
                
                if ( $option == 'delete' )
                {// Удалить критерий
                    // Удаление только после завершения построения массива
                    $tasks[] = [
                                'task' => 'delete',
                                'id' => $id
                    ];
                    continue;
                }
                
                if ( $option == 'addoptsubmit' )
                {// Добавить элемент выпадающего списка
                    // Создание новых элементов только после завершения построения массива
                    $tasks[] = [
                                    'task' => 'addoptsubmit',
                                    'id' => $id
                    ];
                    continue;
                }
                
                if ( $option == 'rate' )
                {// Критерий коэффициента
                    // Преобразуем в float
                    $criterias[$id]->$option = $this->str_to_float($value);
                    continue;
                }
                $criterias[$id]->$option = $value;
            }
        }
        
        if ( isset($formdata->addcriteriagroup['addcriteriasubmit']) )
        {// Добавление элемента критерия
            $add = new stdClass();
            $add->type = $formdata->addcriteriagroup['addcriteriatype'];
            $criterias[] = $add;
        }
        
        // Постобработка
        if ( ! empty($tasks) )
        {// Есть задачи
            foreach ( $tasks as $task )
            {// Обработка каждой задачи
                switch ( $task['task'] )
                {// Действия в завсимости от задачи
                    case 'addoptsubmit' :
                        if ( ! isset($criterias[$task['id']]->options) )
                        {// Массив элементов для данного выпадающего списка не установлен
                            $criterias[$task['id']]->options = [];
                        }
                        // Добавить элемент выпадающего списка
                        $criterias[$task['id']]->options[] = new stdClass();
                        
                        break;
                    case 'delete' :
                        unset( $criterias[$task['id']] );
                            break;
                    default :
                        break;
                }
            }
        }
        return $criterias;
    }
}

/**
 * Форма создания/редактирования данных пользователя
 */
class dof_storage_simple_user_form extends dof_storage_achievementin_form
{
    
    protected function definition_ext(&$mform)
    {
        // Заголовок формы
        $mform->addElement(
            'header',
            'simple_user_form_title',
            $this->achievement->name
        );
        
        // Временная зона текущего пользователя
        $usertimezone = $this->dof->storage('persons')->get_usertimezone_as_number();
        $dataoptions = [];
        $dataoptions['timezone'] = $usertimezone;
        
        if ( isset($this->data['simple_data']) )
        {// Определены критерии
            foreach ( $this->data['simple_data'] as $key => $criteria )
            {
                switch ( $criteria->type )
                {
                    // Текстовое поле
                    case 'text' :
                        $mform->addElement(
                                'text',
                                'simple'.$key.'_value',
                                $criteria->name
                        );
                        $mform->setType('simple'.$key.'_value', PARAM_RAW_TRIMMED);
                        break;
                    // Поле выбора даты
                    case 'data' :
                        $mform->addElement(
                                'dof_date_selector',
                                'simple'.$key.'_value',
                                $criteria->name,
                                $dataoptions
                        );
                        break;
                    // Выпадающий список
                    case 'select' :
                        $select = [];
                        if ( isset($criteria->options) && ! empty($criteria->options) )
                        {// Определены элементы списка
                            // Заполнение
                            foreach ( $criteria->options as $okey => $option )
                            {
                                $select[$okey] = $option->name;
                            }
                        }
                        // Данные поля
                        $mform->addElement(
                                'select',
                                'simple'.$key.'_value',
                                $criteria->name,
                                $select
                        );
                        $mform->setType('simple'.$key.'_value', PARAM_INT);
                        break;
                    // Загрузка файлов
                    case 'file' :
                        $name = 'simple'.$key.'_value_filemanager';
                        if ( isset($this->userdata['simple'.$key.'_value']) )
                        {// ID поля определен
                            $itemid = $this->userdata['simple'.$key.'_value'];
                        } else 
                        {
                            $itemid = NULL;
                        }            
                        
                        // Подготовка файлового менеджера
                        $this->userdata[$name] = $this->dof->modlib('filestorage')->definion_filemanager($name, $itemid);
                        $mform->addElement(
                                'filemanager',
                                $name,
                                $criteria->name ?? '',
                                null,
                                ['maxfiles' => 1, 'subdirs' => false]
                        );
                        break;
                    default :
                        break;
                }
            }
        }
        
        $group = [];
        $group[] = $mform->createElement(
            'submit',
            'submitclose',
            $this->dof->get_string('simple_user_form_save_close', 'achievements', null, 'storage')
            );
        $mform->addGroup($group, 'submit', '', '');
    }

    /**
     * Проверка данных формы
     *
     * @param array $data - данные, пришедшие из формы
     *
     * @return array - массив ошибок, или пустой массив, если ошибок нет
     */
    protected function validation_ext($data, $files)
    {
        // Массив ошибок
        $errors = [];

        // Возвращаем ошибки, если они есть
        return $errors;
    }

    /**
     * Обработать пришедшие из формы данные
     *
     * @return bool
     */
    protected function process_ext($formdata)
    {
        $userdata = [];
        if ( isset($this->data['simple_data']) )
        {// Определены критерии
            foreach ( $this->data['simple_data'] as $key => $criteria )
            {
                switch ( $criteria->type )
                {
                    // Текстовое поле
                    case 'text' :
                        $name = 'simple'.$key.'_value';
                        $userdata[$name] = $formdata->$name;
                        break;
                        // Поле выбора даты
                    case 'data' :
                        $name = 'simple'.$key.'_value';
                        $data = $formdata->$name;
                        $userdata[$name] = $data['timestamp'];
                        break;
                        // Выпадающий список
                    case 'select' :
                        $name = 'simple'.$key.'_value';
                        $value = $formdata->$name;
                        if ( isset($criteria->options[$value]->name) )
                        {// Определены элементы списка
                            $userdata[$name] = $value;
                        }
                        break;
                        // Загрузка файлов
                    case 'file' :
                        $name = 'simple'.$key.'_value';
                        $fname = 'simple'.$key.'_value_filemanager';
                        $draftitemid = $formdata->$fname;
                        if ( isset($this->userdata['simple'.$key.'_value']) )
                        {// ID поля определен
                            $itemid = $this->userdata['simple'.$key.'_value'];
                        } else
                        {
                            $itemid = NULL;
                        }
                        // Обработчик файлового менеджера
                        $itemid = $this->dof->modlib('filestorage')->process_filemanager($fname, $draftitemid, $itemid);
                        if ( ! empty($itemid) )
                        {// Файлы сохранены
                            $userdata[$name] = $itemid;
                        }
                        break;
                    default :
                        break;
                }
            }
        }
        return $userdata;
    }
}
?>