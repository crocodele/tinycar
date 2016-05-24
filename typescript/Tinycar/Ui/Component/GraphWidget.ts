module Tinycar.Ui.Component
{
    export class GraphWidget extends Tinycar.Main.Component
    {
        private htmlGraph:JQuery;
        private htmlGrid:JQuery;
        private htmlSvg:Element;


        // @see Tinycar.Main.Component.buildContent()
        buildContent():void
        {
            super.buildContent();

            // Build heading
            if (this.Model.hasString('heading'))
                this.buildHeading();

            // Build graph container
            this.buildGraph();
            this.buildGrid();

            // Load graph data
            this.loadData();
        }

        // Build heading
        private buildHeading():void
        {
            $('<div>').
                attr('class', 'heading').
                text(this.Model.get('heading')).
                appendTo(this.htmlRoot);
        }

        // Build graph container
        private buildGraph():void
        {
            // Create container
            this.htmlGraph = $('<div>').
                attr('class', 'graph').
                appendTo(this.htmlRoot);
        }

        // Build grid container
        private buildGrid():void
        {
            // Create container
            this.htmlGrid = $('<div>').
                attr('class', 'grid').
                appendTo(this.htmlGraph);
        }

        // Build grid lines
        private buildGridLines(source:Object):void
        {
            // Clear existing grid
            this.htmlGrid.empty();

            // Set maximum width for grid
            this.htmlGrid.css('max-width', source['max_width']);

            // Create items
            source['item_data'].forEach((item:Object, index:number) =>
            {
                // Create item container
                let container = $('<span>').
                    attr('class', 'item').
                    css('width', source['item_percent'] + '%').
                    appendTo(this.htmlGrid);

                // Add value
                $('<span>').
                    attr('class', 'value theme-text').
                    text(item['value']).
                    appendTo(container);

                // Add label
                $('<span>').
                    attr('class', 'label').
                    text(item['label']).
                    appendTo(container);
            });
        }

        // Build SVG container
        private buildSvg(width:number):void
        {
            // Create node for SVG content
            this.htmlSvg = this.createSvgElement('svg', {
                "viewBox" : '0 -5 ' + width + ' 110',
                "width"   : width
            });

            // Add SVG to container
            this.htmlGraph.get(0).appendChild(this.htmlSvg);
        }

        // Build SVG dots
        private buildSvgDots(source:Object):void
        {
            source['item_data'].forEach((item:Object) =>
            {
                this.htmlSvg.appendChild(this.createSvgElement('circle', {
                    'cx'           : item['x'],
                    'cy'           : item['y'],
                    'r'            : 2,
                    "stroke"       : this.App.getThemeColor('base'),
                    "stroke-width" : 1,
                    "fill"         : '#ffffff',
                }));
            });
        }

        // Build graph line
        private buildSvgLine(source:Object):void
        {
            // Clear existing line
            while (this.htmlSvg.firstChild)
                this.htmlSvg.removeChild(this.htmlSvg.firstChild);

            // Create SVG node for new line
            let node = this.createSvgElement('polyline', {
                "fill"         : 'none',
                "stroke"       : this.App.getThemeColor('base'),
                "stroke-width" : 1,
                "points"       : source['item_points'].join(' ')
            });

            // Add to SVG content
            this.htmlSvg.appendChild(node);
        }

        // Create SVG element
        private createSvgElement(name:string, attributes:Object):Element
        {
            let node = document.createElementNS(
                'http://www.w3.org/2000/svg', name
            );

            for (let name in attributes)
                node.setAttribute(name, attributes[name]);

            return node;
        }

        // Load data
        private loadData():void
        {
            // Load data from action
            this.action('data', {}, (data:Object) =>
            {
                // Remove existing SVG node
                if (this.htmlSvg instanceof Element)
                    this.htmlSvg.parentNode.removeChild(this.htmlSvg);

                // Build SVG container
                this.buildSvg(data['max_width']);

                // Create graph grid
                this.buildGridLines(data);

                // Create graph line
                this.buildSvgLine(data);
                this.buildSvgDots(data);
            });
        }
    }
}
