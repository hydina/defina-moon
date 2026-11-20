<?php
namespace Hydina\DefinaMoon\App;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\web\BadRequestHttpException;
use yii\helpers\Json;

class LivewireController extends Controller
{
    public $enableCsrfValidation = false;

    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }
        $body = Json::decode(Yii::$app->request->getRawBody() ?: '{}');
        $token = $body['_token'] ?? Yii::$app->request->getCsrfTokenFromHeader();

        if (!Yii::$app->request->validateCsrfToken($token)) {
            throw new BadRequestHttpException('Invalid CSRF token.');
        }
        return true;
    }

    public function actionUpdate()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $body = Json::decode(Yii::$app->request->getRawBody());

        $responses = [];
        foreach ($body['components'] ?? [] as $componentData) {
            $snapshot = Json::decode($componentData['snapshot']);
            $checksum = $snapshot['checksum'] ?? '';
            unset($snapshot['checksum']);

            $reconstructedJson = json_encode($snapshot, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $expectedChecksum = hash_hmac('sha256', $reconstructedJson, Yii::$app->request->cookieValidationKey);

            if (!hash_equals($expectedChecksum, $checksum)) {
                throw new BadRequestHttpException('Snapshot tampered! Nice try.');
            }
            $widgetClass = $snapshot['memo']['name'];
            /** @var Hydina\DefinaMoon\App\Component $widget */
            $widget = Yii::createObject([
                'class' => $widgetClass,
                'id' => $snapshot['memo']['id'],
            ]);
            $widget->hydrateState($snapshot['data'] ?? []);
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