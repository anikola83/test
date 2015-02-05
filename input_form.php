<?php
/**
 * @author      Nikola Adzic <adzicnikola83@gmail.com>
 * @version     1.0
 */

/**
 * Class for handling with database
 * Design pattern singleton
 */
class Database {
    protected static $_instance = null;
/**
 * Database parameters
 */
    public $host = 'localhost';
    public $dbname = 'test';
    public $username = 'root';
    public $password = '';
    public $dsn = 'mysql:host=$this->host;dbname=$this->dbname';
/**
 * Returns the Singleton instance of this class.
 * @return instance
 */
    public static function instance() {
        if (!isset(self::$_instance)) {
            self::$_instance = new Database();
        }
        return self::$_instance;
    }
/**
 * Protected constructor to prevent creating a new instance
 */
    protected function __construct() {
    }
/**
 * Making connection with database
 * @return connection
 */
    public function connect() {
        $conn = null;
        $conn = new PDO('mysql:host=localhost;dbname=test', 'root', '');
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    }
/**
 * Private clone method to prevent cloning of the instance
 */
    private function __clone() {
        return false;
    }
/**
 * prevent unserializing
 */
    private function __wakeup() {
        return false;
    }
/**
 * @return Return all users fields from database.
 */
    public function selectAllUsers($conn) {
        $results = $conn->query("SELECT * FROM user_info");
        $results->execute();
        $allUsers = $results->fetchAll();
        return $allUsers;
    }
/**
 * Insert new user to database
 */
    public function insertUser($sql, $conn) {
        $query = $conn->prepare($sql);
        $query->execute();
    }
}
/**
 * Class for creating input form and validation of data
 * Design pattern dependency injection
 */
class FormTemplate {
/**
 * Array of input names for form fields
 */
    private $formInputNames = array('first_name', 'last_name', 'city', 'street', 'postal', 'country');
/**
 * Array for define the maximum size of the field
 */
    private $formInputMaxLength = array('30', '30', '20', '100', '6', '20');
/**
 * Array for define type of the field
 */
    private $formInputType = array('text', 'text', 'text', 'text', 'number', 'text');
/**
 * Using for keeping HTML code of input form
 */
    private $form;
/**
 * If this variable is "true" that means that all fields are correctly filled.
 * We assume that everything is correct, and after we deny (set to "false") if something incorrectly filled.
 */
    private $isFormValid = true;
/**
 * Creating whole HTML code for input form
 */
    function __construct() {
        $this->form = '<form method="post" action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '" id="form_id" name="form_id">
<ul id="homeworkcontent">
';
        for ($index = 0;$index < count($this->formInputNames);$index++) {
            $this->form.= '<li><label for="' . $this->formInputNames[$index] . '" id="' . $this->formInputNames[$index] .
            	 '_style" ' . $this->errorStyle($index) . '>' . ucfirst($this->formInputNames[$index]) . 
            	':</label><input id="' . $this->formInputNames[$index] . 
            	'" type="' . (($this->formInputType[$index] == 'text') ? ($this->formInputType[$index] . 
            	'" maxlength="' . $this->formInputMaxLength[$index] . '"') : ($this->formInputType[$index] . 
            	'" min="1" max="99999"')) . ' name="' . $this->formInputNames[$index] . 
            	'" value="' . (($this->formInputNames[$index] != 'country') ? ($this->postMethod($index)) : ('Germany')) . 
            	'" ' . (($this->formInputNames[$index] != 'country') ? ('') : ('readonly')) . '></li>
   ';
        }
        $this->form.= '<li><input type="submit" name="submit" value="Submit" id="submit_id" onclick="JavaScript:return validate();" class="homeinput"></li>
</ul>
</form>
';
    }
/**
 * @return HTML form code.
 */
    public function __toString() {
        return (string)$this->form;
    }
/**
 * @return Array of input names for form fields
 */
    public function getFormInputNames() {
        return $this->formInputNames;
    }
/**
 * Warning: if the field is not correctly filled
 * @param Index of array for current input field
 * @return HTML style
 */
    private function errorStyle($index) {
        if ($this->inputTestType($index)) {
            return 'style="color:#000000"';
        } else {
            $this->setIsFormValid(false);
            return 'style="color:#ff0000"';
        }
    }
/**
 * Define POST method and filter characters
 * @param Index of array for current input field
 * @return filtered POST method
 */
    private function postMethod($index) {
        $data = (empty($_POST[$this->formInputNames[$index]])) ? ('') : ($_POST[$this->formInputNames[$index]]);
        $data = $this->inputFilter($data);
        return $data;
    }
/**
 * Filtering character
 * @param string data
 * @return filtered character
 */
    private function inputFilter($data) {
        return htmlspecialchars(stripslashes(trim($data)));
    }
/**
 * Testing form type for current input field
 * @param Index of array for current input field
 * @return True or false
 */
    private function inputTestType($index) {
        switch ($this->formInputType[$index]) {
            case "text":
                return (is_string($this->postMethod($index)) && !empty($this->postMethod($index))) ? (true) : (false);
            break;
            case "number":
                return (is_numeric($this->postMethod($index))) ? (true) : (false);
            break;
            default:
                return false;
            break;
        }
    }
/**
 * Return data accuracy of form
 * @return True or false
 */
    public function getIsFormValid() {
        return $this->isFormValid;
    }
/**
 * Set data accuracy of form
 */
    private function setIsFormValid($boolTmp) {
        $this->isFormValid = $boolTmp;
    }
/**
 * Return input fields names and values
 * @return Array
 */
    public function formValues() {
        $arrayKeys = $this->getFormInputNames();
        $arrayValues = array();
        for ($index = 0;$index < count($arrayKeys);$index++) {
            $arrayValues[$index] = $this->postMethod($index);
        }
        return array_combine($arrayKeys, $arrayValues);
    }
}
/**
 * Class for design pattern dependency injection
 */
