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
        export function getText(name:string, vars?:Object):string
        {
            // Get target string
            let result = (this.textList.hasOwnProperty(name) ?
                this.textList[name] : name
            );

            // We have custom variables to place
            if (typeof vars !== 'undefined')
            {
                // Replace variables
                for (let key in vars)
                    result = result.split('$' + key).join(vars[key]);
            }

            return result;
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

        // Format specified unix timestamp into specified locale format
        export function toDate(time:number, type:string):string
        {
            // Get date format
            let format = this.getCalendar('format_' + type);

            // Format is invalid
            if (typeof format !== 'string')
                return null;

            // Format with given rule
            return Tinycar.Format.toDate(time, format);
        }
    }
}
