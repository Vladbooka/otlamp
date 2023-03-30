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
 * Блок комментарий преподавателя. Класс блока.
 *
 * @package    block_quiz_teacher_feedback
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// Подключение дополнительных библиотек
require_once($CFG->dirroot . '/blocks/quiz_teacher_feedback/locallib.php');
require_once($CFG->dirroot . '/mod/quiz/locallib.php');
require_once($CFG->libdir.'/grouplib.php');

use block_quiz_teacher_feedback\feedback_form;
use block_quiz_teacher_feedback\feedback_links;

class block_quiz_teacher_feedback extends block_base
{
    /**
     * Сразу отправлять
     * @var int
     */
    const REQUEST_MODE_IMMIDIATELY = 0;
    
    /**
     * Спрашивать (по умолчанию отправлять)
     * @var integer
     */
    const REQUEST_MODE_CHOOSE_DEFAULT_YES = 1;
    
    /**
     * Спрашивать (по умолчанию не отправлять)
     * @var integer
     */
    const REQUEST_MODE_CHOOSE_DEFAULT_NO = 2;
    
    /**
     * Нет ответа от слушателя
     * @var integer
     */
    const RESPONSE_STATUS_EMPTY_ANSWER = 0;
    
    /**
     * Необходимо подтвердить ответ
     * @var integer
     */
    const RESPONSE_STATUS_SHOULD_BE_CONFIRMED = 1;
    
    /**
     * Необходимо подтвердить ответ
     * @var integer
     */
    const RESPONSE_STATUS_SHOULD_BE_RECONFIRMED = 2;
    
    /**
     * Подтвержден
     * @var integer
     */
    const RESPONSE_STATUS_CONFIRMED = 3;
    
    /**
     * Инициализация блока
     *
     * @return void
     */
    public function init()
    {
        $this->blockname = get_class($this);
        // Имя блока
        $this->title = get_string('title', 'block_quiz_teacher_feedback');
    }

    /**
     * Подключение JS-поддержки блока
     *
     * @return void
     */
    public function get_required_javascript()
    {
        parent::get_required_javascript();
        static $jsincluded = false;
        if ( !$jsincluded )
        {
            $jsincluded = true;
            // Подключение JS поддержки блока
            $this->page->requires->js('/blocks/quiz_teacher_feedback/script.js');
        }
    }
    
    /**
     * Поддержка нескольких экземпляров блока на странице
     *
     * @return bool
     */
    public function instance_allow_multiple()
    {
        return false;
    }
    
    /**
     * Поддержка скрытия блока
     *
     * @return bool
     */
    public function instance_can_be_hidden()
    {
        // Блок запрещено скрывать
        return false;
    }
    
    /**
     * Получить HTML-атрибуты блока
     *
     * @return array - Массив атрибутов блока
     */
    public function html_attributes()
    {
        $attributes = parent::html_attributes();
        $attributes['data-contextid'] = $this->context->id;
        return $attributes;
    }
    
