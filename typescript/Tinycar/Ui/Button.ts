module Tinycar.Ui
{
	interface IHandlerList
	{
		[key:string]:Function;
	}
	
	export class Button
	{
		private handlerList:IHandlerList = {};
		private htmlRoot:JQuery;
		private Model:Tinycar.Model.DataItem;
	
		// Initiate class
		constructor(config:Object)
		{
			this.Model = new Tinycar.Model.DataItem(config);
		}
		
		// Build
		build():JQuery
		{
			// Build elements
			this.buildRoot();
			
			// Add icon
			if (this.Model.hasString('icon'))
				this.buildIcon();
			
			// Add label
			if (this.Model.hasString('label'))
				this.buildLabel();
			
			return this.htmlRoot;
		}
		
		// Call specified handler
		callHandler(name:string):void
		{
			if (this.handlerList.hasOwnProperty(name))
				this.handlerList[name]();
		}
		
		// Build icon
		private buildIcon():void
		{
			// Create icon image
			let icon = $('<span>').
				attr('class', 'icon icon-' + this.Model.get('icon')).
				appendTo(this.htmlRoot);
			
			// Set icon size
			icon.addClass((this.Model.has('size') ? 
				'icon-' + this.Model.get('size') : 'icon-small'
			));

			// Revert color for theme buttons
			if (this.Model.get('style') === 'theme-icon')
				icon.addClass('icon-lite');
		}
		
		// Build label
		private buildLabel():void
		{
			this.htmlRoot.text(this.Model.get('label'));
		}
		
		// Build root container
		private buildRoot():void
		{
			// Create container
			this.htmlRoot = $('<a>').
				addClass('tinycar-ui-button').
				addClass(this.Model.get('style'));
			
			// This is a theme icon
			if (this.Model.get('style') === 'theme-icon')
				this.htmlRoot.addClass('theme-base-lite');

			// This is a theme button
			else if (this.Model.get('style') === 'theme-button')
				this.htmlRoot.addClass('theme-base-lite');
			
			// When clicked
			this.htmlRoot.click((e:Event) =>
			{
				e.preventDefault();
				
				// Small delay for visual effect
				window.setTimeout(() =>
				{
					this.callHandler('click');
					
				}, 100);
			});
		}
		
		// Set custom event hander
		setHandler(name:string, callback:Function):void
		{
			this.handlerList[name] = callback;
		}
	}
}