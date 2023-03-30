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
 * Внешние данные
 *
 * @package    block_otexternaldata
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_otexternaldata\connector;

trait webdav {
    
    protected $webdavcon_properties = ['baseUri', 'userName', 'password', 'dirPath'];
    
    /**
     * Инициализация формы
     *
     * @param \MoodleQuickForm $mform
     */
    protected function webdavcon_extend_form_definition(&$mform)
    {
        
        $mform->addElement('text', 'webdavcon_baseUri', get_string('webdavcon_baseUri', 'block_otexternaldata'));
        $mform->addHelpButton('webdavcon_baseUri', 'webdavcon_baseUri', 'block_otexternaldata');
        $mform->setType('webdavcon_baseUri', PARAM_RAW);
        
        $mform->addElement('text', 'webdavcon_userName', get_string('webdavcon_userName', 'block_otexternaldata'));
        $mform->setType('webdavcon_userName', PARAM_RAW);
        
        $mform->addElement('password', 'webdavcon_password', get_string('webdavcon_password', 'block_otexternaldata'));
        $mform->setType('webdavcon_password', PARAM_RAW);
        
        $mform->addElement('text', 'webdavcon_dirPath', get_string('webdavcon_dirPath', 'block_otexternaldata'));
        $mform->addHelpButton('webdavcon_dirPath', 'webdavcon_dirPath', 'block_otexternaldata');
        // $mform->setDefault('webdavcon_dirPath', '/');
        $mform->setType('webdavcon_dirPath', PARAM_RAW);
        
        if (method_exists($this, 'dirpath_substitutions') && method_exists($this, 'get_substitutions_description'))
        {
            $substitutions = $this->dirpath_substitutions();
            $a = $this->get_substitutions_description($substitutions);
            $mform->addElement('static', 'webdavcon_dirPath_desc', '', get_string('webdavcon_dirPath_desc', 'block_otexternaldata', $a));
        }
        
    }
    
    /**
     * Формирование конфига из данных формы, с попутной валидацией
     *
     * @param array $formdata - дынне полученные из формы при отправке
     * @throws \Exception
     * @return array
     */
    protected function webdavcon_compose_config($formdata)
    {
        $config = [];
        foreach($this->webdavcon_properties as $property)
        {
            $property = 'webdavcon_' . $property;
            if (array_key_exists($property, $formdata))
            {
                $config[$property] = $formdata[$property];
            } else
            {
                throw new \Exception('Missing required field ['.$property.']');
            }
        }
        return $config;
    }
    
    /**
     * Валидация и формирование конфига без префиксов
     *
     * @param array $config
     * @throws \Exception
     * @return array
     */
    protected function webdavcon_get_simple_config(array $config)
    {
        $result = [];
        foreach($this->webdavcon_properties as $property)
        {
            if (!array_key_exists('webdavcon_' . $property, $config))
            {
                throw new \Exception('Missing required field \''.$property.'\'');
            }
            $result[$property] = $config['webdavcon_' . $property];
        }
        return $result;
    }
    
    /**
     * Получение WebDAV-клиента для обработки дальнейших запросов
     * @param array $config
     * @return \webdav_client
     */
    private function webdavcon_get_client($config)
    {
        global $CFG;
        $config = $this->webdavcon_get_simple_config($config);
        
        require_once($CFG->libdir . '/webdavlib.php');
        
        // авторизация по умолчанию не требуется
        $auth = false;
        // а если задан логин, значит авторизация нужна
        if (!empty($config['userName']))
        {
            // пока реализуем без настроек самый распространенный вариант - basic
            // если потребуется что-то другое, вынесем в настройку
            $auth = 'basic';
        }
        
        $scheme = parse_url($config['baseUri'], PHP_URL_SCHEME);
        $server = parse_url($config['baseUri'], PHP_URL_HOST);
        $port = parse_url($config['baseUri'], PHP_URL_PORT);
        
        if ($scheme == 'https')
        {
            $socket = 'ssl://';
            $port = $port ?? 443;
        } else
        {
            $socket = '';
            $port = $port ?? 80;
        }
        
        $client = new \webdav_client($server, $config['userName'], $config['password'], $auth, $socket);
        
        // в библиотеке пустой пароль - повод не использовать логин при авторизации. При том, что мы его передаем. Странная логика.
        // что ж, зададим вручную
        if (!empty($config['userName']))
        {
            $client->user = $config['userName'];
            $client->pass = !empty($config['password']) ? $config['password'] : '';
        }
        $client->port = $port;
        // дебаг-логи пишем только если включен дебаг для разработчиков (в стандартный файл ошибок веб-сервера)
        $client->debug = !empty($CFG->debugdeveloper);
        
        return $client;
    }
    
