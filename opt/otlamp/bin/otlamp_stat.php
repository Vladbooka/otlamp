#!/usr/bin/php

<?php

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once(__DIR__.'/otlamp_lib.php');

$scriptname = 'otlamp_stat';

// Типы вычислений по метрикам, которые понимает данный скрипт
$calctypes = [
    'sum',
    'count',
    'average',
    'factored',
];

// Данные, подготовленные для подведения итогов
$calc = [];

$cliparams = otlamp_cli_get_params([
    'help' => false,
    'instance' => [],
    'host' => [],
    'mode' => 'manual',
    'metrics' => []
], [
    'h' => 'help',
    'i' => 'instance',
    'host' => 'host',
    'm' => 'mode',
    'metrics' => 'metrics'
]);
$recognized = $cliparams[0];
$recognized = [];

$unrecognized = $cliparams[1];

if ( ! empty($unrecognized) )
{
    $unrecognized = implode(";  ", $unrecognized).';';
    otlamp_log('error', $scriptname, LOGCODE_UNRECOGNIZED_PARAMETERS, 'Не удалось распознать следующие параметры: '.$unrecognized);
}


if( ! empty($recognized['mode']) && $recognized['mode'] == 'cron')
{
    define('IS_CRON', true);
}
// Пишет в лог запись о старте выполнения
otlamp_notice($scriptname, LOGCODE_LAUNCHED, 'Скрипт запущен с параметрами: '.json_encode($recognized));


if ( ! empty($recognized['help']) )
{
    echo "Запускает скрипт подведения итогов статистики

Options:
-h, --help            	Выводит эту подсказку с помощью
-i, --instance          Выполняет операции только по указанному инстансу
-host, --host           Выполняет операцию только по указанному хосту для инстанса
-metrics, --metrics     Подводить итоги только для указанных через запятую метрик

Example:
\$ php otlamp_stat.php -i=w1
";
    exit(0);
}

if( ! empty($recognized['metrics']) )
{
    $recognized['metrics'] = explode(',', $recognized['metrics']);
}
if( ! empty($recognized['instance']) )
{
    if( is_dir('/var/opt/otlamp/'.$recognized['instance']) )
    {
        $instances = [
            $recognized['instance']
        ];
    } else
    {
        otlamp_notice(
            $scriptname,
            LOGCODE_INSTANCE_NOT_FOUND,
            'Инстанс '.$recognized['instance'].' не найден'
        );
    }
} else
{
    // Список инстансов отлампа на сервере
    $instances = glob('/etc/opt/otlamp/*' , GLOB_ONLYDIR);
    $instances = array_map(function($item) {
        $ex_pop = explode('/',$item);
        return array_pop($ex_pop);
    }, $instances);
}

