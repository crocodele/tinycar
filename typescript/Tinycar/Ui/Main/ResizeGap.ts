module Tinycar.Ui.Main
{
	interface IHandlerList
	{
		[key:string]:Function;
	}
	
	interface ISettingsData
	{
		width:number;
	}
	
	export class ResizeGap
	{
		private defaultWidth:number = 0;
		private handlerList:IHandlerList = {};
		private htmlRoot:JQuery;
		private maxCollapse:number = 0;
		private maxWidth:number = 0;
		private minCollapse:number = 0;
		private minWidth:number = 0;
		private parentNode:JQuery;
		private parentWidth:number;
		private resizeStartX:number = 0;
		private resizeWidth:number;
		private resizeTouch:boolean = false;
		private settingsId:string = 'sidebar';
	
		// Initial settings
		private settingsData:ISettingsData = {
			width : 0
		};
	
		// Initiate class
		constructor(settingsId:string, parentNode:JQuery)
		{
			// Set settings id
			this.settingsId = settingsId;
 
			// Remember node
			this.parentNode = parentNode;
		}
	
		// Build
		build():JQuery
		{
			// Load settings from session
			this.loadSettings();
			
			// Build elements
			this.buildRoot();
			
			// Initiate event listeners
			this.initListeners();
			
			// Set initial width
			if (this.settingsData.width > 0)
				this.initWidth();
			
			return this.htmlRoot;
		}
		
		// Build root container
		private buildRoot():void
		{
			// Create container
			this.htmlRoot = $('<a>').
				attr('class', 'tinycar-ui-main-resizegap');
			
			// Add line
			$('<span>').
				attr('class', 'line').
				appendTo(this.htmlRoot);
		}
		
		// Call specified handler
		private callHandler(name:string, data?:any):void
		{
			if (this.handlerList.hasOwnProperty(name))
				this.handlerList[name](data);
		}
		
		// Get sepcified width that consider collapse rules
		private getAsCollapseWidth(width:number):number
		{
			// Collapse to minimum width
			if (this.minCollapse > 0 && width <= this.minCollapse)
				width = this.minWidth;
			
			// Collapse to maximum width
			if (this.maxCollapse > 0 && width >= this.maxCollapse)
				width = this.maxWidth;
			
			return width;
		}
		
		// Get specified width for parent node
		private getAsParentWidth(width:number):number
		{
			// Enforce minimum value
			width = Math.max(this.minWidth, width);
			
			// Enforce maximum value
			if (this.maxWidth > 0)
				width = Math.min(this.maxWidth, width);
			
			return width;
		}
		
		// Initiate event listeners
		private initListeners():void
		{
			// When drag is started
			var startHandler = (e:JQueryMouseEventObject) =>
			{
				// Internal handler
				this.onResizeStart(e);
				
				// Listen for global movement
				$(document).bind('mousemove', moveHandler);
				$(document).bind('touchmove', moveHandler);
				
				// Listen for when stopped
				$(document).bind('mouseup', stopHandler);
				$(document).bind('touchcancel', stopHandler);
				$(document).bind('touchstop', stopHandler);
			};
			
			// When drag moves
			var moveHandler = (e:JQueryMouseEventObject) =>
			{
				// Internal handler
				this.onResizeMove(e);
			};
			
			// When drag is stopped
			var stopHandler = (e:JQueryMouseEventObject) =>
			{
				// Internal handler
				this.onResizeStop(e);
				
				// Remove global movement listeners
				$(document).unbind('mousemove', moveHandler);
				$(document).unbind('touchmove', moveHandler);
				
				// Listen for when stopped
				$(document).unbind('mouseup', stopHandler);
				$(document).unbind('touchcancel', stopHandler);
				$(document).unbind('touchstop', stopHandler);
			};
			
			// Assign initial event listeners
			this.htmlRoot.bind('mousedown', startHandler);
			this.htmlRoot.bind('touchstart', startHandler);
		}
		
		// Initiate parent width
		private initWidth():void
		{
			// Initial width
			let width = this.getAsCollapseWidth(
				this.getAsParentWidth(this.settingsData.width)
			);
			
			// Set parent node width
			this.parentNode.css('width', width);
			
			// We are at minimum width
			if (width > this.minWidth)
				this.parentNode.addClass('width-expanded');
			
			// Trigger listeners
			this.callHandler('resized', width);
		}
		
		// Load settings
		private loadSettings():void
		{
			// Get existing settings from session
			let settings = Tinycar.Session.get(this.settingsId);
			
			// Default value for setting
			this.settingsData.width = (this.defaultWidth > 0 ? 
				this.defaultWidth : this.minWidth
			);
			
			// Set custom width
			if (settings.hasOwnProperty('width'))
				this.settingsData.width = parseFloat(settings['width']);
		}
		
		// When resizing is started
		private onResizeStart(e:JQueryMouseEventObject):void
		{
			// Cancel default behaviour
			e.preventDefault();
			
			// Update initial widths
			this.parentWidth = this.parentNode.outerWidth();
			this.resizeWidth = this.parentWidth;
			
			// We are using touch
			if (e.originalEvent.hasOwnProperty('touches'))
			{
				this.resizeTouch = true;
				this.resizeStartX = e.originalEvent['touchers'][0].pageX;
			}
			// We are using mouse
			else
			{
				this.resizeTouch = false;
				this.resizeStartX = e.pageX;
			}
			
			// Show skin
			Tinycar.System.Mask.showAsSkin('ew-resize');
			
			// Update root styles
			this.htmlRoot.addClass('is-resizing');
		}
		
		// When resized
		private onResizeMove(e:JQueryMouseEventObject):void
		{
			// Get current X-coordinate
			let newX = (this.resizeTouch ? 
				e.originalEvent['touchers'][0].pageX : 
				e.pageX
			);
			
			// Calculate offset
			let offX = this.resizeStartX - newX;
			
			// Calculate new width
			let newWidth = this.parentWidth - offX;
			
			// Enforce range
			newWidth = this.getAsParentWidth(newWidth);
			
			// Update target element width when needed
			if (newWidth !== this.resizeWidth)
			{
				// Update width
				this.resizeWidth = newWidth;
				
				// Update node width
				this.parentNode.css('width', newWidth);
				
				// Trigger custom handler
				this.callHandler('resizing', this.resizeWidth);
			}
		}
		
		// When resising is stopped
		private onResizeStop(e:JQueryMouseEventObject):void
		{
			// Previous parent width
			let lastWidth = this.parentWidth;
			
			// Calculate new parent width
			this.parentWidth = this.getAsCollapseWidth(
				this.resizeWidth
			);
			
			// Collapsing changed the width
			if (this.resizeWidth !== this.parentWidth)
				this.parentNode.css('width', this.parentWidth);
			
			// We have collapsed to minimum width
			if (lastWidth > this.minWidth && this.parentWidth === this.minWidth)
				this.parentNode.removeClass('width-expanded');
			
			// We have expanded beyound minimum width
			else if (lastWidth === this.minWidth && this.parentWidth > this.minWidth)
				this.parentNode.addClass('width-expanded');
			
			// Hide skin
			Tinycar.System.Mask.hide();
			
			// Update root styles
			this.htmlRoot.removeClass('is-resizing');
			
			// Storew new width
			this.storeSettings({width:this.parentWidth});
			
			// Trigger custom handler
			this.callHandler('resized', this.parentWidth);
		}
		
		// Set default width
		setDefaultWidth(width:number):void
		{
			this.defaultWidth = width;
		}
		
		// Set custom event hander
		setHandler(name:string, callback:Function):void
		{
			this.handlerList[name] = callback;
		}
		
		// Set collapse width for maximum width
		setMaxCollapse(width:number):void
		{
			this.maxCollapse = width;
		}
		
		// Set maximum allowed width
		setMaxWidth(width:number):void
		{
			this.maxWidth = width;
		}
		
		// Set collapse width for minimum width
		setMinCollapse(width:number):void
		{
			this.minCollapse = width;
		}
		
		// Set minimum allowed width
		setMinWidth(width:number):void
		{
			this.minWidth = width;
		}
		
		// Store current settings
		private storeSettings(data:Object):void
		{
			// Update setting values
			for (var name in data)
				this.settingsData[name] = data[name];
			
			// Update settings to session
			Tinycar.Session.set(this.settingsId, this.settingsData);
		}
	}
}