<?php
namespace local_otcontrolpanel\entity\course\modifiers;

use local_otcontrolpanel\modifier\common\abstract_modifier;

class crw_button extends abstract_modifier {
    /**
     * {@inheritDoc}
     * @see \local_otcontrolpanel\modifier\common\abstract_modifier::modify()
     */
    public function modify($value, $record)
    {
        global $OUTPUT;

        $crwurl = new \moodle_url('/local/crw/course.php', ['id' => $record->id]);
        $crwtext = $OUTPUT->pix_icon('ops_page', $record->fullname, 'local_crw');
        $crwattrs = [
            'title' => $record->fullname,
            'target' => '_blank'
        ];

        return \html_writer::link($crwurl, $crwtext, $crwattrs) . ' ' . $value;
    }
}
