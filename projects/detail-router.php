<?php
/* ==========================================================================
   DETAIL-ROUTER.PHP — dynamic router for case studies.
   Routes any non-physical slug.php request in /projects/ to includes/case-study.php.
   ========================================================================== */
$caseSlug = $_GET['slug'] ?? '';
if (empty($caseSlug)) {
    header("Location: /projects.php");
    exit;
}

require __DIR__ . '/../includes/case-study.php';
