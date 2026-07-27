<?php
interface StorageInterface {
    public function save($path, $contents);
    public function read($path);
    public function delete($path);
    public function exists($path);
    public function url($path);
}
