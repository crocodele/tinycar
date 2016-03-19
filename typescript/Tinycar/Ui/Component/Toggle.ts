module Tinycar.Ui.Component
{
	export class Toggle extends Tinycar.Main.Field
	{
	
	
		// Build content
		buildContent()
		{
			// Build elements
			super.buildContent();
			this.buildToggle();
		}
		
		// Build toggle element
		private buildToggle():void
		{
			// Create container
			var container = $('<a>').
				attr('href', '#toggle').
				attr('class', 'toggle').
				appendTo(this.htmlContent);
				
			// Add icon
			$('<span>').
				attr('class', 'icon icon-lite icon-tiny icon-check').
				appendTo(container);
			
			// Add handle
			let handle = $('<span>').
				attr('class', 'handle').
				appendTo(container);
			
			// Add icon
			$('<span>').
				attr('class', 'icon icon-tiny icon-handle').
				appendTo(handle);
			
			// Set initial style
			if (this.getDataValue() === true)
			{
				container.addClass('is-active');
				
				if (this.isTypeEnabled() === true)
					container.addClass('theme-base-lite');				
			}
			
			// When clicked
			container.click((e:Event) =>
			{
				e.preventDefault();
				
				// Change value when clicked if field is enabled
				if (this.isTypeEnabled() == true)
				{
					// Toggle class name
					container.toggleClass('is-active');
					container.toggleClass('theme-base-lite');
							
					// Update value
					this.setDataValue(
						(this.getDataValue() === false)
					);
				}
			});			
		}
	}
}