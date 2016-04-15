module Tinycar.Ui
{
	interface ISettingsData
	{
		visible:boolean;
	}
	
	export class SideList
	{
		private htmlComponents:JQuery;
		private htmlRoot:JQuery;
		private settingsId:string;
	
		// Initial settings
		private settingsData:ISettingsData = {
			visible : true
		};

		App:Tinycar.Ui.Application;
		Model:Tinycar.Model.DataItem;
	
		// Initiate class
		constructor(app:Tinycar.Ui.Application, model:Object)
		{
			this.App = app;
			this.Model = new Tinycar.Model.DataItem(model);
		}
	
		// Build
		build():JQuery
		{
			// Build elements
			this.buildRoot();
			this.buildHeading();
			this.buildComponents();
			
			// Load settings from session
			this.loadSettings();
			
			// Set initial state
			if (this.isVisible())
				Tinycar.Page.addStyle('sidelist-visible');
			
			return this.htmlRoot;
		}
		
		// Build components container
		private buildComponents():void
		{
			// Create container
			this.htmlComponents = $('<div>').
				attr('class', 'components').
				appendTo(this.htmlRoot);
			
			// Create components only for this tab
			this.Model.get('components').forEach((item:Object) =>
			{
				// Create new instance
				let instance = this.App.View.createComponent(item);
				this.htmlComponents.append(instance.build());
				
				// Show component
				instance.setAsVisible(true);
			});
		}
		
		// Build heading
		private buildHeading():void
		{
			let instance = new Tinycar.View.Heading(this.Model);
			this.htmlRoot.append(instance.build());
		}
		

		// Build root container
		private buildRoot():void
		{
			this.htmlRoot = $('<div>').
				addClass('tinycar-ui-sidelist');
		}
		
		// Check if sidelist is currently visible
		isVisible():boolean
		{
			return this.settingsData.visible;
		}
		
		// Load settings
		private loadSettings():void
		{
			// Resolve settings id
			this.settingsId = 'sidelist-' + Tinycar.Url.getUid('app'); 
				
			// Get existing settings from session
			let settings = Tinycar.Session.get(this.settingsId);

			// Set custom visibility
			if (settings.hasOwnProperty('visible'))
				this.settingsData.visible = (settings['visible'] === true);
		}
		
		// Set sidelist as visible or not visible
		setAsVisible(status:boolean):void
		{
			// Update style
			if (status)
				Tinycar.Page.addStyle('sidelist-visible');

			else
				Tinycar.Page.clearStyle('sidelist-visible');
			
			// Store settings
			this.settingsData.visible = status;
			this.storeSettings();
		}
		
		// Store current settings
		private storeSettings():void
		{
			// Update settings to session
			Tinycar.Session.set(
				this.settingsId, this.settingsData
			);
		}
	}
}