<html>
    <head>
    </head>
    <body>

<h1>Project categories editor</h1>

<p>NOTE: Just view, no operations implemented..</p>

<?php
echo("<a href='?q=userProjectList'>Back to project list</a>\n");
echo("<a href='?q=projectCategoriesNodesEditor&projectId=$currentProjectId'>View as table</a><br />");
foreach($nodesNamesWithCategories as $categoryName => $categoryNodes){
    echo("<h2 style='background: none; float: left; width: 100%'>CATEGORY: ". ($categoryName == "0" ? "others" : $categoryName) ."</h2>\n");
    echo("<div style='width: 100%; float: left; background: none;'>");
    foreach($categoryNodes as $node){
        ?>
        <div style="background: none; float: left; margin: 5px; border: 1px solid black;">
            <div style="background: none; width: 100%; text-align: center;"><img width='120px' src='<?= $node["image"]?>' /></div>
            <div style="background: none; width: 100%; text-align: center;"><?= $node["displayName"] ?></div>
            <div style="background: none; width: 100%; text-align: center;"><?= $node["className"] ?></div>
            <br />
            <div>
                <input type="button" value="COPY"/>
                <input type="button" value="DELETE"/>
                <input type="button" value="MOVE"/>
            </div>
        </div>
        <?php
    }
    echo("</div>");
}

foreach ($emptyCategories as $category){
    echo("<h2 style='background: none; float: left; width: 100%'>CATEGORY: ". ($category['name'] == "0" ? "others" : $category['name']) ."</h2>\n");
}
?>

    </body>
</html>
