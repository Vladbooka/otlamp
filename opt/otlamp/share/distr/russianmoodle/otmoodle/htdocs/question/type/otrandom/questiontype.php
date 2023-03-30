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
 * Тип вопроса Случайный вопрос с учетом правил. Класс типа вопроса.
 *
 * @package    qtype
 * @subpackage otrandom
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/questionlib.php');
require_once($CFG->dirroot . '/question/type/otrandom/lib.php');

use qtype_otrandom\groups\base as basegroup;

class qtype_otrandom extends question_type 
{
    /**
     * Буферный список доступных вопросов для добавления
     * 
     * @var array 
     */
    private $availablequestions = [];
    
    /**
     * Дополнительный список типов вопросов, не используемых для добавления в тест
     * 
     * Список типов вопросов через запятую
     * 
     * @var string
     */
    protected $excludedqtypes = null;
    
    /**
     * Доступные группы
     *
     * @var array|null
     */
    protected $groups = null;

    /**
     * Видимость для вопросов типа "Случайный"
     * 
     * @return bool
     */
    public function is_usable_by_random() 
    {
        return false;
    }
    
    /**
     * Объявление дополнительных полей вопроса
     *
     * @return array
     */
    public function extra_question_fields() 
    {
        return [
            'question_otrandom_options',
            'targetcategory',
            'includesubcategories',
            'groupweights',
            'grouplevel'
        ];
    }
    
    /**
     * Поле с идентификатором вопроса в дополнительной таблице опций вопроса
     * 
     * @return string
     */
    public function questionid_column_name() 
    {
        return 'question';
    }

    /**
     * Получить список инициализарованных групп выбора случайного вопроса
     *
     * @return basegroup[] - Массив групп
     */
    public function groups_get_list()
    {
        // Загрузка списка групп
        if ( $this->groups === null )
        {// Требуется инициализация групп
    
            // Подготовка списка
            $this->groups = [];
    
            // Директория с классами групп
            $groupsdir = $this->plugin_dir().'/classes/groups/';
    
            // Процесс подключения классов факторов
            $groups = (array)scandir($groupsdir);
            foreach ( $groups as $group )
            {
                // Базовая фильтрация
                if ( $group === '.' || $group === '..' )
                {
                    continue;
                }
                
                // Инициализация класса фактора
                $classname = '\\qtype_otrandom\\groups\\'.$group.'\\'.$group;
                if ( class_exists($classname) )
                {// Класс фактора найден
                    $instance = new $classname();
                    $pluginname = $classname::get_plugin_name();
                    $this->groups[$pluginname] = $instance;
                }
            }
        }
        
        return $this->groups;
    }
    
    /**
     * Получить экземпляр группы по ее коду
     *
     * @param string $groupcode - Код группы
     *
     * @return null|object - Экземпляр группы или null,
     *                       если группа не найдена
     */
    public function groups_get_by_pluginname($pluginname)
    {
        // Инициализация доступного списка групп
        $groups = $this->groups_get_list();
    
        if ( ! empty($groups[$pluginname]) )
        {// Экземпляр найден
            return $groups[$pluginname];
        }
        return null;
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
        // Добавление опций в экземпляр вопроса
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
    
        // Получение групп
        $groups = (array)$this->groups_get_list();
    
        
        // Определение опций
        $options = new stdClass();
        $options->targetcategory = (int)$formdata->targetcategory;
        $options->includesubcategories = (int)$formdata->includesubcategories;
        $options->grouplevel = (int)$formdata->grouplevel;
        $options->groupweights = new stdClass();
        if ( isset($formdata->groupweights) )
        {// Импорт вопроса
            $options->groupweights = $formdata->groupweights;
        } else 
        {// Данные из формы
            foreach ( $groups as $groupcode => $group )
            {
                if ( isset($formdata->{$groupcode.'_weight'}) )
                {
                    $options->groupweights->$groupcode = $formdata->{$groupcode.'_weight'};
                }
            }
            $options->groupweights = serialize($options->groupweights);
        }

        // Получение данных об сохраненных опциях вопроса
        $obj = $DB->get_record(
            'question_otrandom_options',
            ['question' => $formdata->id]
        );
        
        // Сохранение опций
        if ( empty($obj) )
        {
            $options->question = $formdata->id;
            $DB->insert_record('question_otrandom_options', $options);
        } else
        {
            $options->id = $obj->id;
            $DB->update_record('question_otrandom_options', $options);
        }
    
        return $options;
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
    
        // Удаление дополнительных опций вопроса
        $DB->delete_records(
            'question_otrandom_options',
            ['question' => $questionid]
        );
        // Базовый механизм удаления
        parent::delete_question($questionid, $contextid);
    }
    
