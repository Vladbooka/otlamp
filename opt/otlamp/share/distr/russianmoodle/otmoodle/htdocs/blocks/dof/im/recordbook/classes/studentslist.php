<?php
////////////////////////////////////////////////////////////////////////////
//                                                                        //
// NOTICE OF COPYRIGHT                                                    //
//                                                                        //
// Dean`s Office for Moodle                                               //
// Электронный деканат                                                    //
// <http://deansoffice.ru/>                                               //
//                                                                        //
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
 * Собирает данные для главной страницы дневника
 *
 */
class dof_im_recordbook_studentslist
{
    /**
     * Принцип работы по крупному:
     *
     * входные данные:
     *  id того, кто смотрит страницу
     * 0. Получаем все договоры, в которых смотрящий является
     * учеником, представителем или куратором.
     * 1. Перебираем договоры.
     *      0. Создаем заготовку результирующего массива.
     *         array([studentid] => contracts)
     *      1. Берем договор.
     *      2. Получаем все подписки ученика на потоки.
     *      3. Оставляем подписки, которые относятся
     *         к текущему учебному периоду.
     *      4. Перебираем потоки.
     *          1. Берем поток
     *          2. Получаем подписку на программу.
     *          3. Находим контракт в результирующем массиве.
     *          4. Если еще нет, то добавляем к объекту контракт свойство
     *             contract->programms = array(programm).
     *          5. Записываем в свойство progamm->courses = array(course)
     *             дисциплину, которую получем по programmitemid.
     * 2. Создаем объект для темплатера.
     * 3. Рисуем страницу.
     */


    /**
     * @var dof_control
     */
    protected $dof;

    /**
     * Хранит все информацию,
     * необходимую для рисования таблицы
     * структура:
     * array([studentid][person] = запись из таблицы persons
     * array([studentid][contracts] =
     *    array([contractid] = $contract -> {свойства объекта контракт}
     *                       $contract -> programms =
     *       array([programmid] = $programm -> {свойства объекта программа}
     *                            $programm -> programmitems =
     *          array([programmitemid] = объект дисциплины)
     *            )
     *         )
     *      )
     * @var array
     */
    public $data = array();

    /**
     * Конструктор
     * @param dof_control $dof - методы ядра системы
     * @return void
     */
    public function __construct(dof_control $dof)
    {
        $this->dof = $dof;
    }

    /**
     * Возвращает накопленные данные
     * @return array
     */
    public function get_data()
    {
        return $this->data;
    }

    /**
     * Добавляет студентов и их контракты в $this->data.
     * Создает структуру данных,
     * которую надо заполнять информацией.
     * @param $userid - id пользователя, который смотрит
     * @return bool
     */
    public function set_data($clientid)
    {
        if ( ! $this->dof->storage('persons')->is_exists($clientid) )
        {// клиента нет в базе, никаких данных собрать не получится
            $this->dof->print_error('no_base_data', $this->dof->url_im('recordbook'), (int)$clientid, 'im', 'recordbook');
        }
        //получаем все контракты пользователя
        $contracts = $this->get_allstudents_contracts($clientid);
        if ( is_array($contracts) AND ! empty($contracts) )
        {//контракты есть
            foreach ( $contracts as $one )
            {//перебираем их и добавляем в структуру
                if ( ! $this->add_contract($one->studentid, $one) )
                {//не добавили';
                    return false;
                }
            }
            //все контракты добавлены
            return true;
        }else
        {//контрактов нет
            return true;
        }
    }
    /**
     * Заполняет созданную структуру данными
     * представленных в ней студентов
     * @param int $ageid - id текущего периода
     * @return bool
     */
    public function add_data()
    {
        //перебираем студентов';
        foreach ( $this->data as $uid => $one )
        {//перебираем студентов и заполняем
            //$this->data информацией
            if ( ! $cpassed = $this->get_student_cpasseds($uid) )
            {//не получили учебные потоки студента'.$uid;
                //в текущем периоде студент не учится
                continue;
            }
            if ( ! $this->add_student_cpasseds($cpassed) )
            {//не удалось добавить дисциплины';
                return false;
            }
        }
        //ksort($this->data);
        return true;
    }

