<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Year Filter -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="max-w-xs">
                <x-filament::input.wrapper>
                    <x-filament::input.select wire:model.live="selectedYear">
                        @foreach($this->getYearOptions() as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </x-filament::input.select>
                </x-filament::input.wrapper>
            </div>
        </div>

        <!-- Financial Summary Cards -->
        <div>
            @foreach ($this->getHeaderWidgets() as $widget)
                @livewire($widget, ['selectedYear' => $selectedYear])
            @endforeach
        </div>

        <!-- Cash Flow Chart -->
        <div>
            @foreach ($this->getWidgets() as $widget)
                @livewire($widget, ['selectedYear' => $selectedYear])
            @endforeach
        </div>
    </div>
</x-filament-panels::page>