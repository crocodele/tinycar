module Tinycar.Main
{
	export class Mask
	{
		private htmlRoot:JQuery;
	
	
		// Build
		build():JQuery
		{
			this.buildRoot();
			return this.htmlRoot;
		}
		
		// Build root container
		private buildRoot():void
		{
			this.htmlRoot = $('<div>').
				attr('id', 'tinycar-main-mask');
		}
		
		// Hide mask
		hide():void
		{
			this.htmlRoot.addClass('is-hidden');
		}
		
		// Show mask
		show():void
		{
			this.htmlRoot.
				removeClass('is-hidden').
				addClass('is-visible');
		}
	}
}