<?php

require_once __DIR__ . '/../models/Invoice.php';
require_once __DIR__ . '/../models/Client.php';
require_once __DIR__ . '/../models/Product.php';

class InvoiceController
{
    private Invoice $invoice;
    private Client  $client;
    private Product $product;

    public function __construct()
    {
        $this->invoice = new Invoice();
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
        $invoices = $this->invoice->all($filters);
        $clients  = $this->client->forSelect();
        require __DIR__ . '/../views/invoices/index.php';
    }

    public function show(): void
    {
        requireAuth();
        $id      = (int) ($_GET['id'] ?? 0);
        $invoice = $this->invoice->find($id);
        if (!$invoice) { $this->notFound(); return; }
        $items = $this->invoice->items($id);
        require __DIR__ . '/../views/invoices/show.php';
    }

    public function create(): void
    {
        requireAuth();
        $clients  = $this->client->forSelect();
        $products = $this->product->forSelect();
        $errors   = [];
        $old      = [];
        $nextNum  = nextInvoiceNumber();
        require __DIR__ . '/../views/invoices/create.php';
    }

    public function store(): void
    {
        requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect('invoices'); }

        [$data, $items, $errors] = $this->validateInvoice($_POST);

        if (!empty($errors)) {
            $clients  = $this->client->forSelect();
            $products = $this->product->forSelect();
            $nextNum  = $data['invoice_number'];
            $old      = $_POST;
            require __DIR__ . '/../views/invoices/create.php';
            return;
        }

        try {
            $id = $this->invoice->create($data, $items);
            setFlash('success', 'Facture ' . $data['invoice_number'] . ' créée avec succès.');
            redirect('invoices/show?id=' . $id);
        } catch (Throwable $e) {
            $errors[] = 'Erreur lors de la création : ' . $e->getMessage();
            $clients  = $this->client->forSelect();
            $products = $this->product->forSelect();
            $nextNum  = $data['invoice_number'];
            $old      = $_POST;
            require __DIR__ . '/../views/invoices/create.php';
        }
    }

    public function edit(): void
    {
        requireAuth();
        $id      = (int) ($_GET['id'] ?? 0);
        $invoice = $this->invoice->find($id);
        if (!$invoice) { $this->notFound(); return; }

        $existingItems = $this->invoice->items($id);
        $clients       = $this->client->forSelect();
        $products      = $this->product->forSelect();
        $errors        = [];
        $old           = $invoice;
        require __DIR__ . '/../views/invoices/edit.php';
    }

    public function update(): void
    {
        requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect('invoices'); }

        $id      = (int) ($_POST['id'] ?? 0);
        $invoice = $this->invoice->find($id);
        if (!$invoice) { $this->notFound(); return; }

        [$data, $items, $errors] = $this->validateInvoice($_POST, $id);

        if (!empty($errors)) {
            $existingItems = $this->invoice->items($id);
            $clients  = $this->client->forSelect();
            $products = $this->product->forSelect();
            $old      = $_POST;
            require __DIR__ . '/../views/invoices/edit.php';
            return;
        }

        try {
            $this->invoice->update($id, $data, $items);
            setFlash('success', 'Facture mise à jour.');
            redirect('invoices/show?id=' . $id);
        } catch (Throwable $e) {
            $errors[] = 'Erreur lors de la mise à jour : ' . $e->getMessage();
            $existingItems = $this->invoice->items($id);
            $clients  = $this->client->forSelect();
            $products = $this->product->forSelect();
            $old      = $_POST;
            require __DIR__ . '/../views/invoices/edit.php';
        }
    }

    public function delete(): void
    {
        requireAuth();
        $id = (int) ($_POST['id'] ?? 0);

        $invoice = $this->invoice->find($id);
        if (!$invoice) {
            setFlash('danger', 'Facture introuvable.');
            redirect('invoices');
            return;
        }

        $this->invoice->delete($id);
        setFlash('success', 'Facture ' . $invoice['invoice_number'] . ' supprimée. Stock restauré.');
        redirect('invoices');
    }

    public function printView(): void
    {
        requireAuth();
        $id      = (int) ($_GET['id'] ?? 0);
        $invoice = $this->invoice->find($id);
        if (!$invoice) { $this->notFound(); return; }
        $items = $this->invoice->items($id);
        require __DIR__ . '/../views/invoices/print.php';
    }

    public function pdf(): void
    {
        requireAuth();
        $id      = (int) ($_GET['id'] ?? 0);
        $invoice = $this->invoice->find($id);
        if (!$invoice) { $this->notFound(); return; }
        $items = $this->invoice->items($id);

        // Try Dompdf if installed, else fall back to print page
        $dompdfPath = __DIR__ . '/../vendor/dompdf/dompdf/src/Dompdf.php';
        if (file_exists($dompdfPath)) {
            require_once __DIR__ . '/../vendor/autoload.php';

            // Configure Dompdf
            $options = new \Dompdf\Options();
            $options->set('defaultFont', 'DejaVu Sans');
            $options->set('isRemoteEnabled', false);    // no external HTTP
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isFontSubsettingEnabled', true);
            $options->set('chroot', __DIR__ . '/../');

            ob_start();
            require __DIR__ . '/../views/invoices/print.php';
            $html = ob_get_clean();

            $dompdf = new \Dompdf\Dompdf($options);
            $dompdf->loadHtml($html, 'UTF-8');
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            $dompdf->stream(
                'facture-' . $invoice['invoice_number'] . '.pdf',
                ['Attachment' => true]
            );
            exit;
        } else {
            // Fallback: redirect to print page
            redirect('invoices/print?id=' . $id);
        }
    }

    // ─── Validation ─────────────────────────────────────────

    private function validateInvoice(array $post, int $excludeId = 0): array
    {
        $errors = [];

        $data = [
            'invoice_number' => trim($post['invoice_number'] ?? ''),
            'client_id'      => (int) ($post['client_id'] ?? 0),
            'date'           => trim($post['date'] ?? ''),
            'tax_rate'       => (float) ($post['tax_rate'] ?? TAX_RATE),
            'notes'          => trim($post['notes'] ?? ''),
            'status'         => $post['status'] ?? 'draft',
        ];

        if (empty($data['invoice_number'])) $errors[] = 'Le numéro de facture est obligatoire.';
        if (empty($data['client_id']))      $errors[] = 'Veuillez sélectionner un client.';
        if (empty($data['date']))           $errors[] = 'La date est obligatoire.';

        // Validate status
        $validStatuses = ['draft', 'sent', 'paid', 'cancelled'];
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
                    'product_id' => !empty($item['product_id']) ? (int)$item['product_id'] : null,
                    'label'      => $label,
                    'quantity'   => $qty,
                    'unit_price' => $price,
                ];
            }
        }

        if (empty($rawItems)) {
            $errors[] = 'La facture doit contenir au moins une ligne.';
        }

        // Compute totals
        $totalHt = 0.0;
        foreach ($rawItems as $item) {
            $totalHt += $item['quantity'] * $item['unit_price'];
        }
        $totalHt    = round($totalHt, 2);
        $taxAmount  = round($totalHt * $data['tax_rate'] / 100, 2);
        $totalTtc   = round($totalHt + $taxAmount, 2);

        $data['total_ht']   = $totalHt;
        $data['tax_amount'] = $taxAmount;
        $data['total_ttc']  = $totalTtc;

        return [$data, $rawItems, $errors];
    }

    private function notFound(): void
    {
        http_response_code(404);
        require __DIR__ . '/../views/errors/404.php';
    }
}
