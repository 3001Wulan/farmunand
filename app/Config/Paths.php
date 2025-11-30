<?php

namespace Config;

/**
 * Paths
 *
 * Holds the paths that are used by the system to
 * locate the main directories, app, system, etc.
 *
 * All paths are relative to the project's root folder.
 */
class Paths
{
    /**
     * Path ke folder "system" CI4 di vendor.
     */
    public string $systemDirectory = __DIR__ . '/../../vendor/codeigniter4/framework/system';

    /**
     * Folder "app" (controller, model, view, dll).
     */
    public string $appDirectory = __DIR__ . '/..';

    /**
     * Folder "writable" (logs, cache, uploads, dsb).
     */
    public string $writableDirectory = __DIR__ . '/../../writable';

    /**
     * Folder "tests".
     */
    public string $testsDirectory = __DIR__ . '/../../tests';

    /**
     * Folder support untuk test (BARU, dipakai utk SUPPORTPATH).
     */
    public string $supportDirectory = __DIR__ . '/../../tests/_support';

    /**
     * Folder view (template).
     */
    public string $viewDirectory = __DIR__ . '/../Views';
}
