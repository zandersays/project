var Tip = Class.extend({
    initialize: function(target, options) {
        // Update the options object
        this.options = $.extend(true, {
            'ajax': false,
            'showOn': 'click', // click, hover
            'hideOn': '', // click, hover
            'destroyOnHide': false,
            'content': false,
            'class': '',
            'targetCorner': 'bottomMiddle',
            'tipCorner': 'topLeft',
            'offset' : {
                'top': 0,
                'left': 0
            }
        }, options || {});
        //console.log(this.options);
        
        this.target = $(target);
        this.target.addClass('tipTarget');

        // Set the window and body as a jQuery objects
        this.window = $(window);
        this.body = $('body');
        
        this.create();
        
        // Allow them to create but not show
        //if() {
            this.show(250);
        //}
    },
    
    create: function() {
        //console.log('Creating tip on target: ', this.target);
        
        this.tipWrapper = $('<div />');
        this.tipWrapper.addClass('tipWrapper');
        
        this.tip = $('<div />');
        this.tip.addClass('tip');
        this.tip.addClass(this.options['class']);
        this.tip.appendTo(this.tipWrapper);
        
        // Add the content and set it
        if(this.options.ajax == false) {
            this.setContent(this.options.content);
        }
        
        this.tipWrapper.appendTo($('body'));
        
        if(this.options.ajax !== false) {
            this.loadAjaxContent();
        }
    },
    
    loadAjaxContent: function() {
        this.setContent('<div class="tipLoader">Loading...</div>');
        
        var self = this;
        // The call back function for the AJAX request
        this.options.ajax.success = function(data) {
            self.tipWrapper.css('visibility', 'hidden');
            self.setContent(data);
            self.updatePosition();
            self.tipWrapper.css('visibility', 'visible');
            if(self.options.ajax.onSuccess && typeof(self.options.ajax.onSuccess) == 'function') {
                self.options.ajax.onSuccess();
            }
        }
        this.ajax = $.ajax(this.options.ajax);
    },
    
    show: function() {
        var self = this;
        //console.log('Showing tip on target: ', this.target);
        
        this.updatePosition();
        
        this.tipWrapper.show();
        
        // Add window resize and scroll events
        this.window.resize(function(event) {
            self.updatePosition();
        });
        
        // Add window resize and scroll events
        if(this.target.offsetParent().css('position') == 'fixed') {
            this.window.bind('scroll.fixedTip', function(event) {
                //console.log('Fixed scrolling catch!');
                self.updatePosition();
            });    
        }
        
        // Catch overflowing to the bottom
        var windowHeight = this.window.height();
        var windowScrollTop = this.window.scrollTop();
        var targetPosition = this.target.position();
        var tipWrapperTop = targetPosition.top ;
        var tipWrapperHeight = this.tipWrapper.outerHeight();
        var tipBottom = tipWrapperTop + tipWrapperHeight + 24;
        var windowBottom = windowScrollTop + windowHeight;
        if(windowBottom < tipBottom && tipWrapperTop < windowBottom && tipWrapperTop > 0) {
            $.scrollTo(tipBottom - windowHeight + 'px', 250, {axis:'y'});
        }
        
        // Bind the event listeners to handle persistent tips
        this.tipWrapper.bind('mouseenter.closeTip',
            function() {
                //console.log('Change mouse is inside to true.');
                self.mouseIsInside = true;
            }
        );
        this.tipWrapper.bind('mouseleave.closeTip',    
            function(){ 
                //console.log('Change mouse is inside to false.');
                self.mouseIsInside = false; 
            }
        );
        this.body.bind('mouseup.closeTip', function(event) {
            //console.log(self.mouseIsInside);
            if(!self.mouseIsInside && $(event.target).closest(this.target).length === 0) {
                //console.log('Hiding!');
                self.hide();
            }
        });
    },
    
    hide: function(speed, options) {
        // Unbind the fixed scroll listener if it is not necessary
        if(this.target.offsetParent().css('position') == 'fixed') {
            this.window.unbind('scroll.fixedTip');
        }
        this.body.unbind('.closeTip');
        this.tipWrapper.unbind('.closeTip');
        
        if(options != undefined && options.onAfter != undefined){
            this.tipWrapper.hide(0, function(){
                options.onAfter();
            });
        }
        else {
            this.tipWrapper.hide(0);
        }
        
        if(this.options.destroyOnHide === true) {
            this.destroy();
        }
    },
    
    destroy: function() {
        this.tipWrapper.remove();
    },
    
    setContent: function(content) {
        this.tip.empty().append(content);
    },
    
    updatePosition: function() {
        var targetPosition = this.target.position();
        var targetHeight = this.target.outerHeight(true);
        var targetWidth = this.target.outerWidth();
        var tipWrapperTop = targetPosition.top + parseInt(this.options.offset.top, 10) ;
        var tipWrapperLeft = targetPosition.left + parseInt(this.options.offset.left, 10);
        var tipWrapperHeight = this.tipWrapper.outerHeight(); 
        var tipWrapperWidth = this.tipWrapper.outerWidth();
        var windowHeight = $(window).height();
        var windowWidth = $(window).width();
        var windowScrollTop = $(window).scrollTop();
        
        // Handle tips on position: fixed elements
        if(this.target.offsetParent().css('position') == 'fixed') {
            tipWrapperTop = tipWrapperTop + windowScrollTop;
        }
                
        // Keep the tip in the window by changing the anchor if necessary
        switch(this.options.targetCorner) {
            case 'bottomMiddle':
                tipWrapperTop = tipWrapperTop + targetHeight;
                tipWrapperLeft = tipWrapperLeft + (targetWidth / 2);
                break;
            case 'bottomLeft':
                tipWrapperTop = tipWrapperTop + targetHeight;
                break;
            default:
                break;
        }
        
        // Set the tip position relative to the anchor
        switch(this.options.tipCorner) {
            case 'topLeft':
                break;
            case 'topRight':
                tipWrapperLeft = tipWrapperLeft - tipWrapperWidth;
                break;                
            default:
                break;
        }
        
        // Catch overflowing to the right
        if((tipWrapperWidth + tipWrapperLeft) > windowWidth) {
            //console.log('The tip is too big and will flow off the screen to the right!');
            //console.log('Adjusting left by ', (this.window.width() - (this.tipWrapper.outerWidth() + left)));
            tipWrapperLeft = tipWrapperLeft + (windowWidth - (tipWrapperWidth + tipWrapperLeft)) - 12;
        }
        // Catch overflowing to the left
        else if(tipWrapperLeft < 12) {
            //console.log('The tip is too big and will flow off the screen to the left!');
            tipWrapperLeft = 12;
        }
        
        //console.log(tipWrapperTop, tipWrapperLeft);
        
        this.tipWrapper.css({
            'top': tipWrapperTop,
            'left': tipWrapperLeft
        });
    },
    
    setTarget: function(target) {
        var self = this;

            
        this.target = $(target);
        
        //console.log()
        //console.log(this.tipWrapper, this.tipWrapper.is(':visible'));
        if(this.tipWrapper.is(':visible')) {
            //console.log('Setting target to ', $(target));
            this.hide(0, {
                onAfter: function() {self.show(250);}
            });
        } else {
            this.show();            
        }
        
        
    }
});