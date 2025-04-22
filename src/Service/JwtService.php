<?php

namespace App\Service;

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class JwtService
{
    private Configuration $config;
    private InMemory $secretKey;

    public function __construct(ParameterBagInterface $params)
    {
        $jwtSecret = $params->get('app.jwt_secret');
        if (strlen($jwtSecret) < 32) {
            throw new \InvalidArgumentException('JWT secret must be at least 32 characters long.');
        }

        $this->secretKey = InMemory::plainText($jwtSecret);
        $this->config = Configuration::forSymmetricSigner(new Sha256(), $this->secretKey);
    }

    public function generateToken(array $payload): string
    {
        $now = new \DateTimeImmutable();
        $token = $this->config->builder()
            ->issuedBy('brewery-symfony') 
            ->issuedAt($now)
            ->expiresAt($now->modify('+1 hour'))
            ->withClaim('user', $payload)
            ->getToken($this->config->signer(), $this->config->signingKey());

        return $token->toString();
    }

    public function validateToken(string $token): ?array
    {
        try {
            $token = $this->config->parser()->parse($token);
            $constraints = [
                $this->config->constraints()->permittedFor('brewery-symfony'),
                $this->config->constraints()->issuedBy('brewery-symfony'),
                $this->config->constraints()->lessThan(new \DateTimeImmutable()),
            ];
            $this->config->validator()->assert($token, ...$constraints);
            return $token->claims()->get('user');
        } catch (\Exception $e) {
            return null;
        }
    }
}