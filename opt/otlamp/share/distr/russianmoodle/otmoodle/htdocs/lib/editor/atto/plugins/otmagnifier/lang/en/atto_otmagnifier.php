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
 * Strings for component 'atto_otmagnifier', language 'en'.
 *
 * @package    atto_otmagnifier
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Magnifier';
$string['settings'] = 'Magnifier settings';
$string['clickhandler'] = 'Image click processing';
$string['clickhandler_desc'] = '
<div> If this setting is not disabled, then during content editing, when you click the "Lens" button, additional markup will be applied to the selected images in accordance with the current setting value. </div>
<div> Possible options: <ul>
<li> <strong>Disabled</strong> - click on the image will not be processed </li>
<li> <strong>Open image</strong> - the image will be opened in a separate tab (depending on browser operation, most modern browsers open a separate tab) </li>
<li> <strong>Open in a separate window</strong> - when opening an image, additional parameters will be passed in order to open the image in a separate window, and not in a tab (depends on the browser operation, most modern browsers open a separate window) </li>
<li> <strong>Open in a separate window fullscreen</strong> - in addition to the "Open in a separate window" option, a window size setting is added to match the screen size </li>
</ul>
</div>
<div> If different behavior is required from image to image, you can control the behavior manually by editing the HTML code. </div>
<div> For this, additional classes can be added to the image with the configured "Lens" tool (img tag with the "magnifier" class): <ul>
<li> <strong> magnifier-open </strong> when using this class alone, the behavior corresponds to the "Open Image" setting </li>
<li> <strong> magnifier-separate-window </strong> works only in conjunction with "magnifier-open" and in this case corresponds to the "Open in a separate window" setting </li>
<li> <strong> magnifier-fullscreen </strong> works only in conjunction with "magnifier-separate-window" and "magnifier-open" and in this case corresponds to the setting "Open in a separate window fullscreen" </li>
</ul> </div>';
$string['clickhandler_disabled'] = 'Disabled';
$string['clickhandler_open'] = 'Open image';
$string['clickhandler_openseparatewindow'] = 'Open in a separate window';
$string['clickhandler_openseparatewindowfullscreen'] = 'Open in a separate window fullscreen';
