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
    protected string $serviceName = 'CalculatorService';
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
### 微服务代理接口
```php
#[RpcController(prefix: '/calculator')]  // prefix默认为类名首字母小写
class GoodsService implements GoodsServiceInterface
{
    #[RpcMapping(method: 'get')]  // path默认是方法名 接口: /calculator/add
    public function add($a, $b)
    {
        return $a + $b;
    }
    
    #[RpcMapping(path: 'minus', method: 'get')]  // 指定path地址 接口: /calculator/add
    public function minus($a, $b)
    {
        return $a + $b;
    }
}
```
