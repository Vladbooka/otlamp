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

namespace mod_event3kl\provider\base;

defined('MOODLE_INTERNAL') || die();

use mod_event3kl\otserial;
use mod_event3kl\session;
use mod_event3kl\event3kl;

/**
 * Абстрактный класс провайдера
 *
 * @package   mod_event3kl
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class abstract_provider {
    /**
     * Объект для работы с ot api
     * @var otserial
     */
    protected $otserial = null;

    /**
     * возвращает короткий код текущего инстанса, основываясь на классе
     */
    abstract public function get_code();

    public function __construct() {
        $this->otserial = new otserial();
    }

    abstract public function start_session(session $session, event3kl $event3kl);
    abstract public function finish_session(session $session, event3kl $event3kl);
    abstract public function supports_records_download();
    abstract public function get_records(session $session, event3kl $event3kl) : array;
    abstract public function get_record_content(array $recorddata);

    abstract public function get_participate_link(session $session, event3kl $event3kl, $userid);

    public function set_customdata($customdata) {
        $this->customdata = $customdata;
    }
}
