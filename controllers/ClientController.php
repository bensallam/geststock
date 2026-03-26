<?php

require_once __DIR__ . '/../models/Client.php';

class ClientController
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    public function index(): void
    {
        requireAuth();
        $filters = ['search' => $_GET['search'] ?? '', 'company_id' => currentCompanyId()];
        $clients = $this->client->all($filters);
        require __DIR__ . '/../views/clients/index.php';
    }

    public function create(): void
    {
        requireAuth();
        $errors = [];
        $old    = [];
        require __DIR__ . '/../views/clients/create.php';
    }

    public function store(): void
    {
        requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect('clients'); }

        [$data, $errors] = $this->validate($_POST);

        if (!empty($errors)) {
            $old = $_POST;
            require __DIR__ . '/../views/clients/create.php';
            return;
        }

        $this->client->create($data);
        setFlash('success', 'Client ajouté avec succès.');
        redirect('clients');
    }

    public function edit(): void
    {
        requireAuth();
        $id     = (int) ($_GET['id'] ?? 0);
        $client = $this->client->find($id);
        if (!$client) { $this->notFound(); return; }

        $errors = [];
        $old    = $client;
        require __DIR__ . '/../views/clients/edit.php';
    }

    public function update(): void
    {
        requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect('clients'); }

        $id     = (int) ($_POST['id'] ?? 0);
        $client = $this->client->find($id);
        if (!$client) { $this->notFound(); return; }

        [$data, $errors] = $this->validate($_POST);

        if (!empty($errors)) {
            $old = $_POST;
            require __DIR__ . '/../views/clients/edit.php';
            return;
        }

        $this->client->update($id, $data);
        setFlash('success', 'Client mis à jour.');
        redirect('clients');
    }

    public function delete(): void
    {
        requireAuth();
        $id = (int) ($_POST['id'] ?? $_GET['id'] ?? 0);

        if ($this->client->hasInvoices($id)) {
            setFlash('danger', 'Impossible de supprimer : ce client a des factures associées.');
            redirect('clients');
            return;
        }

        $this->client->delete($id);
        setFlash('success', 'Client supprimé.');
        redirect('clients');
    }

    private function validate(array $post): array
    {
        $errors = [];
        $data   = [
            'company_id' => currentCompanyId(),
            'name'    => trim($post['name']    ?? ''),
            'address' => trim($post['address'] ?? ''),
            'ice'     => trim($post['ice']     ?? ''),
            'phone'   => trim($post['phone']   ?? ''),
            'email'   => trim($post['email']   ?? ''),
        ];

        if (empty($data['name'])) {
            $errors[] = 'Le nom du client est obligatoire.';
        }

        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'L\'adresse email n\'est pas valide.';
        }

        return [$data, $errors];
    }

    private function notFound(): void
    {
        http_response_code(404);
        require __DIR__ . '/../views/errors/404.php';
    }
}
