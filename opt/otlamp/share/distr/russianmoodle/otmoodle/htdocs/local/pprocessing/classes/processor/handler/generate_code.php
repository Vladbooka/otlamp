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
defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../../../locallib.php');

/**
 * Класс обработчика генерации кода
 *
 * @package    local
 * @subpackage pprocessing
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class generate_code extends base
{
    /**
     * {@inheritDoc}
     * @see \local_pprocessing\processor\handler\base::execute()
     */
    protected function execution_process(container $container)
    {
        // уникальный код сценария
        $scenariocode = $container->read('scenario.code');
        
        // если в конфиге указали хотя бы один параметр, подразумевается 
        if (!empty($this->config['custompolicy']))
        {
            // значения по умолчанию для генерации пароля
            $maxlen = 10;
            $numbers = null;
            $symbols = null;
            $lowerletters = null;
            $upperletters = null;
            
            // Максимальная длина генерируемого кода
            if (is_numeric($this->config['maxlen'])) {
                $maxlen = $this->config['maxlen'];
            }
            // Ищем параметр количество чисел
            if (is_numeric($this->config['numbers'])) {
                $numbers = $this->config['numbers'];
            }
            // Ищем параметр количество cимволов
            if (is_numeric($this->config['symbols'])) {
                $symbols = $this->config['symbols'];
            }
            // Ищем параметр количество прописных букв
            if (is_numeric($this->config['lowerletters'])) {
                $lowerletters = $this->config['lowerletters'];
            }
            // Ищем параметр количество заглавных букв
            if (is_numeric($this->config['upperletters'])) {
                $upperletters = $this->config['upperletters'];
            }
            
            $dof = local_pprocessing_get_dof();
            // если деканата нет то пора умерать
            if (!is_null($dof)) {
                $amauser = $dof->modlib('ama')->user(false);
                $code = $amauser->generate_password_moodle($maxlen, $numbers, $symbols, $lowerletters, $upperletters);
            } else {
                
                // чтобы хоть какой-то код все равно был создан
                // генерим пароль мудлом
                $code = generate_password($maxlen);
                
                // тем не менее, делаем запись в лог
                logger::write_log(
                    'processor',
                    $this->get_type()."__".$this->get_code(),
                    'error',
                    [
                        'scenariocode' => $scenariocode
                    ],
                    'Электронный деканат не найден. Генерация пароля с кастомной политикой невозможна'
                );
            }
            
        } else {
            // генерим пароль мудлом
            $code = generate_password();
        }
        
        // запись в лог
        logger::write_log(
            'processor',
            $this->get_type()."__".$this->get_code(),
            'success',
            [
                'scenariocode' => $scenariocode
            ]
        );
        $container->write('generated_code', $code, false);
        return $code;
    }
}

