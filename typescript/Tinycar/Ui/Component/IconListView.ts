module Tinycar.Ui.Component
{
    import m = Tinycar.Main;
    import u = Tinycar.Ui;

    export class IconListView extends m.Component
    {
        htmlList:JQuery;


        // Build content
        buildContent()
        {
            this.buildList();
            this.buildItems();
        }

        // Build items
        buildItems():void
        {
            this.action('data', {}, (list:Array<Object>) =>
            {
                // Create icons
                list.forEach((item:Object) =>
                {
                    let icon = new u.Main.Icon({
                        path  : {app:item['id'], view:'default'},
                        label : item['name'],
                        color : item['color'],
                        icon  : item['icon']
                    });
                    this.htmlList.append(icon.build());
                });
            });
        }

        // Build list container
        buildList():void
        {
            this.htmlList = $('<div>').
                attr('class', 'list').
                appendTo(this.htmlRoot);
        }
    }
}
