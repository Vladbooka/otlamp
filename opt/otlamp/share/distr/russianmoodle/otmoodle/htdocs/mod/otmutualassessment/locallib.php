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

require_once(dirname(__FILE__) . '/../../config.php');

defined('MOODLE_INTERNAL') || die;

function mod_otmutualassessment_get_strategy_list() {
    global $CFG;
    $result = [];
    // Установка пути к стратегиям
    $basedir = $CFG->dirroot . '/mod/otmutualassessment/classes/strategy/';
    if (is_dir($basedir)) {
        // Поиск источников
        foreach (array_diff(scandir($basedir), [
            '..',
            '.'
        ]) as $strategy) {
            if (is_file($basedir . $strategy)) { // Получена директория типа стратегии
                if ($strategy == 'base.php') { // Пропускаем базовый класс стратегии
                    continue;
                }
                $sourcepath = $basedir . $strategy;
                // Регистрация стратегии
                $classname = 'mod_otmutualassessment\\strategy\\' . basename($strategy, '.php');
                require_once($sourcepath);
                if (class_exists($classname)) { // Стратегия существует
                    $result[$classname::get_code()] = $classname;
                }
            }
        }
    }
    return $result;
}

/**
 * Получить объект инстанса
 * @param int $instanceid идентификатор инстанса модуля курса
 * @return stdClass|boolean
 */
function mod_otmutualassessment_get_instance($instanceid) {
    global $DB;
    return $DB->get_record('otmutualassessment', ['id' => $instanceid]);
}

/**
 * Процесс выполнения запланированных задач
 * @param $task указатель на объект экзмепляра класса задачи
 * @param array $actions массив действий, которые необходимо выполнить
 */
function mod_otmutualassessment_execute_task(& $task, $actions = ['full_refresh']) {
    if (!is_array($actions)) {
        debugging('parameter $actions must be array');
        return;
    }
    $cmid = $task->get_custom_data()->cmid;
    $groupid = $task->get_custom_data()->groupid;
    list($course, $cm) = get_course_and_cm_from_cmid($cmid);
    $context = context_module::instance($cmid);
    $instance = mod_otmutualassessment_get_instance($cm->instance);
    $strategylist = mod_otmutualassessment_get_strategy_list();
    $otmutualassessment = new $strategylist[$instance->strategy]($context, $cm, $course);
    $otmutualassessment->refresh($actions, $groupid);
}
