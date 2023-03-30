<?php
namespace otcomponent_rabbitmq;
// Адаптер инициализируется YAML или аналогичным массивом (эквивалентно)
// В массиве объявляются точки доступа, очередь и бинды. (в перспективе - типы объектов)
// Для точек доступа и очередей назначаются алиасы.
// Модуль, объявивший адаптер обращается к очередям и точкам доступа по именам алиасов
// Это дает возможность подсоединять одну и ту же логику к разным очередям
// и запускать несколько экземпляров логики в одном плагине



class adapter
{
    
    // Ссылка на родительский плагин
    protected $plugin;
    /*
     * Объект соединения RabbitMQ
     */
    protected $rabbitmq;
    /*
     * Канал  RabbitMQ
     */
    protected $channel;
    /*
     * Параметры адаптера RabbitMQ: соединение, очереди, точки доступа, маршруты
     */
    protected $options;
    
    /*
     * Инициализация
     * @$plugin - ссылка на MstpRabbitmq
     * @$config mixed - путь к конфигу или объект конфига
     */
    function __construct(main $plugin, $options)
    {
        
        $this->plugin = $plugin;


        $this->options = $options;
            
        // var_dump($this->options);
        
        //
        // Подключаемся к броккерам сообщений и иницализируем объекты броккера сообщений
        $this->rabbitmq = $this->plugin->connection($options['connection'] ?? 'default');
        // Инициализируем основные точки доступа
        
        // Объявляем канал, чтобы создать точки доступа и очереди
        $this->channel = $this->rabbitmq->channel();
        
        // Иницализируем точки обмена
        $this->initExchanges();
        // Инициализируем очереди
        $this->initQueues();
        // Инициализируем маршруты
        $this->initBinds();

    }
    
    /*
     * Деструктор
     * закрывает соединения и прибирает за собой
     */
    function __destruct()
    {
        // Закрываем rabbitmq, а он закрывает все открытые каналы
        // В этом больше нет неоходимости
        // $connection->close();
        
    }
    
    /**
     * Инициализация точек обмена, объявленных в настройках
     */
    protected function initExchanges()
    {
        if (empty($this->options['exchanges']))
        {
            // Точек обмена не объявлено
            return;
        }
        // Просматриваем точки доступа, объявленные в конфигурационном файле
        foreach ($this->options['exchanges'] as $name => $exchange)
        {
            // Объявляем точку доступа
            $this->channel->exchange_declare($exchange['name'], $exchange['type'],
                $exchange['passive'], $exchange['durable'], $exchange['auto_delete']);
        }
    }
    
    /**
     * Инициализация очередей, объявленных в настройках
     */
    protected function initQueues()
    {
        if (empty($this->options['queues']))
        {
            // Очередей не объявлено
            return;
        }
        // Просматриваем очереди, объявленные в конфигурационном файле
        foreach ($this->options['queues'] as $name => $queue)
        {
            // Объявляем очередь
            list($queue_name, ,)=$this->channel->queue_declare(
                $queue['name'],
                $queue['passive'],
                $queue['durable'],
                $queue['exclusive'],
                $queue['auto_delete'],
                $queue['nowait'],
                $queue['arguments'],
                $queue['ticket']);
            // Записываем имя анонимной очереди
            if (empty($queue['name']))
            {
                $this->options['queues'][$name]['name'] = $queue_name;
            }
        }
    }
    
    /**
     * Инициализация маршрутов, объявленных в настройках
     */
    protected function initBinds()
    {
        if (empty($this->options['binds']))
        {
            // Маршрутов не объявлено
            return;
        }
        // Просматриваем маршруты, объявленные в конфигурационном файле
        foreach ($this->options['binds'] as $bind)
        {
            if (!empty($this->options['queues'][$bind['destination']]['name']))
            {
                // Биндим очередь (получатель) к точке доступа (источник)
                $this->channel->queue_bind(
                    $this->options['queues'][$bind['destination']]['name'],
                    $this->options['exchanges'][$bind['source']]['name'],
                    $bind['routing_key'],
                    $bind['nowait'],
                    $bind['arguments'],
                    $bind['ticket']);
            }elseif (!empty($this->options['exchanges'][$bind['destination']]['name']))
            {
                // Биндим точку доступа к точке доступа
                $this->channel->exchange_bind(
                    $this->options['exchanges'][$bind['destination']]['name'],
                    $this->options['exchanges'][$bind['source']]['name'],
                    $bind['routing_key'],
                    $bind['nowait'],
                    $bind['arguments'],
                    $bind['ticket']);
                
            }else
            {
                // Ошибка: ни очередь ни точка доступа с таким внутреннем именем получателя не найдены
                // @todo: надо выкинуть исключение
            }
        }
    }
    
