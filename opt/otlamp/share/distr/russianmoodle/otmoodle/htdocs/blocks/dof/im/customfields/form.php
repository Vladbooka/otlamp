<?php
////////////////////////////////////////////////////////////////////////////
//                                                                        //
// NOTICE OF COPYRIGHT                                                    //
//                                                                        //
// Dean`s Office for Moodle                                               //
// Электронный деканат                                                    //
// <http://deansoffice.ru/>                                               //
//                                                                        //
//                                                                        //
// This program is free software: you can redistribute it and/or modify   //
// it under the terms of the GNU General Public License as published by   //
// the Free Software Foundation, either version 3 of the Licensen.        //
//                                                                        //
// This program is distributed in the hope that it will be useful,        //
// but WITHOUT ANY WARRANTY; without even the implied warranty of         //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the          //
// GNU General Public License for more details.                           //
//                                                                        //
// You should have received a copy of the GNU General Public License      //
// along with this program.  If not, see <http://www.gnu.org/licenses/>.  //
//                                                                        //
////////////////////////////////////////////////////////////////////////////

/**
 * Классы форм
 *
 * @package    im
 * @subpackage customfields
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Подключаем базовые функции плагина
require_once('lib.php');

// Подключаем библиотеку форм
$DOF->modlib('widgets')->webform();

/**
 * Класс формы создания/редактирования полей
 * 
 */
class dof_im_customfields_save_form extends dof_modlib_widgets_form
{
    /**
     * @var dof_control
     */
    protected $dof;
    
    /**
     * GET параметры для ссылки
     * 
     * @var array 
     */
    protected $addvars = [];
    
    /**
     * ID дополнительного поля
     * 
     * @var int
     */
    protected $id;
    
    /**
     * URL для возврата после обработки формы
     *
     * @var string
     */
    protected $returnurl = '';
    
    /**
     * URL для отмены формы
     *
     * @var string
     */
    protected $cancelurl = '';
    
    /**
     * Объект дополнительного поля
     * 
     * @var dof_customfields_base
     */
    protected $customfield = null;
    
    /**
     * Определение формы
     * 
     * @return void
     */
    protected function definition()
    {
        // Cоздаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        
        // Добавляем свойства
        $this->dof = $this->_customdata->dof;
        $this->id = $this->_customdata->id;
        $this->addvars = $this->_customdata->addvars;
        $this->returnurl = $this->_customdata->returnurl;
        $this->cancelurl = $this->_customdata->cancelurl;
        
        // Скрытые поля
        $mform->addElement('hidden', 'sesskey', sesskey());
        $mform->setType('sesskey', PARAM_ALPHANUM);
        $mform->addElement('hidden', 'id', $this->id);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'departmentid', $this->addvars['departmentid']);
        $mform->setType('departmentid', PARAM_INT);
        
        // Инициализация дополнительного поля
        if ( $this->id > 0 )
        {// Инициализация дополнительного поля на основе шаблона
            $this->customfield = $this->dof->modlib('formbuilder')->
                init_customfield_by_item($this->id);
        } elseif ( ! empty($this->addvars['type']) )
        {// Инициализация дополнительного поля на основе типа
            $this->customfield = $this->dof->modlib('formbuilder')->
                init_customfield_by_type($this->addvars['type']);
        }
        
        // Кнопки действий
        $actionsgroup = [];
        
        if ( $this->id == 0 )
        {// Создание нового поля
            // Установка текста заголовка формы
            $headertext = $this->dof->get_string('form_customfields_create_title', 'customfields');
        } else 
        {
            // Установка текста заголовка формы
            $headertext = $this->dof->get_string('form_customfields_edit_title', 'customfields');
        }
        
        // Заголовок формы
        $mform->addElement(
            'header',
            'form_customfields_header_title',
            $headertext
        );
        
        // Поле для вывода сообщений об ошибках скрытых элементов
        $mform->addElement(
            'static',
            'hidden',
            ''
        );
        
        // Тип дополнительного поля
        $cftypes = $this->dof->modlib('formbuilder')->get_customfields_localized_types();
        $typeselect = $mform->createElement(
            'select',
            'type',
            $this->dof->get_string('form_customfields_save_type_select_title', 'customfields'),
            $cftypes
        );
        // Подтверждение типа дополнительного поля
        $submit = $mform->createElement(
            'submit',
            'submit',
            $this->dof->get_string('form_customfields_save_type_select_next', 'customfields')
        );
        
