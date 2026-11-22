<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Category;

class CatalogController extends Controller
{
    private Category $categoryModel;

    public function __construct()
    {
        $this->categoryModel = new Category();
    }

    /**
     * Display Service Catalog
     */
    public function index(Request $request, Response $response): string
    {
        if (!Auth::can('menu.catalog')) {
            $this->setFlash('danger', 'Bạn không có quyền truy cập Cổng dịch vụ.');
            $response->redirect('/dashboard');
            return '';
        }

        $categories = $this->categoryModel->getAllActive();
        
        // Load services for each category
        foreach ($categories as &$cat) {
            $cat['services'] = $this->categoryModel->getServicesByCategory((int)$cat['id']);
        }

        return $this->render('catalog/index', [
            'pageTitle'  => 'Danh Mục Dịch Vụ IT (Service Catalog)',
            'categories' => $categories
        ]);
    }
}

