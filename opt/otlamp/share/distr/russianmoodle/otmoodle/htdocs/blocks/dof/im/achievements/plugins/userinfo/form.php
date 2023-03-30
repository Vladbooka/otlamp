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

/**
 * Плагин информации о пользователе. Классы форм.
 *
 * @package    im
 * @subpackage achievements
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Подключаем базовые функции плагина
require_once('lib.php');

// Подключаем библиотеку форм
$DOF->modlib('widgets')->webform();

/** 
 * Форма настройки фильтра
 */
class dof_im_achievements_userinfo_settingsform extends dof_modlib_widgets_form
{
    /**
     * @var dof_control
     */
    protected $dof;
    
    /**
     * @var $id - ID подразделения
     */
    protected $departmentid = 0;
    
    /**
     * @var $addvars - GET параметры для ссылки
     */
    protected $addvars = [];
    
    public function definition()
    {
        // Cоздаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        
        // Добавляем свойства
        $this->dof = $this->_customdata->dof;
        $this->departmentid = $this->_customdata->departmentid;
        $this->addvars = $this->_customdata->addvars;

        // Скрытые поля
        $mform->addElement('hidden', 'sesskey', sesskey());
        $mform->setType('sesskey', PARAM_ALPHANUM);
        $mform->addElement('hidden', 'departmentid', $this->departmentid);
        $mform->setType('departmentid', PARAM_INT);
        
        // Заголовок формы
        $mform->addElement(
                'header', 
                'form_header', 
                $this->dof->get_string('userinfo_settingsform_header_title', 'achievements')
        );
        
        // Поле для вывода сообщений об ошибках скрытых элементов
        $mform->addElement(
                'static',
                'hidden',
                ''
        );
        
        // Позиции элементов полей пользователя
        $positionselect = [
                        'main' => $this->dof->get_string('userinfo_settingsform_position_main', 'achievements'),
                        'hidden' => $this->dof->get_string('userinfo_settingsform_position_hidden', 'achievements')
        ];
        
        $url = $this->dof->im('achievements')->url('/plugins/userinfo/sortfields.php', $this->addvars);
        $mform->addElement('static', 'orderbutton', '', dof_html_writer::link($url, $this->dof->get_string('orderbutton', 'achievements'), ['class' => 'btn']));
        
        // Формирование набора полей персоны
        $personfields = $this->dof->storage('persons')->get_person_fieldnames();
        if ( ! empty($personfields) && is_array($personfields) )
        {// Поля персоны получены
            $personfieldsgroup = [];
        
            
            // Удаление системных полей
            unset($personfields['id']);
            unset($personfields['sortname']);
            unset($personfields['mdluser']);
            unset($personfields['sync2moodle']);
            unset($personfields['status']);
            unset($personfields['adddate']);
            unset($personfields['passportaddrid']);
            unset($personfields['birthaddressid']);
            unset($personfields['departmentid']);
            
            // Заголовок набора полей пользователя
            $mform->addElement(
                'static',
                'personfields_title',
                $this->dof->get_string('userinfo_settingsform_personfields_title', 'achievements')
            );
            
            foreach ( $personfields as $personfieldkey => $personfieldname )
            {
                if ( $personfieldkey == 'addressid' )
                {// Поле адреса проживания
                    $mform->addElement(
                        'html',
                        dof_html_writer::div(
                            dof_html_writer::tag(
                                'fieldset',
                                $this->dof->get_string('userinfo_settingsform_addressidinfo', 'achievements'),
                                ['class' => 'felement fgroup']
                                ),
                            'fitem fitem_fgroup femptylabel'
                            )
                        );
                    $customgroup = [];
                    // Добавить полея выбора значения
                    $customgroup[] = $mform->createElement(
                            'checkbox',
                            'postalcode',
                            '',
                            $this->dof->get_string('postalcode', 'addresses', NULL, 'storage')
                    );
                    $customgroup[] = $mform->createElement(
                            'select',
                            'position',
                            '',
                            $positionselect
                    );
                    $mform->addGroup($customgroup, 'personfields[addressid][postalcode]', '', ' ');
                    $mform->disabledIf('personfields[addressid][postalcode][position]', 'personfields[addressid][postalcode][postalcode]');
                    $customgroup = [];
                    $customgroup[] = $mform->createElement(
                            'checkbox',
                            'country',
                            '',
                            $this->dof->get_string('country', 'addresses', NULL, 'storage')
                    );
                    $customgroup[] = $mform->createElement(
                            'select',
                            'position',
                            '',
                            $positionselect
                    );
                    $mform->addGroup($customgroup, 'personfields[addressid][country]', '', ' ');
                    $mform->disabledIf('personfields[addressid][country][position]', 'personfields[addressid][country][country]');
                    $customgroup = [];
                    $customgroup[] = $mform->createElement(
                            'checkbox',
                            'region',
                            '',
                            $this->dof->get_string('region', 'addresses', NULL, 'storage')
                    );
                    $customgroup[] = $mform->createElement(
                            'select',
                            'position',
                            '',
                            $positionselect
                    );
                    $mform->addGroup($customgroup, 'personfields[addressid][region]', '', ' ');
                    $mform->disabledIf('personfields[addressid][region][position]', 'personfields[addressid][region][region]');
                    $customgroup = [];
                    $customgroup[] = $mform->createElement(
                            'checkbox',
                            'county',
                            '',
                            $this->dof->get_string('county', 'addresses', NULL, 'storage')
                    );
                    $customgroup[] = $mform->createElement(
                            'select',
                            'position',
                            '',
                            $positionselect
                    );
                    $mform->addGroup($customgroup, 'personfields[addressid][county]', '', ' ');
                    $mform->disabledIf('personfields[addressid][county][position]', 'personfields[addressid][county][county]');
                    $customgroup = [];
                    $customgroup[] = $mform->createElement(
                            'checkbox',
                            'city',
                            '',
                            $this->dof->get_string('city', 'addresses', NULL, 'storage')
                            );
                    $customgroup[] = $mform->createElement(
                            'select',
                            'position',
                            '',
                            $positionselect
                    );
                    $mform->addGroup($customgroup, 'personfields[addressid][city]', '', ' ');
                    $mform->disabledIf('personfields[addressid][city][position]', 'personfields[addressid][city][city]');
                    continue;
                }
                
                $customgroup = [];
                // Добавить поле выбора значения
                $customgroup[] = $mform->createElement(
                        'checkbox',
                        $personfieldkey,
                        '',
                        $personfieldname
                );
                $customgroup[] = $mform->createElement(
                            'select',
                            'position',
                            '',
                            $positionselect
                );
                $mform->addGroup($customgroup, 'personfields['.$personfieldkey.']', '', ' ');
                $mform->disabledIf('personfields['.$personfieldkey.'][position]', 'personfields['.$personfieldkey.']['.$personfieldkey.']');
            }
        }
        
        // Получение дополнительный полей пользователя
        $customfields = $this->dof->modlib('ama')->user(false)->get_user_custom_fields();
        if ( ! empty($customfields) && is_array($customfields) )
        {// Поля персоны получены
            $customfieldsgroup = [];
            
            // Заголовок набора полей пользователя
            $mform->addElement(
                    'static',
                    'customfields_title',
                    $this->dof->get_string('userinfo_settingsform_customfields_title', 'achievements')
                    );
            
            foreach ( $customfields as $customfield )
            {
                $customgroup = [];
                // Добавить поле выбора значения
                $customgroup[] = $mform->createElement(
                        'checkbox',
                        $customfield->shortname,
                        '',
                        $customfield->name
                );
                $customgroup[] = $mform->createElement(
                            'select',
                            'position',
                            '',
                            $positionselect
                );
                $mform->addGroup($customgroup, 'customfields['.$customfield->shortname.']', '', ' ');
                $mform->disabledIf('customfields['.$customfield->shortname.'][position]', 'customfields['.$customfield->shortname.']['.$customfield->shortname.']');
            }
        }
        
        //Получаем кастомные поля персон деканата
        $dofcustomfields = $this->dof->storage('customfields')->get_customfields($this->departmentid, ['linkpcode' => 'persons']);
        if( ! empty($dofcustomfields) && is_array($dofcustomfields) )
        {
            // Заголовок набора кастомных полей персоны
            $mform->addElement(
                'static',
                'dofcustomfields_title',
                $this->dof->get_string('userinfo_settingsform_dofcustomfields_title', 'achievements')
            );
            foreach($dofcustomfields as $dofcustomfield)
            {
                $dofcustomgroup = [];
                // Добавить поле выбора значения
                $dofcustomgroup[] = $mform->createElement(
                    'checkbox',
                    $dofcustomfield->code,
                    '',
                    $dofcustomfield->name
                );
                $dofcustomgroup[] = $mform->createElement(
                    'select',
                    'position',
                    '',
                    $positionselect
                );
                $mform->addGroup($dofcustomgroup, 'dofcustomfields['.$dofcustomfield->code.']', '', ' ');
                $mform->disabledIf('dofcustomfields['.$dofcustomfield->code.'][position]', 'dofcustomfields['.$dofcustomfield->code.']['.$dofcustomfield->code.']');
            }
        }
        
        // Заголовок для зачетной книжки
        $mform->addElement(
            'static',
            'dofrecordbook_title',
            $this->dof->get_string('userinfo_settingsform_dofrecordbook_title', 'achievements')
        );
        $recorbookgroup = [];
        foreach($this->get_recordbook_fields() as $recordbookfield)
        {
            $recorbookgroup[] = $mform->createElement(
                'checkbox', 
                $recordbookfield->code, 
                '', 
                $recordbookfield->name
            );
            $recorbookgroup[] = $mform->createElement(
                'select',
                'position',
                '',
                $positionselect
            );
            $mform->addGroup($recorbookgroup, 'recordbookfields['.$recordbookfield->code.']', '', ' ');
            $mform->disabledIf('recordbookfields['.$recordbookfield->code.'][position]', 'recordbookfields['.$recordbookfield->code.']['.$recordbookfield->code.']');
        }
        
        
        $group = [];
        $group[] = $mform->createElement('submit', 'submit', $this->dof->get_string('userinfo_settingsform_submit', 'achievements'));
        $group[] = $mform->createElement('submit', 'submitclose', $this->dof->get_string('userinfo_settingsform_submit_close', 'achievements'));
        $mform->addGroup($group, 'submit', '', '');
        
        // Применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
    }

