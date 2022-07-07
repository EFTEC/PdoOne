<?php /** @noinspection DuplicatedCode */

/** @noinspection PhpConditionAlreadyCheckedInspection */

namespace eftec;

use eftec\CliOne\CliOne;
use eftec\CliOne\CliOneParam;
use Exception;
use RuntimeException;

/**
 * Class pdoonecli
 * It is the CLI interface for PdoOne.<br>
 * Note, this class is called ON-PURPOSE in lowercase.<br>
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
 * @version       1.1
 */
class PdoOneCli
{
    public const VERSION = '1.1';
//</editor-fold>
    /**
     * @var array
     */
    protected $tablexclass = [];
    /**
     * @var array
     */
    protected $conversion = [];
    /**
     * @var array
     */
    protected $alias = [];
    /**
     * @var array
     */
    protected $extracolumn = [];
    /**
     * @var array
     */
    protected $removecolumn = [];
    /**
     * @var array
     */
    protected $columnsTable = [];
    /**
     * @var array
     */
    protected $columnsAlias = [];
    /** @var CliOne */
    public $cli;
    /**
     * @var CliOneParam
     */
    protected $help;

    public function __construct()
    {
        $this->cli = new CliOne();
        $this->cli->setErrorType();
        $this->conversion = $this->convertReset();
    }

    public function getCli(): CliOne
    {
        return $this->cli;
    }

