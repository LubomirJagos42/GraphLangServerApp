<!DOCTYPE html>
<html lang='en'>
	<head>
		<title>User registration</title>
		<meta charset='utf-8'>
	</head>
	<body>

        <form name="registrationForm" method="post" action="?q=registerUserViaEmail">
            <table>
                <tr>
                    <td>username:</td>
                    <td><input type="text" name="username" value="" /></td>
                </tr>
                <tr>
                    <td>email:</td>
                    <td><input type="text" name="email" value="" /></td>
                </tr>
                <tr>
                    <td>password:</td>
                    <td><input type="password" name="password" value="" /></td>
                </tr>
                <tr>
                    <td>confirm password:</td>
                    <td><input type="password" name="passwordConfirmation" value=""/></td>
                </tr>
            </table>
            <br/>
            <input type="submit" value="register"/>
        </form>

	</body>
</html>
