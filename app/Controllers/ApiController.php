<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Category;

class ApiController extends Controller
{
    public function getServices(Request $request, Response $response): void
    {
        $categoryId = (int)$request->input('category_id');
        if (!$categoryId) {
            $this->json(['success' => false, 'data' => []]);
            return;
        }

        $categoryModel = new Category();
        $services = $categoryModel->getServicesByCategory($categoryId);

        $this->json([
            'success' => true,
            'data'    => $services
        ]);
    }

    public function searchUsers(Request $request, Response $response): void
    {
        $q = trim($request->input('q') ?? '');
        $userModel = new \App\Models\User();
        $users = $userModel->searchUsersForMention($q, 5);
        
        $this->json([
            'success' => true,
            'data'    => $users
        ]);
    }

    /**
     * AI Smart Triage API (Rule 58)
     */
    public function aiTriage(Request $request, Response $response): void
    {
        $title = trim($request->input('title') ?? '');
        $description = trim($request->input('description') ?? '');

        if (empty($title) && empty($description)) {
            $this->json(['success' => false, 'message' => 'Nội dung trống']);
            return;
        }

        $aiService = new \App\Services\AIService();
        $result = $aiService->smartTriage($title, $description);

        $this->json($result);
    }

    /**
     * AI Copilot Suggestions for IT Staff (Rule 58)
     */
    public function aiSuggest(Request $request, Response $response): void
    {
        $ticketId = (int)$request->input('ticket_id');
        if (!$ticketId) {
            $this->json(['success' => false, 'message' => 'Thiếu ticket_id']);
            return;
        }

        $ticketModel = new \App\Models\Ticket();
        $ticket = $ticketModel->findById($ticketId);
        if (!$ticket) {
            $this->json(['success' => false, 'message' => 'Ticket không tồn tại']);
            return;
        }

        $comments = $ticketModel->getComments($ticketId, true);
        $aiService = new \App\Services\AIService();

        $summary = $aiService->summarizeTicket($ticket, $comments);
        $solutionSteps = $aiService->suggestSolution($ticket);
        $draftReply = $aiService->draftResponse($ticket, \App\Core\Auth::user() ?: []);

        $this->json([
            'success'        => true,
            'summary'        => $summary,
            'solution_steps' => $solutionSteps,
            'draft_reply'    => $draftReply
        ]);
    }

    /**
     * AI Knowledge Base Chatbot Endpoint
     */
    public function kbChat(Request $request, Response $response): void
    {
        $message = trim($request->input('message') ?? '');
        if (empty($message)) {
            $this->json([
                'success' => false,
                'message' => 'Nội dung tin nhắn không được để trống.'
            ]);
            return;
        }

        $userId = (int)(\App\Core\Auth::id() ?? 0);
        $aiService = new \App\Services\AIService();
        $result = $aiService->answerKnowledgeQuery($message, $userId);

        $this->json([
            'success'           => true,
            'reply'             => $result['reply'],
            'articles'          => $result['articles'] ?? [],
            'suggested_actions' => $result['suggested_actions'] ?? []
        ]);
    }
}

