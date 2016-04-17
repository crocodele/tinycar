module Tinycar.Ui.Component
{
	interface IItems
	{
		[key:number]:JQuery;
	}
	
	export class Reference extends Tinycar.Main.Field
	{
		private htmlItems:IItems = {};
		private htmlList:JQuery;
		
		
		// Add new item to list
		private addListItem(item:Object):void
		{
			// Target id
			let id = item['id'];
			
			// Find last property's name
			for (var name in item)
			{
			}
			
			// Create container
			let container = $('<div>').
				attr('class', 'item').
				text(item[name]).
				appendTo(this.htmlList);
			
			// Add remove icon
			let close = $('<a>').
				attr('class', 'close').
				click((e:Event) =>
				{
					e.preventDefault();
					this.clearListItem(id);
				}).
				appendTo(container);
			
			// Add icon
			$('<span>').
				attr('class', 'icon icon-tiny icon-close').
				appendTo(close);
			
			// Update reference
			this.htmlItems[id] = container;
		}
		
		// Build button
		private buildButton():void
		{
			// Create button instance
			let instance = new Tinycar.Ui.Button({
				style : 'theme-icon',
				icon  : 'menu-horizontal',
				size  : 'tiny'
			});

			// When clicked
			instance.setHandler('click', () =>
			{
				// Open target dialog
				let dialog = this.App.openDialog(
					this.Model.get('data_dialog')
				);
				
				// Initial component data
				dialog.addDataForComponentType('DataGrid', {
					data_value : this.Model.getList('data_value'),
					data_limit : this.Model.getNumber('data_limit')
				});
				
				// When dialog requsts values
				dialog.setHandler('values', ():Array<number> =>
				{
					return this.getDataValue();
				});
				
				// When dialog selected
				dialog.setHandler('select', (list:Array<number>) =>
				{
					this.setDataValue(list);
					this.updateListItems();
				});

			});
			
			// Add to content
			this.htmlContent.append(instance.build());
		}
	
		// Build content
		buildContent()
		{
			// Build elements
			super.buildContent();
			this.buildListItems();
			this.buildButton();
		}
		
		// Build items
		private buildListItems():void
		{
			// Create container
			this.htmlList = $('<div>').
				attr('class', 'list').
				appendTo(this.htmlContent);
			
			// Add new items
			this.Model.get('type_items').forEach((item:Object) =>
			{
				this.addListItem(item);
			});
		}
		
		// Clear list item
		private clearListItem(id:number):void
		{
			// Remove target list item
			if (typeof this.htmlItems[id] !== 'undefined')
			{
				this.htmlItems[id].remove();
				delete this.htmlItems[id];
			}

			// Find value from current value
			let value = this.getDataValue();
			let index = value.indexOf(id);

			// Remove value from list
			if (index > -1)
				value.splice(index, 1);

			// Update value
			this.setDataValue(value);
		}
		
		
		// @see Tinycar.Main.Field.getDataValue()
		getDataValue():Array<number>
		{
			return this.Model.getList('data_value');
		}
		
		// @see Tinycar.Main.Field.setDataValue()
		setDataValue(value:Array<number>):void
		{
			this.Model.set('data_value', value);
		}
		
		// Update list items to match value
		private updateListItems():void
		{
			this.action('data', {value:this.getDataValue()}, (data:Array<Object>) =>
			{
				// Clear existing items
				this.htmlItems = {};
				this.htmlList.empty();
				
				// Add list items
				data.forEach((item:Object) =>
				{
					this.addListItem(item);
				});
			});
		}
	}
}