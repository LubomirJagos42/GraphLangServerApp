/**
 *  Helper functions.
 */

GLOBAL_AJAX_RESPONSE = {};

serverAjaxPostSendReceive = function (getParams = [], postParams = [], callbackFunction = null) {
    // Creating Our XMLHttpRequest object
    let xhr = new XMLHttpRequest();

    // Making our connection
    // let url = `?q=${operationStr}&projectId=${projectId}`;
    let url = window.location.href.split('?')[0];
    if (getParams !== null && getParams.length > 0) {
        url += '?';
        for (let k = 0; k < getParams.length; k += 2) {
            if (k + 1 < getParams.length) {
                if (k > 0) url += '&';
                url += getParams[k] + '=' + getParams[k + 1];
            }
        }
    }

    console.log(`serverAjaxPostSendReceive -> sending ajax request to: ${url}`);

    // Set HTTP method to POST
    xhr.open("POST", url, true);

    // Send the proper header information along with the request
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    // function execute after request is successful
    let response = {};
    xhr.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            console.log(`serverAjaxPostSendReceive -> raw text response:`);
            console.log(this.responseText);

            response = JSON.parse(this.responseText.replace('"', '\"'));   //THIS IS REALLY NEEDED TO PARSE JSON CORRECTLY WITHOUT THIS IT'S NOT RUNNING AT ALL!!!

            console.log(`serverAjaxPostSendReceive -> parsed response:`);
            console.log(response);

            GLOBAL_AJAX_RESPONSE = response; //to have access to response in browser
            if (callbackFunction) callbackFunction();   //RUN CALLBACK FUNCTION IF PROVIDED
        }
    }

    // POST parameters array serialization into shape param1=someValue&param2=anotherValue&...
    let postParamsSerialized = '';
    if (postParams !== null && postParams.length > 0) {
        for (let k = 0; k < postParams.length; k += 2) {
            if (k + 1 < postParams.length) {
                if (k > 0) postParamsSerialized += '&';
                postParamsSerialized += postParams[k] + '=' + postParams[k + 1];
            }
        }
    }

    console.log(`post params: ${postParamsSerialized}`);

    // Sending our request
    xhr.send(postParamsSerialized);
}

toHex = function (str) {
    var result = '';
    for (var i=0; i<str.length; i++) {
        result += str.charCodeAt(i).toString(16).padStart(2, '0');
    }
    return result;
}