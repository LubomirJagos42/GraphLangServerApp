<?php
	$outputStr = "";    
	$outputStr .= <<< 'EOD'
		window.addEventListener('load', (event) => {
			alert('PHP says Hello world.');

			function includeJsToHead(filename)
			{
				var head = document.getElementsByTagName('head')[0];

				var script = document.createElement('script');
				script.src = filename;
				script.type = 'text/javascript';

				head.appendChild(script)
			}

			includeJsToHead("/GraphLangServerApp/javascript/simpleAlert.js");
		});

	EOD;

	echo $outputStr;
?>

