<?php
////////////////////////////////////////////////////////////////////////////
//                                                                        //
// NOTICE OF COPYRIGHT                                                    //
//                                                                        //
// Dean`s Office for Moodle                                               //
// Электронный деканат                                                    //
// <http://deansoffice.ru/>                                               //
//                                                                        ////                                                                        //
// This program is free software: you can redistribute it and/or modify   //
// it under the terms of the GNU General Public License as published by   //
// the Free Software Foundation, either version 3 of the Licensen.        //
//                                                                        //
// This program is distributed in the hope that it will be useful,        //
// but WITHOUT ANY WARRANTY; without even the implied warranty of         //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the          //
// GNU General Public License for more details.                           //
//                                                                        //
// You should have received a copy of the GNU General Public License      //
// along with this program.  If not, see <http://www.gnu.org/licenses/>.  //
//                                                                        //
////////////////////////////////////////////////////////////////////////////

/**
 * Здесь происходит объявление класса формы, 
 * на основе класса формы из плагина modlib/widgets. 
 * Подключается из init.php. 
 */

// Подключаем библиотеки
require_once('lib.php');
// Подключаем библиотеку форм
$DOF->modlib('widgets')->webform();

/**
 * 
 * Класс формы для пополнения счета
 * 
 */
class dof_im_billing_refill extends dof_modlib_widgets_form
{
    private $contract;
    /**
     * @var dof_control
     */
    protected $dof;
    
    function definition()
    {
        $this->contract = $this->_customdata->contract;
        $this->dof     = $this->_customdata->dof;
        
        // Cоздаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        
        //создаем заголовок формы
        $mform->addElement('header','formtitle', $this->dof->get_string('refill_account', 'billing') );
        
        // Контракт - получатель
        $mform->addElement('hidden','toid', $this->contract);
        $mform->setType('toid', PARAM_INT);
        
        // Тип операции, входит в хэш
        $mform->addElement('hidden','type', 'bank');
        $mform->setType('type', PARAM_TEXT);
        
        // Сумма пополнения
        $mform->addElement('text', 'amount', $this->dof->get_string('account_amount','billing').':', 'size="20"');
        $mform->setType('amount', PARAM_FLOAT);
        $mform->setDefault('amount', 0);
        $mform->addRule('amount',$this->dof->get_string('account_amount_required', 'billing'), 'required',null,'client');
        
        // Дата проведения операции, входит в хэш
        $mform->addElement('date_selector', 'date', $this->dof->get_string('accentry_date','billing').':', 'size="20"');
        $mform->setType('date', PARAM_INT);
        
        // Номер операции, входит в хэш
        $mform->addElement('text', 'accentry_num', $this->dof->get_string('accentry_num','billing').':', 'size="20"');
        $mform->setType('accentry_num', PARAM_INT);
        $mform->addRule('accentry_num',$this->dof->get_string('accentry_num_required', 'billing'), 'required',null,'client');
        
        // БИК плательщика, входит в хэш
        $mform->addElement('text', 'payer_bik', $this->dof->get_string('payer_bik','billing').':', 'size="20"');
        $mform->setType('payer_bik', PARAM_INT);
        $mform->addRule('payer_bik',$this->dof->get_string('payer_bik_required', 'billing'), 'required',null,'client');
        
        // Номер счета плательщика в банке, входит в хэш
        $mform->addElement('text', 'payer_anum', $this->dof->get_string('payer_anum','billing').':', 'size="20"');
        $mform->setType('payer_anum', PARAM_INT);
        $mform->addRule('payer_anum',$this->dof->get_string('payer_anum_required', 'billing'), 'required',null,'client');

        // кнопки сохранить и отмена
        $this->add_action_buttons(true, $this->dof->get_string('save_refill','billing'));
    }
    