    /**
     * Сформировать контент блока
     *
     * @return stdClass - Контент блока
     */
    public function get_content()
    {
        global $PAGE, $DB;
        
        if ( $this->content !== NULL )
        {
            return $this->content;
        }
        
        // Получить рендер блока
        /**
         * @var block_quiz_teacher_feedback_renderer
         */
        $renderer = $PAGE->get_renderer('block_quiz_teacher_feedback');
        
        // Получение данных о текущей странице
        $attemptid = optional_param('attempt', 0, PARAM_INT);
        $page = optional_param('page', 0, PARAM_INT);
        $id = optional_param('id', 0, PARAM_INT);
        $cmid = optional_param('cmid', 0, PARAM_INT);
        $modal = optional_param('modal', 0, PARAM_INT);
        $showall = optional_param('showall', 1, PARAM_INT);
        
        // Установим ID quiz-а
        if ( ! empty($cmid) )
        {
            $id = $cmid;
        }
        
        // Объявление контента блока
        $this->content = new stdClass();
        $this->content->text = '';
        $this->content->footer = '';
        
        // Дефолтные значение переменных
        $showajax = true;
        
        // Добавляем сворачиваемый раздел с незавершенными попытками теста
        if ( ($PAGE->pagetype == 'mod-quiz-view' || $PAGE->pagetype == 'mod-quiz-report' || $PAGE->pagetype == 'mod-quiz-edit') &&
                ! empty($id) &&
                ($cm = get_coursemodule_from_id('quiz', $id)) &&
                ($DB->record_exists('course', ['id' => $cm->course])) &&
                has_capability('mod/quiz:viewreports', context_module::instance($cm->id)) )
        {// Есть ID модуля/курса и есть права
            // Данные для формы
            $customdata = new stdClass();
            $customdata->students_data = $this->get_questions_data($cm, null, $renderer);
            $customdata->courseid = $cm->course;
            $customdata->quizid = $id;
            
            // Объявление формы с попытками студентов
            $form = new feedback_links(null, $customdata);
            
            // Рендеринг формы
            $this->content->text .= $form->render();
        }
        
        if ( $attemptid && $attemptobj = quiz_attempt::create($attemptid) )
        {// Указана попытка прохождения теста
            if ( $PAGE->pagetype == 'mod-quiz-attempt' || $PAGE->pagetype == 'mod-quiz-review' )
            {// Валидные типы страниц
                // Просинхронизируем конфиг блока
                $this->check_quiz_questions($attemptobj);
                if ( ! $attemptobj->is_finished() )
                {// Попытка еще не завершена
                    // Доступ к редактированию оценки
                    $cangrade = $attemptobj->has_capability('mod/quiz:grade');
                    // Нормализация номера страницы
                    if ( empty($page) && $showall && $cangrade )
                    {// Режим отображения - все вопросы на странице
                        $pagenumber = 'all';
                    }
                    else
                    {// Режим отображения - страница
                        $pagenumber = $attemptobj->force_page_number_into_range($page);
                    }
                    // Получение слотов с вопросами на странице
                    $slots = $attemptobj->get_slots($pagenumber);
                    // Проверка на то, что все вопросы подтверждены
                    $showajax = $this->teacher_confirmed($attemptobj);
                    // Отображение блока по каждому из вопросов
                    foreach ( $slots as $slot )
                    {
                        // Получение текущей попытки прохождения вопроса
                        $qa = $attemptobj->get_question_attempt($slot);
                        // Флаг о том, что вопрос оцениваемый
                        if ( $attemptobj->is_real_question($slot) )
                        {
                            // Содержимое блок вопроса
                            if ( $cangrade )
                            {// Добавление формы комментирования для преподавателей
                                if ( $this->is_slot_should_be_graded($qa) )
                                {
                                    // Заголовок вопроса
                                    $this->content->text .= $this->get_question_header($attemptobj, $slot, $cangrade);
                                    
                                    $feedbackform = $this->get_comment_form($qa, $PAGE->url);
                                    $feedbackform->process();
                                    
                                    $this->content->text .= $feedbackform->render();
                                }
                            } else
                            {// Добавление информации о вопросе для студента
                                // Обработчик текущей страницы
                                $this->page_control($attemptobj);
                                // Заголовок вопроса
                                $this->content->text .= $this->get_question_header($attemptobj, $slot, true);
                                // Отображение блока с комментарием
                                $this->content->text .= $renderer->feedback($qa, $this);
                                
                                if ( $this->is_slot_should_be_graded($qa) )
                                {
                                    if ( property_exists($this->config, 'request_mode') && $this->config->request_mode != self::REQUEST_MODE_IMMIDIATELY )
                                    {
                                        $qaid = $qa->get_database_id();
                                        $qubaid = $qa->get_usage_id();
                                        $slot = $qa->get_slot();
                                        $token = md5(sesskey().$qaid.$qubaid.$slot);
                                        
                                        $status = $this->get_request_status($qa);
                                        $feedback = block_quiz_teacher_feedback_get_feedback($qa->get_database_id());
                                        
                                        $string = get_string('send_request', 'block_quiz_teacher_feedback');
                                        if ( ! empty($feedback) && strlen($feedback->grade) )
                                        {
                                            $string = get_string('send_rerequest', 'block_quiz_teacher_feedback');
                                        }
                                        // отображение переключателя отправки ответа на подтверждение
                                        $switchstatushtml = html_writer::label(
                                                $string,
                                                'block_quiz_teacher_feedback__switch_request_status_' . $qa->get_database_id(),
                                                true,
                                                ['class' => 'block_quiz_teacher_feedback__switch_request_status_label']);
                                        $switchstatushtml .= html_writer::checkbox(
                                                'block_quiz_teacher_feedback__switch_request_status',
                                                $status,
                                                $status,
                                                '',
                                                [
                                                    'id' => 'block_quiz_teacher_feedback__switch_request_status_' . $qa->get_database_id(),
                                                    'class' => 'block_quiz_teacher_feedback__switch_request_status',
                                                    'data-qubaid' => $qubaid,
                                                    'data-slot' => $slot,
                                                    'data-token' => $token,
                                                    'data-instance' => $this->instance->id
                                                ]);
                                        $replacecheckbox = ! empty($this->config->replace_checkbox) ? 'block_quiz_teacher_feedback__switch_request_status_wrapper_replace_checkbox' : '';
                                        $this->content->text .= html_writer::div($switchstatushtml, 'block_quiz_teacher_feedback__switch_request_status_wrapper ' . $replacecheckbox);
                                    }
                                }
                            }
                        }
                    }
                }
            } elseif ( $PAGE->pagetype == 'mod-quiz-summary' )
            {// Страница итога тестирования
                    
                if ( ! $attemptobj->is_finished()
                        && ! empty($this->config->user_attempt_control)
                        && ! $attemptobj->has_capability('mod/quiz:grade') )
                {// Попытка еще не завершена, контроль прохождения теста включен и старница просматривается студентом
                    $currentpage = $this->get_current_page($attemptobj);
                    if ( is_int($currentpage) )
                    {// Редирект на страницу с вопросом, который еще не подтвержден преподавателем
                        $this->attempt_redirect($attemptobj, $currentpage);
                    }
                }
            }
        }
        
        // Сформируем модальное окно
        $this->modal_message($modal, $showajax);
        
        return $this->content;
    }
    
