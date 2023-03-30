define(['jquery'], function($) {
    return {
        SVGNS: 'http://www.w3.org/2000/svg',
        XLINKNS: 'http://www.w3.org/1999/xlink',

        textrotate_make_svg: function(el)
        {
          var obj = this;
          var string=el.firstChild.textContent;

            // Add absolute-positioned string (to measure length)
            var abs=document.createElement('div');
            abs.appendChild(document.createTextNode(string));
            abs.style.position='absolute';
            document.body.appendChild(abs);
            var textWidth=abs.offsetWidth * 1.2,textHeight=abs.offsetHeight;
            document.body.removeChild(abs);

            // Create SVG
            var svg=document.createElementNS(obj.SVGNS,'svg');
            svg.setAttribute('version','1.1');
            var width=Math.ceil(textHeight*9/8);
            if(width%2 == 1) { 
            	width++; 
            }
            svg.setAttribute('width',width);
            svg.setAttribute('height',textWidth+20);

            // Add text
            var text=document.createElementNS(obj.SVGNS,'text');
            svg.appendChild(text);
            text.setAttribute('x',textWidth);
            text.setAttribute('y',-width/2);
            text.setAttribute('text-anchor','end');
            text.setAttribute('transform','rotate(90)');
            text.setAttribute('dominant-baseline', 'central');
            if($(el).hasClass('notingroup')) {
            	text.setAttribute('fill', 'red');
            }
            text.appendChild(document.createTextNode(string));

           // Replace original content with this new SVG
            el.firstChild.textContent = '';
            el.appendChild(svg);
        },
        /**
         * Базовая инициализация
         */
        init: function() {
            var obj = this;
            $(function() {
                $('.otmutualassessment-grades-table th.header.caption:not(.first)').each(function(){
                        this.style.verticalAlign='bottom';
                        obj.textrotate_make_svg(this);
                });
            });
        }
    };
});