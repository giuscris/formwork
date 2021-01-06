<?php

namespace Formwork\Admin;

use Formwork\Admin\Security\CSRFToken;
use Formwork\Admin\Users\User;
use Formwork\Admin\Users\Users;
use Formwork\Assets;
use Formwork\Formwork;
use Formwork\Languages\LanguageCodes;
use Formwork\Page;
use Formwork\Response\JSONResponse;
use Formwork\Response\RedirectResponse;
use Formwork\Response\Response;
use Formwork\Router\RouteParams;
use Formwork\Router\Router;
use Formwork\Translations\Translation;
use Formwork\Utils\FileSystem;
use Formwork\Utils\HTTPRequest;
use Formwork\Utils\Notification;
use Formwork\Utils\Session;
use Formwork\Utils\Str;
use Formwork\Utils\Uri;
use RuntimeException;
use Throwable;

final class Admin
{
    /**
     * Router instance
     *
     * @var Router
     */
    protected $router;

    /**
     * All the registered users
     *
     * @var Users
     */
    protected $users;

    /**
     * Errors controller
     *
     * @var Controllers\ErrorsController
     */
    protected $errors;

    /**
     * Assets instance
     *
     * @var Assets
     */
    protected $assets;

    /**
     * Create a new Admin instance
     */
    public function __construct()
    {
        $this->router = new Router(Uri::removeQuery($this->route()));
    }

    /**
     * Return whether a user is logged in
     */
    public function isLoggedIn(): bool
    {
        $username = Session::get('FORMWORK_USERNAME');
        return !empty($username) && $this->users->has($username);
    }

    /**
     * Return all registered users
     */
    public function users(): Users
    {
        return $this->users;
    }

    /**
     * Return currently logged in user
     */
    public function user(): User
    {
        $username = Session::get('FORMWORK_USERNAME');
        return $this->users->get($username);
    }

    /**
     * Run the administration panel
     */
    public function run(): Response
    {
        $this->loadSchemes();

        $this->users = Users::load();

        $this->loadTranslations();
        $this->loadErrorHandler();

        $this->loadRoutes();

        if (HTTPRequest::method() === 'POST') {
            // Validate HTTP request Content-Length according to post_max_size directive
            if (HTTPRequest::contentLength() !== null) {
                $maxSize = FileSystem::shorthandToBytes(ini_get('post_max_size'));
                if (HTTPRequest::contentLength() > $maxSize && $maxSize > 0) {
                    $this->notify($this->translate('admin.request.error.post-max-size'), 'error');
                    return $this->redirectToReferer();
                }
            }

            // Validate CSRF token
            try {
                CSRFToken::validate();
            } catch (RuntimeException $e) {
                CSRFToken::destroy();
                Session::remove('FORMWORK_USERNAME');
                $this->notify($this->translate('admin.login.suspicious-request-detected'), 'warning');
                if (HTTPRequest::isXHR()) {
                    return JSONResponse::error('Bad Request: the CSRF token is not valid', 400);
                }
                return $this->redirect('/login/');
            }
        }

        if ($this->users->isEmpty()) {
            return $this->registerAdmin();
        }

        if (!$this->isLoggedIn() && $this->route() !== '/login/') {
            Session::set('FORMWORK_REDIRECT_TO', $this->route());
            return $this->redirect('/login/');
        }

        $response = $this->router->dispatch();

        if (!$this->router->hasDispatched()) {
            $response = $this->errors->notFound();
        }

        return $response;
    }

    /**
     * Return a URI relative to the request root
     */
    public function uri(string $route): string
    {
        return $this->panelUri() . ltrim($route, '/');
    }

    /**
     * Return a URI relative to the real Admin root
     */
    public function realUri(string $route): string
    {
        return HTTPRequest::root() . 'admin/' . ltrim($route, '/');
    }

    /**
     * Get the URI of the site
     */
    public function siteUri(): string
    {
        return HTTPRequest::root();
    }

    /**
     * Return panel root
     */
    public function panelRoot(): string
    {
        return Uri::normalize(Formwork::instance()->config()->get('admin.root'));
    }

