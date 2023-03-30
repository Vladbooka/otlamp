<?php
namespace local_otcontrolpanel\entity\assign_submission\fields;

class assignuserattempt extends \local_otcontrolpanel\field\abstract_field {

    /**
     * {@inheritDoc}
     * @see \local_otcontrolpanel\field\abstract_field::get_plain_value()
     */
    protected function get_plain_value($record, $suffix='')
    {
        $user = (object)[
            'lastname' => $record->userlastname,
            'firstname' => $record->userfirstname,
            'middlename' => $record->usermiddlename,
            'alternatename' => $record->useralternatename,
            'firstnamephonetic' => $record->userfirstnamephonetic,
            'lastnamephonetic' => $record->userlastnamephonetic
        ];
        return $record->assignname.'; '.fullname($user).'; '.$record->attemptnumber.';';
    }

    public function register_joins() {
        try {
            // подключение пользователя-владельца попытки
            $join = $this->entity->get_basic_join('e_asgnsubm_j_user');
            $join->add_required_db_fields([
                'lastname AS "userlastname"',
                'firstname AS "userfirstname"',
                'middlename as "usermiddlename"',
                'alternatename as "useralternatename"',
                'firstnamephonetic as "userfirstnamephonetic"',
                'lastnamephonetic as "userlastnamephonetic"'
            ]);
            $this->register_join($join);

            // подключение задания, к которому относится попытка
            $join = $this->entity->get_basic_join('e_asgnsubm_j_assign');
            $join->add_required_db_fields(['name AS "assignname"']);
            $this->register_join($join);

        } catch(\Exception $ex){}
    }


}