<?php
namespace Hydina\DefinaMoon\App;

use Yii;
use yii\base\Widget;
use yii\helpers\Html;
use yii\helpers\Json;
use ReflectionClass;
use ReflectionProperty;
use yii\helpers\Url;
use yii\web\View;
use Hydina\DefinaMoon\Assets\LivewireAsset;

abstract class Component extends Widget
{
    public function init()
    {
        $this->registerClientScript();
        parent::init();

        if ($this->getId(false) === null) {
            $this->setId(uniqid('defina-moon-'));
        }

        $updateUri = Url::to(['/livewire/update']);
        $csrf = Yii::$app->request->getCsrfToken();
        $this->getView()->registerJs("
            window.livewireScriptConfig = {
                uri: '{$updateUri}',
                csrf: '{$csrf}',
                progressBar: true
            };
        ", View::POS_END, 'livewire-config');
    }

    public function getState(): array
    {
        $ref = new ReflectionClass($this);
        $state = [];
        foreach ($ref->getProperties(ReflectionProperty::IS_PUBLIC) as $prop) {
            if ($prop->getDeclaringClass()->getName() === static::class) {
                $state[$prop->getName()] = $prop->getValue($this);
            }
        }
        return $state;
    }

    public function buildSnapshot(): string
    {
        $payload = [
            'data' => $this->getState(),
            'memo' => [
                'id' => $this->getId(),
                'name' => static::class,
                'path' => Yii::$app->request->pathInfo,
                'method' => 'GET',
                'children' => [],
                'scripts' => [],
                'assets' => [],
                'errors' => [],
                'locale' => Yii::$app->language,
            ],
        ]; 
        // $json = Json::encode($payload);
        // $payload['checksum'] = hash_hmac('sha256', $json, Yii::$app->request->cookieValidationKey);
        // return Json::encode($payload);
        $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $payload['checksum'] = hash_hmac('sha256', $json, Yii::$app->request->cookieValidationKey);
        return json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    protected function view(string $view, array $params = []): string
    {
        $innerHtml = $this->render($view, array_merge($this->getState(), $params));
        return Html::tag('div', $innerHtml, [
            'wire:id' => $this->getId(),
            'wire:snapshot' => $this->buildSnapshot(),
            'wire:effects' => '{}',
        ]);
    }

    public function registerClientScript()
    {
        LivewireAsset::register($this->view);
    }
}