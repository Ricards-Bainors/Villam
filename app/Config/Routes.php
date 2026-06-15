<?php

namespace Config;

$routes = Services::routes();

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

$routes->get('/', 'AuthController::showLoginForm');

$routes->group('', ['filter' => 'auth'], function ($routes) {
    $routes->get('users/search', 'UserController::search');
    $routes->get('users/search-json', 'UserController::searchJson');

    $routes->get('posts', 'PostController::index');
    $routes->get('post/fetch', 'PostController::fetch');
    $routes->post('post/add', 'PostController::add');
    $routes->get('post/edit/(:num)', 'PostController::edit/$1');
    $routes->delete('post/delete/(:num)', 'PostController::delete/$1');
    $routes->get('post/detail/(:num)', 'PostController::detail/$1');
    $routes->post('post/update', 'PostController::update');
    $routes->get('categories', 'PostController::categories');
    $routes->get('categories/fetch', 'PostController::fetchCategories');
    $routes->post('categories/add', 'PostController::add_category');
    $routes->post('categories/update', 'PostController::update_category');
    $routes->post('categories/delete/(:num)', 'PostController::delete_category/$1');
    $routes->get('admin/users', 'PostController::users');
    $routes->get('admin/users/fetch', 'PostController::fetchUsers');
    $routes->post('admin/users/update', 'PostController::updateUser');
    $routes->delete('admin/users/delete/(:num)', 'PostController::deleteUser/$1');

    $routes->get('advertisements', 'AdvertisementController::index');
    $routes->get('advertisements/fetch', 'AdvertisementController::fetch');
    $routes->post('advertisements/add', 'AdvertisementController::add');
    $routes->post('advertisements/update', 'AdvertisementController::update');
    $routes->delete('advertisements/delete/(:num)', 'AdvertisementController::delete/$1');
    $routes->get('advertisements/detail/(:num)', 'AdvertisementController::detail/$1');

    $routes->get('messages', 'MessageController::index');
    $routes->get('messages/list', 'MessageController::inbox');
    $routes->post('messages/start/(:num)', 'MessageController::start/$1');
    $routes->get('messages/thread/(:num)', 'MessageController::thread/$1');
    $routes->post('messages/send', 'MessageController::send');

    $routes->get('forum', 'ForumController::index');
    $routes->get('forum/fetch', 'ForumController::fetch');
    $routes->post('forum/add', 'ForumController::add');
    $routes->post('forum/update', 'ForumController::update');
    $routes->delete('forum/delete/(:num)', 'ForumController::delete/$1');
    $routes->get('forum/detail/(:num)', 'ForumController::detail/$1');
    $routes->get('forum/replies/(:num)', 'ForumController::replies/$1');
    $routes->post('forum/reply/add', 'ForumController::addReply');
    $routes->delete('forum/reply/delete/(:num)', 'ForumController::deleteReply/$1');

    $routes->post('post/like', 'PostController::like');
    $routes->get('post/comments/(:num)', 'PostController::comments/$1');
    $routes->post('post/comment/add', 'PostController::addComment');
    $routes->delete('post/comment/delete/(:num)', 'PostController::deleteComment/$1');

    $routes->get('profile', 'ProfileController::index');
    $routes->get('profile/settings', 'ProfileController::settings');
    $routes->post('profile/photo', 'ProfileController::updatePhoto');
    $routes->post('profile/password', 'ProfileController::updatePassword');
    $routes->post('profile/email', 'ProfileController::updateEmail');
});
$routes->get('auth/register', 'AuthController::showRegisterForm');
$routes->post('auth/register', 'AuthController::register');

$routes->get('auth/login', 'AuthController::showLoginForm');
$routes->post('auth/login', 'AuthController::login');
$routes->get('login', 'AuthController::showLoginForm');
$routes->post('login', 'AuthController::login');

$routes->get('auth/logout', 'AuthController::logout');

$routes->set404Override(function () {
    echo view('errors/404');
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
