{{--
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
--}}
@extends('layouts.master')

@section('content')
	{!! csrf_field() !!}
	<input type="hidden" id="postId" name="post_id" value="{{ data_get($post, 'id') }}">
	
	@if (session()->has('flash_notification'))
		@includeFirst([config('larapen.core.customizedViewPath') . 'common.spacer', 'common.spacer'])
		<?php $paddingTopExists = true; ?>
		<div class="container">
			<div class="row">
				<div class="col-12">
					@include('flash::message')
				</div>
			</div>
		</div>
		<?php session()->forget('flash_notification.message'); ?>
	@endif
	
	{{-- Archived listings message --}}
	@if (!empty(data_get($post, 'archived_at')))
		@includeFirst([config('larapen.core.customizedViewPath') . 'common.spacer', 'common.spacer'])
		<?php $paddingTopExists = true; ?>
		<div class="container">
			<div class="row">
				<div class="col-12">
					<div class="alert alert-warning" role="alert">
						{!! t('This listing has been archived') !!}
					</div>
				</div>
			</div>
		</div>
	@endif
	
	<div class="main-container">
		
		<?php if (isset($topAdvertising) && !empty($topAdvertising)): ?>
			@includeFirst([config('larapen.core.customizedViewPath') . 'layouts.inc.advertising.top', 'layouts.inc.advertising.top'], ['paddingTopExists' => $paddingTopExists ?? false])
		<?php
			$paddingTopExists = false;
		endif;
		?>
		
		<div class="container {{ (isset($topAdvertising) && !empty($topAdvertising)) ? 'mt-3' : 'mt-2' }}">
			<div class="row">
				<div class="col-md-12">
					
					<nav aria-label="breadcrumb" role="navigation" class="float-start">
						<ol class="breadcrumb">
							<li class="breadcrumb-item"><a href="{{ url('/') }}"><i class="fas fa-home"></i></a></li>
							<li class="breadcrumb-item"><a href="{{ url('/') }}">{{ config('country.name') }}</a></li>
							@if (isset($catBreadcrumb) && is_array($catBreadcrumb) && count($catBreadcrumb) > 0)
								@foreach($catBreadcrumb as $key => $value)
									<li class="breadcrumb-item">
										<a href="{{ $value->get('url') }}">
											{!! $value->get('name') !!}
										</a>
									</li>
								@endforeach
							@endif
							<li class="breadcrumb-item active" aria-current="page">{{ str(data_get($post, 'title'))->limit(70) }}</li>
						</ol>
					</nav>
					
					<div class="float-end backtolist">
						<a href="{{ rawurldecode(url()->previous()) }}"><i class="fa fa-angle-double-left"></i> {{ t('back_to_results') }}</a>
					</div>
				
				</div>
			</div>
		</div>
		
		<div class="container">
			<div class="row">
				<div class="col-lg-9 page-content col-thin-right">
					<?php
					$innerBoxStyle = (!auth()->check() && plugin_exists('reviews')) ? 'overflow: visible;' : '';
					?>
					<div class="inner inner-box items-details-wrapper pb-0" style="{{ $innerBoxStyle }}">
						<h1 class="h4 fw-bold enable-long-words">
							<strong>
								<a href="{{ \App\Helpers\UrlGen::post($post) }}" title="{{ data_get($post, 'title') }}">
									{{ data_get($post, 'title') }}
                                </a>
                            </strong>
							@if (config('settings.single.show_listing_types'))
								@if (!empty(data_get($post, 'postType')))
									<small class="label label-default adlistingtype">{{ data_get($post, 'postType.name') }}</small>
								@endif
							@endif
							@if (data_get($post, 'featured') == 1 && !empty(data_get($post, 'latestPayment.package')))
								<i class="fas fa-check-circle"
								   style="color: {{ data_get($post, 'latestPayment.package.ribbon') }};"
								   data-bs-placement="bottom"
								   data-bs-toggle="tooltip"
								   title="{{ data_get($post, 'latestPayment.package.short_name') }}"
								></i>
                            @endif
						</h1>
						<span class="info-row">
							@if (!config('settings.single.hide_dates'))
							<span class="date"{!! (config('lang.direction')=='rtl') ? ' dir="rtl"' : '' !!}>
								<i class="far fa-clock"></i> {!! data_get($post, 'created_at_formatted') !!}
							</span>&nbsp;
							@endif
							<span class="category"{!! (config('lang.direction')=='rtl') ? ' dir="rtl"' : '' !!}>
								<i class="bi bi-folder"></i> {{ data_get($post, 'category.parent.name', data_get($post, 'category.name')) }}
							</span>&nbsp;
							<span class="item-location"{!! (config('lang.direction')=='rtl') ? ' dir="rtl"' : '' !!}>
								<i class="bi bi-geo-alt"></i> {{ data_get($post, 'city.name') }}
							</span>&nbsp;
							<span class="category"{!! (config('lang.direction')=='rtl') ? ' dir="rtl"' : '' !!}>
								<i class="bi bi-eye"></i> {{
									\App\Helpers\Number::short(data_get($post, 'visits'))
									. ' '
									. trans_choice('global.count_views', getPlural(data_get($post, 'visits')), [], config('app.locale'))
									}}
							</span>
							<span class="category float-md-end"{!! (config('lang.direction')=='rtl') ? ' dir="rtl"' : '' !!}>
								{{ t('reference') }}: {{ hashId(data_get($post, 'id'), false, false) }}
							</span>
						</span>
						
						@include('post.inc.pictures-slider')
						
						@if (config('plugins.reviews.installed'))
							@if (view()->exists('reviews::ratings-single'))
								@include('reviews::ratings-single')
							@endif
						@endif
						

						<div class="items-details">
							<ul class="nav nav-tabs" id="itemsDetailsTabs" role="tablist">
								<li class="nav-item" role="presentation">
									<button class="nav-link active"
									   id="item-details-tab"
									   data-bs-toggle="tab"
									   data-bs-target="#item-details"
									   role="tab"
									   aria-controls="item-details"
									   aria-selected="true"
									>
										<h4>{{ t('listing_details') }}</h4>
									</button>
								</li>
								@if (config('plugins.reviews.installed'))
									<li class="nav-item" role="presentation">
										<button class="nav-link"
										   id="item-{{ config('plugins.reviews.name') }}-tab"
										   data-bs-toggle="tab"
										   data-bs-target="#item-{{ config('plugins.reviews.name') }}"
										   role="tab"
										   aria-controls="item-{{ config('plugins.reviews.name') }}"
										   aria-selected="false"
										>
											<h4>
												{{ trans('reviews::messages.Reviews') }}
												@if (isset($rvPost) && !empty($rvPost))
													({{ $rvPost->rating_count }})
												@endif
											</h4>
										</button>
									</li>
								@endif
							</ul>
							
							{{-- Tab panes --}}
							<div class="tab-content p-3 mb-3" id="itemsDetailsTabsContent">
								<div class="tab-pane show active" id="item-details" role="tabpanel" aria-labelledby="item-details-tab">
									<div class="row pb-3">
										<div class="items-details-info col-md-12 col-sm-12 col-12 enable-long-words from-wysiwyg">
											
											<div class="row">
												{{-- Location --}}
												<div class="col-md-6 col-sm-6 col-6">
													<h4 class="fw-normal p-0">
														<span class="fw-bold"><i class="bi bi-geo-alt"></i> {{ t('location') }}: </span>
														<span>
															<a href="{!! \App\Helpers\UrlGen::city(data_get($post, 'city')) !!}">
																{{ data_get($post, 'city.name') }}
															</a>
														</span>
													</h4>
												</div>
		
												{{-- Price / Salary --}}
												<div class="col-md-6 col-sm-6 col-6 text-end">
													<h4 class="fw-normal p-0">
														<span class="fw-bold">
															{{ getPriceLabel(data_get($post, 'category.type')) }}
														</span>
														<span>
															{!! getPriceInfo(data_get($post, 'price'), data_get($post, 'category.type')) !!}
															@if (!in_array(data_get($post, 'category.type'), ['not-salable']))
																@if (data_get($post, 'negotiable') == 1)
																	<small class="label bg-success"> {{ t('negotiable') }}</small>
																@endif
															@endif
														</span>
													</h4>
												</div>
											</div>
											<hr class="border-0 bg-secondary">
											
											{{-- Description --}}
											<div class="row">
												<div class="col-12 detail-line-content">
													{!! transformDescription(data_get($post, 'description')) !!}
												</div>
											</div>
											
											{{-- Custom Fields --}}
											@includeFirst([config('larapen.core.customizedViewPath') . 'post.inc.fields-values', 'post.inc.fields-values'])
										
											{{-- Tags --}}
											@if (!empty(data_get($post, 'tags')))
												<div class="row mt-3">
													<div class="col-12">
														<h4 class="p-0 my-3"><i class="bi bi-tags"></i> {{ t('Tags') }}:</h4>
														@foreach(data_get($post, 'tags') as $iTag)
															<span class="d-inline-block border border-inverse bg-light rounded-1 py-1 px-2 my-1 me-1">
																<a href="{{ \App\Helpers\UrlGen::tag($iTag) }}">
																	{{ $iTag }}
																</a>
															</span>
														@endforeach
													</div>
												</div>
											@endif
											
											{{-- Actions --}}
											@if (!auth()->check() || (auth()->check() && auth()->id() != data_get($post, 'user_id')))
												<div class="row text-center h2 mt-4">
													<div class="col-4">
													@if (auth()->check())
														@if (auth()->user()->id == data_get($post, 'user_id'))
															<a href="{{ \App\Helpers\UrlGen::editPost($post) }}">
																<i class="far fa-edit"
																   data-bs-toggle="tooltip"
																   title="{{ t('Edit') }}"
																></i>
															</a>
														@else
															{!! genEmailContactBtn($post, false, true) !!}
														@endif
													@else
														{!! genEmailContactBtn($post, false, true) !!}
													@endif
													</div>
													@if (isVerifiedPost($post))
														<div class="col-4">
															<a class="make-favorite" id="{{ data_get($post, 'id') }}" href="javascript:void(0)">
																@if (auth()->check())
																	@if (!empty(data_get($post, 'savedByLoggedUser')))
																		<i class="fas fa-bookmark"
																		   data-bs-toggle="tooltip"
																		   title="{{ t('Remove favorite') }}"
																		></i>
																	@else
																		<i class="far fa-bookmark"
																		   data-bs-toggle="tooltip"
																		   title="{{ t('Save listing') }}"
																		></i>
																	@endif
																@else
																	<i class="far fa-bookmark"
																	   data-bs-toggle="tooltip"
																	   title="{{ t('Save listing') }}"
																	></i>
																@endif
															</a>
														</div>
														<div class="col-4">
															<a href="{{ \App\Helpers\UrlGen::reportPost($post) }}">
																<i class="far fa-flag"
																   data-bs-toggle="tooltip"
																   title="{{ t('Report abuse') }}"
																></i>
															</a>
														</div>
													@endif
												</div>
											@endif
										</div>
										
									</div>
								</div>
								
								@if (config('plugins.reviews.installed'))
									@if (view()->exists('reviews::comments'))
										@include('reviews::comments')
									@endif
								@endif
							</div>
									
							<div class="content-footer text-start">
								@if (auth()->check())
									@if (auth()->user()->id == data_get($post, 'user_id'))
										<a class="btn btn-default" href="{{ \App\Helpers\UrlGen::editPost($post) }}">
											<i class="far fa-edit"></i> {{ t('Edit') }}
										</a>
									@else
										{!! genPhoneNumberBtn($post) !!}
										{!! genEmailContactBtn($post) !!}
									@endif
								@else
									{!! genPhoneNumberBtn($post) !!}
									{!! genEmailContactBtn($post) !!}
								@endif
							</div>
						</div>
					</div>
				</div>

				<div class="col-lg-3 page-sidebar-right">
					<aside>
						<div class="card card-user-info sidebar-card">
							@if (auth()->check() && auth()->id() == data_get($post, 'user_id'))
								<div class="card-header">{{ t('Manage Listing') }}</div>
							@else
								<div class="block-cell user">
									<div class="cell-media">
										<img src="{{ data_get($post, 'user_photo_url') }}" alt="{{ data_get($post, 'contact_name') }}">
									</div>
									<div class="cell-content">
										<h5 class="title">{{ t('Posted by') }}</h5>
										<span class="name">
											@if (isset($user) && !empty($user))
												<a href="{{ \App\Helpers\UrlGen::user($user) }}">
													{{ data_get($post, 'contact_name') }}
												</a>
											@else
												{{ data_get($post, 'contact_name') }}
											@endif
										</span>
										
										@if (config('plugins.reviews.installed'))
											@if (view()->exists('reviews::ratings-user'))
												@include('reviews::ratings-user')
											@endif
										@endif
										
									</div>
								</div>
							@endif
							
							<div class="card-content">
								<?php $evActionStyle = 'style="border-top: 0;"'; ?>
								@if (!auth()->check() || (auth()->check() && auth()->user()->getAuthIdentifier() != data_get($post, 'user_id')))
									<div class="card-body text-start">
										<div class="grid-col">
											<div class="col from">
												<i class="bi bi-geo-alt"></i>
												<span>{{ t('location') }}</span>
											</div>
											<div class="col to">
												<span>
													<a href="{!! \App\Helpers\UrlGen::city(data_get($post, 'city')) !!}">
														{{ data_get($post, 'city.name') }}
													</a>
												</span>
											</div>
										</div>
										@if (!config('settings.single.hide_dates'))
											@if (isset($user) && !empty($user) && !empty(data_get($user, 'created_at_formatted')))
											<div class="grid-col">
												<div class="col from">
													<i class="bi bi-person-check"></i>
													<span>{{ t('Joined') }}</span>
												</div>
												<div class="col to">
													<span>{!! data_get($user, 'created_at_formatted') !!}</span>
												</div>
											</div>
											@endif
										@endif
									</div>
									<?php $evActionStyle = 'style="border-top: 1px solid #ddd;"'; ?>
								@endif
								
								<div class="ev-action" {!! $evActionStyle !!}>
									@if (auth()->check())
										@if (auth()->user()->id == data_get($post, 'user_id'))
											<a href="{{ \App\Helpers\UrlGen::editPost($post) }}" class="btn btn-default btn-block">
												<i class="far fa-edit"></i> {{ t('Update the details') }}
											</a>
											@if (config('settings.single.publication_form_type') == '1')
												<a href="{{ url('posts/' . data_get($post, 'id') . '/photos') }}" class="btn btn-default btn-block">
													<i class="fas fa-camera"></i> {{ t('Update Photos') }}
												</a>
												@if (isset($countPackages) && isset($countPaymentMethods) && $countPackages > 0 && $countPaymentMethods > 0)
													<a href="{{ url('posts/' . data_get($post, 'id') . '/payment') }}" class="btn btn-success btn-block">
														<i class="far fa-check-circle"></i> {{ t('Make It Premium') }}
													</a>
												@endif
											@endif
											@if (empty(data_get($post, 'archived_at')) && isVerifiedPost($post))
												<a href="{{ url('account/posts/list/' . data_get($post, 'id') . '/offline') }}" class="btn btn-warning btn-block confirm-simple-action">
													<i class="fas fa-eye-slash"></i> {{ t('put_it_offline') }}
												</a>
											@endif
											@if (!empty(data_get($post, 'archived_at')))
												<a href="{{ url('account/posts/archived/' . data_get($post, 'id') . '/repost') }}" class="btn btn-info btn-block confirm-simple-action">
													<i class="fa fa-recycle"></i> {{ t('re_post_it') }}
												</a>
											@endif
										@else
											{!! genPhoneNumberBtn($post, true) !!}
											{!! genEmailContactBtn($post, true) !!}
										@endif
										<?php
										try {
											if (auth()->user()->can(\App\Models\Permission::getStaffPermissions())) {
												$btnUrl = admin_url('blacklists/add') . '?';
												$btnQs = (!empty(data_get($post, 'email'))) ? 'email=' . data_get($post, 'email') : '';
												$btnQs = (!empty($btnQs)) ? $btnQs . '&' : $btnQs;
												$btnQs = (!empty(data_get($post, 'phone'))) ? $btnQs . 'phone=' . data_get($post, 'phone') : $btnQs;
												$btnUrl = $btnUrl . $btnQs;
												
												if (!isDemoDomain($btnUrl)) {
													$btnText = trans('admin.ban_the_user');
													$btnHint = $btnText;
													if (!empty(data_get($post, 'email')) && !empty(data_get($post, 'phone'))) {
														$btnHint = trans('admin.ban_the_user_email_and_phone', [
															'email' => data_get($post, 'email'),
															'phone' => data_get($post, 'phone'),
														]);
													} else {
														if (!empty(data_get($post, 'email'))) {
															$btnHint = trans('admin.ban_the_user_email', ['email' => data_get($post, 'email')]);
														}
														if (!empty(data_get($post, 'phone'))) {
															$btnHint = trans('admin.ban_the_user_phone', ['phone' => data_get($post, 'phone')]);
														}
													}
													$tooltip = ' data-bs-toggle="tooltip" data-bs-placement="bottom" title="' . $btnHint . '"';
													
													$btnOut = '<a href="'. $btnUrl .'" class="btn btn-outline-danger btn-block confirm-simple-action"'. $tooltip .'>';
													$btnOut .= $btnText;
													$btnOut .= '</a>';
													
													echo $btnOut;
												}
											}
										} catch (\Throwable $e) {}
										?>
									@else
										{!! genPhoneNumberBtn($post, true) !!}
										{!! genEmailContactBtn($post, true) !!}
									@endif
								</div>
							</div>
						</div>
						
						@if (config('settings.single.show_listing_on_googlemap'))
							<?php
							$mapHeight = 250;
							$mapPlace = (!empty(data_get($post, 'city')))
								? data_get($post, 'city.name') . ',' . config('country.name')
								: config('country.name');
							$mapUrl = getGoogleMapsEmbedUrl(config('services.googlemaps.key'), $mapPlace);
							?>
							<div class="card sidebar-card">
								<div class="card-header">{{ t('location_map') }}</div>
								<div class="card-content">
									<div class="card-body text-start p-0">
										<div class="posts-googlemaps">
											<iframe id="googleMaps" width="100%" height="{{ $mapHeight }}" src="{{ $mapUrl }}"></iframe>
										</div>
									</div>
								</div>
							</div>
						@endif
						
						@if (isVerifiedPost($post))
							@includeFirst([config('larapen.core.customizedViewPath') . 'layouts.inc.social.horizontal', 'layouts.inc.social.horizontal'])
						@endif
						
						<div class="card sidebar-card">
							<div class="card-header">{{ t('Safety Tips for Buyers') }}</div>
							<div class="card-content">
								<div class="card-body text-start">
									<ul class="list-check">
										<li> {{ t('Meet seller at a public place') }} </li>
										<li> {{ t('Check the item before you buy') }} </li>
										<li> {{ t('Pay only after collecting the item') }} </li>
									</ul>
                                    <?php $tipsLinkAttributes = getUrlPageByType('tips'); ?>
                                    @if (!str_contains($tipsLinkAttributes, 'href="#"') && !str_contains($tipsLinkAttributes, 'href=""'))
									<p>
										<a class="float-end" {!! $tipsLinkAttributes !!}>
                                            {{ t('Know more') }}
                                            <i class="fa fa-angle-double-right"></i>
                                        </a>
                                    </p>
                                    @endif
								</div>
							</div>
						</div>
					</aside>
				</div>
			</div>

		</div>
		
		@if (config('settings.single.similar_listings') == '1' || config('settings.single.similar_listings') == '2')
			<?php $widgetType = (config('settings.single.similar_listings_in_carousel') ? 'carousel' : 'normal') ?>
			@includeFirst([
					config('larapen.core.customizedViewPath') . 'search.inc.posts.widget.' . $widgetType,
					'search.inc.posts.widget.' . $widgetType
				],
				['widget' => ($widgetSimilarPosts ?? null), 'firstSection' => false]
			)
		@endif
		
		@includeFirst([config('larapen.core.customizedViewPath') . 'layouts.inc.advertising.bottom', 'layouts.inc.advertising.bottom'], ['firstSection' => false])
		
		@if (isVerifiedPost($post))
			@includeFirst([config('larapen.core.customizedViewPath') . 'layouts.inc.tools.facebook-comments', 'layouts.inc.tools.facebook-comments'], ['firstSection' => false])
		@endif
		
	</div>
