<?php
require_once BASE_PATH . '/app/Models/Client.php';
require_once BASE_PATH . '/app/Models/ClientUser.php';

class ClientController extends Controller {
    public function index() {
        $clientModel = new Client();
        $clients = $clientModel->getAll();

        $clientUserModel = new ClientUser();
        $portalAccessByClientId = [];
        foreach ($clients as $c) {
            $portalAccessByClientId[$c['id']] = $clientUserModel->forClient($c['id']);
        }

        $this->view('layouts/main', [
            'title' => 'Clients - OCTG Intelligence',
            'content_view' => 'clients/index',
            'active_menu' => 'clients',
            'breadcrumbs' => [
                ['label' => 'Clients']
            ],
            'clients' => $clients,
            'portalAccessByClientId' => $portalAccessByClientId,
            'csrf_token' => $this->generateCSRF(),
        ]);
    }

    /**
     * Admin action: create a client-portal login for this client.
     * Generates a temporary password (must_reset_password = 1, so the
     * client is forced to set their own on first login) and hands it
     * back on the redirect so the admin can relay it -- there is no
     * transactional email service wired in yet (see
     * docs/CLIENT_PORTAL.md Known Limitations).
     */
    public function createPortalLogin() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/clients');
            return;
        }
        $this->verifyCSRF();

        $clientId = $_POST['client_id'] ?? null;
        $email = trim($_POST['email'] ?? '');

        if (!$clientId || $email === '') {
            $this->redirect('/clients?error=' . urlencode('Client and email are required.'));
            return;
        }

        $clientModel = new Client();
        $client = $clientModel->getById($clientId);
        if (!$client) {
            $this->redirect('/clients');
            return;
        }

        $clientUserModel = new ClientUser();
        if ($clientUserModel->findByEmail($email)) {
            $this->redirect('/clients?error=' . urlencode('That email already has a portal login.'));
            return;
        }

        $tempPassword = bin2hex(random_bytes(6));
        $hash = password_hash($tempPassword, PASSWORD_DEFAULT);
        $clientUserModel->create($clientId, $email, $hash);

        require_once BASE_PATH . '/app/Services/ClientActivity.php';
        ClientActivity::log((int) $clientId, null, 'Portal login created for ' . $email);

        $loginUrl = '/portal/login';
        $this->redirect('/clients?login_created=1&email=' . urlencode($email)
            . '&temp_password=' . urlencode($tempPassword)
            . '&login_url=' . urlencode($loginUrl));
    }

    public function resetPortalPassword() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/clients');
            return;
        }
        $this->verifyCSRF();

        $clientUserId = $_POST['client_user_id'] ?? null;
        if (!$clientUserId) {
            $this->redirect('/clients');
            return;
        }

        $clientUserModel = new ClientUser();
        $clientUser = $clientUserModel->findById($clientUserId);
        if (!$clientUser) {
            $this->redirect('/clients');
            return;
        }

        $tempPassword = bin2hex(random_bytes(6));
        $hash = password_hash($tempPassword, PASSWORD_DEFAULT);
        $clientUserModel->updatePassword($clientUserId, $hash, true);

        require_once BASE_PATH . '/app/Services/ClientActivity.php';
        ClientActivity::log((int) $clientUser['client_id'], null, 'Portal password reset for ' . $clientUser['email']);

        $this->redirect('/clients?login_created=1&email=' . urlencode($clientUser['email'])
            . '&temp_password=' . urlencode($tempPassword)
            . '&login_url=' . urlencode('/portal/login'));
    }

    public function showModules() {
        $clientId = $_GET['id'] ?? null;
        if (!$clientId) {
            $this->redirect('/clients');
            return;
        }

        $clientModel = new Client();
        $client = $clientModel->getById($clientId);
        if (!$client) {
            $this->redirect('/clients');
            return;
        }

        require_once BASE_PATH . '/app/Models/ClientPortalModule.php';
        $moduleModel = new \ClientPortalModule();
        $disabled = $moduleModel->disabledFor($clientId);

        $this->view('layouts/main', [
            'title' => 'Portal Modules - ' . $client['business_name'],
            'content_view' => 'clients/modules',
            'active_menu' => 'clients',
            'breadcrumbs' => [
                ['label' => 'Clients', 'url' => '/clients'],
                ['label' => $client['business_name'], 'url' => '/clients/edit?id=' . $client['id']],
                ['label' => 'Portal Modules'],
            ],
            'client' => $client,
            'disabled' => $disabled,
            'csrf_token' => $this->generateCSRF(),
        ]);
    }

    public function updateModules() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/clients');
            return;
        }
        $this->verifyCSRF();

        $clientId = $_POST['client_id'] ?? null;
        if (!$clientId) {
            $this->redirect('/clients');
            return;
        }

        require_once BASE_PATH . '/app/Models/ClientPortalModule.php';
        $moduleModel = new \ClientPortalModule();
        $enabledSubmitted = (array) ($_POST['enabled'] ?? []);

        foreach (\ClientPortalModule::ALL_MODULES as $moduleCode) {
            if ($moduleCode === 'dashboard') {
                continue; // Executive Summary is never optional.
            }
            $moduleModel->setEnabled($clientId, $moduleCode, in_array($moduleCode, $enabledSubmitted, true));
        }

        $this->redirect('/clients/modules?id=' . $clientId . '&saved=1');
    }

    public function togglePortalAccess() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/clients');
            return;
        }
        $this->verifyCSRF();

        $clientUserId = $_POST['client_user_id'] ?? null;
        $active = ($_POST['active'] ?? '0') === '1';
        if (!$clientUserId) {
            $this->redirect('/clients');
            return;
        }

        $clientUserModel = new ClientUser();
        $clientUser = $clientUserModel->findById($clientUserId);
        if ($clientUser) {
            $clientUserModel->setActive($clientUserId, $active);
            require_once BASE_PATH . '/app/Services/ClientActivity.php';
            ClientActivity::log(
                (int) $clientUser['client_id'],
                null,
                ($active ? 'Enabled' : 'Disabled') . ' portal access for ' . $clientUser['email']
            );
        }

        $this->redirect('/clients');
    }

    public function updatePortalEmail() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/clients');
            return;
        }
        $this->verifyCSRF();

        $clientUserId = $_POST['client_user_id'] ?? null;
        $newEmail = trim($_POST['new_email'] ?? '');
        
        if (!$clientUserId || !$newEmail) {
            $this->redirect('/clients?error=' . urlencode('Email cannot be empty.'));
            return;
        }

        $clientUserModel = new ClientUser();
        
        // Check if email already exists
        $existing = $clientUserModel->findByEmail($newEmail);
        if ($existing && (int)$existing['id'] !== (int)$clientUserId) {
            $this->redirect('/clients?error=' . urlencode('That email is already in use.'));
            return;
        }
        
        $clientUser = $clientUserModel->findById($clientUserId);
        if ($clientUser) {
            $clientUserModel->updateEmail($clientUserId, $newEmail);
            require_once BASE_PATH . '/app/Services/ClientActivity.php';
            ClientActivity::log((int) $clientUser['client_id'], null, 'Portal email updated to ' . $newEmail);
        }

        $this->redirect('/clients');
    }

    public function sendPortalInvite() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }
        $this->verifyCSRF();

        $clientUserId = $_POST['client_user_id'] ?? null;
        if (!$clientUserId) {
            http_response_code(400);
            echo json_encode(['error' => 'No user specified']);
            return;
        }

        $clientUserModel = new ClientUser();
        $clientUser = $clientUserModel->findById($clientUserId);
        if (!$clientUser) {
            http_response_code(404);
            echo json_encode(['error' => 'User not found']);
            return;
        }
        
        $clientModel = new Client();
        $client = $clientModel->getById($clientUser['client_id']);

        $email = $clientUser['email'];
        $tempPassword = bin2hex(random_bytes(6));
        $hash = password_hash($tempPassword, PASSWORD_DEFAULT);
        $clientUserModel->updatePassword($clientUserId, $hash, true);
        
        $portalUrl = 'https://clients.onechancetogrow.com/client/' . $client['slug'] . '/dashboard';

        // Send email via PHP mail()
        $subject = "Your OCTG Marketing Intelligence Portal is Ready";
        
        $message = "
        <html>
        <head>
          <title>Your OCTG Marketing Intelligence Portal</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
          <div style='max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px;'>
            <h2 style='color: #2563eb;'>Welcome to One Chance To Grow</h2>
            <p>Hello,</p>
            <p>Your Marketing Intelligence Portal has been provisioned. You can now access your customized dashboard to review your Google Ads performance, AI-driven insights, and identified opportunities.</p>
            
            <div style='background: #f8fafc; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                <p><strong>Portal URL:</strong> <a href='{$portalUrl}'>{$portalUrl}</a></p>
                <p><strong>Login Email:</strong> {$email}</p>
                <p><strong>Temporary Password:</strong> <code>{$tempPassword}</code></p>
            </div>
            
            <p><em>Note: You will be prompted to set a permanent password upon your first login.</em></p>
            
            <p>Best regards,<br>The OCTG Team</p>
          </div>
        </body>
        </html>
        ";

        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: One Chance To Grow <noreply@clients.onechancetogrow.com>" . "\r\n";

        if (mail($email, $subject, $message, $headers)) {
            require_once BASE_PATH . '/app/Services/ClientActivity.php';
            ClientActivity::log((int) $clientUser['client_id'], null, 'Portal invite email sent to ' . $email);
            
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to send email. Check server configuration.']);
        }
    }
    
    public function create() {
        $this->view('layouts/main', [
            'title' => 'Add Client - OCTG',
            'content_view' => 'clients/form',
            'active_menu' => 'clients',
            'breadcrumbs' => [
                ['label' => 'Clients', 'url' => '/clients'],
                ['label' => 'Add Client']
            ],
            'client' => null
        ]);
    }
    
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Basic validation
            if (empty($_POST['business_name'])) {
                $this->redirect('/clients/create?error=NameRequired');
                return;
            }
            
            $clientModel = new Client();
            $data = [
                'business_name' => $_POST['business_name'] ?? '',
                'website' => $_POST['website'] ?? '',
                'industry' => $_POST['industry'] ?? '',
                'contact_person' => $_POST['contact_person'] ?? '',
                'email' => $_POST['email'] ?? '',
                'phone' => $_POST['phone'] ?? '',
                'status' => $_POST['status'] ?? 'active'
            ];
            $clientModel->create($data);
            $this->redirect('/clients');
        }
    }
    
    public function edit() {
        $id = $_GET['id'] ?? null;
        if (!$id) $this->redirect('/clients');
        
        $clientModel = new Client();
        $client = $clientModel->getById($id);
        if (!$client) $this->redirect('/clients');
        
        $this->view('layouts/main', [
            'title' => 'Edit Client - OCTG',
            'content_view' => 'clients/form',
            'active_menu' => 'clients',
            'breadcrumbs' => [
                ['label' => 'Clients', 'url' => '/clients'],
                ['label' => 'Edit Client']
            ],
            'client' => $client
        ]);
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;
            if (!$id) $this->redirect('/clients');
            
            $clientModel = new Client();
            $data = [
                'business_name' => $_POST['business_name'] ?? '',
                'website' => $_POST['website'] ?? '',
                'industry' => $_POST['industry'] ?? '',
                'contact_person' => $_POST['contact_person'] ?? '',
                'email' => $_POST['email'] ?? '',
                'phone' => $_POST['phone'] ?? '',
                'status' => $_POST['status'] ?? 'active'
            ];
            $clientModel->update($id, $data);
            $this->redirect('/clients');
        }
    }
    
    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;
            if ($id) {
                $clientModel = new Client();
                $clientModel->delete($id);
            }
            $this->redirect('/clients');
        }
    }
}
