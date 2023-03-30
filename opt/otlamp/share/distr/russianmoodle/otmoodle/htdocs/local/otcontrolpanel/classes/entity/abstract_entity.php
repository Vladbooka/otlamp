<?php
namespace local_otcontrolpanel\entity;

use JsonSerializable;
use local_otcontrolpanel\sql_join;
use local_otcontrolpanel\table;
use local_otcontrolpanel\filter\abstract_filter;
use local_otcontrolpanel\table_column;
use local_otcontrolpanel\field\abstract_field;
use local_otcontrolpanel\view;

abstract class abstract_entity implements JsonSerializable {

    /**
     * @var abstract_filter[]
     */
    protected $filters=[];

    /**
     * @var \stdClass[] - массив записей, результат выборки
     */
    protected $records;

    /**
     * @var abstract_field[]
     */
    protected $fields=[];

    /**
     * @var table - объект таблицы с данными для отображения в интерфейсе
     */
    protected $table;

    /**
     * Название таблицы в БД, данные которой будут использоваться для формирования таблицы
     * @var string
     */
    protected $storagename;

    /**
     * Код поля, используемого по умолчанию для отображения строки сущности
     */
    protected $defaultfield;

    /** @var view $view - вьюха (вкладка), инициализировавшая данную сущность */
    protected $view;

    /** @var array $filterparams - параметры фильтрации, заданные конфигурацией и прилетевшие из формы фильтрации, к примеру */
    protected $filterparams=[];


    public function __construct() {
        if(!isset($this->storagename))
        {
            throw new \coding_exception(get_class($this) . ' must have a storagename');
        }

        // Временное решение, применяющее всегда дефолтные фильтры
        // В будущем планируется, что дефолтные фильтры будут по умолчанию
        // добавляться при создании вкладки через конфигуратор.
        // Но администратор сможет эти фильтры убрать по желанию.
        foreach($this->get_default_filters() as $filter)
        {
            $this->add_filter($filter);
        }
    }

    /**
     * Массив фильтров добавляемых по умолчанию при создании вкладки по сущности
     * Временно применяется всегда на этапе создания экземпляра класса в виду отстуствия должной
     * реализации в конфигураторе
     * @return array
     */
    protected function get_default_filters() {
        return [];
    }

    /**
     * Получение списка реализованных в коде сущностей в виде существующих классов
     * @return string[]
     */
    protected static function get_entity_classnames() {
        global $CFG;

        $classnames = [];

        $pattern = $CFG->dirroot.'/local/otcontrolpanel/classes/entity/*/*.php';
        foreach(glob($pattern) as $entityfilepath)
        {
            $entitycode = basename($entityfilepath, ".php");
            $entityclassname = '\\local_otcontrolpanel\\entity\\'.$entitycode.'\\'.$entitycode;
            if (class_exists($entityclassname))
            {
                $classnames[] = $entityclassname;
            }
        }

        return $classnames;
    }

    /**
     * @return \local_otcontrolpanel\entity\entity[]
     */
    public static function get_known_entities() {

        $knownentities = [];

        //Ищем все entity в коде.
        foreach(self::get_entity_classnames() as $entityclassname)
        {
            $entity = new $entityclassname();
            $knownentities[$entity->get_code()] = $entity;
        }

        $entitycodes = ['user', 'cohort', 'course', 'enrol'];
        foreach($entitycodes as $entitycode)
        {
            if (!array_key_exists($entitycode, $knownentities))
            {
                $entity = new entity($entitycode);
                $knownentities[$entity->get_full_code()] = $entity;
            }
        }

        return $knownentities;
    }

    public static function is_known_entity($entityfullcode)
    {
        return array_key_exists($entityfullcode, self::get_known_entities());
    }

    /**
     *
     * @return \local_otcontrolpanel\relation\abstract_relation[]
     */
    public function get_known_relations() {
        global $CFG;

        $relations = [];

        $pattern = $CFG->dirroot.'/local/otcontrolpanel/classes/entity/'.$this->get_storagename().'/relations/*.php';
        foreach(glob($pattern) as $relationfilepath)
        {
            $relationcode = basename($relationfilepath, ".php");
            $relationclassname = '\\local_otcontrolpanel\\entity\\'.$this->get_storagename().'\\relations\\'.$relationcode;
            if (class_exists($relationclassname))
            {
                $relation = new $relationclassname($this);
                $relations[$relation->get_full_code()] = $relation;
            }
        }

        return $relations;
    }

