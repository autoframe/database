<?php
declare(strict_types=1);

namespace Autoframe\Components\Arr;

use Autoframe\Components\Arr\Export\AfrArrExportArrayAsStringTrait;
use Autoframe\Components\Arr\Merge\AfrArrMergeProfileTrait;
use Autoframe\Components\Arr\Sort\AfrArrSortBySubKeyTrait;
use Autoframe\Components\Arr\Sort\AfrArrXSortTrait;

trait AfrArrCollectionTrait
{
    use AfrArrMergeProfileTrait;
    use AfrArrSortBySubKeyTrait;
    use AfrArrXSortTrait;
    use AfrArrExportArrayAsStringTrait;
}

