<?php

require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Category.php';
require_once __DIR__ . '/../models/StockMovement.php';

class ProductController
{
    private Product  $product;
    private Category $category;

    public function __construct()
    {
        $this->product  = new Product();
        $this->category = new Category();
    }

    public function index(): void
    {
        requireAuth();
        $filters  = [
            'search'      => $_GET['search']    ?? '',
            'category_id' => $_GET['category_id'] ?? '',
            'low_stock'   => $_GET['low_stock']  ?? '',
        ];
        $products   = $this->product->all($filters);
        $categories = $this->category->all();
        require __DIR__ . '/../views/products/index.php';
    }

    public function show(): void
    {
        requireAuth();
        $id      = (int) ($_GET['id'] ?? 0);
        $product = $this->product->find($id);
        if (!$product) { $this->notFound(); return; }

        $movements = (new StockMovement())->forProduct($id);
        require __DIR__ . '/../views/products/show.php';
    }

    public function create(): void
    {
        requireAuth();
        $categories = $this->category->all();
        $errors     = [];
        $old        = [];
        require __DIR__ . '/../views/products/create.php';
    }

    public function store(): void
    {
        requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect('products'); }

        [$data, $errors] = $this->validate($_POST);

        if (!empty($errors)) {
            $categories = $this->category->all();
            $old = $_POST;
            require __DIR__ . '/../views/products/create.php';
            return;
        }

        $id = $this->product->create($data);

        // Log initial stock as IN movement
        if ((int)$data['quantity'] > 0) {
            (new StockMovement())->log($id, 'IN', (int)$data['quantity'], 'Stock initial à la création');
        }

        setFlash('success', 'Produit créé avec succès.');
        redirect('products');
    }

    public function edit(): void
    {
        requireAuth();
        $id      = (int) ($_GET['id'] ?? 0);
        $product = $this->product->find($id);
        if (!$product) { $this->notFound(); return; }

        $categories = $this->category->all();
        $errors     = [];
        $old        = $product;
        require __DIR__ . '/../views/products/edit.php';
    }

    public function update(): void
    {
        requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect('products'); }

        $id      = (int) ($_POST['id'] ?? 0);
        $product = $this->product->find($id);
        if (!$product) { $this->notFound(); return; }

        [$data, $errors] = $this->validate($_POST, $id);

        if (!empty($errors)) {
            $categories = $this->category->all();
            $old = $_POST;
            require __DIR__ . '/../views/products/edit.php';
            return;
        }

        // Track quantity change for stock log
        $oldQty  = (int) $product['quantity'];
        $newQty  = (int) $data['quantity'];
        $delta   = $newQty - $oldQty;

        $this->product->update($id, $data);

        if ($delta !== 0) {
            $type = $delta > 0 ? 'IN' : 'OUT';
            (new StockMovement())->log($id, $type, abs($delta), 'Ajustement manuel (édition produit)');
        }

        setFlash('success', 'Produit mis à jour.');
        redirect('products/show?id=' . $id);
    }

    public function delete(): void
    {
        requireAuth();
        $id = (int) ($_POST['id'] ?? $_GET['id'] ?? 0);
        $this->product->delete($id);
        setFlash('success', 'Produit supprimé.');
        redirect('products');
    }

    // ─── Validation ─────────────────────────────────────────

    private function validate(array $post, int $excludeId = 0): array
    {
        $errors = [];
        $data   = [
            'name'          => trim($post['name']          ?? ''),
            'sku'           => trim($post['sku']           ?? ''),
            'category_id'   => (int) ($post['category_id'] ?? 0),
            'unit_price'    => (float) ($post['unit_price'] ?? 0),
            'cost_price'    => (float) ($post['cost_price'] ?? 0),
            'quantity'      => (int) ($post['quantity']    ?? 0),
            'minimum_stock' => (int) ($post['minimum_stock'] ?? 5),
            'description'   => trim($post['description']   ?? ''),
        ];

        if (empty($data['name']))        $errors[] = 'Le nom est obligatoire.';
        if (empty($data['sku']))         $errors[] = 'La référence (SKU) est obligatoire.';
        if ($data['unit_price'] < 0)     $errors[] = 'Le prix unitaire doit être positif.';
        if ($data['quantity'] < 0)       $errors[] = 'La quantité ne peut pas être négative.';
        if ($data['minimum_stock'] < 0)  $errors[] = 'Le stock minimum ne peut pas être négatif.';

        if (!empty($data['sku']) && $this->product->skuExists($data['sku'], $excludeId)) {
            $errors[] = 'Cette référence (SKU) est déjà utilisée.';
        }

        return [$data, $errors];
    }

    private function notFound(): void
    {
        http_response_code(404);
        require __DIR__ . '/../views/errors/404.php';
    }
}
