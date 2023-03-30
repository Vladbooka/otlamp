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
 * Тема СЭО 3KL. Стандартная страница авторизации.
 *
 * @package    theme
 * @subpackage opentechnology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// При наличии кастомного лейаута для профиля, подключаем его вместо текущего
if ( $profilelayout = theme_opentechnology_get_profile_layout($PAGE, 'login_standard.php') )
{
    include $profilelayout;
} else
{

// Определение написания текста
if ( right_to_left() )
{
    $regionbsid = 'region-bs-main-and-post';
} else {
    $regionbsid = 'region-bs-main-and-pre';
}

// Получать массив свойств для элементов не требуется - уже сделано в login.php более правильно
// $themedata = theme_opentechnology_get_html_for_settings($OUTPUT, $PAGE);

echo $OUTPUT->render_from_template('theme_opentechnology/head', [
    'output' => $OUTPUT,
    'themedata' => $themedata
]);
?>

<body <?php echo $OUTPUT->body_attributes($themedata->additionalbodyclass); ?>>
<div id="body-inner" class="<?php echo $themedata->bodyinnerclasses; ?>">
<?php echo $OUTPUT->standard_top_of_body_html() ?>

<?php
    echo $themedata->collapsiblesection_htop;
    if( $pagestandardheader = theme_opentechnology_get_page_standard_header($PAGE) )
    {
        include $pagestandardheader;
    }
?>
<div class="clearfix"></div>
<div id="blocks-content-heading-wrapper">
    <div id="blocks-content-heading-position" class="container-fluid <?php echo $themedata->pageback_reg_heading_unlimit_width; ?>">
        <?php echo $OUTPUT->blocks('content-heading', ''); ?>
    </div>
</div>
<div class="page-navbar-wrapper">
    <div id="page-navbar" class="container<?php echo $themedata->widthfactorclass; ?> <?php echo $themedata->pageback_breadcrumbs_unlimit_width; ?>">
        <nav class="breadcrumb-nav"><?php echo $themedata->navbar; ?></nav>
        <div class="breadcrumb-button ml-auto d-flex"><?php echo $OUTPUT->page_heading_button(); ?></div>
        <div class="clearfix"></div>
    </div>
</div>
<?php
    echo $themedata->collapsiblesection_ctop;
?>
<div id="page-wrapper">
    <div class="container<?php echo $themedata->widthfactorclass; ?> <?php echo $themedata->pageback_content_unlimit_width; ?>">
        <div id="page" class="row<?php echo $themedata->widthfactorclass; ?>">
        	<div class="page-wrapper col-md-12">
                <div id="course-header">
                    <?php echo $OUTPUT->course_header(); ?>
                </div>
                <?php
                    // получение контентной части
                    $templatecontext = [
                        'display_sectionheader' => false,
                        'display_page_heading' => false,
                        'display_course_content_header' => true,
                        'display_collapsiblesection_cmid' => true,
                        'display_activity_navigation' => false,
                        'display_course_content_footer' => true,
                        'output' => $OUTPUT,
                        'themedata' => $themedata
                    ];
                    foreach($PAGE->blocks->get_regions() as $region)
                    {
                        $regionshortname = str_replace('-', '', $region);
                        $templatecontext['display_region_'.$regionshortname] = true;
                        $templatecontext[$regionshortname.'blocks'] = $OUTPUT->blocks($region);
                        $hascontent = $PAGE->blocks->region_has_content($region, $OUTPUT);
                        $templatecontext['has'.$regionshortname.'blocks'] = $hascontent;
                    }
                    echo $OUTPUT->render_from_template('theme_opentechnology/page-content', $templatecontext);
                ?>
        	</div>
    	</div>
    </div>
</div>
<div class="clearfix"></div>
<?php
echo $OUTPUT->render_from_template('theme_opentechnology/dock', $OUTPUT->dock());
?>
<div id="blocks-content-footing-wrapper">
    <div id="blocks-content-footing-position" class="container-fluid <?php echo $themedata->pageback_reg_footing_unlimit_width; ?>">
        <?php echo $OUTPUT->blocks('content-footing', ''); ?>
    </div>
</div>
<?php
    echo $themedata->collapsiblesection_cbot;
    if( $pagestandardfooter = theme_opentechnology_get_page_standard_footer($PAGE) )
    {
        include $pagestandardfooter;
    }
?>

<?php
    echo $OUTPUT->render_from_template('theme_opentechnology/foot', [
        'output' => $OUTPUT,
        'themedata' => $themedata
    ]);
}
?>