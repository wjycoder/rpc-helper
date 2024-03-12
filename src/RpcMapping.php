<?php
declare(strict_types=1);

namespace Wjy\RpcHelper;

use Hyperf\Di\Annotation\AbstractAnnotation;

#[\Attribute(\Attribute::TARGET_METHOD)]
class RpcMapping extends AbstractAnnotation
{
    public function __construct(
        public string $path = '',
        public string $method = 'POST'
    )
    {
    }
}