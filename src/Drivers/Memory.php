<?php

namespace AgenterLab\Setting\Drivers;

use AgenterLab\Setting\Contracts\Driver;

class Memory extends Driver
{
    /**
     * @param array $data
     */
    public function __construct(array $data = null)
    {
        if ($data) {
            $this->data = $data;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getExtraColumns()
    {
        return [];
    }

    /**
     * Set extra columns to be added to the rows.
     *
     * @param array $columns
     */
    public function setExtraColumns(array $columns)
    {
    
    }

    /**
     * {@inheritdoc}
     */
    protected function read()
    {
        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    protected function write(array $data)
    {
        // do nothing
    }
}
