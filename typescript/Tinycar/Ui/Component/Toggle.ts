module Tinycar.Ui.Component
{
	export class Toggle extends Tinycar.Main.Field
	{
		private htmlContainer:JQuery;

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
			this.htmlContainer = $('<a>').
				attr('href', '#toggle').
				attr('class', 'toggle').
				appendTo(this.htmlContent);

			// Add icon
			$('<span>').
				attr('class', 'icon icon-lite icon-tiny icon-check').
				appendTo(this.htmlContainer);

			// Add handle
			let handle = $('<span>').
				attr('class', 'handle').
				appendTo(this.htmlContainer);

			// Add icon
			$('<span>').
				attr('class', 'icon icon-tiny icon-handle').
				appendTo(handle);

			// Set initial style
			if (this.getDataValue() === true)
			{
				this.htmlContainer.addClass('is-active');

				if (this.isTypeEnabled() === true)
					this.htmlContainer.addClass('theme-base-lite');
			}

			// When clicked
			this.htmlContainer.click((e:Event) =>
			{
				e.preventDefault();

				// Change value when clicked if field is enabled
				if (this.isTypeEnabled() == true)
				{
					// Toggle class name
					this.htmlContainer.toggleClass('is-active');
					this.htmlContainer.toggleClass('theme-base-lite');

					// Update value
					this.setDataValue(
						!(this.getDataValue() === true)
					);
				}
			});

			// When key is pressed
			this.htmlContainer.keydown((e:JQueryKeyEventObject) =>
			{
				// Move toggle switch when space bar is pressed
				if (e.which === 32)
				{
					e.preventDefault();
					this.htmlContainer.click();
				}
			});
		}
	}
}
