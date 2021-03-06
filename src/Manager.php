<?php

namespace AgenterLab\Setting;

use AgenterLab\Setting\Drivers\Database;
use AgenterLab\Setting\Drivers\Json;
use AgenterLab\Setting\Drivers\Memory;
use AgenterLab\Setting\Drivers\Redis;
use Illuminate\Support\Manager as BaseManager;

class Manager extends BaseManager
{
    /**
     * The container instance.
     *
     * @var \Illuminate\Contracts\Container\Container
     */
    protected $container;

    /**
     * The application instance.
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     */
    public function __construct($app = null)
    {
        $this->container = $app ?? app();

        parent::__construct($this->container);
    }

    public function getDefaultDriver()
    {
        return config('setting.driver');
    }

    public function createJsonDriver()
    {
        $path = config('setting.json.path');

        $path = $path ?: $this->container->storagePath() . '/settings.json';

        return new Json($this->container['files'], $path);
    }

    public function createDatabaseDriver()
    {
        $connection = $this->container['db']->connection(config('setting.database.connection'));
        $table = config('setting.database.table');
        $key = config('setting.database.key');
        $value = config('setting.database.value');
        $encryptedKeys = config('setting.encrypted_keys');

        return new Database($connection, $table, $key, $value, $encryptedKeys);
    }

    public function createRedisDriver()
    {
        $connection = $this->container['redis']->connection(config('setting.redis.connection'));
        
        return new Redis($connection,);
    }

    public function createMemoryDriver()
    {
        return new Memory();
    }

    public function createArrayDriver()
    {
        return $this->createMemoryDriver();
    }
}
