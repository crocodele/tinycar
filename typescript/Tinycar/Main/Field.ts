module Tinycar.Main
{
    import m = Tinycar.Model;
    import u = Tinycar.Ui;
    
    export class Field extends Tinycar.Main.Component
    {
        htmlContent:JQuery;
        private isEnabled:boolean = false;
        
        // Build
        build():JQuery
        {
            // Build elements
            this.buildRoot();
            
            // Build type label
            if (this.Model.hasString('type_label'))
                this.buildTypeLabel();
            else
                this.buildEmptyLabel();
            
            this.buildContent();
            
            // Build type instructions
            if (this.Model.hasString('type_instructions'))
                this.buildTypeInstructions();
            
            return this.htmlRoot;
        }
        
        // @see Tinycar.Main.Component.buildContent()
        buildContent():void
        {
            // Create container
            this.htmlContent = $('<div>').
                attr('class', 'type-content').
                appendTo(this.htmlRoot);
        }
        
        // Build empty label
        private buildEmptyLabel():void
        {
            $('<div>').
                attr('class', 'type-label-empty').
                appendTo(this.htmlRoot);
        }
        
        // Build type instructions
        private buildTypeInstructions():void
        {
            $('<div>').
                attr('class', 'type-instructions').
                text(this.Model.get('type_instructions')).
                appendTo(this.htmlContent);
        }
        
        // Build type label
        private buildTypeLabel():void
        {
            // Create container
            let container = $('<div>').
                attr('class', 'type-label').
                appendTo(this.htmlRoot);
            
            // Create label
            let label = $('<label>').
                attr('class', 'label').
                attr('for', this.getFieldId()).
                text(this.Model.get('type_label')).
                appendTo(container);
            
            // This is a required field
            if (this.Model.get('data_required') === true)
            {
                // Add label title
                label.attr('title', 
                    Tinycar.Locale.getText('info_required_field')
                );
                
                // Add required mark
                $('<span>').
                    attr('class', 'required theme-base').
                    appendTo(label);
            }
            
            // This field has a help text
            if (this.Model.hasString('type_help'))
            {
                let help = new Tinycar.Ui.Main.Help(this.Model.get('type_help'));
                container.append(help.build());
            }
        }
        
        // Set focus to this field
        focus():void
        {
            // @note: implement in derived class
        }
        
        // @see Tinycar.Main.Component.getRootStyles()
        getRootStyles():Array<string>
        {
            let result = super.getRootStyles();
            
            // Base class
            result.push('tinycar-main-field');
            
            return result;
        }
        
        // Get current component's data name
        getDataName():string
        {
            return this.Model.get('data_name'); 
        }
        
        // Get current component's data value
        getDataValue():any
        {
            return this.Model.get('data_value');
        }
        
        // Get unique field id
        getFieldId(postfix:string = ''):string
        {
            return 'field-' + this.Model.get('id') + '-' + postfix;
        }
        
        // Check if this field has a dataname
        hasDataName():boolean
        {
            return this.Model.isString('data_name');
        }
        
        // Check if this field is enabled
        isTypeEnabled():boolean
        {
            return (this.Model.get('type_enabled') !== false);
        }
        
        // Set field as enabled
        setAsEnabled(status:boolean):boolean
        {
            // Enable field
            if (status && !this.isEnabled)
            {
                this.isEnabled = true;
                this.htmlRoot.addClass('is-enabled');
                return true;
            }
            
            // Disable field
            if (!status && this.isEnabled)
            {
                this.isEnabled = false;
                this.htmlRoot.removeClass('is-enabled');
                return true;
            }
            
            // State did not change
            return false;
        }
        
        // Set new data value
        setDataValue(value:any):void
        {
            // This is a new value
            if (this.Model.get('data_value') !== value)
            {
                // Set new value
                this.Model.set('data_value', value);
                
                // We must have a name to trigger binding
                if (this.hasDataName())
                {
                    // Update binding property value
                    this.View.Bind.set(
                        this.Model.get('data_name'), 
                        this.Model.get('data_value')
                    );
                    
                    // Trigger bindings
                    this.View.triggerBinding(
                        this.Model.get('data_name') 
                    );
                }
            }
        }
        
        // @see Tinycar.Main.Component 
        start():void
        {
            // Fields that support disabled state should be set
            // disabled by defualt, so set them only as 
            // enabled when initiated
            
            if (this.isTypeEnabled())
                this.setAsEnabled(true);
        }
    }
}