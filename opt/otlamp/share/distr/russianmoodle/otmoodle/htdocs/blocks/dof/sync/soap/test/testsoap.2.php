// <?php
// ////////////////////////////////////////////////////////////////////////////
// //                                                                        //
// // NOTICE OF COPYRIGHT                                                    //
// //                                                                        //
// // Dean`s Office for Moodle                                               //
// // Электронный деканат                                                    //
// // <http://deansoffice.ru/>                                               //
// //                                                                        //
// //                                                                        //
// // This program is free software: you can redistribute it and/or modify   //
// // it under the terms of the GNU General Public License as published by   //
// // the Free Software Foundation, either version 3 of the Licensen.        //
// //                                                                        //
// // This program is distributed in the hope that it will be useful,        //
// // but WITHOUT ANY WARRANTY; without even the implied warranty of         //
// // MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the          //
// // GNU General Public License for more details.                           //
// //                                                                        //
// // You should have received a copy of the GNU General Public License      //
// // along with this program.  If not, see <http://www.gnu.org/licenses/>.  //
// //                                                                        //
// ////////////////////////////////////////////////////////////////////////////
// ini_set('display_startup_errors',1);
// ini_set('display_errors',1);
// ini_set("soap.wsdl_cache_enabled", "0");
// error_reporting(-1);

// /**
//  * Объект для передачи методу set_meta_contract()
//  *
//  * @pw_set nillable=false
//  * @pw_element string $requestlogin Идентификатор системы-отправителя запроса
//  * @pw_set nillable=false
//  * @pw_element int $requesttime Время генерации запроса
//  * @pw_set nillable=false
//  * @pw_element string $requesthash sha1-хеш
//  * @pw_set nillable=false
//  * @pw_element int $id Внешний id метаконтракта
//  * @pw_element string $num Номер метаконтракта
//  * @pw_set nillable=false
//  * @pw_element string $departmentcode Код подразделения
//  * @pw_element object $cov Дополнительный массив cov, содержащий дополнительные поля к объекту
//  * @pw_complex set_meta_contract_soap_in
//  */
// class set_meta_contract_soap_in
// {

//     public $requestlogin;
//     public $requesttime;
//     public $requesthash;
//     public $id;
//     public $num;
//     public $departmentcode;
//     public $cov;

// }

// /**
//  * Объект для передачи методу set_person()
//  *
//  * @pw_set nillable=false
//  * @pw_element string $requestlogin Идентификатор системы-отправителя запроса
//  * @pw_set nillable=false
//  * @pw_element int $requesttime Время генерации запроса
//  * @pw_set nillable=false
//  * @pw_element string $requesthash sha1-хеш
//  * @pw_set nillable=false
//  * @pw_element int $id Внешний id персоны
//  * @pw_set nillable=false
//  * @pw_element string $firstname Имя
//  * @pw_element string $middlename Отчество
//  * @pw_set nillable=false
//  * @pw_element string $lastname Фамилия
//  * @pw_element string $preferredname Префикс для имения (Mr. Dr. Г-н, Г-а)
//  * @pw_element int $dateofbirth Дата рождения в UTS
//  * @pw_set nillable=false
//  * @pw_element string $gender Пол (male, female, unknown)
//  * @pw_set nillable=false
//  * @pw_element string $email Основной адрес электронной почты
//  * @pw_element string $phonehome Домашний телефон
//  * @pw_element string $phonework Рабочий телефон
//  * @pw_element string $phonecell Сотовый телефон
//  * @pw_element int $passtypeid Тип удостоверения личности (1 - свидетельство о рождении, 2 - паспорт гражданина РФ, 3 - загранпасспорт, 4 - разрешение на временное проживание лица без гражданства, 5 - вид на жительство, 6 - военный билет, 7 - водительсткое удостоверение пластиковое, 8 - вод. удостоверение форма 1, 9 - вод. удостоверение международное)
//  * @pw_element string $passportserial Серия удостоверения личности (если предусмотрена типом документа)
//  * @pw_element string $passportnum Номер удостоверения личности
//  * @pw_element int $passportdate Дата выдачи удостоверения личности в UTS
//  * @pw_element string $passportem Название организации, выдавшей удостоверение личности
//  * @pw_element string $citizenship Гражданство
//  * @pw_element string $departmentcode Основной отдел, к которому приписан человек (может редактировать его данные в persons)
//  * @pw_element string $about Характеристика личности
//  * @pw_element string $skype Уникальный идентификатор в Skype
//  * @pw_element string $phoneadd1 Дополнительный телефон 1
//  * @pw_element string $phoneadd2 Дополнительный телефон 2
//  * @pw_element string $phoneadd3 Дополнительный телефон 3
//  * @pw_element string $emailadd1 Дополнительная электронная почта 1
//  * @pw_element string $emailadd2 Дополнительная электронная почта 2
//  * @pw_element string $emailadd3 Дополнительная электронная почта 3 
//  * @pw_element set_address $passportadd Адрес прописки по паспорту (для генерации документов)
//  * @pw_element set_address $address Текущий адрес (почтовый адрес)
//  * @pw_element set_address $birthaddress Адрес рождения персоны
//  * @pw_element object $cov Дополнительный массив cov, содержащий дополнительные поля к объекту
//  * @pw_complex set_person_soap_in
//  */
// class set_person_soap_in
// {

