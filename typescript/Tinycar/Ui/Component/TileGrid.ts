module Tinycar.Ui.Component
{
	export class TileGrid extends Tinycar.Main.Component
	{
		private htmlTable:JQuery;
		private linkPattern:string;
		
		
		// Build content
		buildContent()
		{
			// Build elements
			this.buildTable();

			// Resolve data item link pattern
			this.linkPattern = Tinycar.Url.getAsPath(
				this.Model.getObject('link')
			);
			
			this.loadList();
		}
		
		// Build table data
		private buildData(data:Array<Object>):void
		{
			// Add items
			data.forEach((item:Object) =>
			{
				this.buildDataItem(item);
			});
		}
		
		// Build table data item
		private buildDataItem(item:Object):void
		{
			// Resolve target link PATH
			let link = this.linkPattern.
				split('$model.id').join(item['id']);
			
			// Create container
			let container = $('<a>').
				attr('href', link).
				attr('class', 'item').
				click((e:Event) => 
				{
					e.preventDefault();
					Tinycar.Url.openUrl(link);
				}).
				appendTo(this.htmlTable);
			
			// Add image
			$('<span>').
				attr('class', 'image').
				css('background-color', item['image_color']).
				css('background-image', 'url(' + item['image_data'] + ')').
				appendTo(container);
			
			// Add label
			$('<span>').
				attr('class', 'label').
				text(item['label']).
				appendTo(container);
			
			// Add description
			$('<span>').
				attr('class', 'description').
				text(item['description']).
				appendTo(container);
			
			// Add status name
			$('<span>').
				attr('class', 'status status-' + item['status_type']).
				text(item['status_label']).
				appendTo(container);
		}
		
		// Build table container
		private buildTable():void
		{
			// Create container
			this.htmlTable = $('<div>').
				attr('class', 'table').
				appendTo(this.htmlRoot);
		}
		
		// Load list
		private loadList():void
		{
			// Load data from action
			this.action('data', {}, (data:Array<Object>) => 
			{
				this.buildData(data);
			});
		}
	}
}