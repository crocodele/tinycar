module Tinycar.Ui.Component
{
	export class TextInput extends Tinycar.Main.Field
	{
		private fldInput:JQuery;
	
	
		// Build content
		buildContent()
		{
			// Build elements
			super.buildContent();
			
			// Build textarea when we have multiple rows
			if (this.Model.get('rows') > 0)
				this.buildArea();
			
			// Build input field
			else
				this.buildInput();
			
			// Initiate listener
			this.initListeners();
		}
		
		// Build textarea field
		private buildArea():void
		{
			// Create field
			this.fldInput = $('<textarea>').
				attr('id', this.getFieldId()).
				attr('placeholder', this.Model.getString('placeholder')).
				attr('rows', this.Model.get('rows')).
				prop('spellcheck', false).
				prop('value', this.getDataValue()).
				appendTo(this.htmlContent);
			
			// Add maxlength restriction
			if (this.Model.get('maxlength') > 0)
				this.fldInput.prop('maxlength', this.Model.get('maxlength'));
		}
		
		
		// Build input field
		private buildInput():void
		{
			// Create field
			this.fldInput = $('<input>').
				attr('id', this.getFieldId()).
				attr('type', 'text').
				attr('placeholder', this.Model.getString('placeholder')).
				prop('spellcheck', false).
				prop('value', this.getDataValue()).
				appendTo(this.htmlContent);
			
			// Add maxlength restriction
			if (this.Model.get('maxlength') > 0)
				this.fldInput.prop('maxlength', this.Model.get('maxlength'));
		}
		
		// @see Tinycar.Main.Field.focus()
		focus():void
		{
			this.fldInput.focus();
		}
		
		// Initiate listeners
		private initListeners():void
		{
			var timer = null;
			
			// Change value is changed
			this.fldInput.change((e:Event) =>
			{
				// Update current value
				this.setDataValue(this.fldInput.val());
			});
			
			// When is typed
			this.fldInput.keyup((e:Event) =>
			{
				// Clear old timer
				if (timer)
					window.clearTimeout(timer);

				// Update with a delay
				timer = window.setTimeout(() =>
				{
					this.fldInput.trigger('change');
					
				}, 300);
			});			
		}
	}
}