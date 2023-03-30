<?php
namespace local_otcontrolpanel\entity\course\actions;

use context_system;

class enrol_cohorts extends \local_otcontrolpanel\action\abstract_action {

    /**
     * {@inheritDoc}
     * @see \local_otcontrolpanel\action\abstract_action::definition_after_data()
     */
    public function definition_after_data(&$actionform, &$mform)
    {
        $actionform->set_header(get_string('e_course_a_enrol_cohorts', 'local_otcontrolpanel'));


        // Глобальные группы, для которых надо настроить синхронизацию с курсом
        $cohortslabel = get_string('e_course_a_enrol_cohorts_fe_cohorts', 'local_otcontrolpanel');
        $mform->addElement('cohort', 'cohorts', $cohortslabel, ['multiple' => true]);


        // Плагин синхронизации с ГГ
        $plugin = enrol_get_plugin('cohort');

        $syscontext = context_system::instance();
        // Роли, доступные для записи, дефолтные, поскольку на данном этапе контект курса не доступен
        $roles = [0 => get_string('none')] + get_default_enrol_roles($syscontext);
        $rolelabel = get_string('e_course_a_enrol_cohorts_fe_roleid', 'local_otcontrolpanel');
        $mform->addElement('select', 'roleid', $rolelabel, $roles);
        $mform->setDefault('roleid', $plugin->get_config('roleid'));

        // режим групп курса: можно не использовать группу, а можно создать с таким же именем
        $groupmodelabel = get_string('e_course_a_enrol_cohorts_fe_groupmode', 'local_otcontrolpanel');
        $groupmodes = [
            'nogroup' => get_string('e_course_a_enrol_cohorts_fe_groupmode_nogroup', 'local_otcontrolpanel'),
            'samename' => get_string('e_course_a_enrol_cohorts_fe_groupmode_samename', 'local_otcontrolpanel'),
        ];
        $mform->addElement('select', 'groupmode', $groupmodelabel, $groupmodes);
        $mform->setDefault('groupmode', 'samename');

        $creategrouplabel = get_string('e_course_a_enrol_cohorts_fe_creategroup', 'local_otcontrolpanel');
        $mform->addElement('advcheckbox', 'creategroup', $creategrouplabel);
        $mform->setDefault('creategroup', '1');
        $mform->disabledIf('creategroup', 'groupmode', 'eq', 'nogroup');

        $actionform->add_action_buttons(false,
            get_string('e_course_a_enrol_cohorts_fe_submit', 'local_otcontrolpanel'));
    }

    /**
     * {@inheritDoc}
     * @see \local_otcontrolpanel\action\abstract_action::process_form_data()
     */
    public function process_form_data(&$actionform, $formdata)
    {
        global $DB;

        if ($formdata)
        {// убедились, что дошли до шага, когда уже выбраны и отправлены ГГ, для которых требуется синхронизация

            if (empty($formdata->cohorts))
            {
                return;
            }

            $syscontext = context_system::instance();

            // данные из форм
            $courses = $actionform->ids ?? [];
            $cohorts = $formdata->cohorts ?? [];
            $groupmode = $formdata->groupmode ?? 'samename';
            $creategroup = $formdata->creategroup ?? true;
            $roleid = $formdata->roleid ?? 0;
            $roles = [0 => get_string('none')] + get_default_enrol_roles($syscontext);

            // плагин синхронизации с ГГ
            $plugin = enrol_get_plugin('cohort');

            foreach($courses as $courseid)
            {
                $course = get_course($courseid);
                if ($course->id == SITEID) {
                    $a = (object)[
                        'courseid' => $courseid,
                        'coursefullname' => $course->fullname,
                    ];
                    $message = get_string('e_course_a_enrol_cohorts_err_no_site', 'local_otcontrolpanel', $a);
                    $actionform->add_final_report_message($message);
                    continue;
                }

                $coursecontext = \context_course::instance($courseid);
                $parentcontexts = $coursecontext->get_parent_context_ids();

                foreach($cohorts as $cohortid)
                {
                    // получим имя группы, какое бы было создано, если она бы синхронизировалась автоматически
                    $cohort = $DB->get_record('cohort', ['id' => $cohortid]);

                    if (!in_array($cohort->contextid, $parentcontexts))
                    {
                        $a = (object)[
                            'courseid' => $courseid,
                            'coursefullname' => $course->fullname,
                            'cohortid' => $cohortid,
                            'cohortname' => $cohort->name,
                        ];
                        $message = get_string('e_course_a_enrol_cohorts_err_context_failed', 'local_otcontrolpanel', $a);
                        $actionform->add_final_report_message($message);
                        continue;
                    }

                    // фолбек на режим без групп, если при вычислении групп ничего не найдем
                    $groupid = 0;
                    if ($groupmode == 'samename')
                    {
                        $a = new \stdClass();
                        $a->name = $cohort->name;
                        $a->increment = '';
                        $groupname = trim(get_string('defaultgroupnametext', 'enrol_cohort', $a));

                        // поищем одноименную группу среди всех групп курса
                        foreach (groups_get_all_groups($courseid) as $group) {
                            // будем учитывать не только простое совпадение, но и случай,
                            // когда группа была создана автоматически с правилами формирования дефолтного имени
                            if ($group->name == $groupname || $group->name == $cohort->name)
                            {
                                $groupid = $group->id;
                                break;
                            }
                        }

                        // если группа не определена, а мы хотим создать её в любом случае,
                        // устанавливаем значение -1, которое поймёт плагин синхронизации с ГГ и создаст группу
                        if ($groupid == 0 && !empty($creategroup))
                        {
                            $groupid = -1;
                        }
                    }

                    $instanceid = $plugin->add_instance($course, [
                        'customint1' => $cohortid,
                        'roleid' => $roleid,
                        'customint2' => $groupid
                    ]);

                    $a = (object)[
                        'instanceid' => $instanceid,
                        'courseid' => $courseid,
                        'coursefullname' => $course->fullname,
                        'cohortid' => $cohortid,
                        'cohortname' => $cohort->name,
                        'roleid' => $roleid,
                        'role' => $roles[$roleid],
                        'groupid' => $groupid
                    ];
                    $message = get_string('e_course_a_enrol_cohorts_report_message', 'local_otcontrolpanel', $a);
                    $actionform->add_final_report_message($message);
                }
                // синхронизируем подписки через ГГ по курсу
                $trace = new \null_progress_trace();
                enrol_cohort_sync($trace, $courseid);
            }
        }
    }

    /**
     * {@inheritDoc}
     * @see \local_otcontrolpanel\action\abstract_action::validation()
     */
    public function validation(&$actionform, $data, $files)
    {
        $errors = [];

        $cohorts = $actionform->optional_param('cohorts', null, PARAM_RAW);
        if (!is_null($cohorts) && empty($data['cohorts']))
        {
            $errors['cohorts'] = get_string('e_course_a_enrol_cohorts_err_nocohorts', 'local_otcontrolpanel');
        }
        return $errors;
    }

}