<?php

require_once __DIR__ . '/../models/DeliveryNote.php';
require_once __DIR__ . '/../models/Client.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Company.php';
require_once __DIR__ . '/../models/CompanySettings.php';

class DeliveryNoteController
{
    private DeliveryNote $note;
    private Client $client;

    public function __construct()
    {
        $this->note   = new DeliveryNote();
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
        $notes   = $this->note->all($filters);
        $clients = $this->client->forSelect();
        require __DIR__ . '/../views/delivery_notes/index.php';
    }

    public function show(): void
    {
        requireAuth();
        $id   = (int) ($_GET['id'] ?? 0);
        $note = $this->note->find($id);
        if (!$note) { $this->notFound(); return; }
        $items = $this->note->items($id);
        require __DIR__ . '/../views/delivery_notes/show.php';
    }

    public function create(): void
    {
        requireAuth();
        $clients       = $this->client->forSelect();
        $products      = (new Product())->forSelect();
        $companies     = (new Company())->forSelect();
        $errors        = [];
        $company       = $this->loadCompany();
        $old           = ['company_id' => $company['id'] ?? ''];
        $existingItems = [];
        $nextNum       = nextDeliveryNoteNumber();
        require __DIR__ . '/../views/delivery_notes/create.php';
    }

    public function store(): void
    {
        requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect('delivery-notes'); }

        [$data, $items, $errors] = $this->validate($_POST);

        if (!empty($errors)) {
            $clients       = $this->client->forSelect();
            $products      = (new Product())->forSelect();
            $companies     = (new Company())->forSelect();
            $nextNum       = $data['note_number'];
            $old           = $_POST;
            $existingItems = [];
            require __DIR__ . '/../views/delivery_notes/create.php';
            return;
        }

        try {
            $id = $this->note->create($data, $items);
            setFlash('success', 'Bon de livraison ' . $data['note_number'] . ' créé.');
            redirect('delivery-notes/show?id=' . $id);
        } catch (Throwable $e) {
            $errors[]      = 'Erreur : ' . $e->getMessage();
            $clients       = $this->client->forSelect();
            $products      = (new Product())->forSelect();
            $companies     = (new Company())->forSelect();
            $nextNum       = $data['note_number'];
            $old           = $_POST;
            $existingItems = [];
            require __DIR__ . '/../views/delivery_notes/create.php';
        }
    }

    public function edit(): void
    {
        requireAuth();
        $id   = (int) ($_GET['id'] ?? 0);
        $note = $this->note->find($id);
        if (!$note) { $this->notFound(); return; }

        $clients       = $this->client->forSelect();
        $products      = (new Product())->forSelect();
        $companies     = (new Company())->forSelect();
        $existingItems = $this->note->items($id);
        $errors        = [];
        $old           = $note;
        require __DIR__ . '/../views/delivery_notes/edit.php';
    }

    public function update(): void
    {
        requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect('delivery-notes'); }

        $id   = (int) ($_POST['id'] ?? 0);
        $note = $this->note->find($id);
        if (!$note) { $this->notFound(); return; }

        [$data, $items, $errors] = $this->validate($_POST, $id);

        if (!empty($errors)) {
            $clients       = $this->client->forSelect();
            $products      = (new Product())->forSelect();
            $companies     = (new Company())->forSelect();
            $existingItems = $this->note->items($id);
            $old           = $_POST;
            require __DIR__ . '/../views/delivery_notes/edit.php';
            return;
        }

        try {
            $this->note->update($id, $data, $items);
            setFlash('success', 'Bon de livraison mis à jour.');
            redirect('delivery-notes/show?id=' . $id);
        } catch (Throwable $e) {
            $errors[]      = 'Erreur : ' . $e->getMessage();
            $clients       = $this->client->forSelect();
            $products      = (new Product())->forSelect();
            $companies     = (new Company())->forSelect();
            $existingItems = $this->note->items($id);
            $old           = $_POST;
            require __DIR__ . '/../views/delivery_notes/edit.php';
        }
    }

    public function delete(): void
    {
        requireAuth();
        $id   = (int) ($_POST['id'] ?? 0);
        $note = $this->note->find($id);
        if (!$note) {
            setFlash('danger', 'Bon de livraison introuvable.');
            redirect('delivery-notes');
            return;
        }
        $this->note->delete($id);
        setFlash('success', 'Bon ' . $note['note_number'] . ' supprimé.');
        redirect('delivery-notes');
    }

    public function printView(): void
    {
        requireAuth();
        $id      = (int) ($_GET['id'] ?? 0);
        $note    = $this->note->find($id);
        if (!$note) { $this->notFound(); return; }
        $items   = $this->note->items($id);
        $company = $this->loadDocumentCompany($note);
        require __DIR__ . '/../views/delivery_notes/print.php';
    }

    public function pdf(): void
    {
        requireAuth();
        $id      = (int) ($_GET['id'] ?? 0);
        $note    = $this->note->find($id);
        if (!$note) { $this->notFound(); return; }
        $items   = $this->note->items($id);
        $company = $this->loadDocumentCompany($note);

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
            require __DIR__ . '/../views/delivery_notes/print.php';
            $html = ob_get_clean();

            $dompdf = new \Dompdf\Dompdf($options);
            $dompdf->loadHtml($html, 'UTF-8');
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            $dompdf->stream('bl-' . $note['note_number'] . '.pdf', ['Attachment' => true]);
            exit;
        }

        redirect('delivery-notes/print?id=' . $id);
    }

    // ─── Validation ─────────────────────────────────────────

    private function validate(array $post, int $excludeId = 0): array
    {
        $errors = [];

        $data = [
            'note_number'    => trim($post['note_number']    ?? ''),
            'client_id'      => !empty($post['client_id'])   ? (int) $post['client_id'] : null,
            'customer_name'  => trim($post['customer_name']  ?? ''),
            'delivery_date'  => trim($post['delivery_date']  ?? ''),
            'reference'      => trim($post['reference']      ?? ''),
            'show_prices'    => !empty($post['show_prices'])  ? 1 : 0,
            'payment_method' => $post['payment_method']      ?? '',
            'notes'          => trim($post['notes']          ?? ''),
            'company_id'     => !empty($post['company_id'])  ? (int) $post['company_id'] : null,
            'use_watermark'  => !empty($post['use_watermark']) ? 1 : 0,
        ];

        if (empty($data['note_number'])) {
            $errors[] = 'Le numéro de bon de livraison est obligatoire.';
        } elseif ($this->note->numberExists($data['note_number'], $excludeId)) {
            $errors[] = 'Ce numéro de bon existe déjà.';
        }

        if (empty($data['customer_name'])) $errors[] = 'Le nom du client est obligatoire.';
        if (empty($data['delivery_date'])) $errors[] = 'La date de livraison est obligatoire.';

        // Parse items
        $rawItems = [];
        if (!empty($post['items']) && is_array($post['items'])) {
            foreach ($post['items'] as $item) {
                $label = trim($item['label'] ?? '');
                if ($label === '') continue;
                $qty   = max(0.01, (float) ($item['quantity']   ?? 1));
                $price = (isset($item['unit_price']) && $item['unit_price'] !== '')
                            ? (float) $item['unit_price'] : null;
                $rawItems[] = ['label' => $label, 'quantity' => $qty, 'unit_price' => $price];
            }
        }

        if (empty($rawItems)) {
            $errors[] = 'Le bon doit contenir au moins un article.';
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

    private function notFound(): void
    {
        http_response_code(404);
        require __DIR__ . '/../views/errors/404.php';
    }
}