    /**
     * Выполнение запроса на получение списка ресурсов, доступных по пути
     * @param \webdav_client $client
     * @param string $dirpath
     * @return [
     *      HREF => [
     *          'type' => 'dir|file',
     *          'dirname' => '',
     *          'basename' => '',
     *          'extension' => '',
     *          'filename' => '',
     *      ]
     * ]
     */
    private function webdavcon_propfind($client, $dirpath)
    {
        $result = [];
        
        
        /** @var \webdav_client $client */
        if ($client->open())
        {
            $resources = $client->ls($dirpath);
            if (is_array($resources))
            {
                foreach($resources as $resource)
                {
                    $item = [];
                    
                    if (!empty($resource['resourcetype']) && $resource['resourcetype'] == 'collection') {
                        $item['type'] = 'dir';
                    }else{
                        $item['type'] = 'file';
                    }
                    
                    
                    $pathparts = pathinfo(urldecode($resource['href']));
                    foreach(['basename', 'extension', 'filename'] as $pathinfo)
                    {
                        if (array_key_exists($pathinfo, $pathparts))
                        {
                            $item[$pathinfo] = $pathparts[$pathinfo];
                        }
                    }
                    
                    
                    $result[$resource['href']] = $item;
                }
            }
            $client->close();
        }
        
        return $result;
    }
    
    /**
     * Получение списка ресурсов для передачи в шаблон
     * @param array $config
     * @param string $dirpath
     * @param int $blockinstanceid
     * @param boolean $filesonly
     * @return []
     */
    protected function webdavcon_get_items($config, $dirpath, $blockinstanceid, $filesonly=true)
    {
        $items = [];
        
        $client = $this->webdavcon_get_client($config);
        
        // запрос поиска ресурсов в переданном пути
        $resources = $this->webdavcon_propfind($client, $dirpath, 1);
        
        foreach($resources as $resourcepath => $resource)
        {
            
            if (!$filesonly || $resource['type'] == 'file')
            {
                // Кажется, мы нашли нужны итем (либо запросили все подряд, либо запросили файл и это вроде и есть файл)
                
                
                // найденный ресурс и есть директория, в которой мы выполняем поиск
                $resource['is_current_dir'] = ($resourcepath == $dirpath);
                $resource['hash'] = md5($resourcepath);
                $resource['fileurl'] = (new \moodle_url('/blocks/otexternaldata/file.php', [
                    'resource' => $resource['hash'],
                    'id' => $blockinstanceid
                ]))->out(false);
                
                $items[$resourcepath] = $resource;
            }
        }
        
        return $items;
    }
    
    /**
     *
     * @param array $config
     * @param string $resourcepath
     * @return [
     *      'headers' => [],
     *      'body' => '',
     *      'statusCode' => 200
     * ]
     */
    protected function webdavcon_get_file(array $config, $resourcepath)
    {
        // формат результатов приближен к результатам, возвращаемым библиотекой sabre
        // а случай, если придется на неё пересесть
        $result = [
            'headers' => [],
            'body' => '',
            'statusCode' => null,
        ];
        
        $client = $this->webdavcon_get_client($config);
        /** @var \webdav_client $client */
        if ($client->open())
        {
            $decodedpath = urldecode($resourcepath);
            // заголовки получить из мудловской библиотеки не представляется возможным ((
            // придется делать всегда forcedownload
            $result['statusCode'] = $client->get($decodedpath, $result['body']);
            
            // к сожалению, мудлвская библиотека не предоставляет возможности получить загловки
            // пока делаем принудительное скачивание, по крайне мере до тех пор пока не понадобится
            // внести правки в мудловскую библиотеку или начать использовать другие решения
            $result['headers'] = [
                'Content-Description' => ['File Transfer'],
                'Content-Type' => ['application/octet-stream'],
                'Content-Disposition' => ['attachment', 'filename="'.pathinfo($decodedpath, PATHINFO_BASENAME).'"'],
                'Content-Transfer-Encoding' => ['binary'],
                'Expires' => ['0'],
                'Cache-Control' => ['must-revalidate'],
                'Pragma' => ['public'],
                'Content-Length' => [strlen($result['body'])],
            ];
            $client->close();
        }
        
        return $result;
    }
}