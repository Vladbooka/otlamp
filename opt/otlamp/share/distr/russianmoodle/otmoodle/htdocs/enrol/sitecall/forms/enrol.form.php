<?php require_once ("../../config.php");
global $CFG;
require_once ($CFG->libdir . '/moodlelib.php');
?>
<div class="sc-modal-header sc-clearfix">
  <h3><?php echo get_string('request_course','enrol_sitecall')?></h3>
  <div class="sc-modal-close"></div>
</div>

<div class="sc-modal-content sc-clearfix">
  <h3 class="sc-add-header"><?php echo get_string('enrolling_course','enrol_sitecall')?></h3>
  <h3 class="sc-add-header">"[coursename]"</h3>
  <h3 class="sc-add-header"><?php echo get_string('enter_personl_details','enrol_sitecall')?></h3>
  <form>
    <span class="sc-status sc-form-status"></span>
    
    <label for="lastname"><?php echo get_string('lastname_label','enrol_sitecall')?></label>
    <input type="text" class="sc-form-item" name="lastname" placeholder="<?php echo get_string('lastname_placeholder','enrol_sitecall')?>">
    <span class="sc-status sc-status-lastname"></span>
    
    <label for="firstname"><?php echo get_string('name_label','enrol_sitecall')?></label>
    <input type="text" class="sc-form-item" name="firstname" placeholder="<?php echo get_string('name_placeholder','enrol_sitecall')?>">
    <span class="sc-status sc-status-firstname"></span>
    
    <label for="phone"><?php echo get_string('phone_label','enrol_sitecall')?></label>
    <input type="text" class="sc-form-item" name="phone" placeholder="<?php echo get_string('phone_placeholder','enrol_sitecall')?>">
    <span class="sc-status sc-status-phone"></span>

    <label for="email"><?php echo get_string('email_label','enrol_sitecall')?></label>
    <input type="text" class="sc-form-item" name="email" placeholder="<?php echo get_string('email_placeholder','enrol_sitecall')?>">
    <span class="sc-status sc-status-email"></span>
    
    <label for="comment"><?php echo get_string('org_name_label','enrol_sitecall')?></label>
    <textarea class="sc-form-item" name="orgname"></textarea>
    <span class="sc-status sc-status-org-name"></span>
    
    <label for="comment"><?php echo get_string('sources_label','enrol_sitecall')?></label>
    <textarea class="sc-form-item" name="origins"></textarea>
    <span class="sc-status sc-status-origins"></span>
    
    <label for="comment"><?php echo get_string('comment_label','enrol_sitecall')?></label>
    <textarea class="sc-form-item" name="comment"></textarea>
    <span class="sc-status sc-status-comment"></span>

    <span class="sc-status sc-status-demo-type"></span>

    <button class="sc-form-submit button btn btn-primary"><?php echo get_string('form_submit','enrol_sitecall')?></button>
    <div class="sc-modal-close button btn btn-primary"><?php echo get_string('form_cancel','enrol_sitecall')?></div>
  </form>
  
</div>
