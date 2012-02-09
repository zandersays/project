FormComponentFile = FormComponent.extend({
    initialize: function(parentFormSection, formComponentId, formComponentType, options) {
        this._super(parentFormSection, formComponentId, formComponentType, options);
    },
        
    ajaxOnComplete: function(fileUploadHandler, response) {
        this.component.find('.pseudoFileAjaxStatus').fadeOut(function() {
            $(this).removeClass('pseudoFileAjaxStatusUploading')
        });
        
        //console.log('FormComponentFile ajaxOnComplete called!', fileUploadHandler, response);
        response = Json.decode(response);
        //console.log(response);
        
        // Handle validation failed
        if(response.status == 'failure' && response.errorMessageArray) {
            //console.log(this);
            this.errorMessageArray = response.errorMessageArray;
            this.handleErrors();
        }
        else {
            this.component.find('input.file').addClass('ajaxHandled');
            
            //console.log(this.options.ajax.javaScriptFunction);
            if(this.options.ajax.javaScriptFunctionContext !== false) {
                window[this.options.ajax.javaScriptFunctionContext][this.options.ajax.javaScriptFunction](response, this);
            }
            else {
                var javaScriptFunction = eval(this.options.ajax.javaScriptFunction);
                //console.log(javaScriptFunction);
                if(typeof(javaScriptFunction) == 'function') {
                    javaScriptFunction(response, this);
                }
                else {
                    console.log('Invalid JavaScript function called: ', this.options.ajax.javaScriptFunction);
                }    
            }
        }
    },
    
    ajaxOnStart: function(fileUploadHandler, response) {
        this.component.find('.pseudoFileAjaxStatus').addClass('pseudoFileAjaxStatusUploading').fadeIn();
    },
    
    ajax: function(options) {
        options = $.extend({
            'highlight': true,
            'validate': true,
            'upload': this.component.find('input.file').get(0)
        }, options || {});
        
        // Highlight the field
        if(options.highlight) {
            this.highlight();    
        }
        
        // Validate the component before sending anything
        var validationPassed = true;
        if(options.validate) {
            validationPassed = this.validate()
        }
        
        // Send the file to the file uplaoder
        if(validationPassed) {
            this.options.ajax.fileUploader.upload(options.upload);    
        }
    },
    
    prime: function() {
        var self = this;
        
        // Set the tip target
        var tipTarget = this.component.find('button').parent();
        if(tipTarget.length < 1){
            tipTarget = this.component.find('input:file');
        }
        this.tipTarget = tipTarget;
        
        // Set custom styles
        if(this.options.customStyle){
            this.setOnChange();
        }
                
        // Handle ajax file uploading
        if(this.options.ajax) {
            // Set the defaults for the ajax object
            //console.log(self.getData());
            var ajaxData = {
                'view': self.options.ajax.data.view,
                'formComponentId': self.id,
                'controller': self.options.ajax.data.controller,
                'function': self.options.ajax.data['function'],
                'viewData': self.options.ajax.data.viewData
            };
            //console.log(ajaxData);
            this.options.ajax.fileUploader = new FileUploader('/api/forms/processFormComponentFile/', ajaxData, {
                'onCompleteContext': self,
                'onComplete': 'ajaxOnComplete',
                'onStartContext': self,
                'onStart': 'ajaxOnStart',
                'forceIFrame' : self.options.ajax.forceIFrame
            });
        }
        
        // Validation functions
        this.validationFunctions = {
            'required': function(options) {
                var errorMessageArray = ['Required.'];
                return (options.value != '' || self.component.find('input:file').is('.ajaxHandled')) ? 'success' : errorMessageArray;
            },
            'extension': function(options) {
                var errorMessageArray = ['Must have the .'+options.extension+' extension.'];
                var extensionRegex = new RegExp('\\.'+options.extension+'$');
                return options.value == '' || options.value.match(extensionRegex) ? 'success' : errorMessageArray;
            },
            'extensionType': function(options) {
                var extensionType;
                var errorMessageArray = ['Incorrect file type.'];
                if($.isArray(options.extensionType)){
                    extensionType = new RegExp('\\.('+options.extensionType.join('|')+')$');
                }
                else {
                    var extensionObject = {};
                    extensionObject.image = '\\.(bmp|gif|jpg|png|psd|psp|thm|tif)$';
                    extensionObject.document = '\\.(doc|docx|log|msg|pages|rtf|txt|wpd|wps)$';
                    extensionObject.audio = '\\.(aac|aif|iff|m3u|mid|midi|mp3|mpa|ra|wav|wma)$';
                    extensionObject.video = '\\.(3g2|3gp|asf|asx|avi|flv|mov|mp4|mpg|rm|swf|vob|wmv)$';
                    extensionObject.web = '\\.(asp|css|htm|html|js|jsp|php|rss|xhtml)$';
                    extensionType = new RegExp(extensionObject[options.extensionType]);
                    errorMessageArray = ['Must be an '+options.extensionType+' file type.'];
                }
                return options.value == '' || options.value.match(extensionType) ? 'success' : errorMessageArray;
            },
            'size' : function(options){
                if(this.component.find('input:file')[0].files){
                   var file = this.component.find('input:file')[0].files[0];
                   if(file.size <= options.size){
                       
                   }
                } else {
                    return true;    
                }
                
            },
            'imageDimensions' : function(options){
                return true;
            },
            'minImageDimensions' : function(options){
                return true;
            }
        }
    },

    setOnChange: function(){
        var self = this;

        this.component.find('input:file').change(function(event){
            var value = event.target.value.replace(/.+\\/, '');
            self.component.find('input:text').val(value);
            
            if(self.options.ajax) {
                self.ajax();
            }
        });
        
    },

    setValue: function() {
        return false;
    },

    getValue: function() {
        if(this.disabledByDependency || this.parentFormSection.disabledByDependency){
           return null;
        }
        return this.component.find('input:file').val();
    },

    validate: function() {
        return this._super();
    }
});

