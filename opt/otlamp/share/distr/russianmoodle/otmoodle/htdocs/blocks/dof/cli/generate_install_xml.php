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
 * @package    block
 * @subpackage dof
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require(__DIR__.'/../../../config.php');
require_once $CFG->libdir.'/clilib.php';
require_once $CFG->libdir.'/xmlize.php';

list($options, $unrecognized) = cli_get_params(
    ['help' => false],
    ['h' => 'help']
);

if ($unrecognized)
{
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized), 2);
}

if ($options['help'])
{
    $help = "
    Генерация xml-разметки, содержащей информацию обо всех таблицах БД, описанных деканатом.
    За основу берется xml-документ, описанный в самом плагине (/blocks/dof/db/install.xml).
    Туда добавляются таблицы, найденные в xml-файлах хранилищ по шаблону (/blocks/dof/storages/*/db/install.xml).
    Результат выводится в поток. Аргументы не требуются.
    
    Будьте внимательны. Не переводите вывод сразу в деканатовский файл xml (/blocks/dof/db/install.xml).
    Так он затрется и не сможет быть взят за основу. Выйдет некорректный результат.
    
    Options:
    -h, --help            	Print out this help
    
    Example:
    \$ php generate_install_xml.php > install.xml
";

    cli_writeln($help);
    exit(0);
}



function compose_xml_node($node, $nodesname=null, $level=0)
{
    $properties = [];
    if (!empty($node['@']))
    {
        array_walk($node['@'], function($v, $k) use (&$properties) {
            $properties[] = $k.'="'.htmlentities($v).'"';
        });
    }
    $properties = implode(' ', $properties);
    
    if (array_key_exists('#', $node))
    {
        if (empty($node['#']))
        {
            return str_pad('', $level*4, ' ').'<'.$nodesname.' '.$properties.' />'.PHP_EOL;
        } else // && is_array($node['#'])
        {
            $xml = '';
            if (!is_null($nodesname))
            {
                $xml .= str_pad('', $level*4, ' ').'<'.$nodesname.(!empty($properties) ? ' '.$properties : '').'>' . PHP_EOL;
            }
            foreach($node['#'] as $subnodesname => $subnodes)
            {
                foreach($subnodes as $subnode)
                {
                    $xml .= compose_xml_node($subnode, $subnodesname, ($level+1));
                }
            }
            if (!is_null($nodesname))
            {
                $xml .= str_pad('', $level*4, ' ').'</'.$nodesname.'>' . PHP_EOL;
            }
            return $xml;
        }
    } else
    {
        $xml = '';
        foreach($node as $subnode)
        {
            $xml .= compose_xml_node($subnode, $nodesname, $level);
        }
        return $xml;
    }
}

header('Content-Type: application/xml; charset=utf-8');

$doffile = $CFG->dirroot . '/blocks/dof/db/install.xml';
if (!file_exists($doffile))
{
    cli_error('ERROR: '.$doffile.' not found', 101);
}

$dofxml = file_get_contents($doffile);
if ($dofxml === false)
{
    cli_error('ERROR: Couldn\'t read file '.$doffile, 102);
}

try {
    $dofinstall = xmlize($dofxml, 0, 'UTF-8', true);
} catch(Exception $ex)
{
    cli_error('ERROR: File couldn\'t be parsed as xml ('.$doffile.') ' . $ex->getMessage(), 103);
}

$storagefilestemplate = $CFG->dirroot . '/blocks/dof/storages/*/db/install.xml';
$storagefiles = glob($storagefilestemplate);
if ($storagefiles === false)
{
    cli_error('ERROR: Error occured while searching files by template '.$storagefilestemplate, 104);
}
if (!empty($storagefiles) && is_array($storagefiles))
{
    foreach($storagefiles as $storagefile)
    {
        $storagexml = file_get_contents($storagefile);
        if ($dofxml === false)
        {
            cli_error('ERROR: Couldn\'t read file '.$storagefile, 102);
        }
        
        try {
            $storageinstall = xmlize($storagexml, 0, 'UTF-8', true);
        } catch(Exception $ex)
        {
            cli_error('ERROR: File couldn\'t be parsed as xml ('.$storagefile.') ' . $ex->getMessage(), 103);
        }
        
        if (isset($storageinstall['XMLDB']['#']['TABLES'][0]['#']['TABLE']) &&
            is_array($storageinstall['XMLDB']['#']['TABLES'][0]['#']['TABLE']))
        {
            foreach($storageinstall['XMLDB']['#']['TABLES'][0]['#']['TABLE'] as $table)
            {
                $dofinstall['XMLDB']['#']['TABLES'][0]['#']['TABLE'][] = $table;
            }
        } else
        {
            // Странно. Есть файл, но в нём не описано таблиц..
            cli_problem('Tables not found in '.$storagefile);
        }
    }
}

cli_writeln('<?xml version="1.0" encoding="UTF-8" ?>');
cli_write(compose_xml_node($dofinstall,'XMLDB'));

exit(0);