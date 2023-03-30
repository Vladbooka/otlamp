<?php
namespace local_otcontrolpanel\filter;

use local_otcontrolpanel\entity\abstract_entity;
use local_otcontrolpanel\filter_form_parameter;
use local_otcontrolpanel\sql_join;

abstract class abstract_filter {

    /**
     * @var array - Счетчик созданных экземпляров класса для создания
     */
    protected static $instancescount = 0;

    /**
     * @var int - Порядковый номер экземпляра класса
     */
    private $instancenum = null;

    /**
     * @var abstract_entity
     */
    protected $entity;

    protected $value;

    /**
     * @var sql_join[]
     */
    protected $registered_joins=[];

    public function __construct(abstract_entity $entity, $value) {
        $this->instancenum = ++self::$instancescount;
        $this->entity = $entity;
        $this->value = $value;
    }

    public function get_instance_num()
    {
        return $this->instancenum;
    }

    public static function instance_by_config(abstract_entity $entity, array $filterconfig)
    {
        if (array_key_exists('filtercode', $filterconfig))
        {
            $filtercode = $filterconfig['filtercode'];

            unset($filterconfig['filtercode']);
            $params = array_values($filterconfig);
            array_unshift($params, $entity);

            $classname = '\\local_otcontrolpanel\\entity\\'.$entity->get_storagename().'\\filters\\'.$filtercode;
            if (class_exists($classname))
            {
                $callback = [new \ReflectionClass($classname), 'newInstance'];
                return call_user_func_array($callback, $params);
            }
            $classname = '\\local_otcontrolpanel\\filter\\'.$filtercode;
            if (class_exists($classname))
            {
                $callback = [new \ReflectionClass($classname), 'newInstance'];
                return call_user_func_array($callback, $params);
            }
        }
        throw new \coding_exception('Misconfigured filter for entity '.$entity->get_code());
    }

    public static function instance(abstract_entity $entity, $filtercode, $value)
    {
        $classname = '\\local_otcontrolpanel\\entity\\'.$entity->get_storagename().'\\filters\\'.$filtercode;
        if (class_exists($classname))
        {
            return new $classname($entity, $value);
        }
        throw new \coding_exception('Unknown filter '.$filtercode.' for entity '.$entity->get_code());
    }

    public function get_joins() {
        $this->register_joins();
        return $this->registered_joins;
    }

    protected function get_alias($storagename)
    {
        return 'f_'.$this->get_code().'_'.$storagename;
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

    /**
     * Краткий код фильтра, использующийся в параметре, подставляемом в запрос
     * Парметры имеют ограниченную длину, поэтому здесь приветствуются сильные сокращения
     * @return string
     */
    public function get_param_shortname() {
        return $this->get_code();
    }

    /**
     * Генерация параметра, подставляемого в запрос (если несколько, рекомендуется использвоать индекс для различия параметров)
     * @param int $index - порядковый номер параметра, чтобы не тратить символы на уникальные имена
     * @param int $maxlength - максимальная длина параметра, по умолчанию ограничена 30 символами,
     *                         вынесено в аргументы, чтобы была возможность ограничить еще сильнее
     *                         например, когда используется в качестве префикса для get_in_or_equal(), чтобы тот
     *                         мог добавить еще и свой индекс для уникальности
     * @return string
     */
    protected function param(int $index=null, int $maxlength=30) {
        $suffix = is_null($index) ? '' : '_'.$index;
        $prefix = $this->entity->get_code().'_';
        $param = $prefix.$this->get_param_shortname().$this->get_instance_num().$suffix;
        return mb_substr($param, ($maxlength * -1));
    }

    /**
     * Получить код поля вида [класс_сущности]_[класс_поля]
     * @return string
     */
    public function get_code() {
        $reflect = new \ReflectionClass($this);
        return $reflect->getShortName();
    }

    public function get_full_code() {
        return $this->entity->get_full_code().'_f_'.$this->get_code();
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
     * Получение значения параметра из данных формы фильтрации по коду
     * @param string $parametercode - код параметра, заявленного в связи (см. get_supported_filter_form_parameters)
     * @param mixed $default - значение, которое вернется в случае, если искомый параметр не был заявлен связью
     * @return mixed
     */
    public function get_filter_form_parameter_value(string $parametercode, $default=null) {

        $filterparams = [];
        if (method_exists($this->entity, 'get_filter_params')) {
            $filterparams = $this->entity->get_filter_params();
        }
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

    /**
     * Регистрация sql_join'ов, необходимых для применения фильтра
     * для удобства рекомендуется использовать метод register_join()
     * @return void
     */
    abstract protected function register_joins();
    /**
     * Получение параметров запроса
     * @return array
     */
    abstract public function get_params();
    /**
     * Получение куска запроса, который попадет в WHERE
     * @return string
     */
    abstract public function get_select();
}