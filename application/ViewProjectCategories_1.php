<html>
    <head>
        <script type="text/javascript" src="javascript/utils.js"></script>
        <script type="text/javascript">
            addEventListener("load", (event) => {

                /*
                 *  AJAX function
                 */
                function ajaxPostRequest(operation, categoryId, nodeId, projectId){
                    let xhr = new XMLHttpRequest();
                    let url = `?q=categoryOperation&projectId=${projectId}`;
                    xhr.open("POST", url, true);
                    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

                    xhr.onreadystatechange = function () {
                        if (this.readyState == 4 && this.status == 200) {
                            // console.log(this.responseText);
                            response = JSON.parse(this.responseText.replace('"','\"'));   //THIS IS REALLY NEEDED TO PARSE JSON CORRECTLY WITHOUT THIS IT'S NOT RUNNING AT ALL!!!
                            if (response.error) console.warn(response);
                            else console.log(response);
                        }
                    }
                    xhr.send(`operation=${operation}&categoryId=${categoryId}&nodeId=${nodeId}`);
                }

                /*
                 *  DELETE button handler
                 */
                document.querySelectorAll('input[type="button"]').forEach((element) => {
                    element.addEventListener("click", function(event){
                        // console.log(event);
                        // event.target.value = "changed name";

                        let node_id = element.closest('td').querySelector('input[name="node_id"]').value;
                        let category_id = element.closest('td').querySelector('input[name="category_id"]').value;
                        let project_id = document.querySelector('input[name="project_id"]').value;

                        // console.log(`going delete node id: ${node_id}, category id: ${category_id}, project_id: ${project_id}`);
                        if (element.value == "DELETE"){
                            ajaxPostRequest('delete', category_id, node_id, project_id);
                        }else if (element.value == "COPY"){
                            //TODO: need to add from category -> to category
                            ajaxPostRequest('copy', category_id, node_id, project_id);
                        }else if (element.value == "MOVE"){
                            //TODO: need to add from category -> to category
                            ajaxPostRequest('move', category_id, node_id, project_id);
                        }
                    });
                });
            });
        </script>
    </head>
    <body>
    <h1>Project categories editor</h1>
    <input name="project_id" type="hidden" value="<?= $currentProjectId ?>"/>

    <p>NOTE: Buttons are implemented, this is in development.</p>

    <?php
echo("<a href='?q=userProjectList'>Back to project list</a>\n");
echo("<a href='?q=projectCategoriesNodesEditor&projectId=$currentProjectId&viewType=1'>View as grid</a><br /><br />\n");
echo("<table border='1px'>\n");
foreach($nodesNamesWithCategories as $categoryName => $categoryNodes){
    ?>
    <tr>
        <td><b>CATEGORY: <?= ($categoryName == "0" ? "others" : $categoryName) ?></b></td>
        <td></td>
        <td></td>
        <td></td>
    </tr>
    <?php
    foreach($categoryNodes as $node){
        ?>
        <tr>
            <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?= $node['displayName'] ?></td>
            <td><?= $node['className'] ?></td>
            <td><img width='120px' src='<?= $node['image'] ?>' /></td>
            <td>
                <input name="copyButton" type="button" value="COPY"/><br />
                <input name="deleteButton" type="button" value="DELETE"/><br />
                <input name="moveButton" type="button" value="MOVE"/>
                <input name="node_id" type="hidden" value="<?= $node['id'] ?>"/>
                <input name="category_id" type="hidden" value="<?= $node['categoryId'] ?>"/>
            </td>
        </tr>
        <?php
    }
}
echo("</table>\n");
?>

    </body>
</html>

