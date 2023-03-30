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

namespace local_pprocessing;

use local_pprocessing\processor\exception;

defined('MOODLE_INTERNAL') || die();

/**
 * Класс, отвечающий за исполнение сценариев
 *
 * @package    local_pprocessing
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class executor
{
    /**
     * Предобработка контейнера и добавление корневых переменных окружения
     *
     * @param container $container
     *
     * @return void
     */
    protected static function preprocess_container(container &$container)
    {
        global $CFG;
        
        // объект сайта
        $site = get_site();
        // установка url сайта
        $site->url = $CFG->wwwroot;
        $site->loginurl = $CFG->wwwroot . '/login/';
        $site->signoff = generate_email_signoff();
        
        // установка в контейнер объекта сайта
        $container->write('site', get_site(), true, true);
    }
    
    /**
     * запуск исполнения сценариев
     *
     * @param string $source
     * @param scenario[] $scenarios
     * @param container $container
     * @param mixed $lastresult результат возвращаемый, последним хендлером
     */
    public static function go($source, $scenarios, container $container, $lastresult = null)
    {
        if ( ! array_key_exists($source, constants::sources) )
        {
            return;
        }
        if ( empty($scenarios) )
        {
            return;
        }
        
        // дополнение контейнера системными данными
        self::preprocess_container($container);
        
        foreach ( $scenarios as $scenariocode => $scenario )
        {
            // сброс контейнера
            $container->clear();
            
            // время начала
            $timestart = time();
            
            try {
                self::scenario_go($scenario, $scenariocode, $container, $timestart , $lastresult);
            } catch (exception $ex) {
                $errordata = [
                    'scenario_code' => $scenariocode,
                    'exception_code' => $ex->getCode(),
                    'exception_message' => $ex->getMessage()
                ];
                switch($ex->getCode()) {
                    case 200:
                        logger::write_log('processor', '<- ' . $ex->get_processor()->get_full_code(), 'debug', $errordata, 'scenario execution aborted', $timestart);
                        // плановый выход из сценария - переходим к следующему сценарию
                        continue 2;
                        break;
                    default:
                        logger::write_log('processor', '<- ' . $ex->get_processor()->get_full_code(), 'error', $errordata, 'scenario execution aborted', $timestart);
                        // возникла ошибка - переходим к следующему сценарию
                        continue 2;
                        break;
                }
            }
            
            // запись в лог
            $debugdata = ['scenario_code' => $scenariocode];
            logger::write_log('executor', '< go', 'debug', $debugdata, 'scenario execution ended', $timestart);
        }
    }
    
    /**
     * Запуск конкретного сценария
     * @param mixed $scenario объект сценария
     * @param container $container контейнер
     * @param int $timestart время запуска сценария
     * @param mixed $lastresult результат работы последнего обработчика
     * @throws exception
     */
    public static function scenario_go($scenario, $scenariocode, container $container, $timestart = null, $lastresult = null) {
        if (is_null($timestart)) {
            $timestart = time();
        }
        // запись в лог
        $debugdata = ['scenario_code' => $scenariocode];
        logger::write_log('executor', '> go', 'debug', $debugdata, 'scenario execution started', $timestart);
        
        // установка идентификатора сценария
        $container->write('scenario', ['code' => $scenariocode]);
        $container->write('mainscenario', ['code' => $scenariocode]);
        $processresult = $lastresult;
        
        foreach ( $scenario->get_processors() as $processor )
        {
            try {
                
                if (is_a($processor, '\\local_pprocessing\\processor\\condition\\base'))
                {
                    // условия больше не существуют в качестве обработчиков
                    // их нельзя использовать как отдельную операцию в сценарии
                    // вместо условий в сценариях следует использовать обработчик остановки сценария
                    // stop_scenario_execution с нужным предусловием (классы предусловий сохраняются те же)
                    throw new exception('Using condition as a processor was denied');
                }
                
                $processor->result = $processresult;
                $processresult = $processor->execute($container);
                
            } catch(exception $ex)
            {
                $errordata = [
                    'scenario_code' => $scenariocode,
                    'exception_code' => $ex->getCode(),
                    'exception_message' => $ex->getMessage()
                ];
                
                switch($ex->getCode())
                {
                    case 412:
                        logger::write_log('processor', '<- '.$processor->get_full_code(), 'debug',
                        $errordata, 'processor execution aborted', $timestart);
                        // не выполнились предусловия для обработчика, переходим к следующему обработчику
                        continue;
                        break;
                    default:
                        throw new exception($ex->getMessage(), $ex->getCode(), null, $scenario, $processor);
                        break;
                }
            }
        }
    }
    
    /**
     * запуск исполнения сценариев
     *
     * @param string $source
     * @param scenario[] $scenarios
     * @param container $container
     */
    public static function preconditions($scenariocode, $scenario, container $container, $lastresult)
    {
        $result = true;
        // время начала
        $timestart = time();
        // Код основного сценария
        $mainscenariocode = $container->read('scenario.code');
        
        // запись в лог
        $debugdata = ['scenario_code' => $scenariocode];
        logger::write_log('executor', '---> preconditions', 'debug', $debugdata, 'preconditions execution started', $timestart);
        
        // установка идентификатора сценария
        $container->write('scenario', ['code' => $scenariocode], true, true);
        
        foreach ( $scenario->get_processors() as $processor )
        {
//             logger::write_log('processor', '----'.$processor->get_full_code(), 'debug',
//                 var_export($processor, true), 'precondition processor dump', $timestart);
            try {
                
                if (!is_a($processor, '\\local_pprocessing\\processor\\condition\\base'))
                {
                    // в предусловиях можно использовать только условия,
                    // процессоры использовать нельзя
                    throw new exception('Using processor in preconditions is denied');
                }
                
                $processor->result = $lastresult;
                $processor->set_debugging_level(3);
                $processresult = $processor->execute($container);
                $result = $result && !empty($processresult);
                
            } catch(exception $ex)
            {
                $result = false;
                $errordata = [
                    'scenario_code' => $scenariocode,
                    'exception_code' => $ex->getCode(),
                    'exception_message' => $ex->getMessage()
                ];
                logger::write_log('processor', '<--- '.$processor->get_full_code(), 'error',
                    $errordata, 'preconditions execution aborted', $timestart);
                
                break;
            }
        }
        
        // установка идентификатора сценария
        $container->write('scenario', ['code' => $mainscenariocode], true, true);
        
        // запись в лог
        $debugdata = [
            'scenario_code' => $scenariocode,
            'result' => $result
        ];
        logger::write_log('executor', '<--- preconditions', 'debug', $debugdata, 'preconditions execution ended', $timestart);
        
        return $result;
    }
}

