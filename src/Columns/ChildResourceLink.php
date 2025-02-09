<?php

namespace SevendaysDigital\FilamentNestedResources\Columns;

use Filament\Support\Actions\Concerns\HasGroupedIcon;
use Filament\Support\Actions\Contracts\Groupable;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use SevendaysDigital\FilamentNestedResources\NestedResource;

class ChildResourceLink extends TextColumn implements Groupable
{
    use HasGroupedIcon;

    /**
     * @var NestedResource
     */
    private string $resourceClass;

    /**
     * @param  NestedResource  $name
     */
    public static function make(string $name): static
    {
        $item = parent::make($name);
        $item->forResource($name);
        $item->label($item->getChildLabelPlural());

        return $item;
    }

    public function getChildLabelPlural(): string
    {
        return Str::title($this->resourceClass::getPluralModelLabel());
    }

    public function getChildLabelSingular(): string
    {
        return Str::title($this->resourceClass::getModelLabel());
    }

    public function forResource(string $resourceClass): static
    {
        $this->resourceClass = $resourceClass;

        return $this;
    }

    public function getState(): string
    {
        $count = $this->getCount();

        return $count.' '.($count === 1 ? $this->getChildLabelSingular() : $this->getChildLabelPlural());
    }

    public function getUrl(): ?string
    {
        $baseParams = [];
        if (property_exists($this->table->getLivewire(), 'urlParameters')) {
            $baseParams = $this->table->getLivewire()->urlParameters;
        }
        
		$param = Str::camel(Str::singular($this->resourceClass::getParent()::getSlug()));

        return $this->resourceClass::getUrl(
            'index',
            [...$baseParams, $param => $this->record->getKey()]
        );
    }

    private function getCount(): int
    {
        return $this->resourceClass::getEloquentQuery($this->record->getKey())->count();
    }

    /**
     * Group actions
     * 
     * We need also to create new "view file" (resources\views\vendor\filament\tables\actions\grouped-action.blade.php)
     * contains the content of "vendor\sevendays-digital\filament-nested-resources\src\Table\Actions\Resources\Views\Actions\grouped-action.blade.php"
     * until we make it directly from package files
     *
     * @return static
     */
    public function grouped(): static
    {
        $this->view('vendor.filament.tables.actions.grouped-action');

        return $this;
    }

    /**
     * Used to override the function in "HasRecord" trait, because it throw an error when using "actions group"
     *
     * @param Model|null $record
     * @return static
     */
    public function record(Model $record = null): static
    {
        $this->record = $record;

        return $this;
    }
}
