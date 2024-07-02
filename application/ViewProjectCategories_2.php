<html>
    <head>
        <style type="text/css">
        </style>

        <script type="text/javascript" src="javascript/utils.js"></script>
        <script type="text/javascript">
            addEventListener("load", (event) => {

                /*
                 *  AJAX function
                 *      - input parameter postParams is object like:
                 *          {
                 *              categorySource: number,
                 *              categoryTarget: number,
                 *              nodeId: number,
                 *              projectId: number
                 *          }
                 */
                function ajaxPostRequest(postParams, callbackFunction = null){
                    let xhr = new XMLHttpRequest();
                    let url = `?q=categoryOperation&projectId=${postParams["projectId"]}`;
                    xhr.open("POST", url, true);
                    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

                    xhr.onreadystatechange = function () {
                        if (this.readyState == 4 && this.status == 200) {
                            // console.log(this.responseText);

                            /*
                             *  THIS IS REALLY NEEDED TO PARSE JSON CORRECTLY WITHOUT THIS IT'S NOT RUNNING AT ALL!!!
                             */
                            response = JSON.parse(this.responseText.replace('"','\"'));

                            /*
                             *  Print to console response from server
                             */
                            if (response.error) console.warn(response);
                            else console.log(response);

                            /*
                             *  Run callback function, if is defined
                             */
                            if (typeof callbackFunction == "function") callbackFunction();
                        }
                    }

                    /*
                     *  This serializes input postParams object into POST string format var1_name=value_1&var2_name=value_2&...
                     */
                    let postStr = "";
                    let firstParam = true;
                    for (const [key, value] of Object.entries(postParams)){
                        firstParam ? (firstParam = false) : (postStr += '&');
                        postStr += `${key}=${value}`;
                    }
                    //console.log(postStr);
                    xhr.send(postStr);
                }

                function generalButtonOnClick(event, element) {
                    let node_id = -1;
                    let category_id = -1;
                    let project_id = -1;

                    if (['DELETE', 'MOVE', 'COPY'].indexOf(element.value) > -1) node_id = element.closest('div').querySelector('input[name="node_id"]').value;
                    if (['DELETE', 'MOVE', 'COPY', 'DELETE CATEGORY', 'RENAME CATEGORY'].indexOf(element.value) > -1) category_id = element.closest('div').querySelector('input[name="category_id"]').value;
                    project_id = document.querySelector('input[name="project_id"]').value;

                    if (element.value == "DELETE") {
                        console.log(`DELETE NODE FROM CATEGORY node: ${node_id}, category : ${category_id}`);

                        let postParams = {};
                        postParams["operation"] = "deleteNodeFromCategory";
                        postParams["categoryId"] = category_id;
                        postParams["projectId"] = project_id;
                        postParams["nodeId"] = node_id;
                        ajaxPostRequest(postParams, function () {
                            if (response.status == 1) {

                                nodeInstanciesCount = [...document.querySelectorAll(`input[type="hidden"][value="${node_id}"]`)].length;
                                if (nodeInstanciesCount == 1) {
                                    //change hidden input with category id value to unknown
                                    event.target.closest('div').querySelector('input[type="hidden"][name="category_id"]').value = "";

                                    //if this was last instance move it to others category
                                    //UI move <tr> under category others when delete last instance from category ie. there is no node with same class under different category
                                    categoryIndex = [...document.querySelectorAll('.categoryName')].map(i => i.innerHTML.search('CATEGORY: others') > -1).indexOf(true);
                                    [...document.querySelectorAll('.categoryName')].at(categoryIndex).closest('.categoryBlock').querySelector('.categoryButtons').after(
                                        event.target.closest('.nodeBlock')
                                    );
                                    event.target.closest('div').querySelector('input[name="category_id"]').value = "";
                                } else {
                                    //if there are more instancies just erase row
                                    event.target.closest('.nodeBlock').remove();
                                }

                            }
                        });

                    } else if (element.value == "COPY") {
                        let target_category = -1;
                        target_category = event.target.closest('div').querySelector("select[name='add_target_category']").value;

                        let targetCategoryStartRow = null;
                        let categoryTableRows = [...document.querySelectorAll('.categoryButtons')];
                        categoryTableRows.forEach((htmlRow, index) => {
                            if (htmlRow.querySelector('input[name="category_id"]') &&
                                htmlRow.querySelector('input[name="category_id"]').value == target_category &&
                                htmlRow.querySelector('input[name="deleteCategoryButton"]')) {
                                targetCategoryStartRow = htmlRow;
                            }
                        });

                        let postParams = {};
                        postParams["operation"] = "addNodeToCategory";
                        postParams["categoryId"] = target_category;
                        postParams["projectId"] = project_id;
                        postParams["nodeId"] = node_id;
                        console.log(`COPY - ${JSON.stringify(postParams)}`);
                        ajaxPostRequest(postParams, function () {
                            if (response.status == 1) {
                                let clonedRow = event.target.closest('.nodeBlock').cloneNode(true);
                                clonedRow.querySelector('input[name="category_id"]').value = target_category;   //change hidden category_id input to target category

                                targetCategoryStartRow.after(clonedRow);                                        //add table row under new category
                                renewAllButtonsOnClick();                                                       //buttons were also copied so renew handlers
                            } else {
                                console.warn(`There was error during NODE COPY TO CATEGORY:\n${response.errorMsg}`);
                            }
                        });

                    } else if (element.value == "MOVE") {
                        let postParams;
                        let resultMoveOperation = true;
                        let target_category = -1;
                        target_category = event.target.closest('div').querySelector("select[name='move_target_category']").value;
                        console.log(`target category MOVE ${target_category}`);
                        /*
                         *  Get HTML reference for table target category row
                         */
                        let targetCategoryStartRow = null;
                        let categoryTableRows = [...document.querySelectorAll('.categoryButtons')];
                        categoryTableRows.forEach((htmlRow, index) => {
                            if (htmlRow.querySelector('input[name="category_id"]') &&
                                htmlRow.querySelector('input[name="category_id"]').value == target_category &&
                                htmlRow.querySelector('input[name="deleteCategoryButton"]')) {
                                targetCategoryStartRow = htmlRow;
                            }
                        });

                        /*
                         *  DELETE ROW
                         */
                        postParams = {};
                        postParams["operation"] = "deleteNodeFromCategory";
                        postParams["categoryId"] = category_id;
                        postParams["projectId"] = project_id;
                        postParams["nodeId"] = node_id;
                        console.log(`MOVE - delete - ${JSON.stringify(postParams)}`);
                        ajaxPostRequest(postParams, function () {
                            //TODO: implement ajax post function
                            if (response.status == 1) {
                                //event.target.closest('tr').remove();                            //remove row from table
                                console.log(`MODE - delete - OK`)
                            } else {
                                resultMoveOperation = false;
                                console.warn(`There was error during MOVE NODE - DELETE FROM CATEGORY:\n${response.errorMsg}`);
                            }
                        });

                        /*
                         *  ADD ROW under new category
                         */
                        postParams = {};
                        postParams["operation"] = "addNodeToCategory";
                        postParams["categoryId"] = target_category;
                        postParams["projectId"] = project_id;
                        postParams["nodeId"] = node_id;
                        console.log(`MOVE - add - ${JSON.stringify(postParams)}`);
                        ajaxPostRequest(postParams, function () {
                            //TODO: implement ajax post function
                            if (response.status == 1) {
                                let rowToMove = event.target.closest('.nodeBlock');
                                rowToMove.querySelector('input[name="category_id"]').value = target_category;
                                targetCategoryStartRow.after(rowToMove);                       //add table row under new category
                            } else {
                                resultMoveOperation = false;
                                console.warn(`There was error during MOVE NODE - ADD TO CATEGORY:\n${response.errorMsg}`);
                            }
                        });

                        //if there was error during whole operation there is error displayed
                        if (resultMoveOperation == false) {
                            console.warn(`There was some issue with NODE MOVE TO CATEGORY operation, check result.`);
                        }

                    } else if (element.value == "DELETE CATEGORY") {
                        console.log(`DELETE CATEGORY category: ${category_id}`);

                        let categoryIdInCell = event.target.closest('.categoryBlock').querySelector('.categoryButtons').querySelector('input[name="category_id"]').value;
                        let categoryTableRows = [...document.querySelectorAll('.nodeBlock')];

                        //alert(`categoryDelete, '${categoryNameInCell}', categoryId: ${category_id}`)

                        /*
                         *  iterate over lines and delete nodes, can click on buttons
                         */
                        categoryTableRows.forEach((value, index) => {
                            if (value.querySelector('input[name="category_id"]') &&
                                value.querySelector('input[name="category_id"]').value == categoryIdInCell &&
                                value.querySelector('input[name="deleteButton"]')) {
                                let button = value.querySelector('input[name="deleteButton"]');
                                // button.style.background = "red";
                                button.click();
                            }
                        });

                        let postParams = {};
                        postParams["operation"] = "deleteCategory";
                        postParams["categoryId"] = category_id;
                        postParams["projectId"] = project_id;
                        ajaxPostRequest(postParams, function () {
                            element.closest('.categoryBlock').remove();

                            //remove category option from all select list for moving node to category
                            [...document.querySelectorAll('select[name="add_target_category"] > option')].forEach((element) => {
                                if (element.value == category_id) element.remove();
                            });

                            //remove category option from all select list top copy node to category
                            [...document.querySelectorAll('select[name="move_target_category"] > option')].forEach((element) => {
                                if (element.value == category_id) element.remove();
                            });
                        });
                    } else if (element.value == "ADD CATEGORY") {
                        let postParams = {};
                        postParams["operation"] = "addCategory";
                        postParams["categoryName"] = document.querySelector('input[name="newCategoryName"]').value;
                        postParams["projectId"] = project_id;
                        ajaxPostRequest(postParams, function () {
                            if (response.status == 1) {
                                let rowHtml = "";
                                rowHtml += `<div class="categoryBlock">`;
                                rowHtml += `<h2 class='categoryName' style='background: none; float: left; width: 100%'>CATEGORY: ${response.categoryName}</h2>`;
                                rowHtml += `<div class="categoryButtons" style="float: left; width: 100%; margin-bottom: 20px;">`;
                                rowHtml += `<input name="deleteCategoryButton" type="button" value="DELETE CATEGORY"/>`;
                                rowHtml += `&nbsp;&nbsp;&nbsp;&nbsp;`;
                                rowHtml += `<input name="renameCategoryButton" type="button" value="RENAME CATEGORY"/>`;
                                rowHtml += `<input name="newCategoryName" type="text" value=""/>`;
                                rowHtml += `<input name="category_id" type="hidden" value="${response.categoryId}"/>`;
                                rowHtml += `<input name="category_name" type="hidden" value="${response.categoryName}"/>`;
                                rowHtml += `</div>`;
                                rowHtml += `</div>`;
                                document.querySelector('#allCategoriesWrapper').insertAdjacentHTML('beforeend', rowHtml);

                                //add category option from all select list for moving node to category
                                [...document.querySelectorAll('select[name="add_target_category"]')].forEach((element) => {
                                    element.insertAdjacentHTML('beforeend',`<option value="${response.categoryId}">${response.categoryName}</option>`);
                                });

                                //add category option from all select list top copy node to category
                                [...document.querySelectorAll('select[name="move_target_category"]')].forEach((element) => {
                                    element.insertAdjacentHTML('beforeend',`<option value="${response.categoryId}">${response.categoryName}</option>`);
                                });

                                /*
                                 * There are newly added button so rerun on click handlers assignement.
                                 */
                                renewAllButtonsOnClick();
                            } else {
                                console.warn(`CREATE CATEGORY ERROR: ${response.errorMsg}`);
                            }
                        });
                    } else if (element.value == "RENAME CATEGORY") {
                        let newCategoryName = element.closest('.categoryButtons').querySelector('input[name="newCategoryName"]').value;
                        let postParams = {};
                        postParams["operation"] = "renameCategory";
                        postParams["categoryId"] = category_id;
                        postParams["categoryName"] = newCategoryName;
                        ajaxPostRequest(postParams, function () {
                            if (response.status == 1) {
                                let categoryNameElement = element.closest('.categoryBlock').querySelector('.categoryName');
                                categoryNameElement.innerHTML = "";
                                categoryNameElement.insertAdjacentHTML('afterbegin',`CATEGORY: ${newCategoryName}`);

                                //add category option from all select list for moving node to category
                                [...document.querySelectorAll('select[name="add_target_category"]')].forEach((element) => {
                                    [...element.querySelectorAll('option')].forEach((optionElement) => {
                                        if (optionElement.value == category_id) optionElement.innerHTML = newCategoryName;
                                    });
                                });

                                //add category option from all select list top copy node to category
                                [...document.querySelectorAll('select[name="move_target_category"]')].forEach((element) => {
                                    [...element.querySelectorAll('option')].forEach((optionElement) => {
                                        if (optionElement.value == category_id) optionElement.innerHTML = newCategoryName;
                                    });
                                });

                            } else {
                                console.warn(`RENAME CATEGORY ERROR: ${response.errorMsg}`);
                            }
                        });
                    }
                }

                /*
                 *  General function to add function to all buttons
                 */
                function renewAllButtonsOnClick() {
                    document.querySelectorAll('input[type="button"]').forEach((element) => {

                        /*
                         *  Event listener is not used due I don't know how to properly remove it and therefore if renew...() function is called multiple times same handler
                         *  is attached multiple times and same function is triggered multiple times.
                         *
                         *  Can be used but before assigned event listener must be removed using properly:
                         *      element.removeEventListener("click", functionName);
                         */
                        // element.addEventListener("click", function(event){
                        //     generalButtonOnClick(event, element);
                        // });

                        /*
                         *  Add onclick on button
                         */
                        element.onclick = function(event){
                            generalButtonOnClick(event, element);
                        }
                    });
                }

                /*
                 *  assign on click function to all buttons
                 */
                renewAllButtonsOnClick();
            });
        </script>
    </head>
    <body>