        // Секция с указанием типа дополнительного поля
        if ( $this->id == 0 && empty($this->addvars['type']) )
        {// Требуется выбрать тип
            $selecttypegroup = [
                $typeselect,
                $submit
            ];
        } else 
        {// Тип дополнительного поля выбран
            $selecttypegroup = [
                $typeselect
            ];
            if ( $this->customfield )
            {// Указано поле
                $typeselect->setValue($this->customfield->type());
            } elseif ( ! empty($this->addvars['type']) )
            {
                $typeselect->setValue($this->addvars['type']);
            }
            $typeselect->freeze();
        }
        $mform->addGroup(
            $selecttypegroup,
            'type_select_group',
            $this->dof->get_string('form_customfields_save_select_type_group_title', 'customfields'),
            ''
        );
        
        if ( $this->customfield )
        {// Поле инициализировано
            
            // Кнопка сохранения данных
            $actionsgroup[] = $mform->createElement(
                'submit',
                'submit',
                $this->dof->get_string('form_customfields_save_submit', 'customfields')
            );
            $actionsgroup[] = $mform->createElement(
                'submit',
                'submitclose',
                $this->dof->get_string('form_customfields_save_submitclose', 'customfields')
            );
            
            // Передача управления формой в объект дополнительного поля
            $this->customfield->saveform_definition($this, $mform);
        }
        
        $actionsgroup[] = $mform->createElement(
            'cancel',
            'close',
            $this->dof->get_string('form_customfields_save_type_select_cancel', 'customfields')
        );
        $mform->addGroup(
            $actionsgroup,
            'actions',
            '',
            '',
            false
        );
        
        // применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
    }
    
    /**
     * Проверка данных формы
     *
     * @param array $data - данные, пришедшие из формы
     *
     * @return array - массив ошибок, или пустой массив, если ошибок нет
     */
    public function validation($data, $files)
    {
        // Cоздаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        
        // Массив ошибок
        $errors = parent::validation($data, $files);
        
        // Проверка существования подразделения
        if ( isset($data['departmentid']) )
        {
            $exists = $this->dof->storage('departments')->is_exists($data['departmentid']);
            if ( $exists == false )
            {// Подразделение не найдено
                $errors['hidden'] = $this->dof->get_string(
                    'form_customfields_save_department_error_notfound',
                    'customfields'
                );
            }
        }
        
        if ( $this->customfield )
        {// Передача управления формой в объект дополнительного поля
            $this->customfield->saveform_validation($this, $mform, $errors, $data, $files);
        }
        
        
        // Возвращаем ошибки, если они есть
        return $errors;
    }
    
    /**
     * Обработать пришедшие из формы данные
     *
     * @return bool
     */
    public function process()
    {
        if ( $this->is_cancelled() )
        {// Отмена формы
            redirect($this->cancelurl);
        }
        
        if ( $this->is_submitted() && confirm_sesskey() && 
             $formdata = $this->get_data() )
        {// Форма подтверждена

            // Cоздаем ссылку на HTML_QuickForm
            $mform =& $this->_form;
            
            if ( isset($formdata->type_select_group['submit']) )
            {// Действие выбора типа для нового поля
                
                // Редирект с учетом типа
                redirect($this->get_returnurl(
                    ['type' => $formdata->type_select_group['type']]
                ));
            }
            
            if ( isset($formdata->submit) )
            {// Сохранение данных поля
                // Передача управления формой в объект дополнительного поля
                $cfid = $this->customfield->saveform_process($this, $mform, $formdata);
                
                // Редирект
                redirect($this->get_returnurl(
                    ['id' => $cfid]
                ));
            }
            
            if ( isset($formdata->submitclose) )
            {// Сохранение данных поля
                // Передача управления формой в объект дополнительного поля
                $this->customfield->saveform_process($this, $mform, $formdata);
                
                // Редирект
                redirect($this->cancelurl);
            }
        }
    }

    /**
     * Получить URL для возврата после обработки
     */
    protected function get_returnurl($addvars)
    {
        // Формирование URL для редиректа
        $parsedurl = parse_url($this->returnurl);
        
        // Массив GET-параметров для URL возврата
        $query = [];
        if ( isset($parsedurl['query']) && ! empty($parsedurl['query']) )
        {// В URL возврата указаны GET-параметры
            $parsedquery = explode('&', $parsedurl['query']);
            foreach ( $parsedquery as $parameter )
            {// Формирование GET-массива
                $parameter = explode('=', $parameter);
                if ( isset($parameter[0]) && isset($parameter[1]) )
                {// Валидный параметр
                    // Очистка от возможного параметра-массива
                    $parameter[0] = str_replace('[]', '', $parameter[0]);
                    if ( ! isset($query[$parameter[0]]) )
                    {// Добавление значения
                        $query[$parameter[0]] = $parameter[1];
                    } else
                    {// Параметр уже найден среди имеющихся. Формирование массива значений
                        $query[$parameter[0]] = (array)$query[$parameter[0]];
                        $query[$parameter[0]][] = $parameter[1];
                    }
                }
            }
        }
        
        foreach ( (array)$addvars as $name => $value )
        {
            // Добавление результатов обработки формы
            $query[$name] = $value;
        }
        
        
        // Формирование результирующего URL
        $resultquery = [];
        foreach ( $query as $name => $value )
        {
            if ( is_array($value) )
            {
                foreach ( $value as $element )
                {
                    $resultquery[] = $name.'[]='.$element;
                }
            } else
            {
                $resultquery[] = $name.'='.$value;
            }
        }
        $query = implode('&', $resultquery);
        $url = $parsedurl['path'].'?'.$query;
        
        return $url;
    }
}