//     public $requestlogin;
//     public $requesttime;
//     public $requesthash;
//     public $id;
//     public $firstname;
//     public $middlename;
//     public $lastname;
//     public $preferredname;
//     public $dateofbirth;
//     public $gender;
//     public $email;
//     public $phonehome;
//     public $phonework;
//     public $phonecell;
//     public $passtypeid;
//     public $passportserial;
//     public $passportnum;
//     public $passportdate;
//     public $passportem;
//     public $citizenship;
//     public $departmentcode;
//     public $about;
//     public $skype;
//     public $phoneadd1;
//     public $phoneadd2;
//     public $phoneadd3;
//     public $emailadd1;
//     public $emailadd2;
//     public $emailadd3;
//     public $passportaddr;
//     public $address;
//     public $birthaddress;
//     public $cov;

//     public function __construct()
//     {
//         $this->address      = new set_address();
//         $this->birthaddress = new set_address();
//         $this->passportaddr = new set_address();
//     }
// }

// /**
//  * Объект для передачи методу set_contract()
//  *
//  * @pw_set nillable=false
//  * @pw_element string $requestlogin Идентификатор системы-отправителя запроса
//  * @pw_set nillable=false
//  * @pw_element int $requesttime Время генерации запроса
//  * @pw_set nillable=false
//  * @pw_element string $requesthash sha1-хеш
//  * @pw_set nillable=false
//  * @pw_element int $id Внешний id договора
//  * @pw_element int $typeid Тип договора, если у учебного заведения предусмотрено несколько разных типов договоров
//  * @pw_element string $num Номер договора
//  * @pw_element string $numpass Номер пропуска, студенческого билета и т.п.
//  * @pw_element int $date Дата заключения в UTS
//  * @pw_element int $sellerid Менеджер по работе с клиентами (приемная комиссия, партнер) - добавляет договор, меняет статус до "подписан клиентом", отслеживает статус договора и ход обучения (id по таблице persons)
//  * @pw_element int $clientid Клиент, оплачивающий обучение (законный представитель, сам совершеннолетний ученик или куратор от организации, может принимать значение 0 или null, если клиент создается, а контракт имеет черновой вариант) (по таблице persons)
//  * @pw_element int $studentid Ученик (может принимать значение 0, если ученик создается, а контракт имеет черновой вариант) (по таблице persons)
//  * @pw_element string $notes Заметки
//  * @pw_element string $departmentcode Подразделение в таблице departments , к которому приписан контракт на обучение (например, принявшее ученика)
//  * @pw_element string $contractform Форма договора (шаблон)
//  * @pw_element int $organizationid Юридическое лицо в таблице organizations , оплачивающее договор, если ученик платит за себя сам - то не указывается.
//  * @pw_element int $curatorid Куратор или классный руководитель данного ученика (по таблице persons или не указан), отслеживает учебный процесс, держит связь с учеником, является посредником между учеником и системой, может быть внешней персоной.
//  * @pw_element int $enddate Дата окончания договора в UTS
//  * @pw_element int $metacontractid id метаконтракта, к которому привязан договор, в таблице metacontracts 
//  * @pw_element object $cov Дополнительный массив cov, содержащий дополнительные поля к объекту
//  * @pw_complex set_contract_soap_in
//  */
// class set_contract_soap_in
// {