    /** 
     * Проверка данных формы
     * 
     * @param array $data - данные, пришедшие из формы
     *
     * @return array - массив ошибок, или пустой массив, если ошибок нет
     */
    function validation($data, $files)
    {
        // Cоздаем ссылку на HTML_QuickForm
        $mform =& $this->_form;

        // Массив ошибок
        $errors = array();
        
        // Убираем лишние пробелы со всех полей формы
        $mform->applyFilter('__ALL__', 'trim');

        // Возвращаем ошибки, если они есть
        return $errors;
    }

    /**
     * Заполнение формы данными
     */
    function definition_after_data()
    {
        // Cоздаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        
        // Получение конфигурации фильтра
        $params = [
                        'departmentid' => $this->departmentid,
                        'code' => 'userinfo_fields',
                        'plugintype' => 'im',
                        'plugincode' => 'achievements'
        ];
        $configrecords = $this->dof->storage('config')->get_records($params);
        if ( ! empty($configrecords) )
        {
            // Получение значения конфигурации
            $configvalue = array_pop($configrecords)->value;
            $configvalue = unserialize($configvalue);
            if ( ! empty($configvalue) && is_array($configvalue) )
            {// Указаны группы полей
                foreach ( $configvalue as $position => $groupfields )
                {// Обработка каждой позиции
                    if ( ! empty($groupfields) && is_array($groupfields) )
                    {// В позиции указаны группы полей
                        foreach ( $groupfields as $groupname => $fields )
                        {//Обработка каждой группы полей
                            if ( ! empty($fields) && is_array($fields) )
                            {// В позиции указаны группы полей
                                foreach ( $fields as $fieldname => $fielddata )
                                {// Обработка каждой группы полей
                                    if ( $fieldname == 'addressid' )
                                    {
                                        if ( ! empty($fielddata) && is_array($fielddata) )
                                        {// В позиции указаны группы полей
                                            foreach ( $fielddata as $fielddataname => $fielddatavalue )
                                            {// Обработка каждой группы полей
                                                $mform->setDefault($groupname.'['.$fieldname.']['.$fielddataname.'][position]', $position);
                                                $mform->setDefault($groupname.'['.$fieldname.']['.$fielddataname.']['.$fielddataname.']', 1);
                                            }
                                        }
                                        continue;
                                    }
                                    $mform->setDefault($groupname.'['.$fieldname.']['.$fieldname.']', 1);
                                    $mform->setDefault($groupname.'['.$fieldname.'][position]', $position);
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    
    /** 
     * Обработать пришедшие из формы данные
     *
     * @return bool
     */
    public function process()
    {
        if ( $this->is_submitted() AND confirm_sesskey() AND $formdata = $this->get_data() )
        {
            // Формирование массива полей
            $fields = [];
            if ( isset($formdata->personfields) )
            {// Есть пользовательские поля
                if ( ! empty($formdata->personfields) )
                {
                    // Добавить каждое активное поле
                    foreach ( $formdata->personfields as $customfieldname => $data )
                    {
                        if ( $customfieldname == 'addressid' && ! empty($data) )
                        {// Поле адреса
                            foreach ( $data as $addresscode => $addressdata )
                            {
                                if ( isset($addressdata[$addresscode]) )
                                {// Поле активно
                                    if ( ! isset($fields[$addressdata['position']]['personfields']['addressid']) )
                                {
                                    $fields[$addressdata['position']]['personfields']['addressid'] = [];
                                }
                                $fields[$addressdata['position']]['personfields']['addressid'][$addresscode] = 1;
                            }
                            }
                            continue;
                        }
                        
                        if ( isset($data[$customfieldname]) )
                        {// Поле активно
                            $fields[$data['position']]['personfields'][$customfieldname] = 1;
                        }
                    }
                }
            }
            if ( isset($formdata->customfields) )
            {// Есть пользовательские поля
                if ( ! empty($formdata->customfields) )
                {
                    // Добавить каждое активное поле
                    foreach ( $formdata->customfields as $customfieldname => $data )
                    {
                        if ( isset($data[$customfieldname]) )
                        {// Поле активно
                            $fields[$data['position']]['customfields'][$customfieldname] = 1;
                        }
                    }
                }
            }
            
            if ( isset($formdata->dofcustomfields) )
            {// Есть кастомные поля персон
                if ( ! empty($formdata->dofcustomfields) )
                {
                    // Добавить каждое активное поле
                    foreach ( $formdata->dofcustomfields as $dofcustomfieldname => $data )
                    {
                        if ( isset($data[$dofcustomfieldname]) )
                        {// Поле активно
                            $fields[$data['position']]['dofcustomfields'][$dofcustomfieldname] = 1;
                        }
                    }
                }
            }
            
            if ( isset($formdata->recordbookfields) )
            {// Есть поля зачетной книжки
                if ( ! empty($formdata->recordbookfields) )
                {
                    // Добавить каждое активное поле
                    foreach ( $formdata->recordbookfields as $recordbookfieldname => $data )
                    {
                        if ( isset($data[$recordbookfieldname]) )
                        {// Поле активно
                            $fields[$data['position']]['recordbookfields'][$recordbookfieldname] = 1;
                        }
                    }
                }
            }
            
            // Проверка на существование конфигурации блока
            $params = [
                            'departmentid' => $formdata->departmentid,
                            'code' => 'userinfo_fields',
                            'plugintype' => 'im',
                            'plugincode' => 'achievements'
            ];
            $config = new stdClass();
            $config->departmentid = $formdata->departmentid;
            $config->code = 'userinfo_fields';
            $config->plugintype = 'im';
            $config->plugincode = 'achievements';
            $config->type = 'text';
            
            $configrecords = $this->dof->storage('config')->get_records($params);
            if ( empty($configrecords) )
            {// Конфигурация не найдена
                $config->value = serialize($fields);
                // Добавить значение
                $result = $this->dof->storage('config')->insert($config);
            } else 
            {
                // Получение ID конфигурации
                $configrecord = array_pop($configrecords);
                $id = $configrecord->id;
                if ( count($configrecords) > 1 )
                {// Найдены дубли
                    foreach ( $configrecords as $record )
                    {// Удаление дубля
                        $this->dof->storage('config')->delete($record->id);
                    }
                }
                // Сохраним сортировку старых полей
                $oldconfigvalue = unserialize($configrecord->value);
                if( ! empty($oldconfigvalue) )
                {
                    foreach($oldconfigvalue as $position => $elements)
                    {
                        foreach($elements as $groupfield => $oldfields)
                        {
                            foreach($oldfields as $fieldcode => $fielddata)
                            {
                                if( isset($fields[$position][$groupfield][$fieldcode]) )
                                {
                                    if ( $fieldcode == 'addressid')
                                    { 
                                        foreach ( $fielddata as $addresskey => $addressvalue )
                                        {
                                            if ( isset($fields[$position][$groupfield][$fieldcode][$addresskey]) )
                                            {
                                                $fields[$position][$groupfield][$fieldcode][$addresskey] = $addressvalue;
                                            }
                                        }
                                    }else{
                                    // Если сохраняемое поле есть в текущей настройке, передадим в него индекс сортировки
                                    $fields[$position][$groupfield][$fieldcode] = $fielddata;
                                }
                            }
                        }
                    }
                }
                }
                $config->value = serialize($fields);
                $result = $this->dof->storage('config')->update($config, $id);
            }
            if ( ! empty($result) )
            {
                if ( isset($formdata->submit['submit']) )
                {// Сохранение без перехода на страницу верхнего уровня
                    $this->addvars['settingssavesuccess'] = '1';
                    redirect($this->dof->url_im('achievements', '/plugins/userinfo/settings.php', $this->addvars));
                }
                if ( isset($formdata->submit['submitclose']) )
                {// Сохранение с переходом на страницу верхнего уровня
                    $this->addvars['settingssavesuccess'] = '1';
                    redirect($this->dof->url_im('achievements', '/plugins/index.php', $this->addvars));
                }
            } else
            {
                $this->errors[] = $this->dof->get_string('error_im_achievements_userinfo_settingsform_save', 'achievements');
            }
        }
    }
    
    private function get_recordbook_fields()
    {
        $enable = new stdClass();
        $enable->code = 'link';
        $enable->name = $this->dof->get_string('recordbook_link', 'achievements');
        $fields[] = $enable;
        return $fields;
    }
}