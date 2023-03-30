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
namespace local_pprocessing\processor\handler;
use local_pprocessing\container;
use local_pprocessing\logger;
use stdClass;
use context_system;

require_once($CFG->dirroot . '/cohort/lib.php');
defined('MOODLE_INTERNAL') || die();

/**
 * Класс обработчика добавления пользователя в глобальные группы
 *
 * @package    local_pprocessing
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class add_to_cohorts extends base
{
    /**
     * Уникальный код сценария
     * @var string
     */
    private $scenariocode;
    /**
     * {@inheritDoc}
     * @see \local_pprocessing\processor\handler\base::execute()
     */
    protected function execution_process(container $container)
    {
        // Положим в переменную класса код, чтобы использовать его в локальных методах и не получать каждый раз
        $this->scenariocode = $container->read('scenario.code');

        // получим пользователя из контейнера
        $userid = $container->read('user.id');
        $cohorts = $container->export('cohorts');
        
        // запись в лог
        logger::write_log(
            'processor',
            $this->get_type()."__".$this->get_code(),
            'debug',
            [
                'cohorts' => $cohorts,
                'userid' => $userid,
            ],
            'cohorts_to_add'
        );
        
        if( empty($cohorts) )
        {
            return;
        }

        foreach($cohorts as $cohort)
        {
            $this->add($cohort->id, $userid, $container);
        }
    }

    /**
     * Добавить пользователя в группу
     * @param int $cohortid id группы
     * @param int $userid id пользователя
     */
    protected function add($cohortid, $userid, $container)
    {
        if( ! cohort_is_member($cohortid, $userid) )
        {// Если пользователя еще нет в группе, подгатавливаем данные для записи прецедента
            $cohort = [
                'id' => (int)$cohortid,
                'manage' => 'add'
            ];
            $container->write('cohort', $cohort);
        } else
        {// Если уже в группе - ничего не делаем
            return;
        }
        // Добавляем в группу
        cohort_add_member($cohortid, $userid);
        if( cohort_is_member($cohortid, $userid) )
        {// Пользователь успешно добавлен в группу
            // сохранение данных обработанного прецедента
            $this->add_processed($this->scenariocode, $container);
            $status = 'success';
        } else
        {// во время отправки возникла ошибка
            $status = 'error';
        }
        // запись в лог
        logger::write_log(
            'processor',
            $this->get_type()."__".$this->get_code(),
            $status,
            [
                'scenariocode' => $this->scenariocode,
                'cohortid' => $cohortid,
                'userid' => $userid
            ]
        );
    }
}

