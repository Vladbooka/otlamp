<?PHP
////////////////////////////////////////////////////////////////////////////
//                                                                        //
// NOTICE OF COPYRIGHT                                                    //
//                                                                        //
// Dean`s Office for Moodle                                               //
// Электронный деканат                                                    //
// <http://deansoffice.ru/>                                               //
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
 * Интерфейс плагинов роутинга статусов
 *
 * @package    workflow
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
interface dof_workflow extends dof_plugin
{
	/** 
	 * Получить код обслуживаемого хранилища
	 * 
     * @return string
	 */
	public function get_storage();
	
    /** 
     * Получить список возможных статусов
     * 
     * @return array
     */
    public function get_list();
    
    /** 
     * Получить локализованное имя статуса
     * 
     * @param string $status - Код статуса
     * 
     * @return string
     */
    public function get_name($status);
    
    /** 
     * Получить список статусов, в которые текущий объект может перейти
     * 
     * @param int $id - ID объекта
     * 
     * @return array|false
     */
    public function get_available($id);
    
    /** 
     * Перевод текущего объекта в указанный статус
     * 
     * @param int $id - ID объекта
     * @param string $newstatus - Статус, в который требуется перевести объект
     * @param array $options - Массив дополнительных опций
     * 
     * @return boolean 
     *      true - удалось перевести в указанное состояние, 
     *      false - не удалось перевести в указанное состояние
     */
    public function change($id, $newstatus, $options = null);
    
    /** 
     * Первичная инициализация объекта
     * 
     * @param int $id - ID объекта
     * 
     * @return boolean 
     *      true - удалось инициализировать состояние объекта 
     *      false - не удалось перевести в указанное состояние
     */
    public function init($id);
}
?>
