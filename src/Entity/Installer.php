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
class Installer extends InstallerAbstract
{

    /**
     *
     * @var AdapterInterface
     */
    private $entityDbAdapter;

    public function __construct(ContainerInterface $container, IOInterface $ioComposer)
    {
        parent::__construct($container, $ioComposer);
        $this->entityDbAdapter = $this->container->get('entityDbAdapter');
    }


    public function install()
    {
        $tableManager = new TableManager($this->entityDbAdapter);
        $tableConfig = $this->getTableConfig();
        $tableName = EntityStore::TABLE_NAME;
        $tableManager->rewriteTable($tableName, $tableConfig);
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
}