    public static function is_known_relation($relationfullcode)
    {
        return array_key_exists($relationfullcode, self::get_known_relations());
    }

    /**
     * @param boolean $withrelations - добавлять ли поля связанных сущностей
     * @return \local_otcontrolpanel\field\abstract_field[]
     */
    public function get_known_fields($withrelations=true) {

        global $DB, $CFG;

        $fields = [];

        // Поля, реализованные в классах
        $pattern = $CFG->dirroot.'/local/otcontrolpanel/classes/entity/'.$this->get_storagename().'/fields/*.php';
        foreach(glob($pattern) as $fieldfilepath)
        {
            $fieldcode = basename($fieldfilepath, ".php");
            if (class_exists('\\local_otcontrolpanel\\entity\\'.$this->get_storagename().'\\fields\\'.$fieldcode))
            {
                $field = abstract_field::instance($this, ['fieldcode' => $fieldcode]);
                $fields[$field->get_full_code()] = $field;
            }
        }

        // Колонки из БД
        $columnsinfo = $DB->get_columns($this->storagename);
        foreach(array_keys($columnsinfo) as $fieldcode)
        {
            $field = abstract_field::instance($this, ['fieldcode' => $fieldcode]);
            $fields[$field->get_full_code()] = $field;
        }

        if ($withrelations)
        {
            // Поля, реализованные в связанных таблицах
            foreach($this->get_known_relations() as $relation)
            {
                $relationentity = $relation->get_related_entity();
                $fields = array_merge($fields, $relationentity->get_known_fields(false));
            }
        }

        return $fields;
    }

    public function is_known_field($fieldfullcode) {
        return array_key_exists($fieldfullcode, $this->get_known_fields());
    }

    /**
     *
     * @return \local_otcontrolpanel\modifier\common\abstract_modifier[]
     */
    public function get_known_modifiers() {
        global $CFG;

        $modifiers = [];

        $pattern = $CFG->dirroot.'/local/otcontrolpanel/classes/modifier/*.php';
        foreach(glob($pattern) as $modifierfilepath)
        {
            $modifiercode = basename($modifierfilepath, ".php");
            $modifierclassname = '\\local_otcontrolpanel\\modifier\\'.$modifiercode;
            if (class_exists($modifierclassname))
            {
                $modifier = new $modifierclassname();
                $modifiers[$modifier->get_code()] = $modifier;
            }
        }

        $pattern = $CFG->dirroot.'/local/otcontrolpanel/classes/entity/'.$this->get_storagename().'/modifiers/*.php';
        foreach(glob($pattern) as $modifierfilepath)
        {
            $modifiercode = basename($modifierfilepath, ".php");
            $modifierclassname = '\\local_otcontrolpanel\\entity\\'.$this->get_storagename().'\\modifiers\\'.$modifiercode;
            if (class_exists($modifierclassname))
            {
                $modifier = new $modifierclassname();
                $modifiers[$modifier->get_code()] = $modifier;
            }
        }

        return $modifiers;
    }


    public function is_known_modifier($modifiercode) {
        return array_key_exists($modifiercode, $this->get_known_modifiers());
    }

    /**
     * @param string $storagename - наименование хранилища сущности (код здесь не подходит,
     *                              так как может быть переопределен для длинноименных сущностей)
     * @throws \coding_exception
     * @return abstract_entity
     */
    public static function instance(string $storagename) {
        global $DB;

        // проверим, есть ли класс под такую сущность
        $classname = '\\local_otcontrolpanel\\entity\\'.$storagename.'\\'.$storagename;
        if (class_exists($classname))
        {
            return new $classname();
        }

        // класса под такую сущность не нашлось
        // если найдем таблицу в БД - воспользуемся универсальной сущностью, соответствующей таблице БД
        $tables = $DB->get_tables();
        if (in_array($storagename, $tables))
        {
            return new entity($storagename);
        }

        // ни класса, ни таблицы нет - неизвестная нам сущность
        throw new \coding_exception('requested unknown entity ('.$storagename.')');
    }

