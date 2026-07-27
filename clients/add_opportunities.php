<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('BASE_PATH', __DIR__);
require_once BASE_PATH . '/config/config.php';
require_once BASE_PATH . '/app/Core/Database.php';

try {
    $db = Database::getInstance();
    $clientId = 1;
    $periodStart = '2026-07-01';
    
    // Raw Markdown for Admin side
    $rawMarkdown = "1. Scale Performance Max Budget (High Priority)
Opportunity ID: OPP-AI-PMAX-BUDGET
Priority: High
ROI Impact: High
Effort: Low (Simple budget adjustment)
Description: Increase the daily budget of the Performance Max Calls 1/21/25 campaign by $32.00/day (from $58.00 to $90.00). This campaign is converting exceptionally well (137 conversions at $12.53 CPA) but operates at near-maximum capacity (97% utilization), meaning it is frequently capped. Expanding this budget is forecasted to capture 5.9 additional weekly conversions ($8.4k in weekly conversion value).

2. Add Zero-Conversion Search Terms as Negative Keywords (Medium Priority)
Opportunity ID: OPP-AI-NEG-AUDIT
Priority: Medium
ROI Impact: Medium
Effort: Low (Quick copy-paste in UI)
Description: Identify and exclude zero-conversion local terms (such as \"modesto rv center\") as negative keywords. While it only accounted for $2.81 in wasted spend in the last 30 days, regularly filtering out competing dealership names or services you don't offer keeps your Search campaign highly efficient and preserves your budget for converting terms.

3. Align Ad Copy with Specialized Financing Programs (Medium Priority)
Opportunity ID: OPP-AI-FINANCING-COPY
Priority: Medium
ROI Impact: High
Effort: Medium (Requires drafting new headlines/sitelinks)
Description: Directly feature your unique value propositions, such as ITIN programs and zero down payment options, within your Search ad copy headlines. Since these programs are a primary differentiator for your dealership, highlighting them will significantly improve your ad CTR and capture high-intent buyers looking for flexible financing options.";

    // 1. Update Admin Marketing Workspace (client_ai_imports)
    $stmt = $db->prepare("SELECT id FROM client_ai_import_jobs WHERE client_id = ? AND module = 'google_ads' AND period_start = ? ORDER BY id DESC LIMIT 1");
    $stmt->execute([$clientId, $periodStart]);
    $job = $stmt->fetch();
    
    if ($job) {
        $jobId = $job['id'];
        $upd = $db->prepare("UPDATE client_ai_imports SET raw_response = ? WHERE job_id = ? AND section = 'opportunities'");
        $upd->execute([$rawMarkdown, $jobId]);
        echo "Admin Marketing Workspace updated successfully.<br>";
    } else {
        echo "Job not found for Admin Marketing Workspace.<br>";
    }

    // 2. Update Client Dashboard (intelligence.json)
    $stmt = $db->prepare("SELECT id FROM mi_audits WHERE client_id = ? ORDER BY id DESC");
    $stmt->execute([$clientId]);
    $audits = $stmt->fetchAll();
    
    $storagePath = defined('STORAGE_PATH') ? STORAGE_PATH : dirname(BASE_PATH) . '/storage';
    
    $updated = false;
    foreach ($audits as $audit) {
        $auditId = $audit['id'];
        $contractFile = $storagePath . "/clients/{$clientId}/{$auditId}/09-contract/intelligence.json";
        
        if (file_exists($contractFile)) {
            $json = json_decode(file_get_contents($contractFile), true);
            
            $json['opportunities'] = [
                [
                    'opportunity_id' => 'OPP-AI-PMAX-BUDGET',
                    'problem' => 'Scale Performance Max Budget',
                    'priority' => 'High',
                    'estimated_roi' => 'High',
                    'difficulty' => 'Low',
                    'business_impact' => 'Increase the daily budget of the Performance Max Calls 1/21/25 campaign by $32.00/day (from $58.00 to $90.00). This campaign is converting exceptionally well (137 conversions at $12.53 CPA) but operates at near-maximum capacity (97% utilization), meaning it is frequently capped. Expanding this budget is forecasted to capture 5.9 additional weekly conversions ($8.4k in weekly conversion value).',
                    'evidence' => []
                ],
                [
                    'opportunity_id' => 'OPP-AI-NEG-AUDIT',
                    'problem' => 'Add Zero-Conversion Search Terms as Negative Keywords',
                    'priority' => 'Medium',
                    'estimated_roi' => 'Medium',
                    'difficulty' => 'Low',
                    'business_impact' => 'Identify and exclude zero-conversion local terms (such as "modesto rv center") as negative keywords. While it only accounted for $2.81 in wasted spend in the last 30 days, regularly filtering out competing dealership names or services you don\'t offer keeps your Search campaign highly efficient and preserves your budget for converting terms.',
                    'evidence' => []
                ],
                [
                    'opportunity_id' => 'OPP-AI-FINANCING-COPY',
                    'problem' => 'Align Ad Copy with Specialized Financing Programs',
                    'priority' => 'Medium',
                    'estimated_roi' => 'High',
                    'difficulty' => 'Medium',
                    'business_impact' => 'Directly feature your unique value propositions, such as ITIN programs and zero down payment options, within your Search ad copy headlines. Since these programs are a primary differentiator for your dealership, highlighting them will significantly improve your ad CTR and capture high-intent buyers looking for flexible financing options.',
                    'evidence' => []
                ]
            ];
            
            file_put_contents($contractFile, json_encode($json, JSON_PRETTY_PRINT));
            echo "Client Dashboard intelligence.json (Audit ID $auditId) updated successfully.<br>";
            $updated = true;
            break; // Stop after updating the latest successful one
        }
    }
    
    if (!$updated) {
        echo "No valid intelligence.json found in any audit.<br>";
    }

} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage();
}
