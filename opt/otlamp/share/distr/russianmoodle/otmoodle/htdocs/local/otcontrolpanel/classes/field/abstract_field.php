<?php
namespace local_otcontrolpanel\field;

use JsonSerializable;
use local_otcontrolpanel\entity\abstract_entity;
use local_otcontrolpanel\relation\abstract_relation;
use local_otcontrolpanel\modifier\common\abstract_modifier;
use local_otcontrolpanel\sql_join;

abstract class abstract_field implements JsonSerializable  {

    protected $config;
    protected $entity;
    protected $displayname;
    protected $template=[];
    /** @var abstract_modifier[] */
    protected $modifiers=[];

    /** @var sql_join[] */
    protected $registered_joins=[];

    public function __construct(abstract_entity $entity, $displayname=null)
    {
        $this->entity = $entity;
        $this->displayname = $displayname;
    }

    public function set_config($fieldconfig)
    {
        $this->config = $fieldconfig;
    }

    /**
     * @param abstract_entity $entity
     * @param string $relationcode
     * @throws \coding_exception
     * @return abstract_relation
     */
    protected static function get_relation(abstract_entity $entity, string $relationcode) {

        $classname = '\\local_otcontrolpanel\\entity\\'.$entity->get_storagename().'\\relations\\'.$relationcode;
        if (!class_exists($classname))
        {
            throw new \coding_exception('Unknown relation '.$relationcode.' for entity '.$entity->get_code());
        }
        return new $classname($entity);
    }

    /**
     *
     * @param abstract_entity $entity - сущность, которой должно принадлежать добавляемое поле
     * @param array $fieldconfig - массив с конфигурацией поля, в зависимости от типа поля может быть разным
     *              тип поля определяется автоматически:
     *                  - если указан relationcode, считается, что требуется поле related_table
     *                  - если не указан relationcode и найден класс для fieldcode, используется соотв. класс
     *                  - если не указан relationcode и не найден класс для fieldcode, используется property_field
     *              для классов из сущностей
     *                  - набор необходимых данных может меняться в зависимости от реализации
     *                  - fieldcode - название поля, соответствующее классу сущности
     *                  - displayname (отображаемое имя, необязательно)
     *              для property_field нужны ключи
     *                  - fieldcode (название свойства),
     *                  - displayname (отображаемое имя, необязательно)
     *              для related_table нужны ключи
     *                  - relationcode (код связи, по дефолту соответствует названию связываемой сущности)
     *                  - fields (массив полей в таком же формате как и этот конфиг),
     *                  - displayname (отображаемое имя, необязательно)
     *                  - Для такого поля можно определить параметры фильтрации filterparams, например:
     *                    filterparams: {timecreated_start: date_start, timecreated_end: date_end}
     *                    В конфиге, в массиве параметров фильтрации указывается
     *                    - в ключах - название параметра, принимаемого фильтрами/связями, используемыми в поле,
     *                    - в значениях - название поля, как было указано в конфиге формы фильтрации
     *              - Для любого поля можно указать модификаторы, для этого необходимо в конфиге использовать ключ modifiers, например:
     *              modifiers: [profilelink]
     *              В конфиге, в массиве модификаторов указывается код модификатора, поиск которого будет производиться среди
     *              доступных модификаторов как общих, так и реализованных в сущности, к которой привязано поле.$this
     *              Если реализация указанного модификатора не будет найдена, модификатор просто не применится.

     *
     *
     * @return abstract_field
     */
    public static function instance(abstract_entity $entity, array $fieldconfig) {

        $displayname = $fieldconfig['displayname'] ?? null;
        $relationcode = $fieldconfig['relationcode'] ?? null;

        if (!is_null($relationcode))
        {
            if (!array_key_exists('fields', $fieldconfig))
            {
                throw new \Exception('Fields was not found in field config');
            }
            $relation = self::get_relation($entity, $relationcode);
            // добавление параметров фильтрации к полю
            if (array_key_exists('filterparams', $fieldconfig) && is_array($fieldconfig['filterparams']))
            {
                $relation->set_filter_params($fieldconfig['filterparams']);
            }
            $field = new related_table($entity, $relation, $fieldconfig['fields'], $displayname);
        } else {

            if (!array_key_exists('fieldcode', $fieldconfig))
            {
                throw new \Exception('Fieldcode was not found in field config');
            }
            $fieldcode = $fieldconfig['fieldcode'];
            $classname = '\\local_otcontrolpanel\\entity\\'.$entity->get_storagename().'\\fields\\'.$fieldcode;
            if (class_exists($classname))
            {
                $field = new $classname($entity, $displayname);
            } else {

//                 // добавить проверку существования свойства у сущности
//                 throw new \coding_exception('Unknown field '.$fieldcode.' for entity '.$this->get_code());

                $field = new property_field($entity, $fieldcode, $displayname);
            }
        }
        $field->set_config($fieldconfig);


        // добавление модификаторов к полю
        if (array_key_exists('modifiers', $fieldconfig) && is_array($fieldconfig['modifiers']))
        {
            foreach($fieldconfig['modifiers'] as $modifiercode)
            {
                try {
                    $field->add_modifier($modifiercode);
                } catch(\Exception $ex) {}
            }
        }

        // установка предпочтительного шаблона отображения
        if (array_key_exists('template', $fieldconfig))
        {
            foreach(self::get_known_template_types() as $templatetype)
            {
                if (is_string($fieldconfig['template']))
                {
                    $field->set_template($templatetype, $fieldconfig['template']);

                } else if (is_array($fieldconfig['template']))
                {
                    if (array_key_exists($templatetype, $fieldconfig['template']))
                    {
                        $field->set_template($templatetype, $fieldconfig['template'][$templatetype]);
                    }
                }
            }
        }
        return $field;
    }

