### 可爱猫API接口Demo-PHP版本

 
 
 示例代码可查看 api.php，需要设置可爱猫API接口地址,默认为```http://127.0.0.1:8073/send```
 
 如果外网使用，可参考：
 
 ```
 $url="http://ip:8073/send";
 $config=["url"=>$url];
 $lovelyCat=new lovelyCat($config);
 
 ```
 
 
 单例模式使用方式：
 
 1、使用默认配置（http://127.0.0.1:8073/send）
 
 ```
 $msg="Hello World";
 lovelyCat::getInstance()->sendTextMsg($msg);
 ```
 2、使用外网API接口配置：
 
 ```
 $url="http://ip:8073/send";
 $msg="Hello World";
 lovelyCat::getInstance($url)->sendTextMsg($msg);
 ```
