import $ from 'jquery';

/**
* Настройка popover'ов так, чтобы при перемещении фокуса на сам popover, он не исчезал
*/
const adjustPopovers = () => {
    $(document).on('hide.bs.popover', e => {
       var switcher = $(e.target);
       var popover = $('#' + switcher.attr('aria-describedby'));
       popover.on('mouseleave', () => { switcher.focus(); });
       if (popover.is(':hover')) {
           e.preventDefault();
       }
   });
};

// Донастройка поповеров
adjustPopovers();