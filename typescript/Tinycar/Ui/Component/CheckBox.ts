module Tinycar.Ui.Component
{
	export class CheckBox extends Tinycar.Main.Field
	{
		private htmlField:JQuery;
		private fldInput:JQuery;


		// Build content
		buildContent()
		{
			// Build elements
			super.buildContent();
			this.buildField();
			this.buildInput();
			this.buildLabel();

			// Build  help text
			if (this.Model.hasString('type_help'))
				this.buildHelp();
		}

		// Build field container
		private buildField():void
		{
			this.htmlField = $('<div>').
				attr('class', 'field').
				appendTo(this.htmlContent);
		}

		// Build field help
		private buildHelp():void
		{
			let help = new Tinycar.Ui.Main.Help(this.Model.get('type_help'));
			this.htmlField.append(help.build());
		}

		// Build input field
		private buildInput():void
		{
			// Create field
			this.fldInput = $('<input>').
				attr('id', this.getFieldId()).
				attr('type', 'checkbox').
				appendTo(this.htmlField);

			// Initial value is checked
			if (this.getDataValue() === true)
				this.fldInput.prop('checked', true);

			// When field value is changed
			this.fldInput.change((e:Event) =>
			{
				// Update current value
				this.setDataValue(this.fldInput.prop('checked'));
			});
		}

		// Build label
		private buildLabel():void
		{
			// Create container
			let container = $('<label>').
				attr('for', this.getFieldId()).
				appendTo(this.htmlField);

			// Add mark container
			let mark = $('<span>').
				attr('class', 'mark').
				appendTo(container);

			// Add mark icon
			$('<span>').
				attr('class', 'icon icon-lite icon-tiny icon-check').
				appendTo(mark);

			// Add label
			$('<span>').
				attr('class', 'label').
				text(this.Model.get('label')).
				appendTo(container);
		}

		// @see Tinycar.Main.Field.focus()
		focus():void
		{
			this.fldInput.focus();
		}
	}
}
