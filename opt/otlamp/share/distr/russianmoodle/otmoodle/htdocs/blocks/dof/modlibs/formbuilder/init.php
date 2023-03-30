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
 * Менеджер построения форм
 * 
 * Менеджер для решения задачи построения общих форм с набором полей 
 * по нескольким объектам Деканата с поддержкой дополнительных полей и полей Moodle
 *
 * @package    modlib
 * @subpackage formbuilder
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dof_modlib_formbuilder implements dof_plugin_modlib
{
    /**
     * Объект деканата для доступа к общим методам
     * 
     * @var dof_control
     */
    protected $dof;
    
    /**
     * Доступные типы дополнительных полей
     * 
     * @var array - Массив дополнительных полей в формате ['codetype'] => path_to_class
     */
    protected $customfields = null;
    
    /**
     * Список инициализированных форм
     * 
     * @var array
     */
    protected $forms = [];
    
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
		return 2017091900;
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
        return 'formbuilder';
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
     * Конструктор
     * 
     * @param dof_control $dof - объект с методами ядра деканата
     */
    public function __construct($dof)
    {
        // Сохраняем ссылку на DOF, чтоб вызывать его через $this->dof
        $this->dof = $dof;
        
        require_once $dof->plugin_path('modlib', 'formbuilder', '/classes/exception.php');
        require_once $dof->plugin_path('modlib', 'formbuilder', '/classes/customfieldtypes/base.php');
        
        // Регистрация доступных дополнительных полей
        $this->customfields = $this->init_customfield_types();
    }
    
    // **********************************************
    //              Собственные методы
    // **********************************************
    
    /**
     * Получить список доступных дополнительных полей
     * 
     * @return array
     */
    public function get_customfields_types()
    {
        return $this->customfields;
    }
    
    /**
     * Получить локализованный список дополнительных полей
     *
     * @return array
     */
    public function get_customfields_localized_types()
    {
        $customfieldclasses = $this->get_customfields_types();
        foreach ( $customfieldclasses as &$type )
        {
            $type = $type::get_localized_type();
        }
        return $customfieldclasses;
    }
    
    /**
     * Получить список доступных полей
     *
     * @return array - Массив типов полей
     */
    protected function init_customfield_types()
    {
        global $CFG;
    
        if ( $this->customfields === null )
        {// Получение списка полей
    
            $this->customfields = [];
    
            // Валидация пути до списка полей
            $fieldsdir = $this->dof->plugin_path('modlib', 'formbuilder', '/classes/customfieldtypes');
            if ( is_dir($fieldsdir) )
            {
                // Поиск полей
                foreach ( (array)scandir($fieldsdir) as $fieldname )
                {
                    if ( $fieldname == '.' || $fieldname == '..' )
                    {
                        continue;
                    }
    
                    if ( is_dir($fieldsdir.'/'.$fieldname) )
                    {// Папка с классом дополнительного поля
    
                        $fieldpath = $fieldsdir.'/'.$fieldname.'/init.php';
                        if ( file_exists($fieldpath) )
                        {// Класс дополнительного поля найден
                            require_once($fieldpath);
    
                            // Название класса дополнительного поля
                            $classname = 'dof_customfields_'.$fieldname;
                            if ( class_exists($classname) )
                            {// Класс дополнительного поля найден
                                $this->customfields[$fieldname] = $classname;
                            }
                        }
                    }
                }
            }
        }
        return $this->customfields;
    }
    
    /**
     * Инициализация поля указанного типа без привязки в конкретному шаблону
     * 
     * @param string $type - Тип дополнительного поля
     * 
     * @return null|dof_customfields_base - Дополнительное поле
     */
    public function init_customfield_by_type($type)
    {
        if ( ! isset($this->customfields[$type]) )
        {// Тип поля не зарегистрирован
            return null;
        }
        
        // Тип поля
        $customfieldclass = $this->customfields[$type];
        
        return new $customfieldclass($this->dof);
    }
    
    /**
     * Инициализация поля на основе шаблона
     *
     * @param stdClass|int $item - Шаблон дополнительного поля
     *
     * @return null|dof_customfields_base - Дополнительное поле
     */
    public function init_customfield_by_item($item)
    {
        if ( ! isset($item->type) )
        {// Передан идентификатор шаблона поля
            
            // Получение шаблона поля
            $item = $this->dof->storage('customfields')->get((int)$item);
            if ( empty($item) )
            {// Шаблон поля не найден
                return null;
            }
        }
        
        if ( ! isset($this->customfields[$item->type]) )
        {// Тип поля не зарегистрирован
            return null;
        }
        // Тип поля
        $customfieldclass = $this->customfields[$item->type];
        
        return new $customfieldclass($this->dof, $item);
    }
    
    /**
     * Инициализация шаблона новой формы
     * 
     * @param unknown $formname - Имя формы
     * @param unknown $formurl - URL перенаправления
     * 
     * @return void
     */
    public function init_form($formname, $formurl = null, $customdata = null, $formclass = null )
    {
        require_once $this->dof->plugin_path('modlib', 'formbuilder', '/form.php');
        
        // Нормализация входных данных
        if ( empty($formclass) )
        {// Класс не указан
            // Установка базового класса билдера
            $formclass = 'dof_modlib_formbuilder_form';
        }
        if ( empty($customdata) )
        {
            $customdata = new stdClass();
        }
        $customdata = (object)$customdata;
        
        // Получение имени формы
        $formname = $this->create_form_name((string)$formname);
        
        $customdata->dof = $this->dof;
        $customdata->formname = $formname;
        
        // Инициализация формы
        $this->forms[$formname] = new $formclass($formurl, $customdata);
    }
    
    /**
     * Инициализация шаблона новой формы
     *
     * @param unknown $formname - Имя формы
     * @param unknown $formurl - URL перенаправления
     *
     * @return void
     */
    public function process_form($formname)
    {
        // Получение имени формы
        $formname = $this->create_form_name($formname);

        // Инициализация формы
        $this->forms[$formname]->process();
    }
    
    /**
     * Генерация HTML-кода формы
     *
     * @return void
     */
    public function render_form($formname)
    {
        // Получение имени формы
        $formname = $this->create_form_name($formname);
    
        $htmlform = $this->forms[$formname]->render();
        
        return $htmlform;
    }
    
    /**
     * Инициализация нового раздела в форме
     *
     * @param unknown $formname - Имя формы
     * @param unknown $sectionname - Имя раздела
     *
     * @return string $sectioncode - Код раздела
     */
    public function add_section($formname, $sectionname, $sectiongroup = '')
    {
        // Получение имени формы
        $formname = $this->create_form_name($formname);
    
        // Инициализация формы
        return $this->forms[$formname]->add_section($sectionname, $sectiongroup);
    }
    
    /**
     * Добавление допполей в указанный раздел целевой формы
     *
     * @param unknown $formname - Имя формы
     * @param unknown $sectionname - Код раздела
     * @param unknown $pcode - Код хранилища объекта
     * @param unknown $objectid - ID объекта
     * 
     * @return void
     */
    public function add_customfields($formname, $sectioncode, $pcode, $departmentid, $objectid)
    {
        // Получение имени формы
        $formname = $this->create_form_name($formname);
        
        // Получение дополнительных полей для указанного объекта
        $customfields = (array)$this->dof->storage('customfields')->
            get_customfields($departmentid, ['linkpcode' => $pcode]);
        
        // Добавление в указанную форму всех дополнительных полей объекта
        foreach ( $customfields as $customfielddata )
        {
            // Получение класса дополнительного поля из списка зарегистрированных типов
            $classname = $this->customfields[$customfielddata->type];
            // Инициализация дополнительного поля
            $customfield = new $classname($this->dof, $customfielddata);
            // Добавление указанного дополнительного поля в целевой раздел формы
            $this->forms[$formname]->add_customfield($sectioncode, $customfield, $objectid);
        }
    }
    
    /**
     * Генерация имени формы
     * 
     * @return string
     */
    public function create_form_name($formname = '')
    {
        return 'formbuilder__'.$formname;
    }
    
    /**
     * Проверка доступа к файлам, принадлежащим к текущему хранилищу
     *
     * @param string $fileareacode - Имя файловой зоны
     * @param string $itemid - ITEMID файла в зоне
     *
     * @return boolean
     *
     * @throws dof_storage_customfields_exception
     */
    public function file_access($fileareacode, $itemid)
    {
        // Разбиение кода поля
        $path = explode('_',$fileareacode, 2);
        
        $customfieldcode = array_pop($path);
        $departmentid = array_pop($path);
        
        // Получение шаблона
        $customfields = $this->dof->storage('customfields')->get_customfields(
            $departmentid,
            ['code' => $customfieldcode]
        );

        if ( empty($customfields) )
        {// Шаблон не найден
            return false;
        }
        
        if ( count($customfields) > 1 )
        {
            throw new dof_storage_customfields_exception(
                $this->dof->get_string('error_not_unique_dep_n_code', 'customfields', null, 'storage')
            );
        }
        
        // Получение шаблона допполя
        $customfield = array_shift($customfields);
        
        // Инициализация поля
        $customfield = $this->init_customfield_by_item($customfield);

        // Получение ID владельца
        $objectid = $customfield->get_objectid_from_itemid($itemid);
        
        try
        {
            $customfield->check_access('viewdata', $objectid);
            return true;
        } catch( dof_storage_customfields_exception $e )
        {
            return false;
        }
    }
    
    /**
     * Добавление допполя в существующую форму
     *
     * @param unknown &$mform - Форма, куда надо добавить
     * @param unknown $customfield - Доп поле
     * @param unknown $objectid - ID персоны
     *
     * @return void
     */
    public function add_customfield_to_form(&$mform, $customfield, $objectid, $freeze = false)
    {
        $classname = $this->customfields[$customfield->type];
        $customfield = new $classname($this->dof, $customfield);
        if ($freeze) {
            $customfield->render_element($mform, $objectid);
        } else {
            $customfield->create_element($mform, $objectid);
        }
        return $customfield;
    }
    
    /**
     * Возвращаем статус формы
     * 
     * @param string $formname - Название формы
     * 
     * @return bool
     * 
     */
    public function is_form_submitted($formname)
    {
        $formname = $this->create_form_name($formname);
        return $this->forms[$formname]->is_submitted();
    }
    
    /**
     * Смена статуса дополнительного поля
     *
     * @param string $formname - Название формы
     *
     * @return bool
     */
    public function customfield_status_change($item, $targetstatus, $person = null)
    {
        // Получение дополнительного поля
        if ( ! is_object($item) )
        {
            $item = $this->dof->storage('customfields')->get((int)$item);
        }
        if ( ! empty($item) )
        {
            // Получение доступных статусов перевода
            $available = $this->customfield_status_get_available_by_person($item, $person);
            if ( isset($available[$targetstatus]) )
            {// Перевод доступен
                if( $targetstatus == 'available' )
                {// Производится попытка активировать доп.поле
                    
                    // Получение других активных полей в подразделении с таким же кодом
                    $anotherfields = $this->dof->storage('customfields')->get_customfields(
                        $item->departmentid,
                        [
                            'code' => $item->code
                        ]
                    );
                    if( ! empty($anotherfields) )
                    {// Заданный код не уникален  в рамках подразделения
                        return false;
                    }
                }
                $result = $this->dof->workflow('customfields')->
                    change($item->id, $targetstatus);
                return $result;
            }
        }
        return false;
    }
    
    /**
     * Получить доступные статусы перевода для дополнительного поля
     *
     * @param int|stdClass $item - Дополнительное поле
     * @param int|stdClass $person - Текущая персона, для поторой формируется список
     */
    public function customfield_status_get_available_by_person($item, $person = null)
    {
        // Получение подписки
        if ( ! is_object($item) )
        {
            $item = $this->dof->storage('customfields')->get((int)$item);
        }
        // Получение персоны
        if ( ! is_object($person) )
        {
            $person = $this->dof->storage('persons')->get_bu($person, true);
        }
        
        $departmentid = $this->dof->storage('customfields')->get_field($item->id, 'departmentid');
        
        // Получение возможного набора статусов
        $statuses = (array)$this->dof->workflow('customfields')->get_available($item->id);
        // Фильтрация списка с учетом прав доступа
        $available = [];
        foreach ( $statuses as $status => $statuslocalized )
        {
            // Проверка доступа на смену статуса
            $access = $this->dof->workflow('customfields')->
                is_access('changestatus:to:'.$status, $item->id, $person->mdluser, $departmentid);
            if ( $access )
            {// Доступ разрешен
                $available[$status] = $statuslocalized;
            }
        }
        return $available;
    }
    
}
?>