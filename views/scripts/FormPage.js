/**
 * formPage handles all functions on the page level, including page validation.
 *
 */
FormPage = Class.extend({
    initialize: function(form, pageId, options) {
        this.options = $.extend({
            dependencyOptions: null,
            onScrollTo: {
                onBefore: null,
                onAfter: null,
                notificationHtml: null
            },
            onScrollAway: {
                onBefore: null,
                onAfter: null,
                notificationHtml: null
            }
        }, options || {});
        
        // Setup the onScrollTo functions
        if(this.options.onScrollTo.onBefore !== null) {
            var onBeforeFunction = $.trim(this.options.onScrollTo.onBefore);
            this.options.onScrollTo.onBefore = function() {return eval(onBeforeFunction);};
        }
        if(this.options.onScrollTo.onAfter !== null) {
            var onAfterFunction = $.trim(this.options.onScrollTo.onAfter);
            this.options.onScrollTo.onAfter = function() {return eval(onAfterFunction);};
        }
        
        // Setup the onScrollAway functions
        if(this.options.onScrollAway.onBefore !== null) {
            var onBeforeFunction = $.trim(this.options.onScrollAway.onBefore);
            this.options.onScrollAway.onBefore = function(direction) {return eval(onBeforeFunction);};
        }
        if(this.options.onScrollAway.onAfter !== null) {
            var onAfterFunction = $.trim(this.options.onScrollAway.onAfter);
            this.options.onScrollAway.onAfter = function(direction) {return eval(onAfterFunction);};
        }

        // Class variables
        this.form = form;
        this.id = pageId;
        this.page = $('#'+pageId);
        this.formSections = {};
        this.formData = {};
        this.active = false;
        this.validationPassed = null;
        this.disabledByDependency = false;
        this.durationActiveInSeconds = 0;
    },
    
    getSiblingPage: function(direction) {
        var currentPageIndex = this.form.formPageIdArray.indexOf(this.id);
        
        var siblingPage = null;
        if(direction == 'next') {
            for(var i = (currentPageIndex + 1); i <= (this.form.formPageIdArray - 1); i++ ) {
                if(!this.form.formPages[this.form.formPageIdArray[i]].disabledByDependency){
                    siblingPage = this.form.formPages[this.form.formPageIdArray[i]];
                    i = (this.form.formPageIdArray - 1);
                }
            }
        }
        else {
            for(var k = (currentPageIndex - 1); k >= 0; k--) {
                if(!this.form.formPages[this.form.formPageIdArray[k]].disabledByDependency){
                    siblingPage = this.form.formPages[this.form.formPageIdArray[k]];
                    k = 0;
                }
            }
        }
        
        return siblingPage;
    },
    
    getPreviousPage: function() {
        return this.getSiblingPage('previous');
    },

    addSection: function(section) {
        this.formSections[section.id] = section;
        return this;
    },

    getData: function() {
        //console.log('getting data for page');
        var self = this;

        // Handle disabled pages
        if(this.disabledByDependency) {
            this.formData = null;
        }
        else {
            this.formData = {};
            $.each(this.formSections, function(formSectionKey, formSection) {
                self.formData[formSectionKey] = formSection.getData();
            });
        }

        return this.formData;
    },

    setData: function(data) {
        var self = this;
        $.each(data, function(key, values) {
            if(self.formSections[key] != undefined){
                self.formSections[key].setData(values);
            } else {
                data[key] = undefined;
            }
        });
        this.formData = data;
        return this.formData;
    },

    getTimeActive: function(){
        var currentActiveTime =(new Date().getTime() / 1000) -  this.startTime ;
        return currentActiveTime;
    },

    validate: function(silent) {
        //console.log('validating', this.id);
        // Handle dependencies
        if(this.disabledByDependency) {
            return null;
        }

        var self = this;
        var each = $.each;
        
        self.validationPassed = true;
        each(this.formSections, function(sectionKey, section) {
           each(section.instanceArray, function(instanceIndex, sectionInstance){
                each(sectionInstance.formComponents, function(componentKey, component) {
                    if(component.type == 'FormComponentLikert'){
                        return;
                    }
                    each(component.instanceArray, function(instanceIndex, instance) {
                        instance.validate();
                        if(instance.validationPassed == false) {
                            self.validationPassed = false;
                        }
                    });
                });
            });
        });

        if(self.validationPassed) {
            $('#navigatePage'+(self.form.currentFormPageIdArrayIndex + 1)).removeClass('formPageNavigatorLinkWarning');
        }
        else if(!silent) {
            if(this.id === this.form.currentFormPage.id){
                this.focusOnFirstFailedComponent();
            }
        }

        return self.validationPassed;
    },

    clearValidation: function() {
        $.each(this.formSections, function(sectionKey, section) {
            section.clearValidation();
        });
    },

    focusOnFirstFailedComponent: function() {
        var each = $.each,
        validationPassed = true;
        each(this.formSections, function(sectionLabel, section){
            each(section.instanceArray, function(sectionInstanceIndex, sectionInstance){
                each(sectionInstance.formComponents, function(componentLabel, component){
                    each(component.instanceArray, function(instanceLabel, instance){
                        if(!instance.validationPassed || instance.errorMessageArray.length > 0){
                            var offset = instance.component.offset().top - 30;
                            var top = $(window).scrollTop();
                            if(top < offset && top + $(window).height() > instance.component.position().top) {
                                instance.component.find(':input:first').focus();
                                //instance.highlight();
                            }
                            else {
                                $.scrollTo(offset + 'px', 500, {
                                    onAfter: function() {
                                        instance.component.find(':input:first').focus();
                                        //instance.highlight();
                                    }
                                });
                            }
                            validationPassed = false;
                        }
                        return validationPassed;
                    });
                    return validationPassed;
                });
                return validationPassed;
            });
            return validationPassed;
        });
    },

    scrollTo: function(options) {
        this.form.scrollToPage(this.id, options);
        return this;
    },

    show: function(){
        if(this.page.hasClass('formPageInactive')){
            this.page.removeClass('formPageInactive');
        }
    },

    hide:function() {
        if(!this.active){
            this.page.addClass('formPageInactive');
        }
    },

    disableByDependency: function(disable) {
        // If the condition is different then the current condition
        if(this.disabledByDependency !== disable) {
            var pageIndex = $.inArray(this.id, this.form.formPageIdArray);

            // Disable the page
            if(disable === true) {
                // Hide the page
                this.page.hide();

                // Update the page navigator appropriately
                if(this.form.options.pageNavigator !== false) {
                    if(this.options.pageNavigator && this.options.pageNavigator.hide && this.options.pageNavigator.hide == true) {
                        //console.log('Not showing this in page navigator: ', this.id);
                    }
                    else {
                        // Hide the page link
                        if(this.options.dependencyOptions.display == 'hide') {
                            $('#navigatePage'+(pageIndex+1)).hide();

                            // Renumber appropriately
                            this.form.renumberPageNavigator();
                        }
                        // Lock the page link
                        else {
                            $('#navigatePage'+(pageIndex+1)).addClass('formPageNavigatorLinkDependencyLocked').find('span').html('&nbsp;');
                        }
                    }

                }
            }
            // Show the page
            else {
                this.checkChildrenDependencies();
                this.page.show();

                // Update the page navigator appropriately
                if(this.form.options.pageNavigator !== false) {
                    if(this.options.pageNavigator && this.options.pageNavigator.hide && this.options.pageNavigator.hide == true) {
                        //console.log('Not showing this in page navigator: ', this.id);
                    }
                    else {
                        // Show the page link
                        if(this.options.dependencyOptions.display == 'hide') {
                            $('#navigatePage'+(pageIndex+1)).show();
                        }
                        // Unlock the page link
                        else {
                            $('#navigatePage'+(pageIndex+1)).removeClass('formPageNavigatorLinkDependencyLocked');
                        }
                    }
                    
                    // Renumber the existing links
                    this.form.renumberPageNavigator();
                    
                 }

             }

            this.disabledByDependency = disable;
            this.form.setupControl();
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
    },

    checkChildrenDependencies: function() {
        $.each(this.formSections, function(formSectionKey, formSection) {
            formSection.checkDependencies();
        });
    }
});