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

namespace mod_event3kl\provider;

defined('MOODLE_INTERNAL') || die();

use mod_event3kl\provider\base\provider_interface;
use mod_event3kl\provider\base\abstract_provider;
use Exception;
use mod_event3kl\manage_providers_form;
use moodle_url;
use core\notification;
use mod_event3kl\session;
use mod_event3kl\event3kl;
use mod_event3kl\session_member;
use mod_event3kl\format\base\abstract_format;

/**
 * Класс внешнего провайдера
 *
 * @package   mod_event3kl
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class external extends abstract_provider implements provider_interface {

    protected $manageprovidersurl = '/mod/event3kl/manage_providers.php';
    protected $customdata = [];

    /**
     * в зависимости от текущего действия (бездействия) отображает нужные поля через соответсвующий метод
     * {@inheritDoc}
     * @see \mod_event3kl\provider\base\provider_interface::settings_definition()
     */
    public function settings_definition(& $mform, & $form) {
        $action = $this->customdata['action'] ?? 'default';
        switch($action) {
            case 'add':
                $this->settings_definition_create_provider($mform, $form);
                break;
            case 'edit':
                $this->settings_definition_edit_provider($mform, $form);
                break;
            case 'delete':
                $this->settings_definition_delete_provider($mform, $form);
                break;
            default:
                $this->settings_definition_providers_list($mform, $form);
                $this->settings_definition_select_provider($mform, $form);
        }
    }

    /**
     * в зависимости от текущего действия валидирует форму через соответствующий метод
     * {@inheritDoc}
     * @see \mod_event3kl\provider\base\provider_interface::settings_validation()
     */
    public function settings_validation($data, $files) {
        return [];
    }

    /**
     * в зависимости от текущего действия обрабатывает форму через соответствующий метод
     * {@inheritDoc}
     * @see \mod_event3kl\provider\base\provider_interface::settings_processing()
     */
    public function settings_processing(& $mform, & $form) {
        if ($formdata = $form->get_data()) {
            // Редирект на форму редактирования/удаления провайдера
            if (!empty($formdata->provider_actions) && is_array($formdata->provider_actions)) {
                foreach ($formdata->provider_actions as $provider) {
                    if (!empty($provider['edit']) || !empty($provider['delete'])) {
                        $action = !empty($provider['edit']) ? 'edit' : 'delete';
                        $this->settings_processing_providers_list($action, $provider['name']);
                    }
                }
            }

            // Редирект на форму добавления провайдера
            if (!empty($formdata->available_providers['add'])) {
                $this->settings_processing_select_provider($formdata->available_providers['code']);
            }

            // Процесс обработки добавления провайдера
            if (!empty($formdata->add_provider)) {
                $this->settings_processing_create_provider($formdata);
            }
            // Процесс обработки редактирования провайдера
            if (!empty($formdata->edit)) {
                $this->settings_processing_edit_provider($formdata);
            }

            // Процесс обработки удаления провайдера
            if (!empty($formdata->delete)) {
                $this->settings_processing_delete_provider($formdata);
            }
        }
    }

    /**
     * отображение уже добавленных провайдеров (request_providers_list) с возможностью редактирования / удаления каждого
     */
    public function settings_definition_providers_list(& $mform, & $form) {
        try {
            $providers = $this->request_providers_list();
            foreach($providers as $provider) {
                $group = [];
                $group[] = $mform->createElement('hidden', 'name', $provider['name']);
                $group[] = $mform->createElement('static', 'providerlabel', $provider['name']);
                $group[] = $mform->createElement('submit', 'edit', get_string('edit_provider', 'mod_event3kl'));
                $group[] = $mform->createElement('submit', 'delete', get_string('delete_provider', 'mod_event3kl'));

                $mform->addGroup($group, 'provider_actions['.$provider['name'].']', $provider['name']);
                $mform->setType('provider_actions['.$provider['name'].']', PARAM_RAW);
            }
        } catch (Exception $e) {

        }
    }

    /**
     *
     */
    public function settings_validation_providers_list() {
        ;
    }

    /**
     * редиректит на интерфейс редакторования / удаления
     */
    public function settings_processing_providers_list($action, $name) {
        redirect(new moodle_url($this->manageprovidersurl, [
            'action' => $action,
            'name' => $name
        ]));
    }

    /**
     * форма выбора провайдера для добавления (получение типов реализованных провайдеров - request_possible_providers)
     */
    public function settings_definition_select_provider(& $mform, & $form) {
        try {
            $providers = $this->request_possible_providers();
            if (!empty($providers)) {
                $options = [];
                foreach($providers as $provider) {
                    $options[$provider['code']] = $provider['displayname'];
                }

                $select = $mform->createElement('select', 'code', get_string('add_provider_select_label', 'mod_event3kl'), $options);
                $submit = $mform->createElement('submit', 'add', get_string('add_provider_submit_value', 'mod_event3kl'));
                $mform->addGroup([$select, $submit], 'available_providers', get_string('add_provider_group_label', 'mod_event3kl'));
            }

        } catch (Exception $e) {
            error_log($e->getMessage() . '<br/>' . format_backtrace($e->getTrace()));
            $mform->addElement('static', 'nodata', '', get_string('there_are_no_providers', 'mod_event3kl'));
        }
    }

    /**
     *
     */
    public function settings_validation_select_provider() {
        ;
    }

    /**
     * редиректит на интерфейс добавления нового провайдера выбранного типа
     */
    public function settings_processing_select_provider($code) {
        redirect(new moodle_url($this->manageprovidersurl, [
            'action' => 'add',
            'code' => $code
        ]));
    }

    /**
     * форма добавления нового провайдера (отображает поля-настройки, полученные с помощью request_provider_settings)
     * @param MoodleQuickForm $mform MoodleQuickForm quickform object definition
     * @param manage_providers_form $form объект класса формы
     */
    public function settings_definition_create_provider(& $mform, & $form) {
        $code = $this->customdata['code'] ?? null;
        if (is_null($code)) {
            return;
        }

        // Заголовок
        $mform->addElement('header', 'add_provider__'.$code, $this->get_instance_display_name($code));

        // Код провайдера
        $mform->addElement('hidden', 'credentials[code]', $code);
        $mform->setType('credentials[code]', PARAM_RAW);

        // Реквизиты источника
        $structure = $this->request_provider_settings_structure($code);
        $this->definition_structure($mform, $form, $structure);

        $mform->addElement('submit', 'add_provider', get_string('add_provider', 'mod_event3kl'));
    }

    /**
     * Собрать форму по полученной структуре настроек провайдера
     * @param MoodleQuickForm $mform MoodleQuickForm quickform object definition
     * @param manage_providers_form $form объект класса формы
     * @param array $structure структура настроек провайдера, полученная от ot api
     * @param boolean $requiredrequired нужно ли сделать обязательными переданные обязательные настройки
     */
    protected function definition_structure(& $mform, & $form, $structure, $requiredrequired = true) {
        foreach($structure as $argname => $argdata) {
            if (is_array($argdata) && !empty($argdata['credential'])) {
                $elementname = 'credentials['.$argname.']';
                $label = $argname;
                if (!empty($argdata['title'])) {
                    $label = $argdata['title'];
                }

                switch($argdata['filter'])
                {
                    case FILTER_VALIDATE_BOOLEAN:
                        $elementtype = 'advcheckbox';
                        $paramtype = PARAM_BOOL;
                        $defaultvalue = 0;
                        $requiredrequired = false;
                        break;
                    case FILTER_VALIDATE_URL:
                        $elementtype = 'text';
                        $paramtype = PARAM_URL;
                        $defaultvalue = '';
                        break;
                    case FILTER_UNSAFE_RAW:
                    default:
                        $elementtype = 'text';
                        $paramtype = PARAM_RAW;
                        $defaultvalue = '';
                        break;
                }

                $mform->addElement($elementtype, $elementname, $label);
                $mform->setDefault($elementname, ($argdata['value'] ?? $defaultvalue));

                if (!empty($argdata['required']) && $requiredrequired) {
                    $mform->addRule($elementname, null, 'required');
                }
                if (empty($argdata['filter'])) {
                    $argdata['filter'] = FILTER_UNSAFE_RAW;
                }
                $mform->setType($elementname, $paramtype);
            }
        }
    }

    /**
     *
     */
    public function settings_validation_create_provider() {
        ;
    }

    /**
     * сохраняет через request_provider_create api.ot
     */
    public function settings_processing_create_provider($formdata) {
        try {
            $this->request_provider_create($formdata->credentials);
//             $this->purge_caches();
            redirect(new moodle_url($this->manageprovidersurl));

        } catch(Exception $ex)
        {
            notification::error(get_string('add_provider_instance_failed', 'mod_event3kl'));
        }
    }

    /**
     * форма редактирования ранее добавленного провайдера (отображает поля-настройки, полученные с помощью request_provider_settings)
     */
    public function settings_definition_edit_provider(& $mform, & $form) {
        $name = $this->customdata['name'] ?? null;
        if (is_null($name)) {
            return;
        }

        $providerdata = $this->request_provider_settings($name);

        // Заголовок
        $mform->addElement('header', 'edit_provider__' . $name, get_string('editing_provider', 'mod_event3kl', $name));

        // Код провайдера
        if (!empty($providerdata['code'])) {
            $mform->addElement('hidden', 'credentials[code]', $providerdata['code']);
            $mform->setType('credentials[code]', PARAM_RAW);
        }

        // Наименование провайдера
        if (!empty($providerdata['name'])) {
            $mform->addElement('hidden', 'name', $providerdata['name']);
            $mform->setType('name', PARAM_RAW);
        }

        foreach(array_keys($providerdata['configfields']) as $field) {
            if (array_key_exists($field, $providerdata)) {
                $providerdata['configfields'][$field]['value'] = $providerdata[$field];
            }
        }
        $this->definition_structure($mform, $form, $providerdata['configfields'], false);

        $mform->addElement('submit', 'edit', get_string('edit_provider', 'mod_event3kl'));
    }

    /**
     *
     */
    public function settings_validation_edit_provider() {
        ;
    }

    /**
     * сохраняет через request_provider_edit
     */
    public function settings_processing_edit_provider($formdata) {
        try {
            $this->request_provider_edit($formdata->name, $formdata->credentials);
//             $this->purge_caches();
            redirect(new moodle_url($this->manageprovidersurl));
        } catch(Exception $ex)
        {
            notification::error(get_string('update_provider_instance_failed', 'mod_event3kl'));
        }
    }

    /**
     * форма удаления ранее добавленного провайдера (запрашивает подтверждение на выполнение действия)
     */
    public function settings_definition_delete_provider(& $mform, & $form) {
        $name = $this->customdata['name'] ?? null;
        if (is_null($name)) {
            return;
        }

        // Заголовок
        $mform->addElement('header', 'delete_provider__' . $name, get_string('deleting_provider', 'mod_event3kl', $name));

        // Код провайдера
        $mform->addElement('hidden', 'name', $name);
        $mform->setType('name', PARAM_RAW);

        $mform->addElement('submit', 'delete', get_string('delete_provider', 'mod_event3kl'));
    }

    /**
     *
     */
    public function settings_validation_delete_provider() {
        ;
    }

    /**
     * производит действие через request_provider_delete
     */
    public function settings_processing_delete_provider($formdata) {
        try {
            $this->request_provider_delete($formdata->name);
//             $this->purge_caches();
            redirect(new moodle_url($this->manageprovidersurl));
        } catch(Exception $ex)
        {
            notification::error(get_string('delete_provider_instance', 'mod_event3kl'));
        }
    }

    /**
     * добавляет элемент для выбора из списка провайдеров, полученных через request_providers_list
     * {@inheritDoc}
     * @see \mod_event3kl\provider\base\provider_interface::mod_form_definition()
     */
    public function mod_form_definition(\MoodleQuickForm &$mform, \mod_event3kl_mod_form &$form) {
        $options = [];
        $eproviders = $this->request_providers_list();
        if (!empty($eproviders)) {
            foreach ($eproviders as $eprovider) {
                if ($eprovider['active']) {
                    $options[$eprovider['name']] = $eprovider['name'] . ' (' . $eprovider['displayname'] . ')';
                }
            }
        }
        if (!empty($options)) {
            $mform->addElement('select', 'providerinstance', get_string('providerinstance', 'mod_event3kl'), $options);
            $mform->hideIf('providerinstance', 'provider', 'notequal', 'external');
        } else {
            $select = & $mform->getElement('provider');
            $select->removeOption('external');
        }

    }

    /**
     * {@inheritDoc}
     * @see \mod_event3kl\provider\base\provider_interface::mod_form_validation()
     */
    public function mod_form_validation($data, $files) {
        return [];
    }

    /**
     * {@inheritDoc}
     * @see \mod_event3kl\provider\base\provider_interface::mod_form_processing()
     */
    public function mod_form_processing(array $formdata)
    {
        return ['providerinstance' => $formdata['providerinstance']];
    }

    /**
     * получение списка реализованных провайдеров на стороне api.ot, доступных к настройке и использованию
     */
    public function request_possible_providers() {
        return $this->otserial->get_providers_types();
    }

    /**
     * получение из api.ot списка настроенных ранее провайдеров
     */
    public function request_providers_list() {
        return $this->otserial->get_providers_instances();
    }

    /**
     * получение из api.ot структуры настроек, свойственных указанному провайдеру (код, отображаемое название, тип поля, обязательное ли, указания для валидации)
     * @param string $code код внешнего провайдера
     */
    public function request_provider_settings_structure($code) {
        $providerstype = $this->otserial->get_providers_type($code);
        $settings = $providerstype['configfields'];
        return $settings;
    }

    /**
     * получение из api.ot структуры настроек, свойственных указанному провайдеру, с сохраненными данными
     * @param string $name имя внешнего провайдера
     */
    public function request_provider_settings($name) {
        $providersinstance = $this->otserial->get_providers_instance($name);
        return $providersinstance;
    }

    /**
     * сохраняет через api.ot
     */
    public function request_provider_create($credentials) {
        $this->otserial->create_providers_instance($credentials);
    }

    /**
     * сохраняет через api.ot
     */
    public function request_provider_edit($name, $credentials) {
        $this->otserial->update_providers_instance($name, $credentials);
    }

    /**
     * производит действие через api.ot
     */
    public function request_provider_delete($name) {
        $this->otserial->delete_providers_instance($name);
    }

    /**
     * стартует сессию и возвращает её идентификатор (ентификатор конференции)
     */
    private function request_provider_start_session(string $providername, array $sessiondata) {
        $response = $this->otserial->provider_instance_start_session($providername, $sessiondata);
        return $response;
    }

    /**
     * завершает сессию
     */
    private function request_provider_finish_session(string $providername, $extid) {
        $response = $this->otserial->provider_instance_finish_session($providername, $extid);
        return $response;
    }

    /**
     * получение массива ссылок на записи конференции
     */
    private function request_provider_session_records(string $providername, $extid) {
        $response = $this->otserial->provider_instance_session_records($providername, $extid);
        return $response;
    }

    /**
     * получение ссылки для присоединения к конференции
     */
    private function request_provider_get_session_enter_url($providername, $sessionExternalId, array $userdata) {
        $response = $this->otserial->provider_instance_get_session_enter_url($providername, $sessionExternalId, $userdata);
        return $response;
    }

    /**
     * Отображаемое название типа провайдера ("внешний провайдер")
     * {@inheritDoc}
     * @see \mod_event3kl\provider\base\provider_interface::get_display_name()
     */
    public function get_display_name() {
        return get_string($this->get_code() . '_provider_display_name', 'mod_event3kl');
    }
    /**
     * Отображаемое название инстанса типа провайдера
     */
    public function get_instance_display_name($code) {
        $providerstype = $this->otserial->get_providers_type($code);
        $displayname = $providerstype['displayname'];
        return $displayname;
    }
    /**
     * Получить код типа провайдера
     */
    public function get_code() {
        return (new \ReflectionClass($this))->getShortName();
    }

    private function prepare_userdata($user, $role) {
        return [
            'id' => $user->id,
            'firstname' => $user->firstname,
            'lastname' => $user->lastname,
            'fullname' => fullname($user),
            'email' => $user->email,
            'role' => $role
        ];
    }

    private function prepare_sessiondata(session $session, event3kl $event3kl) {
        $sessiondata = [
            'name' => $session->get('name'),
            'description' => '',
            'users' => []
        ];

        // собираем идентификаторы пользователей, зарегистрированных на сессию
        // не тех, кто мог бы, а тех, кто действительно привязан к сессии
        $userids = [];
        $members = session_member::get_records(['sessionid' => $session->get('id')]);
        foreach($members as $member) {
            $userids[] = $member->get('userid');
        }
        // добавляем этих пользователей в роли студента
        $users = user_get_users_by_id($userids);
        foreach($users as $user) {
            $sessiondata['users'][$user->id] = $this->prepare_userdata($user, 'student');
        }

        // получаем спикеров, доступных на текущий момент для сессии
        // а вот для спикеров не выполняется привязок к сессии, получаем всех кто мог бы спикать в группе
        $eventusers = $event3kl->get_event_users();
        $groupid = $session->get('groupid');
        if (array_key_exists($groupid, $eventusers) && array_key_exists('speakers', $eventusers[$groupid])) {
            foreach($eventusers[$groupid]['speakers'] as $speaker) {
                $sessiondata['users'][$speaker->id] = $this->prepare_userdata($speaker, 'teacher');
            }
        }

        return $sessiondata;
    }

    public function start_session(session $session, event3kl $event3kl) {
        $providerdatajson = $event3kl->get('providerdata');
        $providerdata = json_decode($providerdatajson, true);
        if (array_key_exists('providerinstance', $providerdata)) {
            $providername = $providerdata['providerinstance'];
            $sessiondata = $this->prepare_sessiondata($session, $event3kl);
            return $this->request_provider_start_session($providername, $sessiondata);
        }
        throw new \Exception('undefined providerinstance');
    }

    public function finish_session(session $session, event3kl $event3kl) {
        $extid = $session->get('extid');
        $providerdatajson = $event3kl->get('providerdata');
        $providerdata = json_decode($providerdatajson, true);
        if (array_key_exists('providerinstance', $providerdata)) {
            $providername = $providerdata['providerinstance'];
            return $this->request_provider_finish_session($providername, $extid);
        }
        throw new \Exception('undefined providerinstance');
    }

    public function get_participate_link(session $session, event3kl $event3kl, $userid)
    {
        $providerdatajson = $event3kl->get('providerdata');
        $providerdata = json_decode($providerdatajson, true);
        if (array_key_exists('providerinstance', $providerdata)) {
            $providername = $providerdata['providerinstance'];

            $userdata = null;

            $sessiondata = $this->prepare_sessiondata($session, $event3kl);
            $sessionusers = $sessiondata['users'] ?? [];
            foreach($sessionusers as $sessionuser) {
                if ($userid == $sessionuser['id']) {
                    $userdata = $sessionuser;
                }
            }

            if (!is_null($userdata)) {
                return $this->request_provider_get_session_enter_url($providername, $session->get('extid'), $userdata);
            }

            throw new \Exception('user not belong to session');
        }
        throw new \Exception('undefined providerinstance');
    }

    public function supports_records_download() {
        return true;
    }

    public function get_records(session $session, event3kl $event3kl): array {
        $extid = $session->get('extid');
        $providerdatajson = $event3kl->get('providerdata');
        $providerdata = json_decode($providerdatajson, true);
        if (array_key_exists('providerinstance', $providerdata)) {
            $providername = $providerdata['providerinstance'];
            return $this->request_provider_session_records($providername, $extid);
        }
        throw new \Exception('undefined providerinstance');
    }

    public function get_record_content(array $recorddata) {
        $options = ['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]];
        return  file_get_contents($recorddata['url'], false, stream_context_create($options));
    }




}