class FormValidate {
    private $form;
    public function __construct(FormTemplate $form) {
        $this->form = $form;
    }
    public function __toString() {
        return (string)$this->form;
    }
}
/**
 * Class for receiving, processing, testing data from API server
 */
class ApiTest {
/**
 * Url for receiving data from API server
 */
    private $getUrl;
    private $url = "https://interview.performance-technologies.de/api/address";
    private $token = "?token=f6a7e125d7078626a766b600befb1f01bcb6b9e3";
/**
 * Error message for users
 */
    private $errorMessage;
/**
 * JSON object from API server
 */
    private $json;
/**
 * Making url for receiving data from API server and prepare JSON object
 * @param Array of input fields names and values
 */
    public function __construct($formValues) {
        $this->getUrl = $this->url;
        $this->getUrl.= $this->token;
        foreach ($formValues as $key => $value) {
            $this->getUrl.= '&' . $key . '=' . urlencode($value);
        }
        $this->json = $this->getApiData($this->getUrl);
    }
    public function __toString() {
        return (string)$this->getUrl;
    }
/**
 * Set error message
 * @param error message
 */
    private function setErrorMessage($message) {
        $this->errorMessage = $message;
    }
/**
 * @return error message
 */
    public function getErrorMessage() {
        return $this->errorMessage;
    }
/**
 * This method is for receiving data from API server and store error messages to error log file.
 * @param Url for receiving data from API server
 * @return JSON object
 */
    private function getApiData($getUrl) {
        try {
        	$obj = @file_get_contents($getUrl);
        	if (empty($obj)){
        		throw new Exception('Error receiving data from server API.');
        	}
        }catch (Exception $e) {
    		$this->apiErrorLog($e->getMessage());
    		echo 'Exception: ',  $e->getMessage(), "\n";
		} 
        return json_decode($obj);
    }
/**
 * storing error messages to error log file.
 * @param Error messages
 */
    private function apiErrorLog($errorMessage) {
    	$logFile = fopen($_SERVER['DOCUMENT_ROOT']."/api_error_log.txt", "a") or exit(0);
    	fwrite($logFile,date('Y-m-d',time()).'T'.date('H:i:sP',time()).' '.$errorMessage.'
');
    	fclose($logFile);
    	exit(0);
    }
/**
 * Return Api Data
 * @return JSON object
 */
    public function returnApiData() {
        return $this->json;
    }
}
$errorMessage = "";
// HTML template for headre
require_once $_SERVER['DOCUMENT_ROOT'] . "/template_head_form.php";
// dependency injection
$form = new FormTemplate();
$formTest = new FormValidate($form);
// Printing HTML code for input form
echo $form;
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if ($form->getIsFormValid()) {
        $apiTest = new ApiTest(array_slice($form->formValues(), 2));
        $testingObj = new stdClass();
        $testingObj = $apiTest->returnApiData();
        $errorMessage .= $apiTest->getErrorMessage();
// Testing object from API server. If quality of object is greater than 80, continue further
        if ($testingObj->success) {
            $dataQualityCount = 0;
            $arrayObj=(array)$testingObj->result;
            foreach ($arrayObj as $key => $objTmp) {
 				if (intval($objTmp->quality) >= 80) {
                 		 $dataQualityCount++;
                    	echo intval($objTmp->quality).'<br>';
               	}else {
                    $errorMessage .= "Quality is not ok. Try agein.<br>";
                }
 			}
// If all object have correct quality, begin with registration in the database
            if (count((array)$testingObj->result) == $dataQualityCount)
            {
// Start with storing in DB
// Design pattern singleton
                $pdo = Database::instance();
                $conn = $pdo->connect();
                $sqlInsertValues = $form->formValues();
                $pdo->insertUser('INSERT INTO user_info 
                					(user_name,street_number,postal_code,city,country)
                				 VALUES ("' . $sqlInsertValues['first_name'] . ' ' . $sqlInsertValues['last_name'] . '","' . $sqlInsertValues['street'] . '",' . $sqlInsertValues['postal'] . ',"' . $sqlInsertValues['city'] . '","Germany")', $conn);
                header('Location: /users.php');
            }
        }
    }
}
// HTML template for footer
require_once $_SERVER['DOCUMENT_ROOT'] . "/template_footer_form.php";
?>