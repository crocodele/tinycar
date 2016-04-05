module Tinycar.Ui.Component
{
	export class Image extends Tinycar.Main.Component
	{
		
		
		// @see Tinycar.Main.Field.buildContent()
		buildContent():void
		{
			// Build elements
			super.buildContent();
			this.buildImage();
		}
		
		
		// Build image container
		private buildImage():void
		{
			$('<img>').
				attr('src', this.Model.get('image_data')).
				attr('class', 'image').
				appendTo(this.htmlRoot);
		}
		
	}
}