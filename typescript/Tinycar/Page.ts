module Tinycar
{
    interface IVendor
    {
        script:string;
        styles?:string;
    }

    interface IVendorList
    {
        [key:string]:IVendor;
    }

    export module Page
    {
        export var actionNode:JQuery;
        export var htmlRoot:JQuery;
        export var htmlStyles:JQuery;
        export var loadedVendors:Array<string> = ['app'];

        // Custom styles
        export var customStyles:string = `

            a:hover {
                color: %base%;
            }

            input[type=text]:focus,
            select:focus,
            textarea:focus {
                border-color: %base%;
            }

            div.tinycar-ui-component-checkbox:not(old) label:hover > span.mark,
            div.tinycar-ui-component-radiolist:not(old) label:hover > span.mark {
                border-color: %base% !important;
            }

            div.tinycar-ui-component-checkbox:not(old) input:checked + label > span.mark,
            div.tinycar-ui-component-radiolist:not(old) input:checked + label > span.mark {

                border-color: %base% !important;
                background-color: %base% !important;

            }

            .theme-background {
                background-color: %base% !important;
            }

            .theme-text {
                color: %base%;
            }

            .theme-text-liter {
                color: %liter%;
            }

            .theme-base {
                background-color: %base%;
            }

            .theme-border {
                border-color: %base% !important;
            }

            a.theme-base-lite {

                border-color: %base% !important;
                background-color: %base% !important;
                color: #ffffff;

            }

            a.theme-base-lite:focus,
            a.theme-base-lite:hover {

                border-color: %dark% !important;
                background-color: %dark% !important;
                color: #ffffff;
                text-decoration: none;

            }

            a.theme-base-lite:active {

                border-color: %lite% !important;
                background-color: %lite% !important;
                color: #ffffff;

            }

            div.tinycar-ui-component-datagrid.is-selectable a.is-selected span.check {

                border-color: %base% !important;
                background-color: %base% !important;

            }

            div.tinycar-ui-component-datagrid.is-selectable a:hover span.check,
            div.tinycar-ui-component-datagrid.is-selectable a:focus span.check {
                border-color: %base% !important;
            }

            div.ui-datepicker td:hover > a {

                border-color: %base% !important;
                background-color: #ffffff !important;

            }

            div.ui-datepicker td:active > a {
                border-color: transparent !important;
            }

            div.ui-datepicker td.ui-datepicker-current-day > a {

                background-color: %base% !important;
                color: #ffffff !important;

            }

        `;

        // Add specified node to page
        export function addNode(node:JQuery):void
        {
            this.htmlRoot.append(node);
        }

        // Add style name to page
        export function addStyle(name:string):void
        {
            this.htmlRoot.addClass(name);
        }

        // Build
        export function build():void
        {
            // Get references
            this.htmlRoot = $('body:first');

            // Build styles container
            this.buildStyles();

            // Prepage page
            this.addStyle('is-prepared');
        }

        // Build styles container
        export function buildStyles():void
        {
            this.htmlStyles = $('<style>').
                attr('type', 'text/css').
                appendTo(this.htmlRoot);
        }

        // Clear style name from page
        export function clearStyle(name:string):void
        {
            this.htmlRoot.removeClass(name);
        }

        // Load specified vendor scripts and styles
        export function loadVendor(name:string, callback:Function):boolean
        {
            // This vendor has already been loaded once
            if (this.loadedVendors.indexOf(name) > -1)
            {
                callback();
                return true;
            }

            // Update state
            this.loadedVendors.push(name);

            // Load remote script with requireJS
            require([name], () =>
            {
                callback();
            });

            // Get vendor stylesheets
            let styles = Tinycar.Config.get('VENDOR_STYLES');

            // Load custom stylesheet, if we have some
            if (styles.hasOwnProperty(name))
            {
                $('<link>').
                    attr('type', 'text/css').
                    attr('rel', 'stylesheet').
                    attr('href', styles[name] + '.css').
                    appendTo($('head:first'));
            }

            return true;
        }

        // Set node that initiated current action
        export function setActionNode(node:JQuery):void
        {
            // Set new node
            if (node instanceof Object)
            {
                this.actionNode = node;
                this.actionNode.addClass('is-loading');
            }
            // Remove old node
            else if (this.actionNode instanceof Object)
            {
                this.actionNode.removeClass('is-loading');
                this.actionNode = null;
            }
        }

        // Set layout stule
        export function setLayoutName(name:string):void
        {
            // Set root styles
            this.htmlRoot.addClass('layout-' + name);

            // Theme background
            if (name !== 'main')
                this.htmlRoot.addClass('theme-background');
        }

        // Set sidebar width
        export function setSideBarWidth(width:number):void
        {
            this.htmlRoot.css('padding-left', width);
        }

        // Set page status
        export function setState(state:string):void
        {
            // Change root styles
            this.htmlRoot.addClass('is-' + state);

            // In case of aborted requrested, we need to remove
            // the unloading state after some time
            if (state === 'unloading')
            {
                window.setTimeout(() =>
                {
                    this.htmlRoot.removeClass('is-unloading');

                }, 3000);
            }
        }

        // Set page theme color
        export function setThemeColors(color:Object):void
        {
            let styles = this.customStyles;

            // Set colors
            for (var name in color)
            {
                styles = styles.
                    split('%' + name + '%').
                    join(color[name]);
            }

            // Update styles
            this.htmlStyles.html(styles);
        }

        // Set page title
        export function setTitle(parts:Array<string>):void
        {
            // Update title
            document.title = parts.join(' - ');
        }
    }
}