//     public $requestlogin;
//     public $requesttime;
//     public $requesthash;
//     public $id;
//     public $date;
//     public $sellerid;
//     public $clientid;
//     public $studentid;
//     public $notes;
//     public $departmentcode;
//     public $curatorid;
//     public $metacontractid;
//     public $cov;

// }

// /**
//  * Возвращаемый объект для операций set
//  *
//  * @pw_element int $id Внешний id объекта
//  * @pw_element int $dofid Внутренний id объекта
//  * @pw_element int $modified Дата модификации созданного или обновлённого объекта
//  * @pw_element string $hash Хеш операции
//  * @pw_element string $errorcode Код ошибки, если таковые возникли
//  * @pw_complex set_soap_out
//  */
// class set_soap_out
// {

//     public $id;
//     public $dofid;
//     public $modified;
//     public $hash;
//     public $errorcode;

// }

// /**
//  * Объект (массив) адреса
//  *
//  * @pw_element string $postalcode Почтовый индекс
//  * @pw_element string $country Код страны проживания (по предъявленному паспорту) в ICO 3166-1:1997 (RU)
//  * @pw_element string $region Код региона по ISO 3166-2 (RU-NGR, RU-MOS)
//  * @pw_element string $county Административный район
//  * @pw_element string $city Город
//  * @pw_element string $streetname Название улицы
//  * @pw_element string $streettype Тип улицы
//  * @pw_element string $number Номер дома
//  * @pw_element string $gate Подъезд
//  * @pw_element string $floor Этаж
//  * @pw_element string $apartment Квартира
//  * @pw_element float $latitude Широта в градусах
//  * @pw_element float $longitude Долгота в градусах
//  * @pw_complex set_address
//  */
// class set_address
// {
//     public $postalcode;
//     public $country;
//     public $region;
//     public $county;
//     public $city;
//     public $streetname;
//     public $streettype;
//     public $number;
//     public $gate;
//     public $floor;
//     public $apartment;
//     public $latitude;
//     public $longitude;
// }

// // Класс-обёртка для отладки XML-сообщений
// class SoapClientDebug extends SoapClient
// {
//   public function __doRequest($request, $location, $action, $version, $one_way = 0) {
//       // Add code to inspect/dissect/debug/adjust the XML given in $request here
//       echo '<br>Запрос:';
//       echo '<pre>';
//       var_dump(htmlspecialchars($request));
//       echo '</pre>';
//       // Uncomment the following line, if you actually want to do the request
//       // return parent::__doRequest($request, $location, $action, $version, $one_way);
//   }
// }

// /**
//  * Класс для формирования запросов к SOAP-сервису плагина dof/sync/soap
//  */
// //class dof_soap_client_test extends SoapClientDebug
// class dof_soap_client_test extends SoapClient
// {
//     /**
//      * The WSDL URI
//      *
//      * @var string
//      */
//     public static $wsdluri = 'http://sinergiya.demo2.opentechnology.ru/blocks/dof/sync/soap/soap.php?do=wsdl1c';

//     /**
//      * Идентификатор системы
//      *
//      * @var string
//      */
//     private $requestlogin;

//     /**
//      * Ключ идентификатора системы
//      *
//      * @var string
//      */
//     private $requestpassword;

//     /**
//      * Объект PHP SoapClient
//      *
//      * @var object
//      */
//     public static $server = null;

//     /** Инициализация SOAP-клиента
//      * 
//      * @param string $requestlogin - идентификатор системы
//      * @param string $requestpassword - ключ идентификатора
//      * @param string $wsdluri [optional] - ссылка на описание сервиса (wsdl)
//      */
//     public function __construct($requestlogin = '', $requestpassword = '', $wsdluri = null)
//     {
//         if ( !empty($wsdluri) AND filter_var($wsdluri, FILTER_VALIDATE_URL) )
//         {
//             self::$wsdluri = $wsdluri;
//         }
//         $this->requestlogin    = $requestlogin;
//         $this->requestpassword = $requestpassword;
//         parent::__construct(self::$wsdluri);
//     }
    