    /**
     * Получение формы редактирования комментария преподавателя
     * @param question_attempt $qa
     * @param moodle_url $url
     * @return feedback_form
     */
    public function get_comment_form(question_attempt $qa, moodle_url $url = null) : feedback_form
    {
        return new feedback_form($url, (object)['qa' => $qa, 'block' => $this]);
    }
    
    /**
     * Получить статус чекбокса отправки ответа на проверку
     * @param question_attempt $qa
     * @return bool
     */
    public function get_request_status(question_attempt $qa) : bool
    {
        if ( ! property_exists($this->config, 'request_mode') )
        {
            $this->config->request_mode = 0;
        }
        if ( $this->config->request_mode == self::REQUEST_MODE_IMMIDIATELY )
        {
            return true;
        }
        $feedback = block_quiz_teacher_feedback_get_feedback($qa->get_database_id());
        if ( ! empty($feedback) )
        {
            return (bool)$feedback->needsgrading;
        }
        if ( $this->config->request_mode == self::REQUEST_MODE_CHOOSE_DEFAULT_YES )
        {
            return true;
        }
        if ( $this->config->request_mode == self::REQUEST_MODE_CHOOSE_DEFAULT_NO )
        {
            return false;
        }
        
        return true;
    }
    
    /**
     * Получить статус "необходимо ли преподавателю оценить ответ на вопрос"
     * @param question_attempt $qa
     * @return bool
     */
    public function is_slot_should_be_graded(question_attempt $qa) : bool
    {
        return $qa->get_question()->qtype->is_manual_graded() ||
        (! empty($this->config->user_attempt_control) && ! empty($this->config->{"question_control_id_{$qa->get_slot()}"}));
    }
    
    /**
     * Получить статус "включен ли режим контроля над вопросом"
     * @param question_attempt $qa
     * @return bool
     */
    public function is_slot_under_controlled(question_attempt $qa) : bool
    {
        return (! empty($this->config->user_attempt_control) && ! empty($this->config->{"question_control_id_{$qa->get_slot()}"}));
    }
    
