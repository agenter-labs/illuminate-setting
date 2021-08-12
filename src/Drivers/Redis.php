<?php

namespace AgenterLab\Setting\Drivers;

use AgenterLab\Setting\Contracts\Driver;
use AgenterLab\Setting\Support\Arr;
use Closure;
use Illuminate\Contracts\Redis\Connection;
use Illuminate\Support\Arr as LaravelArr;
use Illuminate\Support\Facades\Crypt;

class Redis extends Driver
{
    /**
     * The database connection instance.
     *
     * @var \Illuminate\Contracts\Redis\Connection
     */
    protected $connection;


    /**
     * Any extra columns that should be added to the rows.
     *
     * @var array
     */
    protected $extra_columns = [];

    /**
     * @param \Illuminate\Contracts\Redis\Connection $connection
     * @param string $table
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Set extra columns to be added to the rows.
     *
     * @param array $columns
     */
    public function setExtraColumns(array $columns)
    {
        $this->extra_columns = $columns;
    }

    /**
     * Get extra columns added to the rows.
     *
     * @return array
     */
    public function getExtraColumns()
    {
        return $this->extra_columns;
    }

    /**
     * {@inheritdoc}
     */
    public function forget($key)
    {
        parent::forget($key);

        // because the database driver cannot store empty arrays, remove empty
        // arrays to keep data consistent before and after saving
        $segments = explode('.', $key);
        array_pop($segments);

        while ($segments) {
            $segment = implode('.', $segments);

            // non-empty array - exit out of the loop
            if ($this->get($segment)) {
                break;
            }

            // remove the empty array and move on to the next segment
            $this->forget($segment);
            array_pop($segments);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function write(array $data)
    {
        $db_data = $this->read();

        $insert_data = LaravelArr::dot($data);
        $delete_keys = [];
        $cache_key = $this->getCacheKey();

        foreach ($db_data as $key => $value) {

            $is_in_insert = $is_different_in_db = $is_same_as_fallback = false;

            if (isset($insert_data[$key])) {
                $is_in_insert = true;
                $is_different_in_db = (string) $insert_data[$key] != (string) $value;
                $is_same_as_fallback = $this->isEqualToFallback($key, $insert_data[$key]);
            }

            if ($is_in_insert) {
                if ($is_same_as_fallback) {
                    // Delete if new data is same as fallback
                    $delete_keys[] = $key;
                    unset($insert_data[$key]);
                }
            } else {
                // Delete if current db not available in new data
                $delete_keys[] = $key;
            }
        }

        if ($delete_keys) {
            array_unshift($delete_keys, $cache_key);
            $this->connection->command('hdel', $delete_keys);
        }

        if ($insert_data) {
            $this->connection->hmset($cache_key, $insert_data);
        }
    }
    
    /**
     * {@inheritdoc}
     */
    protected function read()
    {
        return $this->parseReadData($this->connection->command('hgetall', [$this->getCacheKey()]));
    }

     /**
     * Parse data coming from the database.
     *
     * @param array $data
     *
     * @return array
     */
    public function parseReadData($data)
    {
        $results = [];

        foreach ($data as $key => $value) {
            Arr::set($results, $key, $value);
        }

        return $results;
    }
}
