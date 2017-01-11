<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\install\async\Promise;

use Interop\Container\ContainerInterface;
use zaboy\AbstractInstaller;
use Zend\Db\Adapter\AdapterInterface;
use zaboy\rest\TableGateway\TableManagerMysql as TableManager;
use rollun\promise\Promise\Store as PromiseStore;
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
    private $promiseDbAdapter;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->promiseDbAdapter = $this->container->get('promiseDbAdapter');
    }


    public function install()
    {
        $tableManager = new TableManager($this->promiseDbAdapter);
        $tableConfig = $this->getTableConfig();
        $tableName = PromiseStore::TABLE_NAME;
        $tableManager->rewriteTable($tableName, $tableConfig);
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
}
