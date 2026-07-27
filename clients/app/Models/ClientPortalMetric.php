<?php

declare(strict_types=1);

require_once BASE_PATH . '/app/Models/PortalModule.php';

/**
 * Generic historical metrics store shared by every manual-entry client
 * dashboard module (SEO/GBP/Social/Website Performance).
 */
class ClientPortalMetric extends Model {
    
    private function resolveModuleId($module): int
    {
        if (is_numeric($module)) {
            return (int) $module;
        }
        $pm = new PortalModule();
        return $pm->getIdBySlug((string) $module);
    }

    public function latest($clientId, $module, $platform = '') {
        $moduleId = $this->resolveModuleId($module);
        $stmt = $this->db->prepare(
            "SELECT * FROM client_portal_metrics
             WHERE client_id = ? AND module_id = ? AND platform = ?
             ORDER BY period_start DESC LIMIT 1"
        );
        $stmt->execute([$clientId, $moduleId, $platform]);
        return $stmt->fetch() ?: null;
    }

    public function getForPeriod($clientId, $module, $platform, $periodStart) {
        $moduleId = $this->resolveModuleId($module);
        $stmt = $this->db->prepare(
            "SELECT * FROM client_portal_metrics
             WHERE client_id = ? AND module_id = ? AND platform = ? AND period_start = ?"
        );
        $stmt->execute([$clientId, $moduleId, $platform, $periodStart]);
        return $stmt->fetch() ?: null;
    }

    public function previous($clientId, $module, $platform, $beforePeriodStart) {
        $moduleId = $this->resolveModuleId($module);
        $stmt = $this->db->prepare(
            "SELECT * FROM client_portal_metrics
             WHERE client_id = ? AND module_id = ? AND platform = ? AND period_start < ?
             ORDER BY period_start DESC LIMIT 1"
        );
        $stmt->execute([$clientId, $moduleId, $platform, $beforePeriodStart]);
        return $stmt->fetch() ?: null;
    }

    public function history($clientId, $module, $platform = '', $limit = 12) {
        $moduleId = $this->resolveModuleId($module);
        $stmt = $this->db->prepare(
            "SELECT * FROM client_portal_metrics
             WHERE client_id = ? AND module_id = ? AND platform = ?
             ORDER BY period_start DESC LIMIT " . (int) $limit
        );
        $stmt->execute([$clientId, $moduleId, $platform]);
        return $stmt->fetchAll();
    }

    /**
     * Every distinct platform with data for a client+module (used by the
     * Social module, which can have 0-5 platforms active at once).
     */
    public function platformsWithData($clientId, $module) {
        $moduleId = $this->resolveModuleId($module);
        $stmt = $this->db->prepare(
            "SELECT DISTINCT platform FROM client_portal_metrics WHERE client_id = ? AND module_id = ?"
        );
        $stmt->execute([$clientId, $moduleId]);
        return array_column($stmt->fetchAll(), 'platform');
    }

    /**
     * Insert-or-update for THIS period only.
     */
    public function upsertForPeriod($clientId, $module, $platform, $periodStart, $periodEnd, array $data, $enteredByUserId) {
        $moduleId = $this->resolveModuleId($module);
        $existing = $this->getForPeriod($clientId, $moduleId, $platform, $periodStart);
        $dataJson = json_encode($data);

        if ($existing) {
            $update = $this->db->prepare(
                "UPDATE client_portal_metrics SET data_json = ?, entered_by_user_id = ?, period_end = ? WHERE id = ?"
            );
            $update->execute([$dataJson, $enteredByUserId, $periodEnd, $existing['id']]);
            return (int) $existing['id'];
        }

        $insert = $this->db->prepare(
            "INSERT INTO client_portal_metrics
                (client_id, module_id, platform, period_start, period_end, source, data_json, entered_by_user_id)
             VALUES (?, ?, ?, ?, ?, 'manual', ?, ?)"
        );
        $insert->execute([$clientId, $moduleId, $platform, $periodStart, $periodEnd, $dataJson, $enteredByUserId]);
        return (int) $this->db->lastInsertId();
    }
}
