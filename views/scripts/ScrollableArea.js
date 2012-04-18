var ScrollableArea = Class.extend({
    initialize: function(scrollableArea, options) {
        // Update the options object
        this.options = $.extend(true, {
            'onlyShowScrollerOnHover': true,
            'onscroll': false,
        }, options || {});

        this.scrollableArea = $(scrollableArea).addClass('scrollableArea');
        
        if(this.scrollableArea.data('scrollableArea') !== true) {
            this.create();    
        }
    },
        
    create: function() {
        var self = this;
        
        // Mark the element as being a scrollable area
        this.scrollableArea.data('scrollableArea', true);
        this.scrollableArea.data('mouseOver', false);
        
        // Create the content
        this.scrollableAreaContent = $('<div class="scrollableAreaContent" />')
            .html(this.scrollableArea.html())
            .css({
                'width': self.scrollableArea.width(),
                'padding-top': self.scrollableArea.css('padding-top'),
                'padding-bottom': self.scrollableArea.css('padding-bottom'),
                'padding-left': self.scrollableArea.css('padding-left'),
                'padding-right': self.scrollableArea.css('padding-right')
            })
        ;
        
        // The content steals the area's padding
        var scrollableAreaHeight = this.scrollableArea.outerHeight();
        var scrollableAreaWidth = this.scrollableArea.outerWidth();
        self.scrollableArea.css({
            'padding': 0,
            'height': scrollableAreaHeight,
            'width': scrollableAreaWidth
        });        
        
        // Create the viewport
        this.scrollableAreaViewport = $('<div />')
            .addClass('scrollableAreaViewport')
            .css({
                'position': 'absolute',
                'height': self.scrollableArea.outerHeight(),
                'width': self.scrollableArea.outerWidth() * 1.5 // Make room for the native y scroller
            })
            .append(this.scrollableAreaContent);

        if (this.options.onscroll) { 
            this.scrollableAreaViewport.bind('scroll', function(event){ 
                if(self.scrollableAreaScroller.data('mouseOver') === true){
                    self.options.onscroll(event); 
                } 
            });
        }
        
        // Create the track for the scroller
        this.scrollableAreaTrack = $('<div class="scrollableAreaTrack" />').css({
            'height': self.scrollableArea.outerHeight() 
        });
               
        // Create the scroller 
        this.scrollableAreaScroller = $('<div class="scrollableAreaScroller" />');
        this.scrollableAreaScroller.bind('dragstart', function(event) {
            event.preventDefault();
        });
        this.scrollableAreaScroller.bind('selectstart', function(event) {
            event.preventDefault();
        });
        
        // Append the scroller to the track
        this.scrollableAreaTrack.append(this.scrollableAreaScroller);
                
        // Empty the area and insert the viewport
        this.scrollableArea.empty().append(this.scrollableAreaViewport);
        
        // Add the track to the scrollable area
        this.scrollableArea.append(this.scrollableAreaTrack);
        
        // Adjust for any offsets (the magic number 2 here is assuming the track will be the same offset from the top and bottom) :P
        this.scrollableAreaTrack.height(self.scrollableAreaTrack.outerHeight() - (self.scrollableAreaTrack.position().top * 2));
        
        // Set the height of the scroller
        this.updateScrollerHeight();
        
        // Create the event listener for the mouse down event
        this.scrollableAreaScroller.mousedown(function(event) {
            self.scrollableAreaScroller.data('dragging', true);
            self.mouseStartY = event.pageY;
            event.preventDefault();
        });
                
        // Create the event listener for scroll event
        self.scrollableAreaViewport.bind('scroll', function(event) {
            
            //self.updateScrollerPosition();
            self.updateScroller();
            
        });
        
        // Fade the scroller in on hover
        if(this.options.onlyShowScrollerOnHover) {
            this.scrollableAreaTrack.hide();
            this.scrollableArea.hover(
                function(event) {
                    self.scrollableAreaScroller.data('mouseOver', true);
                    if(self.scrollableAreaScroller.data('dragging') == undefined || self.scrollableAreaScroller.data('dragging') === false) {
                        if(!self.scrollableAreaTrack.is(':visible')) {
                            self.scrollableAreaTrack.fadeIn(150);
                        } else if (self.scrollableAreaTrack.css('opacity') != 1 ) {
                            self.scrollableAreaTrack.clearQueue().stop().css({
                                'opacity': 1
                            });    
                        } else {
                            self.scrollableAreaTrack.clearQueue().stop();
                        }
                        
                    }
                },
                function(event) {
                    self.scrollableAreaScroller.data('mouseOver', false);
                    if(self.scrollableAreaScroller.data('dragging') === undefined || self.scrollableAreaScroller.data('dragging') === false) {
                        self.scrollableAreaTrack.delay(750).fadeOut(250);
                    }
                }
            );
        } else {
            console.log('hurtful')
            this.scrollableAreaTrack.show();
        }
        
        // Update the scroll when the content is resized
        self.scrollableAreaContent.bind('resize', function(event) {
            self.updateScroller();
        });
        
        // Handle the mouse up event
        $(document).mouseup(function(event) {
            //console.log('Stopping drag at y = ', event.pageY);
            self.scrollableAreaScroller.data('dragging', false);
            if(self.scrollableAreaScroller.data('mouseOver') == false){
                self.scrollableAreaTrack.delay(750).fadeOut(250);
            }
        });
        
        // Handle the mouse move event
        $(document).mousemove(function(event) {
            // If we are dragging
            if(self.scrollableAreaScroller.data('dragging') === true) {
                //var trackCurrentY = self.scrollableAreaScroller.offset().top;
                
                // Set the current mouse y
                self.mouseCurrentY = event.pageY;
                
                // Keep within the bounds of the top and bottom y of the track
                if(self.mouseCurrentY < self.scrollableAreaTrack.offset().top || self.mouseCurrentY > self.scrollableAreaTrack.offset().top + self.scrollableAreaTrack.outerHeight()) {
                    return;
                }
                
                if(self.mouseCurrentY > self.scrollableAreaTrack.offset().top + self.scrollableAreaTrack.outerHeight() ){
                    newContentScrollTop = '100%';   
                }
                
                // Get the difference between the current y and where it used to be
                self.mouseDeltaY = self.mouseCurrentY - self.mouseStartY;
                
                // Reset the mouse start to the current
                self.mouseStartY = self.mouseCurrentY;
                
                // Find the distance we have moved in pixels and add it to the current top of the scroller
                var newScrollerTop = self.scrollableAreaScroller.position().top + self.mouseDeltaY;
                var newContentScrollTop = newScrollerTop / self.scrollableAreaTrack.outerHeight() * self.scrollableAreaContent.outerHeight();
                
                // Set the viewport position as long as it is within the bounds of the track
                if(newScrollerTop < 0) {
                    newScrollerTop = 0;
                    newContentScrollTop = 0;
                }
                // Set to maximum scroll
                else if(newScrollerTop + self.scrollableAreaScroller.outerHeight() > self.scrollableAreaTrack.outerHeight()) {
                    newScrollerTop = self.scrollableAreaTrack.outerHeight() - self.scrollableAreaScroller.outerHeight() ;
                    newContentScrollTop = self.scrollableAreaContent.outerHeight() - self.scrollableAreaViewport.outerHeight();
                    //self.scrollableAreaScroller.position().top;
                }
                
                // Don't scroll the viewport if the scroller top hasn't changed
                if(newScrollerTop !== self.scrollableAreaScroller.position().top) {
                    // Set the top of the viewport
                    self.scrollableAreaViewport.scrollTop(newContentScrollTop);    
                }                
                
                // Set the scroller top
                self.scrollableAreaScroller.css({
                    'top': newScrollerTop
                });
            }
        });
    },
    
    updateScroller: function() {
        var self = this;
        
        this.updateScrollerHeight();
        this.updateScrollerPosition();
        
        // Show the track briefly if the content is being updated
        var scrollableAreaScrollerHeight = this.scrollableAreaViewport.outerHeight() / this.scrollableAreaContent.outerHeight() * this.scrollableAreaViewport.outerHeight();
        if(!self.scrollableAreaTrack.is(':visible') && (scrollableAreaScrollerHeight < this.scrollableAreaViewport.outerHeight())) {
            self.scrollableAreaTrack.fadeIn(150, function(){
                //console.log(self.scrollableAreaScroller.data('dragging'));
                if(self.scrollableAreaScroller.data('dragging') === undefined || self.scrollableAreaScroller.data('dragging') === false) {
                    self.scrollableAreaTrack.delay(750).fadeOut(250);
                }
            });
        } else if (self.scrollableAreaTrack.css('opacity') != 1 ) {
            self.scrollableAreaTrack.clearQueue().stop().css({
                'opacity': 1
            }).delay(750).fadeOut(250);
        } else if(self.scrollableAreaScroller.data('mouseOver') == false){
            self.scrollableAreaTrack.clearQueue().stop().delay(750).fadeOut(250);
        }
    },
    
    updateScrollerHeight: function() {
        var scrollableAreaScrollerHeight = this.scrollableAreaViewport.outerHeight() / this.scrollableAreaContent.outerHeight() * this.scrollableAreaViewport.outerHeight();
        
        // Make sure there is a need for a scroller
        if(scrollableAreaScrollerHeight > this.scrollableAreaViewport.outerHeight()) {
            //this.scrollableAreaTrack.hide();
        }
        else {
            //this.scrollableAreaTrack.show();
            this.scrollableAreaScroller.css({
                'height': scrollableAreaScrollerHeight
            });    
        }
    },
    
    updateScrollerPosition: function() {
        var self = this;
        
        // Find the new top of the scroller
        var newTop = (self.scrollableAreaViewport.scrollTop() / self.scrollableAreaContent.outerHeight()) * self.scrollableAreaTrack.outerHeight();
        
        // Make sure the new top of the scroller does make the scroller go out of the bottom of the track
        if(newTop + self.scrollableAreaScroller.outerHeight() > self.scrollableAreaTrack.outerHeight()){
            newTop = self.scrollableAreaTrack.outerHeight() - self.scrollableAreaScroller.outerHeight();
        }
        
        // Set the new top of the scroller
        this.scrollableAreaScroller.css({
            'top': newTop
        });
    }
    
});