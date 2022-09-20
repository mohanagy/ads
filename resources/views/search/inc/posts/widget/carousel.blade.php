<?php
$widget ??= [];
$posts = (array)data_get($widget, 'posts');
$totalPosts = (int)data_get($widget, 'totalPosts', 0);

$sectionOptions ??= [];
$hideOnMobile = (data_get($sectionOptions, 'hide_on_mobile') == '1') ? ' hidden-sm' : '';
$carouselEl = '_' . createRandomString(6);
?>
@if ($totalPosts > 0)
	<?php
	$isFromHome = (str_contains(Illuminate\Support\Facades\Route::currentRouteAction(), 'Web\HomeController'));
	?>
	@if ($isFromHome)
		@includeFirst([config('larapen.core.customizedViewPath') . 'home.inc.spacer', 'home.inc.spacer'], ['hideOnMobile' => $hideOnMobile])
	@endif
	<div class="container{{ $isFromHome ? '' : ' my-3' }}{{ $hideOnMobile }}">
		<div class="col-xl-12 content-box layout-section">
			<div class="row row-featured row-featured-category">
				<div class="col-xl-12 box-title">
					<div class="inner">
						<h2>
							<span class="title-3">{!! data_get($widget, 'title') !!}</span>
							<a href="{{ data_get($widget, 'link') }}" class="sell-your-item">
								{{ t('View more') }} <i class="fas fa-bars"></i>
							</a>
						</h2>
					</div>
				</div>
		
				<div style="clear: both"></div>
		
				<div class="relative content featured-list-row clearfix">
					
					<div class="large-12 columns">
						<div class="no-margin featured-list-slider {{ $carouselEl }} owl-carousel owl-theme">
							@foreach($posts as $key => $post)
								<?php
								// Main Picture
								$defaultImg = config('larapen.core.picture.default');
								$postImg = data_get($post, 'pictures.0.filename', $defaultImg);
								?>
								<div class="item">
									<a href="{{ \App\Helpers\UrlGen::post($post) }}">
										<span class="item-carousel-thumb">
											<span class="photo-count">
												<i class="fa fa-camera"></i> {{ count((array)data_get($post, 'pictures')) }}
											</span>
											{!! imgTag($postImg, 'medium', ['style' => 'border: 1px solid #e7e7e7; margin-top: 2px;', 'alt' => data_get($post, 'title')]) !!}
										</span>
										<span class="item-name">{{ str(data_get($post, 'title'))->limit(70) }}</span>
										
										@if (config('plugins.reviews.installed'))
											@if (view()->exists('reviews::ratings-list'))
												@include('reviews::ratings-list')
											@endif
										@endif
										
										<span class="price">
											@if (!empty(data_get($post, 'category.type')))
												<?php $postPrice = data_get($post, 'price'); ?>
												@if (!in_array(data_get($post, 'category.type'), ['not-salable']))
													@if (is_numeric($postPrice) && $postPrice > 0)
														{!! \App\Helpers\Number::money($postPrice) !!}
													@elseif(is_numeric($postPrice) && $postPrice == 0)
														{!! t('free_as_price') !!}
													@else
														{!! t('Contact us') !!}
													@endif
												@endif
											@else
												{!! t('Contact us') !!}
											@endif
										</span>
									</a>
								</div>
							@endforeach
						</div>
					</div>
		
				</div>
			</div>
		</div>
	</div>
@endif

@section('after_style')
	@parent
@endsection

@section('after_scripts')
	@parent
	<script>
		{{-- Check if RTL or LTR --}}
		var rtlIsEnabled = false;
		if ($('html').attr('dir') === 'rtl') {
			rtlIsEnabled = true;
		}
		
		{{-- Carousel Parameters --}}
		var carouselItems = {{ $totalPosts ?? 0 }};
		var carouselAutoplay = {{ data_get($sectionOptions, 'autoplay') ?? 'false' }};
		var carouselAutoplayTimeout = {{ (int)(data_get($sectionOptions, 'autoplay_timeout') ?? 1500) }};
		var carouselLang = {
			'navText': {
				'prev': "{{ t('prev') }}",
				'next': "{{ t('next') }}"
			}
		};
		
		{{-- Featured Listings Carousel --}}
		var carouselObject = $('.featured-list-slider.{{ $carouselEl }}');
		var responsiveObject = {
			0: {
				items: 1,
				nav: true
			},
			576: {
				items: 2,
				nav: false
			},
			768: {
				items: 3,
				nav: false
			},
			992: {
				items: 5,
				nav: false,
				loop: (carouselItems > 5)
			}
		};
		carouselObject.owlCarousel({
			rtl: rtlIsEnabled,
			nav: false,
			navText: [carouselLang.navText.prev, carouselLang.navText.next],
			loop: true,
			responsiveClass: true,
			responsive: responsiveObject,
			autoWidth: true,
			autoplay: carouselAutoplay,
			autoplayTimeout: carouselAutoplayTimeout,
			autoplayHoverPause: true
		});
	</script>
@endsection