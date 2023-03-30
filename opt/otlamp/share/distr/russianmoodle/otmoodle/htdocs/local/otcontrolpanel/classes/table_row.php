<?php
namespace local_otcontrolpanel;

class table_row {
    
    private $record;
    
    public function __construct(\stdClass $record)
    {
        $this->record = $record;
    }
    
    public function get_record()
    {
        return $this->record;
    }
}
