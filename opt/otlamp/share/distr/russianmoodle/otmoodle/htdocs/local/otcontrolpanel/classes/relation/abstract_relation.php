<?php
namespace local_otcontrolpanel\relation;

use JsonSerializable;
use local_otcontrolpanel\entity\abstract_entity;
use local_otcontrolpanel\field\property_field;
use local_otcontrolpanel\filter_form_parameter;

abstract class abstract_relation implements JsonSerializable {

    protected $entity;
    protected $relatedentity;
    protected $filterparams=[];

    /**
     * @param abstract_entity $entity - сущность, к которой надо присоединить другую
     * @return abstract_entity - присоединенная сущность
     */
    public function __construct(abstract_entity $entity)
    {
        $this->entity = $entity;
    }

    /**
     * @param \stdClass $record - запись, основываясь на данных которой будет производиться присоединение
     * @return abstract_entity - присоединенная сущность
     */
    abstract public function get_connected_entity($record);

    protected function get_entity_field_value($record, $fieldcode)
    {
        $field = new property_field($this->entity, $fieldcode);
        return $field->get_value($record);
    }

    /**
     * Получить код связи
     * @return string
     */
    public function get_code() {
        $reflect = new \ReflectionClass($this);
        return $reflect->getShortName();
    }

    public function get_full_code() {
        return $this->entity->get_full_code().'_r_'.$this->get_code();
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

    /**
     * Получить код связанной сущности
     * при привязке сущности без особой логики, считается, что код связи (он же имя файла и класса) будет
     * соответствовать имени таблицы привязываемой сущности, поэтому по умолчанию будет использоваться имя класса
     * @return string
     */
    public function get_related_entity_storagename() {
        $reflect = new \ReflectionClass($this);
        return $reflect->getShortName();
    }

    public function get_related_entity() {
        $relatedentity = abstract_entity::instance($this->get_related_entity_storagename());
        $relatedentity->set_view($this->entity->get_view());
        $relatedentity->set_filter_params($this->get_filter_params());
        return $relatedentity;
    }


    public function set_filter_params(array $filterparams) {
        $this->filterparams = $filterparams;
    }

    public function get_filter_params() {
        return $this->filterparams;
    }

    /**
     * Получение значения параметра из данных формы фильтрации по коду
     * @param string $parametercode - код параметра, заявленного в связи (см. get_supported_filter_form_parameters)
     * @param mixed $default - значение, которое вернется в случае, если искомый параметр не был заявлен связью
     * @return mixed
     */
    public function get_filter_form_parameter_value(string $parametercode, $default=null) {

        $relatedentity = $this->get_related_entity();
        $filterparams = $relatedentity->get_filter_params();
        $parameters = $this->get_supported_filter_form_parameters();
        foreach ($parameters as $parameter)
        {
            if ($parameter->get_name() == $parametercode)
            {
                return $parameter->get_value($filterparams);
            }
        }
        return $default;
    }

    /**
     * Получение параметров, которые умеет обрабатывать связь (докидывать фильтры по ним)
     * @return filter_form_parameter[]
     */
    public function get_supported_filter_form_parameters() {
        return [];
    }

    public function jsonSerialize()
    {
        return [
            'relation-code' => $this->get_code(),
            'relation-fullcode' => $this->get_full_code(),
            'relation-displayname' => $this->get_display_name(),
            'relation-relatedentity' => $this->get_related_entity()
        ];
    }
}