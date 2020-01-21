<?php

namespace Azonmedia\VueRouter;

require_once '../vendor/autoload.php';



$Router = new VueRouter('./router.js');



$Router->{'/home'} = 'home';
$Router->{'/products/list'} = 'list';

$Router->{'/admin'} = 'admin';
$Router->{'/admin'}->add('crud','@crud');


print $Router;