<?php

class ContentCache 
{
    private static $TMP_ROOT = APP_PATH . "/runtime/tmp";

    private static function createTempFile()
    {
        $tmpPath = createTempFile();
        $file = fopen($tmpPath, "w+");
        if (!$file) {
            throw new Exception("Could not create temporary file");
        }
        return [$tmpPath, $file];
    }


    private static function convertToJpg($input)
    {
        try {
            $image = imagecreatefromstring(file_get_contents($input));
            $tmpPath = self::createTempFile();
            fclose($tmpPath[1]);
            $tmpPath = $tmpPath[0];
            if (!imagejpeg($image, $tmpPath, 100)) {
                $log = "Could not save image to " . $tmpPath;
                error_log($log);
                throw new Exception($log);
            }
            return $tmpPath;
        } catch (Exception $e) {
            if ($tmpPath) {
                unlink($tempPath);
            }
            throw $e;
        } finally {
            if ($image) {
                imagedestroy($image);
            }
        }
    }

    public static function loadAll($all)
    {
        $count = count($all);
        $result = [];
        for ($offset = 0; $offset < $count; $offset += 16) {
            $limit = $count - $offset;
            if ($limit > 16) {
                $limit = 16;
            }
            $result = array_merge($result, self::doLoadAll(array_slice($all, $offset, $limit)));
        }
        return $result;
    }

    private static function doLoadAll($all)
    {
        $tasks = [];
        array_walk($all, function(&$item, $index) use (&$tasks) {
            $cachePath = APP_PATH . "/runtime/cache" . $item;
            if (!file_exists($cachePath)) {
                $tasks[$item] = $index;
                $item = NULL;
            } else {
                $item = $cachePath;
            }
        });
        if (count($tasks) > 0) {
            $mh = curl_multi_init();
            $contexts = [];
            $retried = false;
            array_walk($tasks, function(&$index, $task) use ($mh, &$contexts, &$retried) {
                $handle = curl_init();
                $tmpPath = self::createTempFile();
                $file = $tmpPath[1];
                $tmpPath = $tmpPath[0];
                $context = [$file, $task, $tmpPath, $handle];
                curl_setopt_array($handle, [
                    CURLOPT_URL => "http://" . HI\Config\DOWNLOAD_DOMAIN . $task,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_MAXREDIRS => 8,
                    CURLOPT_HEADER => false,
                    CURLOPT_FILE => $file,
                    CURLOPT_PRIVATE => $task,
                ]);
                error_log("fetch '" . $task . "' at '" . $tmpPath . "' in batch");
                curl_multi_add_handle($mh, $handle);
                $contexts[$task] = $context;
            });
            $active = NULL;
            while (($rc = curl_multi_exec($mh, $active)) == CURLM_CALL_MULTI_PERFORM) {};
          
            while ($active && $rc == CURLM_OK) {
                if (curl_multi_select($mh) == -1) {
                    usleep(1);
                }
                do {
                    $rc = curl_multi_exec($mh, $active);
                } while ($rc == CURLM_CALL_MULTI_PERFORM);
            }
          
            array_walk($contexts, function(&$context, $task) use($mh, $contexts) {
                $handle = $context[3];
                curl_multi_remove_handle($mh, $handle);
                fclose($contexts[curl_getinfo($handle, CURLINFO_PRIVATE)][0]);
                curl_close($handle);
            });
            curl_multi_close($mh);

            array_walk($contexts, function(&$context, $task) use($mh, $tasks, &$all) {
                $task = $context[1];
                $tmpPath = $context[2];
                $fileSize = filesize($tmpPath);
                if ($fileSize == 0 || $fileSize > HI\Config\CONTENT_GENERATOR_FILE_SIZE_LIMIT) {
                    error_log("Fetched " . $fileSize . " bytes file '" . $tmpPath . " from '" . $task . "' beyond valid file size of (0, " . HI\Config\CONTENT_GENERATOR_FILE_SIZE_LIMIT . "]");
                    unlink($tmpPath);
                    return;
                }
                $retried = false;
retryFixed:
                try {
                    switch (Preconditions::checkNotNull(getimagesize($tmpPath))["mime"]) {
                    case "image/jpg":
                    case "image/jpeg":
                        imagedestroy(imagecreatefromjpeg($tmpPath));
                        break;
                    default:
                        if (HI\Config\CONTENT_GENERATOR_FORCE_JPEG) {
                            $tmpPath = self::convertToJpg($tmpPath);
                        }
                    }
                } catch (Exception $e) {
                    if (!$retried) {
                        system("/usr/bin/convert $tmpPath $tmpPath");
                        $retried = true;
                        goto retryFixed;
                    }
                    error_log("Fetched " . $fileSize . " bytes corrupted file '" . $tmpPath . "' from '" . $task . "'");
                    unlink($tmpPath);
                    return;
                }
                $path = APP_PATH . "/runtime/cache" . $task;
                error_log("'" . $task . "' is fetched to '" . $path . "'");
                $retried = false;
retryCache:
                if (!@rename($tmpPath, $path) && !file_exists($path)) {
                    if (!$retried) {
                        $retried = true;
                        $parent = substr($path, 0, strrpos($path, "/"));
                        error_log("Try to create directory " . $parent);
                        if (mkdir($parent, 0755, true)) {
                            goto retryCache;
                        }
                    }
                    unlink($tmpPath);
                } else {
                    $all[$tasks[$task]] = $path;
                }
            });
        }
        return $all;
    }

    public static function load(...$path)
    {
        $path = self::loadAll($path);
        return count($path) == 1 ? $path[0] : $path;
    }
}

?>
