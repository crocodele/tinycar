module Tinycar
{
    interface IAppList
    {
        [key:string]:Tinycar.Ui.Application;
    }

    interface IConfig
    {
        calendar:Object;
        config:Object;
        params:Object;
        text:Object;
        user:Object;
    }

    export module System
    {
        export var appList:IAppList = {};
        export var htmlRoot:JQuery;

        export var Events:Tinycar.EventManager;
        export var Mask:Tinycar.Main.Mask;
        export var Toast:Tinycar.Main.Toast;

      
        // Add event listener
        export function addEvent(name:string, callback:Function):number
        {
            return this.Events.addEvent(name, callback);
        }

        // Build
        export function build():JQuery
        {
            // Event manager
            this.Events = new Tinycar.EventManager();

            // Build elements
            this.buildRoot();

            // Build page
            Tinycar.Page.build();

            // Create mask which is built when used
            this.Mask = new Tinycar.Main.Mask();

            // Create toast
            this.Toast = new Tinycar.Main.Toast();
            this.htmlRoot.append(this.Toast.build());

            // Open default application
            this.openApplication(Tinycar.Url.getParam('app'));

            return this.htmlRoot;
        }

        // Build root container
        export function buildRoot():void
        {
            this.htmlRoot = $('<div>').
                attr('id', 'tinycar-root');
        }
            
        // Call specified event
        export function callEvent(name:string, e:Event):boolean
        {
            return this.Events.callEvent(name, e);
        }

        // Clear specified event
        export function clearEvent(name:string, index:number):void
        {
            this.Events.clearEvent(name, index);
        }

        // Check to see if login is required
        export function isLoginRequired():boolean
        {
            return (
                Tinycar.Config.get('UI_LOGIN') === true &&
                Tinycar.User.hasAuthenticated() === false
            );
        }
            
        // Load configuration data
        export function load(config:IConfig):void
        {
            // Load configuration
            Tinycar.Config.load(config.config);
          
            // Configure URL
            Tinycar.Url.load(config.params);

            // Configure API
            Tinycar.Api.setApiUrl(
                Tinycar.Config.get('API_PATH')
            );

            // Load locale
            Tinycar.Locale.loadCalendar(config.calendar);
            Tinycar.Locale.loadText(config.text);
          
            // Load user
            Tinycar.User.load(config.user);
        }

        // Open specified application
        export function openApplication(name:string):void
        {
            // Target view
            let view = Tinycar.Url.getParam('view');

            // Authentication is required
            if (this.isLoginRequired())
                name = Tinycar.Config.get('APP_LOGIN');

            // Create new instance
            var app = new Tinycar.Ui.Application(name);

            // Add to list
            this.htmlRoot.append(app.build());

            // Load target view
            app.loadView(view);
        }
    }
}