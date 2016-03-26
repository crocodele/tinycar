module Tinycar.Ui
{
	interface IComponentData
	{
		[key:string]:Object;
	}
	
	interface IHandlerList
	{
		[key:string]:Function;
	}
	
	export class Dialog extends Tinycar.Main.View
	{
		private componentData:IComponentData = {};
		private contentPadding:number = 0;
		private handlerList:IHandlerList = {};
		private htmlContent:JQuery;
		private htmlComponents:JQuery;
		private htmlHeading:JQuery;
		private htmlRoot:JQuery;
		private resizeEvent:number;
	
	
		// Add data for specified component type
		addDataForComponentType(type:string, data:Object):void
		{
			this.componentData[type] = data;
		}
	
		
		// Build
		build():JQuery
		{
			this.buildRoot();
			return this.htmlRoot;
		}
		
		// Build actions
		private buildActions():void
		{
			// Build actions container
			let container = $('<div>').
				attr('class', 'actions').
				appendTo(this.htmlContent);
			
			// Build actions
			this.Model.get('actions').forEach((item:Object, index:number) => 
			{
				// Create button instance
				let button = new Tinycar.Ui.Button({
					style : (index === 0 ? 'theme-button' : 'secondary-button'),
					label : item['label']
				});
				
				// When clicked
				button.setHandler('click', () =>
				{
					// Cancel dialog
					if (item['type'] === 'cancel')
						this.close();
					
					// Select dialog
					else if (item['type'] === 'select')
						this.onSelect();
					
					// Other actions
					else
						this.onAction(item);
				});
				
				container.append(button.build());
			});
		}
		
		// Build close icon
		private buildClose():void
		{
			// Create container
			let container = $('<a>').
				attr('class', 'close theme-base-lite').
				appendTo(this.htmlRoot);
			
			// Add icon
			$('<span>').
				attr('class', 'icon icon-lite icon-medium icon-close').
				appendTo(container);
			
			// When clicked
			container.click((e:Event) =>
			{
				e.preventDefault();
				
				// Use a small delay to display the interaction
				window.setTimeout(() => 
				{
					this.close();
					
				}, 300);
			});
		}
		
		// Build components
		private buildComponents():void
		{
			// Create container
			this.htmlComponents = $('<div>').
				attr('class', 'components').
				appendTo(this.htmlContent);
			
			// Create cmponents
			this.Model.get('components').forEach((item:Object) =>
			{
				// Create new model instance
				let model = new Tinycar.Model.DataItem(item);
				
				// Target component type
				let type = model.get('type_name');
				
				// Add custom model data
				if (this.componentData.hasOwnProperty(type))
					model.addAll(this.componentData[type]);
				
				// Create new instance
				let instance = new Tinycar.Ui.Component[type](
					this.App, this, model
				);
				
				// Build to container
				this.htmlComponents.append(instance.build());
				
				// Add to list of components
				this.addComponent(instance);
			});
		}
		
		// Build content container
		private buildContent():void
		{
			this.htmlContent = $('<div>').
				attr('class', 'content').
				appendTo(this.htmlRoot);
		}
		
		// Build heading
		private buildHeading():void
		{
			this.htmlHeading = $('<div>').
				attr('class', 'heading').
				text(this.Model.get('heading')).
				appendTo(this.htmlContent);
		}

		// Build root container
		private buildRoot():void
		{
			this.htmlRoot = $('<div>').
				attr('id', 'tinycar-ui-dialog');
		}
		
		// Close dialog
		close():void
		{
			// Hide mask
			Tinycar.System.Mask.hide();
			
			// Close dialog
			this.App.closeDialog();
			
			// Remove resize event handler
			Tinycar.System.clearEvent('resize', this.resizeEvent);
			
			// Remove root container
			this.htmlRoot.remove();
		}
		
		// Load dialog contents
		load(data:Object):void
		{
			// Try to save
			Tinycar.Api.call({
				service : 'dialog.view',
				params  : {
					app    : this.Model.get('app'),
					dialog : this.Model.get('name'),
					url    : Tinycar.Url.getParams(),
					data   : data
				},
				success  : (data:Object) => 
				{
					// Add new data to amodel
					this.Model.addAll(data);
					
					// Build elements
					this.buildClose();
					this.buildContent();
					this.buildHeading();
					this.buildComponents();
					this.buildActions();
					
					// When window is resized
					this.resizeEvent = Tinycar.System.addEvent('resize', () =>
					{
						this.updateDimensions();
					});
					
					// Trigger iniitial resize after DOM has rendered
					window.setTimeout(() =>
					{
						this.show();
						
					}, 100);
				}
			});
		}
		
		// Call specified action 
		onAction(item:Object):void
		{
			var params = new Tinycar.Model.DataItem(item);
			
			Tinycar.Api.call({
				service : 'dialog.action',
				params  : {
					url    : Tinycar.Url.getParams(),
					app    : this.Model.get('app'),
					dialog : this.Model.get('name'),
					action : params.get('type'),
					data   : this.getComponentsData()
				},
				success  : (result:any) => 
				{
					// Set returned value
					params.set('value', result);
					
					// Show response
					this.onResponse(params);
						
					// Close dialog
					this.close();
				}
			});
		}
		
		// @see Tinycar.Main.View.onComponent()
		onComponent(id:string, name:string, params:Object, callback:Function):void
		{
			Tinycar.Api.call({
				service : 'dialog.component',
				params  : {
					app        : this.Model.get('app'),
					dialog     : this.Model.get('name'),
					component  : id,
					action     : name,
					data       : params
				},
				success : callback
			});
		}
		
		// Pass on selected data to dialog callback
		onSelect():void
		{
			var list = [];
			
			// Get selected items from components
			this.componentList.forEach((instance:Tinycar.Main.Component) =>
			{
				// Get selected items
				if ('getSelectedItems' in instance)
					list = list.concat(instance['getSelectedItems']());
			});
			
			// Pass along to custom handler
			if (this.handlerList.hasOwnProperty('select'))
				this.handlerList['select'](list);
			
			// Close dialog
			this.close();
		}
		
		// Set custom handler
		setHandler(name:string, callback:Function):void
		{
			this.handlerList[name] = callback;
		}
		
		// Show dialog
		private show():void
		{
			// Calculate content padding once
			this.contentPadding = (
				parseInt(this.htmlContent.css('padding-top')) + 
				parseInt(this.htmlContent.css('padding-bottom')) +
				10
			);
			
			// Update root styles
			this.htmlRoot.addClass('is-visible');
			
			// Update dialog dimensions
			this.updateDimensions();
		}
		
		// Update current dialog position
		private updateDimensions():void
		{
			// Current window height
			let winH = $(window).height();
			
			// Calculate new dialog height
			let newH = Math.min(winH, Math.max(
				this.htmlComponents.outerHeight() + this.contentPadding, 
				this.htmlComponents.get(0).scrollTop + this.htmlComponents.get(0).scrollHeight + this.contentPadding 
			));
			
			// Calculate new top-position based on height
			let newT = Math.max(0, Math.round((winH - newH) / 2));
			
			// Update dialog dimensions
			this.htmlRoot.css({
				top    : newT,
				height : newH
			});
		}
	}
}