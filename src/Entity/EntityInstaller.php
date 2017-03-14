<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace rollun\promise\Entity;

use Composer\IO\IOInterface;
use Interop\Container\ContainerInterface;
use rollun\installer\Install\InstallerAbstract;
use rollun\utils\DbInstaller;
use Zend\Db\Adapter\AdapterInterface;
use rollun\datastore\TableGateway\TableManagerMysql as TableManager;
use rollun\promise\Entity\Store as EntityStore;
use rollun\dic\InsideConstruct;

/**
 * Installer class
 *
 * @category   Zaboy
 * @package    zaboy
 */
class EntityInstaller extends InstallerAbstract
{

    /**
     *
     * @var AdapterInterface
     */
    private $entityDbAdapter;

    public function __construct(ContainerInterface $container, IOInterface $ioComposer)
    {
        parent::__construct($container, $ioComposer);
        $this->entityDbAdapter = $this->container->get('db');
    }

    public function isInstall()
    {
        $config = $this->container->get('config');
        return ($this->container->has('entityDbAdapter'));
    }

    public function install()
    {
        $tableManager = new TableManager($this->entityDbAdapter);
        $tableConfig = $this->getTableConfig();
        $tableName = EntityStore::TABLE_NAME;
        $tableManager->rewriteTable($tableName, $tableConfig);
        return [
            'services' => [
                'aliases' => [
                    //this 'callback' is service name in url
                    'entityDbAdapter' => constant('APP_ENV') === 'production' ? 'db' : 'db',
                ],
            ],
        ];
    }

    protected function getTableConfig()
    {
        return [
            EntityStore::ID => [
                'field_type' => 'Varchar',
                'field_params' => [
                    'length' => 128,
                    'nullable' => false
                ]
            ]
        ];
    }

    /**
     * Clean all installation
     * @return void
     */
    public function uninstall()
    {
        $tableManager = new TableManager($this->entityDbAdapter);
        $tableName = EntityStore::TABLE_NAME;
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
                $description = "Позволяет использовать entity";
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
