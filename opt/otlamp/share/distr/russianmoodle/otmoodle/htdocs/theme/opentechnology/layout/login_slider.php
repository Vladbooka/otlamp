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
 * Тема СЭО 3KL. Страница авторизации со слайдером.
 *
 * @package    theme
 * @subpackage opentechnology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// При наличии кастомного лейаута для профиля, подключаем его вместо текущего
if ( $profilelayout = theme_opentechnology_get_profile_layout($PAGE, 'login_slider.php') )
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

<div class="loginpage_slider_images moodle-has-zindex">
<?php foreach ( $themedata->loginpage_slider_images as $imageurl ) { ?>
    <div class="loginpage_slider_image" style="background: url('<?php echo $imageurl; ?>')">
    </div>
<?php } ?>
</div>
<header id="loginpage-header" class="<?php echo $themedata->navbarclass; ?>">
	<div class="wrapper">
        <div id="h_top_wrapper" class="h_top_wrapper container-fluid">
        	<div id="h_top" class="h_top">
               	<div id="h_leftblock_wrapper" class="h_leftblock_wrapper">
               		<div class = "header_logoimage_wrappper">
                   		<?php echo $themedata->header_logoimage; ?>
                   	</div>
               	</div>
               	<div id="h_rightblock_wrapper" class="h_rightblock_wrapper  nocaret">
               		<div class="header_text"><?php echo $themedata->header_text; ?></div>
               	</div>
            </div>
       	</div>
       	<div class="clearfix"></div>
    </div>
</header>
<div class="clearfix"></div>
<div id="page-wrapper">
    <div class="container<?php echo $themedata->widthfactorclass; ?> <?php echo $themedata->pageback_content_unlimit_width; ?>">
        <div id="page" class="row<?php echo $themedata->widthfactorclass; ?>">
        	<div class="page-wrapper">
                <div id="page-content" class="justify-content-end">
                    <div id="<?php echo $regionbsid ?>" class="">
                        <section id="loginpage-region-main" class="">
                        	<input id="loginbox_tab_login" type="radio" name="loginbox_tabs" checked><label for="loginbox_tab_login"><?php print_string('login'); ?></label>
    					    <input id="loginbox_tab_signup" type="radio" name="loginbox_tabs"><label for="loginbox_tab_signup"><?php print_string('h_signup', 'theme_opentechnology'); ?></label>
                            <?php echo $OUTPUT->main_content(); ?>
                        </section>
                    </div>
            	</div>
        	</div>
    	</div>
    </div>
</div>
<div class="clearfix"></div>
<footer id="loginpage-footer">
	<div class="container<?php echo $themedata->widthfactorclass; ?>">
    	<div id="footer_wrapper" class="footer_wrapper row<?php echo $themedata->widthfactorclass; ?>">
    		<div class="wrapper container-fluid">
           		<div id="footer_content">
                   	<div id="f_rightblock_wrapper" class="desktop-last-column">
                       	<div id="copyright_wrapper" class="copyright_wrapper row">
                       		<div id="copyright" class="pull-right col-md-12 desktop-last-column">
                           		<?php echo $OUTPUT->copyright_text(); ?>
                       		</div>
                       	</div>
                   	</div>
               		<div class="clearfix"></div>
           		</div>
       		</div>
    	</div>
	</div>
</footer>

<?php
    echo $OUTPUT->render_from_template('theme_opentechnology/foot', [
        'output' => $OUTPUT,
        'themedata' => $themedata
    ]);
}
?>