    /**
     * Get the URI of the panel
     */
    public function panelUri(): string
    {
        return HTTPRequest::root() . ltrim($this->panelRoot(), '/');
    }

    /**
     * Return the URI of a page
     *
     * @param bool|string $includeLanguage
     */
    public function pageUri(Page $page, $includeLanguage = true): string
    {
        $base = $this->siteUri();
        if ($includeLanguage) {
            $language = is_string($includeLanguage) ? $includeLanguage : $page->language();
            if ($language !== null) {
                $base .= $language . '/';
            }
        }
        return $base . ltrim($page->route(), '/');
    }

    /**
     * Return current route
     */
    public function route(): string
    {
        return '/' . Str::removeStart(HTTPRequest::uri(), $this->panelRoot());
    }

    /**
     * Redirect to a given route
     *
     * @param int $code HTTP redirect status code
     */
    public function redirect(string $route, int $code = 302): RedirectResponse
    {
        return new RedirectResponse($this->uri($route), $code);
    }

    /**
     * Redirect to the site index page
     *
     * @param int $code HTTP redirect status code
     */
    public function redirectToSite(int $code = 302): RedirectResponse
    {
        return new RedirectResponse($this->siteUri(), $code);
    }

    /**
     * Redirect to the administration panel
     *
     * @param int $code HTTP redirect status code
     */
    public function redirectToPanel(int $code = 302): RedirectResponse
    {
        return $this->redirect('/', $code);
    }

    /**
     * Redirect to the referer page
     *
     * @param int    $code    HTTP redirect status code
     * @param string $default Default route if HTTP referer is not available
     */
    public function redirectToReferer(int $code = 302, string $default = '/'): RedirectResponse
    {
        if (HTTPRequest::validateReferer($this->uri('/')) && HTTPRequest::referer() !== Uri::current()) {
            return new RedirectResponse(HTTPRequest::referer(), $code);
        }
        return new RedirectResponse($this->uri($default), $code);
    }

    /**
     * Send a notification
     */
    public function notify(string $text, string $type = Notification::INFO): void
    {
        Notification::send($text, $type);
    }

    /**
     * Get notification from session data
     */
    public function notification(): ?array
    {
        return Notification::exists() ? Notification::get() : null;
    }

    /**
     * Get a translation
     */
    public function translate(...$arguments)
    {
        return Formwork::instance()->translations()->getCurrent()->translate(...$arguments);
    }

    /**
     * Get Assets instance
     */
    public function assets(): Assets
    {
        if ($this->assets !== null) {
            return $this->assets;
        }
        return $this->assets = new Assets(ADMIN_PATH . 'assets' . DS, Formwork::instance()->admin()->realUri('/assets/'));
    }

    /**
     * Available translations helper
     */
    public static function availableTranslations(): array
    {
        static $translations = [];

        if (!empty($translations)) {
            return $translations;
        }

        $path = Formwork::instance()->config()->get('translations.paths.admin');

        foreach (FileSystem::listFiles($path) as $file) {
            if (FileSystem::extension($file) === 'yml') {
                $code = FileSystem::name($file);
                $translations[$code] = LanguageCodes::codeToNativeName($code) . ' (' . $code . ')';
            }
        }

        ksort($translations);

        return $translations;
    }

    /**
     * Load proper panel translation
     */
    protected function loadTranslations(): void
    {
        $languageCode = Formwork::instance()->config()->get('admin.lang');
        if ($this->isLoggedIn()) {
            $languageCode = $this->user()->language();
        }
        $path = Formwork::instance()->config()->get('translations.paths.admin');
        Formwork::instance()->translations()->loadFromPath($path);
        Formwork::instance()->translations()->setCurrent($languageCode);
    }

    protected function loadSchemes(): void
    {
        $path = Formwork::instance()->config()->get('schemes.paths.admin');
        Formwork::instance()->schemes()->loadFromPath('admin', $path);
    }

