<!DOCTYPE html>
<html lang='en'>
	<head>
		<title>Project Deployments</title>
		<meta charset='utf-8'>

        <script type="text/javascript" src="javascript/utils.js"></script>

        <script type="text/javascript">
            addEventListener("load", (event) => {
                //...do nothing now...

                let projectId = new URL(window.location.href).searchParams.get("projectId");
                document.querySelector("div[id='deploymentInfoBlock']").innerHTML = `<h1>auto generated content by JS</h1>\ncurrent project id: ${projectId}`;
            });
        </script>

	</head>
	<body>
        <h1>Project deployments list</h1>
        <a href="?q=userProjectList&debugMode=1">Back to project list</a>
        <p>
            Specify different project deployments, like how project should be compiled, for this these informations are needed:
            <ul>
                <li>program entry point - main schematic node</li>
                <li>target running environment - output can be .exe application or .dll library</li>
                <li>output mode - could be release or debug</li>
            </ul>
        </p>

        <div id="deploymentInfoBlock">
        </div>

	</body>
</html>
