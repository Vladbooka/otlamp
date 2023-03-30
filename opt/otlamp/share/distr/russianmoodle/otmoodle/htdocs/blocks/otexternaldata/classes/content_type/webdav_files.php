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

namespace block_otexternaldata\content_type;

class webdav_files extends \block_otexternaldata\content_type {
    
    use \block_otexternaldata\connector\webdav;
    
    public function extend_form_definition(&$mform)
    {
        $this->webdavcon_extend_form_definition($mform);
        return true;
    }
    
    public function compose_config(array $formdata)
    {
        return $this->webdavcon_compose_config($formdata);
    }
    
    protected function dirpath_substitutions()
    {
        return $this->get_standard_substitutions();
    }
    
    private function get_dirpath(array $config)
    {
        $simpleconfig = $this->webdavcon_get_simple_config($config);
        $dirpath = $simpleconfig['dirPath'];
        
        $dirpath = $this->replace_substitutions($this->dirpath_substitutions(), $dirpath);
        
        return $dirpath;
    }
    
    
    protected function get_items_for_template(array $config)
    {
        $items = $this->get_items($config);
        // в ключах - пути до файлов, их не отдаем
        return array_values($items);
    }
    
    protected function get_items(array $config)
    {
        return $this->webdavcon_get_items(
            $config,
            $this->get_dirpath($config),
            $this->blockinstance->id,
            true
        );
    }
    
    protected function get_additional_data(array $config) {
        $resources = $this->webdavcon_get_items(
            $config,
            $this->get_dirpath($config),
            $this->blockinstance->id,
            false
        );
        return [
            // в ключах - пути до файлов, их не отдаем
            'resources' => array_values($resources),
            'has_resources' => !empty($resources)
        ];
    }
    
    public function validate_config(array $formdata)
    {}

    public function get_item_file(array $config, $data)
    {
        $resourcepath = null;
        $hash = $data;
        
        $resources = $this->get_items($config);
        
        foreach($resources as $url => $resource)
        {
            if ($resource['hash'] == $hash)
            {
                $resourcepath = parse_url($url, PHP_URL_PATH);
                break;
            }
        }
        
        if (is_null($resourcepath))
        {// файл с переданным хэшом не найден или находится в другой директории
            throw new \Exception('Access denied');
        }
        
        return $this->webdavcon_get_file($config, $resourcepath);
    }
    
}