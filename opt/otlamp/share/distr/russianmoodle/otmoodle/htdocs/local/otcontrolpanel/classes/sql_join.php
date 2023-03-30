<?php
namespace local_otcontrolpanel;
class sql_join {

    protected $jointype;
    protected $storagename;
    protected $alias;
    protected $joincondition;
    protected $requiredfields=[];

    /**
     * Конструктор класса
     *
     * @param string $jointype - способ присоединения таблицы, используется в sql как есть, например "LEFT JOIN"
     * @param string $storagename - название присоединяемой таблицы.
     *                              При подготовке sql будет заключено в фигурные скобки, которые обрабатывает мудл,
     *                              Исключение - если строка уже заключена в круглые скобки, тогда оборачивать фигурными не станет (программист написал подзапрос)
     * @param string|NULL $alias - псевдоним, который будет использоваться для подключаемой таблицы
     * @param string|NULL $joincondition - sql-условие подключения таблицы, будет вставлено после ON
     */
    public function __construct($jointype, $storagename, $alias=null, $joincondition=null) {
        $this->jointype = $jointype;
        $this->storagename = $storagename;
        $this->alias = $alias;
        $this->joincondition = $joincondition;
    }

    public function get_storagename() {
        return $this->storagename;
    }

    public function get_alias() {
        return $this->alias;
    }

    /**
     * Установка требуемых полей, подключаемых с таблицей в результате джойна
     * @param array $dbfields - массив полей без указания таблицы(алиаса)
     */
    public function set_required_db_fields(array $dbfields) {
        $this->requiredfields = $dbfields;
    }

    /**
     * Добавление требуемых полей, подключаемых с таблицей в результате джойна
     * @param array $dbfields - массив полей без указания таблицы(алиаса)
     */
    public function add_required_db_fields(array $dbfields) {
        $this->requiredfields = array_merge($this->requiredfields, $dbfields);
    }

    /**
     * Получение массива полей, требуемых от джойна
     * @return string[] - поля уже с указанием таблицы (алиаса)
     */
    public function get_required_db_fields() {
        $requiredfields = [];
        foreach($this->requiredfields as $requiredfield)
        {
            $requiredfields[] = $this->get_alias().'.'.$requiredfield;
        }
        return $requiredfields;
    }

    public function get_sql() {
        $storagename = $this->storagename;
        if (!$this->storage_is_subquery())
        {
            $storagename = '{'.$storagename.'}';
        }
        $sql = $this->jointype.' '.$storagename;
        if (!is_null($this->alias))
        {
            $sql .= ' AS '.$this->alias;
        }
        if (!empty($this->joincondition))
        {
            $sql .= ' ON '.$this->joincondition;
        }
        return $sql;
    }

    protected function storage_is_subquery() {
        $s = trim($this->storagename);
        return (mb_strlen($s) > 1 && $s[0] == '(' && mb_substr($s, -1) == ')');
    }

    /**
     * Метод обновляет условия запроса так, чтобы при использовании нескольких джоинов,
     * условия могли использовать алиасы из других джоинов вместо названия стораджа
     * это позволяет легче писать условие джоина (указывая таблицу, а не алиас)
     * @param sql_join[] $joins
     */
    public function remove_condition_inconsistancy(array $joins) {

        foreach ($joins as $join)
        {
            if ($this->storage_is_subquery())
            {
                continue;
            }
            $storagename = '{'.$join->get_storagename().'}';
            $this->joincondition = str_replace($storagename, $join->get_alias(), $this->joincondition);
        }
    }
}