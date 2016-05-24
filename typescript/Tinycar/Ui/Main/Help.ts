module Tinycar.Ui.Main
{
    interface IConfig
    {
        content:string;
    }

    export class Help
    {
        private contentText:string;
        private htmlContent:JQuery;
        private htmlRoot:JQuery;
        private isContentVisible:boolean = false;


        // Initiate class
        constructor(content:string)
        {
            this.contentText = jQuery.trim(content);
        }

        // Build
        build():JQuery
        {
            // Build elements
            this.buildRoot();
            this.buildLink();

            return this.htmlRoot;
        }

        // Build content
        private buildContent():void
        {
            this.htmlContent = $('<div>').
                attr('class', 'tinycar-ui-main-help-content').
                text(this.contentText).
                appendTo(document.body);
        }

        // Build link
        private buildLink():void
        {
            // Create container
            let container = $('<a>').
                attr('href', '#help').
                attr('class', 'link').
                click((e:Event) =>
                {
                    e.preventDefault();
                    this.toggleContent();
                }).
                appendTo(this.htmlRoot);

            // Add icon
            $('<span>').
                attr('class', 'icon icon-tiny icon-help').
                appendTo(container);
        }

        // Build root container
        private buildRoot():void
        {
            // Create container
            this.htmlRoot = $('<span>').
                attr('class', 'tinycar-ui-main-help');
        }

        // Hide content
        private hideContent():void
        {
            // Update root styles
            this.htmlRoot.removeClass('is-open');

            // Hide content
            this.htmlContent.hide();

            // Update state
            this.isContentVisible = false;
        }

        // Show or hide content
        private showContent():void
        {
            // Create once
            if (!(this.htmlContent instanceof Object))
                this.buildContent();

            // Get current container position
            let position = this.htmlRoot.offset();

            // Update content position
            this.htmlContent.css({
                left : position.left,
                top  : position.top
            });

            // Show content
            this.htmlContent.show();

            // Update root styles
            this.htmlRoot.addClass('is-open');

            // When clicked anywhere on page
            var onClick = () =>
            {
                // Unbind listeners
                $(window).unbind('click', onClick);
                $(window).unbind('resize', onResize);

                // Close content
                this.hideContent();
            };

            // When window is resized
            var onResize = (e:Event) =>
            {
                onClick();
            };

            // Hide content when clicked anywhere on page,
            // but start only after this click event has passed
            window.setTimeout(() =>
            {
                // Bind listeners
                $(window).bind('click', onClick);
                $(window).bind('resize', onResize);

            }, 10);

            // Update state
            this.isContentVisible = true;
        }

        // Toggle content between open or hidden
        private toggleContent():void
        {
            if (this.isContentVisible === true)
                this.hideContent();
            else
                this.showContent();
        }
    }
}
