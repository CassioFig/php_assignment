<?php

namespace Classes;

use JsonSerializable;

class User implements JsonSerializable
{
    private int $id;
    private string $name;
    private string $email;
    private string $password;
    private string $role;

    public function __construct(?int $id, string $name, string $email, ?string $password = null, string $role = 'User')
    {
        if ($id) {
            $this->id = $id;
        }
        $this->name = $name;
        $this->email = $email;
        if ($password){
            $this->password = $password;
        }
        $this->role = $role;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id)
    {
        $this->id = $id;
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
        $this->password = $this->hashPassword($password);
    }

    private function hashPassword(string $plainPassword): string
    {
        $peppered = hash_hmac("sha256", $plainPassword, PEPPER);
        return password_hash($peppered, PASSWORD_BCRYPT, ['cost' => 11]);
    }

    public function getRole(): string
    {
        return $this->role ?? 'User';
    }

    public function setRole(string $role)
    {
        $this->role = $role;
    }

    public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role
        ];
	}
}