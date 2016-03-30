module Tinycar.Ui.Component
{
	export class Group extends Tinycar.Main.Field
	{
		// Build content
		buildContent()
		{
			super.buildContent();
			
			// Build as a table
			if (this.isTable())
				this.buildTable();
			
			// Build as a list
			else
				this.buildList();
		}
		
		// Build a component
		private buildComponent(item:Object):JQuery
		{
			// Create new model instance
			let model = new Tinycar.Model.DataItem(item);
				
			// Target component type
			let type = model.get('type_name');
				
			// Inherit tab name from group
			model.set('tab_name', this.Model.get('tab_name'));
				
			// Create new instance
			let instance = new Tinycar.Ui.Component[type](
				this.App, this.View, model
			);
				
			// Build instance
			let container = instance.build();
				
			// Add to list of components
			this.View.addComponent(instance);
			
			return container;
		}
		
		// Build components as a list
		private buildList():void
		{
			this.Model.get('components').forEach((item:Object) =>
			{
				this.htmlContent.append(this.buildComponent(item));
			});
		}
		
		// Build as a table
		private buildTable():void
		{
			// Number of columns to use
			var columns = this.Model.getNumber('columns');
			var components = this.Model.getList('components');
			
			// Create first row
			var container = $('<div>').
				attr('class', 'row').
				appendTo(this.htmlContent);
			
			// Create components only for this tab
			components.forEach((item:Object, index:number) =>
			{
				// Add new columns container
				if (index % columns === 0)
				{
					container = $('<div>').
						attr('class', 'row').
						appendTo(this.htmlContent);
				}
				
				// Build component to container
				container.append(this.buildComponent(item));
			});
			
			// Add empty table cells to the end
			for (let i = components.length; i % columns !== 0; ++i)
			{
				// Container
				let item = $('<div>').
					addClass('tinycar-main-component').
					addClass('tinycar-main-field').
					appendTo(container);
				
				// Label
				$('<div>').
					attr('class', 'type-label').
					appendTo(item);
			}
		}
		
		// @see Tinycar.Main.Field.getRootStyles()
		getRootStyles():Array<string>
		{
			let result = super.getRootStyles();
			
			// Group style
			result.push('layout-' + this.Model.get('layout'));
			result.push('columns-' + this.Model.get('columns'));
			
			return result;
		}
		
		
		// Check if this group should be displayed as a table
		private isTable():boolean
		{
			return (
				this.Model.get('layout') === 'table' &&
				this.Model.getNumber('columns') > 1
			);
		}
	}
}