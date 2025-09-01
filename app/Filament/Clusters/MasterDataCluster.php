<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;
use BackedEnum;
use UnitEnum;

class MasterDataCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = null;

    protected static UnitEnum|string|null $navigationGroup = 'Financial Management';

    protected static ?string $navigationLabel = 'Master Data';

    protected static ?int $navigationSort = 1;
}