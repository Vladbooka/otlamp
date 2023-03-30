<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * otautoenrol enrolment plugin.
 *
 * This plugin automatically enrols a user onto a course the first time they try to access it.
 *
 * @package    enrol
 * @subpackage otautoenrol
 * @date       July 2013
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Class enrol_otautoenrol_plugin
 */
class enrol_otautoenrol_plugin extends enrol_plugin {

    /**
     * @param array $instances
     *
     * @return array
     */
    public function get_info_icons(array $instances) {
        return array(new pix_icon('icon', get_string('pluginname', 'enrol_otautoenrol'), 'enrol_otautoenrol'));
    }

    /**
     * @return bool
     */
    public function roles_protected() {
        // Users with role assign cap may tweak the roles later.
        return false;
    }

    /**
     * @param stdClass $instance
     *
     * @return bool
     */
    public function allow_unenrol(stdClass $instance) {
        // Users with unenrol cap may unenrol other users manually - requires enrol/otautoenrol:unenrol.
        return true;
    }

    /**
     * @param stdClass $instance
     *
     * @return bool
     */
    public function allow_manage(stdClass $instance) {
        // Users with manage cap may tweak period and status - requires enrol/otautoenrol:manage.
        return false;
    }

    /**
     * @param stdClass $instance
     *
     * @return bool
     */
    public function show_enrolme_link(stdClass $instance) {
        return false;
    }

    /**
     * Returns list of unenrol links for all enrol instances in course.
     *
     * @param int $instance
     *
     * @return moodle_url or NULL if self unenrolment not supported
     */
    public function get_unenrolself_link($instance) {
        if ($instance->customint1 == 0) {
            // Don't offer unenrolself if we are going to re-enrol them on login.
            return null;
        }
        return parent::get_unenrolself_link($instance);
    }


    /**
     * Attempt to automatically enrol current user in course without any interaction,
     * calling code has to make sure the plugin and instance are active.
     *
     * This should return either a timestamp in the future or false.
     *
     * @param stdClass $instance course enrol instance
     *
     * @return bool|int false means not enrolled, integer means timeend
     * @throws coding_exception
     */
    public function try_autoenrol(stdClass $instance) {
        global $USER, $PAGE;
        if ($instance->enrol !== 'otautoenrol') {
            throw new coding_exception('Invalid enrol instance type!');
        }
        if ($instance->customint1 == 1 && $this->enrol_allowed($USER, $instance))
        {
            if ($this->enrol_allowed($USER, $instance)) {
                /**
                 * Выставляем тут системный контекст, т.к. контекст требуется
                 * для метода format_string, который может быть вызван любым
                 * плагином, срабатывающим между запуском try_otautoenrol и устновкой контекста
                 */
                $context = context_system::instance();
                $PAGE->set_context($context);
                $this->enrol_user($instance, $USER->id, $instance->customint3, time(), 0);
                $this->process_group($instance, $USER);
            }
            // запуск синхронизации групп с полем профиля (внутри есть проверка настроек)
            $this->sync_groups_with_field($instance, $USER);

            return 0;
        }
        return false;
    }


