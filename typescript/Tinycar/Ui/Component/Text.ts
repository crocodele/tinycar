module Tinycar.Ui.Component
{
	export class Text extends Tinycar.Main.Field
	{


		// Build content
		buildContent()
		{
			super.buildContent();

			// Build placeholder
			if (this.getDataValue() === null)
				this.buildPlaceholder();

			// Build link
			else if (this.hasLink())
				this.buildLink();

			// Build text content
			else
				this.buildText();
		}

		// Build link
		private buildLink():void
		{
			// Target URL
			let url = (this.Model.isObject('link_path') ?
			    Tinycar.Url.getAsPath(this.Model.get('link_path')) :
			    this.Model.get('link_url')
			);

			// Create link container
			let container = $('<a>').
				attr('href', url).
				attr('class', 'link').
				appendTo(this.htmlContent);

			// Add label
			$('<span>').
				attr('class', 'label').
				html(this.getDataValue()).
				appendTo(container);

			// Add icon
			$('<span>').
				attr('class', 'icon icon-tiny icon-arrow-right').
				appendTo(container);

			// When clicked
			container.click((e:JQueryMouseEventObject) =>
			{
				// Don't do anything field is disabled
				if (!this.isFieldEnabled())
					return;

			    if (e.ctrlKey === false && e.shiftKey === false)
			        Tinycar.Page.setState('unloading');
			});
		}

		// Build placeholder
		private buildPlaceholder():void
		{
			// Get placeholder string
			let value = this.Model.getString('placeholder');

			// Use an empty string
			if (value.length === 0)
				value = '&nbsp;'

			// Create placeholder container
			$('<span>').
				attr('class', 'text placeholder').
				html(value).
				appendTo(this.htmlContent);
		}

		// Build text content
		private buildText():void
		{
			$('<span>').
				attr('class', 'text').
				html(this.getDataValue()).
				appendTo(this.htmlContent);
		}

		// Check if text should have a link
		private hasLink():boolean
		{
		    return (
		        this.Model.isObject('link_path') ||
		        this.Model.hasString('link_url')
		    );
		}
	}
}
