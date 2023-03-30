<?php
///////////////////////////////////////////////////////////////////////////
//                                                                       //
// This file is part of Moodle - http://moodle.org/                      //
// Moodle - Modular Object-Oriented Dynamic Learning Environment         //
//                                                                       //
// Moodle is free software: you can redistribute it and/or modify        //
// it under the terms of the GNU General Public License as published by  //
// the Free Software Foundation, either version 3 of the License, or     //
// (at your option) any later version.                                   //
//                                                                       //
// Moodle is distributed in the hope that it will be useful,             //
// but WITHOUT ANY WARRANTY; without even the implied warranty of        //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         //
// GNU General Public License for more details.                          //
//                                                                       //
// You should have received a copy of the GNU General Public License     //
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.       //
//                                                                       //
///////////////////////////////////////////////////////////////////////////

namespace mod_otresourcelibrary;

use cache;
use cache_store;

require_once($CFG->libdir . '/filelib.php');

class otapi_exception extends \Exception {}

class otapi {
    
    private $baseurl = null;
    public $cachelifetime = 86400;
    public $limitpersource = 30;
    
    public function __construct()
    {
        $otapi = new \mod_otresourcelibrary\otserial();
        // Все запросы делаем через наш апи
        $this->baseurl = $otapi->get_requesturl();
        if (substr($this->baseurl, -1, 1) === '/') {
            $this->baseurl = substr($this->baseurl, 0, strlen($this->baseurl) - 1);
        }
    }
    
    private function otapi_prepare_params($params=[])
    {
        $otapi = new \mod_otresourcelibrary\otserial();
        $otserial = $otapi->get_config_otserial();
        $otkey = $otapi->get_config_otkey();
        $time = 10000*microtime(true);
        $params['otserial'] = $otserial;
        $params['time'] = $time;
        $params['hash'] = sha1($otkey.$time.$otserial);
        
        return $params;
    }
    
    private function otapi_process_response($response)
    {
        global $CFG;
        
        if ($response !== false)
        {
            $decoded = json_decode($response, true);
            if (!is_null($decoded))
            {
                if (!empty($decoded['success']) && array_key_exists('response', $decoded))
                {
                    return $decoded['response'];
                }
                if (array_key_exists('success', $decoded) && $decoded['success'] == false)
                {
                    $description = '';
                    if (array_key_exists('response', $decoded))
                    {
                        $description = $decoded['response'];
                    }
                    if (array_key_exists('debugmessage', $decoded) && !empty($CFG->debugdeveloper))
                    {
                        $description .= $decoded['debugmessage'];
                    }
                    
                    throw new otapi_exception(get_string('error_executing_request', 'otresourcelibrary') . $description);
                }
            }
        }
        $message = get_string('error_response_malformed', 'otresourcelibrary');
        if (!empty($CFG->debugdeveloper))
        {
            $message .= $response;
        }
        throw new otapi_exception($message);
    }
    
    private function otapi_request($method, $actionurl, $params=[])
    {
        global $CFG;
        $curl = new \curl();
        $params = $this->otapi_prepare_params($params);
        $options = [
            'returntransfer' => true,
            'timeout' => 60,
        ];
        if (in_array($method, ['delete']))
        {
            $options['CURLOPT_POSTFIELDS'] = json_encode($params);
        }
        if (in_array($method, ['delete', 'put']))
        {
            $params = json_encode($params);
        }
        
        $response = $curl->$method(
            $this->baseurl . $actionurl,
            $params,
            $options
        );
        try {
            $processed = $this->otapi_process_response($response);
        } catch(otapi_exception $e)
        {
            $debuginfo = [
                'request' => [
                    'method' => $method,
                    'url' => $this->baseurl . $actionurl,
                    'params' => $params,
                    'buildedquery' => http_build_query($params),
                    'options' => $options
                ],
                'response' => $response,
                'trace' => $e->getTraceAsString()
            ];
            
            print_error('otapi_exception', 'mod_otresourcelibrary', '#', $e->getMessage(), json_encode($debuginfo));
        }
        return $processed;
    }
    
    private function otapi_get($actionurl, $params=[])
    {
        $nocache = ['/otresourcelibrary/rest/sourcedata'];
        if (in_array($actionurl, $nocache)) {
            return $this->otapi_request('get', $actionurl, $params);
        }
        // получаем данные кеша
        $cache = cache::make_from_params(cache_store::MODE_APPLICATION, 'mod_otresourcelibrary', 'otapi_get');
        $key = md5(json_encode([
            'actionurl' => $actionurl,
            'params' => $params
        ]));
        $data = $cache->get($key);
        // Пересоздаем кеш если его время вышло
        if (! isset($data['timecreated']) || $data['timecreated'] + $this->cachelifetime < time()) {
            // дата для кеширования
            $data = [
                'data'        => $this->otapi_request('get', $actionurl, $params),
                'timecreated' => time()
            ];
            // записываем данные в кеш
            $cache->set($key, $data);
        }
        return $data['data'];
    }
    
    private function otapi_put($actionurl, $params=[])
    {
        $status = $this->otapi_request('put', $actionurl, $params);
        return $status;
    }
    
    private function otapi_delete($actionurl, $params=[])
    {
        $status = $this->otapi_request('delete', $actionurl, $params);
        return $status;
    }
    
    public function get_implemented_sourcetypes()
    {
        $sourcetypes = [];
        
        $otapiresponse = $this->otapi_get('/otresourcelibrary/rest/sourcetypes');
        
        if ($otapiresponse !== false && is_array($otapiresponse))
        {
            $sourcetypes = $otapiresponse;
        }
        
        return $sourcetypes;
    }
    
