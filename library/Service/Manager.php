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
            $result = null;
		    $error  = null;

            // Try to call service
            try
            {
                // Get service result
                $result = $this->callService(
                    $this->getParameter('api_service'),
                    $this->getParameters(),
                    true
                );
            }
            // Internal exception occured
            catch (Exception $e)
            {
                $error = array(
                    $e->getMessage(),
                    $e->getCustomCode(),
                    $e->getCustomData(),
                );
            }
            // Native exception occured
            catch (\Exception $e)
            {
                $error = array(
                    'native_error',
                    'native_error',
                    array('message' => $e->getMessage()),
                );
            }

            // Get last writer instance
            $writer = $this->getLastWriter();

            // Revert to JSON-RPC writer
            if (is_null($writer) || is_array($error))
            {
                // Get writer instance
                $writer = $this->getWriter('JsonRpc');

                // Set properties
                $writer->setRequestId($this->getParameter('api_id'));

                // Set result
                $writer->setResult($result);

                // We have an array
                if (is_array($error))
                    $writer->setError($error[0], $error[1], $error[2]);
            }

            // Output writer contents
            $writer->output();
        }
    }
