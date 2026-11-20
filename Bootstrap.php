<?php
namespace Hydina\DefinaMoon;

use Hydina\DefinaMoon\App\LivewireController;
use Hydina\DefinaMoon\Console\HydinaController;
use yii\base\BootstrapInterface;
use yii\console\Application as ConsoleApplication;
use yii\web\Application as WebApplication;

class Bootstrap implements BootstrapInterface
{
    public function bootstrap($app)
    {
        if ($app instanceof ConsoleApplication) {
            $app->controllerMap['hydina'] = HydinaController::class;
        }

        if ($app instanceof WebApplication) {
            $app->controllerMap['livewire'] = LivewireController::class;
        }
    }
}