    protected static function get_known_template_types() {
        return ['table', 'list', 'singlevalue', 'nofields'];
    }

    public function set_template($templatetype, $templatename)
    {
        if (in_array($templatetype, self::get_known_template_types()))
        {
            $this->template[$templatetype] = $templatename;
        }
    }

    public function add_modifier($modifiercode)
    {
        $modifiers = $this->entity->get_known_modifiers();
        if (array_key_exists($modifiercode, $modifiers))
        {
            $this->modifiers[] = $modifiers[$modifiercode];
        }
    }

    /**
     * Метод, возвращающий простое значение, до применения модификаторов
     * @param \stdClass $record
     * @param string $suffix -  доп.значение, содержащее информацию о строке и столбце в таблице,
     *                          требуется для уникальной маркировки связанных таблиц,
     *                          скорее всего проблема решаемая суффиксом имеет лучшее решение,
     *                          но не удалось решить проблему в table при помощи spl_object_hash, как планировалось изначально
     */
    abstract protected function get_plain_value($record, $suffix='');

    /**
     * Добавление фильтров не пользователем, а полем
     * Использовать возможность с осторожностью!
     * По текущей логике должны использоваться только связи подключающие лишь одну строку из присоединяемой таблицы
     * И эти фильтры не должны фильтровать! Только подключать строку из связанной таблицы, чтобы отобразить данные оттуда.
     * TODO: обеспечить программную проверку, чтобы связи, добавляемые полями могли присоединять к строке только одну строку, а условий фильтрации не было
     */
    protected function register_joins() {}

    protected function get_alias($storagename)
    {
        return 'fld_'.$this->get_code().'_'.$storagename;
    }

    protected function register_join(sql_join $join)
    {
        $join->remove_condition_inconsistancy($this->registered_joins);
        $this->registered_joins[$join->get_alias()] = $join;
        return $join;
    }

    protected function register_new_join($storagename, $joincondition, $jointype='LEFT JOIN', $alias=null)
    {
        $alias = $alias ?? $this->get_alias($storagename);
        $joincondition = str_replace('{'.$storagename.'}', $alias, $joincondition);
        $join = new sql_join($jointype, $storagename, $alias, $joincondition);

        return $this->register_join($join);
    }

    public function get_joins() {
        $this->register_joins();
        return $this->registered_joins;
    }

    public function get_value($record, $suffix='')
    {
        $value = $this->get_plain_value($record, $suffix);
        return $this->apply_modifiers($value, $record);
    }

    protected function apply_modifiers($value, $record)
    {
        foreach ($this->modifiers as $modifier)
        {
            $value = $modifier->modify($value, $record);
        }
        return $value;
    }

    protected function get_record_property($record, $property)
    {
        if (!property_exists($record, $property))
        {
            throw new \coding_exception('Property '.$property.' is missing');
        }
        return $record->{$property};
    }

    /**
     * Получить код поля
     * @return string
     */
    public function get_code() {
        $reflect = new \ReflectionClass($this);
        return $reflect->getShortName();
    }

    public function get_full_code() {
        return $this->entity->get_full_code().'_fld_'.$this->get_code();
    }

    public function get_default_display_name() {
        return $this->get_code();
    }

    public function get_display_name() {
        if (!is_null($this->displayname))
        {
            return $this->displayname;
        } else {
            if (get_string_manager()->string_exists($this->get_full_code(), 'local_otcontrolpanel'))
            {
                return get_string($this->get_full_code(), 'local_otcontrolpanel');

            } else {
                return $this->get_default_display_name();
            }
        }
    }

    public function jsonSerialize()
    {
        return [
            'field-code' => $this->get_code(),
            'field-fullcode' => $this->get_full_code(),
            'field-displayname' => $this->get_display_name(),
        ];
    }

}