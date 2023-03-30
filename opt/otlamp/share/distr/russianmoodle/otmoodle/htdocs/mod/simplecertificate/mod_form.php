<?php
if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}


require_once($CFG->dirroot . '/course/moodleform_mod.php');
require_once($CFG->libdir . '/filelib.php');


class mod_simplecertificate_mod_form extends moodleform_mod {

    public function definition() {
        global $CFG, $COURSE, $DB;


        $mform =& $this->_form;

        //General options
        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('certificatename', 'simplecertificate'), array('size'=>'64'));

        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEAN);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addHelpButton('name', 'certificatename', 'simplecertificate');

        $this->standard_intro_elements(get_string('intro', 'simplecertificate'));


        //--------------------------------------- Design Options----------------------------------------------------
        $mform->addElement('header', 'designoptions', get_string('designoptions', 'simplecertificate'));

        $maxbytes = get_max_upload_file_size($CFG->maxbytes, $COURSE->maxbytes);

        //Certificate image file
        $mform->addElement('filemanager', 'certificateimage', get_string('certificateimage','simplecertificate'), null,
                $this->get_filemanager_options_array());
        $mform->addHelpButton('certificateimage', 'certificateimage', 'simplecertificate');


        //Certificate Text HTML editor
        $mform->addElement('editor', 'certificatetext', get_string('certificatetext', 'simplecertificate'),
                simplecertificate_get_editor_options($this->context));
        $mform->setType('certificatetext',PARAM_RAW);
        $mform->addRule('certificatetext', get_string('error'), 'required', null, 'client');
        $mform->addHelpButton('certificatetext', 'ot_certificatetext', 'simplecertificate');

        //Certificate Width
        $mform->addElement('text', 'width', get_string('width', 'simplecertificate'), array('size'=>'5'));
        $mform->setType('width', PARAM_INT);
        $mform->setDefault('width', get_config('simplecertificate', 'width'));
        $mform->setAdvanced('width');
        $mform->addHelpButton('width', 'size', 'simplecertificate');


        //Certificate Height
        $mform->addElement('text', 'height', get_string('height', 'simplecertificate'), array('size'=>'5'));
        $mform->setType('height', PARAM_INT);
        $mform->setDefault('height', get_config('simplecertificate', 'height'));
        $mform->setAdvanced('height');
        $mform->addHelpButton('height', 'size', 'simplecertificate');

        //Certificate Position X
        $mform->addElement('text', 'certificatetextx', get_string('certificatetextx', 'simplecertificate'), array('size'=>'5'));
        $mform->setType('certificatetextx',PARAM_INT);
        $mform->setDefault('certificatetextx', get_config('simplecertificate', 'certificatetextx'));
        $mform->setAdvanced('certificatetextx');
        $mform->addHelpButton('certificatetextx', 'textposition', 'simplecertificate');

        //Certificate Position Y
        $mform->addElement('text', 'certificatetexty', get_string('certificatetexty', 'simplecertificate'), array('size'=>'5'));
        $mform->setType('certificatetexty',PARAM_INT);
        $mform->setDefault('certificatetexty', get_config('simplecertificate', 'certificatetexty'));
        $mform->setAdvanced('certificatetexty');
        $mform->addHelpButton('certificatetexty', 'textposition', 'simplecertificate');

        //Certificate Text Width
        $mform->addElement('text', 'certificatetextwidth', get_string('certificatetextwidth', 'simplecertificate'), array('size'=>'5'));
        $mform->setType('certificatetextwidth',PARAM_INT);
        $mform->setDefault('certificatetextwidth', get_config('simplecertificate', 'certificatetextwidth'));
        $mform->setAdvanced('certificatetextwidth');
        $mform->addHelpButton('certificatetextwidth', 'textsize', 'simplecertificate');

        //Certificate Text Height
        $mform->addElement('text', 'certificatetextheight', get_string('certificatetextheight', 'simplecertificate'), array('size'=>'5'));
        $mform->setType('certificatetextheight',PARAM_INT);
        $mform->setDefault('certificatetextheight', get_config('simplecertificate', 'certificatetextheight'));
        $mform->setAdvanced('certificatetextheight');
        $mform->addHelpButton('certificatetextheight', 'textsize', 'simplecertificate');

        //Certificate text border
        $mform->addElement('selectyesno', 'certificatetextborder', get_string('certificatetextborder', 'simplecertificate'));
        $mform->setDefault('certificatetextborder', 0);
        $mform->setAdvanced('certificatetextborder');
        $mform->addHelpButton('certificatetextborder', 'textborder', 'simplecertificate');

        //-----------------------------------------Second page
        $mform->addElement('header', 'secondpageoptions', get_string('secondpageoptions', 'simplecertificate'));
        //Enable back page text

        $mform->addElement('selectyesno', 'enablesecondpage', get_string('enablesecondpage', 'simplecertificate'));
        $mform->setDefault('enablesecondpage', get_config('simplecertificate', 'enablesecondpage'));
        $mform->addHelpButton('enablesecondpage', 'enablesecondpage', 'simplecertificate');

        //Certificate secondimage file
        $mform->addElement('filemanager', 'secondimage', get_string('secondimage','simplecertificate'), null,
                $this->get_filemanager_options_array());
        $mform->addHelpButton('secondimage', 'secondimage', 'simplecertificate');
        $mform->disabledIf('secondimage', 'enablesecondpage', 'eq', 0);

        //Certificate secondText HTML editor
        $mform->addElement('editor', 'secondpagetext', get_string('secondpagetext', 'simplecertificate'),
                simplecertificate_get_editor_options($this->context));
        $mform->setType('secondpagetext',PARAM_RAW);
        $mform->addHelpButton('secondpagetext', 'certificatetext', 'simplecertificate');
        $mform->disabledIf('secondpagetext', 'enablesecondpage', 'eq', 0);

        //Certificate Position X
        $mform->addElement('text', 'secondpagex', get_string('secondpagex', 'simplecertificate'), array('size'=>'5'));
        $mform->setType('secondpagex',PARAM_INT);
        $mform->setDefault('secondpagex', get_config('simplecertificate', 'certificatetextx'));
        $mform->setAdvanced('secondpagex');
        $mform->addHelpButton('secondpagex', 'secondtextposition', 'simplecertificate');
        $mform->disabledIf('secondpagex', 'enablesecondpage', 'eq', 0);

        //Certificate Position Y
        $mform->addElement('text', 'secondpagey', get_string('secondpagey', 'simplecertificate'), array('size'=>'5'));
        $mform->setType('secondpagey',PARAM_INT);
        $mform->setDefault('secondpagey', get_config('simplecertificate', 'certificatetexty'));
        $mform->setAdvanced('secondpagey');
        $mform->addHelpButton('secondpagey', 'secondtextposition', 'simplecertificate');
        $mform->disabledIf('secondpagey', 'enablesecondpage', 'eq', 0);

        //--------------------Variable options
        $mform->addElement('header', 'variablesoptions', get_string('variablesoptions', 'simplecertificate'));
        //Certificate Alternative Course Name
        $mform->addElement('text', 'coursename', get_string('coursename', 'simplecertificate'), array('size'=>'64'));
        $mform->setType('coursename', PARAM_TEXT);
        $mform->setAdvanced('coursename');
        $mform->addHelpButton('coursename', 'coursename', 'simplecertificate');

        //certificate code alternate format
        $mform->addElement('selectyesno', 'alternatecode', get_string('alternatecode', 'simplecertificate'));
        $mform->setDefault('alternatecode', 0);
        $mform->setAdvanced('alternatecode');
        $mform->addHelpButton('alternatecode', 'alternatecode', 'simplecertificate');

        //Certificate Outcomes
        $outcomeoptions = simplecertificate_get_outcomes();
        $mform->addElement('select', 'outcome', get_string('printoutcome', 'simplecertificate'), $outcomeoptions);
        $mform->setDefault('outcome', 0);
        $mform->addHelpButton('outcome', 'printoutcome', 'simplecertificate');

        //Certificate date options
        $mform->addElement('select', 'certdate', get_string('printdate', 'simplecertificate'), simplecertificate_get_date_options());
        $mform->setDefault('certdate', get_config('simplecertificate', 'certdate'));
        $mform->addHelpButton('certdate', 'printdate', 'simplecertificate');

        //Certificate date format
        $mform->addElement('text', 'certdatefmt', get_string('datefmt', 'simplecertificate'));
        $mform->setDefault('certdatefmt', '');
        $mform->setType('certdatefmt', PARAM_TEXT);
        $mform->addHelpButton('certdatefmt', 'datefmt', 'simplecertificate');
        $mform->setAdvanced('certdatefmt');

        //Certificate timestart date format
        $mform->addElement('text', 'timestartdatefmt', get_string('timestartdatefmt', 'simplecertificate'));
        $mform->setDefault('timestartdatefmt', '');
        $mform->setType('timestartdatefmt', PARAM_TEXT);
        $mform->addHelpButton('timestartdatefmt', 'timestartdatefmt', 'simplecertificate');
        $mform->setAdvanced('timestartdatefmt');

        //Certificare grade Options
        $mform->addElement('select', 'certgrade', get_string('printgrade', 'simplecertificate'), simplecertificate_get_grade_options());
        $mform->setDefault('certgrade', 0);
        $mform->addHelpButton('certgrade', 'printgrade', 'simplecertificate');

        //Certificate grade format
        $gradeformatoptions = [
            1 => get_string('gradepercent', 'simplecertificate'),
            2 => get_string('gradepoints', 'simplecertificate'),
            3 => get_string('gradeletter', 'simplecertificate'),
            4 => get_string('gradepointsfrommax', 'simplecertificate'),
            5 => get_string('gradepointsfrommax_percent', 'simplecertificate'),
            6 => get_string('gradepointsfrommax_letter', 'simplecertificate'),
            7 => get_string('gradepointsfrommax_percent_letter', 'simplecertificate')
        ];
        $mform->addElement('select', 'gradefmt', get_string('gradefmt', 'simplecertificate'), $gradeformatoptions);
        $mform->setDefault('gradefmt', 0);
        $mform->addHelpButton('gradefmt', 'gradefmt', 'simplecertificate');

        //Certificate grade decimals
        $gradedecimalsoptions = [
            0 => '0',
            1 => '1',
            2 => '2',
            3 => '3',
            4 => '4'
        ];
        $mform->addElement('select', 'gradedecimals', get_string('gradedecimals', 'simplecertificate'), $gradedecimalsoptions);
        $mform->setDefault('gradedecimals', 0);
        $mform->addHelpButton('gradedecimals', 'gradedecimals', 'simplecertificate');

        //Certificate forced language
        $forcelanguageoptions = ['' => get_string('forcelanguage_auto', 'simplecertificate')] + get_string_manager()->get_list_of_translations();
        $mform->addElement('select', 'forcelanguage', get_string('forcelanguage', 'simplecertificate'), $forcelanguageoptions);
        $mform->setDefault('forcelanguage', 0);
        $mform->addHelpButton('forcelanguage', 'forcelanguage', 'simplecertificate');

        //Certificate module grade format
        $mform->addElement('select', 'grademodfmt', get_string('grademodfmt', 'simplecertificate'), $gradeformatoptions);
        $mform->setDefault('grademodfmt', 0);
        $mform->addHelpButton('grademodfmt', 'grademodfmt', 'simplecertificate');

        //QR code
        $mform->addElement('selectyesno', 'printqrcode', get_string('printqrcode', 'simplecertificate'));
        $mform->setDefault('printqrcode', get_config('simplecertificate', 'printqrcode'));
        $mform->addHelpButton('printqrcode', 'printqrcode', 'simplecertificate');

        $mform->addElement('text', 'codex', get_string('codex', 'simplecertificate'), array('size'=>'5'));
        $mform->setType('codex',PARAM_INT);
        $mform->setDefault('codex', get_config('simplecertificate', 'codex'));
        $mform->setAdvanced('codex');
        $mform->addHelpButton('codex', 'qrcodeposition', 'simplecertificate');

        $mform->addElement('text', 'codey', get_string('codey', 'simplecertificate'), array('size'=>'5'));
        $mform->setType('codey',PARAM_INT);
        $mform->setDefault('codey', get_config('simplecertificate', 'codey'));
        $mform->setAdvanced('codey');
        $mform->addHelpButton('codey', 'qrcodeposition', 'simplecertificate');

        $mform->addElement('selectyesno', 'qrcodefirstpage', get_string('qrcodefirstpage', 'simplecertificate'));
        $mform->setDefault('qrcodefirstpage', get_config('simplecertificate', 'qrcodefirstpage'));
        $mform->addHelpButton('qrcodefirstpage', 'qrcodefirstpage', 'simplecertificate');



        //-------------------------------Issue options----------------------------------

        $mform->addElement('header', 'issueoptions', get_string('issueoptions', 'simplecertificate'));

        // Autoissue on user_graded
        $mform->addElement('selectyesno', 'issueongrade', get_string('issueongrade', 'simplecertificate'));
        $mform->setDefault('issueongrade', 0);
        $mform->addHelpButton('issueongrade', 'issueongrade', 'simplecertificate');

        // Autoissue on otcourselogic_state_switched
        $mform->addElement('selectyesno', 'issueonotcl', get_string('issueonotcl', 'simplecertificate'));
        $mform->setDefault('issueonotcl', 0);
        $mform->addHelpButton('issueonotcl', 'issueonotcl', 'simplecertificate');

        // otcourselogic to autoissue
        $modinfo = get_fast_modinfo($COURSE->id);
        $otclcms = $modinfo->get_instances_of('otcourselogic');
        $otcourselogics = [0 => get_string('choose_otcourselogic', 'simplecertificate')];
        foreach($otclcms as $otclcm)
        {
            /** @var cm_info $cm */
            $otcourselogics[$otclcm->id] = $otclcm->get_formatted_name().' ['.$otclcm->id.']';
        }
        $mform->addElement('select', 'otcltoissue', get_string('otcltoissue', 'simplecertificate'), $otcourselogics);
        $mform->setDefault('otcltoissue', 0);
        $mform->setType('otcltoissue', PARAM_TEXT);
        $mform->hideIf('otcltoissue', 'issueonotcl', 'eq', '0');
        $mform->addHelpButton('otcltoissue', 'otcltoissue', 'simplecertificate');

        // send notification while autoissue
        $mform->addElement('selectyesno', 'autoissuenotification', get_string('autoissuenotification', 'simplecertificate'));
        $mform->setDefault('autoissuenotification', 1);
        $mform->addHelpButton('autoissuenotification', 'autoissuenotification', 'simplecertificate');

        // modalundelivered
        $mform->addElement('selectyesno', 'modalundelivered', get_string('modalundelivered', 'simplecertificate'));
        $mform->setDefault('modalundelivered', 0);
        $mform->addHelpButton('modalundelivered', 'modalundelivered', 'simplecertificate');

        //Email to teachers ?
        $mform->addElement('selectyesno', 'emailteachers', get_string('emailteachers', 'simplecertificate'));
        $mform->setDefault('emailteachers', 0);
        $mform->addHelpButton('emailteachers', 'emailteachers', 'simplecertificate');

        //Email Others
        $mform->addElement('text', 'emailothers', get_string('emailothers', 'simplecertificate'), array('size'=>'40', 'maxsize'=>'200'));
        $mform->setType('emailothers', PARAM_TEXT);
        $mform->addHelpButton('emailothers', 'emailothers', 'simplecertificate');

        //Email From
        $mform->addElement('text', 'emailfrom', get_string('emailfrom', 'simplecertificate'), array('size'=>'40', 'maxsize'=>'200'));
        $mform->setDefault('emailfrom', $CFG->supportname);
        $mform->setType('emailfrom', PARAM_EMAIL);
        $mform->addHelpButton('emailfrom', 'emailfrom', 'simplecertificate');
        $mform->setAdvanced('emailfrom');

        //Delivery Options (Email, Download,...)
        $deliveryoptions = array( 0 => get_string('openbrowser', 'simplecertificate'), 1 => get_string('download', 'simplecertificate'), 2 => get_string('emailcertificate', 'simplecertificate'), 3 => get_string('nodelivering','simplecertificate'));
        $mform->addElement('select', 'delivery', get_string('delivery', 'simplecertificate'), $deliveryoptions);
        $mform->setDefault('delivery', 0);
        $mform->addHelpButton('delivery', 'delivery', 'simplecertificate');

        // Shelf life
        $shelflifelabel = get_string('shelf_life', 'simplecertificate');
        $shelflifeoptions = ['defaultunit' => DAYSECS, 'optional' => true];
        $mform->addElement('duration', 'shelflife', $shelflifelabel, $shelflifeoptions);
        $mform->addHelpButton('shelflife', 'shelf_life', 'simplecertificate');
        $mform->setType('shelflife', PARAM_INT);
        // Default shelf life
        $settingshelflife = get_config('simplecertificate', 'setting_shelf_life');
        if (!empty($settingshelflife))
        {
            $mform->setDefault('shelflife', $settingshelflife);
        }

        //Report Cert
        //TODO acredito que seja para verificar o certificado pelo código, se for isto pode remover.
        $reportfile = "$CFG->dirroot/simplecertificates/index.php";
        if (file_exists($reportfile)) {
            $mform->addElement('selectyesno', 'reportcert', get_string('reportcert', 'simplecertificate'));
            $mform->setDefault('reportcert', 0);
            $mform->addHelpButton('reportcert', 'reportcert', 'simplecertificate');
        }

        $this->standard_coursemodule_elements();
        $this->add_action_buttons();
    }

    /**
     * Prepares the form before data are set
     *
     * Additional wysiwyg editor are prepared here, the introeditor is prepared automatically by core.
     * Grade items are set here because the core modedit supports single grade item only.
     *
     * @param array $data to be set
     * @return void
     */
    public function data_preprocessing(&$data) {
        global $CFG;
        require_once(dirname(__FILE__) . '/locallib.php');
        parent::data_preprocessing($data);
        if ($this->current->instance) {
            // editing an existing certificate - let us prepare the added editor elements (intro done automatically), and files

            //First Page

            //Get firstimage
            $imagedraftitemid = file_get_submitted_draft_itemid('certificateimage');
            //Get firtsimage filearea information
            $imagefileinfo = simplecertificate::get_certificate_image_fileinfo($this->context);
            file_prepare_draft_area($imagedraftitemid, $imagefileinfo['contextid'], $imagefileinfo['component'], $imagefileinfo['filearea'], $imagefileinfo['itemid'],
                         $this->get_filemanager_options_array());

            $data['certificateimage'] = $imagedraftitemid;

            //Prepare certificate text
            $data['certificatetext'] = array('text' =>$data['certificatetext'], 'format'=> FORMAT_HTML);

            //Second page
            //Get Back image
            $secondimagedraftitemid = file_get_submitted_draft_itemid('secondimage');
            //Get secondimage filearea info
            $secondimagefileinfo = simplecertificate::get_certificate_secondimage_fileinfo($this->context);
            file_prepare_draft_area($secondimagedraftitemid, $secondimagefileinfo['contextid'], $secondimagefileinfo['component'], $secondimagefileinfo['filearea'], $secondimagefileinfo['itemid'], $this->get_filemanager_options_array());
            $data['secondimage'] = $secondimagedraftitemid;

            //Get backpage text
            if (!empty($data['secondpagetext'])) {
                $data['secondpagetext'] = array('text' =>$data['secondpagetext'], 'format'=> FORMAT_HTML);
            } else {
                $data['secondpagetext'] = array('text' =>'', 'format'=> FORMAT_HTML);
            }
        } else { //Load default
            $data['certificatetext'] = array('text' =>'', 'format'=> FORMAT_HTML);
            $data['secondpagetext'] = array('text' =>'', 'format'=> FORMAT_HTML);
        }

        //completion rules
        $data['completiontimeenabled'] = !empty($data['requiredtime']) ? 1 : 0;


    }

    public function add_completion_rules() {
        $mform =& $this->_form;

        $group=array();

        $group[] =& $mform->createElement('checkbox', 'completiontimeenabled', ' ', get_string('coursetimereq', 'simplecertificate'));
        $group[] =& $mform->createElement('text', 'requiredtime','', array('size'=>'3'));
        $mform->setType('requiredtime',PARAM_INT);
        $mform->addGroup($group, 'completiontimegroup', get_string('coursetimereq','simplecertificate'), array(' '), false);

        $mform->addHelpButton('completiontimegroup', 'coursetimereq', 'simplecertificate');
        $mform->disabledIf('requiredtime','completiontimeenabled','notchecked');

        return array('completiontimegroup');
    }

    function completion_rule_enabled($data) {
        return (!empty($data['completiontimeenabled']) && $data['requiredtime']!=0);
    }

    public function get_data() {
        global $CFG;
        require_once(dirname(__FILE__) . '/locallib.php');

        $data = parent::get_data();

        if (empty($data)) {
            return false;
        }

        //For Completion Rules
        if (!empty($data->completionunlocked)) {
            // Turn off completion settings if the checkboxes aren't ticked
            $autocompletion = !empty($data->completion) && $data->completion==COMPLETION_TRACKING_AUTOMATIC;
            if (empty($data->completiontimeenabled) || !$autocompletion) {
                $data->requiredtime = 0;
            }
        }
        //File manager always creata a Files folder, so certimages is never empty.
        //I must check if it has a file or it's only a empty files folder reference
        if (isset($data->certificateimage) && !empty($data->certificateimage)) {
            if (!$this->check_has_files('certificateimage')) {
                $data->certificateimage = null;
            }
        }

        if (isset($data->secondimage) && !empty($data->secondimage)) {
            if (!$this->check_has_files('secondimage')) {
                $data->secondimage = null;
            }
        }

        return $data;
    }

    /**
     * Some basic validation
     *
     * @param $data
     * @param $files
     * @return array
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        // Check that the required time entered is valid
        if ((isset($data['requiredtime']) && $data['requiredtime'] < 0)) {
            $errors['requiredtime'] = get_string('requiredtimenotvalid', 'simplecertificate');
        }

        if (!empty($data['issueonotcl']) && empty($data['otcltoissue']))
        {
            $errors['otcltoissue'] = get_string('otcltoissue_required', 'simplecertificate');
        }

        return $errors;
    }

    private function check_has_files($itemname) {
        global $USER;

        $draftitemid = file_get_submitted_draft_itemid($itemname);
        file_prepare_draft_area($draftitemid, $this->context->id, 'mod_simplecertificate', 'imagefilecheck', null,
                                $this->get_filemanager_options_array());

        // Get file from users draft area.
        $usercontext = context_user::instance($USER->id);
        $fs = get_file_storage();
        $files = $fs->get_area_files($usercontext->id, 'user', 'draft', $draftitemid, 'id', false);

        return (count($files) > 0);
    }

    private function get_filemanager_options_array () {
        return array('subdirs' => 0, 'maxbytes' => 0, 'maxfiles' => 1,
                'accepted_types' => array('image'));
    }

}