// File uploader (manages uploads)
FileUploader = Class.extend({
    
    initialize: function(url, data, options) {
        var self = this;
        
        // Set the URL and data
        this.url = url;
        if(!data) {
            this.data = {};
        }
        else {
            this.data = data;    
        }
                
        // Set the options
        this.options = $.extend({
            'maximumConcurrentUploads': 1,
            'onStartContext': false,
            'onStart': function() {},
            'onProgressContext': false,
            'onProgress': function() {},
            'onCompleteContext': false,
            'onComplete': function() {},
            'onCancelContext': false,
            'onCancel': function() {}
        }, options || {});
        //console.log(options);
        
        // Find out of XHR is supported
        this.xhrIsSupported = this.isXhrSupported();
        
        // Initialize the file upload handler array
        this.fileUploadHandlers = [];
        this.fileUploadHandlersQueue = [];
        
        // Set the file upload handler class
        this.fileUploadHandlerClass = 'FileUploadHandlerIFrame';
        if(this.xhrIsSupported) {
            this.fileUploadHandlerClass = 'FileUploadHandlerXhr';
        }
        
        this.preventLeavingWhileFilesAreBeingUploaded();
    },
    
    onStart: function(fileUploadHandler) {
        //console.log('FileUploader: onStart called by: ', fileUploadHandler)
        
        // If there is a custom function
        if(this.options.onStart !== false) {
            if(this.options.onStartContext !== false) {
                this.options.onStartContext[this.options.onStart](fileUploadHandler);
            }
            else {
                this.options.onStart(fileUploadHandler);
            }
        }
    },
    
    onProgress: function(fileUploadHandler) {
        //console.log('FileUploader: onProgress called by: ', fileUploadHandler)
        
        // If there is a custom function
        if(this.options.onProgress !== false) {
            if(this.options.onProgressContext !== false) {
                this.options.onProgressContext[this.options.onProgress](fileUploadHandler);
            }
            else {
                this.options.onProgress(fileUploadHandler);
            }
        }
    },
    
    onComplete: function(fileUploadHandler, response) {
        //console.log('FileUploader: onComplete called by: ', fileUploadHandler)
        
        this.dequeue(fileUploadHandler.id);
        
        // If there is a custom function
        if(this.options.onComplete !== false) {
            if(this.options.onCompleteContext !== false) {
                this.options.onCompleteContext[this.options.onComplete](fileUploadHandler, response);
            }
            else {
                this.options.onComplete(fileUploadHandler, response);
            }
        }
    },
    
    onCancel: function(fileUploadHandler) {
        //console.log('FileUploader: onCancel called by: ', fileUploadHandler)
        
        this.dequeue(fileUploadHandler.id);
        
        // If there is a custom function
        if(this.options.onCancel !== false) {
            if(this.options.onCancelContext !== false) {
                this.options.onCancelContext[this.options.onCancel]();
            }
            else {
                this.options.onCancel();
            }
        }
    },
    
    isUploading: function() {
        var uploading = false;
        
        $.each(this.fileUploadHandlers, function(index, fileUploadHandler) {
            if(fileUploadHandler.getStatus() == 'uploading') {
                uploading = true;
            }
        });
        
        return uploading;
    },
    
    getFileHandlersCountByStatus: function(status) {
        var count = 0;
        
        $.each(this.fileUploadHandlers, function(index, fileUploadHandler) {
            if(fileUploadHandler.getStatus() == status) {
                count++;
            }
        });
        
        return count;
    },
        
    getFileUploadHandlers: function() {
        return this.fileUploadHandlers;
    },
    
    upload: function(upload) {
        
        if(this.fileUploadHandlerClass === 'FileUploadHandlerXhr'){
            // get the constructor (firefox is a object and webkit is a function so make the regex handle both cases)
            var constructorString = upload.constructor.toString().match(/object\s\w+|function\s\w+/);
            // we found a constructor but match returns an array so grab the string
            if(constructorString !== undefined){
                constructorString = constructorString[0];
                var uploadType = constructorString.split(' ')[1];
            }
            else {
                console.log('Something went horribly wrong', 'Check your upload handler');
            }
            //console.log(uploadType);
            // Determine what we are uploading
            var files = [];
            
            // We are handling a single file
            if(uploadType === 'File') {
                files.push(upload);
            }
            // We are working with an HTML5 input with xhr support
            else if((uploadType === 'HTMLInputElement' || uploadType === 'HTMLInputElementConstructor') && this.xhrIsSupported) {
                files = upload.files;
            }
            
            // Pass the array of files to be uploaded
            this.queueFiles(files);
        }
        else if(this.fileUploadHandlerClass === 'FileUploadHandlerIFrame') {
            //console.log(upload);
            this.queue(upload);
        }
    },
    
    queueFiles: function(files) {
        //console.log(files);
        var self = this;
        $.each(files, function(index, file) {
            self.queue(file);
        });
    },
    
    queue: function(file) {
        var fileUploadHandler = new window[this.fileUploadHandlerClass](this, this.fileUploadHandlers.length, file, this.url, this.data);
        
        this.fileUploadHandlers.push(fileUploadHandler);
        this.fileUploadHandlersQueue.push(this.fileUploadHandlers.length);
        
        // Make sure we obey maximum concurrent uploads
        if(this.getFileHandlersCountByStatus('uploading') < this.options.maximumConcurrentUploads) {
            fileUploadHandler.upload();
        }
    },
    
    dequeue: function(id) {
        var self = this;
        
        // Remove the item from the queue (called on cancel or complete)
        this.fileUploadHandlersQueue.splice(this.fileUploadHandlersQueue.indexOf(id), 1);
        
        // Start the next item in the queue
        $.each(this.fileUploadHandlersQueue, function(index, id) {
            if(self.fileUploadHandlers[id].getStatus() == 'waiting') {
                self.fileUploadHandlers[id].upload();
                return false; // break out of the $.each
            }
        });
    },
    
    uploadFile: function(file) {
        var fileUploadHandler = new window[this.fileUploadHandlerClass](this, file, this.url, this.data);
        this.fileUploadHandlers.push(fileUploadHandler);
    },
    
        
    preventLeavingWhileFilesAreBeingUploaded: function() {
        var self = this;
        $(window).bind('beforeunload', function() {
            if(self.isUploading()) {
                return 'Leaving will cancel the upload in progress.';
            }
            else {
                return;
            }
        });
    },
    
    cancelAll: function() {
        $.each(this.fileUploadHandlers, function(index, fileUploadHandler) {
            if(fileUploadHandler.getStatus == 'uploading') {
                fileUploadHandler.cancel();
            }
        });
    },
    
    isXhrSupported: function() {
        var input = document.createElement('input');
        input.type = 'file';
    
        return (
            'multiple' in input &&
            typeof File != "undefined" &&
            typeof (new XMLHttpRequest()).upload != "undefined" &&
            (!this.options.forceIFrame)
        ); 
    }
    
});