    /**
     * Получить статус ответа на вопрос
     * @param question_attempt $qa
     * @return int
     */
    public function get_slot_status(question_attempt $qa) : int
    {
        $data = $qa->get_last_qt_data();
        if ( empty($data) ||
                empty($this->config) )
        {
            return self::RESPONSE_STATUS_EMPTY_ANSWER;
        }
        if ( ! property_exists($this->config, 'request_mode') )
        {
            $this->config->request_mode = 0;
        }
        
        $feedback = block_quiz_teacher_feedback_get_feedback($qa->get_database_id());
        $shouldbeconfirmedstatus = ! empty($feedback) && !strlen($feedback->grade) ? self::RESPONSE_STATUS_SHOULD_BE_CONFIRMED : self::RESPONSE_STATUS_SHOULD_BE_RECONFIRMED;
        if ( ! empty($feedback) )
        {
            if ( ! empty($feedback->needsgrading) )
            {
                return $shouldbeconfirmedstatus;
            }
            if ( $qa->get_question()->qtype->is_manual_graded() )
            {
                if ( empty($feedback->needsgrading) )
                {
                    return self::RESPONSE_STATUS_EMPTY_ANSWER;
                }
                if ( (($this->is_slot_under_controlled($qa) &&  ! empty($feedback->completed)) || !$this->is_slot_under_controlled($qa)) &&
                       strlen($feedback->grade > 0) )
                {
                    return self::RESPONSE_STATUS_CONFIRMED;
                } else
                {
                    return $shouldbeconfirmedstatus;
                }
            }
            if ( $this->is_slot_under_controlled($qa) )
            {
                if ( ! empty($feedback->completed) )
                {
                    return self::RESPONSE_STATUS_CONFIRMED;
                } else 
                {
                    return $shouldbeconfirmedstatus;
                }
            }
            return self::RESPONSE_STATUS_SHOULD_BE_CONFIRMED;
        } else 
        {
            if ( ($this->config->request_mode == self::REQUEST_MODE_IMMIDIATELY) ||
                    ($this->config->request_mode == self::REQUEST_MODE_CHOOSE_DEFAULT_YES) )
            {
                return self::RESPONSE_STATUS_SHOULD_BE_CONFIRMED;
            }
            return self::RESPONSE_STATUS_EMPTY_ANSWER;
        }
    }
    
    /**
     * Состояние завершения текущей страницы теста
     *
     * @param quiz_attempt $attemptobj - Попытка прохождения теста
     * @param int $pagenum - Номер страницы
     *
     * @return boolean
     */
    private function is_page_complete(quiz_attempt $attemptobj, $pagenum)
    {
        // Нормализуем данные
        if ( empty($attemptobj) && ! is_object($attemptobj) )
        {
            return true;
        }
        if ( ! is_numeric($pagenum) )
        {
            return true;
        }
        for ( $page = 0; $page < $pagenum; $page++ )
        {
            // Получение слотов с вопросами на странице
            $slots = (array)$attemptobj->get_slots($page);
            foreach ($slots as $slot)
            {
                if ( $attemptobj->is_real_question($slot) )
                {
                    // Получение текущей попытки прохождения вопроса
                    $qa = $attemptobj->get_question_attempt($slot);
                    $feedback = block_quiz_teacher_feedback_get_feedback($qa->get_database_id());
                    if ( empty($feedback->completed) &&
                            $this->is_slot_under_controlled($qa) )
                    {// Вопрос не завершен
                        return false;
                    }
                }
            }
        }
        // Все вопросы завершены
        return true;
    }
    
    /**
     * Поддержка блоком страницы конфигурации
     *
     * @return boolean
     */
    public function has_config()
    {
        // Страница настроек блока доступна
        return true;
    }

    /**
     * Отображение блока на страницах
     *
     * @return array - Перечень типов страниц, но которых возможно добавление блока
     */
    public function applicable_formats()
    {
        return [
            'all' => false,
            'mod-quiz' => true
        ];
    }
    
    /**
     * Массив с вопросами теста из конфига
     *
     * @return array - массив с вопросами
     */
    public function get_formated_config_questions()
    {
        $questions = [];
        
        if ( ! empty($this->config) )
        {
            foreach ((array)$this->config as $key => $config_object)
            {
                if ( strpos($key, 'question_control_id_') === 0 )
                {// Если это конфиг контроля вопросов
                    $id = substr($key, 20);
                    if ( ! empty($id) )
                    {
                        $questions[$id] = $config_object;
                    }
                }
            }
        }
        return (array)$questions;
    }
    
    /**
     * Обработчик текущей страницы
     *
     * @param quiz_attempt $attemptobj
     *
     * @return void|boolean
     */
    private function page_control(quiz_attempt $attemptobj)
    {
        // Нормализуем данные
        if ( empty($attemptobj) && ! is_object($attemptobj) )
        {
            return false;
        }
        if ( ! empty($this->config->user_attempt_control) )
        {// Контроль прохождения теста включен
            // Получение текущей страницы теста
            $currentpage = $attemptobj->get_currentpage();
            
            if ( ! $this->is_page_complete($attemptobj, $currentpage) )
            {// Вопросы на текущей странице не завершены
                
                // Получение текущей страницы под контролем блока
                $currentcontrolpage = $this->get_current_page($attemptobj);

                if ( $currentcontrolpage !== null && $currentcontrolpage != $currentpage )
                {
                    $this->attempt_redirect($attemptobj, $currentcontrolpage);
                }
            }
        }
    }
    
