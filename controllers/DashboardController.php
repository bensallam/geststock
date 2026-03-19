<?php

require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Client.php';
require_once __DIR__ . '/../models/Invoice.php';

class DashboardController
{
    public function index(): void
    {
        requireAuth();

        $productModel = new Product();
        $clientModel  = new Client();
        $invoiceModel = new Invoice();

        $totalProducts = $productModel->count();
        $totalClients  = $clientModel->count();
        $totalInvoices = $invoiceModel->count();
        $totalRevenue  = $invoiceModel->totalRevenue();
        $lowStock      = $productModel->lowStock();
        $recentInvoices = $invoiceModel->recent(5);

        require __DIR__ . '/../views/dashboard/index.php';
    }
}
