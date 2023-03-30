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

// Подключение библиотек
require_once(dirname(realpath(__FILE__)) . '/../../locallib.php');

/**
 * Справочник реестра синхронизаций. Класс соединения внутреннего и внешнего хранилища
 * 
 * @package    storage
 * @subpackage sync
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dof_storage_sync_connect
{
    /**
     * Объект деканата для доступа к общим методам
     * 
     * @var dof_control
     */
    protected $dof;
    
    /** 
     * Настройки подключения
     *
     * @var stdClass 
     */
    private $options;

    /** 
     * SQL-запрос для инициализации подключения
     *
     * @var string 
     */
    private $basesql;

    /** 
     * Инициализация подключения
     * 
     * @param string $downptype - Тип внутреннего плагина
     * @param string $downpcode - Код внутреннего плагина
     * @param string $downsubstorage - Код внутреннего субсправочника
     * @param string $upptype - Тип внешнего плагина
     * @param string $uppcode - Код внешнего плагина
     * @param string $upsubstorage - Код внешнего субсправочника  
     */
    public function __construct($downptype, $downpcode, $downsubstorage, $upptype, $uppcode, $upsubstorage)
    {
        global $DOF;
        
        // Ссылка на Деканат
        $this->dof = $DOF;
        
        // Параметры подключения
        $this->options = new stdClass();
        $this->options->downptype        = $downptype;
        $this->options->downpcode        = $downpcode;
        $this->options->downsubstorage   = $downsubstorage;
        $this->options->upptype          = $upptype;
        $this->options->uppcode          = $uppcode;
        $this->options->upsubstorage     = $upsubstorage;
        
        // Генерация базового SQL
        if ( is_null($downsubstorage) )
        {
            $downsubstoragesql = " downsubstorage is NULL AND ";
        } else
        {
            $downsubstoragesql = " downsubstorage = '{$this->options->downsubstorage}' AND ";
        }
        if ( is_null($upsubstorage) )
        {
            $upsubstoragesql = " upsubstorage is NULL AND ";
        } else
        {
            $upsubstoragesql = " upsubstorage = '{$this->options->upsubstorage}' AND ";
        }
        $this->basesql = " downptype = '{$this->options->downptype}' AND "
                        ." downpcode = '{$this->options->downpcode}' AND "
                        .$downsubstoragesql
                        ." upptype = '{$this->options->upptype}' AND "
                        ." uppcode = '{$this->options->uppcode}' AND "
                        .$upsubstoragesql
                        ." lastoperation <> 'unsync' AND lastoperation <> 'delete' ";
    }

    /** 
     * Получение параметров подключения
     * 
     * @return stdClass
     */
    public function getOptions()
    {
        return clone $this->options;
    }

    /** 
     * Получение данных о внешних объектах синхронизации
     * 
     * @param string $downid - Внутренний ID объекта синхронизации
     * @param string $downhash - Хэш последних загруженных данных
     * 
     * @return array
     */
    public function checkUp($downid, $downhash = '')
    {
        $rez = [];

        // Валидация входных данных
        if ( intval($downid) <= 0 )
        {
            return $rez;
        }

        // Получение последней операции по объекту
        $select = " downid = {$downid} AND lastoperation <> 'unsync' AND lastoperation <> 'delete' ";
        if ( ! $synclist = $this->dof->storage('sync')->get_records_select($select) )
        {// Данных по последней операции нет
            return $rez;
        }

        // Получение внешних объектов синхронизации
        foreach ( $synclist as $sync )
        {
            $rez[] = $this->checkObjectUp($sync, $downhash);
        }
        return $rez;
    }

    /** 
     * Получение данных о внешнем объекте синхронизации по записи из реестра
     * 
     * @param stdClass $item - Запись синхронизации
     * @param string $downhash - хеш последних загруженных данных
     * 
     * @return array
     */
    public function checkObjectUp($item, $downhash = '')
    {
        $obj = new stdClass();
        $obj->upid = $down->upid;
        if ( $downhash == $down->downhash )
        {// хеши объектов совпадают - присваиваем актуальный статус
            $obj->status = 'actual';
        } else
        {
            $obj->status = 'old';
        }
        return $obj;
    }

    /** 
     * Получение данных о внутренних объектах синхронизации
     * 
     * @param string $upid - внешний id синхронизации
     * @param string $uphash [''] - Хэш последних загруженных данных
     * 
     * @return array
     */
    public function checkDown($upid, $uphash = '')
    {
        $rez = array();

        if ( intval($upid) <= 0 )
        {// проверка id
            return array();
        }

        $select = " upid = {$upid} AND lastoperation <> 'unsync' AND lastoperation <> 'delete' ";
        if ( !$synclist = $this->dof->storage('sync')->get_records_select($select) )
        {// данных нет - выходим
            return array();
        }

        foreach ( $synclist as $sync )
        {// создаем массив с результатом
            $obj = new stdClass();
            $obj->downid = $sync->downid;
            if ( $uphash == $sync->uphash AND $sync->uphash != '' )
            {// хеши объектов совпадают - присваиваем актуальный статус
                $obj->status = 'actual';
            } else
            {
                $obj->status = 'old';
            }
            $rez[] = $obj;
        }
        return $rez;
    }

    /** Обновить статус синхронизации внешних объектов [синхронизация этой системы с внешней]
     * @param int    $downid - внутренний id синхронизации
     * @param string $operation - опреция синхронизации
     * * 'connect' - установлена связь между существующими объектами,
     * * 'create' - создан объект в системе-получателе (по направлению),
     * * 'update', 'delete' - обновление, удаление
     * * 'unsync' - синхронизация разорвана, следующая операция только create или connect
     * @param string $downhash - внутренний хеш обекта синхронизации
     * @param int    $upid [null] - внешний id синхронизации
     * @param string $textlog - текст лога синхронизации
     * @param object $opt -дополнительные параметры лога синхронизации
     * @param bool   $error - есть ли ошибка при синхронизации
     * @return bool
     */
    public function updateUp($downid, $operation, $downhash, $upid = null, $textlog = '', $opt = null, $error = false)
    {
        // получаем список доступных синхронизаций
        $select = $this->basesql . " AND downid = '{$downid}' ";
        if ( $operation == 'create' OR $operation == 'connect' )
        {// создаем новую запись
            if ( $error )
            {// вернулась ошибка - фиксируем ее
                $this->dof->storage('synclogs')->add_log($operation, 'up', 0, $textlog, $opt, $error);
                return false;
            }
            if ( empty($upid) )
            {// ошибка входного параметра
                $textlog = 'updateUp. Empty upid';
                $this->dof->storage('synclogs')->add_log($operation, 'up', 0, $textlog, $opt, true);
                return false;
            }
            $select .= " AND upid = '{$upid}' ";
            if ( $this->dof->storage('sync')->is_exists_select($select) )
            {// такая запись уже существует - ошибка, выходим
                $textlog = 'updateUp. Record is exist';
                $this->dof->storage('synclogs')->add_log($operation, 'up', 0, $textlog, $opt, true);
                return false;
            }

            // совпадений не найдено - создаем новую запись синхронизации
            $obj = $this->getOptions();
            $obj->downid = $downid;
            $obj->upid = $upid;
            $obj->downhash = $downhash;
            $obj->lastoperation = $operation;
            $obj->lasttime = time();
            $obj->direct = 'up';
            if ( $syncid = $this->dof->storage('sync')->insert($obj) )
            {// добавление прошло без ошибок
                $this->dof->storage('synclogs')->add_log($operation, 'up', $syncid, $textlog, $opt);
                return $syncid;
            } else
            {// не смогли вставить запись - добавляем лог
                $opt->insert = $obj;
                $textlog = 'updateUp. Insert record has been failed';
                $this->dof->storage('synclogs')->add_log($operation, 'up', 0, $textlog, $opt, true);
                return false;
            }
        } else
        {// проводим обновление
            if ( !empty($upid) )
            {// найдем записи с таким upid
                $select .= "AND upid = '{$upid}'";
            }
            if ( !$records = $this->dof->storage('sync')->get_records_select($select) )
            {// обновлять нечего - выходим
                return true;
            }
            $result = true;
            foreach ( $records as $record )
            {
                if ( $error )
                {// вернулась ошибка - фиксируем ее
                    $this->dof->storage('synclogs')->add_log($operation, 'up', $record->id, $textlog, $opt, $error, $record->lastoperation);
                    return false;
                }
                $obj = new stdClass();
                $obj->id = $record->id;
                $obj->lastoperation = $operation;
                $obj->lasttime = time();
                $obj->downhash = $downhash;
                $obj->direct = 'up';
                if ( $update = $this->dof->storage('sync')->update($obj) )
                {// обновление прошло успешно 
                    $this->dof->storage('synclogs')->add_log($operation, 'up', $record->id, $textlog, $opt, false, $record->lastoperation);
                } else
                {// не смогли обновить - логируем
                    $opt->update = $obj;
                    $textlog = 'updateUp. Update record has been failed';
                    $this->dof->storage('synclogs')->add_log($operation, 'up', $record->id, $textlog, $opt, true, $record->lastoperation);
                }
                $result = $update && $result;
            }
            return $result;
        }
    }

    /** Обновить статус синхронизации внутренних объектов [синхронизация из внешней системы]
     * @param int $upid - внешний id синхронизации
     * @param string $operation - операция синхронизации:
     * * 'connect' - установлена связь между существующими объектами,
     * * 'create' - создан объект в системе-получателе (по направлению),
     * * 'update', 'delete' - обновление, удаление
     * * 'unsync' - синхронизация разорвана, следующая операция только create или connect
     * @param string $uphash - внешний хеш обекта синхронизации
     * @param int $downid [null] - внутренний id синхронизации
     * @param string $textlog - текст лога синхронизации
     * @param object $opt -дополнительные параметры лога синхронизации
     * @param bool $error - есть ли ошибка при синхронизации
     * @return bool
     */
    public function updateDown($upid, $operation, $uphash, $downid = null, $textlog = '', $opt = null, $error = false)
    {
        // получаем список доступных синхронизаций
        $select = $this->basesql . " AND upid = '{$upid}' ";

        if ( $operation == 'create' OR $operation == 'connect' )
        {// создаем новую запись
            if ( $error )
            {// вернулась ошибка - фиксируем ее
                $this->dof->storage('synclogs')->add_log($operation, 'down', 0, $textlog, $opt, $error);
                return false;
            }
            if ( empty($downid) OR intval($downid) <= 0 )
            {// ошибка входного параметра
                $textlog = 'updateDown. Empty downid';
                $this->dof->storage('synclogs')->add_log($operation, 'down', 0, $textlog, $opt, true);
                return false;
            }
            $select .= "AND downid = '{$downid}'";
            if ( $this->dof->storage('sync')->is_exists_select($select) )
            {// обновлять нечего - выходим
                $textlog = 'updateDown. Record is exist';
                $this->dof->storage('synclogs')->add_log($operation, 'down', 0, $textlog, $opt, true);
                return false;
            }
            // совпадений не найдено - создаем новую запись синхронизации
            $obj = $this->getOptions();
            $obj->downid = $downid;
            $obj->upid = $upid;
            $obj->uphash = $uphash;
            $obj->lastoperation = $operation;
            $obj->lasttime = time();
            $obj->direct = 'down';
            if ( $syncid = $this->dof->storage('sync')->insert($obj) )
            {// добавление прошло без ошибок
                $this->dof->storage('synclogs')->add_log($operation, 'down', $syncid, $textlog, $opt);
                return $syncid;
            } else
            {// не смогли вставить запись - добавляем лог
                $opt->insert = $obj;
                $textlog = 'updateDown. Insert record has been failed';
                $this->dof->storage('synclogs')->add_log($operation, 'down', 0, $textlog, $opt, true);
                return false;
            }
        } else
        {// проводим обновление
            if ( !empty($downid) )
            {// ошибка входного параметра
                $select .= "AND downid = '{$downid}'";
            }
            if ( !$records = $this->dof->storage('sync')->get_records_select($select) )
            {// обновлять нечего - выходим
                return true;
            }
            $result = true;
            foreach ( $records as $record )
            {
                if ( $error )
                {// вернулась ошибка - фиксируем ее
                    $this->dof->storage('synclogs')->add_log($operation, 'down', $record->id, $textlog, $opt, $error, $record->lastoperation);
                    return false;
                }
                $obj = new stdClass();
                $obj->id = $record->id;
                $obj->lastoperation = $operation;
                $obj->lasttime = time();
                $obj->uphash = $uphash;
                $obj->direct = 'down';
                if ( $update = $this->dof->storage('sync')->update($obj) )
                {// обновление прошло успешно 
                    $this->dof->storage('synclogs')->add_log($operation, 'down', $record->id, $textlog, $opt, false, $record->lastoperation);
                } else
                {// не смогли обновить - логируем
                    $opt->update = $obj;
                    $textlog = 'updateDown. Update record has been failed';
                    $this->dof->storage('synclogs')->add_log($operation, 'down', $record->id, $textlog, $opt, true, $record->lastoperation);
                }
                $result = $update && $result;
            }
            return $result;
        }
    }

    /** Получить список доступных синхронизаций для данного подключения
     *
     * @return array
     */
    public function listSync()
    {
        if ( $records = $this->dof->storage('sync')->get_records_select($this->basesql) )
        {// возвращаем записи
            return $records;
        }
        return array();
    }

    /** Получить запись синхронизации для данного подключения
     *
     * @param array $param - дополнительные условия в формате $field => $value
     * @return array
     */
    public function getSync($param = array())
    {
        $select = $this->basesql;
        foreach ( $param as $name => $field )
        {
            if ( $field )
            {// если условие не пустое, то для каждого поля получим фрагмент запроса
                $select .= 'AND ' . $this->dof->storage('sync')->query_part_select($name, $field);
            }
        }
        if ( $records = $this->dof->storage('sync')->get_records_select($select) )
        {// возвращаем только одну запись
            return current($records);
        }
        return array();
    }

    /** Обновлён ли объект синхронизации
     * 
     * @param int $id - в зависимости от $direct:
     *  'down': upid
     *  'up'  : downid
     * @param string $hash - в зависимости от $direct
     *  'down': uphash
     *  'up'  : downhash
     * @param string $direct - направление операции: 'down', 'up'
     * @return bool
     */
    public function is_updated($id, $hash, $direct = 'down')
    {
        $params = array();
        if ( $direct == 'down' )
        {
            $params['upid'] = $id;
        } else if ( $direct == 'up' )
        {
            $params['downid'] = $id;
        } else
        {
            return false;
        }
        $params['direct'] = $direct;
        if ( $record = $this->getSync($params) )
        {
            // Проверим hash
            $result = false;
            if ( $direct == 'down' )
            {
                $result = ($record->uphash == $hash);
            } else if ( $direct == 'up' )
            {
                $result = ($record->downhash == $hash);
            }
            return (bool) $result;
        }
        // Запись не нашли
        return false;
    }
}
?>