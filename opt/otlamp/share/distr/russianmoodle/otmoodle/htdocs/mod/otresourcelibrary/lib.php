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

/////////////////////////////////
// Launch Moodle and include libs
/////////////////////////////////

global $CFG;
require_once($CFG->dirroot . '/config.php');

///////////////////////////////
// Functions required by Moodle
///////////////////////////////

/**
 * Does otresourcelibrary support requested feature?
 *
 * @param $feature
 */
function otresourcelibrary_supports($feature)
{
    switch ($feature) {
        case FEATURE_MOD_INTRO:
            return false;
            break;
        case FEATURE_GRADE_HAS_GRADE:
            return false;
            break;
        case FEATURE_COMPLETION_TRACKS_VIEWS:
            return true;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        default:
            return null;
    }
}

/**
 * Saves a new instance of the mod_endorsement into the database.
 *
 * Given an object containing all the necessary data, (defined by the form
 * in mod_form.php) this function will create a new instance and return the id
 * number of the instance.
 *
 * @param object $moduleinstance An object from the form.
 * @param mod_endorsement_mod_form $mform The form.
 * @return int The id of the newly inserted record.
 */
function otresourcelibrary_add_instance($moduleinstance, $mform = null) {
    global $DB;
    $moduleinstance->timecreated = time();
    $moduleinstance->id = $DB->insert_record('otresourcelibrary', $moduleinstance);
    // Форма настроек создана
    $event = \mod_otresourcelibrary\event\mod_form_created::form_created($moduleinstance);
    $event->trigger();
    return $moduleinstance->id;
}

/**
 * Updates an instance of the mod_otresourcelibrary in the database.
 *
 * Given an object containing all the necessary data (defined in mod_form.php),
 * this function will update an existing instance with new data.
 *
 * @param object $moduleinstance An object from the form in mod_form.php.
 * @param mod_endorsement_mod_form $mform The form.
 * @return bool True if successful, false otherwise.
 */
function otresourcelibrary_update_instance($moduleinstance, $mform = null) {
    global $DB;
    
    $moduleinstance->timemodified = time();
    $moduleinstance->id = $moduleinstance->instance;
    // Форма настроек обновлена
    $event = \mod_otresourcelibrary\event\mod_form_updated::form_updated($moduleinstance);
    $event->trigger();
    return $DB->update_record('otresourcelibrary', $moduleinstance);
}

/**
 * Removes an instance of the mod_otresourcelibrary from the database.
 *
 * @param int $id Id of the module instance.
 * @return bool True if successful, false on failure.
 */
function otresourcelibrary_delete_instance($id) {
    global $DB;
    
    $exists = $DB->get_record('otresourcelibrary', array('id' => $id));
    if (!$exists) {
        return false;
    }
    
    $DB->delete_records('otresourcelibrary', array('id' => $id));
    
    return true;
}

/////////////////////////
// Some helpful functions
/////////////////////////

function mod_otresourcelibrary_output_fragment_router($args)
{
    global $PAGE, $CFG;
    $PAGE->set_context(\context_system::instance());
    
    $formdata = [];
    if (!empty($args['formdata'])) {
        $formdata = json_decode($args['formdata']);
    }
    $modalform = new mod_otresourcelibrary_material_search_form();
    $modalform->set_data($formdata);
    
    return $modalform->render();
}

/**
 * Mark the activity completed (if required) and trigger the course_module_viewed event.
 *
 * @param  stdClass $book       book object
 * @param  stdClass $chapter    chapter object
 * @param  bool $islaschapter   is the las chapter of the book?
 * @param  stdClass $course     course object
 * @param  stdClass $cm         course module object
 * @param  stdClass $context    context object
 * @since Moodle 3.0
 */
function otresourcelibrary_view($otresourcelibrary, $course, $cm, $context) {
    
    // Кинем событие о просмотре
    $event = \mod_otresourcelibrary\event\course_module_viewed::create_from_otresourcelibrary($otresourcelibrary, $context);
    $event->trigger();
    // поставим галку о просмотре
    $completion = new completion_info($course);
    $completion->set_module_viewed($cm);
}

function otresourcelibrary_get_slice_of_shuffled($toshuffle, $offset, $limit)
{
    
//     error_log(':::: TO SHUFFLE ::::');
//     foreach($toshuffle as $sourcename => $sourcedata)
//     {
//         error_log($sourcename.': '.implode(', ', array_keys($sourcedata['resources'])));
//     }
    
    
    // Замешивание ресурсов (из каждого источника по одному ресурсу пока не кончатся или не наберем нужное количество)
    // Массив замешанных ресурсов
    $shuffledresources = [];
    $offsetted = 0;
    // Флаг end of resources - станет true, когда ни в одном источнике не окажется необработанных ресурсов
    // или когда наберется достаточное количество ресурсов для отображения
    $eor = false;
    do {
        $eor = true;
        foreach($toshuffle as $sourcename => $sourcedata)
        {
            if (count($sourcedata['resources']) > 0)
            {
                $resource = array_shift($toshuffle[$sourcename]['resources']);
                
                if ($offset > $offsetted)
                {// Ресурс необходимо пропустить, поэтому мы его забрали из массива (выше)
                    // и не будем добавлять в замес (else)
                    $offsetted++;
                } else
                {
                    // Ресурс, нужный в нашем замесе
                    $shuffledresources[] = $resource;
                }
                
                // если ресурсы еще остались, помечаем, что есть возможность продолжать замешивание ресурсов
                if ($toshuffle[$sourcename]['resources'] > 0)
                {
                    $eor = false;
                }
                
                // Если мы набрали достаточное для отображения количество ресурсов - заканчиваем замес
                if (count($shuffledresources) >= $limit)
                {
                    break 2;
                }
            }
        }
    } while(!$eor);
//     error_log(':::: SHUFFLED ::::');
//     $logshuffled = [];
//     foreach($shuffledresources as $shuffledresource)
//     {
//         $logshuffled[] = $shuffledresource['properties']['sourcename'].' - '.$shuffledresource['properties']['id'];
//     }
//     error_log(implode('; ', $logshuffled));
    
    return $shuffledresources;
}

