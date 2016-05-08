module Tinycar.Ui.Component
{
	export class RichText extends Tinycar.Main.Field
	{
		private htmlEditor:JQuery;


		// Build content
		buildContent()
		{
			// Build elements
			super.buildContent();
			this.buildEditor();

			// Load external module
			Tinycar.Page.loadVendor('trumbowyg', () =>
			{
				this.initEditor();
			});
		}

		// Build editor
		private buildEditor():void
		{
			// Create container
			this.htmlEditor = $('<textarea>').
				attr('class', 'editor').
				attr('placeholder', this.Model.get('placeholder')).
				prop('spellcheck', false).
				prop('value', this.getDataValue()).
				appendTo(this.htmlContent);
		}


		// Initiate editor with vendor functionality
		private initEditor():void
		{
			this.htmlEditor.trumbowyg({
			    removeformatPasted: true // Remove formatting from pasted content
			});

			// Disable spellcheck
			$('div.trumbowyg-editor:first').prop('spellcheck', false);

			// When value is changed
			this.htmlEditor.on('tbwchange', () =>
			{
				// Update current value
				this.setDataValue(this.htmlEditor.trumbowyg('html'));
			});
		}

		// @see Tinycar.Main.Field.focus()
		focus():void
		{
			this.htmlEditor.focus();
		}

		// @see Tinycar.Main.Field.setAsEnabled()
		setAsEnabled(status:boolean):boolean
		{
			// State did not change
			if (!super.setAsEnabled(status))
				return false;

			// Update field status
			this.htmlEditor.prop('disabled', !status);

			// Update rich text editor status if Trumbowyg has been initialized
			if (this.htmlEditor.trumbowyg)
				this.htmlEditor.trumbowyg(status ? 'enable' : 'disable');

			return true;
		}
	}
}