//     /** Вызвать функцию SOAP-сервиса с 
//      * 
//      * @param string $type - тип запроса (set, get, ...)
//      * @param string $method - метод сервиса для выполнения запроса
//      * @param object $params - параметры запроса
//      * @throws SoapFault
//      * @return object|mixed - объект, содержащий ответ сервера или mixed в случае ошибки
//      */
//     public function call($type, $method, $params)
//     {
//         if ( !is_string($type) OR
//              !is_string($method) OR
//              !is_object($params) )
//         {
//             throw new SoapFault('Incorrect params');
//         }
//         // Объект для передачи SOAP-сервису
//         $input = clone $params;
//         $requesttime  = time();
//         $input->requesttime  = $requesttime;
//         $input->requestlogin = $this->requestlogin;
//         $input->requesthash  = $this->hash_object($params, $requesttime);
//         switch ( $type )
//         {
//             case 'set':
//                 return $this->$method($input);
//             default:
//                 throw new SoapFault('Incorrect call type: ['.$type.']');
//         }
//     }

//     /** Отсортировать поля внутри объекта
//      * @param object $object - объект с полями
//      * @return bool|object - false в случае ошибки или объект
//      */
//     public function sort_object_fields($object, $sortorder = SORT_REGULAR)
//     {
//         if ( !is_object($object) OR empty($object) )
//         {
//             return false;
//         }
//         // Преобразуем объект в массив
//         $array = get_object_vars($object);
//         $sorted = new stdClass();
//         // Отсортируем массив
//         ksort($array, $sortorder);
//         // Сформируем объект заново и передадим его
//         foreach ( $array as $key => $value )
//         {
//             $sorted->$key = $value;
//         }
//         return $sorted;
//     }

//     /** Получить sha1-хеш SOAP-запроса
//      * 
//      * @param object $input - 
//      * @param int $time - время генерации запроса
//      * @return string - sha1-хеш
//      */
//     private function hash_object($input, $time)
//     {
//         if ( !is_object($input) )
//         {
//             return false;
//         }
//         // Рассчитаем sha1-хеш от входных параметров
//         $hashstring = '';
//         // Ключ, время, идентификатор системы
//         $hashstring .= $this->requestpassword;
//         $hashstring .= $time;
//         $hashstring .= $this->requestlogin;
//         // Эти поля не нужны при формировании json-строки
//         unset($input->requesthash);
//         unset($input->requestlogin);
//         unset($input->requesttime);
//         $cov = new stdClass();
//         // Если cov не передали, то json_encode($cov) возвратит '{}'
//         if ( isset($input->cov) AND is_object($input->cov) )
//         {
//             $cov = $input->cov;
//             unset($input->cov);
//         }
//         // Сортируем объект и массив cov
//         $sorted = $this->sort_object_fields($input);
//         $sortedcov = $this->sort_object_fields($cov);
//         // Создаём json-строки из них и формируем sha1-hash
//         $hashstring .= json_encode($sorted);
//         $hashstring .= json_encode($sortedcov);
// //        echo '<pre>hash:<br>';
// //        var_dump($hashstring);
// //        echo '</pre>';
//         return sha1($hashstring);
//     }
// }

// // Подключаем конфиг с клиентскими настройками (логины, пароли)
// //$wsdluri = 'http://mdlotdistr.dev/blocks/dof/sync/soap/soap.php?do=wsdl';
// $requestlogin = 'exampleclient';
// $requestpassword = 'secret';
// $soapclient = new dof_soap_client_test($requestlogin, $requestpassword, $wsdluri);

// // Создание метаконтрактов

// $mcontracts = array();
// $mcontract = new set_meta_contract_soap_in();
// $mcontract->id = 1000;
// $mcontract->num = 'Тестовый метаконтракт';
// $mcontract->departmentcode = 'offspin';
// //$mcontract->cov = array(1, 2, 3);
// $mcontracts[$mcontract->id] = $mcontract;

// $mcontract = new set_meta_contract_soap_in();
// $mcontract->id = 1001;
// $mcontract->num = 'Тестовый метаконтракт 1';
// // Без подразделения
// $mcontract->departmentcode = null;
// //$mcontract->cov = array(1, 2, 3);
// $mcontracts[$mcontract->id] = $mcontract;

// make_request($soapclient, $mcontracts, 'set_meta_contract', 'Создание метаконтракта');

// // Создание персон
// $persons = array();

