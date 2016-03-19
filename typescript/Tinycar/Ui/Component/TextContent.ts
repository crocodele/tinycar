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
			$('<div>').
				attr('class', 'text').
				html(this.Model.get('text')).
				appendTo(this.htmlRoot);
		}
	}
}