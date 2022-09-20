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

namespace App\Observers;

use App\Helpers\Files\Storage\StorageDisk;
use App\Models\Language;
use App\Models\Payment;
use App\Models\Picture;
use App\Models\Post;
use App\Models\PostValue;
use App\Models\SavedPost;
use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\StrictActiveScope;
use App\Models\Thread;
use App\Notifications\PostActivated;
use App\Notifications\PostReviewed;
use Prologue\Alerts\Facades\Alert;

class PostObserver
{
	/**
	 * Listen to the Entry updating event.
	 *
	 * @param Post $post
	 * @return void
	 */
	public function updating(Post $post)
	{
		// Get the original object values
		$original = $post->getOriginal();
		
		try {
			// Listing Email address or Phone was not verified
			if (empty($original['email_verified_at']) || empty($original['phone_verified_at'])) {
				// Listing was not approved (reviewed)
				if (empty($original['reviewed_at'])) {
					if (config('settings.single.listings_review_activation') == 1) {
						if (!empty($post->email_verified_at) && !empty($post->phone_verified_at)) {
							if (!empty($post->reviewed_at)) {
								$post->notify(new PostReviewed($post));
							} else {
								$post->notify(new PostActivated($post));
							}
						}
					} else {
						if (!empty($post->email_verified_at) && !empty($post->phone_verified_at)) {
							$post->notify(new PostReviewed($post));
						}
					}
				} else {
					// Listing was approved (reviewed)
					if (!empty($post->email_verified_at) && !empty($post->phone_verified_at)) {
						$post->notify(new PostReviewed($post));
					}
				}
			} else {
				// Listing Email address or Phone was verified
				// Listing was not approved (reviewed)
				if (empty($original['reviewed_at'])) {
					if (!empty($post->email_verified_at) && !empty($post->phone_verified_at)) {
						if (!empty($post->reviewed_at)) {
							$post->notify(new PostReviewed($post));
						}
					}
				}
			}
		} catch (\Throwable $e) {
			if (!isFromApi()) {
				if (isFromAdminPanel()) {
					Alert::error($e->getMessage())->flash();
				} else {
					flash($e->getMessage())->error();
				}
			}
		}
	}
	
	/**
	 * Listen to the Entry deleting event.
	 *
	 * @param Post $post
	 * @return void
	 */
	public function deleting(Post $post)
	{
		// Storage Disk Init.
		$disk = StorageDisk::getDisk();
		
		// Delete all the Post's Custom Fields Values
		$postValues = PostValue::where('post_id', $post->id)->get();
		if ($postValues->count() > 0) {
			foreach ($postValues as $postValue) {
				$postValue->delete();
			}
		}
		
		// Delete all Threads
		$messages = Thread::where('post_id', $post->id);
		if ($messages->count() > 0) {
			foreach ($messages->cursor() as $message) {
				$message->forceDelete();
			}
		}
		
		// Delete all Saved Posts
		$savedPosts = SavedPost::where('post_id', $post->id);
		if ($savedPosts->count() > 0) {
			foreach ($savedPosts->cursor() as $savedPost) {
				$savedPost->delete();
			}
		}
		
		// Delete all Pictures
		$pictures = Picture::where('post_id', $post->id);
		if ($pictures->count() > 0) {
			foreach ($pictures->cursor() as $picture) {
				$picture->delete();
			}
		}
		
		// Delete the Payment(s) of this Post
		$payments = Payment::withoutGlobalScope(StrictActiveScope::class)->where('post_id', $post->id)->get();
		if ($payments->count() > 0) {
			foreach ($payments as $payment) {
				$payment->delete();
			}
		}
		
		// Check Reviews plugin
		if (config('plugins.reviews.installed')) {
			try {
				// Delete the reviews of this Post
				$reviews = \extras\plugins\reviews\app\Models\Review::where('post_id', $post->id);
				if ($reviews->count() > 0) {
					foreach ($reviews->cursor() as $review) {
						$review->delete();
					}
				}
			} catch (\Throwable $e) {
			}
		}
		
		// Remove the listing media folder
		if (!empty($post->country_code) && !empty($post->id)) {
			$directoryPath = 'files/' . strtolower($post->country_code) . '/' . $post->id;
			
			if ($disk->exists($directoryPath)) {
				$disk->deleteDirectory($directoryPath);
			}
		}
		
		// Removing Entries from the Cache
		$this->clearCache($post);
	}
	
	/**
	 * Listen to the Entry saved event.
	 *
	 * @param Post $post
	 * @return void
	 */
	public function saved(Post $post)
	{
		// Create a new email token if the post's email is marked as unverified
		if (empty($post->email_verified_at)) {
			if (empty($post->email_token)) {
				$post->email_token = md5(microtime() . mt_rand());
				$post->save();
			}
		}
		
		// Create a new phone token if the post's phone number is marked as unverified
		if (empty($post->phone_verified_at)) {
			if (empty($post->phone_token)) {
				$post->phone_token = mt_rand(100000, 999999);
				$post->save();
			}
		}
		
		// Removing Entries from the Cache
		$this->clearCache($post);
	}
	
	/**
	 * Listen to the Entry deleted event.
	 *
	 * @param Post $post
	 * @return void
	 */
	public function deleted(Post $post)
	{
		//...
	}
	
	/**
	 * Removing the Entity's Entries from the Cache
	 *
	 * @param $post
	 */
	private function clearCache($post)
	{
		try {
			cache()->forget($post->country_code . '.count.posts');
			
			cache()->forget($post->country_code . '.sitemaps.posts.xml');
			
			cache()->forget($post->country_code . '.home.getPosts.sponsored');
			cache()->forget($post->country_code . '.home.getPosts.latest');
			
			cache()->forget('post.withoutGlobalScopes.with.city.pictures.' . $post->id);
			cache()->forget('post.with.city.pictures.' . $post->id);
			
			// Need to be caught (Independently)
			$languages = Language::withoutGlobalScopes([ActiveScope::class])->get(['abbr']);
			if ($languages->count() > 0) {
				foreach ($languages as $language) {
					cache()->forget('post.withoutGlobalScopes.with.city.pictures.' . $post->id . '.' . $language->abbr);
					cache()->forget('post.with.city.pictures.' . $post->id . '.' . $language->abbr);
					cache()->forget($post->country_code . '.count.posts.per.cat.' . $language->abbr);
				}
			}
			
			cache()->forget('posts.similar.category.' . $post->category_id . '.post.' . $post->id);
			cache()->forget('posts.similar.city.' . $post->city_id . '.post.' . $post->id);
		} catch (\Throwable $e) {
		}
	}
}
