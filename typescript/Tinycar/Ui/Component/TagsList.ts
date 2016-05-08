module Tinycar.Ui.Component
{
	interface IHtmlItems
	{
		[key:string]:JQuery;
	}

	export class TagsList extends Tinycar.Main.Field
	{
		private Button:Tinycar.Ui.Button;
		private fldInput:JQuery;
		private htmlField:JQuery;
		private htmlItems:IHtmlItems = {};
		private htmlList:JQuery;


		// Add new item to list from field value
		private addItemFromField():boolean
		{
			// Trim whitespace
			let name = jQuery.trim(this.fldInput.val());

			// Invalid item label
			if (!this.buildListItem(name))
				return false;

			// Add to current value
			let value = this.getDataValue();
			value.push(name);
			this.setDataValue(value);

			// Clear field and re-focus
			this.fldInput.val('').focus();

			return true;
		}

		// Build content
		buildContent()
		{
			// Build elements
			super.buildContent();
			this.buildList();
			this.buildInput();

			// Build initial items
			this.getDataValue().forEach((name:string) =>
			{
				this.buildListItem(name);
			});
		}

		// Build field container
		private buildField():void
		{
			this.htmlField = $('<div>').
				attr('class', 'field').
				appendTo(this.htmlContent);
		}

		// Build list container
		private buildList():void
		{
			// Create container
			this.htmlList = $('<div>').
				attr('class', 'list').
				appendTo(this.htmlContent);
		}

		// Build new item to list
		private buildListItem(name:string):boolean
		{
			// Item is an empty string
			if (name.length === 0)
				return false;

			// Lowercase for grouping
			var uname = name.toLowerCase();

			// Another item already exists, let's remove it in
			// preference of this one (e.g. for casing changes)
			if (this.htmlItems.hasOwnProperty(uname))
				this.clearListItem(uname);

			// Create item container
			let container = $('<div>').
				attr('class', 'item').
				text(name).
				insertBefore(this.htmlField)

			// Add close icon
			let close = $('<a>').
				attr('class', 'close').
				mousedown((e:Event) =>
				{
					e.preventDefault();
				}).
				click((e:Event) =>
				{
					e.preventDefault();

					// Don't do anything field is disabled
					if (!this.isFieldEnabled())
						return;

					this.clearListItem(uname);
				}).
				appendTo(container);

			// Add icon
			$('<span>').
				attr('class', 'icon icon-tiny icon-close').
				appendTo(close);

			// Update reference
			this.htmlItems[uname] = container;

			return true;
		}

		// Build input field
		private buildInput():void
		{
			// Create container
			this.htmlField = $('<div>').
				attr('class', 'field').
				appendTo(this.htmlList);

			// Add input field
			this.fldInput = $('<input>').
				attr('type', 'text').
				attr('placeholder', this.Model.getString('placeholder')).
				keydown((e:JQueryKeyEventObject) =>
				{
					// Pressed enter
					if (e.keyCode === 13)
					{
						e.preventDefault();
						this.addItemFromField();
					}
				}).
				appendTo(this.htmlField);

			// Create new button instance
			this.Button = new Tinycar.Ui.Button({
				style : 'field-icon',
				icon  : 'add'
			});

			// When clicked
			this.Button.setHandler('click', () =>
			{
				this.addItemFromField();
			});

			// Add to content
			this.htmlField.append(this.Button.build());
		}

		// Clear specified item from list
		clearListItem(uname:string):boolean
		{
			// No such item in list
			if (!this.htmlItems.hasOwnProperty(uname))
				return false;

			// Old name
			let name = this.htmlItems[uname].text();

			// Remove old node
			this.htmlItems[uname].remove();
			delete this.htmlItems[uname];

			// Find value from current value
			let value = this.getDataValue();
			let index = value.indexOf(name);

			// Remove value from list
			if (index > -1)
				value.splice(index, 1);

			// Update value
			this.setDataValue(value);

			return true;
		}

		// @see Tinycar.Main.Field.focus()
		focus():void
		{
			this.fldInput.focus();
		}

		// @see Tinycar.Main.Field.getDataValue()
		getDataValue():Array<string>
		{
			return this.Model.getList('data_value');
		}

		// @see Tinycar.Main.Field.setAsEnabled()
		setAsEnabled(status:boolean):boolean
		{
			// State did not change
			if (!super.setAsEnabled(status))
				return false;

			// Update field status
			this.fldInput.prop('disabled', !status);
			this.Button.setAsEnabled(status);
			return true;
		}

		// @see Tinycar.Main.Field.setDataValue()
		setDataValue(value:Array<string>):void
		{
			this.Model.set('data_value', value);
		}
	}
}
