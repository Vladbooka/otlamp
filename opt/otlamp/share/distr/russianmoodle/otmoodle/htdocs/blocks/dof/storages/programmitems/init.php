<?php
////////////////////////////////////////////////////////////////////////////
//                                                                        //
// NOTICE OF COPYRIGHT                                                    //
//                                                                        //
// Dean`s Office for Moodle                                               //
// Электронный деканат                                                    //
// <http://sourceforge.net/projects/freedeansoffice/>                     //
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
// подключение интерфейса настроек
require_once($DOF->plugin_path('storage', 'config', '/config_default.php'));

/** Справочник учебных программ
 *
 */
class dof_storage_programmitems extends dof_storage implements dof_storage_config_interface
{

    /**
     * @var dof_control
     */
    protected $dof;

    // **********************************************
    // Методы, предусмотренные интерфейсом plugin
    // **********************************************

    public function install()
    {
        if ( !parent::install() )
        {
            return false;
        }
        return $this->dof->storage('acl')->save_roles($this->type(), $this->code(), $this->acldefault());
    }

    /** Метод, реализующий обновление плагина в системе
     * Создает или модифицирует существующие таблицы в БД
     * @param string $oldversion - версия установленного в системе плагина
     * @return boolean
     * @access public
     */
    public function upgrade($oldversion)
    {
        global $DB;
        $dbman = $DB->get_manager();
        $table = new xmldb_table($this->tablename());
        if ( $oldversion < 2012071713 )
        {//добавляем поля
            $field = new xmldb_field('metaprogrammitemid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, 'altgradeitem');
            if ( !$dbman->field_exists($table, $field) )
            {
                $dbman->add_field($table, $field);
            }

            $field = new xmldb_field('metasyncon', XMLDB_TYPE_CHAR, '10', null, XMLDB_NOTNULL, null, '0', 'metaprogrammitemid');
            if ( !$dbman->field_exists($table, $field) )
            {
                $dbman->add_field($table, $field);
            }
        }
        if ( $oldversion < 2013040905 )
        {// добавим поле billingrules
            $field = new xmldb_field('billingtext', XMLDB_TYPE_TEXT, 'big', null, false, null, null, 'metaprogrammitemid');
            if ( !$dbman->field_exists($table, $field) )
            {// поле еще не установлено
                $dbman->add_field($table, $field);
            }
            $field = new xmldb_field('billingrules', XMLDB_TYPE_TEXT, 'big', null, false, null, null, 'billingtext');
            if ( !$dbman->field_exists($table, $field) )
            {// поле еще не установлено
                $dbman->add_field($table, $field);
            }
        }
        if ( $oldversion < 2013062700 )
        {// добавим поле salfactor
            $field = new xmldb_field('salfactor',XMLDB_TYPE_FLOAT, '6', XMLDB_UNSIGNED,
                    true, null, '1', 'billingrules');
            // количество знаков после запятой
            $field->setDecimals('2');
            if ( !$dbman->field_exists($table, $field) )
            {// поле еще не установлено
                $dbman->add_field($table, $field);
            }
            // добавляем индекс к полю
            $index = new xmldb_index('isalfactor', XMLDB_INDEX_NOTUNIQUE,
                    array('salfactor'));
            if (!$dbman->index_exists($table, $index))
            {// если индекс еще не установлен
                $dbman->add_index($table, $index);
            }
        }
        if ( $oldversion < 2013082800 )
        {// добавим поле salfactor
            dof_hugeprocess();
            $index = new xmldb_index('isalfactor', XMLDB_INDEX_NOTUNIQUE,
                    array('salfactor'));
            if ($dbman->index_exists($table, $index))
            {// если индекс еще не установлен
                $dbman->drop_index($table, $index);
            }
            $field = new xmldb_field('salfactor', XMLDB_TYPE_FLOAT, '6, 2', null,
                    XMLDB_NOTNULL, null, '0', 'billingrules');
            $dbman->change_field_default($table, $field);
            if ( !$dbman->index_exists($table, $index) )
            {// если индекс еще не установлен
                $dbman->add_index($table, $index);
            }
        }
        if ( $oldversion < 2014101300 )
        {// добавим поля hourslab, hoursind, hourscontrol, autohours, hoursclassroom
            dof_hugeprocess();
            $fields = array();
            $fields[] = new xmldb_field('hourslab',       XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, 0, 'salfactor');
            $fields[] = new xmldb_field('hoursind',       XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, 0, 'hourslab');
            $fields[] = new xmldb_field('hourscontrol',   XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, 0, 'hoursind');
            $fields[] = new xmldb_field('autohours',      XMLDB_TYPE_INTEGER, '1',  XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 1, 'hourscontrol');
            $fields[] = new xmldb_field('hoursclassroom', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, 0, 'autohours');
            foreach ( $fields as $field )
            {
                if ( !$dbman->field_exists($table, $field) )
                {// поле еще не установлено
                    $dbman->add_field($table, $field);
                }
            }
            
            // Добавляем индексы для полей
            $indexes = array();
            $indexes[] = new xmldb_index('ihourslab', XMLDB_INDEX_NOTUNIQUE, array('hourslab'));
            $indexes[] = new xmldb_index('ihoursind', XMLDB_INDEX_NOTUNIQUE, array('hoursind'));
            $indexes[] = new xmldb_index('ihourscontrol', XMLDB_INDEX_NOTUNIQUE, array('hourscontrol'));
            $indexes[] = new xmldb_index('ihoursclassroom', XMLDB_INDEX_NOTUNIQUE, array('hoursclassroom'));
            foreach ( $indexes as $index )
            {
                if ( !$dbman->index_exists($table, $index) )
                {// индекс еще не установлен
                    $dbman->add_index($table, $index);
                }
            }
            while ( $list = $this->get_records_select('salfactor = 1', null, '', '*', 0, 100) )
            {
                foreach ( $list as $item )
                {// ищем уроки где appointmentid не совпадает с teacherid
                    $obj = new stdClass;
                    $obj->salfactor = 0;
                    $this->update($obj, $item->id);
                }
            }
            // Добавим задание просчитать все часы
            $this->dof->add_todo('storage', 'programmitems', 'count_hours', null, null, 2, time() + 60);
        }
        
        if ($oldversion < 2017042000)
        {
            dof_hugeprocess();
            
            // добавим поле studentslimit
            $field = new xmldb_field('studentslimit', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,
                XMLDB_NOTNULL, null, '0', 'hoursclassroom');
            if( ! $dbman->field_exists($table, $field) )
            { // поле еще не установлено
                $dbman->add_field($table, $field);
            }
            
            // добавим поле selfenrol
            $field = new xmldb_field('selfenrol', XMLDB_TYPE_INTEGER, '3', XMLDB_UNSIGNED,
                XMLDB_NOTNULL, null, '0', 'studentslimit');
            if( ! $dbman->field_exists($table, $field) )
            { // поле еще не установлено
                $dbman->add_field($table, $field);
            }
            // добавляем индекс к полю
            $index = new xmldb_index('iselfenrol', XMLDB_INDEX_NOTUNIQUE, ['selfenrol']);
            if( ! $dbman->index_exists($table, $index) )
            {// если индекс еще не установлен
                $dbman->add_index($table, $index);
            }
        }
        
        if ($oldversion < 2017110901)
        {
            // добавим поле verificationrequested
            $field = new xmldb_field('verificationrequested', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED,
                null, null, '0', 'selfenrol');
            if( ! $dbman->field_exists($table, $field) )
            { // поле еще не установлено
                $dbman->add_field($table, $field);
            }
            // добавляем индекс к полю
            $index = new xmldb_index('iverificationrequested', XMLDB_INDEX_NOTUNIQUE, ['verificationrequested']);
            if( ! $dbman->index_exists($table, $index) )
            {// если индекс еще не установлен
                $dbman->add_index($table, $index);
            }
            
            // добавим поле verificationrequested
            $field = new xmldb_field('coursetemplateversion', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,
                null, null, '0', 'verificationrequested');
            if( ! $dbman->field_exists($table, $field) )
            { // поле еще не установлено
                $dbman->add_field($table, $field);
            }
            // добавляем индекс к полю
            $index = new xmldb_index('icoursetemplateversion', XMLDB_INDEX_NOTUNIQUE, ['coursetemplateversion']);
            if( ! $dbman->index_exists($table, $index) )
            {// если индекс еще не установлен
                $dbman->add_index($table, $index);
            }
        }
        
        if ( $oldversion < 2017111500 )
        {
            // добавим поле тип связи с курсом мудл
            $field = new xmldb_field('courselinktype', XMLDB_TYPE_CHAR, '10', null, null, null, null, 'coursetemplateversion');
            if( ! $dbman->field_exists($table, $field) )
            { // поле еще не установлено
                $dbman->add_field($table, $field);
            }
        }
        
        if ( $oldversion < 20180802200 )
        {
            $field = new xmldb_field('lessonscale', XMLDB_TYPE_CHAR, 255);
            if ( ! $dbman->field_exists($table, $field) )
            {
                $dbman->add_field($table, $field);
            }
            $field = new xmldb_field('lessonpassgrade', XMLDB_TYPE_CHAR, 255);
            if ( ! $dbman->field_exists($table, $field) )
            {
                $dbman->add_field($table, $field);
            }
        }
        if ( $oldversion < 2018091412 )
        {
            $field = new xmldb_field('coursegradesconversation', XMLDB_TYPE_TEXT);
            if ( ! $dbman->field_exists($table, $field) )
            {
                $dbman->add_field($table, $field);
            }
            $field = new xmldb_field('modulegradesconversation', XMLDB_TYPE_TEXT);
            if ( ! $dbman->field_exists($table, $field) )
            {
                $dbman->add_field($table, $field);
            }
        }
        
        return $this->dof->storage('acl')->save_roles($this->type(), $this->code(), $this->acldefault());
    }

    /**
     * Возвращает версию установленного плагина
     *
     * @return string
     * @access public
     */
    public function version()
    {
        // Версия плагина (используется при определении обновления)
        return 2020030200;
    }

    /** Возвращает версии интерфейса Деканата,
     * с которыми этот плагин может работать
     * @return string
     * @access public
     */
    public function compat_dof()
    {
        return 'aquarium';
    }

    /** Возвращает версии стандарта плагина этого типа,
     * которым этот плагин соответствует
     * @return string
     * @access public
     */
    public function compat()
    {
        return 'paradusefish';
    }

    /** Возвращает тип плагина
     * @return string
     * @access public
     */
    public function type()
    {
        return 'storage';
    }

    /** Возвращает короткое имя плагина
     * Оно должно быть уникально среди плагинов этого типа
     * @return string
     * @access public
     */
    public function code()
    {
        return 'programmitems';
    }

    /** Возвращает список плагинов,
     * без которых этот плагин работать не может
     * @return array
     * @access public
     */
    public function need_plugins()
    {
        return array('storage'=>array('departments' => 2009040800,
                                      'programms'   => 2009040800,
                                      'acl'         => 2011041800,
                                      'config'      => 2011080900));
    }

    /** Определить, возможна ли установка плагина в текущий момент
     * Эта функция одинакова абсолютно для всех плагинов и не содержит в себе каких-либо зависимостей
     * @TODO УДАЛИТЬ эту функцию при рефакторинге. Вместо нее использовать наследование
     * от класса dof_modlib_base_plugin
     * @see dof_modlib_base_plugin::is_setup_possible()
     *
     * @param int $oldversion [optional] - старая версия плагина в базе (если плагин обновляется)
     *                                    или 0 если плагин устанавливается
     *
     * @return bool
     *              true - если плагин можно устанавливать
     *              false - если плагин устанавливать нельзя
     */
    public function is_setup_possible($oldversion = 0)
    {
        return dof_is_plugin_setup_possible($this, $oldversion);
    }

    /** Получить список плагинов, которые уже должны быть установлены в системе,
     * и без которых начать установку или обновление невозможно
     *
     * @param int $oldversion [optional] - старая версия плагина в базе (если плагин обновляется)
     *                                    или 0 если плагин устанавливается
     * @return array массив плагинов, необходимых для установки
     *      Формат: array('plugintype'=>array('plugincode' => YYYYMMDD00));
     */
    public function is_setup_possible_list($oldversion = 0)
    {
        return array('storage'=>array('acl'    => 2011040504,
                                      'config' => 2011080900));
    }

    /** Список обрабатываемых плагином событий
     * @return array - array(array('plugintype'=>..,'plugincode'=>..,'eventcode'=>..),...)
     * @access public
     */
    public function list_catch_events()
    {
        return array(array('plugintype'=>'storage', 'plugincode'=>'programmitems', 'eventcode'=>'insert'),
                     array('plugintype'=>'storage', 'plugincode'=>'programmitems', 'eventcode'=>'update'),
            );
    }

    /** Требуется ли запуск cron в плагине
     * @return bool
     * @access public
     */
    public function is_cron()
    {
        // Просим запускать крон не чаще раза в 15 минут
        return 1;
    }

    /** Проверяет полномочия на совершение действий
     * @param string $do - идентификатор действия, которое должно быть совершено
     * @param int $objid - идентификатор экземпляра объекта,
     * по отношению к которому это действие должно быть применено
     * @param int $userid - идентификатор пользователя, полномочия которого проверяются
     * @return bool true - можно выполнить указанное действие по отношению к выбранному объекту
     * false - доступ запрещен
     * @access public
     */
    public function is_access($do, $objid = NULL, $userid = NULL, $depid = null)
    {
        if ( $this->dof->is_access('datamanage') OR $this->dof->is_access('admin')
                OR $this->dof->is_access('manage') )
        {// манагеру можно все
            return true;
        }
        // получаем id пользователя в persons
        $personid = $this->dof->storage('persons')->get_by_moodleid_id($userid);
        // получаем все нужные параметры для функции проверки прав
        $acldata = $this->get_access_parametrs($do, $objid, $personid, $depid);
        // проверка
        if ( $this->acl_check_access_paramenrs($acldata) )
        {// право есть заканчиваем обработку
            return true;
        }
        return false;
    }

    /** Требует наличия полномочия на совершение действий
     * @param string $do - идентификатор действия, которое должно быть совершено
     * @param int $objid - идентификатор экземпляра объекта,
     * по отношению к которому это действие должно быть применено
     * @param int $userid - идентификатор пользователя, полномочия которого проверяются
     * @return bool true - можно выполнить указанное действие по отношению к выбранному объекту
     * false - доступ запрещен
     * @access public
     */
    public function require_access($do, $objid = NULL, $userid = NULL, $depid = null)
    {
        // Используем функционал из $DOFFICE
        //return $this->dof->require_access($do, NULL, $userid);
        if ( !$this->is_access($do, $objid, $userid, $depid) )
        {
            $notice = "{$this->code()}/{$do} (block/dof/{$this->type()}/{$this->code()}: {$do})";
            if ( $objid )
            {
                $notice.=" id={$objid}";
            }
            $this->dof->print_error('nopermissions', '', $notice);
        }
    }

    /**
     * Обработать событие
     *
     * @param string $gentype - тип модуля, сгенерировавшего событие
     * @param string $gencode - код модуля, сгенерировавшего событие
     * @param string $eventcode - код задания
     * @param int $intvar - дополнительный параметр
     * @param mixed $mixedvar - дополнительные параметры
     * @return bool - true в случае выполнения без ошибок
     * @access public
     */
    public function catch_event($gentype, $gencode, $eventcode, $intvar, $mixedvar)
    {
        switch ( $eventcode )
        {
            case 'update':
                // Если ничего пересчитывать не нужно, обновления не будет
                $pitem = false;
                // Пересчитаем hours если указано это сделать
                if ( isset($mixedvar['new']->autohours) AND $mixedvar['new']->autohours == 1 )
                {

                    if ( !isset($mixedvar['old']->hours) )
                    {
                        $oldhours = $this->get_field($mixedvar['new']->id, 'hours');
                    } else
                    {
                        $oldhours = $mixedvar['old']->hours;
                    }

                    $hours = $this->get_total_hours($mixedvar['new']->id);
                    // Если часы нужно пересчитать
                    if ( $oldhours != $hours )
                    {
                        if ( !$pitem )
                        {
                            $pitem = new stdClass();
                        }
                        $pitem->hours = $hours;
                    }
                }

                // Посчитаем аудиторные часы и обновим если нужно
                $classhours = $this->get_classroom_hours($mixedvar['new']->id);
                if ( isset($mixedvar['old']->hoursclassroom) )
                {
                    if ( $mixedvar['old']->hoursclassroom != $classhours )
                    {
                        if ( !$pitem )
                        {
                            $pitem = new stdClass();
                        }
                        $pitem->hoursclassroom = $classhours;
                    }
                } else
                {
                    $oldhoursclass = $this->get_field($mixedvar['new']->id, 'hoursclassroom');
                    if ( $oldhoursclass != $classhours )
                    {
                        if ( !$pitem )
                        {
                            $pitem = new stdClass();
                        }
                        $pitem->hoursclassroom = $classhours;
                    }
                }
                if ( $pitem )
                {
                    $pitem->id = $mixedvar['new']->id;
                    $this->update($pitem, null, true);
                }
                // Синхронизация метадисциплин
                if ( isset($mixedvar['new']->metaprogrammitemid) )
                {
                    if ( $mixedvar['new']->metaprogrammitemid == '0' )
                    {// это метадисциплина - синхронизируем ее наследников
                        if ( !$inheritors = $this->dof->storage('programmitems')->get_records
                                (array('metasyncon' => '1', 'metaprogrammitemid' => $mixedvar['new']->id)) )
                        {// неследников нет - значит все хорошо
                            return true;
                        }
                        foreach ( $inheritors as $id => $obj )
                        {
                            $this->sync_pitem_with_metapitems($obj);
                        }
                    } elseif ( $mixedvar['new']->metasyncon )
                    {// это дисциплина привязанная к метедисциплине - синхронизируем ее
                        $this->sync_pitem_with_metapitems($mixedvar['new']);
                    }
                }
                break;

            default:
                break;
        }
        return true;
    }

    /** Запустить обработку периодических процессов
     * @param int $loan - нагрузка (1 - только срочные, 2 - нормальный режим, 3 - ресурсоемкие операции)
     * @param int $messages - количество отображаемых сообщений (0 - не выводить,1 - статистика,
     *  2 - индикатор, 3 - детальная диагностика)
     * @return bool - true в случае выполнения без ошибок
     * @access public
     */
    public function cron($loan, $messages)
    {
        if($loan === 2)
        {
            return $this->check_deleted_courses();
        }
        return true;
    }

    /**
     * Обработать задание, отложенное ранее в связи с его длительностью
     *
     * @param string $code - код задания
     * @param int $intvar - дополнительный параметр
     * @param mixed $mixedvar - дополнительные параметры
     * @return bool - true в случае выполнения без ошибок
     * @access public
     */
    public function todo($code, $intvar, $mixedvar)
    {
        switch ( $code )
        {
            // пересинхронизация всех потоков дисциплины
            case 'change_mcourse_in_programmitem':
                $pitem = $this->dof->storage('programmitems')->get($intvar);
                if ( ! $mixedvar->mdlcourse && ! $pitem )
                {// Если не передан курс и в дисциплине нет привязки - то и не привязываем
                    return false;
                }
                // останавливаем все потоки этой дисциплины
                $this->dof->storage('cstreams')->todo_itemid_active_to_suspend($intvar, $mixedvar->personid);
                // меняем курс moodle
                $this->change_mcourse_in_programmitem($intvar, $mixedvar->mdlcourse, $mixedvar->personid);
                // возобновляем все потоки этой дисциплины
                $this->dof->storage('cstreams')->todo_itemid_suspend_to_active($intvar, $mixedvar->personid);
                break;
            // Рассчитываем все автоматически вычисляемые часы для дисциплин
            case 'count_hours':
                $this->todo_count_hours();
                break;
        }

        return true;
    }

    /** Конструктор
     * @param dof_control $dof - объект с методами ядра деканата
     * @access public
     */
    public function __construct($dof)
    {
        // Сохраняем ссылку на DOF, чтоб вызывать его через $this->dof
        $this->dof = $dof;
    }

    /** Возвращает название таблицы без префикса (mdl_)
     * @return string
     * @access public
     */
    public function tablename()
    {
        // Имя таблицы, с которой работаем
        return 'block_dof_s_programmitems';
    }

    /** Переопределение функции вставки записи в таблицу - для произведения дополнительных
     * операций с данными до или после вставки
     *
     * @param object $dataobject - объект с данными для вставки
     * @param bool $quiet [optional]- не генерировать событий
     * @return mixed bool false если операция не удалась или id вставленной записи
     */
    public function insert($dataobject, $quiet = false, $bulk = false, $options = [])
    {
        if ( !isset($dataobject->autohours) )
        {// Если автоматический расчёт часов не установлен, по умолчанию активен
            $dataobject->autohours = 1;
        }
        if ( $dataobject->autohours == 1 )
        {// Автоматический расчёт часов активен, рассчитаем часы
            $dataobject->hours = $this->get_total_hours($dataobject);
        }
        $dataobject->hoursclass = $this->get_classroom_hours($dataobject);
        if ( !$id = parent::insert($dataobject, $quiet) )
        {// вставка объекта не удалась
            return false;
        }
        // получаем только что вставленный в базу объект
        $oldobj = $this->get($id);

        if ( $oldobj->code )
        {// если код был уже указан - значит все хорошо
            return $id;
        }
        // Если код записи не указан - то заменим его на id
        $newobj = new stdClass();
        $newobj->id = $id;
        $newobj->code = 'id' . $id;

        // добавляем код к созданной записи и возвращаем результат
        // @todo проверить результат вставки и записать ошибку в лог если это не удалось
        $this->update($newobj);
        return $id;
    }
    // ***********************************************************
    //       Методы для работы с полномочиями и конфигурацией
    // ***********************************************************

    /** Получить список параметров для фунции has_hight()
     *
     * @return object - список параметров для фунции has_hight()
     * @param string $action - совершаемое действие
     * @param int $objectid - id объекта над которым совершается действие
     * @param int $personid
     */
    protected function get_access_parametrs($action, $objectid, $personid, $depid = null)
    {
        $result = new stdClass();
        $result->plugintype = $this->type();
        $result->plugincode = $this->code();
        $result->code = $action;
        $result->personid = $personid;
        $result->departmentid = $depid;
        if ( is_null($depid) )
        {// подразделение не задано - берем текущее
            $result->departmentid = optional_param('departmentid', 0, PARAM_INT);
        }
        $result->objectid = $objectid;
        if ( !$objectid )
        {// если objectid не указан - установим туда 0 чтобы не было проблем с sql-запросами
            $result->objectid = 0;
        } else
        {// если указан - то установим подразделение
            $result->departmentid = $this->dof->storage($this->code())->get_field($objectid, 'departmentid');
        }

        return $result;
    }

    /** Проверить права через плагин acl.
     * Функция вынесена сюда, чтобы постоянно не писать длинный вызов и не перечислять все аргументы
     *
     * @return bool
     * @param object $acldata - объект с данными для функции storage/acl->has_right()
     */
    protected function acl_check_access_paramenrs($acldata)
    {
        return $this->dof->storage('acl')->
                    has_right($acldata->plugintype, $acldata->plugincode, $acldata->code,
                              $acldata->personid, $acldata->departmentid, $acldata->objectid);
    }
      
    /** Задаем права доступа для объектов этого хранилища
     *
     * @return array
     */
    public function acldefault()
    {
        $a = array();
        
        $a['view']           = ['roles' => ['manager', 'methodist']];
        $a['edit']           = ['roles' => ['manager', 'methodist']];
        $a['create']         = ['roles' => ['manager', 'methodist']];
        $a['delete']         = ['roles' => []];
        $a['use']            = ['roles' => ['manager', 'methodist']];
        $a['edit:mdlcourse'] = ['roles' => ['manager']];
        $a['view/meta']      = ['roles' => ['manager']];
        $a['edit/meta']      = ['roles' => ['manager']];
        $a['create/meta']    = ['roles' => ['manager']];
        $a['delete/meta']    = ['roles' => []];
        $a['use/meta']       = ['roles' => ['manager']];
        $a['edit:verificationrequested'] = ['roles' => ['manager', 'methodist']];
        $a['benotified:edit:coursetemplateversion'] = ['roles' => ['methodist']];
        $a['edit:coursetemplateversion'] = ['roles' => ['manager']];
        $a['benotified:edit:verificationrequested'] = ['roles' => ['manager']];
        $a['view:mastercourse'] = ['roles' => ['manager', 'methodist']];
        $a['edit:delete_backups'] = ['roles' => ['manager']];
        return $a;
    }

    /** Функция получения настроек для плагина
     *
     */
    public function config_default($code = null)
    {
        // плагин включен и используется
        $config = array();
        $obj = new stdClass();
        $obj->type = 'checkbox';
        $obj->code = 'enabled';
        $obj->value = '1';
        $config[$obj->code] = $obj;
        // Максимально разрешенное количество объектов этого типа в базе
        // (указывается индивидуально для каждого подразделения)
        $obj = new stdClass();
        $obj->type = 'text';
        $obj->code = 'objectlimit';
        $obj->value = '-1';
        $config[$obj->code] = $obj;

        $obj = new stdClass();
        $obj->type = 'text';
        $obj->code = 'metapitemslimit';
        $obj->value = '-1';
        $config[$obj->code] = $obj;
        
        return $config;
    }
    
    /************************************************/
    /****** Функции для обработки заданий todo ******/
    /************************************************/
    
    /**
     * Изменяет привязку курса к дисциплине
     * @param int $itemid идентификатор дисциплины
     * @param int $mdlcourse идентификатор курса
     * @param int $personid идентификатор персоны, пославшей запрос на смену
     * @return boolean
     */
    public function change_mcourse_in_programmitem($itemid, $mdlcourse, $personid)
    {
        // времени понадобится много
        dof_hugeprocess();

        // сообщаем о том, что начинаем todo
        $this->dof->mtrace(2, '(storage/programmitems:todo) Change mdlcourse for programmitemid=' . $itemid);
        $userid = $this->dof->storage('persons')->get_field($personid, 'mdluser');
        if ( $this->is_access('edit:mdlcourse', $itemid, $userid) )
        {// если есть право - сменим курс мудла
            $pitem = new stdClass();
            $pitem->mdlcourse = $mdlcourse;
            $pitem->id = $itemid;
            $this->update($pitem);
        } else
        {// таких прав нет
            $this->dof->mtrace(2, 'You are not allowed to change course.');
        }

        $this->dof->mtrace(2, '(storage/programmitems:todo) DONE.');

        return true;
    }

    // **********************************************
    //              Собственные методы
    // **********************************************
    /** Получить дисциплину по ее коду
     *
     * @return object - объект, с данными о дисциплине или false
     * @param string $code - код учебной дисциплины
     */
    public function get_pitem_by_code($code)
    {
        return $this->get_record(array('code' => $code));
    }

    /**
     * Список дисциплин, относящихся к учебной программе и учебному периоду
     *
     * @param int $programid - id учебной программы в таблице programs
     * @param int $agenum[optional] - порядковый номер периода или false,
     * обозначающий получение дисциплин отсортированных в порядке
     * возрастания периодов и по названию внутри них
     * @return array - массив записей из таблицы programmitems, удовлетроряющих указанным условиям
     */
    public function get_pitems_list($programmid, $agenum = false, $status = null)
    {
        if ( !$this->dof->storage('programms')->is_exists($programmid) )
        {// не передан необходимый параметр, или такой программы нет в базе
            return false;
        }
        $select = '';
        if ( is_array($status) )
        {
            $select = " AND status IN ('" . implode("','", $status) . "')";
        } elseif ( !is_null($status) )
        {
            $select = ' AND status != \'' . $status . '\'';
        }
        if ( !is_int_string($agenum) )
        {
            return $this->get_records_select('programmid = ' . $programmid . $select, null, 'agenum ASC, name ASC');
        } else
        {
            return $this->get_records_select('programmid = ' . $programmid . ' AND agenum = ' . $agenum . $select, null, 'agenum ASC, name ASC');
        }
    }

    /**
     * Получить максимальное количество периодов для данной учебной программы
     *
     * @param int $programmid - id учебной программы в таблице programms
     * @return bool|int
     *     false - если не нашлось не одной записи
     *     0 - если нашлись только записи с agenum=0, то есть необязательные
     *     в остальных случаях возвращается целое число - максимальное кол-во дисциплин в данном периоде
     * @todo выяснить, как правильно поступать с необязательными дисциплинами
     */
    public function get_maxagenum($programmid)
    {
        $result = $this->get_record(array('programmid' => $programmid), $fields = 'max(agenum) as maxagenum');
        if ( $result === false )
        {// не нашлось ни одной записи с такими параметрами
            return false;
        }
        // возвращает максимальное количество периодов для данной учебной программы
        return (int) $result->maxagenum;
    }

    /** Получить список всех возможных уровней оценки
     *
     * @return array
     */
    public function get_gradelevels()
    {
        return array('notused'    => $this->dof->get_string('notused', 'programmitems', null, 'storage'),
                     'discipline' => $this->dof->get_string('discipline', 'programmitems', null, 'storage'),
                     'coursework' => $this->dof->get_string('coursework', 'programmitems', null, 'storage'),
                     'practice'   => $this->dof->get_string('practice', 'programmitems', null, 'storage'),
                     'finalexam'  => $this->dof->get_string('finalexam', 'programmitems', null, 'storage'),
                     'diplom'     => $this->dof->get_string('diplom', 'programmitems', null, 'storage'));
    }

    /** Получить название уровня оценки по его обозначению
     *
     * @return string
     * @param string $value обозначение уровня оценки в БД
     */
    public function get_gradelevel_name($value)
    {
        if ( $value )
        {
            $levels = $this->get_gradelevels();
            if ( isset($levels[$value]) )
            {// если такой уровень дисциплины зарегестрирован
                return $levels[$value];
            }
            // если уровень дисциплины не опознан - сообщим об этом
            return $this->dof->get_string('unknown_level', 'programmitems');
        }
        return '';
    }

    /** Получить все возможные типы дисциплины
     *
     * @return array
     */
    public function get_types()
    {
        return array('required'    => $this->dof->get_string('type_required', 'programmitems', null, 'storage'),
                     'recommended' => $this->dof->get_string('type_recommended', 'programmitems', null, 'storage'),
                     'free'        => $this->dof->get_string('type_free', 'programmitems', null, 'storage'));
    }

    /** Получить название типа по его идентификатору в базе
     *
     * @return string
     * @param string $value
     */
    public function get_type_name($value)
    {
        if ( $value )
        {
            $types = $this->get_types();
            if ( isset($types[$value]) )
            {// если такой уровень дисциплины зарегестрирован
                return $types[$value];
            }
            // если уровень дисциплины не опознан - сообщим об этом
            return $this->dof->get_string('unknown_type', 'programmitems');
        }
        return '';
    }

    /**
     * Возвращает массив предметов для селекта
     * @param int $programmid - id программы, в которой ищем предметы
     * @param int $agenum - параллель, для которой ищем предметы
     * @return
     */
    public function get_pitems_select_list($programmid, $agenum)
    {
        // найдем предметы указанной программы
        if ( !$this->dof->storage('programms')->is_exists($programmid) )
        {// не передан необходимый параметр, или такой программы нет в базе
            return array();
        }
        $select = "programmid = '" . $programmid . "' AND status = 'active' AND ";
        if ( is_int_string($agenum) )
        {
            $select .= "(agenum = '" . $agenum . "' OR agenum = '0')";
        } else
        {
            $select .= "agenum = '0'";
        }
        if ( !$items = $this->get_records_select($select, null, 'agenum ASC, name ASC') )
        {
            return array();
        }
        $list = array();
        foreach ( $items as $key => $record )
        {
            $list[$key] = $record->name . '[' . $record->code . ']';
        }
        return $list;
    }

    /** Обработка AJAX-запросов из форм
     * @param string $querytype - тип запроса
     * @param int $objectid - id объекта с которым производятся действия
     * @param array $data - дополнительные данные пришедшие из json-запроса
     *
     * @return array
     */
    public function widgets_field_ajax_select($querytype, $objectid, $data)
    {
        switch ( $querytype )
        {
            case 'list_programmitems': return $this->widgets_newitem_form_variants($data);
            default: return array(0 => '--- ' . $this->dof->modlib('ig')->igs('choose') . ' ---');
        }
    }

    /**
     * Получить список вариантов выбора при выдаче одного комплекта
     * Выбираются комплекты определенной категории + все комплекты дочерних категорий
     * @todo оптимизировать выборку по дочерним категориям
     * @todo добавить сортировку по названию категории, к которой принадлежит комплект
     *
     * @param object $data - данные для запроса: подразделение и родит категория
     * @return array массив для подстановки в select-список
     */
    protected function widgets_newitem_form_variants($data)
    {

        $programmid = $data['parentvalue'];
        //$depid = $data['departmentid'];
        $result = array(0 => '--- ' . $this->dof->modlib('ig')->igs('choose') . ' ---');
        if ( !$programmid )
        {
            return $result;
        }
        if ( !$pitems = $this->dof->storage('programmitems')->get_records
                (array('programmid' => $programmid, 'status' => array('active', 'suspend')), 'name') )
        {
            return $result;
        }
        foreach ( $pitems as $id => $pitem )
        {// составляем название комплекта: категория + код
            $result[$id] = $pitem->name . ' [' . $pitem->code . ']';
        }
        return $result;
    }
    /*
     * ДОБАВЛЕННЫЕ МЕТОДЫ ДЛЯ РАБОТЫ С МЕТАДИСЦИПЛИНАМИ
     */

    /**
     * Список метадисциплин, относящихся к параллели, подразделению
     * @return array - массив записей из таблицы programmitems, удовлетроряющих указанным условиям
     */
    public function get_metapitems_list($depid = null, $agenum = false)
    {
        $select = 'metaprogrammitemid = 0 AND ';
        if ( $depid !== null )
        {
            $select .= 'departmentid = ' . $depid . ' AND ';
        }

        if ( is_int_string($agenum) )
        {
            $select .= "(agenum = '" . $agenum . "' OR agenum = '0')";
        } else
        {
            $select .= "agenum = '0'";
        }

        return $this->get_records_select($select, null, 'agenum ASC, name ASC');
    }

    /**
     * Список метадисциплин, относящихся к подразделению со статусом "активен"
     * @param int $depid [optional] - id подразделения
     * @return int - количество метадисциплин в заданном подразделении(либо во всех подразделениях)
     */
    public function get_metapitems_count($depid = 0)
    {
        $cond = array('metaprogrammitemid' => 0, 'status' => 'active');
        if ( $depid != 0 )
        {
            $cond['departmentid'] = $depid;
        }

        return $this->dof->storage($this->code())->count_list($cond);
    }

    /**
     * Лимит метадисциплин, получаемый из настроек
     * @return int-лимит метадисциплин
     */
    public function get_limit_metapitems()
    {
        $depid = optional_param('departmentid', 0, PARAM_INT);

        $num = $this->dof->storage('config')->get_config_value('metapitemslimit', 'storage', $this->code(), $depid);

        if ( $num == '-1' )
        {// бесконечно много
            return '&#8734;';
        }
        if ( $num )
        {// определенное кол-во
            return $num;
        }
        return '';
    }
    
    /**
     * Проверка, не достигнут ли лимит метадисциплин
     *
     * @param int $depid [optional] - id подразделения
     * @return bool - true-лимит не достигнут
     */
    public function check_limit_metapitems($depid = null)
    {
        if ( strcmp($this->get_limit_metapitems(), '&#8734;') == 0 )
        {
            return true;
        }
        if ( $this->get_limit_metapitems() > $this->get_metapitems_count($depid) )
        {
            return true;
        }
        return false;
    }

    public function sync_pitem_with_metapitems($pitem)
    {
        //получим данные метадисциплины
        $programmitemmeta = $this->dof->storage('programmitems')->get($pitem->metaprogrammitemid);

        //Уберем поля, которые не будем синхронизировать
        unset($programmitemmeta->status);
        unset($programmitemmeta->code);
        unset($programmitemmeta->name);
        unset($programmitemmeta->agenum);
        unset($programmitemmeta->programmid);
        unset($programmitemmeta->metaprogrammitemid);
        unset($programmitemmeta->metasyncon);
        unset($programmitemmeta->agenum);
        unset($programmitemmeta->notice);
        unset($programmitemmeta->about);
        unset($programmitemmeta->departmentid);

        return $this->dof->storage('programmitems')->update($programmitemmeta, $pitem->id, true);
    }

    /**
     * Получить общее количество часов (hours) по формуле:
     *  hourstheory + hourslab + hourspractice + hoursind + hourscontrol
     *
     * @param int|object $pitemid - id из таблицы programmitems или объект с полями:
     *       'hourstheory', 'hourslab', 'hourspractice', 'hoursind', 'hourscontrol'
     * @return bool|int - false в случае ошибки или hours по формуле
     */
    public function get_total_hours($pitemid)
    {
        // Поля, по которым суммируем часы
        $fields = array('hourstheory', 'hourslab', 'hourspractice', 'hoursind', 'hourscontrol');
        $pitem = false;
        if ( is_int_string($pitemid) AND $this->is_exists($pitemid) )
        {
            if ( !$pitem = $this->get($pitemid) )
            {
                return false;
            }
        } else if ( is_object($pitemid) )
        {
            $pitem = $pitemid;
            // Проверим, все ли поля есть
            foreach ( $fields as $field )
            {
                if ( empty($pitemid->$field) )
                {
                    $pitemid->$field = 0;
                }
            }
        } else
        {//
            return false;
        }
        $hours = 0;
        foreach ( $fields as $field )
        {
            $hours += $pitem->$field;
        }
        return $hours;
    }

    /**
     * Получить общее количество аудиторных часов (hours) по формуле:
     *  hourstheory + hourslab + hourspractice
     *
     * @param int|object $pitemid - id из таблицы programmitems или объект с полями:
     *       'hourstheory', 'hourslab', 'hourspractice'
     * @return bool|int - false в случае ошибки или hours по формуле
     */
    public function get_classroom_hours($pitemid)
    {
        // Поля, по которым суммируем часы
        $fields = array('hourstheory', 'hourslab', 'hourspractice');
        $pitem = false;
        if ( is_int_string($pitemid) AND $this->is_exists($pitemid) )
        {
            if ( !$pitem = $this->get($pitemid) )
            {
                return false;
            }
        } else if ( is_object($pitemid) )
        {
            $pitem = $pitemid;
            // Проверим, все ли поля есть
            foreach ( $fields as $field )
            {
                if ( empty($pitemid->$field) )
                {
                    $pitemid->$field = 0;
                }
            }
            $pitem = $pitemid;
        } else
        {//
            return false;
        }
        $hours = 0;
        foreach ( $fields as $field )
        {
            $hours += $pitem->$field;
        }
        return $hours;
    }

    /** Получить фрагмент списка учебных предметов для вывода таблицы
     *
     * @param object $conds - список параметров для выборки предметов
     * @param int $limitfrom - начиная с какой записи просматривается фрагмент списка записей
     * @param int $limitnum - сколько записей нужно извлечь из базы
     * @return array массив записей из базы, или false в случае ошибки
     */
    public function get_listing($conds = null, $limitfrom = null, $limitnum = null, $sort = '', $fields = '*', $countonly = false)
    {
        if ( !$conds )
        {// если список предметов не передан - то создадим объект, чтобы не было ошибок
            $conds = new stdClass();
        }
        if ( $limitnum <= 0 AND ! is_null($limitnum) )
        {// количество записей на странице может быть
            //только положительным числом
            $limitnum = $this->dof->modlib('widgets')->get_limitnum_bydefault();
        }
        if ( $limitfrom < 0 AND ! is_null($limitfrom) )
        {//отрицательные значения номера просматриваемой записи недопустимы
            $limitfrom = 0;
        }
        $countselect = $this->get_select_listing($conds);
        // посчитаем общее количество записей, которые нужно извлечь
        $recordscount = $this->dof->storage('programmitems')->count_records_select($countselect);
        if ( $recordscount < $limitfrom )
        {// если количество записей в базе меньше,
            //чем порядковый номер записи, которую надо показать
            //покажем последнюю страницу
            $limitfrom = $recordscount;
        }
        //формируем строку запроса
        $select = $this->get_select_listing($conds);
        //определяем порядок сортировки
        $sort = 'name ASC, departmentid ASC, eduweeks ASC, status ASC';
        // возвращаем ту часть массива записей таблицы, которую нужно
        return $this->dof->storage('programmitems')->get_records_select($select, null, $sort, '*', $limitfrom, $limitnum);
    }

    /** Возвращает фрагмент sql-запроса после слова WHERE
     * @param object $inputconds - список полей с условиями запроса в формате "поле_БД->значение"
     * @return string
     */
    public function get_select_listing($inputconds)
    {
        // создадим массив для фрагментов sql-запроса
        $selects = array();
        $conds = fullclone($inputconds);
        if ( isset($conds->nameorcode) AND strlen(trim($conds->nameorcode)) )
        {// для имени используем шаблон LIKE
            // для кода используем будем проверять соответствие коду в стандарте
            $selects[] = " ( name LIKE '%" . $conds->nameorcode . "%' OR sname LIKE '%" . $conds->nameorcode . "%' OR
                                code = '" . $conds->nameorcode . "' OR scode = '" . $conds->nameorcode . "') ";
            // убираем имя из запроса для того чтобы не создать 2 условия для одного поля
            unset($conds->nameorcode);
        }

        if ( isset($conds->name) AND trim($conds->name) )
        {// для имени используем шаблон LIKE
            $selects[] = " name LIKE '%" . $conds->name . "%' OR sname LIKE '%" . $conds->name . "%'";
            // убираем имя из запроса для того чтобы не создать 2 условия для одного поля
            unset($conds->name);
        }
        if ( isset($conds->code) AND trim($conds->code) )
        {// для кода используем будем проверять соответствие коду в стандарте
            $selects[] = " code = '" . $conds->code . "' OR scode = '" . $conds->code . "' ";
            // убираем код из запроса для того чтобы не создать 2 условия для одного поля
            unset($conds->code);
        }
        if ( isset($conds->agenum) AND ! is_null($conds->agenum) )
        {// для кода используем будем проверять соответствие коду в стандарте
            $selects[] = " agenum = " . $conds->agenum;
            // убираем код из запроса для того чтобы не создать 2 условия для одного поля
            unset($conds->agenum);
        }
        //Добавляем фрагмент для хранения индекса метадисциплины
        if ( isset($conds->metaprogrammitemid) AND ! is_null($conds->metaprogrammitemid) )
        {// для кода используем будем проверять соответствие коду в стандарте
            $selects[] = " metaprogrammitemid = " . $conds->metaprogrammitemid;
            // убираем код из запроса для того чтобы не создать 2 условия для одного поля
            unset($conds->metaprogrammitemid);
        }
        
        if ( ! empty($conds->verificationrequested) )
        {// требуется отобрать только те курсы, которые требуют проверки контента мастер-курса
            $selects[] = " verificationrequested = 1 ";
            // убираем код из запроса для того чтобы не создать 2 условия для одного поля
            unset($conds->verificationrequested);
        }
        // теперь создадим все остальные условия
        foreach ( $conds as $name => $field )
        {
            if ( $field )
            {// если условие не пустое, то для каждого поля получим фрагмент запроса
                $selects[] = $this->dof->storage('programms')->query_part_select($name, $field);
            }
        }
        //формируем запрос
        if ( empty($selects) )
        {// если условий нет - то вернем пустую строку
            return '';
        } elseif ( count($selects) == 1 )
        {// если в запросе только одно поле - вернем его
            return current($selects);
        } else
        {// у нас несколько полей - составим запрос с ними, включив их всех
            return implode($selects, ' AND ');
        }
    }

