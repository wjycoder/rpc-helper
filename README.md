# component-creator

## 安装
```
composer create-project wjy/jsonrpc-client
```

## 使用
### #[RpcClient]
自动配置服务消费者，省去配置`services.consumers`步骤
```php
#[RpcClient(registerCenter: 'nacos')]
class CalculatorServiceClient extends AbstractServiceClient implements CalculatorServiceInterface
{
    protected string $serviceName = 'GoodsService';
    // ...
}
```
指定nacos地址, 默认使用`services.php`配置的nacos
```php
#[RpcClient(registerCenter: 'nacos', address: 'http://localhost:8848')]
```
如果不使用配置中心
```php
#[RpcClient(nodes: ['localhost:9502'])]
```
