<?php /** @noinspection DuplicatedCode */

/** @noinspection PhpConditionAlreadyCheckedInspection */

namespace eftec;

use eftec\CliOne\CliOne;
use Exception;
use RuntimeException;

/**
 * Class pdoonecli
 * It is the CLI interface for PdoOne.<br>
 * Note, this class is called ON-PURPOSE in lowercase.<br>
 * <b>How to execute it?</b><br>
 * In the command line, runs the next line:<br>
 * <pre>
 * php folder/pdoonecli.php
 * </pre>
 *
 * @see           https://github.com/EFTEC/PdoOne
 * @package       eftec
 * @author        Jorge Castro Castillo
 * @copyright (c) Jorge Castro C. Dual Licence: MIT and Commercial License  https://github.com/EFTEC/PdoOne
 * @version       0.9
 */
class pdoonecli
{
    public const VERSION = '0.9';
//</editor-fold>
    /**
     * @var mixed
     */
    protected $tablexclass;
    /**
     * @var array|mixed
     */
    protected $conversion;
    /**
     * @var array|mixed
     */
    protected $extracolumn;
    /**
     * @var array|mixed
     */
    protected $removecolumn;
    /**
     * @var array
     */
    protected $columnsTable;
    /** @var CliOne */
    private $cli;

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
     * It executes the cli Engine.
     *
     * @throws Exception
     */
    public function cliEngine(): void
    {
        $this->cli = new CliOne(__FILE__);
        $this->cli->createParam('interactive', 'longflag', 'i')
            ->setRequired(false)
            ->setAllowEmpty()
            ->setDescription('Interactive', 'set the input interactively', [
                'Example: -interactive'])
            ->setInput(false)
            ->add();
        $interactive = !$this->cli->evalParam('interactive', false)->missing;
        $this->cli->createParam('databasetype', 'longflag', 'dt')
            ->setRequired(false)
            ->setDescription('The type of database', 'Select the type of database', [
                'Values allowed: <option/>'])
            ->setInput($interactive, 'optionshort', ['mysql', 'sqlsrv', 'oci', 'test'])
            ->setCurrentAsDefault()
            ->add();
        $this->cli->createParam('server', 'longflag', 'srv')
            ->setRequired(false)
            ->setDefault('127.0.0.1')
            ->setCurrentAsDefault()
            ->setDescription('The type of database', 'Select the database server', [
                'Example mysql: 127.0.0.1 , 127.0.0.1:3306',
                'Example sqlsrv: (local)\sqlexpress 127.0.0.1\sqlexpress'])
            ->setInput($interactive)
            ->add();
        $this->cli->createParam('user', 'longflag', 'u')
            ->setDescription('The username to access to the database', 'Select the username', ['Example: sa, root'])
            ->setRequired(false)
            ->setCurrentAsDefault()
            ->setInput($interactive)
            ->add();
        $this->cli->createParam('password', 'longflag', 'p')
            ->setRequired(false)
            ->setDescription('The password to access to the database', '', ['Example:12345'])
            ->setCurrentAsDefault()
            ->setInput($interactive, 'password')
            ->add();
        $this->cli->createParam('database', 'longflag', 'db')
            ->setRequired(false)
            ->setDescription('The database/schema', 'Select the database/schema', [
                'Example: sakila,contoso,adventureworks'])
            ->setDefault('')
            ->setCurrentAsDefault()
            ->setInput($interactive)
            ->add();
        $this->cli->createParam('input', 'longflag', 'in')
            ->setRequired(false)
            ->setDescription('The type of input', '', [
                'Example: -input "select * from table" = it runs a query',
                'Example: -input "table" = it runs a table (it could generates a query automatically)'
            ])
            ->setDefault('')
            ->setInput(false)
            ->add();
        $this->cli->createParam('output', 'longflag', 'out')
            ->setRequired(false)
            ->setDescription('The type of output', '', [
                'Values allowed: <option/>',
                '<bold>classcode</bold>: it returns php code with a CRUDL class',
                '<bold>selectcode</bold>: it shows a php code with a select',
                '<bold>arraycode</bold>: it shows a php code with the definition of an array Ex: ["idfield"=0,"name"=>""]',
                '<bold>csv</bold>: it returns a csv result',
                '<bold>json</bold>: it returns the value of the queries as json'])
            ->setDefault('')
            ->setInput(false, 'optionshort', ['classcode', 'selectcode', 'arraycode', 'csv', 'json', 'createcode'])
            ->add();
        $this->cli->createParam('namespace', 'longflag', 'ns')
            ->setRequired(false)
            ->setDescription('The namespace', '', [
                'Example: "customers"'])
            ->setDefault('')
            ->setInput(false)
            ->add();
        $listPHPFiles = $this->getFiles('.', 'php');
        $this->cli->createParam('loadconfig', 'longflag')
            ->setRequired(false)
            ->setDescription('Select the configuration file to load', '', [
                'Example: "--loadconfig myconfig"'])
            ->setDefault('')
            ->setInput(false, 'string', $listPHPFiles)
            ->add();
        $this->cli->createParam('saveconfig', 'longflag')
            ->setRequired(false)
            ->setCurrentAsDefault()
            ->setDescription('save a configuration file', 'Select the configuration file to save', [
                'Example: "--saveconfig myconfig"'])
            ->setDefault('')
            ->setInput($interactive, 'string', $listPHPFiles)
            ->add();
        $this->cli->createParam('cli')
            ->setRequired(false)
            ->setAllowEmpty()
            ->setDescription('It start the cli', '', [
                'Example: "-cli"'])
            ->setDefault('')
            ->setInput(false)
            ->add();
        $v = PdoOne::VERSION;
        $vc = self::VERSION;
        $this->cli->show("
 _____    _       _____           
|  _  | _| | ___ |     | ___  ___ 
|   __|| . || . ||  |  ||   || -_|
|__|   |___||___||_____||_|_||___|  
PdoOne: $v  Cli: $vc  

<yellow>Syntax:php " . basename(__FILE__) . " <args></yellow>

");
        $loadconfig = $this->cli->evalParam('loadconfig', false);
        if ($loadconfig->value) {
            [$ok, $data] = $this->cli->readData($loadconfig->value);
            if ($ok === false) {
                $this->cli->showCheck('ERROR', 'red', "unable to open file $loadconfig->value");
            } else {
                $this->cli->showCheck('OK', 'green', "configuration open $loadconfig->value");
                $this->cli->setArrayParam($data);
                $this->tablexclass = $data['tablexclass']??[];
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
        $input = $this->cli->evalParam('input', false, true);
        $output = $this->cli->evalParam('output', false, true);
        $namespace = $this->cli->evalParam('namespace', false, true);
        $this->RunCliConnection();
        $this->runCliSaveConfig($interactive);
        $cli = $this->cli->evalParam('cli', false);
        if (!$cli->missing) {
            $this->runCliGeneration();
            return;
        }
        $this->cli->showLine();
        if ($database === '' || $server === '' || $user === '' || $pwd === '' || $input === '' || $output === '') {
            if (!$interactive) {
                $this->cli->showParamSyntax('*', 0, 2, ['retry']);
            }
            return;
        }
        $pdo = new PdoOne('test', '127.0.0.1', 'root', 'root', 'db'); // mockup database connection
        $pdo->logLevel = 3;
        $this->cli->showLine($pdo->run($database, $server, $user, $pwd, $db, $input, $output, $namespace));
    }

    protected function RunCliConnection(): ?PdoOne
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
                $this->cli->showCheck('OK', 'green', 'Connected to the database');
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
        }
        return $result;
    }

    protected function RunCliGenerationSaveConfig($config): void
    {
        //$sg = $this->cli->evalParam('savegen', true);
        //if ($sg->value === 'yes') {
        $saveconfig = $this->cli->evalParam('saveconfig');
        if ($saveconfig->value) {
            $arr = $this->cli->getArrayParams(['saveconfig', 'loadconfig', 'cli', 'retry']);
            $arr = array_merge($arr, $config);
            $r = $this->cli->saveData($saveconfig->value, $arr);
            if ($r === '') {
                $this->cli->showCheck('OK', 'green', 'file saved correctly');
            }
        }
        //}
    }

    protected function createConfig(): array
    {
        return [
            'tablexclass' => $this->tablexclass,
            'conversion' => $this->conversion,
            'extracolumn' => $this->extracolumn,
            'removecolumn' => $this->removecolumn,
            'columnsTable' => $this->columnsTable,
            //'classes' => $classes,
            'database' => $this->cli->getValue('database'),
            'classnamespace' => $this->cli->getValue('classnamespace'),
            'classdirectory' => $this->cli->getValue('classdirectory'),
            'classoverride' => $this->cli->getValue('classoverride'),
        ];
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

    protected function databaseSave(): void
    {
        $config = $this->createConfig();
        $sg = $this->cli->evalParam('savegen', true);
        if ($sg->value === 'yes') {
            $filename = $this->cli->createParam('filename', 'none')
                ->setDescription('', 'Select the filename (without extension)')
                ->setInput(true)
                ->evalParam(true);
            if ($filename->value) {
                $r = $this->cli->saveData($filename->value, $config);
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

    /**
     * @return void
     * @throws Exception
     */
    protected function runCliGeneration(): void
    {
        //[$configOk, $config] = $this->cli->readData('myconfig');
        $this->cli->createParam('savegen')
            ->setRequired(false)
            ->setDescription('save a configuration file', 'Do you want to save the configuration of generation?', [
                'Example: "-savegen myconfig"'])
            ->setDefault('yes')
            ->setInput(true, 'optionshort', ['yes', 'no'])
            ->add();
        $this->cli->createParam('namerepo')
            ->setDescription('', 'Select the name of the class repository')
            ->setInput(true)->add();
        $this->cli->createParam('command')
            ->setDescription('', 'Select a command (empty for exit)')
            ->setAllowEmpty()
            ->setInput(true, 'option', [
                'select' => 'Select the tables to work',
                'configure' => 'Configure per table',
                'convertxtype' => 'Configure per type of column',
                'save' => 'Save the current configuration',
                'end' => 'End this menu and save the project'])->add();
        $this->cli->createParam('tables')
            ->setDescription('', '')
            ->setInput(true, 'options', [])->add();
        $this->cli->createParam('tablescolumns')
            ->setDescription('', '')
            ->setAllowEmpty(false)
            ->setInput(true, 'options', [])->add();
        $this->cli->createParam('tablescolumnsvalue')
            ->setDescription('', '')
            ->setRequired(false)
            ->setAllowEmpty(true)
            ->setInput(true, 'string', [])->add();
        $this->cli->createParam('classselected')
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
        $this->cli->createParam('classdirectory')
            ->setDescription('', 'Select the relative directory to create the classes')
            ->setInput(true)->add();
        $this->cli->createParam('classnamespace')
            ->setDescription('', 'Select the namespace of the classes')
            ->setInput(true)->add();
        $this->cli->createParam('classoverride')
            ->setDescription('', 'Do you want to override previous generated classes?')
            ->setInput(true, 'optionshort', ['yes', 'no'])->add();
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
        $pdo = $this->RunCliConnection();
        if ($pdo === null) {
            $this->cli->showCheck('CRITICAL', 'error', 'No connection');
            die(1);
        }
        try {
            $tables = $pdo->objectList('table', true);
        } catch (Exception $e) {
            $this->cli->showCheck('CRITICAL', 'error', 'Unable to read tables');
            die(1);
        }
        $tablesmarked = $tables;
        //$classes=[];
        $this->tablexclass = [];
        $this->columnsTable = [];
        $this->conversion = [];
        $this->extracolumn = [];
        $this->removecolumn = [];
        $def2 = [];
        $pk = [];
        $this->cli->show('<yellow>Please wait, reading structure of tables... </yellow>');
        $this->cli->showWaitCursor(true);
        foreach ($tablesmarked as $table) {
            $this->cli->showWaitCursor(false);
            $class = PdoOne::tableCase($table) . 'Repo';
            //$classes[] = $class;
            $this->tablexclass[$table] = $class;
            $this->extracolumn[$table] = [];
            $columns = $pdo->columnTable($table);
            foreach ($columns as $v) {
                $this->conversion[$v['coltype']] = null;
                $this->columnsTable[$table][$v['colname']] = null;
            }
            $pk[$table] = $pdo->getPK($table);
            $def2[$table] = $pdo->getRelations($table, $pk[$table][0]);
            foreach ($def2[$table] as $k => $v) {
                if (isset($v['key']) && $v['key'] !== 'FOREIGN KEY') {
                    $this->columnsTable[$table][$k] = $v['key'];
                }
            }
        }
        $this->cli->showLine();
        ksort($this->conversion);
        $this->cli->upLevel($this->cli->getParameter('database')->value, ' (db)');
        while (true) {
            $this->cli->setColor(['byellow'])->showBread();
            $com = $this->cli->evalParam('command', true);
            switch ($com->valueKey) {
                case 'end':
                case $this->cli->emptyValue:
                case '':
                    break 2;
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
            }
        }
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
            $tmp = $this->cli->createParam('yn', 'none')
                ->setDescription('', 'Do you want to retry?')
                ->setInput(true, 'optionshort', ['yes', 'no'])
                ->evalParam(true, true);
            if ($tmp === 'no') {
                break;
            }
        } // test namespace
        $this->cli->evalParam('classoverride', true);
        $pdo->generateAllClasses($this->tablexclass, ucfirst($this->cli->getValue('database')),
            $this->cli->getValue('classnamespace'),
            $this->cli->getValue('classdirectory'),
            $this->cli->getValue('classoverride') === 'yes',
            $this->columnsTable,
            $this->extracolumn,
            $this->removecolumn
        );
        $config = $this->createConfig();
        $this->RunCliGenerationSaveConfig($config);
        $this->cli->showLine('<green>Done</green>');
    }

    protected function runCliSaveConfig($interactive): void
    {
        if (!$this->cli->getValue('databasetype')) {
            return;
        }
        // if --saveconfig (empty), then it forces interactivity.
        $saveconfigpresent = $this->cli->isParameterPresent('saveconfig');
        if (!$interactive || $saveconfigpresent === 'empty') {
            return;
        }
        switch ($saveconfigpresent) {
            case 'none':
            case 'empty':
                $sg = $this->cli->createParam('yn', 'none')
                    ->setDescription('', 'Do you want to save the configuration of the connection?')
                    ->setInput(true, 'optionshort', ['yes', 'no'])
                    ->setDefault('yes')
                    ->evalParam(true);
                if ($sg->value === 'yes') {
                    $saveconfig = $this->cli->evalParam('saveconfig', true);
                    if ($saveconfig->value) {
                        $arr = $this->cli->getArrayParams(['saveconfig', 'loadconfig', 'cli', 'retry']);
                        $r = $this->cli->saveData($saveconfig->value, $arr);
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
                    $arr = $this->cli->getArrayParams(['saveconfig', 'loadconfig', 'cli', 'retry']);
                    $r = $this->cli->saveData($saveconfig->value, $arr);
                    if ($r === '') {
                        $this->cli->showCheck('OK', 'green', 'file saved correctly');
                    }
                }
                break;
        }
    }

}


// this code only runs on CLI but only if pdoonecli.php is called directly and via command line.
if (!defined('PHPUNIT_COMPOSER_INSTALL') && !defined('__PHPUNIT_PHAR__')
    && isset($_SERVER['PHP_SELF']) &&
    pdoonecli::isCli() &&
    ( basename($_SERVER['PHP_SELF']) === 'pdoonecli.php' || basename($_SERVER['PHP_SELF']) === 'pdoonecli')
) {
    // we also excluded it if it is called by phpunit.
    $path = pdoonecli::findVendorPath();
    /** @noinspection PhpIncludeInspection */
    include_once __DIR__ . '/' . $path . '/autoload.php';
    $cli = new pdoonecli();
    /** @noinspection PhpUnhandledExceptionInspection */
    $cli->cliEngine();
}

