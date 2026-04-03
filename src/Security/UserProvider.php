<?php

namespace App\Security;

use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProvider implements UserProviderInterface
{
    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        // Symfony appelle cette méthode pour recharger l'user depuis la session.
        // On ne peut pas recharger depuis l'API sans token,
        // donc on lève une exception — Symfony utilisera refreshUser() à la place.
        throw new UserNotFoundException();
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        // On vérifie que c'est bien notre classe User
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Invalid user class "%s".', get_class($user)));
        }

        // On retourne le même user — il est déjà en session
        return $user;
    }

    public function supportsClass(string $class): bool
    {
        return $class === User::class || is_subclass_of($class, User::class);
    }
}