// $person = new set_person_soap_in();
// $person->id = 1000;
// $person->firstname = 'Тест';
// $person->lastname = 'Тестовый';
// $person->dateofbirth = time() - 86400 * 365 * 18;
// $person->email = 'example@example.com';
// $person->gender = 'male';
// $person->skype = 'testskype';
// $person->departmentcode = 'offspin';
// // Нужно проверить, какой вариант работает

// //$person->cov = array();
// //$person->cov['oldfirstname1'] = 'Староеимя1';
// //$person->cov['oldlastname1'] = 'Стараяфамилия1';
// //$person->cov['oldfirstname2'] = 'Староеимя2';
// //$person->cov['oldlastname2'] = 'Стараяфамилия2';
// //
// $person->cov = new stdClass();
// $person->cov->oldfirstname1 = 'Староеимя1';
// $person->cov->oldlastname1 = 'Стараяфамилия1';
// $person->cov->oldfirstname2 = 'Староеимя2';
// $person->cov->oldlastname2 = 'Стараяфамилия2';

// ////$person->cov = array('asdasda', 'asdasd', 'dasdzjo09x', 'dasdzjo09x', 'dasdzjo09x');
// //$person->cov = array(123, 'abc', new set_address());

// //$person->cov = 'asds';
// $persons[$person->id] = $person;

// $person = new set_person_soap_in();
// $person->id = 1001;
// $person->firstname = 'Тест1';
// $person->lastname = 'Тестовый1';
// $person->dateofbirth = time() - 86400 * 365 * 20;
// $person->email = 'example1@example.com';
// $person->gender = 'male';
// $person->skype = 'testskype1';
// $person->departmentcode = 'offspin';
// // Адреса
// $person->passportaddr = new set_address();
// $person->passportaddr->city = 'Москва';
// $person->passportaddr->country = 'RU';
// $person->passportaddr->region = 'RU-MOW';
// $person->passportaddr->postalcode = '117556';

// $person->birthaddress = new set_address();
// $person->birthaddress->city = 'Самара';
// $person->birthaddress->country = 'RU';
// $person->birthaddress->region = 'RU-MOW';
// $person->birthaddress->postalcode = '400556';

// $person->address = new set_address();
// $person->address->city = 'Москва';
// $person->address->country = 'RU';
// $person->address->region = 'RU-MOW';
// $person->address->postalcode = '117556';
// $persons[$person->id] = $person;

// $person = new set_person_soap_in();
// $person->id = 1002;
// $person->firstname = 'Тест2';
// $person->lastname = 'Тестовый2';
// $person->dateofbirth = time() - 86400 * 365 * 25;
// $person->email = 'example2@example.com';
// $person->emailadd1 = 'exampleadd1@example.com';
// $person->emailadd2 = 'exampleadd2@example.com';
// $person->gender = 'male';
// $person->skype = 'testskype2';
// // Без подразделения
// //$person->departmentcode = 'offspin';

// $persons[$person->id] = $person;

// $person = new set_person_soap_in();
// $person->id = 1003;
// $person->firstname = 'Тест3';
// $person->lastname = 'Тестовый3';
// $person->middlename = 'Тестович3';
// $person->dateofbirth = time() - 86400 * 365 * 30;
// $person->email = 'example3@example.com';
// $person->gender = 'male';
// $person->skype = 'testskype3';
// $person->departmentcode = 'offspin';
// $person->phonehome = '+7-499-680-54';
// $person->phonework = '+7-495-119-33';
// $person->phonecell = '+7-912-562-33-21';
// $persons[$person->id] = $person;

// $person = new set_person_soap_in();
// $person->id = 1004;
// $person->firstname = 'Тест4';
// $person->lastname = 'Тестовый4';
// $person->dateofbirth = time() - 86400 * 365 * 40;
// $person->email = 'example4@example.com';
// $person->gender = 'male';
// $person->skype = 'testskype4';
// $person->departmentcode = 'offspin';
// $person->passtypeid = 2;
// $person->passportserial = '1729';
// $person->passportnum = '559291';
// $person->passportdate = time() - 86400 * 365 * 25;
// $person->passportem = 'Отделение УФМС России по г. Москве по району АЛТУФЬЕВСКИЙ';
// $person->citizenship = 'РФ';
// $persons[$person->id] = $person;

