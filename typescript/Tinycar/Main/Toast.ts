module Tinycar.Main
{
	import u = Tinycar.Ui;
	
	interface IToastMessage
	{
		type:string;
		text:string;
		vars?:Object;
		delay?:number;
	}
	
	export class Toast
	{
		private hideTimer:number;
		private htmlMessage:JQuery;
		private htmlRoot:JQuery;
		private isMouseOver:boolean = false;
		private nextMessage:IToastMessage;
		private showTimer:number;
	
		// Build
		build():JQuery
		{
			// Build elements
			this.buildRoot();
			this.buildMessage();
			this.buildClose();
			
			// If we have a store message, show it
			if (Tinycar.Session.has('toast-message'))
				this.loadSession();
			
			return this.htmlRoot;
		}
		
		// Build message container
		private buildMessage():void
		{
			this.htmlMessage = $('<div>').
				attr('class', 'message').
				appendTo(this.htmlRoot);
		}
		
		// Build close icon
		private buildClose():void
		{
			// Create container
			let container = $('<a>').
				attr('class', 'close').
				appendTo(this.htmlRoot);
			
			// Create icon
			$('<span>').
				attr('class', 'icon icon-small icon-close').
				appendTo(container);
			
			// When clicked
			container.click((e:Event) =>
			{
				e.preventDefault();
				this.hide();
			});
		}

		// Build root container
		private buildRoot():void
		{
			// Create container
			this.htmlRoot = $('<div>').
				attr('id', 'tinycar-main-toast');
			
			// When mouse enters container
			this.htmlRoot.mouseenter(() =>
			{
				this.isMouseOver = true;
			});
			
			// When mouse leaves container
			this.htmlRoot.mouseleave(() => 
			{
				this.isMouseOver = false;
			});
		}
		
		// Hide current message
		hide():void
		{
			// Clear old hide timer
			if (this.hideTimer !== null)
			{
				window.clearInterval(this.hideTimer);
				this.hideTimer = null;
			}
			
			// Update styles
			this.htmlRoot.addClass('is-hidden');
		}
		
		// Load initial message from session
		private loadSession():void
		{
			// Get message from session
			let message = Tinycar.Session.get(
				'toast-message'
			);

			// Configure next message
			this.setMessage({
				type  : message['type'],
				text  : message['text'],
				delay : message['delay']
			});
			
			// Show message
			this.show();
			
			// Clear dta from session
			Tinycar.Session.clear('toast-message');
		}
		
		// Set specified message as the next message to display
		setMessage(message:IToastMessage):void
		{
			// Update message contents
			this.nextMessage = message;
		}
		
		// Show next scheduled message
		show():boolean
		{
			// We do not have a message to display
			if (this.nextMessage === null)
				return false;
			
			// Update state
			let message = this.nextMessage;
			this.nextMessage = null;
			
			// Clear old hiding timer
			if (this.hideTimer)
			{
				window.clearInterval(this.hideTimer);
				this.hideTimer = null;
			}
			
			// Clear old show timer
			if (this.showTimer !== null)
			{
				window.clearTimeout(this.showTimer);
				this.showTimer = null;				
			}
			
			// We have a delay
			if (message.hasOwnProperty('delay'))
			{
				this.showTimer = window.setTimeout(() => 
				{
					this.showMessage(message);
					
				}, message.delay * 1000);
			}
			
			// Show right away
			else
				this.showMessage(message);

			return true;
		}
		
		// Show error API error response
		showFromError(error:Object):void
		{
		    // Set message
            this.setMessage({
                type : 'failure',
                vars : error['message'],
                text : Tinycar.Locale.getText('toast_' + error['code'])
            });
            
            // Show message 
            this.show();
		}
		
		// Set specified message
		private showMessage(message:IToastMessage):void
		{
			// Apply custom variables to message
			if (message.hasOwnProperty('vars'))
			{
				for (var name in message.vars)
				{
					message.text = message.text.
						split('$' + name).
						join(message.vars[name]);
				}
			}
			
			// Change contents
			this.htmlMessage.text(message.text);

			// Update styles
			this.htmlRoot.attr('class', 
				'type-' + message.type + ' is-visible'
			);
			
			// Success message is hidden automatically
			if (message.type === 'success')
			{
				// Hide the message with a delay
				this.hideTimer = window.setInterval(() => 
				{
					// Hide trough a timer only if the
					// mouse is not on top of the toast
					
					if (this.isMouseOver === false)
						this.hide();
					
				}, 3000);
			}
		}
		
		// Store next message to be displayed upon next page refresh
		store():void
		{
			// We must have a message to display
			if (this.nextMessage !== null)
			{
				this.nextMessage.delay = 0.5;
				Tinycar.Session.set('toast-message', this.nextMessage);
			}
		}
	}
}