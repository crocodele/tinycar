module Tinycar.Ui.Component
{
	export class TextContent extends Tinycar.Main.Component
	{
	
	
		// Build content
		buildContent()
		{
			// Build elements
			this.buildText();
		}
		
		// Build text content
		private buildText():void
		{
		    // Create content
			let container = $('<div>').
				attr('class', 'text').
				html(this.Model.get('text')).
				appendTo(this.htmlRoot);
			
			// Align text to center
            if (this.Model.get('align') === 'center')
                container.addClass('align-center');
		}
	}
}