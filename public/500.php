<!doctype HTML>
<html>
	<head>
		<title>Error 500: Something went wrong</title>
	</head>
<body>
	<h1>Something went wrong.</h1>
	<p>The application encountered an error while trying to complete your request for <?php
	if(!empty($className))
	{
		echo 'resource <code>'.$className.'</code>';
	}
	else
	{
		echo  'URL <code>'.$url.'</code>';
	} ?>.</p>
</body>
</html>
