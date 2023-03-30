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
            var width=(textHeight*9)/8;
            svg.setAttribute('width',width);
            svg.setAttribute('height',textWidth+20);

            // Add text
            var text=document.createElementNS(obj.SVGNS,'text');
            svg.appendChild(text);
            text.setAttribute('x',textWidth);
            text.setAttribute('y',-textHeight/2);
            text.setAttribute('text-anchor','end');
            text.setAttribute('transform','rotate(90)');
            text.appendChild(document.createTextNode(string));

            // Is there an icon near the text?
            var icon=el.firstChild.firstChild;
            if(icon.nodeName.toLowerCase()=='img') {
              el.firstChild.removeChild(icon);
              var image=document.createElementNS(obj.SVGNS,'image');
              var iconx=el.offsetHeight/4;
              if(iconx>width-16) {iconx=width-16;}
              image.setAttribute('x',iconx);
              image.setAttribute('y',textWidth+4);
              image.setAttribute('width',16);
              image.setAttribute('height',16);
              image.setAttributeNS(obj.XLINKNS,'href',icon.src);
              svg.appendChild(image);
            }

           // Replace original content with this new SVG
            el.firstChild.textContent = '';
            el.firstChild.appendChild(svg);
        },
        /**
         * Базовая инициализация
         */
        init: function() {
            var obj = this;
            $(function() {
                $('.path-grade-report .gradeparent tr.heading th.item.cell').each(function(){
                        this.style.verticalAlign='bottom';
                        obj.textrotate_make_svg(this);
                });
            });
        }
    };
});