<?php declare(strict_types=1);

namespace Tests\Auxiliary;

/**
 * @deprecated only for tests
 */
class A
{
    public string $id;
    public string $desc = '';
    /** @var B[] */
    public array $b_s = [];
}
