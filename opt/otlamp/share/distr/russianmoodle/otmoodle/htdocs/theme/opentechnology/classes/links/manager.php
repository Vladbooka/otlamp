<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * Тема СЭО 3KL. Менеджер привязок
 *
 * @package    theme
 * @subpackage opentechnology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_opentechnology\links;

use moodle_page;

class manager
{
    /**
     * Текущий экземпляр менеджера
     *
     * @var manager
     */
    protected static $instance = null;
    
    /**
     * Список привязок
     *
     * @var array
     */
    protected $links = null;
    
    /**
     * Конструктор
     *
     * Для инициализации менеджера необходимо использовать profilemanager::instance();
     */
    protected function __construct()
    {
    }
    
    /**
     * Клонирование менеджера
     *
     * Клонирование не поддерживается данным классом
     */
    protected function __clone()
    {
    }
    
    /**
     * Инициализация менеджера привязок
     *
     * @return manager
     */
    public static function instance()
    {
        if ( self::$instance == null )
        {// Первичная инициализация менеджера
            self::$instance = new manager();
        }
        return self::$instance;
    }
    
    /**
     * Получение всех типов привязок
     *
     * @return array
     */
    public function get_link_types()
    {
        global $CFG;
        
        if ( $this->links === null )
        {// Привязки не определены
            // Первичная инициализация привязок
            $this->links = [];
            
            // Директория с классами привязок
            $linksdir = $CFG->dirroot.'/theme/opentechnology/classes/links/types/';
            
            // Процесс подключения классов привязок
            $linktypes = (array)scandir($linksdir);
            foreach ( $linktypes as $file )
            {
                // Базовая фильтрация
                if ( $file === '.' || $file === '..' )
                {
                    continue;
                }
            
                $file = mb_strimwidth($file, 0, strripos($file, '.'));
                // Инициализация класса
                $classname = '\\theme_opentechnology\\links\\types\\'.$file;
                if ( class_exists($classname) )
                {// Класс найден
                    $instance = new $classname();
                    $code = $instance->get_code();
                    $this->links[$code] = $instance;
                }
            }
        }
            
        return $this->links;
    }
    
    /**
     * Получение привязки
     *
     * @param string $code - Код привязки
     *
     * @return theme_opentechnology\links\base - Класс привязки
     */
    public function get_link($codeorid)
    {
        global $DB;
    
        if ( is_int($codeorid) )
        {// Инициализация по идентификатору
            $record = $DB->get_record('theme_opentechnology_plinks', ['id' => $codeorid]);
            if ( $record )
            {// Привязка найдена
                $code = $record->linktype;
                $baselink = $this->get_link($record->linktype);
                if ( $baselink )
                {// Привязка найдена
    
                    // Инициализация привязки профиля
                    $profilelink = clone $baselink;
                    $profilelink->set_record($record);
                    return $profilelink;
                }
            }
        } else
        {// Указан код привязки
            $codeorid = (string)$codeorid;
    
            $linktypes = $this->get_link_types();
            if ( isset($linktypes[$codeorid]) )
            {// Привязка определена
                return $linktypes[$codeorid];
            }
        }
    
        return null;
    }
    
    /**
     * Получение привязки
     *
     * @param theme_opentechnology\links\base $profile - Профиль
     *
     * @return array
     */
    public function get_profile_links($profile)
    {
        global $DB;
    
        // Получение привязок
        $linkrecords = (array)$DB->get_records('theme_opentechnology_plinks', ['profileid' => $profile->get_id()]);
    
        $links = [];
        foreach ( $linkrecords as $link )
        {
            $baselink = $this->get_link($link->linktype);
            if ( $baselink )
            {
                $profilelink = clone $baselink;
                $profilelink->set_record($link);
                $links[$link->id] = $profilelink;
            }
        }
    
        return $links;
    }
    
    /**
     * Получение привязки по коду типа привязки
     * 
     * @param string $linktypecode - код типа привязки
     * @param object $object - объект для поиска привязки (пользователь, страница)
     * 
     * @return object|boolean - объект привязки или false в случае неудачи
     */
    public function get_link_by_type($linktypecode, $object)
    {
        // Получение типа привязок
        $linktypes = $this->get_link_types();
        
        if (array_key_exists($linktypecode, $linktypes))
        {
            return $linktypes[$linktypecode]->get_link($object);
        } else {
            return false;
        }
    }
    
    /**
     * Определить наиболее подходящую привязку профиля 
     * 
     * @return object|NULL - объект привязки или NULL если не найдена
     */
    public function detect_link()
    {
        global $PAGE, $USER;        
        // Сбор всех привязок к текущей странице
        $links = [];
        
        // в порядке приоритетов обрабатываем разные типы привязок
        foreach ( ['user', 'currenturl', 'contextcourse', 'lang'] as $linktypecode )
        {
            switch($linktypecode)
            {
                case 'currenturl':
                case 'contextcourse':
                    $object = $PAGE;
                    break;
                case 'user':
                    $object = $USER;
                    break;
                case 'lang':
                    $object = current_language();
                    break;
            }
            
            $link = $this->get_link_by_type($linktypecode, $object);
            
            if( ! empty($link) )
            {
                return $link;
            }
        }
        
        return null;
    }
    
    /**
     * Получение привязки профиля для текущей страницы
     *
     * @param moodle_page $page - Текущая страница
     *
     * @return base - Привязка
     */
    public function get_page_link(moodle_page $page)
    {
        // Сбор всех привязок к текущей странице
        $pagelinks = [];
        
        // Получение типа привязок
        $linktypes = $this->get_link_types();
        foreach ( $linktypes as $linktype )
        {
            $pagelink = $linktype->get_page_link($page);
            if( ! empty($pagelink) )
            {
                $pagelinks[] = $pagelink;
            }
        }
        if ( ! empty($pagelinks) )
        {// Определены привязки профилей, нацеленные на целевую страницу
            // @todo - Приоритеты привязок
            return array_pop($pagelinks);
        }
        return null;
    }
    
    /**
     * Удаление привязки
     *
     * @param $link
     */
    public function delete_link($link)
    {
        global $DB;
        
        $id = $link->get_id();
        if ( $id )
        {
            $DB->delete_records('theme_opentechnology_plinks', ['id' => $id]);
        }
        return true;
    }
}