<?php

class User
{
    private string $name;
    private string $email;
    private string $password;
    private string $role;

    public function __construct(string $name, string $email, string $password, string $role = 'user')
    {
        $this->name = $name;
        $this->email = $email;
        $this->password = $this->hashPassword($password);
        $this->role = $role;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name)
    {
        $this->name = $name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email)
    {
        $this->email = $email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password)
    {
        $this->name = $password;
    }

    private function hashPassword(string $plainPassword): string
    {
        $peppered = hash_hmac("sha256", $plainPassword, PEPPER);
        return password_hash($peppered, PASSWORD_BCRYPT, ['cost' => 11]);
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function setRole(string $role)
    {
        $this->role = $role;
    }
}