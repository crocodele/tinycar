module Tinycar.Main
{
	import m = Tinycar.Model;
	import u = Tinycar.Ui;
	
	export class Field extends Tinycar.Main.Component
	{
		htmlContent:JQuery;
		
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
			
			// This field is not disabled
			if (this.isTypeEnabled() === true)
				result.push('is-enabled');
			
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
		
		// Check if this field is enabled
		isTypeEnabled():boolean
		{
			return (this.Model.get('type_enabled') !== false);
		}
		
		// Set new data value
		setDataValue(value:any):void
		{
			this.Model.set('data_value', value);
		}
	}
}