<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApiSuccessResource extends JsonResource
{
    protected $message;
    protected $metadata;

    public function __construct($resource, string $message = 'Success', array $metadata = [])
    {
        parent::__construct($resource);
        $this->message = $message;
        $this->metadata = $metadata;
    }

    public function toArray(Request $request): array
    {
        return [
            'success' => true,
            'message' => $this->message,
            'data' => $this->resource,
            'metadata' => $this->metadata ?: (object) []
        ];
    }
}
