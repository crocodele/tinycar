module Tinycar.Ui.Component
{
	export class ColorPicker extends Tinycar.Main.Field
	{
		private fldInput:JQuery;
		private htmlField:JQuery;
		private htmlPalette:JQuery;
		private htmlSample:JQuery;
		private visibleState:boolean = false;
	
	
		// Build content
		buildContent()
		{
			// Build elements
			super.buildContent();
			this.buildField();
			this.buildSample();
			this.buildInput();
			this.buildToggle();
			
			// Initiate event listeners
			this.initListeners();
		}
		
		// Build field container
		private buildField():void
		{
			// Create container
			this.htmlField = $('<div>').
				attr('class', 'field').
				appendTo(this.htmlContent);
		}
		
		// Build input field
		private buildInput():void
		{
			// Input placeholder
			let placeholder = Tinycar.Locale.getText(
				'colorpicker_color'
			);
			
			// Add input field
			this.fldInput = $('<input>').
				attr('type', 'text').
				attr('placeholder', placeholder + '...').
				prop('maxlength', 7).
				prop('value', this.getDataValue()).
				appendTo(this.htmlField);
		}
		
		// Build palette colors
		private buildPalette():boolean
		{
			// Get shared palette container
			this.htmlPalette = $('div.tinycar-ui-component-colorpicker-palette:first');

			// Sahred was found
			if (this.htmlPalette.length === 1)
				return true;

			// Create container
			this.htmlPalette = $('<div>').
				attr('class', 'tinycar-ui-component-colorpicker-palette').
				appendTo($('body:first'));
			
			// Build colors
			this.Model.get('colors').forEach((color:string) =>
			{
				this.htmlPalette.append(
					this.buildPaletteColor(color)
				);
			});
			
			// Initially hide palette
			this.htmlPalette.hide();
			
			// When resized
			Tinycar.System.addEvent('resize', () =>
			{
				this.hidePalette();
			});
			
			return true;
		}
		
		// Build single color to palette
		private buildPaletteColor(color:string):JQuery
		{
			// Create container
			let container = $('<a>').
				attr('class', 'color').
				attr('data-color', color).
				css('background-color', color).
				appendTo(this.htmlPalette);
			
			// Add content
			$('<span>').
			    appendTo(container);
			
			// When clicked
			container.click((e:Event) =>
			{
				e.preventDefault();
				this.fldInput.val(color);
				this.updateSample(color);
			});
			
			return container;
		}
		
		// Build sample spot
		private buildSample():void
		{
			// Build sample spot
			this.htmlSample = $('<span>').
				attr('class', 'sample').
				appendTo(this.htmlField);
			
			// Set initial color
			if (this.getDataValue() !== null)
			{
				this.htmlSample.css(
					'background-color', this.getDataValue()
				);
			}
		}
		
		// Build toggle button
		private buildToggle():void
		{
			// Create new instance
			let instance = new Tinycar.Ui.Button({
				style : 'dark-icon',
				icon  : 'menu-horizontal'
			});
			
			// When clicked
			instance.setHandler('click', () =>
			{
				if (this.visibleState === true)
					this.hidePalette();
				else
					this.showPalette();
			});
			
			// Add to content
			this.htmlField.append(instance.build());
		}
		
		// @see Tinycar.Main.Field.focus()
		focus():void
		{
			this.fldInput.focus();
		}
		
		// Hide palette
		private hidePalette():boolean
		{
			// Update state
			this.visibleState = false;
			
			// Show palette
			this.htmlPalette.stop().fadeOut(100);
			
			return true;
		}
		
		// Initiate event listeners
		private initListeners():void
		{
			var timer = null;
				
			// Change value is changed
			this.fldInput.change((e:Event) =>
			{
				this.updateSample(this.fldInput.val());
			});
				
			// When is typed
			this.fldInput.keyup((e:Event) =>
			{
				// Clear old timer
				if (timer)
					window.clearTimeout(timer);
				
				// Update with a delay
				timer = window.setTimeout(() =>
				{
					timer = null;
					this.fldInput.trigger('change');
					
				}, 300);
			});
			
			// When blured
			this.fldInput.blur((e:Event) =>
			{
				// Get field value
				let value = this.fldInput.val();
				
				// Add hash to value
				if (value.length === 6 && value.indexOf('#') === -1)
					this.fldInput.val('#' + value);
			});
		}
		
		// Show palette
		private showPalette():boolean
		{
			// Already visible
			if (this.visibleState === true)
				return false;
			
			// Build once
			if (!this.hasOwnProperty('htmlPalette'))
				this.buildPalette();
			
			// Update state
			this.visibleState = true;
			
			// Unselect previous color
			this.htmlPalette.
				children('a.is-selected:first').
				removeClass('is-selected');

			// Select current color
			if (this.getDataValue() !== null)
			{
				this.htmlPalette.
					children('a[data-color="' + this.getDataValue() + '"]:first').
					addClass('is-selected');
			}
			
			// Current field position
			let pos = this.fldInput.offset();
			
			// Align palette
			this.htmlPalette.css({
				left : pos.left,
				top  : pos.top + 40
			});
			
			// Show palette
			this.htmlPalette.stop().fadeIn(100);
			
			// When clicked anywhere after this 
			// click event has passed
			window.setTimeout(() =>
			{
				$(window).one('click', () =>
				{
					this.hidePalette();
				});
				
			}, 10);
			
			return true;
		}
		
		// Update current sample color
		private updateSample(value:string):void
		{
			// Clean up value
			value = '#' + jQuery.trim(
				value.toLowerCase().split('#').join('')
			);
			
			// Valid length
			if (value.length === 7)
			{
				this.setDataValue(value);
				this.htmlSample.css('background-color', value);
			}
			// Invalid length
			else
			{
				this.setDataValue(null);
				this.htmlSample.css('background-color', 'transparent');
			}
		}
	}
}