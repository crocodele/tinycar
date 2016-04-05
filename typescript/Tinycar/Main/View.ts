module Tinycar.Main
{
	interface IView
	{
		onComponent(id:string, name:string, params:Object, callback:Function):void;
	}
	
	export class View implements IView
	{
		protected componentList:Array<Tinycar.Main.Component> = [];
		protected fieldList:Array<Tinycar.Main.Field> = [];
		
		App:Tinycar.Ui.Application;
		Model:Tinycar.Model.DataItem;
	
		// Initiate class
		constructor(app:Tinycar.Ui.Application, model:Object)
		{
			this.App = app;
			this.Model = new Tinycar.Model.DataItem(model);
		}
		
		
		// Add component to list of components
		addComponent(instance:Tinycar.Main.Component):void
		{
			// Add to list
			this.componentList.push(instance);
			
			// This is a field
			if (instance instanceof Tinycar.Main.Field)
				this.fieldList.push(instance);
		}
		
		// Create a component instance
		createComponent(data:Object):Tinycar.Main.Component
		{
			// Create new model instance
			let model = new Tinycar.Model.DataItem(data);
			
			// Target component type
			let type = model.get('type_name');
			
			// Create new instance
			let instance = new Tinycar.Ui.Component[type](
				this.App, this, model
			);
			
			return instance;
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
		
		// Get dialog name
		getDialogName():string
		{
			return null;
		}
		
		// Get name
		getName():string
		{
			return this.Model.get('name');
		}
		
		// Get view name
		getViewName():string
		{
			return null;
		}
		
		// Refresh currently visible components
		refreshComponents():void
		{
			// Update each component 
			this.componentList.forEach((instance:Tinycar.Main.Component) =>
			{
				if (instance.isVisible())
					instance.refresh();
			});
		}
		
		// Call specified component 
		onComponent(id:string, name:string, params:Object, callback:Function):void
		{
		}
		
		// Handle request response
		onResponse(params:Tinycar.Model.DataItem):void
		{
			// Show toast message
			if (params.hasString('toast'))
			{
				// Set success message
				Tinycar.System.Toast.setMessage({
					type : 'success',
					text : params.get('toast'),
					vars : {value:params.get('value')}
				});
			}
			
			// We must redirect after this message
			if (params.isObject('link') && params.hasString('toast'))
			{
				// Store toast message
				Tinycar.System.Toast.store();
				
				// Move to URL specified by action
				Tinycar.Url.updatePath(params.getObject('link'), {
					url   : Tinycar.Url.getParams(),
					model : {id:params.get('value')}
				});
			}
			
			// Redirect to URL
			else if (params.isObject('link'))
			{
				// Move to URL specified by action
				Tinycar.Url.updatePath(params.getObject('link'), {
					url   : Tinycar.Url.getParams(),
					model : {id:params.get('value')}
				});
			}
			
			// Just show toast message right away
			else if (params.hasString('toast'))
				Tinycar.System.Toast.show();
		}
	}
}