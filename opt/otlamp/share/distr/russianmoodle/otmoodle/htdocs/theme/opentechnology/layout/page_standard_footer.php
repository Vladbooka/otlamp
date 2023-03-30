<footer id="page-footer" class="moodle-has-zindex">
	<div class="footerborder-wrapper">
		<div class="footerborder container<?php echo $themedata->widthfactorclass; ?> <?php echo $themedata->pageback_f_border_unlimit_width; ?>"></div>
	</div>
	<div class="footer-content-wrapper">
    	<div class="container<?php echo $themedata->widthfactorclass; ?> <?php echo $themedata->pageback_footer_unlimit_width; ?>">
        	<div id="footer_wrapper" class="footer_wrapper moodle-has-zindex">
           		<div id="footer_content" class="row">
                    <div id="f_leftblock_wrapper" class="f_logo_wrapper col-md-<?php echo $themedata->footer_logoimage_width; ?> desktop-first-column">
                   		<div id="f_logo_wrapper">
                   			<?php echo $themedata->footer_logoimage; ?>
                   		</div>
                   		<div id="f_logo_text">
                   	   		<?php echo $themedata->footer_logoimage_text; ?>
                   	   	</div>
                   	   	<div id="f_social_wrapper">
                   	   		<?php echo $OUTPUT->social_links(); ?>
                   	   	</div>
                   	   	<div class="clearfix"></div>
                   	</div>
                   	<div id="f_centerblock_wrapper" class="col-md-<?php echo 12-4-$themedata->footer_logoimage_width; ?>">
                   		<div id="f_text_wrapper">
                   	   		<?php echo $OUTPUT->f_text(); ?>
                   	   	</div>
                   	</div>
                   	<div id="f_rightblock_wrapper" class="col-md-4 desktop-last-column">
                   		<div id="logininfo_wrapper" class="logininfo_wrapper">
                        <?php
                            echo $OUTPUT->login_info();
                        ?>
                        </div>
                       	<div id="copyright_wrapper" class="copyright_wrapper row">
                       		<div id="copyright" class="pull-right col-md-12 desktop-last-column">
                           		<?php echo $OUTPUT->copyright_text(); ?>
                       		</div>
                       	</div>
                       	<div id="rm3kl">
                           	<?php echo $themedata->footer_rm3kl_text; ?>
                        </div>
                   	</div>
                   	<div class="systeminfo col-md-12">
                       	<?php echo $OUTPUT->standard_footer_html(); ?>
                    </div>
           		</div>
        	</div>
    	</div>
	</div>
</footer>