    /**
     * Custom function, checks to see if user fulfills
     * our requirements before enrolling them.
     *
     * @param          $USER
     * @param stdClass $instance
     *
     * @return bool
     */
    public function enrol_allowed($USER, stdClass $instance) {
        global $DB;

        if (isguestuser($USER)) {
            // Can not enrol guest!!
            return false;
        }

        if (!$instance->customint8) {
            $context = context_course::instance($instance->courseid);
            if (has_capability('moodle/course:view', $context, $USER) || is_enrolled($context, $USER)) {
                // No need to enrol someone who is already enrolled.
                return false;
            }
        }

        if ($DB->record_exists('user_enrolments', array('userid' => $USER->id, 'enrolid' => $instance->id))) {
            return false;
        }

        if ($instance->customint5 > 0) {
            // We need to check that we haven't reached the limit count.
            $totalenrolments = $DB->count_records('user_enrolments', array('enrolid' => $instance->id));
            if ($totalenrolments >= $instance->customint5) {
                return false;
            }
        }

        // Very quick check to see if the user is being filtered.
        if ( ! empty($instance->customtext3) )
        {
            $conditions = json_decode($instance->customtext3);
            if( ! is_null($conditions) && is_array($conditions) )
            {
                foreach ($conditions as $conditionnum=>$condition)
                {
                    if( $condition->conditionfieldval != '' )
                    {
                        if ( ! is_object($USER) )
                        {
                            return false;
                        }

                        $fieldvalue = $this->get_field_value($USER, $condition->conditionfield);

                        if( ! empty($fieldvalue) )
                        {// Получено значение поля
                            if ( ! empty($condition->conditionfieldsoftmatch) )
                            {// Allow partial
                                $match = strstr(strtolower($fieldvalue), strtolower($condition->conditionfieldval));
                            } else
                            {// Require exact
                                $match = $condition->conditionfieldval == $fieldvalue;
                            }
                        } else
                        {// Не получили значение поля
                            $match = false;
                        }

                        if( ! $match )
                        {
                            return false;
                        }
                    }
                }
            }
        }

        if ($instance->enrolstartdate != 0 and $instance->enrolstartdate > time()) {
            return false;
        }

        if ($instance->enrolenddate != 0 and $instance->enrolenddate < time()) {
            return false;
        }
        return true;
    }

    /**
     * Gets an array of the user enrolment actions.
     *
     * @param course_enrolment_manager $manager
     * @param stdClass                 $ue A user enrolment object
     *
     * @return array An array of user_enrolment_actions
     */
    public function get_user_enrolment_actions(course_enrolment_manager $manager, $ue) {
        $actions = array();
        $context = $manager->get_context();
        $instance = $ue->enrolmentinstance;
        $params = $manager->get_moodlepage()->url->params();
        $params['ue'] = $ue->id;
        if ($this->allow_unenrol_user($instance, $ue) && has_capability("enrol/otautoenrol:unenrol", $context)) {
            $url = new moodle_url('/enrol/unenroluser.php', $params);
            $actions[] = new user_enrolment_action(
                    new pix_icon('t/delete', ''), get_string('unenrol', 'enrol'), $url,
                    array('class' => 'unenrollink', 'rel' => $ue->id));
        }
        return $actions;
    }

    /**
     * Sets up navigation entries.
     *
     * @param          $instancesnode
     * @param stdClass $instance
     *
     * @throws coding_exception
     */
    public function add_course_navigation($instancesnode, stdClass $instance) {
        global $USER;
        if ($instance->enrol !== 'otautoenrol') {
            throw new coding_exception('Invalid enrol instance type!');
        }

        // Проверка пользователей на отписку
        $this->check_users_to_unenrol($instance, [$USER]);

        if (!empty($instance->customint8) && $instance->customint8 == 1 && $instance->customint1 == 1) {
            if ($this->enrol_allowed($USER, $instance)) {
                $this->enrol_user($instance, $USER->id, $instance->customint3, time(), 0);
                $this->process_group($instance, $USER);
            }

            // запуск синхронизации групп с полем профиля (внутри есть проверка настроек)
            $this->sync_groups_with_field($instance, $USER);
        }
        $context = context_course::instance($instance->courseid);
        if (has_capability('enrol/otautoenrol:config', $context)) {
            $managelink = new moodle_url(
                    '/enrol/otautoenrol/edit.php', array('courseid' => $instance->courseid, 'id' => $instance->id));
            $instancesnode->add($this->get_instance_name($instance), $managelink, navigation_node::TYPE_SETTING);
        }
    }


    /**
     * Returns localised name of enrol instance
     *
     * @param object $instance (null is accepted too)
     *
     * @return string
     */
    public function get_instance_name($instance) {
        if ($instance->customchar2 != '') {
            return get_string('auto', 'enrol_otautoenrol') . ' (' . $instance->customchar2 . ')';
        }
        return get_string('pluginname', 'enrol_otautoenrol');
    }

