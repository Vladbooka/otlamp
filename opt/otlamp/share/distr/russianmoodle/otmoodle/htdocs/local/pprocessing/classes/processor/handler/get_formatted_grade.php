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
namespace local_pprocessing\processor\handler;

require_once($CFG->libdir . '/gradelib.php');

use local_pprocessing\container;
use local_pprocessing\processor\condition;
use local_pprocessing\logger;
use grade_category;
use Exception;

defined('MOODLE_INTERNAL') || die();

/**
 * Получить представление оценки в процентах
 *
 * @package local
 * @subpackage pprocessing
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_formatted_grade extends base
{
    /**
     * {@inheritDoc}
     * @see \local_pprocessing\processor\handler\base::execute()
     */
    protected function execution_process(container $container)
    {
        // Значение оценки, которое нужно преобразовать
        $value = $this->get_required_parameter('value');
        // Идентификатор курса или модуля курса (с какой оценкой работаем - за курс или за модуль)
        $cmorcourseid = $this->get_required_parameter('cmorcourseid');
        // Соответственно указываем с курсом или модулем имеем дело
        $itemtype = $this->get_required_parameter('itemtype');
        // Использовать ли локализованный разделитель или нет
        $localized = $this->get_optional_parameter('localized', false);
        // Формат оценки в который необходимо преобразовать переданное значение
        $displaytype = $this->get_optional_parameter('displaytype', GRADE_DISPLAY_TYPE_PERCENTAGE);
        // Количество знаков после запятой
        $decimals = $this->get_optional_parameter('decimals', null);
        $grade = '';
        // Получение основных данных на основе переданных параметров
        switch ($itemtype) {
            case 'course':
                $itemmodule = null;
                $courseid = $cmorcourseid;
                try {
                    $coursecat = grade_category::fetch_course_category($cmorcourseid);
                } catch (Exception $e) {
                    // Категория оценки не найдена
                    logger::write_log(
                        'processor',
                        $this->get_type()."__".$this->get_code(),
                        'debug',
                        [
                            'message' => 'grade category not found',
                            'cmid' => $cmorcourseid
                        ]
                    );
                    return null;
                }
                $instanceid = $coursecat->id;
                break;
            case 'mod':
            default:
                try {
                    list($course, $cm) = get_course_and_cm_from_cmid($cmorcourseid);
                } catch (Exception $e) {
                    // Модуль курса удален
                    logger::write_log(
                        'processor',
                        $this->get_type()."__".$this->get_code(),
                        'debug',
                        [
                            'message' => 'course module not found',
                            'cmid' => $cmorcourseid
                        ]
                    );
                    return null;
                }
                $itemmodule = $cm->modname;
                $courseid = $course->id;
                $instanceid = $cm->instance;
                break;
        }
        $params = ['itemtype' => $itemtype, 'itemmodule' => $itemmodule,
            'iteminstance' => $instanceid, 'courseid' => $courseid];
        try {
            $grade_items = \grade_item::fetch_all($params);
        } catch (Exception $e) {
            // Не удалось получить grade items
            logger::write_log(
                'processor',
                $this->get_type()."__".$this->get_code(),
                'debug',
                [
                    'message' => 'grade items not found',
                    'params' => $params
                ]
            );
            return null;
        }
        if (empty($grade_items)) {
            // запись в лог
            logger::write_log(
                'processor',
                $this->get_type()."__".$this->get_code(),
                'debug',
                [
                    'gi_params' => $params,
                    'value' => $value,
                    'displaytype' => $displaytype,
                    'decimals' => $decimals,
                    'localized' => $localized,
                    'gi' => $grade_items,
                    'formattedgrade' => $grade
                ]
            );
            return $grade;
        }
        $grade_item = array_shift($grade_items);
        $grade = grade_format_gradevalue($value, $grade_item, $localized, $displaytype, $decimals);
        switch ($displaytype) {
            // Дополнительные преобразования
            case GRADE_DISPLAY_TYPE_PERCENTAGE:
                $grade = substr($grade, 0, strpos($grade, ' %'));
                break;
        }
        // запись в лог
        logger::write_log(
            'processor',
            $this->get_type()."__".$this->get_code(),
            'debug',
            [
                'gi_params' => $params,
                'value' => $value,
                'displaytype' => $displaytype,
                'decimals' => $decimals,
                'localized' => $localized,
                'gi' => $grade_items,
                'formattedgrade' => $grade
            ]
        );
        if (empty($grade)) {
            $grade = grade_floatval($grade);
        }
        return $grade;
    }
}

