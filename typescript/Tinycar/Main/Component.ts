module Tinycar.Main
{
    interface IHandlerList
    {
        [key:string]:Function;
    }

    export class Component
    {
        private handlerList:IHandlerList = {};
        private isCmpVisible:boolean = false;
        htmlRoot:JQuery;

        App:Tinycar.Ui.Application;
        Model:Tinycar.Model.DataItem;
        View:Tinycar.Ui.View;


        // Initiate class
        constructor(app:Tinycar.Ui.Application, view:Tinycar.Ui.View, model:Tinycar.Model.DataItem)
        {
            this.App = app;
            this.View = view;
            this.Model = model;
        }

        // Call this component's action
        action(name:string, params:Object, callback:Function):void
        {
            this.View.onComponent(
                this.Model.get('id'), name, params, callback
            );
        }

        // Build
        build():JQuery
        {
            // Build elements
            this.buildRoot();
            this.buildContent();

            return this.htmlRoot;
        }

        // Build content
        buildContent():void
        {
        }

        // Build root container
        buildRoot():void
        {
            // Type name, lowercased
            let name = this.Model.get('type_name').toLowerCase();

            this.htmlRoot = $('<div>').
                addClass(this.getRootStyles().join(' ')).
                addClass('tinycar-ui-component-' + name);
        }

        // Call specified handler
        callHandler(name:string, data?:any):void
        {
            if (this.handlerList.hasOwnProperty(name))
                this.handlerList[name](data);
        }

        // Get componetn id
        getId():string
        {
            return this.Model.get('id');
        }

        // Get root styles
        getRootStyles():Array<string>
        {
            return ['tinycar-main-component'];
        }

        // Get component's tab name
        getTabName():string
        {
            return this.Model.get('tab_name');
        }

        // Check if component has bindings for specified property
        hasBindProperty(name:string):boolean
        {
            let rules = this.Model.getObject('bind_rules');
            return rules.hasOwnProperty(name);
        }

        // Check if component is currently visible
        isVisible():boolean
        {
            return this.isCmpVisible;
        }

        // Process bind proerties for specified property name
        processBindRules():void
        {
            // Component binding rules
            let rules = this.Model.getObject('bind_rules');

            // Process all rules
            for (let name in rules)
                this.processBindRule(name);
        }

        // Process bind rules for specified property name
        processBindRule(name:string):void
        {
            // Component binding rules
            let rules = this.Model.getObject('bind_rules');

            // Current value
            var value = this.View.Bind.get(name);

            // Process rules
            rules[name].forEach((item:Object) =>
            {
                // Update visibility
                if (item['type'] === 'visible')
                    this.setAsVisible((item['value'] === value));
            });
        }

        // Refresh component
        refresh():void
        {
        }

        // Set component as visible or non-visible
        setAsVisible(visible:boolean):void
        {
            // Show
            if (!this.isCmpVisible && visible)
            {
                this.isCmpVisible = true;
                this.htmlRoot.addClass('is-visible');
            }
            // Hide
            else if (this.isCmpVisible && !visible)
            {
                this.isCmpVisible = false;
                this.htmlRoot.removeClass('is-visible');
            }
        }

        // Set custom event hander
        setHandler(name:string, callback:Function):void
        {
            this.handlerList[name] = callback;
        }

        // Start component after added to DOM
        start():void
        {
        }
    }
}
