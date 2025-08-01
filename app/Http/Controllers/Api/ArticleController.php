<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

class ArticleController extends Controller
{
    /**
     * Get all published articles (all authenticated users)
     */
    public function index(Request $request)
    {
        try {
            $articles = Article::with('author.role')
                ->published()
                ->orderBy('published_at', 'desc')
                ->get()
                ->map(function ($article) {
                    return [
                        'id' => $article->id,
                        'title' => $article->title,
                        'content' => $article->content,
                        'status' => $article->status,
                        'author' => [
                            'id' => $article->author->id,
                            'name' => $article->author->name,
                            'role' => $article->author->role->name,
                        ],
                        'published_at' => $article->published_at,
                        'created_at' => $article->created_at,
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'Published articles retrieved successfully',
                'data' => $articles
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve articles',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's own articles (authors)
     */
    public function mine(Request $request)
    {
        try {
            $user = $request->user();
            
            $articles = Article::with('author.role')
                ->where('author_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($article) {
                    return [
                        'id' => $article->id,
                        'title' => $article->title,
                        'content' => $article->content,
                        'status' => $article->status,
                        'author' => [
                            'id' => $article->author->id,
                            'name' => $article->author->name,
                            'role' => $article->author->role->name,
                        ],
                        'published_at' => $article->published_at,
                        'created_at' => $article->created_at,
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'Your articles retrieved successfully',
                'data' => $articles
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve your articles',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new article (authors)
     */
    public function store(Request $request)
    {
        try {
            // Check if user can create articles
            if (!Gate::allows('create-article')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Author access required.'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'content' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation errors',
                    'errors' => $validator->errors()
                ], 422);
            }

            $article = Article::create([
                'title' => $request->title,
                'content' => $request->content,
                'author_id' => $request->user()->id,
                'status' => 'draft',
            ]);

            $article->load('author.role');

            return response()->json([
                'success' => true,
                'message' => 'Article created successfully',
                'data' => [
                    'id' => $article->id,
                    'title' => $article->title,
                    'content' => $article->content,
                    'status' => $article->status,
                    'author' => [
                        'id' => $article->author->id,
                        'name' => $article->author->name,
                        'role' => $article->author->role->name,
                    ],
                    'created_at' => $article->created_at,
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create article',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an article (authors can edit their own)
     */
    public function update(Request $request, $id)
    {
        try {
            $article = Article::find($id);
            
            if (!$article) {
                return response()->json([
                    'success' => false,
                    'message' => 'Article not found'
                ], 404);
            }

            // Check if user can update this article
            if (!Gate::allows('update-article', $article)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. You can only edit your own articles.'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'title' => 'sometimes|required|string|max:255',
                'content' => 'sometimes|required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation errors',
                    'errors' => $validator->errors()
                ], 422);
            }

            $article->update($request->only(['title', 'content']));
            $article->load('author.role');

            return response()->json([
                'success' => true,
                'message' => 'Article updated successfully',
                'data' => [
                    'id' => $article->id,
                    'title' => $article->title,
                    'content' => $article->content,
                    'status' => $article->status,
                    'author' => [
                        'id' => $article->author->id,
                        'name' => $article->author->name,
                        'role' => $article->author->role->name,
                    ],
                    'updated_at' => $article->updated_at,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update article',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete an article (admin only)
     */
    public function destroy(Request $request, $id)
    {
        try {
            $article = Article::find($id);
            
            if (!$article) {
                return response()->json([
                    'success' => false,
                    'message' => 'Article not found'
                ], 404);
            }

            // Check if user can delete articles
            if (!Gate::allows('delete-article')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Admin access required.'
                ], 403);
            }

            $article->delete();

            return response()->json([
                'success' => true,
                'message' => 'Article deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete article',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Publish an article (editors and admins)
     */
    public function publish(Request $request, $id)
    {
        try {
            $article = Article::find($id);
            
            if (!$article) {
                return response()->json([
                    'success' => false,
                    'message' => 'Article not found'
                ], 404);
            }

            // Check if user can publish articles
            if (!Gate::allows('publish-article')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Editor or Admin access required.'
                ], 403);
            }

            $article->update([
                'status' => 'published',
                'published_at' => now(),
            ]);

            $article->load('author.role');

            return response()->json([
                'success' => true,
                'message' => 'Article published successfully',
                'data' => [
                    'id' => $article->id,
                    'title' => $article->title,
                    'content' => $article->content,
                    'status' => $article->status,
                    'author' => [
                        'id' => $article->author->id,
                        'name' => $article->author->name,
                        'role' => $article->author->role->name,
                    ],
                    'published_at' => $article->published_at,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to publish article',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