    /**
     * Получение первого незавершенного вопроса, находящегося под контролем доступа
     *
     * @param quiz_attempt $attemptobj
     *
     * @return int|null - Номер страницы под контролем или null, если такой страницы нет
     */
    private function get_current_page(quiz_attempt $attemptobj)
    {
        // Получение количества страниц теста
        $pages = $attemptobj->get_num_pages();
        // Поиск страницы. на которой есть незавершенные попытки прохождения вопроса
        for ( $page = 0; $page < $pages; $page++ )
        {
            // Получение слотов с вопросами на странице
            $slots = (array)$attemptobj->get_slots($page);
        
            // Поиск текущей страницы для пользователя
            foreach ($slots as $slot)
            {
                if ( $attemptobj->is_real_question($slot) )
                {
                    // Получение текущей попытки прохождения вопроса
                    $qa = $attemptobj->get_question_attempt($slot);
                    $feedback = block_quiz_teacher_feedback_get_feedback($qa->get_database_id());
                    if ( empty($feedback->completed) &&
                            $this->is_slot_under_controlled($qa) )
                    {// Вопрос не завершен
                        return $page;
                    }
                }
            }
        }
        
        // Все вопросы завершены
        return null;
    }
    
    /**
     * Получение страниц попытки, где необходимо преподавателю оценить вопросы
     *
     * @param quiz_attempt $attemptobj
     *
     * @return array|null - Массив номеров страниц с неподтвержденными вопросами
     *
     */
    private function get_current_pages(quiz_attempt $attemptobj, $renderer = null)
    {
        // Нормализация
        if ( empty($renderer) )
        {
            return [];
        }
        // Страницы с вопросами на оценку
        $questions_to_grade = [];
        // Получение количества страниц теста
        $pages = $attemptobj->get_num_pages();
        // Поиск страницы. на которой есть незавершенные попытки прохождения вопроса
        for ( $page = 0; $page < $pages; $page++ )
        {
            // Получение слотов с вопросами на странице
            $slots = (array)$attemptobj->get_slots($page);
            
            // Поиск текущей страницы для пользователя
            foreach ($slots as $slot)
            {
                if ( $attemptobj->is_real_question($slot) )
                {
                    // Получение текущей попытки прохождения вопроса
                    $qa = $attemptobj->get_question_attempt($slot);
                    
                    if ( $this->is_slot_should_be_graded($qa) )
                    {// отображение вручную оцениваемые вопросы
                        $data = $qa->get_last_qt_data();
                        $info_page = new stdClass();
                        $info_page->qa = $qa;
                        $info_page->page_id = $page;
                        $info_page->status = $this->get_slot_status($qa);
                        $info_page->answered = ! empty($data);
                        $info_page->class = $this->get_question_status_class($info_page);
                        $info_page->hint = $this->get_question_status_hint($info_page);
                        $info_page->name_of_slot = $this->get_question_header($attemptobj, $slot, true);
                        $questions_to_grade[$page] = $info_page;
                    }
                }
            }
        }
        
        // Все вопросы завершены
        return $questions_to_grade;
    }
    
    /**
     * Редирект на указанную страницу в попытке проождения теста
     *
     * @param quiz_attempt $attemptobj - Попытка прохождения теста
     * @param integer $pagenum - Номер страницы
     *
     * @return void
     */
    private function attempt_redirect(quiz_attempt $attemptobj, $pagenum = 0)
    {
        global $DB;
        
        // Нормализация номера страницы
        $pagenum = $attemptobj->force_page_number_into_range($pagenum);
        
        // Запись текущей страницы пользователя
        $DB->set_field(
            'quiz_attempts',
            'currentpage',
            $pagenum,
            ['id' => $attemptobj->get_attemptid()]
        );
        
        // Редирект на целевую страницу
        redirect($attemptobj->attempt_url(null, $pagenum) . '&modal=1');
    }
    
    /**
     * Проверим вопросы в конфиге блока и в тесте
     *
     * @param quiz_attempt $attemptobj - Попытка прохождения теста
     *
     * @return void
     */
    private function check_quiz_questions(quiz_attempt $attemptobj)
    {
        if ( ! empty($this->config) && is_object($attemptobj) && ! empty($attemptobj) )
        {
            $slots = array_flip($attemptobj->get_slots());
            $config_questions = array_keys($this->get_formated_config_questions());
            if ( ! empty($config_questions) )
            {
                foreach ($config_questions as $question)
                {
                    if ( ! isset($slots[$question]) )
                    {
                        $field = 'question_control_id_' . $question;
                        unset($this->config->$field);
                    }
                }
                
                // Сохраним конфиг
                $this->instance_config_commit();
            }
        }
    }
    