    /**
     * Returns edit icons for the page with list of instances
     *
     * @param stdClass $instance
     *
     * @return array
     * @throws coding_exception
     */
    public function get_action_icons(stdClass $instance) {
        global $OUTPUT;

        if ($instance->enrol !== 'otautoenrol') {
            throw new coding_exception('invalid enrol instance!');
        }
        $context = context_course::instance($instance->courseid);
        $icons = array();

        if (has_capability('enrol/otautoenrol:config', $context)) {
            $editlink = new moodle_url(
                    "/enrol/otautoenrol/edit.php", array('courseid' => $instance->courseid, 'id' => $instance->id));
            $icons[] = $OUTPUT->action_icon(
                    $editlink, new pix_icon('t/edit', get_string('edit'), 'core', array('class' => 'iconsmall')));
        }

        return $icons;
    }

    /**
     * This is important especially for external enrol plugins,
     * this function is called for all enabled enrol plugins
     * right after every user login.
     *
     * @param object         $user user record
     *
     * @type moodle_database $DB
     * @return void
     */
    public function sync_user_enrolments($user) {
        global $DB;

        // Get records of all the otautoenrol instances which are set to enrol at login.
        $instances = $DB->get_records('enrol', array('enrol' => 'otautoenrol', 'customint1' => 0), null, '*');
        // Now get a record of all of the users enrolments.
        $user_enrolments = $DB->get_records('user_enrolments', array('userid' => $user->id), null, '*');
        // Run throuch all of the otautoenrolment instances and check that the user has been enrolled.
        foreach($instances as $instance) {
            $found = false;
            foreach($user_enrolments as $user_enrolment)
            {
                if( $user_enrolment->enrolid == $instance->id )
                {
                    $found = true;
                }
            }
            if( ! $found && $this->enrol_allowed($user, $instance) )
            {
                $this->enrol_user($instance, $user->id, $instance->customint3, time(), 0);
                $this->process_group($instance, $user);
            }
            // запуск синхронизации групп с полем профиля (внутри есть проверка настроек)
            $this->sync_groups_with_field($instance, $user);

        }
    }

    /**
     * Returns link to page which may be used to add new instance of enrolment plugin in course.
     *
     * @param int $courseid
     *
     * @return moodle_url page url
     */
    public function get_newinstance_link($courseid) {
        global $DB;

        $context = context_course::instance($courseid);

        if (!has_capability('moodle/course:enrolconfig', $context) or !has_capability('enrol/otautoenrol:config', $context)) {
            return null;
        }

        // Multiple instances supported.
        return new moodle_url('/enrol/otautoenrol/edit.php', array('courseid' => $courseid));
    }

    /**
     * Is it possible to hide/show enrol instance via standard UI?
     *
     * @param stdClass $instance
     * @return bool
     */
    public function can_hide_show_instance($instance){
        return true;
    }

    /**
     * Intercepts the instance deletion call and gives some
     * custom instructions before resuming the parent function
     */
    public function delete_instance($instance) {
        global $DB;

        if($this->get_config('removegroups')) {
            require_once("../group/lib.php");

            $groups = $DB->get_records_sql(
                "SELECT * FROM {groups} WHERE " . $DB->sql_like('idnumber', ':idnumber'),
                array('idnumber' => "otautoenrol|$instance->id|%")
            );

            foreach ($groups as $group) {
                groups_delete_group($group);
            }
        }

        parent::delete_instance($instance);
    }

    /**
     * Creates course enrol form, checks if form submitted
     * and enrols user if necessary. It can also redirect.
     *
     * @param stdClass $instance
     *
     * @return string html text, usually a form in a text box
     */
    public function enrol_page_hook(stdClass $instance) {
        return null;
    }

    /**
     * Add new instance of enrol plugin with default settings.
     *
     * @param object $course
     *
     * @return int id of new instance, null if can not be created
     */
    public function add_default_instance($course) {
        $fields = array('status' => 0, 'customint3' => $this->get_config('defaultrole'), 'customint5' => 0, 'customint8' => 0);
        return $this->add_instance($course, $fields);
    }

    public function get_groups_field_config(stdClass $instance) {

        $result = false;
        // получение настроек для получения групп из поля профиля
        if (!empty($instance->customtext4)) {
            // конфиг определён
            $config = json_decode($instance->customtext4, true);
            if (is_array($config) && array_key_exists('groups_field_config', $config)) {
                // в конфиге сохранены настройки поля профиля с группами
                $result = $config['groups_field_config'];
            }
        }

        return $result;
    }

