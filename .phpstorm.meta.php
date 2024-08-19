<?php
namespace PHPSTORM_META
{
    override(
        \Safronik\CodePatterns\Structural\DI::get(),
        map([
            '' => '@',
        ])
    );
}