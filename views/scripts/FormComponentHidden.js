FormComponentHidden = FormComponent.extend({
    initialize: function(parentFormSection, formComponentId, formComponentType, options) {
        this._super(parentFormSection, formComponentId, formComponentType, options);
    },
    
    setValue: function(value) {
        return $('#'+this.id).val(value);
    },

    getValue: function() {
        if(this.disabledByDependency || this.parentFormSection.disabledByDependency){
           return null;
        }
        return $('#'+this.id).val();
    },

    validate: function() {
        this._super();
    }
});
