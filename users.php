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
// HTML template for headre
require_once $_SERVER['DOCUMENT_ROOT'] . "/template_head_users.php";
$error_message = "";
// Start with DB
// Design pattern singleton
$pdo = Database::instance();
$conn = $pdo->connect();
$allUsers = $pdo->selectAllUsers($conn);
// Printing table of users
foreach ($allUsers as $row) {
    echo "<tr>";
    echo "<td>" . $row['user_name'] . "</td>";
    echo "<td>" . $row['street_number'] . "</td>";
    echo "<td>" . $row['postal_code'] . "</td>";
    echo "<td>" . $row['city'] . "</td>";
    echo "<td>" . $row['country'] . "</td>";
    echo "</tr>
";
}
// HTML template for footer
require_once $_SERVER['DOCUMENT_ROOT'] . "/template_footer_users.php";
?>