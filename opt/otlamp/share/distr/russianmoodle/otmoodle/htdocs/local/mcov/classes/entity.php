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
 * Настраиваемые поля. Локальные функции
 *
 * @package    local_mcov
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_mcov;

use otcomponent_yaml\Yaml;
use context;
use settings_navigation;
use Exception;
use context_system;
use required_capability_exception;
use local_mcov\hcfield;

class entity {

    /** @var array - свойства полей формы, доступ к которым мы искуственно ограничиваем, чтобы поддерживать только простой инстурмент для управления значениями полей*/
    const unwanted_form_field_properties = ['repeatgroup', 'rules', 'disabledif', 'autoindex', 'expanded', 'advanced', 'helpbutton'];
    /** @var array - типы полей формы, поддерживаемые плагином */
    const allowed_form_field_types = ['text', 'textarea', 'select', 'checkbox', 'date_selector', 'submit'];
    /** @var string $code - код редактируемой сущности */
    protected $code;
    /** @var mixed $objid - идентификатор редактируемого объекта */
    protected $objid;
    /** @var \otcomponent_customclass\parsers\form\customform - форма, собранная на основе пользовательского конфига и докинутых в него плагинами полей */
    protected $customform = null;
    /**
     * Список обрабатываемых событий и их обработчиков
     * @var array
     */
    protected static $events = [];
    /**
     * Список прав, необходый для доступа к редактированию свойств сущности
     * По умолчанию moodle/site:config, если не нужно проверять права, указывайте null в своем классе
     * @var array
     */
    protected $editcapabilities = ['moodle/site:config'];

    /** @var hcfield[] - служебные поля, объявленные другими плагинами, свойственные сущности */
    protected $hcfields;
    /** @var array - массив конфигов всех полей: и заявленных плагинами, и настроенных через конфигурацию формы сущности */
    private $configuredfields;

    public function __construct($code, $objid=null, $actionurl=null)
    {
        $this->code = $code;
        $this->objid = $objid;
        $this->set_form(null, $actionurl);
    }

    /**
     * Является ли переданное поле - полем текущей сущности, докинутым плагином (а не заданным пользователем через настройки)
     * @param string $prop - название поля
     * @return boolean
     */
    protected function is_hardcoded_entity_field($prop) {
        $hcfields = $this->get_hardcoded_entity_fields();
        return array_key_exists($prop, $hcfields);
    }

    /**
     * Получить поле, принадлежащее текущей сущности, докинутое плагином
     * @param string $prop - название поля
     * @throws hcfield_exception - если поле не найдено с кодом 404
     * @return \local_mcov\hcfield
     */
    protected function get_hardcoded_entity_field($prop) {
        if ($this->is_hardcoded_entity_field($prop))
        {
            $hcfields = $this->get_hardcoded_entity_fields();
            return $hcfields[$prop];
        } else {
            throw new hcfield_exception("Hcfield not found", 404);
        }
    }

    /**
     * Получить все поля текущей сущности, докинутые плагинами
     * @param string $prop - название поля
     * @throws hcfield_exception - если поле не найдено с кодом 404
     * @return \local_mcov\hcfield
     */
    public function get_hardcoded_entity_fields(){
        if (!isset($this->hcfields))
        {
            $mcovhcfields = $this->get_hardcoded_mcov_fields();
            $reflect = new \ReflectionClass($this);
            $entitycode = $reflect->getShortName();
            if (array_key_exists($entitycode, $mcovhcfields))
            {
                $this->hcfields = $mcovhcfields[$entitycode];
            } else {
                $this->hcfields = [];
            }
        }
        return $this->hcfields;
    }


    /**
     * Добавление захардкоженных полей для сущности
     * Даже если админ не настраивал полей для формы, некоторые могут быть добавлены,
     * поскольку необходимы для использования в других плагинах
     *
     * @param array $fields - массив полей формы
     */
    protected function add_hardcoded_fields(array &$fields, $checkcap=true)
    {
        // добавление полей, запрошенных другими плагинами
        foreach($this->get_hardcoded_entity_fields() as $hcfield)
        {
            // поле, редактировать которое нам запрещено, мы не добавляем на форму
            if ($checkcap && !$hcfield->has_edit_capability($this->objid)) {
                continue;
            }

            // даже если поле с таким кодом уже было - переопределяем, так заложено сценарием:
            // "Если поле добавлено плагином, то переопределить его через настройки yml уже невозможно"
            $fields[$hcfield->get_prop()] = $hcfield->get_config();
        }
    }

