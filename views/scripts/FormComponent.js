/**
 *  formComponent is the base class for all components in the form. all specific components extend off of this class
 *  Handles instances, dependencies and trigger bases
 *
 */
FormComponent = Class.extend({
    initialize: function (parentFormSection, formComponentId, formComponentType, options) {
        this.options = $.extend({
            validationOptions: [],                // 'required', 'email', etc... - An array of validation keys used by this.validate() and formValidator
            showErrorTipOnce: false,
            triggerFunction: null,              // set to a function name, is a function
            componentChangedOptions: null,      // set options for when component:changed is run
            dependencyOptions: null,            // options {jsFunction:Javascript, dependentOn:array, display:enum('hide','lock')}
            instanceOptions: null,              // options {max:#, addButtonText:string, removeButtonText:string}
            tipTargetPosition: 'rightMiddle',   // 'rightMiddle' - Where the tooltip will be placed in relation to the component
            tipCornerPosition: 'leftTop',       // 'leftTop' - The corner of the tip that will point to the tip target position
            tipShowEffect: 'fade',
            isInstance: false
        }, options || {});

        //console.count(formComponentType);
        // Class variables
        this.parentFormSection = parentFormSection;
        this.id = formComponentId;
        this.component = $('#' + formComponentId + '-wrapper');
        this.formData = null;                       // Will be an object is there is just one instance, will be an array if there is more than one instance
        this.type = formComponentType;                       // 'SingleLineText', 'TextArea', etc... - The component formComponentType
        this.errorMessageArray = [];            // Used to store error messages displayed in tips or appended to the description
        this.tip = null;
        this.tipDiv = this.component.find('#' + this.id + '-tip');
        this.tipTarget = null;                  // The ID of the element where the tip will be targeted
        this.validationPassed = true;
        this.disabledByDependency = false;
        this.isRequired = false;
        this.requiredCompleted = false;
        this.validationFunctions = {
            'required': function (options) {
                var errorMessageArray = ['Required.'];
                return options.value != '' ? 'success' : errorMessageArray;
            }
        };

        if (this.options.isInstance) {
            this.instanceArray = null;
            this.clone = null; // Clone of the original HTML, only initiates if instances are turned on
        } else { // do parentInstance functions
            if (this.options.instanceOptions !== null) {
                this.clone = this.component.clone();
                this.iterations = 1;
            } else {
                this.clone = null;
            }
            this.instanceArray = [this];
            this.createInstanceButton();
        }
        if (this.options.instanceOptions && this.options.instanceOptions.callback) {
            var instanceCallBackFunction = $.trim(this.options.instanceOptions.callback);
            //type is add or remove 
            this.options.instanceOptions.callback = function (type) {
                return eval(instanceCallBackFunction);
            };
        }

        // Initialize the implemented component
        this.prime();
        this.reformValidations();

        // Initiation functions
        this.addHighlightListeners();
        this.defineComponentChangedEventListener();
        this.catchComponentChangedEventListener();

        // Add a tip if there is content to add
        if ($.trim(this.tipDiv.html()) !== '') {
            this.addTip();
        }

        // Tip listeners
        this.addTipListeners();
    },

    addHighlightListeners: function() {
        var self = this;

        // Focus
        this.component.find(':input:not(button):not(hidden)').each(function (key, input) {
            $(input).bind('focus', function () {
                self.highlight();
            });
            $(input).bind('blur', function (event) {
                self.removeHighlight();

                // Handle multifield highlight and validation
                if ((self.type === 'FormComponentName' || self.type === 'FormComponentAddress' || self.type === 'FormComponentCreditCard') && self.changed === true) {
                    self.validate();
                }
            });
        });

        // Multiple choice
        if (this.component.find('input:checkbox, input:radio').length > 0) {
            this.component.mouseenter(function (event) {
                self.highlight();

            });
            this.component.mouseleave(function (event) {
                self.removeHighlight();
            });
        }

        return this;
    },

    reformValidations: function() {
        var reformedValidations = {},
        self = this;
        $.each(this.options.validationOptions, function(validationFunction, validationOptions) {
            // Check to see if this component is required, take not of it in the options - used to track which components are required for progress bar
            if (validationOptions === 'required'){
                self.isRequired = true;
            }

            // Check to see if the name of the function is actually an array index
            if (validationFunction >= 0) {
                // The function is not an index, it becomes the name of the option with the value of an empty object
                reformedValidations[validationOptions] = {
                    'component': self.component
                    };
            } else if (typeof (validationOptions) !== 'object') {
                // If the validationOptions is a string
                reformedValidations[validationFunction] = {
                    'component': self.component
                    };
                reformedValidations[validationFunction][validationFunction] = validationOptions;
            } else if (typeof (validationOptions) === 'object') { 
                // If validationOptions is an object
                if (validationOptions[0] !== undefined) {
                    reformedValidations[validationFunction] = {};
                    reformedValidations[validationFunction][validationFunction] = validationOptions;
                } else {
                    reformedValidations[validationFunction] = validationOptions;
                }
                reformedValidations[validationFunction].component = self.component;
            }
        });

        this.options.validationOptions = reformedValidations;
    },


    defineComponentChangedEventListener: function () {
        var self = this;

        // Handle IE events
        this.component.find('input:checkbox, input:radio').each(function (key, input) {
            $(input).bind('click', function (event) {
                $(this).trigger('formComponent:changed', self);
                if($.browser.msie && parseFloat($.browser.version) < 9.0 ){
                    console.log($(this).parent().find('label'));
                    $(this).parent().find('label').toggleClass('checked');
                }
            });
            //console.log($.browser.msie && parseFloat($.browser.version) < 9.0);
            //if($.browser.msie && parseFloat($.browser.version) < 9.0 ){
                // TODO: does this work? does it have the desired effect or have any side effects?
                /*$(input).parent().find('label').bind('click', function (event) {
                    $(input).trigger('click');
                });*/
            //}
        });

        this.component.find(':input:not(button, :checkbox, :radio)').each(function(key, input) {
            $(input).bind('change', function (event) {
                $(this).trigger('formComponent:changed', self);
            });
        });
    },

    catchComponentChangedEventListener: function () {
        var self = this;
        this.component.bind('formComponent:changed', function (event) {
            // Run a trigger on change if there is one
            if (self.options.triggerFunction !== null) {
                eval(self.options.triggerFunction);
            }
            // Prevent validation from occuring with components with more than one input
            if (self.type === 'FormComponentName' || self.type === 'FormComponentAddress' || self.type === 'FormComponentLikert' || self.type === 'FormComponentCreditCard'){
                self.changed = true;
            }
            // Validate the component on change if client side validation is enabled
            if (self.parentFormSection.parentFormPage.form.options.clientSideValidation) {
                self.validate();
            }
        });
    },

    highlight: function () {
        // Add the highlight class and trigger the highlight
        this.component.addClass('formComponentHighlight').trigger('formComponent:highlighted', this.component);
        this.component.trigger('formComponent:showTip', this.component);
    },

    removeHighlight: function () {
        var self = this;
        this.component.removeClass('formComponentHighlight').trigger('formComponent:highlightRemoved', this.component);

        // Wait just a microsecond to see if you are still on the same component
        setTimeout(function () {
            if (!self.component.hasClass('formComponentHighlight')) {
                self.component.trigger('formComponent:hideTip', self.component);
            }
        }, 1);
    },

    getData: function () {
        var self = this;

        // Handle disabled component
        if (this.disabledByDependency || this.parentFormSection.disabledByDependency) {
            this.formData = null;
        } else {
            if (this.instanceArray.length > 1) {
                this.formData = [];
                $.each(this.instanceArray, function(index, component) {
                    var componentValue = component.getValue();
                    self.formData.push(componentValue);
                });
            } else {
                this.formData = this.getValue();
            }
        }
        return this.formData;
    },

    setData: function (data) {
        var self = this;
        if ($.isArray(data)) {
            $.each(data, function(index, value) {
                if ((self.type === 'FormComponentMultipleChoice' && ($.isArray(value) ||  self.multipeChoiceType === 'radio')) || self.type !== 'FormComponentMultipleChoice'){
                    if (index !== 0 && self.instanceArray[index] === undefined) {
                        self.addInstance();
                    }
                    self.instanceArray[index].setValue(value);
                } else {
                    self.setValue(data);
                    return false;
                }
            });
        } else {
            this.setValue(data);
        }
    },

    createInstanceButton: function() {
        var self =  this;
        if(this.options.instanceOptions !== null) {
            //if(this.options.instancesAllowed != 1){
            var addButton = $('<button id="'+this.id+'-addInstance" class="formComponentAddInstanceButton">'+this.options.instanceOptions.addButtonText+'</button>');
            // hide the button if there are dependencies... show it later if necessary
            if(this.options.dependencyOptions !== null){
                addButton.hide();
            }
        
            this.component.after(addButton);
            //this.component.after('<button id="'+this.id+'-addInstance" class="formComponentAddInstanceButton">'+this.options.instanceAddText+'</button>');
            this.parentFormSection.section.find('#'+this.id+'-addInstance').bind('click', function(event){
                event.preventDefault();
                if(!self.disabledByDependency){
                    self.addInstance();
                }
            });
        }
    },

    // Creates instance objects for pre-generated instances
    addInitialInstances: function() {
        if(this.options.instanceOptions !== null && this.options.instanceOptions.initialValues !== undefined && this.options.instanceOptions.initialValues !== null) {
            this.setData(this.options.instanceOptions.initialValues);
        }
    },

    addInstance: function() {
        if(this.options.componentChangedOptions !== null && this.options.componentChangedOptions.instance !== undefined && this.options.componentChangedOptions.instance === true){
            this.component.trigger('formComponent:changed', this);
        }
        var parent = this;
        if(this.instanceArray.length < this.options.instanceOptions.max || this.options.instanceOptions.max === 0){
            var instanceClone = this.clone.clone();
            var addButton = this.parentFormSection.section.find('#'+this.id+'-addInstance');
            var animationOptions = {};
            if(this.options.instanceOptions.animationOptions !== undefined){
                animationOptions = $.extend(animationOptions, this.parentFormSection.parentFormPage.form.options.animationOptions.instance, this.options.instanceOptions.animationOptions);
            }
            else {
                animationOptions = this.parentFormSection.parentFormPage.form.options.animationOptions.instance;
            }

            // Create the remove button
            $(instanceClone).append('<button id="'+this.id+'-removeInstance" class="formComponentRemoveInstanceButton">'+this.options.instanceOptions.removeButtonText+'</button>');
            
            // Add an event listener on the remove button
            instanceClone.find('#'+this.id+'-removeInstance').bind('click', function(event){
                var target = $(event.target);
                event.preventDefault();
                
                parent.instanceArray = $.map(parent.instanceArray, function(cloneId, index){
                    if(cloneId.component.attr('id') ===  target.parent().attr('id')){
                        if(cloneId.tip !== null){
                            cloneId.tip.hide();
                        }
                        cloneId = null;
                    }
                    return cloneId;
                });
                if(animationOptions.removeEffect === 'none' || animationOptions.removeDuration === 0){
                    target.parent().remove();
                    target.remove();
                } else {
                    if(animationOptions.removeEffect === 'slide'){
                        target.parent().slideUp(animationOptions.removeDuration, function(){
                            target.parent().remove();
                            target.remove();
                            //parent.parentFormSection.parentFormPage.form.formPageWrapper.dequeue();
                            parent.parentFormSection.parentFormPage.form.adjustHeight(animationOptions);
                        });
                        
                    }else {
                        target.parent().fadeOut(animationOptions.removeDuration, function(){
                            target.parent().remove();
                            target.remove();
                            //parent.parentFormSection.parentFormPage.form.formPageWrapper.dequeue();
                            parent.parentFormSection.parentFormPage.form.adjustHeight(animationOptions);
                        });
                    }
                }
                if(parent.instanceArray.length < parent.options.instanceOptions.max || parent.options.instanceOptions.max === 0){
                    addButton.show();
                }
                parent.relabelInstances(parent.instanceArray, animationOptions);
            });
            instanceClone.hide();
            // Insert the clone right before the add button
            addButton.before(instanceClone);
            if(animationOptions.appearEffect === 'none' || animationOptions.appearDuration === 0){
                
                instanceClone.show();
            } else {
                if(animationOptions.appearEffect === 'slide'){
                    instanceClone.slideDown(animationOptions.appearDuration, function(){
                        parent.parentFormSection.parentFormPage.form.formPageWrapper.dequeue();
                        parent.parentFormSection.parentFormPage.form.adjustHeight(animationOptions);
                    });
                }else {
                    instanceClone.fadeIn(animationOptions.appearDuration, function(){
                        parent.parentFormSection.parentFormPage.form.formPageWrapper.dequeue();
                        parent.parentFormSection.parentFormPage.form.adjustHeight(animationOptions);
                    });
                }
            }

            this.nameInstance(instanceClone);
            
            var instanceObject = this.createInstanceObject(instanceClone, this.options);
            this.instanceArray.push(instanceObject);
            this.relabelInstances(this.instanceArray, animationOptions);
            if(this.instanceArray.length === this.options.instanceOptions.max && this.options.instanceOptions.max !== 0){
                //if(this.instanceArray.length == this.options.instancesAllowed && this.options.instancesAllowed !== 0) {
                addButton.hide();
            }
            
            if(this.options.dependencyOptions !== undefined && this.options.dependencyOptions !== null){
                var objectTop = parent.parentFormSection.parentFormPage.form;
                var dependentOnComponent = objectTop.select(this.options.dependencyOptions.dependentOn);
                dependentOnComponent.component.find(':text, textarea').bind('keyup', function(event) {
                    instanceObject.checkDependencies();
                });

                dependentOnComponent.component.bind('formComponent:changed', function(event) {
                    instanceObject.checkDependencies();
                });
            }
            
            if(this.options.instanceOptions.callback){
                this.options.instanceOptions.callback('add');
            }
        // Resize the page
        //parent.parentFormSection.parentFormPage.scrollTo();
        }
        return this;
    },

    nameInstance: function(component) {
        component = $(component);
        var self = this,
        ending = '';
        this.iterations++;
        component.attr('id', component.attr('id').replace('-wrapper', '-instance'+this.iterations+'-wrapper'));
        component.find('*').each(function(key, child){
            if($(child).attr('id')){
                changeName(child, 'id');
            }
            if($(child).attr('for')){
                changeName(child, 'for');
            }
            if($(child).attr('name')){
                changeName(child, 'name');
            }
        });
        
        function changeName(child, attribute){
            ending = getEnding($(child).attr(attribute)) ;
            if(ending == ''){
                $(child).attr(attribute, $(child).attr(attribute) +'-instance'+self.iterations+ending);
            }else {
                $(child).attr(attribute, $(child).attr(attribute).replace(ending, '-instance'+self.iterations+ending));
            }
        }
        
        function getEnding(identifier){
            var ending = '';
            if(identifier.match(/\-(div|label|tip|removeInstance)\b/)){
                ending = identifier.match(/\-(div|label|tip|removeInstance)\b/)[0];
            } else {

            }
            
            return ending;
        }
        return component;
    },

    createInstanceObject:function(instanceClone, options){
        var tempOptions = $.extend(true, {}, options);
        tempOptions.isInstance = true;
        if(this.options.componentChangedOptions !== null && this.options.componentChangedOptions.children !== undefined && this.options.componentChangedOptions.children === false ){
            tempOptions.componentChangedOptions = null;
        }
        var instanceObject = new window[this.type](this.parentFormSection, this.id + '-instance'+this.iterations, this.type, tempOptions);
        return instanceObject;
    },

    relabelInstances:function(instanceArray, animationOptions){
        $.each(instanceArray, function(key, instance){
            if( key!== 0) {
                var count = key+1,
                label = instance.component.find('#'+instance.component.attr('id').replace('-wrapper','-label'));
                if(label.length > 0) {
                    var star = label.find('span.formComponentLabelRequiredStar');
                    if(star.length > 0){
                        star.remove();
                    }
                    if(label.html().match(/:$/)){
                        label.html(label.html().replace(/(\([0-9]+\))?:/, ' ('+count+'):'));
                    } else {
                        if (label.text().match(/(\([0-9]+\))$/)){
                            label.text(label.text().replace(/(\([0-9]+\))$/, '('+count+')'));
                        } else {
                            label.text(label.text() + ' ('+count+')');
                        }
                    }
                    label.append(star);
                } else {
                    label = instance.component.find('label');
                    var star = label.find('span.formComponentLabelRequiredStar');
                    if(star.length > 0){
                        star.remove();
                    }
                    if (label.text().match(/(\([0-9]+\))$/)){
                        label.text(label.text().replace(/(\([0-9]+\))$/, '('+count+')'));
                    } else {
                        label.text(label.text() + ' ('+count+')');
                    }
                    label.append(star);
                }

            }
        });
        //this.parentFormSection.parentFormPage.form.formPageWrapper.dequeue();
        this.parentFormSection.parentFormPage.form.adjustHeight(animationOptions);
    },

    addTip: function() {
        var self = this;

        // Check to see if the tip already exists
        if(typeof(this.tip) !== 'function') {
            // Create the tip
            var tip = this.tipTarget.simpletip({
                persistent: true,
                focus: true,
                position: 'topRight',
                content: self.tipDiv,
                baseClass: 'formTip',
                showEffect: self.options.tipShowEffect,
                hideEffect: 'none',
                onBeforeShow: function(){
                    if(self.tipDiv.find('.tipContent').text() === ''){
                        return false;
                    }
                },
                onShow: function(){
                    // Scroll the page to show the tip if the tip is off the page
                    var height = $(window).height();
                    var offset = this.getTooltip().offset().top + this.getTooltip().outerHeight() + 12;
                    if($(window).scrollTop() + height < offset) {
                        $.scrollTo(offset - height + 'px', 250, {
                            axis:'y'
                        });
                    }
                }
            });
            this.tip = tip.simpletip();
        }
    },

    addTipListeners: function() {
        var self = this;

        // Show a tip
        this.component.bind('formComponent:showTip', function(event) {
            // Make sure the tip exists and display the tip if it is not empty
            if(self.tip && typeof(self.tip) === 'object' && $.trim(self.tipDiv.html()) !== '') {
                self.tip.show();
            }
            
        });

        // Hide a tip
        this.component.bind('formComponent:hideTip', function(event) {
            // Make sure the tip exists
            if(self.tip && typeof(self.tip) === 'object') {
                self.tip.hide();
            }

            // Show error tips once
            if(self.options.showErrorTipOnce){
                self.clearValidation();
            }
        });

        return this;
    },

    clearValidation: function() {
        // Reset the error message array and validation passes boolean
        this.errorMessageArray = [];
        this.validationPassed = true;

        // Reset the classes
        this.component.removeClass('formComponentValidationFailed');
        this.component.addClass('formComponentValidationPassed');

        // Remove any tipErrorUl from the tip div
        this.component.find('.tipErrorUl').remove();

        // Handle tip display
        if(this.tip && typeof(this.tip) === 'object') {
            // Update the tip content
            this.tip.update(this.tipDiv.html());

            // Hide the tip if the tip is empty
            if($.trim(this.tipDiv.find('.tipContent').html()) === ''){
                this.tipDiv.hide();
            }
        }
    },

    // Abstract functions
    prime: function() { },
    getValue: function() { },
    setValue: function() { },

    clearData: function() {
        this.component.find(':input').val('');
    },

    validate: function(silent) {
        //console.log('validating a component Bi!', this.parentFormSection.parentFormPage.id, this.id);
        var validation, silentValidationPassed;
        // Handle dependencies
        if(this.disabledByDependency || this.parentFormSection.disabledByDependency) {
            return null;
        }

        // If there are no validations, return true
        if(this.options.validationOptions.length < 1) {
            return true;
        }
        if(silent){
            silentValidationPassed = true;
        }

        var self = this;
        this.clearValidation();
        var value = this.getValue();

        if(value === null){
            return true;
        }

        $.each(this.options.validationOptions, function(validationType, validationOptions){
            validationOptions.value = value;
            if(self.validationFunctions[validationType] !== undefined) {
                validation = self.validationFunctions[validationType](validationOptions);    
            }
            else {
                var errorString = validationType + ' is not a valid validation. for '+self.id+' please check your source';
                if(typeof window.console !== undefined){ 
                    console.log(errorString); 
                }
                else {
                    alert(errorString);
                }
            }
            
            if(validation === 'success') {
                if(validationType.match('required')){
                    self.requiredCompleted = true;
                }
                return true;
            }
            else {
                if(validationType.match('required')){
                    self.requiredCompleted = false;
                    if(self.parentFormSection.parentFormPage.form.options.pageNavigator !== false){
                        var pageIndex = $.inArray(self.parentFormSection.parentFormPage.id, self.parentFormSection.parentFormPage.form.formPageIdArray);
                        $('#navigatePage'+(pageIndex + 1)).addClass('formPageNavigatorLinkWarning');
                    }
                }
                if(silent){
                    silentValidationPassed = false;
                } else {
                    $.merge(self.errorMessageArray, validation);   
                }
            }
        });
        
        if(silent) {
            return silentValidationPassed;
        }
        else {
            if(this.errorMessageArray.length > 0 ) {
                this.handleErrors();
                this.validationPassed = false;
            }
            return this.validationPassed;
        }
    },

    handleServerValidationResponse: function(errorMessageArray) {
        // Clear the validation
        $.each(this.instanceArray, function(instanceKey, instance) {
            instance.clearValidation();
        });

        // If there are errors
        if(errorMessageArray !== null && errorMessageArray.length > 0) {
            // If there are instances
            if(this.instanceArray.length !== 1) {
                // Go through each of the instances and assign the error messages
                $.each(this.instanceArray, function(instanceKey, instance) {
                    if(!Utility.empty(errorMessageArray[instanceKey])){
                        $.each(errorMessageArray[instanceKey], function(errorMessageArrayIndex, errorMessage){
                            if(errorMessage !== '') {
                                instance.errorMessageArray.push(errorMessage);
                            }
                        });
                        if(instance.errorMessageArray.length > 0) {
                            instance.validationPassed = false;
                            instance.handleErrors();
                        }
                    }
                });
            }
            // If there aren't instances
            else {
                this.errorMessageArray = errorMessageArray;
                this.validationPassed = false;
                this.handleErrors();
            }
        }
    },

    handleErrors: function() {
        var self = this;

        // Change classes
        this.component.removeClass('formComponentValidationPassed');
        this.component.addClass('formComponentValidationFailed');

        // Add a tip div and tip neccesary
        if(this.tipDiv.length === 0) {
            this.createTipDiv();
        }

        // If validation tips are disabled
        if(!this.parentFormSection.parentFormPage.form.options.validationTips) {
            return;
        }

        // Put the error list into the tip
        var tipErrorUl = $('<ul id="'+this.id+'-tipErrorUl" class="tipErrorUl"></ul>');
        $.each(this.errorMessageArray, function(index, errorMessage){
            tipErrorUl.append("<li>"+errorMessage+"</li>");
        });
        this.tipDiv.find('.tipContent').append(tipErrorUl);

        // Update the tip content
        this.tip.update(self.tipDiv.html());

        // Show the tip if you are currently on it
        if(this.component.hasClass('formComponentHighlight')) {
            this.tip.show();
        }
    },

    createTipDiv: function() {
        // Create a tip div and tip neccesary
        this.tipDiv = $('<div id="'+this.id+'-tip" style="display: none;"></div>');
        this.component.append(this.tipDiv);
        this.addTip();
    },

    disableByDependency: function(disable) {
        //console.log('running disable by dependency ', this.id, disable);

        var self = this;
        var animationOptions = {};

        if(this.options.componentChangedOptions !== null && this.options.componentChangedOptions.dependency !== undefined && this.options.componentChangedOptions.dependency === true){
            this.component.trigger('formComponent:changed', this);
        }

        //stuff we are going to do stuff to...
        var elementsToDisable = this.component;
        $.each(this.instanceArray, function(index, componentInstance){
            if(index !== 0){
                elementsToDisable = elementsToDisable.add(componentInstance.component);
            }
        });
        if(this.options.instanceOptions !== null && (this.instanceArray.length < this.options.instanceOptions.max || this.options.instanceOptions.max === 0)){
            var addButton = $(self.parentFormSection.section.find('#'+this.id+'-addInstance'));
            if(self.parentFormSection.parentFormPage.form.initializing) {
                if(!disable && addButton.is(':hidden')){
                    addButton.show();
                    self.parentFormSection.parentFormPage.form.adjustHeight({
                        adjustHeightDuration:0
                    });
                }
            }
            elementsToDisable = elementsToDisable.add(addButton);
        }
  
        if(self.parentFormSection.parentFormPage.form.initializing) {
            animationOptions = {
                adjustHeightDelay : 0,
                appearDuration : 0,
                appearEffect: 'none',
                hideDuration : 0,
                hideEffect: 'none'

            };
        }
        else if(this.options.dependencyOptions.animationOptions !== undefined){
            animationOptions = $.extend(animationOptions, this.parentFormSection.parentFormPage.form.options.animationOptions.dependency, this.options.dependencyOptions.animationOptions);
        }
        else {
            animationOptions = this.parentFormSection.parentFormPage.form.options.animationOptions.dependency;
        }
        
        // If the condition is different then the current condition or if the form is initializing
        if(this.disabledByDependency !== disable || this.parentFormSection.parentFormPage.form.initializing) {
            // Disable the component
            if(disable) {
                // Clear the validation to prevent validation issues with disabled component
                this.clearValidation();

                // Hide the component
                if(this.options.dependencyOptions.display === 'hide') {
                    //console.log('hiding component ', this.id)
                    if(animationOptions.hideEffect === 'none' || animationOptions.hideDuration === 0){
                        //console.log('hiding component ', elementsToDisable, animationOptions.hideDuration);
                        //
                        if (animationOptions.hideDuration === 0) { 
                            elementsToDisable.hide(); 
                        } else {
                            elementsToDisable.hide(animationOptions.hideDuration); 
                        } 
                        //elementsToDisable.hide(animationOptions.hideDuration);
                        
                        self.parentFormSection.parentFormPage.form.adjustHeight(animationOptions);
                    }
                    else {
                        if(animationOptions.hideEffect === 'fade'){
                            
                            elementsToDisable.fadeOut(animationOptions.hideDuration, function() {
                                self.parentFormSection.parentFormPage.form.adjustHeight(animationOptions);
                            });
                        }
                        else if(animationOptions.hideEffect === 'slide'){
                        
                            elementsToDisable.slideUp(animationOptions.hideDuration, function() {
                                self.parentFormSection.parentFormPage.form.adjustHeight(animationOptions);
                            });
                        }
                    }
                }
                // Lock the component
                else {
                    elementsToDisable.addClass('formComponentDependencyDisabled').find(':input').attr('disabled', 'disabled');
                }
            }
            // Show or unlock the component
            else {
                // Show the component
                if(this.options.dependencyOptions.display === 'hide') {
                    //console.log('showing component')
                    if(animationOptions.appearEffect === 'none' || animationOptions.apearDuration === 0){
                        
                        elementsToDisable.show();
                        self.parentFormSection.parentFormPage.form.adjustHeight(animationOptions);
                    }else {
                        if(animationOptions.appearEffect === 'fade'){
                        
                            elementsToDisable.fadeIn(animationOptions.appearDuration);
                            self.parentFormSection.parentFormPage.form.adjustHeight(animationOptions);
                        }else if(animationOptions.appearEffect === 'slide'){
                        
                            elementsToDisable.slideDown(animationOptions.appearDuration);
                            self.parentFormSection.parentFormPage.form.adjustHeight(animationOptions);
                        }
                    }
                }
                // Unlock the component
                else {
                    elementsToDisable.removeClass('formComponentDependencyDisabled').find(':input').removeAttr('disabled');
                }
            }
            this.disabledByDependency = disable;
        }
    },

    checkDependencies: function() {
        var self = this;
        if(this.options.dependencyOptions !== null) {
            // Run the dependency function
            //console.log(self.options.dependencyOptions.jsFunction);
            //console.log(eval(self.options.dependencyOptions.jsFunction));
            var disable = !(eval(self.options.dependencyOptions.jsFunction));
            this.disableByDependency(disable);
        /*
            if(disable) {
                console.log('disabled ', this.id);
            }
            else {
                console.log('enabled ', this.id);
            }
            */
        }
    }
});