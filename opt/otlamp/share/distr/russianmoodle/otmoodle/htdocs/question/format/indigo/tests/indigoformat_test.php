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
 * Unit-тесты для формата Indigo.
 *
 * @package    qformat
 * @subpackage indigo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/questionlib.php');
require_once($CFG->dirroot . '/question/format.php');
require_once($CFG->dirroot . '/question/format/indigo/format.php');
require_once($CFG->dirroot . '/question/engine/tests/helpers.php');
require_once($CFG->dirroot . '/question/type/ordering/question.php');


/**
 * Unit tests for the Indigo import format.
 *
 * @package    qformat
 * @subpackage indigo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qformat_indigo_test extends question_testcase {
    public function test_import_match() {
        $indigo = 'Соотнесите слово с его переводом:
wall = стена
window = окно
bath = ванна
door = дверь
= балкон
= крыша';
        $lines = preg_split('/[\\n\\r]/', str_replace("\r\n", "\n", $indigo));

        $importer = new qformat_indigo();
        $q = $importer->readquestion($lines);

        $expectedq = (object) array(
            'name' => 'Соотнесите слово с его переводом:',
            'questiontext' => 'Соотнесите слово с его переводом:',
            'questiontextformat' => FORMAT_MOODLE,
            'generalfeedback' => '',
            'generalfeedbackformat' => FORMAT_MOODLE,
            'qtype' => 'match',
            'defaultmark' => 1,
            'penalty' => 0,
            'length' => 1,
            'shuffleanswers' => '1',
            'correctfeedback' => array(
                'text' => '',
                'format' => FORMAT_MOODLE,
                'files' => array(),
            ),
            'partiallycorrectfeedback' => array(
                'text' => '',
                'format' => FORMAT_MOODLE,
                'files' => array(),
            ),
            'incorrectfeedback' => array(
                'text' => '',
                'format' => FORMAT_MOODLE,
                'files' => array(),
            ),
            'subquestions' => array(
                0 => array(
                    'text' => 'wall',
                    'format' => FORMAT_MOODLE,
                    'files' => array(),
                ),
                1 => array(
                    'text' => 'window',
                    'format' => FORMAT_MOODLE,
                    'files' => array(),
                ),
                2 => array(
                    'text' => 'bath',
                    'format' => FORMAT_MOODLE,
                    'files' => array(),
                ),
                3 => array(
                    'text' => 'door',
                    'format' => FORMAT_MOODLE,
                    'files' => array(),
                ),
                4 => array(
                    'text' => '',
                    'format' => FORMAT_MOODLE,
                    'files' => array(),
                ),
                5 => array(
                    'text' => '',
                    'format' => FORMAT_MOODLE,
                    'files' => array(),
                )
            ),
            'subanswers' => array(
                0 => 'стена',
                1 => 'окно',
                2 => 'ванна',
                3 => 'дверь',
                4 => 'балкон',
                5 => 'крыша'
            ),
        );

        // Repeated test for better failure messages.
        $this->assertEquals($expectedq->subquestions, $q->subquestions);
        $this->assert(new question_check_specified_fields_expectation($expectedq), $q);
    }

    public function test_import_multichoice() {
        $indigo = 'Основоположник античной диалектики, автор слов "В одну реку нельзя войти дважды"?
Фалес
*Гераклит
Протагор
Платон';
        $lines = preg_split('/[\\n\\r]/', str_replace("\r\n", "\n", $indigo));

        $importer = new qformat_indigo();
        $q = $importer->readquestion($lines);

        $expectedq = (object) array(
            'name' => 'Основоположник античной диалектики, автор слов "В одну реку нельзя войти дважды"?',
            'questiontext' => 'Основоположник античной диалектики, автор слов "В одну реку нельзя войти дважды"?',
            'questiontextformat' => FORMAT_MOODLE,
            'generalfeedback' => '',
            'generalfeedbackformat' => FORMAT_MOODLE,
            'qtype' => 'multichoice',
            'defaultmark' => 1,
            'penalty' => 0,
            'length' => 1,
            'single' => 1,
            'shuffleanswers' => '1',
            'answernumbering' => 'abc',
            'correctfeedback' => array(
                'text' => '',
                'format' => FORMAT_MOODLE,
                'files' => array(),
            ),
            'partiallycorrectfeedback' => array(
                'text' => '',
                'format' => FORMAT_MOODLE,
                'files' => array(),
            ),
            'incorrectfeedback' => array(
                'text' => '',
                'format' => FORMAT_MOODLE,
                'files' => array(),
            ),
            'answer' => array(
                0 => array(
                    'text' => 'Фалес',
                    'format' => FORMAT_MOODLE,
                    'files' => array(),
                ),
                1 => array(
                    'text' => 'Гераклит',
                    'format' => FORMAT_MOODLE,
                    'files' => array(),
                ),
                2 => array(
                    'text' => 'Протагор',
                    'format' => FORMAT_MOODLE,
                    'files' => array(),
                ),
                3 => array(
                    'text' => 'Платон',
                    'format' => FORMAT_MOODLE,
                    'files' => array(),
                )
            ),
            'fraction' => array(0, 1, 0, 0),
            'feedback' => array(
                0 => array(
                    'text' => '',
                    'format' => FORMAT_MOODLE,
                    'files' => array(),
                ),
                1 => array(
                    'text' => '',
                    'format' => FORMAT_MOODLE,
                    'files' => array(),
                ),
                2 => array(
                    'text' => '',
                    'format' => FORMAT_MOODLE,
                    'files' => array(),
                ),
                3 => array(
                    'text' => '',
                    'format' => FORMAT_MOODLE,
                    'files' => array(),
                )
            ),
        );

        // Repeated test for better failure messages.
        $this->assertEquals($expectedq->answer, $q->answer);
        $this->assertEquals($expectedq->feedback, $q->feedback);
        $this->assert(new question_check_specified_fields_expectation($expectedq), $q);
    }

    public function test_import_multichoice_multi() {
        $indigo = 'Укажите философские категории:
#материя
#сознание
власть
интеграция
#бытие
революция';
        $lines = preg_split('/[\\n\\r]/', str_replace("\r\n", "\n", $indigo));

        $importer = new qformat_indigo();
        $q = $importer->readquestion($lines);

        $expectedq = (object) array(
            'name' => 'Укажите философские категории:',
            'questiontext' => 'Укажите философские категории:',
            'questiontextformat' => FORMAT_MOODLE,
            'generalfeedback' => '',
            'generalfeedbackformat' => FORMAT_MOODLE,
            'qtype' => 'multichoice',
            'defaultmark' => 1,
            'penalty' => 0,
            'length' => 1,
            'single' => 0,
            'shuffleanswers' => '1',
            'answernumbering' => 'abc',
            'correctfeedback' => array(
                'text' => '',
                'format' => FORMAT_MOODLE,
                'files' => array(),
            ),
            'partiallycorrectfeedback' => array(
                'text' => '',
                'format' => FORMAT_MOODLE,
                'files' => array(),
            ),
            'incorrectfeedback' => array(
                'text' => '',
                'format' => FORMAT_MOODLE,
                'files' => array(),
            ),
            'answer' => array(
                0 => array(
                    'text' => 'материя',
                    'format' => FORMAT_MOODLE,
                    'files' => array(),
                ),
                1 => array(
                    'text' => 'сознание',
                    'format' => FORMAT_MOODLE,
                    'files' => array(),
                ),
                2 => array(
                    'text' => 'власть',
                    'format' => FORMAT_MOODLE,
                    'files' => array(),
                ),
                3 => array(
                    'text' => 'интеграция',
                    'format' => FORMAT_MOODLE,
                    'files' => array(),
                ),
                4 => array(
                    'text' => 'бытие',
                    'format' => FORMAT_MOODLE,
                    'files' => array(),
                ),
                5 => array(
                    'text' => 'революция',
                    'format' => FORMAT_MOODLE,
                    'files' => array(),
                )
            ),
            'fraction' => [
                1/3,
                1/3,
                0,
                0,
                1/3,
                0
            ],
            'feedback' => array(
                0 => array(
                    'text' => '',
                    'format' => FORMAT_MOODLE,
                    'files' => array(),
                ),
                1 => array(
                    'text' => '',
                    'format' => FORMAT_MOODLE,
                    'files' => array(),
                ),
                2 => array(
                    'text' => '',
                    'format' => FORMAT_MOODLE,
                    'files' => array(),
                ),
                3 => array(
                    'text' => '',
                    'format' => FORMAT_MOODLE,
                    'files' => array(),
                ),
                4 => array(
                    'text' => '',
                    'format' => FORMAT_MOODLE,
                    'files' => array(),
                ),
                5 => array(
                    'text' => '',
                    'format' => FORMAT_MOODLE,
                    'files' => array(),
                )
            ),
        );

        // Repeated test for better failure messages.
        $this->assertEquals($expectedq->answer, $q->answer);
        $this->assertEquals($expectedq->feedback, $q->feedback);
        $this->assert(new question_check_specified_fields_expectation($expectedq), $q);
    }

    public function test_import_shortanswer() {
        $indigo = 'Кто из античных философов является основоположником формальной логики?
[Аристотель]';
        $lines = preg_split('/[\\n\\r]/', str_replace("\r\n", "\n", $indigo));

        $importer = new qformat_indigo();
        $q = $importer->readquestion($lines);

        $expectedq = (object) array(
            'name' => 'Кто из античных философов является основоположником формальной логики?',
            'questiontext' => 'Кто из античных философов является основоположником формальной логики?',
            'questiontextformat' => FORMAT_MOODLE,
            'generalfeedback' => '',
            'generalfeedbackformat' => FORMAT_MOODLE,
            'qtype' => 'shortanswer',
            'defaultmark' => 1,
            'penalty' => 0,
            'length' => 1,
            'answer' => array(
                'Аристотель'
            ),
            'fraction' => array(1),
            'feedback' => array(
                0 => array(
                    'text' => '',
                    'format' => FORMAT_MOODLE,
                    'files' => array(),
                )
            ),
        );

        // Repeated test for better failure messages.
        $this->assertEquals($expectedq->answer, $q->answer);
        $this->assertEquals($expectedq->fraction, $q->fraction);
        $this->assertEquals($expectedq->feedback, $q->feedback);
        $this->assert(new question_check_specified_fields_expectation($expectedq), $q);
    }
    
    public function test_import_ordering() {
        $indigo = 'Расположите в хронологическом порядке имена правителей Руси:
Владимир Мономах
Александр Невский
Иван IV Грозный
Пётр I Великий
Екатерина II Великая';
        $lines = preg_split('/[\\n\\r]/', str_replace("\r\n", "\n", $indigo));
    
        $importer = new qformat_indigo();
        $q = $importer->readquestion($lines);
    
        $expectedq = (object) array(
            'name' => 'Расположите в хронологическом порядке имена правителей Руси:',
            'questiontext' => 'Расположите в хронологическом порядке имена правителей Руси:',
            'questiontextformat' => FORMAT_MOODLE,
            'generalfeedback' => '',
            'generalfeedbackformat' => FORMAT_MOODLE,
            'qtype' => 'ordering',
            'defaultmark' => 1,
            'penalty' => 0,
            'length' => 1,
            'correctfeedback' => array(
                'text' => '',
                'format' => FORMAT_MOODLE,
                'itemid' => 0,
                'files' => ''
            ),
            'partiallycorrectfeedback' => array(
                'text' => '',
                'format' => FORMAT_MOODLE,
                'itemid' => 0,
                'files' => ''
            ),
            'incorrectfeedback' => array(
                'text' => '',
                'format' => FORMAT_MOODLE,
                'itemid' => 0,
                'files' => ''
            ),
            'answer' => array(
                'Владимир Мономах',
                'Александр Невский',
                'Иван IV Грозный',
                'Пётр I Великий',
                'Екатерина II Великая'
            ),
            'answerformat' => array(FORMAT_MOODLE,FORMAT_MOODLE,FORMAT_MOODLE,FORMAT_MOODLE,FORMAT_MOODLE),
            'fraction' => array(1,1,1,1,1),
            'feedback' => array('','','','',''),
            'feedbackformat' => array(FORMAT_MOODLE,FORMAT_MOODLE,FORMAT_MOODLE,FORMAT_MOODLE,FORMAT_MOODLE),
            'layouttype' => qtype_ordering_question::LAYOUT_VERTICAL,
            'gradingtype' => qtype_ordering_question::GRADING_ALL_OR_NOTHING,
            'selecttype' => qtype_ordering_question::SELECT_ALL,
            'showgrading' => 1,
            'selectcount' => 0
            
        );
    
        // Repeated test for better failure messages.
        $this->assertEquals($expectedq->answer, $q->answer);
        $this->assertEquals($expectedq->fraction, $q->fraction);
        $this->assertEquals($expectedq->feedback, $q->feedback);
        $this->assert(new question_check_specified_fields_expectation($expectedq), $q);
    }
}
