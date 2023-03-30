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
 * Тема СЭО 3KL. Страница обслуживания.
 *
 * @package    theme
 * @subpackage opentechnology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// При наличии кастомного лейаута для профиля, подключаем его вместо текущего
if ( $profilelayout = theme_opentechnology_get_profile_layout($PAGE) )
{
    include $profilelayout;
} else
{
// Get the HTML for the settings bits.
$themedata = theme_opentechnology_get_html_for_settings($OUTPUT, $PAGE);

echo $OUTPUT->render_from_template('theme_opentechnology/head', [
    'output' => $OUTPUT,
    'themedata' => $themedata
]);
?>

<body <?php echo $OUTPUT->body_attributes($themedata->additionalbodyclass); ?>>
<div id="body-inner" class="<?php echo $themedata->bodyinnerclasses; ?>">
<?php echo $OUTPUT->standard_top_of_body_html() ?>
<div id="page-wrapper">
    <div id="page" class="container-fluid">
    
        <header id="page-header" class="clearfix">
        </header>
    
        <div id="page">
        	<div class="page-wrapper">
                <?php
                    // получение контентной части
                    $templatecontext = [
                        'display_sectionheader' => false,
                        'display_page_heading' => false,
                        'display_course_content_header' => false,
                        'display_collapsiblesection_cmid' => false,
                        'display_activity_navigation' => false,
                        'display_course_content_footer' => false,
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
    
        <footer id="page-footer">
            <div class="systeminfo">
           		<?php echo $OUTPUT->standard_footer_html(); ?>
           	</div>
        </footer>
    
        <?php echo $OUTPUT->standard_end_of_body_html() ?>
    
    </div>
</div>

<?php
    echo $OUTPUT->render_from_template('theme_opentechnology/foot', [
        'output' => $OUTPUT,
        'themedata' => $themedata
    ]);
}
?>