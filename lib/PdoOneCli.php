<?php /** @noinspection DuplicatedCode */

namespace eftec;

use eftec\CliOne\CliOne;
use eftec\CliOne\CliOneParam;
use Exception;
use RuntimeException;

/**
 * Class pdoonecli
 * It is the CLI interface for PdoOne.<br>
 * <b>How to execute it?</b><br>
 * In the command line, runs the next line:<br>
 * <pre>
 * php vendor/eftec/PdoOne/lib/pdoonecli
 * or
 * vendor/bin/pdoonecli (Linux/macOS) / vendor/bin/pdoonecli.bat (Windows)
 * </pre>
 *
 * @see           https://github.com/EFTEC/PdoOne
 * @package       eftec
 * @author        Jorge Castro Castillo
 * @copyright (c) Jorge Castro C. Dual Licence: MIT and Commercial License  https://github.com/EFTEC/PdoOne
 * @version       2.0
 */
class PdoOneCli
{
    public const VERSION = '2.0.1';
//</editor-fold>
    /** @var CliOne */
    public $cli;
    /**
     * @var CliOneParam
     */
    public $mainMenu = [];
    protected $help;

    public function __construct()
    {
        $this->cli = new CliOne();
        $this->cli->setErrorType();
    }

    /** @noinspection PhpMissingReturnTypeInspection
     * @noinspection PhpUnused
     * @noinspection ReturnTypeCanBeDeclaredInspection
     */
    protected function runCliConnection($force = false)
    {
        if ($force === false && !$this->cli->getValue('databaseType')) {
            return null;
        }
        if ($force) {
            $this->cli->evalParam('databaseType', true);
            $this->cli->evalParam('server', true);
            $this->cli->evalParam('user', true);
            $this->cli->evalParam('password', true);
            $this->cli->evalParam('database', true);
        }
        $result = null;
        while (true) {
            try {
                $pdo = $this->createPdoInstance();
                if ($pdo === null) {
                    throw new RuntimeException('trying');
                }
                $this->cli->showCheck('OK', 'green', 'Connected to the database <bold>' . $this->cli->getValue('database') . '</bold>');
                $result = $pdo;
                break;
            } catch (Exception $ex) {
            }
            $rt = $this->cli->createParam('retry')
                ->setDescription('', 'Do you want to retry?')
                ->setInput(true, 'optionshort', ['yes', 'no'])->evalParam(true);
            if ($rt->value === 'no') {
                break;
            }
            $this->cli->evalParam('databaseType', true);
            $this->cli->evalParam('server', true);
            $this->cli->evalParam('user', true);
            $this->cli->evalParam('password', true);
            $this->cli->evalParam('database', true);
        } // retry database.
        return $result;
    }

    public function cliEngine(bool $run = true): void
    {
        $this->mainMenu['connect'] = '[{{connect}}] Configure,load and save the connection to the database';
        $this->cli->variables['connect'] = '<red>pending</red>';
        $listPHPFiles = $this->getFiles('.', '.config.php');
        $this->cli->createOrReplaceParam('fileconnect', [], 'longflag')
            ->setRequired(false)
            ->setCurrentAsDefault()
            ->setDescription('select a configuration file to load', 'Select the configuration file to use', [
                    'Example: <dim>"--fileconnect myconfig"</dim>']
                , 'file')
            ->setDefault('')
            ->setInput(false, 'string', $listPHPFiles)
            ->evalParam();
        $this->cli->createOrReplaceParam('fileconnectphp', [], 'longflag')
            ->setRequired(false)
            ->setCurrentAsDefault()
            ->setDescription('Select the file to save the configuration as a PHP file', 'Select the configuration file to save as PHP file', [
                    'Example: <dim>"--fileconnect myconfig --fileconnectphp myphpfile"</dim>']
                , 'file')
            ->setDefault('')
            ->setInput(false, 'string', $listPHPFiles)
            ->evalParam();
        if ($this->cli->getParameter('fileconnect')->missing === false) {
            $this->doReadConfig();
        }
        if ($run) {
            if ($this->cli->getSTDIN() === null) {
                $this->showLogo();
            }
            $this->menuInit();
        }
    }

