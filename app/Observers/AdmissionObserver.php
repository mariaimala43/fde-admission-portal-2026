<?php

namespace App\Observers;

use App\Jobs\SyncStatusToNfemisJob;
use App\Models\Admission;

class AdmissionObserver
{
    public function updated(Admission $admission): void
    {
        if ($admission->isDirty('status')) {
            SyncStatusToNfemisJob::dispatch($admission);
        }
    }
}
