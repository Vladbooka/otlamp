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
 * This script works with otserial data
 *
 * @package    core
 * @subpackage cli
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

use local_opentechnology\otapi;

require(__DIR__.'/../../config.php');
require_once($CFG->libdir.'/clilib.php');


// Now get cli options.
list($options, $unrecognized) = cli_get_params(
    array(
        'help' => false,
        'delete' => false,
        'verbose' => false,
        'plugin' => null,
        'request' => false
    ),
    array(
        'h' => 'help',
        'd' => 'delete',
        'v' => 'verbose',
        'p' => 'plugin',
        'r' => 'request'
    )
);

if ($unrecognized)
{
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if ($options['help'])
{
    $help = "
    Displays current serial numbers of plugins developed by opentechnology.
        
    Options:
    -h, --help            Print out this help.
    -v, --verbose         Describes the script execution process in detail.
    -p, --plugin          Plugin component like mod_otcourselogic to specify plugin in query
    -r, --request         Request new otserial for specified plugin (plugin required)
        
    Example:
    \$sudo -u www-data /usr/bin/php admin/cli/otserial.php --verbose=true --plugin=mod_otcourselogic
";
    
    cli_writeln($help);
    exit(0);
}

$VERBOSE = !empty($options['verbose']);
$PLUGIN = (in_array($options['plugin'], ['core', 'moodle']) ? 'local_opentechnology' : $options['plugin']);
$CFGPLUGIN = ($PLUGIN == 'local_opentechnology' ? 'core' : $PLUGIN);
$OUTPUT_LEVEL = 0;

function cli_otserial_init_otapi()
{
    global $PLUGIN;
    
    $otapi = null;
    
    
    if (is_null($PLUGIN))
    {
        throw new Exception('Plugin (component) required.');
    }
    
    $classname = '\\'.$PLUGIN.'\\otserial';
    if (class_exists($classname))
    {
        $otapi = new $classname();
        
    } else {
        throw new Exception('Couldn\'t construct otserial object for '.$PLUGIN.'. Class doesn\'t exist.');
    }
    
    if (is_null($otapi))
    {
        throw new Exception('Couldn\'t construct otserial object for '.$PLUGIN);
    }
    
    if (!is_a($otapi, '\\local_opentechnology\\otserial_base'))
    {
        throw new Exception('Constructed object for '.$PLUGIN. ' is not child for \\local_opentechnology\\otserial_base');
    }
    
    return $otapi;
}

/**
 * Получение данных (серийник, ключ)
 *
 * @param bool $verbose - отображать подробности исполнения скрипта
 * @param string $plugin - выполнить алгоритм для указанного плагина или всех, если null
 *
 * @return [] - массив плагинов и данных по ним
 */
function cli_otserial_get_otapi_data()
{
    global $VERBOSE, $PLUGIN, $CFGPLUGIN, $OUTPUT_LEVEL;
    
    $result = [];
    
    if (is_null($PLUGIN))
    {
        $result = otapi::get_all_otserials();
        
    } else{
        
        $otapi = cli_otserial_call_wrapped_func('cli_otserial_init_otapi');
        
        $otserial = $otapi->get_config_otserial();
        $otkey = $otapi->get_config_otkey();
        
        if (!empty($otserial) || !empty($otkey))
        {
            $result[$CFGPLUGIN] = [
                'otserial' => $otapi->get_config_otserial(),
                'otkey' => $otapi->get_config_otkey()
            ];
        }
    }
    
    if ($VERBOSE)
    {
        cli_writeln(' '.str_pad('', $OUTPUT_LEVEL+2, '-').' '.count($result).' otserials found.');
    }
    
    return $result;
}

/**
 * Запрос нового серийника для плагина
 *
 * @param bool $verbose - отображать подробности исполнения скрипта
 * @param string $plugin - выполнить алгоритм для указанного плагина
 *
 * @throws Exception
 */
function cli_otserial_request_new_otserial()
{
    global $VERBOSE, $PLUGIN, $CFGPLUGIN, $OUTPUT_LEVEL;
    
    if (is_null($PLUGIN))
    {
        throw new Exception('Plugin (component) required.');
    }
    
    $otserials = cli_otserial_call_wrapped_func('cli_otserial_get_otapi_data');
    
    if (array_key_exists($CFGPLUGIN, $otserials))
    {
        throw new Exception('Серийный ключ для этого плагина уже получен.');
    }
    
    $otapi = cli_otserial_call_wrapped_func('cli_otserial_init_otapi');
    $result = $otapi->issue_serial_and_get_data();
    if (!isset($result['response']))
    {
        throw new Exception(var_export($result, true));
    }
    
}


/**
 * Удаление конфигов, содержащих информацию о серийниках
 *
 * @param bool $verbose - отображать подробности исполнения скрипта
 * @param string $plugin - выполнить алгоритм для указанного плагина или всех, если null
 */
function cli_otserial_delete_otapi_data()
{
    global $VERBOSE, $PLUGIN;
    
    if (is_null($PLUGIN))
    {
        otapi::delete_all_otapi_data();
        
    } else {
        
        $otapi = cli_otserial_call_wrapped_func('cli_otserial_init_otapi');
        $otapi->delete_otapi_data();
    }
}

/**
 * Отображение данных о серийниках
 *
 * @param array $otserials
 * @param string $plugin - выполнить алгоритм для указанного плагина или всех, если null
 */
function cli_otserial_display($otserials)
{
    global $CFGPLUGIN;
    if (count($otserials) > 0)
    {
//         $longestkey = max(array_map('strlen', array_keys($otserials)));
        foreach($otserials as $component => $otserialdata)
        {
            //         $pluginname = str_pad($plugin . ':', $longestkey + 1, " ");
            cli_writeln('');
            cli_writeln('plugin:   ' . $component);
            cli_writeln('otserial: ' . $otserialdata['otserial']);
            cli_writeln('otkey:    ' . $otserialdata['otkey']);
        }
        
    } else {
        cli_writeln('No otserials to ' . (is_null($CFGPLUGIN) ? 'all plugins' : $CFGPLUGIN) . ' found. Nothing to display.');
    }
}

/**
 * Обертка для вызова функций со стандартизированным выводом результатов
 *
 * @param callable $func - функция
 * @param array $params - параметры функции
 *
 * @throws Exception
 * @return mixed - Результат исполнения функции
 */
function cli_otserial_call_wrapped_func($func, $params=[])
{
    global $VERBOSE, $PLUGIN, $OUTPUT_LEVEL;
    
    
    try {
        
        $subject = (is_null($PLUGIN) ? 'all plugins' : $PLUGIN );
        switch($func)
        {
            case 'cli_otserial_get_otapi_data':
                $action = 'get ' . $subject . ' otapi data';
                $errcode = 101;
                break;
            case 'cli_otserial_delete_otapi_data':
                $action = 'delete ' . $subject . ' otapi data';
                $errcode = 102;
                break;
            case 'cli_otserial_request_new_otserial':
                $action = 'request ' . $subject . ' otserial';
                $errcode = 103;
                if (is_null($PLUGIN))
                {
                    throw new Exception('Plugin (component) required.');
                }
                break;
            case 'cli_otserial_init_otapi':
                $action = 'construct ' . $subject . ' object';
                $errcode = 104;
                if (is_null($PLUGIN))
                {
                    throw new Exception('Plugin (component) required.');
                }
                break;
            default:
                $action = 'unknown action directed to '.$subject;
                $errcode = 100;
                break;
                
        }
        
        $OUTPUT_LEVEL+=2;
        
        if ($VERBOSE)
        {
            cli_writeln(' '.str_pad('', $OUTPUT_LEVEL, '-').' Trying to ' . $action . '.');
        }
        
        $result = call_user_func_array($func, $params);
        
        if($VERBOSE)
        {
            cli_writeln(' '.str_pad('', $OUTPUT_LEVEL, '-').' '.ucfirst($action).' succeed.');
        }
        
        $OUTPUT_LEVEL-=2;
        
        return $result;
    
    } catch(Exception $ex) {
        
        $OUTPUT_LEVEL-=2;
        cli_error(ucfirst($action).' failed. ' . PHP_EOL . $ex->getMessage(), $errcode);
        //. PHP_EOL . $ex->getTraceAsString()
    }
    
}









if ($VERBOSE)
{
    cli_logo(3);
    cli_writeln('');
}

if ($options['delete'])
{
    if ($VERBOSE)
    {
        // получим данные для понимания количества данных до и после операции
        cli_otserial_call_wrapped_func('cli_otserial_get_otapi_data');
    }
    cli_otserial_call_wrapped_func('cli_otserial_delete_otapi_data');
}

if ($options['request'])
{
    cli_otserial_call_wrapped_func('cli_otserial_request_new_otserial');
}

$otserials = cli_otserial_call_wrapped_func('cli_otserial_get_otapi_data');

if ($VERBOSE)
{
    cli_writeln('');
}

cli_otserial_display($otserials);

exit(0);
