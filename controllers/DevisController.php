<?php

require_once __DIR__ . '/../models/Devis.php';
require_once __DIR__ . '/../models/Client.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Company.php';
require_once __DIR__ . '/../models/CompanySettings.php';

class DevisController
{
    private Devis   $devis;
    private Client  $client;
    private Product $product;

    public function __construct()
    {
        $this->devis   = new Devis();
        $this->client  = new Client();
        $this->product = new Product();
    }

    public function index(): void
    {
        requireAuth();
        $filters = [
            'search'    => $_GET['search']    ?? '',
            'client_id' => $_GET['client_id'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to'   => $_GET['date_to']   ?? '',
            'status'    => $_GET['status']    ?? '',
        ];
        $devisList = $this->devis->all($filters);
        $clients   = $this->client->forSelect();
        require __DIR__ . '/../views/devis/index.php';
    }

    public function show(): void
    {
        requireAuth();
        $id    = (int) ($_GET['id'] ?? 0);
        $devis = $this->devis->find($id);
        if (!$devis) { $this->notFound(); return; }
        $items = $this->devis->items($id);
        require __DIR__ . '/../views/devis/show.php';
    }

    public function create(): void
    {
        requireAuth();
        $clients   = $this->client->forSelect();
        $products  = $this->product->forSelect();
        $companies = (new Company())->forSelect();
        $errors    = [];
        $company   = $this->loadCompany();
        $old       = ['company_id' => $company['id'] ?? ''];
        $nextNum   = nextDevisNumber();
        require __DIR__ . '/../views/devis/create.php';
    }

    public function store(): void
    {
        requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect('devis'); }

        [$data, $items, $errors] = $this->validateDevis($_POST);

        if (!empty($errors)) {
            $clients   = $this->client->forSelect();
            $products  = $this->product->forSelect();
            $companies = (new Company())->forSelect();
            $nextNum   = $data['devis_number'];
            $old       = $_POST;
            require __DIR__ . '/../views/devis/create.php';
            return;
        }

        try {
            $id = $this->devis->create($data, $items);
            setFlash('success', 'Devis ' . $data['devis_number'] . ' créé avec succès.');
            redirect('devis/show?id=' . $id);
        } catch (Throwable $e) {
            $errors[]  = 'Erreur lors de la création : ' . $e->getMessage();
            $clients   = $this->client->forSelect();
            $products  = $this->product->forSelect();
            $companies = (new Company())->forSelect();
            $nextNum   = $data['devis_number'];
            $old       = $_POST;
            require __DIR__ . '/../views/devis/create.php';
        }
    }

    public function edit(): void
    {
        requireAuth();
        $id    = (int) ($_GET['id'] ?? 0);
        $devis = $this->devis->find($id);
        if (!$devis) { $this->notFound(); return; }

        $existingItems = $this->devis->items($id);
        $clients       = $this->client->forSelect();
        $products      = $this->product->forSelect();
        $companies     = (new Company())->forSelect();
        $errors        = [];
        $old           = $devis;
        require __DIR__ . '/../views/devis/edit.php';
    }

    public function update(): void
    {
        requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect('devis'); }

        $id    = (int) ($_POST['id'] ?? 0);
        $devis = $this->devis->find($id);
        if (!$devis) { $this->notFound(); return; }

        [$data, $items, $errors] = $this->validateDevis($_POST, $id);

        if (!empty($errors)) {
            $existingItems = $this->devis->items($id);
            $clients       = $this->client->forSelect();
            $products      = $this->product->forSelect();
            $companies     = (new Company())->forSelect();
            $old           = $_POST;
            require __DIR__ . '/../views/devis/edit.php';
            return;
        }

