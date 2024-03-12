<?php
declare(strict_types=1);

namespace Wjy\RpcHelper\Listeners;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\ContainerInterface;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Di\ReflectionManager;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BeforeMainServerStart;
use Wjy\RpcHelper\Exceptions\RpcClientException;
use Wjy\RpcHelper\RpcClient;

class RegisterConsumerListener implements ListenerInterface
{
    protected ConfigInterface $config;
    public function __construct(protected ContainerInterface $container)
    {
        $this->config = $this->container->get(ConfigInterface::class);
    }

    public function listen(): array
    {
        return [
            BeforeMainServerStart::class,
        ];
    }

    public function process(object $event): void
    {
        $consumers = $this->config->get('services.consumers', []);

        $classes = AnnotationCollector::getClassesByAnnotation(RpcClient::class);
        foreach ($classes as $className => $metaData) {
            $reflectionProperty = ReflectionManager::reflectProperty($className, 'serviceName');
            $serviceName = ReflectionManager::getPropertyDefaultValue($reflectionProperty);

            $consumer = [
                'name' => $serviceName,
            ];
            if (!empty($metaData->registerCenter)) {
                $consumer['registry'] = [
                    'protocol' => $metaData->registerCenter,
                    'address'  => $metaData->address?:$this->getRegisterCenterAddress($metaData->registerCenter),
                ];
            } else if (!empty($metaData->nodes)) {
                $nodes = [];
                foreach ($metaData->nodes as $node) {
                    $nodes[] = $this->parseNode($node);
                }
                $consumer['nodes'] = $nodes;
            } else {
                throw new RpcClientException("Invalid rpcClient. Option registryCenter or nodes are required!");
            }
            $consumers[] = $consumer;
        }
        $this->config->set('services.consumers', $consumers);
    }

    private function getRegisterCenterAddress($type)
    {
        $driver = $this->config->get('services.drivers.'.$type);
        if (isset($driver['url'])) {
            return $driver['url'];
        }

        return 'http://' . $driver['host'] . ':' . $driver['port'];
    }

    private function parseNode($node)
    {
        $urlInfo = parse_url($node);

        return ['host' => $urlInfo['host'], 'port' => $urlInfo['port']];
    }
}