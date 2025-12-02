<?php
$visitedCategoriesIdList = array();
$recursiveCounter = 0;

/*
 *  Array of visited categories is passed as reference since when I tried it make global it was kind weird, this is ok as referenced array is same over passing to function.
 */
define("MAX_RECURSION_DEPTH", 100);
function recursivePrintCategoryTree($currentParentId = null, &$categoryTree, &$visitedCategoriesIdList){
    global $recursiveCounter;

    echo("<ul>\n");
    foreach ($categoryTree as $categoryId => $categoryProperties) {
        foreach ($categoryProperties["parent_id"] as $categoryParentId) {
            if ($recursiveCounter > MAX_RECURSION_DEPTH) {
                echo("<li>...WARNING: Max recursion depth ".MAX_RECURSION_DEPTH." reached, not continue next!...</li>\n");
                echo("</ul>\n");
                return;
            }

            if ($categoryParentId == $currentParentId) {
                if (in_array($categoryId, $visitedCategoriesIdList)){
                    echo("<li id='cat$categoryId' draggable='true'><b>" . $categoryId . " -> " . $categoryProperties['child_name']." (symlink)</b></li>\n");
                }else{
                    echo("<li id='cat$categoryId' draggable='true'>" . $categoryId . " -> " . $categoryProperties['child_name']."\n");
                    $recursiveCounter++;
                    array_push($visitedCategoriesIdList, $categoryId);
                    recursivePrintCategoryTree($categoryId, $categoryTree, $visitedCategoriesIdList);
                    echo("</li>\n");
                }
                $recursiveCounter--;
            }
        }
    }
    echo("</ul>\n");
}
?>