    /**
     * Добавление сконфигурированных полей для сущности
     * Будут добавлены те поля, которые админ настроил для формы
     *
     * @param array $fields - массив полей формы
     * @param string $config - конфиг формы в yaml
     */
    protected function add_configured_fields(array &$fields, $config, $checkcap=true) {

        if ($checkcap && !$this->has_edit_capabilities()) {
            return;
        }

        $configfields = $this->get_config_fields($config);

        // получение полей из yaml-разметки в виде конфигурационного массива
        $fields = array_merge($fields, $configfields);

        // добавим префиксы к полям, объявленным через интерфейс
        $fields = $this->set_public_fields_prefix($fields);

        // удаление свойств полей, которые пока не реализованы
        $this->remove_unwanted_form_field_properties($fields);

        // удаление полей, тип которых не соответствует ожидаемым
        $this->remove_not_allowed_form_field_types($fields);

    }

    /**
     * Получить все поля, докинутые плагинами, сгруппированные по сущностям (в ключах - код сущности)
     * @return \local_mcov\hcfield[]
     */
    private function get_hardcoded_mcov_fields(){
        /** @var hcfield[] $hcfields - поля для хардкода, заявленные плагинами, сгруппированные по сущностям */
        $hcfields = [];
        $pluginswithfunction = get_plugins_with_function('get_hardcoded_mcov_fields', 'lib.php');
        foreach ($pluginswithfunction as $plugins) {
            foreach ($plugins as $function) {
                try {
                    foreach($function() as $hcfield)
                    {
                        if (is_a($hcfield, '\\local_mcov\\hcfield'))
                        {
                            $entitycode = $hcfield->get_entity_code();
                            if (!array_key_exists($entitycode, $hcfields))
                            {
                                $hcfields[$entitycode] = [];
                            }
                            $hcfields[$entitycode][$hcfield->get_prop()] = $hcfield;
                        }
                    }
                } catch (\Throwable $e) {
                    debugging("Exception calling '$function':".$e->getMessage(), DEBUG_DEVELOPER, $e->getTrace());
                }
            }
        }
        return $hcfields;
    }

    protected function get_form()
    {
        if (!is_null($this->customform))
        {
            return $this->customform;
        } else
        {
            throw new entity_exception(get_string('exception_entity_form_not_set', 'local_mcov'));
        }
    }

    /**
     * Получить настройку с конфигом текущей сущности (yaml)
     * @param boolean $quite - true, чтобы не ругался эксепшенами при отсутствии настройки
     * @throws entity_exception
     * @return string
     */
    protected function get_config($quite=false)
    {
        $config = get_config('local_mcov', $this->code.'_yaml');

        if ($config === false && !$quite)
        {
            throw new entity_exception(get_string('exception_entity_config_empty', 'local_mcov'));
        }

        return $config ?? '';
    }

    /**
     * Засунуть поля (в виде конфигурационного массива) обратно в yaml-конфиг
     * @param string $config
     * @param array $fields
     */
    private function set_fields_to_config(&$config, $fields)
    {
        try {

            // парсинг разметки
            $configarr = Yaml::parse($config, yaml::PARSE_OBJECT);
            if (empty($configarr))
            {
                $configarr = [];
            }
            if (is_array($configarr))
            {
                $configarr['class'] = $fields;
            }

            $config = \otcomponent_yaml\Yaml::dump($configarr);

        } catch (\Exception $e) {}
    }

    /**
     * Получить список всех публичных полей сущности
     * @return array - [код поля => название поля]
     */
    public function get_public_fields_list() {
        $result = [];
        // все поля текущего конфига, вместе с докинутыми от плагинов, не зависимо от прав
        $fields = $this->get_configured_fields($this->get_config(true));
        foreach($fields as $fieldname => $fieldconfig) {
            if (preg_match('/^pub_/', $fieldname) && $fieldconfig['type'] != 'submit')
            {
                $result[$fieldname] = $fieldconfig['label'] ?? $fieldname;
            }
        }
        return $result;
    }

