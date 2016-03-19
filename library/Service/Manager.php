<?php

    namespace Tinycar\Service;
    
    use Tinycar\Core\Exception;
    
    class Manager extends \Tinycar\App\Manager
    {
    	
        
        /**
         * Show output
         */
		public function show()
        {
        	// Default response
            $response = array(
                'id' => $this->getParameter('api_id'),
            );
            
            // Try to call service
            try
            {
                // Get service result
				$response['result']  = $this->callService(
					$this->getParameter('api_service'),
					$this->getParameters(),
					true
               );
            }
            catch (Exception $e)
            {
            	$response['error'] = array(
            		'id'      => $e->getMessage(),
            		'code'    => $e->getCustomCode(),
            		'message' => $e->getCustomData(),
				);
            }
            catch (\Exception $e)
            {
                $response['error'] = array(
                    'id'      => 'native_error',
                    'code'    => 'native_error', 
                    'message' => array(
                    	'message' => $e->getMessage(),
                    ),
                );
            }
            
            // Convert response to JSON
            $response = (defined('JSON_PRETTY_PRINT') ?
            	json_encode($response, JSON_PRETTY_PRINT) : 
            	json_encode($response)
            );
            
            // Send custom headers
            header('Content-Type: application/json; charset=UTF8');
            header('Content-Length: '.mb_strlen($response));
            
            // Output
            echo $response;
        }
    }
