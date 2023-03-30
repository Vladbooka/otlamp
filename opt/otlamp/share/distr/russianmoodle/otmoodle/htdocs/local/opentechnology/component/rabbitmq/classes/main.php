<?php
namespace otcomponent_rabbitmq;

use otcomponent_phpamqplib;

// Плагин для работы с RabbitMQ
// Объявление основного класса

// Для работы надо установить в extlib
// composer require php-amqplib/php-amqplib
// перед этим сам composer и  php7.0-bcmat


class main
{
    /*
     * Массив с подключениями
     */
    protected $connections=array();
    /**
     * Инициализируем библиотеку
     */
    public function __construct()
    {
        otcomponent_phpamqplib\autoload::register();
    }
    
    /*
     * Деструктор
     * закрывает соединения и прибирает за собой
     */
    function __destruct()
    {
        // Закрываем rabbitmq, а он закрывает все открытые каналы
        $this->closeAll();
    }
    
    /**
     * Метод, возвращающий конект по параметрам подключения
     * конекты кешируются и возвращаются повторно
     * Этот метод публичен, на случай, если плагину нужно будет открыть нестандартное соединение
     */
    public function connectionCustom( $host,
        $port,
        $user,
        $password,
        $vhost = '/',
        $insist = false,
        $login_method = 'AMQPLAIN',
        $login_response = null,
        // $locale = 'en_US',
        $locale = 'ru_RU',
        $connection_timeout = 3.0,
        $read_write_timeout = 3.0,
        $context = null,
        $keepalive = false,
        $heartbeat = 0)
    {
        // Считаем хеш по основным параметрам подключения
        $connectionhash = md5("{$host}-{$port}-{$user}-{$password}-{$vhost}");
        // Если соединение уже открыто, возвращащаем его
        if (isset($this->connections[$connectionhash])
            AND
            $this->connections[$connectionhash] instanceof \PhpAmqpLib\Connection\AbstractConnection)
        {
            return $this->connections[$connectionhash];
        }
        
        // Иначе открываем и сохраняем
        return $this->connections[$connectionhash] = new \PhpAmqpLib\Connection\AMQPStreamConnection($host, $port, $user, $password, $vhost,
            $insist,
            $login_method,
            $login_response,
            $locale,
            $connection_timeout,
            $read_write_timeout,
            $context,
            $keepalive,
            $heartbeat);
    }
    
    /**
     * Метод, возвращающий коннект по его имени в конфиге
     *
     */
    public function connection($connectionname = 'default')
    {
        global $CFG;
        
        if (file_exists($CFG->dataroot . '/plugins/otcomponent_rabbitmq/rmqservers.php'))
        {//имеется файл с настройками серверов
            require($CFG->dataroot . '/plugins/otcomponent_rabbitmq/rmqservers.php');
            if( isset($rmqservers) && array_key_exists($connectionname, $rmqservers) )
            {
                // Подставляем параметры из конфига
                // Важно, чтобы в настройка соблюдался порядок условий
                // т.к. вставляем мы их не по именам
                return $this->connectionCustom(...$rmqservers[$connectionname]);
            } else
            {
                throw new \moodle_exception('Unknown rmq-server. There is no such server code in config.');
            }
        } else
        {
            throw new \moodle_exception('Couldn\'t find config with rmq-servers');
        }
    }
    
    /**
     * Метод, закрывающий все открытые соединения
     * Вызывать его можно только из деструктора
     * Т.к. он закроет все открытые соединения, даже те, на которые есть ссылки
     */
    public function closeAll()
    {
        // Просматриваем все открытые соединения
        foreach ($this->connections as $connectionhash => $connection)
        {
            // Закрываем соединение
            // Что, в свою очередь, закроет все кналы
            $connection->close();
            // Удаляем соединение из хеша, чтобы никто повторно им не воспользовался
            // А то будет ошибка
            unset($this->connections[$connectionhash]);
        }
        
        return true;
    }
    
