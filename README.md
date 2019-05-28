# PhoneLocation 奥凡手机号码归属地查

手机号码归属地查询

支持号段 ^1[345789]

记录条数415284条

离线数据库2.8MB


## 安装

~~~php
composer require invoice/phonelocation
~~~

## 使用

~~~php
use Ofan\PhoneLocation;

$mobileNumber = new PhoneLocation();
print_r($mobileNumber->find(15900000767));
print_r($mobileNumber->find(15900008755));
print_r($mobileNumber->find(15919252188));
~~~


~~~php

Array
(
    [sp] => 中国移动
    [province] => 广东
    [city] => 中山
    [zip_code] => 528400
    [area_code] => 0760
)
Array
(
    [sp] => 中国移动
    [province] => 广东
    [city] => 中山
    [zip_code] => 528400
    [area_code] => 0760
)
Array
(
    [sp] => 中国移动
    [province] => 广东
    [city] => 珠海
    [zip_code] => 519000
    [area_code] => 0756
)

~~~