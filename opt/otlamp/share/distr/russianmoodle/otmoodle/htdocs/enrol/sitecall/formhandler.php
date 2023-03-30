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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * Плагин подписки через форму связи с менеджером,
 * javascript обработчик формы
 *
 * @package enrol
 * @subpackage sitecall
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once ("../../config.php");
require_once ($CFG->dirroot . '/enrol/sitecall/forms.php');

/**
 * Переменная содержащая ключ формы, отображающейся с края страницы
 * @type {String}
 */

if ( empty($_GET['sideform']) )
{
    $sideform_key = '';
} else
{
    $sideform_key = $_GET['sideform'];
}

if ( empty($_GET['pos']) )
{
    $sideform_position = 'right';
} else
{
    $sideform_position = $_GET['pos'];
}

// Путь до шаблона модального окна
$modal_template = $CFG->dirroot . '/enrol/sitecall/templates/modal.php';

/**
 * Функция читает html из файла и возвращает
 * его в приемлимом для js виде
 *
 * @param string $path - путь до файла
 * @return string - обработаный html
 */
function html_to_js($path)
{
    ob_start();
    include $path;
    $content = ob_get_clean();
    return '"' . preg_replace('/(\n\s*)/si', '" + "', addslashes($content)) . '"';
}
?>

( function ( window ) {

var
  /**
   * Конфигурация закрытия модального окна по клику на фон
   * @type {Boolean}
   */
  closeOnBgTap = true,
  /**
   * Конфигурация вывода окна успешной отправки формы
   * @type {Boolean}
   */
  detachOnSubmit = true,
  /**
   * Список страниц, на которых
   * запрещен запуск скрипта
   * @type {Array}
   */
  allowPages = [

  ],
  /**
   * Список подключаемых стилей
   * @type {Array}
   */
  stylesheets = [

  ],
  /**
   * Ключ формы боковой кнопки
   * @type {String}
   */
  sideFormKey = '<?php echo $sideform_key; ?>',
  /**
   * Позиция боковой кнопки
   * @type {String}
   */
  sideFormPos = '<?php echo $sideform_position; ?>',
  /**
   * HTML форм
   * @type {Object}
   */
  formsHTML = {<?php
$courseid=(empty($_GET['cid'])?1:$_GET['cid']);
foreach ( $forms as $key => $form )
{
    // Получаем код всех форм
    echo '"' . $key . '" : ' . $form->getHtmlForm($courseid) . ',';
}
?>},
  submitsHTML = {<?php
foreach ( $forms as $key => $form )
{
    // Получаем код всех форм
    echo '"' . $key . '" : ' . $form->getHtmlOk() . ',';
}
?>},
  /**
   * Конструктор для класса форм
   * @param  {String} key   Ключ формы
   * @param  {String} html  Html формы
   * @param  {Boolean} detachOnSubmit
   */
  siteCallForm = function ( key, html, detachOnSubmit ) {
    this.disabled = false;
    this.beforeDiasble = null;
    this.detachOnSubmit = detachOnSubmit;
    /**
     * Ключ формы
     * @type {String}
     */
    this.key = key;
    /**
     * Статус формы
     * error - по умолчанию чтобы нельзя было
     * отправить пустую форму
     * @type {String}
     */
    this.status = "error";
    /**
     * Обертка формы
     * @type {Object}
     */
    this.form = document.createElement( 'div' );
    /**
     * Объект содержащий все активные поля формы
     * в виде :
     *  name : {
     *    field     : element,
     *    state     : last status update
     *    stateElem : element
     *  },
     *  SCformSumit  : submit element,
     *  SCformStatus : text status element
     * @type {Object}
     */
    this.items  = {};
    /**
     * Опциональные данные формы
     * @type {Object}
     */
    this.optValues = {};
    /**
     * События формы
     * @type {Object}
     */
    this.events = {
      'input' : [],
      /**
       * События при переходе с текстового поля
       */
      'blur' : [],
      /**
       * События перед проверкой данных формы
       */
      'beforecheck' : [],
      /**
       * События после проверки данных формы
       */
      'aftercheck' : [],
      /**
       * События перед отправкой формы
       */
      'beforesubmit' : [],
      /**
       * События при отправке формы
       */
      'submit' : [],
      /**
       * События после отправки формы
       */
      'aftersubmit' : [],
      /**
       * События при ошибке отправки формы
       */
      'errorsubmit' : [],
      /**
       * События при ошибке отправки формы на проверку
       */
      'errorcheck' : [],
    };

    // Укажем форме класс
    this.form.className =  'sc-form-wrapper sc-in-form-' + key;
    // Нвполним ее полученным html
    this.form.innerHTML = html;
    // На всеякий случай сброси кеш формы
    this.form.getElementsByTagName('form')[0].reset();
    // Создадим фрагмент документа, в котором будем
    // хранить форму пока она не отображается на странице
    this.holder = document.createDocumentFragment();
    // Перенесем нашу форму в holder
    this.holder.appendChild( this.form );


    var length, item, items, name, type, timeout, _s = this, i = 0, timer = false,
      check = function ( event ) {
        if (this.disabled) return;
        var outTarget = event.target || event.srcElement;

        _s.fireEvent( event.type, outTarget );
        switch ( event.type ) {
          case 'blur' :
            _s.addValue( 'blurout', outTarget.name );
            setTimeout(function(){ _s.send( 'check' ); }, 400);
            break;
          case 'input' :
            if ( timer ) clearTimeout( timeout );

            timer = true;
            timeout = setTimeout(function(){
              _s.send( 'check' );
              timer = false;
            }, 700);
            break;
        }
      };

    // Проверим наличе поле статуса формы
    item = snark.getByClass( this.form, 'sc-form-status' );
    if (item.length > 0) this.items.SCformStatus = item[0];
    // Найдем в получимвшейся форме все активные поля
    items = snark.getByClass( this.form, 'sc-form-item' );
    length = items.length;
    // Переберем и проверим все полученные поля
    for ( ; i < length; i++ ) {
      item = items[i];
      type = item.type;
      // Если у поля нет имени присвоим ему внутренее имя по порядоковому номеру
      name = ( item.name != "" || item.name != undefined || item.name != null ) ? item.name : i;

      if ( !this.items[ name ] ) this.items[ name ] = {};
      // Составим внутренний объект содержащий все поля
      // данной формы в виде: имя => поле.
      // Для полей типа радио кнопок будем создавать массив полей
      // для того чтобы было легче анализировать результаты
      if ( /(radio|checkbox)/.test( item.type ) ) {
        if ( !this.items[ name ].field ) this.items[ name ].field = [];
        this.items[ name ].field.push( item );
      } else {
        this.items[ name ].field = item;
        // Для всех полей кроме чекбоксов, радиокнопок и селектов
        // установим обработчик, который будет отсылать данные на проверку
        if ( !/(select|radio|checkbox)/.test( item.type ) ) {
          snark.bind( item, 'blur', check );
          snark.bind( item, 'input', check );
        }
      }
    };
    // Найдем все кнопки отправки
    item = snark.getByClass( this.form, 'sc-form-submit' );
    // Если найден хоть одна кнопка
    // поставим на нее обработчик отправки по клику
    if ( item.length > 0 ){
      this.items.SCformSubmit = item[0];

      // Сделаем кнопку не активной так как статус - error
      snark.addClass( this.items.SCformSubmit, 'disabled' );
      snark.bind( this.items.SCformSubmit, 'mouseup', function ( event ) {
        _s.send( 'send' );
      });
    };
    // Подключим обработчик нажатий клавишь для
    // отправки данных формы по нажатию enter
    snark.bind( this.form, 'keyup', function( event ) {
      var code = event.charCode || event.keyCode;
      var target = event.target || event.srcElement;

      if ( code === 13 && !/textarea/.test( target.type ) ) {
        _s.send( 'send' );
      }
    });

    snark.bind( this.form.getElementsByTagName('form')[0], 'submit', function( event ) {
      if ( !event.preventDefault )
        event.preventDefault = function ( ) { this.returnValue = false };
      event.preventDefault();
    })
  },

  /**
   * Конструктор модального окна
   * TODO: проверка z-index
   *   можно сделать из конструкторая контроллер
   * @return {[type]} [description]
   */
  siteCallModal = function ( submitsHTML, closeOnBgTap ) {
    var closeButtons, openButtons,
      _s = this,
      fragment   = document.createDocumentFragment(),
      // Создадим все необходимые элементы для отображения модального окна
      background = document.createElement( 'div' ),
      wrapper    = document.createElement( 'div' );

    if ( submitsHTML ) {
      var key, html, element;

      this.holder = document.createDocumentFragment();
      this.submits = {};

      for ( key in submitsHTML ){
        submit = document.createElement('div');
        submit.innerHTML = submitsHTML[key] ;

        this.submits[key] = { element : submit };

        element = snark.getByClass( submit, 'sc-header' );
        if (element.length > 0) this.submits[key].header = element[0];

        element = snark.getByClass( submit, 'sc-success-message' );
        if (element.length > 0) this.submits[key].textarea = element[0];

        element = snark.getByClass( submit, 'sc-modal-close' );
        if (element.length > 0)
          for (var i = element.length - 1; i >= 0; i--)
            snark.bind( element[i], 'click', function() { _s.close() } );

        this.holder.appendChild( submit );

      }

    } else {
      this.submits = false;
    }

    // Присвоим эелементам модального окна базовые классы
    // для того чтобы их можно было стидизовать с помощю css
    background.className = "sc-modal-background sc-invisible";
    wrapper.className    = "sc-modal-wrapper sc-invisible sc-clearfix";
    // Получим html самого модального окна
    wrapper.innerHTML = <?php echo html_to_js($modal_template); ?>;

    _s.wrapper = wrapper;
    _s.background = background;
    // Найдем в полученном html элемент, в который мы будем помещать содержимое
    _s.modal = snark.getByClass( wrapper, 'sc-modal' )[0];

    // Найдем в модальном окне все элемнты закрывающие окна
    // и проставим на них обработчики
    closeButtons = snark.getByClass( wrapper, 'sc-modal-close');
    for (var i = closeButtons.length - 1; i >= 0; i--)
      snark.bind( closeButtons[i], 'click', function ( event ) {
          var target = event.target || event.srcElement;
          _s.close.call( _s, event, target );
        }
      );
    // Поставим обработчик закрытия на фон маодльного окна
    if (closeOnBgTap)
      snark.bind( background, 'click', function () {_s.close(); });
    // Найдем на странице все элементы открывающие модальное окно
    // и проставим на них обработчики
    openButtons  = snark.getByClass( document, 'sc-modal-open' );
    for ( i = openButtons.length - 1; i >= 0; i--)
      snark.bind( openButtons[i], 'click', function ( event ) {
          var target = event.target || event.srcElement;
          _s.open.call( _s, event, target )
        }
      );
    // Включим автоматическое позиционирование модального окна
    // при смене размера окна браузера и при открытии окна
    snark.bind( window, 'resize', function () {_s.resize() });
    this.bind('open', function () {_s.resize() });

    // Добавим модальное окно в конец документа
    fragment.appendChild( wrapper );
    fragment.appendChild( background );
    document.body.appendChild( fragment );
  },
  /**
   * Небольшавя библиотека необходимых функций
   * @type {Object}
   */
  snark = {
    base_url : window.location.protocol+'//'+window.location.hostname,
    /**
     * Объект содержит данные о поддержке
     * базовых функций браузером пользователя
     * @type {Object}
     */
    support : {
      getByClass : !!( document.getElementsByClassName )
    },
    /**
     * Функция запускающая скрипт сразу
     * после полной загрузки документа
     * @param  {Function} event скрипт для запуска
     */
    onDocumentReady : function ( event ){
      var recall;

      if ( document.addEventListener ) {
        recall = function () {
          document.removeEventListener( "DOMContentLoaded", recall, false );
          event.call(document, document);
        };
        document.addEventListener( "DOMContentLoaded", recall, false );
      } else if ( document.attachEvent ) {
        recall = function () {
          if ( document.readyState === "complete" ) {
              document.detachEvent( "onreadystatechange", recall );
              event.call(document, document);
          }
        };
        document.attachEvent("onreadystatechange", recall );
      }
    },
    /**
     * Функция получает элементы по имени класса
     * @param  {Object} context   контекст поиска элементов
     * @param  {String} className Искомое имя класса
     * @return {Array}            Массив найденых элементов
     */
    getByClass : function ( context, className ) {
      if ( this.support.getByClass ) {
        return context.getElementsByClassName( className );
      } else {
        var elems, i,
          result = [];

        elems = context.getElementsByTagName( '*' );
        for ( i in elems ) {
          if( (' ' + elems[i].className + ' ').indexOf(' ' + matchClass + ' ') > -1 )
            result.push( elems[i] );
        }

        return result;
      }
    },
    /**
     * Функция добавляющая класс элементу
     * @param {Object} element   Элемент
     * @param {String} className Класс
     */
    addClass : function ( element, className ) {
      var classRegExp, className,
        i = 1,
        length = arguments.length;

      for ( ; i <= length - 1; i++ ) {
        className = arguments[i];

        classRegExp = new RegExp("(^|\\s)" + className + "(\\s|$)", "g");
        if ( classRegExp.test( element.className ) ) continue;

        element.className = element.className + " " + className;
      }

      return element;
    },
    /**
     * Функция удаляющаяя класс у элемента
     * @param {Object} element   Элемент
     * @param {String} className Класс
     */
    removeClass : function ( element, className ) {

      var classRegExp, className,
        i = 1,
        length = arguments.length;

      for ( ; i <= length - 1; i++ ) {
        className = arguments[i];

        classRegExp = new RegExp("(^|\\s)" + className + "(\\s|$)", "g");
        if ( !classRegExp.test( element.className ) ) continue;

        element.className = element.className.replace(classRegExp, "$1").replace(/\s+/g, " ").replace(/(^ | $)/g, "");
      }

      return element;
    },
    /**
     * Функция для корректной установки обработчиков событий
     * @param  {Object}   element Элемент, на который необходимо проставить обработчик
     * @param  {String}   event   Название обрабатиываемого события
     * @param  {Function} recall  Функция - обработчик
     */
    bind : function ( element, event, recall ) {
      if( element.addEventListener ){
        element.addEventListener( event, recall, false );
      } else if ( element.attachEvent ) {
        element.attachEvent( "on"+event, recall );
      }

      return element;
    },
    /**
     * Функция позволяющая отправлять синхронные и ассинхронные запросы
     * @param  {Object}  conf  Объект конфигурации запроса
     * @param  {Boolean} async Опция указания наобходимости ассинхронного запроса
     */
    ajax : function ( conf, async ) {
      var xmlhttp,
         data = [];

      try {
        xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
      } catch (e) {
        try {
          xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        } catch (E) {
          xmlhttp = false;
        }
      }
      if (!xmlhttp && typeof XMLHttpRequest!='undefined') {
        xmlhttp = new XMLHttpRequest();
      }

      if ( conf.data ) {
        for ( var i in conf.data )
          data.push( i + "=" + conf.data[i] );

        data = data.join("&");
      } else {
        data = null;
      }

      xmlhttp.open( conf.type || "GET", conf.url, async || true );

      if ( conf.type == "POST" && data != null )
        xmlhttp.setRequestHeader( "Content-type","application/x-www-form-urlencoded" );

      xmlhttp.onreadystatechange = function(){

        if (xmlhttp.readyState != 4) return;

        if (xmlhttp.status == 200) {
          if ( conf.ready )
            conf.ready.call( xmlhttp, xmlhttp.responseText );
        } else {
          if ( conf.error )
            conf.error.call( xmlhttp, xmlhttp.statusText, xmlhttp.responseText );
        }

      }
      xmlhttp.send( data );

      setTimeout(function () {
        xmlhttp.abort();

        if ( conf.timmeout )
          conf.timmeout.call( xmlhttp, "Request timed out", xmlhttp.responseText );

      }, conf.timeout || 10000);

    },
    /**
     * Функция возвращает тип переменной
     * @param  {Mixed}  variable Проверяемая переменная
     * @return {String}          Тип переменной
     */
    type : function ( variable ) {
      var name,
        class2type = {},
        toString = class2type.toString,
        classes = "Boolean Number String Function Array Date RegExp Object Error".split(" "),
        i = 0;

      for ( ; i < classes.length; i++ ) {
        name = classes[ i ];
        class2type[ "[object " + name + "]" ] = name.toLowerCase();
      };

      if ( variable == null ) {
        return variable + "";
      }
      return typeof variable === "object" || typeof variable === "function" ?
        class2type[ toString.call(variable) ] || "object" :
        typeof variable;
    }
  };
/**
 * [siteCallForm.prototype]
 * @type {Object}
 */
siteCallForm.prototype = {
  /**
   * Объект содержащий сопоставление статуса и класса
   * @type {Object}
   */
  classNames : {
    'ok'    : 'sc-item-ok',
    'error' : 'sc-item-error',
  },
  /**
   * Функция добавляет форму в элемент
   * @param  {Object} element Элемент, в который необходимо поместить форму
   */
  insertInto : function ( element ) {
    element.appendChild( this.form );
  },
  /**
   * Функция возвращает форму в holder
   */
  detach : function () {
    this.holder.appendChild( this.form );
  },
  /**
   * Добавляет опциональное значение к данным формы
   * @param {String} name  Имя для данного значения
   * @param {Mixed}  value Передаваемое значение
   */
  addValue : function ( name, value ) {
    this.optValues[ name ] = value;
  },

  disable : function () {
    var name, item;

    this.beforeDiasble = this.status;
    this.status = 'error';

    snark.addClass ( this.form, 'sc-form-disabled' );
    snark.addClass ( this.items.SCformSubmit, 'disabled');

    for ( name in this.items ){
      if ( /(SCformSubmit|SCformStatus)/.test(name) ) continue;
      item = this.items[ name ];
      if ( snark.type( this.items[ name ].field ) == 'array' ) {
        for (var i = item.field.length - 1; i >= 0; i--) {
          item.field[ i ].disabled = true;
          snark.addClass( item.field[ i ], 'sc-field-disabled' );
        };
        if ( item.wrapper && item.wrapper != null && item.state.status )
          snark.removeClass( item.wrapper, this.classNames[ item.state.status ] );
      } else {
        if (item.field.value === "") item.field.value = " ";
        item.field.disabled = true;
        snark.addClass( item.field, 'sc-field-disabled' );
        if ( item.state )
          if ( item.state.status )
            snark.removeClass( item.field, this.classNames[ item.state.status ] );
      }
      if ( item.stateElem )
        item.stateElem.innerHTML = "";
    }

    this.disabled = true;
  },

  enable : function () {
    var name, item;

    this.status = this.beforeDiasble;

    snark.removeClass ( this.form, 'sc-form-disabled' );
    if (this.status === 'ok')
      snark.removeClass (this.items.SCformSubmit, 'disabled');

    for ( name in this.items ){
      if ( /(SCformSubmit|SCformStatus)/.test(name) ) continue;
      item = this.items[ name ];
      if ( snark.type( item.field ) == 'array' ) {
        for (var i = item.field.length - 1; i >= 0; i--) {
          item.field[ i ].disabled = false;
          snark.removeClass( item.field[ i ], 'sc-field-disabled' );
        };
        if ( item.wrapper && item.wrapper != null && item.state.status )
          snark.addClass( item.wrapper, this.classNames[ item.state.status ] );
      } else {
        item.field.disabled = false;
        if (item.field.value === " ") item.field.value = "";
        snark.removeClass( item.field, 'sc-field-disabled' );
        if ( item.state )
          if ( item.state.status )
            snark.addClass( item.field, this.classNames[ item.state.status ] );
      }
      if ( item.stateElem )
        if ( item.state )
          if ( item.state.text )
            item.stateElem.innerHTML = item.state.text;
    }

    this.disabled = false;
  },
  /**
   * Возвращает значения полей
   * @return {Object} значения полей формы
   */
  getValues : function ( ) {
    var name,
      values = {};

    for ( name in this.items ){
      if ( /(SCformSubmit|SCformStatus)/.test(name) ) continue;
      if ( snark.type( this.items[ name ].field ) == 'array' ) {
        values[ name ] = [];
        for (var i = this.items[ name ].field.length - 1; i >= 0; i--) {
          if ( this.items[ name ].field[ i ].checked )
            values[ name ].push( this.items[ name ].field[ i ].value );
        };
      } else {
        values[ name ] = this.items[ name ].field.value;
      }
    }

    for ( name in this.optValues )
      values[ name ] = this.optValues[ name ];

    values[ 'form_key' ] = this.key;
    return values;
  },
  /**
   * Функция очищает значения полей включая дополнительные
   */
  clearValues : function () {
    var i, item;
    for ( name in this.items ){
      if ( /(SCformSubmit|SCformStatus)/.test(name) ) continue;
      if ( snark.type( this.items[ name ].field ) == 'array' ) {
        for (var i = this.items[ name ].field.length - 1; i >= 0; i--) {
          if ( this.items[ name ].field[ i ].checked )
            this.items[ name ].field[ i ].checked = false;
        };
        if ( this.items[ name ].wrapper && this.items[ name ].wrapper != null )
          snark.removeClass( this.items[ name ].wrapper, this.classNames[ 'ok' ] );
      } else {
        snark.removeClass( this.items[ name ].field, this.classNames[ 'ok' ] );
        if ( this.items[ name ].stateElem ) {
          this.items[ name ].stateElem.innerHTML = "";
          snark.removeClass( this.items[ name ].stateElem, this.classNames[ 'ok' ] )
        }
        if ( !/select/.test(this.items[ name ].field.type) ) this.items[ name ].field.value = "";
      }
    }

     if ( this.items.SCformStatus ) {
      snark.removeClass(
        this.items.SCformStatus,
        this.classNames[ 'ok' ]
      );
      this.items.SCformStatus.innerHTML = "";
    }

    this.optValues = {};
    this.status = 'error';
  },
  /**
   * Функция смены статусов формы
   * @param  {Object} status объект статусов формы
   */
  check : function ( status ) {
    if (this.disabled) return;
    var fieldStatus, element, statusElement, cache, formStatus;
    // Переберем все поля
    for ( var name in this.items ) {
      if ( /(SCformSubmit|SCformStatus)/.test(name) ) continue;
      // Если для поля есть статус
      if ( status[ name ] ) {
        // Если хоть одно поле пришло со статусом ошибки установим
        // статус формы - ошибка
        if ( status[name].status === 'error' ) formStatus = 'error';

        status[name].text = status[name].text || "";
        // Выберем элемент для отображения текста
        statusElement = this.items[ name ].stateElem || false;
        // Если кеш статусов не пуст и кеш для этого элемента
        // совпадает с переданымим значениями - пропустим шаг
        cache = this.items[ name ].state || { text : '', status : '' };
        if ((cache.text   === status[name].text || statusElement === null )
          && cache.status === status[name].status ) continue;

        // Если статус отличается сменим класс у поля
        if ( cache.status != status[name].status ) {
          if ( snark.type( this.items[ name ].field ) == 'array' ){
            if ( !this.items[ name ].wrapper && this.items[ name ].wrapper != null ) {
              element = snark.getByClass ( this.form, 'sc-' + name + '-wrapper' );
              element = ( element.length > 0 ) ? element[0] : false;
              this.items[ name ].wrapper = ( element ) ? element : null;
            } else {
              if ( this.items[ name ].wrapper != null )
                element = this.items[ name ].wrapper;
              else
                element = false;
            }
          } else {
            element = this.items[ name ].field;
          }

          if ( element ) {
            snark.removeClass( element, this.classNames[ cache.status ] );
            snark.addClass( element, this.classNames[ status[name].status ] );
          }
        }
        // Если элемент для отображения текста еще не выбирался - найдем его
        if ( !statusElement && statusElement != null ) {
          element = snark.getByClass( this.form, 'sc-status-' + name );
          if ( element.length > 0 )
            this.items[ name ].stateElem = statusElement = element[0];
          else
            this.items[ name ].stateElem = statusElement = null;
        }
        // Если есть куда отображать текст статуса
        if ( statusElement != null ) {
          // Сменим текст поля статуса если он отличается
          if ( cache.text != status[name].text )
            statusElement.innerHTML = status[name].text;
          // сменим класс поля статуса если он отличается
          if ( cache.status != status[name].status ) {
            snark.removeClass( statusElement, this.classNames[ cache.status ] );
            snark.addClass( statusElement, this.classNames[ status[name].status ] );
          }
        }

        // Запомним текущий статус в кеш
        this.items[ name ].state = {
          text   : status[name].text,
          status : status[name].status
        };

      // Если для поля нет сатуса
      } else {
        // Если в кеше есть значения для этого поля
        if ( this.items[ name ].state ) {
          cache = this.items[ name ].state;
          // Удалим все следы статусов
          if ( snark.type( this.items[ name ].field ) == 'array' )
            element = snark.getByClass ( this.form, 'sc-' + name + '-wrapper' );
          else
            element = this.items[ name ].field;

          snark.removeClass( element, this.classNames[ cache.status ] );

          if ( this.items[ name ].stateElem && this.items[ name ].stateElem != null ) {
            snark.removeClass( this.items[ name ].stateElem, this.classNames[ cache.status ] );
            this.items[ name ].stateElem.innerHTML = "";
          }

          delete this.items[ name ].state;
        }
      }

    }

    if ( formStatus === 'error' ){
      this.status = 'error';
      snark.addClass (this.items.SCformSubmit, 'disabled');
    } else {
      this.status = 'ok';
      snark.removeClass (this.items.SCformSubmit, 'disabled');
    }

    this.fireEvent( 'aftercheck' );
  },
  /**
   * Пересылает введеные значения формы на сервер
   * @param  {String} type тип передачи
   */
  send : function ( type ) {

    if ( this.disabled || (type === 'send' && this.status === 'error') ) return;

    var
      async = ( type === 'send' ) ? false : true,
      status = type,
      _s = this,
      values = this.getValues( false );

    values.send_type = type;

    if ( type === "send" )
    {
        _s.items.SCformSubmit.disabled = true;
    	_s.items.SCformSubmit.innerHTML = 'Идет отправка…';
    	this.fireEvent( 'submit', values );
	}

    snark.ajax({
      type : "POST",
      url  : snark.base_url + "/enrol/sitecall/formsend.php?id=<?php
    if ( empty($_GET['cid']) )
    {
        echo 1;
    } else
    {
        echo $_GET['cid'];
    }
    ?>",
      data : {
        sitecall : encodeURIComponent(JSON.stringify( values ))
      },
      ready : function ( data ) {
        switch ( type ) {
          case 'check' :
            _s.fireEvent( 'beforecheck', _s.formStatus );
            if ( data != null && data != undefined && data != "") {
              data = JSON.parse( data );
              _s.check( data );
            }
            break;
          case 'send' :
            if ( data != null && data != undefined && data != "") {
              data = JSON.parse( data );

              if ( _s.items.SCformStatus )
                snark.removeClass(
                    _s.items.SCformStatus,
                    _s.classNames[ 'error' ],
                    _s.classNames[ 'ok' ]
                  );

              if ( data.form ) {
                if ( data.form.status ){
                  snark.addClass( _s.items.SCformStatus, _s.classNames[ data.form.status ] );

                  status = data.form.status;

                  if ( status === 'error' ) {
                    _s.fireEvent( 'beforecheck', _s.formStatus );
                    _s.check( data );
                  } else {
                    if (!_s.detachOnSubmit) {

                      _s.disable();

                      if ( data.form.text )
                        _s.items.SCformStatus.innerHTML = data.form.text;

                    } else {
                      _s.clearValues();
                    }
                  }
                }


              }
            }
            _s.fireEvent( 'aftersubmit', status, data );
            break;
        }
        _s.items.SCformSubmit.disabled = false;
    	_s.items.SCformSubmit.innerHTML = <?php echo '\'' . get_string('form_submit','enrol_sitecall') . '\''?>;
      },
      error : function ( timestamp ) {
        switch ( type ) {
          case 'check' :
            _s.fireEvent( 'errorcheck', timestamp );
            break;
          case 'send' :
            _s.fireEvent( 'errorsubmit', timestamp );
            break;
        }
        _s.items.SCformSubmit.disabled = false;
    	_s.items.SCformSubmit.innerHTML = <?php echo '\'' . get_string('form_submit','enrol_sitecall') . '\''?>;
      }
    });
  },
    /**
   * Проставляет обработчики для событий форм
   * @param  {String}   eventName Имя события
   * @param  {Function} recall    Обработчик
   */
  bind : function ( eventName, recall ) {
    if (!this.events[ eventName ]) return;
    var isset = false;
    for (var i = this.events[ eventName ].length - 1; i >= 0; i--)
      if ( this.events[ eventName ][i].toString() === recall.toString() ){
        isset = true; break
      };

    if ( !isset )
      this.events[ eventName ].push( recall );
  },
  /**
   * Запускает обработчики при срабатывании триггера на событие форм
   */
  fireEvent : function ( eventName, data ) {

    var recall, timestamp, i = 0,
      args = Array.prototype.slice.call(arguments),
      resp = true,
      length = this.events[ eventName ].length;

    args.splice(0, 1)

    for ( ; i <= length - 1; i++ ){
      recall = this.events[ eventName ][ i ];
      timestamp = +( new Date() )+'';

      if ( recall.apply( this, [ timestamp ].concat(args) ) === false ) resp = false;
    }

    return resp;
  }
};
/**
 * [prototype description]
 * @type {Object}
 */
siteCallModal.prototype = {
  timer : false,
  /**
   * Элемент обертки модального окна
   */
  wrapper : null,
  /**
   * Элемент модально окна
   */
  modal : null,
  /**
   * Элемент фона модального окна
   */
  background : null,
  /**
   * Ключ текущей формы в модальном окне
   * @type {String}
   */
  currentFormKey : null,
  /**
   * Объект текущей формы модального окна
   */
  form : null,
  /**
   * События модального окна
   * @type {Object}
   */
  events : {
    /**
     * События перед открытием модального окна
     */
    'beforeopen' : [],
    /**
     * События при открытии модального окна
     */
    'open' : [],
    /**
     * События перед закрытием модального окна
     */
    'beforeclose' : [],
    /**
     * События при закрытии модального окна
     */
    'close' : [],
  },
  /**
   * Открывает модальное окно
   * @param  {Object} event  Событие, которым было открыто модальное окно
   * @param  {Object} target Элемен - триггер
   */
  open : function ( event, target ) {

    if ( this.hideSuccessMessage ) delete this.hideSuccessMessage;

    var closeButtons, form,
      _s = this,
      event = event || null,
      target = target || null;

    if ( this.fireEvent( 'beforeopen', target, event ) ) {

      closeButtons = snark.getByClass( this.modal, 'sc-modal-close' );
      for (var i = closeButtons.length - 1; i >= 0; i--)
        snark.bind( closeButtons[i], 'click', function ( event ) {
            var target = event.target || event.srcElement;
            _s.close.call( _s, event, target );
          }
        );

      snark.removeClass( this.wrapper, 'sc-invisible' );
      snark.removeClass( this.background, 'sc-invisible' );

      this.resize();

      form = this.modal.getElementsByTagName('form');
      if ( form.length > 0 ){
       form = form[0]; form[0].focus();
      }


      this.fireEvent( 'open', target, event );

    }

  },
  /**
   * Закрывает модальное окно
   * @param  {Object} event  Событие, которым было закрыто модальное окно
   * @param  {Object} target Элемен - триггер
   */
  close : function ( event, target ) {

    var
      event = event || null,
      target = target || null;
    if ( this.fireEvent( 'beforeclose', target, event ) ) {

    if ( this.submits && this.timer ) {
      this.hideSuccessMessage();
      clearTimeout( this.timer );
      this.timer = false;
    }


      snark.addClass( this.wrapper, 'sc-invisible' );
      snark.addClass( this.background, 'sc-invisible' );

      this.fireEvent( 'close', target, event );

    }

  },
  /**
   * Автоматически позиционирует модальное окно по центру экрана
   */
  resize : function () {
    var
      wrapper = this.wrapper,
      scrollTop = document.documentElement.scrollTop || document.body.scrollTop,
      top  = (window.innerHeight - wrapper.clientHeight)/2,
      left = (window.innerWidth - wrapper.clientWidth)/2;

    wrapper.style.top = wrapper.style.marginTop = 0;

    if ( window.innerHeight > wrapper.clientHeight ){
      wrapper.style.position = 'fixed';
      scrollTop = 0;
    } else
      wrapper.style.position = 'absolute';

    if (top < 0) top = 30;

    wrapper.style.top  = scrollTop + top + 'px';
    wrapper.style.left = left + 'px';
  },
  /**
   * Заполняет окно формой
   * @param  {Object} form Объект формы
   */
  fill : function ( form ) {

    if ( this.currentFormKey === form.key ) return true;
    if ( this.currentFormKey != null ) return false;

    this.currentFormKey = form.key;

    form.insertInto( this.modal );

    return true
  },
  /**
   * [showSuccessMessage description]
   * @return {[type]} [description]
   */
  showSuccessMessage : function ( header, text ) {
    if (!this.submits) return;

    var
      holder = this.holder,
      submit = this.submits[ this.currentFormKey ];

    if (submit.header)
      submit.header.innerHTML = header;

    if (submit.textarea)
      submit.textarea.innerHTML = text;

    this.hideSuccessMessage = function () {

      if (submit.header)
        submit.header.innerHTML = "";

      if (submit.textarea)
        submit.textarea.innerHTML = "";

      holder.appendChild( submit.element );
    }

    this.modal.appendChild( submit.element );
  },
  /**
   * Проставляет обработчики для событий модального окна
   * @param  {String}   eventName Имя события
   * @param  {Function} recall    Обработчик
   */
  bind : function ( eventName, recall ) {
    if (!this.events[ eventName ]) return;

    var
      isset  = false,
      events = this.events[ eventName ];

    for (var i = events.length - 1; i >= 0; i--) {
      if ( events[i].toString() === recall.toString() ) {
        isset = true;
        break;
      }
    }

    if ( !isset ) events.push( recall );

  },
  /**
   * Запускает обработчики при срабатывании триггера на событие модального окна
   * @param  {String}   eventName Имя события
   * @param  {Object}   target    Элемен - триггер
   * @param  {Object}   event     Событие, которым запущено
   */
  fireEvent : function ( eventName, data ) {

    var recall, timestamp, i = 0,
      resp = true,
      args = Array.prototype.slice.call(arguments),
      length = this.events[ eventName ].length;

      args.splice( 0, 1 );

    for ( ; i <= length - 1; i++ ){
      recall = this.events[ eventName ][ i ];
      timestamp = +( new Date() )+'';

      if ( recall.apply( this, [timestamp.substr(0, 10)].concat( args ) ) === false )
        resp = false;
    }

    return resp;
  }
};

var formCache = function ( ){
  this.date = +( new Date );
  this.vault = {};
  this.lastRequest = null;
}

formCache.prototype = {
  set : function ( key, form ) {
    Object.defineProperty( this.vault, key, {
      enumerable: false,
      configurable: false,
      writable: false,
      value: form
    });
    this.date = +( new Date );
  },

  exists : function ( key ) {
    return this.vault.hasOwnProperty( key );
  },

  get : function ( key ) {
    if (this.vault[ key ])
      return this.vault[ key ];
    else
      return false;
  },
};


snark.onDocumentReady( function () {

  var form, modal, cache,
    siteCallForms = {},
    allow = true,
    css = document.createElement("link"),
    page = snark.base_url + window.location.pathname;

  for (var i = stylesheets.length - 1; i >= 0; i--) {
    css.setAttribute( "rel", "stylesheet" );
    css.setAttribute( "type", "text/css" );
    css.setAttribute( "href", snark.base_url + "/sitecall/css/" + stylesheets[i] );

    document.head.appendChild( css );
    if ( i > 0 ) css = document.createElement("link");
  };

  if ( sideFormKey != '' && formsHTML[ sideFormKey ] ) {

    if ( allowPages ) {
      allow = false;
      for (var i = allowPages.length - 1; i >= 0; i--)
        if ( allowPages[ i ] === page ) allow = true;

      if ( allowPages.length < 1 ) allow = true;
    }

    if ( allow ) {
      var button = document.createElement('div');
      button.className = 'sc-modal-open sc-form-' + sideFormKey + ' sc-side-button sc-side-' + sideFormPos;
      document.body.appendChild( button );
    }

  }

  modal = new siteCallModal( submitsHTML, closeOnBgTap );
  cache = new formCache();


  modal.bind( 'beforeopen', function ( timestamp, target, event ) {
    var header, text,
      fillFine = true,
      match = /sc-form-([_a-z0-9-\\]*)/ig.exec(target.className);

    // TODO: modal.error();
    if ( match == null || !match[1] || !formsHTML[match[1]] ) {
      return false;
    } else {
      if ( modal.currentFormKey != match[1]
        && modal.currentFormKey != null ) {
        cache.get( modal.currentFormKey ).detach();
        modal.currentFormKey = null;
      }

      if ( cache.exists(match[1]) ) {
        form = cache.get( match[1] );
      } else {
        form = new siteCallForm( match[1], formsHTML[ match[1] ], detachOnSubmit );
        form.bind( 'aftersubmit', function ( time, status, data ) {
          if ( status === 'ok') {
            if ( detachOnSubmit ) {
              if (!data.form.header) {
                header = snark.getByClass( this.form, 'sc-header' );
                if (header.length > 0) header = header[0].innerHTML;
              } else {
                header = data.form.header;
              }
              this.detach();
              text = data.form.text || '';
              modal.showSuccessMessage( header, data.form.text || '' );
              modal.currentFormKey = null;
            }
            modal.timer = setTimeout( function () {
              modal.close();
              modal.timer = false;
              if ( detachOnSubmit ) {
                modal.hideSuccessMessage();
              }
            }, 15000 )
          }
        });
        cache.set( match[1], form );
      }

      for (var i = target.attributes.length - 1; i >= 0; i--) {
        if ( /^(data-[_a-z0-9-\\]*)$/.test( target.attributes[i].name ) ) {
          form.addValue(target.attributes[i].name, target.attributes[i].value);
        }
      }
      snark.ajax({
        type : 'POST',
        url : snark.base_url + '/enrol/sitecall/formopen.php',
        data : {
          form_key : match[1]
        },
        ready : function ( data ) {
          form.addValue( 'open_key', data );
        }
      });
      fillFine = modal.fill( form  );

      if ( !fillFine ) return false;
    }

  })

  if ( !detachOnSubmit ) {
    modal.bind( 'beforeclose', function () {
      var form;
      if (this.timer) {
        form = cache.get( this.currentFormKey );
        if (form) {
          form.enable();
          form.clearValues();
          clearTimeout( this.timer )
          this.timer = false;
        };
      }
    })
  }
});

}( window ) );
