<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace rollun\promise\Promise;

use Composer\IO\IOInterface;
use Interop\Container\ContainerInterface;
use rollun\installer\Install\InstallerAbstract;
use rollun\utils\DbInstaller;
use Zend\Db\Adapter\AdapterInterface;
use rollun\datastore\TableGateway\TableManagerMysql as TableManager;
use rollun\promise\Promise\Store as PromiseStore;

/**
 * Installer class
 *
 * @category   Zaboy
 * @package    zaboy
 */
class PromiseInstaller extends InstallerAbstract
{

    /**
     *
     * @var AdapterInterface
     */
    private $promiseDbAdapter;

    public function __construct(ContainerInterface $container, IOInterface $ioComposer)
    {
        parent::__construct($container, $ioComposer);
        $this->promiseDbAdapter = $this->container->get('db');
    }


    public function isInstall()
    {
        $config = $this->container->get('config');
        return ($this->container->has('promiseDbAdapter'));
    }

    public function install()
    {
        $tableManager = new TableManager($this->promiseDbAdapter);
        $tableConfig = $this->getTableConfig();
        $tableName = PromiseStore::TABLE_NAME;
        $tableManager->rewriteTable($tableName, $tableConfig);
        return [
            'services' => [
                'aliases' => [
                    //this 'callback' is service name in url
                    'promiseDbAdapter' => constant('APP_ENV') === 'production' ? 'db' : 'db',
                ],
            ],
        ];
    }

    protected function getTableConfig()
    {
        return [
            PromiseStore::ID => [
                'field_type' => 'Varchar',
                'field_params' => [
                    'length' => 128,
                    'nullable' => false
                ]
            ],
            PromiseStore::STATE => [
                'field_type' => 'Varchar',
                'field_params' => [
                    'length' => 128,
                    'nullable' => false
                ]
            ],
            PromiseStore::RESULT => [
                'field_type' => 'Blob',
                'field_params' => [
                    'length' => 65000,
                    'nullable' => true
                ]
            ],
            PromiseStore::ON_FULFILLED => [
                'field_type' => 'Blob',
                'field_params' => [
                    'length' => 65000,
                    'nullable' => true
                ]
            ],
            PromiseStore::ON_REJECTED => [
                'field_type' => 'Blob',
                'field_params' => [
                    'length' => 65000,
                    'nullable' => true
                ]
            ],
            PromiseStore::PARENT_ID => [
                'field_type' => 'Varchar',
                'field_params' => [
                    'length' => 128,
                    'nullable' => true
                ]
            ],
        ];
    }

    /**
     * Clean all installation
     * @return void
     */
    public function uninstall()
    {
        $tableManager = new TableManager($this->promiseDbAdapter);
        $tableName = PromiseStore::TABLE_NAME;
        $tableManager->deleteTable($tableName);
    }

    /**
     * Return string with description of installable functional.
     * @param string $lang ; set select language for description getted.
     * @return string
     */
    public function getDescription($lang = "en")
    {
        switch ($lang) {
            case "ru":
                $description = "Позволяет использовать promise";
                break;
            default:
                $description = "Does not exist.";
        }
        return $description;
    }

    public function getDependencyInstallers()
    {
        return [
            DbInstaller::class
        ];
    }
}
