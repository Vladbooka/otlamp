<?php

/**
 * Поделиться ссылкой
 *
 * @package    block
 * @subpackage otshare
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_otshare\publication_types\types;

use moodle_url;
use stdClass;
use html_writer;
use block_otshare\publication_types\publication_base as publication_parent;
use block_otshare\exception\publication as publication_exception;

class result_course extends publication_parent
{
    protected $name = 'result_course';
    protected $finalgrade_course;
    protected $maxgrade_course;

    protected function check_properties()
    {
        if ( empty($this->courseid) )
        {
            throw new publication_exception('invalid_properties');
        }
        
        return true;
    }
    
    protected function get_insert_data()
    {
        // Сериализованные данные
        $data = new stdClass();
        $data->courseid = $this->courseid;
        $data->type = $this->name;
        $data->userid = $this->userid;
        $data->sn = $this->get_sharer_name();
        $this->set_course_grade();
        $data->finalgrade = $this->finalgrade;
        $data->maxgrade = $this->maxgrade;
        
        return $data;
    }
    
    protected function get_grade_text($short = false, $nowrapp = false)
    {
        // Дефолтные параметры
        $html = '';

        if ( empty($short) )
        {
            $html .= get_string('get_grade_course', 'block_otshare');
            $html .= html_writer::div($this->finalgrade_course, 'block_otshare_lp_grade_course');
            $html .= get_string('for_course', 'block_otshare');
            $html .= html_writer::start_div('block_otshare_lp_name_course');
            $html .= html_writer::span($this->course->fullname);
            $html .= html_writer::end_div();
        } else 
        {
            if( ! empty($nowrapp) )
            {
                $html .= $this->finalgrade_course . '/' . $this->maxgrade_course;
            } else 
            {
                $html .= get_string('get_final_grade', 'block_otshare') . ' (' . $this->finalgrade_course . '/' . $this->maxgrade_course . ')';
            }
        }
        
        return $html;
    }
    
    public function __construct($sharer)
    {
        parent::__construct($sharer);
    }
    
    public function set_params(array $options = [])
    {
        if ( ! empty($options) )
        {
            if ( isset($options['courseid']) )
            {
                $this->courseid = $options['courseid'];
            }
        } else
        {
            $courseid = optional_param('courseid', 0, PARAM_INT);
            if ( ! empty($courseid) )
            {
                $this->courseid = $courseid;
                $this->course = get_course($courseid);
            }
        }
        $this->set_block();
    }
    
    public function set_data($record)
    {
        $data = unserialize($record->data);
        if ( ! isset($data->courseid) && empty($data->courseid) )
        {
            throw new publication_exception('empty_courseid');
        }
        if ( ! isset($data->userid) && empty($data->userid) )
        {
            throw new publication_exception('empty_userid');
        }
        
        $this->id = $record->id;
        $this->timecreated = $record->timecreated;
        $this->courseid = $data->courseid;
        $this->userid = $data->userid;
        $this->course = get_course($data->courseid);
        $this->hash = $record->hash;
        $this->finalgrade_course = $data->finalgrade;
        $this->maxgrade_course = $data->maxgrade;
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
                ''
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