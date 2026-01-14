<?php

namespace App\Providers;

use App\Models\File;
use App\Models\User;
use App\Models\Folder;
use App\Models\FileEntry;
use App\Models\ShareableLink;
use Illuminate\Auth\Events\Login;
use App\Services\AppBootstrapData;
use Illuminate\Support\Facades\URL;
use Common\Auth\Events\UserCreated;
use Illuminate\Support\Facades\Gate;
use App\Policies\ShareableLinkPolicy;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use App\Policies\DriveFileEntryPolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Common\Core\Bootstrap\BootstrapData;
use App\Listeners\HandleDeletedWorkspace;
use App\Listeners\FolderTotalSizeSubscriber;
use Common\Files\FileEntry as CommonFileEntry;
use Common\Workspaces\Events\WorkspaceDeleted;
use App\Services\Admin\GetAnalyticsHeaderData;
use App\Services\Entries\SetPermissionsOnEntry;
use Illuminate\Database\Eloquent\Relations\Relation;
use Common\Notifications\SubscribeUserToNotifications;
use Common\Workspaces\Listeners\AttachWorkspaceToUser;
use Common\Admin\Analytics\Actions\GetAnalyticsHeaderDataAction;

const WORKSPACED_RESOURCES = [FileEntry::class];
const WORKSPACE_HOME_ROUTE = '/drive';

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Model::preventLazyLoading(!app()->isProduction());

        URL::forceScheme('https');

        Relation::enforceMorphMap([
            FileEntry::MODEL_TYPE => FileEntry::class,
            User::MODEL_TYPE      => User::class,
        ]);

        Gate::policy(CommonFileEntry::class, DriveFileEntryPolicy::class);
        Gate::policy(File::class, DriveFileEntryPolicy::class);
        Gate::policy(Folder::class, DriveFileEntryPolicy::class);
        Gate::policy(ShareableLink::class, ShareableLinkPolicy::class);
    }

    public function register()
    {
        $this->app->bind(GetAnalyticsHeaderDataAction::class,
            GetAnalyticsHeaderData::class,);

        $this->app->bind(BootstrapData::class, AppBootstrapData::class);

        $this->app->bind(CommonFileEntry::class, FileEntry::class);

        $this->app->singleton(SetPermissionsOnEntry::class,
            fn() => new SetPermissionsOnEntry(),);

        Event::listen(Login::class, AttachWorkspaceToUser::class);
        Event::listen(Registered::class, AttachWorkspaceToUser::class);
        Event::listen(WorkspaceDeleted::class, HandleDeletedWorkspace::class);

        Event::listen(UserCreated::class, function (UserCreated $event) {
            app(SubscribeUserToNotifications::class)->execute($event->user,
                null,);
        });

        Event::subscribe(FolderTotalSizeSubscriber::class);
    }
}
