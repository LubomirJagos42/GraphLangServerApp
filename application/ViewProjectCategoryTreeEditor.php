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
        });

        /**********************************************************************************************************
         *  Call server method to assign category to some parent category and evaluate response
         **********************************************************************************************************/
        function manualAssignCategoryToCategory(){
            let child_category_id = document.querySelector("input[name='child_category']").value;
            let parent_category_id = document.querySelector("input[name='parent_category']").value;

            serverAjaxPostSendReceive(
                ["q", "categoryOperation"],
                ["operation", "assignCategoryToCategory", "categoryId", child_category_id, "assignToParentCategoryId", parent_category_id],
                function(){
                    // console.log(GLOBAL_AJAX_RESPONSE);
                    let outputElement = document.getElementById("manualEditActionResult");
                    outputElement.innerHTML = "";
                    outputElement.insertAdjacentHTML("beforeend", `<pre>status: ${GLOBAL_AJAX_RESPONSE.status}</pre>`);
                    outputElement.insertAdjacentHTML("beforeend", `<pre>message: ${GLOBAL_AJAX_RESPONSE.message}</pre>`);
                    outputElement.insertAdjacentHTML("beforeend", `<pre>warning: ${GLOBAL_AJAX_RESPONSE.warningMsg}</pre>`);
                    outputElement.insertAdjacentHTML("beforeend", `<pre>error: ${GLOBAL_AJAX_RESPONSE.errorMsg}</pre>`);
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
        <input id="manualCategoryEditButton" type="button" onclick="manualAssignCategoryToCategory()" value="SUBMIT"/>
    </form>
    <br />
    <div id="manualEditActionResult">
    </div>
</div>

<h2>CATEGORIES TREE</h2>
<div id="categoryTree">
<?= recursivePrintCategoryTree(null, $categoryTree, $visitedCategoriesIdList); ?>
</div>

</body>
</html>