    /**
     * Обновить hours и hoursclassroom в справочнике programmitems
     */
    protected function todo_count_hours()
    {
        // времени понадобится много
        dof_hugeprocess();

        $num = 0;
        // сообщаем о том, что начинаем todo
        $this->dof->mtrace(2, '(storage/programmitems:todo) Counting hours');
        // Мусорные статусы не просчитываем
        $junkstatuses = $this->dof->workflow($this->code())->get_meta_list('junk');
        while ( $programmitems = $this->get_records(array(), '', 'id,status', $num, 100) )
        {// собираем все записи об изучаемых или пройденных курсах, которые надо перезапустить
            $num += 100;
            foreach ( $programmitems as $id => $pitem )
            {
                // Если статус не мусорный
                if ( !array_key_exists($pitem->status, $junkstatuses) )
                {
                    $this->dof->mtrace(2, 'Counting for programmitemid = ' . $id);
                    $mixedvar = array();
                    $mixedvar['new'] = new stdClass();
                    $mixedvar['new']->id = $id;
                    $mixedvar['old'] = new stdClass();
                    // Просто пересылаем event, а там автоматически просчитается всё
                    $this->dof->send_event('storage', 'programmitems', 'update', null, $mixedvar);
                } else
                { // Если это удалённая дисциплина
                    // Ничего не делаем
                    $this->dof->mtrace(2, 'deleted programmitemid = ' . $id);
                }
            }
        }

        $this->dof->mtrace(2, '(storage/programmitems:todo) DONE.');
        return true;
    }
    
