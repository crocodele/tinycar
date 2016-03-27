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
			else if (this.Model.isObject('link'))
				this.buildLink();
			
			// Build text content
			else
				this.buildText();
		}
		
		// Build link
		private buildLink():void
		{
			// Target URL
			let url = Tinycar.Url.getAsPath(
				this.Model.getObject('link')
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
			container.click((e:Event) =>
			{
				Tinycar.System.Page.setState('unloading');
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
	}
}