<?php
require_once BASE_PATH . '/app/Models/Client.php';
require_once BASE_PATH . '/app/Models/ClientPortalModule.php';

/**
 * Shared base for every client-facing (not staff) controller.
 *
 * The isolation guarantee ("after login a client sees ONLY their own
 * business") is enforced here, once, rather than trusted to be repeated
 * correctly in every controller method:
 *
 *   - clientId() is the ONLY source of truth for "which client is this
 *     request for" -- it reads $_SESSION['client_id'], set exclusively by
 *     ClientAuthController::login() at authentication time. It never reads
 *     a URL parameter or form field.
 *   - resolveSlugOrRedirect() checks the URL's client slug (used for
 *     bookmarkable, branded URLs like /client/independent-rv/dashboard)
 *     against the session's actual client_id. If a logged-in client edits
 *     the URL to another client's slug, this redirects them back to their
 *     own slug rather than serving the other client's page.
 *
 * Every client-portal controller should extend this class and call
 * resolveSlugOrRedirect($params['slug']) as the first line of every
 * action that takes a slug route parameter.
 */
abstract class ClientPortalBase extends Controller {

    /** @var array<string,mixed>|null */
    protected $currentClient = null;

    protected function clientId() {
        return (int) ($_SESSION['client_id'] ?? 0);
    }

    protected function clientUserId() {
        return (int) ($_SESSION['client_user_id'] ?? 0);
    }

    /**
     * @return array<string,mixed>
     */
    protected function resolveSlugOrRedirect($slug) {
        $clientModel = new Client();
        $client = $clientModel->getById($this->clientId());

        if (!$client) {
            // Session points at a client that no longer exists -- fail
            // safe by logging the session out entirely rather than
            // rendering a page with no client context.
            unset($_SESSION['client_user_id'], $_SESSION['client_id']);
            header('Location: /portal/login');
            exit;
        }

        if ($slug !== null && $slug !== $client['slug']) {
            header('Location: ' . $this->clientUrl($client['slug'], $this->currentPathSuffix($slug)));
            exit;
        }

        $this->currentClient = $client;
        return $client;
    }

    protected function clientUrl($slug, $suffix = 'dashboard') {
        return '/client/' . $slug . '/' . ltrim($suffix, '/');
    }

    /**
     * Best-effort: reuse whatever came after the slug in the current
     * request so a slug-mismatch redirect still lands on the right page
     * (e.g. .../wrong-slug/google-ads redirects to .../my-slug/google-ads,
     * not always back to the dashboard root).
     */
    private function currentPathSuffix($requestedSlug) {
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';
        $prefix = '/client/' . $requestedSlug . '/';
        if (strpos($uri, $prefix) === 0) {
            return substr($uri, strlen($prefix));
        }
        return 'dashboard';
    }

    protected function viewPortal($view, $data = []) {
        extract($data);
        $client = $this->currentClient;
        $csrf_token = $this->generateCSRF();

        $moduleModel = new \ClientPortalModule();
        $disabledModules = $moduleModel->disabledFor($client['id']);

        $viewFile = BASE_PATH . "/app/Views/portal/{$view}.php";
        if (file_exists($viewFile)) {
            require BASE_PATH . '/app/Views/portal/layouts/main.php';
        } else {
            die("Portal view does not exist: " . $viewFile);
        }
    }
}