    /**
     * Получить конфиги всех полей: заявленных плагинами и настроенных через
     * конфигурацию формы, после всех обработок на допустимые своейства и типы полей формы
     * @param string $config - конфиг формы в yaml
     * @return array - [код поля => конфиг поля]
     */
    protected function get_configured_fields($config) {

        if (!isset($this->configuredfields))
        {
            $fields = [];

            // добавление полей, сконфигурированных админом
            $this->add_configured_fields($fields, $config);

            // добавление полей, докинутых плагинами
            $this->add_hardcoded_fields($fields);

            $this->configuredfields = $fields;
        }

        return $this->configuredfields;
    }

    /**
     * Получить все поля, на редактирование которых имеются права
     * * @return array - [код поля => конфиг поля]
     */
    protected function get_editable_fields() {
        $editablefields = [];
        $configfields = $this->get_configured_fields($this->get_config(true));
        foreach ($configfields as $propname => $propconfig)
        {
            if ($this->is_hardcoded_entity_field($propname))
            {
                // объявивший поле плагин "сообщает", можно ли редактировать именно это поле, для этой сущности,
                // данному конкретному пользователю в текущий момент или нельзя. Права local_mcov
                // самим local_mcov при этом не используются (хотя объявивший поле плагин и может их задействовать).
                $hcfield = $this->get_hardcoded_entity_field($propname);
                if ($hcfield->has_edit_capability($this->objid))
                {
                    $editablefields[$propname] = $propconfig;
                }
            } else {
                if ($this->has_edit_capabilities())
                {
                    $editablefields[$propname] = $propconfig;
                }
            }
        }
        return $editablefields;
    }