    /**
     *  Проверки данных формы
     */
    function validation($data, $files)
    {
        $mform = $this->_form;
        // Готовим массив ошибок
        $errors = array();

        if ( $data['toid'] < 1  )
        {// Неверно указан id логовора - получателя
            $errors['toid'] = $this->dof->get_string('error_unvalid_toid', 'billing');
        }
        if ( $data['amount'] < 0 )
        {// Неверно указана сумма
            $errors['amount'] = $this->dof->get_string('error_unvalid_amount', 'billing');
        }
        if ( $data['accentry_num'] < 1 )
        {// Неверно указан номер операции
            $errors['accentry_num'] = $this->dof->get_string('error_unvalid_accentry_num', 'billing');
        }
        if ( $data['payer_bik'] < 1 )
        {// Неверно указан бик плательщика
            $errors['payer_bik'] = $this->dof->get_string('error_unvalid_payer_bik', 'billing');
        } 
        if ( $data['payer_anum'] < 1 )
        {// Неверно указан бик плательщика
            $errors['payer_anum'] = $this->dof->get_string('error_unvalid_payer_anum', 'billing');
        }
        // возвращаем ошибки, если они есть
        return $errors;
    }
    
    /**
     * Метод обработки данных из формы
     * @return null
     */
    public function process($addvars)
    {
        if ( $this->is_cancelled() )
		{//ввод данных отменен - возвращаем на страницу просмотра детализации
		    redirect($this->dof->url_im('billing','/contract_detail.php',$addvars));
		}
		if ( $this->is_submitted() AND $data = $this->get_data() )
		{// Если получили данные и они подтверждены
		    
		    /**** ГЕНЕРАЦИЯ ХЭША ОПЕРАЦИИ ****/
		    
		    // Приводим полученную дату к стандартам деканата
		    $date = dof_usergetdate($data->date);
		    $accentrydate = dof_make_timestamp($date['year'], $date['mon'], $date['mday'], 12, 0, 0);
		    $data->date = $accentrydate;
		    
		    // Формируем массив опций операции для генерации хэша
		    $extentryopts = array();
		    $extentryopts['type'] = $data->type;
		    $extentryopts['accentry_num'] = $data->accentry_num;
		    $extentryopts['amount'] = $data->amount;
		    $extentryopts['date'] = date('d-m-Y', $data->date);
		    $extentryopts['payer_anum'] = $data->payer_anum;
		    $extentryopts['payer_bik'] = $data->payer_bik;
		    
		    // Получаем id счета по договору
		    $toid = $this->dof->modlib('billing')->get_contract_account($data->toid);
		    // Если вернули ошибку
		    if ( ! $toid )
		    {
		        $this->dof->print_error(
		                $this->dof->get_string('error_get_contract_account','billing'),
		                $this->dof->url_im('billing','/contract_detail.php',$addvars),
		                NULL, 'im', 'billing');
		    }
		    
		    // Генерируем описание операции
		    $about = 'Пополнение баланса на сумму '.$data->amount.' руб. '.
		    'от '.date('d-m-Y', $data->date);
		    
		    // Получаем id главного счета
		    $mainaccentryid = $this->dof->modlib('billing')->get_main_account_id();
		    
		    // Получаем объект операции с незаполненным полем id приказа
		    $accentryobj = $this->dof->storage('accentryes')->
		              generate_accentry_record(
		                      $mainaccentryid, // номер счета - источника
		                      $toid, // номер счета - получателя
		                      $data->amount,
		                      $data->date, 
		                      $extentryopts, // опции для генерации внешнего ключа
		                      null,
		                      $about
		              );
		    
		    /**** ДОБАВЛЕНИЕ ПРИКАЗА ПО ОПЕРАЦИИ ****/
		    
		    // Передаем в метод генерации приказа объект операции, на выходе - id созданного приказа
		    if ( is_object($accentryobj) )
		    {// Пришел объект операции - можно действовать дальше
		        
		        // Объект данных приказа
		        $accentry = new stdClass();
		        $accentry->contractid = $data->toid;
		        $accentry->amount = $accentryobj->amount;
		        $accentry->date = $accentryobj->date;
		        $accentry->extentryopts = $accentryobj->extentryopts;
		        $accentry->extentryoptshash = $accentryobj->extentryoptshash;
		        
		        // Формируем массив операций для приказа
		        $data = Array();
		        $data[] = $accentry;
		        
		        // Получаем id приказа, добавленного в систему
		        $orderid = $this->dof->modlib('billing')->refill_contract_balance($data);
		    } else 
		    {// Если вернули ошибку
		         $this->dof->print_error(
		               $this->dof->get_string('error_accentry_generate','billing'), 
		               $this->dof->url_im('billing','/contract_detail.php',$addvars),
		                NULL, 'im', 'billing');
		    }
		    
		    /**** ДОБАВЛЯЕМ ОПЕРАЦИЮ В СПРАВОЧНИК ****/
		    
		    // Проверяем успешность создания приказа
		    if ( ! $orderid )
		    {// Ошибка при создании приказа
		        $this->dof->print_error(
		               $this->dof->get_string('error_order_create','billing'), 
		               $this->dof->url_im('billing','/contract_detail.php',$addvars),
		                NULL, 'im', 'billing');
		    } else
		    {
		        // Дополняем объект операции недостающими данными
		        $accentryobj->orderid = $orderid;
		    }
		    
		    // Пытаемся добавить объект в справочник
		    if ( $this->dof->storage('accentryes')->add_accentry($accentryobj) )
		    {// Операция добавлена
		        $addvars['rsuccess'] = 1;
		        redirect ($this->dof->url_im('billing','/contract_detail.php',$addvars));
		    } else 
		    {// Ошибка при добавлении операции
		       $this->dof->print_error(
		               $this->dof->get_string('refill_error','billing'), 
		               $this->dof->url_im('billing','/contract_detail.php',$addvars),
		                NULL, 'im', 'billing');
		    }
		}
    }
}


