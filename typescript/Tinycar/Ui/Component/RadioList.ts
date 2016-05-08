module Tinycar.Ui.Component
{
	export class RadioList extends Tinycar.Main.Field
	{
		private fldOptions:Array<JQuery> = [];

		// Build content
		buildContent()
		{
			// Build elements
			super.buildContent();
			this.buildOptions();
			this.updateOptions();
		}

		// Build single option
		private buildOption(item:Object):JQuery
		{
			// Create container
			let container = $('<div>').
				attr('class', 'item');

			// Create input field
			let input = $('<input>').
				attr('id', this.getFieldId(item['name'])).
				attr('name', this.getFieldId()).
				attr('type', 'radio').
				attr('value', item['name']).
				appendTo(container);

			// When value is changed
			input.change((e:Event ) =>
			{
				this.setDataValue(input.val());
			});

			// Add label container
			let label = $('<label>').
				attr('for', this.getFieldId(item['name'])).
				appendTo(container);

			// Add mark icon
			let mark = $('<span>').
				attr('class', 'mark').
				appendTo(label);

			// Add mark point
			$('<span>').
				attr('class', 'point').
				appendTo(mark);

			// Add label string
			$('<span>').
				attr('class', 'label').
				text(item['label']).
				appendTo(label);

			// Add help, if we have one
			if (typeof item['help'] === 'string')
			{
				// Create new instance
				let help = new Tinycar.Ui.Main.Help(item['help']);
				container.append(help.build());
			}

			// Add instructions, if we have any
			if (typeof item['instructions'] === 'string')
			{
				$('<div>').
					attr('class', 'instructions').
					text(item['instructions']).
					appendTo(container);
			}

			// Add input reference to list
			this.fldOptions.push(input);

			return container;
		}

		// Build options list
		private buildOptions():void
		{
			this.Model.get('options').forEach((item:Object) =>
			{
				this.htmlContent.append(this.buildOption(item));
			});
		}

		// @see Tinycar.Main.Field.getRootStyles()
		getRootStyles():Array<string>
		{
			let result = super.getRootStyles();
			result.push('layout-' + this.Model.get('layout'));
			return result;
		}

		// @see Tinycar.Main.Field.setAsEnabled()
		setAsEnabled(status:boolean):boolean
		{
			// State did not change
			if (!super.setAsEnabled(status))
				return false;

			// Update field status
			this.fldOptions.forEach((item:JQuery) =>
			{
				item.prop('disabled', !status);
			});
			return true;
		}

		// Update options to reflect curren value
		private updateOptions():void
		{
			// Get current value as a string (for comparison)
			let value = '' + this.getDataValue();

			// Update options
			this.fldOptions.forEach((item:JQuery) =>
			{
				item.prop('checked', (item.val() === value));
			});
		}
	}
}
