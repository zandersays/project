var CropSlider = Class.extend({
    initialize: function(container, options) {
        var self = this;
        // Update the options object
        this.options = $.extend(true, {
            cropSquareImage: false,
            notifyCrossHairUrl: false,
            scaleImageToParent: true
        }, options || {});
        
        //
        this.dragDiv = $(container);
        this.id = this.dragDiv.attr('id');
        
        this.backgroundImage = $('<img />');
        this.crossHairImg = $('<img />');
        this.imgWidth = 0; 
        this.imgHeight = 0; 
        this.aspectRatio = 1;
        
        this.startX = 0;
        this.startY = 0;
        this.backgroundX = 0;
        this.backgroundY =0;
        
        this.dragX = false;
        this.dragY = false;
        
        this.clicked = false;
        
        this.originalCss = { 
            backgroundPosition: $(this.dragDiv).css('background-position') || '',
            backgroundSize: $(this.dragDiv).css('background-size') || 'auto',
            background: $(this.dragDiv).css('background') || ''
        };
        
        this.backgroundImage.hide();
        $(this.dragDiv).append(this.backgroundImage);
        
        this.backgroundImage.attr('src', this.options.imageUrl).load(function(){
            self.imgWidth = self.backgroundImage.width();
            self.imgHeight = self.backgroundImage.height();
            //console.log(self.imgHeight, self.imgWidth);
            if(self.imgHeight == self.imgWidth && !self.options.cropSquareImage) {
                self.scaleBackgroundImage();
              //  console.log('no crop necessary');
                return;
            }
            self.setupDragBehind();
            
        });
        
    },
    crossHairCursor: function() {
        if(this.options.notifyCrossHairUrl === false){
            return;
        }
        var self = this;
        $(this.dragDiv).css({'cursor': 'move'});
        if(!this.options.notifyCrossHairUrl) {
            return;
        }
        this.crossHairImg.css({
            'display':'block',
            'background-color':'rgba(255,255,255,.15)'
        });
        
        this.dragDiv.append(this.crossHairImg);
        this.crossHairImg.attr('src', this.options.notifyCrossHairUrl); 
        this.dragDiv.one('mouseover', function() 
        {
            self.crossHairImg.remove();
        });
        var imageHeight = this.crossHairImg.height();
        var imageContainer = this.crossHairImg.parent().height();
        this.crossHairImg.css('padding', (imageContainer - imageHeight)/2 + "px");
    },
    calculateDragDirection: function() {
        if(this.imgHeight > this.imgWidth) {
            this.dragY = true;
        } else if(this.imgWidth > this.imgHeight) {
            this.dragX = true;
        } else {
            this.dragX = true;
            this.dragY = true;
        }
    },
    scaleBackgroundImage: function() {
        //Can't scale a square background image.
        if (this.imgHeight === this.imgWidth && this.options.cropSquareImage) {
            return;
        }
        if (this.imgHeight > this.imgWidth) {
            //imgHeight is bigger, scale width to match then adjust height
            this.aspectRatio = $(this.dragDiv).width() / this.imgWidth;
        } else {
            //scale height to match dragDiv height and then adjust width
            this.aspectRatio = this.dragDiv.height() / this.imgHeight;
        }
        //set the new size
        this.imgWidth *= this.aspectRatio;
        this.imgHeight *= this.aspectRatio;
        this.dragDiv.css('background', "url(" + this.options.imageUrl + ") no-repeat 0px 0px");
        this.dragDiv.css('background-size', this.imgWidth + "px " + this.imgHeight + "px");          
    },
    setBackgroundPosition: function(x, y) {
        //someone teach me cleaner implementation.  I will buy you a beer, root beer that is.
        // Clean this up, 
        if((this.dragY && !this.dragX) || x > 0) { 
            x = 0;
        }
        if((this.dragX && !this.dragY) || y > 0) { 
            y = 0;
        }
        
        if(Math.abs(x)+this.dragDiv.width() > this.imgWidth){
            x = -(this.imgWidth-this.dragDiv.width());
        }
        if(Math.abs(y)+this.dragDiv.height() > this.imgHeight){
            y = -(this.imgHeight-this.dragDiv.height());
        }
        this.dragDiv.css('background-position', x+ "px " + y + "px");
    },
    setStartXY: function(pageX, pageY) {
        this.startX = Math.round(pageX - this.dragDiv.eq(0).offset().left);
        this.startY = Math.round(pageY - this.dragDiv.eq(0).offset().top);
    },
    getCropCoordinates: function() {
        //break up Method
        this.getBackgroundPosition();
        var scaleRatio = {
            x: 1,
            y: 1
        }
        if(!this.dragX || !this.dragY) {
            scaleRatio.x =  this.backgroundImage.width() / this.imgWidth;
            scaleRatio.y =  this.backgroundImage.height() / this.imgHeight;
        }
        var cropXStart = this.backgroundX * scaleRatio.x;
        var cropYStart = this.backgroundY * scaleRatio.y;
        var cropXEnd = (Math.abs(this.backgroundX)+this.dragDiv.width()) * scaleRatio.x;
        var cropYEnd = (Math.abs(this.backgroundY)+this.dragDiv.height()) * scaleRatio.y;
        
        // don't go outside the bounds of the image
        if(cropYEnd > this.backgroundImage.height()) {
            cropYEnd = this.backgroundImage.height();
        }
        if(cropXEnd > this.backgroundImage.width()) {
            cropXEnd = this.backgroundImage.width();
        }
        // create a coordinates object
        this.coordinates = { 
            'cropXStart' : Math.abs(Math.round(cropXStart)),
            'cropYStart' : Math.abs(Math.round(cropYStart)),
            'cropXEnd' : Math.abs(Math.round(cropXEnd)),
            'cropYEnd' : Math.abs(Math.round(cropYEnd))
        };
        
        return this.coordinates;
        
    },
    setCropCoordinates: function() {
        this.getCropCoordinates();
        //$(settings.cropCoordinatesInput).val(Math.abs(Math.round(bX)) + "," + Math.abs(Math.round(bY)) + "," + Math.abs(Math.round(bX2)) + "," + Math.abs(Math.round(bY2)));
        $(this.options.cropCoordinatesInput).val(this.coordinates.cropXStart + ',' + this.coordinates.cropYStart + ',' + this.coordinates.cropXEnd + ',' + this.coordinates.cropYEnd);
    },
    getBackgroundPosition: function() {
        this.backgroundPosition = $(this.dragDiv).css('background-position');          
        if(this.backgroundPosition.indexOf('%')>1){
            this.backgroundX = (this.dragDiv.width()/2) - (this.imgWidth/2);
            this.backgroundY = (this.dragDiv.height()/2) - (this.imgHeight/2);
        }else{
            this.backgroundPosition = this.backgroundPosition.replace(/px/g, "").split(" ");
            this.backgroundX = parseInt(this.backgroundPosition[0]);
            this.backgroundY = parseInt(this.backgroundPosition[1]); 
        }
    },
    setupDragBehind: function() {
        var self = this;
        this.crossHairCursor();
        this.calculateDragDirection();
        this.scaleBackgroundImage();
        
        //mouse up
        $(document).bind('mouseup.'+this.id, function(event){
            self.clicked = false;
            $('body').removeAttr('onselectstart').removeAttr('style');
            self.setCropCoordinates();
        }).bind('mousemove.'+this.id, function(event){
            if(!self.clicked){
                return;
            }//as we only want this to work while they have clicked 
          
            self.getBackgroundPosition();
            var mouseX = Math.round(event.pageX - self.dragDiv.eq(0).offset().left) - self.startX;
            var mouseY = Math.round(event.pageY - self.dragDiv.eq(0).offset().top) - self.startY;         
            var x = self.backgroundX + (mouseX);
            var y = self.backgroundY + (mouseY);         
          
            self.setStartXY(event.pageX, event.pageY);
                  
            self.setBackgroundPosition(x,y);
        });

        //mouse down, //mouse move
        this.dragDiv.bind('mousedown.'+this.id, function(event){
            self.clicked = true;
            self.setStartXY(event.pageX, event.pageY);
            
            $('body').css({'cursor': 'move !important'}).attr('onselectstart', 'return false;');
            
        });  
    },
    destroy: function() {
        this.dragDiv.unbind('mousedown.'+this.id);
        $(document).unbind('mouseup.'+this.id).unbind('mousemove.'+this.id);

        this.dragDiv.css({
            'cursor': "default",
            'background-position': this.originalCss.backgroundPosition,
            'background-size': this.originalCss.backgroundSize,
            'background': this.originalCss.background
        }).empty();
    }
});