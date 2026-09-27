<x-filament-panels::page>
    @if (! $this->userHasSpareAvailability())
        <x-empty-state
            icon="heroicon-o-user-group"
            heading="Set up your spare availability first"
            description="You need to set your own spare availability before you can view the spare list. Even if you're not available to spare, setting your preferences lets you access the list."
            :action="$this->getCreateUrl()"
            actionLabel="Set My Availability"
            actionIcon="heroicon-m-arrow-right"
        />
    @else
        {{-- Find a Spare Tool --}}
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-xs overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                <div class="flex items-center gap-3">
                    <x-filament::icon icon="heroicon-o-magnifying-glass" class="h-5 w-5 text-primary-600 dark:text-primary-400" />
                    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Find a Spare</h3>
                </div>
            </div>

            <div class="p-5">
                {{-- Day Selector --}}
                <div class="flex items-center gap-2 flex-wrap">
                    @foreach ($this->getWeekDays() as $day)
                        <button
                            wire:click="selectDay('{{ $day['key'] }}')"
                            type="button"
                            @class([
                                'relative flex flex-col items-center px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-150 min-w-[70px]',
                                'bg-primary-600 text-white shadow-md ring-2 ring-primary-600/20' => $findDay === $day['key'] && ! $customDate,
                                'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' => ($findDay !== $day['key'] || $customDate) && ! $day['is_past'],
                                'bg-gray-50 dark:bg-gray-800 text-gray-400 dark:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700' => ($findDay !== $day['key'] || $customDate) && $day['is_past'],
                            ])
                        >
                            <span class="text-xs font-bold uppercase tracking-wide">{{ $day['label'] }}</span>
                            <span @class([
                                'text-[11px] mt-0.5',
                                'text-primary-100' => $findDay === $day['key'] && ! $customDate,
                                'text-gray-500 dark:text-gray-400' => $findDay !== $day['key'] || $customDate,
                            ])>{{ $day['date'] }}</span>
                            @if ($day['is_today'])
                                <span @class([
                                    'absolute -top-1 -right-1 h-2.5 w-2.5 rounded-full border-2',
                                    'bg-white border-primary-600' => $findDay === $day['key'],
                                    'bg-primary-500 border-white dark:border-gray-700' => $findDay !== $day['key'],
                                ])></span>
                            @endif
                        </button>
                    @endforeach

                    {{-- Custom Date Pill or Calendar Button --}}
                    @if ($customDate)
                        <div class="flex items-center gap-1 ml-1">
                            <span class="flex items-center gap-2 px-4 py-2.5 rounded-lg bg-primary-600 text-white text-sm font-medium shadow-md ring-2 ring-primary-600/20 min-h-[52px]">
                                <x-filament::icon icon="heroicon-o-calendar" class="h-4 w-4 text-primary-100" />
                                {{ $customDate }}
                                <button
                                    wire:click="clearCustomDate"
                                    type="button"
                                    class="ml-1 inline-flex items-center justify-center h-5 w-5 rounded-full bg-primary-700 hover:bg-primary-800 text-white transition-colors"
                                    title="Clear custom date"
                                >
                                    <x-filament::icon icon="heroicon-m-x-mark" class="h-3 w-3" />
                                </button>
                            </span>
                        </div>
                    @else
                        <div class="ml-1">
                            {{ $this->pickDateAction }}
                        </div>
                    @endif
                </div>

                {{-- Results --}}
                @php
                    $spares = $this->getAvailableSpares();
                @endphp

                <div class="mt-5">
                    @if ($findDay === null)
                        <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">
                            Select a weekday to find available spares.
                        </p>
                    @elseif ($spares->isEmpty())
                        <div class="text-center py-6">
                            <x-filament::icon icon="heroicon-o-user-minus" class="mx-auto h-10 w-10 text-gray-300 dark:text-gray-600 mb-2" />
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">No spares available for {{ ucfirst($findDay) }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">Try another night or check back later.</p>
                        </div>
                    @else
                        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($spares as $spare)
                                <div class="flex items-center gap-3 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 p-3 hover:border-primary-300 dark:hover:border-primary-700 transition-colors duration-150">
                                    {{-- Avatar / Initial --}}
                                    <div class="flex-shrink-0 h-10 w-10 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center">
                                        <span class="text-sm font-bold text-primary-700 dark:text-primary-300">
                                            {{ strtoupper(substr($spare->user->name, 0, 1)) }}
                                        </span>
                                    </div>

                                    {{-- Name & Notes --}}
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">
                                            {{ $spare->user->name }}
                                        </p>
                                        @if ($spare->notes)
                                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate" title="{{ $spare->notes }}">
                                                {{ $spare->notes }}
                                            </p>
                                        @endif
                                    </div>

                                    {{-- Contact Actions --}}
                                    <div class="flex items-center gap-1.5 flex-shrink-0">
                                        @if ($spare->sms_enabled && $spare->phone_number)
                                            <a
                                                href="sms:{{ $spare->phone_number }}"
                                                class="inline-flex items-center justify-center h-8 w-8 rounded-lg bg-success-100 dark:bg-success-900/30 text-success-700 dark:text-success-400 hover:bg-success-200 dark:hover:bg-success-900/50 transition-colors"
                                                title="Send SMS to {{ $spare->user->name }}"
                                            >
                                                <x-filament::icon icon="heroicon-o-chat-bubble-left" class="h-4 w-4" />
                                            </a>
                                        @endif
                                        @if ($spare->call_enabled && $spare->phone_number)
                                            <a
                                                href="tel:{{ $spare->phone_number }}"
                                                class="inline-flex items-center justify-center h-8 w-8 rounded-lg bg-info-100 dark:bg-info-900/30 text-info-700 dark:text-info-400 hover:bg-info-200 dark:hover:bg-info-900/50 transition-colors"
                                                title="Call {{ $spare->user->name }}"
                                            >
                                                <x-filament::icon icon="heroicon-o-phone" class="h-4 w-4" />
                                            </a>
                                        @endif
                                        @if (! $spare->phone_number || (! $spare->sms_enabled && ! $spare->call_enabled))
                                            <span class="inline-flex items-center justify-center h-8 px-2 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-400 dark:text-gray-500 text-xs">
                                                No contact
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-3">
                            {{ $spares->count() }} {{ Str::plural('spare', $spares->count()) }} available for {{ ucfirst($findDay) }} night
                        </p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Full Spare List Table --}}
        <div class="mt-6">
            {{ $this->table }}
        </div>
    @endif
</x-filament-panels::page>
