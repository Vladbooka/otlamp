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
 * Слайдер изображений. Класс базового слайда.
 *
 * @package    block
 * @subpackage otslider
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_otslider\slides;

use MoodleQuickForm;
use stdClass;
use block_otslider;
use block_otslider\exception\slide as exception_slide;

abstract class base
{
    /**
     * Данные слайда из БД
     * 
     * @var stdClass
     */
    protected $record = null;
    
    /**
     * Ссылка на слайдер, в наборе которого содержится текущий слайд
     * 
     * @var block_otslider
     */
    protected $slider = null;
    
    /**
     * Инициализация слайда
     * 
     * @param stdClass $sliderecord - Запись слайда из БД
     * @param slider - Ссылка на слайдер
     */
    public function __construct($sliderecord, $slider)
    {
        $this->record = $sliderecord;
        $this->slider = $slider;
    }
    
    /**
     * Генерация отображения слайда
     * 
     * @return string - HTML-код слайда
     */
    public function render()
    {
        return '';
    }
    
    /**
     * Добавление полей в форму сохранения слайда
     *
     * @param formsave $formsave - Объект формы
     * @param MoodleQuickForm $mform - Объект конструктора формы
     * @param string $prefix - Префикс элементов формы
     *
     * @return void
     */
    public abstract function saveform_definition($formsave, $mform, $prefix);
    
    /**
     * Предварительная обработка полей формы сохранения слайда
     *
     * Организация заполнения полей данными
     *
     * @param formsave $formsave - Объект формы
     * @param MoodleQuickForm $mform - Объект конструктора формы
     * @param string $prefix - Префикс элементов формы
     *
     * @return void
     */
    public abstract function saveform_set_data($formsave, $mform, $prefix);
    
    /**
     * Валидация полей формы сохранения слайда
     *
     * @param array $errors - Массив ошибок валидации
     * @param formsave $saveform - Объект формы
     * @param MoodleQuickForm $mform - Объект конструктора формы
     * @param array $data - Данные формы сохранения
     * @param array $files - Загруженные файлы формы сохранения
     * @param string $prefix - Префикс элементов формы
     *
     * @return void
     */
    public abstract function saveform_validation($errors, $saveform, $mform, $data, $files, $prefix);
    
    /**
     * Процесс сохранения слайда
     *
     * @param formsave $saveform - Объект формы
     * @param MoodleQuickForm $mform - Объект конструктора формы
     * @param stdClass $formdata - Данные формы сохранения
     * @param string $prefix - Префикс элементов формы
     *
     * @return void
     */
    public abstract function saveform_process($saveform, $mform, $formdata, $prefix);
    
    /**
     * Процесс удаления данных слайда
     * 
     * @return void
     *
     * @throws exception_slide - В случае ошибок при удалении данных слайда
     */
    public abstract function process_delete();
    
    /**
     * Получение опции слайда для дальнейшей передачи рендеру
     * 
     * @return mixed
     */
    public abstract function get_slide_options();
}