    public function menuInit(): void
    {
        $this->cli->createParam('command', ['cmd'])
            ->setRelated(['generate'])
            ->setArgument('longflag', true)
            ->setDescription('The command to run when we are generating a new code'
                , 'Select a command (empty for exit)'
                , ['<cyan><optionkey/></cyan>:<option/>'], 'cmd')
            ->setAllowEmpty()
            ->setInput(true, 'option', $this->mainMenu)
            ->add(true);
        while (true) {
            $this->cli->upLevel('main menu');
            $this->cli->setColor(['byellow'])->showBread();
            $command = $this->cli->evalParam('command', true);
            if ($command->valueKey === '' || $command->valueKey === $this->cli->emptyValue) {
                exit;
            }
            $method = 'menu' . $command->valueKey;
            $this->$method();
        }
    }

    /** @noinspection PhpUnused */
    public function menuConnect(): void
    {
        while (true) {
            $this->cli->upLevel('connect');
            $this->cli->setColor(['byellow'])->showBread();
            $menuConnect = $this->cli->createParam('connect2', '', 'none')
                ->setDescription('The type of database', 'Select an option (empty to return)', [
                    'Values allowed: <cyan><option/></cyan>'])
                ->setInput(true, 'option', [
                    'configure' => '[{{connect}}] configure and connect to the database',
                    'query' => '[{{connect}}] run a query',
                    'load' => '[{{connect}}] load the configuration',
                    'save' => '[{{connect}}] save the configuration',
                    'savephp' => '[{{connect}}] save the configuration as PHP file'
                ])
                ->setAllowEmpty()
                ->evalParam(true)->valueKey;
            switch ($menuConnect) {
                case 'configure':
                    $this->menuconnect_conf();
                    break;
                case 'query':
                    $this->menuConnect_Query();
                    break;
                case 'load':
                    $this->menuConnect_load();
                    break;
                case 'save':
                    $this->menuConnect_save();
                    break;
                case 'savephp':
                    $this->menuConnect_savePHP();
                    break;
                case $this->cli->emptyValue:
                    $this->cli->downLevel(2);
                    break 2; // swith and while
            }
            $this->cli->downLevel();
        }
    }

    public function menuConnect_save(): void
    {
        $this->cli->upLevel('save');
        $this->cli->setColor(['byellow'])->showBread();
        $sg = $this->cli->createParam('yn', [], 'none')
            ->setDescription('', 'Do you want to save the configurations of connection?')
            ->setInput(true, 'optionshort', ['yes', 'no'])
            ->setDefault('yes')
            ->evalParam(true);
        if ($sg->value === 'yes') {
            $saveconfig = $this->cli->getParameter('fileconnect')->setInput()->evalParam(true);
            if ($saveconfig->value) {
                $r = $this->cli->saveData($this->cli->getValue('fileconnect'), [
                    'databaseType' => $this->cli->getValue('databaseType'),
                    'server' => $this->cli->getValue('server'),
                    'user' => $this->cli->getValue('user'),
                    'pwd' => $this->cli->getValue('pwd'),
                    'database' => $this->cli->getValue('database'),]);
                if ($r === '') {
                    $this->cli->showCheck('OK', 'green', 'file saved correctly');
                }
            }
        }
        $this->cli->downLevel();
    }

    public function menuConnect_savePHP(): void
    {
        $this->cli->upLevel('save php');
        $this->cli->setColor(['byellow'])->showBread();
        $sg = $this->cli->createOrReplaceParam('yn', [], 'none')
            ->setDescription('', 'Do you want to save the configurations of connection?')
            ->setInput(true, 'optionshort', ['yes', 'no'])
            ->setDefault('yes')
            ->evalParam(true);
        if ($sg->value === 'yes') {
            $saveconfig = $this->cli->getParameter('fileconnectphp')->setInput()->evalParam(true);
            if ($saveconfig->value) {
                $r = $this->cli->saveDataPHPFormat($this->cli->getValue('fileconnectphp'), [
                        'databaseType' => $this->cli->getValue('databaseType'),
                        'server' => $this->cli->getValue('server'),
                        'user' => $this->cli->getValue('user'),
                        'pwd' => $this->cli->getValue('pwd'),
                        'database' => $this->cli->getValue('database'),]
                    , '.php', 'pdoOneConfig', 'it is the configuration of PdoOne');
                if ($r === '') {
                    $this->cli->showCheck('OK', 'green', 'file saved correctly');
                }
            }
        }
        $this->cli->downLevel();
    }

