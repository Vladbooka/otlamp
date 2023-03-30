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
 * Front-end class.
 *
 * @package    availability_assignfeedback
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace availability_assignfeedback;
 
defined('MOODLE_INTERNAL') || die();
 
class frontend extends \core_availability\frontend {
    /**
     * @var array Cached data
     */
    protected $cache = [];

    /**
     * {@inheritDoc}
     * @see \core_availability\frontend::get_javascript_strings()
     */
    protected function get_javascript_strings() {
        return [
            'inassign',
            'needfeedback',
            'chooseassign',
            'chooseassignfeedback'
        ];
    }
 
    /**
     * {@inheritDoc}
     * @see \core_availability\frontend::get_javascript_init_params()
     */
    protected function get_javascript_init_params($course, \cm_info $cm = null, \section_info $section = null) 
    {
        $jsparams = [
            'suitablemodules' => $this->get_suitable_modules($course, $cm, $section)
        ];
        return [$jsparams];
    }
 
    /**
     * {@inheritDoc}
     * @see \core_availability\frontend::allow_add()
     */
    protected function allow_add($course, \cm_info $cm = null, \section_info $section = null)
    {
        $suitablemodules = $this->get_suitable_modules($course, $cm, $section);
        return ! empty($suitablemodules);
    }
    
    /**
     * Получение массива подходящих модулей типа "задание" (имеющих настроенные и активированные типы отзывов) 
     * 
     * @param \stdClass $course - объект курса Moodle
     * @param \cm_info $cm - редактируемый модуль курса
     * @param \section_info $section - редактируемая секция
     * 
     * @return array - массив с данными по подходящим модулям
     */
    private function get_suitable_modules($course, \cm_info $cm = null, \section_info $section = null)
    {
        global $CFG;
        
        $cachekey = $course->id . '_' . ($cm ? $cm->id : '-') . '_' . ($section ? $section->id : '-');
        
        if ( ! array_key_exists($cachekey, $this->cache) )
        {// В кэшэ еще нет подходящих данных
        
            $suitablemodules = [];
        
            if( file_exists($CFG->dirroot.'/mod/assign/locallib.php'))
            {
                require_once($CFG->dirroot.'/mod/assign/locallib.php');
                // Контекст курса
                $coursecontext = \context_course::instance($course->id);
                // Данные о модулях курса
                $modinfo = get_fast_modinfo($course);
                foreach ($modinfo->cms as $anycm)
                {
                    // Является ли модуль текущим модулем с условиями ограничения доступа
                    $iscurrentcm = ( ! empty($cm) && $cm->id == $anycm->id );
                    
                    // Объект для сбора необходимых данных по модулю
                    $suitablemodule = new \stdClass();
        
                    if ( $anycm->modname=='assign' && ! $iscurrentcm )
                    {// Модуль "задание", не являющийся текущим модулем
                        // Контекст модуля "задание"
                        $anycmcontext = \context_module::instance($anycm->id);
                        // Объект задания
                        $assign = new \assign($anycmcontext, $anycm, $course);
                        // Идентификатор модуля курса
                        $suitablemodule->cmid = $anycm->id;
                        // Наименование модуля курса
                        $suitablemodule->name = format_string($anycm->name, true, [
                            'context' => $coursecontext
                        ]);
                        // Массив с активными типами отзывов в задании
                        $suitablemodule->feedbacks = [];
                        foreach( $assign->get_feedback_plugins() as $plugin )
                        {
                            if( $plugin->is_visible() && $plugin->is_enabled() )
                            {// Плагин доступен
                                // Добавление плагина отзыва
                                $suitablemodule->feedbacks[get_class($plugin)] = $plugin->get_name();
                            }
                        }
                    }
                    
                    if( ! empty($suitablemodule->feedbacks) )
                    {
                        // Добавление модуля в массив подходящих модулей, 
                        // только при условии, есть есть какие-либо активные типы отзывов 
                        $suitablemodules[] = $suitablemodule;
                    }
                }
            }
            // Запись данных в кэш
            $this->cache[$cachekey] = $suitablemodules;
        }
        return $this->cache[$cachekey];
    }
}