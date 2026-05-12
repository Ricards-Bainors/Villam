<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

// Load the system's routing file first, so that the app and ENVIRONMENT
// can override as needed.
if (file_exists(SYSTEMPATH . 'Config/Routes.php')) {
    require SYSTEMPATH . 'Config/Routes.php';
}

/*
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('PostController');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(false);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
$routes->get('/', 'AuthController::showLoginForm'); // Default route
$routes->group('', ['filter' => 'auth'], function ($routes) {
    $routes->get('posts', 'PostController::index'); // Display all posts
    // Other protected routes...
    $routes->get('post/fetch', 'PostController::fetch'); // Fetch posts (e.g., via AJAX)
    $routes->post('post/add', 'PostController::add'); // Add a new post
    $routes->get('post/edit/(:num)', 'PostController::edit/$1'); // Edit a post
    $routes->delete('post/delete/(:num)', 'PostController::delete/$1'); // Delete a post
    $routes->get('post/detail/(:num)', 'PostController::detail/$1'); // View post details
    $routes->post('post/update', 'PostController::update'); // Update a post
    $routes->get('categories', 'PostController::categories'); // Display categories
    $routes->get('categories/fetch', 'PostController::fetchCategories'); // Fetch categories
    $routes->post('categories/add', 'PostController::add_category'); // Add a category
    $routes->post('categories/update', 'PostController::update_category'); // Update a category
    $routes->post('categories/delete/(:num)', 'PostController::delete_category/$1'); // Delete a category

    $routes->get('advertisements', 'AdvertisementController::index');
    $routes->get('advertisements/fetch', 'AdvertisementController::fetch');
    $routes->post('advertisements/add', 'AdvertisementController::add');
    $routes->get('advertisements/detail/(:num)', 'AdvertisementController::detail/$1');

    // Forum (Discussions)
    $routes->get('forum', 'ForumController::index');
    $routes->get('forum/fetch', 'ForumController::fetch');
    $routes->post('forum/add', 'ForumController::add');
    $routes->get('forum/detail/(:num)', 'ForumController::detail/$1');
    $routes->get('forum/replies/(:num)', 'ForumController::replies/$1');
    $routes->post('forum/reply/add', 'ForumController::addReply');
    $routes->delete('forum/reply/delete/(:num)', 'ForumController::deleteReply/$1');

    $routes->post('post/like', 'PostController::like');
    $routes->get('post/comments/(:num)', 'PostController::comments/$1');
    $routes->post('post/comment/add', 'PostController::addComment');
    $routes->delete('post/comment/delete/(:num)', 'PostController::deleteComment/$1');

    $routes->get('profile', 'ProfileController::index');
});
$routes->get('auth/register', 'AuthController::showRegisterForm'); // Registration form
$routes->post('auth/register', 'AuthController::register'); // Process registration

$routes->get('auth/login', 'AuthController::showLoginForm'); // Login form
$routes->post('auth/login', 'AuthController::login'); // Process login

$routes->get('auth/logout', 'AuthController::logout'); // Logout

$routes->set404Override(function () {
    echo view('errors/404'); // Custom 404 error page
});

/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (file_exists(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}