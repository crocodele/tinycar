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
			// Create components only for this tab
			this.Model.get('components').forEach((item:Object) =>
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
				
				// Build to components list
				this.htmlContent.append(instance.build());
				
				// Add to list of components
				this.View.addComponent(instance);
			});
		}
	}
}