module Tinycar.Ui.Component
{
	export class ActionList extends Tinycar.Main.Component
	{
	
	
		// Build content
		buildContent()
		{
			// Build elements
			super.buildContent();
			this.buildList();
		}
		
		// Build textarea field
		private buildList():void
		{
			// Create options
			this.Model.getList('options').forEach((item:Object) =>
			{
				this.htmlRoot.append(this.buildListItem(item));
			});
		}
		
		// Build list items
		private buildListItem(item:Object):JQuery
		{
			// Create model instance from data
			let model = new Tinycar.Model.DataItem(item);
			
			// Create container
			let container = this.buildListItemContainer(model);
			
			// Build icon and label
			container.append(this.buildListItemIcon(model));
			container.append(this.buildListItemLabel(model));
			
			// When clicked
			container.click((e:JQueryMouseEventObject) =>
			{
				// Trigger custom action
				if (this.triggerListItemAction(model))
				{
					e.preventDefault();
				}
				// Open fixed URL
				else if (model.isObject('link'))
				{
					if (e.ctrlKey === false && e.shiftKey === false)
					{
						e.preventDefault();
						Tinycar.Url.openUrl(container.attr('href'));
						container.blur();
					}
				}
			});
			
			
			// This action is for saving, let's add a keyboard
			// short listener to trigger it
			if (model.get('type') === 'save') 
			{
				Tinycar.System.addEvent('ctrl+s', () => 
				{
					container.trigger('click');
				});
			}
			
			return container;
		}
		
		// Build list item container
		private buildListItemContainer(model:Tinycar.Model.DataItem):JQuery
		{
			// Create container
			let container = $('<a>').
				attr('class', 'item');
		
			// Add link to custom path when we the link
			// is clearly intended as a static link
			
			if (!model.hasString('service') && model.isObject('link'))
			{
				let path = Tinycar.Url.getWithVars(
					model.get('link'), 
					{url:Tinycar.Url.getParams()}
				);
				
				container.attr('href', Tinycar.Url.getAsPath(path));
			}
		
			return container;
		}
		
		// Build list item icon
		private buildListItemIcon(model:Tinycar.Model.DataItem):JQuery
		{
			// Create container
			let container = $('<span>').
				attr('class', 'icon icon-small icon-lite icon-' + model.get('icon'));
			
			return container;
		}
		
		// Build list item lable
		private buildListItemLabel(model:Tinycar.Model.DataItem):JQuery
		{
			// Label string
			let label = model.get('label');
			
			// This action opens a dialog
			if (model.hasString('dialog'))
				label += '...';
			
			// Create container
			let container = $('<span>').
				attr('class', 'label').
				text(label);
			
			// Add label icon
			$('<span>').
				attr('class', 'icon icon-small icon-' + model.get('icon')).
				appendTo(container);
			
			return container;
		}
		
		// @see Tinycar.Main.Component.setAsVisible()
		setAsVisible(visible:boolean):void
		{
			// Update visibility only when we have list items
			if (this.Model.getList('options').length > 0)
				super.setAsVisible(visible);
		}
		
		// Trigger list item execution
		private triggerListItemAction(model:Tinycar.Model.DataItem):boolean
		{
			// Open dialog
			if (model.hasString('dialog'))
			{
				this.App.openDialog(model.get('dialog'));
				return true;
				
			}
			
			// Save action
			if (model.get('type') === 'save')
			{
				this.View.onSave(new Tinycar.Model.DataItem({
					link  : model.get('link'),
					toast : model.get('toast')
				}));
				
				return true;
			}
			
			// Expand or collapse list
			if (model.get('type') === 'list')
			{
				this.View.App.toggleSideList();
				return true;
			}
			
			// Call custom service
			if (model.hasString('service'))
			{
				this.View.callAction(model);
				return true;
			}
			
			return false;
		}
	}
}