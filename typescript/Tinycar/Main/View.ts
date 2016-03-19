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
		
		// Call specified component 
		onComponent(id:string, name:string, params:Object, callback:Function):void
		{
		}
	}
}