<?php
declare(strict_types=1);

namespace ReflectionTest;


use ReflectionClass;

enum SomeEnum {
    case Foo;
}

final class SomeClass {
    public function someMethod(SomeEnum $foo = SomeEnum::Foo) {

    }
}

$reflectionClass = new ReflectionClass(SomeClass::class);
$reflectionMethod = $reflectionClass->getMethod('someMethod');
foreach ($reflectionMethod->getParameters() as $reflectionParameter) {
    echo 'Name: ' . $reflectionParameter->getName();
    if ($reflectionParameter->isDefaultValueAvailable()) {
        var_dump($reflectionParameter->getDefaultValue());
    }
}