        try {
            $this->devis->update($id, $data, $items);
            setFlash('success', 'Devis mis à jour.');
            redirect('devis/show?id=' . $id);
        } catch (Throwable $e) {
            $errors[]      = 'Erreur lors de la mise à jour : ' . $e->getMessage();
            $existingItems = $this->devis->items($id);
            $clients       = $this->client->forSelect();
            $products      = $this->product->forSelect();
            $companies     = (new Company())->forSelect();
            $old           = $_POST;
            require __DIR__ . '/../views/devis/edit.php';
        }
    }

    public function delete(): void
    {
        requireAuth();
        $id    = (int) ($_POST['id'] ?? 0);
        $devis = $this->devis->find($id);
        if (!$devis) {
            setFlash('danger', 'Devis introuvable.');
            redirect('devis');
            return;
        }
        $this->devis->delete($id);
        setFlash('success', 'Devis ' . $devis['devis_number'] . ' supprimé.');
        redirect('devis');
    }

    public function printView(): void
    {
        requireAuth();
        $id    = (int) ($_GET['id'] ?? 0);
        $devis = $this->devis->find($id);
        if (!$devis) { $this->notFound(); return; }
        $items   = $this->devis->items($id);
        $company = $this->loadDocumentCompany($devis);
        require __DIR__ . '/../views/devis/print.php';
    }

    public function liveEdit(): void
    {
        requireAuth();
        $id    = (int) ($_GET['id'] ?? 0);
        $devis = $this->devis->find($id);
        if (!$devis) { $this->notFound(); return; }
        $items   = $this->devis->items($id);
        $company = $this->loadDocumentCompany($devis);
        require __DIR__ . '/../views/devis/live_edit.php';
    }

    public function liveUpdate(): void
    {
        requireAuth();
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || empty($input['id'])) {
            echo json_encode(['ok' => false, 'error' => 'Invalid request']); return;
        }
        $id    = (int) $input['id'];
        $devis = $this->devis->find($id);
        if (!$devis) { echo json_encode(['ok' => false, 'error' => 'Devis introuvable']); return; }

        $validStatuses = ['draft', 'sent', 'accepted', 'rejected'];
        $status = $input['status'] ?? $devis['status'];

        $data = [
            'devis_number'   => trim($input['devis_number']  ?? $devis['devis_number']),
            'client_id'      => $devis['client_id'],
            'date'           => trim($input['date']          ?? $devis['date']),
            'validity_date'  => trim($input['validity_date'] ?? $devis['validity_date'] ?? ''),
            'tax_rate'       => isset($input['tax_rate']) ? (float) $input['tax_rate'] : (float) $devis['tax_rate'],
            'notes'          => trim($input['notes']         ?? $devis['notes'] ?? ''),
            'status'         => in_array($status, $validStatuses, true) ? $status : $devis['status'],
            'payment_method' => $devis['payment_method'] ?? '',
            'company_id'     => $devis['company_id'],
            'use_watermark'  => $devis['use_watermark'],
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

        $totalHt   = round(array_sum(array_map(fn($i) => $i['quantity'] * $i['unit_price'], $rawItems)), 2);
        $taxAmount = round($totalHt * $data['tax_rate'] / 100, 2);
        $totalTtc  = round($totalHt + $taxAmount, 2);
        $data['total_ht']   = $totalHt;
        $data['tax_amount'] = $taxAmount;
        $data['total_ttc']  = $totalTtc;

        try {
            $this->devis->update($id, $data, $rawItems);
            echo json_encode(['ok' => true, 'amount_words' => amountInWords($totalTtc)]);
        } catch (Throwable $e) {
            echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
        }
    }

    public function pdf(): void
    {
        requireAuth();
        $id    = (int) ($_GET['id'] ?? 0);
        $devis = $this->devis->find($id);
        if (!$devis) { $this->notFound(); return; }
        $items   = $this->devis->items($id);
        $company = $this->loadDocumentCompany($devis);

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
            require __DIR__ . '/../views/devis/print.php';
            $html = ob_get_clean();

            $dompdf = new \Dompdf\Dompdf($options);
            $dompdf->loadHtml($html, 'UTF-8');
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            $dompdf->stream(
                'devis-' . $devis['devis_number'] . '.pdf',
                ['Attachment' => true]
            );
            exit;
        } else {
            redirect('devis/print?id=' . $id);
        }
    }

    // ─── Validation ─────────────────────────────────────────

    private function validateDevis(array $post, int $excludeId = 0): array
    {
        $errors = [];

        $validMethods = ['cheque', 'espece', 'virement'];
        $pm = $post['payment_method'] ?? '';

        $data = [
            'devis_number'   => trim($post['devis_number']   ?? ''),
            'client_id'      => (int) ($post['client_id']    ?? 0),
            'date'           => trim($post['date']            ?? ''),
            'validity_date'  => trim($post['validity_date']   ?? ''),
            'tax_rate'       => (float) ($post['tax_rate']    ?? TAX_RATE),
            'notes'          => trim($post['notes']           ?? ''),
            'status'         => $post['status']               ?? 'draft',
            'payment_method' => in_array($pm, $validMethods, true) ? $pm : '',
            'company_id'     => !empty($post['company_id'])   ? (int) $post['company_id'] : null,
            'use_watermark'  => !empty($post['use_watermark']) ? 1 : 0,
        ];

        if (empty($data['devis_number'])) $errors[] = 'Le numéro de devis est obligatoire.';
        if (empty($data['client_id']))    $errors[] = 'Veuillez sélectionner un client.';
        if (empty($data['date']))         $errors[] = 'La date est obligatoire.';

        $validStatuses = ['draft', 'sent', 'accepted', 'rejected'];
        if (!in_array($data['status'], $validStatuses, true)) {
            $data['status'] = 'draft';
        }

        // Parse items
        $rawItems = [];
        if (!empty($post['items']) && is_array($post['items'])) {
            foreach ($post['items'] as $item) {
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
        }

        if (empty($rawItems)) {
            $errors[] = 'Le devis doit contenir au moins une ligne.';
        }

        // Compute totals
        $totalHt = 0.0;
        foreach ($rawItems as $item) {
            $totalHt += $item['quantity'] * $item['unit_price'];
        }
        $totalHt   = round($totalHt, 2);
        $taxAmount = round($totalHt * $data['tax_rate'] / 100, 2);
        $totalTtc  = round($totalHt + $taxAmount, 2);

        $data['total_ht']   = $totalHt;
        $data['tax_amount'] = $taxAmount;
        $data['total_ttc']  = $totalTtc;

        return [$data, $rawItems, $errors];
    }

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

    private function notFound(): void
    {
        http_response_code(404);
        require __DIR__ . '/../views/errors/404.php';
    }
}
