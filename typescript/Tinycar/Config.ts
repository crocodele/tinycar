module Tinycar
{
	export module Config
	{
		export var configList:Object = {};
	
		// Load configuration data
		export function load(config:Object):void
		{
			this.configList = config;
		}
		
		// Get specified configuration property value
		export function get(name:string):any
		{
			return (this.configList.hasOwnProperty(name) ?
				this.configList[name] : null
			);
		}
		
		// Set specified configuration property value
		export function set(name:string, value:string):void
		{
			this.configList[name] = value;
		}
	}
}