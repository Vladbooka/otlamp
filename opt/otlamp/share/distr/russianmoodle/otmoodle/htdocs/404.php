<?php 

require_once('config.php');
$PAGE->set_pagelayout('standard');
$PAGE->set_cacheable(false);

$site = get_site();
$PAGE->set_title("Page not found");
$PAGE->set_heading("Page not found");

echo $OUTPUT->header();
echo '<div class="content" style="text-align: center">
<h1 style="font-size: 90px">404</h1>
<h2>Page not found</h2>
<p class="blurb">It\'s possible the page you were looking for might have been moved, updated or deleted.</p>
<p class="hint">Please click the back button.</p>
</div>';
echo $OUTPUT->footer();