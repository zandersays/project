/**
 * form is the steward of the form. Holds base functions which are not specific to any page, section, or component.
 * form is initialized on top of the existing HTML and handles validation, tool tip management, dependencies, instances, triggers, pages, and form submission.
 *
 * @author Kirk Ouimet <kirk@kirkouimet.com>
 * @author Zander Seth Jensen <zander@sethzjensen.com>
 * @version .5
 */
Form = Class.extend({
    initialize: function (formId, options) {
        var self = this;

        // Keep track of when the form starts initializing (turns off at buttom of init)
        this.initializing = true;

        // Keep track of whether or not the page is scrolling
        this.scrollingPage = false;
        this.scrollPosition = 0;

        // Update the options object
        this.options = $.extend(true, {
            animationOptions: {
                pageScroll: {
                    duration: 375,
                    adjustHeightDuration: 375
                },
                instance: {
                    appearDuration: 0,
                    appearEffect: 'fade',
                    removeDuration: 0,
                    removeEffect: 'fade',
                    adjustHeightDuration: 0
                },
                dependency: {
                    appearDuration: 250,
                    appearEffect: 'fade',
                    hideDuration: 100,
                    hideEffect: 'fade',
                    adjustHeightDuration: 100
                },
                alert: {
                    appearDuration: 250,
                    appearEffect: 'fade',
                    hideDuration: 100,
                    hideEffect: 'fade'
                },
                modal: {
                    appearDuration: 0,
                    hideDuration: 0
                }
            },
            trackBind: false,
            disableAnalytics: false,
            setupPageScroller: true,
            validationTips: true,
            pageNavigator: false,
            startingPageId: false,
            saveState: false,
            splashPage: false,
            progressBar: false,
            alertsEnabled: true,
            clientSideValidation: true,
            debugMode: false,
            submitButtonText: 'Submit',
            nextButtonText: 'Next',
            previousButtonText: 'Previous',
            submitProcessingButtonText: 'Processing...',
            onSubmitStart: function () {
                return true;
            },
            onSubmitFinish: function () {
                return true;
            }
        }, options.options || {});

        // Class variables
        this.id = formId;
        this.form = $(['form#', this.id].join(''));
        this.formData = {};
        this.formPageWrapper = this.form.find('div.formPageWrapper');
        this.formPageScroller = this.form.find('div.formPageScroller');
        this.formPageNavigator = null;
        this.formPages = {};
        this.currentFormPage = null;
        this.maxFormPageIdArrayIndexReached = null;
        this.formPageIdArray = [];
        this.currentFormPageIdArrayIndex = null;
        this.blurredTips = [];
        this.lastEnabledPage = false;

        // Stats
        this.initializationTime = (new Date().getTime()) / 1000;
        this.durationInSeconds = 0;
        this.formComponentCount = 0;

        // Controls
        this.control = this.form.find('ul.formControl');
        this.controlNextLi = this.form.find('ul.formControl li.nextLi');
        this.controlNextButton = this.controlNextLi.find('button.nextButton');
        this.controlPreviousLi = this.form.find('ul.formControl li.previousLi');
        this.controlPreviousButton = this.controlPreviousLi.find('button.previousButton');

        // Initialize all of the pages
        this.initializePages(options.formPages);

        // Add a splash page if enabled
        if (this.options.splashPage !== false || this.options.saveState !== false) {
            if (this.options.splashPage === false) {
                this.options.splashPage = {};
            }
            this.addSplashPage();
        } else { // Set the current page
            var startingPageIdIndex = 0;
            if (this.options.startingPageId) {
                startingPageIdIndex = this.formPageIdArray.indexOf(this.options.startingPageId);
            }
            this.currentFormPageIdArrayIndex = startingPageIdIndex;
            this.maxFormPageIdArrayIndexReached = startingPageIdIndex;
            this.currentFormPage = this.formPages[this.formPageIdArray[startingPageIdIndex]];
            this.currentFormPage.active = true;
            this.currentFormPage.startTime = (new Date().getTime() / 1000);
           
            // Add the page navigator
            if (this.options.pageNavigator !== false) {
                this.addPageNavigator();
            }
        }

        // Setup the page scroller - mainly CSS changes to width and height
        if (this.options.setupPageScroller) {
            this.setupPageScroller();
        }

        // Hide all inactive pages
        this.hideInactivePages();

        // Setup the control buttons
        this.setupControl();

        // Add a submit button listener
        this.addSubmitListener();

        // Add enter key listener
        this.addEnterKeyListener();

        // The blur tip listener
        this.addBlurTipListener();

        // Check dependencies
        this.checkDependencies(true);

        // Run onScrollTo functions all the way to the starting page if it is set
        if (this.options.startingPageId) {
            $(document).ready(function () {
                // Get all of the previous pages
                var previousPage = self.currentFormPage;
                var previousPages = [previousPage];
                while (previousPage !== null) {
                    previousPage = previousPage.getPreviousPage();
                    if (previousPage !== null) {
                        previousPages.unshift(previousPage);
                    }
                }

                // Run the on before and on after scroll functions
                $.each(previousPages, function (previousPageIndex, previousPage) {
                    if (previousPage.options.onScrollTo) {
                        var onScrollToFunctions = previousPage.options.onScrollTo;
                        if (onScrollToFunctions.onBefore !== undefined && typeof onScrollToFunctions.onBefore === 'function') {
                            //console.log('Running ', previousPage.id, ' onBefore');
                            onScrollToFunctions.onBefore();
                        }
                        if (onScrollToFunctions.onAfter !== undefined &&  typeof onScrollToFunctions.onAfter === 'function') {
                            //console.log('Running ', previousPage.id, ' onAfter');
                            onScrollToFunctions.onAfter();
                        }
                    }
                    if (previousPage.options.onScrollAway) {
                        var onScrollAwayFunctions = previousPage.options.onScrollAway;
                        if (onScrollAwayFunctions.onBefore !== undefined && typeof onScrollAwayFunctions.onBefore === 'function') {
                            //console.log('Running ', previousPage.id, ' onBefore');
                            onScrollAwayFunctions.onBefore();
                        }
                        if (onScrollAwayFunctions.onAfter !== undefined &&  typeof onScrollAwayFunctions.onAfter === 'function') {
                            //console.log('Running ', previousPage.id, ' onAfter');
                            onScrollAwayFunctions.onAfter();
                        }
                    }
                });
            });
        } else { // run the on scroll to for the first page of the form "initialize the page"
            if (self.currentFormPage.options.onScrollTo) {
                if (self.currentFormPage.options.onScrollTo.onBefore !== undefined && typeof self.currentFormPage.options.onScrollTo.onBefore === 'function') {
                    //console.log('Running ', previousPage.id, ' onBefore');
                    self.currentFormPage.options.onScrollTo.onBefore();
                }
                if (self.currentFormPage.options.onScrollTo.onAfter !== undefined &&  typeof self.currentFormPage.options.onScrollTo.onAfter === 'function') {
                    //console.log('Running ', previousPage.id, ' onAfter');
                    self.currentFormPage.options.onScrollTo.onAfter();
                }
            }
        }

        // Analytics
        //this.recordAnalytics();

        //make sure that the page that we need to be visible is in the view port
        var scrollLeftIndex = 0;
        // grab the index of the page we need
        self.formPageScroller.find('.formPage:visible').each(function (index, page) {
            if (!$(page).hasClass('formPageInactive')) {
                scrollLeftIndex = index;
            }
        });
        // find its left page edge via index and wrapper width and then scroll to it
        this.scrollPosition = self.formPageWrapper.width() * scrollLeftIndex;
        self.formPageWrapper.scrollLeft(this.scrollPosition);
        // run any onScrollTo Functions for the page

        // Record when the form is finished initializing
        this.initializing = false;

        //prevent scrolling when you aren't supposed to be scrolling
        $(self.formPageWrapper).scroll(function (event) {
            if (!self.scrollingPage) {
                event.preventDefault();
                self.formPageWrapper.scrollLeft(self.scrollPosition);
                return false;
            }
        });

        //handle back forward buttons for form navigation
        if ($.address) {
            $.address.externalChange(function (event) {
                var pageId  = event.value.replace(/\//, ''),
                    scrollPageId = self.formPageIdArray[0];
                if (self.formPageIdArray.indexOf(pageId) !== -1 && !self.formPages[pageId].disabledByDependency) {
                    scrollPageId = pageId;
                } else if (self.options.startingPageId) {
                    scrollPageId = self.options.startingPageId;
                }
                if (self.formPageIdArray.indexOf(scrollPageId) !== self.currentFormPageIdArrayIndex) {

                    self.currentFormPageIdArrayIndex = self.formPageIdArray.indexOf(scrollPageId);
                    self.scrollToPage(scrollPageId);
                }
                if (self.formPages[pageId] && self.formPages[pageId].disabledByDependency) {
                    $.address.value('');
                }
            });
        }
        // Instantly adjust the height of the form after the window is loaded      
        $(window).load(function () {
            //console.log('Adjusting height!', self.id);
            self.adjustHeight({
                adjustHeightDuration: 0
            });
        });
    },

    initializePages: function (formPages) {
        var self = this,
            each = $.each,
            dependencies = {};

        each(formPages, function (formPageKey, formPageValue) {
            var formPage = new FormPage(self, formPageKey, formPageValue.options);
            formPage.show();

            // Handle page level dependencies - gather all of the dependencies in the dependencies variable
            if (formPage.options.dependencyOptions !== null) {
                $.each(formPage.options.dependencyOptions.dependentOn, function (index, componentId) {
                    if (dependencies[componentId] === undefined) {
                        dependencies[componentId] = {
                            pages: [],
                            sections: [],
                            components: []
                        };
                    }
                    dependencies[componentId].pages.push({
                        formPageId : formPageKey
                    });
                });
            }

            each(formPageValue.formSections, function (formSectionKey, formSectionValue) {
                var formSection = new FormSection(formPage, formSectionKey, formSectionValue.options);

                // Handle section level dependencies
                if (formSection.options.dependencyOptions !== null) {
                    $.each(formSection.options.dependencyOptions.dependentOn, function (index, componentId) {
                        if (dependencies[componentId] === undefined) {
                            dependencies[componentId] = {
                                pages: [],
                                sections: [],
                                components: []
                            };
                        }
                        dependencies[componentId].sections.push({
                            formPageId: formPageKey,
                            formSectionId: formSectionKey
                        });
                    });
                }

                each(formSectionValue.formComponents, function (formComponentKey, formComponentValue) {
                    self.formComponentCount = self.formComponentCount + 1;
                    var formComponent = new window[formComponentValue.type](formSection, formComponentKey, formComponentValue.type, formComponentValue.options);

                    // Check if there are pregenerated instances and add them
                    formComponent.addInitialInstances();

                    formSection.addComponent(formComponent);

                    // Handle component level dependencies
                    if (formComponent.options.dependencyOptions !== null) {
                        $.each(formComponent.options.dependencyOptions.dependentOn, function (index, componentId) {
                            if (dependencies[componentId] === undefined) {
                                dependencies[componentId] = {
                                    pages: [],
                                    sections: [],
                                    components: []
                                };
                            }
                            dependencies[componentId].components.push({
                                formPageId: formPageKey,
                                formSectionId: formSectionKey,
                                formComponentId: formComponentKey
                            });
                        });
                    }
                });
                if (formSection.options.isInstance) { //formPage.formSections[formSection.id] 
                }
                // Check if there are pregenerated instances and add them
                formSection.addInitialSectionInstances();
                // Add the section to the page
                formPage.addSection(formSection);
            });
            self.addFormPage(formPage);
        });

        // Add listeners for all of the components that are being dependent on
        // We group the component event listeners to prevent them from constantly being called
        $.each(dependencies, function (componentId, dependentTypes) { 
            $('#' + componentId + ':text, textarea#' + componentId).bind('keyup', function (event) {
                $.each(dependentTypes.pages, function (index, object) {
                    self.formPages[object.formPageId].checkDependencies();
                });
                $.each(dependentTypes.sections, function (index, object) {
                    self.formPages[object.formPageId].formSections[object.formSectionId].checkDependencies();
                });
                $.each(dependentTypes.components, function (index, object) {
                    self.formPages[object.formPageId].formSections[object.formSectionId].formComponents[object.formComponentId].checkDependencies();
                });
            });

            $('#' + componentId + '-wrapper').bind('formComponent:changed', function (event) {
                //console.log('running depend check');

                $.each(dependentTypes.pages, function (index, object) {
                    self.formPages[object.formPageId].checkDependencies();
                });
                $.each(dependentTypes.sections, function (index, object) {
                    self.formPages[object.formPageId].formSections[object.formSectionId].checkDependencies();
                });
                $.each(dependentTypes.components, function (index, object) {
                    //console.log('running a check', componentId, 'for', object.formComponentId);
                    self.formPages[object.formPageId].formSections[object.formSectionId].formComponents[object.formComponentId].checkDependencies();
                });
            });

            // Handle instances (this is super kludgy)
            var component = self.select(componentId);
            //console.log(component);
            if (component !== null && component.options.instanceOptions !== null) {
                component.options.dependencies = dependentTypes;
            }
        });
    },

    select: function (formComponentId) {
        var componentFound = false,
            component = null;
        $.each(this.formPages, function (formPageKey, formPage) {
            $.each(formPage.formSections, function (sectionKey, sectionObject) {
                $.each(sectionObject.formComponents, function (componentKey, componentObject) {
                    if (componentObject.id === formComponentId) {
                        component = componentObject;
                        componentFound = true;
                    }
                    return !componentFound;
                });
                return !componentFound;
            });
            return !componentFound;
        });
        return component;
    },

    checkDependencies: function (onInit) {
        $.each(this.formPages, function (formPageKey, formPage) {
            formPage.checkDependencies();

            $.each(formPage.formSections, function (formSectionKey, formSection) {
                formSection.checkDependencies();

                $.each(formSection.formComponents, function (formComponentKey, formComponent) {
                    formComponent.checkDependencies();
                });
            });
        });
    },

    addSplashPage: function () {
        var self = this;

        // Setup the formPage for the splash page
        // Setup default page options for the splash page
        if (!this.options.splashPage.options) {
            this.options.splashPage.options = {};
        }
        this.options.splashPage.formPage = new FormPage(this, this.form.find('div.formSplashPage').attr('id'), this.options.splashPage.options);
        this.options.splashPage.formPage.addSection(new FormSection(this.options.splashPage.formPage, this.form.find('div.formSplashPage').attr('id') + '-section'));
        this.options.splashPage.formPage.page.width(this.form.width());
        this.options.splashPage.formPage.active = true;
        this.options.splashPage.formPage.startTime = (new Date().getTime() / 1000);

        // Set the splash page as the current page
        this.currentFormPage = this.options.splashPage.formPage;

        // Set the height of the page wrapper to the height of the splash page
        this.formPageWrapper.height(this.options.splashPage.formPage.page.outerHeight());

        // If they have a custom button
        if (this.options.splashPage.customButtonId) {
            this.options.splashPage.controlSplashLi = this.form.find('#' + this.options.splashPage.customButtonId);
            this.options.splashPage.controlSplashButton = this.form.find('#' + this.options.splashPage.customButtonId);
        } else {         // Use the native control buttons
            this.options.splashPage.controlSplashLi = this.form.find('li.splashLi');
            this.options.splashPage.controlSplashButton = this.form.find('button.splashButton');
        }

        // Hide the other native controls
        this.setupControl();

        // Handle save state options on the splash page
        if (this.options.saveState !== false) {
            self.addSaveStateToSplashPage();
        } else { // If there is no save state, just setup the button to start the form
            this.options.splashPage.controlSplashButton.bind('click', function (event) {
                event.preventDefault();
                self.beginFormFromSplashPage(false);
            });
        }
    },

    beginFormFromSplashPage: function (initSaveState, loadForm) {
        var self = this;

        // Add the page navigator
        if (this.options.pageNavigator !== false && this.formPageNavigator == null) {
            this.addPageNavigator();
            this.formPageNavigator.show();
        } else if (this.options.pageNavigator !== false) {
            this.formPageNavigator.show();
        }

        // Find all of the pages
        var pages = this.form.find('.formPage');

        // Set the width of each page
        pages.css('width', this.form.find('.formWrapperContainer').width());

        // Mark the splash page as inactive
        self.options.splashPage.formPage.active = false;

        if (!loadForm) {
            // Set the current page index
            self.currentFormPageIdArrayIndex = 0;

            // Scroll to the new page, hide the old page when it is finished
            self.formPages[self.formPageIdArray[0]].scrollTo({
                onAfter: function () {
                    self.options.splashPage.formPage.hide();
                    self.renumberPageNavigator();
                }
            });
        }

        // Initialize the save state is set
        if (initSaveState) {
            self.initSaveState();
        }
    },

    addPageNavigator: function () {
        var self = this;

        this.formPageNavigator = this.form.find('.formPageNavigator');

        this.formPageNavigator.find('.formPageNavigatorLink:first').click(function (event) {
            // Don't scroll to the page if you already on it
            if (self.currentFormPageIdArrayIndex !== 0) {
                self.currentFormPageIdArrayIndex = 0;

                self.scrollToPage(self.formPageIdArray[0], { });
            }
        });

        // Update the style is right aligned
        if (this.options.pageNavigator.position == 'right') {
            this.form.find('.formWrapperContainer').width(this.form.width() - this.formPageNavigator.width() - 30);
        }
    },

    updatePageNavigator: function () {
        var self = this, pageCount, pageIndex;
        for (var i = 1; i <= this.maxFormPageIdArrayIndexReached + 1; i++) {
            pageCount = i;
            var formPageNavigatorLink = $('#navigatePage'+pageCount);

            // Remove the active class from the page you aren't on
            if(this.currentFormPageIdArrayIndex != pageCount - 1) {
                formPageNavigatorLink.removeClass('formPageNavigatorLinkActive');
            }
            // Add the active class to the page you are on
            else {
                formPageNavigatorLink.addClass('formPageNavigatorLinkActive');
            }

            // If the page is currently locked
            if(formPageNavigatorLink.hasClass('formPageNavigatorLinkLocked')){
                // Remove the lock
                formPageNavigatorLink.removeClass('formPageNavigatorLinkLocked').addClass('formPageNavigatorLinkUnlocked');

                formPageNavigatorLink.click(function(event) {
                    var target = $(event.target);
                    if(!target.is('li')){
                        target = target.closest('li');
                    }

                    pageIndex = target.attr('id').match(/[0-9]+$/)
                    pageIndex = parseInt(pageIndex) - 1;

                    // Perform a silent validation on the page you are leaving
                    self.getActivePage().validate(true);

                    // Don't scroll to the page if you already on it
                    if(self.currentFormPageIdArrayIndex != pageIndex) {
                        self.scrollToPage(self.formPageIdArray[pageIndex]);
                    }

                    self.currentFormPageIdArrayIndex = pageIndex;

                });
            }
        }
    },

renumberPageNavigator: function() {
    $('.formPageNavigatorLink:visible').each(function(index, element) {
        // Renumber page link icons
        if($(element).find('span').length > 0) {
            $(element).find('span').html(index+1);
        }
        // Relabel pages that have no title or icons
        else {
            $(element).html('Page '+(index+1));
        }
    });
},
    
addFormPage: function(formPage) {
    this.formPageIdArray.push(formPage.id);
    this.formPages[formPage.id] = formPage;
},

removeFormPage: function(formPageId) {
    var self = this;

    // Remove the HTML
    $('#'+formPageId).remove();

    this.formPageIdArray = $.grep(self.formPageIdArray, function(value) {
        return value != formPageId;
    });
    delete this.formPages[formPageId];
},

addEnterKeyListener: function() {
    var self = this;

    // Prevent the default submission on key down
    this.form.bind('keydown', {
        context:this
    }, function(event) {
        if(event.keyCode === 13 || event.charCode === 13) {
            if($(event.target).is('textarea')){
                return;
            }
            event.preventDefault();
        }
    });

    this.form.bind('keyup', {
        context:this
    }, function(event) {
        // Get the current page, check to see if you are on the splash page
        var currentPage = self.getActivePage().page;

        // Listen for the enter key keycode
        if(event.keyCode === 13 || event.charCode === 13) {
            var target = $(event.target);
            // Do nothing if you are on a text area
            if(target.is('textarea')){
                return;
            }

            // If you are on a button, press it
            if (target.is('button')) {
                event.preventDefault();
                target.trigger('click').blur();
            }
            // If you are on a field where pressing enter submits
            else if (target.is('.formComponentEnterSubmits')){
                event.preventDefault();
                target.blur();
                self.controlNextButton.trigger('click');
            }
            // If you are on an input that is a check box or radio button, select it
            else if (target.is('input:checkbox')) {
                event.preventDefault();
                target.trigger('click');
            }
            // If you are the last input and you are a password input, submit the form
            else if (target.is('input:password')) {
                event.preventDefault();
                target.blur();

                // Handle if you are on the splash page
                if (self.options.splashPage !== false && self.currentFormPage.id == self.options.splashPage.formPage.id) {
                    self.options.splashPage.controlSplashButton.trigger('click');
                }
                else {
                    self.controlNextButton.trigger('click');
                }
            }

        }
    });
},

addSubmitListener: function () {
    var self = this;
    this.form.bind('submit', {
        context: this
    }, function(event) {
        event.preventDefault();
        self.submitEvent(event);
    });
},

initSaveState: function () {
    var self = this, interval = this.options.saveState.interval * 1000;
    if(this.options.saveState === null){
        return;
    }
    this.saveIntervalSetTimeoutId = setInterval(function(){
        self.saveState(self.options.saveState.showSavingAlert);
    }, interval);
    this.saveStateInitialized = true;
    return;
},
    
getData: function () {
    var self = this;
    this.formData = {};
    $.each(this.formPages, function(formKey, formPage) {
        self.formData[formKey] = formPage.getData();
    });
    return this.formData;
},

setData: function (data) {
    var self = this;
    this.formData = data;
    $.each(data, function(key, page) {
        if(self.formPages[key] != undefined){
            self.formPages[key].setData(page);
        }
    });
    return this.formData;
},

setupPageScroller: function (options) {
    var self = this;

    // Set some default values for the options
    var defaultOptions = {
        adjustHeightDuration: 0,
        formWrapperContainerWidth : self.form.find('.formWrapperContainer').width(),
        formPageWrapperWidth : self.formPageWrapper.width(),
        activePageOuterHeight : self.getActivePage().page.outerHeight(),
        scrollToPage: true
    };
    options = $.extend(defaultOptions, options);
        
    // Find all of the pages
    var pages = this.form.find('.formPage');

    // Count the total number of pages
    var pageCount = pages.length;

    // Don't set width's if they are 0 (the form is hidden)
    if(options.formWrapperContainerWidth != 0) {
        // Set the width of each page
        pages.css('width', options.formWrapperContainerWidth)
    }
    pages.show();

    // Don't set width's if they are 0 (the form is hidden)
    if(options.formWrapperContainerWidth != 0) {
        // Set the width of the scroller
        self.formPageScroller.css('width', options.formPageWrapperWidth * (pageCount));
        self.formPageWrapper.parent().css('width', options.formPageWrapperWidth);
    }
        
    // Don't set height if it is 0 (the form is hidden)
    if(options.activePageOuterHeight != 0) {
        // Set the height of the wrapper
        self.formPageWrapper.height(options.activePageOuterHeight);
    }

// Scroll to the current page (prevent weird Firefox bug where the page does not display on soft refresh
//if(options.scrollToPage) { 
//self.scrollToPage(self.currentFormPage.id, options);
//}
},

setupControl: function() {
    //console.log('setting up control');

    var self = this;
    // console.log(this.currentFormPageIdArrayIndex);
    // Setup event listener for next button
    this.controlNextButton.unbind().click(function(event) {
        event.preventDefault();
        event['context'] = self;
        self.submitEvent(event);
    }).removeAttr('disabled');

    //check to see if this is the last enabled page.
    this.lastEnabledPage = false;
    for(i = this.formPageIdArray.length - 1 ; i > this.currentFormPageIdArrayIndex; i--){
        if(!this.formPages[this.formPageIdArray[i]].disabledByDependency){
            this.lastEnabledPage = false;
            break;
        }
        this.lastEnabledPage = true;
    }

    // Setup event listener for previous button
    this.controlPreviousButton.unbind().click(function(event) {
        event.preventDefault();

        // Be able to return to the splash page
        if(self.options.splashPage !== false && self.currentFormPageIdArrayIndex === 0) {
            self.currentFormPageIdArrayIndex = null;
            if(self.formPageNavigator){
                self.formPageNavigator.hide();
            }
            self.options.splashPage.formPage.scrollTo();
        }
        // Scroll to the previous page
        else {
            if(self.formPages[self.formPageIdArray[self.currentFormPageIdArrayIndex - 1]].disabledByDependency){
                for(var i = 1; i <= self.currentFormPageIdArrayIndex; i++){
                    if(self.currentFormPageIdArrayIndex - i == 0 && self.options.splashPage !== false && self.formPages[self.formPageIdArray[self.currentFormPageIdArrayIndex -i]].disabledByDependency ){
                        if(self.formPageNavigator){
                            self.formPageNavigator.hide();
                        }
                        self.options.splashPage.formPage.scrollTo();
                        break;
                    }
                    else if(!self.formPages[self.formPageIdArray[self.currentFormPageIdArrayIndex - i]].disabledByDependency){
                        self.currentFormPageIdArrayIndex = self.currentFormPageIdArrayIndex - i;
                        break;
                    }
                }
            } else {
                self.currentFormPageIdArrayIndex = self.currentFormPageIdArrayIndex - 1;
            }
            self.scrollToPage(self.formPageIdArray[self.currentFormPageIdArrayIndex]);
        }
    });
       
    // First page with more pages after, or splash page
    if(this.currentFormPageIdArrayIndex === 0 && this.currentFormPageIdArrayIndex != this.formPageIdArray.length - 1 && this.lastEnabledPage === false) {
        this.controlNextButton.html(this.options.nextButtonText);
        this.controlNextLi.show();
        this.controlPreviousLi.hide();
        this.controlPreviousButton.html(this.options.previousButtonText);
        this.controlPreviousButton.attr('disabled', 'disabled');
    }
    // Last page
    else if(self.currentFormPageIdArrayIndex == this.formPageIdArray.length - 1 || this.lastEnabledPage === true) {
        this.controlNextButton.html(this.options.submitButtonText);
        this.controlNextLi.show();

        // First page is the last page
        if(self.currentFormPageIdArrayIndex === 0 ) {
            // Hide the previous button
            this.controlPreviousLi.hide();
            this.controlPreviousButton.attr('disabled', '');
        }
        // There is a previous page
        else if(self.currentFormPageIdArrayIndex > 0) {
            this.controlPreviousButton.removeAttr('disabled');
            this.controlPreviousLi.show();
        }
    }
    // Middle page with a previous and a next
    else { 
        this.controlNextButton.html('Next');
        this.controlNextLi.show();
        this.controlPreviousButton.removeAttr('disabled');
        this.controlPreviousLi.show();
    }

    // Splash page
    if(this.options.splashPage !== false) {
        // If you are on the splash page
        if(this.options.splashPage.formPage.active) {
            this.options.splashPage.controlSplashLi.show();
            this.controlNextLi.hide();
            this.controlPreviousLi.hide();
            this.controlPreviousButton.attr('disabled', 'disabled');
        }
        // If you aren't on the splash page, don't show the splash button
        else {
            this.options.splashPage.controlSplashLi.hide();
        }

        // If you are on the first page
        if(this.currentFormPageIdArrayIndex === 0  && this.options.saveState == false) {
            this.controlPreviousButton.removeAttr('disabled');
            this.controlPreviousLi.show();
        }
    }

    // Failure page
    if(this.control.find('.startOver').length == 1){
        // Hide the other buttons
        this.controlNextLi.hide();
        this.controlPreviousLi.hide();

        // Bind an event listener to the start over button
        this.control.find('.startOver').one('click', function(event){
            event.preventDefault();
            self.scrollToPage(self.formPageIdArray[0], {
                onAfter: function(){
                    // Remove the start over button
                    $(event.target).parent().remove();
                    self.removeFormPage(self.id+'formPageFailure');
                }
            });
        });
    }
},
    
nextPage: function() {
    this.form.find('.nextButton').click();
},
    
previousPage: function() {
    this.form.find('.previousButton').click();
},
        
scrollToPage: function(formPageId, options) {
    //console.log('Form('+this.id+'):scrollToPage', formPageId, 'from ', this.currentFormPage.id, options);
    var self = this;
    // Remember the active duration time of the page
        

    // Prevent scrolling to dependency disabled pages
    if(this.formPages[formPageId] && this.formPages[formPageId].disabledByDependency) {
        return false;
    }
        
    var currentFormPage = this.getActivePage();
        
    // Handle onScrollAway onBefore
    //console.log(this.formPageIdArray.indexOf(this.currentFormPage.id), this.currentFormPageIdArrayIndex);
    var direction = this.formPageIdArray.indexOf(formPageId) < this.formPageIdArray.indexOf(currentFormPage.id) ? 'backwards' : 'forwards';
    if(currentFormPage && currentFormPage.options.onScrollAway.onBefore !== null && currentFormPage.options.onScrollAway.onBefore !== undefined) {
            
        // Put a notice up if defined
        if(currentFormPage.options.onScrollAway.notificationHtml !== undefined) {
            if(self.control.find('.formScrollToNotification').length != 0) {
                self.control.find('.formScrollToNotification').html(currentFormPage.options.onScrollAway.notificationHtml);
            }
            else {
                self.control.append('<li class="formScrollToNotification">'+currentFormPage.options.onScrollAway.notificationHtml+'<li>');
            }   
        }
        var onScrollAwayOnBefore = currentFormPage.options.onScrollAway.onBefore(direction, formPageId);
        //console.log(onScrollAwayOnBefore);
        self.control.find('.formScrollToNotification').remove();
            
        // Don't move to the next page if the function returns false
        if(!onScrollAwayOnBefore) {
            // set the correct current page index
            this.currentFormPageIdArrayIndex  = this.formPageIdArray.indexOf(currentFormPage.id);
            return false;
        }
    }
        
    // Indicate the form is scrolling the page
    self.scrollingPage = true;

    // Disable buttons
    this.controlNextButton.attr('disabled', true);
    this.controlPreviousButton.attr('disabled', true);

    // Handle page specific onScrollTo onBefore custom function
    var formPage = null;
    if(self.options.splashPage !== false && formPageId == self.options.splashPage.formPage.id) {
        formPage = self.options.splashPage.formPage;
    }
    else {
        formPage = this.formPages[formPageId];
    }

    // Handle the onScrollTo onBefore for the page we are going to
    if(formPage && formPage.options.onScrollTo.onBefore !== null) {
        // put a notice up if defined
        if(formPage.options.onScrollTo.notificationHtml !== undefined) {
            if(self.control.find('.formScrollToNotification').length != 0 ){
                self.control.find('.formScrollToNotification').html(formPage.options.onScrollTo.notificationHtml);
            }
            else {
                self.control.append('<li class="formScrollToNotification">'+formPage.options.onScrollTo.notificationHtml+'<li>');
            }
                
        }
        formPage.options.onScrollTo.onBefore();
    }
        
        
    currentFormPage.durationActiveInSeconds = currentFormPage.durationActiveInSeconds + currentFormPage.getTimeActive();

    // Show every page so you can see them as you scroll through
    $.each(this.formPages, function(formPageKey, formPage) {
        formPage.show();
        formPage.active = false;
    });

    // If on the splash page, set the current page to the splash page
    if(self.options.splashPage !== false && formPageId == self.options.splashPage.formPage.id) {
        self.currentFormPage = self.options.splashPage.formPage;
        self.currentFormPage.show();
    }
    // Set the current page to the new page
    else {
        this.currentFormPage = this.formPages[formPageId];
    }

    // Mark the current page as active
    this.currentFormPage.active = true;

    // Adjust the height of the page wrapper
    // If there is a custom adjust height duration
    if(options && options.adjustHeightDuration !== undefined) {
        self.adjustHeight({
            adjustHeightDuration: options.adjustHeightDuration
            });
    }
    else {
        self.adjustHeight();
    }

    // Run the next animation immediately
    this.formPageWrapper.dequeue();

    // Scroll the document the top of the form
    this.scrollToTop();
        
    // PageWrapper is like a viewport - this scrolls to the top of the new page, but the document needs to be scrolled too
    var initializing = this.initializing;
    this.formPageWrapper.scrollTo(
        self.currentFormPage.page,
        self.options.animationOptions.pageScroll.duration,
        {
            onAfter: function() {
                // Indicate the form is has stopped scrolling the page
                self.scrollingPage = false;
                    
                    
                    
                self.scrollPosition = self.formPageWrapper.scrollLeft();
                // Don't hide any pages while scrolling
                if($(self.formPageWrapper).queue('fx').length <= 1 ) {
                    self.hideInactivePages(self.getActivePage());
                }

                // Set the max page reach indexed
                if(self.maxFormPageIdArrayIndexReached < self.currentFormPageIdArrayIndex) {
                    self.maxFormPageIdArrayIndexReached = self.currentFormPageIdArrayIndex;
                }

                // Update the page navigator
                self.updatePageNavigator();

                // Start the time for the new page
                self.currentFormPage.startTime = (new Date().getTime()/1000);

                // Run any special functions
                if(options && options.onAfter) {
                    options.onAfter();
                }

                // Run any specific page functions
                //console.log(self.currentFormPage);
                // which one do we need to run?
                if(self.currentFormPage.options.onScrollTo.onAfter) {
                //self.currentFormPage.options.onScrollTo.onAfter();
                }
                    
                // Set hash for history storage
                if($.address && self.currentFormPageIdArrayIndex !== 0){
                    $.address.value(self.currentFormPage.id);    
                }
                else if($.address) {
                    $.address.value('');
                }                    

                // Setup the controls
                self.setupControl();

                // Enable the buttons again
                self.controlNextButton.removeAttr('disabled').blur();
                self.controlPreviousButton.removeAttr('disabled').blur();

                // Focus on the first failed component, if it is failed,
                if(self.currentFormPage.validationPassed === false && !initializing){
                    self.currentFormPage.focusOnFirstFailedComponent();
                }
                    
                // Handle page specific onScrollAway onAfter custom function
                if(currentFormPage && currentFormPage.options.onScrollAway.onAfter !== null && currentFormPage.options.onScrollAway.onAfter !== undefined) {
                    currentFormPage.options.onScrollAway.onAfter(direction, formPageId);
                    if(currentFormPage.options.onScrollAway.notificationHtml !== null) {
                        self.control.find('li.formScrollToNotification').remove();
                    }
                }

                // Handle page specific onScrollTo onAfter custom function
                if(self.formPages[formPageId] && self.formPages[formPageId].options.onScrollTo.onAfter !== null && self.formPages[formPageId].options.onScrollTo.onAfter !== undefined) {
                    self.formPages[formPageId].options.onScrollTo.onAfter();
                    if(self.formPages[formPageId].options.onScrollTo.notificationHtml !== null) {
                        self.control.find('li.formScrollToNotification').remove();
                    }
                }
            }
        }
        );

    return this;
},

scrollToTop: function() {
    if(this.initializing) {
        return;
    }

    var self = this;
    // Only scroll if the top of the form is not visible
    if($(window).scrollTop() > this.form.offset().top) {
        $(document).scrollTo(self.form, self.options.animationOptions.pageScroll.duration, {
            offset: {
                top: -10
            }
        });
    }
},

getActivePage: function() {
    // if active page has not been set
    return this.currentFormPage;
},

getTimeActive: function(){
    var currentTotal = 0;
    $.each(this.formPages, function(key, page){
        currentTotal = currentTotal + page.durationActiveInSeconds;
    });
    currentTotal = currentTotal + this.getActivePage().getTimeActive();
    return currentTotal;
},

hideInactivePages: function(){
    $.each(this.formPages, function(formPageKey, formPage){
        formPage.hide();
    });
},

clearValidation: function() {
    $.each(this.formPages, function(formPageKey, formPage){
        formPage.clearValidation();
    });
},

submitEvent: function(event) {
    var self = this;
    //console.log('last enabled page', self.lastEnabledPage);
    // Stop the event no matter what
    event.stopPropagation();
    event.preventDefault();

    // Remove any failure notices
    self.control.find('.formFailureNotice').remove();
    self.form.find('.formFailure').remove();

    // Run a custom function at beginning of the form submission
    var onSubmitStartResult;
    if(typeof(self.options.onSubmitStart) != 'function') {
        onSubmitStartResult = eval(self.options.onSubmitStart);
    }
    else {
        onSubmitStartResult = self.options.onSubmitStart();
    }

    // Validate the current page if you are not the last page
    var clientSideValidationPassed = false;
    if(this.options.clientSideValidation) {
        if(self.currentFormPageIdArrayIndex < self.formPageIdArray.length - 1 && !self.lastEnabledPage) {
            //console.log('Validating single page.');
            clientSideValidationPassed = self.getActivePage().validate();
        }
        else {
            //console.log('Validating whole form.');
            clientSideValidationPassed = self.validateAll();
        }
    }
    // Ignore client side validation
    else {
        this.clearValidation();
        clientSideValidationPassed = true;
    }

    // Run any custom functions at the end of the validation
    var onSubmitFinishResult = eval(self.options.onSubmitFinish);

    // If the custom finish function returns false, do not submit the form
    if(onSubmitFinishResult) {
        // The user is on the last page, submit the form
        //console.log(clientSideValidationPassed && (self.currentFormPageIdArrayIndex == self.formPageIdArray.length - 1) || (self.lastEnabledPage === true ));
        if(clientSideValidationPassed && (self.currentFormPageIdArrayIndex == self.formPageIdArray.length - 1) || (self.lastEnabledPage === true )) {
            self.submitForm(event);
        }
        // The user is not on the last page, so scroll to the next one
        else if(clientSideValidationPassed && self.currentFormPageIdArrayIndex < self.formPageIdArray.length - 1) {
            // If the next page is disabled by dependency, loop through till you find a good page.
            if(self.formPages[self.formPageIdArray[self.currentFormPageIdArrayIndex + 1]].disabledByDependency === true) {
                for(var i = self.currentFormPageIdArrayIndex + 1; i <= self.formPageIdArray.length - 1; i++){
                    //console.log('formPageIdArray Index:', self.formPageIdArray[i]);
                    // page is enabled, set the proper index, and break out of the loop.
                    if(!self.formPages[self.formPageIdArray[i]].disabledByDependency) {
                        //console.log(self.formPageIdArray[i], ' is not disabled, moving to it.');
                        self.currentFormPageIdArrayIndex = i;
                        break;
                    }
                }
            }
            // If the next page is not disabled, just move to it
            else {
                self.currentFormPageIdArrayIndex = self.currentFormPageIdArrayIndex + 1;
            }
                
            // Scroll to the new page
            self.scrollToPage(self.formPageIdArray[self.currentFormPageIdArrayIndex]);
        }
    }
},

validateAll: function(){
    var self = this;
    var validationPassed = true;
    var index = 0;
    $.each(this.formPages, function(formPageKey, formPage) {
            
        var passed = formPage.validate();
        //console.log(formPage.id, 'passed', passed);
        if(passed === false) {
            self.currentFormPageIdArrayIndex = index;
            if(self.currentFormPage.id != formPage.id) {
                formPage.scrollTo();
            }
            validationPassed = false;
            return false; // Break out of the .each
        }
        index++;
    });
    return validationPassed;
},

adjustHeight: function(options) {
    //console.log('form:adjustHeight', options)

    var self = this;
    var duration = this.options.animationOptions.pageScroll.adjustHeightDuration;

    // Use custom one time duration settings
    if(this.initializing){
        duration = 0;
    }
    else if(options && options.adjustHeightDuration !== undefined) {
        duration = options.adjustHeightDuration;
    }

    if(!this.initializing) {
        this.formPageWrapper.animate({
            'height' : self.getActivePage().page.outerHeight()
        }, duration);
    }
},

submitForm: function(event) {
    var self = this;

    // Use a temporary form targeted to the iframe to submit the results
    var formClone = this.form.clone(false);
    formClone.attr('id', formClone.attr('id')+'-clone');
    formClone.attr('style', 'display: none;');
    formClone.empty();
    formClone.appendTo($(this.form).parent());
    // Wrap all of the form responses into an object based on the component formComponentType
    var formView = $('<input type="hidden" name="view" value="'+$('#'+this.id+'-view').val()+'" />');
    formClone.append(formView);
    var formViewData = $('<input type="hidden" name="viewData" value="'+$('#'+this.id+'-viewData').val()+'" />');
    formClone.append(formViewData);
    var formData = $('<input type="hidden" name="formData" />').attr('value', encodeURI(Json.encode(this.getData()))); // Set all non-file values in one form object
    formClone.append(formData);
        
    // Add any file components for submission
    this.form.find('input:file').not('.ajaxHandled').each(function(index, fileInput) {
        if($(fileInput).val() != '') {
            // grab the IDs needed to pass
            var sectionId = $(fileInput).closest('.formSection').attr('id');
            var pageId = $(fileInput).closest('.formPage').attr('id');
            //var clone = $(fileInput).clone()

            // do find out the section instance index
            if($(fileInput).attr('id').match(/-section[0-9]+/)){
                var sectionInstance = null;
                var section = $(fileInput).closest('.formSection');
                // grab the base id of the section to find all sister sections
                var sectionBaseId = section.attr('id').replace(/-section[0-9]+/, '') ;
                sectionId = sectionId.replace(/-section[0-9]+/, '');
                // Find out which instance it is
                section.closest('.formPage').find('div[id*='+sectionBaseId+']').each(function(index, fileSection){
                    if(section.attr('id') == $(fileSection).attr('id')){
                        sectionInstance = index + 1;
                        return false;
                    }
                    return true;
                });
                fileInput.attr('name', fileInput.attr('name').replace(/-section[0-9]+/, '-section'+sectionInstance));
            }

            // do find out the component instance index
            if($(fileInput).attr('id').match(/-instance[0-9]+/)){
                // grab the base id of the component to find all sister components
                var baseId = $(fileInput).attr('id').replace(/-instance[0-9]+/, '')
                var instance = null;
                // Find out which instance it is
                $(fileInput).closest('.formSection').find('input[id*='+baseId+']').each(function(index, fileComponent){
                    if($(fileComponent).attr('id') == $(fileInput).attr('id')){
                        instance = index + 1;
                        return false;
                    }
                    return true;
                });
                fileInput.attr('name', fileInput.attr('name').replace(/-instance[0-9]+/, '-instance'+instance));
            }

            $(fileInput).attr('name', $(fileInput).attr('name')+':'+pageId+':'+sectionId);
            $(fileInput).appendTo(formClone);
        }
    });
        
    // Submit the form
    formClone.submit();
    formClone.remove(); // Ninja vanish!

    // Find the submit button and the submit response
    if(!this.debugMode){
        this.controlNextButton.text(this.options.submitProcessingButtonText).attr('disabled', 'disabled');
    }
    else {
        this.form.find('iframe:hidden').show();
    }

    // Add a processing li to the form control
    this.control.append('<li class="processingLi" style="display: none;"></li>');
    this.control.find('.processingLi').fadeIn();
},

updateProcessingText: function(text) {
    this.control.find('.nextButton').text(text);
},

handleFormSubmissionResponse: function(json) {
    var self = this;

    // Remove the processing li from the form control
    this.control.find('.processingLi').stop().fadeOut(400, function() {
        self.control.find('.processingLi').remove();
    } );
        
    // Form failed processing
    if(json.status == 'failure') {
        // Handle validation failures
        if(json.response.validationFailed) {
            $.each(json.response.validationFailed, function(formPageKey, formPageValues){
                $.each(formPageValues, function(formSectionKey, formSectionValues){
                    // Handle section instances
                    if($.isArray(formSectionValues)) {
                        $.each(formSectionValues, function(formSectionInstanceIndex, formSectionInstanceValues){
                            var sectionKey;
                            if(formSectionInstanceIndex != 0) {
                                sectionKey = '-section'+(formSectionInstanceIndex + 1);
                            }
                            else {
                                sectionKey = '';
                            }
                            $.each(formSectionInstanceValues, function(formComponentKey, formComponentErrors) {
                                self.formPages[formPageKey].formSections[formSectionKey].instanceArray[formSectionInstanceIndex].formComponents[formComponentKey + sectionKey].handleServerValidationResponse(formComponentErrors);
                            });
                        });
                    }
                    // There are no section instances
                    else {
                        $.each(formSectionValues, function(formComponentKey, formComponentErrors){
                            self.formPages[formPageKey].formSections[formSectionKey].formComponents[formComponentKey].handleServerValidationResponse(formComponentErrors);
                        });
                    }
                });
            });
        }

        // Show the failureHtml if there was a problem
        if(json.response.failureHtml) {
            // Update the failure HTML
            this.control.find('.formFailure').remove();
            this.control.after('<div class="formFailure">'+json.response.failureHtml+'</div>');
        }

        // Strip the script out of the iframe
        this.form.find('iframe').contents().find('body script').remove();
        if(this.form.find('iframe').contents().find('body').html() !== null) {
            this.form.find('.formFailure').append('<p>Output:</p>'+this.form.find('iframe').contents().find('body').html().trim());
        }

        // Reset the page, focus on the first failed component
        this.controlNextButton.text(this.options.submitButtonText);
        this.controlNextButton.removeAttr('disabled');
        this.getActivePage().focusOnFirstFailedComponent();
    }
    // Form passed processing
    else if(json.status == 'success'){
        this.controlNextButton.text(this.options.submitButtonText);
        // Show a success page
        if(json.response.successPageHtml){
            // Stop saving the form
            clearInterval(this.saveIntervalSetTimeoutId);

            // Create the success page html
            var successPageDiv = $('<div id="'+this.id+'formPageSuccess" class="formPage formPageSuccess">'+json.response.successPageHtml+'</div>');
            successPageDiv.css('width', this.formPages[this.formPageIdArray[0]].page.width());
            this.formPageScroller.css('width', this.formPageScroller.width() + this.formPages[this.formPageIdArray[0]].page.width());
            this.formPageScroller.append(successPageDiv);
               
            // Create the success page
            var formPageSuccess = new FormPage(this, this.id+'formPageSuccess');
            this.addFormPage(formPageSuccess);

            // Hide the page navigator and controls
            this.control.hide();
            if(this.formPageNavigator) {
                this.formPageNavigator.hide();
            }
                
            // Scroll to the page
            formPageSuccess.scrollTo();
        }
        // Show a failure page that allows you to go back
        else if(json.response.failurePageHtml){
            // Create the failure page html
            var failurePageDiv = $('<div id="'+this.id+'formPageFailure" class="formPage formPageFailure">'+json.response.failurePageHtml+'</div>');
            failurePageDiv.width(this.formPages[this.formPageIdArray[0]].page.width());
            this.formPageScroller.append(failurePageDiv);

            // Create the failure page
            var formPageFailure = new FormPage(this, this.id+'formPageFailure');
            this.addFormPage(formPageFailure);

            // Create a start over button
            this.control.append($('<li class="startOver"><button class="startOverButton">Start Over</button></li>'));

            // Scroll to the failure page
            formPageFailure.scrollTo();
        }
        // Show a failure notice on the same page
        if(json.response.failureNoticeHtml){
            this.control.find('.formFailureNotice').remove();
            this.control.append('<li class="formFailureNotice">'+json.response.failureNoticeHtml+'</li>');
            this.controlNextButton.text(this.options.submitButtonText);
            this.controlNextButton.removeAttr('disabled');
        }

        // Show a large failure response on the same page
        if(json.response.failureHtml){
            this.control.find('.formFailure').remove();
            this.control.after('<div class="formFailure">'+json.response.failureHtml+'</div>');
            this.controlNextButton.text(this.options.submitButtonText);
            this.controlNextButton.removeAttr('disabled');
        }

        // Evaluate any failure or successful javascript
        if(json.response.successJs){
            eval(json.response.successJs);
                
        }
        else if(json.response.failureJs){
            eval(json.response.failureJs);
        }

        // Redirect the user
        if(json.response.redirect){
            this.controlNextButton.html('Redirecting...');
            document.location = json.response.redirect;
        }

        // Reload the page
        if(json.response.reload){
            this.controlNextButton.html('Reloading...');
            document.location.reload(true);
        }
    }
},

reset: function() {
    this.control.find('.formFailureNotice').remove();
    this.control.find('.formFailure').remove();
    this.controlNextButton.text(this.options.submitButtonText);
    this.controlNextButton.removeAttr('disabled');
},

showAlert: function(message, formComponentType, modal, options){
    if(!this.options.alertsEnabled){
        return;
    }
    var animationOptions = $.extend(this.options.animationOptions.alert, options);


    var alertWrapper = this.form.find('.formAlertWrapper');
    var alertDiv = this.form.find('.formAlert');

    alertDiv.addClass(formComponentType);
    alertDiv.text(message);

    // Show the message
    if(animationOptions.appearEffect == 'slide'){
        alertWrapper.slideDown(animationOptions.appearDuration, function(){
            // hide the message
            setTimeout(hideAlert(), 1000);
        });
    } else if(animationOptions.appearAffect == 'fade') {
        alertWrapper.fadeIn(animationOptions.appearDuration, function(){
            // hide the message
            setTimeout(hideAlert(), 1000);
        });
    }

    function hideAlert(){
        if(animationOptions.hideEffect == 'slide'){
            alertWrapper.slideUp(animationOptions.hideDuration, function() {
                });
        } else if(animationOptions.hideEffect == 'fade'){
            alertWrapper.fadeOut(animationOptions.hideDuration, function() {
                });
        }
    }

},

showModal: function(header, content, className, options) {
    // Get the modal wrapper div element
    var modalWrapper = this.form.find('.formModalWrapper');

    // set animation options
    var animationOptions = $.extend(this.options.animationOptions.modal, options);

    // If there is no modal wrapper, add it
    if(modalWrapper.length == 0) {
        var modalTransparency = $('<div class="formModalTransparency"></div>');
        modalWrapper = $('<div style="display: none;" class="formModalWrapper"><div class="formModal"><div class="formModalHeader">'+header+'</div><div class="formModalContent">'+content+'</div><div class="formModalFooter"><button>Okay</button></div></div></div>');

        // Add the modal wrapper after the alert
        this.form.find('.formAlertWrapper').after(modalTransparency);
        this.form.find('.formAlertWrapper').after(modalWrapper);

        // Add any custom classes
        if(className != '') {
            modalWrapper.addClass(className);
        }

        // Add the onclick event for the Okay button
        modalWrapper.find('button').click(function(event) {
            $('.formModalWrapper').hide(animationOptions.hideDuration);
            $('.formModalTransparency').hide(animationOptions.hideDuration);
            $('.formModalWrapper').remove();
            $('.formModalTransparency').remove();
            $('body').css('overflow','auto');
        });
    }

    // Get the modal div element
    var modal = modalWrapper.find('.formModal');
    modal.css({
        'position':'absolute'
    });
    var varWindow = $(window);
    $('body').css('overflow','hidden');
    // Add window resize and scroll events
    varWindow.resize(function(event) {
        leftMargin = (varWindow.width() / 2) - (modal.width() / 2);
        topMargin = (varWindow.height() / 2) - (modal.height() / 2) + varWindow.scrollTop();
        modal.css({
            'top': topMargin, 
            'left': leftMargin
        });
        $('.formModalTransparency').width(varWindow.width()).height(varWindow.height());
    });

    // If they click away from the modal (on the modal wrapper), remove it
    $('.formModalTransparency').click(function(event) {
        if($(event.target).is('.formModalTransparency')) {
            modalWrapper.hide(animationOptions.hideDuration);
            modalWrapper.remove();
            $('.formModalTransparency').hide(animationOptions.hideDuration);
            $('.formModalTransparency').remove();
            $('body').css('overflow','auto');
        }
    });

    // Show the wrapper
    //modalWrapper.width(varWindow.width()).height(varWindow.height()*1.1).css('top', varWindow.scrollTop());
    modalWrapper.show(animationOptions.appearDuration);

    // Set the position
    var leftMargin = (varWindow.width() / 2) - (modal.width() / 2);
    var topMargin = (varWindow.height() / 2) - (modal.height() / 2) + varWindow.scrollTop();
    $('.formModalTransparency').width(varWindow.width()).height(varWindow.height()*1.1).css('top', varWindow.scrollTop());
    modal.css({
        'top': topMargin, 
        'left': leftMargin
    });
},

recordAnalytics: function() {
    var self = this;
    if(!this.options.disableAnalytics) {
        setTimeout(function() {
            var jsProtocol = "https:" == document.location.protocol ? "https://www." : "http://www.";
            var image = $('<img src="'+jsProtocol+'jformer.com/analytics/analytics.gif?pageCount='+self.formPageIdArray.length+'&componentCount='+self.formComponentCount+'&formId='+self.id+'" style="display: none;" />');
            self.form.append(image);
            image.remove();
        }, 3000);
    }
},

updateProgressBar: function() {
    var totalRequired = 0;
    var totalRequiredCompleted = 0;
    $.each(this.formPages, function(pageKey, pageObject){
        $.each(pageObject.formSections, function(sectionKey, sectionObject){
            $.each(sectionObject.formComponents, function(componentKey, componentObject){
                if(componentObject.isRequired === true && (componentObject.disabledByDependency === false && sectionObject.disabledByDependency === false)) {
                    if(componentObject.type != 'FormComponentLikert'){
                        totalRequired = totalRequired + 1;
                        if(componentObject.requiredCompleted === true){
                            totalRequiredCompleted = totalRequiredCompleted + 1;
                        }
                    }
                }
            });
        });
    });

    var percentCompleted = parseInt((totalRequiredCompleted / totalRequired) * 100);

    this.form.find('.formProgressBar').animate({
        'width': percentCompleted+'%'
    }, 500)
    .html('<p>'+percentCompleted + '%</p>');
},

addBlurTipListener: function(){
    var self = this;
    $(document).bind('blurTip', function(event, tipElement, action){    
        if(action == 'hide'){
            self.blurredTips = $.map(self.blurredTips, function(tip, index){
                if($(tip).attr('id') == tipElement.attr('id')){
                    return null
                } else {
                    return tip;
                }
            });
            if(self.blurredTips[self.blurredTips.length-1] != undefined){
                self.blurredTips[self.blurredTips.length-1].removeClass('formTipBlurred');
            }
        } else if(action == 'show'){
            if(self.blurredTips.length > 0){
                $.each(self.blurredTips, function(index, tip){
                    $(tip).addClass('formTipBlurred')
                })
            }
            self.blurredTips.push(tipElement)
            tipElement.removeClass('formTipBlurred');
        }
    });
//console.log('blurring tips', tipElement, action);
        
//console.log(this.blurredTips);
}
});