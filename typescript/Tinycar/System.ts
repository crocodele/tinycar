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
	
	interface IEventList
	{
		[key:string]:Array<Function>;
		
	}
	
	export module System
	{
		export var appList:IAppList = {};
		export var eventList:IEventList = {};
		export var htmlRoot:JQuery;
	
		export var Toast:Tinycar.Main.Toast;
	
	
		// Call specified event
		export function callEvent(name:string, e:Event):boolean
		{
			// We don't have handlers for this event 
			if (!this.eventList.hasOwnProperty(name))
				return;
			
			// Cancel event behaviour
			e.preventDefault();
				
			// Trigger events
			this.eventList[name].forEach((callback:Function) =>
			{
				if (callback instanceof Function)
					callback(e);
			});
			
			return true;
		}
	
		// Add event listener
		export function addEvent(name:string, callback:Function):number
		{
			// Initiate list
			if (!this.eventList.hasOwnProperty(name))
				this.eventList[name] = [];
			
			// Next index number
			let index = this.eventList[name].length;
			
			// Add callback to list
			this.eventList[name].push(callback);
			
			return index;
		}
	
		// Build
		export function build():JQuery
		{
			// Initiate listeners
			this.initListeners();
			
			// Build elements
			this.buildRoot();
			
			// Build page
			Tinycar.Page.build();
			
			// Create toast
			this.Toast = new Tinycar.Main.Toast();
			this.htmlRoot.append(this.Toast.build());
			
			// Open default application
			this.openApplication(
				Tinycar.Url.getParam('app')
			);
			
			return this.htmlRoot;
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
		
		// Build root container
		export function buildRoot():void
		{
			this.htmlRoot = $('<div>').
				attr('id', 'tinycar-root');
		}
		
		// Clear specified event
		export function clearEvent(name:string, index:number):void
		{
			this.eventList[name][index] = null;
		}
		
		// Initiate event listeners
		export function initListeners():void
		{
			// When key is released
			$(window).keydown((e:JQueryKeyEventObject) =>
			{
				// Ctrl + S
				if (e.ctrlKey && !e.shiftKey && e.keyCode === 83)
					this.callEvent('ctrl+s', e);
			});
			
			// When window is resized
			$(window).resize((e:Event) =>
			{
				this.callEvent('resize', e);
			});
		}
		
		// Check to see if login is required
		export function isLoginRequired():boolean
		{
			return (
				Tinycar.Config.get('UI_LOGIN') === true &&
				Tinycar.User.hasAuthenticated() === false
			);
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