    /**
     * Получить список доступных вопросов
     *
     * @param int $categoryid - ID категории вопросов
     * @param bool - $subcategories - Включение вопросов из подкатегорий
     * 
     * @return array - Массив вопросов, доступных для добавления
     */
    public function get_available_questions_from_category($categoryid, $subcategories) 
    {
        if ( ! isset($this->availablequestions[$categoryid][$subcategories]) ) 
        {// Буферизация данных
            
            // Инициализация всех вопросов
            $this->init_qtype_lists();
            
            // Получение списка категорий для сбора вопросов
            if ( $subcategories )
            {
                $categoryids = question_categorylist($categoryid);
            } else {
                $categoryids = array($categoryid);
            }
            // Получение списка вопросов
            $questionids = question_bank::get_finder()->get_questions_from_categories(
                $categoryids,
                'qtype NOT IN (' . $this->excludedqtypes . ')'
            );
            // Добавление в буфер
            $this->availablequestions[$categoryid][$subcategories] = $questionids;
        }
        
        // Возврат буферных данных
        return $this->availablequestions[$categoryid][$subcategories];
    }
    
    /**
     * Сохранение данных вопроса
     * 
     * @param stdClass $question - Вопрос для сохранения
     * @param stdClass $form - Данные формы
     * 
     * @return stdClass - Сохраненный вопрос
     */
    public function save_question($question, $form) 
    {
        // Переопределение данных случайного вопроса
        $form->tags = [];
        
        return parent::save_question($question, $form);
    }
    
    /**
     * Построение вопроса в наборе
     * 
     * Ядро вопросов Moodle не поддерживает альтернативные случайные вопросы кроме стандартного
     * Поэтому необходимо инициализировать подмену вопроса вручную через хардкод в ядре тестирования
     * 
     * @return question_definition|null
     */
    public function make_question($questiondata, $choose = false)
    {
        if ( $choose )
        {
            // Подмена вопроса другим
            return $this->choose_other_question($questiondata);
        }
        return parent::make_question($questiondata);
    }
    
