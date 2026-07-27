<?php

declare(strict_types=1);

namespace App\Services\AIParser;

use App\Core\DbAdapter;
use App\Modules\MarketingIntel\KnowledgeFactRepository;

class AuditMerger
{
    private DbAdapter $db;

    public function __construct()
    {
        $this->db = DbAdapter::instance();
    }

    /**
     * Merges parsed JSON sections into facts and creates an audit.
     * Returns the generated audit_id.
     */
    public function mergeAndCreateAudit(int $clientId, string $moduleSlug, string $periodStart, array $parsedSections): int
    {
        // Find requirement ID and channel ID (default to first requirement for this channel)
        $req = $this->db->one("SELECT r.id, c.id AS channel_id FROM mi_intelligence_requirements r JOIN mi_channels c ON r.channel_id = c.id WHERE c.code = :mod LIMIT 1", ['mod' => $moduleSlug]);
        $reqId = $req ? (int)$req['id'] : 1; // Fallback to 1
        $channelId = $req ? (int)$req['channel_id'] : 1;

        // Find standard collection attempt
        $attempt = $this->db->one("SELECT id FROM mi_collection_attempts LIMIT 1");
        $attemptId = $attempt ? (int)$attempt['id'] : 1;

        // Create new Audit
        $auditId = $this->db->insert('mi_audits', [
            'client_id' => $clientId,
            'title' => ucwords(str_replace('_', ' ', $moduleSlug)) . ' Audit',
            'user_id' => $_SESSION['user_id'] ?? 1,
            'status' => 'collecting',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        // Link audit channel
        $this->db->insert('mi_audit_channels', [
            'audit_id' => $auditId,
            'channel_id' => $channelId,
            'selected_at' => date('Y-m-d H:i:s')
        ]);

        $factRepo = new KnowledgeFactRepository($this->db);

        foreach ($parsedSections as $section => $data) {
            if ($section === 'kpis' && is_array($data)) {
                // Calculate missing conversions if we have clicks and conversion_rate
                if (empty($data['conversions']) && !empty($data['clicks']) && !empty($data['conversion_rate'])) {
                    $data['conversions'] = round($data['clicks'] * ($data['conversion_rate'] / 100), 2);
                }
                
                foreach ($data as $key => $val) {
                    if ($val !== null && $val !== '') {
                        $factRepo->upsertFact($auditId, $reqId, 'statistics', 'kpis', $key, (string)$val, null, $periodStart, null, 100, $attemptId);
                    }
                }
            } 
            elseif (in_array($section, ['campaigns', 'keywords', 'search_terms', 'locations', 'devices'])) {
                foreach ($data as $item) {
                    $entityKey = $item['campaign_name'] ?? $item['keyword'] ?? $item['search_term'] ?? $item['location'] ?? $item['device'] ?? 'Unknown';
                    foreach ($item as $key => $val) {
                        $factRepo->upsertFact($auditId, $reqId, $section, $entityKey, $key, (string)$val, null, $periodStart, null, 100, $attemptId);
                    }
                }
            }
            elseif ($section === 'recommendations') {
                foreach ($data as $i => $item) {
                    $entityKey = 'ai_rec_' . $i;
                    foreach ($item as $key => $val) {
                        $factRepo->upsertFact($auditId, $reqId, 'ai_recommendations', $entityKey, $key, (string)$val, null, $periodStart, null, 100, $attemptId);
                    }
                }
            }
            elseif ($section === 'opportunities') {
                foreach ($data as $i => $item) {
                    $entityKey = 'ai_opp_' . $i;
                    foreach ($item as $key => $val) {
                        $factRepo->upsertFact($auditId, $reqId, 'ai_opportunities', $entityKey, $key, (string)$val, null, $periodStart, null, 100, $attemptId);
                    }
                }
            }
            elseif ($section === 'executive_summary' && is_array($data)) {
                $entityKey = 'summary';
                foreach ($data as $key => $val) {
                    $valStr = is_array($val) ? json_encode($val) : (string)$val;
                    $factRepo->upsertFact($auditId, $reqId, 'ai_executive_summary', $entityKey, $key, $valStr, null, $periodStart, null, 100, $attemptId);
                }
            }
        }

        return (int)$auditId;
    }
}
