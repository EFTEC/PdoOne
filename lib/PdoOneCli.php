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
 * @version       0.10
 */
class PdoOneCli
{
    public const VERSION = '0.10';
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
    protected $extracolumn = [];
    /**
     * @var array
     */
    protected $removecolumn = [];
    /**
     * @var array
     */
    protected $columnsTable = [];
    /** @var CliOne */
    public $cli;
    /**
     * @var CliOneParam
     */
    protected $help;

    public function __construct()
    {
        $this->cli = new CliOne();
    }

    public function getCli(): CliOne
    {
        return $this->cli;
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
    protected function injectInitParam() : void {
    }
    protected function InjectInitParam2($firstCommand,$interactive):void {

    }
    protected function injectEvalParam($firstCommand,$interactive):void {

    }

    protected function injectRunParam($firstCommand,$interactive):void {

    }
    protected function injectEvalGenerate($command) : void
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
        $this->help = $this->cli->evalParam('help', false);

        $this->cli->createParam('first',[], 'command')
            ->setRelated([])
            ->setRequired(false)
            ->setAllowEmpty()
            ->setDescription('', '',[])
            ->setInput(false)
            ->add();
        $first = $this->cli->evalParam('first', false);

        $this->cli->createParam('interactive', 'i', 'longflag')
            ->setRelated(['common', 'export', 'generate'])
            ->setRequired(false)
            ->setAllowEmpty()
            ->setDescription('Interactive', 'set the input interactively', [
                'Example: <dim>-interactive</dim>'])
            ->setInput(false)
            ->add();
        $interactive = !$this->cli->evalParam('interactive', false)->missing;
        $this->cli->createParam('generate', [], 'first')
            ->setRequired(false)
            ->setAllowEmpty()
            ->setDescription('It generates the repository classes', '', [
                'Example: <dim>"generate --loadconfig myconfig"</dim>.Load a config and generate in interactive mode',
                'Example: <dim>"generate --command scan --loadconfig .\p2.php -og yes"</dim>. Load a config, scan for changes and override'])
            ->setDefault('')
            ->setInput(false)
            ->add();
        $this->cli->evalParam('generate', false);
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
            ->setDescription('', 'Select the relative directory to create the classes')
            ->setInput(true)->add();
        $this->cli->createParam('classnamespace')
            ->setCurrentAsDefault()
            ->setDescription('', 'Select the namespace of the classes')
            ->setInput(true)->add();

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
            case 'export':
                $this->cli->createParam('input', 'in', 'longflag')
                    ->setRelated(['export'])
                    ->setRequired(false)
                    ->setDescription('The type of input', '', [
                        'Example: <dim>-input "select * from table"</dim> = it takes a query as input value',
                        'Example: <dim>-input "table"</dim> = it takkes a table as input value'
                    ])
                    ->setDefault('')
                    ->setInput( $interactive)
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
                $this->inJectInitParam2($first->value,$interactive);
                break;
        }


        $this->showLogo();


        $loadconfig = $this->cli->evalParam('loadconfig', false);
        if ($loadconfig->value) {
            [$ok, $data] = $this->cli->readData($loadconfig->value);
            if ($ok === false) {
                $this->cli->showCheck('ERROR', 'red', "unable to open file $loadconfig->value");
            } else {
                $this->cli->showCheck('OK', 'green', "configuration open $loadconfig->value");
                $this->cli->setArrayParam($data
                    , [], ['databasetype', 'server', 'user', 'password', 'database', 'classdirectory', 'classnamespace']);
                $this->tablexclass = $data['tablexclass'] ?? [];
                $this->columnsTable = $data['columnsTable'] ?? [];
                $this->conversion = $data['conversion'] ?? [];
                $this->extracolumn = $data['extracolumn'] ?? [];
                $this->removecolumn = $config['removecolumn'] ?? [];
            }
        }
        $database = $this->cli->getParameter('databasetype')
            ->evalParam($interactive, true);
        $server = $this->cli->evalParam('server', $interactive, true);
        $user = $this->cli->evalParam('user', $interactive, true);
        $pwd = $this->cli->evalParam('password', $interactive, true);
        $db = $this->cli->evalParam('database', $interactive, true);
        switch ($first->value) {
            case 'export':
                $input = $this->cli->setErrorType('silent')->evalParam('input', false, true);
                $output = $this->cli->setErrorType('silent')->evalParam('output', false, true);
                $namespace = $this->cli->evalParam('namespace', false, true);
                break;
            case 'generate':
                $input='';
                $output='';
                $namespace='';
                break;
            default:
                $input='';
                $output='';
                $namespace='';
                $this->injectEvalParam($first->value,$interactive);
                break;
        }