    /**
     * Применение к выборке фильтра (должно производиться до получения данных)
     * @param string $filterconfig
     */
    public function add_filter_by_config(array $filterconfig) {
        $this->add_filter(abstract_filter::instance_by_config($this, $filterconfig));
    }

    /**
     * Применение к выборке фильтра (должно производиться до получения данных)
     * @param string $filtercode
     */
    public function add_filter_by_code($filtercode, $value) {
        $this->add_filter(abstract_filter::instance($this, $filtercode, $value));
    }

    /**
     * Применение к выборке фильтра (должно производиться до получения данных)
     * @param string $filtercode
     */
    public function add_filter(abstract_filter $filter) {
        $this->filters[] = $filter;
        $this->table = null;
        $this->records = null;
    }

    /**
     * Базовые join'ы сущности
     * (должны обеспечивать подключение таблицы так, чтобы не задваивались строки самой сущности)
     *
     * @return sql_join[] - массив джойнов (в ключах - псевдонимы, с которыми будут подключаться таблицы)
     */
    protected function get_basic_joins() {
        return [];
    }

    /**
     * Базовые join'ы сущности
     * (должны обеспечивать подключение таблицы так, чтобы не задваивались строки самой сущности)
     * @param string $alias - псевдоним с которым будет подключена таблица
     * @return sql_join|NULL -
     */
    public function get_basic_join($alias) {
        $basicjoins = $this->get_basic_joins();
        if (array_key_exists($alias, $basicjoins))
        {
            return $basicjoins[$alias];
        }
        throw new \Exception('unknown join alias "'.$alias.'"');
    }

    /**
     * Применить список джинов к параметрам запроса
     * @param sql_join[] $joins
     * @param array $aliases
     * @param array $joinsql
     * @param array $fields
     */
    protected function apply_joins(array $joins, array &$aliases, array &$joinsql, array &$fields)
    {
        foreach($joins as $join)
        {
            if (!in_array($join->get_alias(), $aliases))
            {
                $aliases[] = $join->get_alias();
                $joinsql[] = $join->get_sql();
            }
            $fields = array_merge($fields, $join->get_required_db_fields());
        }
    }

    protected function get_joins_select_sql($select, $joins) {
        foreach ($joins as $join)
        {
            $select = str_replace('{'.$join->get_storagename().'}', $join->get_alias(), $select);
        }
        return $select;
    }

    protected function apply_filters(array &$aliases, array &$joinsql, array &$fields, array &$selects, array &$params) {
        foreach($this->filters as $filter)
        {
            $joins = $filter->get_joins();
            $this->apply_joins($joins, $aliases, $joinsql, $fields);

            $select = $this->get_joins_select_sql($filter->get_select(), $joins);
            if (!empty($select))
            {
                $selects[] = $select;
            }
            $params = array_merge($params, $filter->get_params());
        }
    }

    protected function apply_fields_joins(array &$aliases, array &$joinsql, array &$fields) {
        foreach($this->fields as $field)
        {
            $joins = $field->get_joins();
            $this->apply_joins($joins, $aliases, $joinsql, $fields);
        }
    }

