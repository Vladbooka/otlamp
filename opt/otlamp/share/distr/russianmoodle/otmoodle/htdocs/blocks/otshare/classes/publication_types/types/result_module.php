<?php

/**
 * Поделиться ссылкой
 *
 * @package    block
 * @subpackage otshare
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_otshare\publication_types\types;

use html_writer;
use moodle_url;
use stdClass;
use block_otshare\publication_types\publication_base as publication_parent;
use block_otshare\exception\publication as publication_exception;
use core\session\exception;
use context_module;

class result_module extends publication_parent
{
    protected $name = 'result_module';
    protected $cmid;
    protected $cm;
    protected $decimal = 2;
    protected $finalgrade_cm;
    protected $maxgrade_cm;
    
    protected function check_properties()
    {
        if ( empty($this->cmid) )
        {
            throw new publication_exception('invalid_properties');
        }
        
        return true;
    }
    
    protected function get_insert_data()
    {
        global $CFG;
        // Сериализованные данные
        $data = new stdClass();
        $data->cmid = $this->cmid;
        $data->type = $this->name;
        $data->userid = $this->userid;
        $data->sn = $this->get_sharer_name();
        $this->set_user_formated_grade();
        $data->finalgrade = $this->finalgrade_cm;
        $data->maxgrade = $this->maxgrade_cm;
        
        $altersharing = $this->get_block_config('altersharing');
        if( ! empty($altersharing) )
        {
            $context = context_module::instance($this->cmid);
            $modinfo = get_fast_modinfo($this->courseid);
            $cm = $modinfo->get_cm($context->instanceid);
            if( $cm->modname == 'quiz' )
            {
                require_once($CFG->dirroot . '/mod/quiz/locallib.php');
                $feedback = quiz_feedback_for_grade($this->finalgrade, $this->get_quiz($cm), $context);
                $data->review = strip_tags($feedback);
            } else
            {
                $data->review = '';
            }
        }
        
        return $data;
    }
    
    private function get_quiz($cm)
    {
        global $DB;
        $quiz = $DB->get_record('quiz', ['id' => $cm->instance]);
        $quiz->cmid = $cm->id;
        return $quiz;
    }
    
    protected function get_grade_text($short = false, $nowrapp = false)
    {
        GLOBAL $OUTPUT;
        
        // Дефолтные параметры
        $html = '';
        
        if ( empty($short) )
        {
            $html .= get_string('get_grade_course', 'block_otshare');
            $html .= html_writer::div($this->finalgrade_cm, 'block_otshare_lp_grade_course');
            $html .= get_string('for', 'block_otshare') . ' ';
            $html .= html_writer::start_div('block_otshare_lp_module_info_wrapper');
            $html .= html_writer::start_tag('img', ['class' => 'block_otshare_lp_module_icon', 'src' => $OUTPUT->image_url('icon', $this->cm->modname)]);
            $html .= html_writer::span($this->cm->name . ' ' . get_string('for_course', 'block_otshare'));
            $html .= html_writer::end_tag('img');
            $html .= html_writer::end_div();
            $html .= html_writer::start_div('block_otshare_lp_name_course');
            $html .= html_writer::span($this->course->fullname);
            $html .= html_writer::end_div();
        } else
        {
            if( ! empty($nowrapp) )
            {
                $html .= $this->finalgrade_cm . '/' . $this->maxgrade_cm;
            } else 
            {
                $html .=
                get_string('get_final_grade', 'block_otshare') . ' (' . $this->finalgrade_cm . '/' . $this->maxgrade_cm . ') ' .
                get_string('for', 'block_otshare') . ' ' . get_string('modulename', 'mod_' . $this->cm->modname) . ' - ' . $this->cm->name;
            }
        }
        
        return $html;
    }
    
    protected function get_scale($scaleid) 
    {
        GLOBAL $DB;
        
        $scaletext = $DB->get_field(
                'scale',
                'scale', 
                ['id' => $scaleid], 
                IGNORE_MISSING
                );
        
        $scale = explode(',', $scaletext);
        
        return $scale;
    }
    
    protected function set_user_formated_grade()
    {
        GLOBAL $DB;
        
        try
        {
            $array_data = get_course_and_cm_from_cmid($this->cmid);
        } catch (exception $e )
        {// Удален элемент
            throw new publication_exception($e);
        }
        
        try
        {
            $module_info = $array_data[1]->get_course_module_record(true);
            $sql = 'SELECT * FROM {grade_items} WHERE
                courseid = ? and
                itemmodule = ? and
                iteminstance = ? and
                itemtype = ? and
                (gradetype = ? or gradetype = ?)';
            $params = [$array_data[0]->id, $module_info->modname, $module_info->instance, 'mod', GRADE_TYPE_VALUE, GRADE_TYPE_SCALE];
            $activity = $DB->get_record_sql($sql, $params, IGNORE_MULTIPLE);
        } catch ( exception $e )
        {
            throw new publication_exception($e);
        }
        
        // Объект результата
        if ( empty($activity->gradetype) || 
                ($activity->gradetype != GRADE_TYPE_VALUE && $activity->gradetype != GRADE_TYPE_SCALE) ) 
        {
            $this->maxgrade_cm = '';
            $this->finalgrade_cm = '-';
        } else 
        {
            $sql = 'SELECT * FROM {grade_grades}
                     WHERE itemid = ? AND finalgrade is not NULL and userid = ?
                     ORDER BY finalgrade, timemodified DESC';
            
            $grade = $DB->get_record_sql($sql, [$activity->id, $this->userid], IGNORE_MULTIPLE);
            
            if ( ! empty($grade) && ! empty($grade->finalgrade) )
            {
                if ( $activity->gradetype == GRADE_TYPE_SCALE )
                {
                    $scale = $this->get_scale($activity->scaleid);
                    
                    $answer = (round($grade->finalgrade, 0, PHP_ROUND_HALF_UP) - 1);
                    
                    if ( isset($scale[$answer]) ) 
                    {
                        $this->maxgrade_cm = $scale[$answer];
                        $this->finalgrade_cm = $scale[(int)$activity->grademax - 1];
                    } else {
                        $this->maxgrade_cm = '';
                        $this->finalgrade_cm = '-';
                    }
                } elseif ( $activity->gradetype == GRADE_TYPE_VALUE )
                {
                    $this->finalgrade_cm = $this->activity_format_grade($grade->finalgrade);
                    $this->maxgrade_cm = $this->activity_format_grade($activity->grademax);
                }
                $this->finalgrade = $grade->finalgrade;
            } else 
            {
                $this->maxgrade_cm = '';
                $this->finalgrade_cm = '-';
                $this->finalgrade = null;
            }
        }
    }
    
    protected function activity_format_grade($grade) 
    {
        return format_float($grade, $this->decimal);
    }
    
    public function __construct($sharer)
    {
        parent::__construct($sharer);
    }
    
    public function set_params(array $options = [])
    {
        global $DB;
        if ( ! empty($options) )
        {
            if ( isset($options['cmid']) )
            {
                $this->cmid = $options['cmid'];
            }
        } else
        {
            $cmid = optional_param('cmid', 0, PARAM_INT);
            if ( ! empty($cmid) )
            {
                $this->cmid = $cmid;
            }
        }
        if ( empty($this->courseid) )
        {
            $cm = $DB->get_record('course_modules', ['id' => $this->cmid]);
            if( ! empty($cm) )
            {
                $this->courseid = $cm->course;
            }
        }
        $this->set_block();
    }
    
    public function set_data($record)
    {
        $data = unserialize($record->data);
        if ( ! isset($data->cmid) && empty($data->cmid) )
        {
            throw new publication_exception('empty_cmid');
        }
        if ( ! isset($data->userid) && empty($data->userid) )
        {
            throw new publication_exception('empty_userid');
        }
        
        try
        {
            $array_data = get_course_and_cm_from_cmid($data->cmid);
        } catch (exception $e )
        {// Удален элемент
            throw new publication_exception($e);
        }
        
        $this->id = $record->id;
        $this->userid = $data->userid;
        $this->cmid = $data->cmid;
        $this->finalgrade_cm = $data->finalgrade;
        $this->maxgrade_cm = $data->maxgrade;
        $this->review = isset($data->review) ? $data->review : '';
        
        $this->timecreated = $record->timecreated;
        $this->course = $array_data[0];
        $this->courseid = $array_data[0]->id;
        $this->cm = $array_data[1]->get_course_module_record(true);
        $this->hash = $record->hash;
        $this->set_course_grade();
        $this->set_block();
    }
    
    public function set_meta_properties()
    {
        GLOBAL $CFG;
        
        $url = new moodle_url('/blocks/otshare/lp/lp.php?hash=' . $this->hash);
        
        $altersharing = $this->get_block_config('altersharing');
        if( ! empty($altersharing) )
        {
            $altercoursename = $this->get_block_config('altercoursename');
            if( ! empty($altercoursename) )
            {
                $sharingcoursename = $altercoursename;
            } else
            {
                $sharingcoursename = $this->course->fullname;
            }
            $caption = (string)$this->get_block_config('altersitename');
            $alterimgurl = $this->get_alter_img_url(
                $caption, 
                $sharingcoursename, 
                $this->get_grade_text(true, true), 
                $this->review
            );
            // Установим разметку для красивого шаринга
            $CFG->additionalhtmlhead = "
                <meta property=\"og:url\" content=\"$url\" />
                <meta property=\"og:type\" content=\"website\" />
                <meta property=\"og:image\" content=\"{$alterimgurl}\" />
            ";
        } else 
        {
            // Установим разметку для красивого шаринга
            $CFG->additionalhtmlhead = "
                <meta property=\"og:url\" content=\"$url\" />
                <meta property=\"og:title\" content=\"{$this->course->fullname}\" />
                <meta property=\"og:type\" content=\"website\" />
                <meta property=\"og:description\" content=\"{$this->get_grade_text(true)}\" />
                <meta property=\"og:image\" content=\"{$this->get_image_url()}\" />
            ";
        }
    }
    
    public function get_name()
    {
        return $this->name;
    }
}