<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Jm
 * Date: 15-07-25
 * Time: 21:54
 * To change this template use File | Settings | File Templates.
 */

namespace Support3w\Api\Generic\Utils;

class FileSystemTool
{

    function createFolderIfNotExist($folder)
    {

        if (!is_dir($folder)) {
            mkdir($folder, 0755, true);
        }

        if (!is_dir($folder)) {
            die('Cannot create folder on host');
        }

        return true;
    }

}