foreach( $instances as $instance )
{
    
    if( ! empty($recognized['host']) )
    {
        if( is_dir('/var/opt/otlamp/'.$instance.'/'.$recognized['host']) )
        {
            $hosts = [
                $recognized['host']
            ];
        } else
        {
            otlamp_notice(
                $scriptname,
                LOGCODE_HOST_NOT_FOUND,
                'Хост '.$recognized['host'].' в инстансе '.$instance.' не найден'
            );
        }
    } else
    {
        // Список хостов
        $hosts = glob('/var/opt/otlamp/'.$instance.'/*', GLOB_ONLYDIR);
        $hosts = array_map(function($item) {
           $exploded = explode('/',$item); 
           return array_pop($exploded);
        }, $hosts);
    }

    //######################################################################################3
    // сбор и подсчет данных по хосту
    $tempcount=0;
    foreach( $hosts as $host )
    {
        otlamp_create_flag($scriptname, $instance, $host);
        $pathtofile = '/var/log/opt/otlamp/'.$instance.'/'.$host.'/stat/stat.json';
        //TODO проверить не старый ли файл
        if (file_exists($pathtofile)) {
            $string = file_get_contents("$pathtofile");
            if ($string === false) {
                // deal with error...
                echo "ERROR";
            }
            $json_a = json_decode($string, true);
            if ($json_a === null) {
            // deal with error...
                otlamp_notice($scriptname, LOGCODE_COMPLETED, 'not JSON');
                echo "not JSON\n";
                break;
            }
            
            foreach( $json_a as $vhost_stat_res_type => $vhost_stat_res ) {
                switch ($vhost_stat_res_type) {
                    case 'average':
                    case 'count':
                    case 'sum':
                        foreach( $vhost_stat_res as $metric => $metricvalue ) {
                            if( ! isset($calc[$instance][$vhost_stat_res_type][$metric]['c']) ) {
                                  $calc[$instance][$vhost_stat_res_type][$metric]['c'] = 0;
                            }
                            if( ! isset($calc[$instance][$vhost_stat_res_type][$metric]['w']) ) {
                                $calc[$instance][$vhost_stat_res_type][$metric]['w'] = 0;
                            }
                            if( ! isset($calc[$instance][$vhost_stat_res_type][$metric]['v']) ) {
                                $calc[$instance][$vhost_stat_res_type][$metric]['v'] = 0;
                            }

                            if( ! isset($calc[$instance][$vhost_stat_res_type][$metric]['r']) ) {
                                $calc[$instance][$vhost_stat_res_type][$metric]['r'] = 0;
                            }

                            $datafile = '/var/log/opt/otlamp/'.$instance.'/'.$host.'/stat/'.$vhost_stat_res_type.'/'.$metric;
                            //данные по хосту
                            $calc[$instance]['hosts'][$host][$vhost_stat_res_type][$metric]['v'] = $metricvalue;
                            $calc[$instance]['hosts'][$host][$vhost_stat_res_type][$metric]['w'] = 0;
                            if( ! isset($calc[$instance]['hosts'][$host][$vhost_stat_res_type][$metric]['c']) ) {
                                $calc[$instance]['hosts'][$host][$vhost_stat_res_type][$metric]['c'] = 0;
                            }
                            
                            $calc[$instance]['hosts'][$host][$vhost_stat_res_type][$metric]['c']++;
                            //суммирование метрик хостов по инстансу
                            $calc[$instance][$vhost_stat_res_type][$metric]['v'] += (double)$metricvalue;
                            $calc[$instance][$vhost_stat_res_type][$metric]['c']++;

                            file_put_contents($datafile, $metricvalue, LOCK_EX);
                            $tempcount++;
                        }
                        break;
                    case 'factored':
                        foreach( $vhost_stat_res as $k => $v ) {
                            foreach( $v as $item => $value ) {
                                switch ($item) {
                                    case 'value':
                                        $datafile = '/var/log/opt/otlamp/'.$instance.'/'.$host.'/stat/'.$vhost_stat_res_type.'/'.$k.'_value';
                                        $calc[$instance]['hosts'][$host][$vhost_stat_res_type][$k]['v'] = $value;
                                        break;
                                        case 'weight':
                                            $datafile = '/var/log/opt/otlamp/'.$instance.'/'.$host.'/stat/'.$vhost_stat_res_type.'/'.$k.'_weight';
                                            $calc[$instance]['hosts'][$host][$vhost_stat_res_type][$k]['w'] = $value;
                                            break;
                                            default:
                                            $datafile = '/var/log/opt/otlamp/'.$instance.'/'.$host.'/stat/'.$vhost_stat_res_type.'/'.$k.'_'.$item;
                                            break;
                                }
                                
  
                                file_put_contents($datafile, $value, LOCK_EX);
                            }
                            
                            if( ! isset($calc[$instance][$vhost_stat_res_type][$k]['r'])) {
                                $calc[$instance][$vhost_stat_res_type][$k]['r'] = 0;
                            }

                            if( ! isset($calc[$instance][$vhost_stat_res_type][$k]['w'])) {
                                $calc[$instance][$vhost_stat_res_type][$k]['w'] = 0;
                            }

                            if( (int)$calc[$instance]['hosts'][$host][$vhost_stat_res_type][$k]['w'] > 0 )
                            {
                                $resultvalue=$calc[$instance]['hosts'][$host][$vhost_stat_res_type][$k]['v'] / $calc[$instance]['hosts'][$host][$vhost_stat_res_type][$k]['w'];
                                (double)$calc[$instance][$vhost_stat_res_type][$k]['r'] +=$resultvalue;
                                $calc[$instance][$vhost_stat_res_type][$k]['w'] += $calc[$instance]['hosts'][$host][$vhost_stat_res_type][$k]['w'];
                            } 
                        break;
                    }
                }
            }
        }    
    }    

   
    //######################################################################################3
    // сбор и подсчет данных по инстансу
    //echo 'сбор и подсчет данных по инстансу';
    foreach( $calctypes as $calctype ) {
        foreach( $calc[$instance][$calctype] as $metric => $metricvalue ) {
 
            if( ! isset($resultcalc['_server'][$calctype][$metric]['c']) ) {
                $resultcalc['_server'][$calctype][$metric]['c'] = 0;
            }

            if( ! isset($resultcalc['_server'][$calctype][$metric]['v']) ) {
                $resultcalc['_server'][$calctype][$metric]['v'] = 0;
            }

            if( ! isset($resultcalc['_server'][$calctype][$metric]['w']) ) {
                $resultcalc['_server'][$calctype][$metric]['w'] = 0;
            }

            if( ! isset($resultcalc['_server'][$calctype][$metric]['r']) ) {
                $resultcalc['_server'][$calctype][$metric]['r'] = 0;
            }
            
            switch ($calctype) {
                case 'sum':
                    $datafile = '/var/log/opt/otlamp/'.$instance.'/stat/'.$calctype.'/'.$metric;
                    $resultcalc['_server'][$calctype][$metric]['v'] +=$calc[$instance][$calctype][$metric]['v'];
                    $resultcalc['_server'][$calctype][$metric]['c']++;
                    file_put_contents($datafile, $calc[$instance][$calctype][$metric]['v'], LOCK_EX);
                    break;
                case 'count':
                    $datafile = '/var/log/opt/otlamp/'.$instance.'/stat/'.$calctype.'/'.$metric;
                    file_put_contents($datafile, $calc[$instance][$calctype][$metric]['c'], LOCK_EX);
                    $resultcalc['_server'][$calctype][$metric]['c']=+$calc[$instance][$calctype][$metric]['c'];
                    break;
                case 'average':
                    $datafile = '/var/log/opt/otlamp/'.$instance.'/stat/'.$calctype.'/'.$metric;
                    if( (int)$calc[$instance][$calctype][$metric]['c'] > 0 )
					{
                        
                        $resultvalue=$calc[$instance][$calctype][$metric]['v'] / $calc[$instance][$calctype][$metric]['c'];
                        $calc[$instance][$calctype][$metric]['r']=$resultvalue;
					}

                    file_put_contents($datafile, $resultvalue, LOCK_EX);
                    break;
                case 'factored':
                    //сохраняем результирующие данные
                    $datafile = '/var/log/opt/otlamp/'.$instance.'/stat/'.$calctype.'/'.$metric.'_value';
                    $resultvalue=$calc[$instance][$calctype][$metric]['r'];
                    file_put_contents($datafile, $resultvalue, LOCK_EX);

                    //сохраняем суммарный вес занчений, пока не понял как использовать но пусть будет
                    $datafile = '/var/log/opt/otlamp/'.$instance.'/stat/'.$calctype.'/'.$metric.'_weight';
                    $resultvalue=$calc[$instance][$calctype][$metric]['w'];
                    file_put_contents($datafile, $resultvalue, LOCK_EX);

                    break;
                }
                
            }
        }
        
    }       
        
        //######################################################################################3
        // сбор и подсчет данных по серверу
