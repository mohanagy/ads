<?php
/*
 * LaraClassifier - Classified Ads Web Application
 * Copyright (c) BeDigit. All Rights Reserved
 *
 * Website: https://laraclassifier.com
 *
 * LICENSE
 * -------
 * This software is furnished under a license and may be used and copied
 * only in accordance with the terms of such license and with the inclusion
 * of the above copyright notice. If you Purchased from CodeCanyon,
 * Please read the full License from here - http://codecanyon.net/licenses/standard
 */

namespace App\Http\Controllers\Api;

use App\Http\Resources\EntityCollection;
use App\Http\Resources\PostTypeResource;
use App\Models\PostType;

/**
 * @group Listings
 */
class PostTypeController extends BaseController
{
	/**
	 * List listing types
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function index()
	{
		$postTypes = PostType::query()->get();
		
		$resourceCollection = new EntityCollection(class_basename($this), $postTypes);
		
		$message = ($postTypes->count() <= 0) ? t('no_post_types_found') : null;
		
		return $this->respondWithCollection($resourceCollection, $message);
	}
	
	/**
	 * Get listing type
	 *
	 * @urlParam id int required The listing type's ID. Example: 1
	 *
	 * @param $id
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function show($id)
	{
		$postType = PostType::query()->where('id', $id);
		
		$postType = $postType->first();
		
		abort_if(empty($postType), 404, t('post_type_not_found'));
		
		$resource = new PostTypeResource($postType);
		
		return $this->respondWithResource($resource);
	}
}
