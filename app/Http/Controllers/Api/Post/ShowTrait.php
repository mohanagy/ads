<?php
/**
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

namespace App\Http\Controllers\Api\Post;

use App\Events\PostWasVisited;
use App\Http\Controllers\Api\Post\Traits\ShowFieldValueTrait;
use App\Http\Resources\PostResource;
use App\Models\Permission;
use App\Models\Post;
use App\Models\Scopes\ReviewedScope;
use App\Models\Scopes\VerifiedScope;
use Illuminate\Support\Facades\Event;

trait ShowTrait
{
	use ShowFieldValueTrait;
	
	/**
	 * @param $id
	 * @return \Illuminate\Http\JsonResponse
	 * @throws \Psr\Container\ContainerExceptionInterface
	 * @throws \Psr\Container\NotFoundExceptionInterface
	 */
	public function showPost($id)
	{
		$guard = 'sanctum';
		if (auth($guard)->check()) {
			$user = auth($guard)->user();
			
			// Get post's details even if it's not activated, not reviewed or archived
			$cacheId = 'post.withoutGlobalScopes.with.city.pictures.' . $id . '.' . config('app.locale');
			$post = cache()->remember($cacheId, $this->cacheExpiration, function () use ($id) {
				return Post::withoutGlobalScopes([VerifiedScope::class, ReviewedScope::class])
					->withCountryFix()
					->where('id', $id)
					->with([
						'category'      => fn($builder) => $builder->with(['parent']),
						'postType',
						'city',
						'pictures',
						'latestPayment' => fn($builder) => $builder->with(['package']),
						'savedByLoggedUser',
					])
					->first();
			});
			
			// If the logged user is not an admin user...
			if (!auth($guard)->user()->can(Permission::getStaffPermissions())) {
				// Then don't get post that are not from the user
				if (!empty($post) && $post->user_id != $user->id) {
					$cacheId = 'post.with.city.pictures.' . $id . '.' . config('app.locale');
					$post = cache()->remember($cacheId, $this->cacheExpiration, function () use ($id) {
						return Post::withCountryFix()
							->unarchived()
							->where('id', $id)
							->with([
								'category'      => fn($builder) => $builder->with(['parent']),
								'postType',
								'city',
								'pictures',
								'latestPayment' => fn($builder) => $builder->with(['package']),
								'savedByLoggedUser',
							])
							->first();
					});
				}
			}
		} else {
			$cacheId = 'post.with.city.pictures.' . $id . '.' . config('app.locale');
			$post = cache()->remember($cacheId, $this->cacheExpiration, function () use ($id) {
				return Post::withCountryFix()
					->unarchived()
					->where('id', $id)
					->with([
						'category'      => fn($builder) => $builder->with(['parent']),
						'postType',
						'city',
						'pictures',
						'latestPayment' => fn($builder) => $builder->with(['package']),
						'savedByLoggedUser',
					])
					->first();
			});
		}
		// Preview Listing after activation
		if (request()->filled('preview') && request()->get('preview') == 1) {
			// Get post's details even if it's not activated and reviewed
			$post = Post::withoutGlobalScopes([VerifiedScope::class, ReviewedScope::class])
				->withCountryFix()
				->where('id', $id)
				->with([
					'category'      => fn($builder) => $builder->with(['parent']),
					'postType',
					'city',
					'pictures',
					'latestPayment' => fn($builder) => $builder->with(['package']),
					'savedByLoggedUser',
				])
				->first();
		}
		
		// Listing not found
		if (empty($post) || empty($post->category) || empty($post->city)) {
			abort(404, t('post_not_found'));
		}
		
		// Increment the Listing's visits counter
		Event::dispatch(new PostWasVisited($post));
		
		$data = [
			'success' => true,
			'result'  => new PostResource($post),
			'extra'   => [
				'fields' => $this->showFieldsValues($post->category->id, $post->id),
			],
		];
		
		return $this->apiResponse($data);
	}
}
