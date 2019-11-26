<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Resources\Json\ResourceResponse;

class DocumentCollection extends ResourceCollection
{
    public static $wrap = null;

    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'document' => $this->resource->items(),
            'pagination' => [
                'page' => $this->resource->currentPage(),
                'perPage' => $this->resource->perPage(),
                'total' => $this->resource->total(),
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public function toResponse($request)
    {
        return (new ResourceResponse($this))->toResponse($request);
    }

    /**
     * @inheritDoc
     */
    protected function collects()
    {
        return null;
    }
}
