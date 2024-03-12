<?php
declare(strict_types=1);

namespace Wjy\RpcHelper\Listeners;

use Hyperf\Context\ApplicationContext;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BeforeMainServerStart;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Router\DispatcherFactory;
use Hyperf\HttpServer\Router\Router;
use Hyperf\Stringable\Str;
use Psr\Container\ContainerInterface;
use Wjy\RpcHelper\RpcController;
use Wjy\RpcHelper\RpcMapping;
use function Hyperf\Support\make;

class RegisterRpcProxy implements ListenerInterface
{
    public function __construct(protected ContainerInterface $container)
    {
    }

    public function listen(): array
    {
        return [
            BeforeMainServerStart::class
        ];
    }

    public function process(object $event): void
    {
        $classesByAnnotation = AnnotationCollector::getClassesByAnnotation(RpcController::class);
        /** @var RpcController $cMetaData */
        foreach ($classesByAnnotation as $className => $cMetaData) {
            if (empty($cMetaData->prefix)) {
                $baseName = Str::afterLast($className, '\\');
                $controllerPrefix = '/' . lcfirst($baseName);
            } else {
                $controllerPrefix = '/' . trim($cMetaData->prefix, '/');
            }
            $reflectionClass = new \ReflectionClass($className);
            $reflectionMethods = $reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC);
            foreach ($reflectionMethods as $method) {
                $classMethodAnnotation = AnnotationCollector::getClassMethodAnnotation($className, $method->getName());
                if (empty($classMethodAnnotation)) {
                    continue;
                }
                /** @var RpcMapping $metaData */
                foreach ($classMethodAnnotation as $anno => $metaData) {
                    if ($anno != RpcMapping::class) {
                        continue;
                    }
                    $requestPath = $metaData->path ?: $method->getName();
                    $path = $controllerPrefix . '/' . $requestPath;
                    var_dump($path);

                    Router::init($this->container->get(DispatcherFactory::class));
                    Router::addServer($cMetaData->serverName, function () use ($className, $method, $path, $metaData) {
                        Router::addRoute($metaData->method, $path, function () use ($className, $metaData, $method) {
                            $request = make(RequestInterface::class);
                            $params = $this->getParameters($method);
                            $data = $request->inputs($params);
                            return ApplicationContext::getContainer()->get($className)->{$method->getName()}(...$data);
                        });
                    });
                }
            }
        }


    }

    private function getParameters(\ReflectionMethod $method)
    {
        $params = [];
        $rParameters = $method->getParameters();
        if (!empty($rParameters)) {
            foreach ($rParameters as $parameter) {
                $params[] = $parameter->getName();
            }
        }
        return $params;
    }
}