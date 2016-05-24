module Tinycar.Ui.Component
{
    export class SelectList extends Tinycar.Main.Field
    {
        private fldList:JQuery;


        // @see Tinycar.Main.Field.build()
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
                appendTo(this.htmlContent);

            // Add arrow symbol
            $('<span>').
                attr('class', 'icon icon-small icon-sort-down').
                appendTo(container);

            // Add select menu
            this.fldList = $('<select>').
                attr('size', 1).
                attr('disabled', 'disabled').
                appendTo(container);

            // Get options
            let options = this.Model.get('options');

            // Create options
            options.forEach((item:Object) =>
            {
                $('<option>').
                    attr('value', item['name']).
                    text(item['label']).
                    appendTo(this.fldList);
            });

            // When value is changed
            this.fldList.change((e:Event) =>
            {
                this.setDataValue(this.fldList.val());
            });

            // No initial value, select first option
            if (this.Model.get('data_value') === null)
            {
                if (options.length > 0)
                    this.Model.set('data_value', options[0]['name']);
            }

            // Select initial custom value
            if (this.Model.get('data_value') !== null)
                this.fldList.val(this.Model.get('data_value'));
        }

        // @see Tinycar.Main.Field.focus()
        focus():void
        {
            this.fldList.focus();
        }

        // @see Tinycar.Main.Field.setAsEnabled()
        setAsEnabled(status:boolean):boolean
        {
            // State did not change
            if (!super.setAsEnabled(status))
                return false;

            // Update field status
            this.fldList.prop('disabled', !status);
            return true;
        }
    }
}
