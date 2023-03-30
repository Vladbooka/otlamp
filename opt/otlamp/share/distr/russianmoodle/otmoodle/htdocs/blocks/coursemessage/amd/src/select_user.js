define(['jquery', 'core/notification', 'core/templates'], function($, Notification, Templates) {
    return {
        _dialogue: $.Deferred(),
        prevUB: '',
        init: function() {
            var SU = this;
            Templates.renderPix('i/completion-manual-y', 'core', 'yes').then(function(html) {
                SU._dialogue.resolve(html);
            }).catch(Notification.exception);
            var block = $('.block_coursemessage');
            var form = block.find('form');
            var select = block.find('select[name="form_recipient"]').css('display', 'none');
            var prevUB = this.prevUB;
            var userblocks = block.find('.userblock').on('click', function() {
                var UB = $(this);
                select.children('option[value=' + UB.data('id') + ']').prop('selected', true);
                form.css('display', 'block');
                $.when(SU._dialogue).done(function(html) {
                    if (prevUB != '') {
                        prevUB.find('.icon[title="yes"]').remove();
                    }
                    if (prevUB != '' && prevUB.data('id') == UB.data('id')) {
                        UB.find('.icon[title="yes"]').remove();
                        form.css('display', '');
                        prevUB = '';
                    } else {
                        UB.append(html);
                        prevUB = UB;
                    }
                });
            });
            Templates.renderPix('i/completion-manual-n', 'core', 'no').then(function(html) {
                                userblocks.append(html);
                            }).catch(Notification.exception);
        }
    };
});