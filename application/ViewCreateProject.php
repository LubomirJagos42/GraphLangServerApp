<!DOCTYPE html>
<html lang='en'>
	<head>
		<title>Create project</title>
		<meta charset='utf-8'>
	</head>
	<body>
        <h1>Create project</h1>

        <form id="createProjectForm" name="createProjectForm" method="post" enctype="multipart/form-data">
            <table>
                <tr>
                    <td>Project name:</td>
                    <td><input name="name" type="text"/></td>
                </tr>
                <tr>
                    <td>IDE version:</td>
                    <td>
                        <select name="ideVersion">
                            <option value="0v1">0v1</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>Description:</td>
                    <td><input name="description" type="text"/></td>
                </tr>
                <tr>
                    <td>Visibility:</td>
                    <td>
                        <select name="visibility">
                            <option value="public">public</option>
                            <option value="private">private</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>Code template:</td>
                    <td>
                        <select name="codeTemplate">
                            <option value="arduino">arduino</option>
                            <option value="desktop">desktop</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>Programming language:</td>
                    <td>
                        <select name="language">
                            <option value="C/C++">C/C++</option>
                            <option value="python">python</option>
                            <option value="javascript">javascript</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>Image:</td>
                    <td><input name="image" type="file"/><br /></td>
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
        <a href="?q=notFound">Home</a>
	</body>
</html>
