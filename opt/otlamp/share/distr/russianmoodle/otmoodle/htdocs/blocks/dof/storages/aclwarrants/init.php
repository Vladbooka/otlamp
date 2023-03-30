<?php
////////////////////////////////////////////////////////////////////////////
//                                                                        //
// NOTICE OF COPYRIGHT                                                    //
//                                                                        //
// Dean`s Office for Moodle                                               //
// Электронный деканат                                                    //
// <http://deansoffice.ru/>                                               //
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
require_once($DOF->plugin_path('storage','config','/config_default.php'));


/** Доверенности системы полномочий
 * 
 */
class dof_storage_aclwarrants extends dof_storage implements dof_storage_config_interface
{
    /**
     * @var dof_control
     */
    protected $dof;
    // **********************************************
    // Методы, предусмотренные интерфейсом plugin
    // **********************************************
    /** Дополнительные действия при установке плагина
     * @todo добавить описание при установке стандартных ролей
     * 
     * @see blocks/dof/lib/dof_storage#install()
     */
    public function install()
    {
        if ( parent::install() )
        {// после установки плагина добавим в таблицу стандартные роли
            $defaultroles = $this->get_default_roles();
            foreach ( $defaultroles as $role )
            {
                $warrant = new stdClass();
                $warrant->linkid      = 0;
                $warrant->linktype    = 'none';
                $warrant->code        = $role;
                $warrant->parentid    = 0;
                $warrant->parenttype  = 'core';
                $warrant->noextend    = 0;
                $warrant->description = '';
                $warrant->name        = $this->dof->get_string($role, $this->code(), null, 'storage');
                
                if ( $id = $this->insert($warrant) )
                {// корневые доверенности должны быть созданы сразу с активным статусом
                    $this->dof->workflow('aclwarrants')->change($id, 'active');
                }
            }
        }
        return true;
    }
    
