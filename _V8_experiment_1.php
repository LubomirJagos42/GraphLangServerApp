
<?php
// Create a new V8Js object
$v8 = new V8Js();

// Define a JavaScript function
$JS = <<<EOT
    (function() {
        return 'Hello World!';
    })();
EOT;

// Execute the JavaScript function using V8js
$result = $v8->executeString($JS);

// Output the result
var_dump($result);
?>

