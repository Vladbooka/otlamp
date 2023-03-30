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
 * Плагин подписки через форму связи с менеджером,
 * главная библиотека плагина
 *
 * @package    enrol
 * @subpackage sitecall
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/message/lib.php');

class enrol_sitecall_plugin extends enrol_plugin {

    /**
     * Вернуть иконку подписки
     *
     * @param array $instances - экземпляры подписки в курсе
     * @return array - иконки
     */
    public function get_info_icons(array $instances)
    {
        return [new pix_icon('icon', get_string('pluginname', 'enrol_sitecall'), 'enrol_sitecall')];
    }

    /**
     * Возвращает ссылку на страницу добавления подписки курса
     *
     * @param int $courseid - ID курса
     *
     * @return moodle_url page url
     */
    public function get_newinstance_link($courseid)
    {
        global $DB;

        // Получаем контекст
        $context = context_course::instance($courseid, MUST_EXIST);
        // Проверяем права
        if ( ! has_capability('moodle/course:enrolconfig', $context) || ! has_capability('enrol/manual:config', $context) )
        {// Нет прав
            return NULL;
        }
        if ( $DB->record_exists('enrol', array('courseid' => $courseid, 'enrol' => 'sitecall')) )
        {// Такой способ подписки уже есть у курса
            return NULL;
        }

        return new moodle_url('/enrol/sitecall/edit.php', array('courseid'=>$courseid));
    }

    /**
     * Формирование формы подписки на странице курса
     *
     * @param stdClass $instance
     * @return string HTML код блока подписки
     */
    public function enrol_page_hook(stdClass $instance)
    {
        global $CFG, $OUTPUT, $USER, $PAGE;

        // В зависимости от пользователя выведем полную или сокращенную форму
        if ( $USER->id > 1 )
        {// Данные о пользователе возьмем из системы
            $class = 'sc-form-enrollogin';
        } else
        {// Пользователь неизвестен - отобразим полную форму
            $class = 'sc-form-enrol';
        }

        // Формируем html код блока подписки
        $html = '';
        // Добавим js обработчик формы
        $html =  '<script type="text/javascript" src="/enrol/sitecall/formhandler.php?cid='.$PAGE->course->id.'"></script>';
        // Добавим кнопку для вызова формы
        $html .= '<div class="sitecall_button">
                    <input
                        name="submitbutton"
                        value="'.get_string('enrolformbutton', 'enrol_sitecall').'"
                        type="submit"
                        class="btn btn-primary sc-modal-open '.$class.'"
                    >
                  </div>';
        return html_writer::div($html);
    }

    /**
     * Сформировать перечень иконок для способа записи на курс в списке подписок курса
     *
     * @param stdClass $instance
     *
     * @return array - Массив иконок
     */
    public function get_action_icons(stdClass $instance)
    {
        global $OUTPUT;

        if ( $instance->enrol !== 'sitecall' )
        {// Ошибочный экземпляр подписки
            throw new coding_exception('invalid enrol instance!');
        }

        // Формирование контекста
        $context = context_course::instance($instance->courseid);

        // Массив иконок
        $icons = [];

        if ( has_capability('enrol/manual:config', $context) )
        {// Кнопка настроек
            $editlink = new moodle_url('/enrol/sitecall/edit.php', ['courseid' => $instance->courseid]);
            $icons[] = $OUTPUT->action_icon(
                    $editlink,
                    new pix_icon(
                            't/edit',
                            get_string('edit'),
                            'core',
                            ['class' => 'iconsmall']
                    )
            );
        }

        return $icons;
    }

    /**
     * Is it possible to hide/show enrol instance via standard UI?
     *
     * @param stdClass $instance
     * @return bool
     */
    public function can_hide_show_instance($instance)
    {
        $context = context_course::instance($instance->courseid);
        return has_capability('enrol/sitecall:config', $context);
    }

    /**
     * Is it possible to delete enrol instance via standard UI?
     *
     * @param stdClass  $instance
     * @return bool
     */
    public function can_delete_instance($instance) {
        return true;
    }
}