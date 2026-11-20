# Defina Moon

[![Latest Stable Version](http://poser.pugx.org/hydina/defina-moon/v)](https://packagist.org/packages/hydina/defina-moon)
[![Total Downloads](http://poser.pugx.org/hydina/defina-moon/downloads)](https://packagist.org/packages/hydina/defina-moon)
[![Latest Unstable Version](http://poser.pugx.org/hydina/defina-moon/v/unstable)](https://packagist.org/packages/hydina/defina-moon)
[![License](http://poser.pugx.org/hydina/defina-moon/license)](https://packagist.org/packages/hydina/defina-moon)
[![PHP Version Require](http://poser.pugx.org/hydina/defina-moon/require/php)](https://packagist.org/packages/hydina/defina-moon)

A full-featured PHP engine for creating dynamic web interfaces using Yii

## Installation Instructions:

```bash
composer require hydina/defina-moon
```

## Get information:

Open console on your Yii project and insert:
```bash
yii hydina
```

## Get started

Create your first component:
```bash
yii hydina/livewire --component=Counter
```

The result will be:
```php
<?php

namespace frontend\livewire\widgets;
use Hydina\DefinaMoon\App\Component;

class Counter extends Component
{
    //
}
```