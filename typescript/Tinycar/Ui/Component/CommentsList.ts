module Tinycar.Ui.Component
{
	export class CommentsList extends Tinycar.Main.Component
	{
		private Button:Tinycar.Ui.Button;
		private htmlAmount:JQuery;
		private htmlList:JQuery;
		private Input:Tinycar.Ui.Component.TextInput;
	

		// Build content
		buildContent()
		{
			// Build elements
			super.buildContent();
			
			// Build heading
			if (this.Model.hasString('heading'))
				this.buildHeading();
			
			// Build list container
			this.buildList();
			
			// Build new message
			this.buildInput();
			this.buildButton();
			
			// Load list
			this.loadList();
		}
		
		// Build butotn
		private buildButton():void
		{
			// Create instance
			let instance = new Tinycar.Ui.Button({
				style : 'theme-button',
				size  : 'small',
				label : this.Model.getString('insert_button')
			});
			
			// When clicked
			instance.setHandler('click', () =>
			{
				this.action('insert', {message:this.Input.getDataValue()}, () =>
				{
					// Show response
					this.View.onResponse(new Tinycar.Model.DataItem({
						toast : this.Model.get('insert_toast')
					}));
					
					// Clear current data value
					this.Input.setDataValue(null);
					
					// Refresh self
					this.refresh();
				});
			});
			
			// Add to container
			this.htmlRoot.append(instance.build());
			
			// Remember
			this.Button = instance;
		}
		
		// Build heading
		private buildHeading():void
		{
			// Create contaner
			let container = $('<div>').
				attr('class', 'heading').
				text(this.Model.get('heading')).
				appendTo(this.htmlRoot);
			
			// Add amount
			this.htmlAmount = $('<span>').
				attr('class', 'amount').
				text('0').
				appendTo(container);
		}
		
		// Build input
		private buildInput():void
		{
			// Textarea parameters
			let params = new Tinycar.Model.DataItem({
				type_name   : 'TextInput',
				type_label  : this.Model.getString('insert_label'),
				placeholder : this.Model.getString('insert_placeholder'),
				rows        : 5
			});
			
			// Create textarea component
			let instance = new Tinycar.Ui.Component.TextInput(
				this.App, this.View, params
			);
			
			// When value existance changes
			instance.setHandler('value', (exists:boolean) =>
			{
				this.Button.setAsEnabled(exists);
			});
			
			// Build input
			this.htmlRoot.append(instance.build());
			
			// Show
			instance.setAsVisible(true);
			
			// Remember
			this.Input = instance;
		}
		
		// Build list container
		private buildList():void
		{
			this.htmlList = $('<div>').
				attr('class', 'list').
				appendTo(this.htmlRoot);
		}
		
		// Build single list item
		private buildListItem(item:Object):JQuery
		{
			// Create container
			let container = $('<div>').
				attr('class', 'item');
			
			// Format source string
			let source = Tinycar.Locale.getText('commentslist_author_wrote_at', {
				author : item['author'],
				time   : Tinycar.Locale.toDate(item['created'], 'datetime') 
			});
			
			// Add container for message line
			let line = $('<div>').
				attr('class', 'line').
				text(source).
				appendTo(container);
			
			// Add Message icon to line
			$('<span>').
				attr('class', 'icon icon-tiny icon-talk').
				prependTo(line);
			
			// Add message contents
			$('<div>').
				attr('class', 'message').
				html(item['message']).
				appendTo(container);
			
			return container;
		}
		
		// Load new list of comments
		private loadList():void
		{
			// Load data from action
			this.action('data', {}, (data:Array<Object>) => 
			{
				// Clear existing list items
				this.htmlList.empty();
				
				// Update message amount
				this.htmlAmount.text('(' + data.length + ')');
				
				// Create new items
				data.forEach((item:Object) =>
				{
					this.htmlList.append(
						this.buildListItem(item)
					);
				});
			});
		}
		
		// @see Tinycar.Main.Component.refresh()   
		refresh():void
		{
			this.loadList();
		}
		
		// @see Tinycar.Main.Field.start()
		start():void
		{
			super.start();
			this.Input.start();
		}
	}
}