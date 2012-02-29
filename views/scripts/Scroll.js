var ScrollClass = Class.extend({
    
    options: {},
    document: $(document),
    window: $(window),
    ajax: null,
        
    infinite: function(options) {
        var self = this;
        self.options.infinite = $.extend(true, {
            // Must be declared
            'startingOffset': null,
            'itemsAvailable': null,
            'itemsPerRequest': null,

            // AJAX
            'url': null,
            'data': {},
            'currentOffsetVariableName': 'currentOffset',
            'itemsPerRequestVariableName': 'itemsPerRequest',
            'paginationVariableName': 'pagination',
            'itemContainerVariableName': 'itemContainer',
            
            // Classes
            'itemContainerClass': 'itemContainer',
            'paginationClass': 'pagination',
            'dividerClass': 'projectScrollInfiniteDivider',
            'loaderClass': 'projectScrollInfiniteLoader',

            // Optional
            'insertionMethod': 'inject', // inject or append
            'showDivider': false,
            'currentOffset': null,
            'requestBreakLimit': null,
            'requestCount': 1,
            'requestBreakText': 'Show more results',
            'loadingText': 'Loading more results...',

            // Used internally
            'currentPage': null,
            'totalPages': null,
            'active': false,
            'previousScrollPosition': 0
        }, options || {});
        //console.log(self.options.infinite);

        // Set the starting marker
        if(!self.options.infinite.currentOffset) {
            self.options.infinite.currentOffset = self.options.infinite.startingOffset;
        }
        
        // Set the total pages
        self.options.infinite.totalPages = Math.ceil(self.options.infinite.itemsAvailable / self.options.infinite.itemsPerRequest);
        
        // Bind a function to the window scroll event
        this.window.scroll(function () {
            //console.log(self.options.infinite.requestBreakLimit);
            var currentScrollPosition = self.window.scrollTop();
            
            // Identify if they are scrolling down
            var scrollingDown = false;
            if(currentScrollPosition > self.options.infinite.previousScrollPosition) {
                //console.log('Scrolling down');
                scrollingDown = true;
            }
            self.options.infinite.previousScrollPosition = currentScrollPosition;
            
            // Set the scroll point to 20% from the bottom
            var scrollPoint = (self.document.height()/5);
            var reachedScrollPoint = self.window.scrollTop() >= (self.document.height() - self.window.height() - scrollPoint);

            // Trigger the infiniteScroll function if they are scrolling down and the scroll point has been reached
            if(scrollingDown && reachedScrollPoint) {
                self.infiniteScroll();
            }
        });
    },
    
    infiniteScroll: function() {
        var self = this;
        
        // If the request is not active and there are more items to fetch
        if(!self.options.infinite.active && !self.options.infinite.ajaxRequest && (self.options.infinite.currentOffset <= self.options.infinite.itemsAvailable)) {
            // Mark the process as active
            self.options.infinite.active = true;

            // If the break limit has been hit
            if(self.options.infinite.requestBreakLimit !== null && self.options.infinite.requestCount > self.options.infinite.requestBreakLimit) {
                // If the break divider doesn't already exist
                if($('.'+self.options.infinite.dividerClass+'.break').length == 0) {
                    // Add the break divider
                    $('.'+self.options.infinite.itemContainerClass+':last').append('<div class="'+self.options.infinite.dividerClass+' break"><p class="center"><a onclick="Scroll.options.infinite.requestBreakLimit = null; Scroll.infiniteScroll(); $(this).parent().parent().remove();">'+self.options.infinite.requestBreakText+'</a>.</p></div>');
                }

                // Mark the request as not active
                self.options.infinite.active = false;

                return;
            }

            // Add a loader notice
            $('.'+self.options.infinite.itemContainerClass+':last').after('<div class="'+self.options.infinite.loaderClass+'"><p>'+self.options.infinite.loadingText+'</p></div>');

            // Get the current page
            self.options.infinite.currentPage = Math.ceil(self.options.infinite.currentOffset / self.options.infinite.itemsPerRequest);

            self.options.infinite.divider = '<div class="'+self.options.infinite.dividerClass+'"><p class="right"><a onclick="Scroll.top();">Back to Top</a></p><p class="left"><!--.03 secs--></p><p class="center">Page <b>'+self.options.infinite.currentPage+'</b> of <b>'+self.options.infinite.totalPages+'</b></p></div>';

            // Set the currentOffset and itemsPerRequest variabless
            self.options.infinite.data[self.options.infinite.currentOffsetVariableName] = self.options.infinite.currentOffset;
            self.options.infinite.data[self.options.infinite.itemsPerRequestVariableName] = self.options.infinite.itemsPerRequest;
            //console.log(self.options.infinite.data);

            // Start the AJAX request
            self.options.infinite.ajaxRequest = $.ajax({
                'type': 'POST',
                'dataType': 'json',
                'url': self.options.infinite.url,
                'data': self.options.infinite.data,
                'success': function(data) {
                    //console.log(data);
                    self.options.infinite.currentOffset += self.options.infinite.itemsPerRequest;

                    // If a divider is set, replace the loader with the divider
                    if(self.options.infinite.showDivider) {
                        $('.'+self.options.infinite.loaderClass+':last').replaceWith($(self.options.infinite.divider));
                    }
                    // If a divider is not set, just remove the loader
                    else {
                        $('.'+self.options.infinite.loaderClass+':last').remove();
                    }
                    
                    // Insert items into existing container
                    if(self.options.infinite.insertionMethod == 'inject') {
                        $('.'+self.options.infinite.itemContainerClass+':last').append($(data[self.options.infinite.itemContainerVariableName]).children());
                    }
                    // Append a new container
                    else if(self.options.infinite.insertionMethod == 'append') {
                        $('.'+self.options.infinite.paginationClass).before(data[self.options.infinite.itemContainerVariableName]);
                    }

                    // Update the pagination
                    $('.'+self.options.infinite.paginationClass).replaceWith(data[self.options.infinite.paginationVariableName]);

                    // Increment the request count, mark the infinite scroll process as inactive, clear the ajaxRequest
                    self.options.infinite.requestCount++;
                    self.options.infinite.active = false;
                    self.options.infinite.ajaxRequest = null;
                }
            });   
        }
    },
    
    elementTop: function(element, options) {
        options = $.extend(true, {
            'onAfter': function() {},
            'onBefore': function() {},
            'offsetTop': 18
        }, options || {});
        
        options.onBefore();
        $('html,body').animate(
            {
                scrollTop: $(element).offset().top - options.offsetTop
            },
            'slow',
            function() {
                options.onAfter();
            }
        );
    },
    
    top: function() {
        Scroll.elementTop($('body'));
    }
});
var Scroll = new ScrollClass();