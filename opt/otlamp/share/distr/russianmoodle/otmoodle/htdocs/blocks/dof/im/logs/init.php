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
 * Интерфейс логов
 *
 * @package    im
 * @subpackage logs
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dof_im_logs implements dof_plugin_im
{
    /**
     * @var dof_control
     */
    protected $dof;
    
    /**
     * Поддерживаемые типы отчетов
     * 
     * @var array
     */
    protected $report_types = ['html', 'pdf', 'xls'];
    
    // **********************************************
    // Методы, предусмотренные интерфейсом plugin
    // **********************************************
    /** Метод, реализующий инсталяцию плагина в систему
     * Создает или модифицирует существующие таблицы в БД
     * и заполняет их начальными значениями
     * @return boolean
     * Может надо возвращать массив с названиями таблиц и результатами их создания?
     * чтобы потом можно было распечатать сообщения о результатах обновления
     * @access public
     */
    
    /** Конструктор
     * @param dof_control $dof - идентификатор действия, которое должно быть совершено
     * @access public
     */
    public function __construct($dof)
    {
        $this->dof = $dof;
    }
    
    public function install()
    {
        return true;
    }
    
    /** Метод, реализующий обновление плагина в системе
     * Создает или модифицирует существующие таблицы в БД
     * @param string $old_version - версия установленного в системе плагина
     * @return boolean
     * Может надо возвращать массив с названиями таблиц и результатами их создания/изменения?
     * чтобы потом можно было распечатать сообщения о результатах обновления
     * @access public
     */
    public function upgrade($oldversion)
    {
        return true;
    }
    
    /** Метод, реализующий удаление плагина в системе  
	 * @return bool
	 */
	public function uninstall()
	{
		return $this->dof->storage('acl')->save_roles($this->type(),$this->code(),array());
	}
	
    /** Возвращает версию установленного плагина
     * @return string
     * @access public
     */
    public function version()
    {
        return 2017081400;
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
        return 'angelfish';
    }
    
    /** Возвращает тип плагина
     * @return string 
     * @access public
     */
    public function type()
    {
        return 'im';
    }
    
    /** Возвращает короткое имя плагина
     * Оно должно быть уникально среди плагинов этого типа
     * @return string
     * @access public
     */
    public function code()
    {
        return 'logs';
    }
    
    /** Возвращает список плагинов, 
     * без которых этот плагин работать не может
     * @return array
     * @access public
     */
    public function need_plugins()
    {
        return [
                'modlib' => [
                        'nvg' => 2008060300,
                        'widgets' => 2009050800
                ],
                'storage' => [
                        'persons' => 2017000000,
                        'acl' => 2011041800,
                        'logs' => 2017070300
                ]
        ];
    }
    
    /** Список обрабатываемых плагином событий 
     * @return array - array(array('plugintype'=>..,'plugincode'=>..,'eventcode'=>..),...)
     * @access public
     */
    public function list_catch_events()
    {
       return [];
    }
    
    /** Требуется ли запуск cron в плагине
     * @return bool
     * @access public
     */
    public function is_cron()
    {
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
        if ( $this->dof->is_access('datamanage') || $this->dof->is_access('admin') || $this->dof->is_access('manage') )
        {// манагеру можно все
            return true;
        }  
        // получаем id пользователя в persons
        $personid = $this->dof->storage('persons')->get_by_moodleid_id($userid);
        // получаем все нужные параметры для функции проверки прав
        $acldata = $this->get_access_parametrs($do, $objid, $personid);   
        // проверка
        return $this->acl_check_access_paramenrs($acldata);
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
    public function require_access($do, $objid = NULL, $userid = NULL)
    {
        if ( ! $this->is_access($do, $objid, $userid) )
        {
            $notice = "{$this->code()}/{$do} (block/dof/{$this->type()}/{$this->code()}: {$do})";
            if ($objid){$notice.=" id={$objid}";}
            $this->dof->print_error('nopermissions','',$notice);
        }
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

    // **********************************************
    // Методы, предусмотренные интерфейсом im
    // **********************************************
    /** Возвращает текст для отображения в блоке на странице dof
     * @param string $name - название набора текстов для отображания
     * @param int $id - id текста в наборе
     * @return string - html-код содержимого блока
     */
    function get_block($name, $id = 1)
    {
        return '';
    }
    /** Возвращает html-код, который отображается внутри секции
     * @param string $name - название набора текстов для отображания
     * @param int $id - id текста в наборе
     * @return string  - html-код содержимого секции секции
     */
    function get_section($name, $id = 0)
    {
        return '';
    }
    
    // ***********************************************************
    //       Методы для работы с полномочиями и конфигурацией
    // ***********************************************************   
    
    /** Получить список параметров для фунции has_hight()
     * @todo завести дополнительные права в плагине storage/persons и storage/contracts 
     * и при редактировании контракта или персоны обращаться к ним
     * 
     * @return object - список параметров для фунции has_hight()
     * @param string $action - совершаемое действие
     * @param int $objectid - id объекта над которым совершается действие
     * @param int $userid
     */
    protected function get_access_parametrs($action, $objectid, $userid, $depid = null)
    {
        $result = new stdClass();
        $result->plugintype   = $this->type();
        $result->plugincode   = $this->code();
        $result->code         = $action;
        $result->userid       = $userid;
        $result->departmentid = $depid;
        if ( is_null($depid) )
        {// подразделение не задано - берем текущее
            $result->departmentid = optional_param('departmentid', 0, PARAM_INT);
        }
        $result->objectid     = $objectid;
        if ( ! $objectid )
        {// если objectid не указан - установим туда 0 чтобы не было проблем с sql-запросами
            $result->objectid = 0;
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
                              $acldata->userid, $acldata->departmentid, $acldata->objectid);
    }

    // **********************************************
    //              Собственные методы
    // **********************************************
    
    /**
     * Возвращение данных по заданным параметрам
     *
     * @param int $id - идентификатор лога
     *
     * @return array
     */
    protected function get_logreport_data($id, $data = true, $storage = true, $object = true, $action = 'all', $status = 'all', $comment = true)
    {
        // Данные отчета
        $report_data = [];
        
        // Заголовки отчета
        $headers = [];
        
        // Проверка идентификатора лога
        if ( ! is_numeric($id) ||
                ! $this->dof->storage('logs')->is_exists(['id' => $id]) )
        {
            return false;
        }
        
        // Данные из лога
        $queue = $this->dof->storage('logs')->init_from_id($id);
        $list = $queue->get_logs();

        array_shift($list);
        
        // Заполнение заголовков
        if ( $data )
        {
            $headers[] = $this->dof->get_string('data','logs');
        }
        if ( $storage )
        {
            $headers[] = $this->dof->get_string('storage','logs');
        }
        if ( $object )
        {
            $headers[] = $this->dof->get_string('object','logs');
        }
        if ( $action )
        {
            $headers[] = $this->dof->get_string('action','logs');
        }
        if ( $status )
        {
            $headers[] = $this->dof->get_string('status','logs');
        }
        if ( $comment )
        {
            $headers[] = $this->dof->get_string('comment','logs');
        }
        
        // Установка заголовков в отчет
        $report_data[] = $headers;
        
        if ( ! empty($list) )
        {
            // Заполнение таблицы
            foreach ( $list as $obj )
            {
                
                // Строка данных
                $row = [];
                
                // Данные
                if ( $data )
                {
                    $data = '';
                    if ( ! empty($obj[4]) )
                    {
                        $unserialized = (array)unserialize($obj[4]);
                        foreach ( $unserialized as $field => $value )
                        {
                            if ( is_array($value) || is_object($value) )
                            {
                                $data .= $field . ' => ' . serialize($value) . '<br>';
                            } else
                            {
                                $data .= $field . ' => ' . $value . '<br>';
                            }
                        }
                    }
                    $row[] = $data;
                }
                
                if ( $storage )
                {
                    $row[] = $obj[1];
                }
                
                // Идентификатор
                if ( $object )
                {
                    $row[] = $obj[2];
                }
                
                // Действие
                if ( $action )
                {
                    $row[] = $obj[0];
                }
                
                // Статус
                if ( $status )
                {
                    $row[] = $obj[3];
                }
                
                // Комментарий
                if ( $comment )
                {
                    $row[] = $obj[5];
                }
                
                
                // Добавление строки в массив
                $report_data[] = $row;
            }
        }

        return $report_data;
    }
    
    /**
     * Прямой экспорт в pdf
     *
     * @param string $headers - заголовки
     * @param string $data - данные
     *
     * @return void
     */
    protected function export_pdf($headers, $data)
    {
        GLOBAL $CFG;
        // Подключение библиотеки pdf
        require_once($CFG->libdir.'/pdflib.php');
        
        // Флаг прямого скачивание файла
        $dest = 'D';
        
        // Сформируем таблицу для вывода в pdf
        $table = new html_table();
        $table->data = $data;
        $table->head = $headers;
        $table->attributes = [
                'border' => '1'
        ];
        
        // Переведем таблицу в HTML
        $html_to_pdf = html_writer::table($table);
        
        // Переведем в PDF и выведем окно сохранения файла
        $pdf = new pdf('L', 'mm', [
                297,
                210
        ], true, 'UTF-8');
        $pdf->SetTitle('report_logs');
        $pdf->SetSubject('report_logs');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetAutoPageBreak(true, 10);
        $pdf->setFontSubsetting(true);
        $pdf->SetMargins(20, 10, 10, true);
        $pdf->AddPage();
        $pdf->writeHTML($html_to_pdf);
        $pdf->Output('report_logs' . '.pdf', $dest);
    }
    
    /**
     * Прямой экспорт в xls
     *
     * @param string $headers - заголовки
     * @param string $data - данные
     *
     * @return void
     */
    protected function export_xls($headers, $data)
    {
        GLOBAL $CFG;
        // Подключение библиотеки xls
        require_once($CFG->libdir.'/excellib.class.php');
        
        // Создание объекта xls файла
        $workbook = new MoodleExcelWorkbook('report_logs');
        // Задаем название файла
        $workbook->send('report_logs');
        $sheettitle = $this->dof->get_string('report_logs', 'logs');
        $myxls = $workbook->add_worksheet($sheettitle);
        
        // Стили
        $style_header = $workbook->add_format();
        $style_header->set_bold(1);
            
        $colnum = 0;
        foreach ( $headers as $item )
        {
            $myxls->write(0, $colnum, $item, $style_header);
            $colnum++;
        }
        $rownum = 1;
        
        foreach ( (array)$data as $item)
        {
            $colnum = 0;
            foreach ( $item as $row )
            {
                $myxls->write($rownum, $colnum, trim(strip_tags($row)));
                $colnum++;
            }
            $rownum++;
        }
        
        $workbook->close();
        exit;
    }
    
    /**
     * Возвращение html кода
     *
     * @param string $headers - заголовки
     * @param string $data - данные
     *
     * @return string
     */
    protected function export_html($headers, $data)
    {
        // Строим таблицу для отображения на странице
        $table = new html_table();
        $table->data = $data;
        $table->head = $headers;
        $table->attributes = [
                'border' => '1'
        ];
        return html_writer::table($table);
    }
    
    /** Получить URL к собственным файлам плагина
     * @param string $adds[optional] - фрагмент пути внутри папки плагина
     *                                 начинается с /. Например '/index.php'
     * @param array $vars[optional] - параметры, передаваемые вместе с url
     * @return string - путь к папке с плагином 
     * @access public
     */
    public function url($adds='', $vars=array())
    {
        return $this->dof->url_im($this->code(), $adds, $vars);
    }

    
    /**
     * Возвращение отчета
     *
     * @param int $id - идентификатор лога
     *
     * @return array
     */
    public function get_logreport($id, $type = 'html', $data = true, $storage = true, $object = true, $action = 'all', $status = 'all', $comment = true)
    {

        if ( ! in_array($type, $this->report_types) )
        {
            return false;
        }
        
        // Получение данных
        $report_data = $this->get_logreport_data($id, $data, $storage, $object, $action, $status, $comment);

        $method_name = 'export_' . $type;
        if ( ! empty($report_data) )
        {
            if ( $type == 'html' )
            {// Возвращение html кода
                return $this->$method_name(array_shift($report_data), $report_data);
            } else 
            {// Прямое скачивание pdf/xls
                $this->$method_name(array_shift($report_data), $report_data);
            }
        } else 
        {
            return false;
        }
    }
    
    /**
     * Возвращает HTML-код таблицы
     *
     * @param array $list - Массив
     * @param array $addvars - Массив GET-параметров
     * @param array $options - Массив дополнительных параметров отображения
     *
     * @return string - HTML-код таблицы
     */
    public function show_table($list, $addvars, $options = [])
    {
        $html = '';

        // Нормализация
        if ( ! is_array($list) )
        {
            $list = [];
        }
        
        // Таблица
        $table = new stdClass();
        $table->cellpadding = 5;
        $table->cellspacing = 5;
        $table->size = ['50px', '50px', '150px', '150px', '50px', '50px', '50px', '50px'];
        $table->align = ["center", "center", "center", "center"];
        // Шапка
        $table->head = [
            $this->dof->get_string('actions','logs'),
            $this->dof->get_string('ptype','logs'),
            $this->dof->get_string('pcode','logs'),
            $this->dof->get_string('subcode','logs'),
            $this->dof->get_string('person','logs'),
            $this->dof->get_string('timestart','logs'),
            $this->dof->get_string('duration','logs'),
            $this->dof->get_string('status','logs')
        ];
        $table->data = [];
        
        if ( ! empty($list) )
        {
            // Заполнение таблицы
            $listvars = $addvars;
            foreach ( $list as $obj )
            {
                $listvars['id'] = $obj->id;
                
                // Действия
                $actions = '';
                $actions .= $this->dof->modlib('ig')->icon(
                    'view',
                    $this->dof->url_im('logs', '/view.php', $listvars),
                    ['title' => $this->dof->get_string('view', 'logs')]
                    );
                
                // Тип
                $type = $this->dof->get_string($obj->ptype, 'logs');
                
                // Код
                $code = $this->dof->get_string('title', $obj->pcode, null, $obj->ptype);
                
                // Сабкод
                $subcode = $this->dof->get_string($obj->subcode, $obj->pcode, null, $obj->ptype);
                
                // Пользователь
                $person_fio = html_writer::link(
                        $this->dof->url_im('persons', '/view.php', ['id' => $obj->personid]), 
                        $this->dof->storage('persons')->get_fullname($obj->personid)
                        );
                
                // Дата начала
                $time_start = date('H:i:s d-m-Y', $obj->timestart);
                
                // Длительность
                if ( ! empty($obj->timeend) )
                {
                    $string = new stdClass();
                    $string->seconds = $obj->timeend - $obj->timestart;
                    $duration = $this->dof->get_string('seconds', 'logs', $string);
                } else 
                {
                    $duration = '';
                }
                
                // Статус
                $status = $this->dof->workflow('logs')->get_name($obj->status);
                
                // Добавление строки в таблицу
                $table->data[] = [$actions, $type, $code, $subcode, $person_fio, $time_start, $duration, $status];
            }
        }
        
        // Формирование таблицы
        return $this->dof->modlib('widgets')->print_table($table, true);
    }
}