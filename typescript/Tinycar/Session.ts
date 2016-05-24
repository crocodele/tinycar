module Tinycar
{
    export module Session
    {
        interface ISessionData
        {
            [key:string]:any;
        }

        export var sessionData:ISessionData;

        // Clear specified session property
        export function clear(name:string):void
        {
            sessionStorage.removeItem(name);
        }

        // Get specified session property value
        export function get(name:string):Object
        {
            // Get data from storage
            let data = sessionStorage.getItem(name);

            // Invalid data to parse
            if (typeof data !== 'string')
                return {};

            // Try to parse JSON string
            data = JSON.parse(data);

            // Get empty object on failure
            if (!(data instanceof Object))
                return {};

            return data;
        }

        // Check if specified session property exists
        export function has(name:string):boolean
        {
            return (sessionStorage.getItem(name) !== null);
        }

        // Set specified session storage property value
        export function set(name:string, value:Object):void
        {
            sessionStorage.setItem(name, JSON.stringify(value));
        }
    }
}
