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

/**
 * Библиотека шаблонизатора
 *
 * @package    im
 * @subpackage modlib
 */
class dof_modlib_templater implements dof_plugin_modlib
{
    /**
     * Объект деканата для доступа к общим методам
     * 
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
     * 
     * @return boolean
     */
    public function install()
    {
        return true;
    }
    
    /** 
     * Метод, реализующий обновление плагина в системе.
     * Создает или модифицирует существующие таблицы в БД
     * 
     * @param string $old_version - Версия установленного в системе плагина
     * 
     * @return boolean
     */
    public function upgrade($oldversion)
    {
        return true;
    }
    
    /**
     * Возвращает версию установленного плагина
     * 
     * @return int - Версия плагина
     */
    public function version()
    {
		return 2017031600;
    }
    
    /** 
     * Возвращает версии интерфейса Деканата, с которыми этот плагин может работать
     * 
     * @return string
     */
    public function compat_dof()
    {
        return 'aquarium';
    }
    
    /**
     * Возвращает версии стандарта плагина этого типа, которым этот плагин соответствует
     * 
     * @return string
     */
    public function compat()
    {
        return 'neon';
    }
    
    /** 
     * Возвращает тип плагина
     * 
     * @return string 
     */
    public function type()
    {
        return 'modlib';
    }
    
    /**
     * Возвращает короткое имя плагина
     *
     * Оно должно быть уникально среди плагинов этого типа
     *
     * @return string
     */
    public function code()
    {
        return 'templater';
    }
    
    /**
     * Возвращает список плагинов, без которых этот плагин работать не может
     *
     * @return array
     */
    public function need_plugins()
    {
        return [
            'modlib' => [
                'pear' => 2009032000
            ]
        ];
    }
    
    /** 
     * Определить, возможна ли установка плагина в текущий момент
     * Эта функция одинакова абсолютно для всех плагинов и не содержит в себе каких-либо зависимостей
     * @TODO УДАЛИТЬ эту функцию при рефакторинге. Вместо нее использовать наследование
     * от класса dof_modlib_base_plugin 
     * 
     * @see dof_modlib_base_plugin::is_setup_possible()
     * @param int $oldversion[optional] - старая версия плагина в базе (если плагин обновляется)
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

    /**
     * Получить список плагинов, которые уже должны быть установлены в системе,
     * и без которых начать установку или обновление невозможно
     *
     * @param int $oldversion[optional] - старая версия плагина в базе (если плагин обновляется)
     *                                    или 0 если плагин устанавливается
     *
     * @return array массив плагинов, необходимых для установки
     *      Формат: array('plugintype'=>array('plugincode' => YYYYMMDD00));
     */
    public function is_setup_possible_list($oldversion = 0)
    {
        return [];
    }
    
    /** 
     * Список обрабатываемых плагином событий 
     * 
     * @return array - array(array('plugintype'=>..,'plugincode'=>..,'eventcode'=>..),...)
     */
    public function list_catch_events()
    {
       return [];
    }
    
    /** 
     * Требуется ли запуск cron в плагине
     * 
     * @return bool
     */
    public function is_cron()
    {
       // Запуск не требуется
       return false;
    }
    
    /** 
     * Проверяет полномочия на совершение действий
     * 
     * @param string $do - идентификатор действия, которое должно быть совершено
     * @param int $objid - идентификатор экземпляра объекта, 
     *                     по отношению к которому это действие должно быть применено
     * @param int $userid - идентификатор пользователя Moodle, полномочия которого проверяются
     * 
     * @return bool 
     *              true - можно выполнить указанное действие по 
     *                     отношению к выбранному объекту
     *              false - доступ запрещен
     */
    public function is_access($do, $objid = NULL, $userid = NULL)
    {
        // Используем функционал из $DOFFICE
        return $this->dof->is_access($do, NULL, $userid);
    }
    
