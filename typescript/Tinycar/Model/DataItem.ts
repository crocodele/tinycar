module Tinycar.Model
{
	export class DataItem
	{
		private dataList:Object = [];
		
		
		// Initiate class
		constructor(data:Object)
		{
			this.dataList = data;
		}
		
		// Add new data properties
		addAll(data:Object):void
		{
			for (let name in data)
				this.dataList[name] = data[name];
		}
		
		// Clear specified data value
		clear(name:string):void
		{
			if (this.dataList.hasOwnProperty(name))
				delete this.dataList[name];
		}
		
		// Get specified data value
		get(name:string):any
		{
			return (this.dataList.hasOwnProperty(name) ? 
				this.dataList[name] : null
			);
		}
		
		// Get all data
		getAll():Object
		{
			return this.dataList;
		}
		
		// Get specified data value at specified array index
		getIndex(name:string, index:number):any
		{
			if (!this.isArray(name))
				return null;
			
			if (this.dataList[name].length < index)
				return null;
			
			return this.dataList[name][index];
		}
		
		// Get sepcified data value as a number
		getNumber(name:string):number
		{
			return (this.isNumber(name) ? this.dataList[name] : 0);
		}
		
		// Get specified data value as a list
		getList(name:string):Array<any>
		{
			return (this.isArray(name) ? this.dataList[name] : []);
		}
		
		// Get specified data value as a string
		getString(name:string):string
		{
			let value = this.get(name);
			return (typeof value === 'string' ? value : '');
		}
		
		// Get specified data value as an object
		getObject(name:string):Object
		{
			return (this.isObject(name) ? this.dataList[name] : {});
		}
		
		// Check if specified data property value exists
		has(name:string):boolean
		{
			return this.dataList.hasOwnProperty(name);
		}
		
		// Load new data into model
		load(data:Object):void
		{
			this.dataList = data;
		}
		
		// Check if specified data value is a number 
		// and is not empty
		hasNumber(name:string):boolean
		{
			return (
				this.isNumber(name) &&
				(this.dataList[name]> 0)
			);
		}
		
		// Check if specified data value is a string 
		// and is not empty
		hasString(name:string):boolean
		{
			return (
				this.isString(name) &&
				(this.dataList[name].length > 0)
			);
		}
		
		// Check if specified data value is an array
		isArray(name:string):boolean
		{
			return (
				this.dataList.hasOwnProperty(name) && 
				(this.dataList[name] instanceof Array)
			);
		}
		
		// Check if specified data value is a number
		isNumber(name:string):boolean
		{
			return (
				this.dataList.hasOwnProperty(name) && 
				(typeof this.dataList[name] === 'number')
			);			
		}
		
		// Check if specified data value is an object
		isObject(name:string):boolean
		{
			return (
				this.dataList.hasOwnProperty(name) && 
				(this.dataList[name] instanceof Object) &&
				!(this.dataList[name] instanceof Array)
			);			
		}
		
		// Check if specified data value is a string
		isString(name:string):boolean
		{
			return (
				this.dataList.hasOwnProperty(name) && 
				(typeof this.dataList[name] === 'string')
			);			
		}
		
		// Set new data property value
		set(name:string, value:any):void
		{
			this.dataList[name] = value;
		}
	}
}