    /**
     * Получить суммарное количество часов и ЗЕТ по нескольким предметам
     *
     * @param array $programmitemids - массив id из таблицы programmitems
     * @return bool|object - false в случае ошибки, или объект с полями:
     * ->maxcredit
     * ->hours
     * ->hourstheory
     * ->hourspractice
     * ->hoursweek
     * ->hourslab
     * ->hoursind
     * ->hourscontrol
     * ->hoursclassroom
     */
    public function get_hours_sum($programmitemids)
    {
        if ( !is_array($programmitemids) )
        {
            return false;
        }
        $fields = array('maxcredit', 'hours', 'hourstheory', 'hourspractice',
                        'hoursweek', 'hourslab', 'hoursind', 'hourscontrol',
                        'hoursclassroom');
        
        $sum = new stdClass();
        foreach ( $fields as $field )
        {
            $sum->$field = 0;
        }
        
        foreach ( $programmitemids as $pitemid )
        {
            if ( $this->is_exists($pitemid) )
            {
                $pitem = $this->get($pitemid, 'id,'. implode(',', $fields));
            } else
            {
                continue;
            }
            foreach ( $fields as $field )
            {
                if ( !empty($pitem->$field) )
                {
                    $sum->$field += $pitem->$field;
                }
            }
        }
        return $sum;
    }
    
