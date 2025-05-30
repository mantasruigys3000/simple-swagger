<?php

namespace Mantasruigys3000\SimpleSwagger\Data;

use Mantasruigys3000\SimpleSwagger\Enums\SecuritySchemeType;

class SecurityScheme
{
    public string $name;
    public SecuritySchemeType $type;
    public string $scheme;
    public string $authorizationUrl;
    public string $tokenUrl;
    public array $scopes;

    public static function BearerAuth(string $name) : static {
        $scheme = new self();
        $scheme->name = $name;
        $scheme->type = SecuritySchemeType::HTTP;
        $scheme->scheme = 'bearer';

        return $scheme;
    }

    public static function OAuth2AuthorizationCode(
        string $name,
        string $authorizationUrl,
        string $tokenUrl,
        array $scopes
    ) : self
    {
        $scheme = new self();
        $scheme->name = $name;
        $scheme->type = SecuritySchemeType::OAUTH2;
        $scheme->scopes = $scopes;
        $scheme->authorizationUrl = $authorizationUrl;
        $scheme->tokenUrl = $tokenUrl;

        return $scheme;
    }

    public function toArray(): array
    {
        if ($this->type == SecuritySchemeType::OAUTH2) {
            return [
                'type' => $this->type->value,
                // Only supporting authorization flow at the moment
                'flows' => [
                    'authorizationCode' => [
                        'scopes'=> $this->scopes,
                        'tokenUrl' => $this->tokenUrl,
                        'authorizationUrl' => $this->authorizationUrl,
                    ],
                ]
            ];
        }

        if ($this->type === SecuritySchemeType::HTTP){
            return [
                'type' => $this->type->value,
                'scheme' => $this->scheme,
            ];
        }

        return [];
    }
}