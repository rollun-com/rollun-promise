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
use Zend\Db\Adapter\AdapterInterface;
use rollun\datastore\TableGateway\TableManagerMysql as TableManager;
use rollun\promise\Promise\Store as PromiseStore;

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
    private $promiseDbAdapter;

    public function __construct(ContainerInterface $container, IOInterface $ioComposer)
    {
        parent::__construct($container, $ioComposer);
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
