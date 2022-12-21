<?php

namespace Buildix\Timex\Resources\EventResource\Pages;

use Buildix\Timex\Events\InteractWithEvents;
use Buildix\Timex\Traits\TimexTrait;
use Closure;
use Filament\Pages\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Buildix\Timex\Resources\EventResource;

class ListEvents extends ListRecords
{
    use TimexTrait;
    protected static string $resource = EventResource::class;

    protected function getActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery();

        if (in_array('participants',\Schema::getColumnListing(self::getEventTableName()))){
            $query->where('organizer','=',\Auth::id());
            $this->filterByParticipants($query);
        }

        return $query;
    }

    private function filterByParticipants(Builder $query): Builder
    {
        if($this->checkCurrentSqlDriver('sqlite')){
            return $query->orWhere('participants','like', '%"'.\Auth::id().'"%');        
        }

        return $query->orWhereJsonContains('participants', \Auth::id());
    }

    private function checkCurrentSqlDriver(string $driver): bool
    {
        $connection = config('database.default');
        $currentDriver = config("database.connections.{$connection}.driver");

        return $currentDriver == $driver;
    }
}
