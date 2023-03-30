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
 * Обмен данных с внешними(?) источниками. Базовый класс moodle-источников данных.
 *
 * @package    modlib
 * @subpackage transmit
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class dof_modlib_transmit_source_moodle extends dof_modlib_transmit_source_base implements Iterator
{    
    /**
     * Текущая строка итератора
     *
     * @var int
     */
    protected $row_counter = 0;
    
    /**
     * Текущая строка итератора
     *
     * @var array
     */
    protected $current_element = [];
    
    /**
     * Уведомление источнику о том, что запись обработана
     * Используется для обновления записи в фреймворке синхронизаций
     *
     * @param array $item
     * @param int $downid - внутренний идентификатор
     *
     * @return void
     */
    public function record_processed($item = [], $downid = null)
    {
        if ( $this->pack_mode && is_numeric($downid) )
        {
            // конфиги пакета
            $packconfig = $this->get_configitem('pack_config');
            
            // получение объекта подключения к фреймворку синхронизаций
            $connection = $this->get_sync_connection();
            
            // получение полей для вычисления хеша
            $hashfields = array_intersect_key($this->current_element, array_flip($packconfig['uphashfields']));
            
            if ( ! empty($hashfields) && ! empty($this->current_element['__main_sync_upid']) )
            {
                // проверка, что есть поля, по по котором необходимо считать хеш
                // и что в пуле лежит служебное поле с идентификатором
                $uphash = $this->dof->storage('sync')->makeHash((object)$hashfields);
                
                // получение записи из реестра синхронизации
                $syncrecord = $connection->getSync(['upid' => $this->current_element['__main_sync_upid']]);
                
                if ( ! empty($syncrecord) )
                {
                    if ( $syncrecord->downid != $downid )
                    {
                        $this->dof->storage('sync')->delete($syncrecord->id);
                        if ( ! empty($downid) )
                        {
                            // создание новой записи
                            $connection->updateDown($this->current_element['__main_sync_upid'], 'create', $uphash, $downid);
                        }
                    } else 
                    {
                        $connection->updateDown($this->current_element['__main_sync_upid'], 'update', $uphash, $downid);
                    }
                } else 
                {
                    // обновление записи
                    $connection->updateDown($this->current_element['__main_sync_upid'], 'create', $uphash, $downid);
                }
            }
        }
    }
    
    /** РЕАЛИЗАЦИЯ ИТЕРАТОРА **/
    
    /**
     * Iterator next()
     *
     * @return void
     */
    public function next()
    {
        $this->row_counter++;
    }
    
    /**
     * Iterator valid()
     *
     * @return bool
     */
    public function valid()
    {
        $obj = $this->get_element();
        if ( ! empty($obj) )
        {
            return true;
        }
        
        return false;
    }
    
    /**
     * Iterator current()
     *
     * @return array
     */
    public function current()
    {
        $this->current_element = (array)$this->get_element();
        
        if ( $this->pack_mode )
        {
            // конфиги пакета
            $packconfig = $this->get_configitem('pack_config');
            
            // получение объекта подключения к фреймворку синхронизаций
            $connection = $this->get_sync_connection();
            
            // получение полей для вычисления хеша
            $hashfields = array_intersect_key($this->current_element, array_flip($packconfig['uphashfields']));
            
            if ( ! empty($hashfields) && ! empty($this->current_element['__main_sync_upid']) )
            {
                // проверка, что есть поля, по по котором необходимо считать хеш
                // и что в пуле лежит служебное поле с идентификатором
                $uphash = $this->dof->storage('sync')->makeHash((object)$hashfields);
                
                // получение записи из реестра синхронизации
                $syncrecord = $connection->getSync(['upid' => $this->current_element['__main_sync_upid']]);
                // проверка актуальности записи
                if ( (! $packconfig['fullsync']) && ! empty($syncrecord->uphash) && ($syncrecord->uphash == $uphash) )
                {
                    return [];
                }
                
                // передадим внутренний идентификатор, если он есть
                if ( ! empty($syncrecord->downid) )
                {
                    $this->current_element['__main_sync_downid'] = $syncrecord->downid;
                }
            }
            
            if ( empty($this->current_element['__main_sync_upid']) )
            {
                $this->current_element['__main_sync_upid'] = 0;
            }
            if ( empty($this->current_element['__main_sync_downid']) )
            {
                $this->current_element['__main_sync_downid'] = 0;
            }
        }
        
        return $this->current_element;
    }
    
    /**
     * Iterator rewind()
     *
     * @return void
     */
    public function rewind()
    {
        $this->row_counter = 0;
    }
    
    /**
     * Iterator key()
     *
     * @return int
     */
    public function key()
    {
        return $this->row_counter;
    }
    
    /**
     * Получение итератора
     *
     * @return Iterator
     */
    public function get_dataiterator()
    {
        // Поля обмена, скорректированные из полей БД
        $this->datafields = (array)$this->get_configitem('fieldsmatching');
        
        if ( $this->pack_mode )
        {
            $packconfig = $this->get_configitem('pack_config');
            $this->datafields[$packconfig['upfieldname']] = '__main_sync_upid';
            $this->datafields['__main_sync_downid'] = '__main_sync_downid';
        }
        
        return $this;
    }
    
    /** РАБОТА С ФОРМАМИ НАСТРОЙКИ ОБМЕНА **/
    
    /**
     * Первичная инициализация формы импорта данных
     *
     * @param dof_modlib_transmit_configurator_configform_base $form
     * @param MoodleQuickForm $mform
     *
     * @return void
     */
    public function configform_definition_import(dof_modlib_transmit_configurator_configform_base &$form, MoodleQuickForm &$mform)
    {
        parent::configform_definition_import($form, $mform);
        
        // ПОле для вывода общих ошибок валидации
        $mform->addElement(
            'static',
            'errors',
            ''
        );
        
    }
    
    /**
     * Заполнить форму данными
     *
     * @param dof_modlib_transmit_configurator_configform_base $form - Форма настройки
     * @param MoodleQuickForm $mform - Контроллер формы
     *
     * @return void
     */
    public function configform_definition_after_data_import(dof_modlib_transmit_configurator_configform_base &$form, MoodleQuickForm &$mform)
    {
        parent::configform_definition_after_data_import($form, $mform);
        
        // Получение полей из источника
        $sourcefields = [
            '' => $this->dof->get_string('source_moodle_configform_matching_not_use', 'transmit', null, 'modlib')
        ] + $this->get_fields();
        if( ! empty($this->maskimportfields) && ! empty($sourcefields) )
        {
            // Текущая конфигурация полей
            $fieldsconfig = $this->get_configitem('fieldsmatching');
            
            // Заголовок полей
            $mform->addElement(
                'header',
                'header_configform_fields',
                $this->dof->get_string('source_moodle_configform_header', 'transmit', null, 'modlib')
            );
            $mform->setExpanded('header_configform_fields', true);

            $mform->addElement('html', html_writer::div($this->dof->get_string('configform_source_info_desc', 'transmit', null, 'modlib'), '', ['style' => 'margin-bottom: 15px;font-weight:bold;']));
            
            foreach($this->maskimportfields as $maskfieldcode => $maskfielddata)
            {
                // пока пропускаем регулярки, их обработку надо писать отдельно
                // @TODO: написать обработку регулярок в форме
                if ( @preg_match((string)$maskfieldcode, null) === false )
                {
                    // наименование поля для отображения
                    $displayfieldcode = $maskfieldcode;
                    if ( isset($fieldinfo['displayedfieldcode']) )
                    {// Переопределение поля
                        $displayfieldcode = $fieldinfo['displayedfieldcode'];
                    }
                    
                    // добавление выпадающего списка в форму
                    $mform->addElement(
                        'select',
                        'field__'.$maskfieldcode,
                        $maskfielddata['description'], //.' ('.$displayfieldcode.')',
                        $sourcefields,
                        [
                            'title' => $maskfielddata['description']
                        ]
                    );
                    
                    // установка выбранного значения
                    $selectedsourcefield = array_search($maskfieldcode, $fieldsconfig);
                    if( $selectedsourcefield !== false )
                    {
                        $mform->setDefault('field__'.$maskfieldcode, $selectedsourcefield);
                    }
                }
            }
        }
    }
    
    /**
     * Валидация формы
     *
     * @param dof_modlib_transmit_configurator_configform_base $form - Форма настройки
     * @param MoodleQuickForm $mform - Контроллер формы
     * @param array $data - Данные формы
     * @param array $files - Загруженные в форму файлы
     *
     * @return array
     */
    public function configform_validation_import(dof_modlib_transmit_configurator_configform_base &$form, MoodleQuickForm &$mform, $data, $files)
    {
        $errors = parent::configform_validation_import($form, $mform, $data, $files);
        
        // Обновление раздела сопоставления имен внешних полей с стратегией обмена
        if ( empty($errors) )
        {
            // Валидация сопоставления полей
            $allfieldsisempty = true;
            foreach ( $data as $element => $sourcefield )
            {
                if ( substr($element, 0, 7 ) === "field__" )
                {// Данные по сопоставлению поля
                    if ( (string)$sourcefield !== '' )
                    {// Поле заполнено
                        $allfieldsisempty = false;
                        break;
                    }
                }
            }
            if ( $allfieldsisempty )
            {
                $errors['errors'] = $this->dof->
                    get_string('source_moodle_error_empty_matchingfields', 'transmit', null, 'modlib');
            }
        }
        
        return $errors;
    }
    
    /**
     * Установка настроек источника на основе данных формы
     *
     * @param dof_modlib_transmit_configurator_configform_base $form
     * @param MoodleQuickForm $mform
     * @param stdClass $formdata
     *
     * @return void
     */
    public function configform_setupconfig_import(dof_modlib_transmit_configurator_configform_base &$form, MoodleQuickForm &$mform, $formdata)
    {
        // Подготовка фильтров по данным из формы
        $this->prepare_filters($mform, $formdata);
        
        // Установка сопоставления полей
        $matchingfields = [];
        foreach ( $formdata as $element => $sourcefield )
        {
            if ( substr($element, 0, 7 ) === "field__" && $sourcefield !== '' )
            {// Данные по сопоставлению поля указаны
                $maskfieldname = (string)substr($element, 7);
                $matchingfields[$sourcefield] = $maskfieldname;
            }
        }
        $this->set_configitem('fieldsmatching', $matchingfields);
    }
    
    /**
     * Получение конфигурации по умолчанию для текущего источника
     *
     * @return array
     */
    protected function config_defaults()
    {
        // Конфигурация для базового источника
        $configdata = parent::config_defaults();
        
        // Cопоставление полей таблицы с полями для обмена
        $configdata['fieldsmatching'] = [];
        
        return $configdata;
    }
    
    /**
     * Получить текущий элемент из БД и преобразовать поля
     *
     * @return array
     */
    protected function get_element()
    {
        return [];
    }
}

