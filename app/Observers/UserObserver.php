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
use App\Models\Post;
use App\Models\SavedPost;
use App\Models\SavedSearch;
use App\Models\Scopes\ReviewedScope;
use App\Models\Scopes\VerifiedScope;
use App\Models\ThreadMessage;
use App\Models\ThreadParticipant;
use App\Models\User;
use App\Notifications\UserActivated;
use Prologue\Alerts\Facades\Alert;

class UserObserver
{
	/**
	 * Listen to the Entry updating event.
	 *
	 * @param User $user
	 * @return void
	 */
	public function updating(User $user)
	{
		// Get the original object values
		$original = $user->getOriginal();
		
		// Listing Email address or Phone was not verified
		try {
			if (empty($original['email_verified_at']) || empty($original['phone_verified_at'])) {
				if (!empty($user->email_verified_at) && !empty($user->phone_verified_at)) {
					$user->notify(new UserActivated($user));
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
	 * @param User $user
	 * @return void
	 */
	public function deleting(User $user)
	{
		// Storage Disk Init.
		$disk = StorageDisk::getDisk();
		
		// Delete the user's photo
		if (isset($user->photo) && !empty($user->photo)) {
			if ($disk->exists($user->photo)) {
				$disk->delete($user->photo);
			}
		}
		
		// Delete all user's Posts
		$posts = Post::withoutGlobalScopes([VerifiedScope::class, ReviewedScope::class])->where('user_id', $user->id);
		if ($posts->count() > 0) {
			foreach ($posts->cursor() as $post) {
				$post->delete();
			}
		}
		
		// Delete all user's Messages
		$messages = ThreadMessage::where('user_id', $user->id);
		if ($messages->count() > 0) {
			foreach ($messages->cursor() as $message) {
				$message->forceDelete();
			}
		}
		
		// Delete all user as Participant
		$participants = ThreadParticipant::where('user_id', $user->id);
		if ($participants->count() > 0) {
			foreach ($participants->cursor() as $participant) {
				$participant->forceDelete();
			}
		}
		
		// Delete all user's Saved Posts
		$savedPosts = SavedPost::where('user_id', $user->id);
		if ($savedPosts->count() > 0) {
			foreach ($savedPosts->cursor() as $savedPost) {
				$savedPost->delete();
			}
		}
		
		// Delete all user's Saved Searches
		$savedSearches = SavedSearch::where('user_id', $user->id);
		if ($savedSearches->count() > 0) {
			foreach ($savedSearches->cursor() as $savedSearch) {
				$savedSearch->delete();
			}
		}
		
		// Check the Reviews Plugin
		if (config('plugins.reviews.installed')) {
			try {
				// Delete the reviews of this User
				$reviews = \extras\plugins\reviews\app\Models\Review::where('user_id', $user->id);
				if ($reviews->count() > 0) {
					foreach ($reviews->cursor() as $review) {
						$review->delete();
					}
				}
			} catch (\Throwable $e) {
			}
		}
		
		// Removing Entries from the Cache
		$this->clearCache($user);
	}
	
	/**
	 * Listen to the Entry saved event.
	 *
	 * @param User $user
	 * @return void
	 */
	public function saved(User $user)
	{
		// Create a new email token if the user's email is marked as unverified
		if (empty($user->email_verified_at)) {
			if (empty($user->email_token)) {
				$user->email_token = md5(microtime() . mt_rand());
				$user->save();
			}
		}
		
		// Create a new phone token if the user's phone number is marked as unverified
		if (empty($user->phone_verified_at)) {
			if (empty($user->phone_token)) {
				$user->phone_token = mt_rand(100000, 999999);
				$user->save();
			}
		}
		
		// Removing Entries from the Cache
		$this->clearCache($user);
	}
	
	/**
	 * Removing the Entity's Entries from the Cache
	 *
	 * @param $user
	 */
	private function clearCache($user)
	{
		try {
			cache()->forget('count.users');
		} catch (\Exception $e) {
		}
	}
}
