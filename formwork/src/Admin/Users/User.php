<?php

namespace Formwork\Admin\Users;

use Formwork\Admin\Security\Password;
use Formwork\Formwork;
use Formwork\Utils\Registry;
use Formwork\Utils\Session;

class User
{
    /**
     * Array containing user data
     */
    protected array $data = [];

    /**
     * Default data of the user
     */
    protected array $defaults = [
        'username'     => null,
        'fullname'     => null,
        'hash'         => null,
        'email'        => null,
        'language'     => 'en',
        'role'         => 'user',
        'avatar'       => null,
        'color-scheme' => 'auto'
    ];

    /**
     * User username
     */
    protected string $username;

    /**
     * User full name
     */
    protected string $fullname;

    /**
     * User password hash
     */
    protected string $hash;

    /**
     * User email
     */
    protected string $email;

    /**
     * User language
     */
    protected string $language;

    /**
     * User role
     */
    protected string $role;

    /**
     * User avatar
     */
    protected Avatar $avatar;

    /**
     * User permissions
     */
    protected Permissions $permissions;

    /**
     * User last access time
     */
    protected ?int $lastAccess;

    /**
     * Create a new User instance
     */
    public function __construct(array $data)
    {
        $this->data = array_merge($this->defaults, $data);
        foreach (['username', 'fullname', 'hash', 'email', 'language', 'role'] as $var) {
            $this->$var = $this->data[$var];
        }

        $this->avatar = new Avatar($this->data['avatar']);
        $this->permissions = new Permissions($this->role);
    }

    /**
     * Return the username
     */
    public function username(): string
    {
        return $this->username;
    }

    /**
     * Return the full name
     */
    public function fullname(): string
    {
        return $this->fullname;
    }

    /**
     * Return the email
     */
    public function email(): string
    {
        return $this->email;
    }

    /**
     * Return the language code
     */
    public function language(): string
    {
        return $this->language;
    }

    /**
     * Return the role
     */
    public function role(): string
    {
        return $this->role;
    }

    /**
     * Return user avatar
     */
    public function avatar(): Avatar
    {
        return $this->avatar;
    }

    /**
     * Return user permissions
     */
    public function permissions(): Permissions
    {
        return $this->permissions;
    }

    /**
     * Return whether a given password authenticates the user
     */
    public function authenticate(string $password): bool
    {
        return Password::verify($password, $this->hash);
    }

    /**
     * Return whether the user is logged or not
     */
    public function isLogged(): bool
    {
        return Session::get('FORMWORK_USERNAME') === $this->username;
    }

    /**
     * Return whether the user has 'admin' role
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Return whether the user can delete a given user
     */
    public function canDeleteUser(User $user): bool
    {
        return $this->isAdmin() && !$user->isLogged();
    }

    /**
     * Return whether the user can change options of a given user
     */
    public function canChangeOptionsOf(User $user): bool
    {
        return $this->isAdmin() || $user->isLogged();
    }

    /**
     * Return whether the user can change the password of a given user
     */
    public function canChangePasswordOf(User $user): bool
    {
        return $this->isAdmin() || $user->isLogged();
    }

    /**
     * Return whether the user can change the role of a given user
     */
    public function canChangeRoleOf(User $user): bool
    {
        return $this->isAdmin() && !$user->isLogged();
    }

    /**
     * Get the user last access time
     */
    public function lastAccess(): ?int
    {
        if (isset($this->lastAccess)) {
            return $this->lastAccess;
        }
        $lastAccessRegistry = new Registry(Formwork::instance()->config()->get('admin.paths.logs') . 'lastAccess.json');
        $lastAccess = (int) $lastAccessRegistry->get($this->username);
        return $this->lastAccess = $lastAccess ?: null;
    }

    /**
     * Get the user color scheme preference
     */
    public function colorScheme(): string
    {
        return $this->data['color-scheme'];
    }

    /**
     * Return an array containing user data
     */
    public function toArray(): array
    {
        return $this->data;
    }

    public function __debugInfo(): array
    {
        $data = $this->data;
        // Unset hash to avoid exposure
        unset($data['hash']);
        return $data;
    }
}
