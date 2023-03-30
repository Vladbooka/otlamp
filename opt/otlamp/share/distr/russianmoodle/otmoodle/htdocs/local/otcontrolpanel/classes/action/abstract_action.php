<?php
namespace local_otcontrolpanel\action;

use local_otcontrolpanel\actionform;
use local_otcontrolpanel\view;
use local_otcontrolpanel\entity\abstract_entity;
use local_otcontrolpanel\filter\property_in;

abstract class abstract_action {

    protected $view;

    public function __construct(view $view) {
        $this->view = $view;
    }

    protected function get_session_var_name() {
        return 'otcp_'.$this->entity->get_full_code();
    }

    /**
     * @param int[] $ids - идентификаторы объектов, для которых необходимо получить значения
     * @param \MoodleQuickForm $mform
     */
    public function get_selected_objects($ids) {
        global $OUTPUT;

        $objects = [];

        $entitycode = $this->view->entity->get_storagename();
        $entity = abstract_entity::instance($entitycode);
        $field = $entity->add_default_field();
        if (!is_null($field))
        {// у сущности есть настроенное дефолтное поле для отражения
            $entity->add_filter(new property_in($entity, 'id', $ids));
            $tablecontext = $entity->get_table()->export_for_template($OUTPUT);
            $table = $tablecontext['table'] ?? [];
            $tbody = $table['body'] ?? [];
            $rows = $tbody['rows'] ?? [];
            foreach($rows as $row)
            {
                $cells = $row['cells'] ?? [];
                foreach($cells as $cell)
                {
                    if (!empty($cell['value']))
                    {
                        $objects[] = $cell['value'];
                    }
                }
            }
        }

        return $objects;
    }

    public static function get_list_actions($viewcode)
    {
        $listactions = [];
        $views = view::get_configured_views();
        $currentview = null;
        foreach($views as $view)
        {
            if ((string)$view->code === (string)$viewcode)
            {
                $currentview = $view;
                break;
            }
        }
        if (!is_null($currentview))
        {
            $listactions = $currentview->get_known_actions();
        }

        return $listactions;
    }

    /**
     * @param actionform $actionform
     * @param \MoodleQuickForm $mform
     */
    abstract public function definition_after_data(&$actionform, &$mform);
    abstract public function validation(&$actionform, $data, $files);
    abstract public function process_form_data(&$actionform, $formdata);

    /**
     * Получить код действия
     * @return string
     */
    public function get_code() {
        $reflect = new \ReflectionClass($this);
        return $reflect->getShortName();
    }

    /**
     * Получить полный код сущности вида a_[код действия]
     * @return string
     */
    public function get_full_code() {
        return $this->view->entity->get_full_code() . '_a_'.$this->get_code();
    }

    public function get_default_display_name() {
        return $this->get_code();
    }

    public function get_display_name() {
        if (get_string_manager()->string_exists($this->get_full_code(), 'local_otcontrolpanel'))
        {
            return get_string($this->get_full_code(), 'local_otcontrolpanel');

        } else {
            return $this->get_default_display_name();
        }
    }
}