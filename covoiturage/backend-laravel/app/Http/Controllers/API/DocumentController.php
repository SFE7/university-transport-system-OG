<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\DocumentSoumisResource;
use App\Models\DocumentSoumis;
use App\Services\DocumentService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    use ApiResponseTrait;

    public function __construct(private readonly DocumentService $service) {}

    public function index(): JsonResponse
    {
        $docs = $this->service->getPending();

        return $this->success(DocumentSoumisResource::collection($docs));
    }

    public function approve(int $id, Request $request): JsonResponse
    {
        $doc = DocumentSoumis::findOrFail($id);
        $doc = $this->service->approve($doc, $request->user());

        return $this->success(new DocumentSoumisResource($doc), 'Document approuvé');
    }

    public function reject(int $id, Request $request): JsonResponse
    {
        $request->validate(['reason' => 'required|string|max:1000']);

        $doc = DocumentSoumis::findOrFail($id);
        $doc = $this->service->reject($doc, $request->user(), (string) $request->reason);

        return $this->success(new DocumentSoumisResource($doc), 'Document rejeté');
    }
}
<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\DocumentSoumis;
use App\Services\DocumentService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private readonly DocumentService $service
    ) {}

    public function index(): JsonResponse
    {
        return $this->success($this->service->getPending());
    }

    public function approve(int $id, Request $request): JsonResponse
    {
        $doc = DocumentSoumis::with('membre')->findOrFail($id);
        $doc = $this->service->approve($doc, $request->user());

        return $this->success($doc, 'Document approuve');
    }

    public function reject(int $id, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $doc = DocumentSoumis::with('membre')->findOrFail($id);
        $doc = $this->service->reject($doc, $request->user(), $validated['reason']);

        return $this->success($doc, 'Document rejete');
    }
}
