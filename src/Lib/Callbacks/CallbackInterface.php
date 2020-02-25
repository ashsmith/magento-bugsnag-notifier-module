<?php
declare(strict_types=1);
namespace Ashsmith\Bugsnag\Lib\Callbacks;

use Bugsnag\Report;

interface CallbackInterface
{
    public function report(Report $report);
}
