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
 * Unit tests for mod_page lib
 *
 * @package    mod_page
 * @category   external
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 3.0
 */

defined('MOODLE_INTERNAL') || die();


/**
 * Юнит-тесты
 *
 * @package    local
 * @subpackage pprocessing
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_pprocessing_locallib_testcase extends advanced_testcase
{
    /**
     * Prepares things before this test case is initialised
     * @return void
     */
    public static function setUpBeforeClass()
    {
        global $CFG;
        require_once($CFG->dirroot . '/local/pprocessing/classes/condition_parser.php');
    }

    /**
     * Test page_view
     * @return void
     */
    public function test_parse_user_db_conditionals()
    {
        global $DB;
        $this->resetAfterTest(true);
        // список тест кейсов
        $testcases = [];
        
        // успешный тест кейс
        // используются только обычные поля профиля
        $testcases[] = [
            'expected' => [
                '(((u.firstname = :value_mf_1) OR (firstname = :value_2)) AND (((firstaccess > :value_3) AND (firstaccess <= :value_4)) OR (lastname = :value_5)))',
                ['value_mf_1' => 'Ivan', 'value_2' => 'Иван', 'value_3' => 17, 'value_4' => 18, 'value_5' => 'Иванов']
            ],
            'params' => [
                'AND' => [
                    [
                        'OR' => [
                            [
                                // необязательное поле, если это обычное поле профиля
                                // базовый парсинг
                                'type' => 'user_main_field',
                                'field' => 'firstname',
                                'operator' => '=',
                                'value' => 'Ivan'
                            ],
                            [
                                'field' => 'firstname',
                                'operator' => '=',
                                'value' => 'Иван'
                            ]
                        ]
                    ],
                    [
                        'OR' => [
                            [
                                'AND' => [
                                    [
                                        'field' => 'firstaccess',
                                        'operator' => '>',
                                        'value' => 17
                                    ],
                                    [
                                        'field' => 'firstaccess',
                                        'operator' => '<=',
                                        'value' => 18
                                    ]
                                ]
                            ],
                            [
                                'field' => 'lastname',
                                'operator' => '=',
                                'value' => 'Иванов'
                            ]
                        ]
                    ],
                ]
            ]
        ];
        
        // успешный тест кейс
        // используются поля профиля/кастом поля/пользовательские настройки
        $testcases[] = [
            'expected' => [
                "(((up.name = 'check' AND up.value = :value_up_1) OR (uif.shortname = 'ispoliceman' AND uid.data = :value_cf_2)) AND (lastname = :value_3))",
                ['value_up_1' => 'hole', 'value_cf_2' => 1, 'value_3' => 'Иванов']
            ],
            'params' => [
                'AND' => [
                    [
                        'OR' => [
                            [
                                'type' => 'user_preference',
                                'field' => 'check',
                                'operator' => '=',
                                'value' => 'hole'
                            ],
                            [
                                'type' => 'user_custom_field',
                                'field' => 'ispoliceman',
                                'operator' => '=',
                                'value' => 1
                            ]
                        ]
                    ],
                    [
                        'field' => 'lastname',
                        'operator' => '=',
                        'value' => 'Иванов'
                    ],
                ]
            ]
        ];

        // успешный тест кейс
        $testcases[] = [
            'expected' => [
                '(danger >= :value_1)',
                ['value_1' => '1check2']
            ],
            'params' => [
                'field' => 'danger',
                'operator' => '>=',
                'value' => '1check2'
            ]
        ];
        
        // тест кейс с ошибкой
        $testcases[] = [
            'expected' => false,
            'params' => [
                'AND' => [
                    [
                        'OR' => [
                            [
                                'type' => 'user_preference',
                                'field' => 'check',
                                'operator' => '=',
                                'value' => 'hole'
                            ]
                        ]
                    ],
                    [
                        'field' => 'lastname',
                        'operator' => '=',
                        'value' => 'Иванов'
                    ],
                ]
            ]
        ];

        // тест кейс с ошибкой
        $testcases[] = [
            'expected' => false,
            'params' => [
                'AND' => [
                    [
                        'OR' => [
                            [
                                'type' => 'user_preference',
                                'field' => 'check',
                                'operator' => '=',
                                'value' => 'hole'
                            ],
                            [
                                'type' => 'user_custom_field',
                                'field' => 'ispoliceman',
                                'operator' => '=',
                                'value' => 1
                            ]
                        ]
                    ]
                ]
            ]
        ];
        
        $collationinfo = explode('_', $DB->get_dbcollation());
        $bincollate = reset($collationinfo) . '_bin';
        // успешный тест кейс
        $testcases[] = [
            'expected' => [
                // тут сука должен быть пробел, потому что мы передаем пустой fieldname
                "(danger  LIKE :value_1 COLLATE $bincollate ESCAPE '\\\')",
                ['value_1' => '%1check2%']
            ],
            'params' => [
                'field' => 'danger',
                'operator' => 'LIKE',
                'value' => '1check2'
            ]
        ];
        
        // успешный тест кейс
        $testcases[] = [
            'expected' => [
                // тут сука должен быть пробел, потому что мы передаем пустой fieldname
                "(danger  NOT LIKE :value_1 COLLATE $bincollate ESCAPE '\\\')",
                ['value_1' => '%1check2%']
            ],
            'params' => [
                'field' => 'danger',
                'operator' => 'NOT LIKE',
                'value' => '1check2'
            ]
        ];
        
        $testcases[] = [
            'expected' => [
                "((TabNumber = :value_1) AND (KursID = :value_2) AND (Cmid IS NULL))",
                ['value_1' => 'moxhatblu', 'value_2' => 76]
            ],
            'params' => [
                'AND' => [
                    [
                        'field' => 'TabNumber',
                        'operator' => '=',
                        'value' => 'moxhatblu'
                    ],
                    [
                        'field' => 'KursID',
                        'operator' => '=',
                        'value' => 76
                    ],
                    [
                        'field' => 'Cmid',
                        'operator' => 'IS NULL',
                        'value' => ''
                    ]
                ]
            ]
        ];
        
        $testcases[] = [
            'expected' => [
                "((TabNumber = :value_1) AND (KursID = :value_2) AND (Cmid IS NOT NULL))",
                ['value_1' => 'moxhatblu', 'value_2' => 76]
            ],
            'params' => [
                'AND' => [
                    [
                        'field' => 'TabNumber',
                        'operator' => '=',
                        'value' => 'moxhatblu'
                    ],
                    [
                        'field' => 'KursID',
                        'operator' => '=',
                        'value' => 76
                    ],
                    [
                        'field' => 'Cmid',
                        'operator' => 'IS NOT NULL',
                        'value' => ''
                    ]
                ]
            ]
        ];
        
        foreach ($testcases as $testcase)
        {
            $parser = new local_pprocessing\condition_parser($testcase['params'], new \local_pprocessing\container());
            $result = $parser->parse();
            $this->assertEquals($testcase['expected'], $result);
        }
    }
}
