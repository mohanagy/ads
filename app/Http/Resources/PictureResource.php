<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PictureResource extends JsonResource
{
	/**
	 * Transform the resource into an array.
	 *
	 * @param $request
	 * @return array
	 * @throws \Psr\Container\ContainerExceptionInterface
	 * @throws \Psr\Container\NotFoundExceptionInterface
	 */
	public function toArray($request): array
	{
		$entity = [
			'id' => $this->id,
		];
		$columns = $this->getFillable();
		foreach ($columns as $column) {
			$entity[$column] = $this->{$column};
		}
		
		$embed = explode(',', request()->get('embed'));
		
		if (in_array('post', $embed)) {
			$entity['post'] = new PostResource($this->whenLoaded('post'));
		}
		
		$entity['filename_url'] = $this->filename_url ?? null;
		$entity['filename_url_small'] = $this->filename_url_small ?? null;
		$entity['filename_url_medium'] = $this->filename_url_medium ?? null;
		$entity['filename_url_big'] = $this->filename_url_big ?? null;
		
		return $entity;
	}
}
