<?php

namespace Contatoseguro\TesteBackend\Controller;

use Contatoseguro\TesteBackend\Service\AdminService;
use Contatoseguro\TesteBackend\Service\CompanyService;
use Contatoseguro\TesteBackend\Service\ProductService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ReportController
{
    private ProductService $productService;
    private CompanyService $companyService;
    private AdminService $adminService;

    public function __construct()
    {
        $this->productService = new ProductService();
        $this->companyService = new CompanyService();
        $this->adminService = new AdminService();
    }

    public function generate(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $adminUserId = $request->getHeader('admin_user_id')[0];

        $data = [];
        $data[] = [
            'Id do produto',
            'Nome da Empresa',
            'Nome do Produto',
            'Valor do Produto',
            'Categorias do Produto',
            'Data de Criação',
            'Logs de Alterações'
        ];

        $stm = $this->productService->getAll($adminUserId);
        $products = $stm->fetchAll();

        foreach ($products as $i => $product) {
            $stm = $this->companyService->getNameById($product->company_id);
            $companyName = $stm->fetch()->name;

            $stm = $this->productService->getLog($product->id);
            $productLogs = $stm->fetchAll();

            $logs = "";
            $logs .= "<table style='font-size: 10px;'>";
            foreach ($productLogs as $log) {
                $stm = $this->adminService->getNameById($log->admin_user_id);
                $adminName = ucfirst($stm->fetch()->name);
                $adminName = strlen($adminName) > 1 ? $adminName : "Sem cadastro";

                $action = "";

                switch ($log->action) {
                    case 'create':
                        $action = "Criação";
                        break;
                    case 'update':
                        $action = "Atualização";
                        break;
                    default:
                        $action = "Remoção";
                        break;
                }

                $logs .= "<tr><td>{$adminName}, {$action}, {$log->timestamp}</td></tr>";
            }
            $logs .= "</table>";

            $data[$i + 1][] = $product->id;
            $data[$i + 1][] = $companyName;
            $data[$i + 1][] = $product->title;
            $data[$i + 1][] = $product->price;
            $data[$i + 1][] = $product->category;
            $data[$i + 1][] = $product->created_at;
            $data[$i + 1][] = $logs;
        }

        $report = "<table style='font-size: 10px;'>";
        foreach ($data as $row) {
            $report .= "<tr>";
            foreach ($row as $column) {
                $report .= "<td>{$column}</td>";
            }
            $report .= "</tr>";
        }
        $report .= "</table>";

        $response->getBody()->write($report);
        return $response->withStatus(200)->withHeader('Content-Type', 'text/html');
    }
}
