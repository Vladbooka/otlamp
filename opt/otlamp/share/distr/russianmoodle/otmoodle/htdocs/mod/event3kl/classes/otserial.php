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

namespace mod_event3kl;

defined('MOODLE_INTERNAL') || die();

use local_opentechnology\otserial_base;

class otserial extends otserial_base {

    protected $ptrequesturi = 'event3kl/rest/providers/types';

    protected $pirequesturi = 'event3kl/rest/providers/instances';

    /**
     * Формирование конфигурации плагина
     *
     */
    protected function setup_plugin_cfg()
    {
        $this->component = 'mod_event3kl';
        $this->tariffcodes = [
            'free',
            'event3kl'
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
        $pcode = 'mod_event3kl';

        // Версия плагина
        if ($upgrade)
        {// Получение версии из файла
            $plugin = new \stdClass();
            require($CFG->dirroot . '/mod/event3kl/version.php');
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

    private function request_json($url, array $postfields=[], $method='get') {

        $response = $this->request($url->out_omit_querystring(), $url->params(), $postfields, $method);
        $decodedresponse = json_decode($response, true);

        if (!array_key_exists('success', $decodedresponse) || $decodedresponse['success'] != true) {
            $debuginfo = $decodedresponse['debugmessage'] ?? '';
            $requestdata = [$url->out_omit_querystring(), $url->params(), $postfields, $method];
            $debuginfo .= PHP_EOL . json_encode($requestdata, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
            $debuginfo .= PHP_EOL . var_export($decodedresponse, true);
            debugging($debuginfo);
            throw new \moodle_exception('request_not_successful', 'mod_event3kl');
        }

        if (!array_key_exists('response', $decodedresponse)) {
            $debuginfo = $decodedresponse['debugmessage'] ?? null;
            debugging($debuginfo);
            throw new \moodle_exception('response_not_isset', 'mod_event3kl');
        }

        return $decodedresponse['response'];
    }

    public function get_providers_instances() {

        if (!$this->plugin_has_configured_otapi_data()) {
            throw new \moodle_exception('otapi_data_not_configured', 'mod_event3kl');
        }

        $url = $this->url($this->pirequesturi, [], 'api');
        $response = $this->request_json($url);

        return $response;
    }

    public function get_providers_types() {

        if (!$this->plugin_has_configured_otapi_data()) {
            throw new \moodle_exception('otapi_data_not_configured', 'mod_event3kl');
        }

        $url = $this->url($this->ptrequesturi, [], 'api');
        $response = $this->request_json($url);

        return $response;
    }

    public function get_providers_type($providercode) {
        if (!$this->plugin_has_configured_otapi_data()) {
            throw new \moodle_exception('otapi_data_not_configured', 'mod_event3kl');
        }

        $url = $this->url($this->ptrequesturi.'/'.$providercode, [], 'api');
        $response = $this->request_json($url);

        return $response;
    }

    public function get_providers_instance($providername) {
        if (!$this->plugin_has_configured_otapi_data()) {
            throw new \moodle_exception('otapi_data_not_configured', 'mod_event3kl');
        }

        $url = $this->url($this->pirequesturi.'/'.$providername, [], 'api');
        $response = $this->request_json($url);

        return $response;
    }

    public function create_providers_instance($providerconfig) {
        if (!$this->plugin_has_configured_otapi_data()) {
            throw new \moodle_exception('otapi_data_not_configured', 'mod_event3kl');
        }

        $url = $this->url($this->pirequesturi, [], 'api');
        $response = $this->request_json($url, $providerconfig, 'post');

        return $response;
    }

    public function update_providers_instance($providername, $providerconfig) {
        if (!$this->plugin_has_configured_otapi_data()) {
            throw new \moodle_exception('otapi_data_not_configured', 'mod_event3kl');
        }

        $url = $this->url($this->pirequesturi . '/' . $providername, [], 'api');
        $response = $this->request_json($url, $providerconfig, 'put');

        return $response;
    }

    public function delete_providers_instance($providername) {
        if (!$this->plugin_has_configured_otapi_data()) {
            throw new \moodle_exception('otapi_data_not_configured', 'mod_event3kl');
        }

        $url = $this->url($this->pirequesturi . '/' . $providername, [], 'api');
        $response = $this->request_json($url, [], 'delete');

        return $response;
    }

    public function provider_instance_start_session($providername, $sessiondata) {

        if (!$this->plugin_has_configured_otapi_data()) {
            throw new \moodle_exception('otapi_data_not_configured', 'mod_event3kl');
        }

        $url = $this->url($this->pirequesturi . '/' . $providername . '/start_session', [], 'api');
        $response = $this->request_json($url, $sessiondata, 'post');

        return $response;

    }

    public function provider_instance_finish_session($providername, $extid) {

        if (!$this->plugin_has_configured_otapi_data()) {
            throw new \moodle_exception('otapi_data_not_configured', 'mod_event3kl');
        }

        $url = $this->url($this->pirequesturi . '/' . $providername . '/finish_session', [], 'api');
        $response = $this->request_json($url, ['sessionExternalId' => $extid], 'post');

        return $response;

    }


    public function provider_instance_session_records($providername, $extid) {

        if (!$this->plugin_has_configured_otapi_data()) {
            throw new \moodle_exception('otapi_data_not_configured', 'mod_event3kl');
        }

        $urlstr = $this->pirequesturi . '/' . $providername . '/session_records';
        $urlparams = ['sessionExternalId' => $extid];
        $url = $this->url($urlstr, $urlparams, 'api');
        $response = $this->request_json($url, [], 'get');

        return $response;

    }



    public function provider_instance_get_session_enter_url($providername, $sessionExternalId, array $userdata) {

        if (!$this->plugin_has_configured_otapi_data()) {
            throw new \moodle_exception('otapi_data_not_configured', 'mod_event3kl');
        }

        $getparams = [
            'sessionExternalId' => $sessionExternalId,
        ];
        foreach($userdata as $userdataprop => $userdatavalue) {
            $getparams['userdata['.$userdataprop.']'] = $userdatavalue;
        }
        $url = $this->url($this->pirequesturi . '/' . $providername . '/getSessionEnterUrl', $getparams, 'api');
        $response = $this->request_json($url, [], 'get');

        return $response;
    }
}