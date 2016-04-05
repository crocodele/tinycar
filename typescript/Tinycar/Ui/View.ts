module Tinycar.Ui
{
	interface ITabFields
	{
		[key:string]:Array<number>;
	}
	
	export class View extends Tinycar.Main.View
	{
		private htmlComponents:JQuery;
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
			
			// Build actions into content
			if (this.App.Model.get('layout_name') !== 'main')
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
				container.append(this.buildAction(
					new Tinycar.Model.DataItem(item)
				));
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
			let instance = new Tinycar.View.Heading(this.Model);
			this.htmlRoot.append(instance.build());
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
					url    : Tinycar.Url.getParams(),
					app    : this.App.getId(),
					view   : this.getName(),
					action : params.get('type'),
					data   : this.getComponentsData()
				},
				success  : () => 
				{
					this.onResponse(params);
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
					url        : Tinycar.Url.getParams(),
					app        : this.App.Model.get('id'),
					view       : this.getName(),
					component  : id,
					action     : name,
					data       : params
				},
				success : callback
			});
		}
		
		// Save current datamodel
		onSave(params:Tinycar.Model.DataItem):void
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
					// Update model id into URL
					if (typeof result === 'number')
					{
						params.set('value', result)
						this.onResponse(params);
					}
					// Response without redirecting
					else
					{
						params.clear('link');
						this.onResponse(params);						
					}
				}
			});
		}
		
		// Open specified dialog
		openDialog(name:string):void
		{
			this.App.openDialog(name);
		}
		
		// Show specified view tab contents
		showTab(name:string):void
		{
			let built = false;
			
			// Set active tab
			this.Tabs.setActiveTab(name);
			
			// Build components for this tab
			if (!this.tabFields.hasOwnProperty(name))
			{
				this.buildComponentsForTab(name);
				
				// @note: starting components should  probably be
				//         global event after application's been built 
			
				// Update visibility for all created compents
				this.componentList.forEach((item:Tinycar.Main.Component) => 
				{
					// Set component visible or hidden
					item.setAsVisible((item.getTabName() === name));
					
					// Start component
					if (item.isVisible())
						item.start();
						
				});
			}
			// Just display components
			else
			{
				// Update visibility for all created compents
				this.componentList.forEach((item:Tinycar.Main.Component) => 
				{
					item.setAsVisible((item.getTabName() === name));
				});
			}
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
			Tinycar.Page.setTitle(path);
		}
	}
}