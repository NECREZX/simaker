<?php
/**
 * Database Connection Class
 * Using PDO for secure database operations
 */

class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    // Prevent cloning
    private function __clone() {}
    
    // Prevent unserialization
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

/**
 * Database Helper Functions
 */

// Get database connection
function db() {
    return Database::getInstance()->getConnection();
}

// Execute query with parameters
function query($sql, $params = []) {
    $db = db();
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

// Fetch all rows
function fetchAll($sql, $params = []) {
    $stmt = query($sql, $params);
    return $stmt->fetchAll();
}

// Fetch single row
function fetchOne($sql, $params = []) {
    $stmt = query($sql, $params);
    return $stmt->fetch();
}

// Insert and return last insert ID
function insert($table, $data) {
    $keys = array_keys($data);
    $fields = implode(', ', $keys);
    $placeholders = ':' . implode(', :', $keys);
    
    $sql = "INSERT INTO $table ($fields) VALUES ($placeholders)";
    $stmt = db()->prepare($sql);
    $stmt->execute($data);
    
    return db()->lastInsertId();
}

// Update records
function update($table, $data, $where, $whereParams = []) {
    $set = [];
    foreach ($data as $key => $value) {
        $set[] = "$key = :$key";
    }
    $setClause = implode(', ', $set);
    
    $sql = "UPDATE $table SET $setClause WHERE $where";
    $params = array_merge($data, $whereParams);
    
    $stmt = db()->prepare($sql);
    return $stmt->execute($params);
}

// Delete records
function delete($table, $where, $params = []) {
    $sql = "DELETE FROM $table WHERE $where";
    $stmt = db()->prepare($sql);
    return $stmt->execute($params);
}

// Count records
function countRecords($table, $where = '1=1', $params = []) {
    $sql = "SELECT COUNT(*) as count FROM $table WHERE $where";
    $result = fetchOne($sql, $params);
    return $result['count'] ?? 0;
}
