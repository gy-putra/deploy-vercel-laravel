<div class="space-y-4">
    @if($files && count($files) > 0)
        @foreach($files as $index => $file)
            @php
                // Generate proper file URL - handle both with and without storage link
                $fileUrl = Storage::disk('public')->url($file);
                $fileName = basename($file);
                $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                $fileSize = Storage::disk('public')->exists($file) ? Storage::disk('public')->size($file) : 0;
                $formattedSize = $fileSize > 0 ? number_format($fileSize / 1024, 2) . ' KB' : 'Unknown';
                
                // Alternative URL using custom route for pilgrim documents
                $alternativeUrl = url('/storage/' . $file);
                
                // Check if file actually exists
                $fileExists = Storage::disk('public')->exists($file);
            @endphp
            
            <div class="border rounded-lg p-4 bg-gray-50 dark:bg-gray-800">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h4 class="font-medium text-gray-900 dark:text-gray-100">
                            {{ $fileName }}
                        </h4>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Size: {{ $formattedSize }} | Type: {{ strtoupper($extension) }}
                            @if(!$fileExists)
                                <span class="text-red-500"> | File not found</span>
                            @endif
                        </p>
                    </div>
                    <div class="flex space-x-2">
                        @if($fileExists)
                            <a href="{{ $fileUrl }}" 
                               target="_blank"
                               class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-primary-600 border border-transparent rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                View
                            </a>
                            <a href="{{ $fileUrl }}" 
                               download="{{ $fileName }}"
                               class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-gray-200 border border-gray-300 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 dark:bg-gray-600 dark:text-gray-200 dark:border-gray-500 dark:hover:bg-gray-700">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Download
                            </a>
                        @else
                            <span class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-500 bg-gray-100 border border-gray-300 rounded-md cursor-not-allowed">
                                File not accessible
                            </span>
                        @endif
                    </div>
                </div>
                
                @if($fileExists)
                    @if(in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                        <!-- Image Preview -->
                        <div class="mt-3">
                            <div class="relative">
                                <img id="img-{{ $index }}" 
                                     src="{{ $fileUrl }}" 
                                     alt="{{ $fileName }}"
                                     class="max-w-full h-auto max-h-96 rounded-lg shadow-sm border mx-auto"
                                     loading="lazy"
                                     style="display: block;"
                                     onload="this.style.display='block'; this.nextElementSibling.style.display='none';"
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='block'; console.log('Image failed to load, trying alternative URL'); this.src='{{ $alternativeUrl }}'; setTimeout(() => { if(this.complete && this.naturalWidth == 0) { this.style.display='none'; this.nextElementSibling.style.display='block'; } else { this.style.display='block'; this.nextElementSibling.style.display='none'; } }, 1000);">
                                <div id="error-{{ $index }}" style="display:none;" class="p-4 bg-red-50 dark:bg-red-900/20 rounded-lg text-center border border-red-200 dark:border-red-800">
                                    <svg class="w-12 h-12 mx-auto mb-2 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.732 13.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                    </svg>
                                    <p class="text-sm text-red-600 dark:text-red-400">
                                        Image could not be loaded
                                    </p>
                                    <p class="text-xs text-red-500 dark:text-red-500 mt-1">
                                        The storage link may not be configured. To fix this, run: <code class="bg-gray-200 px-1 rounded">php artisan storage:link</code>
                                    </p>
                                    <div class="mt-2">
                                        <a href="{{ $fileUrl }}" target="_blank" class="text-primary-600 hover:text-primary-500 underline text-sm">
                                            Open image in new tab
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @elseif($extension === 'pdf')
                        <!-- PDF Preview -->
                        <div class="mt-3">
                            <div class="relative">
                                <iframe src="{{ $fileUrl }}" 
                                        class="w-full h-96 border rounded-lg"
                                        title="{{ $fileName }}"
                                        onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                </iframe>
                                <div style="display:none;" class="p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg text-center border border-yellow-200 dark:border-yellow-800">
                                    <svg class="w-12 h-12 mx-auto mb-2 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <p class="text-sm text-yellow-600 dark:text-yellow-400">
                                        PDF could not be displayed inline
                                    </p>
                                    <a href="{{ $fileUrl }}" target="_blank" class="text-primary-600 hover:text-primary-500 underline">
                                        Click here to open the PDF in a new tab
                                    </a>
                                </div>
                            </div>
                        </div>
                    @else
                        <!-- Other file types -->
                        <div class="mt-3 p-4 bg-gray-100 dark:bg-gray-700 rounded-lg text-center">
                            <svg class="w-12 h-12 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <p class="text-sm text-gray-600 dark:text-gray-300">
                                Preview not available for {{ strtoupper($extension) }} files
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                Click "View" or "Download" to access the file
                            </p>
                        </div>
                    @endif
                @else
                    <!-- File not found -->
                    <div class="mt-3 p-4 bg-red-50 dark:bg-red-900/20 rounded-lg text-center border border-red-200 dark:border-red-800">
                        <svg class="w-12 h-12 mx-auto mb-2 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.732 13.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                        <p class="text-sm text-red-600 dark:text-red-400">
                            File not found or not accessible
                        </p>
                        <p class="text-xs text-red-500 dark:text-red-500 mt-1">
                            The file may have been moved or the storage configuration needs to be set up
                        </p>
                    </div>
                @endif
            </div>
        @endforeach
    @else
        <div class="text-center py-8">
            <svg class="w-12 h-12 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <p class="text-gray-500 dark:text-gray-400">No documents uploaded</p>
        </div>
    @endif
</div>