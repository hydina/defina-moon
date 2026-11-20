<?php
namespace Hydina\DefinaMoon\Assets;

use yii\web\AssetBundle;

class LivewireAsset extends AssetBundle
{
    public $sourcePath = __DIR__ . '/dist';
    public $js = [
        'livewire.min.js',
        'live.defina.moon.js'
    ];
}
