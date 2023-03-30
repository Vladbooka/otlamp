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

namespace local_pprocessing\processor;

// code=200 - Запланированная остановка сценария
// code=422 - Отсутствует обязательный параметр
// code=422 - Параметр не валидный

class exception extends \Exception {
    /**
     * Сценарий, выбросивший исключение
     * @var mixed
     */
    protected $scenario = null;
    
    /**
     * Процессор, выбросивший исключение
     * @var mixed
     */
    protected $processor = null;
    
    public function __construct($message = null, $code = null, $previous = null, $scenario = null, $processor = null) {
        $this->scenario = $scenario;
        $this->processor = $processor;
        parent::__construct($message, $code, $previous);
    }
    
    /**
     * Получить сценарий, выбросивший исключение
     * @return mixed|null
     */
    public function get_scenario() {
        return $this->scenario;
    }
    
    /**
     * Получить процессор, выбросивший исключение
     * @return mixed|null
     */
    public function get_processor() {
        return $this->processor;
    }
}