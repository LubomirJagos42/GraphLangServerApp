<!DOCTYPE html>
<html lang='en'>
	<head>
		<title><?= isset($projectUpdate) ? "Update project" : "Create project" ?></title>
		<meta charset='utf-8'>

        <script type="text/javascript" src="javascript/utils.js"></script>

        <script type="text/javascript">
            addEventListener("load", (event) => {

                serverAjaxPostSendReceive(["q", "getPlatformioInfo", "type", "boardList"], [], (infoList)=>{
                    let embeddedBoardSelectInput = document.querySelector("select[id='formEmbeddedBoard']");
                    for (let infoItem of infoList){
                        let selectOptionElement = document.createElement("option");
                        selectOptionElement.value = infoItem.id;
                        selectOptionElement.text = infoItem.name;
                        embeddedBoardSelectInput.appendChild(selectOptionElement);

                    }
                    <?php if ($projectEmbeddedBoard) echo("document.querySelector(\"select[id='formEmbeddedBoard']\").value = '$projectEmbeddedBoard';\n"); ?>
                });

                serverAjaxPostSendReceive(["q", "getPlatformioInfo", "type", "platformList"], [], (infoList)=>{
                    let embeddedBoardSelectInput = document.querySelector("select[id='formEmbeddedPlatform']");
                    for (let infoItem of infoList){
                        let selectOptionElement = document.createElement("option");
                        selectOptionElement.value = infoItem.name;
                        selectOptionElement.text = infoItem.title;
                        embeddedBoardSelectInput.appendChild(selectOptionElement);
                    }
                    <?php if ($projectEmbeddedPlatform) echo("document.querySelector(\"select[id='formEmbeddedPlatform']\").value = '$projectEmbeddedPlatform';\n"); ?>
                });

                serverAjaxPostSendReceive(["q", "getPlatformioInfo", "type", "frameworkList"], [], (infoList)=>{
                    let embeddedBoardSelectInput = document.querySelector("select[id='formEmbeddedFramework']");
                    for (let infoItem of infoList){
                        let selectOptionElement = document.createElement("option");
                        selectOptionElement.value = infoItem.name;
                        selectOptionElement.text = infoItem.title;
                        embeddedBoardSelectInput.appendChild(selectOptionElement);
                    }
                    <?php if ($projectEmbeddedFramework) echo("document.querySelector(\"select[id='formEmbeddedFramework']\").value = '$projectEmbeddedFramework';\n"); ?>
                });
            });
        </script>

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
                    <td>Embedded board:</td>
                    <td>
                        <select id="formEmbeddedBoard" name="embeddedBoard">
                            <option value=""></option>
                            <!--<option value="nucleo_g071rb">Nucleo G071RB</option>-->
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>Embedded platform:</td>
                    <td>
                        <select id="formEmbeddedPlatform" name="embeddedPlatform">
                            <option value=""></option>
                            <option value="native">native</option>
                            <!--<option value="ststm32">ststm32</option>-->
                            <!--<option value="esp32">esp32</option>-->
                            <!--<option value="espressif32">espressif32</option>-->
                            <!--<option value="avr">avr</option>-->
                            <!--<option value="esp8266">esp8266</option>-->
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>Embedded framework:</td>
                    <td>
                        <select id="formEmbeddedFramework" name="embeddedFramework">
                            <option value=""></option>
                            <!--<option value="arduino">arduino</option>-->
                            <!--<option value="stm32cube">stm32cube</option>-->
                        </select>
                    </td>
                </tr>
            </table>
            <br />

            <input type="submit" value="submit"/>
        </form>
        <br />
        <h3>Help - PlatformIO setup</h3>
        <p>
            <span>
                <b>board</b> - Specific board model<br />
                <ul>
                    <li>Examples: uno, esp32dev, nucleo_f401re</li>
                    <li>Defines: CPU speed, RAM/flash size, pinout, default settings</li>
                </ul>
            </span>

            <span>
                <b>platform</b> - The hardware architecture/chip family<br />
                <ul>
                    <li>Examples: atmelavr (Arduino AVR), espressif32 (ESP32), ststm32 (STM32)</li>
                    <li>Defines toolchain, compiler, upload tools</li>
                </ul>
            </span>

            <span>
                <b>framework</b> - Software framework/SDK<br />
                <ul>
                    <li>Examples: arduino, espidf, stm32cube</li>
                    <li>Provides libraries, APIs, startup code</li>
                </ul>
            </span>

            <span>
                How they work together:
<pre>
    [env:myboard]
    platform = espressif32    # ESP32 chip family
    board = esp32dev          # Specific ESP32 dev board
    framework = arduino       # Use Arduino API (not ESP-IDF)
</pre>
            </span>

            <span>
                Simple parameter explanation:<br />
                <ul>
                    <li>platform = chip architecture</li>
                    <li>board = physical board specs</li>
                    <li>framework = software libraries/API</li>
                </ul>
            </span>
        </p>

        <br />
        <?= isset($projectUpdate) ? '<a href="?q=userProjectList">Back to project list</a>' : '<a href="?q=home">Home</a>' ?>
	</body>
</html>
