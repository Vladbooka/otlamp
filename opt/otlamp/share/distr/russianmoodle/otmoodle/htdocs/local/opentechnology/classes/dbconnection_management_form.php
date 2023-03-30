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

namespace local_opentechnology;

class dbconnection_management_form extends \moodleform {

    protected function definition()
    {
        $mform = &$this->_form;

        $defaults = [];

        $configs = dbconnection::get_list_configs();

        // в начало ассоциативного массива добавим ключ NEW, для того чтобы пользователь имел возможность создать +1 соединение
        $configs = array_reverse($configs);
        $configs['NEW'] = 'NEW';
        $configs = array_reverse($configs);

        foreach ($configs as $code => $name) {

            $group = [];

            if ($code != 'NEW') {
                // Возможность удаления имеющегося коннекшена
                $elementname = 'delete';
                $groupelementname = 'dbc['.$code.']['.$elementname.']';
                $stringidentifier = 'dbconnection_'.$elementname;
                $elementdisplayname = get_string($stringidentifier, 'local_opentechnology');
                $group[] = $mform->createElement('checkbox', $groupelementname, $elementdisplayname);
                
                $url = new \moodle_url('/local/opentechnology/check_connection.php', ['code' => $code]);
                $elementname = 'check_connection';
                $groupelementname = 'dbc['.$code.']['.$elementname.']';
                $stringidentifier = 'dbconnection_'.$elementname;
                $elementdisplayname = get_string($stringidentifier, 'local_opentechnology');
                $group[] = $mform->createElement('static', $groupelementname, get_string('connection', 'local_opentechnology'), 
                    \html_writer::link($url, $elementdisplayname));
            }

            // Название (при создании нового коннекшена - надо придумать название, а при редактировании старого можно поменять)
            $elementname = 'name';
            $groupelementname = 'dbc['.$code.']['.$elementname.']';
            $stringidentifier = 'dbconnection_'.$elementname;
            $elementdisplayname = get_string($stringidentifier, 'local_opentechnology');
            $group[] = $mform->createElement('text', $groupelementname, $elementdisplayname);
            $mform->setType($groupelementname, PARAM_RAW);
            $mform->setDefault($groupelementname, '');
            $mform->hideIf($groupelementname, 'dbc['.$code.'][delete]', 'checked');

            // Хост
            $elementname = 'host';
            $groupelementname = 'dbc['.$code.']['.$elementname.']';
            $stringidentifier = 'dbconnection_'.$elementname;
            $elementdisplayname = get_string($stringidentifier, 'local_opentechnology');
            $group[] = $mform->createElement('text', $groupelementname, $elementdisplayname);
            $mform->setType($groupelementname, PARAM_RAW);
            $mform->setDefault($groupelementname, '127.0.0.1');
            $mform->hideIf($groupelementname, 'dbc['.$code.'][delete]', 'checked');

            // Тип
            $dbtypes = ["access", "ado_access", "ado", "ado_mssql", "borland_ibase", "csv", "db2",
                "fbsql", "firebird", "ibase", "informix72", "informix", "mssql", "mssql_n", "mssqlnative",
                "mysql", "mysqli", "mysqlt", "oci805", "oci8", "oci8po", "odbc", "odbc_mssql", "odbc_oracle",
                "oracle", "pdo", "postgres64", "postgres7", "postgres", "proxy", "sqlanywhere", "sybase", "vfp"];
            $elementname = 'type';
            $groupelementname = 'dbc['.$code.']['.$elementname.']';
            $stringidentifier = 'dbconnection_'.$elementname;
            $elementdisplayname = get_string($stringidentifier, 'local_opentechnology');
            $group[] = $mform->createElement('select', $groupelementname, $elementdisplayname, array_combine($dbtypes, $dbtypes));
            $mform->setDefault($groupelementname, 'mysqli');
            $mform->hideIf($groupelementname, 'dbc['.$code.'][delete]', 'checked');

            // База данных
            $elementname = 'database';
            $groupelementname = 'dbc['.$code.']['.$elementname.']';
            $stringidentifier = 'dbconnection_'.$elementname;
            $elementdisplayname = get_string($stringidentifier, 'local_opentechnology');
            $group[] = $mform->createElement('text', $groupelementname, $elementdisplayname);
            $mform->setType($groupelementname, PARAM_RAW_TRIMMED);
            $mform->setDefault($groupelementname, '');
            $mform->hideIf($groupelementname, 'dbc['.$code.'][delete]', 'checked');

            // Логин
            $elementname = 'user';
            $groupelementname = 'dbc['.$code.']['.$elementname.']';
            $stringidentifier = 'dbconnection_'.$elementname;
            $elementdisplayname = get_string($stringidentifier, 'local_opentechnology');
            $group[] = $mform->createElement('text', $groupelementname, $elementdisplayname);
            $mform->setType($groupelementname, PARAM_RAW_TRIMMED);
            $mform->setDefault($groupelementname, '');
            $mform->hideIf($groupelementname, 'dbc['.$code.'][delete]', 'checked');

            // Пароль
            $elementname = 'pass';
            $groupelementname = 'dbc['.$code.']['.$elementname.']';
            $stringidentifier = 'dbconnection_'.$elementname;
            $elementdisplayname = get_string($stringidentifier, 'local_opentechnology');
            $group[] = $mform->createElement('password', $groupelementname, $elementdisplayname);
            $mform->setType($groupelementname, PARAM_RAW_TRIMMED);
            $mform->setDefault($groupelementname, '');
            $mform->hideIf($groupelementname, 'dbc['.$code.'][delete]', 'checked');

            // Сетап эскюэль ))
            $elementname = 'setupsql';
            $groupelementname = 'dbc['.$code.']['.$elementname.']';
            $stringidentifier = 'dbconnection_'.$elementname;
            $elementdisplayname = get_string($stringidentifier, 'local_opentechnology');
            $group[] = $mform->createElement('text', $groupelementname, $elementdisplayname);
            $mform->setType($groupelementname, PARAM_RAW_TRIMMED);
            $mform->setDefault($groupelementname, '');
            $mform->hideIf($groupelementname, 'dbc['.$code.'][delete]', 'checked');

            // Кодировка
            $elementname = 'extencoding';
            $groupelementname = 'dbc['.$code.']['.$elementname.']';
            $stringidentifier = 'dbconnection_'.$elementname;
            $elementdisplayname = get_string($stringidentifier, 'local_opentechnology');
            $group[] = $mform->createElement('text', $groupelementname, $elementdisplayname);
            $mform->setType($groupelementname, PARAM_RAW_TRIMMED);
            $mform->setDefault($groupelementname, 'utf-8');
            $mform->hideIf($groupelementname, 'dbc['.$code.'][delete]', 'checked');

            // Заголовок группы
            $elementdisplayname = ($code == 'NEW' ? get_string('dbconnection_new', 'local_opentechnology') : $name);
            $mform->addElement('header', $code, $elementdisplayname);
            foreach($group as $element) {
                $mform->addElement($element);
            }

            // Подготовка ранее сохраненных значений
            $dbconnection = new dbconnection($code);
            $config = $dbconnection->get_config_data();
            $groupkeys = array_map(function($key) use ($code) {
                return 'dbc['.$code.']['.$key.']';
            }, array_keys($config));
            $defaults = array_merge($defaults, array_combine($groupkeys, array_values($config)));
            $defaults['dbc['.$code.'][name]'] = $dbconnection->get_name();
        }

        $this->set_data($defaults);
        $this->add_action_buttons();
    }

