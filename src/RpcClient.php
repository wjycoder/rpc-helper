<?php
declare(strict_types=1);

namespace Wjy\JsonrpcClient;

use Hyperf\Di\Annotation\AbstractAnnotation;

#[\Attribute(\Attribute::TARGET_CLASS)]
class RpcClient extends AbstractAnnotation
{
    public function __construct(
        public string $registerCenter = '',
        public string $address = '',
        public array $nodes = []
    )
    {
    }
}