<?php
use Illuminate\Support\Facades\Route;

// Categories' Listings Pages
$noIndexCategoriesPermalinkPages = (
	config('settings.seo.no_index_categories')
	&& str_contains(Route::currentRouteAction(), 'Web\Search\CategoryController')
);
$noIndexCategoriesQueryStringPages = (
	config('settings.seo.no_index_categories_qs')
	&& str_contains(Route::currentRouteAction(), 'Web\Search\SearchController')
	&& (isset($cat) && !empty($cat))
);

// Cities' Listings Pages
$noIndexCitiesPermalinkPages = (
	config('settings.seo.no_index_cities')
	&& str_contains(Route::currentRouteAction(), 'Web\Search\CityController')
);
$noIndexCitiesQueryStringPages = (
	config('settings.seo.no_index_cities_qs')
	&& str_contains(Route::currentRouteAction(), 'Web\Search\SearchController')
	&& (isset($city) && !empty($city))
);

// Users' Listings Pages
$noIndexUsersByIdPages = (
	config('settings.seo.no_index_users')
	&& str_contains(Route::currentRouteAction(), 'Web\Search\UserController@index')
);
$noIndexUsersByUsernamePages = (
	config('settings.seo.no_index_users_username')
	&& str_contains(Route::currentRouteAction(), 'Web\Search\UserController@profile')
);

// Tags' Listings Pages
$noIndexTagsPages = (
	config('settings.seo.no_index_tags')
	&& str_contains(Route::currentRouteAction(), 'Web\Search\TagController')
);

// Filters (and Orders) on Listings Pages (Except Pagination)
$noIndexFiltersOnEntriesPages = (
	config('settings.seo.no_index_filters_orders')
	&& str_contains(Route::currentRouteAction(), 'Web\Search\\')
	&& !empty(request()->except(['page']))
);

// "No result" Pages (Empty Searches Results Pages)
$noIndexNoResultPages = (
	config('settings.seo.no_index_no_result')
	&& str_contains(Route::currentRouteAction(), 'Web\Search\\')
	&& (
		isset($posts)
		&& $posts instanceof Illuminate\Pagination\LengthAwarePaginator
		&& $posts->count() <= 0
	)
);

// Listings Report Pages
$noIndexListingsReportPages = (
	config('settings.seo.no_index_listing_report')
	&& str_contains(Route::currentRouteAction(), 'Web\Post\ReportController')
);

// All Website Pages
$noIndexAllPages = (config('settings.seo.no_index_all'));
?>
@if (
		$noIndexAllPages
		|| $noIndexCategoriesPermalinkPages
		|| $noIndexCategoriesQueryStringPages
		|| $noIndexCitiesPermalinkPages
		|| $noIndexCitiesQueryStringPages
		|| $noIndexUsersByIdPages
		|| $noIndexUsersByUsernamePages
		|| $noIndexTagsPages
		|| $noIndexFiltersOnEntriesPages
		|| $noIndexNoResultPages
		|| $noIndexListingsReportPages
	)
	<meta name="robots" content="noindex,nofollow">
	<meta name="googlebot" content="noindex">
@endif