    public function get_records($limitfrom=0, $limitnum=0) {
        global $DB;

        if (isset($this->records))
        {
            return $this->records;
        }

        // условия запроса, которые попадут в where
        $selects = [];
        // параметры для подстановки в условия запроса
        $params = [];
        // алиасы, используемые при джойнах таблиц
        $aliases = [];
        // дополнительные поля, которые следует получить в основном запросе (поля из подключенных таблиц)
        $fields = [];
        // sql код для подключения таблиц
        $joinsql = [];

        // Применение фильтров и аггрегация результатов в параметры будущего запроса
        $this->apply_filters($aliases, $joinsql, $fields, $selects, $params);

        // Применение джойнов из полей, их использующих и аггрегация результатов в параметры будущего запроса
        $this->apply_fields_joins($aliases, $joinsql, $fields);


//         // Определение первого поля в запросе (с уникальными результатами)
//         if (empty($aliases))
//         {
//             // нет подключенных таблиц, таким полем будем считать просто идентификатор
//             $uniquefield = '{'.$this->storagename.'}.id AS "uniqueid"';
//         } else
//         {
//             // надеюсь, этот код никогда не понадобится и подсоединяемые таблицы не будут использовать
//             // связи "ко многим", что приведет к задвоению строк основной сущности, но если вдруг...
//             // то по правилам мудл, нам требуется первое поле с уникальными данными - сделаем его
//             $unique = [];
//             $unique[] = '{'.$this->storagename.'}.id';
//             foreach($aliases as $alias)
//             {
//                 $unique[] = $alias.'.id';
//             }
//             $uniquefield = 'CONCAT ('.implode(', \'_\', ', $unique).') AS "uniqueid"';
//         }

        // Определение первого поля в запросе (с уникальными результатами)
        $uniquefield = 'DISTINCT({'.$this->storagename.'}.id) AS "uniqueid"';

        // Поля сущности, требуемые во время запроса
        $fieldssequence = $uniquefield.', {'.$this->storagename.'}.*';
        // Некоторые поля могут сразу стать частью запроса - учтём их в строке
        if (!empty($fields))
        {
            $fieldssequence .= ', '.implode(', ', $fields);
        }

        // Подключение таблиц, участвующих в запросе
        $tables = '{'.$this->storagename.'} '. implode(' ', $joinsql);

        // Формирование части с условиями запроса
        $select = '';
        if (!empty($selects))
        {
            $select = 'WHERE '.implode(' AND ', $selects);
        }

        // Настройка параметров сортировки
        $sort = '';

        // Полный запрос
        $sql = "SELECT $fieldssequence FROM $tables $select $sort";

//         if ($this->get_code() == 'user')
//         {
//             var_dump($this->get_filter_params());
//             debugging($sql);
//             debugging(json_encode($params, JSON_PRETTY_PRINT));
//         }

        $this->records = $DB->get_records_sql($sql, $params, $limitfrom, $limitnum);
        return $this->records;
    }

    /**
     * Получение объекта таблицы
     */
    public function get_table($suffix='') {

        if (!isset($this->table))
        {
            $this->table = new table($this->get_records(), $suffix);
            foreach($this->fields as $field)
            {
                $this->table->add_column(new table_column($field));
            }
        }
        return $this->table;
    }

    /**
     * Добавление колонки, требуемой для отображения
     * @param array $fieldconfig - массив с конфигурацией поля, в зависимости от типа поля может быть разным
     *              тип поля определяется автоматически:
     *                  - если указан relationcode, считается, что требуется поле related_table
     *                  - если не указан relationcode и найден класс для fieldcode, используется соотв. класс
     *                  - если не указан relationcode и не найден класс для fieldcode, используется property_field
     *              для классов сущностей
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
     */
    public function add_field(array $fieldconfig)
    {
        $field = abstract_field::instance($this, $fieldconfig);
        $this->fields[] = $field;
        $this->table = null;
        return $field;
    }

    /**
     * Добавление поля, используемого по умолчанию для отображения сущности
     * @return \local_otcontrolpanel\field\abstract_field|NULL
     */
    public function add_default_field()
    {
        if (!empty($this->defaultfield))
        {
            $fieldconfig = ['fieldcode' => $this->defaultfield];
            return $this->add_field($fieldconfig);
        }
        return null;
    }

