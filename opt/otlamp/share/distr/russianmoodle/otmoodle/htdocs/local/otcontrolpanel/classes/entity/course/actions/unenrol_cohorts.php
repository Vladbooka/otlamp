<?php
namespace local_otcontrolpanel\entity\course\actions;


class unenrol_cohorts extends \local_otcontrolpanel\action\abstract_action {

    public function definition_after_data(&$actionform, &$mform)
    {
        global $DB;
        $actionform->set_header(get_string('e_course_a_unenrol_cohorts', 'local_otcontrolpanel'));



        $courses = [];

        // найдем подписки на курсы, имеющие синхронизации с глобальными группами
        $courses = $actionform->ids ?? [];
        list($sqlin, $params) = $DB->get_in_or_equal($courses, SQL_PARAMS_NAMED, 'course');
        $params['enrol'] = 'cohort';
        $enrolcohorts = $DB->get_records_select('enrol', 'enrol=:enrol AND courseid '.$sqlin, $params, '', 'DISTINCT customint1');
        if (!empty($enrolcohorts))
        {
            $cohortids = array_column($enrolcohorts, 'customint1');
            $cohortrecords = $DB->get_records_list('cohort', 'id', $cohortids);
            $cohorts = array_combine(array_keys($cohortrecords), array_column($cohortrecords, 'name'));
        }

        // Курсы, от которых надо отписать глобальную группу
        $cohortslabel = get_string('e_course_a_unenrol_cohorts_fe_cohorts', 'local_otcontrolpanel');
        $mform->addElement('autocomplete', 'cohorts', $cohortslabel, $cohorts, ['multiple' => true]);

        $delgrouplabel = get_string('e_course_a_unenrol_cohorts_fe_delete_empty_group', 'local_otcontrolpanel');
        $mform->addElement('advcheckbox', 'delete_empty_group', $delgrouplabel);

        $actionform->add_action_buttons(false,
            get_string('e_course_a_unenrol_cohorts_fe_submit', 'local_otcontrolpanel'));
    }

    public function process_form_data(&$actionform, $formdata)
    {
        global $DB;


        if ($formdata)
        {// убедились, что дошли до шага, когда уже выбраны и отправлены курсы, для которых требуется отписка от курсов

            if (empty($formdata->cohorts))
            {
                return;
            }

            // данные из форм
            $cohorts = $formdata->cohorts ?? [];
            $courses = $actionform->ids ?? [];

            $params = ['enrol' => 'cohort'];
            list($cohortssqlin, $cohortsparams) = $DB->get_in_or_equal($cohorts, SQL_PARAMS_NAMED, 'cohort');
            $params = array_merge($params, $cohortsparams);
            list($coursessqlin, $coursesparams) = $DB->get_in_or_equal($courses, SQL_PARAMS_NAMED, 'course');
            $params = array_merge($params, $coursesparams);
            $sql = 'enrol=:enrol AND customint1 '.$cohortssqlin.' AND courseid '.$coursessqlin;
            $enrols = $DB->get_records_select('enrol', $sql, $params);

            // плагин синхронизации с ГГ
            $plugin = enrol_get_plugin('cohort');

            foreach($enrols as $enrol)
            {
                try {

                    $groupid = $enrol->customint2;
                    if (!is_number($groupid) || $groupid <= 0)
                    {
                        $groupid = null;
                    }

                    $plugin->delete_instance($enrol);

                    if (!empty($formdata->delete_empty_group) && !is_null($groupid))
                    {
                        $groupmembers = groups_get_members($groupid, 'u.id');
                        if (empty($groupmembers))
                        {
                            groups_delete_group($groupid);
                        }
                    }

                    $course = get_course($enrol->courseid);
                    $cohort = $DB->get_record('cohort', ['id' => $enrol->customint1]);
                    $a = (object)[
                        'instanceid' => $enrol->id,
                        'courseid' => $enrol->courseid,
                        'coursefullname' => $course->fullname,
                        'cohortid' => $enrol->customint1,
                        'cohortname' => $cohort->name,
                        'groupid' => $groupid
                    ];
                    $message = get_string('e_course_a_unenrol_cohorts_report_message', 'local_otcontrolpanel', $a);
                    $actionform->add_final_report_message($message);

                } catch(\Exception $ex)
                {
                    $message = get_string('e_course_a_unenrol_cohorts_err', 'local_otcontrolpanel', $ex->getMessage());
                    $actionform->add_final_report_message($message);
                }
            }
        }
    }
    public function validation(&$actionform, $data, $files)
    {
        $errors = [];

        $cohorts = $actionform->optional_param('cohorts', null, PARAM_RAW);
        if (!is_null($cohorts) && empty($data['cohorts']))
        {
            $errors['cohorts'] = get_string('e_course_a_unenrol_cohorts_err_nocohorts', 'local_otcontrolpanel');
        }
        return $errors;

    }



}