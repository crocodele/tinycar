module Tinycar.Main
{
	export class Mask
	{
		private htmlRoot:JQuery;
	
	
		// Build root container
		private buildRoot():void
		{
			// Create container
			this.htmlRoot = $('<div>').
				attr('id', 'tinycar-main-mask');
			
			// Add to page
			Tinycar.Page.addNode(this.htmlRoot);
		}
		
		// Hide skin or mask
		hide():void
		{
			this.htmlRoot.addClass('is-hidden');
		}
		
		// Show as dark mask
		showAsMask():void
		{
			// Build once
			if (!(this.htmlRoot instanceof Object))
				this.buildRoot();
			
			// Update styles
			this.htmlRoot.
				removeClass('is-hidden').
                removeClass('is-skin').
				addClass('is-visible').
				css('cursor', 'default');
		}
		
		// Show as a transparent skin
		showAsSkin(cursor:string):void
		{
			// Build once
			if (!(this.htmlRoot instanceof Object))
				this.buildRoot();
			
			// Update styles
			this.htmlRoot.
				removeClass('is-hidden').
				addClass('is-skin').
				addClass('is-visible').
				css('cursor', cursor);
		}
	}
}