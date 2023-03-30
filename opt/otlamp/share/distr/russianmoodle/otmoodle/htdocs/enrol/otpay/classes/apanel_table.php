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
 * Плагин записи на курс OTPAY. Класс таблицы панели администрирования заявок.
 *
 * @package    enrol
 * @subpackage otpay
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace enrol_otpay;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/tablelib.php');
require_once($CFG->dirroot . '/course/lib.php');

/**
 * Report table class.
 *
 * @package    report_configlog
 * @copyright  2019 Paul Holden (pholden@greenhead.ac.uk)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class apanel_table extends \table_sql {

    private $providers;
    private $enrolid;
    private $courseid;
    private $rawcurrentrow;

    /**
     * Constructor
     *
     * @param string $search
     */
    public function __construct($options=[]) {
        parent::__construct('enrol-otpay-apanel-table');

        // Define columns.
        $columns = [
            'createdate' => get_string('admin_panel_date', 'enrol_otpay'),
            'fullname' => get_string('admin_panel_fio', 'enrol_otpay'),
            'coursefullname' => get_string('admin_panel_course', 'enrol_otpay'),
            'enroltype' => get_string('admin_panel_enroltype', 'enrol_otpay'),
            'enrolname' => get_string('admin_panel_enrolname', 'enrol_otpay'),
            'comment' => get_string('admin_panel_comment', 'enrol_otpay'),
            'price' => get_string('admin_panel_price', 'enrol_otpay'),
            'status' => get_string('admin_panel_status', 'enrol_otpay'),
        ];
        $this->define_columns(array_keys($columns));
        $this->define_headers(array_values($columns));

        // Table configuration.
        $this->set_attribute('id', $this->uniqueid);
        $this->set_attribute('cellspacing', '0');

        $this->pageable(true);
        $this->collapsible(false);
        $this->sortable(false);
        $this->is_downloadable(true);
        $this->show_download_buttons_at([TABLE_P_BOTTOM]);

        $this->initialbars(false);
        $this->collapsible(false);

        $this->useridfield = 'userid';

        if (array_key_exists('enrolid', $options))
        {
            $this->set_enrolid($options['enrolid']);

        } else if (array_key_exists('courseid', $options))
        {
            $this->set_courseid($options['courseid']);
        }
        $this->init_sql();
    }

    /**
     * Создание экземпляра класса без опций
     * соответствует обычному вызову конструктора без опций, в результате будет сформирована таблица
     * по всем курсам и подпискам, если ограничение не будет позднее добавлено вручную
     *
     * @return \enrol_otpay\apanel_table
     */
    public static function instance()
    {
        return new self();
    }

    /**
     * Создание экземпляра класса по указанной подписке на курс
     * @param int $enrolid - идентификатор подписки
     * @return \enrol_otpay\apanel_table
     */
    public static function instance_by_enrolid($enrolid)
    {
        return new self(['enrolid' => $enrolid]);
    }

    /**
     * Создание экземпляра класса по указанному курсу
     * @param int $courseid - идентификатор курса
     * @return \enrol_otpay\apanel_table
     */
    public static function instance_by_courseid($courseid)
    {
        return new self(['courseid' => $courseid]);
    }

    /**
     * Initializes table SQL properties
     *
     * @return void
     */
    protected function init_sql() {
        global $DB;

        $fields = [
            'otpay.id',
            'otpay.createdate',
            'otpay.userid',
            'c.id as courseid',
            'c.fullname as coursefullname',
            'otpay.paymethod',
            'e.name as enrolname',
            'otpay.currency',
            'otpay.amount',
            'otpay.status',
            'otpay.options'
        ];
        $from = '         {enrol_otpay} otpay
                LEFT JOIN {enrol} e ON e.id=otpay.instanceid
                LEFT JOIN {course} c ON c.id=otpay.courseid';
        $where = ['1=1'];
        $params = [];

        // Массив курсов, к которым имеется доступ по праву
        $courseids = [];
        $courses = get_user_capability_course('moodle/course:view', null, false);
        if (!$courses) {
            $courses = [];
        }
        foreach ($courses as $course) {
            $courseids[$course->id] = true;
        }
        // добавление условия, согласно которому в выборку попадут только те курсы, в которых у пользователя есть право
        if ($courseids) {
            list ($courseidsql, $courseidparams) = $DB->get_in_or_equal(array_keys($courseids), SQL_PARAMS_NAMED);
            $where[] = 'c.id '.$courseidsql;
            $params = array_merge($params, $courseidparams);
        }

        if (isset($this->enrolid))
        {
            $where[] = 'otpay.instanceid = :enrolid';
            $params['enrolid'] = $this->enrolid;
        }

        if (isset($this->courseid))
        {
            $where[] = 'c.id = :courseid';
            $params['courseid'] = $this->courseid;
        }

        $where = implode(' AND ', $where);

        $this->set_sql(implode(', ',$fields), $from, $where, $params);
        $this->set_count_sql('SELECT COUNT(1) FROM ' . $from . ' WHERE ' . $where, $params);
    }

    /**
     * Format report createdate field
     *
     * @param \stdClass $row
     * @return string
     */
    public function col_createdate(\stdClass $row) {
        return userdate($row->createdate);
    }

    /**
     * Format fullname field
     *
     * @param \stdClass $row
     * @return string
     */
    public function col_fullname($row) {
        global $DB;

        $userid = $row->{$this->useridfield};
        $user = $DB->get_record('user', ['id' => $userid]);
        $user->{$this->useridfield} = $userid;

        return parent::col_fullname($user);
    }
    /**
     * Format report coursefullname field
     *
     * @param \stdClass $row
     * @return string
     */
    public function col_coursefullname(\stdClass $row) {
        $coursefullname = $this->format_text($row->coursefullname, FORMAT_PLAIN);
        if ($this->download) {
            return $coursefullname;
        }
        $courseurl = course_get_url($row->courseid);
        return \html_writer::link($courseurl, $coursefullname);
    }

    /**
     * Format report enroltype field
     *
     * @param \stdClass $row
     * @return string
     */
    public function col_enroltype(\stdClass $row) {
        return get_string('otpay_' . $row->paymethod, 'enrol_otpay');
    }

    /**
     * Format report enrolname field
     *
     * @param \stdClass $row
     * @return string
     */
    public function col_enrolname(\stdClass $row) {
        return $this->format_text($row->enrolname, FORMAT_PLAIN);
    }

    /**
     * Format report comment field
     *
     * @param \stdClass $row
     * @return string
     */
    public function col_comment(\stdClass $row) {
        try {
            $provider = $this->get_provider($row->paymethod);
            $comment = $provider->get_comment($row);
        } catch(\Exception $ex) {
            $comment = '';
        }

        if ($this->download) {
            return $this->format_text($comment, FORMAT_PLAIN);
        }

        return $comment;
    }

    /**
     * Format report price field
     *
     * @param \stdClass $row
     * @return string
     */
    public function col_price(\stdClass $row) {
        $amount = $this->format_text($row->amount, FORMAT_PLAIN);

        $strman = get_string_manager();
        if ($strman->string_exists('otpay_currency_' . $row->currency, 'enrol_otpay'))
        {
            return get_string('otpay_currency_' . $row->currency, 'enrol_otpay', $amount);
        } else {
            return $amount;
        }
    }

    /**
     * Format report status field
     *
     * @param \stdClass $row
     * @return string
     */
    public function col_status(\stdClass $row) {

        $statusstring = get_string($row->status, 'enrol_otpay');

        if ($this->download) {
            return $statusstring;
        }

        try {
            $provider = $this->get_provider($row->paymethod);
            // Маршрут статусов
            $route = $provider->get_statuses_route();

            if (empty($route[$row->status]))
            {
                throw new \Exception('No status route');
            }

            $options = [];
            foreach ($route[$row->status] as $st)
            {
                $options[$st] = get_string($st, 'enrol_otpay');
            }
            return \html_writer::select($options, '', '', [$statusstring]);

        } catch(\Exception $ex) {
            return $statusstring;
        }
    }

    /**
     * Установка ограничения последующей выборки по конкретной подписке
     * @param int $enrolid
     */
    public function set_enrolid($enrolid)
    {
        $this->enrolid = $enrolid;
    }

    /**
     * Установка ограничения последующей выборки по конкретному курсу
     * @param int $enrolid
     */
    public function set_courseid($courseid)
    {
        $this->courseid = $courseid;
    }

    /**
     * Get any extra classes names to add to this row in the HTML.
     * @param $row array the data for this row.
     * @return string added to the class="" attribute of the tr.
     */
    function get_row_class($row) {
        return 'admin_panel_row';
    }

    /**
     * Получение экземпляра класса провайдера по способу оплату (коду псевдосабплагина)
     * @param string $paymethod - способ оплаты (код псевдосабплагина)
     * @throws \moodle_exception
     */
    private function get_provider($paymethod)
    {
        if (!isset($this->providers))
        {
            $plugin = new \enrol_otpay_plugin();
            // Все сабплагины
            $this->providers = $plugin->get_providers();
        }

        if (!array_key_exists($paymethod, $this->providers))
        {
            throw new \moodle_exception('Unknown provider "'.$paymethod.'"');
        }

        return $this->providers[$paymethod];
    }

    /**
     * Take the data returned from the db_query and go through all the rows
     * processing each col using either col_{columnname} method or other_cols
     * method or if other_cols returns NULL then put the data straight into the
     * table.
     *
     * After calling this function, don't forget to call close_recordset.
     */
    public function build_table() {

        if ($this->rawdata instanceof \Traversable && !$this->rawdata->valid()) {
            return;
        }
        if (!$this->rawdata) {
            return;
        }

        foreach ($this->rawdata as $row) {
            $this->rawcurrentrow = $row;
            $formattedrow = $this->format_row($row);
            $this->add_data_keyed($formattedrow, $this->get_row_class($row));
        }
    }

    /**
     * Generate html code for the passed row.
     *
     * @param array $row Row data.
     * @param string $classname classes to add.
     *
     * @return string $html html code for the row passed.
     */
    public function get_row_html($row, $classname = '') {
        $html = parent::get_row_html($row, $classname);

        if ($classname != 'emptyrow')
        {
            $doc = new \DOMDocument();
            $doc->loadHTML($html);
            $tr = $doc->getElementsByTagName('tr')->item(0);
            $tr->setAttribute('data-id', $this->rawcurrentrow->id);
            $html = utf8_decode($doc->saveHTML($doc->documentElement));
        }

        return $html;
    }
}