    public function menuConnect_Query(): void
    {
        $this->cli->upLevel('query');
        $this->cli->setColor(['byellow'])->showBread();
        while (true) {
            $query = $this->cli->createOrReplaceParam('query', [], 'none')
                ->setAddHistory()
                ->setDescription('query', 'query (empty to exit)')
                ->setInput()
                ->setAllowEmpty()
                ->evalParam(true);
            if ($query->value === $this->cli->emptyValue || $query->value === '') {
                break;
            }
            $pdo = $this->createPdoInstance();
            if ($pdo !== null) {
                try {
                    $result = $pdo->runRawQuery($query->value);
                    $this->cli->showLine(json_encode($result, JSON_PRETTY_PRINT));
                } catch (Exception $e) {
                    $this->cli->showCheck('ERROR', 'red', $e->getMessage());
                }
            } else {
                $this->cli->showCheck('ERROR', 'red', 'not connected');
            }
        }
        $this->cli->downLevel();
    }

    public function menuConnect_load(): void
    {
        $this->cli->upLevel('load');
        $this->cli->setColor(['byellow'])->showBread();
        $saveconfig = $this->cli->getParameter('fileconnect')
            ->setInput()
            ->evalParam(true);
        if ($saveconfig->value) {
            $this->doReadConfig();
        }
        $this->cli->downLevel();
    }

    public function doReadConfig(): void
    {
        $r = $this->cli->readData($this->cli->getValue('fileconnect'));
        if ($r !== null && $r[0] === true) {
            $this->cli->showCheck('OK', 'green', 'file read correctly');
            $this->cli->variables['connect'] = '<green>ok</green>';
            $this->cli->setParam('databaseType', $r[1]['databaseType'], false, true);
            $this->cli->setParam('server', $r[1]['server'], false, true);
            $this->cli->setParam('user', $r[1]['user'], false, true);
            $this->cli->setParam('pwd', $r[1]['pwd'], false, true);
            $this->cli->setParam('database', $r[1]['database'], false, true);
        } else {
            $this->cli->showCheck('ERROR', 'red', 'unable to read file ' . $this->cli->getValue('fileconnect') . ", cause " . $r[1]);
        }
    }

    public function menuconnect_conf(): void
    {
        while (true) {
            $this->cli->upLevel('configure');
            $this->cli->setColor(['byellow'])->showBread();
            $this->cli->createOrReplaceParam('databaseType', 'dt', 'longflag')
                ->setDescription('The type of database', 'Select the type of database', [
                    'Values allowed: <cyan><option/></cyan>'])
                ->setInput(true, 'optionshort', ['mysql', 'sqlsrv', 'oci', 'test'])
                ->setCurrentAsDefault()
                ->evalParam(true);
            $this->cli->createOrReplaceParam('server', 'srv', 'longflag')
                ->setDefault('127.0.0.1')
                ->setCurrentAsDefault()
                ->setDescription('The database server', 'Select the database server', [
                    'Example <dim>mysql: 127.0.0.1 , 127.0.0.1:3306</dim>',
                    'Example <dim>sqlsrv: (local)\sqlexpress 127.0.0.1\sqlexpress</dim>'])
                ->setInput()
                ->evalParam(true);
            $this->cli->createOrReplaceParam('user', 'u', 'longflag')
                ->setDescription('The username to access to the database', 'Select the username',
                    ['Example: <dim>sa, root</dim>'], 'user')
                ->setRequired(false)
                ->setCurrentAsDefault()
                ->setInput()
                ->evalParam(true);
            $this->cli->createOrReplaceParam('pwd', 'p', 'longflag')
                ->setRequired(false)
                ->setDescription('The password to access to the database', '', ['Example: <dim>12345</dim>'], 'pwd')
                ->setCurrentAsDefault()
                ->setInput(true, 'password')
                ->evalParam(true);
            $this->cli->createOrReplaceParam('database', 'db', 'longflag')
                ->setRequired(false)
                ->setDescription('The database/schema', 'Select the database/schema', [
                    'Example: <dim>sakila,contoso,adventureworks</dim>'], 'db')
                ->setCurrentAsDefault()
                ->setInput()
                ->evalParam(true);
            $this->cli->downLevel();
            try {
                $pdo = $this->createPdoInstance();
                if ($pdo === null) {
                    throw new RuntimeException('trying');
                }
                $this->cli->showCheck('OK', 'green', 'Connected to the database <bold>' . $this->cli->getValue('database') . '</bold>');
                $this->cli->variables['connect'] = '<green>ok</green>';
                //$result = $pdo;
                break;
            } catch (Exception $ex) {
            }
            $rt = $this->cli->createParam('retry')
                ->setDescription('', 'Do you want to retry?')
                ->setInput(true, 'optionshort', ['yes', 'no'])->evalParam(true);
            if ($rt->value === 'no') {
                break;
            }
        }
    }

