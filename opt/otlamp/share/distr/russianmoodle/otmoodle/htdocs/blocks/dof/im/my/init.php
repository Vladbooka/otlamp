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
// подключение интерфейса настроек
require_once($DOF->plugin_path('storage','config','/config_default.php'));

/** Пример плагина интерфейса
 * 
 */
class dof_im_my implements dof_plugin_im
{
    /**
     * @var dof_control
     */
    protected $dof;
    // **********************************************
    // Методы, предусмотренные интерфейсом plugin
    // **********************************************
    /** 
     * Метод, реализующий инсталяцию плагина в систему
     * Создает или модифицирует существующие таблицы в БД
     * и заполняет их начальными значениями
     * @return boolean
     * Может надо возвращать массив с названиями таблиц и результатами их создания?
     * чтобы потом можно было распечатать сообщения о результатах обновления
     * @access public
     */
    public function install()
    {
        return true;
    }
    /** 
     * Метод, реализующий обновление плагина в системе
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
    /** 
     * Возвращает версию установленного плагина
     * @return string
     * @access public
     */
    public function version()
    {
        return 2016081800;
    }
    /** 
     * Возвращает версии интерфейса Деканата, 
     * с которыми этот плагин может работать
     * @return string
     * @access public
     */
    public function compat_dof()
    {
        return 'aquarium';
    }

    /** 
     * Возвращает версии стандарта плагина этого типа, 
     * которым этот плагин соответствует
     * @return string
     * @access public
     */
    public function compat()
    {
        return 'angelfish';
    }
    
    /** 
     * Возвращает тип плагина
     * @return string 
     * @access public
     */
    public function type()
    {
        return 'im';
    }
    /** 
     * Возвращает короткое имя плагина
     * Оно должно быть уникально среди плагинов этого типа
     * @return string
     * @access public
     */
    public function code()
    {
        return 'my';
    }
    /** 
     * Возвращает список плагинов, 
     * без которых этот плагин работать не может
     * @return array
     * @access public
     */
    public function need_plugins()
    {
        return array( 'modlib'=>array('nvg'=>2008060300),
                'im'=>array('admin'=>2008060300),
                'storage'=>array('config'=>2012042500));
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
        return array('storage'=>array('config'=>2012042500));
    }
    /** 
     * Список обрабатываемых плагином событий 
     * @return array - array(array('plugintype'=>..,'plugincode'=>..,'eventcode'=>..),...)
     * @access public
     */
    public function list_catch_events()
    {
       return array();       
    }
    /** 
     * Требуется ли запуск cron в плагине
     * @return bool
     * @access public
     */
    public function is_cron()
    {
		// Этому плагину не нужен крон
		return false;
    }
    