        $this->runCliGenerationParams();
        switch ($first->value) {
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
                        $this->cli->showLine($pdo->run($database, $server, $user, $pwd, $db, $input, $output, $namespace));
                    } catch (Exception $ex) {
                        $this->cli->showCheck('error', 'red', $ex->getMessage(), 'stderr');
                    }
                }
                break;
            case '':
                if (!$interactive) {
                    $this->cli->showParamSyntax2('Commands:', ['first'], [], null, null, 25);
                    $this->cli->showParamSyntax2('Flags common:',
                        ['flag', 'longflag'],
                        ['classdirectory',
                            'classnamespace',
                            'tables',
                            'tablescolumns',
                            'tablecommand',
                            'convertionselected',
                            'convertionnewvalue',
                            'newclassname',
                            'overridegenerate'
                        ]
                        , null, 'common', 25);
                }
                return;
            default:

                $this->injectRunParam($first->value,$interactive);
                break;
        }


    } // end cliEngine()

    protected function showLogo():void
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

    protected function showHelpGenerate(): void
    {
        $this->cli->showParamSyntax2('Commands:', ['first'], [], null, null, 25);
        $this->cli->showParamSyntax2('Flags for generate:',
            ['flag', 'longflag'],
            ['classdirectory',
                'classnamespace',
                'tables',
                'tablescolumns',
                'tablecommand',
                'convertionselected',
                'convertionnewvalue',
                'newclassname',
            ]
            , null, 'generate', 25);
    }

    protected function showHelpExport(): void
    {
        $this->cli->showParamSyntax2('Commands:', ['first'], [], null, null, 25);
        $this->cli->showParamSyntax2('Flags for export:',
            ['flag', 'longflag'],
            ['classdirectory',
                'classnamespace',
                'tables',
                'tablescolumns',
                'tablecommand',
                'convertionselected',
                'convertionnewvalue',
                'newclassname',
            ]
            , null, 'export', 25);
    }

    protected function runCliConnection(): ?PdoOne
    {
        if (!$this->cli->getValue('databasetype')) {
            return null;
        }
        $result = null;
        while (true) {
            try {
                $pdo = new PdoOne(
                    $this->cli->getValue('databasetype'),
                    $this->cli->getValue('server'),
                    $this->cli->getValue('user'),
                    $this->cli->getValue('password'),
                    $this->cli->getValue('database'));
                $pdo->logLevel = 3;
                $pdo->connect();
                $this->cli->showCheck('OK', 'green', 'Connected to the database <bold>' . $this->cli->getValue('database') . '</bold>');
                $result = $pdo;
                break;
            } catch (Exception $ex) {
                $this->cli->showCheck('ERROR', 'red', 'Unable to connect to the database: ' . $ex->getMessage());
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
        if($this->cli->getParameter('command')->origin!=='argument') {
            $sg = $this->cli->createParam('yn', [], 'none')
                ->setDescription('', 'Do you want to save the configuration of the connection?')
                ->setInput(true, 'optionshort', ['yes', 'no'])
                ->setDefault('yes')
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


    protected function databaseConfigure(): void
    {
        $this->cli->upLevel('configure');
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
                                        ->setInput(true)
                                        ->setDescription('', 'Select a name for the new column')
                                        ->evalParam(true);
                                    $tmp2 = $this->cli->createParam('extracolumn_sql')
                                        //->setAllowEmpty()
                                        ->setInput(true)
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
                                    ->setAllowEmpty(true)
                                    ->setRequired(false)
                                    ->setDefault($tablecolumn->value)
                                    ->setPattern('<cyan>[{key}]</cyan> {value}')
                                    ->setInput(true, 'option', [
                                        'PARENT' => 'Same than MANYTONE without the recursivity',
                                        'MANYTOMANY' => 'Many to many',
                                        'ONETOMANY' => 'One to many relation',
                                        'MANYTOONE' => 'Many to one',
                                        'ONETOONE' => 'One to one'
                                    ]);
                            } else {
                                $this->cli->getParameter('tablescolumnsvalue')
                                    ->setDescription('', 'Select a conversion')
                                    ->setDefault($tablecolumn->value)
                                    ->setAllowEmpty()
                                    ->setInput(true, 'option', [
                                        'encrypt' => 'encrypt the value',
                                        'decrypt' => 'decrypt the value',
                                        'datetime3' => 'datetime3',
                                        'datetime4' => 'datetime3',
                                        'datetime2' => 'datetime3',
                                        'datetime' => 'datetime3',
                                        'timestamp' => 'datetime3',
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
                }
            } // end while tablecommand
        } // end while table
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

    protected function databaseConvertXType(): void
    {
        $this->cli->upLevel('convertxtype');
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
        // dummy
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
            $saveconfig = $this->cli->getParameter('saveconfig')->setDefault($current)->setInput(true)->evalParam(true);
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
                'folder' => 'Configure the folders and namespaces',
                'scan' => 'Scan for changes to the database. It adds or removes tables and classes',
                'select' => 'Select or de-select the tables to work',
                'configure' => 'Configure each table and repository separately',
                'convertxtype' => 'Configure all columns per type of data',
                'save' => 'Save the current configuration',
                'convert' => 'Convert and exit (in non-interactive mode is done automatically)'])
            ->add();
        $this->cli->createParam('tables')
            ->setDescription('', '')
            ->setInput(true, 'options', [])->add();
        $this->cli->createParam('tablescolumns')
            ->setDescription('', '')
            ->setAllowEmpty(false)
            ->setInput(true, 'options', [])->add();
        $this->cli->createParam('tablescolumnsvalue', [], 'none')
            ->setDescription('', '')
            ->setRequired(false)
            ->setAllowEmpty(true)
            ->setInput(true, 'string', [])->add();
        $this->cli->createParam('classselected', [], 'none')
            ->setDescription('', 'Select a table to configure')
            ->setAllowEmpty()
            ->setInput(true, 'option3', [])->add();
        $this->cli->createParam('tablecommand')
            ->setDescription('', 'Select the command for the table')
            ->setAllowEmpty(true)
            ->setInput(true, 'option', [
                'rename' => 'rename the class from the table',
                'conversion' => 'column conversion',
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
            ->setDescription('Override the generate values', 'Do you want to override previous generated classes?'
                , ['Values available <cyan><option/></cyan>'], 'bool')
            ->setInput(true, 'optionshort', ['yes', 'no'])->add();
    }

    /**
     * @return void
     * @throws Exception
     */
    protected function runCliGeneration(): void
    {
        $this->cli->getParameter('databasetype')->setInput(true, 'optionshort', ['mysql', 'sqlsrv', 'oci', 'test']);
        $this->cli->getParameter('server')->setInput(true);
        $this->cli->getParameter('user')->setInput(true);
        $this->cli->getParameter('password')->setInput(true);
        $this->cli->getParameter('database')->setInput(true);
        $this->cli->evalParam('databasetype', false);
        $this->cli->evalParam('server', false);
        $this->cli->evalParam('user', false);
        $this->cli->evalParam('password', false);
        $this->cli->evalParam('database', false);
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
                case 'convert':
                    if($this->cli->getValue('classdirectory') && $this->cli->getValue('classnamespace')) {
                        break 2;
                    }
                    $this->cli->showCheck('ERROR','red',[
                        'you must set the directory and namespace',
                        'Use the option <bold><cyan>[folder]</cyan></bold> to set the directory and namespace'],'stderr');
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
                case 'convertxtype':
                    $this->databaseConvertXType();
                    break;
                case 'select':
                    $this->databaseSelect($tablesmarked, $tables);
                    break;
                case 'configure':
                    $this->databaseConfigure();
                    break;
                default:
                    $this->injectEvalGenerate($com->valueKey);
                    break;
            }
            if ($this->cli->isParameterPresent('command') !== 'none') {
                break;
            }
        }
        $this->cli->evalParam('overridegenerate', true);
        $pdo->generateAllClasses($this->tablexclass, ucfirst($this->cli->getValue('database')),
            $this->cli->getValue('classnamespace'),
            $this->cli->getValue('classdirectory'),
            $this->cli->getValue('overridegenerate') === 'yes',
            $this->columnsTable,
            $this->extracolumn,
            $this->removecolumn
        );
        $this->RunCliGenerationSaveConfig();
        $this->cli->showLine('<green>Done</green>');
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
        $this->cli->showWaitCursor(true);
        foreach ($tablesmarked as $table) {
            $this->cli->showWaitCursor(false);
            $class = PdoOne::tableCase($table) . 'Repo';
            //$classes[] = $class;
            $tablexclass[$table] = $class;
            $extracolumn[$table] = [];
            $columns = $pdo->columnTable($table);
            foreach ($columns as $v) {
                $conversion[$v['coltype']] = null;
                $columnsTable[$table][$v['colname']] = null;
            }
            $pk[$table] = $pdo->getPK($table);
            $def2[$table] = $pdo->getRelations($table, $pk[$table][0]);
            foreach ($def2[$table] as $k => $v) {
                if (isset($v['key']) && $v['key'] !== 'FOREIGN KEY') {
                    $columnsTable[$table][$k] = $v['key'];
                }
            }
        }
        // this lines are used for testing
        //unset($tablexclass['actor']);
        //$tablexclass['newtable']='newtablerepo';
        //unset($columnsTable['city']['city']);
        //$columnsTable['city']['xxxx'] = 'new';
        // end testing
        $this->cli->showLine();
        ksort($conversion);
        // merge new with old
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
                    $class = PdoOne::tableCase($table) . 'Repo';
                    $this->tablexclass[$table] = $class;
                    $this->extracolumn[$table] = [];
                }
            }
            //$this->tablexclass=$oldTablexClass;
        } else {
            $this->tablexclass = $tablexclass;
        }
        if (count($this->columnsTable) !== 0) {
            foreach ($this->columnsTable as $table => $columns) {
                foreach ($columns as $column => $v) {
                    if (!array_key_exists($column, $columnsTable[$table])) {
                        $this->cli->showCheck('<bold>deleted</bold>', 'red', "column <bold>$table.$column</bold> deleted");
                        unset($this->columnsTable[$table][$column]);
                    }
                }
            }
            foreach ($columnsTable as $table => $columns) {
                foreach ($columns as $column => $v) {
                    if (isset($this->columnsTable[$table]) && !array_key_exists($column, $this->columnsTable[$table])) {
                        $this->cli->showCheck(' added ', 'green', "column <bold>$table.$column</bold> added");
                        $this->columnsTable[$table][$column] = $v;
                        //unset($this->tablexclass[$table], $this->columnsTable[$table], $this->extracolumn[$table]);
                    }
                }
            }
        } else {
            $this->columnsTable = $columnsTable;
        }
        if (count($this->columnsTable) === 0) {
            $this->columnsTable = $columnsTable;
        }
        if (count($this->extracolumn) === 0) {
            $this->extracolumn = $extracolumn;
        }
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
                    ->setDefault('yes')
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
                $saveconfig = $this->cli->evalParam('saveconfig', false);
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
            'extracolumn' => $this->extracolumn,
            'removecolumn' => $this->removecolumn,
            'columnsTable' => $this->columnsTable,
            //'classes' => $classes
        ];
        $config = array_merge($config, $configcli);
        return $this->cli->saveData($this->cli->getValue('saveconfig'), $config);
    }

}