function otresourcelibrary_seacrh_by_query($sourcenames, $q, $categoryid, $offset, $limit)
{
    $result = [];
    
    $otapi = new \mod_otresourcelibrary\otapi();
    // Инициализация объекта кэша
    $cache = cache::make_from_params(cache_store::MODE_APPLICATION, 'mod_otresourcelibrary', 'search_query_cache');
    $cachekeysuffix = '_' . $categoryid  . '_' . urlencode($q) . '_' . $offset . '_' . $limit;
    // Проверка существования закэшированных данных запроса по каждому из ресурсов
    foreach($sourcenames as $k=>$sourcename)
    {
        // по умолчанию результатов по источнику считаем что нет
        $result[$sourcename] = ['resources' => [], 'total' => 0];
        $cachedata = $cache->get($sourcename.$cachekeysuffix);
        if ($cachedata !== false && ($cachedata['timecreated'] ?? 0 + $otapi->cachelifetime) >= time())
        {// Кэш есть и он еще жив
            $result[$sourcename] = $cachedata;
            unset($sourcenames[$k]);
        }
    }
    
    // Запрос ресурсов по всем источникам, по которым не нашлось закэшированных данных
    if (!empty($sourcenames))
    {
        // Получаем у ресурсов пачку данных и кэшируем, чтобы не гонять за ней постоянно, гуляя по страницам
        $findresourcesresponse = $otapi->find_resources($q, $categoryid, $sourcenames, $offset, $limit);
        foreach($findresourcesresponse as $sourcename => $foundsourcedata)
        {
            $resources = $foundsourcedata['resources'] ?? [];
            $cachedata = [
                'resources' => $resources,
                'resources_count' => count($resources),
                'total' => $foundsourcedata['total'] ?? 0,
                'timecreated' => time()
            ];
            $cache->set($sourcename.$cachekeysuffix, $cachedata);
            $result[$sourcename] = $cachedata;
        }
    }
    
    return $result;
}

/**
 * Обрабатывет и кеширует поиск ресурсов
 *
 * @param string $q
 * @param int $categoryid
 * @param int $sourcenames
 * @param number $offset
 * @param number $limit
 * @throws \moodle_exception
 * @return array
 */
function otresourcelibrary_find_resources($q = null, $categoryid=null, $sourcenames=null, $offset = 0, $limit = 10) {
    $otapi = new \mod_otresourcelibrary\otapi();
    if (empty($sourcenames)) {
        $sourcesinfo = $otapi->get_installation_sources_names();
        foreach ($sourcesinfo as $sourceinfo)
        {
            $sourcenames[] = $sourceinfo['sourcename'];
        }
    } else {
        if (!is_array($sourcenames)) {
            $sourcenames = [$sourcenames];
        }
    }
    if (is_null($q) && is_null($categoryid)) {
        $q = '';
    }
    
    // необходимо рассмотреть отказ от использования указанной настройки
    // теперь возможно использовать тот же лимит, что используется для отображения в библиотеке ресурсов
    // или его же, умноженный на комфортный коэффициент
    $otapilimit = $otapi->limitpersource;
    
    if (!is_null($q) || (!empty($categoryid) && count($sourcenames) == 1)) {
        
        $totalbyquery = null;
        $otapipage = 1;
        $resourcescount = 0;
        $lastresourcescount = 0;
        $resources = [];
        // end of resources
        $eor = false;
        do {
            // получаем результаты страницы
            $pageofresources = otresourcelibrary_seacrh_by_query($sourcenames, $q??'', $categoryid, ($otapipage++-1)*$otapilimit, $otapilimit);
            // добавялем в нашу копилку ресурсов
            $resources = array_replace_recursive($resources, $pageofresources);
            // считаем общую сумму доступных результатов по запросу (по всем источникам, без лимитов)
            if (is_null($totalbyquery))
            {
                $totalbyquery = array_sum(array_column($resources, 'total'));
            }
            // считаем сумму результатов, полученных для этой страницы от отапи
            $resourcescount += array_sum(array_column($resources, 'resources_count'));
            
            // Если в ходе итерации количество ресурсов не изменилось - пора валить из цикла
            // Если в ходе итерации количество ресурсов превысило ожидаемое общее количество по всем источникам - тоже
            if ($resourcescount <= $lastresourcescount || $resourcescount >= $totalbyquery)
            {
                $eor = true;
            }
            // запоминаем количество ресурсов, получившееся в ходе итерации
            $lastresourcescount = $resourcescount;
            
        } while($resourcescount < ($offset + $limit) && !$eor);
        
        // из полученных страниц результата вырезаем только ту часть, которую еще не показывали
        $resourcesslice = otresourcelibrary_get_slice_of_shuffled($resources, $offset, $limit);
        
        return [$resourcesslice, $totalbyquery];
        
    } else {
        throw new \moodle_exception('query or category id and one source name must be set' );
    }
    
}

