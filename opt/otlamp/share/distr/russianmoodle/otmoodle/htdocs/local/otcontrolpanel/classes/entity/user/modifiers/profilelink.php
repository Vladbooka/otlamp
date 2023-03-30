<?php
namespace local_otcontrolpanel\entity\user\modifiers;
use local_otcontrolpanel\modifier\common\abstract_modifier;

class profilelink extends abstract_modifier {
    /**
     * {@inheritDoc}
     * @see \local_otcontrolpanel\modifier\common\abstract_modifier::modify()
     */
    public function modify($value, $record)
    {
        $url = new \moodle_url('/user/profile.php', ['id' => $record->id]);
        return \html_writer::link($url, $value, ['target' => '_blank']);
    }
}