<?php

declare(strict_types=1);

namespace Wwwision\TypesOpenAPI\Security;

use Psr\Http\Message\ServerRequestInterface;
use Wwwision\TypesOpenAPI\Http\Exception\RequestException;
use Wwwision\TypesOpenAPI\Types\SecurityRequirementObject;

interface AuthenticationContextProvider
{
    /**
     * @throws RequestException
     */
    public function getAuthenticationContext(ServerRequestInterface $request, SecurityRequirementObject $securityRequirement): AuthenticationContext|null;

}
