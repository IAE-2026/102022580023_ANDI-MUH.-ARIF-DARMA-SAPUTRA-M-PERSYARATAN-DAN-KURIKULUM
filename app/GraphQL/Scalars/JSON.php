<?php

namespace App\GraphQL\Scalars;

use GraphQL\Language\AST\Node;
use GraphQL\Type\Definition\ScalarType;
use GraphQL\Utils\Utils;

/** @phpstan-extends ScalarType<mixed> */
final class JSON extends ScalarType
{
    public string $name = 'JSON';

    public ?string $description = 'Arbitrary JSON value.';

    public function serialize(mixed $value): mixed
    {
        return $value;
    }

    public function parseValue(mixed $value): mixed
    {
        return $value;
    }

    public function parseLiteral(Node $valueNode, ?array $variables = null): mixed
    {
        return Utils::valueFromASTUntyped($valueNode, $variables);
    }
}
