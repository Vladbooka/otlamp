//этот файл на данный момент не используется.
//функция detectingFlash содержит скрипт, позволяющий определить наличие flash player в браузере


init_check(); 
//инициализация проверки, в зависимости от наличия флеш открывает ролики
//или выводит сообщение
function init_check()
{
    
    //получим код содержимого страницы)
    var page = document.getElementById('page-content');

    if (typeof page.innerHTML !== 'string')
    {
        return null;
    }

    //проверка включен ли плагин flash в браузере
    var flash = detectingFlash();
    if (flash == true)
    {
        //если флеш есть - отобразить все контейнеры с видео
        var pagewithflv = show_flv_video(page.innerHTML);
        //page.innerHTML = pagewithflv;
        replaceHtml(page,pagewithflv);

    }
    else
    {
        //если флеша нет, вместо первого ролика выведем сообщение, о том что не включен флеш
        //var html = page.innerHTML;
        //page.innerHTML = page.innerHTML.replace(/<span id="core_media_flv_(.*?)<\/span>/,
          //      M.str.filter_rmlinksflv.check_flash);
         alert('Для отображения видео включите Shockwave Flash в настройках браузера');
        //page.innerHTML = html;

    }

}


//проверка, включен ли flash в браузере
//возвращает true, если есть флеш
function detectingFlash() {
    //предположим что флеша нет
    var flashinstalled = false;
    //получаем объект со списком плагинов браузера, проверяем по названию браузера
    if (navigator.plugins) {
        if (navigator.plugins["Shockwave Flash"]) {
            flashinstalled = true;
        }
        else if (navigator.plugins["Shockwave Flash 2.0"]) {
            flashinstalled = true;
        }
    }//если браузер не вернул список плагинов, проверяем по поддерживаемым типам данных
    else if (navigator.mimeTypes) {
        var x = navigator.mimeTypes['application/x-shockwave-flash'];
        if (x && x.enabledPlugin) {
            flashinstalled = true;
        }
    }
    else {
        // на всякий случай возвращаем true в случае некоторых экзотических браузеров
        flashinstalled = true;
    }
    return flashinstalled;
}

//раскрыть все div с flv-видео( убрать style="display:none;")
function show_flv_video(html)
{
    var replaced = str_replace('class="mediaplugin mediaplugin_flv" style="display:none;"', 'class="mediaplugin mediaplugin_flv"', html);
    return replaced;
}

//заменить html в элементе
function replaceHtml(el, html) {
	var oldEl = typeof el === "string" ? document.getElementById(el) : el;

	var newEl = oldEl.cloneNode(false);
	newEl.innerHTML = html;
	oldEl.parentNode.replaceChild(newEl, oldEl);
	return newEl;
}


//функция str_replace, аналог функции в php
function str_replace(search, replace, subject) {	// Replace all occurrences of the search string with the replacement string
    // 
    // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   improved by: Gabriel Paderni

    if (!(replace instanceof Array)) {
        replace = new Array(replace);
        if (search instanceof Array) {//If search	is an array and replace	is a string, then this replacement string is used for every value of search
            while (search.length > replace.length) {
                replace[replace.length] = replace[0];
            }
        }
    }

    if (!(search instanceof Array))
        search = new Array(search);
    while (search.length > replace.length) {//If replace	has fewer values than search , then an empty string is used for the rest of replacement values
        replace[replace.length] = '';
    }

    if (subject instanceof Array) {//If subject is an array, then the search and replace is performed with every entry of subject , and the return value is an array as well.
        for (k in subject) {
            subject[k] = str_replace(search, replace, subject[k]);
        }
        return subject;
    }

    for (var k = 0; k < search.length; k++) {
        var i = subject.indexOf(search[k]);
        while (i > -1) {
            subject = subject.replace(search[k], replace[k]);
            i = subject.indexOf(search[k], i);
        }
    }

    return subject;

}
