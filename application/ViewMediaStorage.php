<!DOCTYPE html>
<html lang='en'>
	<head>
		<title>Media Storage</title>
		<meta charset='utf-8'>

        <style type="text/css">
            #mediaStorageTable{
                border: 1px solid #000000;
                border-collapse: collapse
            }

            #mediaStorageTable td, th {
                border: 1px solid #000000;
                border-collapse: collapse;
            }

            #mediaStorageTable th{
                padding: 5px;
            }
            #mediaStorageTable td{
                padding: 5px;
            }
        </style>

        <script type="text/javascript" src="javascript/utils.js"></script>

        <script type="text/javascript">
            addEventListener("load", async (event) => {
                let mediaForm = document.querySelector("form[id='media_storage_form']");
                mediaForm["mediaStorageFormSubmitButton"].onclick = async () => {

                    async function sendMediaForm() {
                        const formData = new FormData(mediaForm);
                        let url = window.location.pathname + "?q=mediaStorageOperation&operation=upload";
                        let result = {};
                        try {
                            const response = await fetch(url, {
                                method: 'POST',
                                body: formData
                            });

                            result = await response.json();
                        } catch (error) {
                            result = error;
                        }

                        return result;
                    }
                    let response = await sendMediaForm();
                    console.log(`--> response from media form submit:`);
                    console.log(response);
                };

                fetch(window.location.pathname+"?q=mediaStorageOperation&operation=getAllUserMedia")
                    .then((response) => {return response.json()})
                    .then(responseJSON => {
                        console.log(responseJSON);
                        const tbody = document.querySelector('#mediaStorageTable tbody');
                        for (let rowJSON of responseJSON){

                            let newRow = ``;
                            newRow += `<tr>\n`;
                            newRow += `<td>${rowJSON.internal_id}</td>`;
                            newRow += `<td>${rowJSON.media_name}</td>`;
                            newRow += `<td>${rowJSON.media_format}</td>`;
                            newRow += `<td>${rowJSON.media_language}</td>`;
                            newRow += `<td>${rowJSON.media_version}</td>`;
                            newRow += `<td>${rowJSON.media_compile_parameters}</td>`;
                            newRow += `</tr>\n`;

                            tbody.insertAdjacentHTML('beforeend', newRow);
                        }
                    })

            });
        </script>

	</head>
	<body>
        <h1>User media storage</h1>
        <a href="?q=home">Back to home</a>
        <p>
            Media storage - files, libraries which could be used with user projects. This is mainly for additional C++ libraries which are linked to project.
        </p>
        <form id="media_storage_form" method="post">
            <table>
                <tr>
                    <td>Name:</td>
                    <td><input name="media_name" type="text" /></td>
                </tr>
                <tr>
                    <td>Version:</td>
                    <td><input name="media_version" type="text" /></td>
                </tr>
                <tr>
                    <td>Compile parameters:</td>
                    <td><input name="media_compile_parameters" type="text" /></td>
                </tr>
                <tr>
                    <td>Language:</td>
                    <td>
                        <select name="media_language">
                            <option value=""></option>
                            <option value="C++">C++</option>
                            <option value="python">python</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>Content:</td>
                    <td><input name="media_content" type="file" /></td>
                </tr>
                <tr>
                    <td></td>
                    <td><input id="mediaStorageFormSubmitButton" type="button" value="SUBMIT"/></td>
                </tr>
            </table>
        </form>

        <h2>Current user media storage content</h2>

        <table id="mediaStorageTable">
            <thead>
                <th>id</th>
                <th>name</th>
                <th>format</th>
                <th>language</th>
                <th>version</th>
                <th>compile parameters</th>
            </thead>
            <tbody>
<!--                <tr>-->
<!--                    <td></td>-->
<!--                    <td></td>-->
<!--                    <td></td>-->
<!--                    <td></td>-->
<!--                    <td></td>-->
<!--                    <td></td>-->
<!--                </tr>-->
            </tbody>
        </table>

        <div id="mediaInfoBlock">
        </div>

	</body>
</html>