    /**
     * Возвращает все контракты, в которых пользователь
     * числиться как студент или как законный представитель
     * @param $userid - id пользователя. Если null, то используется $USER->id.
     * @return array массив контрактов. Сначала идут контракты,
     * в которых пользователь является студентом, потом те,
     * в которых он является законным представителем
     * Если ничего не найдено - возваращается пустой массив
     */
    private function get_allstudents_contracts($userid = null)
    {
        //получаем все договоры,
        //в которых смотрящий проходит
        //учеником,
        //законным представителем или куратором
        //вернуть массив договоров, в перечисленном порядке
        //договоры, в которых он не является учеником,
        //надо отсортировать по ФИО учеников??????
        $contracts = array();
        if ( $iamstudent = $this->dof->storage('contracts')->get_list_by_student($userid) )
        {//получаем контракты смотрящего как студента
            $contracts = array_merge($contracts, $iamstudent);
        }
        if( ! empty($contracts) )
        {//клиент - студент, запомним его id
            $clientid = current($contracts)->studentid;
        }
        if ( $mystudents = $this->dof->storage('contracts')->get_list_by_client($userid) )
        {//получаем все контракты на учеников смотрящего
            foreach ( $mystudents as $key => $one )
            {//перебираем студентов, которых представляет клиент
                if ( isset($clientid) AND $one->studentid == $clientid)
                {//ищем записи, в которых он сам является студентом
                    //если они есть - удаляем их
                    unset($mystudents[$key]);
                }
            }
            $contracts = array_merge($contracts, $mystudents);
        }
        return $contracts;
    }

    /**
     * Получаем все подписки на изучение дисциплин
     * в текущем периоде
     * @param int $studentid - id студента
     * @return mixed - array - массив записей из
     * таблицы cpassed или bool  false
     */
    private function get_student_cpasseds($studentid)
    {
        //получаем все подписки одного студента
        $data = new stdClass();
        $data->studentid = $studentid;
        $data->status = array('active','completed','failed','reoffset','suspend');
        if ( ! $cpasseds = $this->dof->storage('cpassed')->
            get_listing($data) )
        {//не получили
            return false;
        }
        // @todo Убрать этот foreach
        foreach ( $cpasseds as $k => $cp )
        {//перебираем контракты
            if ( ! $this->is_stream_going($cp->cstreamid, $cp->programmsbcid) )
            {//удаляем те, которые из неактивного
                // не делаем проверку на период
                //unset($cpasseds[$k]);
            }
        }
        return $cpasseds;
    }
    /**
     * Добавляем все дисциплины одного студента в
     * общую структуру данных
     * @param array $cpasseds - массив подписок на учебные
     * дисциплины текущего периода
     * @return bool - сообщает об отсутствии ошибок или наоборот
     */
    private function add_student_cpasseds($cpasseds)
    {
        if (! is_array($cpasseds) )
        {//переданы неправильные данные';
            return false;
        }

        foreach ( $cpasseds as $one )
        {//перебираем потоки и добавляем в структуру данных
            if ( ! $progsbc = $this->dof->storage('programmsbcs')->
                get($one->programmsbcid) )
            {//не получили подписку на программу';
                return false;
            }
            if ( ! $this->is_exists_contract($one->studentid, $progsbc->contractid) )
            {//пропускаем дисциплины, которые изучаются по другим контрактам';
                continue;
            }
            //все нормально - добавляем дисциплину в структуру';
            if ( ! $this->add_programmitem($one->studentid,
                $progsbc->contractid, $progsbc->programmid, $progsbc->id, $one->programmitemid, $one->id) )
            {//не удалось добавить дисциплуну';
                return false;
            }
        }
        return true;
    }

    /***** Методы добавления элементов структуры *****/

