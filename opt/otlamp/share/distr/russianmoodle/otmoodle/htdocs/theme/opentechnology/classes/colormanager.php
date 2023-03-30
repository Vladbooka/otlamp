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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * Тема СЭО 3KL. Менеджер генерации цветов Темы.
 *
 * @package    theme
 * @subpackage opentechnology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_opentechnology;

use stdClass;
use moodle_exception;

class colormanager
{
    /**
     * Текущий цвет
     * 
     * @var stdClass
     */
    private $color;
    
    /**
     * @param string $color - Код цвета, подерживаются форматы #FFFFFF, hsl(0,0%,100%), rgb(255,255,255)
     * @throws moodle_exception
     */
    public function __construct($color)
    {
        if( empty($color) )
        {
            throw new moodle_exception('Color string is empty');
        }
        $color = (string)$color;
        $this->color = $this->get_color_object($color);
    }
    
    /**
     * Конвертация объекта в строку
     *
     * @return string
     */
    public function __toString()
    {
        return $this->to_hsl();
    }
    
    /**
     * Получить 16ричный RGB-код текущего цвета
     * 
     * @return string
     */
    public function to_rgb16()
    {
        return '#'.$this->color->r16.$this->color->g16.$this->color->b16;
    }
    
    /**
     * Получить 10ричный RGB-код текущего цвета
     *
     * @return string
     */
    public function to_rgb10()
    {
        return 'rgb('.$this->color->r10.','.$this->color->g10.','.$this->color->b10.')';
    }
    
    /**
     * Получить HSL-код текущего цвета
     *
     * @return string
     */
    public function to_hsl()
    {
        return 'hsl('.$this->color->h.','.$this->color->s.'%,'.$this->color->l.'%)';
    }
    
    /**
     * Смена яркости цвета
     * 
     * @param string $lighten - Яркость цвета (+/- 100)
     */
    public function change_lighten($lighten)
    {
        // Нормализация
        $lighten = (string)$lighten;
        
        // Направление изменения яркости(увеличение/уменьшение)
        $lightenfirstsym = substr($lighten,0,1);
        
        // Установка яркости
        if ( $lightenfirstsym == '+' || $lightenfirstsym == '-' )
        {
            $this->color->l += (int)$lighten;
        } else
        {
            $this->color->l = (int)$lighten;
        }
        
        // Нормализация яркости
        if ( $this->color->l > 100 )
        {
            $this->color->l = 100;
        }
        if ( $this->color->l < 0 )
        {
            $this->color->l = 0;
        }
        
        // Генерация текущего цвета с измененной яркостью
        $this->color = $this->get_color_object(
            'hsl('.$this->color->h.','.$this->color->s.'%,'.$this->color->l.'%)', 
            true
        );
    }
    
    /**
     * Определение яркого/темного цвета
     * 
     * @param number $threshold - порог для определения светлости цвета
     * 
     * @return boolean
     */
    public function is_light($threshold = 190)
    {
        if ( $this->get_yiq() > (int)$threshold )
        {// Цвет является светлым
            return true;
        } else
        {// Цвет является темным
            return false;
        }
    }
    
    /**
     * Получение значения светлоты цвета
     * 
     * @return number
     */
    private function get_yiq()
    {
        $yiq = 255;
        if( ! empty($this->color) )
        {
            $yiq = (($this->color->r10*299)+($this->color->g10*587)+($this->color->b10*114))/1000;
        }
        return $yiq;
    }
    
    /**
     * Конвертация rgb-значений цвета в hsl-значения
     * 
     * @param int $r10 - Красный
     * @param int $g10 - Зеленый
     * @param int $b10 - Синий
     * 
     * @return number[] - Массив с ключами h,s,l и соответствующими значениями
     */
    private function rgb2hsl($r10, $g10, $b10)
    {
        $r = $r10 / 255;
        $g = $g10 / 255;
        $b = $b10 / 255;
        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $l = ($max + $min) / 2;
        if ($max == $min) {
            $h = $s = 0;
        } else
        {
            $d = $max - $min;
            $s = $l > 0.5 ? $d / (2 - $max - $min) : $d / ($max + $min);
            switch ($max) {
                case $r:
                    $h = ($g - $b) / $d + ($g < $b ? 6 : 0);
                    break;
                case $g:
                    $h = ($b - $r) / $d + 2;
                    break;
                case $b:
                    $h = ($r - $g) / $d + 4;
                    break;
            }
            $h /= 6;
        }
        return [
            'h'=>floor($h * 360),
            's'=>floor($s * 100),
            'l'=>floor($l * 100)
        ];
    }


