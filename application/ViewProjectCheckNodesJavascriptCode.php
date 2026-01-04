<html>
<head>
    <style type="text/css">
        table{
            border-collapse: collapse;
            border: 1px solid #000000;
        }
        table th, td{
            border: 1px solid #000000;
            padding: 5px;
        }
    </style>

    <script type="text/javascript" src="javascript/utils.js"></script>

    <script src="<?php echo $htmlIncludeDirPrefix; ?>/lib/jquery.js"></script>
    <script src="<?php echo $htmlIncludeDirPrefix; ?>/lib/jquery-ui.js"></script>
    <script src="<?php echo $htmlIncludeDirPrefix; ?>/lib/jquery.browser.js"></script>
    <script src="<?php echo $htmlIncludeDirPrefix; ?>/lib/jquery.layout.js"></script>
    <script src="<?php echo $htmlIncludeDirPrefix; ?>/lib/jquery.ui.touch-punch.js"></script>

    <script src="<?php echo $htmlIncludeDirPrefix; ?>/../draw2d_hardCopy/draw2d.js"></script>

    <script type="text/javascript">
        GraphLang = {}; //this is used for utils and so, must be here by default

        <?php foreach ($nodeDefaultTreeDefinition as $newObjectName){echo("\t\t$newObjectName = {};\n");} ?>
    </script>


    <script src="<?php echo $htmlIncludeDirPrefix; ?>/gui/HoverConnection.js"></script>
    <script src="<?php echo $htmlIncludeDirPrefix; ?>/gui/MultilineInplaceEditor.js"></script>
    <script src="<?php echo $htmlIncludeDirPrefix; ?>/gui/SelectOptionInplaceEditor.js"></script>

    <script src="<?php echo $htmlIncludeDirPrefix; ?>/GraphLangUtils/RightRelPortLocator.js"></script>
    <script src="<?php echo $htmlIncludeDirPrefix; ?>/GraphLangUtils/BottomRelPortLocator.js"></script>
    <script src="<?php echo $htmlIncludeDirPrefix; ?>/GraphLangUtils/LeftRelPortLocator.js"></script>
    <script src="<?php echo $htmlIncludeDirPrefix; ?>/GraphLangUtils/TopRelPortLocator.js"></script>
    <script src="<?php echo $htmlIncludeDirPrefix; ?>/GraphLangUtils/ArrayClusterInPlaceEditor.js"></script>

    <script type="text/javascript" src="?q=getJavascriptCheckProjectSchematicNodes&projectId=<?php echo($currentProject);?>"></script>

    <script type="text/javascript">
        window.addEventListener("load", () => {
            let projectStatusElement = document.querySelector("div[id='projectStatus']");
            if (global_schematicNodesWithErrorList && Array.isArray(global_schematicNodesWithErrorList) && global_schematicNodesWithErrorList.length > 0){
                projectStatusElement.innerHTML = "ERROR: There are errors in schematic nodes in current project!";

                let tableElement = document.querySelector("table[id='tableNodesWithError']");
                for (let errorNodeInfo of global_schematicNodesWithErrorList){
                    let newTableRow = tableElement.insertRow();

                    let newRowCell = newTableRow.insertCell();
                    newRowCell.innerHTML = errorNodeInfo.id;
                    newRowCell = newTableRow.insertCell();
                    newRowCell.innerHTML = errorNodeInfo.displayName;
                    newRowCell = newTableRow.insertCell();
                    newRowCell.innerHTML = errorNodeInfo.className;
                    newRowCell = newTableRow.insertCell();
                    newRowCell.innerHTML = errorNodeInfo.error;
                }
            }else{
                projectStatusElement.innerHTML = "There is no error in schematic nodes in current project. Everything is OK.";
            }
        });
    </script>

</head>
<body>
    <h1>All project nodes javascript code check</h1>
    <a href='?q=userProjectList'>Back to project list</a>
    <br /><br />
    <div id="projectStatus"></div>
    <br />
    <table id="tableNodesWithError">
        <caption>
            <b>Schematic nodes with error</b>
        </caption>
        <thead>
            <tr>
                <th>id</th>
                <th>display name</th>
                <th>class name</th>
                <th>error</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
    <br /><br />
    <a href='?q=userProjectList'>Back to project list</a>
</body>
</html>
