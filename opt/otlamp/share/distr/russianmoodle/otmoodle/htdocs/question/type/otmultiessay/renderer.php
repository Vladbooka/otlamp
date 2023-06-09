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
 * Тип вопроса Мульти-эссе. Рендер вопроса.
 *
 * @package    qtype
 * @subpackage qtype_otmultiessay
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Класс рендера вопроса
 * 
 */
class qtype_otmultiessay_renderer extends qtype_renderer {
    public function formulation_and_controls(question_attempt $qa,
            question_display_options $options) {

        $question = $qa->get_question();
        $responseoutputs = $question->get_format_renderer($this->page);

        // Answer field.
        $step = $qa->get_last_step_with_qt_var('answer_0');
        $alldata = $step->get_all_data();
        $data = $answers = [];
        
        if (! $step->has_qt_var('answer_0') && empty($options->readonly)) {
            // Question has never been answered, fill it with response template.
            foreach($question->responsetemplate as $key => $responsetemplate)
            {
                if( isset ($alldata['answer_' . $key . '']) )
                {
                    $data['answer_' . $key . ''] = $alldata['answer_' . $key . ''];
                } else 
                {
                    $data['answer_' . $key . ''] = $responsetemplate;
                }
            }
            $step = new question_attempt_step($data);
        }

        if( empty($options->readonly) ) 
        {
            foreach($responseoutputs as $key => $responseoutput)
            {
                if( ! empty($question->enablequestion[$key]) )
                {
                    $answers[$key] = $responseoutput->response_area_input('answer_' . $key . '', $qa,
                        $step, $question->responsefieldlines[$key], $options->context, $key);
                }
            }
        } else 
        {
            foreach($responseoutputs as $key => $responseoutput)
            {
                if( $qa->get_last_step_with_qt_var('answer_' . $key)->get_id() || 
                    $qa->get_last_step_with_qt_var('attachments_' . $key)->get_id() 
                )
                {
                    $answers[$key] = $responseoutput->response_area_read_only('answer_' . $key . '', $qa,
                        $step, $question->responsefieldlines[$key], $options->context);
                }
            }
        }

        $files = '';
        foreach($question->attachments as $key => $attachment)
        {
            if( $attachment ) 
            {
                if( empty($options->readonly) && ! empty($question->enablequestion[$key]) ) 
                {
                    $files[$key] = $this->files_input($qa, $attachment, $options, $key);
            
                } elseif( $qa->get_last_step_with_qt_var('attachments_' . $key)->get_id() ) 
                {
                    $files[$key] = $this->files_read_only($qa, $options, $key);
                } else 
                {
                    $files[$key] = '';
                }
            }
        }

        $innerquestiontext = $question->format_innerquestiontext();

        $result = '';
        $result .= html_writer::tag('div', $question->format_questiontext($qa),
            ['class' => 'qtext']);
        foreach($answers as $key => $answer)
        {
            $cleaninnerquestiontext = trim(strip_tags($innerquestiontext[$key]));
            if( ! empty($cleaninnerquestiontext) )
            {
                $result .= html_writer::start_tag('div', ['class' => 'answerblock clearfix']);
                $result .= html_writer::div($innerquestiontext[$key], 'question float-sm-left col-sm-3');
                $result .= html_writer::start_tag('div', ['class' => 'ablock float-sm-left col-sm-9']);
                $result .= html_writer::tag('div', $answer, ['class' => 'answer']);
                if( isset($files[$key]) )
                {
                    $result .= html_writer::tag('div', $files[$key], ['class' => 'attachments']);
                }
                $result .= html_writer::end_tag('div');
                $result .= html_writer::end_tag('div');
            }
        }
        return $result;
    }

    /**
     * Displays any attached files when the question is in read-only mode.
     * @param question_attempt $qa the question attempt to display.
     * @param question_display_options $options controls what should and should
     *      not be displayed. Used to get the context.
     */
    public function files_read_only(question_attempt $qa, question_display_options $options, $key) {
        $files = $qa->get_last_qt_files('attachments_' . $key, $options->context->id);
        $output = [];

        foreach ($files as $file) {
            $output[] = html_writer::tag('p', html_writer::link($qa->get_response_file_url($file),
                    $this->output->pix_icon(file_file_icon($file), get_mimetype_description($file),
                    'moodle', ['class' => 'icon']) . ' ' . s($file->get_filename())));
        }
        return implode($output);
    }

