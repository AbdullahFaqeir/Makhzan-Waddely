<?php

namespace App\Http\Controllers;

use App\Models\FileEntry;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Services\Entries\FetchDriveEntries;
use App\Services\Entries\DriveEntriesLoader;
use App\Services\Entries\SetPermissionsOnEntry;
use Illuminate\Contracts\Routing\ResponseFactory;
use Common\Files\Controllers\FileEntriesController;

/**
 * Class DriveEntriesController.
 *
 * @package App\Http\Controllers
 * @date    07/01/2026
 * @author  Abdullah Al-Faqeir <abdullah@devloops.net>
 */
class DriveEntriesController extends FileEntriesController
{
    public function __construct(Request $request, FileEntry $entry)
    {
        parent::__construct($request, $entry);
        $this->request = $request;
        $this->entry = $entry;
    }

    public function showModel($fileEntryId
    ): Response|JsonResponse|ResponseFactory {
        $fileEntry = FileEntry::findOrFail($fileEntryId);
        $this->authorize('show', $fileEntry);

        $fileEntry->load('users');
        app(SetPermissionsOnEntry::class)->execute($fileEntry);

        return $this->success(['fileEntry' => $fileEntry]);
    }

    public function index(): array|Response|JsonResponse|ResponseFactory
    {
        $this->middleware('auth');

        $params = $this->request->all();
        $params['userId'] = Auth::id();

        $this->authorize('index', [FileEntry::class, null, $params['userId']]);

        if (isset($params['section'])) {
            return new DriveEntriesLoader($params)->load();
        }

        return app(FetchDriveEntries::class)->execute($params);
    }
}
