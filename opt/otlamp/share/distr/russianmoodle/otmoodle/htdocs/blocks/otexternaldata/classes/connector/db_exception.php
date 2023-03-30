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
 * Внешние данные
 *
 * @package    block_otexternaldata
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_otexternaldata\connector;

class db_exception extends \Exception {
    
    var $dbms;
    var $fn;
    var $sql = '';
    var $params = '';
    var $host = '';
    var $database = '';
    
    function __construct($dbms, $fn, $errno, $errmsg, $p1, $p2, $thisConnection)
    {
        switch($fn) {
            case 'EXECUTE':
                $this->sql = is_array($p1) ? $p1[0] : $p1;
                $this->params = $p2;
                $s = "$dbms error: [$errno: $errmsg] in $fn(\"$this->sql\")";
                break;
                
            case 'PCONNECT':
            case 'CONNECT':
                $user = $thisConnection->user;
                $s = "$dbms error: [$errno: $errmsg] in $fn($p1, '$user', '****', $p2)";
                break;
            default:
                $s = "$dbms error: [$errno: $errmsg] in $fn($p1, $p2)";
                break;
        }
        
        $this->dbms = $dbms;
        if ($thisConnection) {
            $this->host = $thisConnection->host;
            $this->database = $thisConnection->database;
        }
        $this->fn = $fn;
        $this->msg = $errmsg;
        
        if (!is_numeric($errno)) $errno = -1;
        parent::__construct($s,$errno);
    }
}