/**
 * Список дополнительных полей
 */
class dof_im_customfields_list extends dof_modlib_widgets_form
{
    /**
     * @var dof_control
     */
    protected $dof;

    /**
     * GET параметры для ссылки
     *
     * @var array
     */
    protected $addvars = [];
    
    /**
     * Данные фильтра для дополнительной фильтрации
     * 
     * @var array
     */
    protected $filterdata = [];
    
    /**
     * Текущее действие формы
     * 
     * @var array - Массив в формате ['action' => 'ids'];
     */
    private $currentaction = null;

    /**
     * Получение количества дополнительных полей без использования LIMIT
     *
     *  @return int - количество дополнительных полей
     */
    public function get_itemids()
    {
        // Параметры поиска дополнительных полей
        $conditions = [
            'departmentid' => $this->addvars['departmentid']
        ];
        if ( isset($this->filterdata['status']) )
        {
            $conditions['status'] = $this->filterdata['status'];
        }
        if ( isset($this->filterdata['plugincode']) )
        {
            $conditions['linkpcode'] = $this->filterdata['plugincode'];
        }
        if ( isset($this->filterdata['type']) )
        {
            $conditions['type'] = $this->filterdata['type'];
        }
        if ( isset($this->filterdata['required']) )
        {
            $conditions['required'] = $this->filterdata['required'];
        }
        if ( isset($this->filterdata['moderation']) )
        {
            $conditions['moderation'] = $this->filterdata['moderation'];
        }
    
        return $this->dof->storage('customfields')->get_records(
            $conditions, 
            'departmentid, linkpcode, sortorder ASC, id ASC', 
            'id'
        );
    }
    
    /**
     * Обьявление полей формы
     * 
     * @see dof_modlib_widgets_form::definition()
     */
    protected function definition()
    {
        // Создание ссылки на HTML_QuickForm
        $mform =& $this->_form;
        
        // Добавление свойств
        $this->addvars = $this->_customdata->addvars;
        $this->dof = $this->_customdata->dof;

        // Нормализация входных данных
        if ( ! isset($this->addvars['departmentid']) )
        {
            $this->addvars['departmentid'] = 0;
        }
        if ( ! isset($this->addvars['sort']) )
        {
            $this->addvars['sort'] = 'id';
        }
        if ( ! isset($this->addvars['dir']) )
        {
            $this->addvars['dir'] = 'ASC';
        }
        if ( $this->addvars['dir'] != 'ASC' )
        {
            $this->addvars['dir'] = 'DESC';
        }
        
        // Установка HTML-атрибутов формы
        $formattrs = $mform->getAttributes();
        $formattrs['class'] = $formattrs['class'].' customfields-listeditor dof_im_customfields_listeditor_form';
        $formattrs['id'] = 'form_customfields_list_editor';
        $mform->setAttributes($formattrs);

        // Скрытые поля
        $mform->addElement('hidden', 'sesskey', sesskey());
        $mform->setType('sesskey', PARAM_ALPHANUM);
        $mform->addElement('hidden', 'departmentid', $this->addvars['departmentid']);
        $mform->setType('departmentid', PARAM_INT);
        
        // Поле для вывода сообщений об ошибках скрытых элементов
        $mform->addElement(
            'static',
            'hidden',
            ''
        );
        
        // Добавление фильтра
        $this->definition_filter();
        
        // Добавление списка подписок на учебный процесс
        $this->definition_list();
        
        // Добавление массовых действий
        $this->definition_multipleactions();
    }