    /**
     * Добавляет запись дисциплины в
     * результирующий массив
     * @param int $studentid - id студента
     * @param int $contractid - id контракта
     * @param int $programmid - id программы
     * @param int $programmitemid - id дисциплины
     * @return bool true если дисциплина добавлена
     * или false, в ином случае
     */
    private function add_programmitem($studentid, $contractid, $programmid, $programmsbcid, $programmitemid, $cpassedid)
    {
        if ( $this->is_exists_programmitem($studentid,
            $contractid, $programmid, $programmsbcid, $cpassedid) )
        {//дисциплина уже добавлена';
            return true;
        }
        if ( ! $item = $this->dof->storage('programmitems')->get($programmitemid) )
        {//не получили запись дисциплины';
            return false;
        }
        if ( ! $this->is_exists_programmsbc($studentid, $contractid, $programmid, $programmsbcid) )
        {//нет программы - добавляем';
            if ( ! $this->add_programmsbc($studentid, $contractid, $programmid, $programmsbcid) )
            {//не удалось добавить программу
                return false;
            }
        }
        if ( ! isset($this->data[$studentid]['contracts'][$contractid]->
            programms[$programmid]->programmsbcs[$programmsbcid]->programmitems) )
        {//не определено необходимое свойство';
            //определим
            $this->data[$studentid]['contracts'][$contractid]->
            programms[$programmid]->programmsbcs[$programmsbcid]->programmitems = array();
        }
        // заносим дополнительный идентификатор в массив для того чтобы потом создать по нему ссылку
        $item->cpassedid = $cpassedid;
        //заносим дисциплину в массив данных';
        $this->data[$studentid]['contracts'][$contractid]->
        programms[$programmid]->programmsbcs[$programmsbcid]->
        programmitems[$cpassedid] = $item;
        return true;
    }

    /**
     * Добавляет программу в массив данных
     * @param int $studentid - id студента
     * @param int $contractid - id контракта
     * @param int $programmid - id программы
     * @return bool true если дисциплина добавлена
     * или false, в ином случае
     */
    private function add_programmsbc($studentid, $contractid, $programmid, $programmsbcid)
    {
        if ( $this->is_exists_programmsbc($studentid, $contractid, $programmid, $programmsbcid) )
        {//программа уже добавлена
            return true;
        }
        if ( ! $programmsbc = $this->dof->storage('programmsbcs')->get($programmsbcid) )
        {//не удалось получить запись программы
            return false;
        }
        if ( ! $this->is_exists_programm($studentid, $contractid, $programmid) )
        {//контракт не существует - добавляем
            if ( ! $this->add_programm($studentid, $contractid, $programmid) )
            {//не удалось добавить контракт
                return false;
            }
        }
        if ( ! isset($this->data[$studentid]['contracts'][$contractid]->programms[$programmid]->programmsbcs) )
        {//необходимое свойство не определено - определяем
            $this->data[$studentid]['contracts'][$contractid]->programms[$programmid]->programmsbcs = array();
        }
        //добавляет данные одной программы в
        //результирующий массив
        $this->data[$studentid]['contracts'][$contractid]->programms[$programmid]->
        programmsbcs[$programmsbc->id] = $programmsbc;
        return true;
    }

    /**
     * Добавляет программу в массив данных
     * @param int $studentid - id студента
     * @param int $contractid - id контракта
     * @param int $programmid - id программы
     * @return bool true если дисциплина добавлена
     * или false, в ином случае
     */
    private function add_programm($studentid, $contractid, $programmid)
    {
        if ( $this->is_exists_programm($studentid, $contractid, $programmid) )
        {//программа уже добавлена
            return true;
        }
        if ( ! $programm = $this->dof->storage('programms')->get($programmid) )
        {//не удалось получить запись программы
            return false;
        }
        if ( ! $this->is_exists_contract($studentid, $contractid) )
        {//контракт не существует - добавляем
            if ( ! $this->add_contract($studentid, $contractid) )
            {//не удалось добавить контракт
                return false;
            }
        }
        if ( ! isset($this->data[$studentid]['contracts'][$contractid]->programms) )
        {//необходимое свойство не определено - определяем
            $this->data[$studentid]['contracts'][$contractid]->programms = array();
        }
        //добавляет данные одной программы в
        //результирующий массив
        $this->data[$studentid]['contracts'][$contractid]->
        programms[$programm->id] = $programm;
        return true;
    }

