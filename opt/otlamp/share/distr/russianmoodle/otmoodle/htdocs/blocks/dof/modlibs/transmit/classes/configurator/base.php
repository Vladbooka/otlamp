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
 * Обмен данных с внешними источниками. Базовый класс конфигуратора.
 *
 * @package    modlib
 * @subpackage transmit
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class dof_modlib_transmit_configurator_base
{
    /**
     * Контроллер ЭД
     * 
     * @var dof_control
     */
    protected $dof;
    
    /**
     * Кэш доступных источников
     *
     * @var array|null
     */
    protected $available_sources = null;
    
    /**
     * Кэш доступных масок
     *
     * @var array|null
     */
    protected $available_masks = null;
    
    /**
     * Объект маски
     *
     * @var dof_modlib_transmit_strategy_mask_base
     */
    protected $mask = null;
    
    /**
     * Объект маски
     *
     * @var dof_modlib_transmit_source_base
     */
    protected $source = null;
    
    /**
     * CSV логгер
     *
     * @var dof_storage_logs_queuetype_base
     */
    protected $logger = null;
    
    /**
     * Симуляция
     *
     * @var bool
     */
    protected $simulation = false;
    
    /**
     * Объект пакета настроек
     *
     * @var dof_modlib_transmit_pack
     */
    protected $pack = null;
    
    /**
     * Запуск процесса синхронизации
     *
     * @return void
     */
    abstract protected function transmit_process();
    
    /**
     *  Установка источника в конфигуратор
     *
     * @param string $sourcecode - Код источника
     *
     * @return void
     */
    protected final function setup_source($sourcecode)
    {
        // Получение списка доступных источников
        $sources = $this->get_available_sources();
        if ( empty($sources[$sourcecode]) )
        {
            throw new dof_modlib_transmit_exception('invalid_sourcecode', 'modlib_transmit');
        }
        
        $classname = $sources[$sourcecode];
        $this->source = new $classname($this->dof, $this->logger);
        
        $importfields = $this->mask->get_importfields();
        foreach($importfields as $fieldcode => $fieldinfo)
        {
            $strategyclass = $this->mask->get_strategy_class();
            $importfields[$fieldcode]['description'] = $strategyclass::get_fielddescription_localized($fieldcode);
        }
        
        $this->source->set_mask_import_fields($importfields);
    }
    
    /**
     * Установка маски в конфигуратор
     *
     * @param string $maskfullcode - Полный код маски
     *
     * @return void
     */
    protected final function setup_mask($maskfullcode)
    {
        // Получение списка доступных масок
        $masks = $this->get_available_masks();
        if ( empty($masks[$maskfullcode]) )
        {
            throw new dof_modlib_transmit_exception('invalid_maskcode', 'modlib_transmit');
        }
        
        $classname = $masks[$maskfullcode];
        $this->mask = new $classname($this->dof, $this->logger);
    }
    
    /**
     * Получение кода конфигуратора
     *
     * @return string
     */
    public static final function get_code()
    {
        return str_replace('dof_modlib_transmit_configurator_', '', static::class);
    }
    
    /**
     * Получить локализованное имя конфигуратора
     *
     * @return string
     */
    public static function get_name_localized()
    {
        GLOBAL $DOF;
        return $DOF->get_string('configurator_'.static::get_code().'_name', 'transmit', null, 'modlib');
    }
    
    /**
     * Получить локализованное описание конфигуратора
     *
     * @return string
     */
    public static function get_description_localized()
    {
        GLOBAL $DOF;
        return $DOF->get_string('configurator_'.static::get_code().'_description', 'transmit', null, 'modlib');
    }
    
    /**
     * Конструктор конфигуратора
     *
     * @param dof_control $dof - Контроллер Электронного Деканата
     *
     * @return void
     */
    public function __construct($dof)
    {
        $this->dof = $dof;
    }
    
    /**
     * Включить режим симуляции
     *
     * @return void
     */
    public final function simulation_on()
    {
        $this->simulation = true;
        
        // Включение режима симуляции в маске
        if ( ! empty($this->mask) )
        {
            $this->mask->simulation_on();
        }
    }
    
    /**
     * Выключить режим симуляции
     *
     * @return void
     */
    public final function simulation_off()
    {
        $this->simulation = false;
        
        // Выключение режима симуляции в маске
        if ( ! empty($this->mask) )
        {
            $this->mask->simulation_off();
        }
    }
    
    /**
     * Вернуть состояние симуляции
     *
     * @return bool
     */
    public final function get_simulation_status()
    {
        return $this->simulation;
    }
    
    /**
     * Получить доступные источники для текущего конфигуратора
     *
     * @return array - Массив источников данных
     */
    public final function get_available_sources()
    {
        if ( $this->available_sources === null )
        {// Первичная инициализация списка источников
            
            $this->available_sources = [];
            
            // Получение зарегистрированных источников
            $sources = $this->dof->modlib('transmit')->get_registered_sources();
            foreach ( $sources as $sourcecode => $sourceclass )
            {
                // Проверка совместимости
                $method_support = 'support_' . static::get_code();
                if ( method_exists($sourceclass, $method_support) && $sourceclass::$method_support() )
                {// Источник поддерживает текущий конфигуратор
                    $this->available_sources[$sourcecode] = $sourceclass;
                }
            }
        }
        return $this->available_sources;
    }
    
    /**
     * Получить доступные маски для текущего конфигуратора
     *
     * @return array - Массив масок стратегий
     */
    public final function get_available_masks()
    {
        if ( $this->available_masks === null )
        {// Первичная инициализация списка источников
            
            $this->available_masks = [];
            
            // Получение зарегистрированных масок
            $masks = $this->dof->modlib('transmit')->get_registered_masks();
            foreach ( $masks as $maskcode => $maskclass )
            {
                // Проверка совместимости
                $method_support = 'support_' . static::get_code();
                if ( method_exists($maskclass, $method_support) && $maskclass::$method_support() )
                {// Маска поддерживает текущий конфигуратор
                    
                    $this->available_masks[$maskcode] = $maskclass;
                }
            }
        }
        return $this->available_masks;
    }
    
    /**
     * Получить текущий логгер
     *
     * @return dof_storage_logs_queuetype_base|null
     */
    public final function get_logger()
    {
        return $this->logger;
    }
    
    /**
     * Возвращает объект источника
     *
     * @return dof_modlib_transmit_source_base|null
     */
    public final function get_current_source()
    {
        return $this->source;
    }
    
    /**
     * Возвращает код источника
     *
     * @return string
     */
    public final function get_current_sourcecode()
    {
        if ( $this->get_current_source() === null )
        {
            return null;
        }
        return $this->get_current_source()->get_code();
    }
    
    /**
     * Проверка валидности маски
     *
     * @param string $maskcode - Код маски
     *
     * @return bool
     */
    public function is_valid_mask($maskcode)
    {
        // Доступные маски
        $available = $this->get_available_masks();
        
        if ( isset($available[(string)$maskcode]) )
        {
            return true;
        }
        
        return false;
    }
    
    /**
     * Проверка валидности источника
     *
     * @param string $sourcecode - Код источника
     *
     * @return bool
     */
    public function is_valid_source($sourcecode)
    {
        // Доступные маски
        $available = $this->get_available_sources();
        
        if ( isset($available[(string)$sourcecode]) )
        {
            return true;
        }
        
        return false;
    }
    
    /**
     * Возвращает объект маски
     *
     * @return dof_modlib_transmit_strategy_mask_base
     */
    public final function get_current_mask()
    {
        return $this->mask;
    }
    
    /**
     * Возвращает код маски
     *
     * @return string
     */
    public final function get_current_maskcode()
    {
        if ( $this->get_current_mask() === null )
        {
            return null;
        }
        return $this->get_current_mask()->get_code();
    }
    
    /**
     * Получить форму настройки текущего конфигуратора
     *
     * @param string $returnurl - URL возврата
     * @param array $addvars - Массив GET-параметров
     *
     * @return dof_modlib_transmit_configurator_setupform_base - Форма настройки конфигуратора
     */
    public function get_setupform($returnurl = null, $addvars = [])
    {
        // Название класса формы настройки конфигуратора
        $setupform_name = 'dof_modlib_transmit_configurator_'.static::get_code().'_setupform';
        
        // Получение текущих настроек
        $availablemasks = $this->get_available_masks();
        $currentmask = $this->get_current_maskcode();
        if ( $currentmask === null )
        {// Маска не определена
            // Попытка найти маску среди GET-параметров
            if ( isset($addvars['mask']) && $this->is_valid_mask($addvars['mask']) )
            {// Валидная маска найдена среди GET-параметров
                $currentmask = $addvars['mask'];
            }
        }
        $availablesources = $this->get_available_sources();
        $currentsource = $this->get_current_sourcecode();
        if ( $currentsource === null )
        {// Источник не определен
            // Попытка найти источник среди GET-параметров
            if ( isset($addvars['source']) && $this->is_valid_source($addvars['source']) )
            {// Валидный источник найден среди GET-параметров
                $currentsource = $addvars['source'];
            }
        }
        
        // Дополнительные данные формы
        $customdata = new stdClass();
        $customdata->dof = $this->dof;
        $customdata->addvars = $addvars;
        $customdata->masks_available = $availablemasks;
        $customdata->mask = $currentmask;
        $customdata->sources_available = $availablesources;
        $customdata->source = $currentsource;
        $customdata->configurator = &$this;
        
        // Инициализация формы
        return new $setupform_name($returnurl, $customdata);
    }
    
    /**
     * Возвращает форму установки конфигуратора
     *
     * @param string $actionurl - URL формы
     * @param array $addvars - Массив GET-параметров
     *
     * @return dof_modlib_transmit_configurator_configform_base
     */
    public function get_configform($actionurl = null, $addvars = [])
    {
        // Название класса настройки конфигуратора
        $configform_name = 'dof_modlib_transmit_configurator_' . static::get_code() . '_configform';
        
        // Дополнительные данные формы
        $customdata = new stdClass();
        $customdata->dof = $this->dof;
        $customdata->configurator = $this;
        $customdata->addvars = $addvars;
        
        return new $configform_name($actionurl, $customdata);
    }
    
    /**
     * Запуск процесса синхронизации
     *
     * @return void
     */
    public function transmit()
    {
        // Процесс может быть долгим, необходимо увеличить лимиты
        dof_hugeprocess();
        
        // Получение очереди логов
        $this->logger = $this->dof->modlib('logs')->
            create_queue('modlib', 'transmit', static::get_code(), $this->get_packid());

        // Начало транзакции
        $transaction = $this->dof->storage('logs')->begin_transaction();
        
        // Установка логгера в источник
        $this->source->set_logger($this->logger);
        
        // Установка логгера в маску
        $this->mask->set_logger($this->logger);
        
        // Установка менеджера работы с файлами в маску
        $filemanager = new dof_modlib_transmit_source_filemanager(
            $this->dof,
            $this->source
        );
        $this->mask->set_filemanager($filemanager);
        
        // Проверим, что конфигуратор готов к работе
        if ( $this->is_setup() )
        {
            $this->transmit_process();
        }
        
        // Завершение сессии логгера
        $this->dof->modlib('logs')->finish_queue($this->logger->get_id());
        
        if ( $this->simulation )
        {// Включен режим симуляции, откатим транзакцию
            // Удалим лог
            $this->dof->storage('logs')->rollback_transaction($transaction);
        } else
        {// Режим исполнения
            $this->dof->storage('logs')->commit_transaction($transaction);
        }
    }
    
    /**
     * Настроить конфигуратор вручную с указанием маски и источника
     * 
     * @param string $mask - Код маски
     * @param string $source - Код источника
     *
     * @return void
     */
    public function setup_from_code($mask = null, $source = null)
    {
        // Инициализация маски
        $this->setup_mask((string)$mask);
        
        // Инициализация источника данных
        $this->setup_source((string)$source);
    }
    
    /**
     * Настроить конфигуратор автоматически с использованием пакета
     *
     * @param dof_modlib_transmit_pack $pack - Пакет
     *
     * @return void
     */
    public function setup_from_pack(dof_modlib_transmit_pack $pack)
    {
        // установка маски и источника
        $this->setup_from_code($pack->get_mask_code(), $pack->get_source_code());
        
        $mask = $this->get_current_mask();
        $source = $this->get_current_source();
        
        // обозначим источнику, что включен пакетный режим
        // это автоматически заставит источник работать с фреймворком синхронизаций (storage/sync)
        $source->pack_mode_on();
        $source->set_pack($pack);
        
        // установка конфигов источника
        foreach ( $pack->get_source_config() as $code => $val )
        {
            $source->set_configitem($code, $val);
        }
        
        // установка конфигов маски
        foreach ( $pack->get_mask_config() as $code => $val )
        {
            $mask->set_configitem($code, $val);
        }
    }
    
    /**
     * Настроить конфигуратор автоматически с использованием формы настройки
     *
     * @param dof_modlib_transmit_configurator_setupform_base $form - Форма настройки 
     *
     * @return void
     */
    public function setup_from_setupform(dof_modlib_transmit_configurator_setupform_base $form)
    {
        // Установка маски и источника в конфигуратор
        $this->setup_from_code($form->get_maskcode(), $form->get_sourcecode());
    }
    
    /**
     * Проверка конфигуратора на готовность к работе
     * 
     * Конфигуратор может начать обмен данных только в случае полной его настройки
     * 
     * @return bool
     */
    public function is_setup()
    {
        if ( ! empty($this->mask) && 
             ! empty($this->source) && 
             ! empty($this->logger) )
        {// Конфигуратор настроен
            return true;
        }
        return false;
    }
    
    /**
     * Создать пакет на основе текущей настройки конфигуратора
     *
     * @return dof_modlib_transmit_pack|null - Объект пакета
     */
    public function create_pack()
    {
        $pack = $this->get_current_pack();
        $pack->save();
    }
    
    /**
     * Получить идентификатор текущего пакета
     *
     * @return int - Идентификатор пакета
     */
    public function get_packid()
    {
        return 0;
    }
    
    /**
     * Сброс настроек
     *
     * @return void
     */
    public function config_reset()
    {
        $source = $this->get_current_source();
        if ( $source )
        {
            $source->config_reset();
        }
    }
    
    /**
     * Включить цепочку наследования
     *
     * @return void
     */
    public final function chaining_on()
    {
        $mask = $this->get_current_mask();
        if ( $mask )
        {
            $mask->chaining_on();
        }
    }
    
    /**
     * Выключить цепочку наследования
     *
     * @return void
     */
    public final function chaining_off()
    {
        $mask = $this->get_current_mask();
        if ( $mask )
        {
            $mask->chaining_off();
        }
    }
    
    /**
     * Возвращает текущий пакет настроек
     * 
     * @param bool $create - создавать ли пакет в случае отсутствия
     *
     * @return dof_modlib_transmit_pack|null
     */
    public final function get_current_pack($create=false)
    {
        if (is_null($this->pack) && $create)
        {
            $this->pack = new dof_modlib_transmit_pack($this->dof);
        }
        return $this->pack;
    }
}