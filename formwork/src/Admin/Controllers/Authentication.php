<?php

namespace Formwork\Admin\Controllers;

use Formwork\Admin\Admin;
use Formwork\Admin\Security\AccessLimiter;
use Formwork\Admin\Security\CSRFToken;
use Formwork\Core\Formwork;
use Formwork\Utils\Session;
use Formwork\Data\DataGetter;
use Formwork\Utils\HTTPRequest;

class Authentication extends AbstractController
{
    /**
     * Authentication@login action
     */
    public function login(): void
    {
        $limiter = new AccessLimiter(
            $this->admin()->registry('accessAttempts'),
            Formwork::instance()->config()->get('admin.login_attempts'),
            Formwork::instance()->config()->get('admin.login_reset_time')
        );

        if ($limiter->hasReachedLimit()) {
            $minutes = round(Formwork::instance()->config()->get('admin.login_reset_time') / 60);
            $this->error($this->admin()->translate('login.attempt.too-many', $minutes));
            return;
        }

        switch (HTTPRequest::method()) {
            case 'GET':
                if (Session::has('FORMWORK_USERNAME')) {
                    $this->admin()->redirectToPanel();
                }

                // Always generate a new CSRF token
                CSRFToken::generate();

                $this->view('authentication.login', [
                    'title' => $this->admin()->translate('login.login')
                ]);

                break;

            case 'POST':
                // Delay request processing for 0.5-1s
                usleep(random_int(500, 1000) * 1e3);

                $data = new DataGetter(HTTPRequest::postData());

                // Ensure no required data is missing
                if (!$data->hasMultiple(['username', 'password'])) {
                    $this->error($this->admin()->translate('login.attempt.failed'));
                }

                $limiter->registerAttempt();

                $user = Admin::instance()->users()->get($data->get('username'));

                // Authenticate user
                if ($user !== null && $user->authenticate($data->get('password'))) {
                    Session::set('FORMWORK_USERNAME', $data->get('username'));

                    // Regenerate CSRF token
                    CSRFToken::generate();

                    $time = $this->admin()->log('access')->log($data->get('username'));
                    $this->admin()->registry('lastAccess')->set($data->get('username'), $time);

                    $limiter->resetAttempts();

                    if (($destination = Session::get('FORMWORK_REDIRECT_TO')) !== null) {
                        Session::remove('FORMWORK_REDIRECT_TO');
                        $this->admin()->redirect($destination);
                    }

                    $this->admin()->redirectToPanel();
                }

                $this->error($this->admin()->translate('login.attempt.failed'), [
                    'username' => $data->get('username'),
                    'error'    => true
                ]);

                break;
        }
    }

    /**
     * Authentication@logout action
     */
    public function logout(): void
    {
        CSRFToken::destroy();
        Session::remove('FORMWORK_USERNAME');
        Session::destroy();

        if (Formwork::instance()->config()->get('admin.logout_redirect') === 'home') {
            $this->admin()->redirectToSite();
        } else {
            $this->admin()->notify($this->admin()->translate('login.logged-out'), 'info');
            $this->admin()->redirectToPanel();
        }
    }

    /**
     * Display login view with an error notification
     *
     * @param string $message Error message
     * @param array  $data    Data to pass to the view
     */
    protected function error(string $message, array $data = []): void
    {
        // Ensure CSRF token is re-generated
        CSRFToken::generate();

        $defaults = ['title' => $this->admin()->translate('login.login')];
        $this->admin()->notify($message, 'error');
        $this->view('authentication.login', array_merge($defaults, $data));
    }
}