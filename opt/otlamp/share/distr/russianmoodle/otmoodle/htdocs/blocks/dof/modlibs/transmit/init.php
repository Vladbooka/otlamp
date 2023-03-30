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
 * Менеджер обмена данными Деканата
 * 
 * Позволяет собирать готовые интерфейсы для имопрта\экспорта данных 
 * на основе собственных стратегий обмена.
 *
 * @package    modlib
 * @subpackage transmit
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dof_modlib_transmit extends dof_modlib_base
{
    /**
     * Кэш доступных источников
     *
     * @var array|null
     */
    protected $registered_sources = null;
    
    /**
     * Кэш доступных масок
     *
     * @var array|null
     */
    protected $registered_masks = null;
    
    /**
     * Кэш доступных обработчиков
     *
     * @var array|null
     */
    protected $registered_processors = null;
    
    /**
     * Возвращает версию установленного плагина
     * 
     * @return int - Версия плагина
     */
    public function version()
    {
		return 2017112300;
    }
    
    /**
     * Требуется ли запуск cron в плагине
     *
     * @return bool
     */
    public function is_cron()
    {
        return true;
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
    public function cron($loan, $messages)
    {
        $result = true;
        if ( $loan == 2 )
        {
            // Обслуживание менеджера обмениваемых файлов
            dof_modlib_transmit_source_filemanager::cron();
            
            // Сервисная задача
            $sources = $this->get_registered_sources();
            foreach ( $sources as $source )
            {
                $source::cron();
            }
        }
        return $result;
    }
    
    /**
     * Получить доступные источники для текущего конфигуратора
     *
     * @return array - Массив источников данных
     */
    public function get_registered_sources()
    {
        if ( $this->registered_sources === null )
        {// Первичная инициализация списка источников
            
            $this->registered_sources = [];
            
            // Установка пути к источникам данных
            $basedir = $this->dof->plugin_path('modlib', 'transmit', '/classes/source/');
            if ( is_dir($basedir) )
            {
                // Поиск источников
                foreach ( (array)scandir($basedir) as $sourcearchetype )
                {
                    if ( $sourcearchetype == '.' || $sourcearchetype == '..' )
                    {// Пропускаем лишние файлы
                        continue;
                    }
                    
                    if ( is_dir($basedir.$sourcearchetype) )
                    {// Получена директория архетипа источников
                        
                        foreach ( (array)scandir($basedir.$sourcearchetype) as $sourcetype )
                        {
                            if ( $sourcetype == '.' || $sourcetype == '..' )
                            {// Пропускаем лишние файлы
                                continue;
                            }
                            
                            $sourcepath = $basedir.$sourcearchetype.'/'.$sourcetype;
                            if ( is_dir($sourcepath) )
                            {// Найден источник
                                
                                $sourcepath = $sourcepath.'/init.php';
                                if ( file_exists($sourcepath) )
                                {
                                    // Регистрация источника
                                    $classname = 'dof_modlib_transmit_source_'.$sourcearchetype.'_'.$sourcetype;
                                    
                                    if ( class_exists($classname) )
                                    {// Источник существует и поддерживает текущий конфигуратор
                                        $this->registered_sources[$classname::get_code()] = $classname;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return $this->registered_sources;
    }
    
    /**
     * Получить зарегистрированные маски
     *
     * @return array - Массив масок стратегий
     */
    public function get_registered_masks()
    {
        if ( $this->registered_masks === null )
        {// Первичная инициализация списка масок
            $this->available_masks = [];
            
            // Установка пути к стратегиям
            $basedir = $this->dof->plugin_path('modlib', 'transmit', '/classes/strategy');
            
            if ( is_dir($basedir) )
            {
                // Поиск стратегий обмена данных
                foreach ( (array)scandir($basedir) as $strategydir )
                {
                    if ( $strategydir == '.' || $strategydir == '..' )
                    {// Пропускаем лишние файлы
                        continue;
                    }
                    
                    // Путь к папкам с источниками
                    $maskdir = $basedir.'/'.$strategydir.'/mask';
                    
                    if ( is_dir($maskdir) )
                    {// Найдена директория стратегии
                        
                        foreach ( (array)scandir($maskdir) as $mask )
                        {
                            if ( $mask == '.' || $mask == '..' )
                            {// Пропускаем лишние файлы
                                continue;
                            }
                            
                            if ( is_dir($maskdir.'/'.$mask) )
                            {// Найдена маска текущей стратегии
                                
                                $maskpath = $maskdir.'/'.$mask.'/init.php';
                                
                                if ( file_exists($maskpath) )
                                {
                                    // Регистрация маски
                                    $classname = 'dof_modlib_transmit_strategy_'.$strategydir.'_mask_'.$mask;
                                    
                                    if ( class_exists($classname) )
                                    {// Источник существует и поддерживает текущий конфигуратор
                                        $this->registered_masks[$classname::get_fullcode()] = $classname;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return $this->registered_masks;
    }
    
    /**
     * Получить зарегистрированные обработчики
     *
     * @return array - Массив классов обработчиков
     */
    public function get_registered_processors()
    {
        if ( $this->registered_processors === null )
        {// Первичная инициализация списка обработчиков
            $this->registered_processors = [];
            
            // Установка пути к стратегиям
            $basedir = $this->dof->plugin_path('modlib', 'transmit', '/classes/processor');
            if ( is_dir($basedir) )
            {
                $iterator = new RecursiveDirectoryIterator($basedir);
                foreach( new RecursiveIteratorIterator($iterator) as $file ) 
                {
                    if ( $file->isFile() ) 
                    {
                        $classpart = str_replace($basedir, '', $file->getPath());
                        $classpart = str_replace('/', '_', $classpart);
                        
                        $classname = 'dof_modlib_transmit_processor'.$classpart;
                        if ( class_exists($classname) )
                        {
                            $this->registered_processors[$classname::get_fullcode()] = $classname;
                        }
                    }
                }
            }
        }
        return $this->registered_processors;
    }
    
    /**
     * Инициализация конфигуратора импорта
     *
     * Получить конфигуратор для настройки процесса импорта данных в Деканат
     * из различных источников.
     *
     * @return dof_modlib_transmit_configurator_import - Объект конфигуратора
     */
    public function get_import_configurator()
    {
        return new dof_modlib_transmit_configurator_import($this->dof);
    }
    
    /**
     * Инициализация конфигуратора экспорта
     *
     * Получение конфигуратора для ручного проведения экспорта
     *
     * @return dof_modlib_transmit_configurator_export - Объект конфигуратора
     */
    public function get_export_configurator()
    {
        return new dof_modlib_transmit_configurator_export($this->dof);
    }
    
    /**
     * Инициализация обмена с помощью пакета настроек
     *
     * Пакет настроек хранит в себе настройки конфигуратора для
     * автоматического запуска обмена данными(импорт\экспорт)
     *
     * @param dof_modlib_transmit_pack $pack - пакет
     *
     * @return void
     */
    public function transmit_from_pack(dof_modlib_transmit_pack $pack)
    {
        $methodname = 'get_' . $pack->get_transmit_type() . '_configurator';
        /**
         * @var dof_modlib_transmit_configurator_base $configurator
         */
        $configurator = $this->{$methodname}();
        
        // установка конфигарутора из пака
        $configurator->setup_from_pack($pack);
        
        // запуск обмена
        $configurator->transmit();
    }
    
    /**
     * Получение объекта пакета
     * 
     * @param stdClass $record
     * 
     * @return dof_modlib_transmit_pack|boolean
     */
    public function get_pack($record)
    {
        try {
            return new dof_modlib_transmit_pack($this->dof, $record);
        } catch(dof_exception_coding $ex)
        {
            return false;
        }
    }
}
