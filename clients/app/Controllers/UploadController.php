<?php
require_once BASE_PATH . '/app/Services/Storage/Storage.php';
require_once BASE_PATH . '/app/Models/Upload.php';
require_once BASE_PATH . '/app/Models/Audit.php';

class UploadController extends Controller {
    public function process() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['file'])) {
            echo json_encode(['success' => false, 'error' => 'No file uploaded']);
            return;
        }

        $auditId = $_POST['audit_id'] ?? null;
        $clientId = $_POST['client_id'] ?? null;
        
        if (!$auditId || !$clientId) {
            echo json_encode(['success' => false, 'error' => 'Missing audit context']);
            return;
        }
        
        $file = $_FILES['file'];
        
        // Basic Validation
        $allowedTypes = ['application/pdf', 'text/csv', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
        $allowedExts = ['pdf', 'csv', 'xlsx'];
        
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExts) || (!in_array($file['type'], $allowedTypes) && $file['type'] !== '')) {
            // Some environments don't set mime reliably, so extension check is primary
            if (!in_array($ext, $allowedExts)) {
                echo json_encode(['success' => false, 'error' => 'Invalid file type. Only PDF, CSV, and XLSX are allowed.']);
                return;
            }
        }

        // Checksum validation for duplicates
        $checksum = hash_file('sha256', $file['tmp_name']);
        
        $uploadModel = new Upload();
        $existing = $uploadModel->findByChecksumAndAudit($checksum, $auditId);
        if ($existing) {
            echo json_encode(['success' => false, 'error' => 'Duplicate file detected']);
            return;
        }

        // Generate storage path: clients/{client_id}/{audit_id}/01-original/{filename}
        $filename = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9.\-_]/', '', $file['name']);
        $storagePath = "clients/{$clientId}/{$auditId}/01-original/{$filename}";
        
        $contents = file_get_contents($file['tmp_name']);
        
        if (Storage::save($storagePath, $contents)) {
            // Save to DB
            $uploadModel->create([
                'audit_id' => $auditId,
                'client_id' => $clientId,
                'file_path' => $storagePath,
                'original_name' => $file['name'],
                'mime_type' => $file['type'] ?: 'application/octet-stream',
                'size' => $file['size'],
                'checksum' => $checksum,
                'status' => 'validated'
            ]);
            
            // Generate Manifest (Overwrites with latest)
            $this->updateManifest($auditId, $clientId);
            
            echo json_encode(['success' => true, 'file' => $file['name']]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Storage failed']);
        }
    }
    
    private function updateManifest($auditId, $clientId) {
        $uploadModel = new Upload();
        $files = $uploadModel->getByAuditId($auditId);
        
        $auditModel = new Audit();
        $audit = $auditModel->getById($auditId);
        
        $manifest = [
            'audit' => $audit['name'],
            'client' => $audit['business_name'],
            'files' => array_map(function($f) {
                return [
                    'name' => $f['original_name'],
                    'sha256' => $f['checksum'],
                    'status' => $f['status']
                ];
            }, $files)
        ];
        
        Storage::save("clients/{$clientId}/{$auditId}/manifest.json", json_encode($manifest, JSON_PRETTY_PRINT));
        
        // Also log to processing.log
        $logEntry = "[" . date('Y-m-d H:i:s') . "] File Uploaded & Validated: " . end($files)['original_name'] . "\n";
        $logPath = "clients/{$clientId}/{$auditId}/05-logs/processing.log";
        $existingLog = Storage::exists($logPath) ? Storage::read($logPath) : "";
        Storage::save($logPath, $existingLog . $logEntry);
    }
}
