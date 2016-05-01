module Tinycar
{
    interface IAmountList
    {
        [key:string]:number;
    }
    
    interface IEventList
    {
        [key:string]:Array<Function>;
    }
    
    interface IHandlerList
    {
        click:Function;
        keydown:Function;
        resize:Function;
    }
    
    interface ITypeList
    {
        [key:string]:string;
    }

    export class EventManager
    {
        // Current event total
        private eventAmount:IAmountList = {};
        private eventList:IEventList = {};
    
        // Supported event names and used native events
        private handlerTypes:ITypeList = {
                
            "click"  : "click",
            "ctrl+s" : "keydown",
            "esc"    : "keydown",
            "resize" : "resize",
    
        };
    
        // Handlers for each native event type
        private handlerList:IHandlerList = {
                
            // When clicked
            click : (e:Event) =>
            {
                this.callEvent('click', e);
            },
    
            // When key is pressed down
            keydown : (e:JQueryKeyEventObject) =>
            {
                // Ctrl + S
                if (e.ctrlKey && !e.shiftKey && e.which === 83)
                    this.callEvent('ctrl+s', e);

                // Esc
                else if (e.which === 27)
                    this.callEvent('esc', e);
            },
            
            // When resized
            resize : (e:Event) =>
            {
                this.callEvent('resize', e);
            }
            
        };
        
        // Add event listener
        addEvent(name:string, callback:Function):number
        {
            // Initiate list
            if (!this.eventList.hasOwnProperty(name))
            {
                this.eventAmount[name] = 0;
                this.eventList[name] = [];
            }
            
            // Add event listener
            if (this.eventAmount[name] === 0)
                this.addListener(name);
            
            // Next index number
            let index = this.eventList[name].length;
    
            // Add callback to list
            this.eventList[name].push(callback);
            this.eventAmount[name] += 1;
            
            return index;
        }
        
        // Add internal listener
        private addListener(name:string):boolean
        {
            // Event name is not supported
            if (!this.handlerTypes.hasOwnProperty(name))
                return false;
            
            // Event listener type
            let type = this.handlerTypes[name];
            
            // Check if other events exist
            for (let k in this.handlerTypes)
            {
                // Does not use the same type
                if (type !== this.handlerTypes[k])
                    continue;
                
                // No handlers have for this event
                if (!this.eventAmount.hasOwnProperty(k))
                    continue;
                
                // We already have handlers for this event
                if (this.eventAmount[k] > 0)
                    return false;
            }
            
            // Unbind document listener
            if (name === 'click')
                $(document).bind(type, this.handlerList[type]);
            
            // Unbind window listener
            else
                $(window).bind(type, this.handlerList[type]);
            
            return true;
        }
        
        // Call specified event
        callEvent(name:string, e:Event):boolean
        {
            // Unknown event handler
            if (!this.eventAmount.hasOwnProperty(name))
                return false;
            
            // We no longer have handlers for this event
            if (this.eventAmount[name] === 0)
                return false;

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
        
        // Clear specified event
        clearEvent(name:string, index:number):void
        {
            // Update
            this.eventAmount[name] -= 1;
            this.eventList[name][index] = null;
            
            // Clear event listener
            if (this.eventAmount[name] === 0)
                this.clearListener(name);
        }
        
        // Clear internal listener
        private clearListener(name:string):boolean
        {
            // Event name is not supported
            if (!this.handlerTypes.hasOwnProperty(name))
                return false;
            
            // Event listener type
            let type = this.handlerTypes[name];
            
            // Check if other events exist
            for (let k in this.handlerTypes)
            {
                // Does not use the same type
                if (type !== this.handlerTypes[k])
                    continue;
                
                // No handlers have for this event
                if (!this.eventAmount.hasOwnProperty(k))
                    continue;
                
                // We still have handlers for this event
                if (this.eventAmount[k] > 0)
                    return false;
            }
            
            // Unbind document listener
            if (name === 'click')
                $(document).unbind(type, this.handlerList[type]);
            
            // Unbind window listener
            else
                $(window).unbind(type, this.handlerList[type]);
            
            return true;
        }
    }
}
