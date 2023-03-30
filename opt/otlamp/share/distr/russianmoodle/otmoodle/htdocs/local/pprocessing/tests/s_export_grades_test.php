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
 * Юнит-тесты сценариев
 *
 * @package    local
 * @subpackage pprocessing
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class local_pprocessing_export_grades_testcase extends advanced_testcase
{

    private $users = [];
    private $courses = [];
    /**
     * @var cm_info[]
     */
    private $cms = [];

    /**
     * @dataProvider scenario_provider
     * @group pprocessing_scenario
     * @param stdClass $initconfig
     * @param array|string|null $sqlfields - http://web.unife.it/lib/adodb/docs/docs-datadict.htm
     * @param array $profilefields
     */
    public function test_scenario($initconfig, $sqlfields, $profilefields, $testactions, $expectations) {

        $this->resetAfterTest(true);
        $prefix = 'export_grades__';
        $dg = $this->getDataGenerator();

        // Инициализация генератора плагина local_opentechnology
        $otgen = $dg->get_plugin_generator('local_opentechnology');
        // Инициализация генератора плагина local_pprocessing
        $popgen = $dg->get_plugin_generator('local_pprocessing');

        $initconfig = (object)$initconfig;
        // Создадим подключение к БД
        $dbconn = $otgen->create_connection_from_config($initconfig->dbconnconf);
        $dbconncode = $dbconn->get_config_code();
        $db = $dbconn->get_connection();

        // Создадим таблицу во внешней БД при необходимости
        if (!is_null($sqlfields))
        {
            $popgen->create_table($db, $initconfig->dbconntable, $sqlfields);
        }

        // отключим логирование, чтобы тесты быстрее проходили (можно закомментировать если потребуется отлаживать)
//         set_config('disable_logging', true, 'local_pprocessing');

        // Установим все настройки сценария
        set_config($prefix.'status', true, 'local_pprocessing');
        set_config($prefix.'connection', $dbconncode, 'local_pprocessing');
        set_config($prefix.'table', $initconfig->dbconntable, 'local_pprocessing');
        set_config($prefix.'grade_itemtype', $initconfig->gradeitemtype, 'local_pprocessing');
        set_config($prefix.'grade_itemmodule', $initconfig->gradeitemmodule, 'local_pprocessing');
        set_config($prefix.'grade_format', $initconfig->gradeformat, 'local_pprocessing');
        set_config($prefix.'date_format', $initconfig->dateformat, 'local_pprocessing');
        set_config($prefix.'primarykey1', $initconfig->extuserfield, 'local_pprocessing');
        set_config($prefix.'foreignkey1', $initconfig->intuserfield, 'local_pprocessing');
        set_config($prefix.'primarykey2', $initconfig->extmodfield, 'local_pprocessing');
        set_config($prefix.'foreignkey2', $initconfig->intmodfield, 'local_pprocessing');
        set_config($prefix.'primarykey3', $initconfig->extcoursefield, 'local_pprocessing');
        set_config($prefix.'foreignkey3', $initconfig->intcoursefield, 'local_pprocessing');
        foreach ($initconfig->datamapping as $int => $ext) {
            set_config($prefix.'data_mapping_'.$int, $ext, 'local_pprocessing');
        }

        // Создадим поля профиля
        foreach ($profilefields as $profilefieldrecord) {
            $popgen->create_profile_field($profilefieldrecord);
        }

        // Выполним тестовые действия для создания необходимого контекста для проверки
        foreach($testactions as $testaction) {
            foreach($testaction as $actioncode => $actiondata) {
                if (method_exists($this, 'tst_action_'.$actioncode)) {
                    call_user_func([$this, 'tst_action_'.$actioncode], $actiondata);
                }
            }
        }
        
        // Проверим, что действия привели к ожидаемым результатам
        foreach($expectations as $expectation) {
            foreach($expectation as $expectcode => $expectdata) {
                switch($expectcode) {
                    case 'extgradescount':
                        // TODO: надо получать конфиг плагина, а не использовать $initconfig
                        $result = $db->Execute('SELECT * FROM '.$initconfig->dbconntable);
                        if ($result === false) {
                            var_dump($db->errorMsg());
                        }
                        $recordCount = $result->recordCount();
                        $this->assertEquals($expectdata, $recordCount);
                        break;
                    case 'extgradecm':
                        if (!array_key_exists('grade', $expectdata)) {
                            throw new \Exception('Не удается выполить тест, не определена оценка для сравнения');
                        }
                        $grade = $expectdata['grade'];

                        if (!array_key_exists('usercode', $expectdata) || !array_key_exists($expectdata['usercode'], $this->users)) {
                            throw new \Exception('Не удается выполить тест, не определен пользователь');
                        }
                        $user = $this->users[$expectdata['usercode']];

                        if (!array_key_exists('cmcode', $expectdata) || !array_key_exists($expectdata['cmcode'], $this->cms)) {
                            throw new \Exception('Не удается выполить тест, не определен модуль');
                        }
                        $cm = $this->cms[$expectdata['cmcode']];


                        $db->setFetchMode(ADODB_FETCH_ASSOC);
                        // TODO: надо получать конфиг плагина, а не использовать $initconfig
                        $params = [$user->{$initconfig->intuserfield}, $cm->id];
                        $result = $db->Execute('
                            SELECT *
                              FROM '.$initconfig->dbconntable.'
                             WHERE '.$initconfig->extuserfield.' = ?
                               AND '.$initconfig->extmodfield.' = ?', $params);

                        if ($result === false) {
                            var_dump($db->errorMsg());
                        }

                        $this->assertEquals(1, $result->recordCount());
                        $first = $result->fetchRow();

                        // внешнее поле для хранения оценки модуля
                        // TODO: надо получать конфиг плагина, а не использовать $initconfig
                        $extgradecmcol = $initconfig->datamapping['llhcm_finalgrade'];
                        $this->assertEquals($grade, $first[$extgradecmcol]);

                        break;
                    case 'extgradecourse':
                        if (!array_key_exists('grade', $expectdata)) {
                            throw new \Exception('Не удается выполить тест, не определена оценка для сравнения');
                        }
                        $grade = $expectdata['grade'];

                        if (!array_key_exists('usercode', $expectdata) || !array_key_exists($expectdata['usercode'], $this->users)) {
                            throw new \Exception('Не удается выполить тест, не определен пользователь');
                        }
                        $user = $this->users[$expectdata['usercode']];

                        if (!array_key_exists('coursecode', $expectdata) || !array_key_exists($expectdata['coursecode'], $this->courses)) {
                            throw new \Exception('Не удается выполить тест, не определен курс');
                        }
                        $course = $this->courses[$expectdata['coursecode']];


                        $db->setFetchMode(ADODB_FETCH_ASSOC);
                        // TODO: надо получать конфиг плагина, а не использовать $initconfig
                        $params = [$user->{$initconfig->intuserfield}, $course->id];
                        $result = $db->Execute('
                            SELECT *
                              FROM '.$initconfig->dbconntable.'
                             WHERE '.$initconfig->extuserfield.' = ?
                               AND '.$initconfig->extcoursefield.' = ?
                               AND '.$initconfig->extmodfield.' IS NULL', $params);

                        if ($result === false) {
                            var_dump($db->errorMsg());
                        }

                        $this->assertEquals(1, $result->recordCount());
                        $first = $result->fetchRow();

                        // внешнее поле для хранения оценки курса
                        // TODO: надо получать конфиг плагина, а не использовать $initconfig
                        $extgradecoursecol = $initconfig->datamapping['llh_finalgrade'];
                        $this->assertEquals($grade, $first[$extgradecoursecol]);

                        break;
                    case 'extuserprofilefield':
                        if (!array_key_exists('usercode', $expectdata) || !array_key_exists($expectdata['usercode'], $this->users)) {
                            throw new \Exception('Не удается выполить тест, не определен пользователь');
                        }
                        $user = $this->users[$expectdata['usercode']];
                        unset($expectdata['usercode']);

                        if (!array_key_exists('cmcode', $expectdata) || !array_key_exists($expectdata['cmcode'], $this->cms)) {
                            throw new \Exception('Не удается выполить тест, не определен модуль');
                        }
                        $cm = $this->cms[$expectdata['cmcode']];
                        unset($expectdata['cmcode']);


                        $db->setFetchMode(ADODB_FETCH_ASSOC);
                        // TODO: надо получать конфиг плагина, а не использовать $initconfig
                        $params = [$user->{$initconfig->intuserfield}, $cm->id];
                        $result = $db->Execute('
                            SELECT *
                              FROM '.$initconfig->dbconntable.'
                             WHERE '.$initconfig->extuserfield.' = ?
                               AND '.$initconfig->extmodfield.' = ?', $params);

                        if ($result === false) {
                            var_dump($db->errorMsg());
                        }

                        $this->assertEquals(1, $result->recordCount());
                        $first = $result->fetchRow();

                        foreach($expectdata as $fieldcode => $expectvalue) {
                            // название колонки во внешней БД, связанной с полем профиля
                            // TODO: надо получать конфиг плагина, а не использовать $initconfig
                            $extcol = $initconfig->datamapping['user_profile_'.$fieldcode];
                            $this->assertEquals($expectvalue, $first[$extcol]);
                        }

                        break;
                }
            }
        }
    }


    public function scenario_provider() {

        // конфигурация сценария
        $initconfig = [
            'dbconnconf' => null, // null - будет создано подключение к БД СЭО
            'dbconntable' => 'TrainingResults',
            'gradeitemtype' => 'mod', // mod | course | all
            'gradeitemmodule' => 'all', // all | quiz
            'gradeformat' => 2, // 1 - значение, 2 - Проценты (GRADE_DISPLAY_TYPE_PERCENTAGE)
            'dateformat' => 'datetime', // timestamp | date (YYYY-MM-DD) | datetime (YYYY-MM-DD HH:MM:SS)
            'extuserfield' => 'TabNumber',
            'intuserfield' => 'idnumber',
            'extmodfield' => 'CmID',
            'intmodfield' => 'llhcm_cmid', // 0 чтобы не связывать
            'extcoursefield' => 'KursID',
            'intcoursefield' => 'llh_courseid', // 0 чтобы не связывать
            'datamapping' => [
                'llh_courseid' => 'KursID',
                'llh_coursefullname' => 'Kurs',
                'llh_finalgrade' => 'Result',
                'llh_lastupdate' => 'Date',
                'llhm_modname' => 'Cm',
                'llhcm_cmid' => 'CmID',
                'llhcm_finalgrade' => 'Result',
                'llhcm_timemodified' => 'Date',
                'user_idnumber' => 'TabNumber',
                'user_department' => 'Podrazdelenie',
                'user_profile_post' => 'Profession',
                'user_fullname' => 'FIO'
            ]
        ];
        // создание таблицы "внешней" БД
        $sqlfields = '
            ID I KEY AUTO,
            TabNumber C(11) NOTNULL,
            FIO C(100) NOTNULL,
            Napravlenie C(150),
            Podrazdelenie C(150),
            Profession C(150),
            Kurs C(150),
            KursID C(50),
            Cm C(150),
            CmID C(50),
            Date T,
            Result N(6.2)
        ';
        // создание кастомных полей профиля, используемых в сценарии
        $profilefields = [
            'post' => ['shortname' => 'post', 'name' => 'Должность']
        ];

        // данные пользователя
        $userperestukin = [
            'lastname' => 'Перестукин',
            'firstname' => 'Виктор',
            'idnumber' => '0218696',
            'profile_field_post' => 'lazy'
        ];

        // базовые действия: создать пользователя, курс, тест, подписку на курс, поставить за тест 66.66
        $baseactions = [
            ['user' => ['perestukin' => $userperestukin]],
            ['course' => ['unlearned_lessons' => ['fullname' => 'В стране невыученных уроков']]],
            ['cm' => ['punctuation' => ['modulename' => 'quiz', 'coursecode' => 'unlearned_lessons', 'name' => 'казнить нельзя помиловать']]],
            ['enrol' => ['usercode' => 'perestukin', 'coursecode' => 'unlearned_lessons', 'role' => 'student']],
            ['gradecm' => ['cmcode' => 'punctuation', 'usercode' => 'perestukin', 'rawgrade' => '66.66']],
        ];

        $finaltests = [];

        //////////////////////////////////////

        $finaltest1 = '01. Quiz. Первая оценка.';
        $finaltests[$finaltest1] = [
            'initconfig' => $initconfig,
            'sqlfields' => $sqlfields,
            'profilefields' => $profilefields,
            'testactions' => $baseactions,
            'expectations' => [
                ['extgradescount' => 1],
                ['extgradecm' => ['cmcode' => 'punctuation', 'usercode' => 'perestukin', 'grade' => '66.66']],
                ['extuserprofilefield' => ['cmcode' => 'punctuation', 'usercode' => 'perestukin', 'post' => 'lazy']]
            ]
        ];

        ////////////////////////////////////

        $finaltest2 = '02. Quiz. Вторая оценка хуже первой - обновления не происходит. Поэтому и должность не меняется.';
        $finaltests[$finaltest2] = [
            'initconfig' => $initconfig,
            'sqlfields' => $sqlfields,
            'profilefields' => $profilefields,
            'testactions' => $baseactions,
        ];
        $finaltests[$finaltest2]['testactions'][] = ['user' => ['perestukin' => ['profile_field_post' => 'adventurer']]];
        $finaltests[$finaltest2]['testactions'][] = ['gradecm' => ['cmcode' => 'punctuation', 'usercode' => 'perestukin', 'rawgrade' => '33.33']];
        $finaltests[$finaltest2]['expectations'] = [
            ['extgradescount' => 1],
            ['extgradecm' => ['cmcode' => 'punctuation', 'usercode' => 'perestukin', 'grade' => '33.33']],
            ['extuserprofilefield' => ['cmcode' => 'punctuation', 'usercode' => 'perestukin', 'post' => 'adventurer']]
        ];

        ////////////////////////////////////

        $finaltest3 = '03. Quiz. Третья оценка лучше первой - должна соответствовать. Заодно и должность должна смениться на adventurer';
        $finaltests[$finaltest3] = [
            'initconfig' => $initconfig,
            'sqlfields' => $sqlfields,
            'profilefields' => $profilefields,
            'testactions' => $baseactions,
        ];
        $finaltests[$finaltest3]['testactions'][] = ['user' => ['perestukin' => ['profile_field_post' => 'adventurer']]];
        $finaltests[$finaltest3]['testactions'][] = ['gradecm' => ['cmcode' => 'punctuation', 'usercode' => 'perestukin', 'rawgrade' => '33.33']];
        $finaltests[$finaltest3]['testactions'][] = ['gradecm' => ['cmcode' => 'punctuation', 'usercode' => 'perestukin', 'rawgrade' => '99.99']];
        $finaltests[$finaltest3]['expectations'] = [
            ['extgradescount' => 1],
            ['extgradecm' => ['cmcode' => 'punctuation', 'usercode' => 'perestukin', 'grade' => '99.99']],
            ['extuserprofilefield' => ['cmcode' => 'punctuation', 'usercode' => 'perestukin', 'post' => 'adventurer']]
        ];

        ////////////////////////////////////

        $finaltest4 = '04. Выгрузка за модули. Получили 66.66. Настроили выгрузку за курсы. Получили 99.99. В базе осталась ненужная запись оценки модуля (66.66), а за курс 99.99.';
        $finaltests[$finaltest4] = [
            'initconfig' => $initconfig,
            'sqlfields' => $sqlfields,
            'profilefields' => $profilefields,
            'testactions' => $baseactions,
        ];
        $finaltests[$finaltest4]['testactions'][] = ['config' => ['gradeitemtype' => 'course']];
        $finaltests[$finaltest4]['testactions'][] = ['gradecm' => ['cmcode' => 'punctuation', 'usercode' => 'perestukin', 'rawgrade' => '99.99']];
        $finaltests[$finaltest4]['expectations'] = [
            ['extgradescount' => 2],
            ['extgradecourse' => ['coursecode' => 'unlearned_lessons', 'usercode' => 'perestukin', 'grade' => '99.99']],
            // ниже - ненужная запись оценки за модуль, осталась висеть, так как изменились настройки, а базу не очистили
            ['extgradecm' => ['cmcode' => 'punctuation', 'usercode' => 'perestukin', 'grade' => '66.66']],
        ];

        ////////////////////////////////////

        $finaltest5 = '05. Выгрузка за модули. Получили 66.66. Настроили выгрузку за всё. Получили 99.99. В базе две записи, за обе 99.99.';
        $finaltests[$finaltest5] = [
            'initconfig' => $initconfig,
            'sqlfields' => $sqlfields,
            'profilefields' => $profilefields,
            'testactions' => $baseactions,
        ];
        $finaltests[$finaltest5]['testactions'][] = ['config' => ['gradeitemtype' => 'all']];
        $finaltests[$finaltest5]['testactions'][] = ['gradecm' => ['cmcode' => 'punctuation', 'usercode' => 'perestukin', 'rawgrade' => '99.99']];
        $finaltests[$finaltest5]['expectations'] = [
            ['extgradescount' => 2],
            ['extgradecm' => ['cmcode' => 'punctuation', 'usercode' => 'perestukin', 'grade' => '99.99']],
            ['extgradecourse' => ['coursecode' => 'unlearned_lessons', 'usercode' => 'perestukin', 'grade' => '99.99']],
        ];

        ////////////////////////////////////

        $finaltest6 = '06. Выгрузка за курсы. Получили 66.66. Настроили выгрузку за всё. Получили 99.99. В базе две записи, за обе 99.99.';
        $finaltests[$finaltest6] = [
            'initconfig' => array_merge($initconfig, ['gradeitemtype' => 'course']),
            'sqlfields' => $sqlfields,
            'profilefields' => $profilefields,
            'testactions' => $baseactions,
        ];
        $finaltests[$finaltest6]['testactions'][] = ['config' => ['gradeitemtype' => 'all']];
        $finaltests[$finaltest6]['testactions'][] = ['gradecm' => ['cmcode' => 'punctuation', 'usercode' => 'perestukin', 'rawgrade' => '99.99']];
        $finaltests[$finaltest6]['expectations'] = [
            ['extgradescount' => 2],
            ['extgradecm' => ['cmcode' => 'punctuation', 'usercode' => 'perestukin', 'grade' => '99.99']],
            ['extgradecourse' => ['coursecode' => 'unlearned_lessons', 'usercode' => 'perestukin', 'grade' => '99.99']],
        ];

        ////////////////////////////////////

        $finaltest7 = '07. Выгрузка за курсы. Получили 66.66. Настроили выгрузку за модули. Получили 99.99. В базе осталась ненужная запись оценки за курс (66.66), а за модуль 99.99.';
        $finaltests[$finaltest7] = [
            'initconfig' => array_merge($initconfig, ['gradeitemtype' => 'course']),
            'sqlfields' => $sqlfields,
            'profilefields' => $profilefields,
            'testactions' => $baseactions,
        ];
        $finaltests[$finaltest7]['testactions'][] = ['config' => ['gradeitemtype' => 'mod']];
        $finaltests[$finaltest7]['testactions'][] = ['gradecm' => ['cmcode' => 'punctuation', 'usercode' => 'perestukin', 'rawgrade' => '99.99']];
        $finaltests[$finaltest7]['expectations'] = [
            ['extgradescount' => 2],
            ['extgradecm' => ['cmcode' => 'punctuation', 'usercode' => 'perestukin', 'grade' => '99.99']],
            // ниже - ненужная запись, осталась висеть в базе из-за изменения настроек без очистки базы
            ['extgradecourse' => ['coursecode' => 'unlearned_lessons', 'usercode' => 'perestukin', 'grade' => '66.66']],
        ];

        ////////////////////////////////////

        $finaltest8 = '08. Выгрузка за всё (модули и курсы). Получили 66.66. Настроили выгрузку за модули. Получили 99.99. В базе осталась ненужная запись оценки за курс (66.66), а за модуль 99.99.';
        $finaltests[$finaltest8] = [
            'initconfig' => array_merge($initconfig, ['gradeitemtype' => 'all']),
            'sqlfields' => $sqlfields,
            'profilefields' => $profilefields,
            'testactions' => $baseactions,
        ];
        $finaltests[$finaltest8]['testactions'][] = ['config' => ['gradeitemtype' => 'mod']];
        $finaltests[$finaltest8]['testactions'][] = ['gradecm' => ['cmcode' => 'punctuation', 'usercode' => 'perestukin', 'rawgrade' => '99.99']];
        $finaltests[$finaltest8]['expectations'] = [
            ['extgradescount' => 2],
            ['extgradecm' => ['cmcode' => 'punctuation', 'usercode' => 'perestukin', 'grade' => '99.99']],
            // ниже - ненужная запись, осталась висеть в базе из-за изменения настроек без очистки базы
            ['extgradecourse' => ['coursecode' => 'unlearned_lessons', 'usercode' => 'perestukin', 'grade' => '66.66']],
        ];

        ////////////////////////////////////

        $finaltest9 = '09. Выгрузка за всё (модули и курсы). Получили 66.66. Настроили выгрузку за курсы. Получили 99.99. В базе осталась ненужная запись оценки за модуль (66.66), а за курс 99.99.';
        $finaltests[$finaltest9] = [
            'initconfig' => array_merge($initconfig, ['gradeitemtype' => 'all']),
            'sqlfields' => $sqlfields,
            'profilefields' => $profilefields,
            'testactions' => $baseactions,
        ];
        $finaltests[$finaltest9]['testactions'][] = ['config' => ['gradeitemtype' => 'course']];
        $finaltests[$finaltest9]['testactions'][] = ['gradecm' => ['cmcode' => 'punctuation', 'usercode' => 'perestukin', 'rawgrade' => '99.99']];
        $finaltests[$finaltest9]['expectations'] = [
            ['extgradescount' => 2],
            ['extgradecourse' => ['coursecode' => 'unlearned_lessons', 'usercode' => 'perestukin', 'grade' => '99.99']],
            // ниже - ненужная запись, осталась висеть в базе из-за изменения настроек без очистки базы
            ['extgradecm' => ['cmcode' => 'punctuation', 'usercode' => 'perestukin', 'grade' => '66.66']],
        ];

        ////////////////////////////////////

        $finaltest10 = '10. Выгрузка за всё (модули и курсы). Получили 66.66. Удалили пользователя. Во внешней базе остались обе записи.';
        $finaltests[$finaltest10] = [
            'initconfig' => array_merge($initconfig, ['gradeitemtype' => 'all']),
            'sqlfields' => $sqlfields,
            'profilefields' => $profilefields,
            'testactions' => $baseactions,
        ];
        $finaltests[$finaltest10]['testactions'][] = ['deluser' => ['usercode' => 'perestukin']];
        $finaltests[$finaltest10]['expectations'] = [
            ['extgradescount' => 0],
        ];

        ////////////////////////////////////

        $finaltest11 = '11. Выгрузка за всё (модули и курсы). Получили 66.66. Отписали пользователя от курса. Во внешней базе остались обе записи.';
        $finaltests[$finaltest11] = [
            'initconfig' => array_merge($initconfig, ['gradeitemtype' => 'all']),
            'sqlfields' => $sqlfields,
            'profilefields' => $profilefields,
            'testactions' => $baseactions,
        ];
        $finaltests[$finaltest11]['testactions'][] = ['unenrol' => ['usercode' => 'perestukin', 'coursecode' => 'unlearned_lessons']];
        $finaltests[$finaltest11]['expectations'] = [
            ['extgradescount' => 0],
        ];

        ////////////////////////////////////

        $finaltest12 = '12. Выгрузка за всё (модули и курсы). Получили по одному тесту 66.66, по второму 88.88. Во внешней базе три записи: две за модули (66.66 и 88.88) и одна за курс (77.77).';
        $finaltests[$finaltest12] = [
            'initconfig' => array_merge($initconfig, ['gradeitemtype' => 'all']),
            'sqlfields' => $sqlfields,
            'profilefields' => $profilefields,
            'testactions' => $baseactions,
        ];
        // создали еще тест-загадку про землекопов
        $finaltests[$finaltest12]['testactions'][] = ['cm' => ['arithmetic' => ['modulename' => 'quiz', 'coursecode' => 'unlearned_lessons', 'name' => 'землекопы']]];
        // выставили оценку 88.88
        $finaltests[$finaltest12]['testactions'][] = ['gradecm' => ['cmcode' => 'arithmetic', 'usercode' => 'perestukin', 'rawgrade' => '88.88']];
        $finaltests[$finaltest12]['expectations'] = [
            ['extgradescount' => 3],
            ['extgradecm' => ['cmcode' => 'punctuation', 'usercode' => 'perestukin', 'grade' => '66.66']],
            ['extgradecm' => ['cmcode' => 'arithmetic', 'usercode' => 'perestukin', 'grade' => '88.88']],
            ['extgradecourse' => ['coursecode' => 'unlearned_lessons', 'usercode' => 'perestukin', 'grade' => '77.77']],
        ];

        //////////////////////////////////////

        $finaltest13 = '13. Выгрузка за всё (модули и курсы). Получили по одному тесту 66.66, по второму 88.88. Удалили первый тест. Во внешней базе оценка по нему удалилась (осталась только 88.88), а оценка за курс пересчиталась (88.88).';
        $finaltests[$finaltest13] = [
            'initconfig' => array_merge($initconfig, ['gradeitemtype' => 'all']),
            'sqlfields' => $sqlfields,
            'profilefields' => $profilefields,
            'testactions' => $baseactions,
        ];
        // создали еще тест по арифметике
        $finaltests[$finaltest13]['testactions'][] = ['cm' => ['arithmetic' => ['modulename' => 'quiz', 'coursecode' => 'unlearned_lessons', 'name' => 'землекопы']]];
        // выставили оценку за тест по арифметике 88.88
        $finaltests[$finaltest13]['testactions'][] = ['gradecm' => ['cmcode' => 'arithmetic', 'usercode' => 'perestukin', 'rawgrade' => '88.88']];
        // удалили первый тест по пунктуации с оценкой 66.66
        $finaltests[$finaltest13]['testactions'][] = ['delcm' => ['cmcode' => 'punctuation', 'coursecode' => 'unlearned_lessons']];
        // ожидаем, что запись о первой оценке за тест по пунктуации пропала, а оценка за арифметику осталась, оценка за курс пересчиталась
        $finaltests[$finaltest13]['expectations'] = [
            ['extgradescount' => 2],
            ['extgradecm' => ['cmcode' => 'arithmetic', 'usercode' => 'perestukin', 'grade' => '88.88']],
            ['extgradecourse' => ['coursecode' => 'unlearned_lessons', 'usercode' => 'perestukin', 'grade' => '88.88']],
        ];

        //////////////////////////////////////

        return $finaltests;
    }

    /**
     * Создание / редактирование пользователя
     * @param array $actiondata
     * @throws \Exception
     */
    private function tst_action_user(array $actiondata) {

        $dg = $this->getDataGenerator();
        // Инициализация генератора плагина local_pprocessing
        $popgen = $dg->get_plugin_generator('local_pprocessing');

        foreach($actiondata as $usercode => $userdata) {
            if (array_key_exists($usercode, $this->users)) {
                $userdata['id'] = $this->users[$usercode]->id;
            }
            $this->users[$usercode] = $popgen->save_user_data($userdata);
        }
    }

    private function tst_action_deluser(array $actiondata) {

        if (!array_key_exists('usercode', $actiondata)) {
            throw new \Exception('Не передан код пользователя подлежащего удалению');
        }
        $usercode = $actiondata['usercode'];

        if (!array_key_exists($usercode, $this->users)) {
            throw new \Exception('Не найден пользователь по коду');
        }
        $user = $this->users[$usercode];
        user_delete_user($user);
    }

    private function tst_action_course(array $actiondata) {
        global $DB;

        $dg = $this->getDataGenerator();

        foreach($actiondata as $coursecode => $coursedata) {

            if (!array_key_exists($coursecode, $this->courses)) {
                // создание курса
                $this->courses[$coursecode] = $dg->create_course($coursedata);
            } else {
                // обновление курса
                $updaterecord = (object)$coursedata;
                $updaterecord['id'] = $this->courses[$coursecode]->id;
                $DB->update_record('course', $updaterecord);
                $this->courses[$coursecode] = $DB->get_record('course', ['id' => $updaterecord['id']], '*', MUST_EXIST);
            }
        }
    }

    private function tst_action_cm(array $actiondata) {

        $dg = $this->getDataGenerator();

        foreach($actiondata as $cmcode => $cmdata) {
            if (!array_key_exists('modulename', $cmdata)) {
                throw new \Exception('Для создания cm необходимо свойство modulename');
            }
            $modulename = $cmdata['modulename'];
            unset($cmdata['modulename']);

            $modulerecord = [];
            if (array_key_exists('coursecode', $cmdata)) {
                if (!array_key_exists($cmdata['coursecode'], $this->courses)) {
                    throw new \Exception('Не удалось создать cm. Передан неизвестный код курса');
                }
                $modulerecord = ['course' => $this->courses[$cmdata['coursecode']]->id];
                unset($cmdata['coursecode']);
            }
            $modulerecord = array_merge($modulerecord, $cmdata);


            if (!array_key_exists($cmcode, $this->cms)) {
                // создание модуля
                $instance = $dg->create_module($modulename, $modulerecord);
                $cminfo = get_fast_modinfo($instance->course);
                $this->cms[$cmcode] = $cminfo->cms[$instance->cmid];
            } else {
                // обновление модуля не реализовано
                // $modulerecord['id'] = $cms[$cmname]->id;
            }
        }
    }

    private function tst_action_delcm(array $actiondata) {
        if (!array_key_exists('cmcode', $actiondata)) {
            throw new \Exception('Для удаления модуля необходим код модуля');
        }
        $cmcode = $actiondata['cmcode'];
        if (!array_key_exists($cmcode, $this->cms)) {
            throw new \Exception('Не удалось удалить модуль. Передан неизвестный код модуля');
        }
        /** @var cm_info $cm */
        $cm = $this->cms[$cmcode];

        // TODO: переписать на получение курса по идентификатору из cm чтобы не требовать передачи кода
        if (!array_key_exists('coursecode', $actiondata)) {
            throw new \Exception('Для удаления модуля необходим код курса');
        }
        $coursecode = $actiondata['coursecode'];

        if (!array_key_exists($coursecode, $this->courses)) {
            throw new \Exception('Не удалось удалить модуль. Передан неизвестный код курса');
        }
        $course = $this->courses[$coursecode];

        course_delete_module($cm->id);
        grade_regrade_final_grades_if_required($course);
    }

    private function tst_action_enrol(array $actiondata) {

        $dg = $this->getDataGenerator();

        if (!array_key_exists('coursecode', $actiondata)) {
            throw new \Exception('Для создания записи на курс необходим код курса');
        }
        if (!array_key_exists($actiondata['coursecode'], $this->courses)) {
            throw new \Exception('Не удалось создать запись на курс. Передан неизвестный код курса');
        }

        if (!array_key_exists('usercode', $actiondata)) {
            throw new \Exception('Для создания записи на курс необходим код пользователя');
        }
        if (!array_key_exists($actiondata['usercode'], $this->users)) {
            throw new \Exception('Не удалось создать запись на курс. Передан неизвестный код пользователя');
        }

        $userid = $this->users[$actiondata['usercode']]->id;
        $courseid = $this->courses[$actiondata['coursecode']]->id;
        $role = $actiondata['role'] ?? null;
        $dg->enrol_user($userid, $courseid, $role);
    }


    private function tst_action_unenrol(array $actiondata) {
        global $DB;

        if (!array_key_exists('coursecode', $actiondata)) {
            throw new \Exception('Для отписки от курса необходим код курса');
        }
        $coursecode = $actiondata['coursecode'];

        if (!array_key_exists($coursecode, $this->courses)) {
            throw new \Exception('Не удалось отписать от курса. Передан неизвестный код курса');
        }
        $course = $this->courses[$coursecode];

        if (!array_key_exists('usercode', $actiondata)) {
            throw new \Exception('Для отписки от курса необходим код пользователя');
        }
        $usercode = $actiondata['usercode'];

        if (!array_key_exists($usercode, $this->users)) {
            throw new \Exception('Не удалось отписать от курса. Передан неизвестный код пользователя');
        }
        $user = $this->users[$usercode];

        // Получение контекста курса, на который подписываем пользователя
        $context = context_course::instance($course->id);

        if (is_enrolled($context, $user->id))
        {// Пользователь записан на курс, но надо перезаписать

            // Параметры для селекта
            $params = [
                'userid' => $user->id,
                'courseid' => $course->id
            ];

            // Формирование запроса
            $sql = "SELECT e.*
              FROM {enrol} e
              JOIN {user_enrolments} ue ON (e.id = ue.enrolid)
              WHERE e.courseid = :courseid AND ue.userid = :userid;";

            // Получение активных подписок
            $enrolments = $DB->get_records_sql($sql, $params);
            if (!empty($enrolments))
            {
                $plugins = [];
                foreach ($enrolments as $enrolment)
                {
                    $pl = $plugins[$enrolment->enrol] ?? enrol_get_plugin($enrolment->enrol);
                    $pl->unenrol_user($enrolment, $user->id);
                }
            }
        }
    }

    private function tst_action_config(array $data) {
        $prefix = 'export_grades__';

        foreach ($data as $prop => $val) {

            if ($prop == 'datamapping' && is_array($val)) {
                foreach ($val as $int => $ext) {
                    set_config($prefix.'data_mapping_'.$int, $ext, 'local_pprocessing');
                }
                continue;
            }

            $configmapping = [
                'dbconntable' => 'table',
                'gradeitemtype' => 'grade_itemtype',
                'gradeitemmodule' => 'grade_itemmodule',
                'gradeformat' => 'grade_format',
                'dateformat' => 'date_format',
                'extuserfield' => 'primarykey1',
                'intuserfield' => 'foreignkey1',
                'extmodfield' => 'primarykey2',
                'intmodfield' => 'foreignkey2',
                'extcoursefield' => 'primarykey3',
                'intcoursefield' => 'foreignkey3',
            ];

            if (!array_key_exists($prop, $configmapping)) {
                throw new \Exception('Неизвестное свойство конфига, тест не был готов к такому');
            }

            set_config($prefix.$configmapping[$prop], $val, 'local_pprocessing');
        }
    }

    private function tst_action_gradecm(array $actiondata) {

        $dg = $this->getDataGenerator();

        if (array_key_exists('cmcode', $actiondata))
        {
            $cmcode = $actiondata['cmcode'];
            if (!array_key_exists($cmcode, $this->cms)) {
                throw new \Exception('Не удалось выставить оценку за модуль. Передан неизвестный код модуля');
            }
            if (!array_key_exists('usercode', $actiondata) || !array_key_exists($actiondata['usercode'], $this->users)) {
                throw new \Exception('Не удалось выставить оценку за модуль. Не определен пользователь');
            }
            if (!array_key_exists('rawgrade', $actiondata)) {
                throw new \Exception('Не удалось выставить оценку за модуль. Не определена оценка');
            }

            /** @var cm_info $cm */
            $cm = $this->cms[$cmcode];
            $user = $this->users[$actiondata['usercode']];

            unset($actiondata['cmcode']);
            unset($actiondata['usercode']);

            $giparams = [
                'courseid' => $cm->course,
                'itemtype' => 'mod',
                'itemmodule' => $cm->modname,
                'iteminstance' => $cm->instance,
                'grademin' => 0,
                'grademax' => 100
            ];

            $gi = grade_item::fetch($giparams);
            if ($gi === false) {
                $gi = new grade_item($dg->create_grade_item($giparams), false);
            }


            $gi->update_raw_grade($user->id, $actiondata['rawgrade']);
//             grade_regrade_final_grades($cm->course, $user->id, $gi);
        }
    }
}