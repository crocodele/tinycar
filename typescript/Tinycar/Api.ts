module Tinycar
{
	export module Api
	{
		export var apiUrl:string;
		export var requestId:number = 0;
	
		// Call specified user action
		export function call(request:Object):void
		{
			// Request parameters
			let params = {
				api_id      : ++this.requestId,
				api_service : request['service']
			};
			
			// Extend parameters
			if (request.hasOwnProperty('params'))
				jQuery.extend(params, request['params']);
			
			// Create JSON request
			$.ajax({
				type        : 'POST',
			    url         : this.apiUrl,
				dataType    : 'json',
				data        : JSON.stringify(params),
				contentType : 'application/json; charset=utf-8',
				success     : (response:Object) =>
				{
				    // Reset action node
				    Tinycar.Page.setActionNode(null);
				    
					// We have a successfull response
					if (response.hasOwnProperty('result'))
						request['success'](response['result']);
				},
				error       : (error:Object) =>
				{
                    // Reset action node
                    Tinycar.Page.setActionNode(null);
                    
				    // Show error from repsonse
				    this.showApiError(error);
				}
			});
		}
		
        // Get API URL to specified upload preview image
        export function getPreviewLink(name:string):string
        {
            return this.apiUrl + '?' + jQuery.param({
                api_service : 'application.servicelink',
                service     : 'upload.image',
                url         : Tinycar.Url.getParams(),
                data        : {name:name}
            });
        }
		
		// Get API URL to specified service
		export function getServiceLink(service:string):string
		{
            return this.apiUrl + '?' + jQuery.param({
                api_service : 'application.servicelink',
                service     : service,
                url         : Tinycar.Url.getParams()
            });
		}
	
		// Set API URL
		export function setApiUrl(url:string):void
		{
			this.apiUrl = url;
		}
		
		// Show error from JSON response
		export function showApiError(data:Object):boolean
		{
		    // No JSON response
		    if (!data.hasOwnProperty('responseJSON'))
		        return false;

            // No error data
            if (!data['responseJSON'].hasOwnProperty('error'))
                return false;
            
            // Error properties
            let error = data['responseJSON']['error'];

            // Set toast message
            Tinycar.System.Toast.setMessage({
                type : 'failure',
                vars : error['message'],
                text : Tinycar.Locale.getText('toast_' + error['code'])
            });
            
            // Show toast 
            Tinycar.System.Toast.show();
            
            return true;
		}
	}
}