    public function validation($data, $files) {
        $errors = [];

        if (!empty($data['dbc'])) {
            foreach($data['dbc'] as $code => $elements) {
                if ($code != 'NEW' && trim($elements['name']) == '') {
                    $errors['dbc['.$code.'][name]'] = get_string('dbconnection_name_should_not_be_empty', 'local_opentechnology');
                }
            }
        }

        return $errors;
    }

    public function process() {
        if ($formdata = $this->get_data())
        {
            if (!empty($formdata->dbc))
            {
                foreach($formdata->dbc as $code => $elements)
                {
                    $modified = false;
                    if ($code == 'NEW') {
                        if (empty($elements['name'])) {
                            continue;
                        }
                        $dbconnection = new dbconnection();
                    } else {
                        $dbconnection = new dbconnection($code);
                    }

                    if (!empty($elements['name'])) {
                        if ($elements['name'] != $dbconnection->get_name()) {
                            $dbconnection->set_name($elements['name']);
                            $modified = true;
                        }
                        unset($elements['name']);
                    }

                    if (!empty($elements['delete'])) {

                        $dbconnection->delete_config();

                    } else {

                        $config = $dbconnection->get_config_data();

                        foreach(array_keys($config) as $prop) {
                            if (array_key_exists($prop, $elements) && $config[$prop] != $elements[$prop]) {
                                $modified = true;
                                $config[$prop] = $elements[$prop];
                            }
                        }

                        $dbconnection->set_config_data($config);
                    }

                    if ($modified) {
                        $dbconnection->save_config_data();
                    }
                }
            }
            redirect(new \moodle_url('/local/opentechnology/dbconnection_management.php'));
        }
    }
}