module Tinycar.Ui.Component
{
	export class Password extends Tinycar.Main.Field
	{
	    private fldDummy:JQuery;
		private fldInput:JQuery;
	
	
		// Build content
		buildContent()
		{
			// Build elements
			super.buildContent();
			this.buildInput();
			
			// Initiate listener
			this.initListeners();
		}
		
		// Build input field
		private buildInput():void
		{
            // We have an custom string value to prefill, add
		    // a dummy field for the browser to autofill
		    if (this.Model.isString('data_value'))
		    {
		        $('<input>').
                    attr('type', 'password').
                    css('display', 'none').
                    appendTo(this.htmlContent);
		    }
            
			// Create field
			this.fldInput = $('<input>').
				attr('id', this.getFieldId()).
				attr('type', 'password').
				attr('placeholder', this.Model.getString('placeholder')).
				appendTo(this.htmlContent);
			
			// Align text to center
			if (this.Model.get('align') === 'center')
			    this.fldInput.css('text-align', 'center');
		}
		
		// @see Tinycar.Main.Field.focus()
		focus():void
		{
			this.fldInput.focus();
		}
		
		// Initiate listeners
		private initListeners():void
		{
			var timer = null;
			
			// Change value is changed
			this.fldInput.change((e:Event) =>
			{
				// Update current value
				this.setDataValue(this.fldInput.val());
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
					this.fldInput.trigger('change');
					
				}, 300);
			});			
		}
	}
}