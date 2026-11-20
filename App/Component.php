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
use yii\db\ActiveRecord;


abstract class Component extends Widget
{

    private array $computedCache = [];

    protected function computed(string $key, callable $callback): mixed
    {
        if (!array_key_exists($key, $this->computedCache)) {
            $this->computedCache[$key] = $callback();
        }
        return $this->computedCache[$key];
    }

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
            'data' => $this->dehydrateState(),
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

        $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $payload['checksum'] = hash_hmac('sha256', $json, Yii::$app->request->cookieValidationKey);
        return json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    protected function view(string $view, array $params = []): string
    {
        $this->computedCache = [];
        $innerHtml = $this->render($view, array_merge(
            $this->getState(), 
            ['component' => $this],
            $params
        ));
        return Html::tag('div', $innerHtml, [
            'wire:id' => $this->getId(),
            'wire:snapshot' => $this->buildSnapshot(),
            'wire:effects' => '{}',
        ]);
    }


    public function dehydrateState(): array
    {
        $state = [];
        foreach ($this->getState() as $key => $value) {
            if ($value instanceof ActiveRecord) {
                $state[$key] = [
                    '__model' => get_class($value),
                    'pk' => $value->getPrimaryKey(),
                ];
            } else {
                $state[$key] = $value;
            }
        }
        return $state;
    }


    public function hydrateState(array $data): void
    {
        foreach ($data as $key => $value) {
            if (!property_exists($this, $key)) continue;

            if (is_array($value) && isset($value['__model'], $value['pk'])) {
                /** @var class-string<ActiveRecord> $modelClass */
                $modelClass = $value['__model'];
                $this->$key = $modelClass::findOne($value['pk']);
            } else {
                $this->$key = $value;
            }
        }
    }



    public function registerClientScript()
    {
        LivewireAsset::register($this->view);
    }
}