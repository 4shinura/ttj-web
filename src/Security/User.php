<?php

namespace App\Security;

use Symfony\Component\Security\Core\User\UserInterface;

class User implements UserInterface
{
    private int $id;
    private string $email;
    private string $nom;
    private string $prenom;
    private array $roles;

    public function __construct(int $id, string $email, string $nom, string $prenom, array $roles = ['ROLE_USER'])
    {
        $this->id      = $id;
        $this->email   = $email;
        $this->nom     = $nom;
        $this->prenom  = $prenom;
        $this->roles   = $roles;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getNomUtilisateur(): string
    {
        return $this->nom;
    }

    public function getPrenomUtilisateur(): string
    {
        return $this->prenom;
    }

    // --- Méthodes imposées par UserInterface ---

    public function getUserIdentifier(): string
    {
        // Ce que Symfony utilise pour identifier l'utilisateur (ici l'email)
        return $this->email;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function eraseCredentials(): void
    {
        // On ne stocke pas le mot de passe en session, donc rien à faire
    }
}