/**
 * formSection handles all functions on the section level, including dependencies and instances. A section groups components.
 *
 */
FormSection = Class.extend({
    initialize: function(parentFormPage, sectionId, options) {
        this.options = $.extend({
            dependencyOptions: null,            // options {jsFunction:#, dependentOn:array, display:enum('hide','lock')}
            instanceOptions: null              // options {max:#, addButtonText:string, removeButtonText:string}
        }, options || {});

        // Class variables
        this.parentFormPage = parentFormPage;
        this.id = sectionId;
        this.section = $('#'+sectionId);
        this.formComponents = {};
        this.formData = null;                       // Will be an object is there is just one instance, will be an array if there is more than one instance
        this.disabledByDependency = false;
        this.primeInstance = this;

        // Set instance only options
        if(this.options.isInstance) {
            this.instanceArray = null;
            this.clone = null;                  // clone of the original html.. only initiates if instances are turned on...
        }
        // Do parentInstance functions
        else {
            this.instanceArray = [this];

            if(this.options.instanceOptions != null){
                this.clone = this.section.clone();
                //this.clone.find('input, textbox, select').val('');
                this.iterations = 1;
            }
            else {
                this.clone = null;
            }
            
            this.createInstanceButton();
        }
        if(this.options.instanceOptions && this.options.instanceOptions.callback) {
            var instanceCallBackFunction = $.trim(this.options.instanceOptions.callback);
                                                            //type is add or remove 
            this.options.instanceOptions.callback = function(type) {return eval(instanceCallBackFunction);};
        }
    },
    
    select: function(formComponentId) {
        var formComponent = false;
        
        $.each(this.formComponents, function(currentFormComponentId, currentFormComponent) {
            // TODO: This doesn't work with -instance
            //console.log(formComponentId, 'vs', currentFormComponentId.split('-section')[0]);
            if(formComponentId == currentFormComponentId.split('-section')[0]) {
                formComponent = currentFormComponent;
                return false;
            }
        });
        
        return formComponent;
    },

    createInstanceButton: function() {
        var self =  this;
        if(this.options.instanceOptions != null){
            var buttonId = this.id+'-addInstance',
            addButton = $('<button id="'+buttonId+'" class="formSectionAddInstanceButton">' + this.options.instanceOptions.addButtonText + '</button>');
            if(this.options.dependencyOptions !== null) {
                addButton.hide();
            }

            this.instanceArray[this.instanceArray.length - 1].section.after(addButton);

            //section.after(addButton);
            this.parentFormPage.page.find('#'+buttonId).bind('click', function(event){
                event.preventDefault();
                self.addSectionInstance();
            });
        }
    },

    // Creates instance objects for pre-generated instances
    addInitialSectionInstances: function() {
        if(this.options.instanceOptions !== null && this.options.instanceOptions.initialValues !== undefined && this.options.instanceOptions.initialValues !== null) {

            var count = this.options.instanceOptions.initialValues.length - 1;
            for(var i = 0; i < count; i++) {
                this.addSectionInstance(true)
            }
            
            // Move the add button
            var addButton = $('#'+this.id+'-addInstance');
            this.instanceArray[this.instanceArray.length - 1].section.after(addButton);
        }
    },

    addSectionInstance: function(sectionHtmlExists) {
        var parent = this.primeInstance;
        
        var newSectionInstance = false;

        // If more instances are allowed
        if(this.instanceArray.length < this.options.instanceOptions.max || this.options.instanceOptions.max === 0) {
            this.iterations++;
            var instanceClone;

            // Do not use a clone of the first section if the section HTML has already been generated
            if(sectionHtmlExists) {
                instanceClone = $('#'+this.id+'-section'+this.iterations);
            }
            else {
                instanceClone = this.clone.clone();

                // Rename the section instance
                this.nameSectionInstance(instanceClone, sectionHtmlExists);
            }

            // Create the remove button
            var removeButtonId = this.id+'-removeInstance',
            removeButton = '<button id="'+removeButtonId+'" class="formSectionRemoveInstanceButton">'+this.options.instanceOptions.removeButtonText+'</button>';

            // Set the default animation options
            var animationOptions = {};
            if(this.options.instanceOptions.animationOptions !== undefined){
                $.extend(animationOptions, this.parentFormPage.form.options.animationOptions.instance, this.options.instanceOptions.animationOptions);
            }
            else {
                animationOptions = this.parentFormPage.form.options.animationOptions.instance;
            }

            // Add the remove button
            $(instanceClone).append(removeButton);

            // Add the event listener for the remove button
            instanceClone.find('#'+removeButtonId).bind('click', function(event){
                var target = $(event.target);
                event.preventDefault();

                parent.instanceArray = $.map(parent.instanceArray, function(cloneId, index){
                   if(cloneId.section.attr('id') ==  target.parent().attr('id')){
                        cloneId = null;
                   }
                   return cloneId;
                });

                // Handle the animation for the removal of the section
                if(animationOptions.removeEffect == 'none' || animationOptions.removeDuration === 0){
                    target.parent().remove();
                    target.remove();
                }
                else {
                    if(animationOptions.removeEffect == 'slide'){
                        target.parent().slideUp(animationOptions.removeDuration, function(){
                            target.parent().remove();
                            target.remove();
                            
                        });
                        //parent.parentFormPage.form.formPageWrapper.dequeue();
                        parent.parentFormPage.form.adjustHeight(animationOptions);

                    }
                    else {
                        target.parent().fadeOut(animationOptions.removeDuration, function(){
                            target.parent().remove();
                            target.remove();
                            //parent.parentFormPage.form.formPageWrapper.dequeue();
                            parent.parentFormPage.form.adjustHeight(animationOptions);
                        });
                    }
                }

                // Hide or remove the add button based on whether or not more instances can be added
                if(parent.instanceArray.length < parent.options.instanceOptions.max || parent.options.instanceOptions.max === 0) {
                    parent.parentFormPage.page.find('#'+parent.id+'-addInstance').show();
                }

                // Relabel the instance array
                parent.relabelSectionInstances(parent.instanceArray, animationOptions);
                if(parent.options.instanceOptions.callback){
                    parent.options.instanceOptions.callback('remove');
                }
            });

            // Add the clone of the instance only if it not already pre-generated
            if(!sectionHtmlExists) {
                // Put the section in there, but hide it first, just in case
                instanceClone.hide();
                this.parentFormPage.page.find('#'+this.id+'-addInstance').before(instanceClone);    
                
                // Show the instance section immediately
                if(animationOptions.appearEffect == 'none' || animationOptions.appearDuration === 0){
                    instanceClone.show();
                }
                // Show the instance section with an animation
                else {
                    if(animationOptions.appearEffect == 'slide'){

                        instanceClone.slideDown(animationOptions.appearDuration, function(){
                            //parent.parentFormPage.form.formPageWrapper.dequeue();
                            parent.parentFormPage.form.adjustHeight(animationOptions);
                        });                    
                    }
                    else {
                        instanceClone.fadeIn(animationOptions.appearDuration, function(){});
                        //parent.parentFormPage.form.formPageWrapper.dequeue();
                        parent.parentFormPage.form.adjustHeight(animationOptions);
                    }
                }
            }

            // Create an instance object to represent the section instance
            var instanceObject = this.createSectionInstanceObject(instanceClone, this.options);
            instanceObject.primeInstance = this;
            this.instanceArray.push(instanceObject);

            // Add the clone of the instance only if it not already pregenerated
            if(!sectionHtmlExists) {
                this.relabelSectionInstances(this.instanceArray, animationOptions);
            }

            // Add an "add instance" button if the max has not been reached
            if(this.instanceArray.length >= this.options.instanceOptions.max && this.options.instanceOptions.max !== 0) {
                this.parentFormPage.page.find('#'+this.id+'-addInstance').hide();
            }
            
            newSectionInstance = instanceObject;
            if(this.options.instanceOptions.callback){
                this.options.instanceOptions.callback('add');
            }
        }
        
        return newSectionInstance;
    },

    removeInstance: function() {
        return this;
    },

    nameSectionInstance: function(component, sectionHtmlExists) {
        var self = this,
        ending = '';
        $(component).attr('id', $(component).attr('id')+ '-section'+this.iterations);
        $(component).find('*').each(function(key, child){
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
                    $(child).attr(attribute, $(child).attr(attribute) +'-section'+self.iterations+ending);
                }else {
                    $(child).attr(attribute, $(child).attr(attribute).replace(ending, '-section'+self.iterations+ending));
                }
        }

        function getEnding(identifier){
            var ending = '';
            if(identifier.match(/(\-[A-Za-z0-9]+)&?/)){
                ending = identifier.match(/(\-[A-Za-z0-9]+)&?/)[1];
            } else {

            }
            return ending;
        }

        return component;
    },

    createSectionInstanceObject: function(instanceClone, options) {
        var tempOptions = $.extend(true, {}, options);
        tempOptions.isInstance = true;
        var self = this,
        instanceObject = new FormSection(this.parentFormPage, this.id+'-section'+this.iterations, tempOptions);

        $.each(this.formComponents, function(key, component) {
            var componentTempOptions = $.extend(true, {}, component.options);
            componentTempOptions.isInstance = false;
            var componentClone = new window[component.type](instanceObject, component.id+'-section'+self.iterations, component.type, componentTempOptions);
            instanceObject.addComponent(componentClone);
        });
        
        $.each(instanceObject.formComponents, function(key, instancedComponent) {
            if(instancedComponent.options.dependencyOptions != undefined){
                var objectTop = self.parentFormPage.form;

                // Define the dependent on component
                var dependentOnComponent = objectTop.select(instancedComponent.options.dependencyOptions.dependentOn);

                // Check to see if the dependentOn component is within in same section
                if(self.section.find('#'+instancedComponent.options.dependencyOptions.dependentOn+'-wrapper').length != 0) {
                    // If the component that is dependentOn is inside the instanced section, use the instanced section's component as the dependentOn
                    //console.log(instanceObject.formComponents[instancedComponent.options.dependencyOptions.dependentOn+'-section'+self.iterations]);
                    if(instanceObject.formComponents[instancedComponent.options.dependencyOptions.dependentOn+'-section'+self.iterations]) {
                        //console.log('found it')
                        dependentOnComponent = instanceObject.formComponents[instancedComponent.options.dependencyOptions.dependentOn+'-section'+self.iterations];
                    }
                }
                
                //console.log(dependentOnComponent);
                
                dependentOnComponent.component.find(':text, textarea').bind('keyup', function(event) {
                    instancedComponent.checkDependencies();
                });

                dependentOnComponent.component.bind('formComponent:changed', function(event) {
                    instancedComponent.checkDependencies();
                });

                instancedComponent.checkDependencies();
            }
        });

        return instanceObject;
    },

    relabelSectionInstances:function(instanceArray, animationOptions){
        $.each(instanceArray, function(key, instance){
            if( key!== 0) {
                var count = key+1,
                label = instance.section.find('.formSectionTitle').children(':first');
                if(label.length > 0){
                    if (label.text().match(/(\([0-9]+\))$/)){
                        label.text(label.text().replace(/(\([0-9]+\))$/, '('+count+')'));
                    } else {
                        label.text(label.text() + ' ('+count+')');
                    }
                    
                }
            }
       });
       //this.parentFormPage.form.formPageWrapper.dequeue();
       this.parentFormPage.form.adjustHeight(animationOptions);
    },

    addComponent: function(component) {
        this.formComponents[component.id] = component;
        return this;
    },

    clearValidation: function() {
        $.each(this.formComponents, function(componentKey, component) {
            component.clearValidation();
        });
    },

    getData: function() {
        var self = this;

        // Handle disabled sections
        if(this.disabledByDependency) {
            this.formData = null;
        }
        else {
            if(this.instanceArray.length > 1) {
                this.formData = [];
                $.each(this.instanceArray, function(instanceIndex, instanceFormSection) {
                    var sectionData = {};
                    $.each(instanceFormSection.formComponents, function(formComponentKey, formComponent) {
                        if(formComponent.type != 'FormComponentLikertStatement') {
                            formComponentKey = formComponentKey.replace(/-section[0-9]+/, '');
                            sectionData[formComponentKey] = formComponent.getData();
                        }
                    });
                    self.formData.push(sectionData);
                });
            }
            else {
                this.formData = {};
                $.each(this.formComponents, function(key, component) {
                    // Don't include the "view" or "viewData" hidden value in getData requests
                    if(component.type != 'FormComponentLikertStatement' && component.id != self.parentFormPage.form.id+'-view' && component.id != self.parentFormPage.form.id+'-viewData'){
                        self.formData[key] = component.getData();
                    }
                });
            }
        }
        return this.formData;
    },

    setData: function(data) {
        var self = this;
        if($.isArray(data)) {
            $.each(data, function(index, instance){
               if(index !== 0 && self.instanceArray[index] == undefined){
                   self.addSectionInstance();
               }
               $.each(instance, function(key, componentData){
                   if(index !== 0){
                    key = key + '-section'+(index+1);
                   }
                   if(self.instanceArray[index].formComponents[key] != undefined){
                       self.instanceArray[index].formComponents[key].setData(componentData)
                   }
               });
               /*$.each(self.instanceArray[index].formComponents, function(key, component){
                   
                   component.setData(instance[key]);
               });*/
            });
        }
        else {
            $.each(data, function(key, componentData) {
                if(self.formComponents[key] != undefined){
                    self.formComponents[key].setData(componentData);
                }
                
            });
        }
    },

    disableByDependency: function(disable) {
        var self = this;

        if(self.parentFormPage.form.initializing) {
            var animationOptions = {
                adjustHeightDuration : 0,
                appearDuration : 0,
                appearEffect: 'none',
                hideDuration : 0,
                hideEffect: 'none'

            }
        } else if(this.options.dependencyOptions.animationOptions !== undefined){
            animationOptions = $.extend(animationOptions, this.parentFormPage.form.options.animationOptions.dependency, this.options.dependencyOptions.animationOptions);
        } else {
            animationOptions = this.parentFormPage.form.options.animationOptions.dependency;
        }

        var elementsToDisable = this.section;
        $.each(this.instanceArray, function(index, sectionInstance){
            if(index !== 0){
                elementsToDisable = elementsToDisable.add(sectionInstance.section);
            }
        });
        if(this.options.instanceOptions !== null && (this.instanceArray.length < this.options.instanceOptions.max || this.options.instanceOptions.max === 0)){
            var addButton = $(self.parentFormPage.form.form.find('#'+this.id+'-addInstance'));
            if(self.parentFormPage.form.initializing) {
                if(!disable && addButton.is(':hidden')){
                    addButton.show();
                    self.parentFormPage.form.adjustHeight({adjustHeightDuration:0});
                }
            }
            elementsToDisable = elementsToDisable.add(addButton);
        }

        // If the condition is different then the current condition
        if(this.disabledByDependency !== disable) {
            // Disable the section
            if(disable) {
                // Hide the section
                if(this.options.dependencyOptions.display == 'hide') {
                    //console.log('hiding section');
                    if(animationOptions.hideEffect == 'none' || animationOptions.hideDuration === 0){
                        elementsToDisable.hide();
                        self.parentFormPage.form.adjustHeight(animationOptions);
                    } else {
                        if(animationOptions.appearEffect === 'fade'){
                        elementsToDisable.fadeOut(animationOptions.hideDuration, function() {
                            self.parentFormPage.form.adjustHeight(animationOptions);
                        });
                        }else if(animationOptions.appearEffect === 'slide'){
                            elementsToDisable.slideUp(animationOptions.hideDuration, function() {
                                self.parentFormPage.form.adjustHeight(animationOptions);
                            });
                        }
                    }
                    
                }
                // Lock the section and disable all inputs
                else {
                    elementsToDisable.addClass('formSectionDependencyDisabled').find(':not(.formComponentDisabled) > :input').attr('disabled', 'disabled');
                    this.parentFormPage.form.adjustHeight({adjustHeightDuration:0}); // Handle if they are showing a border on the DependencyDisabled class
                }
            }
            // Show or unlock the section
            else {
                // Show the section
                if(this.options.dependencyOptions.display == 'hide') {
                    if(animationOptions.appearEffect == 'none' || animationOptions.appearDuration === 0){
                        elementsToDisable.show();
                        self.parentFormPage.form.adjustHeight(animationOptions);
                        if(self.options.dependencyOptions.onAfterEnable) {
                            //console.log('Running: ', self.options.dependencyOptions.onAfterEnable);
                            eval(self.options.dependencyOptions.onAfterEnable);
                        }
                    }
                    else {
                        if(animationOptions.hideEffect === 'fade') {
                            elementsToDisable.fadeIn(animationOptions.appearDuration, function() {
                                if(self.options.dependencyOptions.onAfterEnable) {
                                    //console.log('Running: ', self.options.dependencyOptions.onAfterEnable);
                                    eval(self.options.dependencyOptions.onAfterEnable);
                                }
                            });
                            self.parentFormPage.form.adjustHeight(animationOptions);
                        }
                        else if(animationOptions.hideEffect === 'slide'){
                            elementsToDisable.slideDown(animationOptions.appearDuration, function() {
                                if(self.options.dependencyOptions.onAfterEnable) {
                                    //console.log('Running: ', self.options.dependencyOptions.onAfterEnable);
                                    eval(self.options.dependencyOptions.onAfterEnable);
                                }
                            });
                            self.parentFormPage.form.adjustHeight(animationOptions);
                        }
                    }
                    //console.log('showing section');
                }
                // Unlock the section and reenable all inputs that aren't manually disabled
                else {
                    elementsToDisable.removeClass('formSectionDependencyDisabled').find(':not(.formComponentDisabled) > :input').removeAttr('disabled');
                    this.parentFormPage.form.adjustHeight({adjustHeightDuration:0}); // Handle if they are showing a border on the DependencyDisabled class
                }

                this.checkChildrenDependencies();
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
        }

        // Handle instances
        $.each(this.instanceArray, function(index, formSectionInstance) {
            //console.log('checking dependencies on ', formSectionInstance.id);
            formSectionInstance.checkChildrenDependencies();
        });
    },

    checkChildrenDependencies: function() {
        $.each(this.formComponents, function(formComponentKey, formComponent) {
            //console.log('checking dependencies on ', formComponent.id);
            formComponent.checkDependencies();
        });
    }
});