    /** Метод, реализующий обновление плагина в системе
     * Создает или модифицирует существующие таблицы в БД
     * @param string $old_version - версия установленного в системе плагина
     * @return boolean
     * @access public
     */
    public function upgrade($oldversion)
    {
        global $DB;
        $dbman = $DB->get_manager();
        $aclwarrants = new xmldb_table($this->tablename());
        if ($oldversion < 2012031100) 
        {//удалим enum поля
            // для поля noextend
            if ( $this->dof->moodle_version() <= 2011120511 )
            {
                $field = new xmldb_field('noextend', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, null, 'parentid');
                $dbman->drop_enum_from_field($aclwarrants, $field);
            }
        }
        if ( $oldversion < 2012091700 )
        {// после удаления enum поля слетели настройки - исправим их
            $defaultroles = $this->get_default_roles();
            foreach ( $defaultroles as $role )
            {// для каждой стандартной роли
                if ( ! $warrant = $this->get_record(array('code'=>$role)) )
                {// если такая найдена
                    continue;
                }
                // меняем наследование
                $warrant->noextend = 0;
                $this->update($warrant);
            }
        }
        if ( $oldversion < 2012101000 )
        {// добавляем новые поля и индексы
            // тип мандаты
            $field = new xmldb_field('parenttype', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, 'none', 'parentid');
            if ( !$dbman->field_exists($aclwarrants, $field) ) 
            {// поле еще не установлено
                $dbman->add_field($aclwarrants, $field);
            }
            // владелец мандаты
            $field = new xmldb_field('ownerid', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, 0, 'status');
            if ( !$dbman->field_exists($aclwarrants, $field) ) 
            {// поле еще не установлено
                $dbman->add_field($aclwarrants, $field);
            }
             // подразделение
            $field = new xmldb_field('departmentid', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, 0, 'ownerid');
            if ( !$dbman->field_exists($aclwarrants, $field) ) 
            {// поле еще не установлено
                $dbman->add_field($aclwarrants, $field);
            }
            // индекс для типа мандаты
            // сначала дропаем индекс parentid, т.к он мешает установке
            $index = new xmldb_index('iparentid', XMLDB_INDEX_NOTUNIQUE, array('parentid'));
            if ($dbman->index_exists($aclwarrants, $index)) 
            {// индекс установлен
                $dbman->drop_index($aclwarrants, $index);
            }
            // ставим его снова
            if ( !$dbman->index_exists($aclwarrants, $index) ) 
            {// индекс еще не установлен
                $dbman->add_index($aclwarrants, $index);
            }
            //ставим индекс типа мандат
            $index = new xmldb_index('iparenttype', XMLDB_INDEX_NOTUNIQUE, array('parenttype'));
            if ( !$dbman->index_exists($aclwarrants, $index) ) 
            {// индекс еще не установлен
                $dbman->add_index($aclwarrants, $index);
            }
            // индекс для владельца мандаты
            $index = new xmldb_index('iownerid', XMLDB_INDEX_NOTUNIQUE, array('ownerid'));
            if ( !$dbman->index_exists($aclwarrants, $index) ) 
            {// индекс еще не установлен
                $dbman->add_index($aclwarrants, $index);
            }
            // индекс для владельца мандаты
            $index = new xmldb_index('idepartmentid', XMLDB_INDEX_NOTUNIQUE, array('departmentid'));
            if ( !$dbman->index_exists($aclwarrants, $index) ) 
            {// индекс еще не установлен
                $dbman->add_index($aclwarrants, $index);
            }
            //меняем значение по умолчанию
            //сначала дропаем индекс
            $index = new xmldb_index('ilinktype', XMLDB_INDEX_NOTUNIQUE, array('linktype'));
            if ($dbman->index_exists($aclwarrants, $index)) 
            {// индекс установлен
                $dbman->drop_index($aclwarrants, $index);
            }
            // меняем значение
            $field = new xmldb_field('linktype', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, 'none', 'linkid');
            $dbman->change_field_default($aclwarrants, $field);
            // удаляем индек linkid мешающего установке
            $index = new xmldb_index('ilinkid', XMLDB_INDEX_NOTUNIQUE, array('linkid'));
            if ($dbman->index_exists($aclwarrants, $index)) 
            {// индекс установлен
                $dbman->drop_index($aclwarrants, $index);
            }
            // ставим его снова
            if ( !$dbman->index_exists($aclwarrants, $index) ) 
            {// индекс еще не установлен
                $dbman->add_index($aclwarrants, $index);
            }
            // ставим индекс linktype
            $index = new xmldb_index('ilinktype', XMLDB_INDEX_NOTUNIQUE, array('linktype'));
            if ( !$dbman->index_exists($aclwarrants, $index) ) 
            {// индекс еще не установлен
                $dbman->add_index($aclwarrants, $index);
            }
            //меняем имя поля
            $index = new xmldb_index('inoextend', XMLDB_INDEX_NOTUNIQUE, array('noextend'));
            if ($dbman->index_exists($aclwarrants, $index)) 
            {// дропаем сначала индекс
                $dbman->drop_index($aclwarrants, $index);
            }
            $field = new xmldb_field('noextend', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, null, 'parenttype');
            $dbman->rename_field($aclwarrants, $field, 'isdelegatable');
            $index = new xmldb_index('iisdelegatable', XMLDB_INDEX_NOTUNIQUE, array('isdelegatable'));
            if ( !$dbman->index_exists($aclwarrants, $index) ) 
            {// добавляем новый индекс
                $dbman->add_index($aclwarrants, $index);
            }
            //заканчиваем с кривой установкой полей
            // правим стандартные роли по новым правилам
            $defaultroles = $this->get_default_roles();
            foreach ( $defaultroles as $role )
            {// для каждой стандартной роли
                if ( ! $warrant = $this->get_record(array('code'=>$role)) )
                {// если такая найдена
                    continue;
                }
                // меняем наследование
                $warrant->linktype = 'none';
                $warrant->linkid = 0;
                $warrant->parenttype = 'core';
                $warrant->departmentid = 0;
                $this->update($warrant);
            }
            // переправляем роли уже созданные на должности
            if ( $warrants = $this->get_records_select("linktype != 'none'") )
            {
                foreach ( $warrants as $warrant )
                {// для каждой стандартной роли меняем наследование
                    if ( ! $record = $this->dof->plugin($warrant->linkptype,$warrant->linkpcode)->get($warrant->linkid) )
                    {// если такая найдена
                        continue;
                    }
                    // меняем наследование
                    $warrant->parenttype = 'ext';
                    $warrant->departmentid = $record->departmentid;
                    $this->update($warrant);
                }
            }
        }
        if ( $oldversion < 2013021500 ) 
        {
            if ( $warrants = $this->get_records_select("linktype != 'none'") )
            {
                foreach ( $warrants as $warrant )
                {// для каждой стандартной роли меняем наследование
                    if ( ! $record = $this->dof->plugin($warrant->linkptype,$warrant->linkpcode)->get($warrant->linkid) )
                    {// если такая найдена
                        continue;
                    }
                    // меняем наследование
                    $warrant->parenttype = 'ext';
                    $warrant->departmentid = $record->departmentid;
                    $this->update($warrant);
                }
            }
        }
        if ( $oldversion < 2015021600 ) 
        {
            $aclwarrants = $this->prefix() . $this->tablename();
            $sql = "UPDATE {$aclwarrants}
                       SET isdelegatable = CASE
                                             WHEN isdelegatable = 1 THEN 0
                                             ELSE 1
                                           END";
            $this->execute($sql);
        }
        if( $oldversion < 2016111502 )
        {
            $role = 'user';
            //проверка наличия доверенности ядра "аутентифицированный пользователь"
            $warrant = $this->get_record([
                'code'=>$role,
                'parentid'=>0,
                'parenttype'=>'core'
            ]);
            if ( empty($warrant) )
            {
                //роли нет - надо создать и активировать
                $warrant = new stdClass();
                $warrant->linkid      = 0;
                $warrant->linktype    = 'none';
                $warrant->code        = $role;
                $warrant->parentid    = 0;
                $warrant->parenttype  = 'core';
                $warrant->isdelegatable = 0;
                $warrant->description = '';
                $warrant->name        = $this->dof->get_string($role, $this->code(), null, 'storage');
                $warrant->noextend    = 0;
            
                if ( $id = $this->insert($warrant) )
                {// корневые доверенности должны быть созданы сразу с активным статусом
                    $this->dof->workflow('aclwarrants')->change($id, 'active');
                }
            }
            //создание прокси-доверенностей для ролей, требующих их
            $this->create_proxy_warrants();
            
            

            //назнчение роли аутентифицированного пользователя всем пользователям в активных статусах
            $persons = $this->dof->storage('persons')->get_records([
                'status' => array_keys($this->dof->workflow('persons')->get_meta_list('active'))
            ]);
            foreach($persons as $person)
            {
                $this->dof->storage('aclwarrantagents')->assign_warrant($person->id, 'storage', 'persons', 'record', $person->id, $person->departmentid, 'user');
            }
            
            //назначение роли студента всем пользователям с договорами в активных статусах
            $contracts = $this->dof->storage('contracts')->get_records([
                'status' => array_keys($this->dof->workflow('contracts')->get_meta_list('active'))
            ]);
            foreach($contracts as $contract)
            {
                $this->dof->storage('aclwarrantagents')->assign_warrant($contract->studentid, 'storage', 'contracts', 'record', $contract->id, $contract->departmentid, 'student');
            }
        }
        return true;// уже установлена самая свежая версия
    }
    /** Возвращает версию установленного плагина
     * @return string
     * @access public
     */
    public function version()
    {
        // Версия плагина (используется при определении обновления)
        return 2017090500;
        
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
        return 'aclwarrants';
    }
    /** Возвращает список плагинов, 
     * без которых этот плагин работать не может
     * @return array
     * @access public
     */
    public function need_plugins()
    {
        return array();
    }
    /** Определить, возможна ли установка плагина в текущий момент
     * Эта функция одинакова абсолютно для всех плагинов и не содержит в себе каких-либо зависимостей
     * @TODO УДАЛИТЬ эту функцию при рефакторинге. Вместо нее использовать наследование
     * от класса dof_modlib_base_plugin 
     * @see dof_modlib_base_plugin::is_setup_possible()
     * 
     * @param int $oldversion[optional] - старая версия плагина в базе (если плагин обновляется)
     *                                    или 0 если плагин устанавливается
     * 
     * @return bool 
     *              true - если плагин можно устанавливать
     *              false - если плагин устанавливать нельзя
     */
    public function is_setup_possible($oldversion=0)
    {
        return dof_is_plugin_setup_possible($this, $oldversion);
    }
    /** Получить список плагинов, которые уже должны быть установлены в системе,
     * и без которых начать установку или обновление невозможно
     * 
     * @param int $oldversion[optional] - старая версия плагина в базе (если плагин обновляется)
     *                                    или 0 если плагин устанавливается
     * @return array массив плагинов, необходимых для установки
     *      Формат: array('plugintype'=>array('plugincode' => YYYYMMDD00));
     */
    public function is_setup_possible_list($oldversion=0)
    {
        return [
            'storage'  => [
                'config'=> 2011080900
            ],
            'workflow' => [
                'aclwarrants'=> 2011041500
            ]
        ];
    }
    /** Список обрабатываемых плагином событий 
     * @return array - array(array('plugintype'=>..,'plugincode'=>..,'eventcode'=>..),...)
     * @access public
     */
    public function list_catch_events()
    {
        return [
            [
                'plugintype' => 'storage',
                'plugincode' => 'aclwarrants',
                'eventcode' => 'insert'
            ],
            [
                'plugintype' => 'storage',
                'plugincode' => 'aclwarrants',
                'eventcode' => 'update'
            ],
            [
                'plugintype' => 'workflow',
                'plugincode' => 'departments',
                'eventcode' => 'status_changed'
            ],
            [
                'plugintype' => 'storage',
                'plugincode' => 'departments',
                'eventcode' => 'update'
            ]
        ];
    }
    /** Требуется ли запуск cron в плагине
     * @return bool
     * @access public
     */
    public function is_cron()
    {
        // Просим запускать крон не чаще раза в 15 минут
        return false;
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
    public function is_access($do, $objid = NULL, $userid = NULL)
    {
        // Используем функционал из $DOFFICE
        return $this->dof->is_access($do, NULL, $userid);
    }
    /** Обработать событие
     * @param string $gentype - тип модуля, сгенерировавшего событие
     * @param string $gencode - код модуля, сгенерировавшего событие
     * @param string $eventcode - код задания
     * @param int $intvar - дополнительный параметр 
     * @param mixed $mixedvar - дополнительные параметры
     * @return bool - true в случае выполнения без ошибок
     * @access public
     */
    public function catch_event($gentype,$gencode,$eventcode,$intvar,$mixedvar)
    {
        if ( $gentype === 'storage' AND $gencode === 'aclwarrants' )
        {//обрабатываем события от своего собственного справочника
          // var_dump($mixedvar);die;
            switch($eventcode)
            {
                case 'insert': return $this->aclwar_insert($mixedvar['new']);
                case 'update':
                    // смена родительской роли 
                    if ($mixedvar['new']->parenttype == 'sub' )
                    {// для субдоверенности права не наследуем
                        return true;
                    }
                    if ( $mixedvar['old']->parentid != $mixedvar['new']->parentid )
                    {
                        return $this->aclwar_newparentid($mixedvar['old']->id, $mixedvar['new']->parentid );
                    } 
                    
                    
            }
        }
        if ( ($gentype === 'workflow' AND $gencode === 'departments' AND $eventcode == 'status_changed') )
        {
            $departmentid = (int)$intvar;
            $this->create_proxy_warrants($departmentid);
        }
        
        if( ($gentype === 'storage' AND $gencode === 'departments' AND $eventcode == 'update') )
        {
            $departmentid = (int)$intvar;
            $olddepartment = $mixedvar['old'];
            $newdepartment = $mixedvar['new'];
            if( $olddepartment->leaddepid != $newdepartment->leaddepid )
            {//был выполнен перенос подразделения
                //необходимо перелинковать имеющиеся прокси-доверенности на ближайшую родительскую подобную доверенность
                $this->relink_warrants_in_replaced_department($departmentid);
                if ( $newdepartment->leaddepid == 0 )
                { //подразделение было перемещено в топ
                    //прокси-доверенности у подразделения внизу могло не быть
                    //создадим прокси-доверенность, если ее еще нет
                    $this->create_proxy_warrants($departmentid);
                }
            }
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
    public function cron($loan,$messages)
    {
        return true;
    }
    /** Обработать задание, отложенное ранее в связи с его длительностью
     * @param string $code - код задания
     * @param int $intvar - дополнительный параметр 
     * @param mixed $mixedvar - дополнительные параметры
     * @return bool - true в случае выполнения без ошибок
     * @access public
     */
    public function todo($code,$intvar,$mixedvar)
    {
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
     * @return text
     * @access public
     */
    public function tablename()
    {
        // Имя таблицы, с которой работаем
        return 'block_dof_s_aclwarrants';
    }

    // **********************************************
    //              Собственные методы
    // **********************************************
    
    /** Функция получения настроек для плагина
     *  
     */
    public function config_default($code=null)
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
        return $config;
    }
  
    /** Получить список стандартных используемых в системе ролей, для того чтобы назначить
     * полномочия по умолчанию 
     * 
     * @return array список ролей по умолчанию
     */
    public function get_default_roles()
    {
        return array('root', 'teacher', 'manager', 'student', 'methodist', 'parent', 'user');
    }
 
    /** Вставка довененности
     * @param (obj) $obj- объект с входными данными
     * @return bool true | false
     */
    public function aclwar_insert($obj)
    {
        $flag = true;
        if ($obj->parenttype == 'sub' )
        {// для субдоверенности права не наследуем
            return true;
        }
        if ( $obj->parentid )
        {// вставка не родителя
            // получаем все родительскте права
            if ( $aclparent = $this->dof->storage('acl')->get_records(array('aclwarrantid' => $obj->parentid)) )
            {
                // перебираем их
                foreach ( $aclparent as $acl)
                {
                    // переопределяем warrant
                    $acl->aclwarrantid = $obj->id;
                    // вставка
                    $flag = ( $flag AND (bool)$this->dof->storage('acl')->insert($acl) ); 
                }
            }
        }
        return $flag;
    }
    
    /** При смене родителя(чьи права он наследует)
     *  происходит переопределение всех где и он родитель 
     * @param (int) $id - запись, которую изменяем
     * @param (int) $newparent - новый родитель
     * @return bool
     */
    public function aclwar_newparentid($id, $newparent)
    {
        
        $flag = true;
        // обработка самого объекта
        if ( $aclparent = $this->dof->storage('acl')->get_records(array('aclwarrantid' => $id)) )
        {
            foreach ( $aclparent as $acl)
            {// удаляем со старым parentid
                $flag = ( $flag AND $this->dof->storage('acl')->delete($acl->id) ); 
            }
        }
        // вставляем новые    
        if ( $aclparent = $this->dof->storage('acl')->get_records(array('aclwarrantid' => $newparent)) )   
        {
            // перебираем их
            foreach ( $aclparent as $acl)
            {
                // переопределяем warrant
                $acl->aclwarrantid = $id;
                // вставка
                $flag = ( $flag AND (bool)$this->dof->storage('acl')->insert($acl) ); 
            }
        }        
        
        // все, что за ним тянеться
        while ( $record = $this->get_records(array('parentid' => $id)) )
        {// его дочерние записи
            $id = array();
            foreach ( $record as $obj)
            {
                // удаляем старые записи - права    
                if ( $aclparent = $this->dof->storage('acl')->get_records(array('aclwarrantid' => $obj->id)) )
                {
                    foreach ( $aclparent as $acl)
                    {// удаляем со старым parentid
                        $flag = ( $flag AND $this->dof->storage('acl')->delete($acl->id) ); 
        
                    }
                }
                // вставляем новые
                // берем полномочия родителя(т.к. он наследует ВСЕ права родителя) 
                // и меняем только warrantid(ставим его собственный)    
                if ( $aclparent = $this->dof->storage('acl')->get_records(array('aclwarrantid' => $obj->parentid)) )   
                {
                    // перебираем их
                    foreach ( $aclparent as $acl)
                    {
                        // переопределяем warrant
                        $acl->aclwarrantid = $obj->id;
                        // вставка
                        $flag = ( $flag AND (bool)$this->dof->storage('acl')->insert($acl) ); 
                    }
                } 
            // записываем id всех будущих родителей    
            $id[] =$obj->id;    
            }
        }   
        return $flag;
    }    
    
    /** Возвращает список мандат по заданным критериям 
     * 
     * @return array массив записей из базы, или false в случае ошибки
     * @param int $limitfrom - начиная с какой записи просматривается фрагмент списка записей
     * @param int $limitnum - сколько записей нужно извлечь из базы
     * @param object $conds[optional] - объект со списком свойств, по которым будет происходить поиск
     * @param bool $countonly[optional] - только вернуть количество записей по указанным условиям
     * @param string $orderby - критерии сортировки в sql
     */
    public function get_listing($conds=null, $limitfrom=null, $limitnum=null, $sort='', $fields='*', $countonly=false)
    {
        if ( ! $conds )
        {// если список потоков не передан - то создадим объект, чтобы не было ошибок
            $conds = new stdClass();
        }
        $conds = (object)$conds;
        if ( ! is_null($limitnum) AND $limitnum <= 0 )
        {// количество записей на странице может быть 
            //только положительным числом
            $limitnum = $this->dof->modlib('widgets')->get_limitnum_bydefault();
        }
        if ( ! is_null($limitfrom) AND $limitfrom < 0 )
        {//отрицательные значения номера просматриваемой записи недопустимы
            $limitfrom = 0;
        }

        $select = $this->get_select_listing($conds);
        // посчитаем общее количество записей, которые нужно извлечь
        if ( $countonly )
        {// посчитаем общее количество записей, которые нужно извлечь
            return $this->count_records_select($select);
        }
        return $this->get_records_select($select,null,$sort,$fields,$limitfrom,$limitnum);
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
        if ( isset($conds->personid) AND intval($conds->personid) )
        {// ищем записи по подразделению
            // получим их из зависимости с потоком
            $was = $this->dof->storage('aclwarrantagents')->get_records(array('personid'=>$conds->personid), null, 'id,aclwarrantid');
            if ( $was )
            {// есть записи принадлежащие такому подразделению
                $warrantids = array();
                foreach ( $was as $wa )
                {// собираем все warrantids
                    $warrantids[] = $wa->aclwarrantid;
                }
                // склеиваем их в строку
                $warrantidsstring = implode(', ', $warrantids);
                // составляем условие
                $selects[] = ' id IN ('.$warrantidsstring.')';
            }else
            {// нет записей принадлежащих такой академической группе
                // составим запрос, который гарантированно вернет false
                return ' id = -1 ';
            }
            // убираем agroupid из запроса для того чтобы не создать 2 условия для одного поля
            unset($conds->personid);
        }
        // теперь создадим все остальные условия
        foreach ( $conds as $name=>$field )
        {
            if ( $field )
            {// если условие не пустое, то для каждого поля получим фрагмент запроса
                $selects[] = $this->query_part_select($name,$field);
            }
        } 
        //формируем запрос
        if ( empty($selects) )
        {// если условий нет - то вернем пустую строку
            return '';
        }elseif ( count($selects) == 1 )
        {// если в запросе только одно поле - вернем его
            return current($selects);
        }else
        {// у нас несколько полей - составим запрос с ними, включив их всех
            return implode($selects, ' AND ');
        }
    }
    
    /**
     * Проверить, можно ли передоверить доверенность
     * 
     * @param int|object $aclwarrantid - id из таблицы aclwarrants или объект для проверки
     * @return bool
     */
    public function is_delegatable($aclwarrantid)
    {
        $id = null;
        if ( is_int_string($aclwarrantid) )
        {
            $id = $aclwarrantid;
        } else if ( is_object($aclwarrantid) )
        {
            if ( !empty($aclwarrantid->isdelegatable) )
            {
                return true;
            } else if ( !empty($aclwarrantid->id) )
            {
                $id = $aclwarrantid->id;
            } else
            {
                return false;
            }
        } else
        {
            dof_debugging('incorrect params', DEBUG_DEVELOPER);
            return false;
        }
        if ( ! $aclwarrant = $this->get($id) )
        {
            dof_debugging("no such aclwarrantid = $id", DEBUG_DEVELOPER);
            return false;
        }
        if ( $aclwarrant->isdelegatable == 1 )
        {
            return true;
        }
        return false;
    }
    
    /**
     * Проверить, можно ли создать указанную субдоверенность
     * Проверки:
     * 1. Создаётся ли мы новая
     * 1.1. Тип родителя не указан - ошибка
     * 1.3. В случае, если у родителя поле isdelegatable == 1, возвращаем true, иначе false
     * 2. Модификация существующей (возможно стоит добавить проверок)
     * 3. Тип родителя - не субдоверенность или родитель не указан/пуст - можно создать
     * 4. В ином случае это субдоверенность и мы проверяем поле isdelegatable у родителя
     * 
     * @param object $aclwarrant - Объект aclwarrant
     * @return bool
     */
    public function is_delegatable_sub(stdClass $aclwarrant, $new = true)
    {
        if ( $new )
        {// Создаём (суб-)доверенность
            // Родитель не указан
            if ( empty($aclwarrant->parenttype) )
            {
                dof_debugging('parenttype is empty', DEBUG_DEVELOPER);
                return false;
            }
        } else
        {// Изменяем доверенность
            // Получим информацию о доверенности
            // @todo Иногда в метод приходит обрезанный тип объекта с id и статусом. Понять откуда.
            $aclwarrant = $this->get($aclwarrant->id);
            if ( empty($aclwarrant) )
            {
                dof_debugging('aclwarant not found', DEBUG_DEVELOPER);
                return false;
            }
        }
        // Это не субдоверенность или родитель не указан
        if ( $aclwarrant->parenttype != 'sub' OR empty($aclwarrant->parentid) )
        {
            return true;
        }
        // Это субдоверенность
        // Проверим родителя
        $parent = $this->get($aclwarrant->parentid);
        // Запретим создавать субдоверенности на субдоверенности
        if ( $parent->parenttype == 'sub' )
        {
            return false;
        } else if ( $parent->isdelegatable == 1 )
        {// У родителя есть право передоверия
            return true;
        }
        return false;        
    }
    
    public function insert($dataobject, $quiet = false, $bulk = false, $options = [])
    {
        // Проверим, можно ли создавать объект
        //  [если у parentid isdelegatable == 1 или parentid == 0]
        if ( $this->is_delegatable_sub($dataobject) )
        {
            return parent::insert($dataobject, $quiet, $bulk);
        } else
        {
            throw new dof_exception('isnotdelegatable', '', '', null, dof_print_object($dataobject));
        }
        return false;
    }
    
    public function update($dataobject, $id = null, $quiet = false, $bulk = false)
    {
        // Проверим, можно ли обновить объект
        //  [если у parentid isdelegatable == 1 или parentid == 0]
        if ( $this->is_delegatable_sub($dataobject, false) )
        {
            return parent::update($dataobject, $id, $quiet, $bulk);
        } else
        {
            throw new dof_exception('isnotdelegatable', '', '', null, dof_print_object($dataobject));
        }
        return false;
    }


    public function create_proxy_warrants( $departmentid = null )
    {
        $result = true;
        
        //коды доверенностей ядра, для которых требуется создание прокси-доверенностей в подразделениях высшего уровня
        $warrantscodestoproxy = [
            'user',
            'student'
        ];

        //условия выборки подразделений высшего уровня
        $departmentconds = [
            'leaddepid'=>0,
            'status' => array_keys($this->dof->workflow('departments')->get_meta_list('real'))
        ];
        
        if( ! empty($departmentid) )
        {//синхронизация будет выполняться для конкретного подразделения
            $departmentconds['id'] = (int)$departmentid;
        }
        //получение подразделений
        $departments = $this->dof->storage('departments')->get_records($departmentconds);
        
        if( ! empty($departments) )
        {
            foreach($warrantscodestoproxy as $warrantcodetoproxy)
            {
                //получение доверенности ядра по коду
                $warrantparent = $this->get_record([
                    'code'=>$warrantcodetoproxy,
                    'parentid'=>0,
                    'parenttype'=>'core'
                ]);
                
                if ( ! empty($warrantparent) )
                {//имеется доверенность ядра, по которой будем создавать прокси-доверенности
                    foreach( $departments as $department )
                    {
                        //проверка на существование прокси-доверенностей в подразделении
                        $departmentproxywarrants = $this->get_records([
                            'departmentid'=>$department->id, 
                            'parentid'=>$warrantparent->id
                        ]);
                        
                        if ( empty($departmentproxywarrants) )
                        {//прокси-доверенности в подразделении нет, создадим
                            $warrant = new stdClass();
                            $warrant->code = $warrantparent->code;// . ' [dep='.$department.']';//
                            $warrant->name = $warrantparent->name;
                            $warrant->description = $warrantparent->description;
                            $warrant->parentid = $warrantparent->id;
                            $warrant->linkid      = 0;
                            $warrant->linktype    = 'none';
                            $warrant->isdelegatable = 0;
                            $warrant->parenttype = 'ext';
                            $warrant->ownerid = 0;
                            $warrant->departmentid = $department->id;
            
                            if ( $id = $this->insert($warrant) )
                            {// прокси-доверенности создаются автоматически и должны быть сразу переведены в активный статус
                                $departmentproxywarrants = [$this->get($id)];
                            } else 
                            {
                                $departmentproxywarrants = [];
                                $result = false;
                            }
                        }
                        
                        foreach($departmentproxywarrants as $departmentproxywarrant)
                        {
                            if ( $department->status == 'active' && $departmentproxywarrant->status != 'active' )
                            {//подразделение активно - активируем и прокси-доверенность
                                if( array_key_exists('active', $this->dof->workflow('aclwarrants')->get_available($id)) )
                                {//прокси-доверенность возможно активировать
                                    //активация
                                    $statuschanged = $this->dof->workflow('aclwarrants')->change($id, 'active'); 
                                    if( ! $statuschanged )
                                    {//не активировалось
                                        $result = false;
                                    }
                                } else 
                                {
                                    $result = false;
                                }
                            }
                        }
                    }
                }
            }
        }
        return $result;
    }
    
    /**
     * Поиск доверенности ядра, от которой была изначально унаследована иерархия доверенностей
     * 
     * @param int $warrantid - идентификатор доверенности
     * @return object|boolean - доверенность ядра или false в случае, если не удалось ее найти
     */
    public function get_core_warrant_by_child($warrantid)
    {
        $warrant = $this->get((int)$warrantid);
        if( ! empty($warrant) )
        {
            if ( $warrant->parenttype == 'core' )
            {
                return $warrant;
            }
            if ( $warrant->parentid != 0 )
            {
                return $this->get_core_warrant_by_child($warrant->parentid);
            }
        }
        return false;
    }
    
    public function get_core_warrant_by_code($code)
    {
        if( array_search($code, $this->get_default_roles()) )
        {
            $warrant = $this->get_record([
                'code' => $code,
                'parenttype' => 'core',
                'parentid' => 0,
                'status' => 'active'
            ]);
            if(!empty($warrant))
            {
                return $warrant;
            } else
            {
                return false;
            }
        } else
        {
            return false;
        }
    }
    
    public function find_warrant_in_nearest_department($corewarrantid, $departmentid, $options=null)
    {
        
        //получим родительские подразделения от нижестоящего к вышестоящему
        $departmenttrace = $this->dof->storage('departments')->get_departmentstrace(
            $departmentid, [
                'inverse' => true
            ]);
        //создадим нулевое псевдо-подразделение
        $nulldepartment = new stdClass();
        $nulldepartment->id = null;
        //доавляем нулевое псевдо-подразделение в путь, чтобы позднее проверить доверенность ядра (вместо привязки к подразделению у доверенности установлен 0)
        $departmenttrace[] = $nulldepartment;
        
        foreach ( $departmenttrace as $nearestdepartment )
        { //для подразделений по порядку от нижестоящего к вышестоящему
            $nearestdepartmentid = $nearestdepartment->id;
            if ( empty($options['selfsearch']) && $nearestdepartmentid == $departmentid )
            { //не ищем ничего в своем же подразделении при наличии соответствующей опции
                continue;
            }
            //получение доверенностей ближайшего родительского подразделения
            $nearestdepartmentwarrants = $this->get_records(
                [
                    'departmentid' => $nearestdepartmentid
                ]);
            
            foreach ( $nearestdepartmentwarrants as $nearestdepartmentwarrant )
            {
                if ( $nearestdepartmentwarrant->parenttype != 'core' )
                { //пытаемся найти доверенность ядра, от которой изначально унаследована доверенность
                    $nearestdepartmentcorewarrant = $this->get_core_warrant_by_child(
                        $nearestdepartmentwarrant->id);
                } else
                { //проверяемая ближайшая доверенность сама является доверенностью ядра
                    $nearestdepartmentcorewarrant = $nearestdepartmentwarrant;
                }
                if ( ! empty($nearestdepartmentcorewarrant) &&
                     $nearestdepartmentcorewarrant->id == $corewarrantid )
                { //это ближайшая доверенность, унаследованная от искомой доверенности ядра (доверенности одного типа)
                    return $nearestdepartmentwarrant;
                }
            }
        }
        return false;
        
    }
    
    public function relink_warrants_in_replaced_department($departmentid)
    {
        global $CFG;
        //получим доверенности, которые были прилинкованы к подразделению
        $departmentwarrants = $this->get_records([
            'departmentid' => $departmentid
        ]);
        
        foreach ( $departmentwarrants as $departmentwarrant )
        { //для всех прокси-доверенностей перенесенного подразделения
            //получим родительскую доверенность ядра
            $corewarrant = $this->get_core_warrant_by_child($departmentwarrant->id);
            //поиск по подразделениям ближайшей доверенности того же типа 
            $nearestdepartmentwarrant = $this->find_warrant_in_nearest_department($corewarrant->id, $departmentwarrant->departmentid);
            if ( !empty($nearestdepartmentwarrant) )
            {
                $changedwarrant = fullclone($departmentwarrant);
                $changedwarrant->parentid = $nearestdepartmentwarrant->id;
                //перелинковка доверенности
                $this->update($changedwarrant);
            }
        }
    }
}
?>