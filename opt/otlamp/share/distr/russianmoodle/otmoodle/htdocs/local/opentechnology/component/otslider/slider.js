require(['jquery'], function($) {
	var otSlider = function(elementDOM){
		this.__slider = $(elementDOM);
		this.__interval = false;
		this.__touchstartX = 0;
		this.__touchstartY = 0;
		this.__touchendX = 0;
		this.__touchendY = 0;

		this.init = function(){
			if ( this.__slider.length > 0 )
			{
			    var blockid = this.__slider.closest(".block_otslider").attr('id');
			    this.__slider.filter('.otslider[data-replace_requested=\'1\']')
			        .closest(".block_otslider").attr('data-replace_requested', 1);
			    if ( blockid.length > 0 )
                {
    				var sliderplaceholder = $('body:not(.editing) #sliderplaceholder' + blockid.substr(4));
    				if ( sliderplaceholder.length > 0 )
    				{
    					this.__slider.filter('.otslider[data-replace_requested=\'1\']').appendTo(sliderplaceholder).slideDown();
    					this.__slider.find('.content').css({
    						'margin': '0',
    						'padding': '0'
    					});
    				}
                }
				if ( this.__slider.data('engineenabled') == 1 )
	            {
    				if ( this.__slider.data('slidescroll') )
    				{
    					this.__initSlideScroll();
    				} else
    				{
    					if ( this.__slider.data('navigation') )
    					{
    						this.__initNavigation();
    					}
    					
    					if( this.__slider.data('navigationpoints') )
    					{
    						this.__initNavigationPoints();
    					}
    					
    					this.__initSlideGestures();
    					
    					this.__setSlideSpeed();
    					
    					//this.selectSlideNumber(0);
    					this.__slider.find('.navigation_point').eq(0).addClass('active');
    					
    				}
	            }
				this.__initSlideHeightAdjust();
				this.__setDefaultBackgroundPosition();
				this.__initParallax();
			}
		};
		
		
		this.__setDefaultBackgroundPosition = function(){

			var s = this;
			if ( typeof s.__slider !== 'undefined' && s.__slider.length > 0 )
			{
				s.__getAllSlides().each(function(){
					slide = $(this).find('.otslider_slide');
					s.__setBackgroundPosition(slide);
				})
			};
		}
		
		this.__setBackgroundPosition = function(slide){
			var s = this;
			if ( typeof s.__slider !== 'undefined' && s.__slider.length > 0 )
			{
				var k = slide.data('parallax');
				if( k === undefined )
				{
					k = 0;
				}
				
				var backgroundPositionTop = slide.data('backgroundpositiontop');
				if( backgroundPositionTop === undefined )
				{
					backgroundPositionTop = 50;
				}
				
				if ( k !== 0 )
				{
					
					var sliderTop = s.__slider.offset().top;
					var sliderHeight = s.__slider.outerHeight();
					var scrollTop = $(document).scrollTop();
					var winHeight = $(window).height();
					
	
					var startView = sliderTop - winHeight;
					var endView = sliderTop + sliderHeight;
					var scrollViewPercent = ( scrollTop - startView ) * 100 / ( endView - startView );
	
					var percent = (scrollViewPercent - 50) * k / 100 + backgroundPositionTop;
					percent = percent > 100 ? 100 : percent;
					percent = percent < 0 ? 0 : percent;
					
					slide.find('.otslider_image').css({
						'background-position': '0 '+ percent + '%'
					});
				} else
				{
					slide.find('.otslider_image').css({
						'background-position': '0 '+ backgroundPositionTop + '%'
					});
				}
			}
		}
		
		this.__initParallax = function(){
			var s = this;
			if ( typeof s.__slider !== 'undefined' && s.__slider.length > 0 )
			{
				$(document).scroll(function() {
					var slide = s.__getActiveSlide();
					if(slide.length > 0)
					{
						s.__setBackgroundPosition(slide.find('.otslider_slide'));
					}
				});
			}
		}
		
		this.__handleGesture = function(){

			var s = this;
			if ( typeof s.__slider !== 'undefined' && s.__slider.length > 0 )
			{
			    if (s.__touchendX < s.__touchstartX) {
			    	console.log('selectNextSlide');
					s.selectNextSlide();
			    }
			    if (s.__touchendX > s.__touchstartX) {
			    	console.log('selectPrevSlide');
					s.selectPrevSlide();
			    }
//			    if (touchendY < touchstartY) {
//			        alert('down!');
//			    }
//			    if (touchendY > touchstartY) {
//			        alert('left!');
//			    }
//			    if (touchendY == touchstartY) {
//			        alert('tap!');
//			    }
			}
		}
		
		this.__initSlideGestures = function(){
			var s = this;
			if ( typeof s.__slider !== 'undefined' && s.__slider.length > 0 )
			{
				s.__slider.get(0).addEventListener('touchstart', function(event) {
					s.__touchstartX = event.touches[0].clientX;
					s.__touchstartY = event.touches[0].clientY;
					clearInterval(s.__interval);
					s.__interval = false;
				}, false);

				s.__slider.get(0).addEventListener('touchend', function(event) {
					s.__touchendX = event.changedTouches[0].clientX;
					s.__touchendY = event.changedTouches[0].clientY;
				    s.__handleGesture();
					s.__setSlideSpeed();
				}, false); 
			}
		}
		
		this.selectSlideNumber = function(slideNumber, skipAnimation) {
			if ( skipAnimation == undefined )
			{
				skipAnimation = false;
			}
			var s = this;
			if ( typeof s.__slider !== 'undefined' && s.__slider.length > 0 )
			{
				var allSlides = s.__getAllSlides();
				if ( allSlides == false )
				{
					return;
				}
				
				var slideToSelect = allSlides.eq(slideNumber);
				var point = slideNumber;
				if ( s.__slider.data('slidetype') == 'triple') {
				    point = point - 1;
				}
				s.__slider.find('.navigation_point').removeClass('active').eq(point).addClass('active');
				
				if( slideToSelect.length > 0 && s.__getActiveSlideNumber() != slideNumber )
				{
					var way;
					// Убираем у других слайдов классы для анимации
	                allSlides.removeClass('back').removeClass('following').removeClass('forward').removeClass('deactivated').removeClass('skipAnimation');
	                
					if ( s.__getNextSlideNumber() == slideNumber || s.__getActiveSlideNumber() < slideNumber )
					{// Выбранный слайд был следующим
						way = 'forward';
					}
					else
					{// Выбранный слайд был предыдущим
						way = 'back';
					}
					if ( s.__slider.data('slidetype') == 'triple') {
    					if (s.__getActiveSlideNumber() == 0 && slideNumber != 1) {
    					    way = 'back';
    					}
    					if (s.__getActiveSlideNumber() == 1 && slideNumber != 0) {
                            way = 'forward';
                        }
					}
					slideToSelect.addClass(way);
					
					// Убираем у других слайдов класс активности
					var deactivated = allSlides.filter('.active')
						.removeClass('active')
						.addClass('deactivated')
						.addClass(way);
					
					if( skipAnimation )
					{
						deactivated.addClass('skipAnimation');
						slideToSelect.addClass('skipAnimation');
					}
					// Помечаем текущий слайд как активный
					slideToSelect.addClass('active');
					
					// найдем слайд, который следующий будет активным, если мы продолжим движение в ту же сторону
					if (way == 'forward')
				    {
	                    var followingSlideNumber = s.__getNextSlideNumber();
				    } else 
			        {
                        var followingSlideNumber = s.__getPrevSlideNumber();
			        }
					var followingSlide = allSlides.eq(followingSlideNumber);
					followingSlide.addClass('following').addClass(way);
				}
			}
		};

		this.selectNextSlide = function(){
			var s = this;
			if ( typeof s.__slider !== 'undefined' && s.__slider.length > 0 )
			{
				var nextSlideNumber = s.__getNextSlideNumber();
				if ( nextSlideNumber !== false )
				{
					s.selectSlideNumber(nextSlideNumber);
				}
			}
		};
		
		this.selectPrevSlide = function(){
			var s = this;
			if ( typeof s.__slider !== 'undefined' && s.__slider.length > 0 )
			{
				var prevSlideNumber = s.__getPrevSlideNumber();
				if ( prevSlideNumber !== false )
				{
					s.selectSlideNumber(prevSlideNumber);
				}
			}
		};
		

		this.__initSlideHeightAdjust = function(){

			var s = this;

			$(window).resize(function(){

				if (s.__slider.data('height') !== 'auto')
				{
					var h = s.__slider.data('height');
					
					if (s.__slider.data('proportionalheight'))
					{
						var k = 1;
					} else
					{
						// максимальный размер слайдера, как и сайта - 1720, вычисляем во сколько раз он уменьшился при изменении размера окна
						var k = 1720/s.__slider.width();
					}
					
					s.__slider.css('padding-bottom', 'calc('+h+'%*'+(k<1 ? 1 : k)+')');
				}
				
			});
			$(window).resize();
		};
		
		this.__initSlideScroll = function(){
			var s = this;
			if ( s.__slider.length > 0 )
			{
				var allSlides = s.__getAllSlides();
				var selectScrollSlide = function(init){
					if( init == undefined )
					{
						init = false;
					}
					// Размер по вертикали в пикселях, при пересечении которого должен сменяться слайд
					var step = $(window).height()/allSlides.length;
					var sliderPosition = s.__slider.offset();
					// Вычисление номера слайда
					var slideNumber = Math.floor(( sliderPosition.top - $(document).scrollTop() + s.__slider.outerHeight()/2 ) / step);
					if ( slideNumber >= allSlides.length )
					{
						slideNumber = allSlides.length - 1;
					}
					if ( slideNumber < 0 )
					{
						slideNumber = 0;
					}
					// Инвертируем номер слайда, так как слайдер сначала появляется снизу
					slideNumber = allSlides.length - 1 - slideNumber;
					if ( init )
					{// При инициализации может оказаться, что страница уже прокручена, тогда сменим слайд быстро, без анимации
						s.selectSlideNumber(slideNumber, true);
					} 
					else if ( s.__getActiveSlideNumber() != slideNumber )
					{// Во время скролла меняем слайды в обычном режиме
						s.selectSlideNumber(slideNumber);
					}
				}
				$(document).scroll(function(){
					selectScrollSlide();
				});
				selectScrollSlide(true);
			}
		}
		
		this.__initNavigationPoints = function(){
			var s = this;
			if ( s.__slider.length > 0 )
			{
				var allSlides = s.__getAllSlides();
				var sliderWrapper = s.__slider.find('.otslider_wrapper').first();
				
				if( allSlides.length > 0 && sliderWrapper.length > 0)
				{
					var navigationPoints = $('<div>')
						.addClass('navigation_points moodle-has-zindex')
						.appendTo(sliderWrapper);
					
					allSlides.each(function(index){
					    if ( s.__slider.data('slidetype') == 'triple') {
    					    index = index + 1;
    					    if (index > allSlides.length - 1) {
    					        index = 0;
    					    }
					    }
					    $('<div>')
                        .addClass('navigation_point')
                        .data('index', index)
                        .on('click', function(){
                            s.selectSlideNumber($(this).data('index'));
                        })
                        .appendTo(navigationPoints);
					})
				}
			}
		};
		
		this.__setSlideSpeed = function(){
			var s = this;
			if ( s.__slider.length > 0 && s.__interval == false )
			{
				var slideSpeed = s.__slider.data('slidespeed');

				if ( slideSpeed > 0 )
				{
					s.__interval = setInterval(function(){
						s.selectNextSlide();
					}, slideSpeed);
					
					s.__slider.hover(function(){
						clearInterval(s.__interval);
						s.__interval = false;
					}, function(){
						s.__setSlideSpeed();
					});
				}
			}
		};
		
		this.__initNavigation = function(){
			var s = this;
			if ( s.__slider.length > 0 )
			{
				var sliderWrapper = s.__slider.find('.otslider_wrapper').first();
				if( sliderWrapper.length > 0 )
				{
					$('<div>')
						.addClass('navigation_arrow moodle-has-zindex')
						.addClass('navigation_arrow_right')
						.on('click', function(){
							s.selectNextSlide();
						})
						.appendTo(sliderWrapper);
					$('<div>')
						.addClass('navigation_arrow moodle-has-zindex')
						.addClass('navigation_arrow_left')
						.on('click', function(){
							s.selectPrevSlide();
						})
						.appendTo(sliderWrapper);
				}
			}
		};
		
		this.__getNextSlideNumber = function(){
			var s = this;
			if ( typeof s.__slider !== 'undefined' && s.__slider.length > 0 )
			{
				var allSlides = s.__getAllSlides();
				if ( allSlides == false )
				{
					return;
				}
				var activeSlide = s.__getActiveSlide();
				if ( activeSlide !== false )
				{
					var activeSlideNumber = $(allSlides).index(activeSlide);
					if( allSlides.eq(activeSlideNumber+1).length > 0 )
					{// Найден следующий слайд
						return (activeSlideNumber+1);
					} else
					{// Следующего нет (последний?), выбираем первый из слайдов
						return 0;
					}
				}
				// Нет активного слайда, выбираем первый
				return 0;
			}
			return false;
		};
		
		this.__getPrevSlideNumber = function(){
			var s = this;
			if ( typeof s.__slider !== 'undefined' && s.__slider.length > 0 )
			{
				var allSlides = s.__getAllSlides();
				if ( allSlides == false )
				{
					return;
				}
				var activeSlide = s.__getActiveSlide();
				if ( activeSlide !== false )
				{
					var activeSlideNumber = $(allSlides).index(activeSlide);
					if( allSlides.eq(activeSlideNumber-1).length > 0 )
					{// Найден предыдущий слайд
						return (activeSlideNumber-1);
					} else
					{// Предыдущего нет (первый?), выбираем последний из слайдов
						return $(allSlides).length-1;
					}
				}
				// Нет активаного слайда, выбираем первый
				return 0;
			}
			return false;
		};
		
		this.__getActiveSlideNumber = function(){
			var s = this;
			if ( typeof s.__slider !== 'undefined' && s.__slider.length > 0 )
			{
				var allSlides = s.__getAllSlides();
				if ( allSlides == false )
				{
					return;
				}
				var activeSlide = s.__getActiveSlide();
				if ( activeSlide !== false )
				{
					return $(allSlides).index(activeSlide);
				}
				// Нет активаного слайда, выбираем первый
				return 0;
			}
			return false;
		};
		
		this.__getActiveSlide = function(){
			var s = this;
			if ( s.__slider.length > 0 )
			{
				var activeSlide = s.__slider.find('.otslider_slidewrapper.active').first();
				if ( activeSlide.length > 0 )
				{
					return activeSlide;
				}
			}
			return false;
		};
		
		this.__getAllSlides = function(){
			var s = this;
			if ( s.__slider.length > 0 )
			{
				return s.__slider.find('.otslider_slidewrapper');
			}
			return false;
		};
	}
	
	$(function(){
		$('.otslider').each(function(){
			var slider = new otSlider(this);
			slider.init();
		});
	});
});