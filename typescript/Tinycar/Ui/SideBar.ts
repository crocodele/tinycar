module Tinycar.Main
{
	export class SideBar
	{
		private htmlRoot:JQuery;
	
		private View:Tinycar.Main.View;
		private Model:Tinycar.Model.DataItem;
		
		
		// Initiate class
		constructor(view:Tinycar.Main.View, model:Object)
		{
			this.View = view;
			this.Model = new Tinycar.Model.DataItem(model);
		}
	
		// Build
		build():JQuery
		{
			// Build elements
			this.buildRoot();
			this.buildComponents();
			
			return this.htmlRoot;
		}
		
		// Build components
		private buildComponents():void
		{
			// Create container
			let container = $('<div>').
				attr('class', 'components').
				appendTo(this.htmlRoot);
			
			// Create components
			this.Model.get('components').forEach((data:Object) =>
			{
				// Create component
				let instance = this.View.createComponent(data);
				container.append(instance.build());
				
				// Show component
				instance.setAsVisible(true);
			});
		}
		
		// Build root container
		private buildRoot():void
		{
			this.htmlRoot = $('<div>').
				attr('id', 'tinycar-main-sidebar').
				attr('class', 'theme-base');
		}
	}
}