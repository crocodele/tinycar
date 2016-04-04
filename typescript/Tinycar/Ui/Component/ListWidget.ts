module Tinycar.Ui.Component
{
	export class ListWidget extends Tinycar.Main.Component
	{
		private htmlList:JQuery;
		private linkPattern:string;
		
		// @see Tinycar.Main.Component.buildContent()
		buildContent():void
		{
			super.buildContent();
			
			// Build heading
			if (this.Model.hasString('heading'))
				this.buildHeading();
			
			// Build list container
			this.buildList();
			
			// Resolve data item link pattern
			if (this.Model.isObject('link'))
			{
				this.linkPattern = Tinycar.Url.getAsPath(
					this.Model.get('link')
				);
			}
			
			// Load list content
			this.loadList();
		}
		
		// Build heading
		private buildHeading():void
		{
			$('<div>').
				attr('class', 'heading').
				text(this.Model.get('heading')).
				appendTo(this.htmlRoot);
		}
		
		// Build list container
		private buildList():void
		{
			this.htmlList = $('<div>').
				attr('class', 'list').
				appendTo(this.htmlRoot);
		}
		
		// Build single list item
		private buildListItem(data:Object):void
		{
			let container;
			
			// Target item id
			let id = data['id']['value'];
			
			// Row contains a linke
			if (typeof this.linkPattern === 'string')
			{
				// Create container
				container = $('<a>').
					attr('href', this.linkPattern.split('$item.id').join(id)).
					attr('class', 'item').
					appendTo(this.htmlList);
				
				// When clicked
				container.click((e:JQueryKeyEventObject) => 
				{
					if (e.ctrlKey === false && e.shiftKey === false)
					{
						e.preventDefault();
						Tinycar.Url.openUrl(container.attr('href'));
					}
				});
			}
			// Row is static
			else
			{
				// Create container
				container = $('<span>').
					attr('class', 'item').
					appendTo(this.htmlList);
			}
			
			// Create columns
			this.Model.get('columns').forEach((column:Object) => 
			{
				container.append(this.buildListItemColumn(column, data));
			});
		}
		
		// Build single list item's column value
		private buildListItemColumn(column:Object, data:Object):JQuery
		{
			// Create content holder
			let container = $('<span>').
				text(data[column['name']]['text']).
				attr('class', 'column');
			
			// Set container width
			if (column['width'] > 0)
				container.css('width', column['width']);
			
			return container;
		}
		
		// Load list
		private loadList():void
		{
			// Load data from action
			this.action('data', {}, (data:Array<Object>) => 
			{
				// Clear existing items
				this.htmlList.empty();

				// Create new items
				data.forEach((item:Object) =>
				{
					this.buildListItem(item);
				});
			});
		}
		
		// @see Tinycar.Main.Component.refresh()   
		refresh():void
		{
			this.loadList();
		}
	}
}