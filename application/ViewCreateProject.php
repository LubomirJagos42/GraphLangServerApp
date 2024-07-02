<!DOCTYPE html>
<html lang='en'>
	<head>
		<title><?= isset($projectUpdate) ? "Update project" : "Create project" ?></title>
		<meta charset='utf-8'>
	</head>
	<body>
        <h1><?= isset($projectUpdate) ? "Update project" : "Create project" ?></h1>

        <form id="createProjectForm" name="createProjectForm" method="post" enctype="multipart/form-data" action="?q=<?= isset($projectUpdate) ? "updateProjectDetails&projectId=$currentProject&doUpdate=1" : "createProject" ?>">
            <table>
                <tr>
                    <td>Project name:</td>
                    <td><input name="name" type="text" value="<?= $projectName ?>" /></td>
                </tr>
                <tr>
                    <td>IDE version:</td>
                    <td>
                        <select name="ideVersion">
                            <option value="0v1" <?= $projectIdeVersion == "0v1" ? "selected" : "" ?>>0v1</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>Description:</td>
                    <td><input name="description" type="text" value="<?= $projectDescription ?>" /></td>
                </tr>
                <tr>
                    <td>Visibility:</td>
                    <td>
                        <select name="visibility">
                            <option value="public" <?= $projectVisibility == "public" ? "selected" : "" ?>>public</option>
                            <option value="private" <?= $projectVisibility == "private" ? "selected" : "" ?>>private</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>Code template:</td>
                    <td>
                        <select name="codeTemplate">
                            <option value="arduino" <?= $projectCodeTemplate == "arduino" ? "selected" : "" ?>>arduino</option>
                            <option value="desktop" <?= $projectCodeTemplate == "desktop" ? "selected" : "" ?>>desktop</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>Programming language:</td>
                    <td>
                        <select name="language">
                            <option value="C/C++" <?= $projectLanguage == "C/C++" ? "selected" : "" ?>>C/C++</option>
                            <option value="python" <?= $projectLanguage == "python" ? "selected" : "" ?>>python</option>
                            <option value="javascript" <?= $projectLanguage == "javascript" ? "selected" : "" ?>>javascript</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>Image:</td>
                    <td>
                        <input name="image" type="file"/><br />
                        no image: <input name="noImage" type="checkbox" />
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                </tr>
            </table>
            <br />
            <input type="submit" value="submit"/>
        </form>

        <br />
        <?= isset($projectUpdate) ? '<a href="?q=userProjectList">Back to project list</a>' : '<a href="?q=home">Home</a>' ?>
	</body>
</html>
