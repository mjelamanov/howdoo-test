<?php

namespace App\Http\Controllers\Api;

use App\Criteria\DocumentOrderedByLatestCriteria;
use App\Criteria\OnlyPublishedDocumentsCriteria;
use App\Criteria\SelectUserDocumentsCriteria;
use App\Document;
use App\Enums\DocumentStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Document as DocumentResource;
use App\Http\Resources\DocumentCollection;
use App\Repositories\DocumentRepository;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class DocumentController extends Controller
{
    /**
     * @var \App\Repositories\DocumentRepository
     */
    protected $repository;

    /**
     * @var \Illuminate\Database\ConnectionInterface
     */
    protected $connection;

    /**
     * DocumentController constructor.
     *
     * @param \App\Repositories\DocumentRepository $repository
     * @param \Illuminate\Database\ConnectionInterface $connection
     */
    public function __construct(DocumentRepository $repository, ConnectionInterface $connection)
    {
        $this->repository = $repository;
        $this->connection = $connection;
    }

    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Resources\Json\JsonResource
     */
    public function index(Request $request)
    {
        if ($user = $request->user('api')) {
            $repository = $this->repository->pushCriteria(new SelectUserDocumentsCriteria($user));
        } else {
            $repository = $this->repository->pushCriteria(new OnlyPublishedDocumentsCriteria());
        }

        $items = $repository->pushCriteria(new DocumentOrderedByLatestCriteria())
            ->paginate($request->input('perPage', config('document.per_page')));

        return DocumentCollection::make($items);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        $this->connection->beginTransaction();

        $document = $this->repository->create(['status' => DocumentStatus::DRAFT(), 'payload' => []]);

        $this->connection->commit();

        return DocumentResource::make($document)
            ->response()
            ->setStatusCode(JsonResponse::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Document  $document
     * @return \Illuminate\Http\Response
     */
    public function show(Document $document)
    {
        return DocumentResource::make($document);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Document $document
     *
     * @return \Illuminate\Http\Response
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, Document $document)
    {
        if ($document->status->is(DocumentStatus::PUBLISHED())) {
            throw new BadRequestHttpException('You cannot update the published document');
        }

        $this->validate($request, [
            'document.payload' => 'bail|required|array',
        ]);

        $this->connection->beginTransaction();

        $document->update(['payload' => $request->input('document.payload')]);

        $this->connection->commit();

        return DocumentResource::make($document);
    }

    /**
     * @param \App\Document $document
     *
     * @return \Illuminate\Http\Response
     */
    public function publish(Document $document)
    {
        $this->connection->beginTransaction();

        $document->update(['status' => DocumentStatus::PUBLISHED()]);

        $this->connection->commit();

        return DocumentResource::make($document);
    }
}
