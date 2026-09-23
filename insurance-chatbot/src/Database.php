<?php
namespace App;

use MongoDB\Client;

class Database {
    private static ?Client $client = null;
    private static string $dbName  = '';

    public static function connect(): \MongoDB\Database {
        if (self::$client === null) {
            $config       = require __DIR__ . '/../config/config.php';
            self::$client = new Client($config['mongodb']['uri'], [], [
                'serverSelectionTimeoutMS' => 3000,
            ]);
            self::$dbName = $config['mongodb']['database'];
        }

        $db = self::$client->selectDatabase(self::$dbName);

        // Eagerly ping to verify the connection is actually alive
        $db->command(['ping' => 1]);

        return $db;
    }

    public static function reset(): void {
        self::$client = null;
        self::$dbName = '';
    }
}
