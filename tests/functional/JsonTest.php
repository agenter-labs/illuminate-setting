<?php

class JsonTest extends AbstractFunctionalTest
{
    protected function createStore(array $data = null)
    {
        $path = dirname(__DIR__) . '/tmp/store.json';

        if ($data !== null) {
            if ($data) {
                $json = json_encode($data);
            } else {
                $json = '{}';
            }

            file_put_contents($path, $json);
        }

        return new \AgenterLab\Setting\Drivers\Json(
            new \Illuminate\Filesystem\Filesystem(),
            $path
        );
    }

    protected function tearDown(): void
    {
        $path = dirname(__DIR__) . '/tmp/store.json';
        unlink($path);
    }
}