    /**
     * Модальное окно
     *
     * @param bool $modal - показывать/скрывать модальное окно
     *
     * @return void
     */
    private function modal_message($modal = true, $showajax = false)
    {
        // Класс для скрытия/отображения модального окна
        $class = '';
        if ( ! $modal )
        {
            $class .= 'quiz_teacher_feedback_modal_hidden';
        }
        if ( $showajax )
        {
            $class .= ' modalajax';
        }
        // Формируем контент для модального окна
        $modal_content = '';
        $modal_content .= html_writer::start_div('quiz_teacher_feedback_modal_wrapper');
        $modal_content .= html_writer::start_div('quiz_teacher_feedback_modal_message_wrapper');
        $modal_content .= html_writer::div('', '', ['id' => 'quiz_teacher_feedback_modal_wrapper_bg']);
        $modal_content .= html_writer::start_div('quiz_teacher_feedback_modal_message');
        $modal_content .= html_writer::div('×', 'quiz_teacher_feedback_modal_message_close', ['id' => 'quiz_teacher_feedback_modal_message_close']);
        $modal_content .= html_writer::tag('h2', get_string('feedback_info_modal_header', 'block_quiz_teacher_feedback'));
        $modal_content .= html_writer::div(get_string('feedback_info_modal_notyet_question', 'block_quiz_teacher_feedback'),
                'quiz_teacher_feedback_modal_message_content');
        $modal_content .= html_writer::div(get_string('feedback_info_modal_check_questions', 'block_quiz_teacher_feedback'),
                'quiz_teacher_feedback_modal_message_content_complete quiz_teacher_feedback_modal_hidden');
        $modal_content .= html_writer::end_div();
        $modal_content .= html_writer::end_div();
        $modal_content .= html_writer::end_div();
        
        // Выведем контент в футер блока
        $this->content->footer .= html_writer::div($modal_content, $class, ['id' => 'quiz_teacher_feedback_modal']);
    }
    
    /**
     * Проверяем, что все вопросы подтверждены
     *
     * @param quiz_attempt $attemptobj - объект попытки прохождения
     *
     * @return bool
     */
    public function teacher_confirmed(quiz_attempt $attemptobj)
    {
        // Нормализуем данные
        if ( empty($attemptobj) )
        {
            return true;
        }
        // Флаг, который указывает на то, что по ajax нужно показывать модальное окно
        // о том, что преподаватель оценил все вопросы
        $flag = true;
        $questions_config = $this->get_formated_config_questions();
        $slots = $attemptobj->get_slots();
        if ( ! empty($questions_config) && ! empty($slots) )
        {
            foreach ($slots as $slot)
            {
                if ( $attemptobj->is_real_question($slot) )
                {
                    // Получение текущей попытки прохождения вопроса
                    $qa = $attemptobj->get_question_attempt($slot);
                    $field = 'question_control_id_' . $slot;
                    $feedback = block_quiz_teacher_feedback_get_feedback($qa->get_database_id());
                    if ( empty($feedback->completed)
                            && isset($this->config->$field)
                            && ! empty($this->config->$field) )
                    {// Вопрос не завершен
                        $flag = false;
                        break;
                    }
                }
            }
        }
        return $flag;
    }
    
    /**
     * Возвращает массив данных о попытках студентов
     *
     * @param stdClass $cm - модуль курса
     * @param int $quizid - id теста
     * @param stdClass $renderer - класс рендера для блока
     *
     * return array - массив данных
     *
     */
    public function get_questions_data($cm = null, $quizid = null, $renderer = null)
    {
        GLOBAL $PAGE;
        
        // Начальные параметры
        $data = [];

        // Нормализация
        if ( empty($cm) && ! empty($quizid) )
        {
            $cm = get_coursemodule_from_id('quiz', $quizid);
        }
        if ( empty($renderer) )
        {
            // Получить рендер блока
            $renderer = $PAGE->get_renderer('block_quiz_teacher_feedback');
        }
        
        // Получим студентов курса
        $students = get_role_users(5, context_course::instance($cm->course));
        if ( ! empty($students) )
        {
            foreach ($students as $student)
            {
                $attempt = quiz_get_user_attempt_unfinished($cm->instance, $student->id);
                if ( ! empty($attempt) )
                {
                    $student_data = new stdClass();
                    $student_data->studentid = $student->id;
                    $student_data->fullname = fullname($student);
                    $student_data->attempt_id = $attempt->id;
                    $student_data->current_page = $this->get_current_page(quiz_attempt::create($attempt->id));
                    $student_data->to_grade_pages = $this->get_current_pages(quiz_attempt::create($attempt->id), $renderer);
                    $data[$student->id] = $student_data;
                }
            }
        }
        
        // Возвращаем
        return $data;
    }
    
