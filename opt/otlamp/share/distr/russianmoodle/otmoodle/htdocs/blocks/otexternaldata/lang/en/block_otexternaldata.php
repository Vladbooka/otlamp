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

$string['pluginname'] = 'External data block';
$string['otexternaldata'] = 'External data';
$string['otexternaldata:addinstance'] = 'Add a new external data block';
$string['otexternaldata:myaddinstance'] = 'Add a new external data block to the My Moodle page';
$string['otexternaldata:managecontent'] = 'Manage content';
$string['configtitle'] = 'External data block title';


$string['content_management'] = 'Content management';
$string['content_type_header'] = 'Content type setting';
$string['content_type'] = 'Content type';
$string['content_management_apply_content_type'] = 'Apply';
$string['mustache'] = 'Mustache template';


$string['replacement_user_id'] = '{{$a->object}.{$a->property}} - id of current user';
$string['replacement_user_username'] = '{{$a->object}.{$a->property}} - username of current user';
$string['replacement_user_email'] = '{{$a->object}.{$a->property}} - email of current user';
$string['replacement_user_idnumber'] = '{{$a->object}.{$a->property}} - external id of current user';
$string['replacement_profilepage_userid'] = '{{$a->object}.{$a->property}} - profile page owner id';
$string['replacement_course_id'] = '{{$a->object}.{$a->property}} - id of current course';
$string['replacement_course_shortname'] = '{{$a->object}.{$a->property}} - short name of current course';
$string['replacement_course_idnumber'] = '{{$a->object}.{$a->property}} - external id of current course';
$string['replacement_course_category'] = '{{$a->object}.{$a->property}} - category id of current course';


$string['error_unknown_content_type'] = 'Unknown content type';
$string['error_getting_block_instance'] = 'Couldn\'t get block info';
$string['error_while_composing_config'] = 'Couldn\'t compose config: {$a}';
$string['error_prepare_content'] = 'Couldn\'t prepare content: {$a}';
$string['error_config_not_valid'] = 'Config not valid. {$a}';


$string['content_type_db_records'] = 'Database records';
$string['db_records_header'] = 'Database records config';
$string['db_records_mustache'] = 'Mustache template';
$string['db_records_mustache_help'] = '
<div> The object passed to the display template has the following properties:
<ul>
<li> items - an array with the results of a selection from external storage </li>
<li> has_items - whether the "items" array is not empty </li>
<li> count_items - the number of items in the "items" array </li>
</ul>
</div>
<div> Each item in the items array is a selection string that will have the fields specified in the sql query. In addition to the fields from the request, additional properties will be added to each element:
<ul>
<li> first_item - is it the first in the selection </li>
<li> last_item - is the last item in the selection </li>
<li> even_item - is an even item </li>
<li> odd_item - is an odd item </li>
<li> item_index_num - ordinal number of the item in the selection </li>
</ul>
For cases when you want to display a selection that has duplicate data in one field, but when displaying it, you should not repeat this data in each line, but display it separately, you can, after sorting the selection by such a field, use the following properties for decoration:
<ul>
<li> group_by_ [field name] _first_in_group - whether the element is the first in the group of duplicate values ​​for the specified field </li>
<li> group_by_ [field name] _last_in_group - whether the element is the last in the group of duplicate values ​​for the specified field </li>
<li> group_by_ [field name] _index_num - the ordinal number of the element in the group of repeated values ​​for the specified field </li>
</ul>
</div>
<div> A simple example for the case when the firstname and lastname were received in the request:
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
<div> Another example. In the request, in addition to the name (firstname) and surname (lastname), the name of the program (programmname) on which the person is trained was requested:
<PRE>
&#123;&#123;#has_items&#125;&#125;
&#123;&#123;#items&#125;&#125;
&nbsp;
&#123;&#123;#first_item&#125;&#125;
&lt;div style="color:#333; font-size:28px; font-weight:600; line-height:1.2; margin:15px auto 5px;"&gt;
    Списки персон, изучающих программы
&lt;/div&gt;
&#123;&#123;/first_item&#125;&#125;
&nbsp;
&#123;&#123;#group_by_programmname_first_in_group&#125;&#125;
&lt;div style="color:#333; font-size:16px; font-weight: 600; line-height:1.2; margin:15px auto 5px;"&gt;
    &#123;&#123;programmname&#125;&#125;