    /**
     * Добавляем контракт в массив данных
     * @param int $studentid - id студента
     * @param mixed object $contractid - запись контракта или
     * int - id контракта
     * @return bool true если контракт добавлен
     * или false, в ином случае
     */
    private function add_contract($studentid, $contractid)
    {
        if ( is_int_string($contractid) )
        {//передан id контракта - получим его';
            if ( ! $contract = $this->dof->storage('contracts')->get($contractid) )
            {//не получили запись контракта
                return false;
            }
        }elseif( is_object($contractid) )
        {//передана запись контракта';
            $contract = clone $contractid;
        }else
        {//передано непонятно что';
            return false;
        }
        if ( $this->is_exists_contract($studentid, $contract->id) )
        {//контракт уже добавлен';
            return true;
        }
        if ( ! $this->is_exists_student($studentid) )
        {//студент отсутствует в структуре - добавляем';
            if ( ! $this->add_student($studentid) )
            {//не удалось добавить студента в массив данных'. $studentid;
                return false;
            }
        }
        if ( ! isset($this->data[$studentid]['contracts']) )
        {//не определено необходимый элемент - определяем
            $this->data[$studentid]['contracts'] = array();
        }
        $this->data[$studentid]['contracts'][$contract->id] = $contract;
        return true;
    }

    /**
     * Добавляет информацию о студенте в
     * структуру данных
     * @param int $studentid - id студента
     * @return bool true если студент добавлен
     * или false, в ином случае
     */
    private function add_student($studentid)
    {
        if ( ! $user = $this->dof->storage('persons')->get($studentid) )
        {//не получили запись студента
            return false;
        }
        $this->data[$user->id] = array('person' => $user);
        return true;
    }

    /***** Методы проверки наличия элементов структуры *****/

    /**
     * Возвращает истину, если поток принадлежит
     * к указанному учебному периоду
     * иначе возвращает ложь
     * @param int $cstreamid - id потока
     * @param $programmsbcid - id подписки на программу
     * @return bool
     */
    private function is_stream_going($cstreamid, $programmsbcid)
    {
        //получаем последнюю запись из learninghistory
        $last = $this->dof->storage('learninghistory')->
        get_actual_learning_data($programmsbcid);
        if ( ! $last )
        {//не получили запись
            return false;
        }
        //из нее берем ageid
        //и проверяем - переданный нам поток с таким же ageid или нет
        $lastageid = $this->dof->storage('ages')->get_next_ageid($last->ageid, $last->agenum);
        return $this->dof->storage('cstreams')->
        is_exists(array('id'=>(int)$cstreamid, 'ageid'=>(int)$lastageid));
    }

    /**
     * Проверяет наличие дисциплины в данных студента
     * @param int $studentid - id студента
     * @param int $contractid - id контракта
     * @param int $programmid - id программы
     * @param int $programmitemid - id дисциплины
     * @return bool
     */
    private function is_exists_programmitem($studentid, $contractid, $programmid, $programmsbcid, $cpassedid)
    {
        if ( ! $this->is_exists_programmsbc($studentid, $contractid, $programmid, $programmsbcid) )
        {//программа не найдена
            return false;
        }
        if ( ! isset($this->data[$studentid]['contracts'][$contractid]->
            programms[$programmid]->programmsbcs[$programmsbcid]->programmitems) )
        {//нет дисциплин
            return false;
        }
        return isset($this->data[$studentid]['contracts'][$contractid]->
            programms[$programmid]->programmsbcs[$programmsbcid]->programmitems[$cpassedid])
            AND
            is_object($this->data[$studentid]['contracts'][$contractid]->
                programms[$programmid]->programmsbcs[$programmsbcid]->programmitems[$cpassedid]);
    }

    /**
     * Проверяет наличие программы в данных студента
     * @param int $studentid - id студента
     * @param int $contractid - id контракта
     * @param int $programmid - id программы
     * @return bool
     */
    private function is_exists_programmsbc($studentid, $contractid, $programmid, $programmsbcid)
    {
        if ( ! $this->is_exists_programm($studentid, $contractid, $programmid) )
        {//не нашли контракта
            return false;
        }
        //получаем контракт
        $programm = $this->data[$studentid]['contracts'][$contractid]->programms[$programmid];
        if ( ! isset($programm->programmsbcs) OR
            ! is_array($programm->programmsbcs) )
        {//нет программ в контракте";
            return false;
        }
        if ( ! isset($programm->programmsbcs[$programmsbcid]) )
        {//нет такой программы в контракте
            return false;
        }
        return is_object($programm->programmsbcs[$programmsbcid]);
    }