    /**
     * Сохранить дисциплину
     *
     * @param string|stdClass|array $programmitemdata - Данные дисциплины(название или комплексные данные)
     * @param array $options - Массив дополнительных параметров
     *
     * @return bool|int - false в случае ошибки или ID дисциплины в случае успеха
     *
     * @throws dof_exception_dml - В случае ошибки
     */
    public function save($programmitemdata = null, $options = [])
    {
        // Нормализация данных
        try {
            $normalized_data = $this->normalize($programmitemdata, $options);
        } catch ( dof_exception_dml $e )
        {
            throw new dof_exception_dml('error_save_'.$e->errorcode);
        }
    
        // Сохранение данных
        if ( isset($normalized_data->id) && $this->is_exists($normalized_data->id) )
        {// Обновление записи
            $programmitem = $this->update($normalized_data);
            if ( empty($programmitem) )
            {// Обновление не удалось
                throw new dof_exception_dml('error_save_programmitem');
            } else
            {// Обновление удалось
                $this->dof->send_event('storage', 'programmitems', 'item_saved', (int)$normalized_data->id);
                return $normalized_data->id;
            }
        } else
        {// Создание записи
            $programmitemid = $this->insert($normalized_data);
            if ( ! $programmitemid )
            {// Добавление не удалось
                throw new dof_exception_dml('error_save_programmitem');
            } else
            {// Добавление удалось
                $this->dof->send_event('storage', 'programmitems', 'item_saved', (int)$programmitemid);
                if( ! empty($options['activate']) )
                {// Требуется активация объекта
                    $this->dof->send_event('storage', 'programmitems', 'activate_request', (int)$programmitemid);
                }
                return $programmitemid;
            }
        }
        return false;
    }
    