    /**
     * Конвертация hsl-значений цвета в rgb-значения
     *
     * @param int $h - тон
     * @param int $s - насыщенность
     * @param int $l - светлота
     * @return number[] - массив с ключами r,g,b и соответствующими значениями
     */
    private function hsl2rgb($h, $s, $l)
    {
        $h /= 60;
        if ($h < 0)
        {
            $h = 6 - fmod(- $h, 6);
        }
        $h = fmod($h, 6);
        
        $s = max(0, min(1, $s / 100));
        $l = max(0, min(1, $l / 100));
        
        $c = (1 - abs((2 * $l) - 1)) * $s;
        $x = $c * (1 - abs(fmod($h, 2) - 1));
        
        if ($h < 1)
        {
            $r = $c;
            $g = $x;
            $b = 0;
        } elseif ($h < 2)
        {
            $r = $x;
            $g = $c;
            $b = 0;
        } elseif ($h < 3)
        {
            $r = 0;
            $g = $c;
            $b = $x;
        } elseif ($h < 4)
        {
            $r = 0;
            $g = $x;
            $b = $c;
        } elseif ($h < 5)
        {
            $r = $x;
            $g = 0;
            $b = $c;
        } else
        {
            $r = $c;
            $g = 0;
            $b = $x;
        }
        
        $m = $l - $c / 2;
        $r = round(($r + $m) * 255);
        $g = round(($g + $m) * 255);
        $b = round(($b + $m) * 255);
        
        return [
            'r10' => $r,
            'g10' => $g,
            'b10' => $b
        ];
    }
    
    /**
     * Получение объекта цвета
     * 
     * @param string $colorstring - строка с цветом
     * @return stdClass
     */
    public function get_color_object($colorstring, $cacheignore=false)
    {
        if ( ! empty($this->color) && !$cacheignore)
        {
            return $this->color;
        }
        
        $colorobj = new stdClass();
        
        if( strpos($colorstring, "transparent") !== false )
        {
            $colorstring = "#FFFFFF";
        }
        
        if( substr($colorstring,0,1) == '#' )
        {
            $colorobj->r16 = substr($colorstring,1,2);
            $colorobj->g16 = substr($colorstring,3,2);
            $colorobj->b16 = substr($colorstring,5,2);
            $colorobj->r10 = hexdec($colorobj->r16);
            $colorobj->g10 = hexdec($colorobj->g16);
            $colorobj->b10 = hexdec($colorobj->b16);
            $hsl = $this->rgb2hsl($colorobj->r10, $colorobj->g10, $colorobj->b10);
            $colorobj->h = $hsl['h'];
            $colorobj->s = $hsl['s'];
            $colorobj->l = $hsl['l'];
            
        } elseif( preg_match("/hsl\((.*),(.*)%,(.*)%\)/", $colorstring, $matches) )
        {
            $colorobj->h = $matches[1];
            $colorobj->s = $matches[2];
            $colorobj->l = $matches[3];
            $rgb = $this->hsl2rgb($colorobj->h, $colorobj->s, $colorobj->l);
            $colorobj->r10 = $rgb['r10'];
            $colorobj->g10 = $rgb['g10'];
            $colorobj->b10 = $rgb['b10'];
            $colorobj->r16 = str_pad(dechex($colorobj->r10), 2, "0", STR_PAD_LEFT);
            $colorobj->g16 = str_pad(dechex($colorobj->g10), 2, "0", STR_PAD_LEFT);
            $colorobj->b16 = str_pad(dechex($colorobj->b10), 2, "0", STR_PAD_LEFT);
            
        } elseif( preg_match("/rgb\((.*),(.*),(.*)\)/", $colorstring, $matches) )
        {
            $colorobj->r10 = (int)$matches[1];
            $colorobj->g10 = (int)$matches[2];
            $colorobj->b10 = (int)$matches[3];
            $colorobj->r16 = str_pad(dechex($colorobj->r10), 2, "0", STR_PAD_LEFT);
            $colorobj->g16 = str_pad(dechex($colorobj->g10), 2, "0", STR_PAD_LEFT);
            $colorobj->b16 = str_pad(dechex($colorobj->b10), 2, "0", STR_PAD_LEFT);
            $hsl = $this->rgb2hsl($colorobj->r10, $colorobj->g10, $colorobj->b10);
            $colorobj->h = $hsl['h'];
            $colorobj->s = $hsl['s'];
            $colorobj->l = $hsl['l'];
        } elseif( preg_match("/rgba\((.*),(.*),(.*),(.*)\)/", $colorstring, $matches) )
        {
            $colorobj->r10 = (int)$matches[1];
            $colorobj->g10 = (int)$matches[2];
            $colorobj->b10 = (int)$matches[3];
            $colorobj->r16 = str_pad(dechex($colorobj->r10), 2, "0", STR_PAD_LEFT);
            $colorobj->g16 = str_pad(dechex($colorobj->g10), 2, "0", STR_PAD_LEFT);
            $colorobj->b16 = str_pad(dechex($colorobj->b10), 2, "0", STR_PAD_LEFT);
            $hsl = $this->rgb2hsl($colorobj->r10, $colorobj->g10, $colorobj->b10);
            $colorobj->h = $hsl['h'];
            $colorobj->s = $hsl['s'];
            $colorobj->l = $hsl['l'];
        }
        return $colorobj;
    }
}