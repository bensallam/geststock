<?php

require_once __DIR__ . '/../models/GuaranteeCertificate.php';
require_once __DIR__ . '/../models/Client.php';
require_once __DIR__ . '/../models/Invoice.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Company.php';
require_once __DIR__ . '/../models/CompanySettings.php';

class GuaranteeCertificateController
{
    private GuaranteeCertificate $cert;
    private Client $client;

    public function __construct()
    {
        $this->cert   = new GuaranteeCertificate();
        $this->client = new Client();
    }

    public function index(): void
    {
        requireAuth();
        $filters = [
            'search'    => $_GET['search']    ?? '',
            'client_id' => $_GET['client_id'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to'   => $_GET['date_to']   ?? '',
        ];
        $filters['company_id'] = currentCompanyId();
        $certs   = $this->cert->all($filters);
        $clients = $this->client->forSelect(currentCompanyId());
        require __DIR__ . '/../views/guarantees/index.php';
    }

    public function show(): void
    {
        requireAuth();
        $id   = (int) ($_GET['id'] ?? 0);
        $cert = $this->cert->find($id);
        if (!$cert) { $this->notFound(); return; }
        $items = $this->cert->items($id);
        require __DIR__ . '/../views/guarantees/show.php';
    }

    public function create(): void
    {
        requireAuth();
        $clients       = $this->client->forSelect(currentCompanyId());
        $invoices      = $this->invoicesForSelect();
        $products      = (new Product())->forSelect();
        $companies     = (new Company())->forSelect();
        $errors        = [];
        $old           = [];
        $existingItems = [];
        $nextNum       = nextCertificateNumber();
        $company       = $this->loadCompany();
        $old           = ['company_id' => $company['id'] ?? ''];
        $defaultTerms  = $company['default_warranty_terms'] ?: $this->defaultTerms();
        require __DIR__ . '/../views/guarantees/create.php';
    }

    public function store(): void
    {
        requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect('guarantees'); }

        [$data, $items, $errors] = $this->validateCertificate($_POST);

        if (!empty($errors)) {
            $clients       = $this->client->forSelect(currentCompanyId());
            $invoices      = $this->invoicesForSelect();
            $products      = (new Product())->forSelect();
            $companies     = (new Company())->forSelect();
            $nextNum       = $data['certificate_number'];
            $defaultTerms  = $this->defaultTerms();
            $old           = $_POST;
            $existingItems = [];
            require __DIR__ . '/../views/guarantees/create.php';
            return;
        }

        try {
            $id = $this->cert->create($data, $items);
            setFlash('success', 'Certificat ' . $data['certificate_number'] . ' créé avec succès.');
            redirect('guarantees/show?id=' . $id);
        } catch (Throwable $e) {
            $errors[]      = 'Erreur : ' . $e->getMessage();
            $clients       = $this->client->forSelect(currentCompanyId());
            $invoices      = $this->invoicesForSelect();
            $products      = (new Product())->forSelect();
            $companies     = (new Company())->forSelect();
            $nextNum       = $data['certificate_number'];
            $defaultTerms  = $this->defaultTerms();
            $old           = $_POST;
            $existingItems = [];
            require __DIR__ . '/../views/guarantees/create.php';
        }
    }

    public function edit(): void
    {
        requireAuth();
        $id   = (int) ($_GET['id'] ?? 0);
        $cert = $this->cert->find($id);
        if (!$cert) { $this->notFound(); return; }

        $clients       = $this->client->forSelect(currentCompanyId());
        $invoices      = $this->invoicesForSelect();
        $products      = (new Product())->forSelect();
        $companies     = (new Company())->forSelect();
        $existingItems = $this->cert->items($id);
        $errors        = [];
        $old           = $cert;
        require __DIR__ . '/../views/guarantees/edit.php';
    }

    public function update(): void
    {
        requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect('guarantees'); }

        $id   = (int) ($_POST['id'] ?? 0);
        $cert = $this->cert->find($id);
        if (!$cert) { $this->notFound(); return; }

        [$data, $items, $errors] = $this->validateCertificate($_POST, $id);

        if (!empty($errors)) {
            $clients       = $this->client->forSelect(currentCompanyId());
            $invoices      = $this->invoicesForSelect();
            $products      = (new Product())->forSelect();
            $companies     = (new Company())->forSelect();
            $existingItems = $this->cert->items($id);
            $old           = $_POST;
            require __DIR__ . '/../views/guarantees/edit.php';
            return;
        }

