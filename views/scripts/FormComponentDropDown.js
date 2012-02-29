FormComponentDropDown = FormComponent.extend({
    initialize: function(parentFormSection, formComponentId, formComponentType, options) {
        this._super(parentFormSection, formComponentId, formComponentType, options);
    },

    prime: function(){
       this.tipTarget = this.component.find('select:last');
    },

    getValue: function() {
        if(this.disabledByDependency || this.parentFormSection.disabledByDependency){
           return null;
        }
            var dropDownValue = $('#'+this.id).val();
            return dropDownValue;
    },

    setValue: function(value){
        $('#'+this.id).val(value).trigger('formComponent:changed');
      //this.component.find('option[value=\''+value+'\']').attr('selected', 'selected').trigger('formComponent:changed');
      this.validate(true);
    }

});