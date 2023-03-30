<?php
namespace block_mastercourse;

abstract class eduportal {
    
    protected $context;
    protected $coursecontext;
    static protected $serviceshortname;
    protected $statuscodes;
    static protected $in_progress=[];
    static public $enabled=false;
    
    abstract public function get_available_status_codes($coursepubstatus, $manual=true);
    
    /**
     * context $context
     * @param \context $context
     */
    public function __construct($context)
    {
        $this->context = $context;
        $this->coursecontext = $context->get_course_context();
        $currentstatuscode = $this->get_current_statuscode();
        if (is_null($currentstatuscode))
        {
            $available = $this->get_available_status_codes($currentstatuscode);
            $this->set_new_status(reset($available), true);
        }
    }
    
    protected function get_available_statuses($coursepubstatus, $manual=true)
    {
        $available = $this->get_available_status_codes($coursepubstatus, $manual);
        
        return array_combine(
            array_values($available),
            array_map('static::get_status', $available)
        );
    }
    
    protected function get_pub_record()
    {
        global $DB;
        $result = $DB->get_record('mastercourse_publication', [
            'courseid' => $this->coursecontext->instanceid,
            'service' => static::$serviceshortname
        ]);
        return $result;
    }
    
    protected function get_current_statuscode()
    {
        // получить текущее значение
        $result = $this->get_pub_record($this->coursecontext->instanceid);
        
        return $result->status ?? null;
    }
    
    /**
     * Массив возможных статусов
     *
     * @return array код статуса => языковая строка
     */
    protected function get_all_statuses()
    {
        $statuses = [];
        foreach($this->statuscodes as $statuscode)
        {
            $statuses[$statuscode] = static::get_status($statuscode);
        }
        return $statuses;
    }
    
    public static function get_statuses_in_progress()
    {
        $statuses = [];
        foreach(static::$in_progress as $statuscode)
        {
            $statuses[$statuscode] = static::get_status($statuscode);
        }
        return $statuses;
    }
    
    
    protected static function get_status($code)
    {
        return get_string('service__'.static::$serviceshortname.'__status__'.$code, 'block_mastercourse');
    }
    
    public function get_service_name()
    {
        return get_string('service__'.static::$serviceshortname, 'block_mastercourse');
    }
    
    public function form_publication_definition(\MoodleQuickForm &$mform)
    {
        $mform->addElement('header', static::$serviceshortname, $this->get_service_name());
        
        $pubrec = $this->get_pub_record();
        $currentstatuscode = $pubrec->status ?? '';
        $currentstatus = static::get_status($currentstatuscode);
        
        $mform->addElement(
            'static',
            'currentstatus',
            get_string('form_publication__field__current_status', 'block_mastercourse'),
            \html_writer::div($currentstatus) . \html_writer::div($pubrec->statusinfo ?? '')
        );
        
        
        $available = $this->get_available_statuses($currentstatuscode);
        // select статусов
        $select = $mform->createElement(
            'select',
            'status',
            get_string('form_publication__field__new_status', 'block_mastercourse')
        );
        $select->addOption(
            get_string('current_status_wrapper', 'block_mastercourse', $currentstatus),
            $currentstatuscode,
            ['disabled' => 'disabled']
        );
        foreach($available as $statuscode => $status)
        {
            $select->addOption($status, $statuscode);
        }
        $mform->addElement($select);
        $mform->setDefault('status', $currentstatuscode);
        
        $mform->disabledIf('submitbutton', 'status', 'eq', $currentstatuscode);
    }
    
    /**
     * Устанавливает новый статус
     *
     * @param string $newstatuscode новый статус
     * @throws \moodle_exception
     */
    public function set_new_status($newstatuscode, $quiet=false, $manual=true, $statusinfo=null)
    {
        global $DB;
        $currentstatuscode = $this->get_current_statuscode($this->coursecontext->instanceid);
        $available = $this->get_available_status_codes($currentstatuscode, $manual);
        
        if (in_array($newstatuscode, $available))
        {
            $pubrecord = $this->get_pub_record();
            if (!empty($pubrecord))
            {
                if (!is_null($newstatuscode))
                {
                    $pubrecord->status = $newstatuscode;
                }
                $pubrecord->lastupdate = time();
                if (!is_null($statusinfo))
                {
                    $pubrecord->statusinfo = $statusinfo;
                }
                $DB->update_record('mastercourse_publication', $pubrecord);
            } else
            {
                $pubrecord = new \stdClass();
                $pubrecord->service = static::$serviceshortname;
                $pubrecord->courseid = $this->coursecontext->instanceid;
                $pubrecord->status = $newstatuscode;
                $pubrecord->lastupdate = time();
                $pubrecord->statusinfo = $statusinfo;
                $DB->insert_record('mastercourse_publication', $pubrecord);
            }
            
            if (!$quiet)
            {
                $eventdata = [
                    'oldstatus' => $currentstatuscode,
                    'newstatus' => $newstatuscode,
                    'courseid' => $this->coursecontext->instanceid,
                    'service' => static::$serviceshortname
                ];
                // сгенерировать новое событие "статус публикации курса изменён",
                // прокинуть через него предыдущий статус, текущий, идентификатор курса
                \block_mastercourse\event\course_pub_status_changed::create([
                    'context' => $this->context,
                    'other' => $eventdata
                ])->trigger();
            }
        } else {
            throw new \moodle_exception(get_string('form_publication__error__status_not_available', 'block_mastercourse'));
        }
    }
    
    public function form_publication_process($data)
    {
        if (has_capability('block/mastercourse:manage_publication', $this->context))
        {
            $this->set_new_status($data->status, false, true, '');
        }
        redirect(new \moodle_url('/blocks/mastercourse/publication.php', ['ctx' => $this->context->id]));
    }
    
    public function get_service_shortname()
    {
        return static::$serviceshortname;
    }
}