/**
 * 
 * Класс формы для списания со счета
 * 
 */
class dof_im_billing_writeof extends dof_modlib_widgets_form
{
    private $contract;
    /**
     * @var dof_control
     */
    protected $dof;

    function definition()
    {
        $this->contract = $this->_customdata->contract;
        $this->dof     = $this->_customdata->dof;

        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        
        //создаем заголовок формы
        $mform->addElement('header','formtitle', $this->dof->get_string('writeof_account', 'billing') );
        
        // Контракт - получатель
        $mform->addElement('hidden','fromid', $this->contract);
        $mform->setType('fromid', PARAM_INT);
        
        // Тип операции
        $mform->addElement('hidden','type', 'bank');
        $mform->setType('type', PARAM_TEXT);

        // Сумма пополнения
        $mform->addElement('text', 'amount', $this->dof->get_string('account_amount','billing').':', 'size="20"');
        $mform->setType('amount', PARAM_FLOAT);
        $mform->setDefault('amount', 0);
        $mform->addRule('amount',$this->dof->get_string('account_amount_required', 'billing'), 'required',null,'client');
        
        // Дата проведения операции
        $mform->addElement('date_selector', 'date', $this->dof->get_string('accentry_date','billing').':', 'size="20"');
        $mform->setType('date', PARAM_INT);
        
        // Формируем элемент формы - иерархический select
        $programmsbcs = $mform->addElement('dof_hierselect', 'programmsbcs',
                $this->dof->get_string('programmsbcsid','billing').':<br/>'.
                $this->dof->get_string('learninghistoryid','billing').':<br/>'.
                $this->dof->get_string('ageid','billing').':<br/>'.
                $this->dof->get_string('agenum','billing').':',
                null, '<div class="col-12 px-0"></div>');
        
        // Получаем массив подписок, содержащий 2 массива
        // >select - массив для поля select (id и описание + нулевое поле)
        // >list   - список объектов подписок ( все поля подписки )
        $programmsbcsarray = $this->get_list_programmsbcs($this->contract);
        
        // Получаем статусы для периодов
        $actualstatuses = $this->dof->workflow('ages')->get_meta_list('actual');
        
        // Поле выбора подписки
        $programmsbcsselect = $programmsbcsarray['select'];
        // Поля в learninghistory
        $learninghistoryselect = array();
        // Выбор учебного периода
        $agesselect = array();
        // Выбор потока
        $agenumselect = array();

        // Получаем массив подписок для формирования select-поля
        $programmsbcslist = $programmsbcsarray['list'];
  
        /* Формируем структуру */
        
        // Подписка по контракту не выбрана или же их нет
        $learninghistoryselect[0][0] = ' - ';
        $agesselect[0][0][0] = ' - ';
        $agenumselect[0][0][0][0] = ' - ';
        
        // Для каждой подписки формируем структуру
        foreach ( $programmsbcslist as $pid => $pitem )
        {
            // Получаем информацию о периодах для текущей подписки
            $conds = array('status' => array_keys($actualstatuses), 'departmentid' => $pitem->departmentid);
            $ages = $this->dof->storage('ages')->get_records($conds, 'id ASC', 'id,name');
            
            // Получаем массив всех периодов для данного подразделения
            $allages = $this->dof->storage('ages')->
                get_records(array('departmentid' => $pitem->departmentid), 'id ASC', 'id,name');
            // Получаем массив текущих периодов
            $agenow = $this->dof->storage('ages')->get_current_ages($pitem->departmentid);
            // Получаем список истории по подписке в отсортированном виде
            $learninghistorys = $this->get_list_hidtorys_sorted($pitem, $agenow);

                // Добавляем для подписки выбор истории
                if ( empty($learninghistorys) )
                {// если истории нет , отображаем ручной ввод

                    /*
                     * Ручной ввод данных при отсутствии истории
                     */
                    
                    // Определяем пункт для ручного ввода
                    $learninghistoryselect[$pid][0] = $this->dof->get_string('custom_learninghistory','billing');                 
                    
                    if ( empty($ages) )
                    {// если периодов нет - то выведем 0. Это необходимо, поскольку связяно с багом hierselect
                            $agenumselect[$pid][0][0] = $this->dof->get_string('no_programm_ages','billing');
                            $agenumselect[$pid][0][0][0] = $this->dof->get_string('no_programm_agenums','billing');
                    } else
                    {// если периоды есть
                            
                        // Добавляем учебные периоды для ручного ввода
                        foreach ( $ages as $aid => $age )
                        {
                            // Добавляем период
                            $agesselect[$pid][0][$aid] = $age->name;
                            
                            // Получаем число параллелей для программы из подписки
                            $agenums = $this->dof->storage('programms')->get_field($pitem->programmid, 'agenums');
                            if ( empty($agenums) )
                            {// если параллелей нет - то выведем 0. Это необходимо, поскольку связяно с багом hierselect
                                $agenumselect[$pid][0][$aid][0] = $this->dof->get_string('no_programm_agenums','billing');
                            } else
                            {// если параллели есть
                                for ( $agenum = 1; $agenum <= $agenums; $agenum++ )
                                {// выдадим полный список всех параллелей для каждой подписки
                                    $agenumselect[$pid][0][$aid][$agenum] = $agenum.' ';
                                }
                            }
                        }
                    }
                    
                } else
                {// если история есть и выбрана
                    
                    // Даем возможность ручного ввода
                    $learninghistoryselect[$pid][0] = $this->dof->get_string('custom_learninghistory','billing');
                    
                    if ( empty($ages) )
                    {// если периодов нет - то выведем 0. Это необходимо, поскольку связяно с багом hierselect
                            $agenumselect[$pid][0][0] = $this->dof->get_string('no_programm_ages','billing');
                    } else
                    {// если периоды есть
                            
                        // Добавляем учебные периоды для ручного ввода
                        foreach ( $ages as $aid => $age )
                        {
                            // Добавляем период
                            $agesselect[$pid][0][$aid] = $age->name;
                            
                            // Получаем число параллелей для программы из подписки
                            $agenums = $this->dof->storage('programms')->get_field($pitem->programmid, 'agenums');
                            if ( empty($agenums) )
                            {// если параллелей нет - то выведем 0. Это необходимо, поскольку связяно с багом hierselect
                                $agenumselect[$pid][0][$aid] = $this->dof->get_string('no_programm_agenums','billing');
                            } else
                            {// если параллели есть
                                for ( $agenum = 1; $agenum <= $agenums; $agenum++ )
                                {// выдадим полный список всех параллелей для каждой подписки
                                    $agenumselect[$pid][0][$aid][$agenum] = $agenum.' ';
                                }
                            }
                        }
                        
                        // Для каждого из элемента истории добавим значение в select
                        foreach ( $learninghistorys as $lid => $litem )
                        {
                            $learninghistoryselect[$pid][$lid] = 'Параллель: '.$litem->agenum.' Период: '.$allages[$litem->ageid]->name;
                            $agesselect[$pid][$lid][$litem->ageid] = $this->dof->get_string('ageid_from_history','billing');
                            $agenumselect[$pid][$lid][$litem->ageid][$litem->agenum] = $this->dof->get_string('agenum_from_history','billing');
                        }
                    }
                }
        }
        // Добавляем опции к select.
        $programmsbcs->setOptions(array($programmsbcsselect, $learninghistoryselect,  $agesselect, $agenumselect ));
        
        // Кнопки сохранить и отмена
        $this->add_action_buttons(true, $this->dof->get_string('save_writeof','billing'));
    }

