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
 * Form for editing HTML block instances.
 *
 * @package    block_mastercourse
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot . '/blocks/mastercourse/locallib.php');

class block_mastercourse extends block_base
{
    /**
     * Инициализация блока
     *
     * @return void
     */
    function init()
    {
        $this->title = get_string('pluginname', 'block_mastercourse');
    }

    /**
     * Флаг присутствия конфигов блока
     * {@inheritDoc}
     * @see block_base::has_config()
     */
    function has_config()
    {
        return true;
    }

    /**
     * Доступные контексты для добавления
     * {@inheritDoc}
     * @see block_base::applicable_formats()
     */
    function applicable_formats()
    {
        return [
            'all' => true,
        ];
    }

    /**
     * Возможность добавления в контекст несколько и более инстансов блока
     * {@inheritDoc}
     * @see block_base::instance_allow_multiple()
     */
    function instance_allow_multiple()
    {
        return false;
    }

    /**
     * {@inheritDoc}
     * @see block_base::get_content()
     */
    function get_content()
    {
        global $CFG, $PAGE;

        if ($this->content !== NULL)
        {
            return $this->content;
        }

        $coursecontext = $this->page->context->get_course_context(false);
        if( ! empty($coursecontext) && ! empty($this->context) &&
            file_exists($CFG->dirroot . '/blocks/dof/locallib.php') )
        {
            require_once($CFG->dirroot . '/blocks/dof/locallib.php');
            global $DOF;
            
            // передаем опцию, означающую, что право видеть матер-курс уже проверено
            $options = [
                'external_capabilities_verified' => [
                    'view_mastercourse' => has_capability('block/mastercourse:view_mastercourse', $this->context)
                ]
            ];
            // формирование ссылки на мастер-курс
            $mastercourselink = $DOF->im('programmitems')->coursedata_mastercourse_link(
                $coursecontext->instanceid,
                $options
            );
            $mastercoursehaslink = ! empty($mastercourselink) ? true : false;
            $mastercourselink = dof_html_writer::div($mastercourselink, 'cvp_mastercourse_link');
            
            // передаем опции, означающую, что права согласования матер-курса уже проверены
            $options = [
                'external_capabilities_verified' => [
                    'request_verification' => has_capability('block/mastercourse:request_verification', $this->context),
                    'respond_requests' => has_capability('block/mastercourse:respond_requests', $this->context)
                ]
            ];
            // формирование панели управления мастер-курсами
            $verificationpanel = $DOF->im('programmitems')->coursedata_verification_panel(
                $coursecontext->instanceid,
                null,
                $options
            );
            
            // получение дисциплин, связанных с курсом
            $programmitems = $DOF->storage('programmitems')->get_mastercourse_programmitems($coursecontext->instanceid);
            if( ! empty($programmitems) )
            {// дисциплины есть, значит текущий курс - мастеркурс
                if( get_config('block_mastercourse', 'display_navbar_caption') )
                {
                    // подключение скрипта, добавляющего в хлебные крошки метку
                    $PAGE->requires->js(new moodle_url('/blocks/mastercourse/script.js'));
                }
                if( get_config('block_mastercourse', 'display_verification_panel_caption') )
                {
                    // оборачивание панели согласования в слой с меткой
                    $verificationpanel = html_writer::div(
                        html_writer::div($verificationpanel, 'data'),
                        'ismastercourse',
                        ['data-caption' => get_string('mastercourse_title', 'block_mastercourse')]
                    );
                }
            }
            if ( ! empty($mastercoursehaslink) || ! empty($verificationpanel) )
            {
                $this->content = new stdClass;
                $this->content->footer = '';
                $this->content->text = $mastercourselink . $verificationpanel;
            }
        }
        // Отображение панели публикации курса на внешнем учебном портале
        if (get_config('block_mastercourse', 'display_publication_panel') &&
            has_capability('block/mastercourse:manage_publication', $this->context))
        {
            if (!isset($this->content))
            {
                $this->content = new stdClass;
                $this->content->footer = '';
                $this->content->text = '';
            }
            
            $parentcontext = $this->context->get_parent_context();
            if ($parentcontext->contextlevel == CONTEXT_COURSE && $parentcontext->instanceid != get_site()->id)
            {
                $publicationurl = new moodle_url('/blocks/mastercourse/publication.php', [
                    'ctx' => $this->context->id
                ]);
                $this->content->text .= html_writer::link($publicationurl, get_string('page__publication', 'block_mastercourse'));
            }
        }

        return $this->content;
    }
}
