<?php

namespace theme_opentechnology\output;

// Be sure to include the H5P renderer so it can be extended
require_once($CFG->dirroot . '/mod/hvp/renderer.php');

/**
 * Class theme_h5pmod_mod_hvp_renderer
 *
 * Extends the H5P renderer so that we are able to override the relevant
 * functions declared there
 */
class mod_hvp_renderer extends \mod_hvp_renderer {

    /**
     * Add styles when an H5P is displayed.
     *
     * @param array $styles Styles that will be applied.
     * @param array $libraries Libraries that wil be shown.
     * @param string $embedType How the H5P is displayed.
     */
    public function hvp_alter_styles(&$styles, $libraries, $embedType) {
        global $CFG;
        
        $plugin = \core_plugin_manager::instance()->get_plugin_info('theme_opentechnology');
        $styles[] = (object) array(
            'path'    => $CFG->wwwroot . '/theme/opentechnology/stylesprofile.php/mod_hvp',
            'version' => '?ver='.$plugin->versiondb,
        );
    }
}