    /**
     * Проверяет, изменились ли данные (AJAX)
     *
     * @param stdClass $cm - модуль курса
     * @param stdClass $renderer - класс рендера для блока
     *
     * return array - массив данных
     *
     */
    public function is_changed($quizid, $data)
    {
        // Возвращаем массив изменений
        $diff_array = [];
        
        // Начальный индекс массива
        $index = 0;
        
        // Получим свежие данные о статусах вопросов
        $new_data = $this->get_questions_data(null, $quizid);

        if ( property_exists($data, 'students_info') && ! empty($data->students_info) )
        {
            foreach ( $data->students_info as $student )
            {
                // Проверяем, если изменения по попытке студента
                if ( in_array((int)$student->studentid, array_keys($new_data)) )
                {// Попытка еще в процессе
                    if ( (int)$student->attempt_id == (int)$new_data[$student->studentid]->attempt_id )
                    {
                        if ( ! empty($student->pages) )
                        {
                            foreach ( $student->pages as $page )
                            {
                                if ( ($page->status != $new_data[$student->studentid]->to_grade_pages[$page->pageid]->status) )
                                {// Статус вопроса изменился, отправляем новые данные
                                    if ( ! isset($diff_array[$index]) && empty($diff_array[$index]) )
                                    {
                                        $diff_array[$index] = new stdClass();
                                        $diff_array[$index]->studentid = $student->studentid;
                                        $diff_array[$index]->attemptid = $student->attempt_id;
                                        $diff_array[$index]->alldone = 0;
                                        $diff_array[$index]->new_attempt = 0;
                                        $diff_array[$index]->new_pages = [];
                                    }
                                    $new_page = new stdClass();
                                    $new_page->pageid = $new_data[$student->studentid]->to_grade_pages[$page->pageid]->page_id;
                                    $new_page->status = (int)$new_data[$student->studentid]->to_grade_pages[$page->pageid]->status;
                                    $new_page->answered = (int)$new_data[$student->studentid]->to_grade_pages[$page->pageid]->answered;
                                    $new_page->class = $new_data[$student->studentid]->to_grade_pages[$page->pageid]->class;
                                    $new_page->hint = $new_data[$student->studentid]->to_grade_pages[$page->pageid]->hint;
                                    $new_page->name_of_slot = $new_data[$student->studentid]->to_grade_pages[$page->pageid]->name_of_slot;
                                    $diff_array[$index]->new_pages[] = $new_page;
                                }
                            }
                        }
                    }
                    unset($new_data[(int)$student->studentid]);
                }
                
                // Увеличим индекс массива
                $index++;
            }
            
            $groupusers = [];
            if (!empty($data->groupid))
            {
                $groupusers = groups_get_members($data->groupid, 'u.id');
            }
            
            // Добавим новые попытки
            if ( ! empty($new_data) )
            {// Добавим новые попытки
                foreach ( $new_data as $row )
                {
                    if ( ! empty($row->to_grade_pages) && (array_key_exists($row->studentid, $groupusers) || empty($data->groupid)) )
                    {
                        $diff_array[$index] = new stdClass();
                        $diff_array[$index]->fullname = $row->fullname;
                        $diff_array[$index]->studentid = $row->studentid;
                        $diff_array[$index]->attemptid = $row->attempt_id;
                        $diff_array[$index]->current_page = $row->current_page;
                        $diff_array[$index]->alldone = 0;
                        $diff_array[$index]->new_attempt = 1;
                        $diff_array[$index]->new_pages = [];
                        
                        foreach( $row->to_grade_pages as $page )
                        {
                            $new_page = new stdClass();
                            $new_page->pageid = $page->page_id;
                            $new_page->status = (int)$page->status;
                            $new_page->answered = (int)$page->answered;
                            $new_page->class = $page->class;
                            $new_page->hint = $page->hint;
                            $new_page->name_of_slot = $page->name_of_slot;
                            $diff_array[$index]->new_pages[] = $new_page;
                        }
                        
                        $index++;
                    }
                }
            }
        }
        
        // Нормализация массива
        $diff_array = array_values($diff_array);
        
        if ( empty($diff_array) )
        {// Нет измененй, вернем false
            return false;
        } else
        {// Есть изменения, вернем измененные данные
            return $diff_array;
        }
    }
    
