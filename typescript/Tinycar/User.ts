module Tinycar
{
	export module User
	{
		export var dataList:Object = {};
		
		// Get specified property from current data
		export function get(name:string):any
		{
			return (this.dataList.hasOwnProperty(name) ?
				this.dataList[name] : null
			);
		}
		
		// Check to see if datalist is empty
		export function hasAuthenticated():boolean
		{
			return (this.get('is_empty') === false);
		}
		
		// Load initial data
		export function load(params:Object):void
		{
			this.dataList = params;
		}
	}
}