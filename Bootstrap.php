<?php
namespace Hydina\DefinaMoon;

use Hydina\DefinaMoon\Console\HydinaController;
use yii\base\BootstrapInterface;
use yii\console\Application;

class Bootstrap implements BootstrapInterface
{
    public function bootstrap($app)
    {
        if ($app instanceof Application) {
            $app->controllerMap['hydina'] = HydinaController::class;
        }
    }
}
