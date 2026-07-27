<?php
class LocalStorageDriver implements StorageInterface {
    private $basePath;

    public function __construct($basePath = null) {
        if ($basePath) {
            $this->basePath = rtrim($basePath, '/\\');
        } else {
            $this->basePath = dirname(BASE_PATH) . '/storage';
        }
    }

    private function getFullPath($path) {
        $path = ltrim($path, '/\\');
        return $this->basePath . DIRECTORY_SEPARATOR . $path;
    }

    private function ensureDirectoryExists($filePath) {
        $dir = dirname($filePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    public function save($path, $contents) {
        $fullPath = $this->getFullPath($path);
        $this->ensureDirectoryExists($fullPath);
        return file_put_contents($fullPath, $contents) !== false;
    }

    public function read($path) {
        $fullPath = $this->getFullPath($path);
        if ($this->exists($path)) {
            return file_get_contents($fullPath);
        }
        return false;
    }

    public function delete($path) {
        $fullPath = $this->getFullPath($path);
        if ($this->exists($path)) {
            return unlink($fullPath);
        }
        return false;
    }

    public function exists($path) {
        return file_exists($this->getFullPath($path));
    }

    public function url($path) {
        // Serve files via an authenticated route
        return '/download?path=' . urlencode($path);
    }
}