&lt;/div&gt;
&#123;&#123;/group_by_programmname_first_in_group&#125;&#125;
&nbsp;
&lt;div style="width: 100%; background-color:&#123;&#123;#odd_item&#125;&#125;#F5F5F5&#123;&#123;/odd_item&#125;&#125;&#123;&#123;#even_item&#125;&#125;#FFF&#123;&#123;/even_item&#125;&#125;"&gt;
    &#123;&#123;group_by_programmname_index_num&#125;&#125;. &#123;&#123;lastname&#125;&#125; &#123;&#123;firstname&#125;&#125;
&lt;/div&gt;
&nbsp;
&#123;&#123;#group_by_programmname_last_in_group&#125;&#125;
&lt;div style="color:#666; font-size:14px; font-weight: 400; line-height:1.2; margin:5px auto; border-top:1px solid #666; padding-top:5px;"&gt;
    Итого подписок на программу "&#123;&#123;programmname&#125;&#125;": &#123;&#123;group_by_programmname_index_num&#125;&#125;
&lt;/div&gt;
&#123;&#123;/group_by_programmname_last_in_group&#125;&#125;
&nbsp;
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
<div><a href="https://docs.moodle.org/dev/Templates#How_do_I_write_a_template.3F">How do I write a template?</a></div>';
$string['notallowedwords'] = 'Not allowed words';
$string['nosemicolon'] = 'It is forbidden to use a semicolon in a query. Only one request is allowed.';
$string['dbcon_dbtype'] = 'Database type';
$string['dbcon_host'] = 'Host';
$string['dbcon_user'] = 'Username';
$string['dbcon_password'] = 'Password';
$string['dbcon_dbname'] = 'Database name';
$string['dbcon_setupsql'] = 'SQL setup command';
$string['dbcon_setupsql_help'] = 'For example, you can input here: SET NAMES \'utf8\'';
$string['dbcon_sql'] = 'SQL';
$string['dbcon_sql_desc'] = 'Substitutions could be used for sql-query {$a}';


$string['content_type_webdav_files'] = 'WebDAV files';
$string['webdav_files_header'] = 'WebDAV files config';
$string['webdav_files_mustache'] = 'Mustache template';
$string['webdav_files_mustache_help'] = '
<div> The object passed to the display template has the following properties:
<ul>
<li> items - an array with the results of your request to external storage </li>
<li> has_items - whether the "items" array is not empty </li>
<li> count_items - the number of items in the "items" array </li>
</ul>
</div>
<div> Each item in the items array is a file from the directory you specified in the settings. The file has the following properties:
<ul>
<li> fileurl - link to download the file </li>
<li> basename - filename with extension </li>
<li> filename - filename without extension </li>
<li> extension - file extension </li>
</ul>
In addition to the fields from the request, additional properties will be added to each element:
<ul>
<li> first_item - is it the first in the selection </li>
<li> last_item - is the last item in the selection </li>
<li> even_item - is an even item </li>
<li> odd_item - is an odd item </li>
<li> item_index_num - ordinal number of the item in the selection </li>
</ul>
</div>
<div> Example:
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
<div><a href="https://docs.moodle.org/dev/Templates#How_do_I_write_a_template.3F">How do I write a template?</a></div>';
$string['webdavcon_baseUri_help'] = '<div>Example:</div><div>https://webdav.yandex.ru</div><div>Enter both the scheme and the host as it shown in the example. According to the scheme in the future, the standard connection socket, port, is automatically determined.</div>';
$string['webdavcon_baseUri'] = 'Base URI';
$string['webdavcon_userName'] = 'Username (could be empty)';
$string['webdavcon_password'] = 'Password';
$string['webdavcon_dirPath'] = 'Directory path ro read content (files) from';
$string['webdavcon_dirPath_help'] = 'If you want to display files from the root of the WebDAV server, you can specify in this field just a slash "/" (without quotes). Some WebDAV servers may adhere to strict rules when specifying a path. Check that there is a slash "/" at the end of your path, for example: "/data/{user.id}/"';
$string['webdavcon_dirPath_desc'] = 'Substitutions could be used for directory path {$a}';