    /**
     * Возвращает время для sleep
     *
     * return int - время в секундах
     *
     */
    public function get_long_poll_sleep_time()
    {
        return 3;
    }
    
    /**
     * Возвращает максимальное количество попыток получить обновленные данные на стороне сервера во время long-poll запроса
     *
     * return int - количество попыток
     *
     */
    public function get_long_poll_max_cycles()
    {
        return 10;
    }
    
    /**
     * Возвращает класс для отображения статуса вопроса
     *
     * @param stdClass - параметры страницы
     *
     * @return string - название класса
     *
     */
    public function get_question_status_class(stdClass $page)
    {
        $class = '';
        switch ( $page->status )
        {
            case self::RESPONSE_STATUS_EMPTY_ANSWER:
                $class = 'button_not_answered';
                break;
                
            case self::RESPONSE_STATUS_SHOULD_BE_CONFIRMED:
            case self::RESPONSE_STATUS_SHOULD_BE_RECONFIRMED:
                $class = 'button_in_process';
                break;
                
            case self::RESPONSE_STATUS_CONFIRMED:
                $class = 'button_graded';
                break;
        }
        return $class;
    }
    
    /**
     * Сформировать HTML-код заголовка вопроса
     *
     * @param quiz_attempt $attemptobj - Объект попытки прохождения теста
     * @param int $slot - Номер слота в попытке
     * @param bool $addname - Добавить в заголовок название вопроса
     *
     * @return string - HTML-код заголовка вопроса
     */
    public function get_question_header(quiz_attempt $attemptobj, $slot, $addname = false)
    {
        $html = '';
        
        // Получение номера текущего вопроса
        $number = $attemptobj->get_question_number($slot);
        // Получение названия вопроса
        $name = $attemptobj->get_question_name($slot);
        
        // Формирование заголовка с номером вопроса
        $numbertext = '';
        if ( is_numeric($number) )
        {
            $numbertext = get_string('questionx', 'question',
                    html_writer::tag('span', $number, ['class' => 'qno'])
                    );
        } else if ( $number == 'i' )
        {
            $numbertext = get_string('information', 'question');
        }
        
        $qa = $attemptobj->get_question_attempt($slot);
        $grade = '';
        if ( ! empty($qa) )
        {
            $feedback = block_quiz_teacher_feedback_get_feedback($qa->get_database_id());
            if ( ! empty($feedback) && strlen($feedback->grade) )
            {
                $grade = get_string('question_header_grade', 'block_quiz_teacher_feedback', format_float($feedback->grade, 2));
            }
        }
        
        if ( (bool)$addname )
        {// Добавить в заголовок название вопроса
            if ( $numbertext )
            {// Заголовок с номером вопроса сгенерирован
                $html .= html_writer::tag('h5', $numbertext.'. '.$name . ' ' . $grade);
            } else
            {// Заголовок с номером вопроса не сгенерирован
                $html .= html_writer::tag('h5', $name . ' ' . $grade);
            }
        } else
        {
            $html.= html_writer::tag('h5', $numbertext . ' ' . $grade, ['class' => 'no']);
        }
        return $html;
    }
    
    /**
     * Возвращает подсказку для отображения статуса вопроса
     *
     * @param stdClass - параметры страницы
     *
     * @return string
     *
     */
    public function get_question_status_hint(stdClass $page)
    {
        $hint = '';
        
        switch ( $page->status )
        {
            case self::RESPONSE_STATUS_EMPTY_ANSWER:
                $hint = get_string('button_not_answered', 'block_quiz_teacher_feedback');
                break;
                
            case self::RESPONSE_STATUS_SHOULD_BE_CONFIRMED:
                $hint = get_string('button_in_process', 'block_quiz_teacher_feedback');
                break;
                
            case self::RESPONSE_STATUS_SHOULD_BE_RECONFIRMED:
                $feedback = block_quiz_teacher_feedback_get_feedback($page->qa->get_database_id());
                if ( strlen($feedback->grade) )
                {
                    $hint = get_string('button_in_process_with_grade', 'block_quiz_teacher_feedback', format_float($feedback->grade, 2));
                } else 
                {
                    $hint = get_string('button_in_process', 'block_quiz_teacher_feedback');
                }
                break;
                
            case self::RESPONSE_STATUS_CONFIRMED:
                $hint = get_string('button_graded', 'block_quiz_teacher_feedback');
                break;
        }
        
        return $hint;
    }
}
