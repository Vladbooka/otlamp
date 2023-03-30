<?php require_once ("../../config.php");
global $CFG;
require_once ($CFG->libdir . '/moodlelib.php');
?>
<div class="sc-modal-header sc-clearfix">
  <h3><?php echo get_string('enrol_course','enrol_sitecall')?></h3>
  <div class="sc-modal-close"></div>
</div>

<div class="sc-modal-content sc-clearfix">
  <h3 class="sc-add-header"><?php echo get_string('enrolling_course','enrol_sitecall')?></h3>
  <h3 class="sc-add-header">"[coursename]"</h3>
  <h3 class="sc-add-header"><?php echo get_string('enrol_header_extra','enrol_sitecall')?></h3>
  <form>
    <span class="sc-status sc-form-status"></span>

    <label for="comment"><?php echo get_string('comment','enrol_sitecall')?></label>
    <textarea class="sc-form-item" name="comment"></textarea>
    <span class="sc-status sc-status-comment"></span>

    <span class="sc-status sc-status-demo-type"></span>

    <button class="sc-form-submit button btn btn-primary"><?php echo get_string('form_submit','enrol_sitecall')?></button>
    <div class="sc-modal-close button btn btn-primary"><?php echo get_string('close','enrol_sitecall')?></div>
  </form>
  
</div>
