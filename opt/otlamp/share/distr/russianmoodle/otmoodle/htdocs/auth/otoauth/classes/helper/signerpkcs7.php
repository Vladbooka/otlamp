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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace auth_otoauth\helper;

use auth_otoauth\helper\signerinterface;
use core\notification;
use moodle_exception;
use CPStore;
use CPSigner;
use CPSignedData;
use Exception;

/**
 * Класс, реализующего подпись в формате PKCS#7 detached signature
 *
 */
class signerpkcs7 implements signerinterface
{
    /**
     * Тип усовершенствованной подписи (http://cpdn.cryptopro.ru/default.asp?url=content/cades/namespace_c_ad_e_s_c_o_m_fe49883d8ff77f7edbeeaf0be3d44c0b_1fe49883d8ff77f7edbeeaf0be3d44c0b.html)
     * @var integer
     */
    const CADES_BES = 1;
    const CADES_DEFAULT = 2;
    const CADES_T = 3;
    const CADES_X_LONG_TYPE_1 = 4;
    const PKCS7_TYPE = 5;
    
    /**
     * Пароль от контейнера с сертификатом
     * @var unknown
     */
    private $keypin = '';
    
    /**
     * Адрес службы штампов
     * @var string
     */
    private $tspaddres = null;
    
    /**
     * Запрос на поиск сертификата в контейнере (CN сертификата)
     * @var string
     */
    private $subject_name_query = '';
    
    /**
     * Тип подписи (для ЕСИА нужен PKCS7_TYPE)
     * @var integer
     */
    private $sign_type = 1;
    
    /**
     * SignerPKCS7 constructor.
     */
    public function __construct()
    {
        
    }
    
    public function set_keypin($keypin)
    {
        $this->keypin = $keypin;
    }
    
    public function set_tspaddres($tspaddres)
    {
        $this->tspaddres = $tspaddres;
    }
    
    public function set_subject_name_query($query)
    {
        $this->subject_name_query = $query;
    }
    
    public function set_sign_type($type)
    {
        $this->sign_type = $type;
    }

    public function SetupCertificate($location, $name, $mode, $find_type, $query, $valid_only, $number)
    {
        $certs = $this->SetupCertificates($location, $name, $mode);
        if ($find_type != null) {
            $certs = $certs->Find($find_type, $query, $valid_only);
            if (is_string($certs))
                return $certs;
            else
                return $certs->Item($number);
        } else {
            $cert = $certs->Item($number);
            return $cert;
        }
    }
    
    public function SetupStore($location, $name, $mode)
    {
        $store = new CPStore();
        $store->Open($location, $name, $mode);
        return $store;
    }
    
    public function SetupCertificates($location, $name, $mode)
    {
        $store = $this->SetupStore($location, $name, $mode);
        $certs = $store->get_Certificates();
        return $certs;
    }
    
    /**
     * Подпись сообщения
     * @param string $message
     * @return string
     * @throws moodle_exception
     */
    public function sign(string $message)
    {
        $cert = $this->SetupCertificate(
            2,//CURRENT_USER_STORE,
            'My',
            0, //STORE_OPEN_READ_ONLY,
            1,//CERTIFICATE_FIND_SUBJECT_NAME,
            $this->subject_name_query,
            0,
            1
        );
        if (!$cert) {
            printf("Certificate not found\n");
            return;
        }
        
        $signer = new CPSigner();
        if (!is_null($this->tspaddres)) {
            // Устанавливаем службу штампов
            $signer->set_TSAAddress($this->tspaddres);
        }
        $signer->set_Certificate($cert);
        $signer->set_KeyPin($this->keypin);
        
        $sd = new CPSignedData();
        $sd->set_ContentEncoding(1);
        $sd->set_Content(base64_encode($message));
        $sm = $sd->SignCades($signer, 1, false, 0);
        if (! $sd->VerifyCades($sm, 1, false)) {
            /**
             * @todo Добавить логирование/обработку, если верификация подписи не прошла
             */
        }
        return $sm;
    }
}