    /**
     * Создать объект сообщения с переданными параметрами
     * если нужно, кодируем $body в json
     */
    public function createMessage($body = '', $properties = array())
    {
        // Устанавливаем контент-тип и кодируем сообщение
        if (!is_scalar($body))
        {
            // Если нам передали массив - то кодируем в json без вариантов
            $properties['content_type'] = 'application/json';
            // Кодируем массив в json
            $body = json_encode($body);
        }elseif(empty($properties['content_type']))
        {
            // В остальных случаях, если не установлен, то text/plain
            $properties['content_type'] = 'text/plain';
        }
        return new \PhpAmqpLib\Message\AMQPMessage($body, $properties);
    }
    /**
     * Создать объект сообщения с переданными параметрами
     */
    public function createMessagePersistent($body = '', $properties = array())
    {
        // Устанавливаем опцию постоянного сообщения
        $properties['delivery_mode']=\PhpAmqpLib\Message\AMQPMessage::DELIVERY_MODE_PERSISTENT;
        return $this->createMessage($body, $properties);
    }
    
    /*
     * Иницализирует адаптер RabbitMQтелеграм-RabbitMQ шлюз
     * @param mixed $config - абсолютный путь к YAML, либо объект с настройками
     */
    public function getAdapter($config)
    {
        // @todo: Необходимо научить класс инициализировать объект по параметрам из YAML
        return new adapter($this, $config);
    }
    
//     /*
//      * Иницализирует сервис на базе RabbitMQ
//      * @param string $code - код файла с YAML описанием сервиса
//      */
//     public function getService($profilecode)
//     {
       
//         // Загружаем опции сервиса
//         $profile = $this->getCore()->p('profiles')->loadYamlDir($this, 'services', $profilecode);
//         if (empty($profile))
//         {
//             // @todo: тут кидать исключение
//             echo "Не удалось загрузить параметры сервиса {$profilecode}\n";
//         }
//         // Определяем имя класса
//         $serviceclass = $profile['rmqservice']['class'];
//         echo "Сервис {$profilecode} базируется на классе {$serviceclass}\n";
        
//         // Проверяем код класса сервиса
//         if (!$this->getCore()->p('str')->isCode($serviceclass))
//         {
//             // @todo Выкинуть исключение - неверное имя профиля
//             echo "Неверное имя класса {$serviceclass}\n";
//             return false;
//         }
//         if (!is_file($this->getPath("lib/services/$serviceclass.php")))
//         {
//             // @todo Выкинуть исключение - неверное имя профиля
//             echo "Не найден файл с классом {$serviceclass}\n";
//             return false;
//         }
//         // Подключаем библиотеку
//         require($this->getPath('lib/service.php'));
//         // Загружаем класс
//         require_once $this->getPath("lib/services/$serviceclass.php");
//         // Создаем экземпляр сервиса
//         $serviceclassname = "MstpRabbitMQService{$serviceclass}";
//         return new $serviceclassname($this,$profile);
        
//     }
    
//     /**
//      * Метод, обрабатывающий прямой вызов из консоли
//      * используется в основном для отладки связи
//      * Вызывается через php my-site/bin/cmd.php rabbitmq <команда>
//      */
//     public function cpeCmdExecute($myargs,$args)
//     {
     
//         // Подключаемся к основному брокеру сообщенийё
//         $connection = $this->connection();
//         // Открываем канал (или получаем ссылку на деволтный открытый, если бы он был)
//         $channel = $connection->channel();
        
//         // Тестовую точку доступа,
//         // т.к. мы обязаны объявлять их оба раза, на случай, если чтение будет запущено
//         // раньше отправки. Чтобы не было ошибки.
//         // Параметры (учитываются только при первом объявлении):
//         // имя
//         // тип (см. памятку)
//         // пассивный? (влияет на поведение при повторном объявлении той же точки c другими параметрам
//         //  например, если была неустойчивая, а пробуем переобъявить, как устойчивую
//         // тип при этом все-равно не сменится, просто ошибки не будет)
//         // устойчивый к перезагрузке?
//         // аутоудаление? если не осталось привязанных очередей
//         $channel->exchange_declare('rabbitmq_test', 'direct', false, true, false);
        
//         switch (@$args[2])
//         {
//             case 'start':
//                 // Запукаем сервис, сконфигурированный в файле
//                 // cfg/services/@$args[2].yaml
//                 echo "Запуск сервиса '{$args[3]}':  \n";
//                 // Запускаем объект шлюза
//                 if ($service = $this->getService(@$args[3]))
//                 {
//                     $service->start();
//                 }else
//                 {
//                     echo "...сервис '{$args[3]}'  не найден\n";
//                 }
//                 break;
//             case 'sendtest':
//                     // Формируем сообщение
//                     $msgtext = "Message sent at ".date('d-m-Y H:i:s');
//                     // У сообщения есть еще ряд атрибутов, например - устойчивость к перезагрузке
//                     $msg = $this->createMessage($msgtext,
//                         array('delivery_mode' => \PhpAmqpLib\Message\AMQPMessage::DELIVERY_MODE_PERSISTENT));
//                     // Отправляем сообщение в точку доступа
//                     $channel->basic_publish($msg, 'rabbitmq_test');
//                     echo " [x] Sent {$msgtext}\n";
//                 break;
//             case 'readtest':
//                     // Слушаем и выводим на экран сообщения, пока запущена команда
                    
//                     // Очередь и биндинг объявляем тут,
//                     // т.к. они нужны только читателю.
//                     // Это приводит к тому, что сообщения, отправленные до первого запуска читателя
//                     // будут потеряны
//                     // Очередь анонимная, будет удалена после отключения
//                     // Имя получаем в момент генерации очереди
//                     // Параметры:
//                     // имя - если не задано, генерируется автоматически
//                     // Пассивная?
//                     // Устойчивая к перезагрузке?
//                     // Эксклюзивная? (доступна только из текущего подключения и удаляется после отключения)
//                     // Авто-удаление, если нет слушателя?
//                     // Не ждать подтверждения при создании?
//                     // Прочие параметры
//                     list($queue_name, ,) =$channel->queue_declare("", false, false, true, false);
//                     // Биндим анонимную очередь к точке доступа
//                     // Параметры
//                     // имя очереди
//                     // имя точки доступа
//                     // ключ маршрутизации (интерпретируется в зависимости от типа точки доступа)
//                     // не ожидать ответа сервера при объявлении?
//                     // параметры (зависят от типа точки доступа)
//                     $channel->queue_bind($queue_name, 'rabbitmq_test');
//                     // Для получения метода нам нужна callback-функция
//                     // Анонимная тоже подходит
//                     $callback = function ($msg)
//                     {
//                         echo ' [x] ', $msg->body, "\n";
//                     };
//                     // Объявляем слушателя очереди
//                     // Параметры:
//                     // Имя очереди
//                     // Тег получателя
//                     // (если не задано - генерируется сервером, это не те теги, что для маршрутизаии)
//                     // не отправлять?
//                     // Не подтверждать получение?
//                     // Эксклюзивно?
//                     //  не ждать подтверждения сервера?
//                     // $callback-функция
//                     // тикет (не понял, что это)
//                     // остальные параметры
                    
//                     $channel->basic_consume($queue_name, '', false, true, false, false, $callback);
                    
//                     // Начинаем слушать очередь (ctrl + C - наш друг)
//                     while (count($channel->callbacks))
//                     {
//                         $channel->wait();
//                     }
                    
//                 break;
//             case 'recivetest':
//                 // После перевого запуска команды, получаем все отправляемые сообщения
                
//                 // Очередь и биндинг объявляем тут,
//                 // т.к. они нужны только читателю.
//                 // Это приводит к тому, что сообщения, отправленные до первого запуска читателя
//                 // будут потеряны
//                 // Очередь именованная, сохраняется после отключения
//                 // Имя получаем в момент генерации очереди
//                 list($queue_name, ,) =$channel->queue_declare("rabbitmq_testlog", false, true, false, false);
//                 // Биндим очередь к точке доступа
//                 $channel->queue_bind($queue_name, 'rabbitmq_test');
//                 // Для получения метода нам нужна callback-функция
//                 // Анонимная тоже подходит
//                 $callback = function ($msg)
//                 {
//                     echo ' [x] ', $msg->body, "\n";
//                     // Подтверждаем получение
//                     $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
//                 };
                
//                 $channel->basic_consume($queue_name, '', false, false, false, false, $callback);
                
//                 // Начинаем слушать очередь (ctrl + C - наш друг)
//                 while (count($channel->callbacks))
//                 {
//                     $channel->wait();
//                 }
                
//                 break;
//             default:
//                 echo "Неизвестная команда\nПоддерживается sendtest, recivetest, readtest\n";
//                 break;
                
//         }
                
//         // Прибираем за собой
//         // Каналы закрывать не обязательно - закрытие соединения включает закрытие всех каналов
//         // $channel->close();
//         // Соединение тоже - деструктор обо всем позаботится
//         // $connection->close();
        
//         return true;
//     }
    
    
}