    /**
     * Нормализация данных дисциплины
     *
     * Формирует объект дисциплины на основе переданных данных. В случае критической ошибки
     * или же если данных недостаточно, выбрасывает исключение.
     *
     * @param string|stdClass|array $programmitemdata - Данные дисциплины(название или комплексные данные)
     * @param array $options - Опции работы
     *
     * @return stdClass - Нормализовализованный Объект подразделения
     * @throws dof_exception_dml - Исключение в случае критической ошибки или же недостаточности данных
     */
    public function normalize($programmitemdata, $options = [])
    {
        // Нормализация входных данных
        if ( is_object($programmitemdata) || is_array($programmitemdata) )
        {// Комплексные данные
            $programmitemdata = (object)$programmitemdata;
        } else
        {// Неопределенные данные
            throw new dof_exception_dml('invalid_data');
        }
        
        // Проверка входных данных
        if ( empty($programmitemdata) )
        {// Данные не переданы
            throw new dof_exception_dml('empty_data');
        }
        
        // Нормализация идентификатора
        if ( isset($departmentdata->id) && (int)$departmentdata->id < 1)
        {
            unset($departmentdata->id);
        }
        
        if ( isset($programmitemdata->id) )
        {// Проверка на существование
            if ( ! $this->get($programmitemdata->id) )
            {// Дисциплина не найдена
                throw new dof_exception_dml('programmitem_not_found');
            }
        }
        
        if ( ! isset($programmitemdata->id) && empty($programmitemdata->name) )
        {// Невозможно определить дисциплину
            throw new dof_exception_dml('create_without_name');
        }
        
        if ( ! isset($programmitemdata->id) && empty($programmitemdata->programmid) )
        {// Невозможно определить дисциплину
            throw new dof_exception_dml('create_without_programm');
        }
        
        // Нормализация программы
        if ( isset($programmitemdata->programmid) )
        {
            if ( ! $this->dof->storage('programms')->get($programmitemdata->programmid) )
            {// Программа не найдена
                throw new dof_exception_dml('programm_not_found');
            }
        }
    
        // Создание объекта для сохранения
        $saveobj = clone $programmitemdata;
    
        // Обработка входящих данных и построение объекта подразделения
        if ( isset($saveobj->id) && $this->is_exists($saveobj->id) )
        {// Подразделение уже содержится в системе
            // Удаление автоматически генерируемых полей
            unset($saveobj->status);
            unset($saveobj->path);
            unset($saveobj->depth);
        } else
        {// Новое подразделение
            // АВТОЗАПОЛНЕНИЕ ПОЛЕЙ
            if ( empty($saveobj->required) )
            {
                $saveobj->required = 1;
            }
            if ( empty($saveobj->controltypeid) )
            {
                $saveobj->controltypeid = 1;
            }
            if ( empty($saveobj->scale) )
            {
                $saveobj->scale = '1,2,3,4,5';
            }
            if ( empty($saveobj->mingrade) )
            {
                $saveobj->mingrade = 1;
            }
            if ( empty($saveobj->gradesyncenabled) )
            {
                $saveobj->gradesyncenabled = 0;
            }
            if ( empty($saveobj->incjournwithoutgrade) )
            {
                $saveobj->incjournwithoutgrade = 0;
            }
            if ( empty($saveobj->salfactor) )
            {
                $saveobj->salfactor = 0;
            }
            if ( empty($saveobj->instrlevelid) )
            {
                $saveobj->instrlevelid = 1;
            }
            if ( empty($saveobj->agenum) )
            {
                $saveobj->agenum = 0;
            }
            if ( empty($saveobj->type) )
            {
                $saveobj->type = 'required';
            }
            if ( empty($saveobj->departmentid) )
            {
                $saveobj->departmentid = 0;
            }
            
            // АВТОМАТИЧЕСКИ ГЕНЕРИРУЕМЫЕ ПОЛЯ
            if ( ! $this->dof->plugin_exists('workflow', 'departments') )
            {// Плагин статусов подразделений не активен, установка статуса по умолчанию
                $saveobj->status = 'active';
            } else
            {// Статус назначается в плагине статусов
                unset($saveobj->status);
            }
        }
    
        // НОРМАЛИЗАЦИЯ ПОЛЕЙ
        // Нормализация названия
        if ( isset($saveobj->name) )
        {
            $saveobj->name = (string)$saveobj->name;
        }
        // Нормализация кода
        if ( isset($saveobj->code) )
        {
            $saveobj->code = (string)$saveobj->code;
        }
    
        // Нормализация подразделения
        if( isset($saveobj->departmentid) )
        {
            if( ! $this->dof->storage('departments')->is_exists((int)$saveobj->departmentid) )
            {
                $saveobj->departmentid = (int)$saveobj->departmentid;
            }
        }
    
        return $saveobj;
    }
    
