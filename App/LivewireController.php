<?php
namespace Hydina\DefinaMoon\App;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\web\BadRequestHttpException;
use yii\helpers\Json;

class LivewireController extends Controller
{
    public function actionUpdate()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $body = Json::decode(Yii::$app->request->getRawBody());

        $responses = [];
        foreach ($body['components'] ?? [] as $componentData) {
            $snapshot = Json::decode($componentData['snapshot']);
            $checksum = $snapshot['checksum'] ?? '';
            unset($snapshot['checksum']);

            $validJson = Yii::$app->security->validateData(
                $checksum, 
                Yii::$app->request->cookieValidationKey
            );
            if ($validJson !== Json::encode($snapshot)) {
                throw new BadRequestHttpException('Snapshot tampered! Nice try.');
            }
            $widgetClass = $snapshot['memo']['name'];
            /** @var Hydina\DefinaMoon\App\Component $widget */
            $widget = Yii::createObject([
                'class' => $widgetClass,
                'id' => $snapshot['memo']['id'],
            ]);
            foreach ($snapshot['data'] as $key => $value) {
                $widget->$key = $value;
            }
            foreach ($componentData['updates'] ?? [] as $prop => $val) {
                if (property_exists($widget, $prop)) {
                    $widget->$prop = $val;
                }
            }

            foreach ($componentData['calls'] ?? [] as $call) {
                $method = $call['method'];
                $params = $call['params'] ?? [];
                if (method_exists($widget, $method)) {
                    $widget->$method(...$params);
                }
            }
            $html = $widget->run();
            $responses[] = [
                'snapshot' => $widget->buildSnapshot(),
                'effects' => [
                    'html' => $html,
                ],
            ];
        }
        return [
            'components' => $responses,
            'assets' => [],
        ];
    }
}