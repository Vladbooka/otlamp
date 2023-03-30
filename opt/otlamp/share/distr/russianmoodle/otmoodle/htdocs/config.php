<?php  
//  Конфигруационный файл СЭО 3KL
// Лучше не трогайте здесь ничего, если не знаете точно, что вы делаете

// Строки для отладки
//ini_set('display_startup_errors',1);
//ini_set('display_errors',1);
//error_reporting(-1);




// Подключение параметров MySQL
require_once(dirname(dirname(__FILE__)) . '/local/mysql.php');
// Подключение доменных имен
require_once(dirname(dirname(__FILE__)) . '/local/domains.php');

// Объявление глобальных переменных
unset($CFG);
global $CFG;
$CFG = new stdClass();

$CFG->dbtype    = 'mysqli';
$CFG->dblibrary = 'native';
$CFG->dbhost    = $OTLAMP_MYSQL_HOST;
$CFG->dbname    = $OTLAMP_MYSQL_DB;
$CFG->dbuser    = $OTLAMP_MYSQL_USER;
$CFG->dbpass    = $OTLAMP_MYSQL_PASS;
$CFG->prefix    = 'mdl_';
$CFG->dboptions = array (
  'dbpersist' => 0,
  'dbsocket' => 0,
);

// (Только для SaaS - блокировка доступа после истечения тарифа)

//$otbilling_sc_offtime_uts = intval('1659225600');
//if ($otbilling_sc_offtime_uts > 0 AND time() > $otbilling_sc_offtime_uts)
//{
//	header('Content-type: text/html; charset=utf-8');
//	require_once(dirname(__FILE__). '/otoff.php');
//	exit();
//}

// Лимит онлайн-пользователей
$CFG->otserialsaasonlinelimit = 0;

// Лимит полезного пространства на диске в мегабайтах (блокирует загрузку файлов при превышении)
// $CFG->moodlesizelimit = 1024; // Указывается в мегабайтах (0 - запрета нет, 1 - запрет безусловный)
//$CFG->moodlesizelimit  = 337920;
$CFG->moodlesizelimit  = 0;


// $CFG->customfrontpageinclude = dirname(__FILE__) . '/local/crw/homepageinclude.php';
//$CFG->showcopyrightlink = true;
// todo: тут нужно автопределение протокола и домена.
// дефолтный домен подставлять только при запуске из консоли (или если домен не определен)
$CFG->wwwroot   = 'https://'.$OTLAMP_DOMAINS['default'];
// Определение протокола и домена, с которого запущена система
if ( isset($_SERVER['HTTP_HOST']) && isset($_SERVER['REQUEST_SCHEME']) )
{
    $CFG->wwwroot = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'];
}


// @error_reporting(E_ALL | E_STRICT); // NOT FOR PRODUCTION SERVERS!
// @ini_set('display_errors', '1');    // NOT FOR PRODUCTION SERVERS!
// $CFG->debug = (E_ALL | E_STRICT);   // === DEBUG_DEVELOPER - NOT FOR PRODUCTION SERVERS!
// $CFG->debugdisplay = 1;             // NOT FOR PRODUCTION SERVERS!

$CFG->userfilterfields = [
    'realname' => 0,
    'lastname' => 0, 
    'firstname' => 0, 
    'email' => 0, 
    'phone1' => 0,
    'phone2' => 0,
    'username' => 1, 
    'profile' => 1, 
    'idnumber' => 1,
    'city' => 1, 
    'country' => 1,
    'confirmed' => 1, 
    'suspended' => 1, 
    'courserole' => 1, 
    'systemrole' => 1,
    'cohort' => 1, 
    'firstaccess' => 1, 
    'lastaccess' => 1, 
    'neveraccessed' => 1, 
    'timemodified' => 1,
    'nevermodified' => 1, 
    'auth' => 1, 
    'mnethostid' => 1,
];

// Отключить отправку электронной почты
// $CFG->niemailever = true;

$CFG->dataroot  = dirname(dirname(__FILE__)) . '/data';
$CFG->dirroot   = dirname(__FILE__);

$CFG->admin     = 'admin';

$CFG->directorypermissions = 0777;

//$CFG->passwordsaltmain = $OTLAMP_SALT;



// Дополнительная информация администратору
$CFG->additionalinfo = 
	"Срок окончания поддержки по договору: 2022-07-31"
	."Срок выключения поддержки: 2022-07-31";


// Вставка дополнительного кода из инвентарного файла в config.php

// Конец вставки дополнительного кода


require_once(dirname(__FILE__) . '/lib/setup.php');

// There is no php closing tag in this file,
// it is intentional because it prevents trailing whitespace problems!

