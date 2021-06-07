<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'productType' => $this->productType,
            'productName' => $this->productName,
            'productDescription' => $this->productDescription,
            'productPrice' => $this->productPrice,
            'productImage' => env('APP_URL', 'http://localhost:8000/') . 'storage/' . $this->productImage,
            'user' =>  new UserResource($this->user),
            'created_at' => $this->created_at->diffForHumans(),
            'updated_at' => $this->updated_at->diffForHumans(),
        ];
    }
}
