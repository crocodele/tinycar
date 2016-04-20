module Tinycar.Ui.Component
{
    export class Image extends Tinycar.Main.Component
    {
        private hasImages:boolean = false;
        
        
        // @see Tinycar.Main.Field.buildContent()
        buildContent():void
        {
            // Build elements
            super.buildContent();
            
            // Build image with custom data
            if (this.Model.hasString('image_data'))
                this.buildImageFromData();
            
            // Build images from paths
            else
            {
                // Build image for screen from custom path
                if (this.Model.hasString('image_screen'))
                    this.buildImageForScreen();
                
                // Build image for mobile from custom path
                if (this.Model.hasString('image_mobile'))
                    this.buildImageForMobile();
            }
        }
        
        // Build image from raw image data
        private buildImageFromData():void
        {
            // Update state
            this.hasImages = true;
            
            // Create image node
            $('<img>').
                attr('src', this.Model.get('image_data')).
                attr('class', 'image image-screen').
                appendTo(this.htmlRoot);
        }
        
        // Build image for mobile from custom path
        private buildImageForMobile():void
        {
            // Update state
            this.hasImages = true;
            
            $('<img>').
                attr('src', this.Model.get('image_mobile')).
                attr('class', 'image image-mobile').
                appendTo(this.htmlRoot);
            
            // Add root style
            this.htmlRoot.addClass('with-mobile-image');
        }
        
        // Build image for screen from custom path
        private buildImageForScreen():void
        {
            // Update state
            this.hasImages = true;
            
            $('<img>').
                attr('src', this.Model.get('image_screen')).
                attr('class', 'image image-screen').
                appendTo(this.htmlRoot);
        }
        
        // @see Tinycar.Main.Component.setAsVisible()
        setAsVisible(visible:boolean):void
        {
            // Update visibility only when we have images
            if (this.hasImages === true)
                super.setAsVisible(visible);
        }
    }
}