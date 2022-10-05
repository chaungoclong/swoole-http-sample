<?php

namespace Chaungoclong\SwooleHttpSample;

class Test
{
    public function __invoke()
    {
        echo 'hello';
    }

    public function greet($name)
    {
        echo "hello $name";
    }
}