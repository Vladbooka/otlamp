<?php

define ('LOGCODE_LAUNCHED', "script_launched");
define ('LOGCODE_COMPLETED', "script_completed");
define ('LOGCODE_COMPLETED_WITH_ERROR', "script_completed_with_error");
define ('LOGCODE_UNRECOGNIZED_PARAMETERS', "unrecodnized_parameters");
define ('LOGCODE_INSTANCE_NOT_FOUND', "instance_not_found");
define ('LOGCODE_HOST_NOT_FOUND', "host_not_found");
define ('LOGCODE_FLAG_OBSOLETE', "flag_obsolete");
define ('LOGCODE_FLAG_CONFLICT', "flag_conflict");
define ('LOGCODE_SCRIPT_RESULT', "script_result");



function otlamp_notice($script, $code, $text, $instance='-', $host='-', $errorcode=0)
{
    if( defined('IS_CRON') )
    {
        otlamp_log('cron', $script, $code, $text, $instance='-', $host='-', $errorcode=0);
    } else
    {
        otlamp_log('manual', $script, $code, $text, $instance='-', $host='-', $errorcode=0);
    }
}

function otlamp_log($type, $script, $code, $text, $instance='-', $host='-', $errorcode=0)
{
    if( in_array($type, ['error', 'cron', 'manual', 'backup', 'lastcron.result', 'lastnightly.result']) )
    {
        $logpath = '/var/log/opt/otlamp';
        if( ! file_exists($logpath) )
        {
            otlamp_shell_exec('mkdir -p '.$logpath);
            otlamp_shell_exec('chmod 755 '.$logpath);
            if( $instance !== '-' )
            {
                otlamp_shell_exec('chown -R '.$instance.":".$instance." ".$logpath);
            }
        }
        $errorlogitem = [
            'date' => date('Y-m-d H:i:s'),
            'script' => $script,
            'instance' => $instance,
            'host' => $host,
            'code' => $code,
            'text' => str_replace('\t','\\_t',str_replace('\c','\\_c',$text))
        ];

        $delimiter = "\t";
        $lineseparator = "\r\n";
        $fileputcontentflags = '>';
        if( substr($type, -6) == 'result' )
        {
            $logpath = '/var/log/opt/otlamp/'.$instance.'/'.$host.'/';
            if( ! file_exists($logpath) )
            {
                otlamp_shell_exec('mkdir -p '.$logpath, $instance);
                otlamp_shell_exec('chmod 755 '.$logpath, $instance);
            }
            $delimiter = "\r\n";
            $lineseparator = "\r\n\r\n--------------------";
            $fileputcontentflags = '';
        }
    
        $cmd = 'echo "'.escapeshellarg(implode($delimiter, $errorlogitem).$lineseparator).'" >'.$fileputcontentflags.' '.$logpath.'/otlamp.'.$type.'.log';
        otlamp_shell_exec($cmd, $instance);
       
        if( $type == 'error' )
        {
            otlamp_notice(
                $script, 
                LOGCODE_COMPLETED_WITH_ERROR, 
                "Во время работы скрипта произошла ошибка. Подробности в журнале ошибок otlamp.error.log", 
                $instance, 
                $host
            );
            fwrite(STDERR, $text.PHP_EOL); 
            die($errorcode);
        }
    }
}

function otlamp_create_flag($flag, $instance=null, $host=null, $value=null)
{
    if( is_null($value) )
    {
        $value = time();
    }
    if( !is_null($host) && !is_null($instance))
    {
        $flagpath = '/var/run/otlamp/'.$instance.'/'.$host;
    } else 
    if( !is_null($instance) )
    {
        $flagpath = '/var/run/otlamp/'.$instance;
    } else
    {
        $flagpath = '/var/run/otlamp';
    }
    // Создание метки
    if( ! is_dir($flagpath))
    {
        otlamp_shell_exec('mkdir -p '.$flagpath);
        otlamp_shell_exec('chmod 755 '.$flagpath);
        otlamp_shell_exec('chown -R '.$instance.":".$instance." ".$flagpath);
    }

    otlamp_shell_exec('echo "'.$value.'" > '.$flagpath.'/'.$flag, $instance);
}

