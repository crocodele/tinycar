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
					// We have a successfull response
					if (response.hasOwnProperty('result'))
						request['success'](response['result']);
					
					// We have have an error
					else if (response.hasOwnProperty('error'))
					{
						Tinycar.System.Toast.setMessage({
							type : 'failure',
							vars : response['error']['message'],
							text : Tinycar.Locale.getText(
								'toast_' + response['error']['code']
							)
						});
						
						Tinycar.System.Toast.show();
					}
				},
				error       : (error:Object) =>
				{
					console.log(error);
				}
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
	}
}