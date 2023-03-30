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

/**
 * Внешние данные
 *
 * @package    block_otexternaldata
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Блок "Внешние данные"';
$string['otexternaldata'] = 'Внешние данные';
$string['otexternaldata:addinstance'] = 'Добавлять блок "Внешние данные"';
$string['otexternaldata:myaddinstance'] = 'Добавлять блок "Внешние данные" в личный кабинет';
$string['otexternaldata:managecontent'] = 'Настраивать контент блока';
$string['configtitle'] = 'Заголовок блока Внешние данные';


$string['content_management'] = 'Управление контентом';
$string['content_type_header'] = 'Настройки типа контента';
$string['content_type'] = 'Тип контента';
$string['content_management_apply_content_type'] = 'Применить';
$string['mustache'] = 'Mustache-шаблон';


$string['replacement_user_id'] = '{{$a->object}.{$a->property}} - id текущего пользователя';
$string['replacement_user_username'] = '{{$a->object}.{$a->property}} - логин текущего пользователя';
$string['replacement_user_email'] = '{{$a->object}.{$a->property}} - Email текущего пользователя';
$string['replacement_user_idnumber'] = '{{$a->object}.{$a->property}} - внешний id текущего пользователя';
$string['replacement_profilepage_userid'] = '{{$a->object}.{$a->property}} - id пользователя, имеющего отношение к просматриваемому профилю';
$string['replacement_course_id'] = '{{$a->object}.{$a->property}} - id курса, на странице которого размещен блок';
$string['replacement_course_shortname'] = '{{$a->object}.{$a->property}} - короткое имя курса, на странице которого размещен блок';
$string['replacement_course_idnumber'] = '{{$a->object}.{$a->property}} - внешний id курса, на странице которого размещен блок';
$string['replacement_course_category'] = '{{$a->object}.{$a->property}} - id категории курса, на странице которого размещен блок';


$string['error_unknown_content_type'] = 'Неизвестный тип контента';
$string['error_getting_block_instance'] = 'Не удалось получить информацию о блоке';
$string['error_while_composing_config'] = 'Не удается составить конфигурацию: {$a}';
$string['error_prepare_content'] = 'Не удается подготовить контент: {$a}';
$string['error_config_not_valid'] = 'Конфигурация не действительна. {$a}';


$string['content_type_db_records'] = 'Записи из базы данных';
$string['db_records_header'] = 'Конфигурация ';
$string['db_records_mustache'] = 'Mustache-шаблон';
$string['db_records_mustache_help'] = '
<div>Объект, передаваемый в шаблон отображения обладает следующими свойствами:
<ul>
<li>items - массив с результатами выборки из внешнего хранилища</li>
<li>has_items - является ли массив "items" не пустым</li>
<li>count_items - количество элементов в массиве "items"</li>
</ul>
</div>
<div>Каждый элемент в массиве items - это строка выборки, которая будет иметь поля, указанные в sql-запросе. Кроме полей из запроса, в каждый элемент будут добавлены дополнительные свойства:
<ul>
<li>first_item - является ли первым в выборке</li>
<li>last_item - является ли последним в выборке</li>
<li>even_item - является ли четным элементом</li>
<li>odd_item - является ли нечетным элементом</li>
<li>item_index_num - порядковый номер элемента в выборке</li>
</ul>
Для случаев, когда требуется отобразить выборку, имеющую повторяющиеся данные в одном поле, но при отображении надо не повторять эти данные в каждой строке, а вывести отдельно, можно, предварительно отсортировав выборку по такому полю, использовать для оформления следующие свойства:
<ul>
<li>group_by_[название поля]_first_in_group - является ли элемент первым в группе повторяющихся значений для указанного поля</li>
<li>group_by_[название поля]_last_in_group - является ли элемент последним в группе повторяющихся значений для указанного поля</li>
<li>group_by_[название поля]_index_num - порядковый номер элемента в группе повторяющихся значений для указанного поля</li>
</ul>
</div>
<div>Простой пример для случая, когда в запросе получили имя (firstname) и фамилию (lastname):
<PRE>
&#123;&#123;#has_items&#125;&#125;
&#123;&#123;#items&#125;&#125;
&nbsp;
&lt;div&gt;
    Имя: &#123;&#123;firstname&#125;&#125;; Фамилия: &#123;&#123;lastname&#125;&#125;;
&lt;/div&gt;
&nbsp;
&#123;&#123;/items&#125;&#125;
&#123;&#123;/has_items&#125;&#125;
&nbsp;
&#123;&#123;^has_items&#125;&#125;
    &lt;div&gt;Данных не найдено&lt;/div&gt;
&#123;&#123;/has_items&#125;&#125;
</PRE>
</div>
<div>Другой пример. В запросе помимо имени (firstname) и фамилии (lastname) было запрошено наименование программы (programmname), на которой обучается персона:
<PRE>
&#123;&#123;#has_items&#125;&#125;
&#123;&#123;#items&#125;&#125;
&nbsp;
&#123;&#123;! Следующий блок будет выведен только для первого элемента выборки &#125;&#125;
&#123;&#123;#first_item&#125;&#125;
&lt;div style="color:#333; font-size:28px; font-weight:600; line-height:1.2; margin:15px auto 5px;"&gt;
    Списки персон, изучающих программы
&lt;/div&gt;
&#123;&#123;/first_item&#125;&#125;
&nbsp;
&#123;&#123;! Следующий блок будет выведен только когда начинается новая группа повторяющихся значений в поле "Программа" (другими словами, когда начинается новая программа обучения в списке) &#125;&#125;
&#123;&#123;#group_by_programmname_first_in_group&#125;&#125;
&lt;div style="color:#333; font-size:16px; font-weight: 600; line-height:1.2; margin:15px auto 5px;"&gt;
    &#123;&#123;programmname&#125;&#125;
&lt;/div&gt;
&#123;&#123;/group_by_programmname_first_in_group&#125;&#125;
&nbsp;
&#123;&#123;! Следующий блок будет выведен для всех элементов, но строки окрашиваются в разные цвета фона в зависимости от четности строки в выборке &#125;&#125;
&lt;div style="width: 100%; background-color:&#123;&#123;#odd_item&#125;&#125;#F5F5F5&#123;&#123;/odd_item&#125;&#125;&#123;&#123;#even_item&#125;&#125;#FFF&#123;&#123;/even_item&#125;&#125;"&gt;
    &#123;&#123;group_by_programmname_index_num&#125;&#125;. &#123;&#123;lastname&#125;&#125; &#123;&#123;firstname&#125;&#125;
&lt;/div&gt;
&nbsp;
&#123;&#123;! Следующий блок будет выведен только для последнего элемента в группе повторяющихся значений (последняя персона для программы обучения, по которым будет сгруппирован список) &#125;&#125;
&#123;&#123;#group_by_programmname_last_in_group&#125;&#125;
&lt;div style="color:#666; font-size:14px; font-weight: 400; line-height:1.2; margin:5px auto; border-top:1px solid #666; padding-top:5px;"&gt;
    Итого подписок на программу "&#123;&#123;programmname&#125;&#125;": &#123;&#123;group_by_programmname_index_num&#125;&#125;
&lt;/div&gt;
&#123;&#123;/group_by_programmname_last_in_group&#125;&#125;
&nbsp;
&#123;&#123;! Следующий блок будет выведен только для самого последнего элемента выборки &#125;&#125;
&#123;&#123;#last_item&#125;&#125;
&lt;div style="color:#333; font-size:14px; font-weight: 400; line-height:1.2; margin:15px auto 5px; border-top:1px solid #333; padding-top: 5px;"&gt;
    Итого подписок на все программы: &#123;&#123;count_items&#125;&#125;
&lt;/div&gt;
&#123;&#123;/last_item&#125;&#125;
&nbsp;
&#123;&#123;/items&#125;&#125;
&#123;&#123;/has_items&#125;&#125;
&nbsp;
&#123;&#123;^has_items&#125;&#125;
    &lt;div&gt;Данных не найдено&lt;/div&gt;
&#123;&#123;/has_items&#125;&#125;
</PRE>
</div>
<div><a href="https://docs.moodle.org/dev/Templates#How_do_I_write_a_template.3F">Как писать шаблоны?</a></div>';
$string['notallowedwords'] = 'Вы используете недопустимые слова в запросе';
$string['nosemicolon'] = 'Запрещено использовать точку с запятой в запросе. Разрешено использовать только один запрос.';
$string['dbcon_dbtype'] = 'Тип базы данных';
$string['dbcon_host'] = 'Хост';
$string['dbcon_user'] = 'Логин';
$string['dbcon_password'] = 'Пароль';
$string['dbcon_dbname'] = 'Имя базы данных';
$string['dbcon_setupsql'] = 'Команда для настройки SQL';
$string['dbcon_setupsql_help'] = 'Например, здесь вы можете прописать: SET NAMES \'utf8\'';
$string['dbcon_sql'] = 'SQL-запрос';
$string['dbcon_sql_desc'] = 'Для sql-запроса можно использовать подстановки {$a}';


$string['content_type_webdav_files'] = 'Файлы по протоколу WebDAV';
$string['webdav_files_header'] = 'Конфигурация ';
$string['webdav_files_mustache'] = 'Mustache-шаблон';
$string['webdav_files_mustache_help'] = '
<div>Объект, передаваемый в шаблон отображения обладает следующими свойствами:
<ul>
<li>items - массив с результатами вашего запроса во внешнее хранилище</li>
<li>has_items - является ли массив "items" не пустым</li>
<li>count_items - количество элементов в массиве "items"</li>
</ul>
</div>
<div>Каждый элемент в массиве items - это файл из директории, которую вы указали в настройках. Файл имеет следующие свойства:
<ul>
<li>fileurl - ссылка на скачивание файла</li>
<li>basename - имя файла с расширением</li>
<li>filename - имя файла без расширения</li>
<li>extension - расширение файла</li>
</ul>
Кроме полей из запроса, в каждый элемент будут добавлены дополнительные свойства:
<ul>
<li>first_item - является ли первым в выборке</li>
<li>last_item - является ли последним в выборке</li>
<li>even_item - является ли четным элементом</li>
<li>odd_item - является ли нечетным элементом</li>
<li>item_index_num - порядковый номер элемента в выборке</li>
</ul>
</div>
<div>Пример:
<PRE>
&#123;&#123;#has_items&#125;&#125;
&#123;&#123;#items&#125;&#125;
&nbsp;
&lt;div&gt;
    &lt;a href="&#123;&#123;fileurl&#125;&#125;" target="_blank"&gt;&#123;&#123;basename&#125;&#125;&lt;/a&gt;
&lt;/div&gt;
&nbsp;
&#123;&#123;/items&#125;&#125;
&#123;&#123;/has_items&#125;&#125;
&nbsp;
&#123;&#123;^has_items&#125;&#125;
    &lt;div&gt;В вашей директории файлов не найдено&lt;/div&gt;
&#123;&#123;/has_items&#125;&#125;
</PRE>
</div>
<div><a href="https://docs.moodle.org/dev/Templates#How_do_I_write_a_template.3F">Как писать шаблоны?</a></div>';
$string['webdavcon_baseUri'] = 'Базовый url сервера';
$string['webdavcon_baseUri_help'] = '<div>Пример:</div><div>https://webdav.yandex.ru</div><div>Вводите как в примере и схему, и хост. По схеме в дальнейшем автоматически определяется стандартный сокет подключения, порт.</div>';
$string['webdavcon_userName'] = 'Имя пользователя для подключения (можно оставить пустым, если не требуется авторизация)';
$string['webdavcon_password'] = 'Пароль для подключения';
$string['webdavcon_dirPath'] = 'Путь до директории, содержимое которой требуется отобразить';
$string['webdavcon_dirPath_help'] = 'Если вы желаете отобразить файлы из корня WebDAV-сервера, вы можете указать в этом поле просто слэш "/" (без кавычек). Некоторые WebDAV-сервера могут придерживаться строгих правил при указании пути. Проверьте, чтобы на конце вашего пути стоял слэш "/", например: "/data/{user.id}/"';
$string['webdavcon_dirPath_desc'] = 'Для пути можно использовать подстановки {$a}';