    /**
     * Синхронизация групп пользвоателя со значениями в поле профиля
     * (если настроен соответствующий режим и пользвоатель уже подписан)
     *
     * @param stdClass $instance - инстанс способа записи
     * @param stdClass $user - запись пользователя
     */
    public function sync_groups_with_field(stdClass $instance, $user) {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/group/lib.php');

        // синхронизировать требуется только если пользователь подписан нашим плагином
        if (!$DB->record_exists('user_enrolments', ['userid' => $user->id, 'enrolid' => $instance->id])) {
            return;
        }

        // получение настроек для получения групп из поля профиля
        $groupsfieldcfg = $this->get_groups_field_config($instance);

        // проверим, что задано поле профиля и регулярка для поиска групп
        if (!$groupsfieldcfg || empty($groupsfieldcfg['field']) || empty($groupsfieldcfg['regex'])) {
            return;
        }

        // массив групп на добавление
        $groupslist = [];
        // текущий курс (в котором был создан текущий инстанс записи на курс)
        $course = $DB->get_record('course', ['id' => $instance->courseid], '*', MUST_EXIST);
        // получаем значения поля профиля нашего пользователя
        $fieldvalue = $this->get_field_value($user, $groupsfieldcfg['field']);
        // совпадения по регулярке будут храниться в массиве совпадений
        $matches = [];
        // ищем при помощи регулярки все пары (группа+курс)
        $founded = preg_match_all($groupsfieldcfg['regex'], $fieldvalue, $matches, PREG_SET_ORDER, 0);
        if ($founded) {
            foreach($matches as $match) {

                // проверка на соответствие курсу (если группа не от нашего курса - пропускаем)

                $courseshortname = $match['course_shortname'] ?? null;
                if (!is_null($courseshortname) && $course->shortname != trim($courseshortname)) {
                    continue;
                }
                $courseidnumber = $match['course_idnumber'] ?? null;
                if (!is_null($courseidnumber) && $course->idnumber != trim($courseidnumber)) {
                    continue;
                }
                if (is_null($courseshortname) && is_null($courseidnumber)) {
                    continue;
                }

                // обработка групп

                $groupfields = [];
                if (array_key_exists('group_idnumber', $match)) {
                    $groupfields['idnumber'] = trim($match['group_idnumber']);
                }
                if (array_key_exists('group_name', $match)) {
                    $groupfields['name'] = trim($match['group_name']);
                }
                if (empty($groupfields)) {
                    // не найдены условия для идентификации группы - пропускаем запись
                    continue;
                }

                // поиск среди существующих групп в курсе
                $groupfields['courseid'] = $instance->courseid;
                $group = $DB->get_record('groups', $groupfields, 'id', IGNORE_MULTIPLE);

                // если группа найдена среди уже существующих
                if ($group !== false) {
                    // запланируем добавление пользователя в неё позднее
                    $groupslist[] = $group->id;
                }

                // если группа не была найдена среди уже существующих,
                // а у нас включена настройка автоматического создания группы для таких случаев
                if ($group === false && !empty($groupsfieldcfg['autocreate'])) {

                    // когда нет имени - генерируем из idnumber
                    if (!array_key_exists('name', $groupfields) && array_key_exists('idnumber', $groupfields)) {
                        $groupfields['name'] = $groupfields['idnumber'];
                    }
                    // создаем группу
                    $creategroupresult = groups_create_group((object)$groupfields);
                    // если получилось - запланируем добавление пользователя в неё позднее
                    if ($creategroupresult !== false) {
                        $groupslist[] = $creategroupresult;
                    }
                }
            }
        }

        // добавим пользователя в группы из поля профиля, которые прошли валидацию
        if (!empty($groupslist)) {
            foreach($groupslist as $groupid) {
                groups_add_member($groupid, $user->id);
            }
        }

        // получим все существующие группы
        $existedgroups = groups_get_all_groups($instance->courseid);
        if (!empty($existedgroups)) {
            // найдем разницу с группами из поля профиля
            $todelete = array_diff(array_keys($existedgroups), $groupslist);
            // удалим из тех групп, которые не соответствуют значениям в поле профиля
            foreach($todelete as $groupid) {
                groups_remove_member($existedgroups[$groupid], $user->id);
            }
        }

    }

