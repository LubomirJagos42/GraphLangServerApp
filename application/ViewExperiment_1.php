<!DOCTYPE html>
<html lang='en'>
	<head>
		<title>GraphLang Server App</title>
		<meta charset='utf-8'>

		<script type="text/javascript" src="application/experimentJavascriptFromServer.php"></script>
	</head>
	<body>
		<h1>Experimental View 1</h1>
		
		User is logged: <?php print_r($isUserLogged); ?><br/>
		User ID: <?php echo $userId; ?><br/>
		Project ID: <?php echo $projectId; ?><br/>
		<br />
		User nodes: total count <?php echo count($userNodesClassNamesArray); ?>

		<table border="1">
		<?php			
			foreach($userNodesClassNamesArray as $userNodeClassName){
				echo("<tr><td>$userNodeClassName</td></tr>\n");
			}
		?>
		</table>

	</body>
</html>
