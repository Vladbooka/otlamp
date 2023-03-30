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
 * Admin settings and defaults.
 *
 * @package auth_otmultildap
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once ($CFG->dirroot . '/auth/otmultildap/locallib.php');

if ($ADMIN->fulltree) {

    if ( ! function_exists('ldap_connect') )
    {
        $settings->add(new admin_setting_heading('auth_otmultildap_noextension', '', get_string('auth_otmultildap_noextension', 'auth_otmultildap')));
    } else
    {
        // получение объекта плагина
        $authplugin = get_auth_plugin('otmultildap');
        $activecode = $authplugin->get_configuration_code_activeregister();

        // получение конфигов плагина
        $fullconfig = get_config('auth_otmultildap');

        $html = '';
        // код конфигурации
        $deleteldapcode = optional_param('deleteldapcode', false, PARAM_BOOL);
        $confirmation = optional_param('confirmationdeleteldapcode', false, PARAM_BOOL);
        $ldapcode = optional_param('ldapcode', null, PARAM_ALPHANUM);
        $availablecodes = $authplugin->get_ldap_codes($fullconfig);
        if ( ! is_null($ldapcode) && in_array($ldapcode, $availablecodes) )
        {
            if ( $deleteldapcode )
            {
                // удаление конфигурации
                if ( $confirmation )
                {
                    // хак, чтоб редирект корректно срабатывал
                    global $PAGE;$PAGE = null;
                    $authplugin->delete_configs_with_ldapcode($ldapcode);
                    redirect(new moodle_url('/admin/settings.php', [
                        'section' => 'authsettingotmultildap'
                    ]));
                }

                $yes = new moodle_url('/admin/settings.php', [
                    'section' => 'authsettingotmultildap',
                    'ldapcode' => $ldapcode,
                    'deleteldapcode' => true,
                    'confirmationdeleteldapcode' => true
                ]);
                $no = new moodle_url('/admin/settings.php', [
                    'section' => 'authsettingotmultildap',
                    'ldapcode' => $ldapcode
                ]);
                $html .= html_writer::link($yes, get_string('delete_confirmation_yes', 'auth_otmultildap'), [
                    'class' => 'btn btn-primary'
                ]).PHP_EOL;
                $html .= html_writer::link($no, get_string('delete_confirmation_no', 'auth_otmultildap'), [
                    'class' => 'btn btn-primary'
                ]).PHP_EOL;

                $settings->add(new admin_setting_heading('auth_otmultildap/delete_configuration',
                        new lang_string('delete_configurations', 'auth_otmultildap', $ldapcode),
                        new lang_string('delete_configurations_description', 'auth_otmultildap') . "\n\n" . $html));
                return;
            } else
            {
                // хак, чтоб редирект корректно срабатывал
                global $PAGE;$PAGE = null;
                // редиректим на нужную конфигурацию
                set_config('choose_configuration', $ldapcode, 'auth_otmultildap');
                redirect(new moodle_url('/admin/settings.php', [
                    'section' => 'authsettingotmultildap'
                ]));
            }
        }

        // флаг удаления конфигурации
        $deleteldapcode = optional_param('delete', null, PARAM_BOOL);


        // Introductory explanation.
        $settings->add(new admin_setting_heading('auth_otmultildap/pluginname', '',
                new lang_string('auth_otmultildapdescription', 'auth_otmultildap')));

        $html = '';
        foreach($availablecodes as $ldapcode)
        {
            $settingsurl = new moodle_url('/admin/settings.php', [
                'section' => 'authsettingotmultildap',
                'ldapcode' => $ldapcode
            ]);
            $settingslink = html_writer::link($settingsurl, $ldapcode);
            $deletesettingsurl = new moodle_url('/admin/settings.php', [
                'section' => 'authsettingotmultildap',
                'ldapcode' => $ldapcode,
                'deleteldapcode' => true
            ]);
            $deletesettingslink = html_writer::link(
                    $deletesettingsurl,
                    html_writer::img($OUTPUT->image_url('t/delete'),get_string('auth_otmultildap_delete_ldapcode_settings_desc', 'auth_otmultildap')));
            $add = '';
            if ($ldapcode == $activecode) {
                $add .= html_writer::div(get_string('ldapcode_general', 'auth_otmultildap'), 'auth_otmultildap_settings_activeregister label');
            }
            $html .= html_writer::div($settingslink.' '.$deletesettingslink . $add, 'auth_otmultildap_settings_link');
        }

        // заголовок существющий конфигураций
        $settings->add(new admin_setting_heading('auth_otmultildap/created_configuration',
                new lang_string('created_configurations', 'auth_otmultildap'),
                new lang_string('created_configurations_description', 'auth_otmultildap') . "\n\n" . $html));

        // заголовок выбора конфигурации
        $settings->add(new admin_setting_heading('auth_otmultildap/configuration_heading',
                new lang_string('choose_ldap_configuration', 'auth_otmultildap'),
                new lang_string('choose_ldap_configuration_description', 'auth_otmultildap')));

        $settings->add(new admin_setting_configtext(
                'auth_otmultildap/choose_configuration',
                get_string('choose_ldap_configuration', 'auth_otmultildap'),
                '',
                '',
                PARAM_ALPHANUMEXT));

        if ( ! empty($deleteldapcode) )
        {

            echo html_writer::div(get_string('auth_otmultildap_confirm_delete_ldap_code', 'auth_otmultildap', $fullconfig->choose_configuration));

            echo html_writer::empty_tag('input', [
                'name' => 'ldap_code_to_delete',
                'id' => 'ldap_code_to_delete',
                'type' => 'hidden',
                'value' => $fullconfig->choose_configuration
            ]);

            $returnurl = new moodle_url('/admin/auth_config.php', ['auth' => 'otmultildap']);

            echo html_writer::link($returnurl, get_string('auth_otmultildap_unconfirm_delete_ldap_code', 'auth_otmultildap'));

        } elseif ( property_exists($fullconfig, 'choose_configuration') && strlen($fullconfig->choose_configuration) > 0 )
        {
            // We use a couple of custom admin settings since we need to massage the data before it is inserted into the DB.
            require_once($CFG->dirroot.'/auth/otmultildap/classes/admin_setting_special_lowercase_configtext.php');
            require_once($CFG->dirroot.'/auth/otmultildap/classes/admin_setting_special_contexts_configtext.php');
            require_once($CFG->dirroot.'/auth/otmultildap/classes/admin_setting_special_ntlm_configtext.php');

            // We need to use some of the Moodle LDAP constants / functions to create the list of options.
            require_once($CFG->dirroot.'/auth/otmultildap/auth.php');

            // LDAP server settings.
            $settings->add(
                    new admin_setting_heading('auth_otmultildap/auth_otmultildapserversettings',
                    new lang_string('auth_otmultildap_server_settings', 'auth_otmultildap', $fullconfig->choose_configuration), ''));

            // Host.
            $settings->add(
                    new admin_setting_configtext("auth_otmultildap/host_url___{$fullconfig->choose_configuration}",
                    get_string('auth_otmultildap_host_url_key', 'auth_otmultildap'),
                    get_string('auth_otmultildap_host_url', 'auth_otmultildap'), '', PARAM_RAW_TRIMMED));

            // Version.
            $versions = array();
            $versions[2] = '2';
            $versions[3] = '3';
            $settings->add(
                    new admin_setting_configselect("auth_otmultildap/ldap_version___{$fullconfig->choose_configuration}",
                            new lang_string('auth_otmultildap_version_key', 'auth_otmultildap'),
                            new lang_string('auth_otmultildap_version', 'auth_otmultildap'), 3, $versions));

            // Start TLS.
            $yesno = array(
                new lang_string('no'),
                new lang_string('yes'),
            );
            $settings->add(
                    new admin_setting_configselect("auth_otmultildap/start_tls___{$fullconfig->choose_configuration}",
                    new lang_string('start_tls_key', 'auth_otmultildap'),
                    new lang_string('start_tls', 'auth_otmultildap'), 0 , $yesno));


            // Encoding.
            $settings->add(
                    new admin_setting_configtext("auth_otmultildap/ldapencoding___{$fullconfig->choose_configuration}",
                            get_string('auth_otmultildap_ldap_encoding_key', 'auth_otmultildap'),
                            get_string('auth_otmultildap_ldap_encoding', 'auth_otmultildap'), 'utf-8', PARAM_RAW_TRIMMED));

            // Page Size. (Hide if not available).
            $settings->add(
                    new admin_setting_configtext("auth_otmultildap/pagesize___{$fullconfig->choose_configuration}",
                            get_string('pagesize_key', 'auth_otmultildap'), get_string('pagesize', 'auth_otmultildap'), '250',
                            PARAM_INT));

            // Bind settings.
            $settings->add(
                    new admin_setting_heading("auth_otmultildap/ldapbindsettings___{$fullconfig->choose_configuration}",
                            new lang_string('auth_otmultildap_bind_settings', 'auth_otmultildap'), ''));

            // Store Password in DB.
            $settings->add(
                    new admin_setting_configselect("auth_otmultildap/preventpassindb___{$fullconfig->choose_configuration}",
                            new lang_string('auth_otmultildap_preventpassindb_key', 'auth_otmultildap'),
                            new lang_string('auth_otmultildap_preventpassindb', 'auth_otmultildap'), 0, $yesno));

            // User ID.
            $settings->add(
                    new admin_setting_configtext("auth_otmultildap/bind_dn___{$fullconfig->choose_configuration}",
                            get_string('auth_otmultildap_bind_dn_key', 'auth_otmultildap'),
                            get_string('auth_otmultildap_bind_dn', 'auth_otmultildap'), '', PARAM_RAW_TRIMMED));

            // Password.
            $settings->add(
                    new admin_setting_configpasswordunmask("auth_otmultildap/bind_pw___{$fullconfig->choose_configuration}",
                            get_string('auth_otmultildap_bind_pw_key', 'auth_otmultildap'),
                            get_string('auth_otmultildap_bind_pw', 'auth_otmultildap'), ''));

            // User Lookup settings.
            $settings->add(
                    new admin_setting_heading("auth_otmultildap/ldapuserlookup___{$fullconfig->choose_configuration}",
                            new lang_string('auth_otmultildap_user_settings', 'auth_otmultildap'), ''));

            // User Type.
            $settings->add(
                    new admin_setting_configselect("auth_otmultildap/user_type___{$fullconfig->choose_configuration}",
                            new lang_string('auth_otmultildap_user_type_key', 'auth_otmultildap'),
                            new lang_string('auth_otmultildap_user_type', 'auth_otmultildap'), 'default',
                            ldap_supported_usertypes()));

            // Contexts.
            $settings->add(
                    new auth_ldap_admin_setting_special_contexts_configtext(
                            "auth_otmultildap/contexts___{$fullconfig->choose_configuration}",
                            get_string('auth_otmultildap_contexts_key', 'auth_otmultildap'),
                            get_string('auth_otmultildap_contexts', 'auth_otmultildap'), '', PARAM_RAW_TRIMMED));

            // Search subcontexts.
            $settings->add(
                    new admin_setting_configselect("auth_otmultildap/search_sub___{$fullconfig->choose_configuration}",
                            new lang_string('auth_otmultildap_search_sub_key', 'auth_otmultildap'),
                            new lang_string('auth_otmultildap_search_sub', 'auth_otmultildap'), 0, $yesno));

            // Dereference aliases.
            $optderef = array();
            $optderef[LDAP_DEREF_NEVER] = get_string('no');
            $optderef[LDAP_DEREF_ALWAYS] = get_string('yes');
            $settings->add(
                    new admin_setting_configselect("auth_otmultildap/opt_deref___{$fullconfig->choose_configuration}",
                            new lang_string('auth_otmultildap_opt_deref_key', 'auth_otmultildap'),
                            new lang_string('auth_otmultildap_opt_deref', 'auth_otmultildap'), LDAP_DEREF_NEVER, $optderef));

            // User attribute.
            $settings->add(
                    new auth_ldap_admin_setting_special_lowercase_configtext(
                            "auth_otmultildap/user_attribute___{$fullconfig->choose_configuration}",
                            get_string('auth_otmultildap_user_attribute_key', 'auth_otmultildap'),
                            get_string('auth_otmultildap_user_attribute', 'auth_otmultildap'), '', PARAM_RAW));

            // Suspended attribute.
            $settings->add(
                    new auth_ldap_admin_setting_special_lowercase_configtext(
                            "auth_otmultildap/suspended_attribute___{$fullconfig->choose_configuration}",
                            get_string('auth_otmultildap_suspended_attribute_key', 'auth_otmultildap'),
                            get_string('auth_otmultildap_suspended_attribute', 'auth_otmultildap'), '', PARAM_RAW));

                // Member attribute.
            $settings->add(
                    new auth_ldap_admin_setting_special_lowercase_configtext(
                            "auth_otmultildap/memberattribute___{$fullconfig->choose_configuration}",
                            get_string('auth_otmultildap_memberattribute_key', 'auth_otmultildap'),
                            get_string('auth_otmultildap_memberattribute', 'auth_otmultildap'), '', PARAM_RAW));

                // Member attribute uses dn.
            $settings->add(
                    new admin_setting_configtext("auth_otmultildap/memberattribute_isdn___{$fullconfig->choose_configuration}",
                            get_string('auth_otmultildap_memberattribute_isdn_key', 'auth_otmultildap'),
                            get_string('auth_otmultildap_memberattribute_isdn', 'auth_otmultildap'), '', PARAM_RAW));

            // Object class.
            $settings->add(
                    new admin_setting_configtext("auth_otmultildap/objectclass___{$fullconfig->choose_configuration}",
                            get_string('auth_otmultildap_objectclass_key', 'auth_otmultildap'),
                            get_string('auth_otmultildap_objectclass', 'auth_otmultildap'), '', PARAM_RAW_TRIMMED));

                // Force Password change Header.
            $settings->add(
                    new admin_setting_heading('auth_otmultildap/ldapforcepasswordchange',
                            new lang_string('forcechangepassword', 'auth'), ''));

                // Force Password change.
            $settings->add(
                    new admin_setting_configselect("auth_otmultildap/forcechangepassword___{$fullconfig->choose_configuration}",
                            new lang_string('forcechangepassword', 'auth'),
                            new lang_string('forcechangepasswordfirst_help', 'auth'), 0 , $yesno));

            // Standard Password Change.
            $settings->add(
                    new admin_setting_configselect("auth_otmultildap/stdchangepassword___{$fullconfig->choose_configuration}",
                            new lang_string('stdchangepassword', 'auth'),
                            new lang_string('stdchangepassword_expl', 'auth') . ' ' .
                            get_string('stdchangepassword_explldap', 'auth'), 0 , $yesno));

            // Password Type.
            $passtype = array();
            $passtype['plaintext'] = get_string('plaintext', 'auth');
            $passtype['md5']       = get_string('md5', 'auth');
            $passtype['sha1']      = get_string('sha1', 'auth');

            $settings->add(new admin_setting_configselect("auth_otmultildap/passtype___{$fullconfig->choose_configuration}",
                            new lang_string('auth_otmultildap_passtype_key', 'auth_otmultildap'),
                            new lang_string('auth_otmultildap_passtype', 'auth_otmultildap'), 'plaintext', $passtype));

                // Password change URL.
            $settings->add(
                    new admin_setting_configtext("auth_otmultildap/changepasswordurl___{$fullconfig->choose_configuration}",
                            get_string('auth_otmultildap_changepasswordurl_key', 'auth_otmultildap'),
                            get_string('changepasswordhelp', 'auth'), '', PARAM_URL));

            // Password Expiration Header.
            $settings->add(new admin_setting_heading('auth_otmultildap/passwordexpire',
                    new lang_string('auth_otmultildap_passwdexpire_settings', 'auth_otmultildap'), ''));

            // Password Expiration.

            // Create the description lang_string object.
            $strno = get_string('no');
            $strldapserver = get_string('pluginname', 'auth_otmultildap');
            $langobject = new stdClass();
            $langobject->no = $strno;
            $langobject->ldapserver = $strldapserver;
            $description = new lang_string('auth_otmultildap_expiration_desc', 'auth_otmultildap', $langobject);

            // Now create the options.
            $expiration = array();
            $expiration['0'] = $strno;
            $expiration['1'] = $strldapserver;

                // Add the setting.
            $settings->add(
                    new admin_setting_configselect("auth_otmultildap/expiration___{$fullconfig->choose_configuration}",
                            new lang_string('auth_otmultildap_expiration_key', 'auth_otmultildap'), $description, 0 , $expiration));

            // Password Expiration warning.
            $settings->add(
                    new admin_setting_configtext("auth_otmultildap/expiration_warning___{$fullconfig->choose_configuration}",
                            get_string('auth_otmultildap_expiration_warning_key', 'auth_otmultildap'),
                            get_string('auth_otmultildap_expiration_warning_desc', 'auth_otmultildap'), '', PARAM_RAW));

                // Password Expiration attribute.
            $settings->add(
                    new auth_ldap_admin_setting_special_lowercase_configtext(
                            "auth_otmultildap/expireattr___{$fullconfig->choose_configuration}",
                            get_string('auth_otmultildap_expireattr_key', 'auth_otmultildap'),
                            get_string('auth_otmultildap_expireattr_desc', 'auth_otmultildap'), '', PARAM_RAW));

                // Grace Logins.
            $settings->add(
                    new admin_setting_configselect("auth_otmultildap/gracelogins___{$fullconfig->choose_configuration}",
                            new lang_string('auth_otmultildap_gracelogins_key', 'auth_otmultildap'),
                            new lang_string('auth_otmultildap_gracelogins_desc', 'auth_otmultildap'), 0 , $yesno));

                // Grace logins attribute.
            $settings->add(
                    new auth_ldap_admin_setting_special_lowercase_configtext(
                            "auth_otmultildap/graceattr___{$fullconfig->choose_configuration}",
                            get_string('auth_otmultildap_gracelogin_key', 'auth_otmultildap'),
                            get_string('auth_otmultildap_graceattr_desc', 'auth_otmultildap'), '', PARAM_RAW));

            // User Creation.
            $settings->add(new admin_setting_heading('auth_otmultildap/usercreation',
                    new lang_string('auth_user_create', 'auth'), ''));

                // Create users externally.
            $settings->add(
                    new admin_setting_configselect("auth_otmultildap/auth_user_create___{$fullconfig->choose_configuration}",
                            new lang_string('auth_otmultildap_auth_user_create_key', 'auth_otmultildap'),
                            new lang_string('auth_user_creation', 'auth'), 0 , $yesno));

            // Context for new users.
            $settings->add(new admin_setting_configtext("auth_otmultildap/create_context___{$fullconfig->choose_configuration}",
                            get_string('auth_otmultildap_create_context_key', 'auth_otmultildap'),
                            get_string('auth_otmultildap_create_context', 'auth_otmultildap'), '', PARAM_RAW_TRIMMED));

            // System roles mapping header.
            $settings->add(new admin_setting_heading('auth_otmultildap/systemrolemapping',
                                            new lang_string('systemrolemapping', 'auth_otmultildap'), ''));

            // Create system role mapping field for each assignable system role.
            $roles = get_ldap_assignable_role_names();
            foreach ($roles as $role) {
                // Before we can add this setting we need to check a few things.
                // A) It does not exceed 100 characters otherwise it will break the DB as the 'name' field
                //    in the 'config_plugins' table is a varchar(100).
                // B) The setting name does not contain hyphens. If it does then it will fail the check
                //    in parse_setting_name() and everything will explode. Role short names are validated
                //    against PARAM_ALPHANUMEXT which is similar to the regex used in parse_setting_name()
                //    except it also allows hyphens.
                // Instead of shortening the name and removing/replacing the hyphens we are showing a warning.
                // If we were to manipulate the setting name by removing the hyphens we may get conflicts, eg
                // 'thisisashortname' and 'this-is-a-short-name'. The same applies for shortening the setting name.
                if (core_text::strlen($role['settingname']) > 100 || !preg_match('/^[a-zA-Z0-9_]+$/', $role['settingname'])) {
                    $url = new moodle_url('/admin/roles/define.php', array('action' => 'edit', 'roleid' => $role['id']));
                    $a = (object)['rolename' => $role['localname'], 'shortname' => $role['shortname'], 'charlimit' => 93,
                        'link' => $url->out()];
                    $settings->add(new admin_setting_heading('auth_otmultildap/role_not_mapped_' . sha1($role['settingname']), '',
                        get_string('cannotmaprole', 'auth_otmultildap', $a)));
                } else {
                    $settings->add(new admin_setting_configtext("auth_otmultildap/" . $role['settingname'] . "___{$fullconfig->choose_configuration}",
                        get_string('auth_otmultildap_rolecontext', 'auth_otmultildap', $role),
                        get_string('auth_otmultildap_rolecontext_help', 'auth_otmultildap', $role), '', PARAM_RAW_TRIMMED));
                }
            }

            // User Account Sync.
            $settings->add(new admin_setting_heading('auth_otmultildap/syncusers',
                    new lang_string('auth_sync_script', 'auth'), ''));

            // Remove external user.
            $deleteopt = array();
            $deleteopt[AUTH_REMOVEUSER_KEEP] = get_string('auth_remove_keep', 'auth');
            $deleteopt[AUTH_REMOVEUSER_SUSPEND] = get_string('auth_remove_suspend', 'auth');
            $deleteopt[AUTH_REMOVEUSER_FULLDELETE] = get_string('auth_remove_delete', 'auth');
            $settings->add(
                    new admin_setting_configselect("auth_otmultildap/removeuser___{$fullconfig->choose_configuration}",
                            new lang_string('auth_remove_user_key', 'auth'), new lang_string('auth_remove_user', 'auth'),
                            AUTH_REMOVEUSER_KEEP, $deleteopt));

                // Sync Suspension.
            $settings->add(
                    new admin_setting_configselect("auth_otmultildap/sync_suspended___{$fullconfig->choose_configuration}",
                            new lang_string('auth_sync_suspended_key', 'auth'), new lang_string('auth_sync_suspended', 'auth'), 0,
                            $yesno));

                // NTLM SSO Header.
            $settings->add(
                    new admin_setting_heading("auth_otmultildap/ntlm___{$fullconfig->choose_configuration}",
                            new lang_string('auth_ntlmsso', 'auth_otmultildap'), ''));

                // Enable NTLM.
            $settings->add(
                    new admin_setting_configselect("auth_otmultildap/ntlmsso_enabled___{$fullconfig->choose_configuration}",
                            new lang_string('auth_ntlmsso_enabled_key', 'auth_otmultildap'),
                            new lang_string('auth_ntlmsso_enabled', 'auth_otmultildap'), 0, $yesno));

                // Subnet.
            $settings->add(
                    new admin_setting_configtext("auth_otmultildap/ntlmsso_subnet___{$fullconfig->choose_configuration}",
                            get_string('auth_ntlmsso_subnet_key', 'auth_otmultildap'),
                            get_string('auth_ntlmsso_subnet', 'auth_otmultildap'), '', PARAM_RAW_TRIMMED));

            // NTLM Fast Path.
            $fastpathoptions = array();
            $fastpathoptions[AUTH_NTLM_FASTPATH_YESFORM] = get_string('auth_ntlmsso_ie_fastpath_yesform', 'auth_otmultildap');
            $fastpathoptions[AUTH_NTLM_FASTPATH_YESATTEMPT] = get_string('auth_ntlmsso_ie_fastpath_yesattempt', 'auth_otmultildap');
            $fastpathoptions[AUTH_NTLM_FASTPATH_ATTEMPT] = get_string('auth_ntlmsso_ie_fastpath_attempt', 'auth_otmultildap');

            $settings->add(new admin_setting_configselect("auth_otmultildap/ntlmsso_ie_fastpath___{$fullconfig->choose_configuration}",
                    new lang_string('auth_ntlmsso_ie_fastpath_key', 'auth_otmultildap'),
                    new lang_string('auth_ntlmsso_ie_fastpath', 'auth_otmultildap'),
                    AUTH_NTLM_FASTPATH_ATTEMPT, $fastpathoptions));

            // Authentication type.
            $types = array();
            $types['ntlm'] = 'NTLM';
            $types['kerberos'] = 'Kerberos';
            $settings->add(
                    new admin_setting_configselect("auth_otmultildap/ntlmsso_type___{$fullconfig->choose_configuration}",
                            new lang_string('auth_ntlmsso_type_key', 'auth_otmultildap'),
                            new lang_string('auth_ntlmsso_type', 'auth_otmultildap'), 'ntlm', $types));

                // Remote Username format.
            $settings->add(
                    new auth_ldap_admin_setting_special_ntlm_configtext(
                            "auth_otmultildap/ntlmsso_remoteuserformat___{$fullconfig->choose_configuration}",
                            get_string('auth_ntlmsso_remoteuserformat_key', 'auth_otmultildap'),
                            get_string('auth_ntlmsso_remoteuserformat', 'auth_otmultildap'), '', PARAM_RAW_TRIMMED));

            // Display locking / mapping of profile fields.
            $help  = get_string('auth_otmultildapextrafields', 'auth_otmultildap');
            $help .= get_string('auth_updatelocal_expl', 'auth');
            $help .= get_string('auth_fieldlock_expl', 'auth');
            $help .= get_string('auth_updateremote_expl', 'auth');
            $help .= '<hr />';
            $help .= get_string('auth_updateremote_ldap', 'auth');
            otmultildap_display_auth_lock_options($settings, $authplugin->authtype, $authplugin->get_user_profile_fields($fullconfig->choose_configuration),
                    $help, true, true, $authplugin->get_custom_user_profile_fields($fullconfig->choose_configuration));
        }
    }
}