    /**
     * Load the panel-styled error handler
     */
    protected function loadErrorHandler(): void
    {
        $this->errors = new Controllers\ErrorsController();
        set_exception_handler(function (Throwable $exception): void {
            $this->errors->internalServerError($exception)->send();
            throw $exception;
        });
    }

    /**
     * Register administration panel if no user exists
     */
    protected function registerAdmin(): Response
    {
        if (!HTTPRequest::isLocalhost()) {
            return $this->redirectToSite();
        }
        if ($this->router->request() !== '/') {
            return $this->redirectToPanel();
        }
        $controller = new Controllers\RegisterController();
        return $controller->register();
    }

    /**
     * Load administration panel routes
     */
    protected function loadRoutes(): void
    {
        // Default route
        $this->router->add(
            '/',
            function (RouteParams $params): Response {
                return $this->redirect('/dashboard/');
            }
        );

        // Authentication
        $this->router->add(
            '/login/',
            Controllers\AuthenticationController::class . '@login',
            ['GET', 'POST']
        );
        $this->router->add(
            '/logout/',
            Controllers\AuthenticationController::class . '@logout'
        );

        // Backup
        $this->router->add(
            '/backup/make/',
            Controllers\BackupController::class . '@make',
            'POST',
            'XHR'
        );
        $this->router->add(
            '/backup/download/{backup}/',
            Controllers\BackupController::class . '@download',
            'POST'
        );

        // Cache
        $this->router->add(
            '/cache/clear/',
            Controllers\CacheController::class . '@clear',
            'POST',
            'XHR'
        );

        // Dashboard
        $this->router->add(
            '/dashboard/',
            Controllers\DashboardController::class . '@index'
        );

        // Options
        $this->router->add(
            '/options/',
            Controllers\OptionsController::class . '@index'
        );
        $this->router->add(
            '/options/system/',
            Controllers\OptionsController::class . '@systemOptions',
            ['GET', 'POST']
        );
        $this->router->add(
            '/options/site/',
            Controllers\OptionsController::class . '@siteOptions',
            ['GET', 'POST']
        );
        $this->router->add(
            '/options/updates/',
            Controllers\OptionsController::class . '@updates'
        );
        $this->router->add(
            '/options/info/',
            Controllers\OptionsController::class . '@info'
        );

        // Pages
        $this->router->add(
            '/pages/',
            Controllers\PagesController::class . '@index'
        );
        $this->router->add(
            '/pages/new/',
            Controllers\PagesController::class . '@create',
            'POST'
        );
        $this->router->add(
            [
                '/pages/{page}/edit/',
                '/pages/{page}/edit/language/{language}/'
            ],
            Controllers\PagesController::class . '@edit',
            ['GET', 'POST']
        );
        $this->router->add(
            '/pages/reorder/',
            Controllers\PagesController::class . '@reorder',
            'POST',
            'XHR'
        );
        $this->router->add(
            '/pages/{page}/file/upload/',
            Controllers\PagesController::class . '@uploadFile',
            'POST'
        );
        $this->router->add(
            '/pages/{page}/file/{filename}/delete/',
            Controllers\PagesController::class . '@deleteFile',
            'POST'
        );
        $this->router->add(
            [
                '/pages/{page}/delete/',
                '/pages/{page}/delete/language/{language}/',
            ],
            Controllers\PagesController::class . '@delete',
            'POST'
        );

        // Updates
        $this->router->add(
            '/updates/check/',
            Controllers\UpdatesController::class . '@check',
            'POST',
            'XHR'
        );
        $this->router->add(
            '/updates/update/',
            Controllers\UpdatesController::class . '@update',
            'POST',
            'XHR'
        );

        // Users
        $this->router->add(
            '/users/',
            Controllers\UsersController::class . '@index'
        );
        $this->router->add(
            '/users/new/',
            Controllers\UsersController::class . '@create',
            'POST'
        );
        $this->router->add(
            '/users/{user}/delete/',
            Controllers\UsersController::class . '@delete',
            'POST'
        );
        $this->router->add(
            '/users/{user}/profile/',
            Controllers\UsersController::class . '@profile',
            ['GET', 'POST']
        );
    }
}
