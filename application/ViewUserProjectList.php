<!DOCTYPE html>
<html lang='en'>
	<head>
		<title>Project list</title>
		<meta charset='utf-8'>
	</head>
	<body>
        <h1>User project list</h1>
		<table border="1px">
            <tr>
                <td>name</td>
                <td>description</td>
                <td>ideVersion</td>
                <td>image</td>
                <td>visibility</td>
                <td>codeTemplate</td>
                <td></td>
                <td></td>
            </tr>

        <?php
        foreach ($projectList as $projectItem){
        ?>
            <tr>
                <td><?php echo($projectItem["name"]); ?></td>
                <td><?php echo($projectItem["description"]); ?></td>
                <td><?php echo($projectItem["ideVersion"]); ?></td>
                <td><img src="<?php echo($projectItem["image"]); ?>" /></td>
                <td><?php echo($projectItem["visibility"]); ?></td>
                <td><?php echo($projectItem["codeTemplate"]); ?></td>
                <td><a href="?q=ide&projectId=<?php echo($projectItem["id"]); ?>">open</a></td>
                <td><a href="?q=deleteProject&projectId=<?php echo($projectItem["id"]); ?>">delete</a></td>
            </tr>
        <?php
        }
        ?>
		</table>

        <h2>Others public projects</h2>
        <table border="1px">
            <tr>
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
                    <td><?php echo($projectItem["ownerName"]); ?></td>
                    <td><?php echo($projectItem["ownerMail"]); ?></td>
                    <td><?php echo($projectItem["name"]); ?></td>
                    <td><?php echo($projectItem["description"]); ?></td>
                    <td><?php echo($projectItem["ideVersion"]); ?></td>
                    <td><img src="<?php echo($projectItem["image"]); ?>" /></td>
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
