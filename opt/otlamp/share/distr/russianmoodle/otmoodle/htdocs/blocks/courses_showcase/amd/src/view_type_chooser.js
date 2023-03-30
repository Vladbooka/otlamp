define(['jquery'], function($) {
    return {
        init: function(){
            var formElement = $('form.mform.block-courses-showcase-js');

            formElement.find('fieldset select#id_config_view_type').change(function() {

                var idValString = '';
                var idElement = formElement.find('input[name=id]');
                if ( idElement.length > 0  ) {
                    idValString = '&id=' + idElement.val();
                }

                var cidValString = '';
                var cidElement = formElement.find('input[name=cid]');
                if ( cidElement.length > 0 ) {
                    cidValString = '&cid=' + cidElement.val();
                }

                var buiValString = '';
                var buiElement = formElement.find('input[name=bui_editid]');
                if ( buiElement.length > 0 ) {
                    buiValString = '&bui_editid=' + buiElement.val();
                }

                var sessValString = '';
                var sessElement = formElement.find('input[name=sesskey]');
                if ( sessElement.length > 0 ) {
                    sessValString = '&sesskey=' + sessElement.val();
                }

                var titleValString = '';
                var titleElement = formElement.find('input[name=config_title]');
                if ( titleElement.length > 0 ) {
                    titleValString = '&title=' + titleElement.val();
                }

                var viewValString = '&viewtype=' +  this.value;

                // При клике на отчет, редиректим для догрузки доп настроек
                window.location.replace(formElement.attr('action') + '?' +
                        buiValString + sessValString + viewValString + idValString + cidValString + titleValString);
            });
        }
    };
});