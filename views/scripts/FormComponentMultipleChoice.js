FormComponentMultipleChoice = FormComponent.extend({
    initialize: function(parentFormSection, formComponentId, formComponentType, options) {
        this._super(parentFormSection, formComponentId, formComponentType, options);
    },

    prime: function(){
        this.tipTarget = this.component;
        this.addChoiceTips();
        this.validationFunctions = {
            //MultipleChoice validations
            'required': function(options) {
                var errorMessageArray = ['Required.'];
                return options.value.length > 0 ? 'success' : errorMessageArray;
            },
            'minOptions': function(options) {
                var errorMessageArray = ['You must select more than '+ options.minOptions +' options'];
                return options.value.length == 0 || options.value.length > options.minOptions ? 'success' : errorMessageArray;
            },
            'maxOptions': function(options) {
                var errorMessageArray = ['You may select up to '+ options.maxOptions +' options. You have selected '+ options.value.length + '.'];
                return options.value.length == 0 || options.value.length <= options.maxOptions ? 'success' : errorMessageArray;
            }
        }
    },

    addChoiceTips: function(){
        var self = this;
        var tips = this.component.find('div.formComponentMultipleChoiceTip');
        if(tips.length > 0) {
            tips.each(function(index, tip) {
                var tipTarget = $(tip).prev('label').find('.formComponentMultipleChoiceTipIcon');
                if (tipTarget.length == 0){
                    tipTarget = $(tip).parent();
                }
                tipTarget.simpletip({
                    position: 'topRight',
                    content: $(tip),
                    baseClass: 'formTip formComponentMultipleChoiceTip',
                    hideEffect: 'none'
                });
            });
        }
    },

    getValue: function() {
        if(this.disabledByDependency || this.parentFormSection.disabledByDependency){
           return null;
        }
        var multipleChoiceValue
        if(this.options.multipleChoiceType == 'checkbox') {
            multipleChoiceValue = [];
            this.component.find('input:checked').each(function(index, input){
                multipleChoiceValue.push($(input).val());
            });
        }
        else {
            if(this.component.find('input:checked').length > 0){
                multipleChoiceValue = this.component.find('input:checked').val();
            }
            else {
                multipleChoiceValue = '';
            }
        }
        return multipleChoiceValue;
    },

    setValue: function(data) {
        var self = this;
        // Checkbox
        if(this.options.multipleChoiceType == 'checkbox') {
            $.each(data, function(key, value){
                self.component.find('input[value=\''+value+'\']').attr('checked', 'checked').trigger('formComponent:changed');
            });
        }
        // Radio button
        else {
            this.component.find('input[value=\''+data+'\']').attr('checked', 'checked').trigger('formComponent:changed');

            if(data == null) {
                this.component.find('input').attr('checked', false).trigger('formComponent:changed');
            }
        }
        this.validate(true);
    }
});