    /**
     * @param \renderer_base $output
     * @return string
     */
    public function auto_render($output, $norecordsstring=null, $options=[])
    {
        $countfields = count($this->fields);
        $syscontext = \context_system::instance();

        // данные для передачи в шаблон
        $context = $this->get_table($options['suffix']??'')->export_for_template($output);
        $context['contextid'] = $syscontext->id;

        // полей - несколько, надо знать как они называются, выводим через таблицу с заголовками
        if ($countfields > 1)
        {
            $templatename = $options['template_table'] ?? 'local_otcontrolpanel/table';
        }
        // поле - одно
        if ($countfields == 1)
        {
            $countrecords = count($this->get_records());
            // значений - много, отобризим в виде списка
            if ($countrecords > 1)
            {
                $templatename = $options['template_list'] ?? 'local_otcontrolpanel/shortlist';
            }
            // значение - тоже одно, через список оно тоже выведется хорошо,
            // но допускаем, что могут быть другие пожелания для singlevalue
            if ($countrecords == 1)
            {
                $templatename = $options['template_singlevalue'] ?? 'local_otcontrolpanel/list';
            }

            // значений нет - надо вывести сообщение об этом
            if ($countrecords == 0)
            {
                $context = [
                    'id' => null,
                    'value' => $norecordsstring ?? get_string('no_records_to_display', 'local_otcontrolpanel'),
                    'contextid' => $syscontext->id,
                ];
                // здесь жестко фиксируем шаблон, так как тут уже не используем табличные результаты
                $templatename = 'local_otcontrolpanel/singlevalue';
            }
        }

        // ни одного поля не добавлено, пока решено в таких случаях
        // по умолчанию отображать количество найденных записей
        if ($countfields == 0)
        {
            $templatename = $options['template_nofields'] ?? 'local_otcontrolpanel/rowscount';
        }
        return $output->render_from_template($templatename, $context);

//         // для сущности не было настроено колонок, это чья-то ошибка
//         return get_string('no_columns_to_display', 'local_otcontrolpanel');
    }

    /**
     * Получить название хранилища (таблицы БД)
     * @return string
     */
    public function get_storagename() {
        return $this->storagename;
    }

    /**
     * Получить название хранилища по коду
     * @param string $entitycode
     * @return mixed string|bool|NULL
     */
    public static function get_storagename_by_entitycode ($entitycode = NULL) {

        if (empty($entitycode)) {
            return FALSE;
        }

        //Ищем все entity в коде.
        foreach(self::get_entity_classnames() as $entityclassname)
        {
            $entity = new $entityclassname();
            if ($entity->get_code() === $entitycode && method_exists($entity, 'get_storagename')) {
                //Если нашлась entity с таким кодом, то возвращаем название хранилища
                return $entity->get_storagename();
            }
        }

        //Если код не пустой и не нашлось entity с таким кодом и методом get_storagename, то вернём код
        return $entitycode;
    }

    /**
     * Получить код сущности вида [класс_сущности]
     * @return string
     */
    public function get_code() {
        $reflect = new \ReflectionClass($this);
        return $reflect->getShortName();
    }

    /**
     * Получить полный код сущности вида e_[класс_сущности]
     * @return string
     */
    public function get_full_code() {
        return 'e_'.$this->get_code();
    }

    public function get_default_display_name() {
        return $this->get_storagename();
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
     * Установка вьюхи (читай вкладки), которая инициализировала данную сущность
     * @param view $view
     */
    public function set_view(view $view=null) {
        $this->view = $view;
    }
    /**
     * Получить вьюху (читай вкладку), которая инициализировала данную сущность
     * @return view
     */
    public function get_view() {
        return $this->view;
    }


    public function set_filter_params(array $filterparams) {

        $this->filterparams = [];

        $view = $this->get_view();
        if (!method_exists($view, 'get_filter_form'))
        {
            return;
        }

        $filterform = $view->get_filter_form();
        if (!method_exists($filterform, 'get_data'))
        {
            return;
        }

        if ($filterformdata = $filterform->get_data()) {
            foreach($filterparams as $filterparam => $formparam)
            {
                if (property_exists($filterformdata, $formparam))
                {
                    $this->filterparams[$filterparam] = $filterformdata->$formparam;
                }
            }
        }
    }

    public function get_filter_params() {
        return $this->filterparams;
    }

    public function jsonSerialize()
    {
        $fields = array_values($this->get_known_fields(false));
        return [
            'entity-code' => $this->get_code(),
            'entity-storagename' => $this->get_storagename(),
            'entity-fullcode' => $this->get_full_code(),
            'entity-displayname' => $this->get_display_name(),
            'entity-fields' => $fields,
            'entity-fields-count' => count($fields),
        ];
    }
}