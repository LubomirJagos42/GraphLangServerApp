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
                            <option value="desktop" <?= $projectCodeTemplate == "desktop" ? "selected" : "" ?>>desktop</option>
                            <option value="embedded" <?= $projectCodeTemplate == "embedded" ? "selected" : "" ?> >embedded</option>
                            <option value="webassembly" <?= $projectCodeTemplate == "webassembly" ? "selected" : "" ?> >webassembly</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>Programming language:</td>
                    <td>
                        <select name="language">
                            <option value="C/C++" <?= $projectLanguage == "C/C++" ? "selected" : "" ?>>C/C++</option>
                            <option value="python" <?= $projectLanguage == "python" ? "selected" : "" ?> disabled>python</option>
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

            <h2>PlatformIO setup (just for embedded target)</h2>
            <table>
                <tr>
                    <td>Embedded platform:</td>
                    <td>
                        <select name="embeddedPlatform">
                            <option value="ststm32">ststm32</option>
                            <option value="esp32">esp32</option>
                            <option value="arduino">arduino</option>
                            <option value="esp8266">esp8266</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>Embedded board:</td>
                    <td>
                        <select name="embeddedBoard">
                            <option value="nucleo_g071rb">Nucleo-64 -> nucleo_g071rb</option>
                        </select>
                    </td>
                </tr>
            </table>
            <br />

            <input type="submit" value="submit"/>
        </form>

        <br />
        <?= isset($projectUpdate) ? '<a href="?q=userProjectList">Back to project list</a>' : '<a href="?q=home">Home</a>' ?>
	</body>
</html>