    /** 
     * Проверяет полномочия на совершение действий
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
    /** 
     * Требует наличия полномочия на совершение действий
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
        // Используем функционал из $DOFFICE
        return $this->dof->require_access($do, NULL, $userid);
    }
    /** 
     * Обработать событие
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
    /** 
     * Запустить обработку периодических процессов
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
    /** 
     * Обработать задание, отложенное ранее в связи с его длительностью
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
    /** 
     * Конструктор
     * @param dof_control $dof - объект $DOF
     * @access public
     */
    public function __construct($dof)
    {
        $this->dof = $dof;
    }
    // **********************************************
    // Методы, предусмотренные интерфейсом im
    // **********************************************
    /** 
     * Возвращает содержимое блока
     * @param string $name - название набора текстов для отображания
     * @param int $id - id текста в наборе
     * @return string - html-код названия блока
     */
    function get_block($name, $id = 1)
    {
        $result = '';

        // Инициализируем генератор HTML
        if ( !class_exists('dof_html_writer') )
        {
            $this->dof->modlib('widgets')->html_writer();
        }

        $addvars = [
            'departmentid' => $this->dof->storage('departments')->get_user_default_department()
        ];
        
        switch ($name)
        {
            case 'link':
                $result = dof_html_writer::link(
                    $this->dof->url_im($this->code(),'/index.php'),
                    $this->dof->get_string('page_main_name')
                );
                break;
		    case 'main': 
		        $result = '';
		        $result .= '<a href="'.$this->dof->url_im('my').'">'
							.$this->dof->get_string('title', 'my').'</a><br>';
			    $result .= '<a href="'.$this->dof->url_im('standard').'">'
							.$this->dof->get_string('title').'</a><br>';
                break;	
		    default: 
		        break;
		}
		return $result;
    }
    /** Возвращает содержимое секции
     * @param string $name - название набора текстов для отображания
     * @param int $id - id текста в наборе
     * @return string  - html-код названия секции
     */
    function get_section($name, $id = 1)
    {
        $result = '';
        
        // Инициализируем генератор HTML
        if ( !class_exists('dof_html_writer') )
        {
            $this->dof->modlib('widgets')->html_writer();
        }
        // Получаем подразделение пользователя
        $departmentid = $this->dof->storage('departments')->get_user_default_department();
        switch($name)
        {
            case 'summary':
                $sections = $this->get_list_section($departmentid, [
                    'code' => 'info',
                    'id' => $id
                ]);
                $result .= $this->dof->modlib('nvg')->print_sections($sections, [
                    'returnhtml' => true
                ]);
                break;
            case 'shortsummary':
                $sections = $this->get_list_section($departmentid, [
                    'code' => 'shortinfo',
                    'id' => $id,
                    'section_output_order' => 'im_acl_user_and_acl_link,im_employees_list_working_data,im_recordbook_list_learning_data,*'
                ]);
                $result .= $this->dof->modlib('nvg')->print_sections($sections, [
                    'returnhtml' => true
                ]);
                break;
            default:
                break;
        }
    	return $result;
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
    
    // ***********************************************************
    //       Методы для работы с конфигурацией
    // ***********************************************************
    
    /** Функция получения настроек для плагина
     *
     */
    public function config_default($code=null)
    {
        $config = array();
        // порядок вывода секций плагина my
        $obj = new stdClass();
        $obj->type = 'text';
        $obj->code = 'section_output_order';
        $obj->value = 'im_acl_my_warrants,im_journal_my_events,im_journal_my_load,*';
        $config[$obj->code] = $obj;
        return $config;
    }
    
    
    // **********************************************
    //              Собственные методы
    // **********************************************
    
    /** Функция отображения секций
     *
     */
    public function get_list_section($departmentid, $options=[])
    {
        global $USER;
        
        $sections = [];
        
        if( empty($options['code']) )
        {
            $options['code'] = 'info';
        }
        if( empty($options['id']) )
        {
            $options['id'] = $this->dof->storage('persons')->get_by_moodleid_id($USER->id);
        }

        // посылаем клич к другим плагинам
        if ( ! $result = $this->dof->send_event('im', 'my', $options['code'], $options['id']) )
        {// никто не отозвался - вернем пустой массив
            return [];
        }
        if( empty($options['section_output_order']) )
        {// получаем конфиг порядка вывода
            $conf = $this->dof->storage('config')->get_config('section_output_order', 'im', 'my', $departmentid);
            if ($conf)
            {
                $sortorder = $conf->value;
            } else
            {// конфига нет - вернем секции как они пришли
                foreach ( $result->raw() as $plugin )
                {
                    foreach ( $plugin->result as $value )
                    {// получаем ключ(рейтинг) для текущих секций
                        $name = "{$plugin->plugintype}_{$plugin->plugincode}_{$value['name']}";
                        $sections[$name] = $value;
                    }
                }
                return $sections;
            }
        } else 
        {
            $sortorder = $options['section_output_order'];
        }
        
        
        // делаем из конфига массив
        $sortorder = array_flip(explode(',', $sortorder));
        // дополнительный массив для отслеживания секций конфига
        $intersortorder = $sortorder;
        // выполняем сортировку
        foreach ( $result->raw() as $plugin )
        {
            foreach ( $plugin->result as $value )
            {// получаем ключ(рейтинг) для текущих секций
                $name = "{$plugin->plugintype}_{$plugin->plugincode}_{$value['name']}";
                if ( !isset($sortorder[$name]) )
                {// если в конфиге нет - кидаем во временный массив
                    $sections[$name] = $value;
                }else
                {// кидаем в массив конфига
                    $sortorder[$name] = $value;
                    // удаляем из доп массива
                    unset($intersortorder[$name]);
                }
            }
        }

        if ( isset($sortorder['*']) )
        {// в конфиге есть * - заменяем ее временным массивом
            array_splice($sortorder,$sortorder['*'],1,$sections);
            unset($intersortorder['*']);
        }
        foreach ( $intersortorder as $name=>$id )
        {// если в доп массиве что-то осталось, значит секция не пришла
            // хотя в конфиге она указана - удаляем их
            unset($sortorder[$name]);
        }
        return $sortorder;
    }
    
    
}
?>