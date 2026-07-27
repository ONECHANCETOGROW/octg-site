<?php

declare(strict_types=1);

namespace App\Modules\ClientPortal;

require_once BASE_PATH . '/app/Modules/ClientPortal/ManualEntryModuleController.php';

final class SocialModuleController extends ManualEntryModuleController
{
    protected string $moduleKey = 'social';
    protected string $viewFolder = 'social';
    protected string $activeMenu = 'social';
    protected bool $supportsPlatforms = true;

    protected function pageTitle(): string
    {
        return 'Social Media';
    }
}
