<?php
namespace Hydina\DefinaMoon\Console;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;
use yii\helpers\FileHelper;

class HydinaController extends Controller
{
    /** @var string */
    public $defaultAction = 'help';

    /** @var string */
    public $component;

    /** @var string|null  */
    public $modelClass;

    /** @var string */
    public $ns = 'frontend\models';

    /** @var string */
    public $targetNamespace = 'frontend\livewire\widgets';

    public function options($actionID)
    {
        return array_merge(parent::options($actionID), [
            'component',
            'modelClass',
            'ns',
            'targetNamespace',
        ]);
    }

    public function optionAliases()
    {
        return array_merge(parent::optionAliases(), [
            'c' => 'component',
            'm' => 'modelClass',
            'n' => 'ns',
            't' => 'targetNamespace',
        ]);
    }

    public function actionHelp()
    {
        $this->stdout("\nDefina Moon - a full-stack framework for the Yii PHP framework that allows you to create dynamic and reactive web interfaces using PHP without having to write complex JavaScript code, based on Livewire\n\n", Console::FG_CYAN, Console::BOLD);

        $this->stdout("Usage:\n", Console::FG_YELLOW);
        $this->stdout("  yii hydina/livewire --component=<Name> [options]\n\n");

        $this->stdout("Available commands:\n", Console::FG_YELLOW);
        $this->stdout("  hydina/livewire    ", Console::FG_GREEN);
        $this->stdout("Creates a new Livewire component class\n");
        $this->stdout("  hydina/help        ", Console::FG_GREEN);
        $this->stdout("Displays this help message\n\n");

        $this->stdout("Options for hydina/livewire:\n", Console::FG_YELLOW);
        $this->stdout("  --component        ", Console::FG_GREEN);
        $this->stdout("(required) The name of the component to create. Example: Counter\n");
        $this->stdout("  --modelClass       ", Console::FG_GREEN);
        $this->stdout("(optional) The name of the model class to bind to. Example: User\n");
        $this->stdout("  --ns               ", Console::FG_GREEN);
        $this->stdout("(optional) The namespace for the model. Default: frontend\\models\n");
        $this->stdout("  --targetNamespace  ", Console::FG_GREEN);
        $this->stdout("(optional) The namespace for the component. Default: frontend\\livewire\\widgets\n\n");

        $this->stdout("Settings for hydina/livewire:\n", Console::FG_YELLOW);
        $this->stdout("  -c, --component        ", Console::FG_GREEN);
        $this->stdout("(required) The name of the component being created. Example: Counter\n");
        $this->stdout("  -m, --modelClass       ", Console::FG_GREEN);
        $this->stdout("(optional) The name of the model class to bind to. Example: User\n");
        $this->stdout("  -n, --ns               ", Console::FG_GREEN);
        $this->stdout("(optional) The namespace for the model. Default: frontend\\models\n");
        $this->stdout("  -t, --targetNamespace  ", Console::FG_GREEN);
        $this->stdout("(optional) The namespace for the component. Default: frontend\\livewire\\widgets\n\n");

        $this->stdout("Examples:\n", Console::FG_YELLOW);
        $this->stdout("  yii hydina/livewire --component=Counter\n");
        $this->stdout("  yii hydina/livewire --component=Counter --modelClass=Counter --ns=\"frontend\\models\"\n\n");

        return ExitCode::OK;
    }

    public function actionLivewire()
    {
        if (empty($this->component)) {
            $this->stderr("Error: A required parameter is missing --component\n", Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }
        $className = ucfirst($this->component);
        $alias = '@' . str_replace('\\', '/', $this->targetNamespace);
        $directory = Yii::getAlias($alias, false);
        if (!$directory) {
            $this->stderr("Error: Failed to determine path for namespace {$this->targetNamespace}\n", Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }
        $filePath = $directory . DIRECTORY_SEPARATOR . $className . '.php';

        if (file_exists($filePath)) {
            $this->stderr("Error: Component {$className} already exists at path: {$filePath}\n", Console::FG_YELLOW);
            return ExitCode::UNSPECIFIED_ERROR;
        }
        FileHelper::createDirectory($directory);
        file_put_contents($filePath, $this->renderTemplate($className));

        $this->stdout("Error: Component {$className} successfully created: {$filePath}\n", Console::FG_GREEN);
        return ExitCode::OK;
    }

    protected function renderTemplate(string $className): string
    {
        $uses = ["use Hydina\\DefinaMoon\\App\\Component;"];
        $body = "    //";
        if (!empty($this->modelClass)) {
            $modelFqn = trim($this->ns, '\\') . '\\' . ucfirst($this->modelClass);
            $modelShortName = ucfirst($this->modelClass);
            $uses[] = "use {$modelFqn};";
            $body = "    public ?{$modelShortName} \$model = null;";
        }
        $usesBlock = implode("\n", $uses);
        return <<<PHP
<?php
namespace {$this->targetNamespace};

{$usesBlock}

class {$className} extends Component
{
{$body}
}

PHP;
    }
}