<?php

namespace Support3w\Api\Command;

use Doctrine\DBAL\Connection;
use Stringy\Stringy as S;
use Doctrine\DBAL\DriverManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateApiFromDb extends Command
{

    /**
     * @var array
     */
    private $params;

    /**
     * @var array
     */
    private $typeMapping;

    /**
     * @var string
     */
    private $apiNamespace;

    /**
     * @var string
     */
    private $modelDestinationDirectory;

    /**
     * @var string
     */
    private $controllerDestinationDirectory;

    public function __construct(array $params)
    {
        parent::__construct();
        $this->params = $params;
        $this->typeMapping = array(
            'int' => 'integer',
            'smallint' => 'integer',
            'double' => 'integer',
            'decimal' => 'integer',
            'bigint' => 'integer',
            'tinyint' => 'boolean',
            'text' => 'string',
            'longtext' => 'string',
            'varchar' => 'string',
            'datetime' => '\Datetime',
            'time' => 'string'
        );
    }

    protected function configure()
    {
        $this
            ->setName('support3w:generate-api-from-db')
            ->setDescription('Generate Models, Controllers and repositories, ready to use.')
            ->addArgument(
                'api_namespace',
                InputArgument::REQUIRED,
                'Specify the namespace of your API wrapped in double quotes. Note that the script will add \Model and \Controller at the end of it during the generation.'
            )
            ->addArgument(
                'table_name',
                InputArgument::OPTIONAL,
                'Specify table_name if you want to generate classes only for 1 resource.'
            )
            ->addOption(
                'write_in_src_folder',
                null,
                InputOption::VALUE_NONE,
                'if --write_in_src_folder is provided, generation script will overwrite all the content in src'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        // CLI arguments
        $this->apiNamespace = $input->getArgument('api_namespace');
        $tableName = $input->getArgument('table_name');
        $writeInSourceFolder = $input->getOption('write_in_src_folder');

        // PATHs for destination of generated files
        $this->modelDestinationDirectory = __DIR__ . '/../../../../../generated/Model';
        $this->controllerDestinationDirectory = __DIR__ . '/../../../../../generated/Controller';

        if($writeInSourceFolder) {
            $this->modelDestinationDirectory = __DIR__ . '/../../../../../src/Model';
            $this->controllerDestinationDirectory = __DIR__ . '/../../../../../src/Controller';
        }

        $informationSchemaParams = $this->params;
        $informationSchemaParams['dbname'] = 'information_schema';

        $informationSchemaConnection = DriverManager::getConnection($informationSchemaParams);
        $data = $informationSchemaConnection->fetchAll(
            'SELECT TABLE_NAME FROM TABLES WHERE TABLE_SCHEMA = ?',
            array($this->params['dbname'])
        );

        $tableNames = array_map(function ($data) {
            return $data['TABLE_NAME'];
        }, $data);

        if ($tableName) {
            $output->writeln('Generating model for table ' . $tableName);

            if (!in_array($tableName, $tableNames)) {
                throw new \Exception('Provided table_name does not exist in schema.');
            }

            // overwrite tableNames so we fetch only the table_name provided
            $tableNames = array($tableName);
        }

        foreach ($tableNames as $tableName) {
            $this->generateControllers($tableName);
            $this->generateModels($informationSchemaConnection, $tableName);
        }

        $output->writeln('Generation done !');
    }

    public function generateControllers($tableName) {
        $tableNamePascalCase = S::create($tableName)->upperCamelize()->__toString();
        $controllerSample = file_get_contents(__DIR__ . '/Sample/ControllerSample.php');

        $controllerSample = str_replace(array('Support3w\Api\Command\Sample'), $this->apiNamespace . '\Controller', $controllerSample);
        $controllerSample = str_replace('ControllerSample', $tableNamePascalCase . 'Controller', $controllerSample);

        if (!file_exists($this->controllerDestinationDirectory)) {
            mkdir($this->controllerDestinationDirectory, 0777, true);
        }

        $destinationPath = $this->controllerDestinationDirectory . '/' . $tableNamePascalCase . 'Controller.php';
        file_put_contents($destinationPath, $controllerSample);
    }

    private function generateModels(Connection $informationSchemaConnection, $tableName)
    {

        $fieldsToIgnored = array('id', 'deleted');
        $tableInformations = $informationSchemaConnection->fetchAll(
            'SELECT COLUMN_NAME, IS_NULLABLE, DATA_TYPE, COLUMN_DEFAULT FROM COLUMNS WHERE TABLE_NAME =  ?',
            array($tableName)
        );

        $propertiesGeneration = '';
        $defaultValuesGeneration = '';
        $tableNamePascalCase = S::create($tableName)->upperCamelize()->__toString();
        $modelSample = file_get_contents(__DIR__ . '/Sample/ModelSample.php');
        foreach ($tableInformations as $column) {
            $columnNamePascalCase = S::create($column['COLUMN_NAME'])->camelize()->__toString();
            if (!in_array($column['COLUMN_NAME'], $fieldsToIgnored)) {

                if (!isset($this->typeMapping[$column['DATA_TYPE']])) {
                    die('Database field type ' . $column['DATA_TYPE'] . ' not found, please add it to GenerateApiFromDb.php and commit / pull request :)');
                }

                $propertiesGeneration .= '

    /**
     * @var ' . $this->typeMapping[$column['DATA_TYPE']] . '
     */
    public $' . $columnNamePascalCase . ';';

                $defaultValue = false;
                if ($column['IS_NULLABLE'] == 'YES') {
                    $defaultValue = 'null';
                }

                if ($column['COLUMN_DEFAULT'] !== 'NULL') {
                    $defaultValue = $column['COLUMN_DEFAULT'];
                }

                if ($defaultValue) {
                    $defaultValuesGeneration .= '
        $this->' . $columnNamePascalCase . ' = ' . $defaultValue . ';';
                }


            }
        }

        $propertyGeneration = S::create($propertiesGeneration)->trimLeft()->__toString();
        $defaultValuesGeneration = S::create($defaultValuesGeneration)->trimLeft()->__toString();
        $modelSample = str_replace('ModelSample', $tableNamePascalCase . 'Model', $modelSample);
        $modelSample = str_replace('//PROPERTIES_PLACE_HOLDER', $propertyGeneration, $modelSample);
        $modelSample = str_replace(array('Support3w\Api\Command\Sample', '//PACKAGE_NAME_PLACE_HOLDER'), $this->apiNamespace . '\Model', $modelSample);
        $modelSample = str_replace('//DEFAULT_PROPERTIES_VALUES_PLACE_HOLDER', $defaultValuesGeneration, $modelSample);

        if (!file_exists($this->modelDestinationDirectory)) {
            mkdir($this->modelDestinationDirectory, 0777, true);
        }

        $destinationPath = $this->modelDestinationDirectory . '/' . $tableNamePascalCase . 'Model.php';
        file_put_contents($destinationPath, $modelSample);
    }
}
