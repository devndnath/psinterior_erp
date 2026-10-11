<div>
    @if ($documents->isEmpty())
        <x-empty-state
            title="No documents yet"
            description="Files shared with you will appear here."
        />
    @else
        <x-table>
            <thead>
                <tr>
                    <th>File</th>
                    <th>Category</th>
                    <th>Size</th>
                    <th>Uploaded</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($documents as $document)
                    <tr wire:key="document-{{ $document->id }}">
                        <td class="font-medium text-ink-900">{{ $document->file_name }}</td>
                        <td>{{ $document->category }}</td>
                        <td>{{ $document->humanFileSize() }}</td>
                        <td>{{ $document->created_at?->format('d M, Y') }}</td>
                        <td class="text-right">
                            <a href="{{ $document->url() }}" target="_blank" class="text-accent-600 hover:underline">Download</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-table>
    @endif
</div>