// Abstract file upload handler
FileUploadHandler = Class.extend({
    
    initialize: function(fileUploader, id, file, url, data) {
        this.fileUploader = fileUploader;
        this.id = id;
        this.file = file;
        this.uploadedBytes = 0;
        this.uploadedKilobytes = 0;
        this.uploadedMegabytes = 0;
        this.uploadedPercent = 0;
        this.url = url;
        this.data = data;
        this.status = 'waiting';
        this.prime();
    },
    
    getStatus: function() {
        return this.status;
    },
    
    prime: function() {  
    },
    
    upload: function() {  
    },
    
    onStart: function() {  
        this.status = 'uploading';
        this.fileUploader.onStart(this);
    },
    
    onCancel: function() {
        this.status = 'cancelled';
        this.fileUploader.onCancel(this);
    },
    
    onProgress: function() {  
    },
    
    onComplete: function() {  
    },
    
    cancel: function() {
    }
    
});

// IFrame implementation
FileUploadHandlerIFrame = FileUploadHandler.extend({
    
    prime: function() {
        var self = this;
        
        // Remember where the file input is, we'll need to move it to the temporary form and then back
        this.fileInputOrigin = $(this.file).parent();
        this.iFrame = this.createIFrame();
        this.form = this.createForm();
    },
    
    upload: function() {
        this.onStart();

        // Either use the load event or script tag within the iframe
        this.form.submit();
    },
    
    onComplete: function() {
        var self = this;
        var document = this.iFrame.get(0).contentDocument ? this.iFrame.get(0).contentDocument : this.iFrame.get(0).contentWindow.document;
        var response = document.body.innerHTML;
        
        this.uploadedBytes = this.file.size;
        this.uploadedKilobytes = this.file.size / 1024;
        this.uploadedMegabytes = this.file.size / 1024 / 1024;
        this.uploadedPercent = '100%';
        this.status = 'complete';
        this.fileInputOrigin.append(this.file);
        this.form.remove();
        this.iFrame.remove();
        
        this.fileUploader.onComplete(this, response);

        return response;
    },
    
    cancel: function() {
        this.status = 'cancelling';
        
        this.form.remove();
        this.iFrame.attr('src', 'javascript:false;').remove();
        
        this.onCancel();
        
        return this;
    },
    
    getName: function(){
        // Making an assumption that file is the file component
        return $(this.file).val().replace(/.*(\/|\\)/, "");
    },
    
    createIFrame: function() {
        var self = this;
        var iFrameId = $(this.file).attr('id') + '-ajaxIFrame';
        
        // src="javascript:false;" removes ie6 prompt on https
        var iFrame = $('<iframe src="javascript:false;" name="'+iFrameId+'" />').attr('id', iFrameId).hide();
        $('body').append(iFrame);
        
        iFrame.load(function() {
            self.onComplete();
        });

        return iFrame;
    },

    createForm: function() {
        var form = $('<form method="post" enctype="multipart/form-data"></form>');
        var url = this.url+'?'+$.param(this.data);
        
        form.attr('action', url);
        form.attr('target', this.iFrame.attr('name'));
        form.hide();
        
        $('body').append(form);
        $(form).append(this.file);
        $(form).append('<input type="hidden" name="fileName" id="fileName" value="'+this.getName+'" />');

        return form;
    }
    
});

