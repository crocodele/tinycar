module Tinycar.Ui
{
	export class Application
	{
		private appName:string;
		private htmlRoot:JQuery;
		private viewName:string;

		Dialog:Tinycar.Ui.Dialog;
		Model:Tinycar.Model.DataItem;
		Side:Tinycar.Ui.SideList;
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
		
		// Get specified application theme color varionta
		getThemeColor(name:string):string
		{
			let colors = this.Model.getObject('colors');
			
			if (!colors.hasOwnProperty(name))
				return '#000000';
			
			return colors[name];
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
				success : (data:Object) =>
				{
					// Create application model
					this.Model = new Tinycar.Model.DataItem(data['app']);
					
					// Add application-specific translations
					Tinycar.Locale.loadText(data['text']);
					
					// Create view UI instance
					this.View = new Tinycar.Ui.View(this, data['view']);
					
					// Create sidebar UI instance
					if (data.hasOwnProperty('side'))
					{
						// Create sidelist and add it to application
						this.Side = new Tinycar.Ui.SideList(this, data['side']);
						this.htmlRoot.append(this.Side.build());
						
						// Update layout style
						Tinycar.Page.addStyle('has-sidelist');
					}
					
					// Add view UI to application
					this.htmlRoot.append(this.View.build());
					
					// Update layout name
					Tinycar.Page.setLayoutName(
						this.Model.get('layout_name')
					);
					
					// Main layout has sidebar
					if (this.Model.get('layout_name') === 'main')
						Tinycar.System.Side.update(this);
					
					// Set system theme color
					Tinycar.Page.setThemeColors(this.Model.get('colors'));
					
					// Set content as rendered
					window.setTimeout(() =>
					{
						// Set page state as rendered
						Tinycar.Page.setState('rendered');
						
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
		
		// Toggle sidelist open or close
		toggleSideList():void
		{
			// No sidelist available
			if (this.Side instanceof Tinycar.Ui.SideList)
				this.Side.setAsVisible(!this.Side.isVisible());
		}
	}
}