    /**
     * @param stdClass $instance
     */
    public function process_group(stdClass $instance, $user) {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/group/lib.php');

        $groupslist = [];
        $result = true;

        // получение настроек для получения групп из поля профиля
        $groupsfieldcfg = $this->get_groups_field_config($instance);

        if (!$groupsfieldcfg) {
            // убедились, что не был выбран вариант записи в группы по полю профиля
            // теперь можно проверить и другие способы определения группы

            if( ! empty($instance->customtext1) )
            {// Необходимо распределить пользователя в конкретные группы
                $groupslist = explode(',', $instance->customtext1);
                if( ! empty($instance->customint6) )
                {// Включено случайное распределение по группам
                    $groupslist = $this->get_random_group($groupslist);
                }
            }
            if( ! empty($instance->customint2) )
            {// Если стоит галка "Добавить пользователей в группу согласно данным поля профиля"
                if( ! empty($instance->customtext3) )
                {// Если указано поле для распределения
                    $conditions = json_decode($instance->customtext3);
                    if( ! is_null($conditions) && is_array($conditions) )
                    {
                        $name = [];
                        foreach ($conditions as $conditionnum=>$condition)
                        {
                            $curfieldname = '';
                            if( ! empty($condition->conditionfieldval) )
                            {// Если указано значения поля, по которому должно быть распределение
                                $curfieldname = (string)$condition->conditionfieldval;
                            } else if(! empty($condition->conditionfield))
                            {// Если значение поля не указано - берем его из профиля пользователя
                                $curfieldname = $this->get_field_value($user, $condition->conditionfield);

                                if( empty($curfieldname) )
                                {// Если в профиле тоже пусто, создадим группу для тех у кого поле не заполнено
                                    $curfieldname = get_string('emptyfield', 'enrol_otautoenrol', $condition->conditionfield);
                                }
                            } else
                            {
                                $curfieldname = get_string('emptyfield', 'enrol_otautoenrol');
                            }
                            $name[] = $curfieldname;
                        }
                        // Получим идентификатор существующей или созданной новой группы
                        $groupslist[] = $this->get_group($instance, implode('_', $name));
                    }
                }
            }
        }

        if (!empty($groupslist)) {
            foreach($groupslist as $groupid) {
                // Зачислим пользовтаеля во все нужные группы
                $result = $result && groups_add_member($groupid, $user->id);
            }
        }

        return $result;
    }

    /**
     * @param stdClass $instance
     * @param $name
     * @param moodle_database $DB
     * @return int|mixed id of the group
     * @throws coding_exception
     * @throws moodle_exception
     */
    private function get_group(stdClass $instance, $name)
    {
        global $DB;
        $idnumber = "otautoenrol|$instance->id|$name";

        $group = $DB->get_record('groups', [
            'idnumber' => $idnumber,
            'courseid' => $instance->courseid
        ]);

        if( ! empty($group) ) {
            $group = $group->id;
        } else
        {
            $newgroupdata = new stdclass();
            $newgroupdata->courseid = $instance->courseid;
            $newgroupdata->name = $name;
            $newgroupdata->idnumber = $idnumber;
            $newgroupdata->description = get_string('auto_desc', 'enrol_otautoenrol');
            $group = groups_create_group($newgroupdata);
        }

        return $group;
    }

    private function get_field_value($user, $shortname)
    {
        global $DB;
        $fieldvalue = '';

        // Получение пользовательских кастомных полей
        /**
         * Корректный метод получения данных кастомных полей пользователя
         * $customfields = profile_user_record($USER->id);
         * не используется, т.к. вызов try_otautoenrol() происходит до установки контекста
         * через $PAGE->set_context, что вызывает ошибку при использовании метода format_string
         * в конструкторах кастомных полей
         */

        if( strpos($shortname, 'profile_field_') !== false )
        {// Передано кастомное поле
            $shortname = str_replace('profile_field_', '', $shortname);
            if( ! empty($shortname) )
            {
                $profilefield = $DB->get_record('user_info_field', ['shortname' => $shortname]);
            } else
            {
                $profilefield = false;
            }
            if( ! empty($profilefield) )
            {
                /**
                 * При использовании метода ядра profile_user_record
                 * получение значения поля должно быть следующим:
                 * $fieldvalue = $customfields->{$profilefield->shortname};
                 */
                $fieldvalue = $this->get_customfield_value($user, $profilefield);
            }
        } else
        {// Передано стандартное поле профиля
            $fieldvalue = $user->$shortname;
        }
        return $fieldvalue;
    }

