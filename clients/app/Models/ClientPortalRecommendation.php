<?php

declare(strict_types=1);

require_once BASE_PATH . '/app/Models/PortalModule.php';

class ClientPortalRecommendation extends Model {
    
    private function resolveModuleId($module): int
    {
        if (is_numeric($module)) {
            return (int) $module;
        }
        $pm = new PortalModule();
        return $pm->getIdBySlug((string) $module);
    }

    public function forClient($clientId, $status = null) {
        if ($status) {
            $stmt = $this->db->prepare(
                "SELECT r.*, pm.slug as module_slug, pm.name as module_name 
                 FROM client_portal_recommendations r
                 JOIN portal_modules pm ON pm.id = r.module_id
                 WHERE r.client_id = ? AND r.status = ? 
                 ORDER BY FIELD(r.priority,'High','Medium','Low'), r.created_at DESC"
            );
            $stmt->execute([$clientId, $status]);
        } else {
            $stmt = $this->db->prepare(
                "SELECT r.*, pm.slug as module_slug, pm.name as module_name 
                 FROM client_portal_recommendations r
                 JOIN portal_modules pm ON pm.id = r.module_id
                 WHERE r.client_id = ? 
                 ORDER BY FIELD(r.priority,'High','Medium','Low'), r.created_at DESC"
            );
            $stmt->execute([$clientId]);
        }
        return $stmt->fetchAll();
    }

    /**
     * Insert-if-new (by source_recommendation_id) -- never overwrites an
     * existing row's status/due_date, only adds genuinely new
     * recommendations from a fresh report.
     */
    public function syncOne(
        int $clientId,
        $module,
        string $sourceId,
        int $reportId,
        string $whatToChange,
        string $whyItMatters,
        string $priority,
        ?int $auditId = null,
        ?string $reportVersion = null,
        ?string $periodStart = null,
        string $source = 'intelligence_engine'
    ) {
        $moduleId = $this->resolveModuleId($module);

        $stmt = $this->db->prepare(
            "SELECT id FROM client_portal_recommendations WHERE client_id = ? AND module_id = ? AND source_recommendation_id = ?"
        );
        $stmt->execute([$clientId, $moduleId, $sourceId]);
        if ($stmt->fetch()) {
            return;
        }

        $insert = $this->db->prepare(
            "INSERT INTO client_portal_recommendations
                (client_id, module_id, source_recommendation_id, source_report_id, audit_id, report_version, period_start, what_to_change, why_it_matters, priority, source)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $insert->execute([$clientId, $moduleId, $sourceId, $reportId, $auditId, $reportVersion, $periodStart, $whatToChange, $whyItMatters, $priority, $source]);
    }

    public function addManual(int $clientId, $module, string $whatToChange, string $whyItMatters, string $priority, ?string $periodStart = null)
    {
        $moduleId = $this->resolveModuleId($module);
        $sourceId = 'manual_' . bin2hex(random_bytes(8));

        $insert = $this->db->prepare(
            "INSERT INTO client_portal_recommendations
                (client_id, module_id, source_recommendation_id, what_to_change, why_it_matters, priority, period_start, source, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, 'manual', 'open')"
        );
        $insert->execute([$clientId, $moduleId, $sourceId, $whatToChange, $whyItMatters, $priority, $periodStart]);
        return (int) $this->db->lastInsertId();
    }

    public function updateStatus($id, $clientId, $status, $dueDate = null) {
        $stmt = $this->db->prepare(
            "UPDATE client_portal_recommendations SET status = ?, due_date = ? WHERE id = ? AND client_id = ?"
        );
        return $stmt->execute([$status, $dueDate, $id, $clientId]);
    }

    public function delete(int $id, int $clientId): bool
    {
        $stmt = $this->db->prepare("DELETE FROM client_portal_recommendations WHERE id = ? AND client_id = ?");
        return $stmt->execute([$id, $clientId]);
    }
}
