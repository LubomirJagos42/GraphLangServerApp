<html>
    <head>
        <script type="text/javascript" src="javascript/utils.js"></script>
        <script type="text/javascript">
            addEventListener("load", (event) => {

                /*
                 *  AJAX function
                 *  TODO: as input use object like: options:{categorySource: number, categoryTarget: number, nodeId: number, projectId: number} so params could be named
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

                        let node_id = -1;
                        if (['DELETE','MOVE','COPY'].indexOf(element.value) > -1) node_id = element.closest('td').querySelector('input[name="node_id"]').value;
                        let category_id = element.closest('td').querySelector('input[name="category_id"]').value;
                        let project_id = document.querySelector('input[name="project_id"]').value;

                        // console.log(`going delete node id: ${node_id}, category id: ${category_id}, project_id: ${project_id}`);
                        if (element.value == "DELETE"){
                            ajaxPostRequest('delete', category_id, node_id, project_id);

                            //UI move <tr> under category others when delete last instance from category ie. there is no node with same class under different category
                            categoryIndex = [...document.querySelectorAll('tr td')].map(i => i.innerHTML.search('CATEGORY: others') > -1).indexOf(true);

                            nodeInstanciesCount = [...document.querySelectorAll(`input[type="hidden"][value="${node_id}"]`)].length;
                            if (nodeInstanciesCount == 1) {
                                //change hidden input with category id value to unknown
                                event.target.closest('td').querySelector('input[type="hidden"][name="category_id"]').value = "";

                                //if this was last instance move it to others category
                                [...document.querySelectorAll('tr td')].at(categoryIndex).closest('tr').after(
                                    event.target.closest('tr')
                                );
                            }else{
                                //if there are more instancies just erase row
                                event.target.closest('tr').remove();
                            }
                        }else if (element.value == "COPY"){
                            //TODO: need to add from category -> to category
                            let target_category = -1;

                            ajaxPostRequest('copy', category_id, target_category, node_id, project_id);
                        }else if (element.value == "MOVE"){
                            //TODO: need to add from category -> to category
                            let target_category = -1;

                            ajaxPostRequest('move', category_id, target_category, node_id, project_id);

                            /* move element <tr> to some other category
                            categoryIndex = $$('tr td').map(i => i.innerHTML.search('Python GUI') > -1).indexOf(true);
                            $$('tr td').at(categoryIndex).closest('tr').after(
                                $('input[value="COPY"]').closest('tr')
                            );
                            */
                        }else if (element.value == "DELETE CATEGORY"){
                            //TODO: call delete for all nodes under it than delete categorz

                            let categoryNameInCell = event.target.closest('td').querySelector('input[name="category_name"]').value;
                            let categoryIdInCell = event.target.closest('td').querySelector('input[name="category_id"]').value;
                            let categoryTableRows = [...document.querySelectorAll('#categoryTable tr')];

                            alert(`categoryDelete, '${categoryNameInCell}', categoryId: ${category_id}`)

                            /*
                             *  iterate over lines and delete nodes, can click on buttons
                             */
                            categoryTableRows.forEach((value, index) => {
                                if (value.querySelector('input[name="category_id"]') &&
                                    value.querySelector('input[name="category_id"]').value == categoryIdInCell &&
                                    value.querySelector('input[name="deleteButton"]'))
                                {
                                    let button = value.querySelector('input[name="deleteButton"]');
                                    button.style.background = "red";
                                    button.click();
                                }
                            });

                            //TODO: call remove category and erase it's line



                            //ajaxPostRequest('move', category_id, target_category, node_id, project_id);

                            /* move element <tr> to some other category
                            categoryIndex = $$('tr td').map(i => i.innerHTML.search('Python GUI') > -1).indexOf(true);
                            $$('tr td').at(categoryIndex).closest('tr').after(
                                $('input[value="COPY"]').closest('tr')
                            );
                            */
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
    <a href='?q=userProjectList'>Back to project list</a>
    <a href='?q=projectCategoriesNodesEditor&projectId=<?= $currentProjectId ?>&viewType=1'>View as grid</a><br /><br />

    <table id="newCategoryEditor">
        <tr>
            <td>Create new category:</td>
            <td><input name="newCategoryName" type="text" value=""/></td>
            <td><input name="newCateggorySaveButton" type="button" value="ADD"/></td>
        </tr>
    </table>
    <br />

    <?php
echo("<table id='categoryTable' border='1px'>\n");
foreach($nodesNamesWithCategories as $categoryName => $categoryNodes){
    ?>
    <tr>
        <td><b>CATEGORY: <?= ($categoryName == "0" ? "others" : $categoryName) ?></b></td>
        <td>
            <?php if ($categoryName != "0") {?>
                <input name="deleteCategoryButton" type="button" value="DELETE CATEGORY"/>
                <input name="category_id" type="hidden" value="<?= $categoryNodes[0]['categoryId'] ?>"/>
                <input name="category_name" type="hidden" value="<?= $categoryName ?>"/>
            <?php } ?>
        </td>
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

foreach ($emptyCategories as $category){
    ?>
    <tr>
        <td><b>CATEGORY: <?= $category['name'] ?></b></td>
        <td>
            <input name="deleteCategoryButton" type="button" value="DELETE CATEGORY"/>
            <input name="category_id" type="hidden" value="<?= $category['id'] ?>"/>
            <input name="category_name" type="hidden" value="<?= $category['name'] ?>"/>
        </td>
        <td></td>
        <td></td>
    </tr>
    <?php
}

echo("</table>\n");
?>

    </body>
</html>

