module Tinycar.Ui.Component
{
    export class NavigationList extends Tinycar.Main.Component
    {


        // Build content
        buildContent()
        {
            // Build elements
            super.buildContent();
            this.buildList();
        }

        // Build textarea field
        private buildList():void
        {
            // Create container
            let container = $('<div>').
                attr('class', 'list').
                appendTo(this.htmlRoot);

            // Create options
            this.Model.get('options').forEach((item:Object) =>
            {
                container.append(this.buildListItem(item));
            });
        }

        // Build list items
        private buildListItem(item:Object):JQuery
        {
            // Resolve target path
            let link = Tinycar.Url.getWithVars(
                this.Model.getObject('link'), {
                    url   : Tinycar.Url.getParams(),
                    item  : {name:item['name']}
                }
            );

            // Create container
            let container = $('<a>').
                attr('href', Tinycar.Url.getAsPath(link)).
                attr('class', 'item').
                text(item['label']);

            // Link target matches current URL
            if (Tinycar.Url.isPathMatch(link))
                container.addClass('is-active');

            // When clicked
            container.click((e:JQueryMouseEventObject) =>
            {
                if (e.ctrlKey === false && e.shiftKey === false)
                {
                    e.preventDefault();
                    Tinycar.Url.openUrl(container.attr('href'));
                }
            });

            return container;
        }
    }
}
