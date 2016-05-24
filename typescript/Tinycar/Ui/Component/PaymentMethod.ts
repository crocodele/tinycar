module Tinycar.Ui.Component
{
    export class PaymentMethod extends Tinycar.Main.Field
    {

        // @see Tinycar.Main.Field.buildContent()
        buildContent()
        {
            // Build elements
            super.buildContent();
            this.buildMessage();
            this.buildMethods();
        }

        // Build visible message, if any
        private buildMessage():void
        {
            // Show messages
            this.Model.getList('messages').forEach((message:Object) =>
            {
                $('<div>').
                    attr('class', 'message').
                    text(message['label']).
                    appendTo(this.htmlContent);
            });
        }

        // Build single method item
        private buildMethod(model:Tinycar.Model.DataItem):JQuery
        {
            // Create container
            let container = $('<a>').
                attr('tabindex', 0).
                addClass('item').
                addClass('item-' + model.getString('type').toLowerCase());

            // When clicked
            container.click((e:Event) =>
            {
                // Prevent default
                e.preventDefault();

                // Remove rectangle focus
                container.blur();

                // Select method after a small delay
                window.setTimeout(() =>
                {
                    this.selectMethod(model);

                }, 200);
            });

            // Add image
            $('<img>').
                attr('src', 'assets/base/images/paymentmethod-paypal.png').
                attr('class', 'image').
                attr('alt', model.get('title')).
                appendTo(container);

            return container;
        }

        // Build method
        private buildMethods():void
        {
            // Create container
            let container = $('<div>').
                attr('class', 'list').
                appendTo(this.htmlContent);

            // Create method items
            this.Model.getList('methods').forEach((item:Object) =>
            {
                container.append(this.buildMethod(
                    new Tinycar.Model.DataItem(item)
                ));
            });
        }

        // Select specified method
        private selectMethod(model:Tinycar.Model.DataItem):void
        {
            // Go to remote URL
            this.action('select', {}, (data:Object) =>
            {
                Tinycar.Url.openUrl(data['redirect']);
            });
        }
    }
}
