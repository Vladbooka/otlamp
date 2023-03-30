<?php
namespace local_otcontrolpanel\entity\cohort\modifiers;

use context;
use html_writer;
use local_otcontrolpanel\modifier\common\abstract_modifier;

class cohort_assign_button extends abstract_modifier {
    /**
     * {@inheritDoc}
     * @see \local_otcontrolpanel\modifier\common\abstract_modifier::modify()
     */
    public function modify($value, $record)
    {
        global $OUTPUT, $PAGE;

        $cohortcontext = context::instance_by_id($record->contextid);

        if (has_capability('moodle/cohort:assign', $cohortcontext)) {
            $assignurl = new \moodle_url('/cohort/assign.php', [
                'id' => $record->id,
                'returnurl' => $PAGE->url->out_as_local_url(false)
            ]);
            $assigntext = $OUTPUT->pix_icon('i/users', get_string('assign', 'core_cohort'));
            $assignattrs = [
                'title' => get_string('assign', 'core_cohort'),
                'target' => '_blank'
            ];

            $value = html_writer::link($assignurl, $assigntext, $assignattrs) . ' ' . $value;
        }

        return $value;
    }
}