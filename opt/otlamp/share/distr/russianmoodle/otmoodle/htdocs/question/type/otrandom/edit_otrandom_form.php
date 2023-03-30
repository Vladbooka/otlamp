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
 * Тип вопроса Случайный вопрос с учетом правил. Класс формы сохранения экземпляра вопроса.
 *
 * @package    qtype
 * @subpackage otrandom
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class qtype_otrandom_edit_form extends question_edit_form 
{
    /**
     * Код вопроса
     * 
     * @return string
     */
    public function qtype() 
    {
        return 'otrandom';
    }
    
    /**
     * Объявление дополнительных полей формы
     * 
     * @return void
     */
    protected function definition_inner($mform) 
    {
        // Скрытие основных полей вопроса
        $mform->removeElement('questiontext');
        $mform->addElement('hidden', 'questiontext', '');
        $mform->setType('questiontext', PARAM_RAW);
        $mform->removeElement('generalfeedback');
        $mform->addElement('hidden', 'generalfeedback', '');
        $mform->setType('generalfeedback', PARAM_RAW);
        $mform->removeElement('defaultmark');
        $mform->addElement('hidden', 'defaultmark', 0);
        $mform->setType('defaultmark', PARAM_RAW);
        
        // Основные настройки
        $mform->addElement(
            'header', 
            'header_randomquestion', 
            get_string('editform_header_randomquestion', 'qtype_otrandom')
        );

            // Целевая категория для выбора случайного вопроса
            $mform->addElement(
                'questioncategory', 
                'targetcategory', 
                get_string('editform_targetcategory_label', 'qtype_otrandom'),
                [
                    'contexts' => $this->contexts->having_cap('moodle/question:useall')
                ]
            );
    
            // Использование подкатегорий для выбора случайного вопроса
            $mform->addElement(
                'advcheckbox', 
                'includesubcategories',
                get_string('editform_includesubcategories_label', 'qtype_otrandom'), 
                null, 
                null, 
                [0, 1]
            );

        // Настройки весов групп
        $mform->addElement(
            'header', 
            'header_factors', 
            get_string('editform_header_groups', 'qtype_otrandom')
        );
        
            // Уровень видимости групп
            $select = [
                0 => get_string('editform_grouplevel_system', 'qtype_otrandom'),
                1 => get_string('editform_grouplevel_course', 'qtype_otrandom')
            ];
            $mform->addElement(
                'select',
                'grouplevel',
                get_string('editform_grouplevel_label', 'qtype_otrandom'),
                $select
            );
            $mform->addHelpButton('grouplevel', 'editform_grouplevel', 'qtype_otrandom');
        
            // Получение типа текущего вопроса
            $qtype = question_bank::get_qtype('otrandom');
            // Получение групп
            $groups = (array)$qtype->groups_get_list();
            
            if ( $groups )
            {// Найдены группы
                
                // Добавление пояснения для раздела
                $mform->addElement(
                    'html',
                    html_writer::tag('p', get_string('editform_groupweight_description', 'qtype_otrandom'))
                );
            }
            // Добавление коэфициента для всех групп
            foreach ( $groups as $groupcode => $group )
            {
                // Плейсхолдер
                $placeholder = $group->get_min_weight().'-'.$group->get_max_weight();
                // Поле для указания веса
                $mform->addElement(
                    'text',
                    $groupcode.'_weight',
                    $group->get_local_name(),
                    'size = "4" placeholder = "'.$placeholder.'"'
                );
                $mform->setType($groupcode.'_weight', PARAM_INT);
                $mform->setDefault($groupcode.'_weight', $group->get_default_weight());
                $mform->addHelpButton($groupcode.'_weight', 'editform_'.$groupcode.'_weight', 'qtype_otrandom');
                
                // JS валидация минимального веса
                $mform->addElement('hidden', $groupcode.'_weight_min', $group->get_min_weight());
                $mform->setType($groupcode.'_weight_min', PARAM_INT);
                $mform->addRule(
                    [$groupcode.'_weight', $groupcode.'_weight_min'], 
                    get_string('error_editform_groupweight_overflow_min', 'qtype_otrandom', $group->get_min_weight()), 
                    'compare', 
                    'gte',
                    'client'
                );
                // JS валидация максимального веса
                $mform->addElement('hidden', $groupcode.'_weight_max', $group->get_max_weight());
                $mform->setType($groupcode.'_weight_max', PARAM_INT);
                $mform->addRule(
                    [$groupcode.'_weight', $groupcode.'_weight_max'], 
                    get_string('error_editform_groupweight_overflow_max', 'qtype_otrandom', $group->get_max_weight()), 
                    'compare', 
                    'lte',
                    'client'
                );
            }
    }

    /**
     * Валидация полей формы сохранения экземпляра вопроса
     *
     * @param array $data - Данные формы сохранения
     * @param array $data - Загруженные файлы формы сохранения
     *
     * @return $errors - Массив ошибок
     */
    public function validation($data, $files) 
    {
        $errors =  parent::validation($data, $files);
    
        // Получение типа текущего вопроса
        $qtype = question_bank::get_qtype('otrandom');
        // Получение групп
        $groups = (array)$qtype->groups_get_list();
        // Проверка весовых коэффициентов
        foreach ( $groups as $groupcode => $group )
        {
            $groupweight = $data[$groupcode.'_weight'];
            if ( $groupweight > $group->get_max_weight() )
            {
                $errors[$groupcode.'_weight'] = get_string(
                    'error_editform_groupweight_overflow_max', 
                    'qtype_otrandom', 
                    $group->get_max_weight()
                );
            } elseif ( $groupweight < $group->get_min_weight() )
            {
                $errors[$groupcode.'_weight'] = get_string(
                    'error_editform_groupweight_overflow_min',
                    'qtype_otrandom',
                    $group->get_min_weight()
                );
            }
        }
        
        // Получение целевой категории
        list($category) = explode(',', $data['targetcategory']);
        // Получение доступных вопросов категории
        $availablequestions = $qtype->
            get_available_questions_from_category($category, $data['includesubcategories']);
        $countavailable = count($availablequestions);
        if ( $countavailable === 0 ) 
        {// Нет доступных вопросов в указанной категории
            $errors['targetcategory'] = get_string('error_editform_emptyavailable', 'qtype_otrandom');
        }
        return $errors;
    }
    
    /**
     * Заполнение формы значениями
     * 
     * @return void
     */
    public function set_data($question) 
    {
        // Текст вопроса
        $question->questiontext = ' ';
        // Отзыв
        $question->generalfeedback = ' ';

        parent::set_data($question);
    }
    
    /**
     * Предварительная обработка полей формы сохранения экземпляра вопроса
     *
     * Организация заполнения полей данными
     *
     * @param stdClass $question - Данные вопроса для заполнения полей формы
     *
     * @return stdClass $question - Данные для заполнения
     */
    protected function data_preprocessing($question) 
    {
        global $DB;
        
        $question = parent::data_preprocessing($question);
        
        // Получение типа текущего вопроса
        $qtype = question_bank::get_qtype('otrandom');
        
        // Текст вопроса
        $question->questiontext = ' ';
        $question->generalfeedback = ' ';
        if ( empty($question->name) )
        {// Автозаполнение имени вопроса
            $question->name = get_string('question_name_default', 'qtype_otrandom');
        }
        
        if ( isset($question->targetcategory) )
        {
            // Конвертация идентификатора категории
            $targetcategory = $DB->get_record(
                'question_categories', ['id' => $question->targetcategory]
                );
            if ( $targetcategory )
            {
                $question->targetcategory = "{$targetcategory->id},{$targetcategory->contextid}";
            }
        }
        
        if ( isset($question->groupweights) )
        {
            // Конвертация поля с данными по весам
            $groupweights = unserialize($question->groupweights);
            
            // Разбиение данных весов групп
            foreach ( $groupweights as $groupcode => $weight )
            {
                $question->{$groupcode.'_weight'} = $weight;
            }
        }
        
        return $question;
    }
}