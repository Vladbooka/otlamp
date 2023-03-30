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

namespace plagiarism_rucont;

use local_opentechnology\otserial_base;
use stdClass;
use local_opentechnology;

class otserial extends otserial_base
{
    /**
     * Формирование конфигурации плагина
     *
     * @see local_opentechnology\otserial_base::setup_plugin_cfg()
     */
    protected function setup_plugin_cfg()
    {
        $this->component = 'plagiarism_rucont';
        $this->tariffcodes = [
            'free',
            'rucont'
        ];
    }

    /**
     * Конструктор
     *
     * @param bool upgrade - обновить версию плагина
     */
    public function __construct($upgrade = false)
    {
        global $CFG;

        // Код плагина
        $pcode = 'plagiarism_rucont';

        // Версия плагина
        if ($upgrade)
        {// Получение версии из файла
            $plugin = new stdClass();
            require($CFG->dirroot . '/plagiarism/rucont/version.php');
            $pversion = $plugin->version;
        } else
        {// Получение версии из конфигурации
            $pversion = get_config($pcode, 'version');
        }

        // URL приложения
        $purl = $CFG->wwwroot;

        // Конструктор родителя
        parent::__construct($pcode, $pversion, $purl, [ 'upgrade' => $upgrade ]);
    }

    /**
     * Произвести REST-запрос к API
     *
     * @param array $data - Массив данных
     * @return boolean|mixed
     */
    public function rest($data = [])
    {
        // Получение URL для запросов вместе с GET-данными для аутентификации
        $url = $this->url('rucont/rest.php', [], 'api');

        try {
            // Запрос с данными
            $response = $this->request($url->out_omit_querystring(), $url->params(), $data, 'post');

            // Декодировать данные от сервера
            $response = json_decode($response);
            if ( isset($response->reponse) )
            {// Сокращение ответа
                $response = $response->reponse;
            }
        } catch ( \Exception $e )
        {
            $response = new stdClass();
            $response->error = new stdClass();
            $response->error->code = $e->getCode();
            $response->error->message = $e->getMessage();
        }

        // Вернуть ответ
        return $response;
    }
}