    /**
     * Проверяет наличие программы в данных студента
     * @param int $studentid - id студента
     * @param int $contractid - id контракта
     * @param int $programmid - id программы
     * @return bool
     */
    private function is_exists_programm($studentid, $contractid, $programmid)
    {
        if ( ! $this->is_exists_contract($studentid, $contractid) )
        {//не нашли контракта
            return false;
        }
        //получаем контракт
        $contract = $this->data[$studentid]['contracts'][$contractid];
        if ( ! isset($contract->programms) OR
            ! is_array($contract->programms) )
        {//нет программ в контракте
            return false;
        }
        if ( ! isset($contract->programms[$programmid]) )
        {//нет такой программы в контракте
            return false;
        }
        return is_object($contract->programms[$programmid]);
    }

    /**
     * Проверяет наличие контракта в данных студента
     * @param int $studentid - id студента
     * @param int $contractid - id контракта
     * @return bool
     */
    private function is_exists_contract($studentid, $contractid)
    {
        if ( ! $this->is_exists_student($studentid) )
        {//не найдена ветка студента
            return false;
        }
        if ( ! isset($this->data[$studentid]['contracts']) )
        {//контракты не найдены
            return false;
        }
        if ( ! isset($this->data[$studentid]['contracts'][$contractid])  )
        {//нет нужного контракта
            return false;
        }
        return is_object($this->data[$studentid]['contracts'][$contractid]);
    }
    /**
     * Проверяет наличие в массиве данных элемента студента для
     * хранения информации о нем
     * @param int $studentid
     * @return bool true - если студент есть
     * false - если не найден
     */
    private function is_exists_student($studentid)
    {
        if ( ! $this->data )
        {//нет структуры данных
            return false;
        }
        //нашли элемент данных нужного студента
        return isset($this->data[$studentid]);
    }

    /***** Методы вывода информации на экран *****/

    /**
     * собирает структуру для templater
     * @param int $clientid - id того, кто просматривает список
     * @param int $ageid - id периода
     * @return object
     */
    public function get_output($clientid)
    {
        //упорядочиваем данные по ФИО студентов
        $this->arrange_students($clientid);
        //формируем выходной объект
        $outdata = new stdClass();
        $outdata->students = array();
        foreach ( $this->data as $stid => $student)
        {
            if ( ! isset($this->data[$stid]['contracts']) )
            {//на указанного студента нет контрактов
                continue;
            }
            $outdata->students[] = $this->get_output_student($stid, $clientid);
        }
        return $outdata;
    }

    /**
     * Возвращает программы, изучаемые студентом по
     * переданному контракту
     * @param int $studentid - id студента, данные по которому надо собрать
     * @param int $contractid - id контракта, по которому учится студент
     * @param int $clientid - id клиента, который направил студента на обучение
     * @param int $ageid - id просматриваемого периода
     * @return object
     */
    private function get_output_student($studentid, $clientid)
    {
        if ( ! isset($this->data[$studentid]) )
        {//нет такого элемента
            return array();
        }
        $one = new stdClass();
        //добавляем номер контракта и ФИО обучающегося
        $usr = $this->data[$studentid]['person'];
        $one->fullname = $this->dof->storage('persons')->get_fullname($usr);
        $one->header = $this->dof->get_string('recordbook_common_data', 'recordbook', $one->fullname);
        $one->contracts = [];
        //перебираем контракты студента и
        foreach ($this->data[$studentid]['contracts'] as $contractid => $contract )
        {//формируем выходные строки
            $one->contracts[] = $this->get_output_contract($studentid, $contractid, $clientid);
        }

        //заносим программу в массив программ текущего контракта
        return $one;
    }