    /**
     * Инициализация фильтра
     *
     * @return void
     */
    protected function definition_filter()
    {
        // Создание ссылки на HTML_QuickForm
        $mform =& $this->_form;
    
        // Получение данных фильтра
        $filterdata = [];
        if ( ! empty($this->addvars['filter']) )
        {
            $filter = (array)explode(';', $this->addvars['filter']);
            foreach ( $filter as $filteritem )
            {
                if ( $filteritem )
                {
                    $filteritem = explode(':', $filteritem, 2);
                    $filterdata[$filteritem[0]] = $filteritem[1];
                }
            }
        }
        // Заголовок - Фильтрация
        $headergroup = [];
        $headergroup[] = $mform->createElement(
            'header',
            'header_filter',
            $this->dof->get_string('form_listeditor_filter_header', 'customfields')
        );
        $mform->addGroup($headergroup, 'groupheader_filter', '', '', true);
    
        // Фильтрация по статусам
        $statuses = $this->dof->workflow('customfields')->get_list();
        $defaultlist = $this->dof->workflow('customfields')->get_meta_list('real');
        $mform->addElement(
            'dof_multiselect',
            'filter_status',
            $this->dof->get_string('form_listeditor_filter_status', 'customfields'),
            $statuses
        );
        if ( ! empty($filterdata['status']) )
        {
            $filterstatus = explode(',', $filterdata['status']);
            $mform->setDefault('filter_status', $filterstatus);
            $this->filterdata['status'] = $filterstatus;
        } else 
        {
            $mform->setDefault('filter_status', array_keys($defaultlist));
            $this->filterdata['status'] = array_keys($defaultlist);
        }
        
        // Фильтрация по хранилищу
        $linkpcodes = $this->dof->storage('customfields')->get_list_linkpcodes([
            'departmentid' => $this->addvars['departmentid']
        ]);
        if( ! empty($linkpcodes) )
        {
            $storages = ['0' =>  $this->dof->get_string('form_listeditor_filter_linkpcode_any', 'customfields')];
            foreach($linkpcodes as $linkpcode=>$value)
            {
                $storages[$linkpcode] = $this->dof->get_string('title', $linkpcode, null, 'storage');
            }
            $mform->addElement(
                'select',
                'filter_linkpcode',
                $this->dof->get_string('form_listeditor_filter_linkpcode', 'customfields'), 
                $storages 
            );
        }

        if ( ! empty($filterdata['linkpcode']) )
        {
            $mform->setDefault('filter_linkpcode', $filterdata['linkpcode']);
            $this->filterdata['linkpcode'] = $filterdata['linkpcode'];
        } else
        {
            $mform->setDefault('filter_linkpcode', '0');
        }
    
        // Кнопки действия
        $button = [];
        $button[] = $mform->createElement(
            'submit',
            'filter_submit',
            $this->dof->get_string('form_listeditor_filter_submit', 'customfields')
        );
        $button[] = $mform->createElement(
            'submit',
            'filter_clear',
            $this->dof->get_string('form_listeditor_filter_clear', 'customfields')
        );
        $mform->addGroup($button, 'groupfilter_buttons', '', '', false);
    }
    