    /**
     * Отправка сообщений
     * другие параметры отправки пока не реализованы - не были нужны
     * @param string $exchange - внутреннее имя очереди или точки обмена в параметрах адаптера
     * @param string|array $data - передаваемое сообщение
     * @param boolean $persistent - сохранять сообщение до получения?
     */
    public function sendMessage($exchange,$data,$persistent=false)
    {
        // Формируем сообщение
        if ($persistent)
        {
            $message = $this->plugin->createMessagePersistent(array('data'=>$data));
        }else
        {
            $message = $this->plugin->createMessage(array('data'=>$data));
        }
        // Отправляем сообщение
        if (!empty($this->options['exchanges'][$exchange]['name']))
        {
            $this->channel->basic_publish($message,$this->options['exchanges'][$exchange]['name']);
        }elseif (!empty($this->options['queues'][$exchange]['name']))
        {
            // Отправляем сообщение через очередь
            $this->channel->basic_publish($message,'', $this->options['queues'][$exchange]['name']);
        }else
        {
            // Ошибка - точка обмена или очередь не заданы в настройках
            // @todo: тут надо выкинуть исключение
        }
    }
    
    /**
     * Подписать $callback на получение сообщения из внутренней очереди, объявленной в yaml
     * simple - потому что только одна $callback
     * @todo: доделать, чтобы в callback передавалась ссылка на адаптер и уже раскодированный data
     * @param unknown $queue
     * @param unknown $callback
     * @param string $consumer_tag
     * @param boolean $no_local
     * @param boolean $no_ack
     * @param boolean $exclusive
     * @param boolean $nowait
     * @param unknown $ticket
     * @param array $arguments
     */
    public function consumeSimple(
        $queue,
        $callback,
        $consumer_tag = '',
        $no_local = false,
        $no_ack = false,
        $exclusive = false,
        $nowait = false,
        $ticket = null,
        $arguments = array())
    {
        $channel = $this->channel;
        // Добавляем потребителя
        $channel->basic_consume(
            $this->options['queues'][$queue]['name'],
            $consumer_tag,
            $no_local,
            $no_ack,
            $exclusive,
            $nowait,
            $callback,
            $ticket,
            $arguments);
        // Начинаем слушать очередь (ctrl + C - наш друг)
        while (count($channel->callbacks))
        {
            $channel->wait();
        }
    }
    
    /**
     * Получить сообщения из очереди
     * @param string $queue
     * @param bool $no_ack
     * @param int $ticket
     * @throws \PhpAmqpLib\Exception\AMQPTimeoutException if the specified operation timeout was exceeded
     * @return mixed
     */
    public function basic_get($queue = '', $no_ack = false, $ticket = null)
    {
        $q = ! empty($this->options['queues'][$queue]['name']) ? $this->options['queues'][$queue]['name'] : '';
        return $this->channel->basic_get($q, $no_ack, $ticket);
    }
    
    /**
     * Подтвердить одно или несколько сообщений
     *
     * @param string $delivery_tag
     * @param bool $multiple
     */
    public function basic_ack($delivery_tag, $multiple = false)
    {
        $this->channel->basic_ack($delivery_tag, $multiple);
    }
    
    /**
     * Запросить закрытие канала
     * @param int $reply_code
     * @param string $reply_text
     * @param array $method_sig
     * @throws \PhpAmqpLib\Exception\AMQPTimeoutException if the specified operation timeout was exceeded
     * @return mixed
     */
    public function close_channel($reply_code = 0, $reply_text = '', $method_sig = [0, 0])
    {
        return $this->channel->close($reply_code, $reply_text, $method_sig);
    }
    
    /**
     * Отправка сообщения в очередь
     *
     * @param AMQPMessage $msg
     * @param string $exchange
     * @param string $routing_key
     * @param bool $mandatory
     * @param bool $immediate
     * @param int $ticket
     */
    public function basic_publish(
        $msg,
        $exchange = '',
        $routing_key = '',
        $mandatory = false,
        $immediate = false,
        $ticket = null
    )
    {
        if( ! empty($this->options['exchanges'][$exchange]['name']) )
        {
            $ex = $this->options['exchanges'][$exchange]['name'];
        } else
        {
            $ex = '';
        }
        $this->channel->basic_publish($msg, $ex, $routing_key, $mandatory, $immediate, $ticket);
    }
    
    
    public function consumeCustomCondition(
        $queue,
        $callback,
        $consumer_tag = '',
        $no_local = false,
        $no_ack = false,
        $exclusive = false,
        $nowait = false,
        $ticket = null,
        $arguments = [],
        callable $condition_callback)
    {
        $channel = $this->channel;
        // Добавляем потребителя
        $channel->basic_consume(
            $this->options['queues'][$queue]['name'],
            $consumer_tag,
            $no_local,
            $no_ack,
            $exclusive,
            $nowait,
            $callback,
            $ticket,
            $arguments);
        // Начинаем слушать очередь (ctrl + C - наш друг)
        while ($condition_callback() === false)
        {
            $channel->wait();
        }
    }
}
