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
 * Обмен данных с внешними источниками. Базовый класс БД-источников данных.
 *
 * @package    modlib
 * @subpackage transmit
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class dof_modlib_transmit_source_db extends dof_modlib_transmit_source_base implements Iterator
{
    /**
     * Драйвер подключения к БД
     *
     * @var PDO
     */
    protected $connection = null;
    
    /**
     * Текущая строка итератора
     *
     * @var int
     */
    protected $row_counter = 0;
    
    /**
     * Текущая строка итератора
     *
     * @var string
     */
    protected $current_element = null;
    
    /** РЕАЛИЗАЦИЯ ИТЕРАТОРА **/
    
    /**
     * Iterator next()
     *
     * @return void
     */
    public function next()
    {
        $this->row_counter++;
        $this->current_element = $this->get_element();
    }
    
    /**
     * Iterator valid()
     *
     * @return bool
     */
    public function valid()
    {
        if ( ! $this->current_element )
        {
            return false;
        }
        return true;
    }
    
    /**
     * Iterator current()
     *
     * @return array
     */
    public function current()
    {
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
        $this->current_element = $this->get_element();
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
        
        // Текущая строка итератора
        $this->row_counter = 0;
        
        return $this;
    }
    
    /**
     * Получить текущий элемент из БД и преобразовать поля
     *
     * @return array|null
     */
    protected function get_element()
    {
        $tablename = $this->get_configitem('tablename');
        
        list($sql, $parameters) = str_replace('{TABLENAME}', $tablename, 
                $this->get_sql_data_element($this->row_counter));
        if (is_null($this->connection)) {
            $this->validate_connect([
                'host' => $this->config['host'],
                'port' => $this->config['port'],
                'user' => $this->config['user'],
                'password' => $this->config['password'],
                'tablename' => $this->config['tablename'],
                'dbname' => $this->config['dbname'],
                'charset' => $this->config['charset'],
            ]);
        }
        $st = $this->connection->prepare($sql);
        $st->execute($parameters);
        if ( ! $st )
        {// Данные не получены
            return null;
        }
        // Получение данных
        $data = (array)$st->fetch(PDO::FETCH_ASSOC);
                
        // Заполнение данными элемента для обмена
        $transmitdata = [];
        foreach ( $data as $field => $data )
        {
            if ( empty($this->datafields[$field]) )
            {// Сопоставление поля не найдено в конфигурации
                continue;
            }
            
            // Добавление поля в результат
            $matchingfield = $this->datafields[$field];
            $transmitdata[$matchingfield] = $data;
        }
        if ( empty($transmitdata) )
        {
            return null;
        }
        return $transmitdata;
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
        
        // Хост
        $mform->addElement(
            'text',
            'host',
            $this->dof->get_string('source_configform_host_title', 'transmit', null, 'modlib')
        );
        $mform->setType('host', PARAM_RAW_TRIMMED);
        $mform->addRule(
            'host',
            $this->dof->get_string('source_configform_host_error_empty', 'transmit', null, 'modlib'),
            'required'
        );
        $mform->setDefault('host', $this->get_configitem('host'));
        
        // Имя БД
        $mform->addElement(
            'text',
            'dbname',
            $this->dof->get_string('source_configform_dbname_title', 'transmit', null, 'modlib')
        );
        $mform->setType('dbname', PARAM_RAW_TRIMMED);
        $mform->addRule(
            'dbname',
            $this->dof->get_string('source_configform_dbname_error_empty', 'transmit', null, 'modlib'),
            'required'
        );
        $mform->setDefault('dbname', $this->get_configitem('dbname'));
        
        // Пользователь
        $mform->addElement(
            'text',
            'user',
            $this->dof->get_string('source_configform_user_title', 'transmit', null, 'modlib')
        );
        $mform->setType('user', PARAM_RAW_TRIMMED);
        $mform->addRule(
            'user',
            $this->dof->get_string('source_configform_user_error_empty', 'transmit', null, 'modlib'),
            'required'
        );
        $mform->setDefault('user', $this->get_configitem('user'));
        
        // Пароль
        $mform->addElement(
            'text',
            'password',
            $this->dof->get_string('source_configform_password_title', 'transmit', null, 'modlib')
        );
        $mform->setType('password', PARAM_RAW_TRIMMED);
        $mform->addRule(
            'password',
            $this->dof->get_string('source_configform_password_error_empty', 'transmit', null, 'modlib'),
            'required'
        );
        $mform->setDefault('password', $this->get_configitem('password'));
        
        // Название таблицы
        $mform->addElement(
            'text',
            'tablename',
            $this->dof->get_string('source_configform_tablename_title', 'transmit', null, 'modlib')
        );
        $mform->setType('tablename', PARAM_RAW_TRIMMED);
        $mform->addRule(
            'tablename',
            $this->dof->get_string('source_configform_tablename_error_empty', 'transmit', null, 'modlib'),
            'required'
        );
        $mform->setDefault('tablename', $this->get_configitem('tablename'));
        
        // Порт
        $mform->addElement(
            'text',
            'port',
            $this->dof->get_string('source_configform_port_title', 'transmit', null, 'modlib')
        );
        $mform->setType('port', PARAM_INT);
        $mform->setDefault('port', $this->get_configitem('port'));
        
        // Кодировка
        $mform->addElement(
            'text',
            'charset',
            $this->dof->get_string('source_configform_charset_title', 'transmit', null, 'modlib')
        );
        $mform->setType('charset', PARAM_RAW_TRIMMED);
        $mform->setDefault('charset', $this->get_configitem('charset'));
    }
    
    protected function get_fields(MoodleQuickForm &$mform)
    {
        $fields = [];
        
        // Проверка подключения к БД на основе указанных реквизитов
        $connectiondata = [
            'host' => $mform->getElementValue('host'),
            'user' => $mform->getElementValue('user'),
            'password' => $mform->getElementValue('password'),
            'port' => $mform->getElementValue('port'),
            'dbname' => $mform->getElementValue('dbname'),
            'tablename' => $mform->getElementValue('tablename'),
            'charset' => $mform->getElementValue('charset')
        ];
        $connectionerrors = (array)$this->validate_connect($connectiondata);
        if ( empty($connectionerrors) )
        {// Соединение открыто
            // Получение полей
            $fields = $this->get_table_columns_list($connectiondata['tablename']);
            
        }
        
        return array_combine($fields, $fields);
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
        
        // Проверка подключения к БД на основе указанных реквизитов
        $connectiondata = [
            'host' => $mform->getElementValue('host'),
            'user' => $mform->getElementValue('user'),
            'password' => $mform->getElementValue('password'),
            'port' => $mform->getElementValue('port'),
            'dbname' => $mform->getElementValue('dbname'),
            'tablename' => $mform->getElementValue('tablename'),
            'charset' => $mform->getElementValue('charset')
        ];
        $connectionerrors = (array)$this->validate_connect($connectiondata);
        if ( empty($connectionerrors) )
        {// Соединение открыто
            // Получение полей
            $fields = $this->get_table_columns_list($connectiondata['tablename']);
            if ( $fields )
            {
                // Заголовок полей
                $mform->addElement(
                    'header',
                    'header_configform_fields',
                    $this->dof->get_string('header_configform_source_db_fields', 'transmit', null, 'modlib')
                );
                $mform->setExpanded('header_configform_fields', true);
                
                // Текущая конфигурация полей
                $fieldsconfig = $this->get_configitem('fieldsmatching');
                
                // Добавление полей в форму
                foreach ( $fields as $field )
                {
                    $mform->addElement(
                        'text',
                        'field__'.$field,
                        $field
                    );
                    $mform->setType('field__'.$field, PARAM_ALPHANUMEXT);
                    if ( isset($fieldsconfig[$field]) )
                    {// Данные по полю хранятся в конфигурации
                        $mform->setDefault('field__'.$field, $matchingfields[$field]);
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
        if ( empty($errors) )
        {// Данные валидны
            // Проверка подключения к БД на основе указанных реквизитов
            $connectionerrors = (array)$this->validate_connect($data);
            
            // Добавление ошибок соединения с БД
            $errors = array_merge($errors, $connectionerrors);
            
            // Обновление раздела сопоставления имен внешних полей с стратегией обмена
            if ( empty($errors) )
            {
                // Валидация сопоставления полей
                $allfieldsisempty = true;
                foreach ( $data as $element => $matching )
                {
                    if ( substr($element, 0, 7 ) === "field__" )
                    {// Данные по сопоставлению поля
                        if ( (string)$matching !== '' )
                        {// Поле заполнено
                            $allfieldsisempty = false;
                        }
                    }
                }
                if ( $allfieldsisempty )
                {
                    $errors['errors'] = $this->dof->
                        get_string('source_db_error_empty_matchingfields', 'transmit', null, 'modlib');
                }
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
        
        // Установка хоста
        $this->set_configitem('host', $formdata->host);
        
        // Установка имени внешней БД
        $this->set_configitem('dbname', $formdata->dbname);
        
        // Установка пользователя
        $this->set_configitem('user', $formdata->user);
        
        // Установка пароля
        $this->set_configitem('password', $formdata->password);
        
        // Установка имени таблицы
        $this->set_configitem('tablename', $formdata->tablename);
        
        if ( empty($formdata->port) )
        {
            $this->set_configitem('port', $formdata->port);
        }
        
        // Установка сопоставления полей
        $matchingfields = [];
        foreach ( $formdata as $element => $matching )
        {
            if ( substr($element, 0, 7 ) === "field__" && $matching !== '' )
            {// Данные по сопоставлению поля указаны
                $fieldname = (string)substr($element, 7, strlen($element) - 7 );
                $matchingfields[$fieldname] = $matching;
            }
        }
        $this->set_configitem('fieldsmatching', $matchingfields);
    }
    
    /** РАБОТА С ДАННЫМИ ДЛЯ ОБМЕНА **/
    
    /**
     * Экспорт
     *
     * @param $fields
     * @param $data
     *
     * @return void
     */
    public function export(array $fields, array $data)
    {
        
    }
    
    /** РАБОТА С БАЗОЙ ДАННЫХ **/
    
    /**
     * Получить SQL-запрос на наличие таблицы в БД
     * 
     * @return string
     */
    protected abstract function get_sql_table_exists();
    
    /**
     * Получить SQL-запрос на наличие полей в таблице БД
     *
     * @return string
     */
    protected abstract function get_sql_fields_exists();
    
    /**
     * Получить SQL-запрос на получение списка полей в таблице БД
     *
     * @return string
     */
    protected abstract function get_sql_fields_list();
    
    /**
     * Получить SQL-запрос на получение строки из таблицы БД
     *
     * @return string
     */
    protected abstract function get_sql_data_element($rownumber);
    
    /**
     * Получить DSN-библиотеку для подключения к БД
     * 
     * @param string
     */
    protected function get_dsnlib_name($connectiondata = [])
    {
        switch ( self::get_code() )
        {
            case 'db_mysql' :
                return 'mysql';
            case 'db_mssql' :
                return 'dblib';
            case 'db_postgresql' :
                return 'pgsql';
            default :
                return null;
        }
    }
    
    /**
     * Получить список полей в таблице
     *
     * @param string
     */
    protected function get_table_columns_list($tablename)
    {
        // Получение списка полей
        $fields_list_query = str_replace('{TABLENAME}', $tablename, $this->get_sql_fields_list());
        $query = $this->connection->query($fields_list_query);
        $fields = $query->fetchAll(PDO::FETCH_COLUMN);
        
        return $fields;
    }
    
    /**
     * Валидация подключения
     *
     * @param array $connectiondata - Данные соединения
     *
     * @return array - Массив ошибок валидации подключения
     */
    protected function validate_connect($connectiondata = [])
    {
        $errors = [];
        
        // Реквизиты соединения
        $host = $connectiondata['host'];
        $port = $connectiondata['port'];
        $user = $connectiondata['user'];
        $password = $connectiondata['password'];
        $dbname = $connectiondata['dbname'];
        $tablename = $connectiondata['tablename'];
        $charset = $connectiondata['charset'] ?? null;
        
        $dsnlib = $this->get_dsnlib_name();
        if ( empty($dsnlib) )
        {
            throw new dof_modlib_transmit_exception('source_db_error_invalid_dsnlib', 'modlib_transmit');
        }
        
        // Формирование DSN
        $dsn = $dsnlib.":host=".$host.";dbname=".$dbname;
        // Порт
        if ( ! empty($port) )
        {
            $dsn .= ";port=".$port;
        }
        // Кодировка
        if ( ! empty($charset) )
        {
            $dsn .= ";charset=".$charset;
        }
        
        // Соединение с экранированием Warning при указании ошибочных реквизитов
        try 
        {
            @$connection = new PDO($dsn, $user, $password);
        } catch ( PDOException $e )
        {// Ошибка соединения с БД
            switch ( $e->getCode() )
            {
                case '1045':
                    $errors['user'] = $this->dof->get_string('source_db_error_access_host_denied', 'transmit', null, 'modlib');
                    break;
                case '1044':
                    $errors['dbname'] = $this->dof->get_string('source_db_error_access_db_denied', 'transmit', null, 'modlib');
                    break;
                default:
                    $stringvars = new stdClass();
                    $stringvars->errortext = $e->getMessage();
                    $stringvars->errorcode = $e->getCode();
                    $errors['host'] = $this->dof->get_string('source_db_error_undefined', 'transmit', $stringvars, 'modlib');
                    break;
            }
            return $errors;
        }
        
        // Проверка существования таблицы
        $table_exists_query = str_replace('{TABLENAME}', $tablename, $this->get_sql_table_exists());
        $table_exists = $connection->query($table_exists_query);
        if ( ! $table_exists )
        {
            $errors['tablename'] = $this->dof->get_string('source_configform_db_error_table_not_found', 'transmit', null, 'modlib');
            return $errors;
        }
        
        // Проверка существования полей
        $fields_exists_query = str_replace('{TABLENAME}', $tablename, $this->get_sql_table_exists());
        $fields_exists = $connection->query($fields_exists_query);
        if ( ! $fields_exists )
        {
            $errors['tablename'] = $this->dof->get_string('source_configform_db_error_fields_not_found', 'transmit', null, 'modlib');
            return $errors;
        }

        // Установка соединения
        $this->connection = $connection;
        return $errors;
    }
    
    /** РАБОТА С КОНФИГУРАЦИЕЙ ИСТОЧНИКА **/
    
    /**
     * Получение конфигурации по умолчанию для текущего источника
     *
     * @return array
     */
    protected function config_defaults()
    {
        // Конфигурация для базового источника
        $configdata = parent::config_defaults();
        
        // Хост для соединения с БД
        $configdata['host'] = 'localhost';
        
        // Порт для соединения с БД
        $configdata['port'] = 3306;
        
        // Пользователь для соединения с БД
        $configdata['user'] = '';
        
        // Пароль для соединения с БД
        $configdata['password'] = '';
        
        // Имя БД
        $configdata['dbname'] = '';
        
        // Имя таблицы
        $configdata['tablename'] = '';
        
        // Кодировка
        $configdata['charset'] = 'utf8';
        
        // Cопоставление полей таблицы с полями для обмена
        $configdata['fieldsmatching'] = [];
        
        return $configdata;
    }
    
    protected function get_sql_conditions()
    {
        $filters = $this->get_configitem('filters');
        
        $conditions = [];
        $parameters = [];
        if (!empty($filters))
        {
            foreach($filters as $filter)
            {
                $conditions[] = $filter->fieldname.' '.$filter->operator.' :fv_'.$filter->fieldname;
                $parameters[':fv_'.$filter->fieldname] = $filter->value;
            }
        } else
        {
            $conditions = ['1=1'];
        }
        
        return [$conditions, $parameters];
    }
}

