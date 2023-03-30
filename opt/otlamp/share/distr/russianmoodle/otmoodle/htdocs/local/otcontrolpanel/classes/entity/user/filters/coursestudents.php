<?php
namespace local_otcontrolpanel\entity\user\filters;

use local_otcontrolpanel\filter_form_parameter;

class coursestudents extends \local_otcontrolpanel\filter\abstract_filter {

    protected $gradebookrolessql;
    protected $enrolledsql;
    protected $relatedctxsql;
    protected $params;

    public function get_supported_filter_form_parameters() {
        return [
            new filter_form_parameter('enrol__startdate__start', null, PARAM_INT),
            new filter_form_parameter('enrol__startdate__end',   null, PARAM_INT),
        ];
    }

    public function __construct($entity, $value) {
        global $CFG, $DB;

        parent::__construct($entity, $value);

        $courseid = $this->value;
        $coursecontext = \context_course::instance($courseid);

        // подзапрос для получения пользователей с оцениваемой ролью
        list($this->gradebookrolessql, $gradebookrolesparams) = $DB->get_in_or_equal(explode(',', $CFG->gradebookroles), SQL_PARAMS_NAMED, $this->param(1, 20));

        // подзапрос для получения пользователей записанных на курс
        // ограничиваем 29 символами, так как из доступных 30 один уходит на символ u зашитый в мудловской логике
        $euprefix = $this->param(2, 29);
        $capjoin = get_enrolled_with_capabilities_join($coursecontext, $euprefix);
        // предыдущая функция джоинит таблицу и мы не знаем её алиас :(
        // но нам надо дополнить запрос для фильтрации - приходится вычленять :(
        preg_match_all('/ej\d+_e/m', $capjoin->joins, $matches, PREG_SET_ORDER, 0);
        $ejalias = $matches[0][0];

        // добавление в условия выборки фильтрации по датам начала обучения
        $datestart = $this->get_filter_form_parameter_value('enrol__startdate__start', null);
        $dateend = $this->get_filter_form_parameter_value('enrol__startdate__end', null);
        if (!is_null($datestart) || !is_null($dateend))
        {
            // условия для подписок, в которых дата начала задана настройками
            $startdatewheres = [$ejalias.'.enrolstartdate > 0'];
            // условия для подписок, в которых дата начала не задана настройками и
            // определяется в таком случае датой создания подписки
            $timecreatedwheres = [$ejalias.'.enrolstartdate = 0'];

            if (!is_null($datestart))
            {
                $startdatewheres[] = $ejalias.'.enrolstartdate > :'.$this->param(3);
                $capjoin->params[$this->param(3)] = $datestart;
                $timecreatedwheres[] = $ejalias.'.timecreated > :'.$this->param(4);
                $capjoin->params[$this->param(4)] = $datestart;
            }
            if (!is_null($dateend))
            {
                $startdatewheres[] = $ejalias.'.enrolstartdate < :'.$this->param(5);
                $capjoin->params[$this->param(5)] = $dateend;
                $timecreatedwheres[] = $ejalias.'.timecreated < :'.$this->param(6);
                $capjoin->params[$this->param(6)] = $dateend;
            }
            $capjoin->wheres .= ' AND (('.implode(' AND ', $startdatewheres).') OR ('.implode(' AND ', $timecreatedwheres).'))';
        }
        $this->enrolledsql = "(SELECT DISTINCT {$euprefix}u.id
                                 FROM {user} {$euprefix}u
                                      $capjoin->joins
                                WHERE $capjoin->wheres)";
        $enrolledparams = $capjoin->params;

        // We want to query both the current context and parent contexts.
        list($this->relatedctxsql, $relatedctxparams) = $DB->get_in_or_equal($coursecontext->get_parent_context_ids(true), SQL_PARAMS_NAMED, $this->param(7));

        $this->params = array_merge($gradebookrolesparams, $enrolledparams, $relatedctxparams);

    }

    /**
     * {@inheritDoc}
     * @see \local_otcontrolpanel\filter\abstract_filter::get_param_shortname()
     */
    public function get_param_shortname() {
        return 'crsstud';
    }

    public function get_params()
    {
        return $this->params;
    }

    public function get_select()
    {
        return 'ra.roleid ' . $this->gradebookrolessql . '
                AND {user}.deleted = 0
                AND ra.contextid '. $this->relatedctxsql;
    }

    protected function register_joins()
    {
        $this->register_new_join('('.$this->enrolledsql.')', 'je.id={user}.id', 'JOIN', 'je');
        $this->register_new_join('role_assignments', '{user}.id=ra.userid', 'JOIN', 'ra');
    }


    //     // Для получения только активных записей, в moodle после запроса дополнительно выполняется такой код
    //     $count = 0;
    //     // Check if user's enrolment is active and should be displayed.
    //     if (!empty($selectedusers)) {
    //         $coursecontext = $this->context->get_course_context(true);

    //         $defaultgradeshowactiveenrol = !empty($CFG->grade_report_showonlyactiveenrol);
    //         $showonlyactiveenrol = get_user_preferences('grade_report_showonlyactiveenrol', $defaultgradeshowactiveenrol);
    //         $showonlyactiveenrol = $showonlyactiveenrol || !has_capability('moodle/course:viewsuspendedusers', $coursecontext);

    //         if ($showonlyactiveenrol) {
    //             $useractiveenrolments = get_enrolled_users($coursecontext, '', 0, 'u.id',  null, 0, 0, true);
    //         }

    //         foreach ($selectedusers as $id => $value) {
    //             if (!$showonlyactiveenrol || ($showonlyactiveenrol && array_key_exists($id, $useractiveenrolments))) {
    //                 $count++;
    //             }
    //         }
    //     }
}