    /**
     * Поиск дисциплин с удаленным курсами, отвязка удаленных курсов от дисциплин
     */
    protected function check_deleted_courses()
    {
        dof_mtrace(3, $this->dof->get_string('start_check_deleted_courses', $this->code(), null, $this->type()));
        $mdlcourses = $programmitems = $courses = [];
        $key = false;
        $result = true;
        // Получим реальные статусы дисциплин
        $statuses = array_keys($this->dof->workflow('programmitems')->get_meta_list('real'));
        // Выберем все дисциплины из базы
        $pitems = $this->dof->storage('programmitems')->get_records(['status' => $statuses]);
        if( ! empty($pitems) )
        {
            dof_mtrace(3, $this->dof->get_string('programmitems_recieved', $this->code(), null, $this->type()));
            foreach($pitems as $pitem)
            {
                if( ! empty($pitem->mdlcourse) )
                {// Берем только те дисциплины, где есть привязка к курсу
                    // Сложим их именно так, т.к. дальше понадобится поиск дисциплины по курсу
                    $mdlcourses[$pitem->id] = $pitem->mdlcourse;
                }
            }
        }
        if( ! empty($mdlcourses) )
        {
            dof_mtrace(3, $this->dof->get_string('courses_recieved', $this->code(), null, $this->type()));
            // Получим идентификаторы курсов без повторов (один и тот же курс может быть привязан к нескольким дисциплинам)
            $mdlcoursesid = array_values(array_unique($mdlcourses));
            // Получим курсы по идентификаторам, которые мы получили из дисциплин
            $courses = $this->dof->modlib('ama')->course(false)->get_list(['id' => $mdlcoursesid]);
            // Получим разницу между курсами, привязанными к дисциплинам, и курсами реально существующими в системе
            $delcourses = array_diff($mdlcoursesid, array_keys($courses));
            if( ! empty($delcourses) )
            {// Если нашли курсы, которые были удалены, но еще связаны с дисциплинами
                $userid = $this->dof->storage('persons')->get_bu()->mdluser;
                foreach($delcourses as $delcourse)
                {// Если курс уже удален, пользователи уже отписаны, нет смысла останавливать cstream и cpassed'ы,
                    // можно просто отвязать курс и удалить привязку групп к учебным процессам
                    if( ! $this->dof->modlib('ama')->course(false)->is_exists($delcourse) )
                    {// Проверим, что курс действительно удален
                        $a = new stdClass();
                        $a->id = $delcourse;
                        dof_mtrace(3, $this->dof->get_string('course_realy_deleted', $this->code(), $a, $this->type()));
                        while( $key = array_search($delcourse, $mdlcourses) )
                        {// Ищем все дисциплины, связанные с курсом
                            $programmitems[] = $key;
                            unset($mdlcourses[$key]);
                        }
                        if( ! empty($programmitems) )
                        {
                            foreach($programmitems as $programmitem)
                            {
                                $a = new stdClass();
                                $a->programmitemid = $programmitem;
                                if( $rez = $this->change_mcourse_in_programmitem($programmitem, null, $userid) )
                                {// Смена прошла успешно
                                    dof_mtrace(3, $this->dof->get_string(
                                        'change_mdlcourse_successful',
                                        $this->code(),
                                        $a,
                                        $this->type()
                                    ));
                                } else
                                {// Смена не удалась
                                    dof_mtrace(3, $this->dof->get_string(
                                        'change_mdlcourse_not_successful',
                                        $this->code(),
                                        $a,
                                        $this->type()
                                    ));
                                }
                                $result = $result && $rez;
                                // Получим учебные процессы, связанные с дисциплиной
                                $cstreams = $this->dof->storage('cstreams')->get_programmitem_cstream($programmitem);
                                if( ! empty($cstreams) )
                                {
                                    foreach($cstreams as $cstream)
                                    {// Удалим привязку групп к учебным процессам
                                        $a->cstreamid = $cstream->id;
                                        if( $rez = $this->dof->sync('courseenrolment')->mdl_delete_cstream_group($cstream->id) )
                                        {// Привязка удалена
                                            dof_mtrace(3, $this->dof->get_string(
                                                'cstream_group_was_delete',
                                                $this->code(),
                                                $a,
                                                $this->type()
                                            ));
                                        } else
                                        {// Не удалось удалить привязку
                                            dof_mtrace(3, $this->dof->get_string(
                                                'cstream_group_was_not_delete',
                                                $this->code(),
                                                $a,
                                                $this->type()
                                            ));
                                        }
                                        $result = $result && $rez;
                                    }
                                }
                            }
                        }
                    }
                }
            } else
            {
                dof_mtrace(3, $this->dof->get_string('delcourses_not_recieved', $this->code(), null, $this->type()));
            }
        }
        return $result;
    }
    
