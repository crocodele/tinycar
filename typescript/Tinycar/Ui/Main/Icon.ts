module Tinycar.Ui.Main
{
	import m = Tinycar.Model;
	
	export class Icon
	{
		htmlRoot:JQuery;
	
		Model:m.DataItem;
	

		// Initiate class
		constructor(data:Object)
		{
			this.Model = new m.DataItem(data);
		}
	
	
		// Build
		build():JQuery
		{
			// Build elements
			this.buildRoot();
			this.buildIcon();
			this.buildLabel();
			
			return this.htmlRoot;
		}
		
		// Build icon
		buildIcon():void
		{
			$('<span>').
				attr('class', 'icon').
				css('background-color', this.Model.get('color')).
				css('background-image', 'url(' + this.Model.get('icon') + ')').
				appendTo(this.htmlRoot);
		}
	
		// Build label
		buildLabel():void
		{
			$('<span>').
				attr('class', 'label').
				text(this.Model.get('label')).
				appendTo(this.htmlRoot);
		}
		
		// Build root container
		buildRoot():void
		{
			this.htmlRoot = $('<a>').
				attr('class', 'tinycar-ui-main-icon').
				attr('href', Url.getAsPath(this.Model.get('path')));
				
			// When clicked
			this.htmlRoot.click((e:Event) =>
			{
				e.preventDefault();
				Tinycar.Url.openUrl(this.htmlRoot.attr('href'));
			});
		}
	}
}