    /**
     * Метод получения значения кастомного поля пользователя
     * @param stdClass $field запись таблицы user_info_field
     * @return string|boolean
     */
    private function get_customfield_value($user, $field)
    {
        global $DB;
        if( ! empty($field) )
        {
            $fieldvalue = $DB->get_record('user_info_data', ['fieldid' => $field->id, 'userid' => $user->id]);
            if( ! empty($fieldvalue) )
            {
                return $fieldvalue->data;
            } else
            {
                return false;
            }
        } else
        {
            return false;
        }
    }

    /**
     * Возвращает случайную группу из массива групп
     * @param array $grouplist массив идентитфикаторов групп
     * @return array
     */
    private function get_random_group($grouplist)
    {
        global $DB;
        if( ! empty($grouplist) )
        {
            $rndkey = array_rand($grouplist);
            if( $DB->get_record('groups', ['id' => $grouplist[$rndkey]]) )
            {// Группа существует - вернем ее идентификатор
                return [$rndkey => $grouplist[$rndkey]];
            } else
            {// Нет такой группы
                // удалим ее из массива и запустим процесс заново
                unset($grouplist[$rndkey]);
                return $this->get_random_group($grouplist);
            }
        }
        return [];
    }

    public function can_delete_instance($instance)
    {
        return true;
    }

    /**
     * Проверка пользователей на соответствие полей
     * Если не совпадают, происходит отписка
     *
     * @param stdClass $instance
     *
     * @return void
     */
    public function check_users_to_unenrol(stdClass $instance, $users = [])
    {
        global $DB;

        if ( empty($instance->customint7) )
        {
            return;
        }
        if ( empty($users) )
        {
            $users = $DB->get_records('user_enrolments', ['enrolid' => $instance->id], '', 'userid');
        } else
        {
            $cloneusers = $users;
            $users = [];
            foreach ( $cloneusers as $userfilter )
            {
                $users = array_merge($users, $DB->get_records('user_enrolments', ['enrolid' => $instance->id, 'userid' => $userfilter->id]));
            }
        }
        if ( empty($users) )
        {
            return;
        }
        if ( ! empty($instance->customtext3) )
        {
            $conditions = json_decode($instance->customtext3);
            if( is_null($conditions) || ! is_array($conditions) )
            {
                return;
            }
        } else
        {
            return;
        }

        $userspool = [];
        foreach ( $users as $userenrolment )
        {
            if ( ! array_key_exists($userenrolment->userid, $userspool) )
            {
                try
                {
                    $user = $DB->get_record('user', ['id' => $userenrolment->userid]);
                    $userspool[$user->id] = $user;
                } catch ( dml_exception $e )
                {
                    continue;
                }
            }
            foreach ( $conditions as $conditionnum => $condition )
            {
                if( $condition->conditionfieldval != '' )
                {
                    $fieldvalue = $this->get_field_value($userspool[$userenrolment->userid], $condition->conditionfield);

                    if( ! empty($fieldvalue) )
                    {// Получено значение поля
                        if ( ! empty($condition->conditionfieldsoftmatch) )
                        {// Allow partial
                            $match = strstr(strtolower($fieldvalue), strtolower($condition->conditionfieldval));
                        } else
                        {// Require exact
                            $match = $condition->conditionfieldval == $fieldvalue;
                        }
                    } else
                    {// Не получили значение поля
                        $match = false;
                    }

                    if( ! $match )
                    {
                        $this->unenrol_user($instance, $userenrolment->userid);
                    }
                }
            }
        }
    }
}
