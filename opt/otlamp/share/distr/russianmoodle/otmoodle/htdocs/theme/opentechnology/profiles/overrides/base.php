<?php

class theme_opentechnology_profile
{
    /**
     * Пользовательское меню
     *  
     * @param core_renderer $renderer
     * @param moodle_page $page
     * @param bool $loginpage
     * @param stdClass $user
     * @param bool $withlinks
     */
    public function user_menu(core_renderer $renderer, moodle_page $page, $loginpage, $user = null, $withlinks = null)
    {
    }
    
    /**
     * замена макроподстановок в кастом меню
     *
     * @param string $custommenu
     *
     * @return void
     */
    public function custom_menu_replace_macrosubstitutions(&$custommenu)
    {
    }
    
    /**
     * Returns a search box.
     *
     * @param  string $id     The search box wrapper div id, defaults to an autogenerated one.
     * @return string         HTML with the search form hidden by default.
     */
    public function search_box($id = false)
    {
        
    }
}