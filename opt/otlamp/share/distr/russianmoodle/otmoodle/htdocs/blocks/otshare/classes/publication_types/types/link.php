<?php

/**
 * Поделиться ссылкой
 *
 * @package    block
 * @subpackage otshare
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_otshare\publication_types\types;

use stdClass;
use block_otshare\publication_types\publication_base as publication_parent;
use block_otshare\exception\publication as publication_exception;

class link extends publication_parent
{
    protected $name = 'result_course';
    protected $url;
    
    protected function normalize_url($url)
    {
        throw new publication_exception('forbidden');
    }
    
    protected function get_insert_data ()
    {
        throw new publication_exception('forbidden');
    }
    
    protected function get_grade_text ()
    {
        throw new publication_exception('forbidden');
    }
    
    protected function check_properties()
    {
        if ( empty($this->url) || empty($this->instance) )
        {
            throw new publication_exception('invalid_properties');
        }
        
        return true;
    }
    
    protected function get_insert_row()
    {
        GLOBAL $USER;
        
        $insert_obj = new stdClass();
        
        return $insert_obj;
    }
    
    public function __construct($sharer)
    {
        parent::__construct($sharer);
    }
    
    public function get_name()
    {
        return $this->name;
    }

    public function set_data ($data)
    {
        throw new publication_exception('forbidden');
    }
    
    public function set_params(array $options = [])
    {
        if ( ! empty($options) )
        {
            if ( isset($options['url']) )
            {
                $this->url = $options['url'];
            }
        } else
        {
            $url = optional_param('url', '', PARAM_URL);
            if ( ! empty($url) )
            {
                $this->url = $url;
            }
        }
    }
    public function set_meta_properties ()
    {
        throw new publication_exception('forbidden');
    }

}