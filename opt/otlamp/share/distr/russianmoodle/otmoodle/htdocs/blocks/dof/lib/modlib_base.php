<?PHP
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

/**
 * Базовый класс библиотеки Деканата
 *
 * @package    modlib
 * @subpackage base
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class dof_modlib_base implements dof_plugin_modlib
{
    /**
     * Контроллер Деканата
     *
     * @var dof_control
     */
    protected $dof;

    /**
     * Получить версию установленного плагина
     *
     * @return int - Версия плагина
     */
    public abstract function version();
    
    /**
     * Конструктор плагина
     *
     * @param dof_control $dof - Контроллер Деканата
     */
    public function __construct($dof)
    {
        // Сохраняем ссылку на DOF, чтоб вызывать его через $this->dof
        $this->dof = $dof;
    }
    
    /**
     * Процесс установки плагина
     * 
     * Создает или модифицирует существующие таблицы в БД
     * и заполняет их начальными значениями
     *
     * @return bool
     */
    public function install()
    {
        return true;
    }
    
    /**
     * Процесс обновления плагина
     * 
     * Создает или модифицирует существующие таблицы в БД
     *
     * @param int $old_version - Версия установленного плагина
     *
     * @return bool
     */
    public function upgrade($oldversion)
    {
        return true;
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
     * Получить тип плагина
     *
     * @return string
     */
    public function type()
    {
        return 'modlib';
    }
    
    /**
     * Получить уникальный код плагина указанного типа
     *
     * @return string
     */
    public function code()
    {
        $classname = get_called_class();
        $classname = str_replace('dof_modlib_', '', $classname);
        return $classname;
    }
    
    /**
     * Возвращает список плагинов, без которых этот плагин работать не может
     *
     * @return array
     */
    public function need_plugins()
    {
        return [];
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
     * Получить настройки для плагина со значениями по умолчанию
     *
     * @param string $code
     *
     * @return array - Массив настроек плагина
     */
    public function config_default($code = null)
    {
        // Плагин включен и используется
        $config = [];
        
        return $config;
    }
    
    /**
     * Получить список параметров для фунции has_hight()
     *
     * @return object - список параметров для фунции has_hight()
     * @param string $action - совершаемое действие
     * @param int $objectid - id объекта над которым совершается действие
     * @param int $personid
     */
    protected function get_access_parametrs($action, $objectid, $personid, $depid = null)
    {
        $result = new stdClass();
        $result->plugintype   = $this->type();
        $result->plugincode   = $this->code();
        $result->code         = $action;
        $result->personid     = $personid;
        $result->departmentid = $depid;
        $result->objectid     = $objectid;
        
        if ( is_null($depid) )
        {// Подразделение не задано - ищем в GET/POST
            $result->departmentid = optional_param('departmentid', 0, PARAM_INT);
        }
        if ( ! $objectid )
        {// Если objectid не указан - установим туда 0 чтобы не было проблем с sql-запросами
            $result->objectid = 0;
        }
        
        return $result;
    }
    
    /**
     * Проверить права через плагин acl.
     *
     * Функция вынесена сюда, чтобы постоянно не писать
     * длинный вызов и не перечислять все аргументы
     *
     * @param object $acldata - объект с данными для функции storage/acl->has_right()
     *
     * @return bool
     */
    protected function acl_check_access_paramenrs($acldata)
    {
        return $this->dof->storage('acl')->
            has_right(
                $acldata->plugintype,
                $acldata->plugincode,
                $acldata->code,
                $acldata->personid,
                $acldata->departmentid,
                $acldata->objectid
            );
    }
    
    /**
     * Сформировать права доступа для интерфейса
     *
     * @return array - Массив с данными по правам доступа
     */
    public function acldefault()
    {
        $a = [];
        
        return $a;
    }
    
    
}
?>
