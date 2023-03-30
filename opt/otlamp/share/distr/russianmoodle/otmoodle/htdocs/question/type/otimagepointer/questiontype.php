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

/**
 * Тип вопроса Объекты на изображении. Класс типа вопроса.
 *
 * @package    qtype
 * @subpackage otimagepointer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/questionlib.php');

/**
 * Класс типа вопроса
 * 
 */
class qtype_otimagepointer extends question_type 
{
    /**
     * Доступные источники изображения
     * 
     * @var array|null
     */
    protected $imagesources = null;
    
    /**
     * Получить список инициализированных источников изображения
     *
     * @return array - Массив источников изображения
     */
    public function imagesources_get_list()
    {
        global $CFG;
    
        // Загрузка списка источников
        if ( $this->imagesources === null )
        {// Требуется инициализация источников основного изображения
            
            // Подготовка списка
            $this->imagesources = [];
    
            // Директория с классами источников
            $imagesourcesdir = $CFG->dirroot.'/question/type/otimagepointer/classes/baseimagesources/';
    
            // Процесс подключения классов источников
            $imagesources = (array)scandir($imagesourcesdir);
            foreach ( $imagesources as $file )
            {
                // Базовая фильтрация
                if ( $file === '.' || $file === '..' )
                {
                    continue;    
                }
                
                // Инициализация класса источника
                $classname = '\\qtype_otimagepointer\\baseimagesources\\'.$file.'\\'.$file;
                if ( class_exists($classname) )
                {// Класс источника основного изображения найден
                    $instance = new $classname();
                    $pluginname = $classname::get_plugin_name();
                    $this->imagesources[$pluginname] = $instance;
                }
            }
        }
        return $this->imagesources;
    }
    
    /**
     * Получить код источника изображения по умолчанию
     *
     * @return string|null - Код источника изображения по умолчанию
     */
    public function imagesources_get_default_pluginname()
    {
        // Инициализация доступного списка источников
        $this->imagesources_get_list();
        // Перевод указателя на первый элемент
        reset($this->imagesources);
        // Вернуть ключ
        return key($this->imagesources);
    }
    
    /**
     * Получить экземпляр источника изображения по его коду
     *
     * @param string $imagesourcecode - Код источника изображения
     *
     * @return null|object - Экземпляр источника изображения или null,
     *                       если источник не найден
     */
    public function imagesources_get_by_pluginname($pluginname)
    {
        // Инициализация доступного списка источников
        $this->imagesources_get_list();
    
        if ( ! empty($this->imagesources[$pluginname]) )
        {// Экземпляр найден
            return $this->imagesources[$pluginname];
        }
        return null;
    }

    /**
     * Поддержка ручного оценивания вопроса
     * 
     * @return bool
     */
    public function is_manual_graded() 
    {
        return true;
    }
    
    /**
     * Используемые файловые зоны в ответе
     * 
     * @return array - Массив файловых зон
     */
    public function response_file_areas() 
    {
        return ['user_baseimage' => 'user_baseimage'];
    }
    
    /**
     * Получить опции экземпляра вопроса
     * 
     * @param $question - Объект экземпляра вопроса
     * 
     * @return void
     */
    public function get_question_options($question)
    {
        global $DB;
        
        // Добавление дополнительных опций в экземпляр вопроса
        $question->options = $DB->get_record(
            'question_otimagepointer_opts',
            ['question' => $question->id],
            '*',
            MUST_EXIST
        );
        parent::get_question_options($question);
    }
    
    /**
     * Сохранить конфигурацию вопроса
     * 
     * @param $formdata - Данные формы конфигурации
     */
    public function save_question_options($formdata)
    {
        global $DB;
        
        $context = $formdata->context;
        
        // Получение дополниетльных настроек вопроса
        $obj = $DB->get_record(
            'question_otimagepointer_opts',
            ['question' => $formdata->id]
        );
        
        // Сохранение дополнительных опций
        if ( empty($obj) )
        {
            $options = new stdClass;
            $options->question = $formdata->id;
            $options->imagesourcetype = $formdata->imagesource;
            $options->imagesourcedata = '';
            $options->id = $DB->insert_record('question_otimagepointer_opts', $options);
        } else
        {
            $options = new stdClass;
            $options->id = $obj->id;
            $options->question = $formdata->id;
            $options->imagesourcetype = $formdata->imagesource;
            $DB->update_record('question_otimagepointer_opts', $options);
        }
        
        // Передача процесса сохранения в источник изображения
        $this->imagesources_get_list();
        $imagesource = $this->imagesources[$formdata->imagesource];
        $saveresult = $imagesource->process_save_question($this, $formdata);
        
        return $saveresult;
    }
    
    /**
     * Процесс инициализации вопроса
     * 
     * @param question_definition $question - Экземпляр вопроса
     * @param stdClass - Объект данных вопроса
     * 
     * @return void
     */
    protected function initialise_question_instance(question_definition $question, $questiondata)
    {
        // Базовый процесс инициализации
        parent::initialise_question_instance($question, $questiondata);
        
        // Дополнительные поля вопроса
        $question->imagesourcetype = $questiondata->options->imagesourcetype;
        $question->imagesourcedata = $questiondata->options->imagesourcedata;
    }
    
    /**
     * Процесс удаления вопроса
     * 
     * @param int $questionid - ID удаляемого вопроса
     * @param int $contextid - ID текущего контекста категории вопроса
     * 
     * @return void
     */
    public function delete_question($questionid, $contextid)
    {
        global $DB;
        
        // Получение списка источников
        $this->imagesources_get_list();
        
        // Запуск процесса удаления вопроса для каждого источника изображения отдельно
        foreach ( $this->imagesources as $imagesource )
        {
            $imagesource->process_delete_question($questionid, $contextid);
        }
        
        // Получение менеджера файлов
        $fs = get_file_storage();
        
        // Удаление всех общих файлов, связанных с попытками прохождения вопроса
        $fs->delete_area_files(
            $contextid,
            'qtype_otimagepointer',
            'user_response',
            $questionid
        );
        
        // Удаление дополнительных опций вопроса
        $DB->delete_records(
            'question_otimagepointer_opts',
            ['question' => $questionid]
        );
        // Базовый механизм удаления
        parent::delete_question($questionid, $contextid);
    }
    
    /**
     * Процесс перемещения файлов
     */
    public function move_files($questionid, $oldcontextid, $newcontextid)
    {
        // Получение списка источников
        $this->imagesources_get_list();
        
        // Запуск процесса перемещения файлов каждого источника изображения отдельно
        foreach ( $this->imagesources as $imagesource )
        {
            $imagesource->process_move_files($questionid, $oldcontextid, $newcontextid);
        }
        
        // Получение менеджера файлов
        $fs = get_file_storage();
        
        // Перенос всех общих файлов, связанных с попытками прохождения вопроса
        $fs->move_area_files_to_new_context(
            $oldcontextid,
            $newcontextid, 
            'qtype_otimagepointer', 
            'user_response', 
            $questionid
        );
        
        // Базовый процесс перемещения
        parent::move_files($questionid, $oldcontextid, $newcontextid);
    }
    
    
}