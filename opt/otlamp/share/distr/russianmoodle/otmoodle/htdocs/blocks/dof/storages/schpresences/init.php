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


/** Справочник учебных программ
 * 
 */
class dof_storage_schpresences extends dof_storage
{
    /**
     * @var dof_control
     */
    protected $dof;
    // **********************************************
    // Методы, предусмотренные интерфейсом plugin
    // **********************************************

    /** Метод, реализующий обновление плагина в системе
     * Создает или модифицирует существующие таблицы в БД
     * @param string $old_version - версия установленного в системе плагина
     * @return boolean
     * @access public
     */
    public function upgrade($oldversion)
    {
        global $DB;
        // Модификация базы данных через XMLDB
        $result = true;
        
        $dbman = $DB->get_manager();
        $table = new xmldb_table($this->tablename());
        if ($oldversion < 2014031400)
        {// добавим поле salfactor
            $field = new xmldb_field('mdlevent', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, 
                    null, null, null, 'orderid');
            if ( !$dbman->field_exists($table, $field) )
            {// поле еще не установлено
                $dbman->add_field($table, $field);
            }
            // добавляем индекс к полю
            $index = new xmldb_index('imdlevent', XMLDB_INDEX_NOTUNIQUE,
                    array('mdlevent'));
            if (!$dbman->index_exists($table, $index))
            {// если индекс еще не установлен
                $dbman->add_index($table, $index);
            }
        }
        
        if ($oldversion < 2017081600)
        {// добавим поле ID причины
            $field = new xmldb_field('reasonid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, 'mdlevent');
            if ( ! $dbman->field_exists($table, $field) )
            {// Поле еще не установлено
                $dbman->add_field($table, $field);
            }
            // Добавляем индекс к полю
            $index = new xmldb_index('ireasonid', XMLDB_INDEX_NOTUNIQUE, ['reasonid']);
            if ( ! $dbman->index_exists($table, $index) )
            {// Если индекс еще не установлен
                $dbman->add_index($table, $index);
            }
        }
        return $result;
    }
    /** Возвращает версию установленного плагина
     * @return string
     * @access public
     */
    public function version()
    {
        // Версия плагина (используется при определении обновления)
        return 2017092900;
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
        return 'schpresences';
    }
    /** Возвращает список плагинов, 
     * без которых этот плагин работать не может
     * @return array
     * @access public
     */
    public function need_plugins()
    {
		return [
                'storage' => [
                        'schevents' => 2009060800,
                        'orders' => 2009052500,
                        'persons' => 2009060400,
                        'schabsenteeism' => 2017052500
                ]
        ];
    }
    /** Список обрабатываемых плагином событий 
     * @return array - array(array('plugintype'=>..,'plugincode'=>..,'eventcode'=>..),...)
     * @access public
     */
    public function list_catch_events()
    {
        // Пока событий не обрабатываем
        return array(// Обрабатываем подписку/отписку группы на поток
                     array('plugintype'=>'storage', 'plugincode'=>'schevents', 'eventcode'=>'insert'),
                     array('plugintype'=>'storage', 'plugincode'=>'schevents', 'eventcode'=>'update'),
                     );
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
        if ( $gentype === 'storage' AND $gencode === 'schevents' )
        {//обрабатываем события от справочника cstreamlink
            switch($eventcode)
            {
                //синхронизируем подписки группы
                case 'insert': 
                	if ( ! $cpassed = $this->dof->storage('cpassed')->get_records(array('cstreamid'=>$mixedvar['new']->cstreamid)) )
                	{
                		return true;
                	}
                	foreach ( $cpassed as $cpass )
                	{
                		$obj = new stdClass;
                		$obj->personid = $cpass->studentid;
                        $obj->eventid = $mixedvar['new']->id;
                        $this->save_present_student($obj);
                	}
                //удаляем подписки';
                case 'update': 
                    if ( empty($mixedvar['new']->status) )
                    {
                		return true;
                	}
                	if ( $mixedvar['new']->status == 'plan' OR $mixedvar['new']->status == 'completed' )
                    {
                		return true;
                	}
                	if ( ! $schpresences = $this->get_records(array('eventid'=>$mixedvar['new']->id)) )
                	{
                	    return true;
                	}
                	foreach ( $schpresences as $schpresence )
                	{
                	    return $this->delete($schpresence->id);
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
        return 'block_dof_s_schpresences';
    }

    // **********************************************
    //              Собственные методы
    // **********************************************
    /**
     * Сохранить статус присутствия/отсутствия ученика на занятии  
     * 
     * @param object $obj - запись в таблицу
     * 
     * @return mixed int id вставленной записи при вставке, bool true при обновлении
     * или false если операции не удались 
     */
    public function save_present_student($obj)
    {
        $params = [];
        $params['personid'] = $obj->personid;
        $params['eventid'] = $obj->eventid;
        if( $obj_existing = $this->get_record($params) )
    	{ 
    	    return $this->update($obj, $obj_existing->id);
    	}
    	return $this->insert($obj);
    }
    
    /** 
     * Сохранить список статусов присутствия/отсутствия учеников на занятии  
     * 
     * @param int $evid - id события
     * @param int $orid - id приказа
     * @param array $students - ключ - id персоны, значение - статус присутствия
     * 
     * @return bool true если все записи сохранились и false в остальных случаях
     */
    public function save_present_students($obj)
    {
    	$result = true;
    	
    	foreach ($obj->presents as $cpid=>$presence)
        {
        	$obj->personid = $this->dof->storage('cpassed')->get_field($cpid, 'studentid');
        	$obj->present = $presence;
        	if ( ! $this->save_present_student($obj) )
        	{
        		$result = false;
        	}
        }
        return $result;
    }
    
    /** 
     * Получить статус присутствия ученика на занятии 
     * 
     * @param int $stid - id студента
     * @param int $evid - ученика
     * 
     * @return mixed int статус присутствия или bool false если событие не найдено
     */
    public function get_present_status($stid, $evid)
    {
        $params = array();
        $params['personid'] = $stid;
        $params['eventid'] = $evid;
    	if ( ! $obj = $this->get_record($params) )
    	{ 
    		return false;
    	}
    	return $obj->present;
    }
    /** 
     * Получить статусы присутствия учеников на занятии 
     * 
     * @param int $evid - id события
     * 
     * @return array ключ - id персоны, значение - статус присутствия 
     */
    public function get_present_students($evid)
    {
    	$mas = array();
    	if (  $presences = $this->get_records(array('eventid'=>$evid)) )
    	{
    	    foreach ( $presences as $student )
    	    {
    		    $mas[$student->personid] = $this->get_present_status($student->personid, $evid);
    	    }
    	}    	
    	return $mas;
    }

    /** 
     * Получить причину отсутствия
     * 
     * @param int $evid - id события
     * @param int $personid - id персоны
     * 
     * @return string - причина отсутствия в виде строки
     */
    public function get_student_reason($evid, $personid)
    {
        if ( empty($evid) && empty($personid) )
        {
            return false;
        }
        
        // Проверим, что такая запись существует
        $params = [];
        $params['personid'] = $personid;
        $params['eventid'] = $evid;
        if ( ! $obj = $this->get_record($params) )
        {
            return false;
        }
        
        if ( $reason = $this->dof->storage('schabsenteeism')->get($obj->reasonid) )
        {
            return $reason->name;
        } else 
        {
            return false;
        }
    }
    
    /**
     * Сохранить информацию о посещении студентами занятия 
     * (Новый метод при старте занятия через новую форму)
     * @todo смержить с существующим методом при полном рефакторинге журнала
     *
     * @param array $persons - массив объектов персон
     *
     * @return bool
     */
    public function save_students_presence($persons = [], $orderid = null)
    {
        if ( empty($persons) || empty($orderid) )
        {
            return false;
        }
        
        $result = true;
        
        foreach ( $persons as $person)
        {
            // Сохраним ID приказа в объекте персоны
            $person->orderid = $orderid;
            
            if ( ! $this->save($person) )
            {
                $result = false;
            }
        }
        
        return $result;
    }
    
    /**
     * Сохранить причину
     *
     * @param stdClass|array $data - Данные о посещение занятия студентом(название или комплексные данные)
     * @param array $options - Массив дополнительных параметров
     *
     * @return int - false в случае ошибки или ID причины в случае успеха
     *
     * @throws dof_exception_dml - В случае ошибки
     */
    public function save($data = null, $options = [])
    {
        // Нормализация данных
        try {
            $normalized_data = $this->normalize($data, $options);
        } catch ( dof_exception_dml $e )
        {
            throw new dof_exception_dml('error_save_'.$e->errorcode);
        }
        
        // Сохранение данных
        if ( isset($normalized_data->id) && $this->is_exists($normalized_data->id) )
        {// Обновление записи
            $schpresences = $this->update($normalized_data);
            if ( empty($schpresences) )
            {// Обновление не удалось
                throw new dof_exception_dml('error_save_schpresences');
            } else
            {// Обновление удалось
                $this->dof->send_event('storage', 'schpresences', 'item_saved', (int)$normalized_data->id);
                return $normalized_data->id;
            }
        } else
        {// Создание записи
            $schpresencesid = $this->insert($normalized_data);
            if ( ! $schpresencesid )
            {// Добавление не удалось
                throw new dof_exception_dml('error_save_schabsenteeism');
            } else
            {// Добавление удалось
                $this->dof->send_event('storage', 'schpresencesid', 'item_saved', (int)$schpresencesid);
                return $schpresencesid;
            }
        }
    }
    
    /**
     * Нормализация данных посещения занятия студентом
     *
     * Формирует объект посещения на основе переданных данных. В случае критической ошибки
     * или же если данных недостаточно, выбрасывает исключение.
     *
     * @param stdClass|array $data - Данные посещения студентом занятия (комплексные данные)
     * @param array $options - Опции работы
     *
     * @return stdClass - Нормализовализованный Объект посещения
     *
     * @throws dof_exception_dml - Исключение в случае критической ошибки или же недостаточности данных
     */
    public function normalize($data, $options = [])
    {
        // Нормализация входных данных
        if ( is_object($data) || is_array($data) )
        {// Комплексные данные
            $data = (object)$data;
        } else
        {// Неопределенные данные
            throw new dof_exception_dml('invalid_data');
        }
        
        // Нормализация идентификатора
        if ( isset($data->id) && $data->id < 1)
        {
            unset($data->id);
        }
        // Проверка входных данных
        if ( empty($data) )
        {// Данные не переданы
            throw new dof_exception_dml('empty_data');
        }
        if ( empty($data->eventid) || empty($data->personid) )
        {// Данные не переданы
            throw new dof_exception_dml('empty_data');
        }
        
        if ( isset($data->id) )
        {// Проверка на существование
            if ( ! $this->get($data->id) )
            {// Причина не найдена
                throw new dof_exception_dml('schpresences_not_found');
            }
        }
        
        // Проверка на существование по полям
        if ( ! isset($data->id) && 
                $existing_obj = $this->get_record(['eventid' => $data->eventid, 'personid' => $data->personid]) )
        {// Причина найдена
            $data->id = $existing_obj->id;
        }
        
        // Создание объекта для сохранения
        $saveobj = clone $data;
        
        // Обработка входящих данных и построение объекта
        if ( isset($saveobj->id) && $this->is_exists($saveobj->id) )
        {// Посещение уже содержится в системе
            // Удаление автоматически генерируемых полей
            unset($saveobj->status);
        } else
        {// Новая причина
            
            // АВТОЗАПОЛНЕНИЕ ПОЛЕЙ
            if ( ! isset($saveobj->present) || (int)$saveobj->present < 0 )
            {// Установка статуса присутствия
                $saveobj->present = NULL;
            }
            if ( ! isset($saveobj->orderid) || (int)$saveobj->orderid < 0 )
            {// Установка ID приказа
                $saveobj->orderid = NULL;
            }
            if ( ! isset($saveobj->mdlevent) || (int)$saveobj->mdlevent < 0 )
            {// Установка ID события в мудл
                $saveobj->mdlevent = NULL;
            }
            if ( ! isset($saveobj->reasonid) || (int)$saveobj->reasonid < 0 )
            {// Установка ID причины отсутствия
                $saveobj->reasonid = NULL;
            }
        }
        
        // НОРМАЛИЗАЦИЯ ПОЛЕЙ
        if ( isset($saveobj->present) )
        {
            $saveobj->present = (int)$saveobj->present;
        }
        if ( isset($saveobj->orderid) )
        {
            $saveobj->orderid = (int)$saveobj->orderid;
        }
        if ( isset($saveobj->mdlevent) )
        {
            $saveobj->mdlevent = (int)$saveobj->mdlevent;
        }
        if ( isset($saveobj->reasonid) )
        {
            $saveobj->reasonid = (int)$saveobj->reasonid;
        }
        
        // ВАЛИДАЦИЯ ДАННЫХ
        // Проверки причины
        if ( ! empty($saveobj->reasonid) )
        {
            if ( ! $this->dof->storage('schabsenteeism')->is_exists($saveobj->reasonid) )
            {
                throw new dof_exception_dml('notvalid_reason');
            }
        }
        
        return $saveobj;
    }
} 
?>