{{-- resources/views/filament/infolists/components/recipe-instructions-entry.blade.php --}}
<x-dynamic-component :component="$getEntryWrapperView()" :entry="$entry">
    @php
        $instructions = $getState() ?? []; // Expecting an array of instructions
        $globalStepCounter = 1;
    @endphp

    <style>
        .recipe-instructions {
            margin: 0;
            padding: 0;
        }

        .instruction-step {
            display: flex;
            gap: 12px;
            align-items: flex-start;
            margin-bottom: 16px;
        }

        .step-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 24px;
            height: 24px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 500;
            padding: 0 8px;
            flex-shrink: 0;
        }

        .step-content {
            flex: 1;
        }

        .step-name {
            font-weight: 600;
            margin: 0 0 4px 0;
        }

        .step-text {
            margin: 0;
            line-height: 1.5;
        }

        .instruction-section {
            margin-bottom: 24px;
        }

        .section-title {
            font-weight: 600;
            margin: 0 0 12px 0;
        }

        .section-steps {
            margin-left: 16px;
        }
    </style>

    <div class="recipe-instructions">
        @foreach ($instructions as $instruction)
            @if (($instruction['type'] ?? '') === 'step')
                <div class="instruction-step">
                    <span class="step-number fi-badge">{{ $globalStepCounter }}</span>
                    <div class="step-content">
                        <p class="step-text">
                            @if (!empty($instruction['name']))
                                <span class="step-name">{{ $instruction['name'] }} - </span>
                            @endif
                            {{ $instruction['text'] }}
                        </p>
                    </div>
                </div>
                @php $globalStepCounter++; @endphp
            @elseif (($instruction['type'] ?? '') === 'section')
                <div class="instruction-section">
                    <h3 class="section-title"># {{ $instruction['name'] ?? '' }}</h3>
                    <div class="section-steps">
                        @if (!empty($instruction['steps']) && is_array($instruction['steps']))
                            @foreach ($instruction['steps'] as $step)
                                <div class="instruction-step">
                                    <span class="step-number fi-badge">{{ $globalStepCounter }}</span>

                                    <div class="step-content">
                                        <p class="step-text">
                                            @if (!empty($step['name']))
                                                <span class="step-name">{{ $step['name'] }} - </span>
                                            @endif
                                            {{ $step['text'] }}
                                        </p>
                                    </div>
                                </div>
                                @php $globalStepCounter++; @endphp
                            @endforeach
                        @endif
                    </div>
                </div>
            @endif
        @endforeach
    </div>
</x-dynamic-component>
