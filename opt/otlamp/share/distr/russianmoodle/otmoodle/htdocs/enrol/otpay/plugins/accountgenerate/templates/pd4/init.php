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
 * Шаблон ПД-4
 *
 * @package    enrol
 * @subpackage otpay
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class otpay_accountgenerate_template_pd4 extends otpay_accountgenerate_template_base
{
    /**
     * Получение html формы с макроподстановками для генерации
     * Макроподстановки вида ${name}
     *
     * @return string
     */
    public function get_html($with_additional_fields = false)
    {
        if ( ! empty($with_additional_fields) )
        {
            $add = '<li><strong>ИНН плательщика:</strong> ${payerinn}</li>
                    <li><strong>№ л/сч. плательщика:</strong> ${payerlaccount}</li>';
        }
        else 
        {
            $add = '';
        }
        
        $html = '
           <table style="border: 1px solid #000000; width: 600px;">
<tbody>
<tr style="height: 196px;">
<td style="border-bottom: 1px solid #000000; border-right: 1px solid #000000; height: 196px; width: 100px;" align="center" valign="top"><strong>Извещение</strong></td>
<td style="border-bottom: 1px solid #000000; border-right: 1px solid #000000; height: 196px; width: 490px;" valign="top">
<ul style="list-style-type: none;">
<li></li>
<li><strong>Получатель: </strong> ${recipient}</li>
<li><strong>КПП:</strong> ${kpp} <strong> ИНН:</strong> ${inn}</li>
<li><strong>ОКТМО:</strong> ${oktmo} <strong> P/сч.:</strong> ${raccount}</li>
<li><strong>Банк:</strong> ${rinn}</li>
<li><strong>БИК:</strong> ${bik} <strong> К/сч.: </strong> ${kaccount}</li>
<li><strong>Код бюджетной классификации (КБК):</strong> ${kbk}</li>
<li><strong>Платеж:</strong> ${for_account_number_course_code}</li>
<li><strong>Плательщик:</strong> ${payer}</li>
<li><strong>Адрес плательщика:</strong> ${payeraddr}</li>
'. $add .'
<li><strong>Сумма:</strong> ${amount} <br /> <strong>Дата:</strong> «___» __________ 20__ г.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong>Подпись:</strong> _______________</li>
<li></li>
</ul>
</td>
</tr>
<tr style="height: 204px;">
<td style="border-bottom: 1px solid #000000; border-right: 1px solid #000000; height: 204px; width: 100px;" align="center" valign="top"><strong>Квитанция</strong></td>
<td style="border-bottom: 1px solid #000000; border-right: 1px solid #000000; height: 204px; width: 490px;" valign="top">
<ul style="list-style-type: none;">
<li></li>
<li><strong>Получатель: </strong> ${recipient}</li>
<li><strong>КПП:</strong> ${kpp} <strong> ИНН:</strong> ${inn}</li>
<li><strong>ОКТМО:</strong> ${oktmo} <strong> P/сч.:</strong> ${raccount}</li>
<li><strong>Банк:</strong> ${rinn}</li>
<li><strong>БИК:</strong> ${bik} <strong> К/сч.: </strong> ${kaccount}</li>
<li><strong>Код бюджетной классификации (КБК):</strong> ${kbk}</li>
<li><strong>Платеж:</strong> ${for_account_number_course_code}</li>
<li><strong>Плательщик:</strong> ${payer}</li>
<li><strong>Адрес плательщика:</strong> ${payeraddr}</li>
'. $add .'
<li><strong>Сумма:</strong> ${amount} <br /> <strong>Дата:</strong> «___» __________ 20__ г.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong>Подпись:</strong> _______________</li>
<li></li>
</ul>
</td>
</tr>
</tbody>
</table>';
        
        return $html;
    }
}