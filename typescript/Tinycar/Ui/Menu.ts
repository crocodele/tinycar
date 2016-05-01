module Tinycar.Ui
{
    interface IHandlerList
    {
        [key:string]:Function;
    }
    
    export class Menu
    {
        private clickEvent:number;
        private handlerList:IHandlerList = {};
        private htmlRoot:JQuery;
        private itemList:Array<Object> = [];
        private menuVisible:boolean = false;
        private parentHeight:number;
        private parentNode:JQuery;
        private parentWidth:number;
        private resizeEvent:number;
        
        
        // Initiate class
        constructor(node:JQuery)
        {
            this.parentNode = node;
        }
    
        // Add item to menu list
        addItem(name:string, item:Object):void
        {
            item['name']= name;
            this.itemList.push(item);
        }
        
        // Build item
        private buildItem(item:Object):JQuery
        {
            // Create container
            let container = $('<a>').
                attr('tabindex', 0).
                attr('class', 'item').
                text(item['label']);
            
            // When clicked
            container.click((e:Event) =>
            {
                e.preventDefault();
                e.stopPropagation();
                
                // Small delay for visual effect
                window.setTimeout(() =>
                {
                    // Trigger custom handler
                    this.callHandler(
                        'select', new Tinycar.Model.DataItem(item)
                    );
                    
                    // Hide menu
                    this.hide();
                    
                }, 100);

            });
            
            return container;
        }
        
        // Build root container
        private buildRoot():void
        {
            // Create container
            this.htmlRoot = $('<div>').
                attr('class', 'tinycar-ui-menu');
            
            // Add to page
            Tinycar.Page.addNode(this.htmlRoot);
        }
        
        // Call specified handler
        callHandler(name:string, data?:any):void
        {
            if (this.handlerList.hasOwnProperty(name))
                this.handlerList[name](data);
        }
        
        // Hide menu
        hide():boolean
        {
            // Already hidden
            if (!this.menuVisible)
                return false;
            
            // Update state
            this.menuVisible = false;
            
            // Reset itemlist
            this.itemList = [];
            
            // Hide
            this.htmlRoot.removeClass('is-visible');
            
            // Clear listeners
            Tinycar.System.clearEvent('click', this.clickEvent);
            Tinycar.System.clearEvent('resize', this.resizeEvent);
            
            // Trigger custom handler
            this.callHandler('hide');
            
            return true;
        }
        
        // Check if menu is visible
        isVisible():boolean
        {
            return this.menuVisible;
        }
        
        // Set custom event hander
        setHandler(name:string, callback:Function):void
        {
            this.handlerList[name] = callback;
        }
        
        // Show menu
        show():boolean
        {
            // Already visible
            if (this.menuVisible)
                return false;
            
            // Update state
            this.menuVisible = true;
            
            // Set initial position
            this.updatePosition();
            
            // Show
            this.htmlRoot.addClass('is-visible');
            
            // Update menu position when resized
            this.resizeEvent = Tinycar.System.addEvent('resize', () =>
            {
                this.updatePosition();
            });
            
            // Update menu position when resized
            this.clickEvent = Tinycar.System.addEvent('click', () =>
            {
                this.hide();
            });
            
            // Trigger custom handler
            this.callHandler('show');
            
            return true;
        }
        
        // Update contents
        update():void
        {
            // Create root container once
            if (!(this.htmlRoot instanceof Object))
                this.buildRoot();
            
            // Clear existing items
            this.htmlRoot.empty();
            
            // Build menu items
            this.itemList.forEach((item:Object) =>
            {
                this.htmlRoot.append(this.buildItem(item));
            });
        }
        
        // Update menu
        private updatePosition():void
        {
            // Resolve parent dimensions just once
            if (typeof this.parentHeight !== 'number')
            {
                this.parentWidth  = this.parentNode.outerWidth();
                this.parentHeight = this.parentNode.outerHeight();
            }
            
            // Get parent node position
            let parentP = this.parentNode.offset();
            
            // Get menu dimensions
            let menuW = this.htmlRoot.outerWidth();
            
            // Update menu position
            this.htmlRoot.css({
                left : parentP.left - menuW + this.parentWidth,
                top  : parentP.top + this.parentHeight
            });
        }
    }
}