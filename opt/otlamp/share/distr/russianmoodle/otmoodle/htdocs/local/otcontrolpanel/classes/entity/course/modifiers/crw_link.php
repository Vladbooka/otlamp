<?php
namespace local_otcontrolpanel\entity\course\modifiers;

use local_otcontrolpanel\modifier\common\abstract_modifier;

class crw_link extends abstract_modifier {
    /**
     * {@inheritDoc}
     * @see \local_otcontrolpanel\modifier\common\abstract_modifier::modify()
     */
    public function modify($value, $record)
    {
        $crwurl = new \moodle_url('/local/crw/course.php', ['id' => $record->id]);
        $crwtext = $value;
        $crwattrs = [
            'title' => $record->fullname,
            'target' => '_blank'
        ];
        return \html_writer::link($crwurl, $crwtext, $crwattrs);
    }
}
