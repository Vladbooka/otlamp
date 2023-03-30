YUI.add('moodle-theme_opentechnology-ajaxpopup', function(Y) {

    var ULP = {
        NAME : 'Login-as Manager',
        /** Properties **/
        BASEBACK : 'baseback',
        BASE : 'base',
        SEARCH : 'search',
        PARAMS : 'params',
        URL : 'url',
        AJAXURL : 'ajaxurl',
        MULTIPLE : 'multiple',
        PAGE : 'page',
        COURSEID : 'courseid',
        USERS : 'users',
        USERCOUNT : 'userCount',
        REQUIREREFRESH : 'requiresRefresh',
        LASTSEARCH : 'lastPreSearchValue',
        LOGINTOKEN : 'logintoken'
    };
    /** CSS classes for nodes in structure **/
    var CSS = {
        PANELBACKGROUND : 'user-loginas-panel-background',
		PANEL : 'user-loginas-panel',
        WRAP : 'ulp-wrap',
        HEADER : 'ulp-header',
        CONTENT : 'ulp-content',
        AJAXCONTENT : 'ulp-ajax-content',
        SEARCHRESULTS : 'ulp-search-results',
        TOTALUSERS : 'totalusers',
        USERS : 'users',
        USER : 'user',
        SEARCHBUTTONS : 'ulp-search-buttons',
        NEXTRESULTS : 'ulp-next-results',
        PREVRESULTS : 'ulp-prev-results',
        LIGHTBOX : 'ulp-loading-lightbox',
        LOADINGICON : 'loading-icon',
        FOOTER : 'ulp-footer',
        COUNT : 'count',
        PICTURE : 'picture',
        DETAILS : 'details',
        LOGINASLINK : 'loginaslink',
        FULLNAME : 'fullname',
        EXTRAFIELDS : 'extrafields',
        OPTIONS : 'options',
        ODD  : 'odd',
        EVEN : 'even',
        HIDDEN : 'hidden',
        SEARCH : 'ulp-search',
        CLOSE : 'close',
        CUSTOMPLACE : 'custom-place',
        CLOSEBTN : 'close-button'
    };
    var create = Y.Node.create;

    var USERLOGINNER = function(config) {
        USERLOGINNER.superclass.constructor.apply(this, arguments);
    };
    Y.extend(USERLOGINNER, Y.Base, {
        _searchTimeout : null,
        _loadingNode : null,
        _escCloseEvent : null,
        initializer : function(config) {
        	
        	if ( ! config.header ) {
        		config.header = M.str.theme_opentechnology.ajaxpopupheader;
        	};
        	this.set(ULP.BASEBACK, create('<div class="' + CSS.PANELBACKGROUND + ' ' + CSS.HIDDEN + '"></div>'));
            this.set(ULP.BASE, create('<div class="' + CSS.PANEL + ' ' + CSS.HIDDEN + ' moodle-has-zindex"></div>')
                .append(create('<div class="' + CSS.WRAP + '"></div>')
                    .append(create('<div class="' + CSS.HEADER + ' header"></div>')
                        .append(create('<div class="' + CSS.CLOSE + ' moodle-has-zindex"></div>'))
                        .append(create('<div class="' + CSS.CUSTOMPLACE + ' moodle-has-zindex"></div>'))
                        .append(create('<h2>' + config.header + '</h2>'))
                    )
                    .append(create('<div class="' + CSS.CONTENT + '"></div>')
                        .append(create('<div class="' + CSS.AJAXCONTENT + '"></div>'))
                        .append(create('<div class="' + CSS.LIGHTBOX + ' ' + CSS.HIDDEN + '"></div>')
                            .append(create('<img alt="loading" class="' + CSS.LOADINGICON + '" />')
                                .setAttribute('src', M.util.image_url('i/loading', 'moodle')))
                            .setStyle('opacity', 0.5))
                    )
                    .append(create('<div class="' + CSS.FOOTER + '"></div>'))
                )
            );
            
            Y.all('.theme_opentechnology_plugin input').each(function(node){
                if (node.getAttribute('type', 'submit')) {
                    node.on('click', this.show, this);
                }
            }, this);
            
            // Add event to the settings link
            var loginlinks = Y.all('#page-footer .logininfo a.btn.ajaxpopup-footer-login, .login a, .crw_buttonholder.logininfo a.button');
            if (loginlinks) {
            	loginlinks.on('click', this.show, this);
            };

            this.get(ULP.BASE).one('.' + CSS.HEADER + ' .' + CSS.CLOSE).on('click', this.hide, this);
            this.get(ULP.BASEBACK).on('click', this.hide, this);
            this._loadingNode = this.get(ULP.BASE).one('.' + CSS.CONTENT + ' .' + CSS.LIGHTBOX);
            var params = this.get(ULP.PARAMS);
            params['id'] = this.get(ULP.COURSEID);
            this.set(ULP.PARAMS, params);

            //Y.on('key', this.preSearch, this.get(ULP.SEARCH), 'down:13', this);

            Y.one(document.body).append(this.get(ULP.BASEBACK));
            Y.one(document.body).append(this.get(ULP.BASE));

            var base = this.get(ULP.BASE);
            base.plug(Y.Plugin.Drag);
            base.dd.addHandle('.' + CSS.HEADER + ' h2');
            base.one('.' + CSS.HEADER + ' h2').setStyle('cursor', 'move');
        },
        preSearch : function(e) {
            this.search(null, 0);
        },
        correctPosition: function(){
            var base = this.get(ULP.BASE);
            var x = ( base.get('winWidth') / 2 - base.get('offsetWidth') / 2 );
		    if ( (x + base.get('offsetWidth')) > parseInt(base.get('winWidth')) ) {
		        x = 0;
		    }
            var y = (parseInt(base.get('winHeight')) - base.get('offsetHeight')) / 2 + parseInt(base.get('docScrollY'));
            if (y < parseInt(base.get('winHeight')) * 0.1) {
                y = parseInt(base.get('winHeight')) * 0.1;
            }
            base.setXY([x,y]);
        },
        show : function(e) {
            e.preventDefault();
            e.halt();

            this.get(ULP.BASEBACK).removeClass(CSS.HIDDEN);
            
            var base = this.get(ULP.BASE);
            base.removeClass(CSS.HIDDEN);
            this.correctPosition();

            if (this.get(ULP.USERS) === null) {
                this.search(e, false);
            }

            this._escCloseEvent = Y.on('key', this.hide, document.body, 'down:27', this);
        },
        hide : function(e) {
            if (this._escCloseEvent) {
                this._escCloseEvent.detach();
                this._escCloseEvent = null;
            }
            this.get(ULP.BASE).addClass(CSS.HIDDEN);
            this.get(ULP.BASEBACK).addClass(CSS.HIDDEN);
            
            if (this.get(ULP.REQUIREREFRESH)) {
                window.location = this.get(ULP.URL);
            }
        },
        search : function(e, go) {
            if (e) {
                e.halt();
                e.preventDefault();
            }
            var on, params;

            var gopage = go == 0 ? 0 : this.get(ULP.PAGE) + go;
            this.set(ULP.USERCOUNT, gopage * 25);
            this.set(ULP.PAGE, gopage);

            params = this.get(ULP.PARAMS);
            params['sesskey'] = M.cfg.sesskey;
            params['action'] = 'getpage';
            params['url'] = e.target.get('href');
            params['wantsurl'] = window.location.href;
            
            Y.io(M.cfg.wwwroot + this.get(ULP.AJAXURL), {
                method: 'POST',
                data: build_querystring(params),
                on: {
                    start: this.displayLoading,
                    complete: this.outputpage,
                    end: this.removeLoading
                },
                context:this,
                arguments: {
                    contextid:params['contextid']
                }
            });
        },
        displayLoading : function() {
            this._loadingNode.removeClass(CSS.HIDDEN);
        },
        removeLoading : function() {
            this._loadingNode.addClass(CSS.HIDDEN);
        },
        outputpage : function(tid, outcome, args) {
            try {
                var result = Y.JSON.parse(outcome.responseText);
                if (result.error) {
                    return new M.core.ajaxException(result);
                }
            } catch (e) {
                new M.core.exception(e);
            }
            if (result.success) {
            	this.setContent(result.response);
            }
        },
        setContent: function(content) {
        	var element = create(content);
        	
        	var loginbox = element.one('.loginbox');
        	
        	var token =  element.one('input[name="logintoken"]');
        	if( token )
        	{
        		token.set('value', this.get(ULP.LOGINTOKEN));
        	}
        	
        	//пояснение про cookies переносим в заголовок окна
        	var elementcookies = element.one('.loginbox > .desc');
        	if(elementcookies)
    		{
                var base = this.get(ULP.BASE);
                base.one('.' + CSS.HEADER + '.header .' + CSS.CUSTOMPLACE).setContent('<div class="icon"></div>').insert(elementcookies);
    		}
        	

        	var labelusername = element.one('.loginbox .loginpanel .loginsub .loginform label[for="username"]');
        	var inputusername = element.one('.loginbox .loginpanel .loginsub .loginform input[name="username"]');
        	if(labelusername && inputusername)
    		{
            	inputusername
            		.setAttribute('placeholder', labelusername.get('text').trim())
        			.setAttribute('tabindex', '1');
//            	labelusername.ancestor().remove();	
    		}
        	
        	
        	var labelpassword = element.one('.loginbox .loginpanel .loginsub .loginform label[for="password"]');
        	var inputpassword = element.one('.loginbox .loginpanel .loginsub .loginform input[name="password"]');
        	if(labelpassword && inputpassword)
    		{
            	inputpassword
            		.setAttribute('placeholder', labelpassword.get('text').trim())
            		.setAttribute('tabindex', '2');
//            	labelpassword.ancestor().remove();
    		}
        	
        	var elementrememberpass = element.one('.loginbox .loginpanel .loginsub .rememberpass');
        	if(elementrememberpass && inputusername && inputpassword)
    		{
        		inputusernamewrapper = inputusername.ancestor();
        		inputusernamewrapper.insert(elementrememberpass, 'after');
        		
        		elementrememberpass.setAttribute('tabindex', '3');
    		}
        	

        	var elementforgetpass = element.one('.loginbox .loginpanel .loginsub .forgetpass');
        	var loginbtn = element.one('.loginbox .loginpanel .loginsub input[type="submit"]');
        	if(elementforgetpass && loginbtn)
    		{
        		loginbtn
        			.setAttribute('tabindex', '4');
        		var loginbtnwrapper = create('<div class="loginbtnwrapper"></div>')
        			.insert(loginbtn)
        			.setStyle('float','left');
        		
        		elementforgetpass
        			.setStyle('float','left')
        			.setStyle('margin-left','15px')
        			.insert(loginbtnwrapper,'before')
        			.insert(create('<div class="clearer"></div>'),'after');
    		}
        	
            this.get(ULP.BASE).one('.' + CSS.CONTENT + ' .' + CSS.AJAXCONTENT).setContent(element);
            this.correctPosition();
        }
    }, {
        NAME : ULP.NAME,
        ATTRS : {
            url : {
                validator : Y.Lang.isString
            },
            ajaxurl : {
                validator : Y.Lang.isString
            },
            logintoken : {
                validator : Y.Lang.isString
            },
            base : {
                setter : function(node) {
                    var n = Y.one(node);
                    if (!n) {
                        Y.fail(ULP.NAME + ': invalid base node set');
                    }
                    return n;
                }
            },
            users : {
                validator : Y.Lang.isArray,
                value : null
            },
            courseid : {
                value : null
            },
            params : {
                validator : Y.Lang.isArray,
                value : []
            },
            instances : {
                validator : Y.Lang.isArray,
                setter : function(instances) {
                    var i, ia = [], count = 0;
                    for (i in instances) {
                        ia.push(instances[i]);
                        count++;
                    }
                    this.set(ULP.MULTIPLE, (count > 1));
                }
            },
            multiple : {
                validator : Y.Lang.isBool,
                value : false
            },
            page : {
                validator : Y.Lang.isNumber,
                value : 0
            },
            userCount : {
                value : 0,
                validator : Y.Lang.isNumber
            },
            requiresRefresh : {
                value : false,
                validator : Y.Lang.isBool
            },
            search : {
                setter : function(node) {
                    var n = Y.one(node);
                    if (!n) {
                        Y.fail(ULP.NAME + ': invalid search node set');
                    }
                    return n;
                }
            },
            lastPreSearchValue : {
                value : '',
                validator : Y.Lang.isString
            },
            strings  : {
                value : {},
                validator : Y.Lang.isObject
            }
        }
    });
    Y.augment(USERLOGINNER, Y.EventTarget);

    M.theme_opentechnology = M.theme_opentechnology || {};
    M.theme_opentechnology.ajaxpopup = {
        init : function(cfg) {
            new USERLOGINNER(cfg);
        }
    }

}, '@VERSION@', {requires:['base','node', 'overlay', 'io-base', 'test', 'json-parse', 'event-delegate', 'dd-plugin', 'event-key']});
