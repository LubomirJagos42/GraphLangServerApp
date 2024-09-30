<html>
    <head>
        <style type="text/css">
        </style>

        <script type="text/javascript" src="javascript/utils.js"></script>

        <script type="text/javascript">
            addEventListener("load", (event) => {
                document.querySelector("#uploadCodeButton").addEventListener("click", function(){
                    let projectId = document.querySelector("input[name='projectId").value;
                    let nodeId = document.querySelector("input[name='nodeId']").value;
                    let nodeClassName = document.querySelector("input[name='nodeClassName']").value;
                    let nodeCodeContent = document.getElementById("codeAreaEditor").value;

                    serverAjaxPostSendReceive(
                        ["q", "nodeUpload"],
                        ["nodeId", nodeId, "projectId", projectId, "nodeClassName", nodeClassName ,"nodeCodeContent", toHex(nodeCodeContent)],
                        function(){
                            // console.log(GLOBAL_AJAX_RESPONSE);
                            let outputElement = document.getElementById("operationOutput");
                            outputElement.innerHTML = "";
                            outputElement.insertAdjacentHTML("beforeend", `<pre>status: ${GLOBAL_AJAX_RESPONSE.status}</pre>`);
                            outputElement.insertAdjacentHTML("beforeend", `<pre>message: ${GLOBAL_AJAX_RESPONSE.message}</pre>`);
                            outputElement.insertAdjacentHTML("beforeend", `<pre>warning: ${GLOBAL_AJAX_RESPONSE.warningMsg}</pre>`);
                            outputElement.insertAdjacentHTML("beforeend", `<pre>error: ${GLOBAL_AJAX_RESPONSE.errorMsg}</pre>`);
                        }
                    );
                });
            });
        </script>
    </head>
    <body>
        <h1>Node code editor</h1>

        <a href='?q=projectCategoriesNodesEditor&projectId=<?= $projectId ?>'>Back to category editor</a>
        <br /><br />

        project id: <input name="projectId" type="input" size="8" value="<?= $projectId ?>"/>
        node id: <input name="nodeId" type="input" size="8" value="<?= $nodeId ?>"/>
        node display name: <input name="nodeDisplayName" type="input" size="18" value="<?= $nodeDisplayName ?>" disabled/>
        node class name: <input name="nodeClassName" type="input" size="40" value="<?= $nodeClassName ?>"/>
        <br /><br />
        <input id="uploadCodeButton" name="uploadCodeButton" type="button" value="UPLOAD"/>
        <br /><br />

        <div id="operationOutput"></div>

        <textarea id="codeAreaEditor" name="codeAreaEditor" rows="50" cols="160">
<?= $nodeInfo["node_content_code"] ?>
        </textarea>

        <h2>Debuging output from PHP - nodeInfo</h2>
        <pre><?= var_dump($nodeInfo) ?></pre>

    </body>
</html>

