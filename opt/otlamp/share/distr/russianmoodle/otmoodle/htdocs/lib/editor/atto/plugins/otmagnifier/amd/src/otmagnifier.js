define(['jquery'], function($) {
    return {
        __edge: 50,
        __defaults: {
            'object-fit': 'fill',
            'object-position': '50% 50%'
        },
        __setDefaults: function(img) {
            img.css(this.__defaults);
        },
        __rescalePosition: function(pos, oldmin, oldmax, newmin, newmax, cutedges) {

            var scale = (newmax - newmin) / (oldmax - oldmin);
            var newpos = (((pos - oldmin) * scale) + newmin);
            if (cutedges)
            {
                newpos = (newpos < oldmin ? oldmin : (newpos > oldmax ? oldmax : newpos));
            }
            return newpos;

        },
        __addImgHandlers: function(img){

            var self = this;

            img.on('mousemove', function(event) {

                var width = img.width();
                var xpos = event.pageX - img.offset().left;
                var xedge = self.__edge;
                if (xedge > width / 6)
                {// изображение маленькое по ширине, установим отступ по краям под стать размеру изображения
                    xedge = Math.ceil(width / 6);
                }
                xpos = self.__rescalePosition(xpos, 0, width, 0 - xedge, width + xedge, true);

                var height = img.height();
                var ypos = event.pageY - img.offset().top;
                var yedge = self.__edge;
                if (yedge > width / 6)
                {// изображение маленькое по высоте, установим отступ по краям под стать размеру изображения
                    yedge = Math.ceil(height / 6);
                }
                ypos = self.__rescalePosition(ypos, 0, height, 0 - yedge, height + yedge, true);

                img.css({
                    'object-fit': 'none',
                    'object-position': (xpos * 100 / width) + '% ' + (ypos * 100 / height) + '%',
                });
            });

            img.on('mouseout', function() {
                self.__setDefaults(img);
            });

        },
        __initMagnifier: function(img){

            var self = this;

            var image = new Image();
            image.onload = function(){
                if (image.naturalWidth > img.width() || image.naturalHeight > img.height())
                {
                    self.__setDefaults(img);
                    img.off('mousemove, mouseout');
                    self.__addImgHandlers(img);
                }
            };
            image.src = img.attr('src');

            if (img.hasClass('magnifier-open'))
            {
                img.off('click');
                img.addClass('magnifier-clickable');

                img.on('click', function() {

                    var args = {
                        url: $(this).attr('src'),
                        options: ''
                    };

                    if ($(this).hasClass('magnifier-separate-window')) {
                        args.fullscreen = $(this).hasClass('magnifier-fullscreen');
                        args.options = [
                            'height=600',
                            'width=800',
                            'top=0',
                            'left=0',
                            'menubar=0',
                            'location=0',
                            'scrollbars',
                            'resizable',
                            'toolbar',
                            'status',
                            'directories=0',
                            'fullscreen=0',
                            'dependent'
                        ].join(',');
                    }
                    window.openpopup(false, args);
                });
            }

        },
        init: function() {

            var self = this;

            $('img.magnifier').each(function() {
                if ($(this).parents('.editor_atto').length == 0)
                {
                    self.__initMagnifier($(this));
                }
            });

        },
    };
});