    /**
     * Displays the input control for when the student should upload a single file.
     * @param question_attempt $qa the question attempt to display.
     * @param int $numallowed the maximum number of attachments allowed. -1 = unlimited.
     * @param question_display_options $options controls what should and should
     *      not be displayed. Used to get the context.
     */
    public function files_input(question_attempt $qa, $numallowed,
            question_display_options $options, $key) {
        global $CFG;
        require_once($CFG->dirroot . '/lib/form/filemanager.php');

        $pickeroptions = new stdClass();
        $pickeroptions->mainfile = null;
        $pickeroptions->maxfiles = $numallowed;
        $pickeroptions->itemid = $qa->prepare_response_files_draft_itemid(
                'attachments_' . $key, $options->context->id);
        $pickeroptions->context = $options->context;
        $pickeroptions->return_types = FILE_INTERNAL;

        $pickeroptions->itemid = $qa->prepare_response_files_draft_itemid(
                'attachments_' . $key, $options->context->id);

        $fm = new form_filemanager($pickeroptions);
        $filesrenderer = $this->page->get_renderer('core', 'files');
        return $filesrenderer->render($fm). html_writer::empty_tag(
                'input', array('type' => 'hidden', 'name' => $qa->get_qt_field_name('attachments') . '_' . $key,
                'value' => $pickeroptions->itemid));
    }

    public function manual_comment(question_attempt $qa, question_display_options $options) {
        if ($options->manualcomment != question_display_options::EDITABLE) {
            return '';
        }
        $question = $qa->get_question();
        $html = '';
        foreach($question->graderinfo as $key => $graderinfo)
        {
            if( $qa->get_last_step_with_qt_var('answer_' . $key)->get_id() || 
                    $qa->get_last_step_with_qt_var('attachments_' . $key)->get_id() 
            )
            {
                $cleangraderinfo = trim(strip_tags($graderinfo));
                if( ! empty($cleangraderinfo) )
                {
                    $questionnumber = (int)$key + 1;
                    $graderinfoblockcaption = html_writer::div(get_string('qtype_otmultiessay_grager_info_block_caption', 'qtype_otmultiessay') . ' ' . $questionnumber);
                    $html .= html_writer::nonempty_tag('div', $graderinfoblockcaption . $question->format_text(
                        $question->graderinfo[$key], $question->graderinfo[$key], $qa, 'qtype_otmultiessay',
                        'graderinfo_' . $key, $question->id), ['class' => 'graderinfo clearfix']);
                }
            }
        }
        return $html;
    }
}


/**
 * A base class to abstract out the differences between different type of
 * response format.
 *
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class qtype_otmultiessay_format_renderer_base extends plugin_renderer_base {
    /**
     * Render the students respone when the question is in read-only mode.
     * @param string $name the variable name this input edits.
     * @param question_attempt $qa the question attempt being display.
     * @param question_attempt_step $step the current step.
     * @param int $lines approximate size of input box to display.
     * @param object $context the context teh output belongs to.
     * @return string html to display the response.
     */
    public abstract function response_area_read_only($name, question_attempt $qa,
            question_attempt_step $step, $lines, $context);

    /**
     * Render the students respone when the question is in read-only mode.
     * @param string $name the variable name this input edits.
     * @param question_attempt $qa the question attempt being display.
     * @param question_attempt_step $step the current step.
     * @param int $lines approximate size of input box to display.
     * @param object $context the context teh output belongs to.
     * @return string html to display the response for editing.
     */
    public abstract function response_area_input($name, question_attempt $qa,
            question_attempt_step $step, $lines, $context);

    /**
     * @return string specific class name to add to the input element.
     */
    protected abstract function class_name();
}