        try {
            $this->cert->update($id, $data, $items);
            setFlash('success', 'Certificat mis à jour.');
            redirect('guarantees/show?id=' . $id);
        } catch (Throwable $e) {
            $errors[]      = 'Erreur : ' . $e->getMessage();
            $clients       = $this->client->forSelect(currentCompanyId());
            $invoices      = $this->invoicesForSelect();
            $products      = (new Product())->forSelect();
            $companies     = (new Company())->forSelect();
            $existingItems = $this->cert->items($id);
            $old           = $_POST;
            require __DIR__ . '/../views/guarantees/edit.php';
        }
    }

    public function delete(): void
    {
        requireAuth();
        $id   = (int) ($_POST['id'] ?? 0);
        $cert = $this->cert->find($id);
        if (!$cert) {
            setFlash('danger', 'Certificat introuvable.');
            redirect('guarantees');
            return;
        }
        $this->cert->delete($id);
        setFlash('success', 'Certificat ' . $cert['certificate_number'] . ' supprimé.');
        redirect('guarantees');
    }

    public function printView(): void
    {
        requireAuth();
        $id      = (int) ($_GET['id'] ?? 0);
        $cert    = $this->cert->find($id);
        if (!$cert) { $this->notFound(); return; }
        $items   = $this->cert->items($id);
        $company = $this->loadDocumentCompany($cert);
        require __DIR__ . '/../views/guarantees/print.php';
    }

    public function liveEdit(): void
    {
        requireAuth();
        $id      = (int) ($_GET['id'] ?? 0);
        $cert    = $this->cert->find($id);
        if (!$cert) { $this->notFound(); return; }
        $items   = $this->cert->items($id);
        $company = $this->loadDocumentCompany($cert);
        require __DIR__ . '/../views/guarantees/live_edit.php';
    }

    public function liveUpdate(): void
    {
        requireAuth();
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || empty($input['id'])) {
            echo json_encode(['ok' => false, 'error' => 'Invalid request']); return;
        }
        $id   = (int) $input['id'];
        $cert = $this->cert->find($id);
        if (!$cert) { echo json_encode(['ok' => false, 'error' => 'Certificat introuvable']); return; }

        $data = [
            'certificate_number' => trim($input['certificate_number'] ?? $cert['certificate_number']),
            'client_id'          => $cert['client_id'],
            'invoice_id'         => $cert['invoice_id'],
            'customer_name'      => trim($input['customer_name']  ?? $cert['customer_name']  ?? ''),
            'client_address'     => trim($input['client_address'] ?? $cert['client_address'] ?? ''),
            'reference'          => trim($input['reference']      ?? $cert['reference']      ?? ''),
            'product_details'    => trim($input['product_details'] ?? $cert['product_details'] ?? ''),
            'start_date'         => trim($input['start_date']     ?? $cert['start_date']     ?? ''),
            'end_date'           => trim($input['end_date']       ?? $cert['end_date']       ?? ''),
            'delivery_date'      => trim($input['delivery_date']  ?? $cert['delivery_date']  ?? ''),
            'terms'              => trim($input['terms']          ?? $cert['terms']          ?? ''),
            'notes'              => trim($input['notes']          ?? $cert['notes']          ?? ''),
            'payment_method'     => $cert['payment_method'] ?? '',
            'company_id'         => $cert['company_id'],
            'use_watermark'      => $cert['use_watermark'],
        ];

        $rawItems = [];
        foreach (($input['items'] ?? []) as $item) {
            $label = trim($item['label'] ?? '');
            $qty   = (float) ($item['quantity']  ?? 0);
            $price = (float) ($item['unit_price'] ?? 0);
            if ($label === '' || $qty <= 0) continue;
            $rawItems[] = [
                'product_id' => !empty($item['product_id']) ? (int) $item['product_id'] : null,
                'label'      => $label,
                'quantity'   => $qty,
                'unit_price' => $price,
            ];
        }

        try {
            $this->cert->update($id, $data, $rawItems);
            echo json_encode(['ok' => true]);
        } catch (Throwable $e) {
            echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
        }
    }

    public function pdf(): void
    {
        requireAuth();
        $id      = (int) ($_GET['id'] ?? 0);
        $cert    = $this->cert->find($id);
        if (!$cert) { $this->notFound(); return; }
        $items   = $this->cert->items($id);
        $company = $this->loadDocumentCompany($cert);

        $dompdfPath = __DIR__ . '/../vendor/dompdf/dompdf/src/Dompdf.php';
        if (file_exists($dompdfPath)) {
            require_once __DIR__ . '/../vendor/autoload.php';

            $options = new \Dompdf\Options();
            $options->set('defaultFont', 'DejaVu Sans');
            $options->set('isRemoteEnabled', false);
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isFontSubsettingEnabled', true);
            $options->set('chroot', __DIR__ . '/../');

            ob_start();
            require __DIR__ . '/../views/guarantees/print.php';
            $html = ob_get_clean();

            $dompdf = new \Dompdf\Dompdf($options);
            $dompdf->loadHtml($html, 'UTF-8');
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            $dompdf->stream(
                'garantie-' . $cert['certificate_number'] . '.pdf',
                ['Attachment' => true]
            );
            exit;
        }

        redirect('guarantees/print?id=' . $id);
    }

    // ─── Validation ─────────────────────────────────────────

    private function validateCertificate(array $post, int $excludeId = 0): array
    {
        $errors = [];

        $validMethods = ['cheque', 'espece', 'virement'];
        $pm = $post['payment_method'] ?? '';

        $data = [
            'company_id'         => !empty($post['company_id'])    ? (int) $post['company_id'] : null,
            'use_watermark'      => !empty($post['use_watermark'])  ? 1 : 0,
            'certificate_number' => trim($post['certificate_number'] ?? ''),
            'client_id'          => !empty($post['client_id'])  ? (int) $post['client_id']  : null,
            'invoice_id'         => !empty($post['invoice_id']) ? (int) $post['invoice_id'] : null,
            'reference'          => trim($post['reference']      ?? ''),
            'customer_name'      => trim($post['customer_name']  ?? ''),
            'product_details'    => trim($post['product_details'] ?? ''),
            'start_date'         => trim($post['start_date']     ?? ''),
            'end_date'           => trim($post['end_date']       ?? ''),
            'delivery_date'      => trim($post['delivery_date']  ?? '') ?: null,
            'terms'              => trim($post['terms']          ?? ''),
            'notes'              => trim($post['notes']          ?? ''),
            'payment_method'     => in_array($pm, $validMethods, true) ? $pm : '',
        ];

        if (empty($data['certificate_number'])) {
            $errors[] = 'Le numéro de certificat est obligatoire.';
        } elseif ($this->cert->numberExists($data['certificate_number'], $excludeId)) {
            $errors[] = 'Ce numéro de certificat existe déjà.';
        }

        if (empty($data['customer_name'])) $errors[] = 'Le nom du client est obligatoire.';
        if (empty($data['start_date']))    $errors[] = 'La date de début de garantie est obligatoire.';
        if (empty($data['end_date']))      $errors[] = 'La date de fin de garantie est obligatoire.';

        if (!empty($data['start_date']) && !empty($data['end_date'])
            && $data['end_date'] <= $data['start_date']) {
            $errors[] = 'La date de fin doit être postérieure à la date de début.';
        }

        // Parse line items
        $rawItems = [];
        if (!empty($post['items']) && is_array($post['items'])) {
            foreach ($post['items'] as $item) {
                $label = trim($item['label'] ?? '');
                if ($label === '') continue;
                $qty   = max(0.01, (float) ($item['quantity'] ?? 1));
                $price = (isset($item['unit_price']) && $item['unit_price'] !== '')
                            ? (float) $item['unit_price'] : null;
                $rawItems[] = [
                    'label'      => $label,
                    'quantity'   => $qty,
                    'unit_price' => $price,
                ];
            }
        }

        if (empty($rawItems) && empty($data['product_details'])) {
            $errors[] = 'Ajoutez au moins un article ou une description du produit couvert.';
        }

        return [$data, $rawItems, $errors];
    }

    // ─── Helpers ────────────────────────────────────────────

    private function loadDocumentCompany(array $doc): array
    {
        if (!empty($doc['company_id'])) {
            $cm      = new Company();
            $company = $cm->find((int) $doc['company_id']);
            if ($company) {
                $company['logo_data_uri']      = $cm->logoDataUri((int) $doc['company_id']);
                $company['watermark_data_uri'] = $cm->watermarkDataUri((int) $doc['company_id']);
                return $company;
            }
        }
        return $this->loadCompany();
    }

    private function loadCompany(): array
    {
        $cm     = new Company();
        $active = $cm->getActive();
        if ($active) {
            $active['logo_data_uri']      = $cm->logoDataUri((int) $active['id']);
            $active['watermark_data_uri'] = $cm->watermarkDataUri((int) $active['id']);
            return $active;
        }
        $cs      = new CompanySettings();
        $company = $cs->get();
        $company['logo_data_uri']      = $cs->logoDataUri();
        $company['watermark_data_uri'] = null;
        return $company;
    }

    private function invoicesForSelect(): array
    {
        $stmt = db()->query(
            "SELECT i.id, i.invoice_number, c.name AS client_name
             FROM invoices i
             JOIN clients c ON c.id = i.client_id
             ORDER BY i.date DESC, i.id DESC LIMIT 200"
        );
        return $stmt->fetchAll();
    }

    private function defaultTerms(): string
    {
        return implode("\n\n", [
            "1. Cette garantie couvre les défauts de fabrication et les pannes techniques survenant dans des conditions normales d'utilisation.",
            "2. La garantie ne couvre pas les dommages résultant d'un mauvais usage, d'accidents, de modifications non autorisées, ou de catastrophes naturelles.",
            "3. En cas de panne couverte, veuillez contacter notre service client en présentant ce certificat et la preuve d'achat.",
            "4. Cette garantie est strictement personnelle et ne peut être cédée ou transférée à un tiers.",
            "5. Toute réparation effectuée par un technicien non agréé par notre société annule de plein droit cette garantie.",
        ]);
    }

    private function notFound(): void
    {
        http_response_code(404);
        require __DIR__ . '/../views/errors/404.php';
    }
}
