<?php
namespace local_otcontrolpanel;
use JsonSerializable;
use moodle_url;
use local_otcontrolpanel\entity\entity;
use local_otcontrolpanel\entity\abstract_entity;

class view implements JsonSerializable {

    const public = ['code','entity','fields','displayname','config','editdisabled'];
    protected $code;
    protected $displayname;
    protected $entity;
    protected $fields=[];
    protected $filterform;
    protected $config;
    protected $formactionurl=null;
    protected $editdisabled=false;

    public function __construct($viewcode, array $viewconfig, moodle_url $formactionurl=null) {

        if (!array_key_exists('entitycode', $viewconfig))
        {
            throw new \Exception('Entitycode was not found in config (view '.$viewcode.')');
        }
        if (!array_key_exists('fields', $viewconfig))
        {
            throw new \Exception('Fields was not found in config (view '.$viewcode.')');
        }

        $this->config = $viewconfig;
        $this->code = $viewcode;
        $this->set_displayname($viewconfig['displayname'] ?? null);
        $this->set_form_action_url($formactionurl);
        $this->editdisabled = !empty($viewconfig['editdisabled']);

        if (array_key_exists('filterform', $viewconfig))
        {
            $this->set_filter_form($viewconfig['filterform']);
        }
        
        //Преобразуем код в название хранилища, так как дальше понадобится именно название хранилища.
        $entitystorage = abstract_entity::get_storagename_by_entitycode($viewconfig['entitycode']);
        
        $this->set_entity($entitystorage, ($viewconfig['filterparams'] ?? []));
        $this->set_filters($viewconfig['filters'] ?? []);
        $this->set_fields($viewconfig['fields']);
    }

    public function __get($prop) {
        if (in_array($prop, static::public) && property_exists($this, $prop))
        {
            return $this->{$prop};
        }
        throw new \Exception("Inexistent property: $prop");
    }

    public function __isset($prop) {
        if (in_array($prop, static::public))
        {
            return isset($this->{$prop});
        }
        return false;
    }

    private function set_displayname(string $displayname=null)
    {
        if (is_null($displayname))
        {
            $a = new \stdClass();
            $a->entityname = $this->entity->get_display_name();
            $a->viewcode = $this->code;
            $displayname = get_string('tab_noname', 'local_otcontrolpanel', $a);
        }
        $this->displayname = $displayname;
    }

    private function set_entity(string $entitystorage, array $filterparams=[]) {
        $this->entity = entity::instance($entitystorage);
        $this->entity->set_filter_params($filterparams);
        $this->entity->set_view($this);
    }

    public function get_filter_form()
    {
        return $this->filterform;
    }

    private function set_filter_form($filterformconfig) {

        // в конфиге панели позволяем не объявлять ключ класса при описании формы
        // поэтому дополняем его самостоятельно перед тем как парсить
        if (is_array($filterformconfig))
        {
            if (!array_key_exists('class', $filterformconfig))
            {
                $filterformconfig['class'] = $filterformconfig;
            }
        }

        $filterformconfigyaml = \otcomponent_yaml\Yaml::dump($filterformconfig);

        $result = \otcomponent_customclass\utils::parse($filterformconfigyaml);

        if ($result->is_form_exists())
        {
            // Форма
            $this->filterform = $result->get_form();
            // выяолняем отложенный вызов конструктора формы
            $this->filterform->setForm($this->formactionurl);
        }
    }

    protected function set_form_action_url(moodle_url $action=null) {
        $this->formactionurl = $action;
    }

    private function set_fields($fieldsconfig) {
        if (is_array($fieldsconfig))
        {
            foreach($fieldsconfig as $fieldconfig)
            {
                try {
                    $this->fields[] = $this->entity->add_field($fieldconfig);
                } catch(\Exception $ex) {
                    debugging('Error occured while adding field ('.json_encode($fieldconfig).')' .
                                'to entity ('.$this->entity->get_code().')');
                    continue;
                }
            }
        }
    }

    private function set_filters($filtersconfig)
    {
        if (is_array($filtersconfig))
        {
            foreach($filtersconfig as $filterconfig)
            {
                try {
                    $this->entity->add_filter_by_config($filterconfig);
                } catch(\Exception $ex) {
                    debugging('Error occured while adding filter ('.json_encode($filterconfig).')' .
                        'to entity ('.$this->entity->get_code().'): '.$ex->getMessage());
                    continue;
                }
            }
        }
    }

    public static function get_view_by_code($viewcode)
    {
        $views = self::get_configured_views();
        foreach($views as $view)
        {
            if ((string)$view->code === (string)$viewcode)
            {
                return $view;
            }
        }
        return null;
    }

    public static function get_configured_views(moodle_url $formactionurl=null) {
        $views = [];
        $config = config::get_config();
        foreach($config as $viewcode => $viewconfig)
        {
            try {
                $views[] = new view($viewcode, $viewconfig, $formactionurl);
            } catch(\Exception $ex) {
                debugging('Error occured while constructing view ('.$viewcode.')');
                continue;
            }
        }
        return $views;
    }

    public function jsonSerialize()
    {
        return [
            'view-code' => $this->code,
            'view-displayname' => $this->displayname,
            'view-config' => $this->config,
            'view-editdisabled' => $this->editdisabled,
        ];
    }


    public function get_known_actions() {

        global $CFG;

        $actions = [];
        $pattern = $CFG->dirroot.'/local/otcontrolpanel/classes/entity/'.$this->entity->get_code().'/actions/*.php';
        foreach(glob($pattern) as $actionfilepath)
        {
            $actioncode = basename($actionfilepath, ".php");
            $actionclassname = '\\local_otcontrolpanel\\entity\\'.$this->entity->get_code().'\\actions\\'.$actioncode;
            if (class_exists($actionclassname))
            {
                /** @var abstract_action $action */
                $action = new $actionclassname($this);
                $actions[$action->get_full_code()] = $action;
            }
        }
        return $actions;
    }

}