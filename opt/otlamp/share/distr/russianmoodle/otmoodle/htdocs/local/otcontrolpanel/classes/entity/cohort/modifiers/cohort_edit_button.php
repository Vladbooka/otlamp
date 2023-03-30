<?php
namespace local_otcontrolpanel\entity\cohort\modifiers;

use context;
use html_writer;
use local_otcontrolpanel\modifier\common\abstract_modifier;

class cohort_edit_button extends abstract_modifier {
    /**
     * {@inheritDoc}
     * @see \local_otcontrolpanel\modifier\common\abstract_modifier::modify()
     */
    public function modify($value, $record)
    {
        global $OUTPUT, $PAGE;

        $cohortcontext = context::instance_by_id($record->contextid);

        if (has_capability('moodle/cohort:manage', $cohortcontext)) {

            $editurl = new \moodle_url('/cohort/edit.php', [
                'id' => $record->id,
                'returnurl' => $PAGE->url->out_as_local_url(false)
            ]);
            $edittext = $OUTPUT->pix_icon('t/edit', get_string('edit'));
            $editattrs = [
                'title' => get_string('edit'),
                'target' => '_blank'
            ];

            $value = html_writer::link($editurl, $edittext, $editattrs) . ' ' . $value;
        }

        return $value;
    }
}