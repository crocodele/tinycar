module Tinycar.Ui.Component
{
	export class TextInput extends Tinycar.Main.Field
	{
		private fldInput:JQuery;
		private hasValue:boolean;
	    private isArea:boolean;
        private minAreaHeight:number = 0;


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
			// Remember type
			this.isArea = true;
			
			// Create field
			this.fldInput = $('<textarea>').
				attr('id', this.getFieldId()).
				attr('placeholder', this.Model.getString('placeholder')).
				prop('spellcheck', false).
				prop('value', this.getDataValue()).
				appendTo(this.htmlContent);
			
			// Add maxlength restriction
			if (this.Model.get('maxlength') > 0)
				this.fldInput.prop('maxlength', this.Model.get('maxlength'));
			
			// Minimum area height based on row amount
            this.minAreaHeight = this.Model.getNumber('rows') * 23;
		}


		// Build input field
		private buildInput():void
		{
			// Remember type
			this.isArea = false;
			
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


		// Get vertical field padding
		private getVerticalFieldPadding():number
		{
			// @note: The box model probably affects which properties should
			// be used. Here we assume that box-sizing: border-box; is used.
			return this.fldInput.innerHeight() - this.fldInput.height();
		}


		// Set textarea height to fit content
		private fitHeightToContent(padding:number):void
		{
			// First set to natural height to allow shrinking when user removes text
			this.fldInput.css('height', 'auto');
			
			// Take as much vertical space as needed to fit content perfectly
			this.fldInput.height(Math.max(
		          this.minAreaHeight, 
		          this.fldInput.prop('scrollHeight') - padding
		    ));
		}


		// @see Tinycar.Main.Field.focus()
		focus():void
		{
			this.fldInput.focus();
		}

		// Initiate listeners
		private initListeners():void
		{
			// Resolve field padding once
			let padding = this.getVerticalFieldPadding();

			// When user types, copy-pastes etc. text into field
			this.fldInput.on('input', (e:Event) =>
			{
				let value = this.fldInput.val();

				// Update current value
				this.setDataValue(value);

				// Auto-resize textarea vertically to fit content
				if (this.isArea)
					this.fitHeightToContent(padding);
			});
		}

		// @see Tinycar.Main.Field.setDataValue()
		setDataValue(value:string):void
		{
			// Update component value
			super.setDataValue(value);

			// Update field value
			this.fldInput.val(value);

			// New value existance state
			let hasValue = this.Model.hasString('data_value');

			// State has changed, trigger event
			if (this.hasValue !== hasValue)
			{
				this.hasValue = hasValue;
				this.callHandler('value', this.hasValue);
			}
		}

		// @see Tinycar.Main.Field.start()
		start():void
		{
			super.start();

			// Trigger initial value state listener
			this.hasValue = this.Model.hasString('data_value');
			this.callHandler('value', this.hasValue);

			// Set initial height of textarea
			if (this.isArea)
				this.fitHeightToContent(this.getVerticalFieldPadding());
		}
	}
}