    public function get_structure_by_sourcecode($sourcecode)
    {
        $structure = [];
        
        $otapiresponse = $this->otapi_get('/otresourcelibrary/rest/sourcedata', [
            'sourcecode' => $sourcecode,
            'view' => 'structure'
        ]);
        
        if ($otapiresponse !== false && is_array($otapiresponse))
        {
            $structure = $otapiresponse;
        }
        
        return $structure;
    }
    
    public function get_structure_by_sourcename($sourcename)
    {
        $params = [
            'sourcename' => $sourcename,
            'view' => 'structure'
        ];
        $otapiresponse = $this->otapi_get('/otresourcelibrary/rest/sourcedata', $params);
        return $otapiresponse;
    }
    
    public function get_installation_sources_names()
    {
        $params = [
            'view' => 'sources_info'
        ];
        $otapiresponse = $this->otapi_get('/otresourcelibrary/rest/sourcedata', $params);
        return $otapiresponse;
    }
    
    public function save_installation_source_credentials($sourcename, $sourcecode, $credentials)
    {
        $params = [
            'sourcename' => $sourcename,
            'sourcecode' => $sourcecode,
            'credentials' => $credentials
        ];
        $otapiresponse = $this->otapi_put('/otresourcelibrary/rest/sourcedata', $params);
        return $otapiresponse;
    }
    
    public function save_installation_sources_activity($activityinfo)
    {
        $params = [
            'activityinfo' => $activityinfo
        ];
        $otapiresponse = $this->otapi_put('/otresourcelibrary/rest/sourcedata', $params);
        return $otapiresponse;
    }
    
    public function delete_installation_source($sourcename)
    {
        $params = [
            'sourcename' => $sourcename
        ];
        $otapiresponse = $this->otapi_delete('/otresourcelibrary/rest/sourcedata', $params);
        return $otapiresponse;
    }
    
    public function get_resource($sourcename, $resourceid, $pointertype=null, $pointerval=null)
    {
        
        $params = [
            'sourcename' => $sourcename
        ];
        if (!is_null($pointertype) && !is_null($pointerval))
        {
            $params['pointer_type'] = $pointertype;
            $params['pointer_value'] = $pointerval;
        }
        $otapiresponse = $this->otapi_get('/otresourcelibrary/rest/resources/'.$resourceid, $params);
        return $otapiresponse;
    }
    
    public function get_resource_content($proxyscript, $resourcedata, $force=null)
    {
        $html = '';
        
        if (array_key_exists('content', $resourcedata))
        {
            $content = $resourcedata['content'];
            
            // способ отображения контента по умолчанию не определен
            $outputmode = null;
            
            if (is_null($force))
            {
                // для случаев, когда мы не хотели отобразить контент каким-то конкретным
                // способом - ориентируемся на сведения, переданные ресурсом и отдаемся собственным предпочтениям
                
                // способы отображения, выстроенные в порядке наших предпочтений
                $outputmodes = ['iframe', 'embeded', 'int_url', 'ext_url'];
                
                // поднимем в топ способ, предпочтительный для ресурса, если был указан
                if (array_key_exists('preferred_content_output_mode', $resourcedata))
                {
                    array_unshift($outputmodes, $resourcedata['preferred_content_output_mode']);
                    $outputmodes = array_unique($outputmodes);
                }
                
                // найдём наилучший доступный способ отображения
                foreach ($outputmodes as $outputmodecandidat)
                {
                    if (array_key_exists($outputmodecandidat, $content) && !is_null($content[$outputmodecandidat]))
                    {
                        $outputmode = $outputmodecandidat;
                        break;
                    }
                }
                
            } elseif(array_key_exists($force, $content) && !is_null($content[$force]))
            {
                // мы хотели отобразить контент каким-то конкретным способом и он доступен
                $outputmode = $force;
                // если будет недоступен, то $outputmode останется null и будет отображен текст ошибки
            }
            
            switch ($outputmode)
            {
                case 'iframe':
                    $html .= $content['iframe'];
                    break;
                case 'embeded':
                    $html .= str_replace('%proxy_script%', $proxyscript, $content['embeded']);
                    break;
                case 'int_url':
                    redirect(str_replace('%proxy_script%', $proxyscript, $content['int_url']));
                    break;
                case 'ext_url':
                    redirect($content['ext_url']);
                    break;
                default:
                    $html .= get_string('error_get_content', 'otresourcelibrary');
                    break;
                    
            }
        }
            
        return $html;
    }
    
    public function find_resources($q = null, $categoryid=null, $sourcenames=null, $offset = 0, $limit = 10)
    {
        $params = [];
        if (!is_null($q)) {
            $params['q'] = $q;
        }
        if (!is_null($categoryid)) {
            $params['categoryid'] = $categoryid;
        }
        if (!is_null($sourcenames)) {
            $params['sourcenames'] = $sourcenames;
        }
        $params['offset'] = $offset;
        $params['limit'] = $limit;
        return $this->otapi_get('/otresourcelibrary/rest/resources', $params);
    }
    
    
    public function get_resource_media($sourcename, $resourceid, $url)
    {
        $params = [
            'sourcename' => $sourcename,
            'resourceid' => $resourceid,
            'url' => $url
        ];
        $otapiresponse = $this->otapi_get('/otresourcelibrary/rest/resourcemedia/'.$resourceid, $params);
        return $otapiresponse;
    }
    
    public function get_source_categories($sourcename, $parentid=null)
    {
        $params = [
            'sourcenames' => [$sourcename],
            'parentid' => $parentid
        ];
            
        $otapiresponse = $this->otapi_get('/otresourcelibrary/rest/categories', $params);
        return $otapiresponse;
    }
}