    /**
     * Возвращает программы, изучаемые студентом по
     * переданному контракту
     * @param int $studentid - id студента, данные по которому надо собрать
     * @param int $contractid - id контракта, по которому учится студент
     * @param int $clientid - id клиента, который направил студента на обучение
     * @param int $ageid - id просматриваемого периода
     * @return object
     */
    private function get_output_contract($studentid, $contractid, $clientid)
    {
        if ( ! isset($this->data[$studentid]['contracts'][$contractid]) )
        {//нет такого элемента
            return [];
        }
        $cont = $this->data[$studentid]['contracts'][$contractid];
        $one = new stdClass();
        //добавляем номер контракта
        $one->num = dof_html_writer::span(
            $this->dof->get_string('contractnum', 'recordbook', $cont->num)
            );
        $one->contract_status = $cont->status;
        //добавляем дисциплины учебной программы
        $one->programms = $this->get_output_programms($studentid, $cont->id, $clientid);
        //заносим программу в массив программ текущего контракта
        return $one;
    }

    /**
     * возвращает все дисциплины и программы, изучаемые студентом по контракту
     * @param int $studentid - id студента
     * @param int $contractid - id контракта
     * @param int $clientid - id того кто смотрит страницу
     * @param int $ageid - id периода
     * @return array
     */
    private function get_output_programms($studentid, $contractid, $clientid)
    {
        $depid = optional_param('departmentid', 0, PARAM_INT);
        $addvars = array();
        $addvars['departmentid'] = $depid;
        if ( ! isset($this->data[$studentid]['contracts']
            [$contractid]->programms) )
        {//нет такого элемента
            return array();
        }
        $programms = array();
        $allprogs = $this->data[$studentid]['contracts']
        [$contractid]->programms;
        foreach ( $allprogs as $prog )
        {//перебираем элементы учебной программы
            $one = new stdClass();
            $one->programm = dof_html_writer::span(
                $this->dof->get_string('program_title', 'recordbook', $prog->name)
                );
            $one->subscription_programm = dof_html_writer::span(
                $this->dof->get_string('subscription_programm', 'recordbook', $prog->name)
                );
            $one->programm_status = $prog->status;
            //добавляем дисциплины учебной программы
            $one->programmsbcs = $this->get_output_programmscbs($studentid, $contractid, $clientid, $prog->id);
            //заносим программу в массив программ текущего контракта
            $programms[] = $one;
        }
        //возвращаем результат
        return $programms;

    }

    /**
     * возвращает все дисциплины и программы, изучаемые студентом по контракту
     * @param int $studentid - id студента
     * @param int $contractid - id контракта
     * @param int $clientid - id того кто смотрит страницу
     * @param int $ageid - id периода
     * @return array
     */
    private function get_output_programmscbs($studentid, $contractid, $clientid, $programmid)
    {
        $depid = optional_param('departmentid', 0, PARAM_INT);
        $addvars = [];
        $addvars['departmentid'] = $depid;
        if ( ! isset($this->data[$studentid]['contracts']
            [$contractid]->programms[$programmid]->programmsbcs) )
        {//нет такого элемента
            return array();
        }
        $programms = array();
        $allprogsbcs = $this->data[$studentid]['contracts']
        [$contractid]->programms[$programmid]->programmsbcs;
        foreach ( $allprogsbcs as $progsbc )
        {//перебираем элементы учебной программы
            $one = new stdClass();
            //добавляем название программы как ссылку на
            //страницу описания программы
            $addvars['programmsbcid'] = $progsbc->id;
            //ссылка на расписание
            $one->schedule_path = dof_html_writer::link(
                $this->dof->url_im('recordbook','/recordbook.php',$addvars),
                $this->dof->get_string('lesson_schedule', 'recordbook')
                );
            //ссылка на зачетную книжку (текст - Зачетная книжка)
            $one->recordbook_program = dof_html_writer::link(
                $this->dof->url_im('recordbook','/program.php',$addvars),
                $this->dof->get_string('recordbook_program', 'recordbook')
                );
            //ссылка на зачетную книжку (текст - подписка №)
            $one->programmsbc_link = dof_html_writer::link(
                $this->dof->url_im('recordbook','/program.php',$addvars),
                $this->dof->get_string('subscription', 'recordbook', $progsbc->id)
                );
            //текст подписка на программу
            $one->programmsbc = dof_html_writer::span(
                $this->dof->get_string('subscription', 'recordbook', $progsbc->id)
                );
            // рейтинг по программе
            $one->rating = dof_html_writer::link(
                    $this->dof->url_im('rtreport', '/index.php', array_merge($addvars, ['type' => 'my', 'pt' => 'im', 'pc' => 'recordbook'])),
                    $this->dof->get_string('rating', 'recordbook')
                    );
            //статус подписки на программу обучения
            $one->programmsbc_status = $progsbc->status;
            //добавляем дисциплины учебной программы
            $one->items = $this->get_output_items($studentid, $contractid, $programmid, $clientid, $progsbc->id);
            //заносим программу в массив программ текущего контракта
            $programms[] = $one;
        }
        //возвращаем результат
        return $programms;

    }

