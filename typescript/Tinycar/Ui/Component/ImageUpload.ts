module Tinycar.Ui.Component
{
    export class ImageUpload extends Tinycar.Main.Field
    {
        private htmlButton:JQuery;
        private htmlPreview:JQuery;
        private hasBuiltUpload:boolean = false;

        
        // Build upload button container
        private buildButton():void
        {
            this.htmlButton = $('<div>').
                attr('class', 'button').
                appendTo(this.htmlContent);
        }
    
        // @see Tinycar.Main.Field.buildContent()
        buildContent()
        {
            // Build elements
            super.buildContent();
            this.buildPreview();
            this.buildButton();
            
            // Build initial images
            this.buildImages();
        }
        
        // Build single preview image
        private buildImage(id:string, index:number):JQuery
        {
            // Create container
            let container = $('<div>').
                attr('class', 'image');
            
            // Image URL
            let url = Tinycar.Api.getPreviewLink(id);
            
            // Add image
            $('<span>').
                attr('class', 'image').
                css('background-image', 'url(' + url + ')').
                appendTo(container);
            
            // Add remove button
            let button = new Tinycar.Ui.Button({
                style : 'background-icon',
                icon  : 'close',
                size  : 'tiny'
            });
            
            // When clicked
            button.setHandler('click', () =>
            {
                this.removeImage(index);                
            });
            
            // Add to container
            container.append(button.build());
            
            return container;
        }
        
        // Build preview images into previews container
        private buildImages():void
        {
            // Clear existing images
            this.htmlPreview.empty();
            
            // Build upload when needed
            if (this.getDataValue().length === 0)
            {
                // Build upload once when needed
                if (!this.hasBuiltUpload)
                    this.buildUpload();
                
                // Update root styles                
                this.htmlRoot.removeClass('has-images');
            }
            else
                this.htmlRoot.addClass('has-images');
            
            // Build images
            this.getDataValue().forEach((id:string, index:number) =>
            {
                this.htmlPreview.append(this.buildImage(id, index));
            });
        }
        
        // Build previews container
        private buildPreview():void
        {
            this.htmlPreview = $('<div>').
                attr('class', 'previews').
                appendTo(this.htmlContent);
        }
    
        // Build upload form
        private buildUpload():void
        {
            // Update state
            this.hasBuiltUpload = true;
            
            // Load vendor scripts
            Tinycar.Page.loadVendor('jquery.fileupload', () =>
            {
                // Add icon
                $('<span>').
                    attr('class', 'icon icon-small icon-dark icon-add').
                    appendTo(this.htmlButton);
                    
                // Create file input
                let input = $('<input>').
                    attr('type', 'file').
                    attr('name', 'files[]').
                    attr('multiple', 'multiple').
                    appendTo(this.htmlButton);
                
                // Configure upload 
                input.fileupload({
                    
                    url                    : Tinycar.Api.getServiceLink('upload.images'),
                    dataType               : 'json',
                    type                   : 'POST',
                    autoUpload             : true,
                    dropZone               : null,
                    limitMultiFileUploads  : this.Model.getNumber('data_limit'),
                    
                    // When file upload begins
                    send:():boolean =>
                    {
                        return true;
                    },
                    
                    // When file upload fails
                    fail:(e:Event, data:Object) =>
                    {
                        if (data['jqXHR'].hasOwnProperty('responseJSON'))
                        {
                            if (typeof data['jqXHR']['responseJSON'] === 'object')
                            {
                                if (typeof data['jqXHR']['responseJSON']['error'] === 'object')
                                {
                                    Tinycar.System.Toast.showFromError(
                                        data['jqXHR']['responseJSON']['error']
                                    );
                                }
                            }
                        }
                    },
                    
                    // When file upload is done
                    done:(e:Event, data:Object) =>
                    {
                        if (data['jqXHR'].hasOwnProperty('responseJSON'))
                        {
                            if (typeof data['jqXHR']['responseJSON'] === 'object')
                            {
                                if (data['jqXHR']['responseJSON'].hasOwnProperty('result'))
                                {
                                    // Update current list
                                    this.setDataValue(this.getDataValue().concat(
                                        data['jqXHR']['responseJSON']['result']
                                    ));
                                    
                                    // Build preview images
                                    this.buildImages();
                                }
                            }
                        }
                    }
                });
            });
        }
        
        // @see Tinycar.Main.Field.getDataValue()
        getDataValue():Array<string>
        {
            return this.Model.getList('data_value');
        }
        
        // @see Tinycar.Main.Field.getRootStyles()
        getRootStyles():Array<string>
        {
            let result = super.getRootStyles();
            
            // Single file upload
            if (this.isSingleFileUpload())
                result.push('is-single-upload');
            
            return result;
        }
        
        // Check if only a single file is allowed
        private isSingleFileUpload():boolean
        {
            return (this.Model.getNumber('data_limit') < 2);
        }
        
        // Remove specified image
        private removeImage(index:number):void
        {
            // Update value
            let list = this.getDataValue();
            list.splice(index, 1);
            this.setDataValue(list);
            
            // Re-build images
            this.buildImages();
        }
        
        // @see Tinycar.Main.Field.setDataValue()
        setDataValue(value:Array<string>):void
        {
            super.setDataValue(value);
        }
    }
}