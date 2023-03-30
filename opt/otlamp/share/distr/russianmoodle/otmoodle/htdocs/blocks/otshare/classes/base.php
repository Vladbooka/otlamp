<?php

namespace block_otshare;

use html_writer;
use moodle_url;
use block_otshare\publication_types\sn_publication_interface as sn_interface;

abstract class base implements sn_interface
{
    // идентификатор инстанса блока
    protected $instanceid;
    // URL для поделиться
    protected $urltoshare;
    // текс для ссылки, отображаемой до выполнения js
    protected $placeholderlinktext;
    
    /**
     * @param int $instanceid - идентификатор инстанса блока
     * @param string $urltoshare - URL для поделиться
     * @param string $placeholderlinktext - текс для ссылки, отображаемой до выполнения js
     */
    public function __construct($instanceid, $urltoshare, $placeholderlinktext='')
    {
        if ( isset($instanceid) )
        {
            $this->instanceid = (int)$instanceid;
        }
        if ( isset($urltoshare) )
        {
            $this->urltoshare = (string)$urltoshare;
        }
        $this->placeholderlinktext = (string)$placeholderlinktext;
    }
    
    /**
     * Получить код ссылки/кнопка для поделиться
     * 
     * @return string
     */
    public function get_share()
    {
        if ( ! empty($this->placeholderlinktext) )
        {// настроено отображение аутентичных кнопок
            // формирование ссылки-плейсхолдера до выполнения js
            $sharelink = $this->create_share_link($this->placeholderlinktext);
            // формирование аутентичной кнопки
            $sharebutton = $this->create_share_button();
            // класс кнопки
            $sharetype = 'otshare_button_wrapper';
        } else
        {// отображение ссылки в нашем виде (css)
            // добавление div в ссылку
            $linksharetext = html_writer::div('', $this->get_serviceshortname().'_share');
            // формирование ссылки
            $sharelink = $this->create_share_link($linksharetext);
            // аутентичной кнопки не будет
            $sharebutton = '';
            // класс ссылки
            $sharetype = 'otshare_link_wrapper';
        }
        
        // сформированный код
        return html_writer::div(
            $sharelink . $sharebutton,
            'otshare_wrapper otshare_'.$this->get_serviceshortname().' '.$sharetype,
            [
                'data-serviceshortname' => $this->get_serviceshortname(),
                'data-shareurl' => $this->urltoshare,
                'data-buttontype' => $this->get_servicebuttontype()
            ]
        );
    }
    
    /**
     * Формирование кода кнопки 
     * 
     * @return string
     */
    protected function create_share_button()
    {
        global $PAGE;

        //Подключение js социалочки
        $PAGE->requires->js($this->get_servicejsurl());
        //Подключение нашего js-скрипта
        $PAGE->requires->js(new moodle_url('/blocks/otshare/script.js'));
        
        // Получение параметров, которые необходимо накинуть на кнопку
        $buttonparameters = $this->get_share_button_parameters();
        $buttonparameters['id'] = 'otshare_button_'.$this->get_serviceshortname().'_'.$this->instanceid;
        if ( ! empty($buttonparameters['class']) )
        {
            $buttonparameters['class'] .= ' otshare_button';
        } else
        {
            $buttonparameters['class'] = 'otshare_button';
        }

        //формирование тега, который будет заменен на кнопку
        $sharebutton = html_writer::tag(
            $this->get_service_tag(),
            '',
            $buttonparameters
        );
        
        return $sharebutton;
    }
    
    /**
     * Формирование ссылки
     * 
     * @param string $sharetext
     * @return string
     */
    protected function create_share_link($sharetext)
    {
        //формирование прямого url для шаринга
        $shareurl = new moodle_url(
            $this->get_servicelinkshareurl(),
            $this->get_share_link_parameters()
        );
        
        //формирование ссылки
        $sharelink = html_writer::link(
            $shareurl,
            (string)$sharetext,
            [
                'class' => 'otshare_link',
                'target' => '_blank'
            ]
        );
        
        return $sharelink;
    }
    
    /**
     * Вернуть ссылку для редиректа
     *
     * @return string
     */
    public function get_share_url()
    {
        return new moodle_url(
                $this->get_servicelinkshareurl(),
                $this->get_share_link_parameters()
                );
    }
    
    /**
     * Установить ссылку
     *
     * @return void
     */
    public function set_url($url)
    {
        $this->urltoshare = $url;
    }
    
    /**
     * Получение тега, который умеет обрабатывать сервис
     * 
     * @return string
     */
    protected function get_service_tag()
    {
        return 'div';
    }

    /**
     * Получение короткого названия сервиса
     * 
     * @return string
     */
    protected function get_serviceshortname()
    {
        return '';
    }
    
    /**
     * Получение URL для формирования ссылки
     * 
     * @return string
     */
    protected function get_servicelinkshareurl()
    {
        return '';
    }
    
    /**
     *  Получение массива параметров, которые можно передать в ссылку для поделиться
     *  
     *  @return array
     */
    protected function get_share_link_parameters()
    {
        return [];
    }
    
    /**
     * URL для подключения js-файла социалочки
     * 
     * @return string
     */
    protected function get_servicejsurl()
    {
        return '';
    }
    
    /**
     * Тип кнопки, понятный сервису (button, small и т.д.)
     * 
     * @return string
     */
    protected function get_servicebuttontype()
    {
        return '';
    }
    
    /**
     * Параметры, которые необходимо передать в тег кнопки, чтобы js распознал и обработал его
     * 
     * @return array
     */
    protected function get_share_button_parameters()
    {
        return [];
    }
}