    /**
     * Отображение списка элементов
     *
     * @return void
     */
    protected function definition_list()
    {
        // Создание ссылки на HTML_QuickForm
        $mform =& $this->_form;
        
        // Получение данных по допполям для формирования таблицы
        $items = $this->definition_list_data();
    
        // Заголовок - Список
        $headergroup = [];
        $headergroup[] = $mform->createElement(
            'header',
            'header_list',
            $this->dof->get_string('form_listeditor_list_header', 'customfields')
        );
        $mform->addGroup($headergroup, 'groupheader_list', '', '', true);
    
        $sortableclass = '';
        $handler = '';
        $tabledata = '';
        if ( isset($this->filterdata['status']) && ! empty($this->filterdata['linkpcode']) )
        {
            if( $this->filterdata['status'] == ['available'] )
            {
                $sortableclass = ' dof_customfield_sortable';
                $handler = $this->dof->modlib('ig')->icon('sortvertical');
                $tabledata = ' data-department-id="'.$this->addvars['departmentid'].'" data-linkpcode="'.$this->filterdata['linkpcode'].'"';
            }
        }
        // Формирование заголовка таблицы
        $mform->addElement('html', '<table class="generaltable boxaligncenter'.$sortableclass.'" '.$tabledata.'><tr>');
        if( ! empty($handler) )
        {// иконка для сортировки
            $mform->addElement('html', '<th style="width:15px;"></th>');
        }
        $mform->addElement('html', '<th width="0">');
        $this->add_checkbox_controller(1, $this->dof->get_string('form_listeditor_headercheckbox', 'customfields'));
        $mform->addElement('html', '</th>');
        $mform->addElement('html', '<th>'.implode('</th><th>',$items['header']).'</th></tr>');
    
        // Добавление строк
        foreach( $items as $id => $row )
        {
            if ( is_string($id) )
            {
                continue;
            }
    
            $this->items[$id] = $row;
    
            $mform->addElement('html', '<tr class="dof_customfield_item" data-customfield-id="'.$id.'">');

            // иконка для сортировки
            if( ! empty($handler) )
            {
                $mform->addElement('html', '<td class="dof_customfield_sort_handler">'.$handler.'</td>');
            }
            
            // Чекбокс
            $mform->addElement('html', '<td>');
            $mform->addElement('advcheckbox', 'itemids['.$id.']', '', '', ['group' => 1]);
            $mform->addElement('html', '</td>');
    
            foreach( $row as $cellname => $celldata )
            {
                $mform->addElement('html', '<td>');
                switch ( $cellname )
                {
                    case 'status' :
                        $availablestatuses = $this->definition_list_changestatus_list((int)$id);
                        if ( ! empty($availablestatuses) )
                        {
                            $availablestatuses = ['' => $celldata] + $availablestatuses;
                            $group = [];
                            $group[] = $mform->createElement(
                                'select',
                                'changestatus_select',
                                $this->dof->get_string('form_listeditor_changestatus_select_label', 'customfields'),
                                $availablestatuses,
                                ['data-dofsingleselect' => 'true']
                            );
                            $group[] = $mform->createElement(
                                'submit',
                                'changestatus_submit',
                                $this->dof->get_string('form_listeditor_changestatus_submit_label', 'customfields')
                            );
                            $mform->addGroup(
                                $group,
                                'changestatus_'.$id,
                                $this->dof->get_string('form_listeditor_changestatusgroup_label', 'customfields')
                            );
                            break;
                        }
                    default :
                        $mform->addElement('html', $celldata);
                        break;
    
                }
                $mform->addElement('html', '</td>');
            }
            $mform->addElement('html', '</tr>');
        }
        $mform->addElement('html', '</table>');
    }
    
    /**
     * Получить список статусов, в которые может перейти указанное дополнительное поле
     *
     * @param int $id - ID дополнительного поля
     *
     * @return array - Список статусов
     */
    protected function definition_list_changestatus_list($id)
    {
        return $this->dof->workflow('customfields')->get_available($id);
    }
    
