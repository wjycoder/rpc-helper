<?php
declare(strict_types=1);

namespace Wjy\RpcHelper;

use Hyperf\Di\Annotation\AbstractAnnotation;

#[\Attribute(\Attribute::TARGET_CLASS)]
class RpcController extends AbstractAnnotation
{
    public function __construct(
        public string $prefix='',
        public string $serverName = 'http'
    )
    {
    }
}