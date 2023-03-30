<?php
////////////////////////////////////////////////////////////////////////////
//                                                                        //
// NOTICE OF COPYRIGHT                                                    //
//                                                                        //
// Dean`s Office for Moodle                                               //
// Электронный деканат                                                    //
// <http://deansoffice.ru/>                                               //
//                                                                        //
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

$string['title'] = 'Статусы пользовательских достижений';

$string['status:draft'] = 'Черновик';
$string['status:wait_approval'] = 'Цель |Требует одобрения';
$string['status:fail_approve'] = 'Цель |Отклонена';
$string['status:wait_completion'] = 'Цель |Ожидает достижения';
$string['status:notavailable'] = 'Достижение |Требует подтверждения';
$string['status:available'] = 'Достижение актуально';
$string['status:suspend'] = 'Достижение |Требует актуализации';
$string['status:archived'] = 'В архиве';
$string['status:deleted'] = 'Удалено';

/**
 * Права/Capabilities
 */
$string['acl_view:wait_approval'] = 'Не используется [[workflow_achievementins_view:wait_approval]]';
$string['acl_view:wait_approval/owner'] = 'Не используется [[workflow_achievementins_view:wait_approval/owner]]';
$string['acl_view:wait_completion'] = 'Не используется [[workflow_achievementins_view:wait_completion]]';
$string['acl_view:wait_completion/owner'] = 'Не используется [[workflow_achievementins_view:wait_completion/owner]]';
$string['acl_view:notavailable'] = 'Просматривать неподтвержденные достижения';
$string['acl_view/owner:notavailable'] = 'Просматривать свои неподтвержденные достижения';