/**
 * An essay format renderer for essays where the student should not enter
 * any inline response.
 *
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_otmultiessay_format_noinline_renderer extends plugin_renderer_base {

    protected function class_name() {
        return 'qtype_otmultiessay_noinline';
    }

    public function response_area_read_only($name, $qa, $step, $lines, $context) {
        return '';
    }

    public function response_area_input($name, $qa, $step, $lines, $context) {
        return '';
    }

}

/**
 * An essay format renderer for essays where the student should use the HTML
 * editor without the file picker.
 *
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_otmultiessay_format_editor_renderer extends plugin_renderer_base {
    protected function class_name() {
        return 'qtype_otmultiessay_editor';
    }

    public function response_area_read_only($name, $qa, $step, $lines, $context) {
        return html_writer::tag('div', $this->prepare_response($name, $qa, $step, $context),
                array('class' => $this->class_name() . ' qtype_otmultiessay_response readonly'));
    }

    public function response_area_input($name, $qa, $step, $lines, $context, $key) {
        global $CFG;
        require_once($CFG->dirroot . '/repository/lib.php');

        $inputname = $qa->get_qt_field_name($name);
        $responseformat = $step->get_qt_var($name . 'format');
        $id = $inputname . '_id';

        $editor = editors_get_preferred_editor($responseformat);
        $strformats = format_text_menu();
        $formats = $editor->get_supported_formats();
        foreach ($formats as $fid) {
            $formats[$fid] = $strformats[$fid];
        }

        list($draftitemid, $response) = $this->prepare_response_for_editing(
                $name, $step, $context);

        $editor->use_editor($id, $this->get_editor_options($context),
                $this->get_filepicker_options($context, $draftitemid));

        $output = '';
        $output .= html_writer::start_tag('div', array('class' =>
                $this->class_name() . ' qtype_otmultiessay_response'));

        $output .= html_writer::tag('div', html_writer::tag('textarea', s($response),
                array('id' => $id, 'name' => $inputname, 'rows' => $lines, 'cols' => 60)));

        $output .= html_writer::start_tag('div');
        if (count($formats) == 1) {
            reset($formats);
            $output .= html_writer::empty_tag('input', array('type' => 'hidden',
                    'name' => $inputname . 'format', 'value' => key($formats)));

        } else {
            $output .= html_writer::label(get_string('format'), 'menu' . $inputname . 'format', false);
            $output .= ' ';
            $output .= html_writer::select($formats, $inputname . 'format', $responseformat, '');
        }
        $output .= html_writer::end_tag('div');

        $output .= $this->filepicker_html($inputname, $draftitemid);

        $output .= html_writer::end_tag('div');
        return $output;
    }

    /**
     * Prepare the response for read-only display.
     * @param string $name the variable name this input edits.
     * @param question_attempt $qa the question attempt being display.
     * @param question_attempt_step $step the current step.
     * @param object $context the context the attempt belongs to.
     * @return string the response prepared for display.
     */
    protected function prepare_response($name, question_attempt $qa,
            question_attempt_step $step, $context) {
        if (!$step->has_qt_var($name)) {
            return '';
        }

        $formatoptions = new stdClass();
        $formatoptions->para = false;
        return format_text($step->get_qt_var($name), $step->get_qt_var($name . 'format'),
                $formatoptions);
    }

    /**
     * Prepare the response for editing.
     * @param string $name the variable name this input edits.
     * @param question_attempt_step $step the current step.
     * @param object $context the context the attempt belongs to.
     * @return string the response prepared for display.
     */
    protected function prepare_response_for_editing($name,
            question_attempt_step $step, $context) {
        return array(0, $step->get_qt_var($name));
    }

    /**
     * @param object $context the context the attempt belongs to.
     * @return array options for the editor.
     */
    protected function get_editor_options($context) {
        return array('context' => $context);
    }

    /**
     * @param object $context the context the attempt belongs to.
     * @param int $draftitemid draft item id.
     * @return array filepicker options for the editor.
     */
    protected function get_filepicker_options($context, $draftitemid) {
        return array('return_types'  => FILE_INTERNAL | FILE_EXTERNAL);
    }

    /**
     * @param string $inputname input field name.
     * @param int $draftitemid draft file area itemid.
     * @return string HTML for the filepicker, if used.
     */
    protected function filepicker_html($inputname, $draftitemid) {
        return '';
    }
}


