module Tinycar
{
	export module Locale
	{
		interface ITextList
		{
			[key:string]:string;
		}

		export var calendarList:Object = {};
		export var textList:ITextList = {};
		
		// Get specified calendar property value
		export function getCalendar(name:string):any
		{
			return (this.calendarList.hasOwnProperty(name) ?
				this.calendarList[name] : name
			);
		}
	
		// Get specified text from current locale
		export function getText(name:string):string
		{
			return (this.textList.hasOwnProperty(name) ?
				this.textList[name] : name
			);
		}
		
		// Load calendar configuration
		export function loadCalendar(params:Object):void
		{
			for (let name in params)
				this.calendarList[name] = params[name];
		}
	
		// Load text contents
		export function loadText(params:Object):void
		{
			for (let name in params)
				this.textList[name] = params[name];
		}
	}
}