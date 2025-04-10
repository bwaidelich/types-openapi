<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenApi\Security;

use Psr\Http\Message\ServerRequestInterface;
use Wwwision\TypesOpenApi\Http\Exception\RequestException;
use Wwwision\TypesOpenApi\Types\SecurityRequirementObject;

interface AuthenticationContextProvider
{
    /**
     * @throws RequestException
     */
    public function getAuthenticationContext(ServerRequestInterface $request, SecurityRequirementObject $securityRequirement): AuthenticationContext|null;

}