    public function convertReset(): array
    {
        return ["bigint" => null, "blob" => null, "char" => null, "date" => null, "datetime" => null,
            "decimal" => null, "double" => null, "enum" => null, "float" => null, "geometry" => null,
            "int" => null, "json" => null, "longblob" => null, "mediumint" => null, "mediumtext" => null,
            "set" => null, "smallint" => null, "text" => null, "time" => null, "timestamp" => null,
            "tinyint" => null, "varbinary" => null, "varchar" => null, "year" => null];
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

    public static function isCli(): bool
    {
        return !http_response_code();
    }

    /**
     * This method is used for to be injected when the initial parameteres are created.
     * @return void
     */
    protected function injectInitParam(): void
    {
    }

    protected function InjectInitParam2($firstCommand, $interactive): void
    {
    }

    protected function InjectLoadFile($firstCommand, $interactive): void
    {
    }

    /** @noinspection PhpUnused */
    protected function injectEvalParam($firstCommand, $interactive): void
    {
    }

    /** @noinspection PhpUnused */
    protected function injectRunParam($firstCommand, $interactive): void
    {
    }

    /** @noinspection PhpUnused */
    protected function injectEvalGenerate($command): void
    {
    }

    /** @noinspection PhpUnused */
    protected function injectEngine($first): void
    {
    }

    /**
     * It executes the cli Engine.
     *
     * @throws Exception
     */
    public function cliEngine(): void
    {
        $this->cli->createParam('help', 'h', 'longflag')
            ->setRelated(['common', 'export', 'generate'])
            ->setRequired(false)
            ->setAllowEmpty()
            ->setDescription('This help', '', [
                'Example:<dim> --help</dim>',
                'Example:<dim> <command> --help</dim>'], 'command')
            ->setInput(false)
            ->add();
        $this->help = $this->cli->evalParam('help');
        $this->cli->createParam('first', [], 'command')
            ->setRelated([])
            ->setRequired(false)
            ->setAllowEmpty()
            ->setDescription('', '', [])
            ->setInput(false)
            ->add();
        $first = $this->cli->evalParam('first');
        $this->cli->createParam('interactive', 'i', 'longflag')
            ->setRelated(['common', 'export', 'generate', 'definition'])
            ->setRequired(false)
            ->setAllowEmpty()
            ->setDescription('Interactive', 'set the input interactively', [
                'Example: <dim>-interactive</dim>'])
            ->setInput(false)
            ->add();
        $interactive = !$this->cli->evalParam('interactive')->missing;
        $this->cli->createParam('definition', [], 'first')
            ->setRequired(false)
            ->setAllowEmpty()
            ->setDescription('It returns the definition of the database', '', [
                'Example: <dim>"definition --loadconfig myconfig"</dim>.Load a config and generate in interactive mode',
                'Example: <dim>"definition --command scan --loadconfig .\p2.php -og yes"</dim>. Load a config, scan for changes and override'])
            ->setDefault('')
            ->setInput(false)
            ->add();
        $this->cli->evalParam('definition');
        $this->cli->createParam('generate', [], 'first')
            ->setRequired(false)
            ->setAllowEmpty()
            ->setDescription('It generates the repository classes', '', [
                'Example: <dim>"generate --loadconfig myconfig"</dim>.Load a config and generate in interactive mode',
                'Example: <dim>"generate --command scan --loadconfig .\p2.php -og yes"</dim>. Load a config, scan for changes and override'])
            ->setDefault('')
            ->setInput(false)
            ->add();
        $this->cli->evalParam('generate');
        $this->cli->createParam('export', [], 'first')
            ->setRequired(false)
            ->setAllowEmpty()
            ->setDescription('It export a query of table into a file', '', [
                'Example: <dim>"export -in table -out csv"</dim>'])
            ->setDefault('')
            ->setInput(false)
            ->add();
        $this->cli->evalParam('export');

        $this->inJectInitParam();

        $this->cli->createParam('databasetype', 'dt', 'longflag')
            ->setRelated(['common', 'export', 'generate'])
            ->setRequired(false)
            ->setDescription('The type of database', 'Select the type of database', [
                'Values allowed: <cyan><option/></cyan>'])
            ->setInput($interactive, 'optionshort', ['mysql', 'sqlsrv', 'oci', 'test'])
            ->setCurrentAsDefault()
            ->add();
        $this->cli->createParam('server', 'srv', 'longflag')
            ->setRelated(['common', 'export', 'generate'])
            ->setRequired(false)
            ->setDefault('127.0.0.1')
            ->setCurrentAsDefault()
            ->setDescription('The database server', 'Select the database server', [
                'Example <dim>mysql: 127.0.0.1 , 127.0.0.1:3306</dim>',
                'Example <dim>sqlsrv: (local)\sqlexpress 127.0.0.1\sqlexpress</dim>'])
            ->setInput($interactive)
            ->add();
        $this->cli->createParam('user', 'u', 'longflag')
            ->setRelated(['common', 'export', 'generate'])
            ->setDescription('The username to access to the database', 'Select the username',
                ['Example: <dim>sa, root</dim>'], 'usr')
            ->setRequired(false)
            ->setCurrentAsDefault()
            ->setInput($interactive)
            ->add();
        $this->cli->createParam('password', 'p', 'longflag')
            ->setRelated(['common', 'export', 'generate'])
            ->setRequired(false)
            ->setDescription('The password to access to the database', '', ['Example: <dim>12345</dim>'], 'pwd')
            ->setCurrentAsDefault()
            ->setInput($interactive, 'password')
            ->add();
        $this->cli->createParam('database', 'db', 'longflag')
            ->setRelated(['common', 'export', 'generate'])
            ->setRequired(false)
            ->setDescription('The database/schema', 'Select the database/schema', [
                'Example: <dim>sakila,contoso,adventureworks</dim>'], 'db')
            ->setDefault('')
            ->setCurrentAsDefault()
            ->setInput($interactive)
            ->add();
        $this->cli->createParam('classdirectory')
            ->setCurrentAsDefault()
            ->setDescription('',
                'Select the relative directory where the repository classes will be created',
                ['Example: repo'])
            ->setInput()->add();
        $this->cli->createParam('classpostfix')
            ->setDefault('Repo')
            ->setCurrentAsDefault()
            ->setDescription('',
                'Select the postfix of the class',
                ['Example: Repo'])
            ->setInput()->add();
        $this->cli->createParam('classnamespace')
            ->setCurrentAsDefault()
            ->setDescription('',
                'Select the repository\'s namespace.',
                ['It must coincide with the definition of Composer\'s autoloader',
                    'Example: ns1\\ns2'])
            ->setInput()->add();
        $this->cli->createParam('namespace', 'ns', 'longflag')
            ->setRequired(false)
            ->setDescription('The namespace', 'The namespace used', [
                'Example: <dim>"customers"</dim>'])
            ->setDefault('')
            ->setInput(false)
            ->add();
        $listPHPFiles = $this->getFiles('.', 'php');
        $this->cli->createParam('loadconfig', [], 'longflag')
            ->setRelated(['common', 'export', 'generate'])
            ->setRequired(false)
            ->setDescription('Select the configuration file to load', '', [
                    'It loads a configuration file, the file mustn\'t have extension',
                    'Example: <dim>"--loadconfig myconfig"</dim>']
                , 'file')
            ->setDefault('')
            ->setInput(false, 'string', $listPHPFiles)
            ->add();
        $this->cli->createParam('saveconfig', [], 'longflag')
            ->setRelated(['common', 'export', 'generate'])
            ->setRequired(false)
            ->setCurrentAsDefault()
            ->setDescription('save a configuration file', 'Select the configuration file to save', [
                    'Example: <dim>"--saveconfig myconfig"</dim>']
                , 'file')
            ->setDefault('')
            ->setInput($interactive, 'string', $listPHPFiles)
            ->add();
        switch ($first->value) {
            case 'definition':
                $this->cli->createParam('output', 'out', 'longflag')
                    ->setRelated(['export'])
                    ->setRequired(false)
                    ->setDescription('The output file', '', ["The output file"])
                    ->setDefault('')
                    ->setInput($interactive)
                    ->add();
                break;
            case 'export':
                $this->cli->createParam('input', 'in', 'longflag')
                    ->setRelated(['export'])
                    ->setRequired(false)
                    ->setDescription('The type of input', '', [
                        'Example: <dim>-input "select * from table"</dim> = it takes a query as input value',
                        'Example: <dim>-input "table"</dim> = it takkes a table as input value'
                    ])
                    ->setDefault('')
                    ->setInput($interactive)
                    ->add();
                $this->cli->createParam('output', 'out', 'longflag')
                    ->setRelated(['export'])
                    ->setRequired(false)
                    ->setDescription('The type of output', '', [
                        'Values allowed: <cyan><option/></cyan>',
                        '<bold>classcode</bold>: it returns php code with a CRUDL class',
                        '<bold>selectcode</bold>: it shows a php code with a select',
                        '<bold>arraycode</bold>: it shows a php code with the definition of an array Ex: ["idfield"=0,"name"=>""]',
                        '<bold>csv</bold>: it returns a csv result',
                        '<bold>json</bold>: it returns the value of the queries as json'])
                    ->setDefault('')
                    ->setInput($interactive, 'optionshort', ['classcode', 'selectcode', 'arraycode', 'csv', 'json', 'createcode'])
                    ->add();
                break;
            case '':
                break;
            default:
                $this->inJectInitParam2($first->value, $interactive);
                break;
        }
        $ok=false;
        switch ($first->value) {
            case 'definition':
            case 'export':
            case 'generate':
            case '':
                if ($this->cli->getSTDIN() === null) {
                    $this->showLogo();
                }
                $loadconfig = $this->cli->evalParam('loadconfig');
                if ($loadconfig->value) {
                    [$ok, $data] = $this->cli->readData($loadconfig->value);
                    if ($ok === false || !is_array($data)) {
                        $this->cli->showCheck('ERROR', 'red', "unable to open file $loadconfig->value");
                    } else {
                        $this->cli->showCheck('OK', 'green', "Configuration PdoOneCli open $loadconfig->value");
                        $this->cli->setArrayParam($data
                            , [], ['databasetype', 'server', 'user', 'password', 'database', 'classdirectory', 'classpostfix', 'classnamespace']);
                        $this->tablexclass = $data['tablexclass'] ?? [];
                        $this->columnsTable = $data['columnsTable'] ?? [];
                        $this->columnsAlias = $data['columnsAlias'] ?? [];
                        $this->conversion = ($data['conversion'] === null || count($data['conversion']) === 0)
                            ? $this->convertReset()
                            : $data['conversion'];
                        $this->alias = $data['alias'] ?? [];
                        $this->extracolumn = $data['extracolumn'] ?? [];
                        $this->removecolumn = $config['removecolumn'] ?? [];
                    }
                }
                break;
            default:
                $this->injectLoadFile($first->value, $interactive);
                break;
        }
        if (!$ok) {// $first->value) {
            $database = $this->cli->evalParam('databasetype', $interactive, true);
            $server = $this->cli->evalParam('server', $interactive, true);
            $user = $this->cli->evalParam('user', $interactive, true);
            $pwd = $this->cli->evalParam('password', $interactive, true);
            $db = $this->cli->evalParam('database', $interactive, true);
        } else {
            $database = $this->cli->getValue('databasetype');
            $server = $this->cli->getValue('server');
            $user = $this->cli->getValue('user');
            $pwd = $this->cli->getValue('password');
            $db = $this->cli->getValue('database');
        }
        switch ($first->value) {
            case 'definition':
                $output = $this->cli->setErrorType('silent')->evalParam('output', false, true);
                break;
            case 'export':
                $input = $this->cli->setErrorType('silent')->evalParam('input', false, true);
                $output = $this->cli->setErrorType('silent')->evalParam('output', false, true);
                $namespace = $this->cli->evalParam('namespace', false, true);
                break;
            case 'generate':
                $input = '';
                $output = '';
                $namespace = '';
                break;
            case '':
                $interactive = false;
                $this->cli->showCheck('ERROR', 'red', "No command is set");
                break;
            default:
                $input = '';
                $output = '';
                $namespace = '';
                $this->injectEvalParam($first->value, $interactive);
                break;
        }
        $this->runCliGenerationParams();
        switch ($first->value) {
            case 'definition':
                if (!$this->help->missing) {
                    $this->showHelpDefinition();
                } else {
                    $this->runCliDefinition();
                }
                return;
            case 'generate':
                if (!$this->help->missing) {
                    $this->showHelpGenerate();
                } else {
                    $this->runCliGeneration();
                }
                return;
            case 'export':
                $this->runCliSaveConfig($interactive);
                if (!$this->help->missing || !$database) {
                    $this->showHelpExport();
                } else {
                    try {
                        $pdo = new PdoOne('test', '127.0.0.1', 'root', 'root', 'db'); // mockup database connection
                        $pdo->logLevel = 3;
                        /** @noinspection PhpUndefinedVariableInspection */
                        $this->cli->showLine($pdo->run($database, $server, $user, $pwd, $db, $input, $output, $namespace));
                    } catch (Exception $ex) {
                        $this->cli->showCheck('error', 'red', $ex->getMessage(), 'stderr');
                    }
                }
                break;
            case '':
                if (!$interactive) {
                    $this->cli->showParamSyntax2('Commands:', ['first'], [], null, null, 25);
                    $arr=$this->getArrayParameters();
                    $arr[]='overridegenerate';
                    $this->cli->showParamSyntax2('Flags common:',
                        ['flag', 'longflag'],
                        $arr
                        , null, 'common', 25);
                }
                return;
            default:
                $this->injectRunParam($first->value, $interactive);
                break;
        }
    } // end cliEngine()

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
    }

