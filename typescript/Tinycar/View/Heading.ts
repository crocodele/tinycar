module Tinycar.View
{
	export class Heading
	{
		private htmlRoot:JQuery;
		private Model:Tinycar.Model.DataItem;
	
	
		// Initiate class
		constructor(model:Tinycar.Model.DataItem)
		{
			this.Model = model;
		}
		
		// Build
		build():JQuery
		{
			// Build elements
			this.buildRoot();
			this.buildHeading();
			this.buildDetails();
			
			return this.htmlRoot;
		}
		
		// Build details
		private buildDetails():void
		{
			// Create container
			let container = $('<div>').
				attr('class', 'details').
				appendTo(this.htmlRoot);
			
			// We have creation time
			if (this.Model.hasNumber('created_time'))
			{
				$('<span>').
					text(this.getDateLabel('created_time')).
					appendTo(container);
			}
			
			// We have modified time
			if (this.Model.hasNumber('modified_time'))
			{
				$('<span>').
					text(this.getDateLabel('modified_time')).
					appendTo(container);
			}

		}
		
		// Build heading
		private buildHeading():void
		{
			$('<strong>').
				text(this.Model.get('heading')).
				appendTo(this.htmlRoot);
		}
		
		// Build root container
		private buildRoot():void
		{
			this.htmlRoot = $('<div>').
				attr('class', 'tinycar-view-heading');
		}
		
		// Get date label for specified property
		private getDateLabel(name:string):string
		{
			// Format timestamp 
			let value = Tinycar.Locale.toDate(
				this.Model.get(name), 'datetime'
			);
			
			// Add timestamp to text
			return Tinycar.Locale.getText(
				'view_' + name, {time:value}
			);
		}
	}
}