// make_request($soapclient, $persons, 'set_person', 'Создание персоны');

// $contracts = array();
// $contract = new set_contract_soap_in();
// $contract->id = 1000;
// $contract->date = time() - 86400 * 7;
// //$contract->sellerid;
// $contract->clientid = 1000;
// $contract->studentid = 1001;
// $contract->notes = 'Some notes';
// $contract->departmentcode = 'offspin';
// $contract->curatorid = 1002;
// $contract->metacontractid = 1000;
// $contracts[$contract->id] = $contract;

// $contract = new set_contract_soap_in();
// $contract->id = 1001;
// $contract->date = time() - 86400 * 7;
// //$contract->sellerid;
// $contract->clientid = 1002;
// $contract->studentid = 1002;
// $contract->notes = 'Some notes 2';
// //$contract->departmentcode = 'offspin';
// $contract->curatorid = 1001;
// $contract->metacontractid = 1001;
// $contracts[$contract->id] = $contract;

// $contract = new set_contract_soap_in();
// $contract->id = 1002;
// $contract->date = time() - 86400 * 7;
// //$contract->sellerid;
// $contract->clientid = 1001;
// $contract->studentid = 1001;
// $contract->notes = 'Some notes 2';
// //$contract->departmentcode = 'offspin';
// $contract->curatorid = 1002;
// $contracts[$contract->id] = $contract;

// $contract = new set_contract_soap_in();
// $contract->id = 1003;
// $contract->date = time() - 86400 * 7;
// //$contract->sellerid;
// $contract->clientid = 1000;
// $contract->studentid = 1000;
// $contract->notes = 'Some notes 3';
// //$contract->departmentcode = 'offspin';
// $contract->curatorid = 1001;
// $contracts[$contract->id] = $contract;

// $contract = new set_contract_soap_in();
// $contract->id = 1004;
// $contract->date = time() - 86400 * 17;
// //$contract->sellerid;
// $contract->clientid = 1000;
// $contract->studentid = 1002;
// $contract->notes = 'Some notes 4';
// $contract->departmentcode = 'offspin';
// $contract->curatorid = 1001;
// $contracts[$contract->id] = $contract;

// $contract = new set_contract_soap_in();
// $contract->id = 1005;
// $contract->date = time() - 86400 * 17;
// //$contract->sellerid;
// $contract->clientid = 1003;
// $contract->studentid = 1002;
// $contract->notes = 'Some notes 5';
// $contract->departmentcode = 'offspin';
// $contract->curatorid = 1001;
// $contracts[$contract->id] = $contract;

// make_request($soapclient, $contracts, 'set_contract', 'Создание контракта');

// function make_request(dof_soap_client_test $soapclient, $objects, $method, $description = null)
// {
//     if ( empty($description) )
//     {
//         $description = 'Создание объекта';
//     }
//     foreach ( $objects as $id => $object )
//     {
//         echo '<br>' . $id . ':';
//         $result = null;
//         try
//         {
//             $result = $soapclient->call('set', $method, $object);
//         } catch ( SoapFault $e )
//         {
//             echo '<br>SoapFault caught, message: ' . $e->getMessage();
//             echo '<br>Last Response: ' . $soapclient->__getLastResponse();
//             echo '<br>getCode: ';
//             echo '<pre>';
//             var_dump($e->getCode());
//             echo '</pre>';
//             echo '<br>getFile: ';
//             echo '<pre>';
//             var_dump($e->getFile());
//             echo '</pre>';
//             echo '<br>getLine: ';
//             echo '<pre>';
//             var_dump($e->getLine());
//             echo '</pre>';
//             echo '<br>getPrevious: ';
//             echo '<pre>';
//             var_dump($e->getPrevious());
//             echo '</pre>';
//             echo '<br>getTraceAsString: ';
//             echo '<pre>';
//             var_dump(htmlentities($e->getTraceAsString()));
//             echo '</pre>';
//         }
//         echo '<br>' . $description . ' [' . $id . ']:';
//         if ( !empty($result) )
//         {
//             echo '<pre>';
//             var_dump($result);
//             echo '</pre>';
//         } else
//         {
//             echo '<pre>';
//             echo 'Пусто!';
//             echo '</pre>';
//         }
//     }
// }

// ?>