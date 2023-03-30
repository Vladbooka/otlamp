<?php

/**
 * Поделиться ссылкой
 *
 * @package    block
 * @subpackage otshare
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_otshare\publication_types;

use block_otshare\publication_types\types\link;
use block_otshare\publication_types\types\result_course;
use block_otshare\publication_types\types\result_module;

use block_otshare\fb;
use block_otshare\gp;
use block_otshare\ok;
use block_otshare\tw;
use block_otshare\vk;

use html_writer;
use block_otshare\exception\publication as publication_exception;

class publication_builder
{
    private $available_sn = ['fb', 'gp', 'ok', 'tw', 'vk'];
    private $available_pl = ['link', 'result_course', 'result_module'];
    
    
    protected $sn;
    protected $pl;
    protected $hash;
    
    public function set_sn($sn)
    {
        if ( ! is_string($sn) || ! in_array($sn, $this->available_sn) )
        {
            throw new publication_exception('invalid_builder_input_type');
        }
        
        $sn = "block_otshare\\" . $sn;
        $this->sn = new $sn(
                '',
                '',
                ''
                );
    }
    
    public function set_pl($pl)
    {
        if ( ! is_string($pl) || ! in_array($pl, $this->available_pl) )
        {
            throw new publication_exception('invalid_builder_input_type');
        }

        if ( empty($this->sn) )
        {
            throw new publication_exception('empty_sn');
        }
        
        $pl = "block_otshare\publication_types\\types\\" . $pl;
        $this->pl = new $pl($this->sn);
    }
    
    public function set_hash($hash)
    {
        $this->hash = $hash;
    }
    
    public function get_publicator()
    {
        
        if ( empty($this->pl) || empty($this->sn) )
        {
            throw new publication_exception('empty_properties');
        }

        return $this->pl;
    }
    
    public function get_publicator_by_hash()
    {
        GLOBAL $DB;
        
        if ( empty($this->hash) )
        {
            throw new publication_exception('empty_hash');
        }
        
        $record = $DB->get_record('block_otshare_shared_data', ['hash' => $this->hash]);
        
        if ( empty($record) )
        {
            throw new publication_exception('record_doesnt_exists');
        }
        
        $data = unserialize($record->data);
        
        // Сбилдим публикатора по полученным данным
        $this->set_sn($data->sn);
        $this->set_pl($data->type);
        $this->set_publicator_data($record);
        
        // Вернем объект публикатора
        return $this->pl;
    }
    
    public function get_require_registration_info()
    {
        $html = '';
        
        $html .= html_writer::start_div('loginbox clearfix onecolumn block_otshare_lp_require_login_wrapper');
        $html .= html_writer::div('<h2>' . get_string('should_reg', 'block_otshare') . '</h2>');
        $html .= html_writer::div('<a class="btn" href="/login/index.php">' . get_string('registration', 'block_otshare')) . '</a>';
        $html .= html_writer::end_div();
        
        return $html;
    }
    
    protected function set_publicator_data($data)
    {
        if ( empty($this->pl) )
        {
            throw new publication_exception('empty_pl');
        }
        
        $this->pl->set_data($data);
    }
}