    /** 
	 * Требует наличия полномочия на совершение действий
	 * 
     * @param string $do - идентификатор действия, которое должно быть совершено
     * @param int $objid - идентификатор экземпляра объекта, 
     *                     по отношению к которому это действие должно быть применено
     * @param int $userid - идентификатор пользователя Moodle, полномочия которого проверяются
     * 
     * @return bool 
     *              true - можно выполнить указанное действие по 
     *                     отношению к выбранному объекту
     *              false - доступ запрещен
     */
    public function require_access($do, $objid = NULL, $userid = NULL)
    {
        if ( ! $this->is_access($do, $objid, $userid) )
        {
            $code = $this->code();
            $type = $this->type();
            $notice = $code.'/'.$do.' (block/dof/'.$type.'/'.$code.': '.$do.')';
            if ($objid){$notice.=" id={$objid}";}
            $this->dof->print_error('nopermissions','',$notice);
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
     * 
     * @return bool - true в случае выполнения без ошибок
     */
    public function catch_event($gentype,$gencode,$eventcode,$intvar,$mixedvar)
    {
        return false;
    }
    
    /**
     * Запустить обработку периодических процессов
     * 
     * @param int $loan - нагрузка (
     *              1 - только срочные, 
     *              2 - нормальный режим, 
     *              3 - ресурсоемкие операции
     *        )
     * @param int $messages - количество отображаемых сообщений (
     *              0 - не выводить,
     *              1 - статистика,
     *              2 - индикатор, 
     *              3 - детальная диагностика
     *        )
     *        
     * @return bool - true в случае выполнения без ошибок
     */
    public function cron($loan,$messages)
    {
        return true;
    }
    
    /**
     * Обработать задание, отложенное ранее в связи с его длительностью
     *
     * @param string $code - код задания
     * @param int $intvar - дополнительный параметр
     * @param mixed $mixedvar - дополнительные параметры
     *
     * @return bool - true в случае выполнения без ошибок
     */
    public function todo($code,$intvar,$mixedvar)
    {
        return true;
    }
    
    /**
     * Конструктор
     *
     * @param dof_control $dof - объект с методами ядра деканата
     */
    public function __construct($dof)
    {
        // Сохраняем ссылку на DOF, чтоб вызывать его через $this->dof
        $this->dof = $dof;
    }
    
    // **********************************************
    // Собственные методы
    // **********************************************
    
    /** 
     * Получение шаблонизатора для указанного плагина
     * 
     * @param string $plugintype - Тип плагина
     * @param string $pluginname - Имя плагина
     * @param array $exportdata - Данные для экспорта 
     * @param string $templatename - Имя шаблона
     * 
     * @return dof_modlib_templater_package - Объект для работы с шаблоном
     */
    public function template($plugintype, $pluginname, $exportdata, $templatename = '')
    {
        $templatename = (string)$templatename;
        
        if ( ! empty($templatename) )
        {// Указан шаблонизатор
            
            // Путь до шаблонизатора
            $path = $this->template_path($plugintype, $pluginname, $templatename, 'init.php', true);
            if ( file_exists($path) )
            {// Подключение шаблонизатора
                require_once($this->dof->plugin_path($this->type(), $this->code(), '/package.php'));
                require_once($path);
                
                // Название класса
                $classname = 'dof_'.$plugintype.'_'.$pluginname.'_templater_'.$templatename;
                if ( class_exists($classname) )
                {// Класс найден
                    return new $classname($this->dof, $plugintype, $pluginname, $exportdata, $templatename);
                }
            }
        }

        // Подключение стандартного шаблонизатора
        return $this->get_standard_package($plugintype, $pluginname, $exportdata, $templatename);
    }
    
    /** 
     * Инициализация стандартного шаблонизатора
     * 
     * @param string $plugintype - Тип плагина
     * @param string $pluginname - Имя плагина
     * @param array $exportdata - Данные для экспорта 
     * @param string $templatename - Имя шаблона
     * 
     * @return false|dof_modlib_templater_package
     */
    private function get_standard_package($plugintype, $pluginname, $exportdata, $templatename = null)
    {
        // Путь до шаблонизатора
		$path = $this->template_path($this->type(), $this->code(), null, 'package.php');
		
        if ( ! file_exists($path) )
        {// Файл отсутствует
            return false;
        }
        
        // Подключение шаблонизатора
        require_once($path);
        
        $classname = 'dof_modlib_templater_package';
        if ( ! class_exists($classname) )
        {// Класс не найден
            return false;
        }
        // Инициализация базового шаблонизатора
        return new $classname($this->dof, $plugintype, $pluginname, $exportdata, $templatename);
    }
    
    /** 
     * Возвращает путь к шаблону (корню или внутренней папке) 
     * @return string путь к плагину
     * @param string $plugintype - тип плагина (im, storage, и др.)
     * @param string $pluginname - имя плагина
     * @param string $templatename[optional] - имя шаблона форматирования (order, report и т. д.)
     * @param string $adds[optional] - дополнительные параметры
     * @param bool   $fromplugin - определяет, где искать подключаемые файлы. 
     *                             Если null  - то и во внешнем плагине, и в modlib.
     *                             Если true  - только во внешнем плагине
     *                             Если false - только в modlib
     *                             
     * @todo оптимизировать код, он может занимать в 2 раза меньше места
     */
    public function template_path($plugintype, $pluginname, $templatename = null, $addpath = '', $fromplugin=null)
    {
        $addpath = (string)$addpath;
        $addpath = trim($addpath);
        if ( ! empty($addpath) )
        {
            $addpath = '/'.$addpath;
        }
        if ( ! empty($templatename) )
        {
            $templatename = '/'.$templatename;
        }
        
        // Определение путей для подключения шаблонизатора
        $externalpath = $this->dof->plugin_path($plugintype, $pluginname,'/templater'.$templatename.$addpath);
        $internalpath = $this->dof->plugin_path('modlib', 'templater', $addpath);

        if ( is_null($templatename) )
        {// имя переопределенного плагина не указано
            
            if ($fromplugin === true)
            {// если имя плагина не задано, но сказано использовать внешний планин - то это ошибка 
                return false;
            }
            
            if ($this->path_is_exists($internalpath))
            {// в этой ветке всегда возвращаем внутренний путь
                return $internalpath;
            }else
            {//путь внутри нашего плагина не существует
                return false;
            }
        }else
        {// имя переопределенного плагина указано - берем из этой папки
            if ( $fromplugin === null )
            {//ищем и в modlib/templater и во внешнем плагине 
                if ($this->path_is_exists($externalpath))
                {// во внешнем плагине есть необходимый файл или папка
                    return $externalpath;
                }elseif($this->path_is_exists($internalpath))
                {// если во внешнем плагине нет - ищем во внутреннем
                    return $internalpath;
                }else
                {// указанный путь не существует ни во внутреннем ни во внешнем плагине
                    return false;
                }
            }elseif( $fromplugin === true )
            {//ищем только во внешнем плагине
                if ($this->path_is_exists($externalpath))
                {
                    return $externalpath;
                }else
                {
                    return false;
                }
            }elseif( $fromplugin === false )
            {//ищем только в modlib/templater 
                if ($this->path_is_exists($internalpath))
                {
                    return $internalpath;
                }else
                {
                    return false;
                }
            }
        }
    }
    
    /** 
     * Проверяет, является ли указанный путь 
     * директорией, файлом, или символической ссылкой
     * @return true или false
     * @param string $path - путь к файлу или папке
     */
    private function path_is_exists($path)
    {
        return is_file($path) OR is_dir($path) OR is_link($path);
    }
}
?>