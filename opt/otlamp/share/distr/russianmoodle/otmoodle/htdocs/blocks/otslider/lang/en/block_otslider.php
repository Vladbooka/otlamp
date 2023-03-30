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
 * Слайдер изображений. Языковые файлы.
 *
 * @package    block
 * @subpackage otslider
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// LINE SYSTEM
$string ['pluginname'] = 'Slider';
$string ['otslider:addinstance'] = 'Add new slider block';
$string ['otslider:myaddinstance'] = 'Add new block "Slider" to the page /my (My courses, Personal account control Panel)';
$string ['otslider:viewallfields'] = 'View in all custom fields slides displaying objects of type user';

// SETTINGS
$string ['config_header_main_label'] = 'Basic block settings';
$string ['config_slidername'] = 'slider Name (code)';
$string ['config_slidername_help'] = 'This string can be used as a selector to style a particular slider, no uniqueness is checked';
$string ['config_height'] = 'the Height of the slider (percent of the width slider)';
$string ['config_height_help'] = 'It is recommended to be sure to set this value. <br>
                                If the value is 0 or empty, the slider will not be displayed, unless the animation type "No animation" is selected.
                                When you combine the above two settings, slides will be displayed as high as the content of the slide.
                                This will allow you to place a slider (for example with lists) where the height of each slide will change depending on the content.';
$string ['config_proportionalheight'] = 'Proportional height';
$string ['config_proportionalheight_help'] = 'When you reduce the screen size, the slider can decrease proportionally or while keeping the height unchanged';
$string ['config_slidetype'] = 'animation Type';
$string ['slidetype_simple'] = 'No animation';
$string ['slidetype_fadein'] = ' fade-in (appearance)';
$string ['slidetype_slide'] = 'slide (flyout)';
$string ['slidetype_slideoverlay'] = ' slide-overlay (overhang)';
$string['slidetype_triple'] = 'Three images on a slide in a row';
$string ['config_parallax'] = 'Enable parallax effect';
$string ['config_parallax_help'] = 'Effect where part of the image visible in the slider is also scrolled while the page is scrolled.';
$string ['config_slidescroll'] = 'to change the slides at the best personal Finance';
$string ['config_slidescroll_help'] = ' <div>Slides will switch as the page scrolls while the slider is visible to the user.</div><div>the Order of the slides is set according to the expected moment of their appearance (the user sees first the first slide at the bottom of the page, then the last one at the top of the page).</div><div>it is Recommended to use a small number of slides (two-three) and a combination with the type of animation "appearance" (darkening).</div>';
$string ['config_slidespeed'] = 'the Interval between slides (in seconds)';
$string ['config_navigation'] = 'Show arrows to scroll';
$string ['config_navigationpoints'] = 'Display points to select slide';
$string['config_zoomview'] = 'View slider images in a modal window';
$string ['config_themeprofile'] = 'Show only in selected theme profile';
$string ['themeprofile_all'] = 'Any profile';
$string ['config_blockreplace'] = 'to Place the slider in the placeholder';
$string ['config_slidemanagerlink_label'] = 'Manage slides';
$string ['config_slidemanagerlink_emptyslides'] = ' do Not specify any slide! Please go to manage slides to add.';
$string ['config_arrowtype'] = 'arrow Style';
$string ['arrowtype_thick'] = 'Thick';
$string ['arrowtype_thin'] = 'Thin';



$string ['use_placeholder'] = ' <div>To display the slider, insert the following code in any place:  </div><div>&lt;div id="sliderplaceholder{$a}"&gt;&lt;/div&gt;</div><div> a prerequisite for displaying the slider in the specified placeholder is the presence of this block on the page.</div>';
$string['need_config'] = 'Settings are required to display the slider';

// CUSTOM STRING
$string ['title'] = 'Slider';
$string ['go_back'] = 'Go back';
$string ['slidemanager_page_title'] = 'slide Control';

