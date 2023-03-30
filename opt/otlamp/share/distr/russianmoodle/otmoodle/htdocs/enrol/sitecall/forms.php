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
 * Плагин подписки через форму связи с менеджером,
 * базовый класс форм обратной связи
 *
 * @package enrol
 * @subpackage sitecall
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Массив форм
$forms = array ();
// ДОбавляем имеющиеся формы
include_once (dirname(__FILE__) . '/forms/enrol.php');
$forms['enrol'] = new sitecall_form_enrol();
include_once (dirname(__FILE__) . '/forms/enrollogin.php');
$forms['enrollogin'] = new sitecall_form_enrollogin();

/**
 * Родительский класс для всех форм
 */
class sitecall_form
{
    /**
     * Базовый путь к папке с файлами форм
     */
    protected $basepath = '';
    /**
     * Путь к файлу с формой
     */
    protected $formpath = '';
    /**
     * Путь к файлу с формой
     */
    protected $okpath = '';
    /**
     * Код формы (задается при наследовании)
     */
    protected $code = '';
    
    /**
     * Конструктор класса
     */
    function __construct()
    {
        // Формируем путь к папке с формами
        $this->basepath = dirname(__FILE__) . '/forms/';
        $this->formpath = "{$this->basepath}{$this->code}.form.php";
        $this->okpath = "{$this->basepath}{$this->code}.ok.php";
    }
    
    /**
     * Возвращает html-файл, обработанный для JS
     *
     * @return String - обработаный html
     */
    protected function getHtmlToJS($path)
    {
        ob_start();
        include $path;
        $content = ob_get_clean();
        return '"' . preg_replace('/(\n\s*)/si', '" + "', addslashes($content)) . '"';
    }
    
    /**
     * Фозвращает html-код формы
     * 
     * @param int $courseid идентификатор курса
     * 
     * @return {String} обработаный html
     */
    public function getHtmlForm($courseid)
    {
	global $DB;
	$course=$DB->get_record('course',array("id"=>$courseid));
	$htmlform=$this->getHtmlToJS($this->formpath);
        return str_replace("[coursename]",$course->fullname,$htmlform);
    }
    
    /**
     * Фозвращает html-код страницы ОК
     * 
     * @return {String} обработаный html
     */
    public function getHtmlOk()
    {
        return $this->getHtmlToJS($this->okpath);
    }
    
    /**
     * Проверить данные формы
     * 
     * @return {Array} результат проверки
     */
    public function checkData($form)
    {
        return array (
                'form' => array (
                        'status' => 'ok',
                        'text' => get_string('ok','enrol_sitecall'),
                ),
                'status' => 'ok',
                'text' => get_string('ok','enrol_sitecall')
        );
    }
    
    /**
     * Преобразовать данные для отправки
     * 
     * @return {Array} результат проверки
     */
    public function msgData($form)
    {
        return print_r($form, true);
    }
}
