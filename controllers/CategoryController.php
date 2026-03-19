<?php

require_once __DIR__ . '/../models/Category.php';

class CategoryController
{
    public function store(): void
    {
        requireAuth();
        header('Content-Type: application/json');

        $name = trim($_POST['name'] ?? '');
        if (empty($name)) {
            echo json_encode(['error' => 'Nom de catégorie requis.']);
            return;
        }

        try {
            $id = (new Category())->create($name);
            echo json_encode(['id' => $id, 'name' => $name]);
        } catch (PDOException $e) {
            // Duplicate entry
            echo json_encode(['error' => 'Cette catégorie existe déjà.']);
        }
    }
}
