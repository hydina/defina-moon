<?php
namespace Hydina\DefinaMoon\Assets;

use yii\web\AssetBundle;

class LivewireAsset extends AssetBundle
{
    public $sourcePath = '@vendor/livewire/livewire/dist';
    public $js = [
        'livewire.min.js',
    ];
}