@endsection
<?php
if (!session()->has('emailVerificationSent') && !session()->has('phoneVerificationSent')) {
	if (session()->has('message')) {
		session()->forget('message');
	}
}
?>

@section('modal_message')
	@if (config('settings.single.show_security_tips') == '1')
		@includeFirst([config('larapen.core.customizedViewPath') . 'post.inc.security-tips', 'post.inc.security-tips'])
	@endif
	@if (auth()->check() || config('settings.single.guests_can_contact_authors')=='1')
		@includeFirst([config('larapen.core.customizedViewPath') . 'account.messenger.modal.create', 'account.messenger.modal.create'])
	@endif
@endsection

@section('after_styles')
@endsection

@section('before_scripts')
	<script>
		var showSecurityTips = '{{ config('settings.single.show_security_tips', '0') }}';
	</script>
@endsection

@section('after_scripts')
    @if (config('services.googlemaps.key'))
		{{-- More Info: https://developers.google.com/maps/documentation/javascript/versions --}}
        <script async src="https://maps.googleapis.com/maps/api/js?v=weekly&key={{ config('services.googlemaps.key') }}"></script>
    @endif
    
	<script>
		{{-- Favorites Translation --}}
        var lang = {
            labelSavePostSave: "{!! t('Save listing') !!}",
            labelSavePostRemove: "{!! t('Remove favorite') !!}",
            loginToSavePost: "{!! t('Please log in to save the Listings') !!}",
            loginToSaveSearch: "{!! t('Please log in to save your search') !!}"
        };
		
		$(document).ready(function () {
			{{-- Tooltip --}}
			var tooltipTriggerList = [].slice.call(document.querySelectorAll('[rel="tooltip"]'));
			var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
				return new bootstrap.Tooltip(tooltipTriggerEl)
			});
			
			@if (config('settings.single.show_listing_on_googlemap'))
				{{--
				let mapUrl = '{{ addslashes($mapUrl) }}';
				let iframe = document.getElementById('googleMaps');
				iframe.setAttribute('src', mapUrl);
				--}}
			@endif
			
			{{-- Keep the current tab active with Twitter Bootstrap after a page reload --}}
            $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
                /* save the latest tab; use cookies if you like 'em better: */
                /* localStorage.setItem('lastTab', $(this).attr('href')); */
				localStorage.setItem('lastTab', $(this).attr('data-bs-target'));
            });
			{{-- Go to the latest tab, if it exists: --}}
            let lastTab = localStorage.getItem('lastTab');
            if (lastTab) {
				{{-- let triggerEl = document.querySelector('a[href="' + lastTab + '"]'); --}}
				let triggerEl = document.querySelector('button[data-bs-target="' + lastTab + '"]');
				if (typeof triggerEl !== 'undefined' && triggerEl !== null) {
					let tabObj = new bootstrap.Tab(triggerEl);
					if (tabObj !== null) {
						tabObj.show();
					}
				}
            }
		});
	</script>
@endsection