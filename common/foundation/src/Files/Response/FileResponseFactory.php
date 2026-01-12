<?php

namespace Common\Files\Response;

use Common\Files\FileEntry;
use Common\Files\Uploads\Uploads;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use League\Flysystem\Local\LocalFilesystemAdapter;

/**
 * Class FileResponseFactory.
 *
 * @package Common\Files\Response
 * @date    07/01/2026
 * @author  Abdullah Al-Faqeir <abdullah@devloops.net>
 */
class FileResponseFactory
{
    public function create(
        FileEntry $entry,
        string $disposition = 'inline',
    ): mixed {
        $options = [
            'useThumbnail' => request('thumbnail') && $entry->thumbnail,
            'disposition'  => $disposition,
        ];

        return $this->resolveResponseClass($entry, $disposition)
                    ->make($entry, $options);
    }

    private function resolveResponseClass(
        FileEntry $entry,
        string $disposition = 'inline',
    ): FileResponse {
        $isLocalDrive = $entry->getDisk()
                              ->getAdapter() instanceof LocalFilesystemAdapter;
        $staticFileDelivery = config('filesystems.static_file_delivery');

        if ($this->shouldRedirectToRemoteUrl($entry)) {
            dd(1, $entry, $isLocalDrive, $staticFileDelivery);
            return new RemoteFileResponse();
        }

        if ($isLocalDrive && !$entry->public && $staticFileDelivery) {
            dd(2, $entry, $isLocalDrive, $staticFileDelivery);
            return $staticFileDelivery === 'xsendfile' ? new XSendFileResponse() : new XAccelRedirectFileResponse();
        }

        if (!$isLocalDrive
            && config('filesystems.use_presigned_s3_urls')) {
            dd(3, $entry, $isLocalDrive, $staticFileDelivery);
            return new StreamedFileResponse();
        }

        if ($disposition === 'inline'
            && $this->shouldReturnRangeResponse($entry)) {
            dd(4, $entry, $isLocalDrive, $staticFileDelivery);
            return new RangeFileResponse();
        }
        dd(5, $entry, $isLocalDrive, $staticFileDelivery);
        return new StreamedFileResponse();
    }

    private function shouldReturnRangeResponse(FileEntry $entry): bool
    {
        return $entry->type === 'video'
               || $entry->type === 'audio'
               || $entry->mime === 'application/ogg';
    }

    private function shouldRedirectToRemoteUrl(FileEntry $entry): bool
    {
        $adapter = $entry->getDisk()
                         ->getAdapter();
        $isS3 = $adapter instanceof AwsS3V3Adapter;
        $shouldUsePublicUrl = $entry->public && $isS3;
        $shouldUsePresignedUrl = config('filesystems.use_presigned_s3_urls') && $isS3;
        $hasCustomCdnUrl = config('filesystems.file_preview_endpoint')
                           || ($entry->backend_id
                               && !!Uploads::backend($entry->backend_id)->customDomain);
        return $shouldUsePresignedUrl
               || $shouldUsePublicUrl
               || $hasCustomCdnUrl;
    }
}
