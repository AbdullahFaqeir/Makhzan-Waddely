<?php

namespace Common\Files\Response;

use Common\Files\FileEntry;

/**
 * Class XSendFileResponse.
 *
 * @package Common\Files\Response
 * @date    07/01/2026
 * @author  Abdullah Al-Faqeir <abdullah@devloops.net>
 */
class XSendFileResponse implements FileResponse
{
    public function make(FileEntry $entry, array $options): void
    {
        $path = $entry->getDisk()
                      ->path('').$entry->getStoragePath($options['useThumbnail']);
        $disposition = $options['disposition'];
        header("X-Sendfile: $path");
        header("Content-Type: {$entry->mime}");
        header("Content-Disposition: $disposition; filename=\"".$entry->getNameWithExtension().'"');
        exit();
    }
}
