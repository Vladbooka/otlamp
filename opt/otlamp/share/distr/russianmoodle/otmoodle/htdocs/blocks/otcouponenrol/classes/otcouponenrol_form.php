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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * Плагин записи на курс OTPAY. Классы форм.
 *
 * @package    enrol
 * @subpackage otpay
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_otcouponenrol;

use moodleform;
use moodle_url;
use html_writer;
use context_course;

require_once($CFG->libdir . '/formslib.php');
require_once ($CFG->dirroot.'/enrol/otpay/plugins/coupon/lib.php');

/**
 * Форма подписки пользователя на курс
 *
 * @package block
 * @subpackage otcouponenrol
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class otcouponenrol_form extends moodleform
{
    /**
     * Показвывать ссылку на курс или редирект
     * @var bool
     */
    private $show_link;
    /**
     * Общее текущее время
     * @var int
     */
    private $time;
    
    
    /**
     * Overriding this function to get unique form id for multiple self enrolments.
     *
     * @return string form identifier
     */
    protected function get_form_identifier() {
        return parent::get_form_identifier().'_'.($this->_customdata['id'] ?? '0');
    }
    
    /**
     *
     * {@inheritDoc}
     * @see moodleform::definition()
     */
    public function definition()
    {
        // Базовая инициализация
        $mform = $this->_form;
        $this->show_link = $this->_customdata['show_link'];
        
        $mform->addElement('text', 'couponcode', get_string('coupon_payform_field_enter_code','block_otcouponenrol'));
        $mform->updateElementAttr(['couponcode'], [
            'placeholder' => get_string('coupon_payform_field_placeholder_coupon_code','block_otcouponenrol')
        ]);
        $mform->setType('couponcode', PARAM_ALPHANUM);
        
        $this->add_action_buttons(false,
            get_string('coupon_payform_field_submit', 'block_otcouponenrol'));
    }

    /**
     *
     * {@inheritDoc}
     * @see moodleform::validation()
     */
    public function validation($data, $files)
    {
        global $USER;
        $errors = parent::validation($data, $files);
        // Валидация купона
        if ( empty($data['couponcode']) ) {
            $errors['couponcode'] = get_string('coupon_error_form_validation_emptycouponcode',
                'block_otcouponenrol');
        } else {
            if ( ! $coupon = $this->get_coupon_freeaccess($data['couponcode']) )
            { //нет подходящего действующего купона
                $errors['couponcode'] = get_string('coupon_error_form_validation_badcouponcode',
                    'block_otcouponenrol');
            } elseif ($coupon->courseid == 0) {
                // Купон универсвальный и не может записать на конкретный курс
                $errors['couponcode'] = get_string('no_universal_coupons',
                    'block_otcouponenrol');
            } else {
                
                $context = context_course::instance($coupon->courseid);
                // курс не имеет способов записи на курс с поддержкой купонов
                if( ! $enrolrecord = $this->get_coupon_enrol($coupon->courseid))
                {
                    $errors['couponcode'] = get_string('contact_admin',
                        'block_otcouponenrol');
                } else {
                    if (is_enrolled (
                        $context,
                        $USER->id,
                        '',
                        $enrolrecord->customint5 ? false : true
                        ))
                    {
                        $errors['couponcode'] = get_string('active_subscription', 'block_otcouponenrol');
                        return $errors;
                    }
                }
            }
        }
        return $errors;
    }
    /**
     * Получить купон свободного входа по коду купона
     *
     * @param string $couponcode
     * @return /stdClass|false
     */
    private function get_coupon_freeaccess($couponcode)
    {
        global $DB;
        if (empty($this->time)) {
            $this->time = time();
        }
        $couponsselect = "code=:code
                AND status='active'
                AND discounttype='freeaccess'
                AND (lifetime=0 OR createtime+lifetime>:curtime)";
        $couponsparams = [
            'code' => $couponcode,
            'curtime' => $this->time
        ];
        // найдем купон
        $coupon = $DB->get_record_select(
            'enrol_otpay_coupons', $couponsselect, $couponsparams, '*', IGNORE_MULTIPLE);
        return $coupon;
    }
    /**
     * Получить способ зписи на курс по купону в курсе
     *
     * @param int $courseid
     * @return /stdClass|false
     */
    private function get_coupon_enrol($courseid)
    {
        global $DB;
        if (empty($this->time)) {
            $this->time = time();
        }
        // customchar1 - подплагин типа купон добавлен в курс
        // customint5 - разрешать пользователю подписываться раньше даты начала подписки
        $enrolselect = "courseid=:courseid
                    AND enrol='otpay'
                    AND status=0
                    AND customchar1='coupon'
                    AND (enrolstartdate=0 OR enrolstartdate<:curtime OR customint5=1)
                    AND (enrolenddate=0 OR enrolenddate>:curtime2)";
        $enrolparams = [
            'courseid' => $courseid,
            'curtime' => $this->time,
            'curtime2' => $this->time
        ];
        $enrol = $DB->get_record_select(
            'enrol', $enrolselect, $enrolparams, '*', IGNORE_MULTIPLE);
        return $enrol;
    }

    /**
     * Обработка формы
     *
     * @return void redirect, string с сылкой на курс, bool false если форма не передана
     */
    public function process()
    {
        global $USER;
        if ( $formdata = $this->get_data() ) {// Данные получены
            $plugin = enrol_get_plugin('otpay');
            // найдем купон
            $coupon = $this->get_coupon_freeaccess($formdata->couponcode);
            // Найдем способ записи
            $enrol = $this->get_coupon_enrol($coupon->courseid);
            // подпишем пользователя
            $data = \otpay_coupon::enrol_draft_and_subscription(
                $enrol->id, $coupon->courseid, $USER->id, $formdata->couponcode, $plugin);
            // перенаправить на курс или показать ссылку
            if ($this->show_link) {
                $coursemoodleurl = new moodle_url('/course/view.php',
                    [
                        'id' => $coupon->courseid
                    ]);
                return html_writer::link(
                    $coursemoodleurl->out(false), get_course($coupon->courseid)->fullname);
            } else {
                // перенаправим на курс
                \otpay_coupon::enrol_redirect($data, $coupon->courseid);
            }
        } else {
            return false;
        }
    }
}