    /**
     * List of the parameters to store, read and display in the help.
     * @return string[]
     */
    protected function getArrayParameters():array {
        return ['classdirectory', 'classpostfix', 'classnamespace', 'tables', 'tablescolumns', 'tablecommand', 'convertionselected', 'convertionnewvalue', 'newclassname',];
    }

    protected function showHelpDefinition(): void
    {
        $this->cli->showParamSyntax2('Commands:', ['first'], [], null, null, 25);
        $this->cli->showParamSyntax2('Flags for definition:',
            ['flag', 'longflag'],
            $this->getArrayParameters()
            , null, 'export', 25);
    }

    protected function showHelpGenerate(): void
    {
        $this->cli->showParamSyntax2('Commands:', ['first'], [], null, null, 25);
        $this->cli->showParamSyntax2('Flags for generate:',
            ['flag', 'longflag'],
            $this->getArrayParameters()
            , null, 'generate', 25);
    }

    protected function showHelpExport(): void
    {
        $this->cli->showParamSyntax2('Commands:', ['first'], [], null, null, 25);
        $this->cli->showParamSyntax2('Flags for export:',
            ['flag', 'longflag'],
            $this->getArrayParameters()
            , null, 'export', 25);
    }

    protected function runCliConnection($force=false): ?PdoOne
    {
        if ($force===false &&!$this->cli->getValue('databasetype')) {
            return null;
        }
        if($force) {
            $this->cli->evalParam('databasetype',true);
            $this->cli->evalParam('server',true);
            $this->cli->evalParam('user',true);
            $this->cli->evalParam('password',true);
            $this->cli->evalParam('database',true);
        }
        $result = null;
        while (true) {
            try {
                $pdo = $this->createPdoInstance();
                if($pdo===null) {
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
            $this->cli->evalParam('databasetype', true);
            $this->cli->evalParam('server', true);
            $this->cli->evalParam('user', true);
            $this->cli->evalParam('password', true);
            $this->cli->evalParam('database', true);
        } // retry database.
        return $result;
    }

    protected function RunCliGenerationSaveConfig(): void
    {
        if ($this->cli->getParameter('command')->origin !== 'argument') {
            $sg = $this->cli->createParam('yn', [], 'none')
                ->setDescription('', 'Do you want to save the configurations entered in the CLI?')
                ->setInput(true, 'optionshort', ['yes', 'no'])
                ->setDefault('no')
                ->evalParam(true);
            if ($sg->value === 'yes') {
                $saveconfig = $this->cli->evalParam('saveconfig');
                if ($saveconfig->value) {
                    $r = $this->utilSaveConfig();
                    if ($r === '') {
                        $this->cli->showCheck('OK', 'green', 'file saved correctly');
                    }
                }
            }
        }
    }


    protected function databaseDetail(): void
    {
        $this->cli->upLevel('detail');
        while (true) {
            $this->cli->setColor(['byellow'])->showBread();
            //$tmp = $this->cli->getValue('tables');
            $this->cli->getParameter('classselected')
                ->setDescription('', 'Select a table to configure')
                ->setInput(true, 'option3', $this->tablexclass);
            $classselected = $this->cli->evalParam('classselected', true);
            if ($classselected->value === '') {
                $this->cli->downLevel();
                break; // return to command
            }
            //$oldnameclass = $classselected->value;
            $ktable = $classselected->valueKey;
            $this->cli->upLevel($ktable, '(table)');
            while (true) { // tablecommand
                $this->cli->setColor(['byellow'])->showBread();
                $tablecommand = $this->cli->evalParam('tablecommand', true);
                switch ($tablecommand->valueKey) {
                    case $this->cli->emptyValue:
                        //$this->cli->downLevel();
                        $this->cli->downLevel();
                        break 2; // while tablecommand
                    case 'rename':
                        $this->cli->upLevel('rename');
                        $this->cli->setColor(['byellow'])->showBread();
                        $this->cli->getParameter('newclassname')->setDefault($classselected->value);
                        $newclassname = $this->cli->evalParam('newclassname', true);
                        //$k=array_search($classselected->value,$classes,true);
                        //$classes[$k]=$newclassname->value;
                        $this->tablexclass[$ktable] = $newclassname->value;
                        $this->cli->downLevel();
                        break;
                    case 'remove':
                        $this->databaseConfigureRemove($ktable);
                        break;
                    case 'extracolumn':
                        $this->cli->upLevel('extracolumn');
                        while (true) {
                            $this->cli->setColor(['byellow'])->showBread();
                            $this->cli->showValuesColumn($this->extracolumn[$ktable], 'option2');
                            $ecc = $this->cli->createParam('extracolumncommand')
                                ->setAllowEmpty()
                                ->setInput(true, 'optionshort', ['add', 'remove'])
                                ->setDescription('', 'Select an operation')
                                ->evalParam(true);
                            switch ($ecc->value) {
                                case '':
                                    break 2;
                                case 'add':
                                    $tmp = $this->cli->createParam('extracolumn_name')
                                        //->setAllowEmpty()
                                        ->setInput()
                                        ->setDescription('', 'Select a name for the new column')
                                        ->evalParam(true);
                                    $tmp2 = $this->cli->createParam('extracolumn_sql')
                                        //->setAllowEmpty()
                                        ->setInput()
                                        ->setDescription('', 'Select a sql for the new column')
                                        ->evalParam(true);
                                    $this->extracolumn[$ktable][$tmp->value] = $tmp2->value;
                                    break;
                                case 'remove':
                                    $tmp = $this->cli->createParam('extracolumn_delete')
                                        ->setAllowEmpty()
                                        ->setInput(true, 'option2', $this->extracolumn[$ktable])
                                        ->setDescription('', 'Select a columne to delete')
                                        ->evalParam(true);
                                    if ($tmp->valueKey !== $this->cli->emptyValue) {
                                        unset($this->extracolumn[$ktable][$tmp->valueKey]);
                                    }
                                    break;
                            }
                        }
                        $this->cli->downLevel();
                        break;
                    case 'conversion':
                        $this->cli->upLevel('conversion');
                        while (true) {

                            $this->cli->setColor(['byellow'])->showBread();
                            $this->cli->getParameter('tablescolumns')
                                ->setDescription('', 'Select a column (or empty for end)')
                                ->setAllowEmpty()
                                ->setInput(true, 'option3', $this->columnsTable[$ktable]);
                            $tablecolumn = $this->cli->evalParam('tablescolumns', true);
                            if ($tablecolumn->value === '') {
                                // exit
                                break;
                            }
                            $this->cli->upLevel($tablecolumn->valueKey, ' (column)');
                            $this->cli->setColor(['byellow'])->showBread();
                            if ($tablecolumn->valueKey[0] === '_') {
                                $this->cli->getParameter('tablescolumnsvalue')
                                    ->setDescription('', 'Select a relation')
                                    ->setAllowEmpty()
                                    ->setRequired(false)
                                    ->setDefault($tablecolumn->value)
                                    ->setPattern('<cyan>[{key}]</cyan> {value}')
                                    ->setInput(true, 'option', [
                                        'PARENT' => 'The field is related (similar to MANYTONE) but it is not loaded recursively',
                                        'MANYTOMANY' => 'Many to many relation',
                                        'ONETOMANY' => 'One to many relation',
                                        'MANYTOONE' => 'Many to one relation',
                                        'ONETOONE' => 'One to one'
                                    ]);
                            } else {
                                $this->cli->getParameter('tablescolumnsvalue')
                                    ->setDescription('', 'Select a conversion')
                                    ->setDefault($tablecolumn->value)
                                    ->setAllowEmpty()
                                    ->setInput(true, 'option', [
                                        'string' => 'the value is converted to string',
                                        'encrypt' => 'encrypt the value',
                                        'decrypt' => 'decrypt the value',
                                        'datetime3' => 'date/time is convert from human readable to SQL format',
                                        'datetime4' => 'date/time is not converted',
                                        'datetime2' => 'date/time is converted from ISO to SQL format',
                                        'datetime' => 'date/time is converted from a DateTime PHP class to SQL format',
                                        'timestamp' => 'date/time is converted from timestamp to SQL format',
                                        'bool' => 'the value will be converted into a boolean (0=false,other=true)',
                                        'int' => 'the value will be converted into a int',
                                        'float' => 'the value will be converted into a float',
                                        'decimal' => 'the value will be converted into a float',
                                        'null' => 'pending.',
                                        'nothing' => "it does nothing"]);
                            }
                            $tablecolumnsvalue = $this->cli->evalParam('tablescolumnsvalue', true);
                            if ($tablecolumnsvalue->valueKey !== $this->cli->emptyValue) {
                                $this->columnsTable[$ktable][$tablecolumn->valueKey] = $tablecolumnsvalue->valueKey;
                            }
                            $this->cli->downLevel();
                        }
                        $this->cli->downLevel();
                        break;
                    case 'alias':
                        $this->cli->upLevel('alias');

                        while (true) {
                            $this->cli->setColor(['byellow'])->showBread();
                            $this->cli->getParameter('tablescolumns')
                                ->setDescription('', 'Select a column (or empty for end)')
                                ->setAllowEmpty()
                                ->setInput(true, 'option3', $this->columnsAlias[$ktable]);
                            $tablecolumn = $this->cli->evalParam('tablescolumns', true);
                            if ($tablecolumn->value === '') {
                                // exit
                                break;
                            }
                            $this->cli->upLevel($tablecolumn->valueKey, ' (column)');
                            $this->cli->setColor(['byellow'])->showBread();
                            $this->cli->getParameter('tablescolumnsalias')->setDefault($tablecolumn->value);
                            $tablescolumnalias = $this->cli->evalParam('tablescolumnsalias', true);
                            $this->columnsAlias[$ktable][$tablecolumn->valueKey] = $tablescolumnalias->value;
                            $this->cli->downLevel();
                        }
                        $this->cli->downLevel();
                        break;
                }
            } // end while tablecommand
        } // end while table
    }
    public function createPdoInstance(): ?PdoOne {
        try {
            $pdo = new PdoOne(
                $this->cli->getValue('databasetype'),
                $this->cli->getValue('server'),
                $this->cli->getValue('user'),
                $this->cli->getValue('password'),
                $this->cli->getValue('database'));
            $pdo->logLevel = 1;
            $pdo->connect();
        } catch(Exception $ex) {
            /** @noinspection PhpUndefinedVariableInspection */
            $this->cli->showCheck('ERROR','red',['Unable to connect to database',$pdo->lastError(),$pdo->errorText]);
            return null;
        }
        $pdo->logLevel = 2;
        return $pdo;
    }

    protected function databaseConfigureRemove($ktable): void
    {
        $this->cli->upLevel('remove');
        while (true) {
            $this->cli->setColor(['byellow'])->showBread();
            if (isset($this->removecolumn[$ktable])) {
                $this->cli->showValuesColumn($this->removecolumn[$ktable], 'option3');
            }
            $ecc = $this->cli->createParam('extracolumncommand')
                ->setAllowEmpty()
                ->setInput(true, 'optionshort', ['add', 'remove'])
                ->setDescription('', 'Do you want to add or remove a column from the remove-list')
                ->evalParam(true);
            switch ($ecc->value) {
                case '':
                    break 2;
                case 'add':
                    $tmp = $this->cli->createParam('extracolumn_name')
                        //->setAllowEmpty()
                        ->setInput(true, 'option3', array_keys($this->columnsTable[$ktable]))
                        ->setDescription('', 'Select a name of the column to remove')
                        ->evalParam(true);
                    $this->removecolumn[$ktable][] = $tmp->value;
                    break;
                case 'remove':
                    $tmp = $this->cli->createParam('extracolumn_delete')
                        ->setAllowEmpty()
                        ->setInput(true, 'option2', $this->removecolumn[$ktable])
                        ->setDescription('', 'Select a columne to delete')
                        ->evalParam(true);
                    if ($tmp->valueKey !== $this->cli->emptyValue) {
                        unset($this->removecolumn[$ktable][$tmp->valueKey - 1]);
                    }
                    // renumerate
                    $this->removecolumn[$ktable] = array_values($this->removecolumn[$ktable]);
                    break;
            }
        }
        $this->cli->downLevel();
    }

    protected function databaseConfigureXType(): void
    {
        $this->cli->upLevel('Configure x type');
        while (true) {
            $this->cli->setColor(['byellow'])->showBread();
            $this->cli->getParameter('convertionselected')
                ->setInput(true, 'option3', $this->conversion);
            $convertionselected = $this->cli->evalParam('convertionselected', true);
            if ($convertionselected->valueKey === $this->cli->emptyValue) {
                break;
            }
            $this->cli->upLevel($convertionselected->valueKey, ' (type)');
            $this->cli->setColor(['byellow'])->showBread();
            $convertionnewvalue = $this->cli->getParameter('convertionnewvalue')
                ->setDefault($convertionselected->value ?? '')
                ->evalParam(true);
            $this->conversion[$convertionselected->valueKey] = $convertionnewvalue->valueKey;
            $this->cli->downLevel();
        }
        $this->cli->downLevel();
    }

    protected function databaseFolder(): void
    {
        $this->cli->upLevel('folder');
        $this->cli->setColor(['byellow'])->showBread();
        $this->cli->evalParam('classdirectory', true);
        try {
            $configOk = @mkdir($this->cli->getValue('classdirectory'));
            if (!$configOk) {
                throw new RuntimeException('failed to create folder, maybe the folder already exists');
            }
            $this->cli->showCheck('OK', 'green', 'directory created');
        } catch (Exception $ex) {
            $this->cli->show('<yellow>');
            $this->cli->showCheck('note', 'yellow', 'Unable to create directory ' . $ex->getMessage());
            $this->cli->show('</yellow>');
            // $this->cli->showCheck('WARNING', 'yellow', 'unable to create directory ' . $ex->getMessage());
        }
        $this->cli->evalParam('classpostfix', true);
        // dummy.
        while (true) {
            $this->cli->showCheck('info', 'yellow', 'The target path is ' . getcwd() . '/' . $this->cli->getValue('classdirectory'));
            $this->cli->getParameter('classnamespace')->setDefault($this->cli->getValue('classnamespace'))->evalParam(true);
            $nameclass = '\\' . $this->cli->getValue('classnamespace') . '\\DummyClass';
            $filename = $this->cli->getValue('classdirectory') . '/DummyClass.php';
            $content = "<?php\nnamespace " . $this->cli->getValue('classnamespace') . ";\nclass DummyClass {}";
            try {
                $r = @file_put_contents($filename, $content);
                if ($r === false) {
                    throw new RuntimeException('Unable to write file ' . $filename);
                }
            } catch (Exception $ex) {
                $this->cli->showCheck('warning', 'yellow', 'Unable to create test class, ' . $ex->getMessage());
            }
            $ce = class_exists($nameclass, true);
            if ($ce) {
                $this->cli->showCheck('ok', 'green', 'Namespace tested correctly');
                @unlink($filename);
                break;
            }
            $this->cli->showCheck('warning', 'yellow', 'Unable test namespace');
            $tmp = $this->cli->createParam('yn', [], 'none')
                ->setDescription('', 'Do you want to retry?')
                ->setInput(true, 'optionshort', ['yes', 'no'])
                ->evalParam(true, true);
            if ($tmp === 'no') {
                break;
            }
        } // test namespace
        $this->cli->downLevel();
    }

    protected function databaseSave(): void
    {
        $sg = $this->cli->evalParam('savegen', true);
        if ($sg->value === 'yes') {
            $current = $this->cli->getParameter('saveconfig')->value ?: $this->cli->getParameter('loadconfig')->value;
            $saveconfig = $this->cli->getParameter('saveconfig')->setDefault($current)->setInput()->evalParam(true);
            if ($saveconfig->value) {
                $r = $this->utilSaveConfig();
                if ($r === '') {
                    $this->cli->showCheck('OK', 'green', 'file saved correctly');
                }
            }
        }
    }

    protected function databaseSelect($tablesmarked, $tables): void
    {
        $this->cli->upLevel('select');
        $this->cli->setColor(['byellow'])->showBread();
        $this->cli->getParameter('tables')
            ->setDefault($tablesmarked ?? [])
            ->setDescription('', 'Select or de-select a table to process')
            ->setInput(true, 'multiple2', $tables);
        $this->cli->evalParam('tables', true);
        $this->cli->downLevel();
    }

    protected function getFiles($path, $extension): array
    {
        $scanned_directory = array_diff(scandir($path), array('..', '.'));
        $scanned2 = [];
        foreach ($scanned_directory as $k) {
            if (@pathinfo($k)['extension'] === $extension) {
                $scanned2[$k] = $k;
            }
        }
        return $scanned2;
    }

    protected function runCliGenerationParams(): void
    {
        //[$configOk, $config] = $this->cli->readData('myconfig');
        $this->cli->createParam('savegen')
            ->setRequired(false)
            ->setDescription('save a configuration file', 'Do you want to save the configuration of generation?', [
                'Example:<dim> "-savegen myconfig"</dim>'])
            ->setDefault('yes')
            ->setInput(true, 'optionshort', ['yes', 'no'])
            ->add();
        $this->cli->createParam('command', ['cmd'])
            ->setRelated(['generate'])
            ->setArgument('longflag', true)
            ->setDescription('The command to run when we are generating a new code'
                , 'Select a command (empty for exit)'
                , ['<cyan><optionkey/></cyan>:<option/>'], 'cmd')
            ->setAllowEmpty()
            ->setInput(true, 'option', [
                'connect' => 'Connect to the database or change the connection',
                'folder' => 'Configure the repository folder and namespace',
                'scan' => 'Scan for changes to the database. It adds or removes tables and classes',
                'select' => 'Select or de-select the tables to work',
                'detail' => 'Configure each table and columns separately',
                'type' => 'Configure the conversion of the columns per type',
                'save' => 'Save the current configuration',
                'create' => 'Create the PHP repository classes (in non-interactive mode is done automatically)',
                'exit' => 'Save and exit'])
            ->add();
        $this->cli->createParam('tables')
            ->setDescription('', '')
            ->setInput(true, 'options', [])->add(true);
        $this->cli->createParam('tablescolumns')
            ->setDescription('', '')
            ->setAllowEmpty(false)
            ->setInput(true, 'options', [])->add();
        $this->cli->createParam('tablescolumnsvalue', [], 'none')
            ->setDescription('', '')
            ->setRequired(false)
            ->setAllowEmpty()
            ->setInput(true, 'string', [])->add();
        $this->cli->createParam('tablescolumnsalias', [], 'none')
            ->setDescription('', 'Select the new alias of the column. Use: PROPERCASE to set propercase')
            ->setRequired(false)
            ->setAllowEmpty()
            ->setInput(true, 'string', [])->add();
        $this->cli->createParam('classselected', [], 'none')
            ->setDescription('', 'Select a table to configure')
            ->setAllowEmpty()
            ->setInput(true, 'option3', [])->add();
        $this->cli->createParam('tablecommand')
            ->setDescription('', 'Select the command for the table')
            ->setAllowEmpty()
            ->setInput(true, 'option', [
                'rename' => 'rename the class from the table',
                'conversion' => 'A conversion of type per column',
                'alias' => 'rename a column',
                'extracolumn' => 'configure extra columns that could be read',
                'remove' => 'remove a column'
            ])->add();
        $this->cli->createParam('convertionselected')
            ->setDescription('', 'Select a type of data to convert')
            ->setAllowEmpty()
            ->setInput(true, 'option3', [])->add();
        $this->cli->createParam('convertionnewvalue')
            ->setDescription('', 'Select the conversion')
            ->setAllowEmpty()
            ->setInput(true, 'option', [
                'encrypt' => 'encrypt and decrypt the value',
                'decrypt' => 'encrypt and decrypt the value',
                'datetime3' => 'convert an human readable date to SQL',
                'datetime4' => 'no conversion, it keeps the format of SQL',
                'datetime2' => 'convert between ISO standard and SQL',
                'datetime' => 'convert between PHP Datetime object and SQL',
                'timestamp' => 'convert between a timestamp number and sql',
                'bool' => 'the value will be converted into a boolean (0,"" or null=false,other=true)',
                'int' => 'the value will be cast into a int',
                'float' => 'the value will be cast into a float',
                'decimal' => 'the value will be cast into a float',
                'null' => 'the value will be null',
                'nothing' => "it does nothing"])->add();
        $this->cli->createParam('newclassname')
            ->setDescription('', 'Select the name of the class')
            ->setInput(true, 'string', [])->add();
        $this->cli->createParam('overridegenerate', ['og'], 'longflag')
            ->setRelated(['generate'])
            ->setDefault('no')
            ->setDescription('Override the generate values', 'Do you want to override previous repository classes (abstract classes are always override)?'
                , ['Values available <cyan><option/></cyan>'], 'bool')
            ->setInput(true, 'optionshort', ['yes', 'no'])->add();
    }

    /**
     * @throws Exception
     */
    protected function runCliDefinition(): void
    {
        $this->cli->getParameter('databasetype')->setInput();
        $this->cli->getParameter('server')->setInput();
        $this->cli->getParameter('user')->setInput();
        $this->cli->getParameter('password')->setInput();
        $this->cli->getParameter('database')->setInput();
        $this->cli->evalParam('databasetype');
        $this->cli->evalParam('server');
        $this->cli->evalParam('user');
        $this->cli->evalParam('password');
        $this->cli->evalParam('database');
        $pdo = $this->runCliConnection();
        if ($pdo === null) {
            $this->cli->showCheck('CRITICAL', 'red', 'No connection');
            die(1);
        }
        $this->cli->show('<yellow>Please wait, reading tables... </yellow>');
        try {
            $tables = $pdo->objectList('table', true);
        } catch (Exception $e) {
            $this->cli->showCheck('CRITICAL', 'red', 'Unable to read tables');
            die(1);
        }
        $result = [];
        foreach ($tables as $table) {
            $result[$table] = $pdo->getDefTable($table);
        }
        $result = json_encode($result, JSON_PRETTY_PRINT);
        $output = $this->cli->getValue('output');
        if ($output) {
            try {
                $r = @file_put_contents($output, $result);
                if ($r === false) {
                    throw new RuntimeException('Unable to write file ' . $output);
                }
                $this->cli->showCheck('ok', 'green', "file generated [$output]");
            } catch (Exception $ex) {
                $this->cli->showCheck('error', 'red', 'Unable to create file, ' . $ex->getMessage());
            }
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    protected function runCliGeneration(): void
    {
        $this->cli->getParameter('databasetype')->setInput();
        $this->cli->getParameter('server')->setInput();
        $this->cli->getParameter('user')->setInput();
        $this->cli->getParameter('password')->setInput();
        $this->cli->getParameter('database')->setInput();
        $this->cli->evalParam('databasetype');
        $this->cli->evalParam('server');
        $this->cli->evalParam('user');
        $this->cli->evalParam('password');
        $this->cli->evalParam('database');

        $pdo = $this->runCliConnection();
        if ($pdo === null) {
            $this->cli->showCheck('CRITICAL', 'red', 'No connection');
            die(1);
        }
        $this->cli->show('<yellow>Please wait, reading tables... </yellow>');
        try {
            $tables = $pdo->objectList('table', true);
        } catch (Exception $e) {
            $this->cli->showCheck('CRITICAL', 'red', 'Unable to read tables');
            die(1);
        }
        $tablesmarked = $tables;
        if (count($this->tablexclass) === 0) {
            // no values, scanning...
            $this->databaseScan($tablesmarked, $pdo);
        }
        $this->cli->upLevel($this->cli->getParameter('database')->value, ' (db)');
        while (true) {
            $this->cli->setColor(['byellow'])->showBread();
            $com = $this->cli->getParameter('command')->evalParam(true); // evalParam('command');
            switch ($com->valueKey) {
                case 'end':
                case $this->cli->emptyValue:
                case '':
                case 'create':
                case 'convert':
                    if ($this->cli->getValue('classdirectory') && $this->cli->getValue('classnamespace')) {
                        $this->cli->evalParam('overridegenerate', true);
                        $pdo->generateCodeClassConversions($this->conversion);
                        $tmpTableXClass = [];
                        foreach ($this->tablexclass as $k => $v) {
                            $tmpTableXClass[$k] = $v . $this->cli->getValue('classpostfix');
                        }
                        $pdo->generateAllClasses($tmpTableXClass, ucfirst($this->cli->getValue('database')),
                            $this->cli->getValue('classnamespace'),
                            $this->cli->getValue('classdirectory'),
                            $this->cli->getValue('overridegenerate') === 'yes',
                            $this->columnsTable,
                            $this->extracolumn,
                            $this->removecolumn,
                            $this->columnsAlias
                        );
                        $this->RunCliGenerationSaveConfig();
                        $this->cli->showLine('<green>Done</green>');
                        break 2;
                    }
                    $this->cli->showCheck('ERROR', 'red', [
                        'you must set the directory and namespace',
                        'Use the option <bold><cyan>[folder]</cyan></bold> to set the directory and namespace'], 'stderr');
                    break;
                case 'connect':
                    $pdo=$this->runCliConnection(true);
                    break;
                case 'scan':
                    $this->databaseScan($tablesmarked, $pdo);
                    break;
                case 'folder':
                    $this->databaseFolder();
                    break;
                case 'save':
                    $this->databaseSave();
                    break;
                case 'type':
                    $this->databaseConfigureXType();
                    break;
                case 'select':
                    $this->databaseSelect($tablesmarked, $tables);
                    break;
                case 'detail':
                    $this->databaseDetail();
                    break;
                case 'exit':
                    break 2;
                default:
                    $this->injectEvalGenerate($com->valueKey);
                    break;
            }
            if ($this->cli->isParameterPresent('command') !== 'none') {
                break;
            }
        }
    }

    /** @noinspection DisconnectedForeachInstructionInspection */
    protected function databaseScan($tablesmarked, $pdo): void
    {

        $tablexclass = [];
        $columnsTable = [];
        $conversion = [];
        $extracolumn = [];
        //$this->removecolumn = [];
        $def2 = [];
        $pk = [];
        $this->cli->show('<yellow>Please wait, reading structure of tables... </yellow>');
        $this->cli->showWaitCursor();
        foreach ($tablesmarked as $table) {
            $this->cli->showWaitCursor(false);
            $class = PdoOne::tableCase($table);
            //$classes[] = $class;
            $tablexclass[$table] = $class;
            $extracolumn[$table] = [];
            $columns = $pdo->columnTable($table);
            foreach ($columns as $v) {
                $conversion[$v['coltype']] = null;
                $columnsTable[$table][$v['colname']] = null;
            }
            $pk[$table] = $pdo->getPK($table);

            if($pk[$table]===false) {
                $def2[$table] = $pdo->getRelations($table, null);
            } else {
                $def2[$table] = $pdo->getRelations($table, $pk[$table][0]);
            }
            foreach ($def2[$table] as $k => $v) {
                if (isset($v['key']) && $v['key'] !== 'FOREIGN KEY') {
                    $columnsTable[$table][$k] = $v['key'];
                }
            }
        }
        // The next lines are used for testing:
        //unset($tablexclass['actor']);
        //$tablexclass['newtable']='newtablerepo';
        //unset($columnsTable['city']['city']);
        //$columnsTable['city']['xxxx'] = 'new';
        // end testing
        $this->cli->showLine();
        ksort($conversion);
        // merge new with old
        // *** TABLEXCLASS
        if (count($this->tablexclass) !== 0) {
            foreach ($this->tablexclass as $table => $v) {
                if (!isset($tablexclass[$table])) {
                    $this->cli->showCheck('<bold>deleted</bold>', 'red', "table <bold>$table</bold> deleted");
                    unset($this->tablexclass[$table], $this->columnsTable[$table], $this->extracolumn[$table]);
                }
            }
            foreach ($tablexclass as $table => $v) {
                if (!isset($this->tablexclass[$table])) {
                    $this->cli->showCheck(' added ', 'green', "table <bold>$table</bold> added");
                    $class = PdoOne::tableCase($table);
                    $this->tablexclass[$table] = $class;
                    $this->extracolumn[$table] = [];
                }
            }
        } else {
            $this->tablexclass = $tablexclass;
        }
        // *** COLUMNSTABLE
        $this->columnsTable = $this->updateMultiArray($columnsTable, $this->columnsTable, 'Columns Table');
        if (count($this->columnsTable) === 0) {
            $this->columnsTable = $columnsTable;
        }
        $alias = $columnsTable;
        foreach ($columnsTable as $table => $columns) {
            foreach ($columns as $column => $v) {
                if ($column[0] !== '_') {
                    $alias[$table][$column] = $column;
                } else {
                    unset($alias[$table][$column]);
                }
            }
        }
        // add onetomany and onetoone alias
        foreach ($columnsTable as $ktable => $columns) {
            $pk = '??';
            $pk = $pdo->service->getPK($ktable, $pk);
            $pkFirst = (is_array($pk) && count($pk) > 0) ? $pk[0] : null;
            /** @noinspection PhpUnusedLocalVariableInspection */
            [$relation, $linked] = $pdo->generateGetRelations($ktable, $this->columnsTable, $pkFirst, $alias);
            foreach ($relation as $colDB => $defs) {
                if (!isset($alias[$ktable][$colDB])) {
                    $alias[$ktable][$colDB] = $defs['alias'];
                }
            }
        }
        //$oldAlias = $this->columnsAlias;
        //$this->columnsAlias = [];
        // **** COLUMNSALIAS
        $this->columnsAlias = $this->updateMultiArray($alias, $this->columnsAlias, 'Columns Alias');
        if (count($this->extracolumn) === 0) {
            $this->extracolumn = $extracolumn;
        }

    }

    /**
     *
     * @param array|null $oldArray
     * @param array|null $newArray
     * @param string     $name
     * @return array|null
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

    protected function runCliSaveConfig($interactive): void
    {
        if (!$this->cli->getValue('databasetype')) {
            // not enough data to save
            $this->cli->showCheck('ERROR', 'red', 'unable to save without type of database');
            return;
        }
        // if --saveconfig (empty), then it forces interactivity.
        $saveconfigpresent = $this->cli->isParameterPresent('saveconfig');
        if (!$interactive && ($saveconfigpresent === 'empty' || $saveconfigpresent === 'none')) {
            return;
        }
        switch ($saveconfigpresent) {
            case 'none':
            case 'empty':
                $sg = $this->cli->createParam('yn', [], 'none')
                    ->setDescription('', 'Do you want to save the configuration of the connection?')
                    ->setInput(true, 'optionshort', ['yes', 'no'])
                    ->setDefault('no')
                    ->evalParam(true);
                if ($sg->value === 'yes') {
                    $saveconfig = $this->cli->evalParam('saveconfig', true);
                    if ($saveconfig->value) {
                        $r = $this->utilSaveConfig();
                        if ($r === '') {
                            $this->cli->showCheck('OK', 'green', 'file saved correctly');
                        }
                    }
                }
                break;
            case 'value':
                // if --saveconfig is set, then it doesn't ask, just save
                $saveconfig = $this->cli->evalParam('saveconfig');
                if ($saveconfig->value) {
                    $r = $this->utilSaveConfig();
                    if ($r === '') {
                        $this->cli->showCheck('OK', 'green', 'file saved correctly');
                    }
                }
                break;
        }
    }

    protected function utilSaveConfig(): string
    {
        $config = $this->cli->getArrayParams([
            'saveconfig',
            'loadconfig',
            'generate',
            'export',
            'retry',
            'interactive',
            'command']);
        $configcli = [
            'tablexclass' => $this->tablexclass,
            'conversion' => $this->conversion,
            'alias' => $this->alias,
            'extracolumn' => $this->extracolumn,
            'removecolumn' => $this->removecolumn,
            'columnsTable' => $this->columnsTable,
            'columnsAlias' => $this->columnsAlias,
            //'classes' => $classes
        ];
        $config = array_merge($config, $configcli);
        return $this->cli->saveData($this->cli->getValue('saveconfig'), $config);
    }

}