<html>
<head>
    <script type="text/javascript" src="javascript/utils.js"></script>

    <style type="text/css">
        #categoryTree li{

        }

        [draggable=true] {
            cursor: move;
        }
    </style>
    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', (event) => {
            console.log(`-> document page loaded`);

            //this example source: https://web.dev/articles/drag-and-drop

            // function handleDragStart(e) {
            //     this.style.opacity = '0.4';
            //     this.style.color = '#FF0000';
            // }

            function handleDragStart(e) {
                this.style.opacity = '0.4';

                dragSrcEl = this;

                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/html', this.innerHTML);

                console.log(`-> drag start, category: ${this.id}`);
            }

            function handleDragEnd(e) {
                this.style.opacity = '1';

                items.forEach(function (item) {
                    item.classList.remove('over');
                });
                this.style.color = '#000000';
            }

            function handleDragOver(e) {
                e.preventDefault();
                return false;
            }

            function handleDragEnter(e) {
                this.classList.add('over');
            }

            function handleDragLeave(e) {
                this.classList.remove('over');
            }

            // function handleDrop(e) {
            //     e.stopPropagation(); // stops the browser from redirecting.
            //     return false;
            // }

            function handleDrop(e) {
                e.stopPropagation();

                if (dragSrcEl !== this) {
                    dragSrcEl.innerHTML = this.innerHTML;
                    this.innerHTML = e.dataTransfer.getData('text/html');
                }

                return false;
            }

            let items = document.querySelectorAll('#categoryTree li');
            items.forEach(function(item) {
                item.addEventListener('dragstart', handleDragStart);
                item.addEventListener('dragover', handleDragOver);
                item.addEventListener('dragenter', handleDragEnter);
                item.addEventListener('dragleave', handleDragLeave);
                item.addEventListener('dragend', handleDragEnd);
                item.addEventListener('drop', handleDrop);
            });

            /*
             *  Refresh ode category tree
             */
            getCategoryTree();
        });

        /**********************************************************************************************************
         *  Call server method to assign category to some parent category and evaluate response
         **********************************************************************************************************/
        function manualCategoryToCategoryAdd(){
            let child_category_id = document.querySelector("input[name='child_category']").value;
            let parent_category_id = document.querySelector("input[name='parent_category']").value;
            let projectId = document.querySelector('input[name="project_id"]').value;

            serverAjaxPostSendReceive(
                ["q", "categoryOperation"],
                ["operation", "assignCategoryToCategory", "categoryId", child_category_id, "assignToParentCategoryId", parent_category_id, "projectId", projectId],
                function(){
                    // console.log(GLOBAL_AJAX_RESPONSE);
                    let outputElement = document.getElementById("manualEditActionResult");
                    outputElement.innerHTML = "";
                    outputElement.insertAdjacentHTML("beforeend", `<pre>status: ${GLOBAL_AJAX_RESPONSE.status}</pre>`);
                    outputElement.insertAdjacentHTML("beforeend", `<pre>message: ${GLOBAL_AJAX_RESPONSE.message}</pre>`);
                    outputElement.insertAdjacentHTML("beforeend", `<pre>warning: ${GLOBAL_AJAX_RESPONSE.warningMsg}</pre>`);
                    outputElement.insertAdjacentHTML("beforeend", `<pre>error: ${GLOBAL_AJAX_RESPONSE.errorMsg}</pre>`);

                    if (GLOBAL_AJAX_RESPONSE.status == "1"){
                        getCategoryTree();
                    }
                }
            );
        }

        function manualCategoryToCategoryRemove(){
            let child_category_id = document.querySelector("input[name='child_category']").value;
            let parent_category_id = document.querySelector("input[name='parent_category']").value;
            let projectId = document.querySelector('input[name="project_id"]').value;

            serverAjaxPostSendReceive(
                ["q", "categoryOperation"],
                ["operation", "deleteCategoryToCategory", "categoryId", child_category_id, "assignToParentCategoryId", parent_category_id, "projectId", projectId],
                function(){
                    // console.log(GLOBAL_AJAX_RESPONSE);
                    let outputElement = document.getElementById("manualEditActionResult");
                    outputElement.innerHTML = "";
                    outputElement.insertAdjacentHTML("beforeend", `<pre>status: ${GLOBAL_AJAX_RESPONSE.status}</pre>`);
                    outputElement.insertAdjacentHTML("beforeend", `<pre>message: ${GLOBAL_AJAX_RESPONSE.message}</pre>`);
                    outputElement.insertAdjacentHTML("beforeend", `<pre>warning: ${GLOBAL_AJAX_RESPONSE.warningMsg}</pre>`);
                    outputElement.insertAdjacentHTML("beforeend", `<pre>error: ${GLOBAL_AJAX_RESPONSE.errorMsg}</pre>`);

                    if (GLOBAL_AJAX_RESPONSE.status == "1"){
                        getCategoryTree();
                    }
                }
            );
        }

        /**********************************************************************************************************
         *  Get category tree using ajax post
         **********************************************************************************************************/
        function recursivePrintCategoryTree(outputElement, currentParentId = null){
            let treeParentElement = document.createElement("ul");
            outputElement.appendChild(treeParentElement);

            for (const [categoryId, categoryValues] of Object.entries(categoryTree)){
                for (let parent_id of categoryValues.parent_id){
                    if (RECURSIVE_COUNTER > MAX_RECURSION_DEPTH) {
                        let treeCategoryElement = document.createElement("li");
                        treeParentElement.appendChild(treeCategoryElement);
                        treeCategoryElement.insertAdjacentHTML("beforeend", `...WARNING: Max recursion depth ${MAX_RECURSION_DEPTH} reached, not continue next!...`);
                        return;
                    }

                    if (parent_id == currentParentId){
                        let treeCategoryElement = document.createElement("li");
                        treeCategoryElement.insertAdjacentHTML("beforeend", `<input type="hidden" name="category_id" value="${categoryId}" />`);

                        treeParentElement.appendChild(treeCategoryElement);

                        if (visitedCategoriesIdList.includes(categoryId)){
                            treeCategoryElement.insertAdjacentHTML("beforeend", `<b>${categoryId} -> ${categoryValues.child_name} (symlink)</b>`);
                        }else{
                            visitedCategoriesIdList.push(categoryId);
                            treeCategoryElement.insertAdjacentHTML("beforeend", `${categoryId} -> ${categoryValues.child_name}`);
                            RECURSIVE_COUNTER++;
                            recursivePrintCategoryTree(treeCategoryElement, categoryId);
                        }
                        RECURSIVE_COUNTER--;
                    }
                }
            }

        }

        function fillCategoryTree(inputCategoryTree = {}){
            let outputElement = document.querySelector('#categoryTree');
            outputElement.innerHTML = "";

            MAX_RECURSION_DEPTH = 10;
            RECURSIVE_COUNTER = 0;

            categoryTree = inputCategoryTree;
            console.log(categoryTree);
            visitedCategoriesIdList = [];

            /*
             *  This prints category tree, starts from root element
             */
            outputElement.insertAdjacentHTML('beforeend', 'Categories starting at root:<br />')
            recursivePrintCategoryTree(outputElement, null);

            /*
             *  There could be loop referencies and therefore some categories doesn't have to be printed if they have no null parent in circular referencies
             *  therefore check if all categories were printed and print additional not used ones in recursive functions.
             */
            outputElement.insertAdjacentHTML('beforeend', 'Categories with circular symlinks not acessible from root category:<br />')
            for (const [categoryId, categoryValues] of Object.entries(categoryTree)){
                if (visitedCategoriesIdList.includes(categoryId) === false){
                    recursivePrintCategoryTree(outputElement, categoryId);
                }
            }
        }

        function getCategoryTree(){
            //project id is taken from hidden input from current page
            let projectId = document.querySelector('input[name="project_id"]').value;
            console.log(`-> getting project category tree for project: ${projectId}`);

            serverAjaxPostSendReceive(
                ["q", "projectCategoryTreeEditor"],
                ["projectId", projectId, "usePost", "T"],
                function(){
                    fillCategoryTree(GLOBAL_AJAX_RESPONSE);
                }
            );
        }
    </script>
</head>
<body>
<h1>Project Category Tree Editor</h1>
<input name="project_id" type="hidden" value="<?= $currentProjectId ?>"/>

<a href='?q=userProjectList'>Back to project list</a>&nbsp;&nbsp;&nbsp;&nbsp;
<br /><br />

<h2>MANUAL EDITOR</h2>
<div id="manualCategoryEditor">
    <form id="manualCategoryEditorForm" method="post">
        Child: <input name="child_category" type="text" />
        Parent: <input name="parent_category" type="text" />
        <input id="manualCategoryAssignmentAddButton" type="button" onclick="manualCategoryToCategoryAdd()" value="ADD"/>
        <input id="manualCategoryAssignmentRemoveButton" type="button" onclick="manualCategoryToCategoryRemove()" value="REMOVE"/>
    </form>
    <br />
    <div id="manualEditActionResult">
    </div>
</div>

<h2>CATEGORIES TREE</h2>
<span>
    <input type="button" value="REFRESH" onClick="getCategoryTree()"/>
</span>
<div id="categoryTree">
<?php //= recursivePrintCategoryTree(null, $categoryTree, $visitedCategoriesIdList); ?><!--<hr />-->
</div>

</body>
</html>