    /**
     * Проверить, есть ли хоть одно поле, редактирование которого доступно текущему пользователю
     * @return boolean
     */
    public function has_editable_fields() {

        $config = $this->get_config(true);

        $configfields = [];
        // добавление полей, сконфигурированных админом
        $this->add_configured_fields($configfields, $config, false);
        // добавление полей, докинутых плагинами
        $this->add_hardcoded_fields($configfields, false);

        foreach (array_keys($configfields) as $propname)
        {
            if ($this->is_hardcoded_entity_field($propname))
            {
                // объявивший поле плагин "сообщает", можно ли редактировать именно это поле, для этой сущности,
                // данному конкретному пользователю в текущий момент или нельзя. Права local_mcov
                // самим local_mcov при этом не используются (хотя объявивший поле плагин и может их задействовать).
                $hcfield = $this->get_hardcoded_entity_field($propname);
                if ($hcfield->has_edit_capability($this->objid))
                {
                    return true;
                }
            } else {
                if ($this->has_edit_capabilities())
                {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Получить конфиги полей, описанные в конфигурации формы настраиваемых полей сущности
     * @param string $config - конфиг формы в yaml
     * @return array
     */
    private function get_config_fields($config)
    {
        $fields = [];
        try {

            // парсинг разметки
            $configarr = Yaml::parse($config, yaml::PARSE_OBJECT);

            if (empty($configarr))
            {
                $configarr = [];
            }

            if (is_array($configarr))
            {
                if (!array_key_exists('class', $configarr))
                {
                    $configarr['class'] = [];
                }
                if (is_array($configarr['class']))
                {
                    $fields = $configarr['class'];
                }
            }

        } catch (\Exception $e) {}

        return $fields;
    }

    /**
     * Исключить из конфигов полей неподдерживаемые свойства
     * @param array $fields
     */
    private function remove_unwanted_form_field_properties(array &$fields)
    {
        foreach($fields as $fieldname => $fielddata)
        {
            if (!is_array($fielddata))
            {
                continue;
            }

            foreach(array_keys($fielddata) as $property)
            {
                if (in_array($property, self::unwanted_form_field_properties))
                {
                    unset($fields[$fieldname][$property]);
                }
            }
        }
    }

    /**
     * Имеет ли набор полей поле, являющееся субмитом
     * @param array $fields
     * @return boolean
     */
    protected function fields_have_submit(array $fields)
    {
        foreach ($fields as $fielddata)
        {
            if (is_array($fielddata) && array_key_exists('type', $fielddata))
            {
                if ($fielddata['type'] == 'submit')
                {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Исключить из набора полей поля с неподдерживаемыми типами
     * @param array $fields
     */
    private function remove_not_allowed_form_field_types(array &$fields)
    {
        foreach ($fields as $fieldname => $fielddata)
        {
            if (is_array($fielddata) && array_key_exists('type', $fielddata))
            {
                if (!in_array($fielddata['type'], self::allowed_form_field_types))
                {
                    unset($fields[$fieldname]);
                }
            }
        }
    }

    /**
     * Установить всем передыннм полям префикс pub_
     * @param array $fields
     * @return array
     */
    protected function set_public_fields_prefix($fields) {
        $prefix = 'pub_';
        $result = [];
        foreach($fields as $fieldname => $fielddata) {
            $result[$prefix.$fieldname] = $fielddata;
        }
        return $result;
    }

    protected function preprocess_config($config)
    {
        // Все поля: извлеченные из конфига формы, обработанные и к ним добавлены поля запрошенные плагинами
        $fields = $this->get_configured_fields($config);


        // добавление сабмита (в случае, если пользователь настроил поля, но не добавил ни одного сабмита)
        if (!empty($fields) && !$this->fields_have_submit($fields))
        {
            $fields['submit'] = [
                'type' => 'submit',
                'label' => get_string('fld_submit', 'local_mcov')
            ];
        }

        // кладём поля обратно в конфиг, в yaml разметку
        $this->set_fields_to_config($config, $fields);

        return $config;
    }

    protected function set_form($config=null, $actionurl=null)
    {
        if (is_null($config))
        {
            try {
                $config = $this->get_config();
            } catch (entity_exception $e) {
                /**
                 * @todo добавить обработку исключения
                 */
            }
        }
        $config = $this->preprocess_config($config);
        if (!empty($config)) {

            $result = \otcomponent_customclass\utils::parse($config);

            if ($result->is_form_exists())
            {
                // Форма
                $this->customform = $result->get_form();
                // выяолняем отложенный вызов конструктора формы
                $this->customform->setForm($actionurl);
            }
        }
    }

    public function get_mcovs_select(string $sql, array $params, string $sort='', string $fields='*', int $limitfrom=0, int $limitnum=0)
    {
        global $DB;

        $sql = 'entity=:entity AND ('.$sql.')';
        $params['entity'] = $this->code;

        $mcovs = $DB->get_records_select('local_mcov', $sql, $params, $sort, $fields, $limitfrom, $limitnum);
        if (!empty($mcovs))
        {
            return $mcovs;
        }

        return [];
    }

    public function get_mcovs(array $conditions=[], string $sort='', string $fields='*', int $limitfrom=0, int $limitnum=0)
    {
        global $DB;

        $conditions['entity'] = $this->code;
        $mcovs = $DB->get_records('local_mcov', $conditions, $sort, $fields, $limitfrom, $limitnum);
        if (!empty($mcovs))
        {
            return $mcovs;
        }

        return [];
    }

    public function get_mcov($objid, $property, $imitatenull=true)
    {
        $record = $this->get_object_records($objid, $property);
        if (empty($record) && $imitatenull)
        {
            $record = new \stdClass();
            $record->entity = $this->code;
            $record->objid = $objid;
            $record->prop = $property;
            $record->value = null;
            $record->searchval = null;
            $record->sortval = null;
        }
        return $record;
    }

    /**
     * Метод, позволяющий сущности передать данные в форму в виде, отличном от того, в каком она хранится в БД
     * @param mixed $storedvalue
     * @param string $fieldname
     * @return mixed
     */
    protected function process_stored_field_value($storedvalue, $fieldname)
    {
        try {
            $hcfield = $this->get_hardcoded_entity_field($fieldname);
            // запуск обработки значения полем
            return $hcfield->process_stored_value($storedvalue);
        } catch (\local_mcov\hcfield_exception $ex) {
            // отлавливаем только случаи, когда
            // - нет такого захардкоженного поля (тогда никаких доп.обработок и не надо)
            // - поле не реализовало свою обработку значений - тогда без обработок
            if (!in_array($ex->getCode(), [501, 404])) {
                // остальные ошибки пробрасываем дальше - мы не планировали их обрабатывать
                throw $ex;
            }
        }
        return $storedvalue;
    }

    public function get_object_values($objid)
    {
        $data = [];
        $records = $this->get_object_records($objid);
        foreach($records as $record)
        {
            $data[$record->prop] = $this->process_stored_field_value($record->value, $record->prop);
        }
        return $data;
    }

    protected function get_object_records($objid, $property=null)
    {
        global $DB;

        $result = [];

        $conditions = ['entity' => $this->code, 'objid' => $objid];
        if (!is_null($property))
        {
            $conditions['prop'] = $property;
        }

        $mcovs = $DB->get_records('local_mcov', $conditions);
        if (!empty($mcovs))
        {
            if (!is_null($property) && count($mcovs) == 1)
            {
                $result = array_shift($mcovs);
            } else
            {
                $result = $mcovs;
            }
        }

        return $result;
    }

    public function set_form_data()
    {
        $objid = $this->objid;

        // Форма редактирования настраиваемых полей
        $customform = $this->get_form();
        // Кастомные поля формы
        $formfields = $customform->get_fields();
        // Значения, сохраненные в базе по объекту
        $storedvalues = $this->get_object_values($objid);

        // В форму требуется установить только те значения, которые есть в текущем составе формы - фильтруем
        $data = array_filter($storedvalues, function($k) use ($formfields) {
            return array_key_exists($k, $formfields);
        }, ARRAY_FILTER_USE_KEY);

        // Установка значений в форму
        $customform->set_data($data);
    }

    public function save_mcov($record)
    {
        global $DB;

        if (property_exists($record, 'id'))
        {
            return $DB->update_record('local_mcov', $record);

        } else
        {
            return $DB->insert_record('local_mcov', $record);
        }
    }

    /**
     * Обработка, вызывающаяся при отсутствии полей на форме перед их удалением
     * можно предотвратить удаление, например, когда опле отсутствует на форме из-за отсутствия прав
     * @param array $propertiestoremove
     */
    protected function preprocess_properties_to_remove(array &$propertiestoremove) {

        $hcfields = $this->get_hardcoded_entity_fields();
        foreach($propertiestoremove as $k => $propname)
        {
            if (array_key_exists($propname, $hcfields))
            {
                // перед нами захардкоженное поле, у каждого своя реализация права на редактирование
                $hcfield = $hcfields[$propname];

                if (!$hcfield->has_edit_capability($this->objid))
                {
                    // нет прав редактировать это поле, поэтому и удалять не надо
                    // когда нет прав - поле просто должно сохранить старое значение
                    unset($propertiestoremove[$k]);
                }
            } else {
                // перед нами поле, сконфигурированное админом
                // право на редактирование таких полей определяется на уровне сущности
                if (!$this->has_edit_capabilities()) {
                    // нет прав редактировать это поле, поэтому и удалять не надо
                    // когда нет прав - поле просто должно сохранить старое значение
                    unset($propertiestoremove[$k]);
                }
            }
        }
    }

    protected function remove_mcov_properties($objid, array $propertiestoremove)
    {
        global $DB;

        // во время предобработки из списка полей на удаление убираются те,
        // которые по всей видимости существуют, просто к ним нет прав доступа
        // таким образом остаются к удалению только несуществующие, значения по которым надо очистить
        $this->preprocess_properties_to_remove($propertiestoremove);

        if (!empty($propertiestoremove))
        {
            list($sqlin, $params) = $DB->get_in_or_equal($propertiestoremove, SQL_PARAMS_NAMED, 'prop');
            $sql = 'objid=:objid AND prop '.$sqlin;
            $params['objid'] = $objid;
            $DB->delete_records_select('local_mcov', $sql, $params);
        }
    }

    /**
     * Метод, позволяющий сущности сделать собственные преобразования перед сохранением
     *
     * @param \stdClass $formdata
     * @param string $formfieldname
     * @param mixed $oldvalue - ранее сохраненное значение, может быть использовано, если надо
     *                          сохранить старое в виду того, что нет прав менять на новое
     */
    protected function process_form_field_value($formdata, $formfieldname, $oldvalue=null)
    {
        $formvalue = null;
        if (property_exists($formdata, $formfieldname)) {
            $formvalue = $formdata->{$formfieldname};
        }

        try {
            $hcfield = $this->get_hardcoded_entity_field($formfieldname);
            if($hcfield->has_edit_capability($this->objid))
            {
                // запуск обработки значения полем
                return $hcfield->process_form_value($formvalue);
            } else {
                // нет прав на редактирование - оставим значение как было
                return $oldvalue;
            }
        } catch (\local_mcov\hcfield_exception $ex) {
            // отлавливаем только случаи, когда
            // - нет такого захардкоженного поля (тогда никаких доп.обработок и не надо)
            // - поле не реализовало свою обработку значений - тогда без обработок
            if (!in_array($ex->getCode(), [501, 404])) {
                // остальные ошибки пробрасываем дальше - мы не планировали их обрабатывать
                throw $ex;
            }
        }

        return $formdata->{$formfieldname};
    }

    public function process_form()
    {
        $objid = $this->objid;

        // Форма редактирования настраиваемых полей
        $customform = $this->get_form();

        // Обработка отправленной формы
        if ($formdata = $customform->get_data())
        {
            // Массив для сбора обработанных полей
            $processedfields = [];

            // Поля формы
            $formfields = $customform->get_fields();
            // Сохранение формы, если не было удаления
            foreach($formfields as $formfieldname => $formfield)
            {
                // Поле не дефайнилось в форме, но прилетело в дате - игнорируем
                if (!isset($formdata->{$formfieldname}))
                {
                    continue;
                }

                // Тип поля не в разрешенных типах - игнорируем
                if (!in_array($formfield['type'], self::allowed_form_field_types))
                {
                    continue;
                }

                // Субмиты не требуется сохранять
                if ($formfield['type'] == 'submit')
                {
                    continue;
                }

                $processedfields[] = $formfieldname;

                $mcov = $this->get_mcov($objid, $formfieldname);
                $mcov->value = $this->process_form_field_value($formdata, $formfieldname, $mcov->value);
                $mcov->searchval = $this->prepare_searchval($mcov->value, $formfield, $formfieldname);
                $mcov->sortval = $this->prepare_sortval($mcov->value, $formfield, $formfieldname);

                $this->save_mcov($mcov);
            }

            // Все значения, сохраненные в базе по объекту
            $storedvalues = $this->get_object_values($objid);
            // Поля, которые не редактировались сейчас. Подлежат удалению
            // TODO: по мере реализации новый функций может оказаться так, что в базе должны сохраниться и другие значения
            // тогда надо будет реализовать удаление только тех полей, которые не обозначены в конфиге формы
            // или вовсе избавиться от удаления, но вместе с этим предусмотреть возможные проблемы, например, сохранение false для чекбокса (не передается через формдату)
            $fieldstoremove = array_diff(array_keys($storedvalues), $processedfields);
            // Удаление устаревших данных
            $this->remove_mcov_properties($objid, $fieldstoremove);

            return true;
        }
        return false;
    }

    public function get_display_value(string $formfieldname)
    {
        $customform = $this->get_form();
        $element = $customform->get_element($formfieldname);
        return $element->render_display_value();
    }

    protected function prepare_searchval($value, array $formfield, string $formfieldname)
    {
        // очистим от тегов, если вдруг есть, в поисковом поле они не нужны
        $value = strip_tags($value);
        // в базе под поиск выделена ограниченная длина, обрежем
        $value = mb_substr($value, 0, 232);

        return $value;
    }

    protected function prepare_sortval($value, array $formfield, string $formfieldname)
    {
        if (array_key_exists('filter', $formfield) && $formfield['filter'] == 'int')
        {
            return clean_param($value, PARAM_INT);
        }
        if (array_key_exists('type', $formfield) && $formfield['type'] == 'date_selector')
        {
            return clean_param($value, PARAM_INT);
        }
        return null;
    }

    public function render_form()
    {
        // Форма редактирования настраиваемых полей
        $customform = $this->get_form();
        return $customform->render();
    }

    /**
     * Удалить свойства из хранилища
     * @param array $record запись, которую нужно удалить
     * @return boolean
     */
    public function delete_mcov($record) {
        global $DB;
        $record = (array)$record;
        if ($this->code == $record['entity']) {
            // Удаляем только те свойства, которые создавали, чужие не трогаем
            return $DB->delete_records('local_mcov', $record);
        }
        return false;
    }

    /**
     * Узнать подписана ли сущность на обработку события
     * @param \core\event\base $event
     * @return boolean
     */
    public static function is_subscribed($event) {
        return array_key_exists($event->eventname, self::get_events());
    }

    /**
     * Получить список обрабатываемых событий и их обработчиков
     * @return array
     */
    public static function get_events() {
        return self::$events;
    }

    /**
     * Запустить обработчик события
     * @param mixed $event
     */
    public function handle_event($event) {
        $handler = $this->get_events()[$event->eventname];
        if (method_exists($this, $handler)) {
            try {
                $this->$handler($event);
            } catch (Exception $e) {
                /**
                 * @todo Добавить обработку ошибок во время запуска обработчиков событий
                 */
            }
        }
    }

    /**
     * Переопределение навигации по настройкам сущностью
     *
     * @param settings_navigation $settingsnav
     * @param context $context
     */
    public function extend_settings_navigation(settings_navigation $settingsnav, context $context)
    {

    }

    /**
     * Управление навигацией по пользовательскому профилю сущностью
     *
     * @param settings_navigation $settingsnav
     * @param context $context
     */
    public function myprofile_navigation(\core_user\output\myprofile\tree $tree, $user, $iscurrentuser, $course) {

    }

    /**
     * Проверить, есть ли права на редактирование свойств сущности
     * @param mixed $context
     */
    public function has_edit_capabilities($context = null) {

        if (is_null($this->get_edit_capabilities())) {
            return true;
        }
        if (is_null($context)) {
            $context = context_system::instance();
        }
        $result = true;
        foreach ($this->get_edit_capabilities() as $capability) {
            $result = $result && has_capability($capability, $context);
        }
        return $result;
    }

    /**
     * Получить список прав, необходимый для доступа к редактированию свойств сущности
     * @return array
     */
    public function get_edit_capabilities() {
        return $this->editcapabilities;
    }

    /**
     * Проверяет наличие прав для доступа к редактированию свойств сущности и отображает ошибку, если у пользователя нет такой возможности.
     * @param mixed $context
     * @param int $objid - идентификатор редактируемого объекта
     * @throws required_capability_exception
     */
    public function require_edit_capabilities($context = null, $objid = null) {

        if (is_null($this->get_edit_capabilities())) {
            return;
        }

        if (is_null($context)) {
            $context = context_system::instance();
        }
        if (is_null($objid)) {
            $objid = $this->objid;
        }

        foreach ($this->get_edit_capabilities() as $capability) {
            if (!has_capability($capability, $context)) {
                throw new required_capability_exception($context, $capability, 'nopermissions', '');
            }
        }
    }

    /**
     * Имя объекта
     * @return string
     */
    public function get_displayname() {
        return '['.$this->objid.']';
    }

    /**
     * Заголовок объекта
     * @return string
     */
    public function get_entity_title() {
        $a = (object)[
            'entity' => get_string('entity_'.$this->code, 'local_mcov'),
            'object' => $this->get_displayname($this->objid)
        ];
        return get_string('entity_title', 'local_mcov', $a);
    }

    /**
     * Получение заголовка редактирования объекта
     *
     * @param boolean $abstract - абстрактный заголовок без указания сущности
     * @return string
     */
    public function get_edit_entity_title($abstract=false) {
        $abstracttitle = get_string('edit_abstract_entity_title', 'local_mcov');
        if ($abstract) {
            return $abstracttitle;
        }

        $a = (object)[
            'entity_title' => $this->get_entity_title(),
            'entity' => get_string('entity_'.$this->code, 'local_mcov'),
            'object' => $this->get_displayname($this->objid),
            'edit_abstract_entity_title' => $abstracttitle,
        ];
        return get_string('edit_entity_title', 'local_mcov', $a);
    }
}