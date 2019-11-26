<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Document extends JsonResource
{
    public static $wrap = 'document';

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
