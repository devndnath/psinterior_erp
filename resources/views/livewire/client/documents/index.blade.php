<div>
    <div class="card overflow-hidden overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">File</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Category</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Size</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Uploaded</th>
                    <th></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse ($documents as $document)
                    <tr>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $document->file_name }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $document->category }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $document->humanFileSize() }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $document->created_at?->format('d M, Y') }}</td>
                        <td class="px-4 py-3 text-right text-sm">
                            <a href="{{ $document->url() }}" target="_blank" class="text-brand-600 hover:underline">Download</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-sm text-gray-400">No documents yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
