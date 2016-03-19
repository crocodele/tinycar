module Tinycar.Ui.Component
{
	export class SelectList extends Tinycar.Main.Field
	{
		private fldList:JQuery;
	
	
		// Build content
		buildContent()
		{
			// Build elements
			super.buildContent();
			this.buildList();
		}
		
		// Build textarea field
		private buildList():void
		{
			// Create container
			let container = $('<div>').
				attr('class', 'list').
				appendTo(this.htmlContent);
			
			// Add arrow symbol
			$('<span>').
				attr('class', 'icon icon-small icon-sort-down').
				appendTo(container);
			
			// Add select menu
			this.fldList = $('<select>').
				attr('size', 1).
				appendTo(container);
			
			// Create options
			this.Model.get('options').forEach((item:Object) =>
			{
				$('<option>').
					attr('value', item['name']).
					text(item['label']).
					appendTo(this.fldList);
			});
				
			// When value is changed
			this.fldList.change((e:Event) =>
			{
				this.setDataValue(this.fldList.val());
			});
			
			// Set initial value
			this.fldList.val(this.Model.get('data_value'));
		}
		
		// @see Tinycar.Main.Field.focus()
		focus():void
		{
			this.fldList.focus();
		}
	}
}