$string ['slide_image_name'] = 'Image';
$string ['slide_image_descripton'] = 'Slide with the image formatted to the size of the slide';
$string ['slide_image_formsave_image_label'] = 'slide Image';
$string ['slide_image_formsave_backgroundpositiontop_label'] = 'Position the image vertically in percent';
$string ['slide_image_formsave_parallax_label'] = 'image scroll Offset factor (parallax effect, values from -100 to 100 are supported)';
$string ['slide_image_formsave_title_label'] = 'Header';
$string ['slide_image_formsave_description_label'] = 'Description';
$string ['slide_image_formsave_summary_label'] = 'Resume';
$string ['slide_image_formsave_captiontop_label'] = 'the Indentation of the text area from the top';
$string ['slide_image_formsave_captionright_label'] = 'Indent the text area to the right';
$string ['slide_image_formsave_captionbottom_label'] = 'the Indentation of the text area from the bottom';
$string ['slide_image_formsave_captionleft_label'] = 'the Indentation of the text area on the left';
$string ['slide_image_formsave_captionalign_label'] = 'text area Alignment';
$string ['slide_image_formsave_captionalign_left'] = 'Left';
$string ['slide_image_formsave_captionalign_right'] = 'Right';
$string ['slide_image_formsave_title_error_maxlen'] = 'you have Exceeded the maximum header length';
$string ['slide_image_formsave_summary_error_maxlen'] = 'you have Exceeded the maximum length of summary';
$string ['slide_image_formsave_cpationalign_error_value'] = 'Specified is not a valid value';
$string ['slide_image_formsave_backgroundpositiontop_error_range'] = 'you Must specify a value from 0 100 100';
$string ['slide_image_formsave_parallax_error_range'] = 'you Must specify a value from -100 to 100';
$string ['slide_image_delete_error_options'] = 'slide data deletion Error';
$string ['slide_image_formsave_backgroundpositiontop_error_range'] = 'Value invalid';

$string ['slide_html_name'] = 'HTML';
$string ['slide_html_htmlcode'] = 'Slide using HTML code';
$string ['slide_html_formsave_htmlcode_label'] = 'Description';
$string ['slide_html_delete_error_options'] = 'slide data deletion Error';

$string ['slide_listitems_name'] = 'List';
$string['slide_listitems_description'] = '';
$string ['slide_listitems_delete_error_options'] = 'slide data deletion Error';
$string ['slide_listitems_formsave_title_label'] = 'slide Title';
$string ['slide_listitems_formsave_background_label'] = 'Background';
$string ['slide_listitems_formsave_items_label'] = 'List (one element per string)';
$string ['slide_listitems_formsave_rendermode_label'] = 'display Method';
$string ['slide_listitems_formsave_rendermode_checkboxes'] = 'List with checkboxes';
$string ['slide_listitems_formsave_rendermode_blocks_by_grid'] = 'grid Blocks';

$string ['filtering'] = 'filter Settings';
$string ['groupon'] = 'profile Field';
$string ['g_none'] = ' Select...';
$string ['groupon_help'] = ' the Specified profile field can be used to filter users.';
$string ['filter'] = 'Must match';
$string ['filter_help'] = 'the value Specified in this field will be used to filter users (users whose profile field is filled with a value other than the specified one will not be added to the slider)';
$string ['softmatch'] = 'Use non-strict match';
$string ['softmatch_help'] = 'setting enables softer comparison when filtering: partial match allowed, not case sensitive';
$string ['auth'] = 'authorization Method';
$string ['lang'] = 'Language';


$string ['error_slider_slide_action_error_notvalid'] = 'Unknown task';
$string ['error_slider_slide_type_notvalid'] = 'Unknown slide type';
$string ['error_slider_slide_create_error'] = 'slide creation Error';
$string ['error_slider_slide_delete_error_notfound'] = 'Slide not found';
$string ['error_slider_slide_delete_error_delete'] = 'slide deletion Error';
$string ['error_slider_slide_orderup_error_notfound'] = 'Slide not found';
$string ['error_slider_slide_orderup_error_swap'] = 'slide permutation Error';
$string ['error_slider_slide_orderdown_error_notfound'] = 'Slide not found';
$string ['error_slider_slide_orderdown_error_swap'] = 'slide permutation Error';


$string ['slidemanager_formsave_slide_orderup_label'] = 'Move up';
$string ['slidemanager_formsave_slide_orderdown_label'] = 'Move down';
$string ['slidemanager_formsave_slide_delete_label'] = 'Remove';
$string ['slidemanager_formsave_confirm_label'] = 'Apply';
$string ['slidemanager_formsave_createslide_header_label'] = 'Add new slide';
$string ['slidemanager_formsave_createslide_select_select'] = 'Select slide type';
$string ['slidemanager_formsave_createslide_select_label'] = 'Add new slide';
$string ['slidemanager_formsave_createslide_submit_label'] = 'Add';
$string ['slidemanager_formsave_createslide_select_error_empty'] = 'you have Not selected the slide type';
$string ['slidemanager_formsave_createslide_select_error_notvalid'] = 'you Specified an unknown type of slide';
