<?php
$koneksi = mysqli_connect("localhost", "root", "", "republik_computer_db");

if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}


// Konfigurasi Database
define('DB_HOST', 'localhost');     // Host database (biasanya localhost)
define('DB_USER', 'root');          // Username MySQL (default: root)
define('DB_PASS', '');              // Password MySQL (default: kosong)
define('DB_NAME', 'republik_computer_db'); // Nama database

// Zona waktu
date_default_timezone_set('Asia/Jakarta');

// Class Database Connection
class Database {
    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $dbname = DB_NAME;
    
    public $conn;
    
    /**
     * Method untuk membuat koneksi database
     * @return mysqli connection
     */
    public function connect() {
        $this->conn = null;
        
        try {
            // Membuat koneksi mysqli
            $this->conn = new mysqli(
                $this->host, 
                $this->user, 
                $this->pass, 
                $this->dbname
            );
            
            // Set charset ke UTF-8
            $this->conn->set_charset("utf8mb4");
            
            // Cek koneksi
            if ($this->conn->connect_error) {
                throw new Exception("Koneksi gagal: " . $this->conn->connect_error);
            }
            
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
            exit;
        }
        
        return $this->conn;
    }
    
    /**
     * Method untuk menutup koneksi
     */
    public function close() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}

/**
 * Fungsi helper untuk mendapatkan koneksi database
 * @return mysqli connection
 */
function getConnection() {
    $database = new Database();
    return $database->connect();
}

/**
 * Fungsi helper untuk membersihkan input dari SQL injection
 * @param string $data - Data yang akan dibersihkan
 * @return string - Data yang sudah dibersihkan
 */
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Fungsi helper untuk validasi email
 * @param string $email
 * @return boolean
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Fungsi helper untuk hash password
 * @param string $password
 * @return string
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Fungsi helper untuk verifikasi password
 * @param string $password - Password plain text
 * @param string $hash - Password yang sudah di-hash
 * @return boolean
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Test koneksi (bisa dikomentari setelah berhasil)
// Uncomment 3 baris di bawah untuk test koneksi
/*
$db = new Database();
$conn = $db->connect();
echo "Koneksi berhasil!";
*/
?>