<?php

    // Get runtime
    require_once(dirname(__DIR__).'/run.php');
    
    // Create new manager instance
    $app = new Tinycar\Web\Manager();
    
    // Add request paramters
    $app->addParameters($_GET);
    $app->addParameters($_POST);
    
?>
<!DOCTYPE html>
<html>
	<head>
		<title><?php echo $app->getSystemTitle(); ?></title>
		
		<meta http-equiv="x-ua-compatible" content="IE=edge,chrome=1" />
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<meta http-equiv="content-language" content="fi" />
		<meta http-equiv="imagetoolbar" content="false" />
		
		<meta name="mssmarttagspreventparsing" content="true" />
		<meta name="robots" content="noindex, nofollow, noarchive" />
		<meta name="viewport" content="width=device-width" />
		
</head>
<body>

	<link 
    	rel="stylesheet" 
    	type="text/css"
		href="assets/base/styles/common.min.css" />
		
    <script 
    	type="text/javascript"
    	src="assets/vendor/requirejs/require.js">
	</script>
	
	<?php if ($app->install()) { ?>
	
    	<script type="text/javascript">

	    	requirejs.config(<?php echo json_encode($app->getRequireConfig()); ?>);

			require(['jquery', 'app'], function()
			{
   				Tinycar.System.load(<?php echo json_encode($app->getSystemConfig()); ?>);
   				$('body:first').append((Tinycar.System.build()));
			});
    
	    </script>
	    
	<?php } else { ?>
		
		<div id="install-failure">
			<?=$app->getInstallError()?>
		</div>
		
	<?php } ?>

</body>
</html>