function otlamp_shell_exec($cmd, $instance = null)
{
    $user = preg_replace('/\r\n|\r|\n/u', '', shell_exec("whoami"));
    if( is_null($instance) || $user == $instance || $instance == '-')
    {
//         echo $cmd."\r\n";
        return shell_exec($cmd);
    } else
    {
//         echo 'su -c "'.str_replace('"', '\"', $cmd).'" '.$instance."\r\n";
        return shell_exec('su -c "'.str_replace('"', '\"', $cmd).'" '.$instance);
    }
}

function otlamp_flag_conflict($instance, $host, $flagname, $lifetime, $script='-')
{
    // Путь до метки
    $flagpath = '/var/run/otlamp/'.$instance.'/'.$host.'/'.$flagname;
    if( file_exists($flagpath) )
    {
        $flagdate = file_get_contents($flagpath);
        // Имеется метка, проверим ее актуальность
        if( (time() - (int)$flagdate)  > $lifetime )
        {
            // Метка существует больше отведенного ей времени, конфликта нет, но надо залогировать ошибку
            otlamp_log(
                'error', 
                $script, 
                LOGCODE_FLAG_OBSOLETE, 
                'Метка "'.$flagname.'" устарела', 
                $instance, 
                $host
            );
            return false;
        } else
        {
            // Метка жива, говорим что есть конфликт (продолжать работы не рекомендуется)
            otlamp_notice(
                $script, 
                LOGCODE_FLAG_CONFLICT, 
                'Метка "'.$flagname.'" еще жива. Нельзя продолжать операцию.', 
                $instance, 
                $host
            );
            return true;
        }
    }
    // метки нет - нет конфликта
    return false;
}

function otlamp_create_metric($instance, $host, $type, $name, $value)
{
    $savemetricpath = '/var/log/opt/otlamp/'.$instance.'/'.$host.'/stat/'.$type;
    // Создание директории для хранения итогов (если еще не существует)
    if( ! file_exists($savemetricpath) )
    {
        otlamp_shell_exec('mkdir -p '.$savemetricpath, $instance);
        otlamp_shell_exec('chmod 755 '.$savemetricpath, $instance);
    }
    otlamp_shell_exec('echo "'.$value.'" > '.$savemetricpath.'/'.$name, $instance);
}


/**
 * Returns cli script parameters.
 * @param array $longoptions array of --style options ex:('verbose'=>false)
 * @param array $shortmapping array describing mapping of short to long style options ex:('h'=>'help', 'v'=>'verbose')
 * @return array array of arrays, options, unrecognised as optionlongname=>value
 */
function otlamp_cli_get_params(array $longoptions, array $shortmapping=null) {
    $shortmapping = (array)$shortmapping;
    $options      = array();
    $unrecognized = array();

    if (empty($_SERVER['argv'])) {
        // bad luck, we can continue in interactive mode ;-)
        return array($options, $unrecognized);
    }
    $rawoptions = $_SERVER['argv'];

    //remove anything after '--', options can not be there
    if (($key = array_search('--', $rawoptions)) !== false) {
        $rawoptions = array_slice($rawoptions, 0, $key);
    }

    //remove script
    unset($rawoptions[0]);
    foreach ($rawoptions as $raw) {
        if (substr($raw, 0, 2) === '--') {
            $value = substr($raw, 2);
            $parts = explode('=', $value);
            if (count($parts) == 1) {
                $key   = reset($parts);
                $value = true;
            } else {
                $key = array_shift($parts);
                $value = implode('=', $parts);
            }
            if (array_key_exists($key, $longoptions)) {
                $options[$key] = $value;
            } else {
                $unrecognized[] = $raw;
            }

        } else if (substr($raw, 0, 1) === '-') {
            $value = substr($raw, 1);
            $parts = explode('=', $value);
            if (count($parts) == 1) {
                $key   = reset($parts);
                $value = true;
            } else {
                $key = array_shift($parts);
                $value = implode('=', $parts);
            }
            if (array_key_exists($key, $shortmapping)) {
                $options[$shortmapping[$key]] = $value;
            } else {
                $unrecognized[] = $raw;
            }
        } else {
            $unrecognized[] = $raw;
            continue;
        }
    }
    //apply defaults
    foreach ($longoptions as $key=>$default) {
        if (!array_key_exists($key, $options)) {
            $options[$key] = $default;
        }
    }
    // finished
    return array($options, $unrecognized);
}