// Xhr implementation
FileUploadHandlerXhr = FileUploadHandler.extend({
      
    cancel: function() {
        this.status = 'cancelling';
        if(this.xhr) {
            this.xhr.abort();    
        }
        
        this.onCancel();
        
        return this;
    },
    
    upload: function() {
        var self = this;

        this.onStart();
        
        this.xhr = new XMLHttpRequest();
                                        
        this.xhr.upload.onprogress = function(event){
            self.onProgress(event);
        };

        this.xhr.onreadystatechange = function(event) {
            if(self.xhr.readyState == 4) {
                self.onComplete(self.xhr.responseText);
            }
        };

        // Set the data
        var data = this.data;
        data.fileName = this.file.name;

        // Build the URL
        //var url = this.url
        var url = this.url+'?'+$.param(this.data);
        //console.log(url);
        
        this.xhr.open('POST', url, true);
        this.xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        this.xhr.setRequestHeader('X-File-Name', encodeURIComponent(this.file.name));
        this.xhr.setRequestHeader('Content-Type', 'application/octet-stream');
        this.xhr.send(this.file);
        
        return this;
    },
    
    onComplete: function(response) {
        this.uploadedBytes = this.file.size;
        this.uploadedKilobytes = this.file.size / 1024;
        this.uploadedMegabytes = this.file.size / 1024 / 1024;
        this.uploadedPercent = '100%';
        this.status = 'complete';
        this.fileUploader.onComplete(this, response);
    },
    
    onProgress: function(event) {
        this.status = 'uploading';
        
        if(event.lengthComputable) {
            this.uploadedBytes = event.loaded;
            this.uploadedPercent = (Math.floor(this.uploadedBytes/event.total*1000)/10)+'%';
        }
        
        this.fileUploader.onProgress(this, event);
    }
    
});

