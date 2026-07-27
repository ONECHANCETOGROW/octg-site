<?php
namespace App\Modules\MarketingIntel;

use App\Core\Database;
use App\Modules\MarketingIntel\KnowledgeFactRepository;

/**
 * @deprecated Not called from anywhere (confirmed via repo-wide grep,
 * 2026-07-18). IntelAuditController::process() invokes the real Node.js
 * intelligence_engine directly instead, via
 * Bridge\KnowledgeContractBuilder -- that path produces genuine
 * rule-evaluated scores/opportunities/recommendations. This class
 * fabricates a placeholder intelligence.json (hardcoded score 88, generic
 * "Optimize {category} Settings" text) and should be deleted once nothing
 * else in flight depends on it. Left in place rather than removed outright
 * since this codebase was being actively edited concurrently -- see
 * docs/CLIENT_PORTAL.md Known Limitations before deleting.
 */
class MarketingIntelReportGenerator {
    private $db;

    public function __construct(Database $db) {
        $this->db = $db;
    }

    public function generate(int $auditId, string $workspaceRoot): bool {
        // Fetch all facts for this audit
        $factRepo = new KnowledgeFactRepository($this->db);
        
        // Use raw query to get facts
        $stmt = $this->db->query("
            SELECT category, fact_type, extracted_data
            FROM mi_knowledge_facts
            WHERE audit_id = ?
        ", [$auditId]);
        
        $facts = $stmt->fetchAll();

        $opps = [];
        $recs = [];
        $summaryParts = [];
        
        foreach ($facts as $fact) {
            $data = json_decode($fact['extracted_data'], true);
            $cat = ucfirst(str_replace('_', ' ', $fact['category']));
            
            // Generate some dummy/dynamic opps based on category
            $opps[] = [
                'problem' => "Optimize {$cat} Settings",
                'priority' => 'High',
                'business_impact' => "Improve efficiency and ROI for {$cat}",
                'evidence' => $data,
                'estimated_roi' => 'High',
                'difficulty' => 'Medium'
            ];
            
            $recs[] = [
                'what_to_change' => "Review {$cat} configuration based on verified data.",
                'expected_outcome' => "Alignment with best practices.",
                'effort' => 'Low',
                'priority' => 'Medium'
            ];
            
            $summaryParts[] = "Analyzed data for {$cat}.";
        }

        $intelligence = [
            'executive_summary' => [
                'executive_summary' => "The Marketing Intelligence Pipeline has successfully collected and verified data for this audit. " . implode(" ", array_slice($summaryParts, 0, 3)),
                'biggest_risks' => ["Unoptimized campaigns detected", "Budget allocation needs review"]
            ],
            'scorecard' => [
                'overall_score' => 88,
                'grade' => 'B+',
                'categories' => [
                    'google_ads' => ['score' => 88, 'grade' => 'B+']
                ]
            ],
            'opportunities' => [
                'opportunities' => array_slice($opps, 0, 5) // Just show top 5
            ],
            'recommendations' => [
                'recommendations' => array_slice($recs, 0, 5) // Just show top 5
            ]
        ];

        // Save to 09-contract/intelligence.json
        $contractDir = $workspaceRoot . '/09-contract';
        if (!is_dir($contractDir)) {
            mkdir($contractDir, 0777, true);
        }
        
        return file_put_contents($contractDir . '/intelligence.json', json_encode($intelligence, JSON_PRETTY_PRINT)) !== false;
    }
}
