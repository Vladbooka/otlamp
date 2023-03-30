<?php

require_once(dirname(realpath(__FILE__)) . '/cross_format_table_cell_style.php');

class dof_cross_format_table_cell
{
    protected $text='';
    protected $colspan=1;
    protected $rowspan=1;
    protected $style;
    
    public function __construct($text='')
    {
        $this->set_text((string)$text);
        $this->style = null;
    }
    
    public function set_text($text)
    {
        $this->text = (string)$text;
        return $this;
    }
    
    public function set_colspan($colspan)
    {
        $this->colspan = (int)$colspan;
        return $this;
    }
    
    public function set_rowspan($rowspan)
    {
        $this->rowspan = (int)$rowspan;
        return $this;
    }
    
    public function set_style(dof_cross_format_table_cell_style $cellstyle)
    {
        $this->style = $cellstyle;
        return $this;
    }
    
    public function get_text()
    {
        return $this->text;
    }
    
    public function get_rowspan()
    {
        return $this->rowspan;
    }
    
    public function get_colspan()
    {
        return $this->colspan;
    }
    
    public function has_style()
    {
        return ! is_null($this->style);
    }
    
    public function get_style_clone()
    {
        return clone($this->style);
    }
    
    public function get_style_attribute($deniedstyles=[])
    {
        if( is_null($this->style) )
        {
            return '';
        }
        else
        {
            return $this->style->get_style_attribute($deniedstyles);
        }
    }
    
    public function set_data($attr, $value)
    {
        $this->data[$attr] = $value;
    }
    
    public function get_data($attr)
    {
        return isset($this->data[$attr]) ? $this->data[$attr] : null;
    }
    
    public function __call($methodname, $parameters)
    {
        $style = new dof_cross_format_table_cell_style();
        if( method_exists($style, $methodname) )
        {
            if( is_null($this->style) )
            {
                $this->style = $style;
            }
            call_user_func_array([$this->style, $methodname], $parameters);
            return $this;
        } else
        {// в классе со стилями есть свой магический метод, попробуем использовать его
            try
            {
                if( is_null($this->style) )
                {
                    $this->style = $style;
                }
                $result = call_user_func_array([$this->style, $methodname], $parameters);
                if( is_scalar($result) || is_array($result) )
                {
                    return $result;
                }
            }
            catch(Exception $ex)
            {
                
            }
        }
        return $this;
    }
}