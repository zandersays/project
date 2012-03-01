var Transition = Class.extend({
    initialize: function(container, options) {
        //console.log('Creating a new transition');
        //console.log(container, options);
        
        this.options = $.extend(true, {
            onHover: true,
            initialDelay: 50,
            delay: 750
        }, options || {});
        
        // Class variables
        this.active = false;
        this.timeout = null;
        this.container = $(container);
        this.slides = $(container).children();
        this.activeSlide = this.container.find('.active');
        this.nextSlide = null;
        this.previousSlide = null;
        
        // Set the first slide to active
        if(this.activeSlide.length == 0) {
            this.activeSlide = this.slides.first().addClass('active');
        }
        
        // Get the next and previous slides
        this.getNextSlide();
        this.getPreviousSlide();
                
        // Add hover listener
        if(this.options.onHover) {
            this.addHoverListener();
        }        
        
        return this;
    },
        
    start: function() {
        //console.log('Starting transition');
        var self = this;
        
        // Don't transition on one slide
        if(this.active == false && this.slides.length > 1) {
            self.timeout = setTimeout(function() {
                self.next();
            }, self.options.initialDelay);
        }

        // Mark the system as active
        this.active = true;
        
        return this;
    },
    
    stop: function() {
        //console.log('Stopping transition');
        
        clearTimeout(this.timeout);
        this.active = false;
        
        // Go back to the beginning
        this.beginning();
    },
    
    transition: function(slideA, slideB, options) {
        // Do not perform transitions if the slides are the same or if there is only one slide
        if(slideA[0] === slideB[0] || this.slides.length <= 1) {
            return;
        }
        
        // Transition from slide A
        this.previousSlide = slideA.fadeOut(400);
        
        // Transition to slide B
        this.activeSlide = slideB.fadeIn(400, function() {
            if(options && options.callback) {
                //console.log('calling callback!');
                options.callback();
            }
        });
        
        // Get the next slide
        this.getNextSlide();
    },
    
    // Go to the next slide (not the same as forward)
    next: function() {
        var self = this;
        
        this.forward({
            'callback': function() {
                // Keep moving if active
                if(self.active) {
                    self.timeout = setTimeout(function() {
                        self.next();
                    }, self.options.delay);        
                }
            }
        });
    },
    
    // Go to the previous slide (not the same as backward)
    previous: function(options) {
        this.backward(options);
    },
    
    forward: function(options) {
        //console.log('Moving forward');
       
        // Transition forward
        this.transition(this.activeSlide, this.nextSlide, options);
    },
    
    backward: function(options) {
        //console.log('Moving backward');
        
        // Transition backward
        this.transition(this.activeSlide, this.previousSlide, options);
    },
    
    beginning: function(options) {
        //console.log('Going to beginning');
        
        this.transition(this.activeSlide, this.slides.first(), options);
    },
    
    end: function(options) {
        //console.log('Going to end');
        
        this.transition(this.activeSlide, this.slides.last(), options);
    },
    
    getNextSlide: function() {
        this.nextSlide = this.activeSlide.next();
        //console.log(this.nextSlide);
        if(this.nextSlide.length == 0) {
            this.nextSlide = this.slides.first();
        }
        
        return this.nextSlide;
    },
    
    getPreviousSlide: function() {
        this.previousSlide = this.activeSlide.prev();
        if(this.previousSlide.length == 0) {
            //console.log(this.previousSlide);
            this.previousSlide = this.slides.last();
        }
        
        return this.previousSlide;
    },
        
    addHoverListener: function() {
        var self = this;
        
        if(this.slides.length <= 1) {
            return;
        }
        
        this.container.hover(
            function() {
                self.start();
            },
            function() {
                self.stop();
            }
        );
    }
    
});