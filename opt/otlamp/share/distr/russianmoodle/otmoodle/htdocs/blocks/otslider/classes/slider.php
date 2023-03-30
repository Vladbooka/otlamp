<?php
///////////////////////////////////////////////////////////////////////////
//                                                                       //
// This file is part of Moodle - http://moodle.org/                      //
// Moodle - Modular Object-Oriented Dynamic Learning Environment         //
//                                                                       //
// Moodle is free software: you can redistribute it and/or modify        //
// it under the terms of the GNU General Public License as published by  //
// the Free Software Foundation, either version 3 of the License, or     //
// (at your option) any later version.                                   //
//                                                                       //
// Moodle is distributed in the hope that it will be useful,             //
// but WITHOUT ANY WARRANTY; without even the implied warranty of        //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         //
// GNU General Public License for more details.                          //
//                                                                       //
// You should have received a copy of the GNU General Public License     //
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.       //
//                                                                       //
///////////////////////////////////////////////////////////////////////////

/**
 * Слайдер изображений. Класс слайдера.
 *
 * @package    block
 * @subpackage otslider
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_otslider;

use block_base;
use stdClass;
use dml_exception;
use block_otslider\slides\base as slidebase;
use block_otslider\exception\slider as exception_slider;
use block_otslider\exception\slider as exception_slide;

class slider
{
    /**
     * Набор слайдов
     * 
     * @var slidebase[]
     */
    private $slides = null;
    
    /**
     * Список типов слайдов
     * 
     * @var array
     */
    private $slidetypes = null;
    
    /**
     * Экземпляр блока
     * 
     * @var block_base
     */
    private $block = null;
    
    /**
     * Инициализация слайдера
     * 
     * @param block_base $block - Экземпляр блока
     */
    public function __construct(block_base $block)
    {
        // Сохранение ссылки на экземпляр блока
        $this->block = $block;
    }
    
    /**
     * Получить экземпляр блока слайдера
     * 
     * @return block_base
     */
    public function get_block()
    {
        return $this->block;
    }
    
    /**
     * Получение всех типов слайдов
     *
     * @return array
     */
    public function get_slide_types()
    {
        global $CFG;
    
        if ( $this->slidetypes === null )
        {// Типы слайдов не определены
            // Первичная инициализация типов слайдов
            $this->slidetypes = [];
    
            // Директория с классами слайдов
            $classesdir = $CFG->dirroot.'/blocks/otslider/classes/slides/types/';
    
            // Процесс подключения классов слайдов
            $slidetypes = (array)scandir($classesdir);
            foreach ( $slidetypes as $file )
            {
                // Базовая фильтрация
                if ( $file === '.' || $file === '..' )
                {
                    continue;
                }
    
                $file = mb_strimwidth($file, 0, strripos($file, '.'));
                // Инициализация класса
                $classname = '\\block_otslider\\slides\\types\\'.$file;
                if ( class_exists($classname) && in_array($file, ['html', 'image', 'listitems']) )
                {// Класс найден
                    $this->slidetypes[$classname::get_code()] = $classname;
                }
            }
        }
    
        return $this->slidetypes;
    }
    
    /**
     * Получить число слайдов
     * 
     * @return int
     */
    public function count_slides()
    {
        return count($this->get_slides());
    }
    
    /**
     * Получение слайдов
     * 
     * @return slidebase[]
     */
    public function get_slides()
    {
        if ( $this->slides === null )
        {// Cлайды не определены
            $this->slides = [];
            // Получить слайды
            $sliderecords = $this->get_slide_records();
            
            foreach ( $sliderecords as $slideid => $slide )
            {
                $slidetypes = $this->get_slide_types();
                // Класс слайда
                $slideclass = $slidetypes[$slide->type];
                // Инициализация слайда
                $this->slides[(int)$slideid] = new $slideclass($slide, $this);
            }
        }
        return $this->slides;
    }
    
    /**
     * Получение слайдов из БД
     *
     * @return stdClass[]
     */
    public function get_slide_records()
    {
        global $DB;
        
        // Получение
        return $DB->get_records(
            'block_otslider_slides',
            ['blockinstanceid' => $this->block->instance->id],
            'ordering ASC, id ASC'
        );
    }
    
    /**
     * Добавить слайд в слайдер
     * 
     * @param string $type - Тип добавляемого слайда
     * 
     * @return int - ID добавленного слайда
     * 
     * @throws exception_slider - В случае ошибок при добавлении слайда
     */
    public function add_slide($type)
    {
        global $DB;
        
        // Проверка валидности типа слайда
        $availabletypes = $this->get_slide_types();
        if ( ! isset($availabletypes[$type]) )
        {// Указанный тип слайда не является валидным типом
            throw new exception_slider('error_slider_slide_type_notvalid', 'block_otslider');
        }
        
        // Добавление слайда в набор слайдера
        $slide = new stdClass();
        $slide->blockinstanceid = $this->block->instance->id;
        $slide->type = $type;
        
        try 
        {
            $slideid = $DB->insert_record('block_otslider_slides', $slide);
            
            // Перестроить сортировку слайдов
            $this->build_ordering();
            
            
            return $slideid;
        } catch ( dml_exception $e )
        {// Ошибка добавления слайда
            throw new exception_slider('error_slider_slide_create_error', 'block_otslider', '', null, $e->getMessage());
        }
    }
    
    /**
     * Удалить слайд из слайдера
     *
     * @param int $slideid - ID удаляемого слайда
     *
     * @return void
     *
     * @throws exception_slider - В случае ошибок при удалении слайда
     */
    public function delete_slide($slideid)
    {
        global $DB;
    
        $slides = $this->get_slides();
        if ( ! isset($slides[(int)$slideid]) )
        {// Указанный слайд не найден в наборе
            throw new exception_slider('error_slider_slide_delete_error_notfound', 'block_otslider');
        }
        
        // Запуск процесса удаления слайда
        $transaction = $DB->start_delegated_transaction();
        
        // Попытка удаления внутренних данных слайда
        try
        {
            $slide = $slides[(int)$slideid];
            $slide->process_delete();
        } catch ( exception_slide $e )
        {// Ошибка удаления слайда
            $transaction->rollback($e);
            throw new exception_slider('error_slider_slide_delete_error_delete', 'block_otslider', '', null, $e->getMessage());
        }
        // Попытка удаления общих данных слайда
        try 
        {
            $DB->delete_records('block_otslider_slides', ['id' => $slideid]);
        } catch ( dml_exception $e )
        {// Ошибка удаления слайда
            $transaction->rollback($e);
            throw new exception_slider('error_slider_slide_delete_error_delete', 'block_otslider', '', null, $e->getMessage());
        }
        $transaction->allow_commit();
        
        // Перестроить сортировку слайдов
        $this->build_ordering();
    }
    
    /**
     * Переместить слайд вверх в наборе слайдера
     *
     * @param int $slideid - ID перемещаемого слайда
     *
     * @return void
     *
     * @throws exception_slider - В случае ошибок при перемещении слайда
     */
    public function orderup_slide($slideid)
    {
        global $DB;
    
        $slides = $this->get_slides();
        if ( ! isset($slides[(int)$slideid]) )
        {// Указанный слайд не найден в наборе
            throw new exception_slider('error_slider_slide_orderup_error_notfound', 'block_otslider');
        }
    
        // Поменять местами сортировку слайдов
        $prevslideid = $this->get_previous_slideid($slideid);
        if ( empty($slideid) || empty($prevslideid) )
        {
            throw new exception_slider('error_slider_slide_orderup_error_swap', 'block_otslider');
        }
        $this->swap_ordering($slideid, $prevslideid);
        
        // Перестроить сортировку слайдов
        $this->build_ordering();
    }

    /**
     * Переместить слайд вниз в наборе слайдера
     *
     * @param int $slideid - ID перемещаемого слайда
     *
     * @return void
     *
     * @throws exception_slider - В случае ошибок при перемещении слайда
     */
    public function orderdown_slide($slideid)
    {
        global $DB;
    
        $slides = $this->get_slides();
        if ( ! isset($slides[(int)$slideid]) )
        {// Указанный слайд не найден в наборе
            throw new exception_slider('error_slider_slide_orderdown_error_notfound', 'block_otslider');
        }
    
        // Поменять местами сортировку слайдов
        $nextslideid = $this->get_next_slideid($slideid);
        
        if ( empty($slideid) || empty($nextslideid) )
        {
            throw new exception_slider('error_slider_slide_orderdown_error_swap', 'block_otslider');
        }
        $this->swap_ordering($slideid, $nextslideid);
        
        // Перестроить сортировку слайдов
        $this->build_ordering();
    }   
    
    /**
     * Перестроить сортировку слайдов
     * 
     * @return void
     */
    public function build_ordering()
    {
        global $DB;
        
        // Получение слайдов
        $slides = $this->get_slide_records();
        
        // Слайды с пустой сортировкой
        $emptyordering = [];
        // Текущий индекс сортировки
        $sortindex = 1;
        
        // Запуск перестройки очередности слайдов
        $transaction = $DB->start_delegated_transaction();
        foreach ( $slides as $slideid => $slide )
        {
            if ( empty($slide->ordering) )
            {// Слайд без сортировки
                $emptyordering[(int)$slideid] = $slide;
            } else 
            {// Слайд с сортировкой
                $update = new stdClass();
                $update->id = $slideid;
                $update->ordering = $sortindex++;
                
                // Обновление индекса сортировки слайда
                try 
                {
                    $DB->update_record('block_otslider_slides', $update);
                } catch ( dml_exception $e )
                {// Ошибка обновления слайда
                    $transaction->rollback($e);
                }
            }
        }
        // Добавление индекса для всех слайдов с пустой сортировкой
        foreach ( $emptyordering as $slideid => $slide )
        {
            $update = new stdClass();
            $update->id = $slideid;
            $update->ordering = $sortindex++;
        
            // Обновление индекса сортировки слайда
            try
            {
                $DB->update_record('block_otslider_slides', $update);
            } catch ( dml_exception $e )
            {// Ошибка обновления слайда
                $transaction->rollback($e);
            }
        }
        $transaction->allow_commit();
    }
    
    /**
     * Поменять местами сортировку слайдов
     *
     * @param int $slideid1 - ID перемещаемого слайда
     * @param int $slideid2 - ID перемещаемого слайда
     *
     * @return void
     */
    protected function swap_ordering($slideid1, $slideid2)
    {
        global $DB;
        
        // Получение индексов сортировки
        $orderindex1 = $DB->get_field('block_otslider_slides', 'ordering', ['id' => $slideid1]);
        $orderindex2 = $DB->get_field('block_otslider_slides', 'ordering', ['id' => $slideid2]);
        
        // Обновление индексов
        $update1 = new stdClass();
        $update1->id = $slideid1;
        $update1->ordering = $orderindex2;
        
        $update2 = new stdClass();
        $update2->id = $slideid2;
        $update2->ordering = $orderindex1;
        
        // Обновление индексов сортировки слайда
        $DB->update_record('block_otslider_slides', $update1);
        $DB->update_record('block_otslider_slides', $update2);
    }
    
    /**
     * Получить идентификатор следующего слайда в наборе
     * 
     * @param int $currentslideid
     * 
     * @return null|int
     */
    protected function get_next_slideid($currentslideid)
    {
        $currentslideid = (int)$currentslideid;
        if ( ! isset($this->slides[$currentslideid]) )
        {
            return null;
        }
        
        // Сброс указателя массива слайдов
        reset($this->slides);
        
        // Установка указателя на целевой слайд
        while ( key($this->slides) !== $currentslideid ) 
        {
            next($this->slides);
        }
        
        $slideid = next($this->slides);
        if ( $slideid ) 
        {
            return key($this->slides);
        }
        return null;
    }
    
    /**
     * Получить идентификатор предыдущего слайда в наборе
     *
     * @param int $currentslideid
     *
     * @return null|int
     */
    protected function get_previous_slideid($currentslideid)
    {
        $currentslideid = (int)$currentslideid;
        if ( ! isset($this->slides[$currentslideid]) )
        {
            return null;
        }
    
        // Сброс указателя массива слайдов
        reset($this->slides);
    
        // Установка указателя на целевой слайд
        while ( key($this->slides) !== $currentslideid )
        {
            next($this->slides);
        }
    
        $slideid = prev($this->slides);
        if ( $slideid )
        {
            return key($this->slides);
        }
        return null;
    }
}