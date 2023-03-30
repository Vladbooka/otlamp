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
 * mylearninghistory block caps.
*
* @package    block_mylearninghistory
* @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/
defined('MOODLE_INTERNAL') || die();


$capabilities = array(
		// Право индивидуального размещения блока
		'block/mylearninghistory:myaddinstance' => array(
				'captype' => 'write',
				'contextlevel' => CONTEXT_SYSTEM,
				'archetypes' => array(
						'guest' => CAP_PREVENT,
						'user' => CAP_ALLOW,
						'manager' => CAP_ALLOW
				),

				'clonepermissionsfrom' => 'moodle/my:manageblocks'
		),

		// Право размещения блока
		'block/mylearninghistory:addinstance' => array(
				'riskbitmask' => RISK_SPAM | RISK_XSS,

				'captype' => 'write',
				'contextlevel' => CONTEXT_BLOCK,
				'archetypes' => array(
						'guest' => CAP_PREVENT,
						'manager' => CAP_ALLOW
				),

				'clonepermissionsfrom' => 'moodle/site:manageblocks'
		),
		'block/mylearninghistory:viewmylearninghistory' => array (
				'captype' => 'write',
				'contextlevel' => CONTEXT_SYSTEM,
				'archetypes' => array (
						'guest' => CAP_PREVENT,
						'user' => CAP_ALLOW,
						'manager' => CAP_ALLOW,
						'editingteacher' => CAP_ALLOW
				)
		),
		'block/mylearninghistory:viewuserslearninghistory' => array (
				'riskbitmask' => RISK_PERSONAL,
				'captype' => 'write',
				'contextlevel' => CONTEXT_USER,
				'archetypes' => array (
						'guest' => CAP_PREVENT,
						'user' => CAP_PREVENT,
						'manager' => CAP_ALLOW,
						'editingteacher' => CAP_ALLOW
				)
		)
);

