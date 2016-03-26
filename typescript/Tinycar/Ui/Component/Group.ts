module Tinycar.Ui.Component
{
	export class Group extends Tinycar.Main.Field
	{
		// Build content
		buildContent()
		{
			// Build elements
			super.buildContent();
			this.buildComponents();
		}
		
		// Build group components
		private buildComponents():void
		{
			// Default container
			var container = this.htmlContent;
			
			// Number of columns to use
			var columns = this.Model.getNumber('columns');
			
			// Create first row
			if (columns > 0)
			{
				container = $('<div>').
					attr('class', 'row').
					appendTo(this.htmlContent);
			}
			
			// Create components only for this tab
			this.Model.get('components').forEach((item:Object, index:number) =>
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
				
				// Add new columns container
				if (columns > 0 && index % columns === 0)
				{
					container = $('<div>').
						attr('class', 'row').
						appendTo(this.htmlContent);
				}
				
				// Build to components list
				container.append(instance.build());
				
				// Add to list of components
				this.View.addComponent(instance);
			});
		}
		
		// @see Tinycar.Main.Field.getRootStyles()
		getRootStyles():Array<string>
		{
			let result = super.getRootStyles();
			
			// Group format style
			result.push('format-' + this.Model.get('format'));
			result.push('columns-' + this.Model.get('columns'));
			
			return result;
		}
	}
}