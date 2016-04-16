module Tinycar.Ui.Component
{
	export class DateTime extends Tinycar.Main.Field
	{
		private htmlField:JQuery;
		private fldInput:JQuery;
		private hasPicker:boolean = false;
		private lastValue:string;
	
	
		// Build content
		buildContent()
		{
			// Build elements
			super.buildContent();
			this.buildField();
			this.buildButton();
			this.buildLine();
		}
		
        // Build datepicker field and behaviour
		private buildDatePicker():void
        {
			// Load external vendor
			Tinycar.Page.loadVendor('jqueryui', () =>
			{
				// Re-set instance to add loaded functionality
				this.fldInput = $(this.fldInput);
				
				// Build datapicker and show it
				this.fldInput.datepicker(this.getDatePickerConfig());
				this.fldInput.datepicker('show');
				this.fldInput.blur();
			
	            // Update state
	            this.hasPicker = true;
			});
        }
		
		// Build button
		private buildButton():void
        {
			// Create new button instance
			let instance = new Tinycar.Ui.Button({
				style : 'field-icon',
				icon  : 'date'
			});
			
			// When clicked
			instance.setHandler('click', () =>
			{
                // Build datepicker now that we need it
                if (this.hasPicker === false)
                	this.buildDatePicker();
                
                // Show it straight away
                else
                {
                	this.fldInput.datepicker('show');
                	this.fldInput.blur();
                }				
			});
			
			// Add to content
			this.htmlField.append(instance.build());
        }
		
		// Build field
		private buildField():void
		{
            // Create container
            this.htmlField = $('<div>').
            	attr('class', 'field').
                appendTo(this.htmlContent);

            // Create input node
            this.fldInput = $('<input>').
            	attr('type', 'text').
                attr('placeholder', this.Model.get('placeholder')).
                keyup((e:Event) =>
                {
                	this.updateValueFromInput();
                }).
                change((e:Event) =>
                {
                	this.updateValueFromInput();
                }).
                appendTo(this.htmlField);
            
            // Formatting has a timestamp
            if (this.hasDateTimeFormat() === true)
            	this.htmlField.addClass('has-datetime');
            
            // Set initial value
            if (this.getDataValue() !== null)
            {
            	// Format timestmp into value
            	this.lastValue = Tinycar.Format.toDate(
                	this.getDataValue(), 
                	this.Model.get('data_format')
                );
            	
            	// Update field value
            	this.fldInput.prop('value', this.lastValue);
            }
		}
		
		// Build line separator
		private buildLine():void
		{
			$('<span>').
				attr('class', 'line').
				appendTo(this.htmlContent);
		}
		
		// @see Tinycar.Main.Field.focus()
		focus():void
		{
			this.fldInput.focus();
		}
		
		// Get configuratoin for datepicker
		private getDatePickerConfig():Object
		{
			// Desired field format
			let format = this.Model.get('data_format');
			
			// Turn our date format into datepicker's format
			format = this.getDatePickerFormat(format);

			// Resolve configuration
            let result = {

            	showOn         : 'button',
                constrainInput : false,
                dateFormat     : format,
                dayNames       : [],
                dayNamesMin    : [],
                firstDay       : Tinycar.Locale.getCalendar('first_weekday'),
                monthNames     : [],
                nextText       : Tinycar.Locale.getText('calendar_next_month'),
                prevText       : Tinycar.Locale.getText('calendar_prev_month'),
                showWeek       : Tinycar.Locale.getCalendar('show_weeks'),
                weekHeader     : '',

                // Before picker is displayed
                beforeShow : (input:Object, ui:Object) =>
                {
                	let value = this.getDataValue();

                	// Update datepicker with current date, 
                	// but restore the field value to keep
                	// the current time of day 
                    if (value > 0)
                    {
                    	let value = this.fldInput.val();
                    	
                    	this.fldInput.datepicker('setDate', 
                    		new Date(value * 1000)
                    	);
                    	
                    	this.fldInput.val(value);
                    }
           		},

                // When a date is selected
           		onSelect : (date:Object, ui:Object) =>
           		{
                	// Update value
                    this.updateValueFromInput();
           		},

                // When picker is closed
           		onClose : (date:Object, ui:Object) =>
           		{
           			// Update value
           			this.updateValueFromInput();
           		}
            };

            // Add localized daynames
            for (let i = 0; i <= 6; ++i)
            {
            	let name = Tinycar.Locale.getText('calendar_day_' + i);
            	
            	result.dayNames.push(name);
            	result.dayNamesMin.push(name.substring(0, 1));
            }
            
            // Add localized monthnames
            for (var i = 1; i <= 12; ++i)
            	result.monthNames.push(Tinycar.Locale.getText('calendar_month_' + i));
            
            return result;
		}
		
		// Get format as datepicker format
		private getDatePickerFormat(format:string):string
		{
			// Supported formatting properties
            format = format.replace('d', 'dd');
            format = format.replace('m', 'mm');
            format = format.replace('j', 'd');
            format = format.replace('n', 'm');
            format = format.replace('Y', 'yy');
            format = format.replace('H', '00');
            format = format.replace('i', '00');
            
            return format;
		}
		
        // Update value to match input text
		private updateValueFromInput():boolean
		{
			// Get active field value
			let value = this.fldInput.val();
			
            // Field value has not change since
            // it was last parsed into a timestamp
            if (this.lastValue === value)
            	return false;
            
            // Try to convert to a unix timestamp
           	let time = Tinycar.Format.toTime(value);

            // We had a string but failed get timestamp
            if (value.length > 0 && time === null)
            	return false;

            // Update cache
            this.lastValue = value;

            // Update value
            this.setDataValue(time);

            return true;
		}
		
		// @see Tinycar.Main.Field.getDataValue()
		getDataValue():number
		{
			return (this.Model.isNumber('data_value') ?
				this.Model.get('data_value') : null
			);
		}
		
		// Check to see if formatting contains both a 
		// date and a timestamp
		hasDateTimeFormat():boolean
		{
			return (this.Model.get('data_format').indexOf('H') > 5);
		}
		
		// @see Tinycar.Main.Field.setDataValue()
		setDataValue(value:number):void
		{
			this.Model.set('data_value', value);
		}
		
	}
	
}