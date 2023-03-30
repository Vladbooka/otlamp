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
 * otimagepointer question type restore handler
 *
 * @package    qtype_otimagepointer
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Restore plugin class that provides the necessary information needed to restore otimagepointer
 */
class restore_qtype_otimagepointer_plugin extends restore_qtype_plugin {

    /**
     * Returns the paths to be handled by the plugin at question level
     */
    protected function define_question_plugin_structure() {

        $paths = [];

        // This qtype uses question_answers, add them.
        $this->add_question_question_answers($paths);

        // Add own qtype stuff.
        $paths[] = new restore_path_element(
            'otimagepointer', 
            $this->get_pathfor('/otimagepointer') // We used get_recommended_name() so this works.
            );

        return $paths; // And we return the interesting paths.
    }

    /**
     * Process the qtype/otimagepointer element
     *
     * @param array $data
     */
    public function process_otimagepointer($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        // Detect if the question is created or mapped
        // "question" is the XML tag name, not the DB field name.
        $oldquestionid   = $this->get_old_parentid('question');
        $newquestionid   = $this->get_new_parentid('question');

        // If the question has been created by restore,
        // we need to create a "question_otimagepointer_opts" record
        // and create a mapping from the $oldid to the $newid.
        if ($this->get_mappingid('question_created', $oldquestionid)) {
            $data->question = $newquestionid;
            $newid = $DB->insert_record('question_otimagepointer_opts', $data);
            $this->set_mapping('question_otimagepointer_opts', $oldid, $newid);
        }
    }

    /**
     * Given one question_states record, return the answer
     * recoded pointing to all the restored stuff for ordering questions.
     * If not empty, answer is one question_answers->id.
     *
     * @param object $state
     */
    public function recode_legacy_state_answer($state) {
        $answer = $state->answer;
        $result = '';
        if ($answer) {
            $result = $this->get_mappingid('question_answer', $answer);
        }
        return $result;
    }
}