    /**
     * Получить название целевой дисциплины
     *
     * @param stdClass|int $programmitem - Дисциплина, или ID дисциплины
     *
     * @return string|null
     */
    public function get_name($programmitem)
    {
        if ( isset($programmitem->name) )
        {// Передан объект дисциплины
            return (string)$programmitem->name;
        }
    
        // Получение данных по ID дисциплины
        $name = $this->get_field((int)$programmitem, 'name');
        if ( $name === false )
        {// Ошибка получения имени
            return null;
        }
        return (string)$name;
    }
    
    /**
     * Получить настройку самостоятельной записи по целевой дисциплине
     *
     * @param stdClass|int $programmitem - Дисциплина, или ID дисциплины
     *
     * @return int|null
     */
    public function get_selfenrol($programmitem)
    {
        if ( isset($programmitem->selfenrol) )
        {// Передан объект дисциплины
            return (int)$programmitem->selfenrol;
        }
        
        // Получение данных по ID дисциплины
        $selfenrol = $this->get_field((int)$programmitem, 'selfenrol');
        if ( $selfenrol === false )
        {// Ошибка получения настройки
            return null;
        }
        return (int)$selfenrol;
    }
    
    /**
     * Получить настройку лимита обучающихся студентов по целевой дисциплине
     *
     * @param stdClass|int $programmitem - Дисциплина, или ID дисциплины
     *
     * @return int|null
     */
    public function get_studentslimit($programmitem)
    {
        if ( isset($programmitem->studentslimit) )
        {// Передан объект дисциплины
            return (int)$programmitem->studentslimit;
        }
    
        // Получение данных по ID дисциплины
        $studentslimit = $this->get_field((int)$programmitem, 'studentslimit');
        if ( $studentslimit === false )
        {// Ошибка получения настройки
            return null;
        }
        return (int)$studentslimit;
    }

    /**
     * Получить массив дисциплин по курсу Moodle
     */
    public function get_pitems_by_mdlcourse($mdlcourse, $statuses=[])
    {
        $conditions = [
            'mdlcourse' => $mdlcourse
        ];
        if( ! empty($statuses) )
        {
            $conditions['status'] = $statuses;
        }
        $programmitems = $this->dof->storage('programmitems')->get_records($conditions);
        if( ! empty($programmitems) )
        {
            return $programmitems;
        } else
        {
            return [];
        }
    }

    /**
     * Запросить проверку мастер-курса
     *
     * @param int $programmitemid - идентификатор дисциплины
     * @throws dof_exception
     * @return boolean
     */
    public function request_coursedata_verification($programmitemid, $options=[])
    {
        if ( $this->is_access('edit:verificationrequested', $programmitemid) || ! empty($options['external_capability_verified']) )
        {
            $programmitem = $this->get($programmitemid);
            

            if( empty($programmitem) )
            {// не удалось получить дисциплину
                throw new dof_exception(
                    $this->dof->get_string('programmitem_not_found','programmitems',null,'storage')
                );
            }
            if( empty($programmitem->mdlcourse) )
            {// не удалось определить курс дисциплины
                throw new dof_exception(
                    $this->dof->get_string('mdlcourse_not_found','programmitems',null,'storage')
                );
            }
            // получение курса moodle
            $mdlcourse = $this->dof->sync('mcourses')->get_course($programmitem->mdlcourse);
            // получение текущей персоны
            $person = $this->dof->storage('persons')->get_bu(NULL, true);
            
            $programmitem->verificationrequested = 1;
            $savepitemresult = $this->save($programmitem);
            if($savepitemresult == false)
            {// сохранение обновленной дисциплины
                throw new dof_exception(
                    $this->dof->get_string('programmitem_save_failed', 'programmitems', null, 'storage')
                );
            }
            
            // Отправка уведомлений списку пользователей, которые могут согласовывать данные мастер-курса
            // Именно им интересно получить информацию о том, что новая версия готова и ждет согласования
            $a = new stdClass();
            $a->course = $mdlcourse->fullname;
            $a->discipline = $programmitem->name;
            $a->initiator = $this->dof->storage('persons')->get_fullname($person);
            // уведомление всем, у кого есть полномочие для дисциплины в деканате
            $notificationresult = $this->dof->sync('messager')->notify_persons_with_acl(
                //$this->dof->get_string('coursedata_verification_requested_mail_subject', 'programmitems', $a, 'storage'),
                $this->dof->get_string('coursedata_verification_requested_mail_text', 'programmitems', $a, 'storage'),
                'storage',
                'programmitems',
                'benotified:edit:verificationrequested',
                $programmitem->departmentid,
                $programmitem->id,
                ['userfrom' => 'support_user']
            );

            // Отправка события о запросе
            $eventdata = [
                'course' => $a->course,
                'discipline' => $a->discipline,
                'initiator' => $a->initiator,
                'notificationresult' => $notificationresult
            ];
            $this->dof->send_event('storage', 'programmitems', 'coursedata_verification_requested', $mdlcourse->id, $eventdata);
            
            return true;
            
        } else
        {
            throw new dof_exception(
                $this->dof->get_string('edit_verificationrequested_access_denied','programmitems',null,'storage')
            );
        }
    }

