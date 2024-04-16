<?php
declare(strict_types=1);

namespace Autoframe\Components\Arr;

interface AfrArrCollectionInterface extends
    Export\AfrArrExportArrayAsStringInterface,
    Merge\AfrArrMergeProfileInterface,
    Sort\AfrArrXSortInterface,
    Sort\AfrArrSortBySubKeyInterface
{

}