    /**
     * Получение данных по дополнительным полям
     *
     * @return array - Массив с данными для таблицы
     */
    protected function definition_list_data()
    {
        // Параметры поиска дополнительных полей
        $conditions = [
            'departmentid' => $this->addvars['departmentid']
        ];
        if ( isset($this->filterdata['status']) )
        {
            $conditions['status'] = $this->filterdata['status'];
        }
        if ( ! empty($this->filterdata['linkpcode']) )
        {
            $conditions['linkpcode'] = $this->filterdata['linkpcode'];
        }
    
        // Получение списка дополнительных полей
        $items = (array)$this->dof->storage('customfields')->get_records(
            $conditions,
            'departmentid, linkpcode, sortorder ASC, id ASC',
            '*',
            $this->addvars['limitfrom']-1,
            $this->addvars['limitnum']
        );
    
        // Заголовок таблицы подписок на учебный процесс
        $listdata = [];
        $listdata['header'] =
        [
            'actions' => $this->dof->get_string('form_listeditor_actions_label','customfields'),
            'code' => $this->dof->get_string('form_listeditor_code_label','customfields'),
            'name' => $this->dof->get_string('form_listeditor_name_label','customfields'),
            'depnameartmentid' => $this->dof->get_string('form_listeditor_depnameartmentid_label','customfields'),
            'linkpcode' => $this->dof->get_string('form_listeditor_linkpcode_label','customfields'),
            'type' => $this->dof->get_string('form_listeditor_type_label','customfields'),
            'required' => $this->dof->get_string('form_listeditor_required_label','customfields'),
            'moderation' => $this->dof->get_string('form_listeditor_moderation_label','customfields'),
            'status' => $this->dof->get_string('form_listeditor_status_label','customfields')
        ];
    
        // Получение типов дополнительных полей
        $customfieldtypes = $this->dof->modlib('formbuilder')->get_customfields_types();
        // Строки таблицы
        foreach ( $items as $item )
        {
            // Базовые данные по элементу
            $code = $item->code;
            $name = $this->dof->storage('customfields')->get_name($item);
            $departmentid = $this->dof->storage('departments')->get_name($item->departmentid);
            $plugincode = $this->dof->get_string('title', $item->linkpcode, null, 'storage');
            
            $type = $item->type;
            if ( isset($customfieldtypes[$type]) )
            {// Тип поля зарегистрирован
                $type = $customfieldtypes[$type]::get_localized_type();
            }
            
            $required = $this->dof->get_string('no', 'customfields');
            if ( $item->required )
            {
                $required = $this->dof->get_string('yes', 'customfields');
            }
            $moderation = $this->dof->get_string('no', 'customfields');
            if ( $item->moderation )
            {
                $moderation = $this->dof->get_string('yes', 'customfields');
            }
            $status = $this->dof->workflow('customfields')->get_name($item->status);
            
            // Действия
            $actions = '';
            if ( $this->dof->storage('customfields')->is_access('edit', $item->id) )
            {// Редактирование поля
                $somevars = $this->addvars;
                $somevars['id'] = $item->id;
                $actions .= $this->dof->modlib('ig')->icon(
                    'edititem',
                    $this->dof->url_im('customfields', '/save.php', $somevars)
                );
            }
            
            $listdata[(int)$item->id] = [
                'actions' => $actions,
                'code' => $code,
                'name' => $name,
                'departmentid' => $departmentid,
                'linkpcode' => $plugincode,
                'type' => $type,
                'required' => $required,
                'moderation' => $moderation,
                'status' => $status
            ];
        }
        return $listdata;
    }
    
    /**
     * Инициализация блока массовых действий
     *
     * @return void
     */
    protected function definition_multipleactions()
    {
        // Создание ссылки на HTML_QuickForm
        $mform =& $this->_form;
    
        // Список действий над элементами
        $actions = [
            '' => $this->dof->get_string('form_listeditor_massactions_select_action', 'customfields')
        ];
        // Список опций массовых действий над подписками
        $actionoptions = [];
        $actionoptions[''][''] = $this->dof->get_string('form_listeditor_massactions_select_option', 'customfields');
        
        // Массовые действия над элементами
        $select = $mform->addElement(
            'dof_hierselect',
            'form_listeditor_massactions',
            $this->dof->get_string('form_listeditor_massactions_title', 'customfields'),
            '',
            null,
            '<div class="col-12 px-0"></div>'
        );
        // Установка набора значений
        $select->setOptions([$actions, $actionoptions]);

        // Кнопка подтверждения
        /*$options = [
            'modalbuttonname' => $this->dof->get_string('form_listeditor_modalbuttonname', 'customfields'),
            'modaltitle' => $this->dof->get_string('form_listeditor_modaltitle', 'customfields'),
            'modalcontent' => $this->dof->get_string('form_listeditor_modalcontent', 'customfields'),
            'submitbuttonname' => $this->dof->get_string('form_listeditor_submitbuttonname', 'customfields'),
            'cancelbuttonname' => $this->dof->get_string('form_listeditor_cancelbuttonname', 'customfields')
        ];
        $mform->addElement(
            'dof_confirm_submit',
            'confirm_submit',
            'confirm_submit',
            $options
        );*/
    }
    
