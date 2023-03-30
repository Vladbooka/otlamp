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
 * Класс обработчика удаления пользователя из глобальных групп
 *
 * @package    local_pprocessing
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class remove_from_cohorts extends base
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
        if( empty($cohorts) )
        {
            return;
        }
        $cohorts = array_keys($cohorts);

        $usercohorts = $this->get_user_cohorts($userid);
        $needcohorts = array_intersect($usercohorts, $cohorts);
        foreach($needcohorts as $cohortid)
        {
            $this->remove($cohortid, $userid, $container);
        }
    }

    /**
     * Отчислить пользователя из группы
     * @param int $cohortid id группы
     * @param int $userid id пользователя
     */
    protected function remove($cohortid, $userid, $container)
    {
        if( cohort_is_member($cohortid, $userid) )
        {// Если пользователь в группе, подгатавливаем данные для поиска прецедента добавления в группу
            $cohort = [
                'id' => (int)$cohortid,
                'manage' => 'add'
            ];
            $container->write('cohort', $cohort);
        } else
        {// Если не в группе - ничего не делаем
            return;
        }
        if( $this->config['cohorts_manage_mode'] == 'enable' )
        {// Если включен режим с ручным управлением составом ГГ
            if( ! $this->is_precedent_processed($this->scenariocode, $container, 'add_to_cohorts') )
            {// И не мы добавляли пользователя в группу - ничего не делаем
                return;
            }
        }
        // Убираем пользователя из группы
        cohort_remove_member($cohortid, $userid);
        if( ! cohort_is_member($cohortid, $userid) )
        {// Если успешно убрали
            // Убираем записи о прецеденте добавления
            $this->remove_processed($this->scenariocode, $container, 'add_to_cohorts');
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
    
    /**
     * Получить группы, в которые зачислен пользователь
     * @param int $userid идентификатор пользователя
     * @return array массив идентификаторов групп
     */
    protected function get_user_cohorts($userid)
    {
        global $DB;
        $result = [];
        $sql = 'SELECT c.id
                  FROM {cohort} c
                  JOIN {cohort_members} cm
                    ON c.id = cm.cohortid
                 WHERE cm.userid = :userid';
        $params['userid'] = $userid;
        if( $items = $DB->get_records_sql($sql, $params) )
        {
            $result = array_keys($items);
        }
        return $result;
    }
}

