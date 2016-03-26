module Tinycar.Ui
{
	export class Application
	{
		private appName:string;
		private htmlRoot:JQuery;
		private viewName:string;

		Dialog:Tinycar.Ui.Dialog;
		Model:Tinycar.Model.DataItem;
		View:Tinycar.Ui.View;
	
		
		// Initiate class
		constructor(name:string)
		{
			this.appName = name;
			this.Model = new Tinycar.Model.DataItem({});
		}
		
		// Build
		build():JQuery
		{
			// Build elements
			this.buildRoot();
			
			return this.htmlRoot;
		}
		
		// Close dialog
		closeDialog():void
		{
			// Reset reference
			this.Dialog = null;
			
			// Refresh visible components
			this.View.refreshComponents();
		}
		
		// Get application id
		getId():string
		{
			return this.Model.get('id');
		}
		
		// Load specified view
		loadView(view:string):void
		{
			// Load contents
			Tinycar.Api.call({
				service : 'application.view',
				params  : {
					app  : this.appName,
					view : view,
					url  : Tinycar.Url.getParams()
				},
				success : (data) =>
				{
					// Add application-specific translations
					Tinycar.Locale.loadText(data.text);
					
					// Create application model
					this.Model = new Tinycar.Model.DataItem(data.app);
					
					// Create view UI instance
					this.View = new Tinycar.Ui.View(this, data.view);
					
					// Build UI view
					this.htmlRoot.append(this.View.build());
					
					// Update layout name
					Tinycar.System.Page.setLayoutName(
						this.Model.get('layout_name')
					);
					
					// Main layout has sidebar
					if (this.Model.get('layout_name') === 'main')
						Tinycar.System.Side.update(this);
					
					// Set system theme color
					Tinycar.System.Page.setThemeColors(
						this.Model.get('colors')
					);
					
					// Set content as rendered
					window.setTimeout(() =>
					{
						// Set page state as rendered
						Tinycar.System.Page.setState('rendered');
						
						// Adding new item, autofocus
						if (!Tinycar.Url.hasParam('id'))
							this.View.focus();
						
					}, 100);
				}
			});
		}

		// Build root container
		private buildRoot():void
		{
			this.htmlRoot = $('<div>').
				attr('class', 'tinycar-ui-application');
		}
		
		// Open dialog
		openDialog(name:string):Tinycar.Ui.Dialog
		{
			// @todo: show loading state
			
			// Show mask
			Tinycar.System.Mask.show();
			
			// Default application id
			let app = this.getId();

			// We have a custom application name
			if (name.indexOf(':') > -1)
			{
				app  = name.split(':').shift();
				name = name.split(':').pop();
			}
			
			// Create dialog instance
			this.Dialog = new Tinycar.Ui.Dialog(this, {
				app  : app,
				name : name
			});
			
			// Build dialog
			this.htmlRoot.append(this.Dialog.build());
			
			// Load dialog contents
			this.Dialog.load(this.View.getComponentsData());
			
			return this.Dialog;
		}
	}
}