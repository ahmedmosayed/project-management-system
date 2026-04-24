<div class="border rounded p-3 bg-light">
    <div class="fw-semibold small mb-2">{{ __('Attachments') }}</div>
    <ul class="list-unstyled small mb-2">
        @foreach ($attachments as $a)
            <li class="d-flex justify-content-between align-items-center py-1 border-bottom border-white">
                <a href="{{ route('task-attachments.download', $a) }}" class="text-truncate me-2">{{ $a->original_name }}</a>
                <button type="button" class="btn btn-sm btn-outline-danger py-0" wire:click="deleteAttachment({{ $a->id }})"
                        wire:confirm="{{ __('Remove this file?') }}">{{ __('Remove') }}</button>
            </li>
        @endforeach
    </ul>
    <div class="d-flex flex-wrap gap-2 align-items-center">
        <input type="file" class="form-control form-control-sm" wire:model="uploads" multiple wire:loading.attr="disabled">
        <button type="button" class="btn btn-sm btn-primary" wire:click="saveUploads" wire:loading.attr="disabled">{{ __('Upload') }}</button>
    </div>
    @error('uploads.*') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
    <div wire:loading wire:target="uploads,saveUploads" class="small text-muted mt-1">{{ __('Uploading…') }}</div>
</div>
