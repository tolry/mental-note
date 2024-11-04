<?php

declare(strict_types=1);

// src/Acme/UserBundle/Entity/User.php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Repository\UserRepository;

#[ORM\Table(name: "fos_user")]
#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, EquatableInterface, \Serializable, PasswordAuthenticatedUserInterface
{
    public const ROLE_DEFAULT = 'ROLE_USER';
    public const ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';

    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    #[ORM\Id]
    protected $id;

    #[ORM\Column(type: "string")]
    protected $username;

    #[ORM\Column(type: "string")]
    protected $email;

    #[ORM\Column(type: "boolean")]
    protected $enabled;

    #[ORM\Column(type: "string")]
    protected $password;

    protected $plainPassword;

    #[ORM\Column(type: "datetime_immutable")]
    protected \DateTimeImmutable $lastLogin;

    #[ORM\Column(type: "array")]
    protected $roles;

    /**
     * User constructor.
     */
    public function __construct(string $username, string $email)
    {
        $this->username = $username;
        $this->email = $email;
        $this->lastLogin = new \DateTimeImmutable();

        $this->enabled = false;
        $this->roles = [];
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getUsername();
    }

    public function __serialize(): array
    {
        return [
            $this->password,
            $this->username,
            $this->enabled,
            $this->id,
            $this->email,
        ];
    }

    public function __unserialize(array $data): void
    {
        [
            $this->password,
            $this->username,
            $this->enabled,
            $this->id,
            $this->email,
        ] = $data;
    }

    /**
     * @internal
     */
    final public function serialize()
    {
        return serialize($this->__serialize());
    }

    /**
     * @internal
     */
    final public function unserialize($serialized)
    {
        $this->__unserialize(unserialize($serialized, ['allowed_classes' => [self::class]]));
    }

    public function addRole($role)
    {
        $role = strtoupper($role);
        if ($role === static::ROLE_DEFAULT) {
            return $this;
        }

        if (!in_array($role, $this->roles, true)) {
            $this->roles[] = $role;
        }

        return $this;
    }

    /**
     * @return void
     */
    public function eraseCredentials(): void
    {
        $this->plainPassword = null;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getUserIdentifier(): string
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    /**
     * Gets the last login time.
     *
     * @return \DateTime|null
     */
    public function getLastLogin()
    {
        return $this->lastLogin;
    }

    public function getConfirmationToken()
    {
        return $this->confirmationToken;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;

        // we need to make sure to have at least one role
        $roles[] = static::ROLE_DEFAULT;

        return array_values(array_unique($roles));
    }

    public function hasRole($role)
    {
        return in_array(strtoupper($role), $this->getRoles(), true);
    }

    public function isEnabled()
    {
        return $this->enabled;
    }

    public function isSuperAdmin()
    {
        return $this->hasRole(static::ROLE_SUPER_ADMIN);
    }

    public function removeRole($role)
    {
        if (false !== $key = array_search(strtoupper($role), $this->roles, true)) {
            unset($this->roles[$key]);
            $this->roles = array_values($this->roles);
        }

        return $this;
    }

    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    public function setEnabled($boolean)
    {
        $this->enabled = (bool) $boolean;

        return $this;
    }

    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    public function setSuperAdmin($boolean)
    {
        if (true === $boolean) {
            $this->addRole(static::ROLE_SUPER_ADMIN);
        } else {
            $this->removeRole(static::ROLE_SUPER_ADMIN);
        }

        return $this;
    }

    public function setPlainPassword($password)
    {
        $this->plainPassword = $password;

        return $this;
    }

    public function setLastLogin(\DateTime $time = null)
    {
        $this->lastLogin = $time;

        return $this;
    }

    public function setConfirmationToken($confirmationToken)
    {
        $this->confirmationToken = $confirmationToken;

        return $this;
    }

    public function setPasswordRequestedAt(\DateTime $date = null)
    {
        $this->passwordRequestedAt = $date;

        return $this;
    }

    /**
     * Gets the timestamp that the user requested a password reset.
     *
     * @return \DateTime|null
     */
    public function getPasswordRequestedAt()
    {
        return $this->passwordRequestedAt;
    }

    public function isPasswordRequestNonExpired($ttl)
    {
        return $this->getPasswordRequestedAt() instanceof \DateTime
            && $this->getPasswordRequestedAt()->getTimestamp() + $ttl > time();
    }

    public function setRoles(array $roles)
    {
        $this->roles = [];

        foreach ($roles as $role) {
            $this->addRole($role);
        }

        return $this;
    }

    public function isEqualTo(UserInterface $user): bool
    {
        if (!$user instanceof self) {
            return false;
        }

        if ($this->password !== $user->getPassword()) {
            return false;
        }

        if ($this->username !== $user->getUsername()) {
            return false;
        }

        return true;
    }
}
