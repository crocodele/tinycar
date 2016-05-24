module Tinycar
{
    export module Url
    {
        interface IUrlParams
        {
            [key:string]:string;
        }

        export var paramList:IUrlParams = {};

        // Get link path to target
        export function getAsPath(custom:Object):string
        {
            // Defaults
            let params = {
                app  : this.getParam('app'),
                view : this.getParam('view')
            };

            // Set custom app
            if (custom.hasOwnProperty('app'))
                params['app'] = custom['app'];

            // Set custom view
            if (custom.hasOwnProperty('view'))
                params['view'] = custom['view'];

            // Set custom id
            if (custom.hasOwnProperty('id'))
                params['id'] = custom['id'];

            // Remove login app
            if (params['app'] === Tinycar.Config.get('APP_LOGIN'))
                delete params['app'];

            // Remove home app
            else if (params['app'] === Tinycar.Config.get('APP_HOME'))
                delete params['app'];

            // Remove default view
            if (params['view'] === 'default')
                delete params['view'];

            let path = [];

            // Create path syntax
            for (var name in params)
                path.push(name + ':' + params[name]);

            // No path needed, but keep similar syntax
            if (path.length === 0)
                return '?' + Tinycar.Config.get('PATH_PARAM') + '=/';

            // Get path as URL parameter
            return '?' +
                Tinycar.Config.get('PATH_PARAM') +
                '=/' + path.join('/') + '/';
        }

        // Get specified parameter value
        export function getParam(name:string):string
        {
            return (this.paramList.hasOwnProperty(name) ?
                this.paramList[name] : null
            );
        }

        // Get all parameters
        export function getParams():Object
        {
            return this.paramList;
        }

        // Get unique id for active URL
        export function getUid(target?:string):string
        {
            let result = 'uid';
            let names  = ['app', 'view', 'id'];

            // Limit to application level
            if (typeof target === 'string' && target === 'app')
                names = ['app'];

            // Pick relevant URL properties to string
            names.forEach((name:string) =>
            {
                if (this.paramList.hasOwnProperty(name))
                    result += '-' + this.paramList[name];
            });

            return result;
        }

        // Get link proeprties evaluated with custom variables
        export function getWithVars(custom:Object, vars:Object):Object
        {
            // Copy source object
            let result = jQuery.extend(true, {}, custom);

            // Go through variables
            for (let type in vars)
            {
                // Variables properties
                for (let name in vars[type])
                {
                    // Update custom properties
                    for (let key in custom)
                    {
                        if (result[key] === '$' + type + '.' + name)
                            result[key] = vars[type][name];
                    }
                }

            }

            return result;
        }

        // Check if specified parameter exits
        export function hasParam(name:string):boolean
        {
            return this.paramList.hasOwnProperty(name);
        }

        // Check if specified path properties match current path
        export function isPathMatch(custom:Object, fuzzy:Boolean = false):boolean
        {
            for (var name in custom)
            {
                // When fuzzy, we should omit the default view
                if (fuzzy === true && name === 'view' && custom[name] === 'default')
                    continue;

                // This path part does not match property
                if (this.getParam(name) !== custom[name])
                    return false;
            }

            return true;
        }

        // Load parameters
        export function load(params:Object):void
        {
            // Set custom parameters
            for (let name in params)
                this.paramList[name] = params[name];

            // Set default application name
            if (!this.paramList.hasOwnProperty('app'))
                this.paramList['app'] = Tinycar.Config.get('APP_HOME');

            // Set default view
            if (!this.paramList.hasOwnProperty('view'))
                this.paramList['view'] = 'default';
        }

        // Open specified URL
        export function openUrl(url:string):void
        {
            Tinycar.Page.setState('unloading');
            location.href = url;
        }

        // Set specified parameter value
        export function setParam(name:string, value:string):void
        {
            this.paramList[name] = value;
        }

        // Update path by replacing with another
        export function updatePath(custom:Object, vars?:Object):void
        {
            // Add custom variables
            if (typeof vars === 'object')
                custom = this.getWithVars(custom, vars);

            // Update URL
            this.updateUrl(this.getAsPath(custom));
        }

        // Update URL by replacing it with another
        export function updateUrl(url:string):void
        {
            Tinycar.Page.setState('unloading');
            location.replace(url);
        }
    }
}
