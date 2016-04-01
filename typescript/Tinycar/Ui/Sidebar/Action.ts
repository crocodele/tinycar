module Tinycar.Ui.Sidebar
{
	export class Action
	{
		htmlRoot:JQuery;
	
		Model:Tinycar.Model.DataItem;
		View:Tinycar.Ui.View;
	

		// Initiate class
		constructor(view:Tinycar.Ui.View, data:Object)
		{
			this.Model = new Tinycar.Model.DataItem(data);
			this.View = view;
		}
	
	
		// Build
		build():JQuery
		{
			// Build elements
			this.buildRoot();
			this.buildIcon();
			this.buildLabel();
			
			return this.htmlRoot;
		}
		
		// Build icon
		private buildIcon():void
		{
			$('<span>').
				attr('class', 'icon icon-lite icon-small icon-' + this.Model.get('type')).
				appendTo(this.htmlRoot);
		}
	
		// Build label
		private buildLabel():void
		{
			// Label string
			let label = this.Model.get('label');
			
			// This action opens a dialog
			if (this.Model.hasString('dialog'))
				label += '...';
			
			// Create container
			let container = $('<span>').
				attr('class', 'label').
				appendTo(this.htmlRoot);
			
			// Add label
			container.text(label);
			
			// Add icon
			$('<span>').
				attr('class', 'icon icon-small icon-' + this.Model.get('type')).
				appendTo(container);
		}
		
		// Build root container
		private buildRoot():void
		{
			// Create container
			this.htmlRoot = $('<a>').
				attr('href', '#' + this.Model.get('type')).
				addClass('tinycar-ui-sidebar-action');
			
			// This is a static link
			if (this.isLinkAction())
			{
				this.htmlRoot.attr('href', 
					Tinycar.Url.getAsPath(this.Model.get('link'))
				);
			}
			
			// When clicked
			this.htmlRoot.click((e:JQueryMouseEventObject) =>
			{
				if (e.ctrlKey === false && e.shiftKey === false)
				{
					e.preventDefault();
					this.execute();
					this.htmlRoot.blur();
				}
			});
		}
		
		// Execute action
		execute():boolean
		{
			// Expand or collapse list
			if (this.isListAction())
			{
				this.View.App.toggleSideList();
				return true;
			}
			
			// Open specified dialog
			if (this.Model.hasString('dialog'))
			{
				this.View.openDialog(this.Model.get('dialog'));
				return true;
			}
			
			// Save current view
			if (this.isSaveAction())
			{
				this.View.onSave(new Tinycar.Model.DataItem({
					link  : this.Model.get('link'),
					toast : this.Model.get('toast')
				}));
				
				return true;
			}
			
			// Call remote service
			if (this.isServiceAction())
			{
				this.View.callAction(this.Model);
				return true;
			}
			
			// Move to target URL
			if (this.isLinkAction())
			{
				Tinycar.Url.openUrl(this.htmlRoot.attr('href'));
				return true;
			}
			
			return false;
		}

		// Get action type
		getType():string
		{
			return this.Model.get('type');
		}
		
		// Check if we have a link URL
		hasLinkUrl():boolean
		{
			return this.Model.isObject('link');
		}
		
		// Check if we have a custom toast message
		hasToastMessage():boolean
		{
			return this.Model.hasString('toast');
		}
		
		// Check if this a static link action
		isLinkAction():boolean
		{
			return (
				!this.isSaveAction() &&
				!this.isServiceAction() && 
				this.Model.isObject('link')
			);
		}
		
		// Check if this is a list action
		isListAction():boolean
		{
			return (this.Model.get('type') === 'list');
		}
		
		// Check if this is an action for saving
		isSaveAction():boolean
		{
			return (this.Model.get('type') === 'save');			
		}
		
		// Check if this is a session action
		isSessionAction():boolean
		{
			return (this.Model.get('target') === 'session');
		}
		
		// Check if this an action with a remote service
		isServiceAction():boolean
		{
			return this.Model.hasString('service');
		}
		
		// Check if this is a view action
		isViewAction():boolean
		{
			return (this.Model.get('target') === 'view');
		}
	}
}