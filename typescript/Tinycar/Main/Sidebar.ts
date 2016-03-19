module Tinycar.Main
{
	export class Sidebar
	{
		private htmlIcon:JQuery;
		private htmlRoot:JQuery;
		private sessionActions:Array<Tinycar.Ui.Sidebar.Action> = [];
		private systemActions:Array<Tinycar.Ui.Sidebar.Action> = [];
		private viewActions:Array<Tinycar.Ui.Sidebar.Action> = [];
	
		// Build
		build():JQuery
		{
			this.buildRoot();
			return this.htmlRoot;
		}
		
		// Add action to sidebar
		addAction(action:Tinycar.Ui.Sidebar.Action):void
		{
			// This action is for saving, let's add
			// calling it to the global key listeners
			if (action.isSaveAction())
			{
				Tinycar.System.addEvent('ctrl+s', () => 
				{
					action.execute();
				});
			}
			
			// View action
			if (action.isViewAction())
				this.viewActions.push(action);
			
			// Session action
			else if (action.isSessionAction())
				this.sessionActions.push(action);

			// System action
			else
				this.systemActions.push(action);
		}
		
		// Build application icon
		private buildAppIcon():void
		{
			// Create container
			let container = $('<div>').
				attr('class', 'app-icon').
				appendTo(this.htmlRoot);
			
			// Add icon
			this.htmlIcon = $('<span>').
				appendTo(container);
		}
		
		// Build root container
		private buildRoot():void
		{
			this.htmlRoot = $('<div>').
				attr('id', 'tinycar-main-sidebar').
				attr('class', 'theme-base');
		}
		
		// Build session actions container
		private buildSessionActions():void
		{
			// Create container
			let container = $('<div>').
				attr('class', 'session-actions').
				appendTo(this.htmlRoot);
			
			// Add actions
			this.sessionActions.forEach((action:Tinycar.Ui.Sidebar.Action) =>
			{
				container.append(action.build());
			});
		}
		
		// Build system actions container
		private buildSystemActions():void
		{
			// Create container
			let container = $('<div>').
				attr('class', 'system-actions').
				appendTo(this.htmlRoot);
			
			// Add actions
			this.systemActions.forEach((action:Tinycar.Ui.Sidebar.Action) =>
			{
				container.append(action.build());
			});
		}
		
		// Build view actions container
		private buildViewActions():void
		{
			// Create container
			let container = $('<div>').
				attr('class', 'view-actions').
				appendTo(this.htmlRoot);
			
			// Add actions
			this.viewActions.forEach((action:Tinycar.Ui.Sidebar.Action) =>
			{
				container.append(action.build());
			});
		}
		
		// Update contents
		update(app:Tinycar.Ui.Application):void
		{
			// Build application icon
			this.buildAppIcon();
			
			// Build system actions
			if (this.systemActions.length > 0)
				this.buildSystemActions();
			
			// Build view actions
			if (this.viewActions.length > 0)
				this.buildViewActions();
			
			// Build session actions
			if (this.sessionActions.length > 0)
				this.buildSessionActions();
			
			// Show container
			this.htmlRoot.show();
			
			// Update icon image
			if (app.Model.get('icon') !== null)
			{
				this.htmlIcon.css('background-image', 
					'url(' + app.Model.get('icon') + ')'
				);
			}
		}
	}
}