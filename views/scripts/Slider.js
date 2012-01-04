var Slider = Class.extend({
    initialize: function(wrapper, options) {
        // Update the options object
        this.options = $.extend(true, {
            // Slideshow
            slideshow:  7000,
            continuous: true,
            // Animation
            animation: { 
                type :'fade', // fade | vertical | horizontal | scroll (coda style)
                delay: null, // null or delay wait for a callback 
                duration: 250
            },
            // Navigation
            navigation: {
                arrow: true,
                carousel: false,
                pauseOnNavigate: false,
                slideshow: false,
                keyboard: false
            },
            // Prefixes
            prefix: 'page',
            carouselPrefix: 'nav-',
            // set classes
            classes: {
                prevButton: 'prevButton',
                nextButton: 'nextButton',
                playButton: 'playButton',
                pauseButton: 'pauseButton',
                slide: 'page',
                active: 'active',
                carousel: 'sliderCarousel'
            }
        }, options || {});
        this.wrapper = $(wrapper);
        this.id = this.wrapper.attr('id');
        this.classes = this.options.classes;
        this.slides = this.wrapper.find('.'+this.classes.slide);
        this.slideshow = null;
        this.scroller = null;
        
        // force styles for animations
        this.wrapper.css({
            'overflow' : 'hidden',
            'position' : 'relative'
        });
        
        if(this.options.animation.type == 'scroll'){
            this.slides.css({'position': 'static', 'float': 'left'}).width(this.wrapper.width());
            var scroller;
            if(this.slides.parent().get(0) === this.wrapper.get(0)){
                // slide have no wrapper and we need to create a scroll wrapper
                scroller = $('<div />').addClass('sliderScroller');
                this.wrapper.append(scroller);
                scroller.append(this.slides);

            } else {
                scroller = this.slides.parent();
                scroller.addClass('sliderScroller');
            }
            this.scroller;
            scroller.css('position', 'absolute')
                .width((this.slides.length * this.wrapper.width()))
                .height(this.wrapper.height());
            this.scroller = scroller;
        } else if (this.options.animation.type == 'vertical' || this.options.animation.type == 'horizontal'|| this.options.animation.type == 'fade') {
            this.slides.css('position', 'absolute');
        }
        
        if(this.slides.filter('.'+this.classes.active).length != 0){
            if(this.options.animation.type != 'scroll'){
                this.slides.not('.'+this.classes.active).hide();
            }
        } else {
            this.slides.first().addClass(this.classes.active);
            if(this.options.animation.type != 'scroll'){
                this.slides.not(':first').hide();
            }
        }
        
        if(this.options.slideshow){
            this.playSlideshow();
        }

        this.setClickListeners();
        
        
    },
    
    playSlideshow: function(){
        var self = this;
        if(this.slideshow == null){
            this.slideshow = setInterval(function(){
                self.displayNext();
            }, this.options.slideshow);
        }
    },
    
    pauseSlideshow: function(){
        clearInterval(this.slideshow);
        this.slideshow = null;
    },
    
    // reset slideshow timer
    resetSlideshow: function() {
        this.pauseSlideshow();
        if(!this.options.navigation.pauseOnNavigate){
            this.playSlideshow();
        }
    },
    
    displayNext: function() {
        var active = this.slides.filter('.'+this.classes.active);
        // determine if its the last slide, only go to the first slide if its continuous
        var nextSlide = false;
        if($('.'+this.classes.slide+':last').hasClass(this.classes.active)){
            if(this.options.continuous){
                nextSlide = $('.'+this.classes.slide+':first');
            }
        } else {
            nextSlide = active.next();
        }
        if(nextSlide) this.displaySlide(nextSlide);
    },
    displayPrevious: function() {
        var active = this.slides.filter('.'+this.classes.active);
        // determine if its the last slide, only go to the first slide if its continuous
        var previousSlide = false;
        if($('.'+this.classes.slide+':first').hasClass(this.classes.active)){
            if(this.options.continuous){
                previousSlide = $('.'+this.classes.slide+':last');
            }
        } else {
            previousSlide = active.prev();
        }
        if(previousSlide) this.displaySlide(previousSlide);
    },
    displaySlide: function(slide){
        //self.slides.css('position', 'absolute');
        var current = this.getActiveSlide();
        var animationOptions = this.options.animation;
        var offset;
        // delay options for animation
        this.slides.removeClass(this.classes.active);
        slide.addClass(this.classes.active);
        if (animationOptions.type == 'vertical') {
            // get offset
            
            offset = this.wrapper.height();
            // position slide
            slide.css({
                'top' : '-'+offset+'px'
            }
            ).show();
            current.animate({
                'top': offset+'px'
            }, animationOptions.duration, function(){
                // delay showing new slide if delay is 0 then it shows right after animation is done
                if(animationOptions.delay !== null){
                    setTimeout(function(){
                        slide.animate({
                            'top': '0px'
                        }, animationOptions.duration);
                    }, animationOptions.delay);
                }
                current.hide();
            });
            if(animationOptions.delay === null){
                // console.log('showing my slide', slide);
                slide.animate({
                    'top': '0px'
                }, animationOptions.duration);
            }

        }
        else if (animationOptions.type == 'horizontal') {
            offset = this.wrapper.width();
            slide.css({
                'left' : offset+'px'
            }
            ).show();
            current.animate({
                'left': '-'+offset+'px'
            }, animationOptions.duration, function(){
                // delay showing new slide if delay is 0 then it shows right after animation is done
                current.hide();
                if(animationOptions.delay !== null){
                    setTimeout(function(){
                        slide.animate({
                            'left': '0px'
                        }, animationOptions.duration).show();
                    }, animationOptions.delay);
                }
            });
            if(animationOptions.delay === null){
                slide.animate({
                    'left': '0px'
                }, animationOptions.duration).show();
            }
                    
        }
        else if (animationOptions.type == 'scroll') {
            var index = slide.index();
            offset = this.wrapper.width();
            offset = offset * index;
            this.scroller.stop().dequeue();
            this.scroller.animate({
                'left': '-'+offset+'px'
            }, animationOptions.duration, function(){
                // delay showing new slide if delay is 0 then it shows right after animation is done
                });
        }
        else {
            //show new slide via fade
            this.slides.fadeOut(animationOptions.duration, function(){
                // delay showing new slide if delay is 0 then it shows right after animation is done
                if(animationOptions.delay !== null){
                    setTimeout(function(){
                        slide.fadeIn(animationOptions.duration);
                    }, animationOptions.delay);
                }
            });
            // concurrent animation if delay is null
            if(animationOptions.delay === null){
                slide.fadeIn(animationOptions.duration);
            }
        }
        if(this.options.navigation.carousel){
            $('.'+this.classes.carousel).removeClass(this.classes.active);
            var carouselId = this.options.carouselPrefix+slide.attr('id');
            $('#'+carouselId).addClass(this.classes.active);
        }
        if(this.options.continuous === false && $('.'+this.classes.slide+':last').hasClass(this.classes.active)){
            this.pauseSlideshow();
        }
    },

    getActiveSlide: function(){
        return this.slides.filter('.'+this.classes.active);
    },
    
    setClickListeners: function() {
        var self = this;
        // arrow navigation
        if(this.options.navigation.arrow){
            $('.'+this.classes.prevButton).click(function(event){
                self.displayPrevious();
                if(self.options.slideshow && self.slideshow){
                    self.resetSlideshow();
                }

            });
            $('.'+this.classes.nextButton).click(function(event){
                self.displayNext();
                if(self.options.slideshow && self.slideshow){
                    self.resetSlideshow();
                }
            });
        }
        //carasoul navigation
        if(this.options.navigation.carousel){
            //console.log($('.'+this.classes.carousel));
            $('.'+this.classes.carousel).click(function(event){
                //console.log('click', $(event.target));
                var targetId = $(event.target).attr('id');
                    
                var slideId = targetId.replace(self.options.carouselPrefix, '');
                var slide = $('#'+slideId);
                    
                self.displaySlide(slide);
                if(self.options.slideshow && self.slideshow){
                    self.resetSlideshow();
                }
            });
        }
    // slideshow controls
            
    }
    
});