module Tinycar.Ui.Component
{
    export class NumbersWidget extends Tinycar.Main.Component
    {
        private htmlList:JQuery;


        // @see Tinycar.Main.Component.buildContent()
        buildContent():void
        {
            super.buildContent();

            // Build heading
            if (this.Model.hasString('heading'))
                this.buildHeading();

            // Build list container
            this.buildList();

            // Load list
            this.loadList();
        }

        // Build heading
        private buildHeading():void
        {
            $('<div>').
                attr('class', 'heading').
                text(this.Model.get('heading')).
                appendTo(this.htmlRoot);
        }

        // Build list container
        private buildList():void
        {
            this.htmlList = $('<div>').
                attr('class', 'list').
                appendTo(this.htmlRoot);
        }

        // Build list item
        private buildListItem(item:Object, data:Object):JQuery
        {
            // Create container
            let container = $('<div>').
                addClass('item');

            // Add value
            $('<span>').
                attr('class', 'value theme-text').
                text(data['value']).
                appendTo(container);

            // Add label
            $('<span>').
                attr('class', 'label').
                text(item['label']).
                appendTo(container);

            return container;
        }

        // Load list
        private loadList():void
        {
            // Load data from action
            this.action('data', {}, (data:Object) =>
            {
                // Clear existing items
                this.htmlList.empty();

                // Amount of options visible
                var amount = 0;

                // Current options
                let options = this.Model.getObject('options');

                // Create options
                for (var name in options)
                {
                    // Create option only if we have data for it
                    if (!data.hasOwnProperty(name))
                        continue;

                    // Increment count
                    ++amount;

                    // Create option
                    this.htmlList.append(
                        this.buildListItem(options[name], data[name])
                    );
                }

                // Update root styles
                this.htmlRoot.addClass('options-' + amount)
            });
        }
    }
}