    /**
     * Одобрить и обновить версию мастер-курса
     *
     * @param int $programmitemid - идентификатор дисциплины
     * @throws dof_exception
     * @return boolean
     */
    public function accept_coursedata($programmitemid, $options=[])
    {
        if ( $this->is_access('edit:coursetemplateversion', $programmitemid) || ! empty($options['external_capability_verified']) )
        {// есть права на сохранение шаблона курса
            $programmitem = $this->get($programmitemid);
            
            // текущая версия бэкапа
            $actualversion = $programmitem->coursetemplateversion;
            
            if( empty($programmitem) )
            {// не удалось получить дисциплину
                throw new dof_exception(
                    $this->dof->get_string('programmitem_not_found','programmitems',null,'storage')
                );
            }
            if( empty($programmitem->mdlcourse) )
            {// не удалось определить курс дисциплины
                throw new dof_exception(
                    $this->dof->get_string('mdlcourse_not_found','programmitems',null,'storage')
                );
            }
            // получение курса moodle
            $mdlcourse = $this->dof->sync('mcourses')->get_course($programmitem->mdlcourse);
            
            $newversion = time();
            
            $fileoptions = [
                'filearea' => 'im_programmitems_programmitem_coursetemplate',
                'itemid' => $programmitem->id,
                'filepath' => '/',
                'filename' => $newversion.'.mbz'
            ];
            $backupoptions = [
                'activities' => 1,
                'blocks' => 1,
                'filters' => 1,
                'users' => 0,
                'role_assignments' => 0,
                'comments' => 0,
                'userscompletion' => 0,
                'logs' => 0,
                'grade_histories' => 0
            ];
            // сохранение шаблона курса с привязкой к дисциплине
            $backupresult = $this->dof->sync('mcourses')->create_backup($mdlcourse->id, $backupoptions, $fileoptions);
            if ( $backupresult == false )
            {// не удалось создать шаблон курса
                throw new dof_exception(
                    $this->dof->get_string('coursedata_backup_failed', 'programmitems', null, 'storage')
                );
            }
            
            // сохраняем в дисциплину новую версию и сбрасываем флаг согласования
            $programmitem->verificationrequested = 0;
            $programmitem->coursetemplateversion = $newversion;
            $savepitemresult = $this->save($programmitem);
            if($savepitemresult == false)
            {// сохранение обновленной дисциплины
                throw new dof_exception(
                    $this->dof->get_string('programmitem_save_failed', 'programmitems', null, 'storage')
                );
            }
            
            // либо сохраняем все версии
            // либо удаляем последнюю версию в зависимости от конфига
            if ( ! empty($actualversion) )
            {
                $savemdltype = $this->dof->storage('config')->get_config_value('mdlbackups_save_all', 'sync', 'mcourses', $programmitem->departmentid);
                
                if ( empty($savemdltype) )
                {
                    // новый файл сохранен, версия в дисциплине обновлена,
                    // удаление старой версии
                    $this->dof->send_event(
                            'storage',
                            'programmitems',
                            'delete_backupfile',
                            $programmitem->id,
                            [
                                'filearea' => 'im_programmitems_programmitem_coursetemplate',
                                'itemid' => $programmitem->id,
                                'filepath' => '/',
                                'filename' => $actualversion . '.mbz'
                            ]);
                }
            }
            
            // Получение списка пользователей, которые могут отправлять запрос на проверку
            // Именно им интересно получить ответ о том, приняли их версию контента или нет
            $a = new stdClass();
            $a->course = $mdlcourse->fullname;
            $a->discipline = $programmitem->name;
            // уведомление всем, у кого есть полномочие для дисциплины в деканате
            $notificationresult = $this->dof->sync('messager')->notify_persons_with_acl(
                //$this->dof->get_string('coursedata_accepted_mail_subject', 'programmitems', $a, 'storage'),
                $this->dof->get_string('coursedata_accepted_mail_text', 'programmitems', $a, 'storage'),
                'storage',
                'programmitems',
                'benotified:edit:coursetemplateversion',
                $programmitem->departmentid,
                $programmitem->id,
                ['userfrom' => 'support_user']
            );

            // Отправка события о положительном решении
            $eventdata = [
                'course' => $a->course,
                'discipline' => $a->discipline,
                'notificationresult' => $notificationresult
            ];
            $this->dof->send_event('storage', 'programmitems', 'coursedata_accepted', $mdlcourse->id, $eventdata);
            
            return true;
        } else
        {
            throw new dof_exception(
                $this->dof->get_string('edit_coursetemplate_access_denied','programmitems',null,'storage')
            );
        }
    }

    /**
     * Отклонить предложенное обновление версии мастер-курса
     *
     * @param int $programmitemid - идентификатор дисциплины
     * @throws dof_exception
     * @return boolean
     */
    public function decline_coursedata($programmitemid, $options=[])
    {
        if ( $this->is_access('edit:coursetemplateversion', $programmitemid) || ! empty($options['external_capability_verified']) )
        {
            $programmitem = $this->get($programmitemid);
            

            if( empty($programmitem) )
            {// не удалось получить дисциплину
                throw new dof_exception(
                    $this->dof->get_string('programmitem_not_found','programmitems',null,'storage')
                );
            }
            if( empty($programmitem->mdlcourse) )
            {// не удалось определить курс дисциплины
                throw new dof_exception(
                    $this->dof->get_string('mdlcourse_not_found','programmitems',null,'storage')
                );
            }
            // получение курса moodle
            $mdlcourse = $this->dof->sync('mcourses')->get_course($programmitem->mdlcourse);
            
            $programmitem->verificationrequested = 0;
            $savepitemresult = $this->save($programmitem);
            if($savepitemresult == false)
            {// сохранение обновленной дисциплины
                throw new dof_exception(
                    $this->dof->get_string('programmitem_save_failed', 'programmitems', null, 'storage')
                );
            }

            // Получение списка пользователей, которые могут отправлять запрос на проверку
            // Именно им интересно получить ответ о том, приняли их версию контента или нет
            $a = new stdClass();
            $a->course = $mdlcourse->fullname;
            $a->discipline = $programmitem->name;
            // уведомление всем, у кого есть полномочие для дисциплины в деканате
            $notificationresult = $this->dof->sync('messager')->notify_persons_with_acl(
                //$this->dof->get_string('coursedata_declined_mail_subject', 'programmitems', $a, 'storage'),
                $this->dof->get_string('coursedata_declined_mail_text', 'programmitems', $a, 'storage'),
                'storage',
                'programmitems',
                'benotified:edit:coursetemplateversion',
                $programmitem->departmentid,
                $programmitem->id,
                ['userfrom' => 'support_user']
            );

            // Отправка события о положительном решении
            $eventdata = [
                'course' => $a->course,
                'discipline' => $a->discipline,
                'notificationresult' => $notificationresult
            ];
            $this->dof->send_event('storage', 'programmitems', 'coursedata_declined', $mdlcourse->id, $eventdata);
            
            return true;
        } else
        {
            throw new dof_exception(
                $this->dof->get_string('edit_coursetemplate_access_denied','programmitems',null,'storage')
            );
        }
    }
    
    /**
     * Получение ссылки на мастер-курс
     *
     * @param int $mdlcourse
     *
     * @return false | string
     */
    public function get_mastercourse_url($mdlcourse)
    {
        if ( (int)$mdlcourse > 0 )
        {
            $cstreams = $this->dof->storage('cstreams')->get_records([
                'mdlcourse' => (int)$mdlcourse
            ]);
            
            if( ! empty($cstreams) )
            {
                foreach($cstreams as $cstream)
                {
                    if( ! empty($cstream->programmitemid) )
                    {
                        $pitem = $this->get($cstream->programmitemid);
                        if( ! empty($pitem->mdlcourse) )
                        {
                            $courseurl = $this->dof->sync('mcourses')->get_course_link($pitem->mdlcourse);
                            if( ! empty($courseurl))
                            {
                                return $courseurl;
                            }
                        }
                    }
                }
            }
        }
        
        return false;
    }


    /**
     * Получение дисциплин, связанных с курсом
     *
     * @param int $mdlcourse
     *
     * @return false | string
     */
    public function get_mastercourse_programmitems($mdlcourse)
    {
        $activestatuses = $this->dof->workflow('programmitems')->get_meta_list('active');
    
        return $this->get_pitems_by_mdlcourse(
            $mdlcourse,
            array_keys($activestatuses)
        );
    }
}
?>
