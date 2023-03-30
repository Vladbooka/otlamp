<?php require_once ("../../config.php");
global $CFG;
require_once ($CFG->libdir . '/moodlelib.php');
?>
<div class="sc-modal-header sc-clearfix">
  <h3 class='sc-header'><?php echo get_string('course_enrolment','enrol_sitecall')?></h3>
  <div class="sc-modal-close"></div>
</div>
<div class="sc-modal-content sc-clearfix">
  <div class="sc-success-message sc-add-header"></div>
  <div class="button sc-modal-close btn btn-primary"><?php echo get_string('close','enrol_sitecall')?></div>
</div>