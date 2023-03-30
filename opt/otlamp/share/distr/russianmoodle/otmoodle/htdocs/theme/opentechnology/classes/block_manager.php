<?php

namespace theme_opentechnology;

require_once $CFG->libdir.'/blocklib.php';

class block_manager extends \block_manager {

    public function load_blocks($includeinvisible = null) {
        parent::load_blocks($includeinvisible);

        if (!$this->page->user_is_editing() )
        {// Пользователь не находится в режиме редактирования

            // Инициализация менеджера профилей
            $manager = profilemanager::instance();
            // Получение профиля текущей страницы
            $profile = $manager->get_current_profile();

            foreach($this->birecordsbyregion as $region => $instances)
            {
                // Получение настройки
                $configname = str_replace('-', '_', 'region_'.$this->page->pagelayout.'_'.$region);
                $config = $manager->get_theme_setting($configname, $profile);
                if ($config == 'dock')
                {
                    if (!array_key_exists('dock', $this->birecordsbyregion))
                    {
                        $this->birecordsbyregion['dock'] = [];
                    }
                    $this->birecordsbyregion['dock'] = array_merge($this->birecordsbyregion['dock'], $instances);
                    $this->birecordsbyregion[$region] = [];
                }

            }
        }
    }
}