<?php
namespace local_otcontrolpanel;

class filter_form_parameter {

    /**
     * @param string $name - название параметра для поиска в массиве данных
     * @param mixed $default - значение, которое будет возвращено при отсутствии искомого параметра
     * @param string $type - тип данных для очистки параметра, как в optional_param - константы с префиксом PARAM_
     */
    public function __construct(string $name, $default, $type) {
        $this->name = $name;
        $this->default = $default;
        $this->type = $type;
    }

    public function get_name() {
        return $this->name;
    }

    /**
     * Получение значения для искомого параметра
     * @param array $data - массив данных для поиска параметра
     * @return mixed
     */
    public function get_value(array $data) {

        // Поиск значения по имени
        if (array_key_exists($this->name, $data)) {
            $value = $data[$this->name];
        } else {
            // значение не найдено - вернём дефолтное без обработок
            return $this->default;
        }

        // обработка на случай, если найденное значение оказалось массивом
        if (is_array($value))
        {
            $result = array();
            foreach ($value as $k=>$v) {
                // проверка имени ключа - как в ядре, в optional_param_array
                if (!preg_match('/^[a-z0-9_-]+$/i', $k)) {
                    debugging('Invalid key name in optional_param_array() detected: '.$k.', parameter: '.$this->name);
                    continue;
                }
                $result[$k] = clean_param($v, $this->type);
            }
            return $result;
        }

        // остальные обычные значения - просто чистим в соответствии с потребностями и отдаем
        return clean_param($value, $this->type);
    }
}