<h1>Project categories editor</h1>
<input name="project_id" type="hidden" value="<?= $currentProjectId ?>"/>

<a href='?q=userProjectList'>Back to project list</a>
<a href='?q=projectCategoriesNodesEditor&projectId=<?= $currentProjectId ?>'>View as table</a>
<br />
<br />

<table id="newCategoryEditor">
    <tr>
        <td>Create new category:</td>
        <td><input name="newCategoryName" type="text" value=""/></td>
        <td><input name="newCateggorySaveButton" type="button" value="ADD CATEGORY"/></td>
    </tr>
</table>

<div id="allCategoriesWrapper">
<?php
foreach($nodesNamesWithCategories as $categoryName => $categoryNodes){
    ?>
    <div class="categoryBlock" style='width: 100%; float: left; background: none;'>
        <h2 class='categoryName' style='background: none; float: left; width: 100%'>CATEGORY: <?= ($categoryName == "0" ? "others" : $categoryName) ?></h2>
        <div class="categoryButtons" style="float: left; width: 100%; margin-bottom: 20px;">
            <?php if ($categoryName != "0") {?>
                <input name="deleteCategoryButton" type="button" value="DELETE CATEGORY"/>
                &nbsp;&nbsp;&nbsp;&nbsp;
                <input name="renameCategoryButton" type="button" value="RENAME CATEGORY"/>
                <input name="newCategoryName" type="text" value=""/>

                <input name="category_id" type="hidden" value="<?= $categoryNodes[0]['categoryId'] ?>"/>
                <input name="category_name" type="hidden" value="<?= $categoryName ?>"/>
            <?php } ?>
        </div>

    <?php
    foreach($categoryNodes as $node){
        ?>
        <div class="nodeBlock" style="background: none; float: left; margin: 5px; border: 1px solid black;">
            <div style="background: none; width: 100%; text-align: center;"><img width='120px' src='<?= $node["image"]?>' /></div>
            <div style="background: none; width: 100%; text-align: center;"><?= $node["displayName"] ?></div>
            <div style="background: none; width: 100%; text-align: center;"><?= $node["className"] ?></div>
            <br />
            <div>
                <a href="?q=shapeDesigner&projectId=<?= $currentProjectId ?>&nodeClassName=<?= $node['className']?>">edit symbol</a>
                <br /><br />
                <input name="copyButton" type="button" value="COPY"/>
                <select name="add_target_category">
                    <option value="-1"></option>
                    <?php foreach ($categoriesIdNamesList as $category ){ ?>
                        <option value="<?= $category['id'] ?>"><?= $category['name'] ?></option>
                    <?php } ?>
                </select>
                <br />
                <input name="deleteButton" type="button" value="DELETE"/><br />
                <input name="moveButton" type="button" value="MOVE"/>
                <select name="move_target_category">
                    <option value="-1"></option>
                    <?php foreach ($categoriesIdNamesList as $category ){ ?>
                        <option value="<?= $category['id'] ?>"><?= $category['name'] ?></option>
                    <?php } ?>
                </select>
                <input name="node_id" type="hidden" value="<?= $node['id'] ?>"/>
                <input name="category_id" type="hidden" value="<?= $node['categoryId'] ?>"/>
            </div>
        </div>
    <?php
    }
    ?>
    </div>
    <?php
}

foreach ($emptyCategories as $category){
    ?>
    <div class="categoryBlock">
        <h2 class='categoryName' style='background: none; float: left; width: 100%'>CATEGORY: <?= ($category['name'] == "0" ? "others" : $category['name']) ?></h2>
        <div class="categoryButtons" style="float: left; width: 100%; margin-bottom: 20px;">
                <input name="deleteCategoryButton" type="button" value="DELETE CATEGORY"/>
                &nbsp;&nbsp;&nbsp;&nbsp;
                <input name="renameCategoryButton" type="button" value="RENAME CATEGORY"/>
                <input name="newCategoryName" type="text" value=""/>

                <input name="category_id" type="hidden" value="<?= $category['id'] ?>"/>
                <input name="category_name" type="hidden" value="<?= $category['name'] ?>"/>
        </div>
    </div>
    <?php
}
?>
</div>
    </body>
</html>
