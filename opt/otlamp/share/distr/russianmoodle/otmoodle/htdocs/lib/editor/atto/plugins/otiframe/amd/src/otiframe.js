import $ from 'jquery';

// При установке высоты фрейма точь-в-точь, как пришло изнутри, может выйти так, что по ширине контент не умещается.
// Из-за этого появляется полоса прокрутки. Из-за этого уже и по высоте не помещается - появляется еще одна прокрутка.
// Поэтому мы делаем высоту фрейма больше на размер указанный здесь, в extraHeight.
// Но как следствие документ отображаемый в iframe может отловить изменение размера и послать вновь модифицированный размер.
// Поэтому это же значение используется и в качестве порога изменения высоты, на которое мы не реагируем.
// Если высота увеличилась менее чем на 30px - ничего не делаем. Больше или уменьшилась - ставим переданную + extraHeight.
const extraHeight = 30;

// Требуется ли пытаться подстраивать ширину под контент
// По умолчанию ширина 100%
// Пока опция отключена, так как при уменьшении ширины, обратно контент не попросится стать шире
const adjustWidth = false;

// Есть пример, когда документ внутри iframe при изменении размера iframe зачем-то меняет свою ширину (obed.ru)
// Передает ширину - ставим её, ловит изменение - передает значение поменьше и т.д., пока не получится мобильный вид :)
// На такой случай теперь имеется вот такая константа, определяющая порог изменения ширины, меньше которого мы не реагируем.
const ignoreDiffWidth = 20;

// В случае, если циклическое изменение размеров все же запускается, разработан защитный механизм
// Он не позволяет запускать последовательно изменение размеров больше указанного в этой константе количества раз
// Этот счетчик сбрасывается при изменении размеров окна и по таймеру
const changesInSequence = 5;

// Счетчик changesInSequence сбрасывается при изменении размеров окна и по таймеру
// Задержка перед сбросом счетчика указывается в константе delayToClearAfterSequence
const delayToClearAfterSequence = 100;

/**
* Обработка otiframe'ов.
*/
export const init = () => {

    /*
     * мы не можем в редакторе сразу задать src, так как в него будет положен draftfile,
     * у которого принудительно forcedownload,
     * поэтому перекладываем путь из data-source в src
     */
    $('.otiframe').each((index, otiframe) => {
        var source = $(otiframe).attr('src');
        var sourceDefined = (typeof source !== 'undefined' && source !== false);
        var dataSource = $(otiframe).data('source');
        var dataSourceDefined = (typeof dataSource !== 'undefined' && dataSource !== false);
        if (!sourceDefined && dataSourceDefined) {
            /* направляем на проксирующий скрипт, в котором планируем в будущем задавать высоту */
            var localurl = '/lib/editor/atto/plugins/otiframe/file.php?ois='+encodeURIComponent(dataSource);
            $(otiframe)
                .removeAttr('srcdoc')
                .attr({
                    'src': localurl,
                    'frameborder': 0
                })
                .on('load', function(){
                    $(this).addClass('loaded');
                    otiframeAutoHeight(this);
                });
            setChangesCounter(otiframe, 0, 0);
        }
    });

    // При изменении размеров окна сбрасываем счетчик изменений,
    // чтобы вновь обработать сообщение фрейма что бы ни было ранее
    window.onresize = function(){
        $('.otiframe').each((index, otiframe) => {
            setChangesCounter(otiframe, 0, 0);
            otiframeAutoHeight(otiframe);
        });
    };
    // Обработчик сообщений из iframe
    window.addEventListener('message', processFrameMessage);
};

const setChangesCounter = (otiframe, heightChanges, widthChanges) => {
    $(otiframe).data('heightChanges', heightChanges);
    $(otiframe).data('widthChanges', widthChanges);
};

const otiframeAutoHeight = (otiframe) => {
    if ($(otiframe).hasClass('autosize')) {
        // Попытка установки размеров iframe по контенту. Может не работать с cross-origin
        otiframe.style.height = (otiframe.contentWindow.document.body.scrollHeight + extraHeight) + 'px';
        if (adjustWidth) {
            otiframe.style.width = otiframe.contentWindow.document.body.scrollWidth + 'px';
        }
    }
};

/**
 * Обработчик сообщений из iframe
 */
const processFrameMessage = (event) => {

    var frameWidth=null, frameHeight=null;

    if (event.data.hasOwnProperty('frameHeight')) {
        frameHeight = event.data.frameHeight;
    }
    if (event.data.hasOwnProperty('frameWidth')) {
        frameWidth = event.data.frameWidth;
    }
    if (frameWidth === null && frameHeight === null) {
        return;
    }

    // Определение DOM-элемента, приславшего сообщение
    var sourceFrame = getFrameMessageOwner(event);
    if (sourceFrame === null) {
        return;
    }

    if ($(sourceFrame).hasClass('autosize')) {

        // Очистка счетчика количества изменений по таймеру
        if ($(sourceFrame).data('inactiveTimer')) {
            clearTimeout($(sourceFrame).data('inactiveTimer'));
        }
        $(sourceFrame).data('inactiveTimer', setTimeout(function(){
            setChangesCounter(sourceFrame, 0, 0);
        }, delayToClearAfterSequence));

        var heightChanges = $(sourceFrame).data('heightChanges');
        var widthChanges = $(sourceFrame).data('widthChanges');

        // изменение высоты
        if (frameHeight !== null) {
            var diffHeight = frameHeight - sourceFrame.scrollHeight;
            var allowedDiff = (diffHeight < 0 || diffHeight > extraHeight);
            window.console.log(sourceFrame, frameHeight, sourceFrame.scrollHeight, allowedDiff, heightChanges, changesInSequence);
            if (allowedDiff && heightChanges < changesInSequence) {
                sourceFrame.style.height = (frameHeight+extraHeight)+'px';
                heightChanges++;
            }
        }

        // Изменение ширины
        if (frameWidth !== null && adjustWidth) {
            var diffWidth = frameWidth - sourceFrame.scrollWidth;
            var allowedDiff = (diffWidth < -ignoreDiffWidth || diffWidth > ignoreDiffWidth);
            if (allowedDiff && widthChanges < changesInSequence) {
                sourceFrame.style.width = frameWidth+'px';
                widthChanges++;
            }
        }

        // Обновление счетчика изменений размеров
        setChangesCounter(sourceFrame, heightChanges, widthChanges);
    }
};

/**
 * Определение DOM-элемента, приславшего сообщение
 */
const getFrameMessageOwner = (event) => {

    var sourceFrame = null; // this is the IFRAME which send the postMessage

    var myFrames = document.getElementsByTagName("IFRAME");
    var eventSource = event.source; // event is the event raised by the postMessage
    var eventOrigin = event.origin; // origin domain, e.g. http://example.com

    // detect the source for IFRAMEs with same-origin URL
    for (var i=0; i<myFrames.length; i++) {
        var f = myFrames[i];
        if (f.contentWindow==eventSource || // for absolute URLs
            f.contentWindow==eventSource.parent) { // for relative URLs
            sourceFrame = f;
            break;
        }
    }

    // detect the source for IFRAMEs with cross-origin URL
    // (because accessing/comparing event.source properties is not allowed for cross-origin URL)
    if (sourceFrame === null) {
        for (var i=0; i<myFrames.length; i++) {
            if (myFrames[i].src.indexOf(eventOrigin)==0) {
                sourceFrame = myFrames[i];
                break;
            }
        }
    }

    return sourceFrame;
};