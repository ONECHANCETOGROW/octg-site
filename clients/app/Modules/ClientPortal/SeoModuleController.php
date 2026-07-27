<?php

declare(strict_types=1);

namespace App\Modules\ClientPortal;

require_once BASE_PATH . '/app/Modules/ClientPortal/ManualEntryModuleController.php';

final class SeoModuleController extends ManualEntryModuleController
{
    protected string $moduleKey = 'seo';
    protected string $viewFolder = 'seo';
    protected string $activeMenu = 'seo';

    protected function pageTitle(): string
    {
        return 'Website SEO';
    }
}
