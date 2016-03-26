module Tinycar.Ui.Component
{
	export class Button extends Tinycar.Main.Component
	{
		// Build button
		private buildButton():void
		{
			// Parameters
			let params = {
				style : 'theme-icon',
				size  : 'tiny'
			};
			
			// We have a custom icon
			if (this.Model.hasString('icon'))
				params['icon'] = this.Model.get('icon');
			
			// Create button instance
			let instance = new Tinycar.Ui.Button(params);

			// When clicked
			instance.setHandler('click', () =>
			{
				// Call remote service
				this.action('click', this.View.getComponentsData(), (value:number) =>
				{
					this.View.onResponse(new Tinycar.Model.DataItem({
						value : value,
						toast : this.Model.get('toast')
					}));
				});
			});
		
			// Add to content
			this.htmlRoot.append(instance.build());
		}
	
		// Build content
		buildContent()
		{
			// Build elements
			this.buildButton();
		}
	}
}