/**
 * An essay format renderer for essays where the student should use the HTML
 * editor with the file picker.
 *
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_otmultiessay_format_editorfilepicker_renderer extends qtype_otmultiessay_format_editor_renderer {
    protected function class_name() {
        return 'qtype_otmultiessay_editorfilepicker';
    }

    protected function prepare_response($name, question_attempt $qa,
            question_attempt_step $step, $context) {
        if (!$step->has_qt_var($name)) {
            return '';
        }

        $formatoptions = new stdClass();
        $formatoptions->para = false;
        $text = $qa->rewrite_response_pluginfile_urls($step->get_qt_var($name),
                $context->id, 'answer', $step);
        return format_text($text, $step->get_qt_var($name . 'format'), $formatoptions);
    }

    protected function prepare_response_for_editing($name,
            question_attempt_step $step, $context) {
        return $step->prepare_response_files_draft_itemid_with_text(
                $name, $context->id, $step->get_qt_var($name));
    }

    protected function get_editor_options($context) {
        return array(
            'subdirs' => 0,
            'maxbytes' => 0,
            'maxfiles' => -1,
            'context' => $context,
            'noclean' => 0,
            'trusttext'=>0
        );
    }

    /**
     * Get the options required to configure the filepicker for one of the editor
     * toolbar buttons.
     * @param mixed $acceptedtypes array of types of '*'.
     * @param int $draftitemid the draft area item id.
     * @param object $context the context.
     * @return object the required options.
     */
    protected function specific_filepicker_options($acceptedtypes, $draftitemid, $context) {
        $filepickeroptions = new stdClass();
        $filepickeroptions->accepted_types = $acceptedtypes;
        $filepickeroptions->return_types = FILE_INTERNAL | FILE_EXTERNAL;
        $filepickeroptions->context = $context;
        $filepickeroptions->env = 'filepicker';

        $options = initialise_filepicker($filepickeroptions);
        $options->context = $context;
        $options->client_id = uniqid();
        $options->env = 'editor';
        $options->itemid = $draftitemid;

        return $options;
    }

    protected function get_filepicker_options($context, $draftitemid) {
        global $CFG;

        return array(
            'image' => $this->specific_filepicker_options(array('image'),
                            $draftitemid, $context),
            'media' => $this->specific_filepicker_options(array('video', 'audio'),
                            $draftitemid, $context),
            'link'  => $this->specific_filepicker_options('*',
                            $draftitemid, $context),
        );
    }

    protected function filepicker_html($inputname, $draftitemid) {
        $nonjspickerurl = new moodle_url('/repository/draftfiles_manager.php', array(
            'action' => 'browse',
            'env' => 'editor',
            'itemid' => $draftitemid,
            'subdirs' => false,
            'maxfiles' => -1,
            'sesskey' => sesskey(),
        ));

        return html_writer::empty_tag('input', array('type' => 'hidden',
                'name' => $inputname . ':itemid', 'value' => $draftitemid)) .
                html_writer::tag('noscript', html_writer::tag('div',
                    html_writer::tag('object', '', array('type' => 'text/html',
                        'data' => $nonjspickerurl, 'height' => 160, 'width' => 600,
                        'style' => 'border: 1px solid #000;'))));
    }
}


/**
 * An essay format renderer for essays where the student should use a plain
 * input box, but with a normal, proportional font.
 *
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_otmultiessay_format_plain_renderer extends plugin_renderer_base {
    /**
     * @return string the HTML for the textarea.
     */
    protected function textarea($response, $lines, $attributes) {
        $attributes['class'] = $this->class_name() . ' qtype_otmultiessay_response';
        $attributes['rows'] = $lines;
        $attributes['cols'] = 60;
        return html_writer::tag('textarea', s($response), $attributes);
    }

    protected function class_name() {
        return 'qtype_otmultiessay_plain';
    }

    public function response_area_read_only($name, $qa, $step, $lines, $context) {
        return $this->textarea($step->get_qt_var($name), $lines, array('readonly' => 'readonly'));
    }

    public function response_area_input($name, $qa, $step, $lines, $context) {
        $inputname = $qa->get_qt_field_name($name);
        return $this->textarea($step->get_qt_var($name), $lines, array('name' => $inputname)) .
                html_writer::empty_tag('input', array('type' => 'hidden',
                    'name' => $inputname . 'format', 'value' => FORMAT_PLAIN));
    }
}


/**
 * An essay format renderer for essays where the student should use a plain
 * input box with a monospaced font. You might use this, for example, for a
 * question where the students should type computer code.
 *
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_otmultiessay_format_monospaced_renderer extends qtype_otmultiessay_format_plain_renderer {
    protected function class_name() {
        return 'qtype_otmultiessay_monospaced';
    }
}