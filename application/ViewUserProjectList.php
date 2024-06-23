<!DOCTYPE html>
<html lang='en'>
	<head>
		<title>Project list</title>
		<meta charset='utf-8'>

        <style type="text/css">
            table, th, td {
                border: 1px solid black;
                border-collapse: collapse;
            }
            table td{
                padding: 8px;
            }
        </style>
	</head>
	<body>
        <h1>User project list</h1>
		<table>
            <tr>
                <td>ID</td>
                <td>name</td>
                <td>description</td>
                <td>ideVersion</td>
                <td>image</td>
                <td>visibility</td>
                <td>codeTemplate</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>

        <?php
        foreach ($projectList as $projectItem){
        ?>
            <tr>
                <td><?php echo($projectItem["id"]); ?></td>
                <td><?php echo($projectItem["name"]); ?></td>
                <td><?php echo($projectItem["description"]); ?></td>
                <td><?php echo($projectItem["ideVersion"]); ?></td>
                <td><img width="100px" src="<?php echo($projectItem["image"]); ?>" /></td>
                <td><?php echo($projectItem["visibility"]); ?></td>
                <td><?php echo($projectItem["codeTemplate"]); ?></td>
                <td><a href="?q=ide&projectId=<?php echo($projectItem["id"]); ?>">open</a></td>
                <td><a href="?q=deleteProject&projectId=<?php echo($projectItem["id"]); ?>">delete</a></td>
                <td><a href="?q=getOrderedNodes&projectId=<?php echo($projectItem["id"]); ?>">nodes list</a></td>
                <td><a href="?q=downloadIde&projectId=<?php echo($projectItem["id"]); ?>">download</a></td>
                <td><a href="?q=shapeDesigner&projectId=<?php echo($projectItem["id"]); ?>">shape designer</a></td>
                <td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
                <td><a href="?q=getJavascriptForNodes&projectId=<?php echo($projectItem["id"]); ?>">JS nodes</a></td>
                <td><a href="?q=doExperimentDebug&projectId=<?php echo($projectItem["id"]); ?>">categories and nodes list</a></td>
            </tr>
        <?php
        }
        ?>
		</table>

        <h2>Others public projects</h2>
        <table border="1px">
            <tr>
                <td>ID</td>
                <td>owner name</td>
                <td>owner mail</td>
                <td>project name</td>
                <td>description</td>
                <td>ideVersion</td>
                <td>image</td>
                <td>visibility</td>
                <td>codeTemplate</td>
                <td></td>
                <td></td>
            </tr>

            <?php
            foreach ($othersProjectList as $projectItem){
                ?>
                <tr>
                    <td><?php echo($projectItem["id"]); ?></td>
                    <td><?php echo($projectItem["ownerName"]); ?></td>
                    <td><?php echo($projectItem["ownerMail"]); ?></td>
                    <td><?php echo($projectItem["name"]); ?></td>
                    <td><?php echo($projectItem["description"]); ?></td>
                    <td><?php echo($projectItem["ideVersion"]); ?></td>
                    <td><img width="100px" src="<?php echo($projectItem["image"]); ?>" /></td>
                    <td><?php echo($projectItem["visibility"]); ?></td>
                    <td><?php echo($projectItem["codeTemplate"]); ?></td>
                    <td><a href="?q=ide&projectId=<?php echo($projectItem["id"]); ?>">open</a></td>
                    <td><a href="?q=copyProject&projectId=<?php echo($projectItem["id"]); ?>">copy</a></td>
                </tr>
                <?php
            }
            ?>
        </table>

        <br />
        <a href="?q=notFound">Home</a>
	</body>
</html>