    /**
     * Инициализация случайного вопроса
     * 
     * @param object       $questiondata the data defining a random question.
     * 
     * @return question_definition|null - Объявление вопроса или null
     * 
     * @throws coding_exception
     */
    public function choose_other_question($questiondata)
    {
        // Получение доступных вопросов из категории с фильтрацией по исключенным типам
        $availablequestions = $this->get_available_questions_from_category(
            $questiondata->options->targetcategory,
            (bool)$questiondata->options->includesubcategories
        );
        
        // Получение групп
        $groups = $this->groups_get_list();
        
        // Конвертация поля с данными по весам
        $groupweights = unserialize($questiondata->options->groupweights);
        
        // Нормализация весов групп
        $sumweight = array_sum((array)$groupweights);
        foreach ( $groupweights as &$groupweight )
        {
            $groupweight = $groupweight/$sumweight;
        }
        
        // Получение попыток прохождения каждого вопроса
        $questionattempts = $this->get_question_attempts(
            $availablequestions, $questiondata->options->grouplevel);
        
        // Рейтинг вопросов для выбора
        $rating = [];
        
        // Регистрация вопросов в группах
        $groupquestions = [];
        foreach ( $availablequestions as $questionid )
        {
            // Базовый рейтинг вопроса
            $rating[$questionid] = 0;
            
            // Регистрация вопроса в группах
            foreach ( $groups as $code => $group )
            {
                // Проверка на возможность регистрации вопроса в группе
                if ( $group->question_is_valid($questionid, $questionattempts) )
                {// Вопрос валиден для указанной группы
                    
                    // Регистрация вопроса в группе
                    $groupquestions[$code][] = $questionid;
                }
            }
        }
        
        
        // Вычисление рейтинга каждого из вопросов
        foreach ( $groupquestions as $groupcode => $questions )
        {
            // Получение веса группы
            $weight = $groupweights->$groupcode;
            
            // Вычисление рейтинга для каждого вопроса в группе
            $grouprating = $weight * count($availablequestions) / count($questions);
            // Добавление рейтинга группы к итоговому рейтингу вопросов
            foreach ( $questions as $questionid )
            {
                $rating[$questionid] += $grouprating;
            }
        }
        
        // Генерация итогового массива из вопросов на основе рейтинга каждого из вопросов
        $random = [];
        foreach ( $rating as $questionid => $ratingindex )
        {
            // Округление рейтинга до большего целого
            $ratingindex = ceil($ratingindex);
            if ( (int)$ratingindex == 0 )
            {
                $ratingindex = 1;
            }
            
            // Добавление в массив вопроса по числу вхождений
            $randompart = array_fill(0, $ratingindex, $questionid);
            $random = array_merge($random, $randompart);
        }

        if ( ! empty($random) )
        {
            // Перемешивание массива
            shuffle($random);
            
            // Инициализация вопроса
            return question_bank::load_question(current($random), true);
        }
        return null;
    }
    
    /**
     * Инициализация списка исключаемых типов вопросов
     *
     * @return void
     */
    protected function init_qtype_lists()
    {
        if ( ! is_null($this->excludedqtypes) )
        {
            return;
        }
        
        $excludedqtypes = [];
        foreach ( question_bank::get_all_qtypes() as $qtype )
        {
            $quotedname = "'" . $qtype->name() . "'";
            if ( ! $qtype->is_usable_by_random())
            {
                $excludedqtypes[] = $quotedname;
            }
        }
        $this->excludedqtypes = implode(',', $excludedqtypes);
    }
    
    /**
     * Получение попыток прохождения вопросов с учетом видимости
     *
     * @param int[] $questions - Массив идентификаторов вопросов
     * @param int $level - Уровень видимости попыток
     * 
     * @return array - Массив попыток прохождения каждого из вопросов
     */
    protected function get_question_attempts($questions, $level, $userid = null)
    {
        global $DB, $USER, $COURSE;
        
        // Нормализация имени пользователя
        $userid = (int)$userid;
        if ( $userid < 1 )
        {
            $userid = (int)$USER->id;
        }
        
        $attempts = [];
        
        foreach ( (array)$questions as $questionid )
        {
            // Генерация условий
            $wherepart = ' quiza.userid = :userid AND qa.questionid = :questionid ';
            $params = ['userid' => $userid, 'questionid' => $questionid];
            
            if ( $level == 1 )
            {// Дополнительная фильтрация попыток с учетом курса
                // Получение модулей в курсе
                $modules = get_coursemodules_in_course('quiz', $COURSE->id);
                $availablemodules = [];
                foreach ( $modules as $module )
                {
                    $availablemodules[] = $module->instance;
                }
                $sqlinpart = implode(',', $availablemodules);
                $wherepart .= ' AND quiza.quiz IN ('.$sqlinpart.') ';
            }
                
            // Поиск попыток
            $attempts[(int)$questionid] = (array)$DB->get_records_sql('
                    SELECT qa.*
                    FROM {quiz_attempts} quiza
                        RIGHT JOIN {question_attempts} qa
                        ON qa.questionusageid = quiza.uniqueid
                    WHERE '.$wherepart.'
                    ORDER BY quiza.timestart DESC',
                $params
            );
        }
        return $attempts;
    }
}