    /** @noinspection PhpMissingReturnTypeInspection
     * @noinspection ReturnTypeCanBeDeclaredInspection
     */
    public function createPdoInstance()
    {
        try {
            $pdo = new PdoOne(
                $this->cli->getValue('databaseType'),
                $this->cli->getValue('server'),
                $this->cli->getValue('user'),
                $this->cli->getValue('pwd'),
                $this->cli->getValue('database'));
            $pdo->logLevel = 1;
            $pdo->connect();
        } catch (Exception $ex) {
            /** @noinspection PhpUndefinedVariableInspection */
            $this->cli->showCheck('ERROR', 'red', ['Unable to connect to database', $pdo->lastError(), $pdo->errorText]);
            return null;
        }
        $pdo->logLevel = 2;
        return $pdo;
    }

    public function getCli(): CliOne
    {
        return $this->cli;
    }

    public static function isCli(): bool
    {
        return !http_response_code();
    }

    /***
     * It finds the vendor path (where composer is located).
     * @param string|null $initPath
     * @return string
     *
     */
    public static function findVendorPath(?string $initPath = null): string
    {
        $initPath = $initPath ?: __DIR__;
        $prefix = '';
        $defaultvendor = $initPath;
        // finding vendor
        for ($i = 0; $i < 8; $i++) {
            if (@file_exists("$initPath/{$prefix}vendor/autoload.php")) {
                $defaultvendor = "{$prefix}vendor";
                break;
            }
            $prefix .= '../';
        }
        return $defaultvendor;
    }

    /**
     * It gets a list of files filtered by extension.
     * @param string $path
     * @param string $extension . Example: ".php", "php" (it could generate false positives)
     * @return array
     */
    protected function getFiles(string $path, string $extension): array
    {
        $scanned_directory = array_diff(scandir($path), ['..', '.']);
        $scanned2 = [];
        foreach ($scanned_directory as $k) {
            $fullname = pathinfo($k)['extension'] ?? '';
            if (str_ends_with($fullname, $extension)) {
                $scanned2[$k] = $k;
            }
        }
        return $scanned2;
    }

    protected function showLogo(): void
    {
        $v = PdoOne::VERSION;
        $vc = self::VERSION;
        $this->cli->show("
 _____    _       _____           
|  _  | _| | ___ |     | ___  ___ 
|   __|| . || . ||  |  ||   || -_|
|__|   |___||___||_____||_|_||___|  
PdoOne: $v  Cli: $vc  

<yellow>Syntax:php " . basename(__FILE__) . " <command> <flags></yellow>

");
        $this->cli->showParamSyntax2();
    }

    /**
     * It is used internally to merge two arrays.
     * @noinspection PhpUnused
     */
    protected function updateMultiArray(?array $oldArray, ?array $newArray, string $name): ?array
    {
        if (count($newArray) !== 0) {
            // delete
            foreach ($newArray as $tableName => $columns) {
                if (isset($oldArray[$tableName])) {
                    foreach ($columns as $column => $v) {
                        if (!array_key_exists($column, $oldArray[$tableName])) {
                            $this->cli->showCheck('<bold>deleted</bold>', 'red', "$name: Column <bold>$tableName.$column</bold> deleted");
                            unset($newArray[$tableName][$column]);
                        }
                    }
                } else {
                    $this->cli->showCheck('<bold>deleted</bold>', 'red', "$name: Table <bold>$tableName</bold> delete");
                    unset($newArray[$tableName]);
                }
            }
            // insert
            foreach ($oldArray as $tableName => $columns) {
                if (isset($newArray[$tableName])) {
                    foreach ($columns as $column => $v) {
                        if (!array_key_exists($column, $newArray[$tableName])) {
                            $this->cli->showCheck(' added ', 'green', "$name: Column <bold>$tableName.$column</bold> added");
                            $newArray[$tableName][$column] = $v;
                            //unset($this->tablexclass[$tableName], $this->columnsTable[$tableName], $this->extracolumn[$tableName]);
                        }
                    }
                } else {
                    $this->cli->showCheck(' added ', 'green', "$name: Table <bold>$tableName</bold> added");
                    $newArray[$tableName] = $columns;
                }
            }
        } else {
            $newArray = $oldArray;
        }
        return $newArray;
    }
}