    /**
     * Возвращает список дисциплин, изучаемых в рамках программы
     * @param int $studentid - id студента
     * @param int $contractid - id контракта
     * @param int $programmid - id программы
     * @return array
     */
    private function get_output_items($studentid, $contractid, $programmid, $clientid, $progsbcid)
    {
        $depid = optional_param('departmentid', 0, PARAM_INT);
        $addvars = array();
        $addvars['departmentid'] = $depid;
        if ( ! isset($this->data[$studentid]['contracts']
            [$contractid]->programms[$programmid]->programmsbcs[$progsbcid]->programmitems) )
        {//нет такого элемента
            return array();
        }
        $items = array();
        //Добавляем ссылку на дневник';
        $param = '?programmsbcid='.$progsbcid;
        $path = $this->dof->url_im('recordbook','/recordbook.php'.$param,$addvars);
        $linkname = '<a href="'.$path.'">'.$this->dof->get_string('lesson_schedule','recordbook').'</a>';
        if ( $this->dof->storage('programmsbcs')->get_field($progsbcid,'status') != 'active' )
        {
            $linkname = '<span class=gray_link>'.$linkname.'</span>';
        }
        @$items[]->item = $linkname;
        //получаем элементы учебной программы
        $allitems = $this->data[$studentid]['contracts']
        [$contractid]->programms[$programmid]->programmsbcs[$progsbcid]->programmitems;
        foreach ( $allitems as $item )
        {//перебираем элементы учебной программы
            $one = new stdClass();
            $param = '?cpassedid='.$item->cpassedid;
            $path = $this->dof->url_im('recordbook','/discipline.php'.$param,$addvars);
            $one->item = '<a href="'.$path.'">'.$item->name.'</a>';
            if ( $this->dof->storage('cpassed')->get_field($item->cpassedid,'status') != 'active' )
            {
                $one->item = '<span class=gray_link>'.$one->item.'</span>';
            }
            //название каждого заносим в массив
            $items[] = $one;
        }
        //print_object($items);
        //возвращаем результат
        return $items;
    }
    /**
     * Упорядочивает собранную структуру
     * по ФИО студентов. ФИО кдлиента идет первым.
     * Если он есть в списке студентов
     * @param int $clientid - id того, кто просматривает страницу
     * если null, то текущий пользователь.
     * @return bool true
     */
    private function arrange_students($clientid = null )
    {
        global $USER;
        //сортируем массив по именам студентов
        uasort($this->data,'arrange_pair');
        if ( is_null($clientid) )
        {//id смотрящего не передан -
            //значит это текущий пользователь
            $clientid = $USER->id;
        }
        if ( array_key_exists($clientid, $this->data) )
        {//данные того, кто смотрит всегда первые
            //получили их
            $first = array($clientid => $this->data[$clientid]);
            //удалили из массива
            unset($this->data[$clientid]);
            //вставили в начало массива
            $this->data = $first + $this->data;
        }
        return true;
    }
}

/**
 * Сравнивает два имени
 * Используется для сортировки студентов по алфавиту
 * в $this->data класса dof_im_recordbook_studentslist
 * Вызывается функцией сортировки массива uasort,
 * которая используется в методе arrange_students
 * @param array $fullone - элемент массива
 * $this->data из класса dof_im_recordbook_studentslist
 * @param array $fulltwo - элемент массива
 * $this->data из класса dof_im_recordbook_studentslist
 * @return int -1, 0, 1.
 */
function arrange_pair($fullone, $fulltwo)
{
    global $DOF;
    $one = $fullone['person'];
    $two = $fulltwo['person'];
    $onefullname = $DOF->storage('persons')->get_fullname($one);
    $twofullname = $DOF->storage('persons')->get_fullname($two);
    return strcasecmp($onefullname, $twofullname);

}