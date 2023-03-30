<?php
require_once '../../../../config.php';
$PAGE->set_context(context_system::instance());
$PAGE->set_url('/local/opentechnology/component/customforms/test.php');

echo $OUTPUT->header();

$corret = "
class:
  header:
      type: 'header'
      label: 'Заполните анкету'
  lastname:
      type: 'text'
      label: 'Фамилия'
      rules: [required]
  firstname:
      type: 'text'
      label: 'Имя'
      rules: [required]
  middlename:
      type: 'text'
      label: 'Отчество'
      rules: [required]
  sex:
      type: 'select'
      label: 'Пол'
      options: ['Мужской', 'Женский']
      rules: [required]
  birthday:
      type: 'date'
      filter: 'int'
      label: 'Дата рождения'
      options: {'startyear' : 1970, 'stopyear' : 2018}
  citizenship:
      type: 'country'
      label: 'Гражданство'
      rules: [required]
  city:
      type: 'text'
      label: 'Страна, город'
      rules: [required]
  address:
      type: 'textarea'
      label: 'Место жительства (указать почтовый индекс, адрес прописки)'
      options: {'rows' : 3}
      rules: [required]
  lastedu:
      type: 'text'
      label: 'Предыдущее образование'
      rules: [required]
  lasteduyear:
      type: 'text'
      label: 'Год окончания'
      rules: [required]
  speciality:
      type: 'select'
      label: 'Наименование специальности/направления подготовки'
      options: ['Прикладная информатика', 'Экономика', 'Менеджмент']
      rules: [required]
  phone:
      type: 'text'
      label: 'Контактный домашний телефон (с кодом города)'
      rules: [required]
  phonecell:
      type: 'text'
      label: 'Контактный сотовый телефон'
      rules: [required]
  email:
      type: 'text'
      label: 'Контактный e-mail'
      rules: [required]
  permitprocessingpersonaldata:
      type: 'checkbox'
      label: 'Согласен(а) передать на обработку личные данные'
      rules: [required]
  submit:
      type: 'submit'
      label: 'Отправить данные'
";
echo $OUTPUT->footer();