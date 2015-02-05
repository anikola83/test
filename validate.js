// form=false This means that the input form is not filled in correctly
var form = false;
// Fields for verification
var inputElements = ["first_name", "last_name", "city", "street", "postal", "country"];
var inputValues = [];
/**
 * If input form is correct generate click on submit button
 * @return Click on submit button
 */
function formTest($bool) {
    form = $bool;
    if (form) {
        document.getElementById("submit_id").click();
    }
}
/**
 * Error messages for users
 */
function errorMessage(message) 
{
    var messageElement = document.getElementById("errorMessage");
    messageElement.innerHTML = messageElement.innerHTML + "<br>" + message;
}
/**
 * Validate input form fields
 * @return Click on submit button
 */
function validate() {
// Number of filled inputs
    var filled = 0;
// Test form valuse
    for (index = 0; index < inputElements.length; index++) {
        if (document.getElementById(inputElements[index]).value.length == 0) {
            document.getElementById(inputElements[index] + '_style').style.color = "#ff0000";
        } else {
            document.getElementById(inputElements[index] + '_style').style.color = "#000000";
            filled++;
            inputValues[index] = document.getElementById(inputElements[index]).value;
        }
    }
    if (filled == inputElements.length) {
        loadXMLDoc();
        if (form == true) {
            return (true);
        } else {
            return (false);
        }
    } else {
        return (false);
    }
}
/**
 * Generate url for API server request
 * @return url
 */
function generateUrl(elements, values) {
    var getUrl = 'https://interview.performance-technologies.de/api/address?token=';
    getUrl += "f6a7e125d7078626a766b600befb1f01bcb6b9e3"
    for (index = 2; index < elements.length; index++) {
        getUrl += "&" + elements[index] + "=" + encodeURIComponent(values[index]);
    }
    return getUrl;
}
/**
 * Making AJAX request
 */
function loadXMLDoc() {
    req = false;
    if (window.XMLHttpRequest) {
        try {
            req = new XMLHttpRequest();
        } catch (e) {
            req = false;
        }
    } else if (window.ActiveXObject) {
        try {
            req = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (e) {
            try {
                req = new ActiveXObject("Microsoft.XMLHTTP");
            } catch (e) {
                req = false;
            }
        }
    }
    if (req) {
        req.open("GET", generateUrl(inputElements, inputValues), true);
        req.send(null);
    }
    req.onreadystatechange = function() {
        if (req.readyState == 4 && req.status == 200) {
            jsonParse(req.responseText);
        }
        if (req.status == 404) {
            errorMessage("Error retrieve data! File not found: 404");
            formTest(false);
            return (false);
        }
    }
}
/**
 * Testing quality of JSON object
 * @param JSON object
 * @return True or false
 */
function jsonParse(obj) {
    try {
        var jsonObj = JSON.parse(obj);
        var dataQuality = 0;
        if (jsonObj.success == "true") {
            var keys = Object.keys(jsonObj.result).sort();
            var numObj = keys.length;
// Testing object from API server. If quality of object is greater than 80, continue further            
            for (index = 0; index < numObj; index++) {
                if (parseInt(jsonObj.result[keys[index]].quality) >= 80) {
                    dataQuality++;
                } else {
                    errorMessage("Quality is not correct for object:" + index + ". Try again with new values in form.");
                    formTest(false);
                    return (false);
                }
            }
// If all object have correct quality, we set variable form to true and generate click on submit form, else report error message
            if (numObj == dataQuality) {
                form = true;
                formTest(true);
                return (true);
            } else {
                errorMessage("Data is not correct. Try again with new values in form.");
                formTest(false);
                return (false);
            }
        } else {
            errorMessage("Error, object is not correct. Try again with new values in form.");
            formTest(false);
            return (false);
        }
    } catch (e) {
        errorMessage("Error parse JSON object. Try again with new values in form.");
        formTest(false);
        return (false);
    }
    formTest(false);
}