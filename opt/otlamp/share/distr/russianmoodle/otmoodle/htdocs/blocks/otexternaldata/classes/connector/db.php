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
 * Внешние данные
 *
 * @package    block_otexternaldata
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_otexternaldata\connector;

use ADOConnection;
use context;
use moodle_exception;

require_once($CFG->libdir.'/adodb/adodb.inc.php');

trait db {
    
    protected $dbcon_properties = ['dbtype', 'host', 'user', 'password', 'dbname', 'setupsql', 'sql'];
    
    protected $dbcon_dbtypes = ["access", "ado_access", "ado", "ado_mssql", "borland_ibase", "csv", "db2",
        "fbsql", "firebird", "ibase", "informix72", "informix", "mssql", "mssql_n", "mssqlnative",
        "mysql", "mysqli", "mysqlt", "oci805", "oci8", "oci8po", "odbc", "odbc_mssql", "odbc_oracle",
        "oracle", "pdo", "postgres64", "postgres7", "postgres", "proxy", "sqlanywhere", "sybase", "vfp"];
    
    protected function dbcon_extend_form_definition(&$mform)
    {
        $dbtypes = array_combine($this->dbcon_dbtypes, $this->dbcon_dbtypes);
        $mform->addElement('select', 'dbcon_dbtype', get_string('dbcon_dbtype', 'block_otexternaldata'), $dbtypes);
        
        $mform->addElement('text', 'dbcon_host', get_string('dbcon_host', 'block_otexternaldata'));
        $mform->setType('dbcon_host', PARAM_RAW);
        
        $mform->addElement('text', 'dbcon_user', get_string('dbcon_user', 'block_otexternaldata'));
        $mform->setType('dbcon_user', PARAM_RAW);
        
        $mform->addElement('password', 'dbcon_password', get_string('dbcon_password', 'block_otexternaldata'));
        $mform->setType('dbcon_password', PARAM_RAW);
        
        $mform->addElement('text', 'dbcon_dbname', get_string('dbcon_dbname', 'block_otexternaldata'));
        $mform->setType('dbcon_dbname', PARAM_RAW);
        
        $mform->addElement('text', 'dbcon_setupsql', get_string('dbcon_setupsql', 'block_otexternaldata'));
        $mform->addHelpButton('dbcon_setupsql', 'dbcon_setupsql', 'block_otexternaldata');
        $mform->setType('dbcon_setupsql', PARAM_RAW);
        
        $mform->addElement('textarea', 'dbcon_sql', get_string('dbcon_sql', 'block_otexternaldata'),
            ['class' => 'otexternaldata_textarea']);
        $mform->setType('dbcon_sql', PARAM_RAW);
        
        if (method_exists($this, 'sql_substitutions') && method_exists($this, 'get_substitutions_description'))
        {
            $substitutions = $this->sql_substitutions();
            $a = $this->get_substitutions_description($substitutions);
            $mform->addElement('static', 'dbcon_sql_desc', '', get_string('dbcon_sql_desc', 'block_otexternaldata', $a));
        }
    }
    
    protected function dbcon_compose_config($formdata)
    {
        $config = [];
        foreach($this->dbcon_properties as $property)
        {
            $property = 'dbcon_' . $property;
            if (array_key_exists($property, $formdata))
            {
            
                if ($property == 'type' && !in_array($formdata[$property], $this->dbcon_dbtypes))
                {
                    throw new \Exception('Unknown database type ['.$formdata[$property].']');
                }
                
                $config[$property] = $formdata[$property];
            } else
            {
                throw new \Exception('Missing required field ['.$property.']');
            }
        }
        return $config;
    }
    
    public static function adodb_error_handler($dbms, $fn, $errno, $errmsg, $p1, $p2, $thisConnection) {
        global $ADODB_EXCEPTION;
        if (is_string($ADODB_EXCEPTION) && class_exists($ADODB_EXCEPTION)) {
            $errfn = $ADODB_EXCEPTION;
        } else  {
            $errfn = '\\block_otexternaldata\\connector\\db_exception';
        }
        throw new $errfn($dbms, $fn, $errno, $errmsg, $p1, $p2, $thisConnection);
    }
    
    /**
     * Connect to external database.
     *
     * @return ADOConnection
     * @throws moodle_exception
     */
    protected function dbcon_init($dbtype, $host, $user, $password, $dbname, $setupsql) {
        global $CFG;
        
        if (!defined('ADODB_ERROR_HANDLER_TYPE')) define('ADODB_ERROR_HANDLER_TYPE',E_USER_ERROR);
        if (!defined('ADODB_ERROR_HANDLER')) define('ADODB_ERROR_HANDLER','\\block_otexternaldata\\connector\\db::adodb_error_handler');
        
        if (!in_array($dbtype, $this->dbcon_dbtypes))
        {
            throw new moodle_exception('Wrong db type');
        }
        
        // Connect to the external database (forcing new connection).
        $db = ADONewConnection($dbtype);
        $db->Connect($host, $user, $password, $dbname, true);
        $db->SetFetchMode(ADODB_FETCH_ASSOC);
        
        if (!empty($setupsql))
        {
            $db->Execute($setupsql);
        }
        
        return $db;
    }
    
    protected function dbcon_init_via_config($config)
    {
        $initprops = $this->dbcon_properties;
        unset($initprops['sql']);
        
        foreach($initprops as $property)
        {
            if (!array_key_exists('dbcon_'.$property, $config))
            {
                throw new \Exception('Missing required field \''.$property.'\'');
            }
            
            ${$property} = $config['dbcon_'.$property];
        }
        
        return $this->dbcon_init($dbtype, $host, $user, $password, $dbname, $setupsql);
    }
    
    protected function dbcon_extract_sql_from_config($config)
    {
        if (!array_key_exists('dbcon_sql', $config))
        {
            throw new \Exception('Missing required field \'sql\'');
        }
        return $config['dbcon_sql'];
    }
    
    protected function dbcon_get_records_via_config($extbd, $config)
    {
        $sql = $this->dbcon_extract_sql_from_config($config);
        return $this->dbcon_get_records($extbd, $sql);
    }
    
    
    protected function dbcon_get_records($extdb, $sql)
    {
        $sql = trim($sql);
        
        // Simple test to avoid evil stuff in the SQL.
        $regex = '/\b(ALTER|CREATE|DELETE|DROP|GRANT|INSERT|INTO|TRUNCATE|UPDATE|SET|VACUUM|REINDEX|DISCARD|LOCK)\b/i';
        if (preg_match($regex, $sql))
        {
            throw new \Exception(get_string('notallowedwords', 'block_otexternaldata'));
        }
        if (strpos($sql, ';') !== false)
        {
            throw new \Exception(get_string('nosemicolon', 'block_otexternaldata'));
        }
        
        $results = [];
        $queryresult = $extdb->SelectLimit($sql);
        while ($r = $queryresult->fetchRow())
        {
            $results[] = $r;
        }
        
        $extdb->Close();
        
        return $results;
    }
    
}