    /**
     *  Проверки данных формы
     */
    function validation($data, $files)
    {
        $mform = $this->_form;
        $errors = array();
        
        if ( $data['fromid'] < 1 )
        {// Неверно указан id договора - источника
            $errors['fromid'] = $this->dof->get_string('error_unvalid_fromid', 'billing');
        }
        if ( $data['amount'] < 0 )
        {// Неверно указана сумма
            $errors['amount'] = $this->dof->get_string('error_unvalid_amount', 'billing');
        }
        if ( $data['programmsbcs'][0] == 0 )
        {// Не указана подписка
            $errors['programmsbcs'] = $this->dof->get_string('error_unvalid_programmsbcs', 'billing');
            return $errors;
        }
        if ( $data['programmsbcs'][1] == 0 )
        {// Ручной ввод истории
            if ( $data['programmsbcs'][2] == 0 )
            {// Не указан период
                $errors['programmsbcs'] = $this->dof->get_string('error_unvalid_ageid', 'billing');
                return $errors;
            }
            if ( $data['programmsbcs'][3] == 0 )
            {// Не указана параллель
                $errors['programmsbcs'] = $this->dof->get_string('error_unvalid_agenum', 'billing');
                return $errors;
            }
        }
        // Возвращаем ошибки, если они есть
        return $errors;
    }
    
