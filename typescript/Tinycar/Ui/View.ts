module Tinycar.Ui
{
	interface ITabFields
	{
		[key:string]:Array<number>;
	}
	
	export class View extends Tinycar.Main.View
	{
		private htmlComponents:JQuery;
		private htmlHeading:JQuery;
		private htmlRoot:JQuery;
		private tabFields:ITabFields = {};
	
		Tabs:Tinycar.View.Tabs;
	
		// Build
		build():JQuery
		{
			// Build elements
			this.buildRoot();
			this.buildHeading();
			this.buildTabs();
			this.buildComponents();
			
			// Build actions into sidebar
			if (this.App.Model.get('layout_name') === 'main')
				this.buildSidebar();

			// Build actions into content
			else
				this.buildActions();
			
			// Update page title
			this.updatePageTitle();
			
			// Set first tab active
			this.showTab(this.Tabs.getFirstTab());
			
			return this.htmlRoot;
		}
		
		// Build action button
		private buildAction(data:Tinycar.Model.DataItem):JQuery
		{
			// Create button instance
			let instance = new Tinycar.Ui.Button({
				style : 'theme-button',
				label : data.get('label')
			});
			
			// When clicked
			instance.setHandler('click', () => 
			{
				this.callAction(data);
			});
			
			// Submit event
			if (data.get('type') === 'submit')
			{
				Tinycar.System.addEvent('submit', () => 
				{
					instance.callHandler('click');
				});
			}

			// Create button
			return instance.build();
		}
		
		// Build actions
		private buildActions():void
		{
			// Create container
			let container = $('<div>').
				attr('class', 'actions').
				appendTo(this.htmlRoot);
			
			// Build view
			this.Model.get('actions').forEach((item:Object) => 
			{
				if (item['target'] === 'view') 
				{
					container.append(this.buildAction(
						new Tinycar.Model.DataItem(item)
					));
				}
			});				
		}
		
		// Build components container
		private buildComponents():void
		{
			this.htmlComponents = $('<div>').
				attr('class', 'components').
				appendTo(this.htmlRoot);
		}
		
		// Build components for specified tab name
		private buildComponentsForTab(name:string):void
		{
			// Initiate tab fields list
			this.tabFields[name] = [];
			
			// Create components only for this tab
			this.Model.get('components').forEach((item:Object) =>
			{
				// Create new model instance
				let model = new Tinycar.Model.DataItem(item);
				
				// Invalid tab name
				if (model.get('tab_name') !== name)
					return;
				
				// Target component type
				let type = model.get('type_name');
				
				// Create new instance
				let instance = new Tinycar.Ui.Component[type](
					this.App, this, model
				);
				
				// Build to components list
				this.htmlComponents.append(instance.build());
				
				// Add to list of components
				this.addComponent(instance);
				
				// This is a field
				if (instance instanceof Tinycar.Main.Field)
					this.tabFields[name].push(this.fieldList.length - 1);
			});
		}
		
		// Build heading
		private buildHeading():void
		{
			// Create container
			let container = $('<div>').
				attr('class', 'heading').
				appendTo(this.htmlRoot);
			
			// Add heading label
			this.htmlHeading = $('<strong>').
				text(this.Model.get('heading')).
				appendTo(container);
		}

		// Build root container
		private buildRoot():void
		{
			// Create container
			this.htmlRoot = $('<form>').
				attr('method', 'POST').
				addClass('tinycar-ui-view').
				addClass('layout-' + this.Model.get('layout_type'));
			
			// When submitted
			this.htmlRoot.submit((e:Event) =>
			{
				e.preventDefault();
				Tinycar.System.callEvent('submit', e);
			});
			
			// Add non-visible button so that enter will 
			// trigger the form submission
			
			$('<button>').
				attr('type', 'submit').
				attr('class', 'view-submit').
				appendTo(this.htmlRoot);
		}
		
		// Build sidebar for view
		private buildSidebar():void
		{
			// Build actions to sidebar
			this.Model.get('actions').forEach((item:Object) => 
			{
				let instance = new Tinycar.Ui.Sidebar.Action(this, item);
				Tinycar.System.Side.addAction(instance);
			});
		}
		
		// Build view tabs
		private buildTabs():void
		{
			// Get tabs
			let tabs = this.Model.get('tabs');
			
			// Create tabs instance
			this.Tabs = new Tinycar.View.Tabs(this);

			// We have more than one tab
			if (tabs.length > 1)
			{
				// Add tabs
				tabs.forEach((item:Object) =>
				{
					this.Tabs.addItem({
						name : item['name'],
						label : item['label']
					});
				});
				
				// Add to content
				this.htmlRoot.append(this.Tabs.build());
			}
		}
		
		// Call specified action
		callAction(params:Tinycar.Model.DataItem):void
		{
			Tinycar.Api.call({
				service : 'application.action',
				params  : {
					app    : this.App.getId(),
					view   : this.getName(),
					url    : Tinycar.Url.getParams(),
					action : params.get('type'),
					data   : this.getComponentsData()
				},
				success  : () => 
				{
					// Show toast message
					if (params.hasString('toast'))
					{
						// Set success message
						Tinycar.System.Toast.setMessage({
							type : 'success',
							text : params.get('toast')
						});
					}
					
					// We must redirect after this message
					if (params.isObject('link') && params.hasString('toast'))
					{
						// Store toast message
						Tinycar.System.Toast.store();
						
						// Move to URL specified by action
						Tinycar.Url.updatePath(params.getObject('link'), {
							url : Tinycar.Url.getParams()
						});
					}
					
					// Redirect to URL
					else if (params.isObject('link'))
					{
						// Move to URL specified by action
						Tinycar.Url.updatePath(params.getObject('link'), {
							url : Tinycar.Url.getParams()
						});
					}
					
					// Just show toast message right away
					else if (params.hasString('toast'))
						Tinycar.System.Toast.show();
				}
			});
		}
		
		// Focus to content
		focus():boolean
		{
			// Get active tab name
			let name = this.Tabs.getActiveTab();

			// This tab has not been built
			if (!this.tabFields.hasOwnProperty(name))
				return false;
			
			// This tab has no fields
			if (this.tabFields[name].length === 0)
				return false;
			
			// Set focus to first component
			let index = this.tabFields[name][0];
			this.fieldList[index].focus();
			
			return true;
		}
		
		// Get data from current components
		getComponentsData():Object
		{
			var data = {};
			
			// Get original data for all components
			this.Model.get('components').forEach((item:Object) =>
			{
				if (item.hasOwnProperty('data_value'))
					data[item['id']] = item['data_value'];
			});
			
			// Get data from rendered field components 
			this.fieldList.forEach((instance:Tinycar.Main.Field) =>
			{
				data[instance.getId()] = instance.getDataValue();
			});
			
			return data;
		}
		
		// Check if this default view
		private isDefaultView():boolean
		{
			return (this.getName() === 'default');
		}
		
		// @see Tinycar.Main.View.onComponent()
		onComponent(id:string, name:string, params:Object, callback:Function):void
		{
			Tinycar.Api.call({
				service : 'application.component',
				params  : {
					app        : this.App.Model.get('id'),
					view       : this.getName(),
					component  : id,
					action     : name,
					data       : params
				},
				success : callback
			});
		}
		
		// Open specified dialog
		openDialog(name:string):void
		{
			this.App.openDialog(name);
		}
		
		// Save current model from an action
		saveModel(action:Tinycar.Ui.Sidebar.Action):void
		{
			// Try to save
			Tinycar.Api.call({
				service : 'application.save',
				params  : {
					app  : this.App.getId(),
					view : this.getName(),
					url  : Tinycar.Url.getParams(),
					data : this.getComponentsData()
				},
				success  : (result:any) => 
				{
					// Set success message
					Tinycar.System.Toast.setMessage({
						type : 'success',
						text : Tinycar.Locale.getText('toast_saved_success')
					});

					// We have a new id, update URL parameters
					if (typeof result === 'number')
					{
						// Store toast message
						Tinycar.System.Toast.store();
						
						// Move to URL specified by action
						Tinycar.Url.updateUrl(action.getLinkUrl({
							id:result
						}));
					}
					// Just show the toast message
					else
					{
						Tinycar.System.Toast.show();
					}
				}
			});
		}
		
		// Show specified view tab contents
		showTab(name:string):void
		{
			// Set active tab
			this.Tabs.setActiveTab(name);
			
			// Build components for this tab once
			if (!this.tabFields.hasOwnProperty(name))
				this.buildComponentsForTab(name);
			
			// Update visibility for all created compents
			this.componentList.forEach((item:Tinycar.Main.Component) => 
			{
				item.setAsVisible((item.getTabName() === name));
			});
		}
		
		// Update current page title
		private updatePageTitle():void
		{
			let path = [];
			
			// Not the default view
			if (!this.isDefaultView())
				path.push(this.Model.get('heading'));

			// Application name and system name
			path.push(this.App.Model.get('name'));
			path.push(Tinycar.Config.get('SYSTEM_TITLE'));
			
			// Update title
			Tinycar.System.Page.setTitle(path);
		}
	}
}