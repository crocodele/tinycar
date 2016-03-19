module Tinycar.Ui.Component
{
	export class Text extends Tinycar.Main.Field
	{
	
	
		// Build content
		buildContent()
		{
			// Build elements
			super.buildContent();
			
			// Build placeholder
			if (this.getDataValue() === null)
				this.buildPlaceholder();
			
			// Build text content
			else
				this.buildText();
		}
		
		// Build placeholder
		private buildPlaceholder():void
		{
			$('<div>').
				attr('class', 'placeholder').
				html(this.Model.get('placeholder')).
				appendTo(this.htmlContent);
		}
		
		// Build text content
		private buildText():void
		{
			$('<div>').
				attr('class', 'text').
				html(this.getDataValue()).
				appendTo(this.htmlContent);
		}
	}
}