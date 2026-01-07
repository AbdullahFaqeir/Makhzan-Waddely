<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

/**
 * Class File.
 *
 * @package App\Models
 * @date    07/01/2026
 * @author  Abdullah Al-Faqeir <abdullah@devloops.net>
 */
class File extends FileEntry
{
    /**
     * @var string
     */
    protected $table = 'file_entries';

    /**
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('fsType', static function (Builder $builder) {
            $builder->where('type', '!=', 'folder');
        });
    }
}
