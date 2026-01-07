<?php

namespace Common\Files\Controllers;

use Common\Files\FileEntry;
use Illuminate\Http\Request;
use Common\Core\BaseController;
use Illuminate\Http\UploadedFile;
use Common\Files\FileEntryPayload;
use Common\Files\Actions\StoreFile;
use Illuminate\Support\Facades\Auth;
use Common\Files\Events\FileUploaded;
use Common\Files\Actions\CreateFileEntry;
use Common\Database\Datasource\Datasource;
use Common\Files\Actions\FileUploadValidator;
use Common\Files\Response\FileResponseFactory;
use Common\Files\Actions\Deletion\DeleteEntries;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

/**
 * Class FileEntriesController.
 *
 * @package Common\Files\Controllers
 * @date    07/01/2026
 * @author  Abdullah Al-Faqeir <abdullah@devloops.net>
 */
class FileEntriesController extends BaseController
{
    public function __construct(
        protected Request $request,
        protected FileEntry $entry,
    ) {
        $this->middleware('auth')
             ->only(['index']);
    }

    public function index(
    ): \Illuminate\Http\Response|\Illuminate\Http\JsonResponse|\Illuminate\Contracts\Routing\ResponseFactory
    {
        $params = $this->request->all();
        $params['userId'] = $this->request->get('userId');

        // scope files to current user by default if it's an API request
        if (!requestIsFromFrontend() && !$params['userId']) {
            $params['userId'] = Auth::id();
        }

        $this->authorize('index', FileEntry::class);

        $dataSource = new Datasource($this->entry->with(['users']), $params);

        $pagination = $dataSource->paginate();

        return $this->success(['pagination' => $pagination]);
    }

    public function show(FileEntry $fileEntry, FileResponseFactory $response)
    {
        $this->authorize('show', $fileEntry);

        try {
            return $response->create($fileEntry);
        } catch (FileNotFoundException $e) {
            abort(404);
        }
    }

    public function showModel(FileEntry $fileEntry
    ): \Illuminate\Http\Response|\Illuminate\Http\JsonResponse|\Illuminate\Contracts\Routing\ResponseFactory {
        $this->authorize('show', $fileEntry);

        return $this->success(['fileEntry' => $fileEntry]);
    }

    public function store(
    ): \Illuminate\Http\Response|\Illuminate\Http\JsonResponse|\Illuminate\Contracts\Routing\ResponseFactory
    {
        $parentId = (int)request('parentId') ?: null;
        request()->merge(['parentId' => $parentId]);

        $this->authorize('store', [FileEntry::class, request('parentId')]);

        $file = $this->request->file('file');
        $payload = new FileEntryPayload($this->request->all());

        $this->validate($this->request, [
            'file'         => [
                'required',
                'file',
                function ($attribute, UploadedFile $value, $fail) use (
                    $payload,
                ) {
                    $errors = FileUploadValidator::validateForUploadType($payload->uploadType,
                        $payload->size, $payload->clientExtension,
                        $payload->clientMime,);
                    if ($errors) {
                        $fail($errors->first());
                    }
                },
            ],
            'parentId'     => 'nullable|exists:file_entries,id',
            'relativePath' => 'nullable|string',
        ]);

        (new StoreFile())->execute($payload, ['file' => $file]);

        $fileEntry = (new CreateFileEntry())->execute($payload);
        $fileEntry = $payload->uploadType->runHandler($fileEntry,
            $this->request->all(),);

        event(new FileUploaded($fileEntry));

        return $this->success(['fileEntry' => $fileEntry->load('users')], 201);
    }

    public function update(int $entryId
    ): \Illuminate\Http\Response|\Illuminate\Http\JsonResponse|\Illuminate\Contracts\Routing\ResponseFactory {
        $this->authorize('update', [FileEntry::class, [$entryId]]);

        $this->validate($this->request, [
            'name'        => 'string|min:3|max:200',
            'description' => 'nullable|string|min:3|max:200',
        ]);

        $params = $this->request->all();
        $entry = $this->entry->findOrFail($entryId);

        $entry->fill($params)
              ->update();

        return $this->success(['fileEntry' => $entry->load('users')]);
    }

    public function destroy(string|null $entryIds = null)
    {
        if ($entryIds) {
            $entryIds = explode(',', $entryIds);
        } else {
            $entryIds = $this->request->get('entryIds');
        }

        $userId = Auth::id();

        $this->validate($this->request, [
            'entryIds'      => 'array|exists:file_entries,id',
            'paths'         => 'array',
            'deleteForever' => 'boolean',
            'emptyTrash'    => 'boolean',
        ]);

        $this->blockOnDemoSite();

        // get all soft deleted entries for user, if we are emptying trash
        if ($this->request->get('emptyTrash')) {
            $entryIds = $this->entry->where('owner_id', $userId)
                                    ->onlyTrashed()
                                    ->pluck('id')
                                    ->toArray();
        }

        app(DeleteEntries::class)->execute([
            'paths'    => $this->request->get('paths'),
            'entryIds' => $entryIds,
            'soft'     => !$this->request->get('deleteForever', true)
                          && !$this->request->get('emptyTrash'),
        ]);

        return $this->success();
    }
}
