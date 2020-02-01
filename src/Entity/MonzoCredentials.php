<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MonzoCredentialsRepository")
 */
class MonzoCredentials
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=2048)
     */
    private $accessToken;

    /**
     * @ORM\Column(type="string", length=2048)
     */
    private $refreshToken;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $clientId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $scope;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $tokenType;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $monzoUserId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $userId;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    public function setAccessToken(string $accessToken): self
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    public function getRefreshToken(): ?string
    {
        return $this->refreshToken;
    }

    public function setRefreshToken(string $refreshToken): self
    {
        $this->refreshToken = $refreshToken;

        return $this;
    }

    public function getClientId(): ?string
    {
        return $this->clientId;
    }

    public function setClientId(string $clientId): self
    {
        $this->clientId = $clientId;

        return $this;
    }

    public function getScope(): ?string
    {
        return $this->scope;
    }

    public function setScope(string $scope): self
    {
        $this->scope = $scope;

        return $this;
    }

    public function getTokenType(): ?string
    {
        return $this->tokenType;
    }

    public function setTokenType(string $tokenType): self
    {
        $this->tokenType = $tokenType;

        return $this;
    }

    public function getMonzoUserId(): ?string
    {
        return $this->monzoUserId;
    }

    public function setMonzoUserId(string $monzoUserId): self
    {
        $this->monzoUserId = $monzoUserId;

        return $this;
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function setUserId(string $userId): self
    {
        $this->userId = $userId;

        return $this;
    }
}
