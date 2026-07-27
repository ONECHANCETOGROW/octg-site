<?php
require_once __DIR__ . '/StorageInterface.php';
require_once __DIR__ . '/LocalStorageDriver.php';

class Storage {
    private static $driver = null;

    public static function getDriver() {
        if (self::$driver === null) {
            $storagePath = defined('STORAGE_PATH') ? STORAGE_PATH : dirname(BASE_PATH) . '/storage';
            self::$driver = new LocalStorageDriver($storagePath);
        }
        return self::$driver;
    }

    public static function save($path, $contents) {
        return self::getDriver()->save($path, $contents);
    }

    public static function read($path) {
        return self::getDriver()->read($path);
    }

    public static function delete($path) {
        return self::getDriver()->delete($path);
    }

    public static function exists($path) {
        return self::getDriver()->exists($path);
    }

    public static function url($path) {
        return self::getDriver()->url($path);
    }
}
