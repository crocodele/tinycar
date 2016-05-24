module Tinycar.Ui
{
    interface IHandlerList
    {
        [key:string]:Function;
    }

    export class Button
    {
        private handlerList:IHandlerList = {};
        private htmlRoot:JQuery;
        private isEnabled:boolean = true;
        private Menu:Tinycar.Ui.Menu;
        private Model:Tinycar.Model.DataItem;

        // Initiate class
        constructor(config:Object)
        {
            this.Model = new Tinycar.Model.DataItem(config);
        }

        // Build
        build():JQuery
        {
            // Build elements
            this.buildRoot();

            // Add icon
            if (this.Model.hasString('icon'))
                this.buildIcon();

            // Add label
            if (this.Model.hasString('label'))
                this.buildLabel();

            // Add loading icon
            this.buildLoading();

            return this.htmlRoot;
        }

        // Build icon
        private buildIcon():void
        {
            // Create icon image
            let icon = $('<span>').
                attr('class', 'icon icon-' + this.Model.get('icon')).
                appendTo(this.htmlRoot);

            // Set icon size
            icon.addClass((this.Model.has('size') ?
                'icon-' + this.Model.get('size') : 'icon-small'
            ));

            // Revert color for theme buttons
            if (this.Model.get('style') === 'theme-icon')
                icon.addClass('icon-lite');
        }

        // Build label
        private buildLabel():void
        {
            // Add label
            $('<span>').
                attr('class', 'label').
                text(this.Model.get('label')).
                appendTo(this.htmlRoot);

            // Set button size
            this.htmlRoot.addClass((this.Model.has('size') ?
                'button-' + this.Model.get('size') : 'button-default'
            ));
        }

        // Build loading icon
        private buildLoading():void
        {
            $('<span>').
                attr('class', 'icon icon-lite icon-small icon-loading').
                appendTo(this.htmlRoot);
        }

        // Build menu instance
        private buildMenu():void
        {
            // Create instance
            this.Menu = new Tinycar.Ui.Menu(this.htmlRoot);

            // When selected
            this.Menu.setHandler('select', (item:Object) =>
            {
                this.callHandler('option', item);
            });

            // When shown
            this.Menu.setHandler('show', () =>
            {
                this.htmlRoot.addClass('is-open');
            });

            // When hidden
            this.Menu.setHandler('hide', () =>
            {
                this.htmlRoot.removeClass('is-open');
            });
        }

        // Build root container
        private buildRoot():void
        {
            var linkTarget = null;

            // Create container
            this.htmlRoot = $('<a>').
                addClass('tinycar-ui-button').
                addClass(this.Model.get('style')).
                attr('tabindex', 0);

            // This is a theme icon
            if (this.Model.get('style') === 'theme-icon')
                this.htmlRoot.addClass('theme-base-lite');

            // This is a theme button
            else if (this.Model.get('style') === 'theme-button')
                this.htmlRoot.addClass('theme-base-lite');

            // We have a fixed link target
            if (this.Model.isObject('link'))
            {
                linkTarget = Tinycar.Url.getAsPath(
                    this.Model.get('link')
                );

                this.htmlRoot.attr('href', linkTarget);
            }

            // When clicked
            this.htmlRoot.click((e:Event) =>
            {
                e.preventDefault();

                // Set as action node when we have a click handler
                if (this.isEnabled && this.handlerList.hasOwnProperty('click'))
                    Tinycar.Page.setActionNode(this.htmlRoot);

                // Small delay for visual effect
                window.setTimeout(() =>
                {
                    // Must be enabled
                    if (this.isEnabled)
                    {
                        // We have options to display
                        if (this.handlerList.hasOwnProperty('options'))
                            this.showMenu();

                        // Open static URL
                        else if (typeof linkTarget === 'string')
                            Tinycar.Url.openUrl(linkTarget);

                        // Call custom handler
                        else
                            this.callHandler('click', e);
                    }

                }, 100);
            });

            // When key is pressed
            this.htmlRoot.keydown((e:JQueryKeyEventObject) =>
            {
                // Trigger button action when space bar or Enter is pressed
                if (e.which === 32 || e.which === 13)
                {
                    e.preventDefault();
                    this.callHandler('click');
                }
            });
        }

        // Call specified handler
        callHandler(name:string, data?:any):void
        {
            if (this.handlerList.hasOwnProperty(name))
                this.handlerList[name](data);
        }

        // Focus on button
        focus():void
        {
            this.htmlRoot.focus();
        }

        // Set button as enabled or disabled
        setAsEnabled(status:boolean):void
        {
            if (status)
            {
                this.isEnabled = true;
                this.htmlRoot.removeClass('is-disabled');
            }
            else
            {
                this.isEnabled = false;
                this.htmlRoot.addClass('is-disabled');
            }
        }

        // Set custom event hander
        setHandler(name:string, callback:Function):void
        {
            this.handlerList[name] = callback;
        }

        // Set internal link path
        setLink(link:Object):void
        {
            this.Model.set('link', link);
        }

        // Show options menu
        private showMenu():void
        {
            // Build menu instance once
            if (!(this.Menu instanceof Tinycar.Ui.Menu))
                this.buildMenu();

             // Add menu items using custom handler
               this.callHandler('options', this.Menu);

               // Update visible options
               this.Menu.update();

               // Show options
               this.Menu.show();
        }
    }
}
