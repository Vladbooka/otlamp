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

class input_parameter {

    private $value;
    private $container;
    private $lastresult;
    private $scenario;

    /**
     *
     * @param mixed $value - значение сходящего параметра, подлежащего обработке (может содержать инструкции для преобразования значения)
     * @param container $container - контейнер переменных
     * @param mixed $lastresult - результат, который вернул предыдущий обработчик
     */
    public function __construct($value, container $container, $lastresult) {
        $this->value = $value;
        $this->container = $container;
        $this->lastresult = $lastresult;
        $this->scenario = $container->read('mainscenario.code');
    }

    public function get_value() {

        $result = $this->value;

        if (is_string($result)) {
            $splittedresult = explode('.', $result);
            $primarymarker = $splittedresult[0] ?? $result;
            $secondarymarker = $splittedresult[1] ?? null;
            switch($primarymarker)
            {
                case '$VAR':
                    $result = [
                        'source_type' => 'container',
                        'source_value' => substr($result, strlen($primarymarker)+1)
                    ];
                    break;
                case '$RES':
                    $result = [
                        'source_type' => 'lastresult',
                        'source_value' => true
                    ];
                    break;
                case '$ENV':
                    switch ($secondarymarker) {
                        // Запросили $ENV.config.*
                        case 'config':
                            // Отдаем настройку по текущему сценарию, 
                            // если нужна настройка не по текущему сценарию, используй get_config_value
                            $result = [
                                'source_type' => 'static',
                                'source_value' => get_config('local_pprocessing', $this->scenario . '__' . substr($result, strlen($primarymarker . '.' . $secondarymarker)+1))
                            ];
                            break;
                        // Если запросили не конфиг, вернем то, что лежит внутри
                        default:
                            if (!is_null($secondarymarker)) {
                                $result = [
                                    'source_type' => 'container',
                                    'source_value' => substr($result, strlen($primarymarker . $secondarymarker)+1)
                                ];
                            } else {
                                $result = [
                                    'source_type' => 'container',
                                    'source_value' => substr($result, strlen($primarymarker)+1)
                                ];
                            }
                            break;
                    }
                    break;
            }
        }

        if (is_array($result) && array_key_exists('source_type', $result) && array_key_exists('source_value', $result))
        {
            switch($result['source_type'])
            {
                case 'static':
                    $result = $result['source_value'];
                    break;
                case 'container':
                    $result = $this->container->read($result['source_value'], null, false);
                    break;
                case 'container_export':
                    $result = $this->container->export($result['source_value']);
                    break;
                case 'lastresult':
                    $result = $this->lastresult;
                    break;
            }
        }

        if (is_array($result))
        {
            foreach($result as $k=>$v)
            {
                $splittedresult = explode('.', $k);
                $primarymarker = $splittedresult[0] ?? $k;
                switch($primarymarker)
                {
                    case '$VAR':
                        $oldk = $k;
                        $k = [
                            'source_type' => 'container',
                            'source_value' => substr($k, strlen($primarymarker)+1)
                        ];
                        $self = new self($k, $this->container, $this->lastresult);
                        $k = $self->get_value();
                        unset($result[$oldk]);
                        break;
                }
                $self = new self($v, $this->container, $this->lastresult);
                $result[$k] = $self->get_value();
            }
        }
        if (is_array($result) && array_key_exists('source_type', $result) && array_key_exists('source_value', $result))
        {
            $self = new self($result, $this->container, $this->lastresult);
            $result = $self->get_value();
        }

        //                 $this->debugging('inputparam', [
        //                     'source_type' => $inputparam['source_type'],
        //                     'source_value' => $inputparam['source_value'],
        //                     'result' => $result,
        //                     'container' => $container->get_all()
        //                 ]);

        return $result;
    }
}
