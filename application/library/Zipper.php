<?php

class Zipper {
    private static $TMP_ROOT = APP_PATH . "/runtime/tmp";
    public static function zip($files, $output = NULL, $title = NULL, $storeOnly = true)
    {
        Preconditions::checkNotEmpty($files);
        $zipFiles = array_keys($files);
        sort($zipFiles, SORT_LOCALE_STRING);
        Execution::autoUnlink(function($unlink) use ($files, $zipFiles, $output, $title,$storeOnly) {
            $zip = new ZipArchive();
            try {
                if (Predicates::isEmpty($output)) {
                    $tmpPath = createTempFile();
                    $realOutput = $tmpPath;
                    $unlink($tmpPath);
                } else {
                    $realOutput = $output;
                }
                $flags = ZipArchive::CREATE | ZipArchive::OVERWRITE;
                if ($storeOnly) {
                    $flags |= ZipArchive::CM_STORE;
                }
                Preconditions::checkArgument($zip->open($realOutput, $flags));
                array_reduce($zipFiles, function($lastDir, $zipFile) use ($files, $zip) {
                    $parts = explode("/", $zipFile);
                    $dir = implode("/", array_splice($parts, 0, count($parts) - 1));
                    if ($lastDir !== $dir) {
                        $zip->addEmptyDir($dir);
                    }
                    if (!($zip->addFile($files[$zipFile], $dir . "/" . $parts[0]))) {
                        error_log("Could not add file " . $files[$zipFile] . " as " . $dir . "/" . $parts[0] . " to zip file");
                        Preconditions::checkArgument(false);
                    }
                    return $dir;
                });
            } finally {
                $zip->close();
            }
             if (Predicates::isEmpty($output)) {
                 $fileSize = filesize($realOutput);
                 header("Content-Type: application/zip");
                 header("Content-Length:" . $fileSize);
                 $title = Predicates::isEmpty($title) ? "订单" : $title;
                 header("Content-Disposition: attachment; filename=" . $title . ".zip");
                 ob_end_flush();
                 readfile($realOutput);
             }
        });
    }
};

?>
