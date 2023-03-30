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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace local_opentechnology\admin_setting;

use admin_setting_configempty;
use core_component;

/**
 * Локальный плагин Техподдержка СЭО 3KL. Класс административной настройки в виде кнопки с фронтенд-обработчиком.
 * @author moxhatblu
 *
 */
class button extends admin_setting_configempty {
    
    /**
     * Данные для передачи в темплейт отображения кнопки
     * @var array - [
     *     'buttontext' => 'Текст кнопки',
     *     'buttonclasses' => ['button', 'classes']
     * ]
     */
    private $buttondata = [];
    
    /**
     * Данные для передачи в темплейт отображения контента
     * @var array - [
     *     'fields' => [
     *         [
     *             'field_name' => 'название поля (ключ)'
     *             'field_displayname' => 'название поля для отображения пользователям'
     *         ],
     *         [
     *             'field_name' => 'название поля (ключ)'
     *             'field_displayname' => 'название поля для отображения пользователям'
     *         ],
     *         ...
     *     ]
     * ]
     */
    private $contentdata = null;
    
    /**
     * Фронтенд-обработчик ("component/module")
     */
    private $frontendhandler = null;
    
    /**
     * Шаблон отображения кнопки ("component/template")
     */
    private $buttontemplate = null;
    
    /**
     * Шаблон отображения контента модалки ("component/template")
     */
    private $contenttemplate = null;
    
    /**
     * Заголовок модального окна
     */
    private $dialogueheader = null;
    
    /**
     * Опциональные данные для передачи в amd-модуль
     * @var array
     */
    private $initoptions = [];
    
    /**
     * @param string $name
     * @param string $visiblename
     * @param string $description
     * @param mixed $dataoramd  - если массив, то воспринимается как данные для рендера с помощью темплейта, заданного set_content_template
     *                          - если нет, то воспринимается как имя кастомного amd-модуля для отображения вручную
     */
    public function __construct($name, $visiblename, $description, $dataoramd) {
        
        parent::__construct($name, $visiblename, $description, '', PARAM_RAW);
        
        if (is_array($dataoramd))
        {
            $this->contentdata = $dataoramd;
        } else
        {
            $this->frontendhandler = $dataoramd;
        }
        
    }
    
    /**
     * @param string $dialogueheader - заголовок модального окна
     *                          (если не задавать, используется значение по умолчанию)
     */
    public function set_init_options(array $initoptions)
    {
        $this->initoptions = $initoptions;
    }
    
    /**
     * @param string $dialogueheader - заголовок модального окна
     *                          (если не задавать, используется значение по умолчанию)
     */
    public function set_dialogue_header($dialogueheader)
    {
        $this->dialogueheader = $dialogueheader;
    }
    
    /**
     * Установка шаблона для отображения тела модалки
     * (используется только если были переданы данные в массиве при создании экземпляра через параметр $dataoramd)
     *
     * @param string $contenttemplate - шаблон для отображения тела модалки
     *                          (если не задавать, используется значение по умолчанию)
     */
    public function set_content_template($contenttemplate)
    {
        $this->contenttemplate = $contenttemplate;
    }
    
    /**
     *
     * @param array $buttondata - данные для отображения элемента с кнопкой, вызывающей модалку
     *                          (имеются значения по умолчанию, которые можно переопределить задав массив)
     *                          [
     *                              'buttontext' => 'Текст кнопки',
     *                              'buttonclasses' => ['button', 'classes']
     *                          ]
     */
    public function set_button_data($buttondata)
    {
        $this->buttondata = $buttondata;
    }
    
    /**
     * @param string $buttontemplate - шаблон для отображения элемента с кнопкой, вызывающей модалку
     *                          (если не задавать, используется значение по умолчанию)
     */
    public function set_button_template($buttontemplate)
    {
        $this->buttontemplate = $buttontemplate;
    }
    
    /**
     * Returns an XHTML string for the hidden field
     *
     * @param string $data
     * @param string $query
     * @return string XHTML string for the editor
     */
    public function output_html($data, $query='') {
        global $OUTPUT, $PAGE;
        
        // элемент с кнопкой и скрытым полем
        $element = $OUTPUT->render_from_template($this->get_buttontemplate(), $this->get_buttondata($data));
        
        $jsparams = [
            $this->get_id(),
            $this->get_dialogueheader()
        ];
        if (is_null($this->get_contentdata()))
        {
            $func = 'init';
            $jsparams[] = $this->get_init_options();
        } else
        {
            $func = 'initAndRender';
            $jsparams[] = $this->get_contentdata();
            $jsparams[] = $this->get_contenttemplate();
        }
        
        $PAGE->requires->js_call_amd($this->get_jsmodule(), $func, $jsparams);
        
        $default = $this->get_defaultsetting();
        return format_admin_setting($this, $this->visiblename, $element, $this->description, true, '', $default, $query);
    }
    
    public function get_jsmodule()
    {
        global $CFG;
        
        $jsmodule = 'local_opentechnology/admin_setting_button';
        
        if (!is_null($this->frontendhandler)) {
            list($component, $module) = explode('/', $this->frontendhandler, 2);
            $component = clean_param($component, PARAM_COMPONENT);
            $module = clean_param($module, PARAM_ALPHANUMEXT);
            if (!empty($component) && !empty($module)) {
                $componentdir = core_component::get_component_directory($component);
                $amdpath = '/amd/' . ($CFG->cachejs === false ? 'src/' . $module . '.js' : 'build/' . $module . '.min.js');
                if (file_exists($componentdir . $amdpath)) {
                    $jsmodule = $this->frontendhandler;
                } else {
                    debugging(get_string('frontend_handler_not_found', 'local_opentechnology', $componentdir . $amdpath));
                }
            } else {
                debugging(get_string('frontend_handler_not_found', 'local_opentechnology', $componentdir . $amdpath));
            }
        }
        
        return $jsmodule;
    }
    
    /**
     * Получить Данные для передачи в темплейт отображения кнопки
     * @return array
     */
    public function get_buttondata($data) {
        
        // данные по умолчанию для отображения элемента формы
        $buttoncontext = [
            'id' => $this->get_id(),
            'name' => $this->get_full_name(),
            'buttontext' => get_string('admin_setting_button_text', 'local_opentechnology'),
            'buttonclasses' => ['btn'],
            'value' => $data
        ];
        
        if (!is_null($this->buttondata))
        {
            $buttoncontext = array_merge($buttoncontext, $this->buttondata);
        }
        
        return $buttoncontext;
    }
    
    /**
     * Получить Данные для передачи в темплейт отображения контента
     * @return array
     */
    public function get_contentdata() {
        return $this->contentdata;
    }
    
    /**
     * Получить Шаблон отображения кнопки ("component/template")
     * @return string
     */
    public function get_buttontemplate() {
        return $this->buttontemplate ?? 'local_opentechnology/setting_configbutton';
    }
    
    /**
     * Получить Шаблон отображения контента модалки ("component/template")
     * @return string
     */
    public function get_contenttemplate() {
        return $this->contenttemplate ?? 'local_opentechnology/setting_configbutton_dialoguebody';
    }
    
    /**
     * Получить заголовок модального окна
     */
    public function get_dialogueheader() {
        return $this->dialogueheader ?? get_string('admin_setting_dialogue_header', 'local_opentechnology');
    }
    
    public function get_init_options() {
        return $this->initoptions;
    }
}