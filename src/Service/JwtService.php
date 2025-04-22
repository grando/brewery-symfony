<?php

namespace App\Service;

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Validation\Constraint\IssuedBy;
use Lcobucci\JWT\Validation\Constraint\PermittedFor;
use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\Validation\Constraint\ValidAt;
use Lcobucci\JWT\UnencryptedToken;
use Lcobucci\JWT\Validation\Constraint\StrictValidAt;
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
            ->permittedFor('brewery-symfony') // Set the audience
            ->issuedBy('brewery-symfony') 
            ->issuedAt($now)
            ->canOnlyBeUsedAfter($now) // Set the "Not Before" claim
            ->expiresAt($now->modify('+1 hour'))
            ->withClaim('user', $payload)
            ->getToken($this->config->signer(), $this->config->signingKey());

        return $token->toString();
    }

    public function validateToken(string $token): ?array
    {
        try {
            $token = $this->config->parser()->parse($token);

            if (!$token instanceof UnencryptedToken) {
                throw new \InvalidArgumentException('Invalid token type.');
            }

            $constraints = [
                new PermittedFor('brewery-symfony'),
                new IssuedBy('brewery-symfony'),
                new StrictValidAt(new SystemClock(new \DateTimeZone('UTC'))),
            ];
            $this->config->validator()->assert($token, ...$constraints);            
            return $token->claims()->get('user');
        } catch (\Exception $e) {
            var_dump($e);
            return null;
        }
    }
}