    public function validation($data, $files)
    {
        // Получение результата базовой валидации
        $errors = parent::validation($data, $files);

        // Установка текущего действия в зависимости от нажатой в форме кнопки 
        $this->currentaction = $this->get_current_action($data);
        
        if ( empty($this->currentaction) )
        {// Действие не найдено
            $errors['static'] = $this->dof->get_string('form_listeditor_error_undefined_action', 'cpassed');
            return $errors;
        }
        // Валидация только тех участков формы, в которых происходит действие
        foreach ( $this->currentaction as $action => $targetcpasseds )
        {
            switch ( $action )
            {
                // Применение фильтра
                case 'filter_submit' :
                case 'filter_clear' :
                    break;
                // Единичная смена статуса   
                case 'changestatus_single' :
                    
                    // Получение менеджера работы с подписками на дисциплину
                    $manager = $this->dof->modlib('learningplan')->get_manager('cpassed');
                    
                    // Проверка возможности перехода в указанный статус
                    $targetcpassed = (int)$targetcpasseds;
                    $targetstatus = $data['changestatus_'.$targetcpassed]['changestatus_select'];
                    
                    if ( $manager->is_request($targetcpassed) )
                    {// Текущая подписка на дисциплину является заявкой
                        // Активация блока отправки сообщения
                        $this->validation_raise_messager_block($errors, $targetstatus, [$targetcpassed]);
                    }
                    break;
                // Массовое действие
                case 'action_multiple' :
                    if ( empty($data['form_listeditor_massactions'][0] ) )
                    {// Действие не указано
                        $errors['form_listeditor_massactions'] = $this->dof->
                            get_string('form_listeditor_message_operation_not_selected', 'cpassed');
                    } elseif ( $data['form_listeditor_massactions'][0] == 'change_status' )
                    {// Действие смены статуса
                        
                        // Целевой статус
                        $targetstatus = $data['form_listeditor_massactions'][1];
                        $access = $this->dof->workflow('cpassed')->is_access(
                            'changestatus:to:'.$targetstatus, 
                            null, 
                            null, 
                            $this->cstream->departmentid
                        );
                        if ( empty($targetstatus) )
                        {
                            $errors['form_listeditor_massactions'] = $this->dof->
                                get_string('form_listeditor_message_operation_not_selected', 'cpassed');
                        } elseif ( ! $access )
                        {
                            $errors['form_listeditor_massactions'] = $this->dof->
                                get_string("form_listeditor_message_cpassed_changestatus_denied",'cpassed');
                        }
                        
                        // Получение менеджера работы с подписками на дисциплину
                        $manager = $this->dof->modlib('learningplan')->get_manager('cpassed');
                        
                        // Проверка cpassed на необходимость отправки сообщений
                        $cpassedsmessage = [];
                        $cpassedsnomessage = [];
                        foreach ( $targetcpasseds as $cpassedid )
                        {
                            if ( $manager->is_request($cpassedid) )
                            {// Текущая подписка на дисциплину является заявкой
                                $cpassedsmessage[] = $cpassedid;
                            } else 
                            {
                                $cpassedsnomessage[] = $cpassedid;
                            }
                        }
                        
                        if ( ! empty($cpassedsmessage) )
                        {// Найдены заявки, которые требуют отправки сообщения
                            // Активация блока отправки сообщения
                            $this->validation_raise_messager_block($errors, $targetstatus, $cpassedsmessage, $cpassedsnomessage);
                        }
                    } elseif ( $data['form_listeditor_massactions'][0] == 'change_cstream' )
                    {// Действие смены учебного процесса
                        
                        $targetcstream = $data['form_listeditor_massactions'][1];
                        $access = $this->dof->storage('cpassed')->is_access(
                            'edit',
                            null,
                            null,
                            $this->cstream->departmentid
                        );
                        if ( empty($data['form_listeditor_massactions'][1] ) )
                        {
                            $errors['form_listeditor_massactions'] = $this->dof->
                                get_string('form_listeditor_message_operation_not_selected', 'cpassed');
                        } elseif ( ! $this->dof->storage('cpassed')->is_access('edit') )
                        {
                            $errors['form_listeditor_massactions'] = $this->dof->
                                get_string("form_listeditor_message_cpassed_edit_denied",'cpassed');
                        }
                    }
                    break;
            }
        }

        return $errors;
    }

