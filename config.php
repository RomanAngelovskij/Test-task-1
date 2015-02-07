<?php
$Config['mainCache'] = 'redis';
$Config['reserveCache'] = 'mongoDB';

$Config['db']['host'] = 'localhost';
$Config['db']['name'] = '';
$Config['db']['user'] = '';
$Config['db']['password'] = '';

$Config['allowedIP'] = '[
  {
    "name": "Operator name",
    "coutry_code": "RU",
    "subnets": [
      "5.44.32.0\/21",
      "176.28.80.0/21",
      "77.244.112.0/20",
      "5.191.0.0/16"
    ]
  }
  ,{"name":"SevStar","coutry_code":"RU","subnets":["31.28.224.0\/255"]}
]';
?>