<?php

namespace local_otcontrolpanel;

class config {

    /**
     * Получение дефолтного конфига, который хранится в файле в плагине
     * @return array
     */
    public static function get_default_config() {
        global $CFG;

        $configpath = $CFG->dirroot.'/local/otcontrolpanel/viewsconfig.yaml';
        try {
            $config = \otcomponent_yaml\Yaml::parseFile($configpath);

        } catch(\otcomponent_yaml\Exception\ParseException $ex)
        {
            $config = [];
        }

        return array_values($config);
    }

    /**
     * Удаление пользовательского конфига панели текущего пользователя
     * @return boolean
     */
    public static function delete_user_config() {
        global $USER;

        // идентификатор редактируемого объекта
        $objid = $USER->id;

        // проверка прав на редактирование конфига
        if (!self::has_access_config_otcontrolpanel($objid))
        {
            return false;
        }

        $usermcov = new \local_mcov\entity('user', $objid);
        return $usermcov->delete_mcov([
            'entity' => 'user',
            'objid' => $objid,
            'prop' => 'local_otcontrolpanel_viewsconfig'
        ]);
    }

    /**
     * Редактирование пользовательского конфига панели текущего пользователя
     * @param string $yaml - конфиг в yaml разметке
     * @return boolean
     */
    public static function save_user_config($yaml)
    {
        global $USER;

        $config = \otcomponent_yaml\Yaml::parse($yaml);
        $jsonconfig = json_encode($config, JSON_UNESCAPED_UNICODE);

        // идентификатор редактируемого объекта
        $objid = $USER->id;

        // проверка прав на редактирование конфига
        if (!self::has_access_config_otcontrolpanel($objid))
        {
            return false;
        }

        $usermcov = new \local_mcov\entity('user', $objid);
        $configrecord = $usermcov->get_mcov($objid, 'local_otcontrolpanel_viewsconfig');
        $configrecord->value = $jsonconfig;
        $configrecord->searchval = mb_substr(strip_tags($configrecord->value), 0, 232);
        return $usermcov->save_mcov($configrecord);
    }

    /**
     * Получение пользовательского конфига панели текущего пользователя
     * @return mixed|NULL
     */
    public static function get_user_config() {
        global $USER;

        // идентификатор редактируемого объекта
        $objid = $USER->id;

        $usermcov = new \local_mcov\entity('user', $objid);
        $configrecord = $usermcov->get_mcov($objid, 'local_otcontrolpanel_viewsconfig', false);

        if (!empty($configrecord))
        {
            return json_decode($configrecord->value, true);
        }

        return null;
    }

    /**
     * Получение конфига текущего пользователя
     * (если есть - пользовательского, если нет - дефолтного)
     *
     * @return array
     */
    public static function get_config() {

        $config = self::get_user_config();

        if (is_null($config))
        {
            $config = self::get_default_config();
        }

        return array_values($config);
    }

    /**
     * Проверка наличия прав на редактирование конфига панели управления СЭО 3KL
     * @param int $userid - идентификатор пользователя, чей конфиг планируется редактировать
     * @return boolean
     */
    public static function has_access_config_otcontrolpanel($userid) {

        global $USER;

        $syscontext = \context_system::instance();

        // есть право настраивать конфиг любым пользователям
        if (has_capability('local/otcontrolpanel:config', $syscontext)) {
            return true;
        }
        // есть право настраивать конфиг себе, и перед нами сам владелец
        if (has_capability('local/otcontrolpanel:config_my', $syscontext) && $USER->id == $userid) {
            return true;
        }

        return false;
    }

}