    /**
     * Определить действие в зависимости от нажатой кнопки
     *
     * При отправки формы определяется очередь действий, 
     * которые необходимо исполнить
     * 
     * @param (array) $data - Данные формы
     *
     * @return array - Список действий, которые необходимо произвести
     */
    private function get_current_action($data)
    {
        $actions = [];
        if ( empty($data['itemids']) )
        {
            $data['itemids'] = [];
        }
        // Фильтр
        if ( isset($data['filter_submit']) )
        {// Применение фильтра
            $actions['filter_submit'] = null;
        } elseif ( isset($data['filter_clear']) )
        {// Сброс фильтра
            $actions['filter_clear'] = null;
        }

        // Поиск единичных действий для одного элемента
        foreach ( $data['itemids'] as $itemid => $checkbox )
        {
            // Смена статуса элемента
            if ( isset($data['changestatus_'.$itemid]['changestatus_submit']) )
            {// Нажата кнопка подтверждения смены статуса
                $actions['changestatus_single'] = $itemid;
            }
        }
        
        // Массовое действие
        if ( isset($data['confirm_submit']) )
        {// Массовое действие
            // Сбор выбранных элементов
            $itemids = [];
            foreach( $data['itemids'] as $itemid => $checkbox )
            {
                if ( $checked )
                {// Элемент выбран
                    $itemids[$itemid] = $itemid;
                }
            }
            if ( $itemids )
            {// Выбраны элементы для массового действия
                $actions['action_multiple'] = $itemids;
            }
        }
        
        // Список действий
        return $actions;
    }
    
    public function process()
    {
        if ( $this->is_submitted() && confirm_sesskey() &&
             $this->is_validated() && $formdata = $this->get_data() )
        {// Форма подтверждена и данные получены
            
            // Сообщения при редиректе
            $redirectmessages = [];
            
            // Исполнение действий формы
            foreach ( $this->currentaction as $action => $itemids )
            {
                switch ( $action )
                {
                    // Сброс фильтра
                    case 'filter_clear' :
                        unset($this->addvars['filter']);
                        $this->addvars['limitfrom'] = 1;
                        break;
                    // Применение фильтра
                    case 'filter_submit' :
                        $this->addvars['filter'] = $this->get_filter_addvar($formdata);
                        $this->addvars['limitfrom'] = 1;
                        break;
                    // Единичная смена статуса элемента
                    case 'changestatus_single' :
                        
                        // Получение целевого статуса
                        $targetstatus = $formdata->{'changestatus_'.(int)$itemids}['changestatus_select'];
                        
                        // Смена статуса
                        $result = $this->dof->modlib('formbuilder')->
                            customfield_status_change((int)$itemids, $targetstatus);
                        if ( $result )
                        {
                            $this->addvars['changestatus_success'] = true;
                        } else 
                        {
                            $this->addvars['changestatus_error'] = true;
                        }
                        break;
                }
            }
            if ( ! $redirectmessages )
            {
                redirect($this->dof->url_im('customfields', '/list.php', $this->addvars));
            }
            redirect($this->dof->url_im('customfields', '/list.php', $this->addvars), implode('<br/>', $redirectmessages), 15);
        }
    }
    
    /**
     * Генерация GET-параметра для фильтра
     * 
     * @param stdClass $formdata - Данные формы
     */
    protected function get_filter_addvar($formdata)
    {
        $filters = [];
        if ( ! empty($formdata->filter_status) )
        {// Фильтрация по статусу
            $statuses = implode(',', (array)$formdata->filter_status);
            $filters['status'] = $statuses;
        }
        if ( ! empty($formdata->filter_linkpcode) )
        {// Фильтрация по справочнику
            $filters['linkpcode'] = $formdata->filter_linkpcode;
        }
        
        $addvar = '';
        foreach ( $filters as $filtername => $filterdata )
        {
            $addvar .= $filtername.':'.$filterdata.';';
        }
        return $addvar;
    }
}