    /**
     * Функци обработки данных из формы
     * @return null
     */
    public function process($addvars)
    {
    if ( $this->is_cancelled() )
		{// Ввод данных отменен - возвращаем на страницу просмотра детализации
		    redirect($this->dof->url_im('billing','/contract_detail.php',$addvars));
		}
		
		if ( $this->is_submitted() AND $data = $this->get_data() )
		{// Если получили данные и они подтверждены
		    
		    /**** ПОЛУЧЕНИЕ ДАННЫХ ОПЕРАЦИИ ****/
		    
		    // Приводим полученную дату к стандартам деканата
		    $date = dof_usergetdate($data->date);
		    $accentrydate = dof_make_timestamp($date['year'], $date['mon'], $date['mday'], 12, 0, 0);
		    $data->date = $accentrydate;
		    
		    // Получаем id счета по договору
		    $fromid = $this->dof->modlib('billing')->get_contract_account($data->fromid);
		    
		    // Если вернули ошибку
		    if ( ! $fromid )
		    {
		        $this->dof->print_error(
		                $this->dof->get_string('error_get_contract_account','billing'),
		                $this->dof->url_im('billing','/contract_detail.php',$addvars),
		                NULL, 'im', 'billing');
		    }
		    
		    if ( $data->programmsbcs[1] == 0 )
		    {// Ручной ввод - берем данные из полей
		        
		        // ID периода
		        $ageid = $data->programmsbcs[2];
		        // Номер параллели
		        $agenum = $data->programmsbcs[3];
		        
		        // Получаем период
		        $age = $this->dof->storage('ages')->get_record(array('id' => $ageid));
		        
		    } else 
		    {// Получаем период и параллель из истории
		        
		        // Получаем объект истории
		        $history = $this->dof->storage('learninghistory')->get_record(array('id' => $data->programmsbcs[1]));
		        
		        // Если вернули ошибку
		        if ( ! $history )
		        {
		            $this->dof->print_error(
		                    $this->dof->get_string('error_accentry_generate','billing'),
		                    $this->dof->url_im('billing','/contract_detail.php',$addvars),
		                    NULL, 'im', 'billing');
		        }
		        // Получаем период
		        $age = $this->dof->storage('ages')->get_record(array('id' => $history->ageid));
		        // Если вернули ошибку
		        if ( ! $age )
		        {
		            $this->dof->print_error(
		                    $this->dof->get_string('error_accentry_generate','billing'),
		                    $this->dof->url_im('billing','/contract_detail.php',$addvars),
		                    NULL, 'im', 'billing');
		        }
		        // Номер параллели
		        $agenum = $history->agenum;
		    }

		    // Получаем объект подписки
		    $programmbc = $this->dof->storage('programmsbcs')->get_record(array('id' => $data->programmsbcs[0]));
		    // Если вернули ошибку
		    if ( ! $programmbc )
		    {
		        $this->dof->print_error(
		                $this->dof->get_string('error_accentry_generate','billing'),
		                $this->dof->url_im('billing','/contract_detail.php',$addvars),
		                NULL, 'im', 'billing');
		    }
		    
		    // Получаем программу, на которую оформлена подписка
		    $programm = $this->dof->storage('programms')->get_record(array('id' => $programmbc->programmid));
		    // Если вернули ошибку
		    if ( ! $programm )
		    {
		        $this->dof->print_error(
		                $this->dof->get_string('error_accentry_generate','billing'),
		                $this->dof->url_im('billing','/contract_detail.php',$addvars),
		                NULL, 'im', 'billing');
		    }
		    
		    // Формируем описание операции
		    $about =
		      'Списание баланса на сумму '.$data->amount.' руб. <br/>'.
		      'Программа: '.$programm->name.'<br/>'.
		      'Период: '.$age->name.'<br/>'.
		      'Параллель: '.$agenum.'<br/>';

		    // Формируем массив опций операции для генерации хэша
		    $extentryopts = array();
		    $extentryopts['type'] = $data->type;
		    $extentryopts['programm_id'] = $programmbc->programmid;
		    $extentryopts['programm_name'] = $programm->name;
		    $extentryopts['amount'] = $data->amount;
		    $extentryopts['age_id'] = $age->id;
		    $extentryopts['age_name'] = $age->name;
		    $extentryopts['age_num'] = $agenum;
		    
		    // Получаем id главного счета
		    $mainaccentryid = $this->dof->modlib('billing')->get_main_account_id();
		    
		    // Получаем объект операции с незаполненным полем id приказа и описанием
		    $accentryobj = $this->dof->storage('accentryes')->
		              generate_accentry_record(
		                      $fromid, // номер счета - источника
		                      $mainaccentryid, // номер счета - получателя
		                      $data->amount,
		                      $data->date, 
		                      $extentryopts, // опции операции
		                      null,
		                      $about
		              );
		    
		    
		    // Передаем в метод генерации приказа объект операции, на выходе - id созданного приказа
		    if ( is_object($accentryobj) )
		    {// Пришел объект операции - можно действовать дальше
		        
		        // Объект данных приказа
		        $accentry = new stdClass();
		        $accentry->contractid = $data->fromid;
		        $accentry->amount = $accentryobj->amount;
		        $accentry->date = $accentryobj->date;
		        $accentry->extentryopts = $accentryobj->extentryopts;
		        $accentry->extentryoptshash = $accentryobj->extentryoptshash;
		        $accentry->programsbcsid = $data->programmsbcs[0];
		        $accentry->learninghistoryid = $data->programmsbcs[1];
		        $accentry->ageid = $age->id;
		        $accentry->agenum = $agenum;
		        
		        // Формируем массив операций для приказа
		        $data = Array();
		        $data[] = $accentry;
		        
		        // Получаем id приказа, добавленного в систему
		        $orderid = $this->dof->modlib('billing')->writeof_contract_balance($data);
		    } else 
		    {// Если вернули ошибку
		         $this->dof->print_error(
		               $this->dof->get_string('error_accentry_generate','billing'), 
		               $this->dof->url_im('billing','/contract_detail.php',$addvars),
		                NULL, 'im', 'billing');
		    }
		    
		    // Проверяем успешность создания приказа
		    if ( ! $orderid )
		    {// Ошибка при создании приказа
		        $this->dof->print_error(
		               $this->dof->get_string('error_order_create','billing'), 
		               $this->dof->url_im('billing','/contract_detail.php',$addvars),
		                NULL, 'im', 'billing');
		    } else
		    {
		        // Дополняем объект операции недостающими данными
		        $accentryobj->orderid = $orderid;
		    }
		    
		    // Сбрасываем хэш для нашей операции
		    $accentryobj->extentryoptshash = null;
		    
		    // Пытаемся добавить объект в справочник
		    if ( $this->dof->storage('accentryes')->add_accentry($accentryobj) )
		    {// Операция добавлена
		        $addvars['wsuccess'] = 1;
		        redirect ($this->dof->url_im('billing','/contract_detail.php',$addvars));
		    } else 
		    {// Ошибка при добавлении операции
		       $this->dof->print_error(
		               $this->dof->get_string('writeof_error','billing'), 
		               $this->dof->url_im('billing','/contract_detail.php',$addvars),
		                NULL, 'im', 'billing');
		    }
		}
    }