foreach( $instances as $instance )
        {    
    foreach( $calctypes as $calctype ) {
        foreach( $resultcalc['_server'][$calctype] as $metric => $metricvalue ) {
            switch ($calctype) {
                case 'sum':
                    $datafile = '/var/log/opt/otlamp/stat/'.$calctype.'/'.$metric;
                    file_put_contents($datafile, $resultcalc['_server'][$calctype][$metric]['v'], LOCK_EX);
                    break;
                case 'count':
                    $datafile = '/var/log/opt/otlamp/stat/'.$calctype.'/'.$metric;
                    file_put_contents($datafile, $resultcalc['_server'][$calctype][$metric]['c'], LOCK_EX);
                    break;                    
                case 'average':
                    $datafile = '/var/log/opt/otlamp/stat/'.$calctype.'/'.$metric;

                    if( (int)$calc[$instance][$calctype][$metric]['c'] > 0 )
    				{
                        $resultvalue=$calc[$instance][$calctype][$metric]['v'] / $calc[$instance][$calctype][$metric]['c'];
					}
                    else {
                        $resultvalue=0;
                    }

                    file_put_contents($datafile, $resultvalue, LOCK_EX);
                    break;
                case 'factored':
                    $datafile = '/var/log/opt/otlamp/stat/'.$calctype.'/'.$metric.'_value';
                    $resultcalc['_server'][$calctype][$metric]['r'] +=$calc[$instance][$calctype][$metric]['r'];
                    file_put_contents($datafile, $resultcalc['_server'][$calctype][$metric]['r'], LOCK_EX);
                    $datafile = '/var/log/opt/otlamp/stat/'.$calctype.'/'.$metric.'_weight';
                    $resultcalc['_server'][$calctype][$metric]['w'] +=$calc[$instance][$calctype][$metric]['w'];
                    file_put_contents($datafile, $resultcalc['_server'][$calctype][$metric]['w'], LOCK_EX);
                    break;
                }

        }

    }
}

// Пишет в лог запись о завершении выполнения
otlamp_notice($scriptname, LOGCODE_COMPLETED, 'Скрипт завершил работу');
