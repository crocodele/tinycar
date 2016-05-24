module Tinycar.Ui.Component
{
    interface IListItems
    {
        [key:number]:JQuery;
    }

    interface ISettingsData
    {
        order:number;
        sort:string;
        query:string;
    }

    export class DataGrid extends Tinycar.Main.Component
    {
        private dataList:Array<Object> = [];
        private fldSearch:JQuery;
        private htmlListColumns:Array<JQuery> = [];
        private htmlListItems:IListItems = {};
        private htmlListData:JQuery;
        private htmlListTable:JQuery;
        private isSearchVisible:boolean = false;
        private isSelectable:boolean = false;
        private lastQuery:string = '';
        private linkPattern:string;
        private selectedItems:Array<number> = [];
        private selectedLimit:number = 0;
        private settingsId:string;
        private viewList:Array<number> = [];

        // Initial settings
        private settingsData:ISettingsData = {
            order : 0,
            sort  : 'asc',
            query : ''
        };

        // Build menu action
        private buildMenuAction():void
        {
            // Create menu button
            let button = new Tinycar.Ui.Button({
                style : 'dark-icon',
                icon  : 'menu-vertical',
                size  : 'small'
            });

            // When option is selected
            button.setHandler('option', (model:Tinycar.Model.DataItem) =>
            {
                // Flip search
                if (model.get('name') === 'search')
                    this.showSearch(!this.isSearchVisible, true);

                // Refresh datagrid
                else if (model.get('name') === 'refresh')
                    this.refresh();

                // Link to service
                else if (model.hasString('link_service'))
                {
                    Tinycar.Url.openUrl(Tinycar.Api.getServiceLink(
                        model.get('link_service')
                    ));
                }
            });

            // When options are provided
            button.setHandler('options', (menu:Tinycar.Ui.Menu) =>
            {
                // Add custom items
                this.Model.getList('actions').forEach((item:Object) =>
                {
                    menu.addItem('default', item);
                });

                // Toggle search
                menu.addItem('search', {
                    label: Tinycar.Locale.getText('action_search')
                });

                // Refresh
                menu.addItem('refresh', {
                    label : Tinycar.Locale.getText('action_refresh')
                });
            });

            // Add to container
            this.htmlListTable.append(button.build());
        }

        // Build content
        buildContent()
        {
            // Build elements
            this.buildListTable();
            this.buildMenuAction();
            this.buildListColumns();
            this.buildListData();

            // Default styles
            this.htmlRoot.addClass('search-hidden');

            // Resolve data item link pattern
            if (this.Model.isObject('link'))
            {
                this.linkPattern = Tinycar.Url.getAsPath(
                    this.Model.get('link')
                );
            }
            // Datagrid is selectable
            else
            {
                // Update state
                this.isSelectable = true;

                // Update root styles
                this.htmlRoot.addClass('is-selectable');

                // Update properties
                this.selectedLimit = this.Model.getNumber('data_limit');
                this.selectedItems = this.Model.getList('data_value');
            }

            // Load settings from session
            this.loadSettings();

            // Set initial visual column order
            this.setColumnOrder(this.settingsData.order);

            // Set initial search state
            if (this.settingsData.query.length > 0)
                this.showSearch(true, false);

            // Load list content
            this.loadList();
        }

        // Build column item
        private buildListColumn(index:number, item:Object):JQuery
        {
            // Create container
            let container = $('<div>').
                attr('class', 'column');

            // Add link
            let link = $('<a>').
                text(item['label']).
                appendTo(container);

            // When clicked
            link.click((e:Event) =>
            {
                e.preventDefault();

                // Update sort and order
                this.setColumnSort(index);
                this.setColumnOrder(index);
                this.storeSettings();

                // Sort and update list
                this.updateListOrder();
                this.buildListItems();
            });

            // Add icon for down
            $('<span>').
                attr('class', 'icon icon-small icon-sort-down').
                appendTo(link);

            // Add icon for up
            $('<span>').
                attr('class', 'icon icon-small icon-sort-up').
                appendTo(link);

            // Set container width
            if (item['width'] > 0)
                container.css('width', item['width']);

            // Remember column
            this.htmlListColumns.push(container);

            return container;
        }

        // Build columns
        private buildListColumns():void
        {
            // Create container
            var container = $('<div>').
                attr('class', 'columns').
                appendTo(this.htmlListTable);

            // Create columns
            this.Model.get('columns').forEach((item:Object, index:number) =>
            {
                container.append(this.buildListColumn(index, item));
            });
        }

        // Build list container
        private buildListData():void
        {
            this.htmlListData = $('<div>').
                attr('class', 'list').
                appendTo(this.htmlListTable);
        }

        // Build single list item
        private buildListItem(index:number):void
        {
            // Get reference to item instance
            var data = this.dataList[index];

            // Target item id
            let id = data['id']['value'];

            // Create container
            let container = $('<a>').
                attr('class', 'item').
                appendTo(this.htmlListData);

            // Link to static URL
            if (typeof this.linkPattern === 'string')
            {
                // Add link
                container.attr('href', this.linkPattern.
                    split('$model.id').join(id)
                );
            }
            // Selectable row
            else if (this.isSelectable === true)
            {
                // Preselect item
                if (this.selectedItems.indexOf(id) > -1)
                    container.addClass('is-selected');
            }

            // When clicked
            container.click((e:Event) =>
            {
                e.preventDefault();

                if (this.isSelectable === true)
                    this.selectListItem(id);
                else
                    Tinycar.Url.openUrl(container.attr('href'));
            });

            // Create columns
            this.Model.get('columns').forEach((column:Object, index:number) =>
            {
                container.append(
                    this.buildListItemColumn(index, column, data)
                );
            });

            // Remember
            this.htmlListItems[id] = container;
        }

        // Build single list item's column value
        private buildListItemColumn(index:number, column:Object, data:Object):JQuery
        {
            // Create content holder
            let container = $('<span>').
                text(data[column['name']]['text']).
                attr('class', 'column');

            // First column, add checkbox
            if (index === 0)
            {
                // Add checkbox container
                let check = $('<span>').
                    attr('class', 'check').
                    appendTo(container);

                // Add icon
                $('<span>').
                    attr('class', 'icon icon-tiny icon-lite icon-check').
                    appendTo(check);
            }

            return container;
        }

        // Build list rows
        private buildListItems():void
        {
            // Clear existing items
            this.htmlListItems = {};
            this.htmlListData.empty();

            // Create new list items
            this.dataList.forEach((item:Object, index:number) =>
            {
                if (this.viewList.indexOf(index) > -1)
                    this.buildListItem(index);
            });
        }

        // Build list container
        private buildListTable():void
        {
            this.htmlListTable = $('<div>').
                attr('class', 'table').
                appendTo(this.htmlRoot);
        }

        // Build search field
        private buildSearchField():void
        {
            var timer = null;

            // Create container
            let container = $('<div>').
                attr('class', 'search').
                insertBefore(this.htmlListData);

            // Create content
            let content = $('<div>').
                attr('class', 'content theme-base').
                appendTo(container);

            // Add input field
            let input = $('<input>').
                attr('type', 'text').
                attr('placeholder', Tinycar.Locale.getText('datagrid_type_search')).
                prop('value', this.settingsData.query).
                appendTo(content);

            // Add reset link
            let reset = $('<a>').
                attr('class', 'reset').
                attr('title', Tinycar.Locale.getText('datagrid_reset_search')).
                click((e:Event) =>
                {
                    e.preventDefault();
                    this.showSearch(false, false);
                }).
                appendTo(content);

            // Add resset icon
            $('<span>').
                attr('class', 'icon icon-small icon-close').
                appendTo(reset);

            // When value is changed
            input.change((e:Event) =>
            {
                // Update items to match query
                this.filterDataItems(input.val());
            });

            // When value is typed
            input.keydown((e:JQueryKeyEventObject) =>
            {
                // Clear old timer
                if (timer)
                    window.clearTimeout(timer);

                // Hide search on ESC
                if (!e.ctrlKey && e.keyCode === 27)
                {
                    e.preventDefault();
                    this.showSearch(false, false);
                }
                // Update with a timer
                else
                {
                    timer = window.setTimeout(() =>
                    {
                        timer = null;
                        input.trigger('change');

                    }, 250);
                }
            });

            // Remember input instance
            this.fldSearch = input;
        }

        // Filter data items to match search query
        private filterDataItems(query:string):void
        {
            // Already filtered to this query
            if (this.lastQuery === query)
                return;

            // Update settings
            this.settingsData.query = query;
            this.storeSettings();

            // Update state
            this.lastQuery = query;

            // Normalize for query string comparisons
            query = jQuery.trim(query.toLowerCase());

            // Query is empty, show all items
            if (query.length === 0)
                this.resetViewList();

            // Find matching rows
            else
            {
                var result = [];

                // Find matching items
                this.dataList.forEach((item:Object, index:number) =>
                {
                    // Study each column value
                    for (var name in item)
                    {
                        // We found a match
                        if (item[name]['text'].toLowerCase().indexOf(query) > -1)
                        {
                            // We found a match
                            result.push(index);
                            break;
                        }
                    }
                });

                // Update visible list
                this.viewList = result;
            }

            // Build list items agains
            this.buildListItems();
        }

        // Get selected items
        getSelectedItems():Array<number>
        {
            return this.selectedItems;
        }

        // Load list
        private loadList():void
        {
            // Load data from action
            this.action('data', {}, (data:Array<Object>) =>
            {
                // Set data list
                this.dataList = data;

                // Reset view index
                this.resetViewList();

                // Order list to current settings
                this.updateListOrder();

                // Set initial filter query
                if (this.settingsData.query.length > 0)
                    this.filterDataItems(this.settingsData.query);

                // Build list
                this.buildListItems();

                // Resize when inside dialog
                $(window).trigger('resize');
            });
        }

        // Load settings
        private loadSettings():void
        {
            // Resolve settings id
            this.settingsId = 'datagrid-' +
                Tinycar.Url.getUid() + '-' +
                this.getId();

            // Get existing settings from session
            let settings = Tinycar.Session.get(
                this.settingsId
            );

            // Set custom order
            if (settings.hasOwnProperty('order'))
                this.settingsData.order = settings['order'];

            // Set custom sort
            if (settings.hasOwnProperty('sort'))
                this.settingsData.sort = settings['sort'];

            // Set custom query
            if (settings.hasOwnProperty('query'))
                this.settingsData.query = settings['query'];
        }

        // @see Tinycar.Main.Component.refresh()
        refresh():void
        {
            this.loadList();
        }

        // Reset current view list items to display all data
        private resetViewList():void
        {
            let result = [];

            this.dataList.forEach((item:Object, index:number) =>
            {
                result.push(index);
            });

            this.viewList = result;
        }

        // Select specified list item
        private selectListItem(id:number):void
        {
            let index = this.selectedItems.indexOf(id);

            // Already selected, unselect
            if (index > -1)
            {
                // Remove from list
                this.selectedItems.splice(index, 1);

                // Unselect old row
                if (typeof this.htmlListItems[id] !== 'undefined')
                    this.htmlListItems[id].removeClass('is-selected');
            }
            // Nott yet selected, selected
            else
            {
                // Add to list
                this.selectedItems.push(id);

                // Select new row
                if (typeof this.htmlListItems[id] !== 'undefined')
                    this.htmlListItems[id].addClass('is-selected');

                // We have limited amount of items we can select
                if (this.selectedLimit > 0)
                {
                    // Unselect the first item in list
                    if (this.selectedItems.length > this.selectedLimit)
                        this.selectListItem(this.selectedItems[0]);
                }
            }
        }

        // Set ordered column
        private setColumnOrder(index:number):void
        {
            // Clear styles from old column
            if (this.settingsData.order !== index)
            {
                // Reset columns' styles
                let i = this.settingsData.order;
                this.htmlListColumns[i].attr('class', 'column');
            }

            // Update new column's style
            let sort = this.settingsData.sort;
            this.htmlListColumns[index].attr('class', 'column sort-' + sort);

            // Update settings value
            this.settingsData.order = index;
        }

        // Set column sorting order
        private setColumnSort(index:number):void
        {
            // Default sort order
            let result = 'asc';

            // Still the same column, change order
            if (this.settingsData.order === index)
                result = (this.settingsData.sort === 'asc' ? 'desc' : 'asc');

            // Update settings value
            this.settingsData.sort = result;
        }

        // Set selected items
        setSelectedItems(list:Array<number>):void
        {

        }

        // Show or hide search field
        private showSearch(show:boolean, autofocus:boolean):void
        {
            // Show search
            if (show === true)
            {
                // Build search
                if (!(this.fldSearch instanceof Object))
                    this.buildSearchField();

                // Update styles
                this.htmlRoot.
                    removeClass('search-hidden').
                    addClass('search-visible');

                // Set focus to field
                if (autofocus === true)
                    this.fldSearch.focus();
            }
            // Hide search
            else
            {
                // Reset current field value
                this.fldSearch.val('');

                // Filter data items
                this.filterDataItems('');

                // Update styles
                this.htmlRoot.
                    removeClass('search-visible').
                    addClass('search-hidden');
            }

            // Upddate state
            this.isSearchVisible = show;
        }

        // Store current settings
        private storeSettings():void
        {
            // Update settings to session
            Tinycar.Session.set(
                this.settingsId, this.settingsData
            );
        }

        // Update list order to match current setings
        private updateListOrder():void
        {
            // Target column properties
            let column = this.Model.getIndex(
                'columns', this.settingsData.order
            );

            // Target column properties
            var name = column['name'];
            var type = column['type'];

            // Sort order
            var sortA = this.settingsData.sort === 'asc' ? -1 : +1;
            var sortB = this.settingsData.sort === 'asc' ? +1 : -1;

            // Sort datalist list by comparing values
            this.dataList.sort((a:Object, b:Object):number =>
            {
                let valA = a[name]['value'];
                let valB = b[name]['value'];

                if (valA < valB)
                    return sortA;

                if (valA > valB)
                    return sortB;

                return 0;
            });
        }
    }
}
