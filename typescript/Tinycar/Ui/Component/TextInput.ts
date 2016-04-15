module Tinycar.Ui.Component
{
	export class TextInput extends Tinycar.Main.Field
	{
		private fldInput:JQuery;
		private hasValue:boolean;
		private type:string;


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
			this.type = 'area';

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
			// Remember type
			this.type = 'input';

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


		// Set textarea height to fit content
		private fitHeightToContent(padding:number):void
		{
			// First set to natural height to allow shrinking when user removes text
			this.fldInput.css('height', 'auto');

			// Take as much vertical space as needed to fit content perfectly
			this.fldInput.height(this.fldInput.prop('scrollHeight') - padding);
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
				let value = this.fldInput.val();

				// Update current value
				this.setDataValue(value);
			});

			// Set up vertical auto-resize for textarea
			if (this.type === 'area')
			{
				// Resolve field padding
				// @note: The box model probably affects which properties should
				// be used. Here we assume that box-sizing: border-box; is used.
				var padding = this.fldInput.innerHeight() - this.fldInput.height();

				// When user types, copy-pastes etc.
				this.fldInput.on('input', (e:Event) =>
				{
					this.fitHeightToContent(padding);
				});
			}

			// When is typed
			this.fldInput.keyup((e:Event) =>
			{
				// Clear old timer
				if (timer)
					window.clearTimeout(timer);

				// Update with a delay
				timer = window.setTimeout(() =>
				{
					timer = null;
					this.fldInput.trigger('change');

				}, 300);
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

			// Trigger input event to set initial height of textarea
			if (this.type === 'area')
				this.fldInput.trigger('input');
		}
	}
}
