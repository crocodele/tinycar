module Tinycar.View
{
	interface ITabItem
	{
		name:string;
		label:string;
	}

	interface IHtmlTabs
	{
		[key:string]:JQuery;
	}

	export class Tabs
	{
		private activeTab:string;
		private htmlRoot:JQuery;
		private htmlTabs:IHtmlTabs = {};
		private tabList:Array<Object> = [];
		private tabNames:Array<string> = [];
		private View:Tinycar.Ui.View;


		// Initiate class
		constructor(view:Tinycar.Ui.View)
		{
			this.View = view;
		}

		// Add new tab item
		addItem(item:ITabItem):void
		{
			this.tabList.push(item);
		}

		// Build
		build():JQuery
		{
			// Build elements
			this.buildRoot();
			this.buildLine();

			// Build tabs
			this.tabList.forEach((item:Object, index:number) =>
			{
				this.buildItem(item, index);
			});

			return this.htmlRoot;
		}

		// Build new tab item
		private buildItem(item:Object, index:number):void
		{
			// Create link container
			let container = $('<a>').
				attr('class', 'item').
				attr('tabindex', 0).
				text(item['label']).
				appendTo(this.htmlRoot);

			// When clicked
			container.click((e:Event) =>
			{
				e.preventDefault();
				this.View.showTab(item['name']);
			});

			// When key is pressed
			container.keydown((e:JQueryKeyEventObject) =>
			{
				// Open dialog when space bar or Enter is pressed
				if (e.which === 32 || e.which === 13)
				{
					e.preventDefault();
					container.click();
				}
				// Focus to previous tab link
				else if (e.which === 37 && index !== 0)
				{
					e.preventDefault();
					$(e.target).prev().focus();
				}
				// Focus to next tab link
				else if (e.which === 39 && index !== this.tabNames.length - 1)
				{
					e.preventDefault();
					$(e.target).next().focus();
				}
			});

			// Add name to list
			this.tabNames.push(item['name']);

			// Remember instance
			this.htmlTabs[item['name']] = container;
		}

		// Build lien
		private buildLine():void
		{
			$('<span>').
				attr('class', 'line').
				appendTo(this.htmlRoot);
		}

		// Build root container
		private buildRoot():void
		{
			this.htmlRoot = $('<div>').
				attr('class', 'tinycar-view-tabs');
		}

		// Get first available tab name
		getFirstTab():string
		{
			return (this.tabNames.length > 0 ?
				this.tabNames[0] : 'default'
			);
		}

		// Get current active name
		getActiveTab():string
		{
			return this.activeTab;
		}

		// Set specified tab name as active
		setActiveTab(name:string):void
		{
			// Unselect current tab
			if (typeof this.activeTab === 'string')
			{
				this.htmlTabs[this.activeTab].
					removeClass('is-active').
					removeClass('theme-border');
			}

			// Remember
			this.activeTab = name;

			// Select new tab
			if (this.htmlTabs.hasOwnProperty(name))
			{
				this.htmlTabs[this.activeTab].
					addClass('is-active').
					addClass('theme-border');
			}
		}
	}
}