// Manage drop zones
FileDropZone = Class.extend({
    
    initialize: function(element, url, ajaxData, options) {
        this.element = $(element);
        
        // If file dropping is not supported
        if(!this.isSupported()){
            // Hide the drop zone if it is not supported
            element.hide();
            
            //console.log('File dropping is not supported.');
            
            return false;
        }
        // File dropping is supported
        else {
            var self = this;

            this.options = $.extend({
                'onDrop': function() { },
                'onEnter': function() { },
                'onLeave': function() { },
                'onUploadStartContext': false,
                'onUploadStart': function() { },
                'onUploadProgressContext': false,
                'onUploadProgress': function() { },
                'onUploadCompleteContext': false,
                'onUploadComplete': function() { },
                'disableDropOutside': true
            }, options || {});
            
            // Create the file uploader for the drop zone
            this.fileUploader = new FileUploader(url, ajaxData, {
                'onStartContext': self,
                'onStart': 'onUploadStart',
                'onProgressContext': self,
                'onProgress': 'onUploadStart',
                'onCompleteContext': self,
                'onComplete': 'onUploadComplete'
            });

            this.attachEvents();

            return this;    
        }
    },
    
    getFileList: function(element) {
        //console.log('File upload handlers: ', this.fileUploader.fileUploadHandlers);
        var self = this;
        
        var fileList = $('<ul class="fileDropZoneFileList"></ul>');
        
        if(element) {
            var element = $(element);
            element.find('.fileDropZoneFileList').remove();
            fileList = fileList.appendTo(element);
        }
        
        $.each(this.fileUploader.fileUploadHandlers, function(index, fileUploadHandler) {
            var fileListItem = '';

            // Get the appropriate file size
            var fileSize = 0;
            var fileSizeType = 'Bytes';
            var fileSizeTypeAbbreviation = 'B';
            if(fileUploadHandler.file.size > 1024) {
                fileSize = fileUploadHandler.file.size / 1024;
                fileSizeType = 'Kilobytes';
                fileSizeTypeAbbreviation = 'KB';
            }
            if(fileUploadHandler.file.size > 1048576) {
                fileSize = fileUploadHandler.file.size / 1024 / 1024;
                fileSizeType = 'Megabytes';
                fileSizeTypeAbbreviation = 'MB';
            }

            fileListItem += '<li class="fileDropZoneFileListItem fileDropZoneFileListItemStatus'+fileUploadHandler.getStatus()[0].toUpperCase() + fileUploadHandler.getStatus().slice(1)+'">';
            fileListItem += fileUploadHandler.file.name+' ';
            fileListItem += '<span class="fileDropZoneFileListItemUploadedAmount">'+fileUploadHandler['uploaded'+fileSizeType].toFixed(1)+fileSizeTypeAbbreviation+'</span> ';
            fileListItem += '<span class="fileDropZoneFileListItemUploadedPercent">('+fileUploadHandler.uploadedPercent+')</span> ';
            fileListItem += ' of ';
            fileListItem += '<span class="fileDropZoneFileListItemSize">'+fileSize.toFixed(1)+fileSizeTypeAbbreviation+'</span> ';
            fileListItem += '</li>';
            fileListItem = $(fileListItem).appendTo(fileList);

            // Add a cancel link to uploading files
            var cancelHtml;
            if(fileUploadHandler.getStatus() == 'uploading' || fileUploadHandler.getStatus() == 'waiting') {
                cancelHtml = $('<a class="fileDropZoneFileListItemCancel">Cancel</a>')
                    .bind('click', function() {
                        fileUploadHandler.cancel();
                        fileListItem.addClass('fileDropZoneFileListItemStatusCancelled');
                    });
            }
            // Show cancelled status
            else if(fileUploadHandler.getStatus() == 'cancelled') {
                cancelHtml = $('<span class="fileDropZoneFileListItemCancel">(Cancelled)</span>')
            }
            fileListItem.append(cancelHtml);
        });
        
        return fileList;
    },
    
    isSupported: function() {
        return (window.FileReader && Utility.eventIsSupported('drop') && Utility.eventIsSupported('dragenter') && typeof XMLHttpRequest === 'function');
    },
    
    attachEvents: function() {
        var self = this;
        
        // dragover Event
        this.element.bind('dragover', function(event) {
            if(!self.isValidFileDrag(event.originalEvent)) {
                return;
            }
            
            self.onEnter(event.originalEvent);
            
            event.stopPropagation();
            event.preventDefault();
        });
        
        // dragenter Event
        this.element.bind('dragenter', function(event) {
            if(!self.isValidFileDrag(event.originalEvent)) {
                return;
            }
            
            self.onEnter(event.originalEvent);
        });
        
        // dragleave Event
        this.element.bind('dragleave', function(event) {
            if(!self.isValidFileDrag(event.originalEvent)) {
                return;
            }
            
            self.onLeave(event.originalEvent);
            
            event.stopPropagation();
            event.preventDefault();
        });
        
        
        // drop Event
        this.element.bind('drop', function(event) {
            if(!self.isValidFileDrag(event.originalEvent)) {
                return;
            }
            
            event.preventDefault();
            event.originalEvent.preventDefault(); // Necessary for Chrome
            
            self.onDrop(event.originalEvent);
        });
        
        // Disable drop outside
        if(this.options.disableDropOutside) {
            this.disableDropOutside();
        }
    },
    
    disableDropOutside: function(event) {
        $(document).bind('dragover', function(event) {
            if(event.originalEvent.dataTransfer) {
                event.originalEvent.dataTransfer.dropEffect = 'none';
                event.preventDefault();
            }
        });
    },
    
    onDrop: function(event) {
        var self = this;
        
        this.element.removeClass('fileDropZoneInvalidFile');
        this.element.removeClass('fileDropZoneEnter');
        this.element.addClass('fileDropZoneDrop');
        
        //console.log('Dropped file(s): ', event.dataTransfer.files);
        // Upload the dropped files
        $.each(event.dataTransfer.files, function(index, file) {
            self.fileUploader.upload(file);    
        });
                
        //console.log(event.dataTransfer.files);
        this.options.onDrop(event.dataTransfer.files);
        
        event.stopPropagation();
        event.preventDefault();
    },
    
    onEnter: function(event) {
        this.element.addClass('fileDropZoneEnter');
        
        if(!this.isValidFileDrag(event)) {
            this.element.addClass('fileDropZoneInvalidFile');
        }
        else {
            this.element.removeClass('fileDropZoneInvalidFile');
        }
        
        this.options.onEnter();
        
        if(event.dataTransfer) {
            var effect = event.dataTransfer.effectAllowed;
            if(effect == 'move' || effect == 'linkMove') {
                event.dataTransfer.dropEffect = 'move'; // for FF (only move allowed)
            }
            else {
                event.dataTransfer.dropEffect = 'copy'; // for Chrome
            }
        }
        
        event.stopPropagation();
    },
    
    onLeave: function(event) {
        this.element.removeClass('fileDropZoneEnter');
        
        this.options.onLeave();
        
        event.stopPropagation();
    },
    
    onUploadStart: function(fileUploadHandler) {
        //console.log('FileDropZone: onStart called by: ', fileUploadHandler)
        
        // If there is a custom function
        if(this.options.onUploadStart !== false) {
            if(this.options.onUploadStartContext !== false) {
                this.options.onUploadStartContext[this.options.onUploadStart](fileUploadHandler);
            }
            else {
                this.options.onUploadStart(fileUploadHandler);
            }
        }
    },
    
    onUploadProgress: function(fileUploadHandler) {
        //console.log('FileDropZone: onProgress called by: ', fileUploadHandler)
        
        // If there is a custom function
        if(this.options.onUploadProgress !== false) {
            if(this.options.onUploadProgressContext !== false) {
                this.options.onUploadProgressContext[this.options.onUploadProgress](fileUploadHandler);
            }
            else {
                this.options.onUploadProgress(fileUploadHandler);
            }
        }
    },
    
    onUploadComplete: function(fileUploadHandler, response) {
        //console.log('FileDropZone: onComplete called by: ', fileUploadHandler);
        //console.log('FileDropZone: onComplete response: ', response);
        
        // If there is a custom function
        if(this.options.onUploadComplete !== false) {
            if(this.options.onUploadCompleteContext !== false) {
                this.options.onUploadCompleteContext[this.options.onUploadComplete](fileUploadHandler, response);
            }
            else {
                this.options.onUploadComplete(fileUploadHandler, response);
            }
        }
    },
    
    isValidFileDrag: function(event) {
        var dataTransfer = false;
        if(event.dataTransfer) {
            dataTransfer = event.dataTransfer;
        }
        
        // Do not check dt.types.contains in webkit, because it crashes Safari 4
        var isWebkit = (navigator.userAgent.indexOf('AppleWebKit') > -1);

        // dataTransfer.effectAllowed is none in Safari 5
        // dataTransfer.types.contains check is for firefox
        return dataTransfer && dataTransfer.effectAllowed != 'none' && (dataTransfer.files || (!isWebkit && dataTransfer.types.contains && dataTransfer.types.contains('Files')));
    }
    
});