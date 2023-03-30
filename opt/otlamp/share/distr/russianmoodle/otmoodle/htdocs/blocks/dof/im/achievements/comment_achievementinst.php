<?php
// Подключаем библиотеки
require_once('lib.php');

// ID комментируемого достижения
$achievementinid = required_param('id', PARAM_INT);
$thisaddvars = $addvars;
$thisaddvars['id'] = $achievementinid;
// ID персоны
$personid = optional_param('personid', 0, PARAM_INT);
if ( $personid > 0 )
{
    $addvars['personid'] = $personid;
}

// Добавление уровня навигации
$DOF->modlib('nvg')->add_level(
    $DOF->get_string('mypage_title', 'achievements'),
    $DOF->url_im('achievements', '/my.php'),
    $addvars
    );
// Добавление уровня навигации
$DOF->modlib('nvg')->add_level(
    $DOF->get_string('commentpage_title', 'achievements'),
    $DOF->url_im('achievements', '/comment_achievementinst.php'),
    $thisaddvars
    );

$html = '';

if ( ! $DOF->im('achievements')->is_access('achievementins/view', $achievementinid) )
{// Нет доступа к просмотру достижения
    $DOF->messages->add($DOF->get_string('error_achievementins_access_view_denied', 'achievements'),'notice');
} else 
{
    $udata = $DOF->storage('achievementins')->get_formatted_data($achievementinid);
    $html .= $DOF->modlib('widgets')->print_table($udata, true);
}

$commentsoptions = [];

if ( !$DOF->storage('achievementins')->is_access('view_comments', $achievementinid) )
{// Нет доступа к просмотру комментариев - выведем ошибку
    $commentsoptions['return_comments_list'] = false;
    $commentsoptions['return_form'] = false;
    $DOF->messages->add($DOF->get_string('error_achievementins_access_viewcomments_denied', 'achievements'),'error');
}
if ( !$DOF->storage('achievementins')->is_access('create_comments', $achievementinid) ||
     !$DOF->storage('comments')->is_access('create'))
{// Нет доступа к комментированию достижения
    //не будем отображать форму редактирования
    $commentsoptions['return_form'] = false;
}

if( !$DOF->messages->errors_exists() )
{//если ошибок нет, можно предоставить форму комментирования
    $commentsoptions['return_html'] = true;
    $comments = $DOF->im('comments')->commentsform('storage', 'achievementins', $achievementinid, $thisaddvars, NULL, $commentsoptions);
    if( !empty($comments) )
    {
        $html .= dof_html_writer::div($DOF->get_string('achievementin_comments', 'achievements'));
        $html .= $comments;
    } else 
    {
        $html .= dof_html_writer::div($DOF->get_string('achievementin_no_comments', 'achievements'));
    }
}

// Печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

echo $html;

// Печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);