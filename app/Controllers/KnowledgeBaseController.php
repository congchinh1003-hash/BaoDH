<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\KnowledgeBase;

class KnowledgeBaseController extends Controller
{
    private KnowledgeBase $kbModel;

    public function __construct()
    {
        $this->kbModel = new KnowledgeBase();
    }

    /**
     * Knowledge Base Portal (All users)
     */
    public function index(Request $request, Response $response): string
    {
        if (!Auth::can('menu.kb')) {
            $this->setFlash('danger', 'Bạn không có quyền truy cập Cơ sở tri thức.');
            $response->redirect('/dashboard');
            return '';
        }

        $filters = [
            'category_id' => $request->input('category_id'),
            'search'      => $request->input('search'),
        ];

        $categories = $this->kbModel->getCategories();
        $articles = $this->kbModel->getArticles($filters);
        $popular = $this->kbModel->getPopularArticles(5);

        $selectedCategory = null;
        if (!empty($filters['category_id'])) {
            $selectedCategory = $this->kbModel->getCategoryById((int)$filters['category_id']);
        }

        return $this->render('kb/index', [
            'pageTitle'        => 'Cơ Sở Tri Thức & Hướng Dẫn Tự Khắc Phục (Knowledge Base)',
            'categories'       => $categories,
            'articles'         => $articles,
            'popular'          => $popular,
            'filters'          => $filters,
            'selectedCategory' => $selectedCategory
        ]);
    }

    /**
     * Read KB Article
     */
    public function show(Request $request, Response $response, string $id): string
    {
        $articleId = (int)$id;
        $article = $this->kbModel->getArticleById($articleId);

        if (!$article) {
            $this->setFlash('danger', 'Không tìm thấy bài viết yêu cầu.');
            $response->redirect('/kb');
        }

        // Increment view count
        $this->kbModel->incrementView($articleId);

        $relatedArticles = $this->kbModel->getArticles([
            'category_id' => $article['category_id'],
            'limit'       => 4
        ]);

        return $this->render('kb/show', [
            'pageTitle'       => $article['title'],
            'article'         => $article,
            'relatedArticles' => $relatedArticles
        ]);
    }

    /**
     * Vote helpful or unhelpful
     */
    public function voteHelpful(Request $request, Response $response, string $id): void
    {
        $articleId = (int)$id;
        $isHelpful = (bool)$request->input('is_helpful', true);

        $this->kbModel->voteHelpful($articleId, $isHelpful);

        $this->setFlash('success', 'Cảm ơn ý kiến đóng góp của bạn đối với bài viết!');
        $response->redirect("/kb/{$articleId}");
    }

    /**
     * Quick search API for ticket create form
     */
    public function searchApi(Request $request, Response $response): void
    {
        $query = trim($request->input('q') ?? '');
        if (mb_strlen($query) < 2) {
            $this->json(['success' => true, 'data' => []]);
            return;
        }

        $results = $this->kbModel->search($query, 4);
        $this->json(['success' => true, 'data' => $results]);
    }

    /**
     * Manage KB articles (Staff / Admin)
     */
    public function manage(Request $request, Response $response): string
    {
        if (!Auth::isStaff() && !Auth::isManager()) {
            $this->setFlash('danger', 'Bạn không có quyền quản trị kho tri thức.');
            $response->redirect('/kb');
        }

        $articles = $this->kbModel->getArticles(['status' => '']);
        $categories = $this->kbModel->getCategories();

        return $this->render('kb/manage', [
            'pageTitle'  => 'Quản Trị Bài Viết Kho Tri Thức (KB)',
            'articles'   => $articles,
            'categories' => $categories
        ]);
    }

    /**
     * Create Article Form
     */
    public function create(Request $request, Response $response): string
    {
        if (!Auth::isStaff() && !Auth::isManager()) {
            $this->setFlash('danger', 'Bạn không có quyền thực hiện.');
            $response->redirect('/kb');
        }

        $categories = $this->kbModel->getCategories();

        return $this->render('kb/create', [
            'pageTitle'  => 'Soạn Thảo Bài Viết Hướng Dẫn Mới',
            'categories' => $categories
        ]);
    }

    /**
     * Store article
     */
    public function store(Request $request, Response $response): void
    {
        if (!Auth::isStaff() && !Auth::isManager()) {
            $this->setFlash('danger', 'Bạn không có quyền thực hiện.');
            $response->redirect('/kb');
            return;
        }

        $title = trim($request->input('title') ?? '');
        $categoryId = (int)$request->input('category_id');
        $content = trim($request->input('content') ?? '');
        $status = $request->input('status', 'PUBLISHED');

        if (empty($title) || empty($content) || !$categoryId) {
            $this->setFlash('danger', 'Vui lòng nhập đầy đủ tiêu đề, danh mục và nội dung bài viết.');
            $response->redirect('/kb/create');
            return;
        }

        try {
            $articleId = $this->kbModel->createArticle([
                'title'       => $title,
                'category_id' => $categoryId,
                'content'     => $content,
                'author_id'   => Auth::id(),
                'status'      => $status
            ]);

            $this->setFlash('success', 'Xuất bản bài viết hướng dẫn thành công!');
            $response->redirect("/kb/{$articleId}");
        } catch (\Exception $e) {
            $this->setFlash('danger', 'Lỗi: ' . $e->getMessage());
            $response->redirect('/kb/create');
        }
    }

    /**
     * Delete article
     */
    public function delete(Request $request, Response $response, string $id): void
    {
        if (!Auth::isStaff() && !Auth::isManager()) {
            $this->setFlash('danger', 'Bạn không có quyền xóa bài viết.');
            $response->redirect('/kb');
            return;
        }

        $this->kbModel->deleteArticle((int)$id);
        $this->setFlash('success', 'Đã xóa bài viết khỏi kho tri thức.');
        $response->redirect('/kb/manage');
    }
}

