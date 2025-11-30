<?php

use CodeIgniter\Boot;
use Config\Paths;

/**
 *---------------------------------------------------------------
 * SET THE FRONT CONTROLLER PATH
 *---------------------------------------------------------------
 *
 * FCPATH = folder tempat index.php berada (public/).
 */
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);

/**
 *---------------------------------------------------------------
 * LOAD PATHS CONFIG
 *---------------------------------------------------------------
 * Memuat konfigurasi path (app, system, writable, tests, dll).
 */
require FCPATH . '../app/Config/Paths.php';

$paths = new Paths();

/**
 *---------------------------------------------------------------
 * DEFINE SUPPORTPATH (FIX UNTUK VERSI CI4 BARU)
 *---------------------------------------------------------------
 * Beberapa versi CI4 butuh konstanta SUPPORTPATH sebelum
 * AutoloadConfig dibuat. Di proyek lama ini belum ada,
 * jadi kita definisikan manual dari $paths->supportDirectory.
 */
if (! defined('SUPPORTPATH')) {
    $supportDir = property_exists($paths, 'supportDirectory')
        ? $paths->supportDirectory
        : $paths->testsDirectory . '_support';

    if (is_dir($supportDir)) {
        define('SUPPORTPATH', realpath($supportDir) . DIRECTORY_SEPARATOR);
    } else {
        // fallback aman walaupun folder belum ada
        define('SUPPORTPATH', $supportDir . DIRECTORY_SEPARATOR);
    }
}

/**
 *---------------------------------------------------------------
 * LOAD THE FRAMEWORK BOOTSTRAP FILE
 *---------------------------------------------------------------
 */
require $paths->systemDirectory . '/Boot.php';

/**
 *---------------------------------------------------------------
 * BOOT THE WEB APPLICATION
 *---------------------------------------------------------------
 */
exit(Boot::bootWeb($paths));
