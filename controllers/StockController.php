<?php

require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/StockMovement.php';

class StockController
{
    private Product       $product;
    private StockMovement $movement;

    public function __construct()
    {
        $this->product  = new Product();
        $this->movement = new StockMovement();
    }

    /**
     * Stock overview list.
     */
    public function index(): void
    {
        requireAuth();
        $filters  = [
            'search'    => $_GET['search']    ?? '',
            'low_stock' => $_GET['low_stock'] ?? '',
        ];
        $products = $this->product->all($filters);
        require __DIR__ . '/../views/stock/index.php';
    }

    /**
     * Show the manual adjustment form.
     */
    public function adjust(): void
    {
        requireAuth();
        $id      = (int) ($_GET['id'] ?? 0);
        $product = $this->product->find($id);
        if (!$product) {
            setFlash('danger', 'Produit introuvable.');
            redirect('stock');
            return;
        }
        $errors = [];
        require __DIR__ . '/../views/stock/adjust.php';
    }

    /**
     * Process manual stock adjustment.
     */
    public function store(): void
    {
        requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect('stock'); }

        $id      = (int) ($_POST['product_id'] ?? 0);
        $type    = $_POST['type']     ?? '';
        $qty     = (int) ($_POST['quantity'] ?? 0);
        $note    = trim($_POST['note'] ?? '');
        $product = $this->product->find($id);

        $errors = [];

        if (!$product) {
            $errors[] = 'Produit introuvable.';
        }

        if (!in_array($type, ['IN', 'OUT'], true)) {
            $errors[] = 'Type de mouvement invalide.';
        }

        if ($qty <= 0) {
            $errors[] = 'La quantité doit être supérieure à 0.';
        }

        if (empty($errors) && $type === 'OUT') {
            if ($qty > (int) $product['quantity']) {
                $errors[] = 'Quantité insuffisante en stock (' . $product['quantity'] . ' disponible).';
            }
        }

        if (!empty($errors)) {
            require __DIR__ . '/../views/stock/adjust.php';
            return;
        }

        // Update quantity
        $delta = $type === 'IN' ? $qty : -$qty;
        $this->product->adjustQuantity($id, $delta);

        // Log movement
        $this->movement->log($id, $type, $qty, $note ?: 'Ajustement manuel');

        setFlash('success', 'Stock mis à jour avec succès.');
        redirect('products/show?id=' . $id);
    }

    /**
     * Movement history (all or per-product).
     */
    public function movements(): void
    {
        requireAuth();
        $filters = [
            'product_id' => (int) ($_GET['product_id'] ?? 0),
            'type'       => $_GET['type'] ?? '',
        ];
        $movements = $this->movement->all($filters);
        $products  = $this->product->forSelect();
        require __DIR__ . '/../views/stock/movements.php';
    }
}
