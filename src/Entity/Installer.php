<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\install\async\Entity;

use Interop\Container\ContainerInterface;
use zaboy\AbstractInstaller;
use Zend\Db\Adapter\AdapterInterface;
use zaboy\rest\TableGateway\TableManagerMysql as TableManager;
use rollun\promise\Entity\Store as EntityStore;
use zaboy\res\Di\InsideConstruct;

/**
 * Installer class
 *
 * @category   Zaboy
 * @package    zaboy
 */
class Installer extends AbstractInstaller
{

    /**
     *
     * @var AdapterInterface
     */
    private $entityDbAdapter;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
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
