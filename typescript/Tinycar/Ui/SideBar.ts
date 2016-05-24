module Tinycar.Main
{
    export class SideBar
    {
        private htmlRoot:JQuery;
        private View:Tinycar.Main.View;
        private Model:Tinycar.Model.DataItem;

        // Initiate class
        constructor(view:Tinycar.Main.View, model:Object)
        {
            this.View = view;
            this.Model = new Tinycar.Model.DataItem(model);
        }

        // Build
        build():JQuery
        {
            // Build elements
            this.buildRoot();
            this.buildComponents();
            this.buildResizeGap();

            return this.htmlRoot;
        }

        // Build components
        private buildComponents():void
        {
            // Create container
            let container = $('<div>').
                attr('class', 'components').
                appendTo(this.htmlRoot);

            // Create components
            this.Model.get('components').forEach((data:Object) =>
            {
                // Create component
                let instance = this.View.createComponent(data);
                container.append(instance.build());

                // Show component
                instance.setAsVisible(true);
            });
        }

        // Build resize gap
        private buildResizeGap():void
        {
            // Create new instance
            let instance = new Tinycar.Ui.Main.ResizeGap(
                'sidebar8', this.htmlRoot
            );

            // Default width
            instance.setDefaultWidth(
                this.Model.getNumber('default_width')
            );

            // Set allowed width range
            instance.setMinWidth(64);
            instance.setMaxWidth(250);

            // Set limit where we should collapse to minimum width
            instance.setMinCollapse(100);

            // After resized
            instance.setHandler('resized', (width:number) =>
            {
                Tinycar.Page.setSideBarWidth(width);
            });

            // Add to container
            this.htmlRoot.append(instance.build());
        }

        // Build root container
        private buildRoot():void
        {
            this.htmlRoot = $('<div>').
                attr('id', 'tinycar-main-sidebar').
                attr('class', 'theme-base');
        }
    }
}
