<div class="space-y-4">
    <div class="space-y-3">
        @foreach ($endpoints as $endpoint)
            <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-3">
                <div class="flex items-center gap-2 mb-1">
                    <span class="inline-flex items-center rounded-md bg-primary-50 dark:bg-primary-900/20 px-2 py-1 text-xs font-medium text-primary-700 dark:text-primary-400">
                        {{ $endpoint['method'] }}
                    </span>
                    <code class="text-sm text-gray-700 dark:text-gray-300">{{ $endpoint['path'] }}</code>
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $endpoint['description'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="rounded-lg bg-gray-50 dark:bg-gray-800 p-4">
        <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Example (fetch):</p>
        <pre class="text-xs text-gray-600 dark:text-gray-400 overflow-x-auto"><code>const response = await fetch('{{ url('/api/v1/'.$type.'/'.$exampleSlug) }}');
const data = await response.json();</code></pre>
    </div>

    <div class="rounded-lg bg-gray-50 dark:bg-gray-800 p-4">
        <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Example (curl):</p>
        <pre class="text-xs text-gray-600 dark:text-gray-400 overflow-x-auto"><code>curl {{ url('/api/v1/'.$type.'/'.$exampleSlug) }}</code></pre>
    </div>

    <div class="rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 p-3">
        <p class="text-sm text-amber-700 dark:text-amber-400">
            These endpoints are public and require no authentication. Only published documents are returned.
        </p>
    </div>
</div>