    /** Получить список подписок для элемента select
     * @return array массив подписок в формате 'id' => 'Название учебной программы, на которую оформлена подписка'
     */
    private function get_list_programmsbcs($contractid)
    {
        // Готовим вывод
        $programmsbcs = array();
        
        
        // извлекаем все подписки для контракта
        $list = $this->dof->storage('programmsbcs')->get_records(array('contractid'=> $contractid ));

        if ( ! is_array($list) )
        {//получили не массив - это ошибка';
            $programmsbcs['list'] = array();
            $programmsbcs['select'] = array(0 => '--- '.$this->dof->get_string('to_select', 'billing').' ---');
            return $programmsbcs;
        }
        
        // Готовим список select
        $select = array();
        foreach ( $list as $id => $item )
        {// забиваем массив данными
            $programm = $this->dof->storage('programms')->get_record(array( 'id'=> $item->programmid ));
            $select[$id]  = $programm->name.' ['.$programm->code.']';
        }
        // оставим в списке только те объекты, на использование которых есть право
        //$permissions = array(array('plugintype'=>'storage', 'plugincode'=>'programms', 'code'=>'use'));
        //$select = $this->dof_get_acl_filtered_list($select, $permissions);
     
        asort($select);
        
        $programmsbcs['list'] = $list;
        $programmsbcs['select'] = array(0 => '--- '.$this->dof->get_string('to_select','billing').' ---') + $select;
        return $programmsbcs;
    }
    
    /** Получить список истории по подписке
     * @param $pitem - объект подписки
     * @return array массив истории по подписке
     */
    private function get_list_hidtorys_sorted($pitem, $agenow)
    {
        // Массив - результат 
        $result = array();
        
        // Получаем историю по текущей подписке
        $learninghistorys = $this->dof->storage('learninghistory')->get_records(array('programmsbcid' => $pitem->id));
        
        if ( empty($learninghistorys) )
        {// Если нет истории
            return $result;
        }
        
        // Для каждого элемента массива текущих периодов
        if (is_array($agenow))
        {
            foreach ( $agenow as $aid => $aitem )
            {
                foreach ( $learninghistorys as $lid => $litem )
                {
                    // Если среди истории нашли элемент с текущим периодом
                    if ( $litem->ageid == $aid )
                    {
                        $result[$lid] = $litem;
                        unset($learninghistorys[$lid]);
                    }
                }
            }
        }
        return $result + $learninghistorys;
    }
}

?>