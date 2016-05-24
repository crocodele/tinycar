module Tinycar.Ui.Component
{
    export class Button extends Tinycar.Main.Field
    {
        // Build button
        private buildButton():void
        {
            // Create button instance
            let instance = new Tinycar.Ui.Button(
                this.getButtonProperties()
            );

            // When clicked
            instance.setHandler('click', () =>
            {
                this.execute();
            });

            // Add to content
            this.htmlContent.append(instance.build());
        }

        // Build content
        buildContent()
        {
            // Build elements
            super.buildContent();
            this.buildButton();
        }

        // Execute button action
        private execute():void
        {
            // We have a custom dialog to open
            if (this.Model.hasString('button_dialog'))
            {
                this.App.openDialog(
                    this.Model.get('button_dialog')
                );
            }
            // Call remote service
            else
            {
                this.action('click', this.View.getComponentsData(), (value:number) =>
                {
                    this.View.onResponse(new Tinycar.Model.DataItem({
                        value : value,
                        toast : this.Model.get('button_toast')
                    }));

                    // Refresh visible components
                    this.View.refreshComponents();
                });
            }
        }

        // Get properties for button
        private getButtonProperties():Object
        {
            let result = {};

            // We have a custom icon
            if (this.Model.hasString('button_icon'))
            {
                result = {
                    style : 'theme-icon',
                    size  : 'tiny',
                    icon  : this.Model.get('button_icon')
                };
            }
            // We have a custom label
            else if (this.Model.hasString('button_label'))
            {
                result = {
                    style : 'theme-button',
                    size  : 'small',
                    label : this.Model.get('button_label')
                };

                // Add postfix to label when opening a dialog
                if (this.Model.hasString('button_dialog'))
                    result['label'] += '...';
            }

            return result;
        }
    }
}
