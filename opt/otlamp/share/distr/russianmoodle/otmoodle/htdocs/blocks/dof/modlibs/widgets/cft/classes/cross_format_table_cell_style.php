<?php
class dof_cross_format_table_cell_style
{
    protected $border_width = 1;
    protected $border_style = 'solid';
    protected $border_color = [0, 0, 0];
    protected $background_color = [255, 255, 255];
    protected $color = [0, 0, 0];
    protected $font_size = 14;
    protected $font_weight = 400;
    protected $vertical_align = 'top';
    protected $text_align = 'left';
    protected $word_break = 'break-all';
    protected $text_decoration = 'none';
    
    public function __construct()
    {
        
    }
    
    public function set_border_width($borderwidth)
    {
        $this->border_width = (int)$borderwidth;
    }
    
    public function set_border_color($r, $g, $b)
    {
        $this->border_color = [(int)$r, (int)$g, (int)$b];
    }
    
    public function set_background_color($r, $g, $b)
    {
        $this->background_color = [(int)$r, (int)$g, (int)$b];
    }
    
    public function set_color($r, $g, $b)
    {
        $this->color = [(int)$r, (int)$g, (int)$b];
    }
    
    public function set_font_size($fontsize)
    {
        $this->font_size = (int)$fontsize;
    }
    
    public function set_font_weight($fontweight)
    {
        $this->font_weight = $fontweight;
    }
    
    public function set_vertical_align($verticalalign)
    {
        $this->vertical_align = (string)$verticalalign;
    }
    
    public function set_text_align($textalign)
    {
        $this->text_align = (string)$textalign;
    }
    
    public function set_word_break($wordbreak)
    {
        $this->word_break = (string)$wordbreak;
    }
    
    public function set_text_decoration($textdecoration)
    {
        $this->text_decoration = (string)$textdecoration;
    }
    
    public function __call($methodname, $parameters)
    {
        if( strpos($methodname, 'get_') == 0 && property_exists(get_class($this), substr($methodname, 4)) )
        {
            $prop = substr($methodname, 4);
            return $this->$prop;
        }
        throw new dof_exception('call to undefined method');
    }
    
    public function get_style_attribute($deniedstyles=[])
    {
        $styles = [];
        
        if (!in_array('border-width', $deniedstyles))
        {
            $styles[] = 'border-width: ' .  $this->border_width . 'px';
        }
        if (!in_array('border-style', $deniedstyles))
        {
            $styles[] = 'border-style: ' . $this->border_style;
        }
        if (!in_array('border-color', $deniedstyles))
        {
            $bordercolor = '#' .
                str_pad(dechex($this->border_color[0]), 2, '0', STR_PAD_LEFT) .
                str_pad(dechex($this->border_color[1]), 2, '0', STR_PAD_LEFT) .
                str_pad(dechex($this->border_color[2]), 2, '0', STR_PAD_LEFT);
            $styles[] = 'border-color: ' . $bordercolor;
        }
        if (!in_array('background-color', $deniedstyles))
        {
            $backgroundcolor = '#' .
                str_pad(dechex($this->background_color[0]), 2, '0', STR_PAD_LEFT) .
                str_pad(dechex($this->background_color[1]), 2, '0', STR_PAD_LEFT) .
                str_pad(dechex($this->background_color[2]), 2, '0', STR_PAD_LEFT);
            $styles[] = 'background-color: ' . $backgroundcolor;
        }
        if (!in_array('color', $deniedstyles))
        {
            $color = '#' .
                str_pad(dechex($this->color[0]), 2, '0', STR_PAD_LEFT) .
                str_pad(dechex($this->color[1]), 2, '0', STR_PAD_LEFT) .
                str_pad(dechex($this->color[2]), 2, '0', STR_PAD_LEFT);
            $styles[] = 'color: ' . $color;
        }
        if (!in_array('font-size', $deniedstyles))
        {
            $styles[] = 'font-size: ' . $this->font_size . 'px';
        }
        if (!in_array('font-weight', $deniedstyles))
        {
            $styles[] = 'font-weight: ' . $this->font_weight;
        }
        if (!in_array('vertical-align', $deniedstyles))
        {
            $styles[] = 'vertical-align: ' . $this->vertical_align;
        }
        if (!in_array('text-align', $deniedstyles))
        {
            $styles[] = 'text-align: ' . $this->text_align;
        }
        if (!in_array('word-break', $deniedstyles))
        {
            $styles[] = 'word-break: ' . $this->word_break;
        }
        if (!in_array('text-decoration', $deniedstyles))
        {
            $styles[] = 'text-decoration: '. $this->text_decoration;
        }
        
        return implode('; ', $styles).' ;';
    }
}